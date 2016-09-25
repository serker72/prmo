<?
session_start();
require_once('classes/global.php');
 

 require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/supplieritem.php');
 

require_once('classes/v2/delivery_templates.class.php');


require_once('classes/authuser.php');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',943)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	} 
 

 

$razd=new Delivery_TemplateItem;
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
	
	
	 
 
		$razdel=$razd->GetItemById($id);
	 

	
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
	
	$sm->assign('data', $razdel);
	
	
	$page=$sm->fetch('delivery/view_template.html');
 

echo $page;




//нижний шаблон
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;
$smarty->display('page_email_bottom.html');
?>