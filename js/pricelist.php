<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');

require_once('../classes/pl_positem.php');
require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_dismaxvalitem.php');
require_once('../classes/pl_disitem.php');

require_once('../classes/pl_posgroup_forbill.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="edit_items")){
	if(!$au->user_rights->CheckAccess('w',602)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	//print_r($_POST);
	
	//die();
	/*
	"checked_ids[]":checked_ids,
						"price[]":price,
						"discount_id[]":discount_id,
						"discount_value[]":discount_value,
						"discount_rub_or_percent[]":discount_rub_or_percent,
						"dl_value[]":dl_value,
						"dl_rub_or_percent[]":dl_rub_or_percent*/
						
						
						
	$checked_ids=$_POST['checked_ids'];
	$_pi=new PlPosItem;
	
	foreach($checked_ids as $k=>$v){
		$id=abs((int)$v);
		
		$pi=$_pi->GetItemById($id);
		
		if($pi!==false){	
			$params=array();
			$params['price']=abs((float)str_replace(",",".",$_POST['price'][$k]));
			
			//определим, какая скидка активна - заносим ее
			//если неактивно ни одной - обнуляем скидку
			$discount_ids=explode(',',$_POST['discount_id'][$k]);
			$discount_values=explode(',',$_POST['discount_value'][$k]);
			$discount_rub_or_percents=explode(',',$_POST['discount_rub_or_percent'][$k]);
			$dl_values=explode(',',$_POST['dl_value'][$k]);
			$dl_rub_or_percents=explode(',',$_POST['dl_rub_or_percent'][$k]);
			
			
			$active_discount_offset=-1;
			$active_discount_id=0;
			foreach($discount_values as $kk=>$vv){
				if(abs((float)str_replace(",",".",$vv))>0){
					$active_discount_offset=$kk;
					$active_discount_id=abs((int)$discount_ids[$kk]);
					break;
				}
			}
			
			
			if($active_discount_id!=0){
				$params['discount_id']=$active_discount_id;
				$params['discount_value']=abs((float)str_replace(",",".",$discount_values[$active_discount_offset]));
				$params['discount_rub_or_percent']=abs((int)$discount_rub_or_percents[$active_discount_offset]);
				
			}else{
				$params['discount_id']=1;
				$params['discount_value']=0;
				$params['discount_rub_or_percent']=0;
			}
			
			
			
			$_pi->Edit($id, $params);
			
			foreach($params as $kk=>$vv){
				if($pi[$kk]!=$vv){	
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$id);
				
				}
			}
			
			
			//внесем ограничения на скидку
			//разобрать блок ограничений
			//если текущее ограничение равно пустой строке - удалить если есть такое
			//если равно строке - внести строку
			if($au->user_rights->CheckAccess('w',605)) foreach($discount_ids as $kk=>$vv){
				$_mvi=new PlDisMaxValItem;
				$_test_mvi=$_mvi->GetItemByFields(array('pl_position_id'=>$id, 'discount_id'=>abs((int)$vv)));
				$_dis=new PlDisItem;
				$_test_dis=$_dis->GetItemById($vv);
				
				if(trim($dl_values[$kk])==""){
					if($_test_mvi!==false){
						$_mvi->Del($_test_mvi['id']);
						//запись в журнал о снятии ограничения	
						$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': удалено максимальное значение поля '.SecStr($_test_dis['name']),$id);
					}
				}else{
					
					if($_test_mvi!==false){
						$m_params=array();
						/*$m_params['pl_position_id']=$id;
						$m_params['discount_id']=abs((int)$vv);*/
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						
						$_mvi->Edit($_test_mvi['id'], $m_params);
					}else{
						$m_params=array();
						$m_params['pl_position_id']=$id;
						$m_params['discount_id']=abs((int)$vv);
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						$_mvi->Add($m_params);
					}
					if($m_params['rub_or_percent']==0) $descr='руб.';
					elseif($m_params['rub_or_percent']==1) $descr='%';
					
					//запись в журнал об установке ограничения
					$log->PutEntry($result['id'],'редактировал позицию прайс-листа',NULL,602, NULL,'позиция '.SecStr($pi['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$id);
				}
			}
			
		}
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_items")){
	if(!$au->user_rights->CheckAccess('w',601)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
			
	//print_r($_POST);
	
	/*die();
	*/
						
	$checked_ids=$_POST['checked_ids'];
	$_pi=new PlPosItem;
	$_ppi=new PosItem;
	
	foreach($checked_ids as $k=>$v){
		$id=abs((int)$v);
		
		$ppi=$_ppi->GetItemById($id);
		$pi=$_pi->GetItemByFields(array('position_id'=>$id));
		
		if(($ppi!==false)&&($pi===false)){	
			$params=array();
			$params['position_id']=$id;
			$params['price']=abs((float)str_replace(",",".",$_POST['price'][$k]));
			
			
			//определим, какая скидка активна - заносим ее
			//если неактивно ни одной - обнуляем скидку
			$discount_ids=explode(',',$_POST['discount_id'][$k]);
			$discount_values=explode(',',$_POST['discount_value'][$k]);
			$discount_rub_or_percents=explode(',',$_POST['discount_rub_or_percent'][$k]);
			$dl_values=explode(',',$_POST['dl_value'][$k]);
			$dl_rub_or_percents=explode(',',$_POST['dl_rub_or_percent'][$k]);
			
			
			$active_discount_offset=-1;
			$active_discount_id=0;
			foreach($discount_values as $kk=>$vv){
				if(abs((float)str_replace(",",".",$vv))>0){
					$active_discount_offset=$kk;
					$active_discount_id=abs((int)$discount_ids[$kk]);
					break;
				}
			}
			
			
			if($active_discount_id!=0){
				$params['discount_id']=$active_discount_id;
				$params['discount_value']=abs((float)str_replace(",",".",$discount_values[$active_discount_offset]));
				$params['discount_rub_or_percent']=abs((int)$discount_rub_or_percents[$active_discount_offset]);
				
			}else{
				$params['discount_id']=1;
				$params['discount_value']=0;
				$params['discount_rub_or_percent']=0;
			}
			
			//print_r($params);
			
			
			
			
			
			$code=$_pi->Add($params);
			
			foreach($params as $kk=>$vv){
			
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($ppi['name']).': в поле '.$kk.' установлено значение '.SecStr($vv),$code);
				
				
			}
			
			
			//внесем ограничения на скидку
			//разобрать блок ограничений
			//если текущее ограничение равно пустой строке - удалить если есть такое
			//если равно строке - внести строку
			if($au->user_rights->CheckAccess('w',605)) foreach($discount_ids as $kk=>$vv){
				$_mvi=new PlDisMaxValItem;
				$_test_mvi=$_mvi->GetItemByFields(array('pl_position_id'=>$code, 'discount_id'=>abs((int)$vv)));
				$_dis=new PlDisItem;
				$_test_dis=$_dis->GetItemById($vv);
				
				if(trim($dl_values[$kk])!=""){
					if($_test_mvi!==false){
						$m_params=array();
						/*$m_params['pl_position_id']=$id;
						$m_params['discount_id']=abs((int)$vv);*/
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						
						$_mvi->Edit($_test_mvi['id'], $m_params);
					}else{
						$m_params=array();
						$m_params['pl_position_id']=$code;
						$m_params['discount_id']=abs((int)$vv);
						$m_params['value']=abs((float)$dl_values[$kk]);
						$m_params['rub_or_percent']=abs((int)$dl_rub_or_percents[$kk]);
						$_mvi->Add($m_params);
					}
					if($m_params['rub_or_percent']==0) $descr='руб.';
					elseif($m_params['rub_or_percent']==1) $descr='%';
					
					//print_r($m_params);
					
					//запись в журнал об установке ограничения
					$log->PutEntry($result['id'],'создал позицию прайс-листа',NULL,601, NULL,'позиция '.SecStr($ppi['name']).': установлено максимальное значение поля '.SecStr($_test_dis['name']).': '.$m_params['value'].' '.$descr,$id);
				}
			}
			
			
			
			
			
		}
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_pos")){

//получим список позиций по фильтру
	$_pg=new PlPosGroupForBill;
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	//$except_id=abs((int)$_POST['except_id']);
	//$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	$except_ids=$_POST['except_ids'];
	if(count($except_ids)>0){
		$dec->AddEntry(new SqlEntry('pl.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_ids));		
		
	}
	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	
	//if($group_id>0) $dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('p.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('p.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('p.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('p.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('p.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('p.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	$_pg->itemsname='pospos';
	
	//нужен другой метод, который бы возвращал данные в нужном формате
	
	
	$_pg->ShowPos('bills/position_edit_finded.html', $dec,0,1000,false,false,true);
	
	$items=$_pg->items;
	
	//добавим стоимости, кол-во
	foreach($items as $k=>$v){
		
		$items[$k]['quantity']=0;	
		$items[$k]['price_pm']=$items[$k]['price_f'];
		$items[$k]['cost']=0;
		$items[$k]['total']=0;
		$items[$k]['nds_proc']=NDS;
		$items[$k]['nds_summ']=0;
		$items[$k]['nds_summ']=0;
		$items[$k]['value']=0;
		$items[$k]['discount_value']=0;
		/*$items[$k]['pl_discount_id']=1;
		$items[$k]['pl_discount_value']=0;
		$items[$k]['pl_discount_rub_or_percent']=0;*/
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent']);
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	$ret=$sm->fetch('bills/position_edit_finded.html');
	
	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>