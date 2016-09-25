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

require_once('classes/orgitem.php');
require_once('classes/an_rent.php');
require_once('classes/period_checker.php');






$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',719)&&!$au->user_rights->CheckAccess('w',720)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//дополнительная авторизация в разделе...
$semi_auth_session_name='an_rent';
require_once('inc/semi_auth_js.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Рентабельность бизнеса');


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',720)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

$log->PutEntry($result['id'],'перешел в Отчет Рентабельность бизнеса',NULL,719,NULL,NULL);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=56;
	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	//декоратор используем для многостраничности (если понадобится)
	$decorator=new DBDecorator;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=  mktime(0,0,0,date('m'),1,date('Y'));  //DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2= mktime(0,0,0,(int)date('m')+1,0,date('Y'));   //DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	
	

	//var_dump($supplier_name);
	
	if(isset($_GET['extended_an'])&&($_GET['extended_an']==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	
	/*
	
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
		$template='an_rent/an_rent_list.html';	
	}else{
		
		$template='an_rent/an_rent_list'.$print_add.'.html';	
		
	}
	
	$_org=new OrgItem;
	$org=$_org->getitembyid($result['org_id']);
	$_pch=new PeriodChecker;
	
	$as=new AnRent($org['program_begin_ost'], $_pch->GetDate(), $result);
	$filetext=$as->ShowData(DateFromdmY($pdate1), DateFromdmY($pdate2), $extended_an, $result['org_id'], $template,$decorator,'an_rent.php', isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1),$au->user_rights->CheckAccess('w',720), DEC_SEP );
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log',$filetext);
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Рентабельность бизнеса',NULL,719,NULL, NULL);	
	}
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	
	$content=$sm->fetch('an_rent/an_rent_form'.$print_add.'.html');
	
	
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