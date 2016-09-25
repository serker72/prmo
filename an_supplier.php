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
/*require_once('classes/fileitem.php');
require_once('classes/filegroup.php');
*/
require_once('classes/an_supplier.php');

require_once('classes/supplier_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'¬едомость по контрагентам');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',111)&&!$au->user_rights->CheckAccess('w',358)){
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
	if(!$au->user_rights->CheckAccess('w',358)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
elseif(($print!=0)&&!isset($_GET['do_print_akt'])) $smarty->display('top_print.html');
unset($smarty);

	
	$_menu_id=21;
	
	if($print==0) include('inc/menu.php');
	
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//ограничени€ по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	
	//декоратор используем дл€ многостраничности (если понадобитс€)
	$decorator=new DBDecorator;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name']));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name']);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		}
	}else $supplier_name='';

	//var_dump($supplier_name);
	
	if(isset($_GET['extended_an'])&&($_GET['extended_an']==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	if(isset($_GET['similar_firms'])&&($_GET['similar_firms']==1)){
		$similar_firms=1;
		$decorator->AddEntry(new UriEntry('similar_firms',1));	
			
	}else{
		$similar_firms=0;
		$decorator->AddEntry(new UriEntry('similar_firms',0));	
	}
	
	
	if(isset($_GET['by_contract'])&&($_GET['by_contract']==1)){
		$by_contract=1;
		$decorator->AddEntry(new UriEntry('by_contract',1));	
			
	}else{
		$by_contract=0;
		$decorator->AddEntry(new UriEntry('by_contract',0));	
	}
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	if($print==0){
		$template='an_supplier/an_supplier_list.html';	
	}else{
		if(isset($_GET['do_print_ved'])){
			$template='an_supplier/an_supplier_list'.$print_add.'.html';
		}elseif(isset($_GET['do_print_akt'])){
			$template='an_supplier/an_supplier_list_sverka.html';
		}else{
			$template='an_supplier/an_supplier_list'.$print_add.'.html';	
		}
	}
	
	
	$as=new AnSupplier;
	$filetext=$as->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $extended_an, $template,$decorator,'an_supplier.php', isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1),$au->user_rights->CheckAccess('w',358), DEC_SEP, $similar_firms, $by_contract, $limited_supplier, $au->user_rights->CheckAccess('w',877), $au->user_rights->CheckAccess('w',880)  );
	
	//$filetext='<em>»звините, у ¬ас нет прав дл€ доступа в этот раздел.</em>';
	
	$sm->assign('log',$filetext);
	
	
	//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчет ¬едомость по контрагенту',NULL,111,NULL,NULL);	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет ¬едомость по контрагенту',NULL,111,NULL, NULL);	
	}
	
	
	//общие пол€
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	
	$content=$sm->fetch('an_supplier/an_supplier_form'.$print_add.'.html');
	
	
	if(($print!=0)&&isset($_GET['do_print_akt'])){
	
		//echo $content; die();
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		$comand = "wkhtmltopdf-i386 --page-size A4 --orientation Portrait --encoding windows-1251 --image-quality 100 --margin-top 5mm --margin-bottom 5mm --margin-left 10mm --margin-right 10mm  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
 		 
	 
		exec($comand);
	
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="јкт_сверки.pdf"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf'); 
		
	//	readfile(ABSPATH.'/tmp/'.$tmp.'.html');
		
		
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	
		
		exit();	
	}
	
	
	
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