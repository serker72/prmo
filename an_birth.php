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


require_once('classes/an_birth.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Дни рождения');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=1;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);


 

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',912)&&!$au->user_rights->CheckAccess('w',913)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);





$log=new ActionLog;
 $log->PutEntry($result['id'],'перешел в Отчет Дни рождения',NULL,912,NULL,NULL);
 
//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
 unset($smarty);

	
	$_menu_id=68;

	if($print==0) include('inc/menu.php');
	
	

	 
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	 
	
	
	$sm=new SmartyAdm;
	
	
	 
	//Вкладка
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	 $prefix='';
	$decorator=new DBDecorator; 
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	 
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("01.01.Y"));
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("31.12.Y"));
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	

	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users=explode(';', $_GET['user'.$prefix]);
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));

		
	}else $_users=NULL;
	 
	//фильтр по контрагенту
	if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier'.$prefix]);
		$decorator->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		
		//поиск по субхолдингам - передаем его в отчет через UriEntry
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
		}
		

		
	}else $_suppliers=NULL;
	 
	 
	//учитывать год рождения
	if(isset($_GET['check_year'.$prefix])&&(strlen($_GET['check_year'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('check_year',  1));
	}
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	$supplier_flt='';
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		 	
	}
	
	//ограничения по сотруднику
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		 
	}
	
	
	
	$as1=new AnBirth;
	
	$filetext=$as1->ShowData($result['org_id'], $_suppliers, $_users,  $limited_user,  $limited_supplier,  $pdate1, $pdate2, 'an_birth/an_birth'.$prefix.$print_add.'.html',$decorator,'an_birth.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,   $au->user_rights->CheckAccess('w',913),  $au->user_rights->CheckAccess('w',909), $au->user_rights->CheckAccess('w',11), $au->user_rights->CheckAccess('w',87),     $alls1, $alls2, $result);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	//var_dump($alls);
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if(isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Дни рождения',NULL,913,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Дни рождения',NULL,912,NULL, NULL);	
	}
	 
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('an_birth/an_birth_form'.$print_add.'.html');
	
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
 	else {
		
		//echo $content;
		
		$sm2=new SmartyAdm;
		
		$content=$sm2->fetch('plan_pdf/pdf_header.html').$content;
		
		
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		
		//скомпилируем подвал
		$sm=new SmartyAdm;
		$sm->assign('print_pdate', date("d.m.Y H:i:s"));
			//$username=$result['login'];
			$username=stripslashes($result['name_s']); //.' '.$username;	
			$sm->assign('print_username',$username);
		$foot=$sm->fetch('plan_pdf/pdf_footer.html');
		$ftmp='f'.time();
		
		$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
		fputs($f, $foot);
		fclose($f);
		
		
		//if( isset($_GET['doSub6'])||isset($_GET['doSub6_x'])){
			//$orient='--orientation Landscape ';
		//}else $orient='--orientation Portrait';
		 $orient='--orientation Portrait';
		
		//$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 ".$orient." --margin-top 73mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html --header-html ".SITEURL."/tpl-sm/plan_pdf/pdf_header.html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
		$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 ".$orient."  --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html   ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
	 

	 
	
		exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Отчет_Дни_рождения.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	 
		
	
	
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
		
		exit;
		
	}
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
 
unset($smarty);
?>