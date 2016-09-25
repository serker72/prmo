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
require_once('classes/sectorgroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Склады');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['sector_from'])){
		$from=abs((int)$_SESSION['sector_from']);
	}else $from=0;
	$_SESSION['sector_from']=$from;
	
if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);	



if(!$au->user_rights->CheckAccess('w',78)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//журнал событий 
//журнал событий 
$log=new ActionLog;
if($print==0){
	$log->PutEntry($result['id'],'открыл раздел Склады',NULL,78);
}else{
	$log->PutEntry($result['id'],'открыл раздел Склады: версия для печати',NULL,78);
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=9;
	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	//покажем лог
	$log=new SectorGroup; 
	
	$log->SetAuthResult($result);

	//Разбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	$decorator=new DBDecorator;
	
	if(isset($_GET['is_active'])){
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}else{
		if(count($_GET)>0) $decorator->AddEntry(new UriEntry('is_active',0));	
		else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		}
	}
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	if(isset($_GET['name'])&&(strlen($_GET['name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name']));
	}
	
	
	
	if(isset($_GET['fact_address'])&&(strlen($_GET['fact_address'])>0)){
		$decorator->AddEntry(new SqlEntry('p.fact_address',SecStr($_GET['fact_address']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('fact_address',$_GET['fact_address']));
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
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	
	if($print==0) $llg=$log->ShowPos('sector/sector_list'.$print_add.'.html',$decorator,$from,$to_page,$au->user_rights->CheckAccess('w',73),  $au->user_rights->CheckAccess('w',74),$au->user_rights->CheckAccess('w',544));
	else $llg=$log->ShowPos('sector/sector_list'.$print_add.'.html',$decorator,0,1000000,$au->user_rights->CheckAccess('w',73), $au->user_rights->CheckAccess('w',74),$au->user_rights->CheckAccess('w',544));
	
	
	$sm->assign('log',$llg);
	
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	
	$content=$sm->fetch('sector/sector'.$print_add.'.html');
	
	
	

	
	
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