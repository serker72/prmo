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
require_once('classes/komplgroup.php');


require_once('classes/supplier_to_user.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр заявок');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['komplekt_from'])){
		$from=abs((int)$_SESSION['komplekt_from']);
	}else $from=0;
	$_SESSION['komplekt_from']=$from;


if(!$au->user_rights->CheckAccess('w',80)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Заявки',NULL,80);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=14;

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
	
	
	//покажем лог
	$log=new KomplGroup; 
	//Разбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	//декоратор себя не оправдывает!
	
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	

	
	//блок фильтров статуса
	/*$status_ids=array();
	$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $cou_stat++;
	if($cou_stat>0){
		//есть гет-запросы	
		
		foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $status_ids[]=(int)eregi_replace('^status_id_','',$k);
	}else{
		$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^status_id_', $k)) $cou_stat++;
		
		if($cou_stat>0){
			//есть кукисы
			foreach($_COOKIE as $k=>$v) if(eregi('^status_id_', $k)) $status_ids[]=(int)eregi_replace('^status_id_','',$k);
		}else{
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}
	}
	
	if(count($status_ids)>0){
		foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
		$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
	}

	*/
	
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^komplekt_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^komplekt_status_id_', $k)) $status_ids[]=(int)eregi_replace('^komplekt_status_id_','',$k);
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
		
	
	
	
	
	
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	

	
	
	
	 
	
	if(isset($_GET['name'])&&(strlen($_GET['name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name']));
	}
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sup.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
	}
	
	
	
	if(isset($_GET['fact_address'])&&(strlen($_GET['fact_address'])>0)){
		$decorator->AddEntry(new SqlEntry('p.fact_address',SecStr($_GET['fact_address']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('fact_address',$_GET['fact_address']));
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		/*case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;*/
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.end_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.begin_pdate',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.end_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('p.begin_pdate',SqlOrdEntry::ASC));
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
		 
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			 
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	
	$log->SetAuthResult($result);
	$llg=$log->ShowPos('komplekt/komplekt_list.html',$decorator,$from,$to_page,  $au->user_rights->CheckAccess('w',82)||$au->user_rights->CheckAccess('w',282),  $au->user_rights->CheckAccess('w',83),DateFromdmY($pdate1), DateFromdmY($pdate2),true,false, $au->user_rights->CheckAccess('w' ,132),$limited_sector , $au->user_rights->CheckAccess('w',81), $au->user_rights->CheckAccess('w',282), $au->user_rights->CheckAccess('w',97), $au->user_rights->CheckAccess('w',862), $au->user_rights->CheckAccess('w',863),$limited_supplier);
	
	
	$sm->assign('log',$llg);
	
	$content=$sm->fetch('komplekt/komplekt.html');
	
	
	

	
	
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