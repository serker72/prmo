<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');
require_once('classes/pl_positem.php');
require_once('classes/pl_posgroup.php');
require_once('classes/pl_disitem.php');


require_once('classes/posdimitem.php');


require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

require_once('classes/sh_i_group.php');
require_once('classes/sh_i_item.php');

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/sh_i_notesgroup.php');
require_once('classes/sh_i_notesitem.php');

require_once('classes/acc_group.php');
require_once('classes/maxformer.php');

//require_once('classes/isitem.php');

require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/pl_disitem.php');
require_once('classes/pl_disgroup.php');
require_once('classes/pl_positem.php');
require_once('classes/pl_dismaxvalgroup.php');

require_once('classes/kpitem.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование распоряжения на отгрузку');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_ship=new ShIItem;
$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PosItem;
$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;
$_posgroupgroup=new PosGroupGroup;

$_supgroup=new SuppliersGroup;


$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();

$_kp=new KpItem;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;




if($action==0){
	if(!isset($_GET['bill_id'])){
		if(!isset($_POST['bill_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $bill_id=abs((int)$_POST['bill_id']);	
	}else $bill_id=abs((int)$_GET['bill_id']);
	
	//проверка наличия s4eta
	$bill=$_bill->GetItemById($bill_id);
	if($bill===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
	
}elseif(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_ship->GetItemByFields(array('id'=>$id,'is_incoming'=>0));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	$bill_id=$editing_user['bill_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
	
	
	
}



if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);


$object_id=array();
switch($action){
	case 0:
	$object_id[]=215;
	break;
	case 1:
	$object_id[]=219;
	$object_id[]=285;
	break;
	case 2:
	$object_id[]=226;
	break;
	default:
	$object_id[]=215;
	break;
}
//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


if($print!=0){
	if(!$au->user_rights->CheckAccess('w',285)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$_editable_status_id=array();
$_editable_status_id[]=1;






if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',215)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));;
	$params['bill_id']=abs((int)$_POST['bill_id']);
	
	$params['is_incoming']=0;
	
	
	
	if(strlen($_POST['pdate_shipping_plan'])==10) $params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
	
	$params['manager_id']=$result['id'];
	
	
	$params['is_confirmed']=0;
	
	
	$code=$_ship->Add($params);
	
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал распоряжение на отгрузку по исходящему счету',NULL,93,NULL,NULL,$bill_id);	
		
		
		$log->PutEntry($result['id'],'создал распоряжение на отгрузку по исходящему счету',NULL,215,NULL,NULL,$code);	
		
		
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',216))){
		//позиции
		$positions=array();
		
		
		$_pos=new plPosItem;
		$_pdi=new PosDimItem;
		
		
		$_pldi=new pldisitem;
		
		foreach($_POST as $k=>$v){
			if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_hash_","",$k);
			  
			  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			 
			  
			  if($_POST['new_has_pm_'.$hash]==0) $pms=NULL;
			  else{
				  $pms=array(
					  'plus_or_minus'=>abs((int)$_POST['new_plus_or_minus_'.$hash]),
					  'rub_or_percent'=>abs((int)$_POST['new_rub_or_percent_'.$hash]),
					  'value'=>(float)str_replace(",",".",$_POST['new_value_'.$hash])
				  );	
			  }
			  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			  //$kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>abs((int)$_POST['new_komplekt_ved_id_'.$hash]), 'position_id'=>$pos_id));
			  $pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
			  
			  $positions[]=array(
				  'sh_i_id'=>$code,				  
				  'position_id'=>$pos_id,
				  'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$hash]),
				  'pl_discount_id'=>abs((int)$_POST['new_pl_discount_id_'.$hash]),
				  'pl_discount_value'=>((float)str_replace(",",".",$_POST['new_pl_discount_value_'.$hash])),
				  'pl_discount_rub_or_percent'=>abs((int)$_POST['new_pl_discount_rub_or_percent_'.$hash]),
				  'kp_id'=>abs((int)$_POST['new_kp_id_'.$hash]),
				  
				  'name'=>SecStr($pos['name']),
				  'dimension'=>SecStr($dimension['name']),
				  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
				  'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
				  'price_f'=>(float)str_replace(",",".",$_POST['new_price_f_'.$hash]),
				  'price_pm'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash]),
				  'total'=>(float)str_replace(",",".",$_POST['new_total_'.$hash]),
				 
				  'pms'=>$pms
			  );
			  
		  }
			
		}
		//print_r($_POST);
		//print_r($positions);
		//die();
		//внесем позиции
		$_ship->AddPositions($code,$positions);
		
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'];
				
				
				
				$log->PutEntry($result['id'],'добавил позицию распоряжения на отгрузку', NULL, 93,NULL,$descr,$bill_id);	
				
				$log->PutEntry($result['id'],'добавил позицию распоряжения на отгрузку', NULL, 216,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: ed_bill.php?action=1&id=".$bill_id."#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',219)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_ship.php?action=1&id=".$code.'&from_begin=1');
		die();	
		
	}else{
		header("Location: ed_bill.php?action=1&id=".$bill_id);
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',219)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//редактирование возможно, если is_confirmed==0
	
	
	$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
		$params=array();
		//обычная загрузка прочих параметров
		
		//echo 'zzzzzzzzzzzzz'; die();
		
		
		if(strlen($_POST['pdate_shipping_plan'])==10) $params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
		
		
		
		$_ship->Edit($id, $params,$result);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='pdate_shipping_plan'){
					$log->PutEntry($result['id'],'редактировал плановую дату поставки',NULL,93,NULL,'дата: '.$_POST['pdate_shipping_plan'],$bill_id);
					
					$log->PutEntry($result['id'],'редактировал плановую дату поставки',NULL,219,NULL,'дата: '.$_POST['pdate_shipping_plan'],$id);
					continue;	
				}
				
				$log->PutEntry($result['id'],'редактировал распоряжение на отгрузку',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v,$bill_id);
				
				$log->PutEntry($result['id'],'редактировал распоряжение на отгрузку',NULL,219, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
			
		}
		
		if($au->user_rights->CheckAccess('w',216)){
		  $positions=array();
		  
		    $_pos=new PlPosItem;
		  $_pdi=new PosDimItem;
		  //$_kpi=new KomplPosItem;
		  
		  $_pldi=new pldisitem;
		 
		 
		  foreach($_POST as $k=>$v){
			  
			 // if(eregi("^new_position_id_([0-9]+)",$k)){
			if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
				  $hash=eregi_replace("^new_hash_","",$k);
				  
				  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
				   

				  
				  if($_POST['new_has_pm_'.$pos_id]==0) $pms=NULL;
				  else{
					  $pms=array(
						  'plus_or_minus'=>abs((int)$_POST['new_plus_or_minus_'.$hash]),
						  'rub_or_percent'=>abs((int)$_POST['new_rub_or_percent_'.$hash]),
						  'value'=>(float)str_replace(",",".",$_POST['new_value_'.$hash])
					  );	
				  }
				  
				  
				  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
				  $pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
				  
				  $positions[]=array(
					  'sh_i_id'=>$id,
					  
					  'position_id'=>$pos_id,
					  'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$hash]),
					'pl_discount_id'=>abs((int)$_POST['new_pl_discount_id_'.$hash]),
					'pl_discount_value'=>((float)str_replace(",",".",$_POST['new_pl_discount_value_'.$hash])),
					'pl_discount_rub_or_percent'=>abs((int)$_POST['new_pl_discount_rub_or_percent_'.$hash]),
					  'kp_id'=>abs((int)$_POST['new_kp_id_'.$hash]),
					  
					  'name'=>SecStr($pos['name']),
					  'dimension'=>SecStr($dimension['name']),
					  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
					  'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
					  'price_f'=>(float)str_replace(",",".",$_POST['new_price_f_'.$hash]),
					  'price_pm'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash]),
					  'total'=>(float)str_replace(",",".",$_POST['new_total_'.$hash]),
					  
					  'pms'=>$pms
				  );
				  
				  //print_r($pms);
			  }
		  }
		  //print_r($_POST);
		  //print_r($positions);
		  //внесем позиции
		  //die();
		  
		  
		  $log_entries=$_ship->AddPositions($id,$positions);
		  
		 
		  
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  $description=SecStr($v['name']).' <br /> Кол-во: '.$v['quantity'];
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию распоряжения на отгрузку',NULL,93,NULL,$description,$bill_id);	
				  $log->PutEntry($result['id'],'добавил позицию распоряжения на отгрузку',NULL,216,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию распоряжения на отгрузку',NULL,93,NULL,$description,$bill_id);
				  
				  $log->PutEntry($result['id'],'редактировал позицию распоряжения на отгрузку',NULL,217,NULL,$description,$id);
				  
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию распоряжения на отгрузку',NULL,93,NULL,$description,$bill_id);
				  
				  $log->PutEntry($result['id'],'удалил позицию распоряжения на отгрузку',NULL,218,NULL,$description,$id);
				  
			  }
			  
		  }
		}
		
	}
	
	
	
	//утверждение цен
	if($editing_user['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',225))||$au->user_rights->CheckAccess('w',96)){
			if(!isset($_POST['is_confirmed'])&&in_array($editing_user['status_id'],array(2,7,8))&&in_array($_POST['current_status_id'],array(2,7,8))){
			
				if($_ship->DocCanUnconfirm($id,$reas)){
				  $_ship->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение распоряжения на отгрузку',NULL,93, NULL, NULL,$bill_id);
				  
				  $log->PutEntry($result['id'],'снял утверждение распоряжения на отгрузку',NULL,225, NULL, NULL,$id);	
				}
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',224)||$au->user_rights->CheckAccess('w',96)){
			if(isset($_POST['is_confirmed'])&&($editing_user['status_id']==1)&&($_POST['current_status_id']==1)){
				$_ship->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил распоряжение на отгрузку',NULL,93, NULL, NULL,$bill_id);	
				
				$log->PutEntry($result['id'],'утвердил распоряжение на отгрузку',NULL,224, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: ed_bill.php?action=1&id=".$bill_id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',219)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_ship.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: ed_bill.php?action=1&id=".$bill_id);
		die();
	}
	
	die();
}






//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);



	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($orgitem['opf_id']);
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		
		$sm1->assign('bill_id',$bill_id);
		
		
		
		//поставщик
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($supplier['opf_id']);
		$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
		
		$sm1->assign('sdelka_string', 'Исходящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		//плановая дата поставки - из счета
		if($bill['pdate_shipping_plan']==0) $bill['pdate_shipping_plan']='-';
		else $bill['pdate_shipping_plan']=date("d.m.Y",$bill['pdate_shipping_plan']);
		
		$sm1->assign('pdate_shipping_plan' ,$bill['pdate_shipping_plan']);
		
		
		//передать позиции...
		$positions=array();
		$_bpf1=new BillPosPMFormer;
		$_mf=new MaxFormer;
		
		$total_cost=0; $total_nds=0; $was_get=false;
		$input_array=array();
		foreach($_GET as $k=>$v){
			//print_r($v);
			//сделать оптимизацию входных данных из счета
			if(eregi("^to_ship_",$k)){
				
				$_t_arr=explode(';',$v);
				
				 //url=url+"&"+$(value).attr("id")+"="+$("#new_position_id_"+thash).val()+";"+$("#new_pl_position_id_"+thash).val()+";"+$("#new_pl_discount_id_"+thash).val()+";"+$("#new_pl_discount_value_"+thash).val()+";"+$("#new_pl_discount_rub_or_percent_"+thash).val()+";"+value.value;
				
				
				$input_array[]=array(
					'position_id'=>$_t_arr[0],
					'pl_position_id'=>$_t_arr[1],
					'pl_discount_id'=>$_t_arr[2],
					'pl_discount_value'=>$_t_arr[3],
					'pl_discount_rub_or_percent'=>$_t_arr[4],
					'quantity'=>$_t_arr[5],
					'kp_id'=>$_t_arr[6]
				);
			}
		}
		
		$_pdm=new PlDisMaxValGroup;
		$_pli=new PlPosItem;
		
		
		foreach($input_array as $kk=>$vv){
			$was_get=true;
			
		
			$sql='select p.id as p_id, p.bill_id, p.position_id as position_id, p.pl_position_id as pl_position_id,
				p.pl_discount_id, p.pl_discount_value, p.pl_discount_rub_or_percent,	
				 p.kp_id as kp_id, kp.code as kp_code,
					 
				
				 p.name as position_name, p.dimension as dim_name, 
				 p.quantity, p.price, p.price_f, p.price_pm, p.total, 
				 pd.id as dimension_id,
				 pm.plus_or_minus, pm.value, pm.rub_or_percent			 
	
	from bill_position as p 
		left join bill_position_pm as pm on pm.bill_position_id=p.id
		left join catalog_dimension as pd on pd.name=p.dimension
		left join kp as kp on kp.id=p.kp_id
	where 
		p.bill_id="'.$bill_id.'" 
		and position_id="'.$vv['position_id'].'" 
		and pl_position_id="'.$vv['pl_position_id'].'" 
		and pl_discount_id="'.$vv['pl_discount_id'].'" 
		and pl_discount_value="'.$vv['pl_discount_value'].'" 
		and pl_discount_rub_or_percent="'.$vv['pl_discount_rub_or_percent'].'" 
		and kp_id="'.$vv['kp_id'].'" 
		
		
		';
		//echo $sql.'<br>';
			
			$set=new mysqlset($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			$h=mysqli_fetch_array($rs);
			
			
			$h['has_pm']=($h['plus_or_minus']!="");
		
			$pm=$_bpf1->Form($h['price_f'], $vv['quantity'],$h['has_pm'],$h['plus_or_minus'],$h['value'],$h['rub_or_percent'], $h['price_pm'], $h['total']);
			
			$h['price_pm']=$pm['price_pm'];
			$h['cost']=$pm['cost'];
			$h['total']=$pm['total'];
			
			$h['in_bill']=$h['quantity'];
			
			
			
			$h['not_in_bill']=$h['quantity']- $_mf->MaxInShI($h['bill_id'], $h['position_id'], $h['pl_position_id'],  $h['pl_discount_id'],  $h['pl_discount_value'],  $h['pl_discount_rub_or_percent'],  NULL,$h['kp_id']);
			
			$h['in_acc']=$_mf->MaxInAcc($h['bill_id'],  $h['position_id'], $h['pl_position_id'],  $h['pl_discount_id'],  $h['pl_discount_value'],  $h['pl_discount_rub_or_percent'],0, 0,NULL, $h['kp_id']);
			
			//обнулим незаполненный плюс/минус
			if($h['plus_or_minus']=="") $h['plus_or_minus']=0;
			if($h['rub_or_percent']=="") $h['rub_or_percent']=0;
			if($h['value']=="") $h['value']=0;
			
			$h['nds_summ']=sprintf("%.2f",($h['total']-$h['total']/((100+NDS)/100)));
			
			$total_cost+=$h['total'];
			$total_nds+=$h['nds_summ'];
			
			//также получить набор макс. скидок для позиции
			$max_vals=array();
			$max_vals=$_pdm->GetItemsByIdArr($h['pl_position_id']);
			
			$kp=$_kp->GetItemById($h['kp_id']);
			
			
			//print_r($h);
			if($vv['quantity']>0){
			  
			
			  $positions[]=array(
				 // 'hash'=>md5($pos_id.'_'.$h['komplekt_ved_id']),
				  'hash'=>md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent'].'_'.$h['kp_id']),
				  'pl_position_id'=>$h['pl_position_id'],
				  'position_id'=>$h['position_id'],
				  
				  'pl_discount_id'=>$h['pl_discount_id'],
				  'pl_discount_value'=>$h['pl_discount_value'],
				  'pl_discount_rub_or_percent'=>$h['pl_discount_rub_or_percent'],
				  
				  
				  'position_name'=>$h['position_name'],
				  'dim_name'=>$h['dim_name'],
				  'dimension_id'=>$h['dimension_id'],
				  'quantity'=>$vv['quantity'],
				  'price'=>$h['price'],
				  'price_f'=>$h['price_f'],
				  'price_pm'=>$h['price_pm'],
				  'has_pm'=>$h['has_pm'],
				  'cost'=>$h['cost'],
				  'total'=>$h['total'],
				  
				  
				  
				  'plus_or_minus'=>$h['plus_or_minus'],
				  'rub_or_percent'=>$h['rub_or_percent'],
				  'value'=>$h['value'],
				  'nds_proc'=>NDS,
				  'nds_summ'=>$h['nds_summ'],
				  'quantity_confirmed'=>$vv['quantity'],
				  'max_quantity'=>$vv['quantity'],
				  'in_rasp'=>0,
				  'in_bill'=>$h['in_bill'],
				  'not_in_bill'=>$h['not_in_bill'],
				  'in_acc'=>$h['in_acc'],
				  'discs1'=>$max_vals,
				  'kp_id'=>$h['kp_id'],
				  'kp_code'=>$kp['code']
			  );
			}
			
		}
		
		
		
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		$_pld=new PlDisGroup;
		$sm1->assign('discs1',$_pld->GetItemsArr());
		
		$sm1->assign('can_modify',true);
			 
		//$sm1->assign('total_cost',$total_cost);
		//$sm1->assign('total_nds',$total_nds);
		$sm1->assign('positions',$positions);
		
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',303)); 
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',216)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',218)); 
		
		$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',293)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',215)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',219)); 
		
		$user_form=$sm1->fetch('ships/ship_create.html');
		
		$sm->assign('accs','В данном режиме просмотр реализаций по распоряжению на отгрузку недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать распоряжение и перейти к утверждению" на вкладке "Распоряжение на отгрузку" для получения возможности просмотра реализаций.');	
		
	}elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		
		
		
		$sm1=new SmartyAdm;
		$_temp_bill=$_bill->GetItemById($editing_user['bill_id']);
			$sm1->assign('bill',$_temp_bill);
		if($print!=0){
			
			
		
		}	
		
		
		
		
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($orgitem['opf_id']);
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		
		//$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		//поставщик
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($supplier['opf_id']);
		$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
		
		$sm1->assign('sdelka_string', 'Исходящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'].' ('.$cu['login'].')';
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		//плановая дата поставки - из счета
		if($editing_user['pdate_shipping_plan']==0) $editing_user['pdate_shipping_plan']='-';
		else $editing_user['pdate_shipping_plan']=date("d.m.Y",$editing_user['pdate_shipping_plan']);
		
		$sm1->assign('pdate_shipping_plan' ,$editing_user['pdate_shipping_plan']);
		
		
		
		//фактическая дата поставки - увязана с реализации
		$_acg=new AccGroup;
			
		$dec2=new DBDecorator;
		
		$dec2->AddEntry(new SqlEntry('p.sh_i_id',$editing_user['id'], SqlEntry::E));
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$dec2->AddEntry(new UriEntry('is_confirmed_acc',1));
		
		$_acg->SetAuthResult($result);
		$ships=$_acg->ShowPos($editing_user['bill_id'],'bills/fact_dates.html', $dec2, $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',95), $au->user_rights->CheckAccess('w',96));
		
		$sm1->assign('fact_days',$ships); 	
		
		
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new ShIPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		$_bpf=new BillPosPMFormer;
		//получим виды скидок
		$_pld=new PlDisGroup;
		$sm1->assign('discs1',$_pld->GetItemsArr());
	//	$total_cost=$_bpf->CalcCost($bpg);
	//	$total_nds=$_bpf->CalcNDS($bpg);
	//	$sm1->assign('total_cost',$total_cost);
	//	$sm1->assign('total_nds',$total_nds);
		
		
		
		//Примечания
		$rg=new ShINotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,($editing_user['is_confirmed']==1),$au->user_rights->CheckAccess('w',341), $au->user_rights->CheckAccess('w',350),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',220));
		
		
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ship->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',226);
		if(!$au->user_rights->CheckAccess('w',226)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ship->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$editing_user['can_restore']=$_ship->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',227);
			if(!$au->user_rights->CheckAccess('w',227)) $reason='недостаточно прав для данной операции';
		
		
		$sm1->assign('ship',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		//$sm1->assign('can_modify',$editing_user['is_confirmed']==0);
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
		
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed']==1){
			if($au->user_rights->CheckAccess('w',96)){
				//полные права
				$can_confirm_price=true;	
			}elseif($au->user_rights->CheckAccess('w',225)){
				//есть права + сам утвердил
				$can_confirm_price=true;	
			}else{
				$can_confirm_price=false;
			}
		}else{
			//95
			$can_confirm_price=$au->user_rights->CheckAccess('w',224)&&($editing_user['status_id']==1);
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		$_acg=new AccGroup;
		
		
		$cannot_edit_reason='';
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',303)&&(in_array($editing_user['status_id'],$_editable_status_id))&&$_ship->CanEditQuantities($editing_user['id'],$cannot_edit_reason,$editing_user)); 
		if(strlen($cannot_edit_reason)>0) $cannot_edit_reason.=', либо ';
		$sm1->assign('cannot_edit_reason',$cannot_edit_reason);
		
		
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',216)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',218)); 
		$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',293)); 
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',285)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',215)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',219)); 
		
		$sm1->assign('can_make_acceptance', $au->user_rights->CheckAccess('w',229));
		
		//проверка закрыотого периода
		$not_in_closed_period=$_ship->CheckClosePdate($editing_user['id'], $closed_period_reason, $editing_user);
		$sm1->assign('not_in_closed_period', $not_in_closed_period);
		$sm1->assign('closed_period_reason', $closed_period_reason);
		
		
		$cannot_select_positions=true;
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',229);
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',293);
		
		$cannot_select_positions=$cannot_select_positions&&(!$au->user_rights->CheckAccess('w',303));
		
		$sm1->assign('cannot_select_positions', $cannot_select_positions);
		
		
		$print_add1=$print_add;
		if(($_temp_bill['interstore_id']!=0)&&($print!=0)) $print_add1=$print_is_add.$print_add;
		$user_form=$sm1->fetch('ships/ship_edit'.$print_add1.'.html');
		
		
		//реестр распоряжений
		
		$dec2=new DBDecorator;
		
		
		$_acg->SetPagename('ed_ship.php');
			
			
			
			
		if(isset($_GET['acc_status_id'])){
			if($_GET['acc_status_id']>0){
					  $dec2->AddEntry(new SqlEntry('p.status_id',abs((int)$_GET['acc_status_id']), SqlEntry::E));
				  }
			$dec2->AddEntry(new UriEntry('acc_status_id',$_GET['acc_status_id']));
		}else{
		
		  if(isset($_COOKIE['acc_status_id'])){
				  $acc_status_id=$_COOKIE['acc_status_id'];
		  }else $acc_status_id=0;
		  
		  if($acc_status_id>0) $dec2->AddEntry(new SqlEntry('p.status_id',$acc_status_id, SqlEntry::E));
		  $dec2->AddEntry(new UriEntry('acc_status_id',$acc_status_id));
		}
			
		
		
		$dec2->AddEntry(new SqlEntry('sh_i_id',$editing_user['id'], SqlEntry::E));
		$dec2->AddEntry(new UriEntry('sh_i_id',$editing_user['id']));
			
		
			
		
			
		$_acg->SetAuthResult($result);
		$accs=$_acg->ShowPos($editing_user['bill_id'],'acc/acc_list.html', $dec2, $au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',242), $au->user_rights->CheckAccess('w',240), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',243), $limited_sector, NULL,$au->user_rights->CheckAccess('w',241) );
		
		$sm->assign('accs',$accs); 	
			
		
		
		
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',525)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			if(!isset($_GET['pdate1'])){
			
					$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
					$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
				
			}else $pdate1 = $_GET['pdate1'];
			
			
			
			if(!isset($_GET['pdate2'])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			}else $pdate2 = $_GET['pdate2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
			$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
			
			
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(199,
214,
215,
216,
217,
218,
219,
220,
221,
222,
223,
224,
225,
285,
226,
227,
293)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('do_show_log',1));
			if(!isset($_GET['do_show_log'])){
				$do_show_log=false;
			}else{
				$do_show_log=true;
			}
			$sm->assign('do_show_log',$do_show_log);
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log.html',$decorator,$from,$to_page,'ed_ship.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	
	$content=$sm->fetch('ships/ed_ship_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>