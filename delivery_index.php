<?
session_start();
require_once('classes/global.php');
require_once('classes/smarty/SmartyAdm.class.php');
 require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/supplieritem.php');


require_once('classes/authuser.php');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',944)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	} 
 

//����� �� �������
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;
$smarty->clear_all_assign();

$smarty->assign("SITETITLE",'GYDEX.�������� - '.SITETITLE);

//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

 

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=70;
	if($print==0) include('inc/menu.php'); 

 $smarty = new SmartyAdm;


/*
//����������� �������
require_once('classes/v2/context.php');
//(  $object_id, $right_kind, $module_constant, $name, $url, $is_help , $_auth_result)
$_context=new Context;

$_context->AddContext(new ContextItem( 11, 'r', "", "������ ������", "tree.php", false , $global_profile  ));
$_context->AddContext(new ContextItem( 11, 'r', "", "������� � �������", "razds.php", false , $global_profile  ));
$_context->AddContext(new ContextItem( 11, 'a', "", "������� ������", "ed_razd.php?action=0", false , $global_profile  ));
$_context->AddContext(new ContextItem( 18, 'a', "HAS_NEWS", "������� �������", "ed_news.php?action=0", false , $global_profile  ));
$_context->AddContext(new ContextItem( 20, 'a', "HAS_PAPERS", "������� ������", "ed_paper.php?action=0", false , $global_profile  ));
$_context->AddContext(new ContextItem( 22, 'a', "HAS_PRICE", "������� �����", "ed_price.php?action=0", false , $global_profile  ));
$_context->AddContext(new ContextItem( 21, 'a', "HAS_PHOTOS", "������� ����", "ed_photo.php?action=0", false , $global_profile  ));

$_context->AddContext(new ContextItem( 11, 'r', "", "������", "viewotzyv.php", false , $global_profile  ));

$_context->AddContext(new ContextItem( 11, 'r', "", "�������", "viewads.php", false , $global_profile  ));

$_context->AddContext(new ContextItem( 14, 'r', "HAS_BASKET", "������", "vieworders.php", false , $global_profile  ));


$_context->AddContext(new ContextItem( 4, 'r', "", "������������ � �����", "discr_matrix_user.php", false , $global_profile  ));

$_context->AddContext(new ContextItem( 3, 'r', "", "����� �����", "discr_matrix_group.php", false , $global_profile  ));



$_context->AddContext(new ContextItem( "", '',  "", "�������", "common.html", true , $global_profile  ));

$context=$_context->BuildContext();
$smarty->assign('context', $context);
$smarty->assign('context_caption', '������� ��������');
*/
require_once('delivery_menu.php');
$smarty->assign("vmenu",$vmenu);

 
//������ ������


  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	 $smarty->display('page_site.html');









$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>