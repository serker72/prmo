<?
 

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
 


require_once('classes/semi_authuser.php');







$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Дополнительная авторизация');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
 



if(!isset($semi_auth_session_name)) $semi_auth_session_name='an_pm';


$_semi_auth=new SemiAuthUser($semi_auth_session_name);
$semi_result=$_semi_auth->Auth($result['login']);
/*
 var_dump($semi_result);
 die();*/

if($semi_result===NULL){
	

	
	
		 
	//работа с хедером
	require_once('inc/header.php');
	if(isset($header_res)){
		$smarty->assign('header',$header_res);
	}else $smarty->assign('header','');
	
	$smarty->display('top.html');
	unset($smarty);
	
	
	
		include('inc/menu.php');
		
		
		
		//демонстрация страницы
		$smarty = new SmartyAdm;
		
		$sm=new SmartyAdm;
		
		
		
		$sm->assign('name', $_semi_auth->GetSessionName());
		 
		$sm->assign('login', $result['login']); 
		$content=$sm->fetch('semi_auth.html');
		 
		
		
		$smarty->assign('main_menu',$menu_res);
		$smarty->assign('content',$content);
		$smarty->display('page.html');
		unset($smarty);
	
	
	$smarty = new SmartyAdm;
	
	//работа с футером
	require_once('inc/footer.php');
	if(isset($footer_res)){
		$smarty->assign('footer',$footer_res);
	}else $smarty->assign('footer','');
	
	$smarty->display('bottom.html');
	unset($smarty);	
	
	exit();
}
?>