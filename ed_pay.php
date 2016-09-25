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

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/bill_in_item.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

require_once('classes/payitem.php');

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/paynotesgroup.php');
require_once('classes/paynotesitem.php');

require_once('classes/payforbillgroup.php');
require_once('classes/payforbillitem.php');
require_once('classes/invcalcitem.php');


require_once('classes/paycreator.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/paycodeitem.php');
require_once('classes/paycodegroup.php');

require_once('classes/supplier_to_user.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование исходящей оплаты');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_pay=new PayItem;

$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PosItem;
$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;
$_posgroupgroup=new PosGroupGroup;

$_supgroup=new SuppliersGroup;

$lc=new PayCreator;

$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

$object_id=array();
switch($action){
	case 0:
	$object_id[]=268;
	break;
	case 1:
	$object_id[]=272;
	$object_id[]=281;
	break;
	case 2:
	$object_id[]=279;
	break;
	default:
	$object_id[]=268;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=14;


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

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',281)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

	



if($action==0){
	if(!isset($_GET['bill_id'])){
		if(!isset($_POST['bill_id'])){
			/*header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();*/
			$bill_id=0;
		}else $bill_id=abs((int)$_POST['bill_id']);	
	}else $bill_id=abs((int)$_GET['bill_id']);
	
	//проверка наличия s4eta
	$bill=$_bill->GetItemById($bill_id);
	if($bill===false){
		/*header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();*/
	}
	
	if(!isset($_GET['supplier_id'])){
		if(!isset($_POST['supplier_id'])){
			/*header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();*/
			$supplier_id=0;
		}else $supplier_id=abs((int)$_POST['supplier_id']);	
	}else $supplier_id=abs((int)$_GET['supplier_id']);
	
	
	
	
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
	$editing_user=$_pay->GetItemByFields(array('id'=>$id,'is_incoming'=>0));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	$bill_id=$editing_user['bill_id'];
	$supplier_id=$editing_user['supplier_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
	$supplier=$_supplier->GetItemById($editing_user['supplier_id']);
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);





	if($limited_supplier!==NULL){
		/**/
		
		$_bpg=new PayForBillGroup;
		$_bpg->SetIdName('payment_id');
		$bpg=$_bpg->GetItemsByIdForPage($editing_user['id']);
		/*echo '<pre>';
		print_r($bpg);
		echo '</pre>';*/
		//$_in_bill_ids=array();
		$binded_supplier_ids=array();
		$_bill_in=new BillInItem;
		foreach($bpg as $k=>$v){
			// $_in_bill_ids[]=$v['id'];
			$bill_in=$_bill_in->getitembyid($v['id']);
			if(!in_array($bill_in['supplier_id'], $binded_supplier_ids)) $binded_supplier_ids[]=$bill_in['supplier_id'];
		}
		//найти к-тов по этим счетам
		//если наш к-т среди них - то показывать оплату!
		
		$was_in=false;
		foreach($limited_supplier as $k=>$sup_id){
			foreach($binded_supplier_ids as $k=>$binded_id) if($sup_id==$binded_id) $was_in=$was_in||true;	
		}
		if(!$was_in){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
	}
	
	//если нет прав на просмотр всех исх оплат - фильтровать по коду А и получателлю
	if(!$au->user_rights->CheckAccess('w',877)){
			
		$was_in=false;
		
		if($editing_user['code_id']==59) $was_in=$was_in||true;
		if($editing_user['inner_user_id']==$result['id'])  $was_in=$was_in||true;
		
		if(!$was_in){
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
	$log->PutEntry($result['id'],'открыл карту исходящей оплаты',NULL,272, NULL, $editing_user['code'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту исходящей оплаты: версия для печати',NULL,281, NULL, $editing_user['code'],$id);
				
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',268)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	$params['bill_id']=abs((int)$_POST['bill_id']);
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	$params['supplier_bdetails_id']=abs((int)$_POST['bdetails_id']);
	$params['org_bdetails_id']=abs((int)$_POST['org_bdetails_id']);
	$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	
	$params['value']=((float)str_replace(",",".",$_POST['value']));
	$params['manager_id']=abs((int)$result['id']);
	$params['contract_id']=abs((int)$_POST['contract_id']);
	$params['is_incoming']=0;
	
	//$params['notes']=SecStr($_POST['notes']);
	
	$params['is_confirmed']=0;
	
//	$params['code']=SecStr($_POST['code']);
	$lc->ses->ClearOldSessions();
		
		$params['code']=$lc->GenLogin($result['id']);
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	
	if(isset($_POST['pay_for_dogovor'])) $params['pay_for_dogovor']=1;
	else $params['pay_for_dogovor']=0;
	
	if(isset($_POST['pay_for_bill'])) $params['pay_for_bill']=1;
	else $params['pay_for_bill']=0;
	
	$params['code_id']=SecStr($_POST['code_id']);
	
	
	if(isset($_POST['is_return'])) $params['is_return']=1;
	else $params['is_return']=0;
	
	
	if(isset($_POST['is_inner_pay'])) $params['is_inner_pay']=1;
	else $params['is_inner_pay']=0;
	
	$params['inner_user_id']=abs((int)$_POST['inner_user_id']);
	
	
	$code=$_pay->Add($params);
	
	
	
	
	
	
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал исходящую оплату',NULL,613,NULL,NULL,$bill_id);	
		
		$log->PutEntry($result['id'],'создал исходящую оплату',NULL,268,NULL,NULL,$code);
		
		
		
		foreach($params as $k=>$v){
			
		 
				if($k=='supplier_id'){
					$_si=new SupplierItem; $_opf=new OpfItem;
					$si=$_si->GetItemById($v); $opf=$_opf->GetItemById($si['opf_id']);
					
					
					$log->PutEntry($result['id'],'создал исходящую оплату',NULL,268, NULL, SecStr('установлен контрагент '.$si['code'].' '.$opf['name'].' '.$si['full_name']),$code);			
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'создал исходящую оплату',NULL,268, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
	}
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		if($bill_id!=0) header("Location: ed_bill_in.php?action=1&id=".$bill_id.'&do_show_pay=1');
		else header("Location: all_pay.php");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',272)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_pay.php?action=1&id=".$code);
		die();	
		
	}else{
		if($bill_id!=0) header("Location: ed_bill_in.php?action=1&id=".$bill_id.'&do_show_pay=1');
		else header("Location: all_pay.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',272)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//редактирование возможно, если is_confirmed==0
	//if($editing_user['is_confirmed']==0){
		
		
	$condition=true;
	
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	
	if($condition){	
		$params=array();
		//обычная загрузка прочих параметров
		
		$params['supplier_id']=abs((int)$_POST['supplier_id']);
		$params['contract_id']=abs((int)$_POST['contract_id']);
		
		$params['supplier_bdetails_id']=abs((int)$_POST['bdetails_id']);
		$params['org_bdetails_id']=abs((int)$_POST['org_bdetails_id']);
	    
		if(strlen($_POST['given_pdate'])==10) $params['given_pdate']=DateFromdmY($_POST['given_pdate']);
		
		//$params['notes']=SecStr($_POST['notes']);
		
		$params['value']=((float)str_replace(",",".",$_POST['value']));
		
		$params['code_id']=SecStr($_POST['code_id']);
	
		
		if(isset($_POST['pay_for_dogovor'])) $params['pay_for_dogovor']=1;
		else $params['pay_for_dogovor']=0;
		
		if(isset($_POST['pay_for_bill'])) $params['pay_for_bill']=1;
		else $params['pay_for_bill']=0;
		
		$params['given_no']=SecStr($_POST['given_no']);
		
		if(isset($_POST['is_return'])) $params['is_return']=1;
		else $params['is_return']=0;
		
		
		if(isset($_POST['is_inner_pay'])) $params['is_inner_pay']=1;
		else $params['is_inner_pay']=0;
		
		$params['inner_user_id']=abs((int)$_POST['inner_user_id']);
	
		
		$_pay->Edit($id, $params);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='given_pdate'){
					$log->PutEntry($result['id'],'редактировал заданную дату исходящей оплаты',NULL,613,NULL,'дата: '.$_POST['given_pdate'],$bill_id);
					
					$log->PutEntry($result['id'],'редактировал заданную дату',NULL,272,NULL,'дата: '.$_POST['given_pdate'],$id);
					continue;	
				}
				
				$log->PutEntry($result['id'],'редактировал исходящую оплату',NULL,613, NULL, 'в поле '.$k.' установлено значение '.$v,$bill_id);
				
				$log->PutEntry($result['id'],'редактировал исходящую оплату',NULL,272, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
			
		}
		
		
		if($au->user_rights->CheckAccess('w',270)){
		  $positions=array();
		  
		  $_pos=new BillItem;
		  $_invc=new InvCalcItem;
	  
		  $_kpi=new PayForBillItem;
		  foreach($_POST as $k=>$v){
			  
			  //if(eregi("^new_position_id_([0-9]+)",$k)){
				if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
			 		$hash=eregi_replace("^new_hash_","",$k);
				  	
					$pos_id=abs((int)$_POST['new_position_id_'.$hash]);
					$kind=abs((int)$_POST['new_kind_'.$hash]);
					
				
				  if($kind==0){
					$positions[]=array(
						'payment_id'=>$id,
						'bill_id'=>$pos_id,
						'kind'=>$kind,
						'value'=>((float)str_replace(",",".",$_POST['new_value_'.$hash]))
					);
				  }elseif($kind==1){
					$positions[]=array(
						'payment_id'=>$id,
						'invcalc_id'=>$pos_id,
						'kind'=>$kind,
						'value'=>((float)str_replace(",",".",$_POST['new_value_'.$hash]))
					); 
				  }
				  
			  }
		  }
		  //print_r($_POST);
		//  print_r($positions);
		  	//die();
		  //внесем позиции
		  $log_entries=$_pay->AddPositions($id,$positions);
		  
		  //
		  //
		  
		  //запишем в журнал
		  foreach($log_entries as $k=>$v){
			  if($v['action']==0){
				  if($v['kind']==0){
					 $pos=$_pos->GetItemByFields(array('code'=>$v['code'])); 
					 if($pos!==false){
						$descr=$pos['code'].'<br /> сумма '.$v['value'].' руб.<br />';
						$log->PutEntry($result['id'],'добавил счет к исходящей оплате', NULL, 613,NULL,$descr,$pos['id']);	
					
						$log->PutEntry($result['id'],'добавил счет к исходящей оплате', NULL, 270,NULL,$descr,$id);	
					 }
				  }else{
					 $pos=$_invc->GetItemByFields(array('code'=>$v['code'])); 
					 if($pos!==false) {
						 $descr=$pos['code'].'<br /> сумма '.$v['value'].' руб.<br />';
						$log->PutEntry($result['id'],'добавил инвентаризационный акт к оплате', NULL, 452,NULL,$descr,$pos['id']);	
					
						$log->PutEntry($result['id'],'добавил инвентаризационный акт к оплате', NULL, 270,NULL,$descr,$id);	  
					 }
				  }
				  
			  }elseif($v['action']==2){
				   if($v['kind']==0){
					 $pos=$_pos->GetItemByFields(array('code'=>$v['code'])); 
					 if($pos!==false){
						$descr=$pos['code'].'<br /> сумма '.$v['value'].' руб.<br />';
						$log->PutEntry($result['id'],'удалил счет из исходящей оплаты', NULL, 613,NULL,$descr,$pos['id']);	
					
						$log->PutEntry($result['id'],'удалил счет из исходящей оплаты', NULL, 270,NULL,$descr,$id);	
					 }
				  }else{
					 $pos=$_invc->GetItemByFields(array('code'=>$v['code'])); 
					 if($pos!==false) {
						 $descr=$pos['code'].'<br /> сумма '.$v['value'].' руб.<br />';
						$log->PutEntry($result['id'],'удалил инвентаризационный акт из оплаты', NULL, 452,NULL,$descr,$pos['id']);	
					
						$log->PutEntry($result['id'],'удалил инвентаризационный акт из оплаты', NULL, 270,NULL,$descr,$id);	  
					 }
				  }
			  }
		  }	//
		}
		
		
	}
	
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed']==1){
		//есть права: либо есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',278))||$au->user_rights->CheckAccess('w',96)){
			if(!isset($_POST['is_confirmed'])&&($editing_user['status_id']==15)&&($_POST['current_status_id']==15)){
				$_pay->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение исходящей оплаты',NULL,613, NULL, NULL,$bill_id);	
				
				$log->PutEntry($result['id'],'снял утверждение исходящей оплаты',NULL,278, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',277)||$au->user_rights->CheckAccess('w',96)){
			if(isset($_POST['is_confirmed'])&&($editing_user['status_id']==14)&&($_POST['current_status_id']==14)){
				$_pay->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'],'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'утвердил исходящую оплату',NULL,613, NULL, NULL,$bill_id);	
				
				$log->PutEntry($result['id'],'утвердил исходящую оплату',NULL,277, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		if($bill_id>0) header("Location: ed_bill_in.php?action=1&id=".$bill_id.'&do_show_pay=1');
		else header("Location: all_pay.php");
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',272)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_pay.php?action=1&id=".$id);
		die();	
		
	}else{
		if($bill_id>0) header("Location: ed_bill_in.php?action=1&id=".$bill_id.'&do_show_pay=1');
		else header("Location: all_pay.php");
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


$_menu_id=35;
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		
		//организация
		
		$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		//основные реквизиты по организации
		$_bi=new BDetailsItem; $selected_bd_id=0;
		$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$orgitem['id']));
		if($bi!==false){
				$sm1->assign('org_bdetails_id_string',' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'');
				
				
				$sm1->assign('org_bdetails_id',$bi['id']);
				 $selected_bd_id=$bi['id'];
				
			}
		//все реквизиты организации - для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($orgitem['id'],$selected_bd_id);
		$sm1->assign('orgpos',$arr);
		
		$sm1->assign('bill_id',$bill_id);
		
		
		
		//коды оплаты
		$_pcg=new PayCodeGroup;
		$arr=$_pcg->GetItemsArr();
		$sm1->assign('codespos',$arr);
		
		
		
		//поставщики
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$_supgroup->GetItemsForPay('pay/suppliers_list.html', new DBDecorator, false, $supgroup,$result);
		
		$sm1->assign('suppliers',$supgroup);
		//print_r($supgroup);
		
		if($bill!==false){
			//поставщик
			
			$supplier=$_supplier->GetItemById($bill['supplier_id']);
			$_supopf=new OpfItem;
			$supopf=$_supopf->GetItemById($supplier['opf_id']);
			
			$sm1->assign('supplier_id_string' ,$supopf['name'].' '.$supplier['full_name']);
			$sm1->assign('supplier_id' ,$supplier['id']);
			
			//реквизиты по умолчанию
			
			$_bi=new BDetailsItem;
			$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$supplier['id']));
			if($bi!==false){
				$sm1->assign('bdetails_id_string',' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'');
				
				
				$sm1->assign('bdetails_id',$bi['id']);
				
			}
			
			//все реквизиты п-ка для выбора
			$_bd=new BDetailsGroup;
			$arr=$_bd->GetItemsByIdArr($supplier['id'],$bi['id']);
			//print_r($arr);
			$sm1->assign('pos',$arr);
			
			
			//договор по умолчанию
			$_supcontract=new SupContractItem;
			$supcontract=$_supcontract->GetItemById($bill['contract_id']);
			$sm1->assign('contract_id',$supcontract['id']);
			$sm1->assign('contract_no',$supcontract['contract_no']);
			$sm1->assign('contract_pdate',$supcontract['contract_pdate']);
			
			
			//все договора поставщика
			$_scg=new SupContractGroup;
			$scg=$_scg->GetItemsByIdArr($supplier['id'],$bill['contract_id']/*,1*/);
			//print_r($scg);
			$sm1->assign('pos2',$scg);
			
			
			
		}else{
			$sm1->assign('supplier_id',$supplier_id);	
		}
		
		
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		$sm1->assign('org_id', $result['org_id']);
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',269)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',268)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',272)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$sm1->assign('current_user_id', $result['id']);
		
		 //сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}
		$sm1->assign('inner_user_id_ids',$_ids);
		$sm1->assign('inner_user_id_vals',$_vals);
		
		
		
		$user_form=$sm1->fetch('pay/pay_create.html');
	}elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		
		
		
		$sm1=new SmartyAdm;
		
		//организация
		
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($orgitem['opf_id']); 
		$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		//реквизиты по организации
		$_bi=new BDetailsItem; $selected_bd_id=$editing_user['org_bdetails_id'];
		$bi=$_bi->GetItemById($editing_user['org_bdetails_id']);
		if($bi!==false){
				$editing_user['org_bdetails_id_string']=' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'';
				
				
				
			}
		//все реквизиты организации - для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($orgitem['id'],$selected_bd_id);
		$sm1->assign('orgpos',$arr);
		
		
		
		//коды оплаты
		$_pcg=new PayCodeGroup;
		$arr=$_pcg->GetItemsArr(0, $editing_user['code_id']);
		$sm1->assign('codespos',$arr);
		$_pci=new PayCodeItem;
		$pci=$_pci->GetItemById($editing_user['code_id']);
		if($pci!==false){
				$editing_user['code_id_string']=''.stripslashes($pci['code']).' '.stripslashes($pci['name'].'. '.$pci['descr'].'').'';
		}
		
		
		
		
		$sm1->assign('bill_id',$bill_id);
		
		
		
		//договор оплаты
		$_supcontract=new SupContractItem;
		$supcontract=$_supcontract->GetItemById($editing_user['contract_id']);
		//$editing_user['org_bdetails_id_string']=$supcontract['id'];
		$editing_user['contract_no']=$supcontract['contract_no'];
		$editing_user['contract_pdate']=$supcontract['contract_pdate'];
		
		
		//все договора поставщика
		$_scg=new SupContractGroup;
		$scg=$_scg->GetItemsByIdArr($supplier['id'],$bill['contract_id']/*,1*/);
		//print_r($scg);
		$sm1->assign('pos2',$scg);
		
		
		if($editing_user['given_pdate']>0) $editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
		else $editing_user['given_pdate']='-';
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		
		
	
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$sopfitem=$_opfitem->getItemById($si['opf_id']); 
		
		
		$editing_user['supplier_id_string']= $sopfitem['name'].' '.$si['full_name'];
		
		
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$_supgroup->GetItemsForPay('pay/suppliers_list.html', new DBDecorator, false, $supgroup,$result);
		$sm1->assign('suppliers',$supgroup);
		
		
		//банк. реквизиты
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetItemById($editing_user['supplier_bdetails_id']);
		$editing_user['bdetails_id_string']='р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city'];
		
		//все реквизиты п-ка для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($editing_user['supplier_id'],$editing_user['supplier_bdetails_id']);
		//print_r($arr);
		$sm1->assign('pos',$arr);
		
		
		
		//сделки - получить позиции
		//позиции!
		//$sm1->assign('has_positions',true);
		$_bpg=new PayForBillGroup;
		$_bpg->SetIdName('payment_id');
		$bpg=$_bpg->GetItemsByIdForPage($editing_user['id']);
		/*echo '<pre>';
		print_r($bpg);
		echo '</pre>';*/
		$sm1->assign('positions',$bpg);
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		
		//блок аннулирования
			$editing_user['can_annul']=$_pay->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',279);
			if(!$au->user_rights->CheckAccess('w',279)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
		
		$editing_user['can_restore']=$_pay->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',280);
			if(!$au->user_rights->CheckAccess('w',280)) $reason='недостаточно прав для данной операции';
		
		
		
		//Примечания
		$rg=new PaymentNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,false,false,false,$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',273)/*&&($editing_user['is_confirmed']==0)*/);
		
		
		
		$sm1->assign('org_id', $result['org_id']);
		
		
		$sm1->assign('ship',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		
		$sm1->assign('current_user_id', $result['id']);
		
		 //сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}
		$sm1->assign('inner_user_id_ids',$_ids);
		$sm1->assign('inner_user_id_vals',$_vals);
		
		
		
		
		
		
		
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed']==1){
			if($au->user_rights->CheckAccess('w',96)){
				//полные права
				$can_confirm_price=true;	
			}elseif($au->user_rights->CheckAccess('w',278)){
				//есть права + сам утвердил
				$can_confirm_price=true;	
			}else{
				$can_confirm_price=false;
			}
		}else{
			//95
			$can_confirm_price=$au->user_rights->CheckAccess('w',277)&&($editing_user['status_id']==14);
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',281)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',268)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',272)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		$user_form=$sm1->fetch('pay/pay_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',527)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(266, 267,
268,
269,
270,
271,
272,
273,
274,
275,
276,
277,
278,
279,
280,
281)));
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_pay.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('pay/ed_pay_page'.$print_add.'.html');
	
	
	
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