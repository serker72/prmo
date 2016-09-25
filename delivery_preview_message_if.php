<?
session_start();
require_once('classes/global.php');
require_once('classes/mmenuitem.php');
require_once('classes/langgroup.php');
require_once('classes/langitem.php');

require_once('classes/allmenu_template_group.php');

require_once('classes/v2/delivery_templates.class.php');
require_once('classes/v2/delivery.class.php');

//административная авторизация
require_once('inc/adm_header.php');

$razd=new Delivery_Item;
$_list=new Delivery_TemplateGroup;

if(!isset($_GET['from'])){
	if(!isset($_POST['from'])){
		$from=0;
	}else $from = $_POST['from'];	
}else $from = $_GET['from'];	
$from=abs((int)$from);	


if(!isset($_GET['to_page'])){
	if(!isset($_POST['to_page'])){
		$to_page=ITEMS_PER_PAGE;
	}else $to_page = $_POST['to_page'];	
}else $to_page = $_GET['to_page'];	
$to_page=abs((int)$to_page);	

  
	//проверим id
	if(!isset($_GET['id']))
		if(!isset($_POST['id'])) {
			header('Location: index.php');
			die();
		}
		else $id = $_POST['id'];		
	else $id = $_GET['id'];		
	$id=abs((int)$id);
	
	
	 
	$rights_man=new DistrRightsManager;
	if($rights_man->CheckAccess($global_profile['login'], $global_profile['passw'], 'r', 26)) {
	
		$razdel=$razd->GetItemById($id);
	}else{
		header('Location: no_rights.php');
   	    die();		
	}

	
	//echo $razdel['parent_id']; die();
	
	if($razdel==false){
		header('Location: index.php');
		die();	
	}
 
 

 //вывод из шаблона
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;

$smarty->assign("SITETITLE",'Электронное сообщение');
$smarty->assign("NAVIMENU",'');
$smarty->assign("SITEURL",SITEURL);
 
 


$smarty->display('page_email_top.html');

 

$sm=new SmartyAdm();

$sm->assign('pagename', $razd->GetPageName());
$sm->assign('list_pagename', $_list->GetPageName());
$sm->assign('action', $action);
$sm->assign('from', $from);
$sm->assign('to_page', $to_page);

 
	foreach($razdel as $k=>$v) $razdel[$k]=stripslashes($v);
	
	$_pro=new Delivery_Fields;
	$_pro->ProcessFields(NULL, $razdel);
	
		$sm->assign('data', $razdel);
	
	$page=$sm->fetch('delivery/view_message_if.html');
 

echo $page;

 

//нижний шаблон
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;
$smarty->display('page_email_bottom.html'); 
?>