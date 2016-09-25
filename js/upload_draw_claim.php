<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');



//require_once('../classes/messagegroup.php');
//require_once('../classes/messageitem.php');




$au=new AuthUser();
$result=$au->Auth();

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';

$ui=new UserItem;

if(isset($_POST['action'])&&($_POST['action']=="add_file_entry")){
	
	$factname=$_POST['factname'];
	$realname=$_POST['realname'];
	$kind_id=$_POST['kind_id'];
	
	$sm=new SmartyAj;
	$sm->assign('factname',iconv("utf-8","windows-1251",$factname));
	$sm->assign('realname',iconv('utf-8', 'windows-1251',$realname));
	$sm->assign('kind_id',iconv('utf-8', 'windows-1251',$kind_id));
	
	$ret=$sm->fetch('orders/uploaded_kind_file.html');
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>