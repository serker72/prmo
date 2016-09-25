<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');


require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/kpgroup.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'–еестр коммерческих предложений');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['bills_from'])){
		$from=abs((int)$_SESSION['bills_from']);
	}else $from=0;
	$_SESSION['bills_from']=$from;



if(!$au->user_rights->CheckAccess('w',695)&&!$au->user_rights->CheckAccess('w',712)){
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
	
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	

	
	
/*********** KP ****************************************************************************/		
	
	//покажем лог
	$log=new KpGroup;
	//–азбор переменных запроса
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	//$decorator->AddEntry(new SqlEntry('p.is_incoming',0, SqlEntry::E));
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	
	
	if(isset($_GET['status_id'])){
		if($_GET['status_id']>0){
					$decorator->AddEntry(new SqlEntry('p.status_id',abs((int)$_GET['status_id']), SqlEntry::E));
				}
		$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id']));
	}else{
	
	  if(isset($_COOKIE['kp_status_id'])){
			  $status_id=$_COOKIE['kp_status_id'];
	  }else $status_id=0;
	  
	  if($status_id>0) $decorator->AddEntry(new SqlEntry('p.status_id',$status_id, SqlEntry::E));
	  $decorator->AddEntry(new UriEntry('status_id',$status_id));
	}
	
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	
	if(!isset($_GET['supplier_bill_pdate1'])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['supplier_bill_pdate1'];
		 $_given_pdate1= DateFromdmY($_GET['supplier_bill_pdate1']);
	}
	
	
	
	if(!isset($_GET['supplier_bill_pdate2'])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['supplier_bill_pdate2'];
		  $_given_pdate2= DateFromdmY($_GET['supplier_bill_pdate2']);
	}
	
	if(isset($_GET['supplier_bill_pdate1'])&&isset($_GET['supplier_bill_pdate2'])&&($_GET['supplier_bill_pdate2']!="")&&($_GET['supplier_bill_pdate2']!="-")&&($_GET['supplier_bill_pdate1']!="")&&($_GET['supplier_bill_pdate1']!="-")){
		
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',$given_pdate2));
		
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate1',''));
		$decorator->AddEntry(new UriEntry('supplier_bill_pdate2',''));
	}
	
	
	
	//сортировку можно подписать как дополнительный параметр дл€ UriEntry
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
	/*		$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			
		break;*/
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	//$log->AutoAnnul();
	//$log->AutoEq();
	$log->SetAuthResult($result);
	
	
	$llg=$log->ShowPos('kp/kps_list.html',
	  $decorator,
	  $from,
	  $to_page, 
	  $au->user_rights->CheckAccess('w',696),
	  $au->user_rights->CheckAccess('w',701)||$au->user_rights->CheckAccess('w',712),
	  $au->user_rights->CheckAccess('w',713), '',
	  $au->user_rights->CheckAccess('w',709),
	  $au->user_rights->CheckAccess('w',96), true,false, 
	  $au->user_rights->CheckAccess('w',714),  NULL,
	  $au->user_rights->CheckAccess('w',711)
	);
	
	
	$sm->assign('log',$llg);
	$sm->assign('has_kps', $au->user_rights->CheckAccess('w',695));
	
	
	
	
	
	
	
	$content=$sm->fetch('kp/kps.html');
	
	
	

	
	
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