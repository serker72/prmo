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

require_once('classes/billgroup.php');

require_once('classes/bill_in_group.php');

require_once('classes/supplier_to_user.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр счетов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['bills_from'])){
		$from=abs((int)$_SESSION['bills_from']);
	}else $from=0;
	$_SESSION['bills_from']=$from;



if(!$au->user_rights->CheckAccess('w',97)&&!$au->user_rights->CheckAccess('w',606)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//журнал событий 
$log=new ActionLog;
if($au->user_rights->CheckAccess('w',97)) $log->PutEntry($result['id'],'открыл раздел Счета',NULL,97);
if($au->user_rights->CheckAccess('w',606)) $log->PutEntry($result['id'],'открыл раздел Счета',NULL,606);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=15;

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
	
	
	
/*********** ВХОД. СЧЕТА ****************************************************************************/	
	
	$log=new BillInGroup;
	//echo 'zz'; die();
	$prefix=$log->prefix;
	
	//Разбор переменных запроса
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	//$decorator->AddEntry(new SqlEntry('p.is_incoming',0, SqlEntry::E));
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	//блок фильтров статуса
	 
	 
	 $status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'bill_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'bill_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'bill_'.$prefix.'status_id_','',$k);
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
	 
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	if(isset($_GET['supplier_bill_no'.$prefix])&&(strlen($_GET['supplier_bill_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no'.$prefix]));
	}
	
	if(isset($_GET['name'.$prefix])&&(strlen($_GET['name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name'.$prefix]));
	}
	
	
	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	
	if(!isset($_GET['supplier_bill_pdate1'.$prefix])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['supplier_bill_pdate1'.$prefix];
		 $_given_pdate1= DateFromdmY($_GET['supplier_bill_pdate1'.$prefix]);
	}
	
	
	
	if(!isset($_GET['supplier_bill_pdate2'.$prefix])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['supplier_bill_pdate2'.$prefix];
		  $_given_pdate2= DateFromdmY($_GET['supplier_bill_pdate2'.$prefix]);
	}
	
	if(isset($_GET['supplier_bill_pdate1'.$prefix])&&isset($_GET['supplier_bill_pdate2'.$prefix])&&($_GET['supplier_bill_pdate2'.$prefix]!="")&&($_GET['supplier_bill_pdate2'.$prefix]!="-")&&($_GET['supplier_bill_pdate1'.$prefix]!="")&&($_GET['supplier_bill_pdate1'.$prefix]!="-")){
		
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
		
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',''));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',''));
	}
	
	
	
	  if(isset($_GET['sector_id'.$prefix])&&(strlen($_GET['sector_id'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.sector_id',abs((int)$_GET['sector_id'.$prefix]), SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id'.$prefix]));
	  }
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	
	
	switch($sortmode){
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
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
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
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	$log->SetAuthResult($result);
	
	
	$llg=$log->ShowPos('bills_in/bills_list.html',//0
	$decorator,//1
	$from,//2
	$to_page,//3 
	false, //4
	$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), //5
	$au->user_rights->CheckAccess('w',626),  //6
	'',  //7
	$au->user_rights->CheckAccess('w',620), //8
	$au->user_rights->CheckAccess('w',96), //9
	true,	//10
	false,	//11
	$au->user_rights->CheckAccess('w',627),	//12
	$limited_sector,	//13
	NULL,	//14
	$au->user_rights->CheckAccess('w',621),	//15
	$au->user_rights->CheckAccess('w',622), //16
	$au->user_rights->CheckAccess('w',623), //17
	$bills_list, //18
	$au->user_rights->CheckAccess('w',623),
	$au->user_rights->CheckAccess('w',625),
	false,
	$limited_supplier,
	$au->user_rights->CheckAccess('w',865),
	$au->user_rights->CheckAccess('w',874)
	);
	
	
	$sm->assign('log2',$llg);
	
	
	
	
	$sm->assign('has_incoming_bills', $au->user_rights->CheckAccess('w',606));
	
	
	
/*********** исход. СЧЕТА ****************************************************************************/		
	
	//покажем лог
	$log=new BillGroup; 
	//Разбор переменных запроса
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	//$decorator->AddEntry(new SqlEntry('p.is_incoming',0, SqlEntry::E));
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	//блок фильтров статуса
	/*$status_ids=array();
	$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $cou_stat++;
	if($cou_stat>0){
		//есть гет-запросы	
		
		foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $status_ids[]=(int)eregi_replace('^status_id_','',$k);
	}else{
		$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^bill_status_id_', $k)) $cou_stat++;
		
		if($cou_stat>0){
			//есть кукисы
			foreach($_COOKIE as $k=>$v) if(eregi('^bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_status_id_','',$k);
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
			}
		}
		*/
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^bill_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_status_id_','',$k);
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
		
	
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	if(isset($_GET['supplier_bill_no'])&&(strlen($_GET['supplier_bill_no'])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no']));
	}
	
	if(isset($_GET['name'])&&(strlen($_GET['name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name']));
	}
	
	
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	if(isset($_GET['user_confirm_price_id'])&&(strlen($_GET['user_confirm_price_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.user_confirm_price_id',abs((int)$_GET['user_confirm_price_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('user_confirm_price_id',$_GET['user_confirm_price_id']));
	}
	
	if(!isset($_GET['supplier_bill_pdate1'])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['supplier_bill_pdate1'];
		 $_given_pdate1= DateFromdmY($_GET['supplier_bill_pdate1']);
	}
	
	
	
	if(!isset($_GET['supplier_bill_pdate2'])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['supplier_bill_pdate2'];
		  $_given_pdate2= DateFromdmY($_GET['supplier_bill_pdate2']);
	}
	
	if(isset($_GET['supplier_bill_pdate1'])&&isset($_GET['supplier_bill_pdate2'])&&($_GET['supplier_bill_pdate2']!="")&&($_GET['supplier_bill_pdate2']!="-")&&($_GET['supplier_bill_pdate1']!="")&&($_GET['supplier_bill_pdate1']!="-")){
		
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
		
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',''));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',''));
	}
	
	
	 if(isset($_GET['sector_id'])&&(strlen($_GET['sector_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id']));
	}
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	
	
	switch($sortmode){
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
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	$log->SetAuthResult($result);
	
	
	$llg=$log->ShowPos(
		'bills/bills_list.html',//0
		$decorator,//1
		$from,//2
		$to_page,//3 
		$au->user_rights->CheckAccess('w',128), //4
		$au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), //5
		$au->user_rights->CheckAccess('w',94), //6
		'', //7
		$au->user_rights->CheckAccess('w',95), //8
		$au->user_rights->CheckAccess('w',96),//9
		true,//10
		false,//11
		$au->user_rights->CheckAccess('w',131),//12
		$limited_sector,//13
		NULL,//14
		$au->user_rights->CheckAccess('w',195),//15
		$au->user_rights->CheckAccess('w',196), //16
		$au->user_rights->CheckAccess('w',197),//17
		$bills_list,//18
		$au->user_rights->CheckAccess('w',283), //19
		$au->user_rights->CheckAccess('w',860), //20
		$au->user_rights->CheckAccess('w',835), //21
		$limited_supplier,  //22
		false, //23
		$au->user_rights->CheckAccess('w',873) //24
	);
	
	
	$sm->assign('log',$llg);
	$sm->assign('has_bills', $au->user_rights->CheckAccess('w',97));
	
	
	
	
	
	
	
	$content=$sm->fetch('bills/bills.html');
	
	
	

	
	
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