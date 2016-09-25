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


 


require_once('classes/posdimitem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

 
require_once('classes/user_s_item.php');

require_once('classes/cash_in_item.php');

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/cash_in_notesgroup.php');
require_once('classes/cash_in_notesitem.php');
 
require_once('classes/cash_in_creator.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/cash_in_codeitem.php');
require_once('classes/cash_in_codegroup.php');
require_once('classes/billitem.php');


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование прихода наличных');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_pay=new CashInItem;

 
$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;
 

$_supgroup=new SuppliersGroup;

$lc=new CashInCreator;

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
	$object_id[]=885;
	break;
	case 1:
	$object_id[]=886;
	$object_id[]=898;
	break;
	case 2:
	$object_id[]=896;
	break;
	default:
	$object_id[]=885;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;


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
	if(!$au->user_rights->CheckAccess('w',898)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

	


if($action==0){
	
	
	
	
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
	$editing_user=$_pay->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
/*	$available_kind_id=array(2,3);*/
	if(!$au->user_rights->CheckAccess('w',834)){
		if(($editing_user['manager_id']!=$result['id'])&&($editing_user['responsible_user_id']!=$result['id'])
		/*&&(!in_array($editing_user['kind_id'], $available_kind_id))*/
		  ){
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
	$log->PutEntry($result['id'],'открыл карту прихода наличных',NULL,886, NULL, $editing_user['code'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту прихода наличных: версия для печати',NULL,898, NULL, $editing_user['code'],$id);
				
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',885)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	
	$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	 
	 
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	//$params['supplier_bdetails_id']=abs((int)$_POST['bdetails_id']);
	//$params['org_bdetails_id']=abs((int)$_POST['org_bdetails_id']);
	//$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	
	$params['value']=((float)str_replace(",",".",$_POST['value']));
	$params['manager_id']=abs((int)$result['id']);
	//$params['contract_id']=abs((int)$_POST['contract_id']);
	 
	
	//$params['notes']=SecStr($_POST['notes']);
	
	$params['is_confirmed']=0;
	$params['is_confirmed_given']=0;
	
	//$params['code']=SecStr($_POST['code']);
	
		
		$lc->ses->ClearOldSessions();
		
		$params['code']=$lc->GenLogin($result['id']);
	
	//$params['given_no']=SecStr($_POST['given_no']);
	
	
	$params['responsible_user_id']=abs((int)$_POST['responsible_user_id']); 
	
	$params['code_id']=abs((int)$_POST['code_id']);
	
/*	$params['month']=abs((int)$_POST['month']);
	$params['year']=abs((int)$_POST['year']);
	$params['quarter']=abs((int)$_POST['quarter']);*/
	
	if(isset($_POST['wo_supplier'])) $params['wo_supplier']=1; else $params['wo_supplier']=0;
	
	
	$code=$_pay->Add($params);
	
	
	
	
	
	
	
	//запись в журнале
	if($code>0){
		
		$log->PutEntry($result['id'],'создал приход наличных',NULL,885,NULL,NULL, $code);
		
		
		foreach($params as $k=>$v){
			
		 
				 
				
				$log->PutEntry($result['id'],'создал приход наличных',NULL,885, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}	
	}
	
	
	
	 
	
	//перенаправления
	if(isset($_POST['doNew'])){
		 header("Location: all_pay.php");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',886)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_cash_in.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: all_pay.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',836)){
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
	//	$params['contract_id']=abs((int)$_POST['contract_id']);
		
	//	$params['supplier_bdetails_id']=abs((int)$_POST['bdetails_id']);
		//$params['org_bdetails_id']=abs((int)$_POST['org_bdetails_id']);
	    
		if(strlen($_POST['given_pdate'])==10) $params['given_pdate']=DateFromdmY($_POST['given_pdate']);
		
		 
		$params['value']=((float)str_replace(",",".",$_POST['value']));
		
		$params['code_id']=SecStr($_POST['code_id']);
	
	 
		//$params['given_no']=SecStr($_POST['given_no']);
		
		$params['responsible_user_id']=abs((int)$_POST['responsible_user_id']); 
		
		
	  if(isset($_POST['wo_supplier'])) $params['wo_supplier']=1; else $params['wo_supplier']=0;
	
		
		
		$_pay->Edit($id, $params);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='given_pdate'){
					 
					$log->PutEntry($result['id'],'редактировал заданную дату',NULL,836,NULL,'дата: '.$_POST['given_pdate'],$id);
					continue;	
				}
				
				 
				$log->PutEntry($result['id'],'редактировал расход наличных',NULL,836, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
			
		}
		
		
		 
		
		
	}
	
	
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed_given']==0){
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',893))){
			  if((!isset($_POST['is_confirmed']))&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2))){
				  
				 
				  $_pay->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнение',NULL,893, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',892)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))){
				  
				  $_pay->Edit($id, array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение',NULL,892, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//утверждение выдачи суммы
	if($editing_user['is_confirmed']==1){
	  if($editing_user['is_confirmed_given']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',895))){
			 
			  if((!isset($_POST['is_confirmed_given'])) &&in_array($editing_user['status_id'], array(19))&&in_array($_POST['current_status_id'], array(19))){
				  $_pay->Edit($id,array('is_confirmed_given'=>0, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'снял утверждение прихода',NULL,895, NULL, NULL,$id);	
			  }
		  } 
	  }else{
		  //есть права
		  
		  if($au->user_rights->CheckAccess('w',894)){
			  
			  if(isset($_POST['is_confirmed_given'])&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2))){
				  
				  $_pay->Edit($id,array('is_confirmed_given'=>1, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'утвердил приход',NULL,894, NULL, NULL,$id);	
					  
			  }
		  } 
	  }
	}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		 header("Location: all_pay.php");
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',886)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_cash_in.php?action=1&id=".$id);
		die();	
		
	}else{
		 header("Location: all_pay.php");
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
		$_pcg=new CashInCodeGroup;
		$arr=$_pcg->GetItemsArr();
		$sm1->assign('codespos',$arr);
		
		
		
		//поставщики
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$dec=new DBDecorator;
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result); 
		$sm1->assign('suppliers',$supgroup);
		//print_r($supgroup);
		
		 
		 
		//сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}
		$sm1->assign('responsible_user_id_ids',$_ids);
		$sm1->assign('responsible_user_id_vals',$_vals);
		 
		 
		 
		//месяцы
		$_month_ids=array(0); $_month_names=array('-выберите-');
		$_month_ids[]=1; $_month_names[]='Январь';
		$_month_ids[]=2; $_month_names[]='Февраль';
		$_month_ids[]=3; $_month_names[]='Март';
		$_month_ids[]=4; $_month_names[]='Апрель';
		$_month_ids[]=5; $_month_names[]='Май';
		$_month_ids[]=6; $_month_names[]='Июнь';
		$_month_ids[]=7; $_month_names[]='Июль';
		$_month_ids[]=8; $_month_names[]='Август';
		$_month_ids[]=9; $_month_names[]='Сентябрь';
		$_month_ids[]=10; $_month_names[]='Октябрь';
		$_month_ids[]=11; $_month_names[]='Ноябрь';
		$_month_ids[]=12; $_month_names[]='Декабрь';
		$sm1->assign('_month_ids', $_month_ids); $sm1->assign('_month_names', $_month_names); 
		 
		//кварталы
		$_quart_ids=array(0); $_quart_names=array('-выберите-');
		$_quart_ids[]=1; $_quart_names[]='1 квартал';
		$_quart_ids[]=2; $_quart_names[]='2 квартал';
		$_quart_ids[]=3; $_quart_names[]='3 квартал';
		$_quart_ids[]=4; $_quart_names[]='4 квартал';
		$sm1->assign('_quart_ids', $_quart_ids); $sm1->assign('_quart_names', $_quart_names); 
		 
		//годы
		$_year_ids=array(0); $_year_names=array('-выберите-');
		//
		for($i=2013; $i<=date('Y'); $i++){
			$_year_ids[]=$i; $_year_names[]=$i;
		}
		$sm1->assign('_year_ids', $_year_ids); $sm1->assign('_year_names', $_year_names);  $sm1->assign('year', date('Y'));
		 
		 
		 
		
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		 
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',885)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',886)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$user_form=$sm1->fetch('cash_in/cash_in_create.html');
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
		/*$_bi=new BDetailsItem; $selected_bd_id=$editing_user['org_bdetails_id'];
		$bi=$_bi->GetItemById($editing_user['org_bdetails_id']);
		if($bi!==false){
				$editing_user['org_bdetails_id_string']=' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'';
				
				
				
			}
		//все реквизиты организации - для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($orgitem['id'],$selected_bd_id);
		$sm1->assign('orgpos',$arr);*/
		
		
		
		//коды оплаты
		$_pcg=new CashInCodeGroup;
		$arr=$_pcg->GetItemsArr(0, $editing_user['code_id']);
		$sm1->assign('codespos',$arr);
		$_pci=new CashInCodeItem;
		$pci=$_pci->GetItemById($editing_user['code_id']);
		if($pci!==false){
				$editing_user['code_id_string']=''.stripslashes($pci['code']).' '.stripslashes($pci['name'].'. '.$pci['descr'].'').'';
		}
		
		
		
		
		//месяцы
		$_month_ids=array(0); $_month_names=array('-выберите-');
		$_month_ids[]=1; $_month_names[]='Январь';
		$_month_ids[]=2; $_month_names[]='Февраль';
		$_month_ids[]=3; $_month_names[]='Март';
		$_month_ids[]=4; $_month_names[]='Апрель';
		$_month_ids[]=5; $_month_names[]='Май';
		$_month_ids[]=6; $_month_names[]='Июнь';
		$_month_ids[]=7; $_month_names[]='Июль';
		$_month_ids[]=8; $_month_names[]='Август';
		$_month_ids[]=9; $_month_names[]='Сентябрь';
		$_month_ids[]=10; $_month_names[]='Октябрь';
		$_month_ids[]=11; $_month_names[]='Ноябрь';
		$_month_ids[]=12; $_month_names[]='Декабрь';
		$sm1->assign('_month_ids', $_month_ids); $sm1->assign('_month_names', $_month_names); 
		$sm1->assign('month_shown', ($editing_user['code_id']>=8)&&($editing_user['code_id']<=12));
		 
		//кварталы
		$_quart_ids=array(0); $_quart_names=array('-выберите-');
		$_quart_ids[]=1; $_quart_names[]='1 квартал';
		$_quart_ids[]=2; $_quart_names[]='2 квартал';
		$_quart_ids[]=3; $_quart_names[]='3 квартал';
		$_quart_ids[]=4; $_quart_names[]='4 квартал';
		$sm1->assign('_quart_ids', $_quart_ids); $sm1->assign('_quart_names', $_quart_names); 
		$sm1->assign('quarter_shown', ($editing_user['code_id']==17)||($editing_user['code_id']==18)||($editing_user['code_id']==62)  );  
		 
		//годы
		$_year_ids=array(0); $_year_names=array('-выберите-');
		//
		for($i=2013; $i<=date('Y'); $i++){
			$_year_ids[]=$i; $_year_names[]=$i;
		}
		$sm1->assign('_year_ids', $_year_ids); $sm1->assign('_year_names', $_year_names); 
		$sm1->assign('year_shown', (($editing_user['code_id']>=8)&&($editing_user['code_id']<=12))||($editing_user['code_id']==17)||($editing_user['code_id']==18)||($editing_user['code_id']==62));  
		 
		
		
		
		
		
		
		
		
		
		 
		
		
		//договор оплаты
		/*$_supcontract=new SupContractItem;
		$supcontract=$_supcontract->GetItemById($editing_user['contract_id']);
		//$editing_user['org_bdetails_id_string']=$supcontract['id'];
		$editing_user['contract_no']=$supcontract['contract_no'];
		$editing_user['contract_pdate']=$supcontract['contract_pdate'];
		
		
		//все договора поставщика
		$_scg=new SupContractGroup;
		$scg=$_scg->GetItemsByIdArr($supplier['id'],$bill['contract_id'],1);
		//print_r($scg);
		$sm1->assign('pos2',$scg);
		
		
		if($editing_user['given_pdate']>0) $editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
		else $editing_user['given_pdate']='-';
		*/
		
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
		
		
		$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$sm1->assign('suppliers',$supgroup);
		
		
		//банк. реквизиты
		/*$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetItemById($editing_user['supplier_bdetails_id']);
		$editing_user['bdetails_id_string']='р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city'];
		
		//все реквизиты п-ка для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($editing_user['supplier_id'],$editing_user['supplier_bdetails_id']);
		//print_r($arr);
		$sm1->assign('pos',$arr);*/
		
		
		 //сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr($editing_user['responsible_user_id'], 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}
		$sm1->assign('responsible_user_id_ids',$_ids);
		$sm1->assign('responsible_user_id_vals',$_vals);
		
		
		//водитель
	   
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		$editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
		
		
		
		//блок аннулирования
			$editing_user['can_annul']=$_pay->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',896);
			if(!$au->user_rights->CheckAccess('w',896)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
		
		$editing_user['can_restore']=$_pay->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',897);
			if(!$au->user_rights->CheckAccess('w',897)) $reason='недостаточно прав для данной операции';
		
		
		
		//Примечания
		$rg=new CashInNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0, ($editing_user['is_confirmed']==0),$au->user_rights->CheckAccess('w',899), $au->user_rights->CheckAccess('w',899), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',887)/*&&($editing_user['is_confirmed']==0)*/);
		
		
		
		
		$sm1->assign('ship',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
	
		
		
		
		//блок утверждения затрат!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_given']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',893)){
				  //есть права 
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',892)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		
		//блок утв. выдачи суммы
		if(($editing_user['is_confirmed_given']==1)&&($editing_user['user_confirm_given_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_given_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirmed_given_pdate']);
			
			$sm1->assign('is_confirmed_given_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_confirmed_given']==1){
			  if($au->user_rights->CheckAccess('w',895)){
				  //есть права  
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',894);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_given',$can_confirm_shipping);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',898)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',885)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',886)); 
		
		$sm1->assign('can_percent',$au->user_rights->CheckAccess('w',891)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		//выбор формы для отображения
		 $formname='cash_in/cash_edit'.$print_add.'.html';
	 
		
		$user_form=$sm1->fetch($formname);
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',900)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(883,

884,
885,
886,
887,
888,
889,
890,
891,
892,
893,
894,
895,
896,
897,
898,
899,
900

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
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_cash.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('cash_in/ed_cash_page'.$print_add.'.html');
	
	
	
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