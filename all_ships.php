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

require_once('classes/billitem.php');

require_once('classes/sh_i_group.php');
require_once('classes/sh_i_in_group.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр распоряжений');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['ships_from'])){
		$from=abs((int)$_SESSION['ships_from']);
	}else $from=0;
	$_SESSION['ships_from']=$from;

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',199)&&!$au->user_rights->CheckAccess('w',638)){
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



	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
		
/*********** РАСП НА ПРИЕМКУ ****************************************************************************/	
	
	
	$sm->assign('bill',$user);
	
	
	$_pp=new ShIInGroup;
	$prefix=$_pp->prefix;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	
	if(isset($_GET['status_id'.$prefix])){
		if($_GET['status_id'.$prefix]>0){
			$decorator->AddEntry(new SqlEntry('p.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
		}
		$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
	}else{
	
	  if(isset($_COOKIE['ship_status_id'.$prefix])){
			  $status_id=$_COOKIE['ship_status_id'.$prefix];
	  }else $status_id=0;
	  
	  if($status_id>0) $decorator->AddEntry(new SqlEntry('p.status_id',$status_id, SqlEntry::E));
	  $decorator->AddEntry(new UriEntry('status_id',$status_id));
	}		
	
	
	
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'.$prefix])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'.$prefix];
	
	
	
	if(!isset($_GET['pdate2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'.$prefix];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
	}
	
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
	}
	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	
	
	
	
	$_pp->SetPageName('all_ships.php');
	
	
	//$_pp->AutoAnnul();
	//$_pp->AutoEq();
	
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('ships_in/all_ships_list.html', $decorator, 
	$au->user_rights->CheckAccess('w',644)||$au->user_rights->CheckAccess('w',653), 
	$au->user_rights->CheckAccess('w',654),$from, $to_page, 
	$au->user_rights->CheckAccess('w',651), 
	$au->user_rights->CheckAccess('w',96),true,false,
	$au->user_rights->CheckAccess('w',655),$limited_sector,
	$au->user_rights->CheckAccess('w',652));
			
	$sm->assign('log2',$ships); 	
	
	$sm->assign('has_incoming_ships', $au->user_rights->CheckAccess('w',638));
	
	
	
	
	
	
	
	
	
	
	
	
	
/*********** РАСП НА ОТГРУЗКУ ****************************************************************************/		
		
	
	
	
	$sm->assign('bill',$user);
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	
	if(isset($_GET['status_id'])){
		if($_GET['status_id']>0){
			$decorator->AddEntry(new SqlEntry('p.status_id',abs((int)$_GET['status_id']), SqlEntry::E));
		}
		$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id']));
	}else{
	
	  if(isset($_COOKIE['ship_status_id'])){
			  $status_id=$_COOKIE['ship_status_id'];
	  }else $status_id=0;
	  
	  if($status_id>0) $decorator->AddEntry(new SqlEntry('p.status_id',$status_id, SqlEntry::E));
	  $decorator->AddEntry(new UriEntry('status_id',$status_id));
	}		
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	
	
	
	$_pp=new ShIGroup;
	$_pp->SetPageName('all_ships.php');
	
	
	//$_pp->AutoAnnul();
	//$_pp->AutoEq();
	
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('ships/all_ships_list.html', $decorator, $au->user_rights->CheckAccess('w',219)||$au->user_rights->CheckAccess('w',285), $au->user_rights->CheckAccess('w',226),$from, $to_page, $au->user_rights->CheckAccess('w',224), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',227),$limited_sector,$au->user_rights->CheckAccess('w',225));
			
	$sm->assign('log',$ships); 	
	$sm->assign('has_ships', $au->user_rights->CheckAccess('w',199));
	
	
	
	$content=$sm->fetch('ships/all_ships_page.html');
	
	
	
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