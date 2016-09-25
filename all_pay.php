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
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/billfileitem.php');
require_once('classes/billfilegroup.php');

require_once('classes/supplieritem.php');

require_once('classes/billitem.php');

require_once('classes/paygroup.php');
require_once('classes/pay_in_group.php');

require_once('classes/cashgroup.php');

require_once('classes/cash_in_group.php');

require_once('classes/supplier_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр оплат');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['pay_from'])){
		$from=abs((int)$_SESSION['pay_from']);
	}else $from=0;
	$_SESSION['pay_from']=$from;

$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',266)&&!$au->user_rights->CheckAccess('w',677)&&!$au->user_rights->CheckAccess('w',833)&&!$au->user_rights->CheckAccess('w',883)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//журнал событий 
$log=new ActionLog;
if($au->user_rights->CheckAccess('w',266)) $log->PutEntry($result['id'],'открыл раздел Входящие оплаты',NULL,266);
if($au->user_rights->CheckAccess('w',677)) $log->PutEntry($result['id'],'открыл раздел Исходящие оплаты',NULL,677);
if($au->user_rights->CheckAccess('w',833)) $log->PutEntry($result['id'],'открыл раздел Расход наличных',NULL,833);

if($au->user_rights->CheckAccess('w',833)) $log->PutEntry($result['id'],'открыл раздел Приход наличных',NULL,883);



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=35;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	
/*********** ВХОД ОПЛАТЫ ****************************************************************************/	
	$decorator=new DBDecorator;
	
	
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	$_pp=new PayInGroup;
	$_pp->prefix='_in';
	$prefix=$_pp->prefix;
	
	
	 
	
	//блок фильтров статуса
	 
	
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^pay_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^pay_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_'.$prefix.'status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
	
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}

	if(isset($_GET['given_no'.$prefix])&&(strlen($_GET['given_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no'.$prefix]));
	}
	
	
	if(isset($_GET['value'.$prefix])&&(strlen($_GET['value'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.value',SecStr($_GET['value'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('value',$_GET['value'.$prefix]));
	}
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['given_pdate1'.$prefix];
		 $_given_pdate1= DateFromdmY($_GET['given_pdate1'.$prefix]);
	}
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['given_pdate2'.$prefix];
		  $_given_pdate2= DateFromdmY($_GET['given_pdate2'.$prefix]);
	}
	
	if(isset($_GET['given_pdate1'.$prefix])&&isset($_GET['given_pdate2'.$prefix])&&($_GET['given_pdate2'.$prefix]!="")&&($_GET['given_pdate2'.$prefix]!="-")&&($_GET['given_pdate1'.$prefix]!="")&&($_GET['given_pdate1'.$prefix]!="-")){
		
		$decorator->AddEntry(new UriEntry('given_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
		$decorator->AddEntry(new SqlEntry('p.given_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
				$decorator->AddEntry(new UriEntry('given_pdate1',''));
			$decorator->AddEntry(new UriEntry('given_pdate2',''));
	}	
	
	
	//$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	//сортировка
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=12;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.given_no',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.given_no',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.value',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.value',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
			
		break;
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	//var_dump( $sortmode);
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	$decorator->AddEntry(new UriEntry('sortmode'.$prefix,$sortmode));
	
	
	$_pp->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('pay_in/all_pays_list.html', $decorator, 
	$au->user_rights->CheckAccess('w',683)||$au->user_rights->CheckAccess('w',693),  
	$au->user_rights->CheckAccess('w',691),$from, $to_page,
	$au->user_rights->CheckAccess('w',689),  
	$au->user_rights->CheckAccess('w',96),true,false, 
	$au->user_rights->CheckAccess('w',692), 
	$au->user_rights->CheckAccess('w',690),
	$au->user_rights->CheckAccess('w',693),
	$limited_supplier
	);
			
	$sm->assign('log2',$ships); 
	
	$sm->assign('has_incoming_pays', $au->user_rights->CheckAccess('w',677));
	
	
	
	
	
	
	
	
/*********** ИСХ ОПЛАТЫ ****************************************************************************/		
		
		
	
	
	
	$decorator=new DBDecorator;
	
	
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	//если нет прав на просмотр всех исх оплат - фильтровать по коду А и получателлю
	if(!$au->user_rights->CheckAccess('w',877)){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		 
		$decorator->AddEntry(new SqlEntry('p.inner_user_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.code_id',59, SqlEntry::E));
		 
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));	
	}
	
	
	
	
	
	//блок фильтров статуса
	
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^pay_out_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^pay_out_status_id_', $k)) $status_ids[]=(int)eregi_replace('^pay_out_status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('statuses[]',$v));
			  }
		  } 
		
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}

	
	
	if(isset($_GET['code_code'])&&(strlen($_GET['code_code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code_id',SecStr($_GET['code_code']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code_code',$_GET['code_code']));
	}
	
	
	
	
	if(isset($_GET['value'])&&(strlen($_GET['value'])>0)){
		$decorator->AddEntry(new SqlEntry('p.value',SecStr($_GET['value']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('value',$_GET['value']));
	}

	

	if(isset($_GET['given_no'])&&(strlen($_GET['given_no'])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no']));
	}
	
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		//мб фильтр по сотруднику
		
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('inu.name_s',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('inu.login',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
	}
	
	
	
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	
	
	if(!isset($_GET['given_pdate1'])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['given_pdate1'];
		 $_given_pdate1= DateFromdmY($_GET['given_pdate1']);
	}
	
	
	
	if(!isset($_GET['given_pdate2'])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['given_pdate2'];
		  $_given_pdate2= DateFromdmY($_GET['given_pdate2']);
	}
	
	if(isset($_GET['given_pdate1'])&&isset($_GET['given_pdate2'])&&($_GET['given_pdate2']!="")&&($_GET['given_pdate2']!="-")&&($_GET['given_pdate1']!="")&&($_GET['given_pdate1']!="-")){
		
		$decorator->AddEntry(new UriEntry('given_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
		$decorator->AddEntry(new SqlEntry('p.given_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
				$decorator->AddEntry(new UriEntry('given_pdate1',''));
			$decorator->AddEntry(new UriEntry('given_pdate2',''));
	}	
	
	
	
	//сортировка
	if(!isset($_GET['sortmode'])){
		$sortmode=12;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.given_no',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.given_no',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.value',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.value',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('inu_name_s',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('inu_name_s',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
			
		break;
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('code_code',SqlOrdEntry::DESC));
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('code_code',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	//var_dump( $sortmode);
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	 
	
	
	
	
	$_pp=new PayGroup;
	$_pp->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('pay/all_pays_list.html', $decorator, $au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281),  $au->user_rights->CheckAccess('w',279),$from, $to_page,$au->user_rights->CheckAccess('w',277),  $au->user_rights->CheckAccess('w',96),true,false, $au->user_rights->CheckAccess('w',280), $au->user_rights->CheckAccess('w',278), 
	$au->user_rights->CheckAccess('w',281),
	$limited_supplier,
	$au->user_rights->CheckAccess('w',877)
	);
			
	$sm->assign('log',$ships); 	
	$sm->assign('has_pays', $au->user_rights->CheckAccess('w',266));
	
	




//***************************************** РАСХОД НАЛИЧНЫХ *********************************************************
	$decorator=new DBDecorator;
	
	
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	$_pp=new CashGroup;
	$_pp->prefix='_cash';
	$prefix=$_pp->prefix;
	
	//блок фильтров статуса
	
	$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^cash_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^cash_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^cash_'.$prefix.'status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
	
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	
	
	if(isset($_GET['code_code'.$prefix])&&(strlen($_GET['code_code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code_id',SecStr($_GET['code_code'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code_code',$_GET['code_code'.$prefix]));
	}
	

	if(isset($_GET['given_no'.$prefix])&&(strlen($_GET['given_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no'.$prefix]));
	}
	
	
	if(isset($_GET['value'.$prefix])&&(strlen($_GET['value'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.value',SecStr($_GET['value'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('value',$_GET['value'.$prefix]));
	}
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['ru_name'.$prefix])&&(strlen($_GET['ru_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('ru.name_s',SecStr($_GET['ru_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('ru_name',$_GET['ru_name'.$prefix]));
	}
	
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	//если нет прав на просмотр всех расходов - просматривать только свои
	if(!$au->user_rights->CheckAccess('w',834)&&$au->user_rights->CheckAccess('w',875)){
		//есть права на просмотр доставок, экспедированией всех сотрудников
//		$decorator->AddEntry(new SqlEntry('p.code_id',SecStr($_GET['code_code'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('p.kind_id', NULL, SqlEntry::IN_VALUES, NULL,array(2,3)));		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		
		$decorator->AddEntry(new SqlEntry('mn.id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.responsible_user_id',$result['id'], SqlEntry::E));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		//echo 'zz';
	}elseif(!$au->user_rights->CheckAccess('w',834)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('mn.id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.responsible_user_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	} 
	
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['given_pdate1'.$prefix];
		 $_given_pdate1= DateFromdmY($_GET['given_pdate1'.$prefix]);
	}
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['given_pdate2'.$prefix];
		  $_given_pdate2= DateFromdmY($_GET['given_pdate2'.$prefix]);
	}
	
	if(isset($_GET['given_pdate1'.$prefix])&&isset($_GET['given_pdate2'.$prefix])&&($_GET['given_pdate2'.$prefix]!="")&&($_GET['given_pdate2'.$prefix]!="-")&&($_GET['given_pdate1'.$prefix]!="")&&($_GET['given_pdate1'.$prefix]!="-")){
		
		$decorator->AddEntry(new UriEntry('given_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
		$decorator->AddEntry(new SqlEntry('p.given_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
				$decorator->AddEntry(new UriEntry('given_pdate1',''));
			$decorator->AddEntry(new UriEntry('given_pdate2',''));
	}	
	
	
	//сортировка
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=2;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('code_code',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('code_code',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('kind_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.value',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.value',SqlOrdEntry::ASC));
			
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('ru_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('ru_name',SqlOrdEntry::ASC));
			
		break;
		 
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	//var_dump( $sortmode);
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	$decorator->AddEntry(new UriEntry('sortmode'.$prefix,$sortmode));
	
	
	$_pp->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('cash/all_cash_list.html', $decorator, 
	
	$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
	$au->user_rights->CheckAccess('w',846) ,$from, $to_page,
	$au->user_rights->CheckAccess('w',842) ,  
	false ,true,false, 
	$au->user_rights->CheckAccess('w',847) ,  
	$au->user_rights->CheckAccess('w',843),
	
	$au->user_rights->CheckAccess('w',835),
	$au->user_rights->CheckAccess('w',844),
	$au->user_rights->CheckAccess('w',845),
	$some,
	$au->user_rights->CheckAccess('w',851),
	$au->user_rights->CheckAccess('w',848)
	);
			
	$sm->assign('cash',$ships);











	$sm->assign('has_cash', $au->user_rights->CheckAccess('w',833));












//***************************************** ПРИХОД НАЛИЧНЫХ *********************************************************
	$decorator=new DBDecorator;
	
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	$_pp=new CashInGroup;
	$_pp->prefix='_cash_in';
	$prefix=$_pp->prefix;
	
	//блок фильтров статуса
	
	 $status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^cash_in_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^cash_in_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^cash_in_'.$prefix.'status_id_','',$k);
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
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
	
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	
	
	if(isset($_GET['code_code'.$prefix])&&(strlen($_GET['code_code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code_id',SecStr($_GET['code_code'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code_code',$_GET['code_code'.$prefix]));
	}
	

	if(isset($_GET['given_no'.$prefix])&&(strlen($_GET['given_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no'.$prefix]));
	}
	
	
	if(isset($_GET['value'.$prefix])&&(strlen($_GET['value'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.value',SecStr($_GET['value'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('value',$_GET['value'.$prefix]));
	}
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['ru_name'.$prefix])&&(strlen($_GET['ru_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('ru.name_s',SecStr($_GET['ru_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('ru_name',$_GET['ru_name'.$prefix]));
	}
	
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	//если нет прав на просмотр всех приходов - просматривать только свои
	if(!$au->user_rights->CheckAccess('w',884)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('mn.id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.responsible_user_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	} 
	
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['given_pdate1'.$prefix];
		 $_given_pdate1= DateFromdmY($_GET['given_pdate1'.$prefix]);
	}
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['given_pdate2'.$prefix];
		  $_given_pdate2= DateFromdmY($_GET['given_pdate2'.$prefix]);
	}
	
	if(isset($_GET['given_pdate1'.$prefix])&&isset($_GET['given_pdate2'.$prefix])&&($_GET['given_pdate2'.$prefix]!="")&&($_GET['given_pdate2'.$prefix]!="-")&&($_GET['given_pdate1'.$prefix]!="")&&($_GET['given_pdate1'.$prefix]!="-")){
		
		$decorator->AddEntry(new UriEntry('given_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
		$decorator->AddEntry(new SqlEntry('p.given_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
				$decorator->AddEntry(new UriEntry('given_pdate1',''));
			$decorator->AddEntry(new UriEntry('given_pdate2',''));
	}	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=2;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('code_code',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('code_code',SqlOrdEntry::ASC));
		break;
		
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('value',SqlOrdEntry::DESC));
		break;			
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('value',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
			
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('ru_name',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('ru_name',SqlOrdEntry::ASC));
			
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
			
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
			
		break;
		
		
		 
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	//var_dump( $sortmode);
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	$decorator->AddEntry(new UriEntry('sortmode'.$prefix,$sortmode));
	
	
	$_pp->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('cash_in/all_cash_list.html', $decorator, 
	
	$au->user_rights->CheckAccess('w',886)||$au->user_rights->CheckAccess('w',898),  
	$au->user_rights->CheckAccess('w',896) ,$from, $to_page,
	$au->user_rights->CheckAccess('w',892) ,  
	false ,true,false, 
	$au->user_rights->CheckAccess('w',897) ,  
	$au->user_rights->CheckAccess('w',893),
	
	$au->user_rights->CheckAccess('w',885),
	$au->user_rights->CheckAccess('w',894),
	$au->user_rights->CheckAccess('w',895),
	$some,
	false,
	$au->user_rights->CheckAccess('w',898)
	);
			
	$sm->assign('cash_in',$ships);






	$sm->assign('has_cash_in', $au->user_rights->CheckAccess('w',883));















	
	
	$content=$sm->fetch('pay/all_pays_page.html');
	

	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>