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
$au->Auth();
$log=new ActionLog;

$backurl=getenv('HTTP_REFERER');


//разлогинимся
if(isset($_GET['doOut'])){
		$pro=$au->GetProfile();
		
		if(isset($pro['id'])) $log->PutEntry($pro['id'], "выход из системы");
		$au->DeAuthorize();
		header('Location: /');
		die();
}

//залогинимся
if(isset($_POST['doLog'])){
	$login=$_POST['login'];
	$password=md5(($_POST['password']));
	//if(isset($_POST['rem_me'])) $rem=true; else $rem=false;
	$rem=false;
	//echo htmlspecialchars($login);
	
	
	
	//авторизуем юзера на сайте
	$au->Authorize($login,$password,$rem);
	$profile=$au->GetProfile();
	if($profile===NULL){
		$code=$au->GetErrorCode();
		header('Location: /?error_code='.$code); //'.$backurl);
	}else {
		$log->PutEntry($profile['id'], "вход в систему");
		header('Location: /');
	}
	die();
}

//выбор адреса
if(isset($_POST['doAddr'])){
	$org_id=abs((int)$_POST['org_id']);
	//$pin=md5(($_POST['pin']));
	
	$profile=$au->GetProfile();
	if($profile!==NULL){
	  
	
	  $au->AuthorizeOrgId($org_id);
	  //var_dump($profile);
	  
	  if(!$au->CheckOrgId()){
		  header('Location: /?org_id='.$org_id.'&error_code='.urlencode('Невозможно войти в систему под данной организацией!'));
	  }else{
		  
		  $descr='';
		  
		  $_oi=new Orgitem;
		  $_opf=new OpfItem;
		  $oi=$_oi->GetItemById($org_id);
		  $opf=$_opf->GetItemById($oi['opf_id']);
		  
		  $descr=SecStr($oi['full_name'].', '.$opf['name']);
		  
		  $log->PutEntry($profile['id'], "выбор организации",NULL,NULL,NULL,$descr);
		  header('Location: /');
	  }
	  
	}else{
		header('Location: /?error_code='.urlencode('Ошибка авторизации!'));	
	}
	die();
}


header('Location: /');
die();

?>