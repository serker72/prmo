<?
session_start();
require_once('classes/global.php');
/*require_once('classes/mmenulist.php');
require_once('classes/langgroup.php');
require_once('classes/langitem.php');
require_once('classes/filetext.php');
require_once('classes/authuser.php');
require_once('classes/program_group.php');
require_once('classes/program_item.php');
*/

require_once('classes/supplieritem.php');
require_once('classes/v2/delivery.class.php');
require_once('classes/v2/delivery_lists.class.php');


if(!isset($_GET['user_id']))
	if(!isset($_POST['user_id'])) {
			header("HTTP/1.1 404 Not Found");
header("Status: 404 Not Found");
include("404.php");
	}
	else $user_id = $_POST['user_id'];		
else $user_id = $_GET['user_id'];		
$user_id=abs((int)$user_id);


if(!isset($_GET['id']))
	if(!isset($_POST['id'])) {
			header("HTTP/1.1 404 Not Found");
header("Status: 404 Not Found");
include("404.php");
	}
	else $id = $_POST['id'];		
else $id = $_GET['id'];		
$id=abs((int)$id);

$_du=new Delivery_UserItem;
$_dl=new Delivery_LinkItem;
$_dsh=new Delivery_LinkHitItem;

$du=$_du->GetItemById($user_id);
if($du===false){
	
}

$dl=$_dl->GetItemById($id);
if($dl===false){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
}

//занести статистику
if(($dl!==false)&&($du!==false)){
	$_dsh->Put($id, $user_id, getenv('HTTP_X_REAL_IP')/*$_SERVER['REMOTE_ADDR']*/);	
}

header("Location: ".$dl['url']);
exit();
 
?>