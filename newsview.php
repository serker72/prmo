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
require_once('classes/actionlog.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');


require_once('classes/news.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

 
if(!$au->user_rights->CheckAccess('w',1118)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
$_ni=new News_NewsItem;
$editing_user=$_ni->GetItemById($id);

if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}

$_ng=new News_StreamGroup;
$_ng->ToggleRead($id, $result['id']);
	
$log->PutEntry($result['id'],'открыл раздел Новости',NULL,1118, NULL, SecStr('Новость от '.$editing_user['pdate'].' '.$editing_user['title'].' '.$editing_user['url']),$id);		

header('Location: '.$editing_user['url']);
exit();
?>