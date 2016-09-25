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

require_once('classes/posdimitem.php');
require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/kpitem.php');
require_once('classes/kppositem.php');
require_once('classes/kpposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');


require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/kpnotesgroup.php');
require_once('classes/kpnotesitem.php');

require_once('classes/kpcreator.php');

require_once('classes/pergroup.php');

require_once('classes/period_checker.php');

require_once('classes/pl_disitem.php');
require_once('classes/pl_disgroup.php');
require_once('classes/pl_positem.php');
require_once('classes/pl_dismaxvalgroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');


$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование коммерческого предложения');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new KpItem;
$_bpi=new KpPosItem;
$_position=new PosItem;


$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;


$lc=new KpCreator;

$_supgroup=new SuppliersGroup;
$_opf=new OpfItem;


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',712)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;


$object_id=array();
switch($action){
	case 0:
	$object_id[]=696;
	break;
	case 1:
	$object_id[]=701;
	$object_id[]=712;
	break;
	case 2:
	$object_id[]=713;
	break;
	default:
	$object_id[]=696;
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

$_editable_status_id=array();
$_editable_status_id[]=1;

if($action==0){
	$orgitem=$_orgitem->getitembyid($result['org_id']);	
}

if(($action==1)||($action==2)){
	
	
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_bill->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',696)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	
	$params['contract_id']=abs((int)$_POST['contract_id']);
	
	
	
	$params['bdetails_id']=abs((int)$_POST['bdetails_id']);
	
	
	$params['code']=SecStr($_POST['code']);
	
	$params['code']=SecStr($_POST['code']);
	
	
	$params['is_confirmed_price']=0;
	$params['is_confirmed_shipping']=0;
	
	
	$params['manager_id']=$result['id'];
	
	
	$params['supplier_bill_no']=SecStr($_POST['supplier_bill_no']);
	
	
	if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);
	
	
	$params['valid_pdate']=DateFromdmY($_POST['valid_pdate']);
	
	$code=$_bill->Add($params);
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал коммерческое предложение',NULL,696,NULL,NULL,$code);	
		
		
		
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',697))){
		//позиции
		$positions=array();
		
		$_pos=new plPosItem;
		$_pdi=new PosDimItem;
		
		
		$_pldi=new pldisitem;
			
		foreach($_POST as $k=>$v){
		  if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_hash_","",$k);
			  
			  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			 
			  
			  $pms=NULL;
			  
			  
			 
			  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			  $pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
			  $positions[]=array(
				  'kp_id'=>$code,
				  
				  'position_id'=>$pos_id,
				  'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$hash]),
				  'pl_discount_id'=>abs((int)$_POST['new_pl_discount_id_'.$hash]),
				  'pl_discount_value'=>((float)str_replace(",",".",$_POST['new_pl_discount_value_'.$hash])),
				  'pl_discount_rub_or_percent'=>abs((int)$_POST['new_pl_discount_rub_or_percent_'.$hash]),
				  
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
			
		
		/*
		echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die();*/
		//внесем позиции
		$_bill->AddPositions($code,$positions);
		//die();
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'].'<br /> цена '.$v['price'].' руб. <br />';
				$descr.=' цена со скидкой '.$v['price_f'].' руб. <br />';
				if($v['pl_discount_value']>0){
					$dis=$_pldi->GetItemById($v['pl_discount_id']);
					$descr.=SecStr($dis['name']).' '.$v['pl_discount_value'].'';
					if($v['pl_discount_rub_or_percent']==0) $descr.=' руб. <br />';
					else  $descr.=' % <br />';
				}
				
				
				
				$log->PutEntry($result['id'],'добавил позицию коммерческого предложения', NULL, 701,NULL,$descr,$code);	
				
				//сравнить цены и скидки позиции в счете и в прайс-листе, если отличаются - создать автопримечания
				if($pos!==false){
					if($pos['price']!=$v['price']){
						$descr=SecStr($pos['name']).'<br /> цена по прайс-листу '.$pos['price'].' руб. <br />цена в коммерческом предложении '.$v['price'].' руб. <br />';
						
						$log->PutEntry($result['id'],'изменение цены в коммерческом предложении', NULL, 701,NULL,$descr,$code);	
						$_kni=new KpNotesItem;
						$notes_params=array();
						$notes_params['is_auto']=1;
						$notes_params['user_id']=$code;
						$notes_params['pdate']=time();
						$notes_params['posted_user_id']=$result['id'];
					
					
						$notes_params['note']='Автоматическое примечание: при создании коммерческого предложения была изменена цена позиции прайс-листа: позиция '.$descr;
						$_kni->Add($notes_params);
					}
					if(($pos['discount_value']!=$v['pl_discount_value'])||($pos['discount_rub_or_percent']!=$v['pl_discount_rub_or_percent'])||($pos['discount_id']!=$v['pl_discount_id'])){
					    $dis=$_pldi->GetItemById($v['pl_discount_id']);
						
						$descr=SecStr($pos['name']).'<br /> установлена '.SecStr($dis['name']).' ';
						$descr.=$v['pl_discount_value'];
						if($v['pl_discount_rub_or_percent']==0) $descr.=' руб.';
						else $descr.='%';
						
						$log->PutEntry($result['id'],'изменение скидки в коммерческом предложении', NULL, 701,NULL,$descr,$code);	
						
						$_kni=new KpNotesItem;
						$notes_params=array();
						$notes_params['is_auto']=1;
						$notes_params['user_id']=$code;
						$notes_params['pdate']=time();
						$notes_params['posted_user_id']=$result['id'];
					
					
						$notes_params['note']='Автоматическое примечание: при создании коммерческого предложения была изменена скидка: позиция '.$descr;
						$_kni->Add($notes_params);
					}
				}
			}
		}	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: kps.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',701)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_kp.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: kps.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',701)){
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
		
		
		if(isset($_POST['supplier_id'])) $params['supplier_id']=abs((int)$_POST['supplier_id']);
	
		if(isset($_POST['contract_id'])) $params['contract_id']=abs((int)$_POST['contract_id']);
	
		
		
		
		
		if(isset($_POST['bdetails_id'])) $params['bdetails_id']=abs((int)$_POST['bdetails_id']);
		
		
		
		if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);
		
		if(strlen($_POST['valid_pdate'])==10) $params['valid_pdate']=DateFromdmY($_POST['valid_pdate']);
		
		$_bill->Edit($id, $params,false,$result);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				$log->PutEntry($result['id'],'редактировал коммерческое предложение',NULL,701, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
	}
	
	
	
	$condition_positions=$condition;
	//правим позиции. можно их править, если у счета не утв. отгрузка....
	$condition_positions=$condition_positions||(($editing_user['status_id']!=3)&&($editing_user['is_confirmed_price']==0))
	;
	
	
	//правим позиции	
	if($condition_positions){	
		
		
		if($au->user_rights->CheckAccess('w',697)){
		  $positions=array();
		  
		  $_pos=new PlPosItem;
		  $_pdi=new PosDimItem;
		  //$_kpi=new KomplPosItem;
		  
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
						'value'=>(float)str_replace(",",".",$_POST['new_value_'.$hash]),
						
						'discount_rub_or_percent'=>abs((int)$_POST['new_discount_rub_or_percent_'.$hash]),
						'discount_value'=>(float)str_replace(",",".",$_POST['new_discount_value_'.$hash])
					);	
				}
				$dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			  	$pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
				$positions[]=array(
					'kp_id'=>$id,
					'position_id'=>$pos_id,
					'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$hash]),
					'pl_discount_id'=>abs((int)$_POST['new_pl_discount_id_'.$hash]),
					'pl_discount_value'=>((float)str_replace(",",".",$_POST['new_pl_discount_value_'.$hash])),
					'pl_discount_rub_or_percent'=>abs((int)$_POST['new_pl_discount_rub_or_percent_'.$hash]),
					
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
		  
		  
		/*  echo '<pre>';
		  //print_r($_POST);
		  print_r($positions);
		  echo '</pre>';
		  die();
		 */
		  //внесем позиции
		  
		  
		  
		 $can_change_cascade=false;
		  $log_entries=$_bill->AddPositions($id,$positions,$can_change_cascade,false,$result);
		  
		 
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  
			  $pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
			  
			  $description=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'].'<br /> цена '.$v['price'].' руб. <br />';
				$description.=' цена со скидкой '.$v['price_f'].' руб. <br />';
				if($v['pl_discount_value']>0){
					$dis=$_pldi->GetItemById($v['pl_discount_id']);
					$description.=SecStr($dis['name']).' '.$v['pl_discount_value'].'';
					if($v['pl_discount_rub_or_percent']==0) $description.=' руб. <br />';
					else  $description.=' % <br />';
				}
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию коммерческого предложения',NULL,701,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию коммерческого предложения',NULL,701,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию коммерческого предложения',NULL,701,NULL,$description,$id);
			  }
			  
			  if(($v['action']==0)||($v['action']==1)){
				  //сравнить цены и скидки позиции в счете и в прайс-листе, если отличаются - создать автопримечания
				  if($pos!==false){
					  if($pos['price']!=$v['price']){
						  $descr=SecStr($pos['name']).'<br /> цена по прайс-листу '.$pos['price'].' руб. <br />цена во исходящем счете '.$v['price'].' руб. <br />';
						  
						  $log->PutEntry($result['id'],'изменение цены в коммерческом предложении', NULL, 701,NULL,$descr,$id);	
						  $_kni=new KpNotesItem;
						  $notes_params=array();
						  $notes_params['is_auto']=1;
						  $notes_params['user_id']=$id;
						  $notes_params['pdate']=time();
						  $notes_params['posted_user_id']=$result['id'];
					  
					  
						  $notes_params['note']='Автоматическое примечание: при редактировании коммерческого предложения была изменена цена позиции прайс-листа: позиция '.$descr;
						  $_kni->Add($notes_params);
					  }
					  if(($pos['discount_value']!=$v['pl_discount_value'])||($pos['discount_rub_or_percent']!=$v['pl_discount_rub_or_percent'])||($pos['discount_id']!=$v['pl_discount_id'])){
						  $dis=$_pldi->GetItemById($v['pl_discount_id']);
						  
						  $descr=SecStr($pos['name']).'<br /> установлена '.SecStr($dis['name']).' ';
						  $descr.=$v['pl_discount_value'];
						  if($v['pl_discount_rub_or_percent']==0) $descr.=' руб.';
						  else $descr.='%';
						  
						  $log->PutEntry($result['id'],'изменение скидки в коммерческом предложении', NULL, 701,NULL,$descr,$id);	
						  
						  $_kni=new KpNotesItem;
						  $notes_params=array();
						  $notes_params['is_auto']=1;
						  $notes_params['user_id']=$id;
						  $notes_params['pdate']=time();
						  $notes_params['posted_user_id']=$result['id'];
					  
					  
						  $notes_params['note']='Автоматическое примечание: при редактировании коммерческого предложения была изменена скидка: позиция '.$descr;
						  $_kni->Add($notes_params);
					  }
				  }  
			  }
			  
			  
			  
			  
			}
		  }
		 
		
		}
	}
	
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed_shipping']==0){
	  if($editing_user['is_confirmed_price']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',711))||$au->user_rights->CheckAccess('w',96)){
			  if((!isset($_POST['is_confirmed_price']))&&in_array($editing_user['status_id'], array(2,9,10))&&in_array($_POST['current_status_id'], array(2,9,10))){
				  
				  //&&($editing_user['status_id']==5)&&($_POST['current_status_id']==5)
				  $_bill->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение цен',NULL,711, NULL, NULL,$id);	
				 // $_bill->FreeBindedPayments($id);
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',709)||$au->user_rights->CheckAccess('w',96)){
			  if(isset($_POST['is_confirmed_price'])&&($_POST['is_confirmed_price']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))){
				  
				  $_bill->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил цены',NULL,709, NULL, NULL,$id);	
				  
				  //если выбран режим - заносим в оплаты
				//  if(isset($_POST['can_add_to_payments'])&&($_POST['can_add_to_payments']==1)) $_bill->BindPayments($id,$result['org_id']);	  
				  //die();
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	
	

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: kps.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',701)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_kp.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: kps.php");
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
	
	$opf=$_opf->GetItemById($orgitem['opf_id']);
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		//поставщики
		$_supgroup->GetItemsForBill('kp/suppliers_list.html', new DBDecorator, false, $supgroup, $result);
		
		$sm1->assign('suppliers',$supgroup);
		
		
			
		
		//передать позиции...
		$positions=array();
		
		$_pdm=new PlDisMaxValGroup;
		$_pli=new PlPosItem;
		$_bpf=new BillPosPMFormer;
		
		/*foreach($_GET['pl_position_id'] as $k=>$v){
			
				$pos_id=abs((int)$v); //eregi_replace("^pl_position_id","",$k));
				$qua=1 ;//((float)$v);	
				
				
				$sql='select 
				p.id as pl_position_id,  p.price as price, p.discount_id as pl_discount_id,	p.discount_value as pl_discount_value, p.discount_rub_or_percent as pl_discount_rub_or_percent,
				
				pos.name as position_name, pos.id as position_id,
				
				dim.name as dim_name, dim.id as dimension_id 
				
				
			from pl_position as p 
				inner join catalog_position as pos on p.position_id=pos.id 
				left join catalog_dimension as dim on pos.dimension_id=dim.id 
				
				where p.id="'.$pos_id.'" order by position_name asc, p.id asc';
				
				
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				$h=mysqli_fetch_array($rs);
				//print_r($h);
				
				//также получить набор макс. скидок для позиции
				$max_vals=array();
				$max_vals=$_pdm->GetItemsByIdArr($h['pl_position_id']);
				
				
				if($qua>0){
					
					
				  $price_f=$_pli->CalcPriceF($h['pl_position_id']);
				  
				  
				 
				  
				  
				  $positions[]=array(
					  'pl_position_id'=>$pos_id,
					  'position_id'=>$h['position_id'],
					  'hash'=>md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent']),
					  'position_name'=>$h['position_name'],
					  'dim_name'=>$h['dim_name'],
					  'dimension_id'=>$h['dimension_id'],
					  'quantity'=>$qua,
					  'price'=>$h['price'],
					  'price_f'=>$price_f,
					  'price_pm'=>$price_f,
					  'has_pm'=>false,
					  'cost'=>$price_f,
					  'total'=>$price_f,
					  'plus_or_minus'=>0,
					  'rub_or_percent'=>0,
					  'value'=>0,
					  'in_rasp'=>0,
					  
					  'discount_rub_or_percent'=>0,
					  'discount_value'=>0,
					  'nds_proc'=>NDS,
					  'nds_summ'=>sprintf("%.2f",($price_f-$price_f/((100+NDS)/100))),
					  'pl_discount_id'=>	$h['pl_discount_id'],
					  'pl_discount_value'=>	$h['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>	$h['pl_discount_rub_or_percent'],
					  'discs1'=>$max_vals
					  
											  
				  );
				  
				   
				}
				
			
		}*/
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		$sm1->assign('can_modify',true);
			 
		$sm1->assign('total_cost',$_bpf->CalcCost($positions));
		$sm1->assign('total_nds',$_bpf->CalcNDS($positions));
		
		//получим виды скидок
		$_pld=new PlDisGroup;
		$sm1->assign('discs1',$_pld->GetItemsArr());
		
		$sm1->assign('positions',$positions);  

		
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',696)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',701)); 
		
		
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',715)); 
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',697)); 
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',699)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		
		$user_form=$sm1->fetch('kp/kp_create.html');
		
		
		
	
		
		if($au->user_rights->CheckAccess('w',716)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','В данном режиме просмотр журнала событий коммерческого предложения недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать коммерческое предложение и перейти к утверждению" на вкладке "Коммерческое предложение" для получения возможности просмотра журнала событий.');		
		}
		
		
		
	}elseif($action==1){
		//редактирование позиции
		
		
		
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$sm1=new SmartyAdm;
		
		
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
		
	
		
		if($editing_user['supplier_bill_pdate']==0) $editing_user['supplier_bill_pdate']='-';
		else $editing_user['supplier_bill_pdate']=date("d.m.Y", $editing_user['supplier_bill_pdate']);
		
		if($editing_user['valid_pdate']==0) $editing_user['valid_pdate']='-';
		else $editing_user['valid_pdate']=date("d.m.Y", $editing_user['valid_pdate']);
		
		
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($si['opf_id']); 
		$editing_user['supplier_id_string']=$opfitem['name'].' '.$si['full_name'];
		
		
		/*$supgroup=*/
		$_supgroup->GetItemsForBill('bills/suppliers_list.html', new DBDecorator, false, $supgroup, $result); //GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('suppliers',$supgroup);
		
		
		//банк. реквизиты
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetItemById($editing_user['bdetails_id']);
		$editing_user['bdetails_id_string']='р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city'];
		
		
		//реквизиты - получить список по тек. поставщику
		//bdetails
		$_bdg=new BDetailsGroup;
		$bdg=$_bdg->GetItemsByIdArr($editing_user['supplier_id'], $editing_user['bdetails_id']);
		$editing_user['bdetails']=$bdg;
		
		//договор п-ка
		$_scg=new SupContractGroup;
		$scg=$_scg->GetItemsByIdArr($editing_user['supplier_id'], $editing_user['contract_id'],0);
		$editing_user['condetails']=$scg;
		
		//подставить даты договора
		$_sci=new SupContractItem;
		$sci=$_sci->GetItemById($editing_user['contract_id']);
		$editing_user['contract_no']=$sci['contract_no'];
		$editing_user['contract_pdate']=$sci['contract_pdate'];
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new KpPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		//получим виды скидок
		$_pld=new PlDisGroup;
		$sm1->assign('discs1',$_pld->GetItemsArr());
		
		//стоимость и итого
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',713);
		if(!$au->user_rights->CheckAccess('w',713)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',713);
			if(!$au->user_rights->CheckAccess('w',714)) $reason='недостаточно прав для данной операции';
		
		
		
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		//если у счета утверждены цены - просматривать можно при наличии прав 365 (выдача +/- в счете)
		//в других статусах: 130 (работа с +/-)
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',700));
		
		
		$sm1->assign('not_changed_pos',true);
		
	
		//поставщики
		$supgroup=$_supgroup->GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('pos',$supgroup);
		
	
		
		
		
		//Примечания
		$rg=new KpNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed_price']==1, $au->user_rights->CheckAccess('w',703), $au->user_rights->CheckAccess('w',704), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',702)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',712)); 
		
		
		$cannot_edit_reason='';
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',715)&&in_array($editing_user['status_id'],$_editable_status_id)&&$_bill->CanEditQuantities($editing_user['id'],$cannot_edit_reason,$editing_user)); 
		if(strlen($cannot_edit_reason)>0) $cannot_edit_reason.=', либо ';
		$sm1->assign('cannot_edit_reason',$cannot_edit_reason);
		
		
		
		
		
		//кнопка доступна, если есть права и не утв-на отгрузка счета
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',697)&&(($editing_user['is_confirmed_price']==0)&&($editing_user['status_id']!=3)));
		
		
		
		
	
		
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',699)); 
		
		
		//проверка закрыотого периода
		$not_in_closed_period=$_bill->CheckClosePdate($editing_user['id'], $closed_period_reason);
		$sm1->assign('not_in_closed_period', $not_in_closed_period);
		$sm1->assign('closed_period_reason', $closed_period_reason);
		
		
		//связанные оплаты
		$sm1->assign('binded_payments',$_bill->GetBindedPayments($editing_user['id'],$binded_payments_summ));
		$sm1->assign('binded_payments_summ',$binded_payments_summ);
		
		
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
	
	
	
		//блок утверждения цен!
		if(($editing_user['is_confirmed_price']==1)&&($editing_user['user_confirm_price_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_price_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['confirm_price_pdate']);
			
						
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_price_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_shipping']==0){
			
			  
		  
		  if($editing_user['is_confirmed_price']==1){
			  if($au->user_rights->CheckAccess('w',96)){
				  //полные права
				  $can_confirm_price=true;	
			  }elseif($au->user_rights->CheckAccess('w',711)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //709
			  $can_confirm_price=$au->user_rights->CheckAccess('w',709)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm_price',$can_confirm_price);
		
		
		
		
		$reason='';
		
		

		
		
		$sm1->assign('can_create_outcoming_bill',$au->user_rights->CheckAccess('w',92)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',696)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',701)); 
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129)); 
		
		
		$user_form=$sm1->fetch('kp/kp_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',716)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(695,
696,
697,
698,
699,
700,
701,
702,
703,
704,
705,
706,
707,
708,
709,
710,
711,
712,
713,
714,
715,
716

)));
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
			
			$llg=$log->ShowLog('syslog/log.html',$decorator,$from,$to_page,'ed_kp.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$content=$sm->fetch('kp/ed_kp_page'.$print_add.'.html');
	
	
	
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