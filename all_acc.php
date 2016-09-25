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

require_once('classes/acc_group.php');


require_once('classes/bill_in_item.php');

require_once('classes/acc_in_group.php');

require_once('classes/demandgroup.php');

require_once('classes/supplier_to_user.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Поступления/реализации товара');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['acc_from'])){
		$from=abs((int)$_SESSION['acc_from']);
	}else $from=0;
	$_SESSION['acc_from']=$from;
	

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',200)&&!$au->user_rights->CheckAccess('w',659)&&!$au->user_rights->CheckAccess('w',855)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//журнал событий 
$log=new ActionLog;
if($au->user_rights->CheckAccess('w',200)) $log->PutEntry($result['id'],'открыл раздел Реализации',NULL,200);
if($au->user_rights->CheckAccess('w',659)) $log->PutEntry($result['id'],'открыл раздел Поступления',NULL,659);
if($au->user_rights->CheckAccess('w',855)) $log->PutEntry($result['id'],'открыл раздел Требования',NULL,855);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=34;
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
	

/*********** ПОСТУПЛЕНИЯ ****************************************************************************/	
	$_pp=new AccInGroup;
	
	$prefix=$_pp->prefix;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	//блок фильтров статуса
	 
	
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[ $prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$prefix.'acc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_'.$prefix.'acc_status_id_','',$k);
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
	
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
	}

	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	if(isset($_GET['sector_id'.$prefix])&&(strlen($_GET['sector_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id'.$prefix]));
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
	
	
	
	
	//$_pp->AutoAnnul();
	
	$_pp->SetPageName('all_acc.php');
	
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('acc_in/all_accs_list.html', //0
	 $decorator, //1
	$au->user_rights->CheckAccess('w',664)||$au->user_rights->CheckAccess('w',673), //2
	$au->user_rights->CheckAccess('w',674), //3
	$from, //4
	$to_page,  //5
	$au->user_rights->CheckAccess('w',671),   //6
	$au->user_rights->CheckAccess('w',96), //7
	true, //8
	false , //9
	$au->user_rights->CheckAccess('w',675) ,//10
	$limited_sector, //11
	$au->user_rights->CheckAccess('w',672), //12
	$au->user_rights->CheckAccess('w',673), //13
	false, //14
	$limited_supplier, //15
	$au->user_rights->CheckAccess('w',930)); //16
			
	$sm->assign('log2',$ships); 	
	
	$sm->assign('has_incoming_accs', $au->user_rights->CheckAccess('w',659));
	
	
	
	
	
	
	
/*********** РЕАЛИЗАЦИИ ****************************************************************************/		
		
	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	//блок фильтров статуса
 
	
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_status_id_','',$k);
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
	
	if(isset($_GET['given_no'])&&(strlen($_GET['given_no'])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no']));
	}
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}

	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	if(isset($_GET['sector_id'])&&(strlen($_GET['sector_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id']));
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
	
	
	
	$_pp=new AccGroup;
	
	//$_pp->AutoAnnul();
	
	$_pp->SetPageName('all_acc.php');
	
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos(
		'acc/all_accs_list.html', 
		$decorator, 
		$au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), 
		$au->user_rights->CheckAccess('w',242), 
		$from, 
		$to_page,  
		$au->user_rights->CheckAccess('w',240),  
		$au->user_rights->CheckAccess('w',96), 
		true,
		false ,
		$au->user_rights->CheckAccess('w',243) ,
		$limited_sector,
		$au->user_rights->CheckAccess('w',241),
		$au->user_rights->CheckAccess('w',286), 
		$au->user_rights->CheckAccess('w',861),
		$limited_supplier );
			
	$sm->assign('log',$ships); 	
	$sm->assign('has_accs', $au->user_rights->CheckAccess('w',200));
	





/* ***************** ТРЕБОВАНИЯ ******************************************************************************* */

	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	$_pp=new DemandGroup;
	$_pp->prefix='_in';
	$prefix=$_pp->prefix;

	 
$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[ $prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^dem_'.$prefix.'_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^dem_'.$prefix.'_status_id_', $k)) $status_ids[]=(int)eregi_replace('^dem_'.$prefix.'_status_id_','',$k);
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
	
	
	
	
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
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
		//мб фильтр по сотруднику
		
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		/*$decorator->AddEntry(new SqlEntry('mn.id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.responsible_user_id',$result['id'], SqlEntry::E));*/
		
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('inu.name_s',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('inu.login',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
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
	
	
	
	$_pp->SetPageName('all_acc.php');
	
	//$_pp->AutoAnnul();
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('demands/all_demands_list.html', $decorator, $au->user_rights->CheckAccess('w',855)||$au->user_rights->CheckAccess('w',855),  $au->user_rights->CheckAccess('w',855),$from, $to_page,$au->user_rights->CheckAccess('w',855),  $au->user_rights->CheckAccess('w',855),true,false, $au->user_rights->CheckAccess('w',855), $au->user_rights->CheckAccess('w',855),  $au->user_rights->CheckAccess('w',855));
			



	$sm->assign('log3',$ships); 	
	
	$sm->assign('has_demands', $au->user_rights->CheckAccess('w',855));
	







	
	
	$content=$sm->fetch('acc/all_accs_page.html');
	
	
	
	
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