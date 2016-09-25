<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

 
 
require_once('../classes/usersession.php');


$au=new AuthUser();
$result=$au->Auth(false,false,false,false);
$log=new ActionLog;

if($result===NULL){
	/*header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		*/
}


if(isset($_GET['action'])&&($_GET['action']=="check_session")){
	
	 
	
	$_us=new UserSession;
	
	$ses=$_us->GetItemByFields(array('user_id'=>$result['id'],'ip'=>getenv('REMOTE_ADDR'),'sid'=>session_id()));
	if($result!==NULL){
	if($ses!==false){
		//есть
		//найдем разницу в секундах...
		$delta=((int)$ses['ttime']+$au->GetMaxCookieTime() -time());
		
		if(($delta>=0)&&($delta<=30)) $ret=$delta;
		elseif($delta<=0) $ret=0;
		else $ret=-1;
		//echo $delta;
			
	}else{
		$ret=-1;	
	}
	}else $ret=0;
	
}




	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>