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

require_once('classes/program_group.php');
require_once('classes/program_item.php');
 

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	 
 

// определим товар
if(!isset($_GET['program_id']))
	if(!isset($_POST['program_id'])) {
			header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	}
	else $program_id = $_POST['program_id'];		
else $program_id = $_GET['program_id'];		
$program_id=abs((int)$program_id);	

$gd=new ProgramItem;
$good=$gd->GetItemById($program_id);

if(($good===false)||($good['is_active']==0)){
	 
		header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
}
	
if(!isset($_GET['login']))
	if(!isset($_POST['login'])) {
			header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	}
	else $login = $_POST['login'];		
else $login = $_GET['login'];		
 	 


if(!isset($_GET['password']))
	if(!isset($_POST['password'])) {
			header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	}
	else $password = $_POST['password'];		
else $password = $_GET['password'];
 

$debug_prefix=''; if(DEBUG_REDIRECT) $debug_prefix='debug_';
 
if(!isset($_GET['org_id']))
	if(!isset($_POST['org_id'])) {
		$org_id='';	 
	}
	else $org_id = $_POST['org_id'];		
else $org_id = $_GET['org_id'];
if($org_id!='') $org_id='&org_id='.$org_id;


//редирект в программу
header("Location: ".$good[$debug_prefix.'url']."/user_mail_login.php?login=".$login."&password=".$password. $org_id);

exit();

 
?>