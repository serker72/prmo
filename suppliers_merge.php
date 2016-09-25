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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/suppliersgroup.php');

require_once('classes/supplier_to_user.php');

require_once('classes/supplier_merge.class.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Объединение контрагентов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['suppliers_from'])){
		$from=abs((int)$_SESSION['suppliers_from']);
	}else $from=0;
	$_SESSION['suppliers_from']=$from;




if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

 


if(!$au->user_rights->CheckAccess('w',914)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


if(isset($_POST['doMerge'])){
	
	//разобрать массив данных, объединить карты
	$_merge=new SuppliersMerge;
	
	$res_id=$_merge->Merge($_POST, $result);
	
	header("Location: supplier.php?action=1&id=".$res_id);
	
	die();	
}


$log=new ActionLog;
 
	$log->PutEntry($result['id'],'открыл раздел Объединение контрагентов',NULL,914);
 


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=27;
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	 
	
	
	//Разбор переменных запроса
	 /*
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	//$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	
	
	if(isset($_GET['is_active'])&&($_GET['is_active']==1)){
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}elseif(isset($_GET['is_active'])&&($_GET['is_active']==0)){
		 $decorator->AddEntry(new UriEntry('is_active',0));	
	}else{
		if(count($_GET)>1){
			 $decorator->AddEntry(new UriEntry('is_active',0));	
			 //echo 'ZZZZZZZZZZZZzz';
		}else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		}
	}
	
	
	 
	
	if(isset($_GET['legal_address'])&&(strlen($_GET['legal_address'])>0)){
		$decorator->AddEntry(new SqlEntry('p.legal_address',SecStr($_GET['legal_address']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('legal_address',$_GET['legal_address']));
	}
	
	if(isset($_GET['inn'])&&(strlen($_GET['inn'])>0)){
		$decorator->AddEntry(new SqlEntry('p.inn',SecStr($_GET['inn']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('inn',$_GET['inn']));
	}
	
	if(isset($_GET['kpp'])&&(strlen($_GET['kpp'])>0)){
		$decorator->AddEntry(new SqlEntry('p.kpp',SecStr($_GET['kpp']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('kpp',$_GET['kpp']));
	}
	
	if(isset($_GET['full_name'])&&(strlen($_GET['full_name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.full_name',SecStr($_GET['full_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('full_name',$_GET['full_name']));
	}
	
	
	if(isset($_GET['resp'])&&(strlen($_GET['resp'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_responsible_user as sr inner join user as u on u.id=sr.user_id where u.name_s LIKE "%'.SecStr($_GET['resp']).'%" ', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('resp',$_GET['resp']));
	}
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);


 
	
	
	$ug=new SuppliersGroup;
	if($print==0) $uug= $ug->GetItems('suppliers/suppliers'.$print_add.'.html',$decorator,$from,$to_page,false,$au->user_rights->CheckAccess('w',543),  $limited_supplier,  $result, $au->user_rights->CheckAccess('w',914));
	else $uug= $ug->GetItems('suppliers/suppliers'.$print_add.'.html',$decorator,0,1000000,false,$au->user_rights->CheckAccess('w',543), $limited_supplier,  $result, $au->user_rights->CheckAccess('w',914));
	
	
	
	*/
	
	
	$sm1=new SmartyAdm;
	
	$uug=$sm->fetch('suppliers/merge.html');
	
	$sm->assign('users',$uug);
	
	
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	
	$sm->assign('username',$username);
	
	
	$content=$sm->fetch('suppliers/suppliers_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>