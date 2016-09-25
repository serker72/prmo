<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей
 


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');


require_once('classes/help.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_POST['topic_id']))
if(!isset($_GET['topic_id'])) $topic_id=0;
else $topic_id=abs((int)$_GET['topic_id']);
else $topic_id=abs((int)$_POST['topic_id']);

if($topic_id!==0){
	$_hi=new HelpElemItem;
	$hi=$_hi->TestItemById($topic_id);
	if($hi===false){
		 header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die(); 
	}
}


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","GYDEX.Отчеты");

$au=new AuthUser();
$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

 

if($result!==NULL){
$smarty = new SmartyAdm;


	
	  include('inc/menu.php');
	  $sm=new SmartyAdm;
	  
	  
	  
	  $sm->assign('fast_menu', $menu_arr_fast);
	  
	 
$content=$sm->fetch('fast_reports.html');

$smarty->assign('fast_menu', $menu_arr_fast);
 
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);
 

 }
 
$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>