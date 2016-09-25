<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей
require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/actionlog.php');

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');


$au=new AuthUser(); $backurl='/';
//$au->Auth();
$log=new ActionLog;
 

if(!isset($_GET['login']))
	if(!isset($_POST['login'])) {
			header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
		die();
	}
	else $login = $_POST['login'];		
else $login = SecStr($_GET['login']);		
$login=SecStr($login);	 


if(!isset($_GET['password']))
	if(!isset($_POST['password'])) {
			header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
		die();
	}
	else $password = SecStr($_POST['password']);		
else $password = SecStr($_GET['password']);

if(!isset($_GET['org_id']))
	if(!isset($_POST['org_id'])) {
			header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
		die();
	}
	else $org_id = abs((int)$_POST['org_id']);		
else $org_id = abs((int)$_GET['org_id']);

 
if((strlen(trim($login))==0)||(strlen(trim($password))==0)){
	header("HTTP/1.1 404 Not Found");
	header("Status: 404 Not Found");
	include("404.php");
	die();
}
	
	 
	
	
	//if(isset($_POST['rem_me'])) $rem=true; else $rem=false;
	$rem=false;
	//echo htmlspecialchars($login);
	
	//авторизуем юзера на сайте
	
	$sql='select * from user where is_active=1 and email_s="'.$login.'" and email_s<>"" and password="'.$password.'" ';
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->getresultnumrows();
	
	if($rc==0){
	
 		
		header('Location: /?error_code=1'); //'.$backurl);
		exit();
	}else {
		$profile=mysqli_fetch_array($rs);
		
		$au->Authorize($profile['login'],$profile['password'],$rem);
		
		$log->PutEntry($profile['id'], "вход в систему",NULL,NULL,NULL,"вход в систему через интернет-портал");
		
		if($org_id!=0){
			 $au->AuthorizeOrgId($org_id);
			 
			 if(!$au->CheckOrgId()){
				  header('Location: /?org_id='.$org_id.'&error_code='.urlencode('Невозможно войти в систему под данной организацией!'));
				  exit();
			  }else{
				  
				  $descr='';
				  
				  $_oi=new Orgitem;
				  $_opf=new OpfItem;
				  $oi=$_oi->GetItemById($org_id);
				  $opf=$_opf->GetItemById($oi['opf_id']);
				  
				  $descr=SecStr($oi['full_name'].', '.$opf['name']);
				  
				  $log->PutEntry($profile['id'], "выбор организации",NULL,NULL,NULL,"при входе через интернет-портал была выбрана организация: ".$descr);
				  header('Location: /');
				  exit();
			  }	
		}
		
		
		//выброс сотрудника из программы
		/*if(($profile['id']!=1)&&($profile['id']!=2)&&($profile['id']!=65)){
			
			$log->PutEntry($profile['id'], "автоматический выход из системы");
			$au->DeAuthorize();
			header('Location: /index_expired.php');
			die();
		}*/
		//конец выброса сотрудника
		
		
		header('Location: /');
		exit();
	}
	die();
 
 
header('Location: /');
die();

?>