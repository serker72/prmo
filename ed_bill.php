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

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');
require_once('classes/sectorgroup.php');

require_once('classes/user_s_item.php');

/*require_once('classes/sh_i_group.php');
require_once('classes/acc_group.php');

require_once('classes/paygroup.php');
*/
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/billnotesgroup.php');
require_once('classes/billgroup.php');

require_once('classes/billnotesitem.php');

require_once('classes/billcreator.php');

//require_once('classes/payforbillgroup.php');
require_once('classes/invcalcgroup.php');
require_once('classes/pergroup.php');

require_once('classes/period_checker.php');

require_once('classes/propisun.php');

/*require_once('classes/kpitem.php');
require_once('classes/kpposgroup.php');
*/
require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/pay_in_group.php');
require_once('classes/pay_in_item.php');

require_once('classes/komplitem.php');

require_once('classes/cashgroup.php');

require_once('classes/cash_bill_position_group.php');
require_once('classes/supplier_to_user.php');

require_once('classes/supplier_ruk_item.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/suppliercontactdataitem.php');


$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование исходящего счета');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PosItem;

$_kp=new KomplItem;

$_sectors=new SectorGroup;
$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;


$lc=new BillCreator;

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

$printmode=1;
if(isset($_GET['printmode'])){
	$printmode=$_GET['printmode'];
}

if(!isset($_GET['force_print'])){
	if(!isset($_POST['force_print'])){
		$force_print=0;
	}else $force_print=abs((int)$_POST['force_print']); 
}else $force_print=abs((int)$_GET['force_print']);


if($print!=0){
	if(!$au->user_rights->CheckAccess('w',283)){
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
	$object_id[]=92;
	break;
	case 1:
	$object_id[]=93;
	$object_id[]=283;
	break;
	case 2:
	$object_id[]=94;
	break;
	default:
	$object_id[]=92;
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
	
	//родит. комм предложение
	if(!isset($_GET['kp_id'])){
		if(!isset($_POST['kp_id'])){
			$kp_id=0;
		}else $kp_id=abs((int)$_POST['kp_id']); 
	}else $kp_id=abs((int)$_GET['kp_id']);
		
	$kp=$_kp->GetItemById($kp_id);
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
	$editing_user=$_bill->GetItemByFields(array('id'=>$id, 'is_incoming'=>0));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);





	if($limited_supplier!==NULL){
		if(!in_array($editing_user['supplier_id'], $limited_supplier)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
	}
}



//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту исходящего счета',NULL,93, NULL, $editing_user['code'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту исходящего счета: версия для печати',NULL,93, NULL, $editing_user['code'],$id);
				
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',92)){
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
	
	if(isset($_POST['suppliers_are_equal'])){
		$params['ship_supplier_id']=abs((int)$_POST['supplier_id']);
		$params['suppliers_are_equal']=1;
	}else{
		$params['ship_supplier_id']=abs((int)$_POST['ship_supplier_id']);
		$params['suppliers_are_equal']=0;
	}
	
	//$params['contract_no']=SecStr($_POST['contract_no']);
	//$params['contract_pdate']=SecStr($_POST['contract_pdate']);
	$params['contract_id']=abs((int)$_POST['contract_id']);
	
	$params['komplekt_ved_id']=abs((int)$_POST['komplekt_ved_id']);
	$params['sector_id']=abs((int)$_POST['sector_id']);
	
	if(strlen($_POST['pdate_shipping_plan'])==10) $params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
	if(strlen($_POST['pdate_payment_contract'])==10) $params['pdate_payment_contract']=DateFromdmY($_POST['pdate_payment_contract']);
	
	$params['bdetails_id']=abs((int)$_POST['bdetails_id']);
	
	
		
	 
	$params['is_incoming']=0;
	
	//$params['notes']=SecStr($_POST['notes']);
	
	$lc->ses->ClearOldSessions();
	$params['code']=$lc->GenLogin($result['id']); //SecStr($_POST['code']);
	
	
	$params['is_confirmed_price']=0;
	$params['is_confirmed_shipping']=0;
	
	
	$params['manager_id']=$result['id'];
	
	
	$params['supplier_bill_no']=SecStr($_POST['supplier_bill_no']);
	
	if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);
	
	
	if($au->user_rights->CheckAccess('w',474)){
		if(isset($_POST['cannot_eq'])){
			 $params['cannot_eq']=1;
			  $params['cannot_eq_id']=$result['id'];
			 $params['cannot_eq_pdate']=time();
		}else $params['cannot_eq']=0;
	}
	
	if($au->user_rights->CheckAccess('w',538)){
		if(isset($_POST['cannot_an'])){
			 $params['cannot_an']=1;
			  $params['cannot_an_id']=$result['id'];
			 $params['cannot_an_pdate']=time();
		}else $params['cannot_an']=0;
	}
	
	$code=$_bill->Add($params);
	
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал исходящий счет',NULL,92,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		 
				if($k=='supplier_id'){
					$_si=new SupplierItem; $_opf=new OpfItem;
					$si=$_si->GetItemById($v); $opf=$_opf->GetItemById($si['opf_id']);
					
					
					$log->PutEntry($result['id'],'создал исходящий счет',NULL,92, NULL, SecStr('установлен контрагент '.$si['code'].' '.$opf['name'].' '.$si['full_name']),$code);			
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'создал исходящий счет',NULL,92, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
		if($au->user_rights->CheckAccess('w',474)&&($params['cannot_eq']==1)){
			//создать автопримечание
			$_kni=new BillNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$code;
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=$result['id'];
		
		
			$notes_params['note']='Автоматическое примечание: было отключено автоматическое выравнивание счета.';
			$_kni->Add($notes_params);
		}
		
		if($au->user_rights->CheckAccess('w',538)&&($params['cannot_an']==1)){
			//создать автопримечание
			$_kni=new BillNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$code;
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=$result['id'];
		
		
			$notes_params['note']='Автоматическое примечание: было отключено автоматическое аннулирование счета.';
			$_kni->Add($notes_params);
		}
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',188))){
		//позиции
		$positions=array();
		
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		$_kpi=new KomplPosItem;
		
		
			
		foreach($_POST as $k=>$v){
		  if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_hash_","",$k);
			  
			  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			  $komplekt_ved_id=abs((int)$_POST['new_komplekt_ved_id_'.$hash]);
			  
			  
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
			  $kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$komplekt_ved_id, 'position_id'=>$pos_id));
			  $pos=$_pos->GetItemById($pos_id);
			  $positions[]=array(
				  'bill_id'=>$code,
				  'komplekt_ved_pos_id'=>(int)$kpi['id'],
				  'position_id'=>$pos_id,
				  'name'=>SecStr($pos['name']),
				  'dimension'=>SecStr($dimension['name']),
				  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
				  'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
				  'storage_id'=>(int)$_POST['new_storage_id_'.$hash],
				  'sector_id'=>(int)$_POST['new_sector_id_'.$hash],
				  'komplekt_ved_id'=>(int)$_POST['new_komplekt_ved_id_'.$hash],
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
			$pos=$_pos->GetItemById($v['position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'].'<br /> цена '.$v['price'].' руб. <br />';
				if($v['pms']!==NULL){
					if($v['pms']['plus_or_minus']==0){
						$descr.=' + ';	
					}else{
						$descr.=' - ';	
					}
					$descr.=$v['pms']['value'];
					if($v['pms']['rub_or_percent']==0){
						$descr.=' руб. ';	
					}else{
						$descr.=' % ';	
					}
					
					$descr.=' дисконт +/-: ';
					
					$descr.=$v['pms']['discount_value'];
					if($v['pms']['discount_rub_or_percent']==0){
						$descr.=' руб. ';	
					}else{
						$descr.=' % ';	
					}
				}
				
				$log->PutEntry($result['id'],'добавил позицию исходящего счета', NULL, 93,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: bills.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',93)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_bill.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: bills.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',93)){
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
		
		//print_r($_POST); die();
		
		if(isset($_POST['supplier_id'])) $params['supplier_id']=abs((int)$_POST['supplier_id']);
	
		//if(isset($_POST['contract_no'])) $params['contract_no']=SecStr($_POST['contract_no']);
		//if(isset($_POST['contract_pdate'])) $params['contract_pdate']=SecStr($_POST['contract_pdate']);
		if(isset($_POST['contract_id'])) $params['contract_id']=abs((int)$_POST['contract_id']);
	
		if(strlen($_POST['pdate_shipping_plan'])==10) $params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
		if(strlen($_POST['pdate_payment_contract'])==10) $params['pdate_payment_contract']=DateFromdmY($_POST['pdate_payment_contract']);
		
		if(isset($_POST['sector_id'])) $params['sector_id']=abs((int)$_POST['sector_id']);
		
		
		if(isset($_POST['bdetails_id'])) $params['bdetails_id']=abs((int)$_POST['bdetails_id']);
		
		
		
		if(isset($_POST['supplier_bill_no'])) $params['supplier_bill_no']=SecStr($_POST['supplier_bill_no']);
	
		if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);
		
		
		if(isset($_POST['suppliers_are_equal'])){
			$params['ship_supplier_id']=abs((int)$_POST['supplier_id']);
			$params['suppliers_are_equal']=1;
		}else{
			$params['ship_supplier_id']=abs((int)$_POST['ship_supplier_id']);
			$params['suppliers_are_equal']=0;
		}
		
		
		
		$_bill->Edit($id, $params,false,$result);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				if($k=='pdate_shipping_plan'){
					$log->PutEntry($result['id'],'редактировал плановую дату поставки',NULL,93,NULL,'дата: '.$_POST['pdate_shipping_plan'],$id);
					continue;	
				}
				
				if($k=='pdate_payment_contract'){
					$log->PutEntry($result['id'],'редактировал дату оплаты по договору',NULL,93,NULL,'дата: '.$_POST['pdate_payment_contract'],$id);
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'редактировал исходящий счет',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
	}
	
	//правим откл автоаннул
	/*отдельным блоком - правка откл автовыравнивания*/
	if(
	(in_array($_POST['current_status_id'],array(1,2,9,20,21))&&in_array($editing_user['status_id'],array(1,2,9,20,21))&&($editing_user['is_confirmed_shipping']==0)&&$au->user_rights->CheckAccess('w',474))||
	($au->user_rights->CheckAccess('w',485))
	){
		
		$params1=array();
		if(isset($_POST['cannot_eq'])) {
			$params1['cannot_eq']=1;
			 $params1['cannot_eq_id']=$result['id'];
			 $params1['cannot_eq_pdate']=time();
		}else $params1['cannot_eq']=0;
		
		$_bill->Edit($id, $params1, false, $result);
		
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params1 as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'редактировал исходящий счет',NULL,474, NULL, 'в поле '.$k.' установлено значение '.$v,$id);	
				
				if($k=='cannot_eq'){
				  //создать автопримечание
				  $_kni=new BillNotesItem;
				  $notes_params=array();
				  $notes_params['is_auto']=1;
				  $notes_params['user_id']=$id;
				  $notes_params['pdate']=time();
				  $notes_params['posted_user_id']=$result['id'];
				  
				  if($params1['cannot_eq']==1){
					  $notes_params['note']='Автоматическое примечание: было отключено автоматическое выравнивание счета.';
				  }else{
					  $notes_params['note']='Автоматическое примечание: было включено автоматическое выравнивание счета.';
				  }
				  
				  $_kni->Add($notes_params);	
				}
			}
		}
	}
	
	
	
	/*отдельным блоком - правка откл автоаннулирования*/
	if(
	(in_array($_POST['current_status_id'],array(1,2,9,20,21))&&in_array($editing_user['status_id'],array(1,2,9,20,21))&&($editing_user['is_confirmed_shipping']==0)&&$au->user_rights->CheckAccess('w',538))||
	($au->user_rights->CheckAccess('w',539))
	){
		
		$params1=array();
		if(isset($_POST['cannot_an'])){
			 $params1['cannot_an']=1;
			  $params1['cannot_an_id']=$result['id'];
			 $params1['cannot_an_pdate']=time();
		}else $params1['cannot_an']=0;
		
		$_bill->Edit($id, $params1, false, $result);
		
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params1 as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'редактировал исходящий счет',NULL,538, NULL, 'в поле '.$k.' установлено значение '.$v,$id);	
				
				if($k=='cannot_an'){
				  //создать автопримечание
				  $_kni=new BillNotesItem;
				  $notes_params=array();
				  $notes_params['is_auto']=1;
				  $notes_params['user_id']=$id;
				  $notes_params['pdate']=time();
				  $notes_params['posted_user_id']=$result['id'];
				  
				  if($params1['cannot_an']==1){
					  $notes_params['note']='Автоматическое примечание: было отключено автоматическое аннулирование счета.';
				  }else{
					  $notes_params['note']='Автоматическое примечание: было включено автоматическое аннулирование счета.';
				  }
				  
				  $_kni->Add($notes_params);	
				}
			}
		}
	}
	
	
		$condition_positions=$condition;
	//правим позиции. можно их править, если у счета не утв. отгрузка....
	$condition_positions=$condition_positions||(($editing_user['status_id']!=3)&&($editing_user['is_confirmed_shipping']==0))||
	(($editing_user['is_confirmed_price']==1)&&
		($editing_user['is_confirmed_shipping']==1)&&
		$_bill->HasShsorAccs($editing_user['id'])&&
		$au->user_rights->CheckAccess('w',523))
	;
	
	
	//правим позиции	
	if($condition_positions){	
		if($au->user_rights->CheckAccess('w',93)){
			//плановая дата поставки
			$a_params=array();
			
			if(strlen($_POST['pdate_shipping_plan'])==10) $a_params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
			
			$_bill->Edit($id, $a_params,false,$result);
			
			
			foreach($a_params as $k=>$v){
			
			  if(addslashes($editing_user[$k])!=$v){
				  if($k=='pdate_shipping_plan'){
					  $log->PutEntry($result['id'],'редактировал плановую дату поставки',NULL,93,NULL,'дата: '.$_POST['pdate_shipping_plan'],$id);
					  continue;	
				  }
				  
				 
				  
				  $log->PutEntry($result['id'],'редактировал входящий счет',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				 		  
			  }
		  }
		};
		
		
		
		if($au->user_rights->CheckAccess('w',188)){
		  $positions=array();
		  
		  $_pos=new PosItem;
		  $_pdi=new PosDimItem;
		  $_kpi=new KomplPosItem;
		  
		  
		  $check_delta_summ=(($editing_user['is_confirmed_price']==1)&&
		  ($editing_user['is_confirmed_shipping']==1)&&
		  $_bill->HasShsorAccs($editing_user['id'])&&
		  $au->user_rights->CheckAccess('w',523));
		  		//найти и запомнить старую сумму счета
			
				
		  if($check_delta_summ){
				$old_summ=$_bill->CalcCost($id);  
				
		  }
		  
		  foreach($_POST as $k=>$v){
			if(eregi("^new_hash_([0-9a-z]+)",$k)){
				
				$hash=eregi_replace("^new_hash_","",$k);
				
				$pos_id=abs((int)$_POST['new_position_id_'.$hash]);
				$komplekt_ved_id=abs((int)$_POST['new_komplekt_ved_id_'.$hash]);
				
				
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
				$kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$komplekt_ved_id, 'position_id'=>$pos_id));
				$pos=$_pos->GetItemById($pos_id);
				$positions[]=array(
					'bill_id'=>$id,
					'komplekt_ved_pos_id'=>(int)$kpi['id'],
					'position_id'=>$pos_id,
					'name'=>SecStr($pos['name']),
					'dimension'=>SecStr($dimension['name']),
					'quantity'=>((float)$_POST['new_quantity_'.$hash]),
					'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
					'storage_id'=>(int)$_POST['new_storage_id_'.$hash],
					'sector_id'=>(int)$_POST['new_sector_id_'.$hash],
					'komplekt_ved_id'=>(int)$_POST['new_komplekt_ved_id_'.$hash],
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
		  
		  
		  
		  if($_POST['can_change_cascade']==1) $can_change_cascade=true;
		  else $can_change_cascade=false;
		  $log_entries=$_bill->AddPositions($id,$positions,$can_change_cascade,$check_delta_summ,$result);
		  
		  //предусмотреть блок удаления совсем удаленных у товара позиций!
		  // перенесено в функцию AddPositions!!!
		  
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  $description=SecStr($v['name']).' <br /> Кол-во: '.$v['quantity'].'<br /> '.'Цена '.$v['price'].' руб. <br />';
			  if($v['pms']!==NULL){
				  $description.='<br /> ';
				  if($v['pms']['plus_or_minus']==0){
					  $description.=' + ';
				  }else{
					  $description.=' - ';
				  }
				  $description.=$v['pms']['value'];
				  if($v['pms']['rub_or_percent']==0){
					  $description.=' руб. ';
				  }else{
					  $description.=' % ';
				  }
				  
				  $description.=' дисконт +/-: ';
				 
				  $description.=$v['pms']['discount_value'];
				  if($v['pms']['discount_rub_or_percent']==0){
					  $description.=' руб. ';	
				  }else{
					  $description.=' % ';	
				  }
			  }
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию исходящего счета',NULL,93,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию исходящего счета',NULL,93,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию исходящего счета',NULL,93,NULL,$description,$id);
			  }
			  
		  }
		  
		  if($check_delta_summ){
				$new_summ=$_bill->CalcCost($id);  
				
				/*echo $old_summ.'   '.$new_summ; 
				die();
				*/
				if($new_summ!=$old_summ){
					 $description='старая сумма: '.$old_summ.' руб., новая сумма: '.$new_summ.' руб.';
					 $log->PutEntry($result['id'],'изменение суммы счета при редактировании +/-',NULL,523,NULL,$description,$id);
				}
		  }
		
		}
	}
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed_shipping']==0){
	  if($editing_user['is_confirmed_price']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',196))||$au->user_rights->CheckAccess('w',96)){
			  if((!isset($_POST['is_confirmed_price']))&&in_array($editing_user['status_id'], array(2,9,10,20,21))&&in_array($_POST['current_status_id'], array(2,9,10,20,21))&&($_bill->DocCanUnconfirmPrice($id,$rss32))){
				  
				  //&&($editing_user['status_id']==5)&&($_POST['current_status_id']==5)
				  $_bill->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение цен',NULL,196, NULL, NULL,$id);	
				  $_bill->FreeBindedPayments($id);
				  
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',95)||$au->user_rights->CheckAccess('w',96)){
			  if(isset($_POST['is_confirmed_price'])&&($_POST['is_confirmed_price']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))&&($_bill->DocCanConfirmPrice($id,$rss32))){
				  
				  $_bill->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил цены',NULL,95, NULL, NULL,$id);	
				  
				  //если выбран режим - заносим в оплаты
				  if(isset($_POST['can_add_to_payments'])&&($_POST['can_add_to_payments']==1)) $_bill->BindPayments($id,$result['org_id']);	  
				  //die();
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	//утверждение отгрузки
	if($editing_user['is_confirmed_price']==1){
	  if($editing_user['is_confirmed_shipping']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',197))||$au->user_rights->CheckAccess('w',96)){
			  //if(!isset($_POST['is_confirmed_shipping'])){
			  if((!isset($_POST['is_confirmed_shipping'])) &&in_array($editing_user['status_id'], array(2,9,10,20,21))&&in_array($_POST['current_status_id'], array(2,9,10,20,21))&&($_bill->DocCanUnconfirmShip($id, $rss32))){
				  $_bill->Edit($id,array('is_confirmed_shipping'=>0, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение отгрузки',NULL,197, NULL, NULL,$id);	
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',195)||$au->user_rights->CheckAccess('w',96)){
			  if(isset($_POST['is_confirmed_shipping'])&&in_array($editing_user['status_id'], array(2,9,10,20,21))&&in_array($_POST['current_status_id'], array(2,9,10,20,21))&&($_bill->DocCanConfirmShip($id, $rss32))){
				  $_bill->Edit($id,array('is_confirmed_shipping'=>1, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил отгрузку',NULL,195, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: bills.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',93)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_bill.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: bills.php");
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
//else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=15;

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
		
		$supplier_id=abs((int)$_GET['supplier_id']);
	
		
		
		
		
		//поставщики
		/*$_supgroup->GetItemsForBill('bills/suppliers_list.html', new DBDecorator, false, $supgroup, $result, $supplier_id);
		
		$sm1->assign('suppliers',$supgroup);*/
		//поставщики
		$dec=new DBDecorator;
		//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result);
		
		$sm1->assign('suppliers',$supgroup);
		
		
		//принять заявку, объект, участок, позиции (номенклатура + кол-во)
		if(isset($_GET['komplekt_ved_id'])){
			$komplekt_ved_id=abs((int)$_GET['komplekt_ved_id']);
			
			$sm1->assign('komplekt_ved_id',$komplekt_ved_id);
			$_ki=new KomplItem;
			$ki=$_ki->getItemById($komplekt_ved_id);
		//	$sm1->assign('komplekt_ved_id_string','Заявка № '.$komplekt_ved_id);
		}else $komplekt_ved_id=0;
		
		
		
		//склады	
		$sectors=$_sectors->GetItemsArr(0,1);
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($sectors as $k=>$v){
			
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		
		$sm1->assign('group_id', 1); 
		
		$sm1->assign('group_ids', $st_ids);
		$sm1->assign('group_names', $st_names);
		
		
		
		$_bpf=new BillPosPMFormer;
		
		if($ki!==false){
			//поставщик
			$_si=new SupplierItem;
			$si=$_si->GetItemById($ki['supplier_id']);
			$_opfitem=new OpfItem;
			
			$opfitem=$_opfitem->getItemById($si['opf_id']); 
			$sm1->assign('supplier_id_string', $opfitem['name'].' '.$si['full_name']);
			$sm1->assign('supplier_id', $ki['supplier_id']);
			
			
			//договор
			$_scg=new SupContractGroup;
			$scg=$_scg->GetItemsByIdArr($ki['supplier_id'], 0,0);
			$sm1->assign('pos2',$scg);		
			
			
			
			//подставить даты договора
			$_sci=new SupContractItem;
			$sci=$_sci->GetItemByfields(array('is_basic'=>1, 'is_incoming'=>0, 'user_id'=>$ki['supplier_id'])); //Id($ki['contract_id']);
			$sm1->assign('contract_no',$sci['contract_no']);
			$sm1->assign('contract_pdate',$sci['contract_pdate']);
			$sm1->assign('contract_id', $sci['id']);	 
			
			
			//реквизиты - получить список по тек. поставщику
			$_bdi=new BDetailsItem;
			$bdi=$_bdi->GetItemByFields(array('user_id'=>$ki['supplier_id'], 'is_basic'=>1)); //Id($ki['bdetails_id']);
			$sm1->assign('bdetails_id_string', 'р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city']);
			$sm1->assign('bdetails_id', $bdi['id']);
			//bdetails
			$_bdg=new BDetailsGroup;
			$bdg=$_bdg->GetItemsByIdArr($ki['supplier_id'], 0);
			$sm1->assign('pos', $bdg);
		}
		
		
	
		$_mf=new MaxFormer;	
		
				
			
	//передать позиции...
		$positions=array();
		
		foreach($_GET as $k=>$v){
			if(eregi("^to_bill_",$k)){
				$pos_id=abs((int)eregi_replace("^to_bill_","",$k));
				$qua=((float)$v);	
				
				
				$sql='select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id, 
				sec.id as sector_id, sec.name as sector_name
				
				from komplekt_ved_pos as p 
				inner join catalog_position as pos on p.position_id=pos.id 
				left join catalog_dimension as dim on pos.dimension_id=dim.id 
			
				left join sector as sec on sec.id="'.$sector_id.'"
				where komplekt_ved_id="'.$komplekt_ved_id.'" and p.position_id="'.$pos_id.'" order by position_name asc, id asc';
				
				
				//echo $sql.'<br>';
				
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				$h=mysqli_fetch_array($rs);
				//print_r($h);
				if($qua>0){
					
					
				  if($h['komplekt_ved_id']!=0) $komplekt_ved_name='Заявка № '.$h['komplekt_ved_id'];
				  else $komplekt_ved_name='-';
				  
				 
				  
				  
				  $positions[]=array(
					  'id'=>$pos_id,
					  'hash'=>md5($pos_id.'_'.$h['storage_id'].'_'.$h['sector_id'].'_'.$h['komplekt_ved_id']),
					  'position_name'=>$h['position_name'],
					  'dim_name'=>$h['dim_name'],
					  'dimension_id'=>$h['dimension_id'],
					  'quantity'=>$qua,
					  'price'=>0,
					  'price_pm'=>0,
					  'has_pm'=>false,
					  'cost'=>0,
					  'total'=>0,
					  'plus_or_minus'=>0,
					  'rub_or_percent'=>0,
					  'value'=>0,
					  
					  'discount_rub_or_percent'=>0,
					  'discount_value'=>0,
					  'nds_proc'=>NDS,
					  'nds_summ'=>0,
					  'quantity_confirmed'=>$h['quantity_confirmed'],
					  'max_quantity'=>$qua,
					  'in_rasp'=>0,
					  'in_rasp_in'=>0, //round($_mf->MaxInAccIn($id, $f['id'],NULL, NULL, $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id']),3),
					  'in_free'=>0,
					  'storage_id'=>$h['storage_id'],
					  'storage_name'=>$h['storage_name'],
					  
					  //новые поля
					  'sector_id'=>$h['sector_id'],
					  'sector_name'=>$h['sector_name'],
					  
					  'komplekt_ved_id'=>$h['komplekt_ved_id'],
					  //'komplekt_ved_name'=>'Заявка № '.$h['komplekt_ved_id'],
					  'komplekt_ved_name'=>$komplekt_ved_name
											  
				  );
				}
				
			}
		}
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		$sm1->assign('can_modify',true);
			 
		$sm1->assign('total_cost',$_bpf->CalcCost($positions));
		$sm1->assign('total_nds',$_bpf->CalcNDS($positions));
		
		
		$sm1->assign('positions',$positions);  

		
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',92)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',93)); 
		//$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129));
		
		//можно ли отключать автовыравнивание?
		$sm1->assign('can_cannot_eq', $au->user_rights->CheckAccess('w',474));
		
		//кто имеет право вводить низкий дисконт (менее 10%)
		$can_lower_users=array();
		$_usg=new UsersSGroup;
		$can_lower_users=$_usg->GetUsersByRightArr('w',  881);
	
		$sm1->assign('can_lower_users',$can_lower_users);
		$sm1->assign('can_lower_discount',$au->user_rights->CheckAccess('w',881));
	
		
		//можно ли отключать автоаннулирование?
		$sm1->assign('can_cannot_an', $au->user_rights->CheckAccess('w',538));
		
		
		
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',302)); 
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',188)); 
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',190)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		
		$user_form=$sm1->fetch('bills/bill_create.html');
		
		$sm->assign('has_bills_in', true); 
		$sm->assign('bills_in','В данном режиме просмотр входящий счетов по исходящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать исходящий счет и перейти к утверждению" на вкладке "Исходящий счет" для получения возможности просмотра входящий счетов.');		
 		
 
 
 		$sm->assign('has_acc', true); //($editing_user['is_confirmed_shipping']==1));
		$sm->assign('accs','В данном режиме просмотр реализаций товара по исходящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать исходящий счет и перейти к утверждению" на вкладке "Исходящий счет" для получения возможности просмотра реализаций товара.');		
 		
 
		
		$sm->assign('has_pays', true);//($editing_user['is_confirmed_price']==1));
		$sm->assign('pays','В данном режиме просмотр оплат по исходящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать исходящий счет и перейти к утверждению" на вкладке "Исходящий счет" для получения возможности просмотра оплат.');	
 
	$sm->assign('has_cash', $au->user_rights->CheckAccess('w',833));//($editing_user['is_confirmed_price']==1));
		$sm->assign('cash','В данном режиме просмотр затрат по исходящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать исходящий счет и перейти к утверждению" на вкладке "Исходящий счет" для получения возможности просмотра затрат.');		
		
		
		$sm->assign('has_pm_folder', $au->user_rights->CheckAccess('w',365));
		$sm->assign('pm_folder','В данном режиме просмотр выданных +/- по исходящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать исходящий счет и перейти к утверждению" на вкладке "Исходящий счет" для получения возможности просмотра выданных +/- .');	
		
		
		if($au->user_rights->CheckAccess('w',522)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','В данном режиме просмотр журнала событий исходящего счета недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать исходящий счет и перейти к утверждению" на вкладке "Исходящий счет" для получения возможности просмотра журнала событий.');		
		}
		
		
		
	}elseif($action==1){
		//редактирование позиции
		
		
		
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$sm1=new SmartyAdm;
		
		$sm1->assign('force_print', $force_print);
		
		
		
		//даты
		$editing_user['pdate_unf']=$editing_user['pdate'];
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		$m='';
		switch(date('m',$editing_user['pdate_unf'])){
			case 1:
				$m='января';
			break;
			case 2:
			$m='февраля';
			break;
			case 3:
			$m='марта';
			break;
			case 4:
			$m='апреля';
			break;
			case 5:
			$m='мая';
			break;
			case 6:
			$m='июня';
			break;
			case 7:
			$m='июля';
			break;
			case 8:
			$m='августа';
			break;
			case 9:
			$m='сентября';
			break;
			case 10:
			$m='октября';
			break;
			case 11:
			$m='ноября';
			break;
			case 12:
			$m='декабря';
			break;	
			
		}
		$editing_user['pdate_print']=date('d ', $editing_user['pdate_unf']).$m.date(' Y',$editing_user['pdate_unf']);
		
		 
		//реквизиты компании
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetBasic($result['org_id']);
		$editing_user['bd_print']=$bdi;
		
		//
		$_org=new OrgItem;
		$org=$_org->GetItemById($result['org_id']);
		
		//добавим подписи, печати
		$_sri=new SupplierRukItem;
			$sri_1=$_sri->GetActualByPdate($orgitem['id'],date("d.m.Y", $editing_user['pdate_unf']), 1);
		$sri_2=$_sri->GetActualByPdate($orgitem['id'], date("d.m.Y",$editing_user['pdate_unf']), 2);
		
		$org['chief']=$sri_1['fio'];
		$org['print_sign_dir']=$sri_1['sign'];
		
		$org['main_accountant']=$sri_2['fio'];
		$org['print_sign_buh']=$sri_2['sign'];
		
		
		//данные для печатной формы - представитель организации и его телефон:
		$_cont=new SupplierContactItem;
		$cont=$_cont->GetItemByFields(array('supplier_id'=>$org['id']));
		$org['cont']=$cont['name'];
		$_phone=new SupplierContactDataItem;
		$phone=$_phone->getitembyfields(array('contact_id'=>$cont['id'], 'kind_id'=>3));
		$org['phone']=$phone['value'];
		
		
	 
		$phone=$_phone->getitembyfields(array('contact_id'=>$cont['id'], 'kind_id'=>5));
		$org['email']=$phone['value'];
		
		$editing_user['org']=$org;
		$editing_user['org']['bill_comments']=str_replace('%{$bill_data}%', 'по счету на оплату № '.$editing_user['supplier_bill_no'].' от '.$editing_user['pdate_print'].' ', $editing_user['org']['bill_comments']);
		
		
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($org['opf_id']);
		$editing_user['opf']=$opf;
		
		//костыль для замены ТД СЯ на ТД "СЯ
		$orgitem_fact=$orgitem;
		//$orgitem_fact['full_name']=eregi_replace('Торговый Дом Строительная Ярмарка', 'Торговый Дом "Строительная Ярмарка',$orgitem_fact['full_name']);
		$sm1->assign('print_org_fact' ,$orgitem_fact);
		
		$sm1->assign('print_org_opf' ,$opf);
		
		//поставщик
		$_si=new supplieritem();
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$editing_user['supplier']=$si;
		
		$sopf=$_opf->GetItemById($si['opf_id']);
		$editing_user['si_opf']=$sopf;
		
		
		//грузополучатель
		$si1=$_si->GetItemById($editing_user['ship_supplier_id']);
		$editing_user['ship_supplier']=$si1;
		
		$sopf1=$_opf->GetItemById($si1['opf_id']);
		$editing_user['ship_si_opf']=$sopf1;
		
		
		
		//реквизиты
		$sbdi=$_bdi->getitembyid($editing_user['bdetails_id']);
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		if($editing_user['pdate_shipping_plan']==0) $editing_user['pdate_shipping_plan']='-';
		else $editing_user['pdate_shipping_plan']=date("d.m.Y", $editing_user['pdate_shipping_plan']);
		
		if($editing_user['pdate_payment_contract']==0) $editing_user['pdate_payment_contract']='-';
		else $editing_user['pdate_payment_contract']=date("d.m.Y", $editing_user['pdate_payment_contract']);
		
	
		
		//фактическая дата поставки - увязана с реализации
		$_acg=new AccGroup;
			
		$dec2=new DBDecorator;
		
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$dec2->AddEntry(new UriEntry('is_confirmed_acc',1));
		
		$_acg->SetAuthResult($result);
		$ships=$_acg->ShowPos($id,'bills/fact_dates.html', $dec2, $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',95), $au->user_rights->CheckAccess('w',96));
		
		$sm1->assign('fact_days',$ships); 	
		
		
		
		//фактическая дата оплаты - увязана с оплатами
		$_pays=new PayInGroup;
		$_pays->SetPagename('ed_bill.php');
		$dec2=new DBDecorator;
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$_pays->SetAuthResult($result);
		$pays=$_pays->ShowPos($editing_user['id'], $editing_user['supplier_id'], 'bills/fact_pays.html', $dec2, $au->user_rights->CheckAccess('w',272), $au->user_rights->CheckAccess('w',279), $au->user_rights->CheckAccess('w',277),   $au->user_rights->CheckAccess('w',96),true,false, $au->user_rights->CheckAccess('w',280), $au->user_rights->CheckAccess('w',278),true, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481), $total_cost, $_bill->CalcPayed($editing_user['id']));
		
		//добавим также инв. акты
		$_invg=new InvCalcGroup;
		$_invg->SetPageName('ed_bill.php');
		$dec2=new DBDecorator;
		$dec2->AddEntry(new SqlEntry('p.is_confirmed_inv',1, SqlEntry::E));
		$dec2->AddEntry(new SqlEntry('p.supplier_id',$editing_user['supplier_id'], SqlEntry::E));
		
		$_invg->SetAuthResult($result);
		$pays.=$_invg->ShowPosByBill($editing_user['id'],'bills/fact_invs.html',$dec2,0,10000, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),true,false,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461));
		
			
		$sm1->assign('fact_pays',trim($pays)); 	
		
		
		
		
		
		if($editing_user['supplier_bill_pdate']==0) $editing_user['supplier_bill_pdate']='-';
		else $editing_user['supplier_bill_pdate']=date("d.m.Y", $editing_user['supplier_bill_pdate']);
		
		
		//склады	
		$sectors=$_sectors->GetItemsArr(0,1);
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($sectors as $k=>$v){
			
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		
		$sm1->assign('group_id', $editing_user['sector_id']); 
		
		$sm1->assign('group_ids', $st_ids);
		$sm1->assign('group_names', $st_names);
		$sm1->assign('sectors', $sectors);
		
		
		
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($si['opf_id']); 
		$editing_user['supplier_id_string']=$opfitem['name'].' '.$si['full_name'];
		
		//грузополучатель
		$si1=$_si->GetItemById($editing_user['ship_supplier_id']);
		 
		$opfitem1=$_opfitem->getItemById($si1['opf_id']); 
		$editing_user['ship_supplier_id_string']=$opfitem1['name'].' '.$si1['full_name'];
		
		
		/*$supgroup=*/
	/*	$_supgroup->GetItemsForBill('bills/suppliers_list.html', new DBDecorator, false, $supgroup, $result); //GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('suppliers',$supgroup);
		*/
		
		$dec=new DBDecorator;
		$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result);
		
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
		$_bpg=new BillPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		
		$number_per_page=26;
		if($editing_user['org_id']==33) $number_per_page=39;
		
		$was_last=false; $num_of_pages=0;
		
		if($print==1){
				//позиции для печати
				$cter=1;
				foreach($bpg as $k=>$v){
					if($cter==$number_per_page){
						 $bpg[$k]['break_after']=true;
						 $num_of_pages++;
					}
					elseif(($cter>$number_per_page)&&((($cter-$number_per_page)%56)==0)){
						  $bpg[$k]['break_after']=true;
						  $num_of_pages++;
					}
					
					if($editing_user['org_id']==33){
						//если это последний лист и позиция >40й, следовательно поставить разрыв листа
						//как понять, что это последний лист???
						
				 
						
						//намбер пер пэдж - 1 страница, 56 - на страницу всего
						if(($cter>$number_per_page)){
							
						 	
						  $was_on=$number_per_page+($num_of_pages-1)*56;
						  
						//  echo $was_on;
						  if((count($bpg)-$was_on)<=56){
							 
							  //$co=count($bpg)-$cter;
							  if(($cter-$was_on)>50){
							  //if
								  if(!$was_last) {
									  $bpg[$k]['break_after']=true;
									  $was_last=true;
								  }
							  }
						  }
						}
					}
					
					$cter++;
				}
			
		}
		
		$sm1->assign('positions',$bpg);
		/*echo '<pre>';
		
		print_r($bpg);
		echo '</pre>';*/
		
		
		
		//стоимость и итого
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
		
		require_once('classes/propis.php');
		require_once('classes/propis1.php');
		require_once('classes/propis_cifr_kop.php');
		require_once('classes/propis_cifr_kop1.php');
		$_pn=new PropisUn(); $_pp=new Propis; $_pp1=new Propis1; $_pck=new PropisCifrKop;
		
		$summa_propis=trim($_pp->propis(floor($total_cost)));
	//	$summa_propis=mb_convert_case(substr($summa_propis, 1,1), MB_CASE_UPPER, 'windows-1251').substr($summa_propis, 2,strlen($summa_propis));
		
		 
		//$summa_propis=mb_convert_case($summa_propis,MB_CASE_UPPER, 'windows-1251');
		//$sm1->assign('count_propis',$_pn->propis(count($bpg)));
		//substr($a, 1,2);
		
		$summa_propis= mb_convert_case(substr($summa_propis, 0, 1), MB_CASE_UPPER, 'windows-1251').substr($summa_propis, 1,strlen($summa_propis));
		
		//$summa_propis= strtoupper(substr($summa_propis, 0, 1)).substr($summa_propis, 1,strlen($summa_propis));
		
		
		$sm1->assign('total_cost_rub_propis', $summa_propis);
		
		if($editing_user['org_id']==33) {
			$sm1->assign('total_cost_kop_propis', $_pp1->propis(round(100*((float)$total_cost-floor((float)$total_cost)))   /*' '. $_pck->propis(round(100*((float)$total_cost-floor((float)$total_cost))) */  ) );	
		}else{
			$sm1->assign('total_cost_kop_propis', ' '. $_pck->propis(round(100*((float)$total_cost-floor((float)$total_cost)))  ) );
		}
		$sm1->assign('printmode',$printmode);
		
		
		
		//коррекция +/-
		$sm1->assign('can_modify_pms',($editing_user['is_confirmed_price']==1)&&
		($editing_user['is_confirmed_shipping']==1)&&
		$_bill->HasShsorAccs($editing_user['id'])&&
		$au->user_rights->CheckAccess('w',523));
		
		 
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',94);
		if(!$au->user_rights->CheckAccess('w',94)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',131);
			if(!$au->user_rights->CheckAccess('w',131)) $reason='недостаточно прав для данной операции';
		
		
		
		
		//$sm1->assign('org',$orgitem['name']);
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		//если у счета утверждены цены - просматривать можно при наличии прав 365 (выдача +/- в счете)
		//в других статусах: 130 (работа с +/-)
		if($editing_user['is_confirmed_price']==1){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
		
		
		$sm1->assign('not_changed_pos',true);
		
		//есть ли реализации, расп.
		$sm1->assign('has_rasp_or_post',$_bill->HasR($editing_user['id']));
		$sm1->assign('rasp_or_post_list',$_bill->HasRList($editing_user['id']));
		
		
		//поставщики
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_active'=>1));
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$dec=new DBDecorator;
		//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result);
		$sm1->assign('pos',$supgroup);
		
	
		
		//реестр утв. отгр. исход. счетов (для включения в доставку, экспедирование)
		//bills_to_cash
		$_bills=new BillGroup;
		$dec_bills=new DBDecorator();
		
		$dec_bills->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		$dec_bills->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		$dec_bills->AddEntry(new SqlEntry('p.is_confirmed_shipping',1, SqlEntry::E));
		
		$bills_to_cash1=$_bills->ShowPos('bills/bills_list.html', 
			$dec_bills,
			0,
			100000, 
			false, 
			false, 
			false, 
			'', 
			false,
			false, 
			true, 
			false, 
			false,
			NULL,
			NULL, 
			false, 
			false, 
			false, 
		$bills_to_cash);
		$sm1->assign('bills_to_cash',$bills_to_cash);
		
		
		//уже готовые (утв.) доставки, экспед-ия по счету
		$_cg1=new CashGroup;
		
		$dec_c1=new DBDecorator();
		$dec_c1->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		
		$dec_c1->AddEntry(new SqlEntry('p.id','select distinct cash_id from cash_to_bill where bill_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
		
		$dec_c1->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));	
		$dec_c1->AddEntry(new SqlEntry('p.kind_id',2, SqlEntry::E));
		
		$cash1=$_cg1->ShowAllPos('cash/cash_list.html', $dec_c1, 
			
			$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
			$au->user_rights->CheckAccess('w',846) ,0, 1000,
			$au->user_rights->CheckAccess('w',842) ,  
			false ,true,false, 
			$au->user_rights->CheckAccess('w',847) ,  
			$au->user_rights->CheckAccess('w',843),
			
			$au->user_rights->CheckAccess('w',835),
			$au->user_rights->CheckAccess('w',844),
			$au->user_rights->CheckAccess('w',845),
			$dostavki
		
		);
		$sm1->assign('dostavki',$dostavki);
		//также нужны другие смежные счета
		$sm1->assign('another_nested_d', $_cg1->GetNestedBills($editing_user['id'], 2));
		
		
		//exped
		$dec_c1=new DBDecorator();
		$dec_c1->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		
		$dec_c1->AddEntry(new SqlEntry('p.id','select distinct cash_id from cash_to_bill where bill_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
		
		$dec_c1->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));	
		$dec_c1->AddEntry(new SqlEntry('p.kind_id',3, SqlEntry::E));
		
		$cash1=$_cg1->ShowAllPos('cash/cash_list.html', $dec_c1, 
			
			$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
			$au->user_rights->CheckAccess('w',846) ,0, 1000,
			$au->user_rights->CheckAccess('w',842) ,  
			false ,true,false, 
			$au->user_rights->CheckAccess('w',847) ,  
			$au->user_rights->CheckAccess('w',843),
			
			$au->user_rights->CheckAccess('w',835),
			$au->user_rights->CheckAccess('w',844),
			$au->user_rights->CheckAccess('w',845),
			$exped
		
		);
		$sm1->assign('exped',$exped);
		//также нужны другие смежные счета
		$sm1->assign('another_nested_e', $_cg1->GetNestedBills($editing_user['id'], 3));
		
		
		//времена работы (для экспед-ия)
		$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',"09");
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',"00");
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',"18");
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',"00");
		
		
		
		
		
		//Примечания
		$rg=new BillNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed_price']==1, $au->user_rights->CheckAccess('w',339), $au->user_rights->CheckAccess('w',349), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',191)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',283)&&($editing_user['is_confirmed_price']==1)); 
		$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',292)); 
		
		$cannot_edit_reason='';
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',302)&&in_array($editing_user['status_id'],$_editable_status_id)&&$_bill->CanEditQuantities($editing_user['id'],$cannot_edit_reason,$editing_user)); 
		if(strlen($cannot_edit_reason)>0) $cannot_edit_reason.=', либо ';
		$sm1->assign('cannot_edit_reason',$cannot_edit_reason);
		
		
		
		
		
		//кнопка доступна, если есть права и не утв-на отгрузка счета
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',188)&&(($editing_user['is_confirmed_price']==0)&&($editing_user['status_id']!=3)));
		
		$sm1->assign('can_change_pdate_shipping_plan', in_array($editing_user['status_id'],$_editable_status_id)||(($editing_user['is_confirmed_shipping']==0)&&($editing_user['status_id']!=3)));
		
		
		
		
		
		//можно ли отключать автовыравнивание?
		
		$sm1->assign('can_super_neq',$au->user_rights->CheckAccess('w',485));
		
		$sm1->assign('can_neq', 
			$au->user_rights->CheckAccess('w',474)
		);
		
		
		//можно ли отключать автоаннулирование?
		$sm1->assign('can_super_an',$au->user_rights->CheckAccess('w',539));
		
		$sm1->assign('can_an', 
			$au->user_rights->CheckAccess('w',538)
		);
		
		
		//можно ли создать входящий счет
		$sm1->assign('can_create_incoming_bill', $au->user_rights->CheckAccess('w',608)); 
		
		//можно ли создать затраты $can_make_cash
		$sm1->assign('can_make_cash', $au->user_rights->CheckAccess('w',835)); 
		
		
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',190)); 
		
		$sm1->assign('can_email_pdf',$au->user_rights->CheckAccess('w',860));
		 
		 
		//принудительный вызов   создания доставки, экспедирования
		if(isset($_GET['force_make_delivery'])&&($_GET['force_make_delivery']==1) ) $sm1->assign('force_make_delivery',1);
		if(isset($_GET['force_make_exped'])&&($_GET['force_make_exped']==1) ) $sm1->assign('force_make_exped',1); 
		 
		 
		//проверка закрыотого периода
		$not_in_closed_period=$_bill->CheckClosePdate($editing_user['id'], $closed_period_reason);
		$sm1->assign('not_in_closed_period', $not_in_closed_period);
		$sm1->assign('closed_period_reason', $closed_period_reason);
		
		
		//связанные оплаты
		$sm1->assign('binded_payments',$_bill->GetBindedPayments($editing_user['id'],$binded_payments_summ));
		$sm1->assign('binded_payments_summ',$binded_payments_summ);
		
		//авансовые оплаты
		$_pfg=new PayForBillInGroup;
		$sm1->assign('avans_payments',$_pfg->GetAvans($editing_user['supplier_id'],$result['org_id'],$editing_user['id'],$avans, $raw_ids, $raw_invs, $editing_user['contract_id']));
		
		//echo $avans;
		//var_dump($_pfg->GetAvans($editing_user['supplier_id'],$result['org_id'],$editing_user['id'],$avans, $raw_ids, $raw_invs, $editing_user['contract_id']));
		
		$sm1->assign('avans_payments_summ',$avans);
		$sm1->assign('sum_by_bill',$_pfg->SumByBill($editing_user['id']));
		
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		//echo $avans;
		
		
		//кто имеет право вводить низкий дисконт (менее 10%)
		$can_lower_users=array();
		$_usg=new UsersSGroup;
		$can_lower_users=$_usg->GetUsersByRightArr('w',  881);
	
		$sm1->assign('can_lower_users',$can_lower_users);
		$sm1->assign('can_lower_discount',$au->user_rights->CheckAccess('w',881));
	
		
		//блок утверждения цен!
		if(($editing_user['is_confirmed_price']==1)&&($editing_user['user_confirm_price_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_price_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_price_pdate']);
			
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_price_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_shipping']==0){
			
			  
		  
		  if($editing_user['is_confirmed_price']==1){
			  if($au->user_rights->CheckAccess('w',96)){
				  //полные права
				  $can_confirm_price=true;	
			  }elseif($au->user_rights->CheckAccess('w',196)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',95)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm_price',$can_confirm_price);
		
		
		//блок утв. отгрузки
		if(($editing_user['is_confirmed_shipping']==1)&&($editing_user['user_confirm_shipping_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_shipping_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_shipping_pdate']);
			
			$sm1->assign('is_confirmed_shipping_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed_price']==1){
		
		  if($editing_user['is_confirmed_shipping']==1){
			  if($au->user_rights->CheckAccess('w',96)){
				  //полные права
				  $can_confirm_shipping=true;	
			  }elseif($au->user_rights->CheckAccess('w',197)){
				  //есть права + сам утвердил
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',195);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed_price']==1);
		
		
		$sm1->assign('can_confirm_shipping',$can_confirm_shipping);
		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_bill->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		//блок вывода оплат
		if($editing_user['is_confirmed_price']==1){
			
			if($au->user_rights->CheckAccess('w',678)){
			
			
			  $_pays=new PayInGroup;
			  $_pays->prefix='_pay';
			  
			  $_pays->SetPagename('ed_bill.php');
			  
			  //$sm2=new SmartyAdm;
			  $dec2=new DBDecorator;
			  
			  
			  
			  
			 //блок фильтров статуса
			/*$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^'.$_pays->prefix.'pay_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^'.$_pays->prefix.'pay_status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$_pays->prefix.'pay_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^pay_'.$_pays->prefix.'pay_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^pay_'.$_pays->prefix.'pay_status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_'.$_pays->prefix.'pay_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$dec2->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('pay_status_id_'.$v,1));
				$dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}
			*/
			
			
			$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$_pays->prefix.'pay_statuses'])&&is_array($_GET[$_pays->prefix.'pay_statuses'])) $cou_stat=count($_GET[$_pays->prefix.'pay_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$_pays->prefix.'pay_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^pay_'.$_pays->prefix.'pay_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^pay_'.$_pays->prefix.'pay_status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_'.$_pays->prefix.'pay_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $dec2->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $dec2->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('status_id_'.$v,1));
				  $dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry($_pays->prefix.'pay_statuses[]',$v));
			  }
		  } 
			
			  
			  $dec2->AddEntry(new UriEntry('bill_id',$id));
			  
			  $_pays->SetAuthResult($result);
			  $pays=$_pays->ShowPos($id, 
			  $editing_user['supplier_id'], 
			  'pay_in/pays_list.html', 
			  $dec2, 
			  $au->user_rights->CheckAccess('w',683)||$au->user_rights->CheckAccess('w',693), 
			  $au->user_rights->CheckAccess('w',691),
			  $au->user_rights->CheckAccess('w',689),  
			  $au->user_rights->CheckAccess('w',96),
			  true,
			  false,
			  $au->user_rights->CheckAccess('w',692),
			  $au->user_rights->CheckAccess('w',690),
			  NULL, 
			  $au->user_rights->CheckAccess('w',643), 
			  $au->user_rights->CheckAccess('w',635),  
			  $total_cost, 
			  $_bill->CalcPayed($editing_user['id']),
			  $limited_supplier,
			   false,
			    $au->user_rights->CheckAccess('w',693)
			  );
			  
			  
			  $sm->assign('pays',$pays); 	
			}else  $sm->assign('pays','У Вас недостаточно прав для просмотра оплат по счету.'); 
			
		}else $sm->assign('pays','В данном режиме просмотр оплат по исходящему счету недоступен.<br />
 Пожалуйста, проставьте галочку "Утверждаю цену" и нажмите кнопку "Сохранить и остаться" на вкладке "Исходящий счет" для получения возможности просмотра оплат.'); 	
 
 
 
 		//блок вывода затрат
		if($editing_user['is_confirmed_shipping']==1){
			
			if($au->user_rights->CheckAccess('w',833)){
			
				$_pays=new CashGroup;
			 	$_pays->prefix='_cash';
			  
			 	$_pays->SetPagename('ed_bill.php');	
				
				$dec2=new DBDecorator;
				
				
				$dec2->AddEntry(new UriEntry('action',1));
				$dec2->AddEntry(new UriEntry('id',$editing_user['id']));
				
				
				$dec2->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
				//$dec2->AddEntry(new SqlEntry('p.bill_id',$editing_user['id'], SqlEntry::E));
				
				$dec2->AddEntry(new SqlEntry('p.id','select distinct cash_id from cash_to_bill where bill_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
			
				
				 //если нет прав на просмотр всех расходов - просматривать только свои
			   if(!$au->user_rights->CheckAccess('w',834)&&$au->user_rights->CheckAccess('w',875)){
					//есть права на просмотр доставок, экспедированией всех сотрудников
			//		$decorator->AddEntry(new SqlEntry('p.code_id',SecStr($_GET['code_code'.$prefix]), SqlEntry::E));
					$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
					$dec2->AddEntry(new SqlEntry('p.kind_id', NULL, SqlEntry::IN_VALUES, NULL,array(2,3)));		
					
					$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
					
					
					$dec2->AddEntry(new SqlEntry('mn.id',$result['id'], SqlEntry::E));
					
					$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
					$dec2->AddEntry(new SqlEntry('p.responsible_user_id',$result['id'], SqlEntry::E));
					
					
					$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
					
					//echo 'zz';
				}elseif(!$au->user_rights->CheckAccess('w',834)){
				  $dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
				  $dec2->AddEntry(new SqlEntry('mn.id',$result['id'], SqlEntry::E));
				  
				  $dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
				  $dec2->AddEntry(new SqlEntry('p.responsible_user_id',$result['id'], SqlEntry::E));
				  
				  $dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			  } 
			 
	 
				
				$dec2->AddEntry(new UriEntry('id',$id));
				
				$_pays->SetAuthResult($result);
				$cash=$_pays->ShowAllPos('cash/cash_list.html',//1
								 $dec2, //1
				
				$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848), //2 
				$au->user_rights->CheckAccess('w',846) ,//3
				0, //4
				1000,//5
				$au->user_rights->CheckAccess('w',842) ,  //6
				false , //7
				true,//8
				false, //9
				$au->user_rights->CheckAccess('w',847) ,  //10
				$au->user_rights->CheckAccess('w',843),//11
								
				$au->user_rights->CheckAccess('w',835),//12
				$au->user_rights->CheckAccess('w',844),//13
				$au->user_rights->CheckAccess('w',845),//14
				$temp_cash,
				$au->user_rights->CheckAccess('w',851),
				$au->user_rights->CheckAccess('w',848)
				
				);
			  
			  
			  $sm->assign('cash',$cash); 	
			}else  $sm->assign('cash','У Вас недостаточно прав для просмотра затрат по счету.'); 
			
		}else $sm->assign('cash','В данном режиме просмотр затрат по исходящему счету недоступен.<br />
 Пожалуйста, проставьте обе галочки утверждения: "Утверждаю цену"  и нажмите кнопку "Сохранить и остаться", затем "Утверждаю отгрузку"  и нажмите кнопку "Сохранить и остаться" на вкладке "Исходящий счет" для получения возможности просмотра затрат.'); 
		
		
		//блок вывода вход. счетов
		if($editing_user['is_confirmed_price']==1){
			
			if($au->user_rights->CheckAccess('w',606)){
			
				
				
			
			$bg=new BillInGroup; 
			$bg->prefix='_in_bill';
			$bg->SetPageName('ed_bill.php');
			
			
		 
			
			//Разбор переменных запроса
			if(isset($_GET['from_bill'])) $from_bill=abs((int)$_GET['from_bill']);
			else $from_bill=0;
			
			if(isset($_GET['to_page_bill'])) $to_page_bill=abs((int)$_GET['to_page_bill']);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			//$decorator->AddEntry(new SqlEntry('p.komplekt_ved_id',$editing_user['id'], SqlEntry::E)); //-- перенесем в аргументы
			$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from bill_position where out_bill_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
			
			
			/*
			
			//блок фильтров статуса
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_in_bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
			
				$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['bill_in_bill_statuses'])&&is_array($_GET['bill_in_bill_statuses'])) $cou_stat=count($_GET['bill_in_bill_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['bill_in_bill_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_in_bill_statuses[]',$v));
			  }
		  } 
		


			
			if(!isset($_GET['pdate_bill1_in_bill'])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012');//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_bill1_in_bill'];
			
			
			
			if(!isset($_GET['pdate_bill2_in_bill'])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_bill2_in_bill'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_bill1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_bill2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_bill_in_bill'])&&(strlen($_GET['code_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_bill_in_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_bill',$_GET['code_bill_in_bill']));
			}
			
			if(isset($_GET['name_bill_in_bill'])&&(strlen($_GET['name_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name_bill_in_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('name_bill',$_GET['name_bill_in_bill']));
			}
			
			if(isset($_GET['supplier_name_bill_in_bill'])&&(strlen($_GET['supplier_name_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_bill_in_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('supplier_name_bill',$_GET['supplier_name_bill_in_bill']));
			}
			
			 
			
			
			if(isset($_GET['user_confirm_price_id_bill_in_bill'])&&(strlen($_GET['user_confirm_price_id_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.user_confirm_price_id',abs((int)$_GET['user_confirm_price_id_bill_in_bill']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_confirm_price_id_bill',$_GET['user_confirm_price_id_bill_in_bill']));
			}
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode_bill_in_bill'])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_bill_in_bill']);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode_bill',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			//do_show_bills_in
			$decorator->AddEntry(new UriEntry('do_show_bills_in',1));
			
			$bg->SetAuthResult($result);
			
			$bills_in=$bg->ShowPos('bills_in/bills_list_komplekt.html',
				$decorator,
				$from,
				$to_page, 
				$au->user_rights->CheckAccess('w',607),
				$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), 
				$au->user_rights->CheckAccess('w',626),
				'_in_bill', 
				$au->user_rights->CheckAccess('w',620),
				$au->user_rights->CheckAccess('w',96),
				true,
				false,
				$au->user_rights->CheckAccess('w',627),
				$limited_sector, 
				$editing_user['id'], 
				$au->user_rights->CheckAccess('w',621),
				$au->user_rights->CheckAccess('w',622), 
				$au->user_rights->CheckAccess('w',623),//17
				$temp_bills,
				$au->user_rights->CheckAccess('w',625),
				false,
				false,
				$limited_supplier
				);	
				
				
			
			
			  $sm->assign('bills_in',$bills_in); 	
			}else  $sm->assign('bills_in','У Вас недостаточно прав для просмотра входящих счетов по счету.'); 
			
		}else $sm->assign('bills_in','В данном режиме просмотр входящих счетов по исходящему счету недоступен.<br />
 Пожалуйста, проставьте галочку "Утверждаю цену" и нажмите кнопку "Сохранить и остаться" на вкладке "Исходящий счет" для получения возможности просмотра входящих счетов.'); 	
		
		
		if($editing_user['is_confirmed_shipping']==1){
			
			//вкладка +-/
			if($au->user_rights->CheckAccess('w',365)){
				$pos_pm_given='';
			  	
				if($editing_user['status_id']==10){
				
				  $sm2=new SmartyAdm;
				  
				  $pos_pms=$bpg;
				  $_cpg=new CashBillPositionGroup;
				  
				  $to_give_summ=0;
				  $given_summ=0;
				  foreach($pos_pms as $k=>$v){
					  
					  //получить сумму Выдано из документов 
						
					  $pos_pms[$k]['discount_given']=$_cpg->CalcGiven($v['p_id']);
					  $v['discount_given']=$_cpg->CalcGiven($v['p_id']);
					  
					  	
					  //какие документы на выдачу???
					  $pos_pms[$k]['docs']=$_cpg->GetCashesSemiByBillPosition($v['p_id']);
					 $pos_pms[$k]['semi_discount_given']=$_cpg->CalcSemiGiven($v['p_id']); 
					 $v['semi_discount_given']=$pos_pms[$k]['semi_discount_given']; 
					  
					  $pos_pms[$k]['pm_per_unit']=number_format($v['price_pm']-$v['price'],2,'.',DEC_SEP);
					  $pos_pms[$k]['pm_per_cost']=number_format(($v['price_pm']-$v['price'])*$v['quantity'],2,'.',DEC_SEP);	
					  
					  $disc=$vv=$_bpf->FormDiscount($v['price_f'], $v['quantity'], $v['has_pm'], $v['plus_or_minus'], $v['value'], $v['rub_or_percent'],$v['price_pm'],$v['total'],$v['discount_value'],$v['discount_rub_or_percent']);
						  
					 $pos_pms[$k]['discount_value']=number_format($v['discount_value'],2,'.',DEC_SEP);
					 $pos_pms[$k]['discount_rub_or_percent']=$v['discount_rub_or_percent'];
					 
					 //найдем дисконт
					 $pos_pms[$k]['discount_amount']=number_format($disc['discount_amount'],2,'.',DEC_SEP);
					
					 $pos_pms[$k]['discount_total_amount']=number_format($disc['discount_amount']*$v['quantity'],2,'.',DEC_SEP);
					
					  $pos_pms[$k]['vydacha']= round(($v['price_pm']-$v['price'])*$v['quantity'] -  $disc['discount_amount']*$v['quantity'],2);    //$v['total']-$vv['cost'];
					
					$to_give_summ+=  ($v['price_pm']-$v['price'])*$v['quantity'] -  $disc['discount_amount']*$v['quantity'];
					$given_summ+=$v['semi_discount_given'];
				  }
				  
				  $sm2->assign('items',$pos_pms);
				  
			 	 $sm2->assign('to_give_summ',round($to_give_summ,2));
			  	 $sm2->assign('given_summ',round($given_summ,2));
				
				
				  //сотр.-получатели
				$_ug=new UsersGroup;
				$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
				$_ids=array(); $_vals=array();
				$_ids[]=0; $_vals[]='-выберите-';
				foreach($ug as $k=>$v){
					$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
				}
				$sm2->assign('responsible_user_id_ids',$_ids);
				$sm2->assign('responsible_user_id_vals',$_vals);
				
				$sm2->assign('responsible_user_id',$result['id']);
						
				
				$pos_pm_given=$sm2->fetch('bills/positions_pm.html');
				}else{
					$pos_pm_given='Статус данного счета не позволяет вносить суммы выданных +/-. Для работы с выданными +/- необходимо, чтобы статус счета был "Выполнен".';
				}
			  $sm->assign('pm_folder',$pos_pm_given);
			  
			   	
			}else  $sm->assign('pm_folder','У Вас недостаточно прав для просмотра выданных +/- по счету.'); 
			
			
			
			
			
		
			
			
			if($au->user_rights->CheckAccess('w',228)){
			  //вывод реализации
			  $_acg=new AccGroup;
			  
			  $_acg->SetPagename('ed_bill.php');
			  
			  $dec2=new DBDecorator;
			  
			
			  
			 //блок фильтров статуса
			/*$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^acc_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_acc_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$dec2->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			//print_r($status_ids);
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('acc_status_id_'.$v,1));
				$dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}
			  */
			  
			  $status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['acc_statuses'])&&is_array($_GET['acc_statuses'])) $cou_stat=count($_GET['acc_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['acc_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_acc_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $dec2->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $dec2->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('status_id_'.$v,1));
				  $dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				  foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('acc_statuses[]',$v));
			  }
		  } 
		
			  
			  if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
				  $dec2->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
				  $dec2->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
			  }
			  
			  
			  $_acg->SetAuthResult($result);
			  $ships=$_acg->ShowPos(
			  	$id,
				'acc/acc_list.html', 
				$dec2, 
				$au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), 
				$au->user_rights->CheckAccess('w',242), 
				$au->user_rights->CheckAccess('w',240),
				$au->user_rights->CheckAccess('w',96),
				true,
				false, 
				$au->user_rights->CheckAccess('w',243),
				$limited_sector, //10
				NULL, 
				$au->user_rights->CheckAccess('w',241),
				$temp_accs,
				$au->user_rights->CheckAccess('w',286),
				false,
				$limited_supplier
				);
			  
			  $sm->assign('accs',$ships); 	
			}else $sm->assign('accs','У Вас недостаточно прав для просмотра реализаций по счету.'); 	
			
			
		}else{
			$sm->assign('pm_folder','В данном режиме просмотр выданных +/- по исходящему счету недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю отгрузку" и нажмите кнопку "Сохранить и остаться" на вкладке "Исходящий счет" для получения возможности просмотра выданных +/-.'); 
			
		
			
		
			
			$sm->assign('accs','В данном режиме просмотр реализаций товара по исходящему счету недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю отгрузку" и нажмите кнопку "Сохранить и остаться" на вкладке "Исходящий счет" для получения возможности просмотра реализаций товара.');
			
		}
		
		if(!isset($_GET['do_show_log'])){
			$do_show_log=false;
		}else{
			$do_show_log=true;
		}
		$sm->assign('do_show_log',$do_show_log);
		
	 
	 	if(!isset($_GET['do_show_bills_in'])){
			$do_show_bills_in=false;
		}else{
			$do_show_bills_in=true;
		}
		$sm->assign('do_show_bills_in',$do_show_bills_in);
	 
	 
		
		if(!isset($_GET['do_show_acc'])){
			$do_show_acc=false;
		}else{
			$do_show_acc=true;
		}
		$sm->assign('do_show_acc',$do_show_acc);
		
		if(!isset($_GET['do_show_pay'])){
			$do_show_pay=false;
		}else{
			$do_show_pay=true;
		}
		$sm->assign('do_show_pay',$do_show_pay);
		
		
		
		if(!isset($_GET['do_show_cash'])){
			$do_show_cash=false;
		}else{
			$do_show_cash=true;
		}
		$sm->assign('do_show_cash',$do_show_cash);
		
			
		if(!isset($_GET['do_show_pm_folder'])){
			$do_show_pm_folder=false;
		}else{
			$do_show_pm_folder=true;
		}
		$sm->assign('do_show_pm_folder',$do_show_pm_folder);
		
		$sm->assign('has_bills_in', true);	
		 
		$sm->assign('has_pays', true);//($editing_user['is_confirmed_price']==1));
		
		$sm->assign('has_acc', true);	
		
		$sm->assign('has_pm_folder', true);	
		
		$sm->assign('has_cash', true);
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',92)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',93)); 
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129)); 
		
		
		if(($print_add!='')&&($editing_user['org_id']==33)) $user_form=$sm1->fetch('bills/bill_edit_nt'.$print_add.'.html');
		else		
		$user_form=$sm1->fetch('bills/bill_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',522)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			
			
			
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(97,
92,
128,
188,
189,
190,
129,
133,
130,
93,
191,
339,
349,
192,
193,
194,
95,
195,
196,
197,
292,
283,
94,
131,
302,
365,
474,
485,
538,
539,
480,
481,
522,
523,
860
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_bill.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	/*if(($print==1)&&($printmode==2)){
		
	}else */
	$content=$sm->fetch('bills/ed_bill_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else {
		
		//header
		$sm_h=new SmartyAdm;
		
		$sm_h->assign('bill', $editing_user);
		$header=$sm_h->fetch('bills/bill_edit_header.html');
		$tmp1='h'.time();
		
		$f1=fopen(ABSPATH.'/tmp/'.$tmp1.'.html','w');
		fputs($f1, $header);
		fclose($f1);
		
		
		//echo $content; die();
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		$comand = "wkhtmltopdf-i386 --page-size A4 --orientation Portrait --encoding windows-1251 --image-quality 100 --margin-top 5mm --margin-bottom 5mm --margin-left 10mm --margin-right 10mm  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
 		 
	 
		exec($comand);
	
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Счет_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf'); 
		
	//	readfile(ABSPATH.'/tmp/'.$tmp.'.html');
		
		
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/'.$tmp1.'.html'); 
		
	exit;
	}
	
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
//else $smarty->display('bottom_print.html');
unset($smarty);
?>