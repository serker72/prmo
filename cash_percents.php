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

require_once('classes/paygroup.php');
require_once('classes/pay_in_group.php');

require_once('classes/cash_percent_group.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'% вывода наличных');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	 

$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',851)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел % вывода наличных',NULL,851);



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
	
	
	
/*********** % ****************************************************************************/	
	
	
	/*
	$_pp=new PayInGroup;
	$_pp->prefix='_in';
	$prefix=$_pp->prefix;
	
	
	if(isset($_GET['status_id'.$prefix])){
		if($_GET['status_id'.$prefix]>0){
			$decorator->AddEntry(new SqlEntry('p.status_id',abs((int)$_GET['status_id'.$prefix]), SqlEntry::E));
		}
		$decorator->AddEntry(new UriEntry('status_id',$_GET['status_id'.$prefix]));
	}else{
	
	  if(isset($_COOKIE['pay_status_id'.$prefix])){
			  $status_id=$_COOKIE['pay_status_id'.$prefix];
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

	if(isset($_GET['given_no'.$prefix])&&(strlen($_GET['given_no'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no'.$prefix]));
	}
	
	
	if(isset($_GET['value'.$prefix])&&(strlen($_GET['value'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.value',SecStr($_GET['value'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('value',$_GET['value'.$prefix]));
	}
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
	
	
	
	
	if(!isset($_GET['given_pdate1'.$prefix])){
	
			$_given_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
			
		
	}else{
		 $given_pdate1 = $_GET['given_pdate1'.$prefix];
		 $_given_pdate1= DateFromdmY($_GET['given_pdate1'.$prefix]);
	}
	
	
	
	if(!isset($_GET['given_pdate2'.$prefix])){
			
			$_given_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
			
			$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
	}else{
		 $given_pdate2 = $_GET['given_pdate2'.$prefix];
		  $_given_pdate2= DateFromdmY($_GET['given_pdate2'.$prefix]);
	}
	
	if(isset($_GET['given_pdate1'.$prefix])&&isset($_GET['given_pdate2'.$prefix])&&($_GET['given_pdate2'.$prefix]!="")&&($_GET['given_pdate2'.$prefix]!="-")&&($_GET['given_pdate1'.$prefix]!="")&&($_GET['given_pdate1'.$prefix]!="-")){
		
		$decorator->AddEntry(new UriEntry('given_pdate1',$given_pdate1));
		$decorator->AddEntry(new UriEntry('given_pdate2',$given_pdate2));
		$decorator->AddEntry(new SqlEntry('p.given_pdate', $_given_pdate1, SqlEntry::BETWEEN,$_given_pdate2 ));
	}else{
				$decorator->AddEntry(new UriEntry('given_pdate1',''));
			$decorator->AddEntry(new UriEntry('given_pdate2',''));
	}	
	
	
	
	$_pp->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('pay_in/all_pays_list.html', $decorator, 
	$au->user_rights->CheckAccess('w',683)||$au->user_rights->CheckAccess('w',693),  
	$au->user_rights->CheckAccess('w',691),$from, $to_page,
	$au->user_rights->CheckAccess('w',689),  
	$au->user_rights->CheckAccess('w',96),true,false, 
	$au->user_rights->CheckAccess('w',692), 
	$au->user_rights->CheckAccess('w',690));
			
	$sm->assign('log2',$ships); 
	

	*/
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlOrdEntry('begin_pdate',SqlOrdEntry::ASC));
	

	
	$_cp=new CashPercentGroup;
	
	$_cp->SetAuthResult($result);
	$ships=$_cp->ShowAllPos('cash/percents_list.html',  $decorator, $some);


	

	$sm->assign('log2',$ships); 
	
	
	
	$content=$sm->fetch('cash/cash_percent_page.html');
	

	
	
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