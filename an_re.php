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


require_once('classes/an_re.php');


require_once('classes/supplier_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Рентабельность сделок');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',853)&&!$au->user_rights->CheckAccess('w',854)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',854)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

$log->PutEntry($result['id'],'перешел в Отчет РЕ',NULL,853,NULL,NULL);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);

	
	$_menu_id=64;
	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//декоратор используем для многостраничности (если понадобится)
	$decorator=new DBDecorator;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	if(!isset($_GET['pdate1'])){
			//с 1го числа 
			$_pdate1= DateFromdmY('01.'.date('m').'.'.date('Y'));  //DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			//по сегодня!!!
			$_pdate2=DateFromdmY(date("d.m.Y")); //+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		
		/*if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name']));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'])));
		}else{*/
			 $supplier_name=SecStr($_GET['supplier_name']);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		//}
	}else $supplier_name='';
	
	$decorator->AddEntry(new SqlEntry('b.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	

	//var_dump($supplier_name);
	
	/*if(isset($_GET['extended_an'])&&($_GET['extended_an']==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	
	
	
	if(isset($_GET['by_org'])&&($_GET['by_org']==1)){
		$by_org=1;
		$decorator->AddEntry(new UriEntry('by_org',1));	
			
	}else{
		$by_org=0;
		$decorator->AddEntry(new UriEntry('by_org',0));	
	}
	*/
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	if($print==0){
		$template='an_re/an_re_list.html';	
	}else{
		
		$template='an_re/an_re_list'.$print_add.'.html';	
		
	}
	
	
	
	
	if(!isset($_GET['sortmode'])){
		if(!isset($_POST['sortmode'])){
			$sortmode=-1;
		}else $sortmode=((int)$_POST['sortmode']); 
	}else $sortmode=((int)$_GET['sortmode']);

	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('name',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('name',SqlOrdEntry::ASC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('dimension',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('dimension',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('out_ap_quantity',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('out_ap_quantity',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('in_price_pm',SqlOrdEntry::DESC)); //составное!
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('in_price_pm',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('in_ap_total',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('in_ap_total',SqlOrdEntry::ASC));
		break;
		
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('in_supplier_name',SqlOrdEntry::DESC)); //sostavnoe
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('in_supplier_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('in_given_no',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('in_given_no',SqlOrdEntry::ASC));
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('in_given_pdate',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('in_given_pdate',SqlOrdEntry::ASC));
		break;
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('price',SqlOrdEntry::DESC));
		break;	
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('price',SqlOrdEntry::ASC));
		break;
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('price_pm',SqlOrdEntry::DESC));
		break;	
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('price_pm',SqlOrdEntry::ASC));
		break;
		case 20:
			$decorator->AddEntry(new SqlOrdEntry('total',SqlOrdEntry::DESC));
		break;	
		case 21:
			$decorator->AddEntry(new SqlOrdEntry('total',SqlOrdEntry::ASC));
		break;
		
		
		case 22:
			$decorator->AddEntry(new SqlOrdEntry('out_supplier_name',SqlOrdEntry::DESC)); //sostavnoe!!!
		break;
		case 23:
			$decorator->AddEntry(new SqlOrdEntry('out_supplier_name',SqlOrdEntry::ASC));
		break;
		
		case 24:
			$decorator->AddEntry(new SqlOrdEntry('supplier_bill_no',SqlOrdEntry::DESC));
		break;	
		case 25:
			$decorator->AddEntry(new SqlOrdEntry('supplier_bill_no',SqlOrdEntry::ASC));
		break;
		case 26:
			$decorator->AddEntry(new SqlOrdEntry('out_given_no',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 27:
			$decorator->AddEntry(new SqlOrdEntry('out_given_no',SqlOrdEntry::ASC));
		break;
		case 28:
			$decorator->AddEntry(new SqlOrdEntry('out_given_pdate',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 29:
			$decorator->AddEntry(new SqlOrdEntry('out_given_pdate',SqlOrdEntry::ASC));
		break;
		case 30:
			$decorator->AddEntry(new SqlOrdEntry('sum_2',SqlOrdEntry::DESC));
		break;	
		case 31:
			$decorator->AddEntry(new SqlOrdEntry('sum_2',SqlOrdEntry::ASC));
		break;
		
		case 32:
			$decorator->AddEntry(new SqlOrdEntry('sum_3',SqlOrdEntry::DESC));
		break;
		case 33:
			$decorator->AddEntry(new SqlOrdEntry('sum_3',SqlOrdEntry::ASC));
		break;
		
		case 34:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 35:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 36:
			//$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 37:
			//$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 38:
			$decorator->AddEntry(new SqlOrdEntry('percent_percent',SqlOrdEntry::DESC));
		break;	
		case 39:
			$decorator->AddEntry(new SqlOrdEntry('percent_percent',SqlOrdEntry::ASC));
		break;
		case 40:
			$decorator->AddEntry(new SqlOrdEntry('cash',SqlOrdEntry::DESC));
		break;	
		case 41:
			$decorator->AddEntry(new SqlOrdEntry('cash',SqlOrdEntry::ASC));
		break;
		
		
		case 42:
			$decorator->AddEntry(new SqlOrdEntry('pm_res',SqlOrdEntry::DESC));
		break;
		case 43:
			$decorator->AddEntry(new SqlOrdEntry('pm_res',SqlOrdEntry::ASC));
		break;
		
		case 44:
			$decorator->AddEntry(new SqlOrdEntry('pm_to_give',SqlOrdEntry::DESC));
		break;	
		case 45:
			$decorator->AddEntry(new SqlOrdEntry('pm_to_give',SqlOrdEntry::ASC));
		break;
		case 46:
			$decorator->AddEntry(new SqlOrdEntry('pm_given',SqlOrdEntry::DESC));
		break;	
		case 47:
			$decorator->AddEntry(new SqlOrdEntry('pm_given',SqlOrdEntry::ASC));
		break;
		case 48:
			$decorator->AddEntry(new SqlOrdEntry('pribyl',SqlOrdEntry::DESC));
		break;	
		case 49:
			$decorator->AddEntry(new SqlOrdEntry('pribyl',SqlOrdEntry::ASC));
		break;
	
		
		
		default:
		//	$decorator->AddEntry(new SqlOrdEntry('name',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	
	
	
	
	
	$as=new AnRe;
	$filetext=$as->ShowData(DateFromdmY($pdate1), DateFromdmY($pdate2),$supplier_name, $template,$decorator, 'an_re.php', isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1),  $au->user_rights->CheckAccess('w',854), DEC_SEP, $alls, $limited_supplier);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log',$filetext);
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет РЕ',NULL,853,NULL, NULL);	
	}
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	
	$content=$sm->fetch('an_re/an_re_form'.$print_add.'.html');
	
	
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