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
require_once('classes/billfileitem.php');
require_once('classes/billfilegroup.php');

require_once('classes/supplieritem.php');
require_once('classes/orgitem.php');

 require_once('classes/supplier_ruk_group.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Руководители контрагента/организации');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['supplier_id'])){
		if(!isset($_POST['supplier_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $supplier_id=abs((int)$_POST['supplier_id']);	
	}else $supplier_id=abs((int)$_GET['supplier_id']);
	 

$_si=new SupplierItem;
$si=$_si->GetItemById($supplier_id);

$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',87)&&!$au->user_rights->CheckAccess('w',121)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=27;

	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
/*********** % ****************************************************************************/	
	
	 
	
	$decorator=new DBDecorator;
	
	//$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry('p.supplier_id',$supplier_id, SqlEntry::E));
	
	$decorator->AddEntry(new SqlOrdEntry('begin_pdate',SqlOrdEntry::ASC));
	

	
	$_cp=new SupplierRukGroup;
	
	$_cp->SetAuthResult($result);
	$ships=$_cp->ShowAllPos($supplier_id, 'supplier_ruk/supplier_ruk_list.html',  $decorator,  $some);


	

	$sm->assign('log2',$ships); 
	
	
	
	$content=$sm->fetch('supplier_ruk/supplier_ruk_page.html');
	

	
	
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