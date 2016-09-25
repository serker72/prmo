<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/semi_authuser.php');
 

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 
 

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="semi_auth")){
	
	$name=$_POST['name'];
	$_semi_auth=new SemiAuthUser($name);

	$login=SecStr($_POST['login']);
	$passw=SecStr(md5($_POST['passw']));
	
	$_semi_auth->Authorize($login, $passw );
	
	/*$_semi_auth->Auth($login);*/
	
	$rt=$_semi_auth->AuthUser($login, $passw);
	if($rt) $ret=0;
	else $ret='¬веден неверный пароль!';
	
	//$_semi_auth->Auth($login);
	
	/*$code=$_semi_auth->ShowError($_semi_auth->GetErrorCode());
	if($code=="") $ret=0;
	else $ret=$code; //.$_semi_auth->GetErrorCode();
	*/
	 
} 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>