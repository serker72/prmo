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
require_once('classes/specdelgroup.php');
require_once('classes/specdelitem.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Состав группы спецрассылок');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}
 


if(!$au->user_rights->CheckAccess('w',852)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


if(isset($_POST['doLoad'])){
	
	
	if(!$au->user_rights->CheckAccess('w',852)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
	
	//print_r($_POST);
	$_sdi=new SpecDelItem;
	$_sdi->Clear();
	foreach($_POST as $k=>$v){
		if(eregi("^user_",$k)){
			
			$params=array();
			$params['user_id']=abs((int)$v);
			$_test_sdi=$_sdi->GetItemByFields($params);	
			if($_test_sdi===false) $_sdi->Add($params);
			
		}
	}
	
	header("Location: spec_delivery.php");	
	die();	
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=63;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
 
	
	$sm2=new SmartyAdm;
	$sdg=new SpecDelGroup;
	$sm2->assign('items',$sdg->GetItemsArr());
	
	
	$llg=$sm2->fetch('spec_delivery/spec_delivery_form.html');
	
	
	$sm->assign('log',$llg);
	$content=$sm->fetch('spec_delivery/spec_delivery_page.html');
	
		
	
	$smarty->assign('fast_menu', $menu_arr_fast);
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
?>