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

require_once('classes/bill_in_posgroup.php');
require_once('classes/sectorgroup.php');

require_once('classes/user_s_item.php');



require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/billnotesgroup.php');
require_once('classes/billnotesitem.php');

require_once('classes/billcreator.php');

//require_once('classes/payforbillgroup.php');
require_once('classes/invcalcgroup.php');
require_once('classes/pergroup.php');

require_once('classes/period_checker.php');

 

require_once('classes/bill_in_item.php');

require_once('classes/bill_in_creator.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

 
require_once('classes/acc_in_group.php');

require_once('classes/supplier_to_user.php');

$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование входящего счета');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new BillInItem;
$_out_bill=new BillItem;

$_bpi=new BillPosItem;
$_position=new PosItem;

$_sectors=new SectorGroup;

//$lc=new LoginCreator;
$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;


$lc=new BillInCreator;

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
	if(!$au->user_rights->CheckAccess('w',625)){
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
	$object_id[]=607;
	$object_id[]=608;
	break;
	case 1:
	$object_id[]=613;
	$object_id[]=625;
	break;
	case 2:
	$object_id[]=626;
	break;
	default:
	$object_id[]=608;
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

	//родит. счет
	if(!isset($_GET['out_bill_id'])){
		if(!isset($_POST['out_bill_id'])){
			$out_bill_id=0;
		}else $out_bill_id=abs((int)$_POST['out_bill_id']); 
	}else $out_bill_id=abs((int)$_GET['out_bill_id']);
		
	$out_bill=$_out_bill->GetItemById($out_bill_id);
	
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
	$editing_user=$_bill->GetItemByFields(array('id'=>$id, 'is_incoming'=>1));
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
			
			//также проверить, чтобы этот счет был связан с исход. счетом от этого п-ка
			$out_bill_ids=array();
			$sql='select id from bill where is_incoming=0 and supplier_id in ('.implode(', ',$limited_supplier).')';
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$out_bill_ids[]=$f['id'];
			}
			
			if(!in_array($editing_user['out_bill_id'], $out_bill_ids)){
			
				header("HTTP/1.1 403 Forbidden");
				header("Status: 403 Forbidden");
				include("403.php");
				die();	
			}
		}
	}
	
}



//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту входящего счета',NULL,613, NULL, $editing_user['code'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту входящего счета: версия для печати',NULL,625, NULL, $editing_user['code'],$id);
				
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',607)&&!$au->user_rights->CheckAccess('w',608)){
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
	
	//if(isset($_POST['contract_no'])) $params['contract_no']=SecStr($_POST['contract_no']);
		//if(isset($_POST['contract_pdate'])) $params['contract_pdate']=SecStr($_POST['contract_pdate']);
		$params['contract_id']=abs((int)$_POST['contract_id']);
	
	if(strlen($_POST['pdate_shipping_plan'])==10) $params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
	if(strlen($_POST['pdate_payment_contract'])==10) $params['pdate_payment_contract']=DateFromdmY($_POST['pdate_payment_contract']);
	
	$params['bdetails_id']=abs((int)$_POST['bdetails_id']);
	$params['out_bill_id']=abs((int)$_POST['out_bill_id']);
	
	$params['sector_id']=abs((int)$_POST['sector_id']);
	
	//$params['notes']=SecStr($_POST['notes']);
	//$params['code']=SecStr($_POST['code']);
	
	$lc->ses->ClearOldSessions();
	$params['code']=$lc->GenLogin($result['id']); //SecStr($_POST['code']);
	
	$params['is_incoming']=1;
	
	
	$params['is_confirmed_price']=0;
	$params['is_confirmed_shipping']=0;
	
	
	$params['manager_id']=$result['id'];
	
	
	$params['supplier_bill_no']=SecStr($_POST['supplier_bill_no']);
	
	if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);
	
	
	if($au->user_rights->CheckAccess('w',630)){
		if(isset($_POST['cannot_eq'])){
			 $params['cannot_eq']=1;
			  $params['cannot_eq_id']=$result['id'];
			 $params['cannot_eq_pdate']=time();
		}else $params['cannot_eq']=0;
	}
	
	if($au->user_rights->CheckAccess('w',632)){
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
		$log->PutEntry($result['id'],'создал входящий счет',NULL,607,NULL,NULL,$code);	
		
		
		foreach($params as $k=>$v){
			
		 
				if($k=='supplier_id'){
					$_si=new SupplierItem; $_opf=new OpfItem;
					$si=$_si->GetItemById($v); $opf=$_opf->GetItemById($si['opf_id']);
					
					
					$log->PutEntry($result['id'],'создал входящий счет',NULL,607, NULL, SecStr('установлен контрагент '.$si['code'].' '.$opf['name'].' '.$si['full_name']),$code);			
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'создал входящий счет',NULL,607, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
		
		if($au->user_rights->CheckAccess('w',630)&&($params['cannot_eq']==1)){
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
		
		if($au->user_rights->CheckAccess('w',632)&&($params['cannot_an']==1)){
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
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',609))){
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
				  'out_bill_id'=>(int)$_POST['new_out_bill_id_'.$hash],
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
				
				$log->PutEntry($result['id'],'добавил позицию входящего счета', NULL, 613,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: bills.php#user_1_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',613)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_bill_in.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: bills.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',613)){
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
	
		//if(isset($_POST['contract_no'])) $params['contract_no']=SecStr($_POST['contract_no']);
		//if(isset($_POST['contract_pdate'])) $params['contract_pdate']=SecStr($_POST['contract_pdate']);
		if(isset($_POST['contract_id'])) $params['contract_id']=abs((int)$_POST['contract_id']);
	
		if(strlen($_POST['pdate_shipping_plan'])==10) $params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
		if(strlen($_POST['pdate_payment_contract'])==10) $params['pdate_payment_contract']=DateFromdmY($_POST['pdate_payment_contract']);
		
		
		
		
		if(isset($_POST['bdetails_id'])) $params['bdetails_id']=abs((int)$_POST['bdetails_id']);
		
		if(isset($_POST['out_bill_id'])) $params['out_bill_id']=abs((int)$_POST['out_bill_id']);
	
		if(isset($_POST['sector_id'])) $params['sector_id']=abs((int)$_POST['sector_id']);
		
		
		if(isset($_POST['supplier_bill_no'])) $params['supplier_bill_no']=SecStr($_POST['supplier_bill_no']);
	
		if(strlen($_POST['supplier_bill_pdate'])==10) $params['supplier_bill_pdate']=DateFromdmY($_POST['supplier_bill_pdate']);
		
		
		
		$_bill->Edit($id, $params,false,$result);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				if($k=='pdate_shipping_plan'){
					$log->PutEntry($result['id'],'редактировал плановую дату поставки',NULL,613,NULL,'дата: '.$_POST['pdate_shipping_plan'],$id);
					continue;	
				}
				
				if($k=='pdate_payment_contract'){
					$log->PutEntry($result['id'],'редактировал дату оплаты по договору',NULL,613,NULL,'дата: '.$_POST['pdate_payment_contract'],$id);
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'редактировал входящий счет',NULL,613, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
	}
	
	//правим откл автоаннул
	/*отдельным блоком - правка откл автовыравнивания*/
	if(
	(in_array($_POST['current_status_id'],array(1,2,9,20,21))&&in_array($editing_user['status_id'],array(1,2,9,20,21))&&($editing_user['is_confirmed_shipping']==0)&&$au->user_rights->CheckAccess('w',630))||
	($au->user_rights->CheckAccess('w',631))
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
				$log->PutEntry($result['id'],'редактировал входящий счет',NULL,630, NULL, 'в поле '.$k.' установлено значение '.$v,$id);	
				
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
	(in_array($_POST['current_status_id'],array(1,2,9,20,21))&&in_array($editing_user['status_id'],array(1,2,9,20,21))&&($editing_user['is_confirmed_shipping']==0)&&$au->user_rights->CheckAccess('w',632))||
	($au->user_rights->CheckAccess('w',633))
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
				$log->PutEntry($result['id'],'редактировал входящий счет',NULL,632, NULL, 'в поле '.$k.' установлено значение '.$v,$id);	
				
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
		$au->user_rights->CheckAccess('w',637))
	;
	
	
	//правим позиции	
	if($condition_positions){	
		if($au->user_rights->CheckAccess('w',613)){
			//плановая дата поставки
			$a_params=array();
			
			if(strlen($_POST['pdate_shipping_plan'])==10) $a_params['pdate_shipping_plan']=DateFromdmY($_POST['pdate_shipping_plan']);
			
			$_bill->Edit($id, $a_params,false,$result);
			
			
			foreach($a_params as $k=>$v){
			
			  if(addslashes($editing_user[$k])!=$v){
				  if($k=='pdate_shipping_plan'){
					  $log->PutEntry($result['id'],'редактировал плановую дату поставки',NULL,613,NULL,'дата: '.$_POST['pdate_shipping_plan'],$id);
					  continue;	
				  }
				  
				 
				  
				  $log->PutEntry($result['id'],'редактировал входящий счет',NULL,613, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				 		  
			  }
		  }
		};
		
		
		
		if($au->user_rights->CheckAccess('w',609)){
		  $positions=array();
		  
		  $_pos=new PosItem;
		  $_pdi=new PosDimItem;
		  $_kpi=new KomplPosItem;
		  
		  
		  $check_delta_summ=(($editing_user['is_confirmed_price']==1)&&
		  ($editing_user['is_confirmed_shipping']==1)&&
		  $_bill->HasShsorAccs($editing_user['id'])&&
		  $au->user_rights->CheckAccess('w',637));
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
					'out_bill_id'=>(int)$_POST['new_out_bill_id_'.$hash],
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
				  $log->PutEntry($result['id'],'добавил позицию входящего счета',NULL,613,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию входящего счета',NULL,613,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию входящего счета',NULL,613,NULL,$description,$id);
			  }
			  
		  }
		  
		  if($check_delta_summ){
				$new_summ=$_bill->CalcCost($id);  
				
				/*echo $old_summ.'   '.$new_summ; 
				die();
				*/
				if($new_summ!=$old_summ){
					 $description='старая сумма: '.$old_summ.' руб., новая сумма: '.$new_summ.' руб.';
					 $log->PutEntry($result['id'],'изменение суммы счета при редактировании +/-',NULL,637,NULL,$description,$id);
				}
		  }
		
		}
	}
	
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed_shipping']==0){
	  if($editing_user['is_confirmed_price']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',622))||$au->user_rights->CheckAccess('w',96)){
			  if((!isset($_POST['is_confirmed_price']))&&in_array($editing_user['status_id'], array(2,9,10,20,21))&&in_array($_POST['current_status_id'], array(2,9,10,20,21))&&($_bill->DocCanUnconfirmPrice($id, $rss32))){
				  
				  //&&($editing_user['status_id']==5)&&($_POST['current_status_id']==5)
				  $_bill->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение цен',NULL,622, NULL, NULL,$id);	
				  $_bill->FreeBindedPayments($id);
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',620)||$au->user_rights->CheckAccess('w',96)){
			  if(isset($_POST['is_confirmed_price'])&&($_POST['is_confirmed_price']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))&&($_bill->DocCanConfirmPrice($id, $rss32))){
				  
				  $_bill->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил цены',NULL,620, NULL, NULL,$id);	
				  
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
		  if(($au->user_rights->CheckAccess('w',623))||$au->user_rights->CheckAccess('w',96)){
			  //if(!isset($_POST['is_confirmed_shipping'])){
			  if((!isset($_POST['is_confirmed_shipping'])) &&in_array($editing_user['status_id'], array(2,9,10,20,21))&&in_array($_POST['current_status_id'], array(2,9,10,20,21))&&($_bill->DocCanUnconfirmShip($id, $rss32))){
				  $_bill->Edit($id,array('is_confirmed_shipping'=>0, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение приемки',NULL,623, NULL, NULL,$id);	
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',621)||$au->user_rights->CheckAccess('w',96)){
			  if(isset($_POST['is_confirmed_shipping'])&&in_array($editing_user['status_id'], array(2,9,10,20,21))&&in_array($_POST['current_status_id'], array(2,9,10,20,21))&&($_bill->DocCanConfirmShip($id, $rss32))){
				  $_bill->Edit($id,array('is_confirmed_shipping'=>1, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил приемку',NULL,621, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: bills.php#user_1_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',613)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_bill_in.php?action=1&id=".$id.'&from_begin='.$from_begin);
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
else $smarty->display('top_print.html');
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
		
		$sm1->assign('out_bill_id', $out_bill_id);
		
		//поставщики
		$dec=new DBDecorator;
		//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result);
		
		$sm1->assign('suppliers',$supgroup);
		
		
		
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
		
		//родит. счет
		$sm1->assign('out_bill', $out_bill);
		
		//передать позиции...
		$positions=array();
 
		$_bpf=new BillPosPMFormer;
	 
		
		//из исход. счета: получить позиции исход. счета, подставить их сюда
	
	
		$_posits=new BillPosGroup;
		$posits=$_posits->GetItemsByIdArr($out_bill_id, 0, true, $out_bill);
		$out_bill=$_out_bill->GetItemById($out_bill_id);
		$_mf=new maxformer;
		foreach($posits as $k=>$h){
		  
		 
			
			  $pos_id=$h['position_id'];
			  
			  
			  $qua=$h['max_for_incoming_quantity'] ;//((float)$v);	
			  
			  
			
			  
			  if($qua>0){
				  
				 // echo $pos_id.'_'.$h['storage_id'].'_'.$h['sector_id'].'_'.$h['komplekt_ved_id'].'_'.$out_bill_id.'<br>';
			  	 $positions[]=array(
					  'id'=>$pos_id,
					  'hash'=>md5($pos_id.'_'.$h['storage_id'].'_'.$h['sector_id'].'_'.$h['komplekt_ved_id'].'_'.$out_bill_id),
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
					  'quantity_confirmed'=>$_mf->MaxInBill($out_bill_id, $pos_id,NULL,NULL,$h['komplekt_ved_id']), //сколько в род. исход. счете вообще?
					  'max_quantity'=>$qua, //сколько доступно по род. исход. счету?
					  'in_rasp'=>0,
					  'storage_id'=>$h['storage_id'],
					  'storage_name'=>$h['storage_name'],
					  
					  //новые поля
					  'sector_id'=>$h['sector_id'],
					  'sector_name'=>$h['sector_name'],
					  
					  'komplekt_ved_id'=>$h['komplekt_ved_id'],
					  //'komplekt_ved_name'=>'Заявка № '.$h['komplekt_ved_id'],
					  'komplekt_ved_name'=>$h['komplekt_ved_name'],
					  'out_bill_id'=>$out_bill_id,
						'out_bill_code'=>$out_bill['code']
											  
				  );
				
			   
				
				
				 
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
		
		$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',608)||$au->user_rights->CheckAccess('w',607)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',614)); 
		//$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129));
		
		//можно ли отключать автовыравнивание?
		$sm1->assign('can_cannot_eq', $au->user_rights->CheckAccess('w',630));
		
		
		//можно ли отключать автоаннулирование?
		$sm1->assign('can_cannot_an', $au->user_rights->CheckAccess('w',632));
		
		
		
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',628)); 
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',609)); 
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',611)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',NDS);
		
		$user_form=$sm1->fetch('bills_in/bill_create.html');
		
		
	
 
 
 		$sm->assign('has_acc', true); //($editing_user['is_confirmed_shipping']==1));
		$sm->assign('accs','В данном режиме просмотр поступлений товара по входящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать входящий счет и перейти к утверждению" на вкладке "Входящий счет" для получения возможности просмотра поступлений товара.');		
 		
 
		
		$sm->assign('has_pays', true);//($editing_user['is_confirmed_price']==1));
		$sm->assign('pays','В данном режиме просмотр оплат по входящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать входящий счет и перейти к утверждению" на вкладке "Входящий счет" для получения возможности просмотра оплат.');		
		
		
		$sm->assign('has_pm_folder', $au->user_rights->CheckAccess('w',629));
		$sm->assign('pm_folder','В данном режиме просмотр выданных +/- по входящему счету недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать входящий счет и перейти к утверждению" на вкладке "Входящий счет" для получения возможности просмотра выданных +/- .');	
		
		
		if($au->user_rights->CheckAccess('w',636)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','В данном режиме просмотр журнала событий входящего счета недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать входящий счет и перейти к утверждению" на вкладке "Входящий счет" для получения возможности просмотра журнала событий.');		
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
			$ccu=$cu['name_s'] ;
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		if($editing_user['pdate_shipping_plan']==0) $editing_user['pdate_shipping_plan']='-';
		else $editing_user['pdate_shipping_plan']=date("d.m.Y", $editing_user['pdate_shipping_plan']);
		
		if($editing_user['pdate_payment_contract']==0) $editing_user['pdate_payment_contract']='-';
		else $editing_user['pdate_payment_contract']=date("d.m.Y", $editing_user['pdate_payment_contract']);
		
			
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
		
		//фактическая дата поставки - увязана с поступлениями
		$_acg=new AccInGroup;
			
		$dec2=new DBDecorator;
		
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$dec2->AddEntry(new UriEntry('is_confirmed_acc',1));
		
		$_acg->SetAuthResult($result);
		$ships=$_acg->ShowPos($id,'bills_in/fact_dates.html', $dec2, $au->user_rights->CheckAccess('w',664), $au->user_rights->CheckAccess('w',664), $au->user_rights->CheckAccess('w',671), $au->user_rights->CheckAccess('w',96));
		
		$sm1->assign('fact_days',$ships); 	
		
		
		
		//фактическая дата оплаты - увязана с оплатами
		$_pays=new PayGroup;
		$_pays->SetPagename('ed_bill_in.php');
		$dec2=new DBDecorator;
		$dec2->AddEntry(new SqlEntry('p.is_confirmed',1, SqlEntry::E));
		$_pays->SetAuthResult($result);
		$pays=$_pays->ShowPos($editing_user['id'], $editing_user['supplier_id'], 'bills_in/fact_pays.html', $dec2, $au->user_rights->CheckAccess('w',272), $au->user_rights->CheckAccess('w',279), $au->user_rights->CheckAccess('w',277),   $au->user_rights->CheckAccess('w',96),true,false, $au->user_rights->CheckAccess('w',280), $au->user_rights->CheckAccess('w',278),true, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481), $total_cost, $_bill->CalcPayed($editing_user['id']));
		
		//добавим также инв. акты
		$_invg=new InvCalcGroup;
		$_invg->SetPageName('ed_bill_in.php');
		$dec2=new DBDecorator;
		$dec2->AddEntry(new SqlEntry('p.is_confirmed_inv',1, SqlEntry::E));
		
		$dec2->AddEntry(new SqlEntry('p.supplier_id',$editing_user['supplier_id'], SqlEntry::E));
		
		$_invg->SetAuthResult($result);
		$pays.=$_invg->ShowPosByBill($editing_user['id'],'bills_in/fact_invs.html',$dec2,0,10000, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),true,false,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461));
		
			
		$sm1->assign('fact_pays',trim($pays)); 	
		
		
		
		
		
		if($editing_user['supplier_bill_pdate']==0) $editing_user['supplier_bill_pdate']='-';
		else $editing_user['supplier_bill_pdate']=date("d.m.Y", $editing_user['supplier_bill_pdate']);
		
		
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($si['opf_id']); 
		$editing_user['supplier_id_string']=$opfitem['name'].' '.$si['full_name'];
		
		
		/*$supgroup=*/
		$dec=new DBDecorator;
		//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result); //GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
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
		$scg=$_scg->GetItemsByIdArr($editing_user['supplier_id'], $editing_user['contract_id'],1);
		$editing_user['condetails']=$scg;
		
		//подставить даты договора
		$_sci=new SupContractItem;
		$sci=$_sci->GetItemById($editing_user['contract_id']);
		$editing_user['contract_no']=$sci['contract_no'];
		$editing_user['contract_pdate']=$sci['contract_pdate'];
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new BillInPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		
		
		//стоимость и итого
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg,true,$editing_user['supplier_id']);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
		
		
		//коррекция +/-
		$sm1->assign('can_modify_pms',($editing_user['is_confirmed_price']==1)&&
		($editing_user['is_confirmed_shipping']==1)&&
		$_bill->HasShsorAccs($editing_user['id'])&&
		$au->user_rights->CheckAccess('w',637));
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',626);
		if(!$au->user_rights->CheckAccess('w',626)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',627);
			if(!$au->user_rights->CheckAccess('w',627)) $reason='недостаточно прав для данной операции';
		
		
		
		
		//$sm1->assign('org',$orgitem['name']);
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		//если у счета утверждены цены - просматривать можно при наличии прав 365 (выдача +/- в счете)
		//в других статусах: 130 (работа с +/-)
		if($editing_user['is_confirmed_price']==1){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
		
		
		$sm1->assign('not_changed_pos',true);
		
		//есть ли поступл, расп.
		$sm1->assign('has_rasp_or_post',$_bill->HasR($editing_user['id']));
		$sm1->assign('rasp_or_post_list',$_bill->HasRList($editing_user['id']));
		
		
		//поставщики
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_active'=>1));
		$supgroup=$_supgroup->GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('pos',$supgroup);
		
	
		
		
		
		//Примечания
		$rg=new BillNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed_price']==1, $au->user_rights->CheckAccess('w',615), $au->user_rights->CheckAccess('w',616), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',614)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		$sm1->assign('BILLUP',BILLUP);
		$sm1->assign('NDS',$_si->FindNDS($editing_user['supplier_id']));
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',625)); 
		$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',624)); 
		
		$cannot_edit_reason='';
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',628)&&in_array($editing_user['status_id'],$_editable_status_id)&&$_bill->CanEditQuantities($editing_user['id'],$cannot_edit_reason,$editing_user)); 
		if(strlen($cannot_edit_reason)>0) $cannot_edit_reason.=', либо ';
		$sm1->assign('cannot_edit_reason',$cannot_edit_reason);
		
		
		
		
		
		//кнопка доступна, если есть права и не утв-на отгрузка счета
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',609)&&(($editing_user['is_confirmed_price']==0)&&($editing_user['status_id']!=3)));
		
		$sm1->assign('can_change_pdate_shipping_plan', in_array($editing_user['status_id'],$_editable_status_id)||(($editing_user['is_confirmed_shipping']==0)&&($editing_user['status_id']!=3)));
		
		
		
		
		
		//можно ли отключать автовыравнивание?
		
		$sm1->assign('can_super_neq',$au->user_rights->CheckAccess('w',631));
		
		$sm1->assign('can_neq', 
			$au->user_rights->CheckAccess('w',630)
		);
		
		
		//можно ли отключать автоаннулирование?
		$sm1->assign('can_super_an',$au->user_rights->CheckAccess('w',633));
		
		$sm1->assign('can_an', 
			$au->user_rights->CheckAccess('w',632)
		);
		
		
		
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',611)); 
		
		
		//проверка закрыотого периода
		$not_in_closed_period=$_bill->CheckClosePdate($editing_user['id'], $closed_period_reason);
		$sm1->assign('not_in_closed_period', $not_in_closed_period);
		$sm1->assign('closed_period_reason', $closed_period_reason);
		
		
		//связанные оплаты
		$sm1->assign('binded_payments',$_bill->GetBindedPayments($editing_user['id'],$binded_payments_summ));
		$sm1->assign('binded_payments_summ',$binded_payments_summ);
		
		//авансовые оплаты
		$_pfg=new PayForBillGroup;
		$sm1->assign('avans_payments',$_pfg->GetAvans($editing_user['supplier_id'],$result['org_id'],$editing_user['id'],$avans, $raw_ids, $raw_inv, $editing_user['contract_id']));
		
		/*var_dump($avans); var_dump($_pfg->GetAvans($editing_user['supplier_id'],$result['org_id'],$editing_user['id'],$avans, $raw_ids, $raw_inv, $editing_user['contract_id']));*/
		
		$sm1->assign('avans_payments_summ',$avans);
		$sm1->assign('sum_by_bill',$_pfg->SumByBill($editing_user['id']));
		
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		//echo $avans;
		
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
			  }elseif($au->user_rights->CheckAccess('w',622)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',620)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm_price',$can_confirm_price);
		
		//возможность утвердить с ценами, большими чем в исх счете
		$sm1->assign('can_confirm_price_bigger',$au->user_rights->CheckAccess('w',865));
		
		
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
			  }elseif($au->user_rights->CheckAccess('w',623)){
				  //есть права + сам утвердил
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',621);
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
			
			if($au->user_rights->CheckAccess('w',267)){
			
			
			  $_pays=new PayGroup;
			  $_pays->prefix='_pay';
			  
			  $_pays->SetPagename('ed_bill_in.php');
			  
			  //$sm2=new SmartyAdm;
			  $dec2=new DBDecorator;
			  
			  
			  
			/*
			  
//блок фильтров статуса
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^pay_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^pay_status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_status_id_','',$k);
				
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^pay_pay_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^pay_pay_status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_pay_status_id_','',$k);
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
		if(isset($_GET['pay_statuses'])&&is_array($_GET['pay_statuses'])) $cou_stat=count($_GET['pay_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['pay_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^pay_pay_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^pay_pay_status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_pay_status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('pay_statuses[]',$v));
			  }
		  } 
		
			  
			  $dec2->AddEntry(new UriEntry('bill_id',$id));
			  
			  $_pays->SetAuthResult($result);
			  $pays=$_pays->ShowPos($id, 
			  	$editing_user['supplier_id'], 
				'pay/pays_list.html', 
				$dec2, 
				$au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), 
				$au->user_rights->CheckAccess('w',279),
				$au->user_rights->CheckAccess('w',277),  
				$au->user_rights->CheckAccess('w',96),
				true,
				false,
				$au->user_rights->CheckAccess('w',280),
				$au->user_rights->CheckAccess('w',278),
				NULL, 
				$au->user_rights->CheckAccess('w',480), 
				$au->user_rights->CheckAccess('w',481),  
				$total_cost, 
				$_bill->CalcPayed($editing_user['id']),
				$limited_supplier,
				false,
				$au->user_rights->CheckAccess('w',281)
			);
			  
			
			  
			  $sm->assign('pays',$pays); 	
			}else  $sm->assign('pays','У Вас недостаточно прав для просмотра оплат по счету.'); 
			
		}else $sm->assign('pays','В данном режиме просмотр оплат по входящему счету недоступен.<br />
 Пожалуйста, проставьте галочку "Утверждаю цену" и нажмите кнопку "Сохранить и остаться" на вкладке "Входящий счет" для получения возможности просмотра оплат.'); 	
		
		
		
		if($editing_user['is_confirmed_shipping']==1){
			
			//вкладка +-/
			if($au->user_rights->CheckAccess('w',629)){
				$pos_pm_given='';
			  	
				if($editing_user['status_id']==10){
				
				  $sm2=new SmartyAdm;
				  
				  $pos_pms=$bpg;
				  
				  $to_give_summ=0;
				  $given_summ=0;
				  foreach($pos_pms as $k=>$v){
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
					$given_summ+=$v['discount_given'];
				  }
				  
				  $sm2->assign('items',$pos_pms);
				  
			 	 $sm2->assign('to_give_summ',round($to_give_summ,2));
			  	 $sm2->assign('given_summ',round($given_summ,2));
				
				
				$pos_pm_given=$sm2->fetch('bills_in/positions_pm.html');
				}else{
					$pos_pm_given='Статус данного счета не позволяет вносить суммы выданных +/-. Для работы с выданными +/- необходимо, чтобы статус счета был "Выполнен".';
				}
			  $sm->assign('pm_folder',$pos_pm_given);
			  
			   	 
			}else  $sm->assign('pm_folder','У Вас недостаточно прав для просмотра выданных +/- по счету.'); 
			
			
			
			
			
		
			
			if($au->user_rights->CheckAccess('w',660)){
			  //вывод поступлений
			  $_acg=new AccInGroup;
			   $_acg->prefix='_acc';
			  $_acg->SetPagename('ed_bill_in.php');
			  
			  $dec2=new DBDecorator;
			  
			  
		/*
			  
			 
//блок фильтров статуса
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^'.$_acg->prefix.'acc_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^'.$_acg->prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$_acg->prefix.'acc_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_'.$_acg->prefix.'acc_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$dec2->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('acc_status_id_'.$v,1));
				$dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}
			*/
			
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$_acg->prefix.'acc_statuses'])&&is_array($_GET[$_acg->prefix.'acc_statuses'])) $cou_stat=count($_GET[$_acg->prefix.'acc_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$_acg->prefix.'acc_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_'.$_acg->prefix.'acc_status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry($_acg->prefix.'acc_statuses[]',$v));
			  }
		  } 
		
			  
			  
			  if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
				  $dec2->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
				  $dec2->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
			  }
			  
			  //  echo 'test';
			  $_acg->SetAuthResult($result);
			  $ships=$_acg->ShowPos($id, //0
			  'acc_in/acc_list.html', //1
			  $dec2, //2
			  $au->user_rights->CheckAccess('w',664)||$au->user_rights->CheckAccess('w',673), //3
			  $au->user_rights->CheckAccess('w',674), //4
			  $au->user_rights->CheckAccess('w',671), //5
			  $au->user_rights->CheckAccess('w',96), //6
			  true, //7
			  false,  //8
			  $au->user_rights->CheckAccess('w',675), //9
			  $limited_sector,//10
			  NULL, //11
			  $au->user_rights->CheckAccess('w',672), //12
			  $temp_accs, //13
			  $au->user_rights->CheckAccess('w',673), //14
			  false, //15
			  $limited_supplier,  //16
			  $au->user_rights->CheckAccess('w',930)
			  );
			 
			  
			  $sm->assign('accs',$ships); 	
			}else $sm->assign('accs','У Вас недостаточно прав для просмотра поступлений по счету.'); 	
			
			
		}else{
			$sm->assign('pm_folder','В данном режиме просмотр выданных +/- по входящему счету недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю приемку" и нажмите кнопку "Сохранить и остаться" на вкладке "Входящий счет" для получения возможности просмотра выданных +/-.'); 
			
		
			
			$sm->assign('ships','В данном режиме просмотр распоряжений на приемку по входящему счету недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю приемку" и нажмите кнопку "Сохранить и остаться" на вкладке "Входящий счет" для получения возможности просмотра распоряжений на приемку.'); 
			
			$sm->assign('accs','В данном режиме просмотр поступлений товара по входящему счету недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю приемку" и нажмите кнопку "Сохранить и остаться" на вкладке "Входящий счет" для получения возможности просмотра поступлений товара.');
			
		}
		
		if(!isset($_GET['do_show_log'])){
			$do_show_log=false;
		}else{
			$do_show_log=true;
		}
		$sm->assign('do_show_log',$do_show_log);
		
		if(!isset($_GET['do_show_shi'])){
			$do_show_shi=false;
		}else{
			$do_show_shi=true;
		}
		$sm->assign('do_show_shi',$do_show_shi);
		
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
			
		if(!isset($_GET['do_show_pm_folder'])){
			$do_show_pm_folder=false;
		}else{
			$do_show_pm_folder=true;
		}
		$sm->assign('do_show_pm_folder',$do_show_pm_folder);
		
		
		$sm->assign('has_ship', true); //($editing_user['is_confirmed_shipping']==1));
		$sm->assign('has_pays', true);//($editing_user['is_confirmed_price']==1));
		
		$sm->assign('has_acc', true);	
		
		$sm->assign('has_pm_folder', true);	
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',92)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',93)); 
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129)); 
		
		
		$user_form=$sm1->fetch('bills_in/bill_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',636)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(606,
607,
608,
609,
610,
611,
612,
613,
614,
615,
616,
617,
618,
619,
620,
621,
622,
623,
624,
625,
626,
627,
628,
629,
630,
631,
632,
633,
634,
635,
636,
637

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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_bill_in.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$content=$sm->fetch('bills_in/ed_bill_page'.$print_add.'.html');
	
	
	
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