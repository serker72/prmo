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
require_once('classes/fileitem.php');
require_once('classes/filegroup.php');


require_once('classes/an_pm.php');

require_once('classes/supplier_to_user.php');




$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',363)&&!$au->user_rights->CheckAccess('w',546)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//дополнительная авторизация в разделе...
$semi_auth_session_name='an_pm';
require_once('inc/semi_auth_js.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет +/-');

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);

if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',364)&&!$au->user_rights->CheckAccess('w',547)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

$log->PutEntry($result['id'],'перешел в Отчеты +/-',NULL,363,NULL,NULL);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);

	$_menu_id=23;

	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	
	
	// вкладка Не выдано
	$decorator=new DBDecorator;
	
	
	if(!isset($_GET['pdate1'])){
	
			
			$pdate1=date('d.m.Y',0);
			$pdate_decor1='-';
		
	}else{
		 if(($_GET['pdate1']=='-')||($_GET['pdate1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate2'])){
	
			
			$pdate2='31.12.2030';
			$pdate_decor2='-';
		
	}else{
		 if(($_GET['pdate2']=='-')||($_GET['pdate2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate2'];
		 
	}
	
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate_decor2));
	
	
	if(isset($_GET['only_payed'])&&($_GET['only_payed']==1)){
		$only_payed=1;
		$decorator->AddEntry(new UriEntry('only_payed',1));
	}elseif(isset($_GET['only_payed'])&&($_GET['only_payed']==0)){
		$only_payed=0;
		$decorator->AddEntry(new UriEntry('only_payed',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub'])||isset($_GET['doSub_x']))) {
		
			$only_payed=0;
			$decorator->AddEntry(new UriEntry('only_payed',0));
	
		}else{
			$only_payed=1;
			$decorator->AddEntry(new UriEntry('only_payed',1));
		}
	
	
	}
	
	if(isset($_GET['only_not_payed'])&&($_GET['only_not_payed']==1)){
		$only_not_payed=1;
		$decorator->AddEntry(new UriEntry('only_not_payed',1));
	}elseif(isset($_GET['only_not_payed'])&&($_GET['only_not_payed']==0)){
		$only_not_payed=0;
		$decorator->AddEntry(new UriEntry('only_not_payed',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub'])||isset($_GET['doSub_x']))) {
		
			$only_not_payed=0;
			$decorator->AddEntry(new UriEntry('only_not_payed',0));
	
		}else{
			$only_not_payed=1;
			$decorator->AddEntry(new UriEntry('only_not_payed',1));
		}
	
	
	}
	
	if(isset($_GET['only_semi_payed'])&&($_GET['only_semi_payed']==1)){
		$only_semi_payed=1;
		$decorator->AddEntry(new UriEntry('only_semi_payed',1));
	}elseif(isset($_GET['only_semi_payed'])&&($_GET['only_semi_payed']==0)){
		$only_semi_payed=0;
		$decorator->AddEntry(new UriEntry('only_semi_payed',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub'])||isset($_GET['doSub_x']))) {
		
			$only_semi_payed=0;
			$decorator->AddEntry(new UriEntry('only_semi_payed',0));
	
		}else{
			$only_semi_payed=1;
			$decorator->AddEntry(new UriEntry('only_semi_payed',1));
		}
	
	
	}
	
	
	
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name']));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name']);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		}
	}else $supplier_name='';
	
	
	if(isset($_GET['given_no'])&&(strlen($_GET['given_no'])>0)){
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no']));
		$given_no=SecStr($_GET['given_no']);
	}else $given_no=NULL;
	
	
	if(isset($_GET['supplier_bill_no'])&&(strlen($_GET['supplier_bill_no'])>0)){
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no']));
		$decorator->AddEntry(new SqlEntry('b.supplier_bill_no',SecStr($_GET['supplier_bill_no']), SqlEntry::LIKE));
		
		$supplier_bill_no=SecStr($_GET['supplier_bill_no']);
	}else $supplier_bill_no='';
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::DESC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('b.pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('b.pdate',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::DESC));
			
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::ASC));
			
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('b.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('b.id',SqlOrdEntry::ASC));
	
	
	$as=new AnPm;
	
	$filetext=$as->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2),'an_pm/an_pm_list'.$print_add.'.html',$decorator,'an_pm.php',isset($_GET['doSub'])||isset($_GET['doSub_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)),$au->user_rights->CheckAccess('w',364)||$au->user_rights->CheckAccess('w',547),' ',$alls,true,$only_payed, $only_semi_payed,$only_not_payed, $given_no, $au->user_rights->CheckAccess('w',363),$limited_supplier );
	
	//$filetext='<em>Раздел находится в разработке.</em>';
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log',$filetext);
	
	
//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		$log->PutEntry($result['id'],'открыл отчет Отчет +/-: не выдано',NULL,363,NULL, NULL);	
	}
	
	
	
//*********************************************************************************************


// вкладка Выдано
	$decorator=new DBDecorator;
	
	
	if(!isset($_GET['pdate2_1'])){
	
			
			$pdate1=date('d.m.Y',0);
			$pdate_decor1='-';
		
	}else{
		 if(($_GET['pdate2_1']=='-')||($_GET['pdate2_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate2_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate2_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate2_2'])){
	
			
			$pdate2='31.12.2030';
			$pdate_decor2='-';
		
	}else{
		 if(($_GET['pdate2_2']=='-')||($_GET['pdate2_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate2_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate2_2'];
		 
	}
	
	
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate_decor2));
	
	
	if(isset($_GET['only_payed2'])&&($_GET['only_payed2']==1)){
		$only_payed2=1;
		$decorator->AddEntry(new UriEntry('only_payed2',1));
	}elseif(isset($_GET['only_payed2'])&&($_GET['only_payed2']==0)){
		$only_payed2=0;
		$decorator->AddEntry(new UriEntry('only_payed2',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub2'])||isset($_GET['doSub2_x']))) {
		
			$only_payed2=0;
			$decorator->AddEntry(new UriEntry('only_payed2',0));
	
		}else{
			$only_payed2=1;
			$decorator->AddEntry(new UriEntry('only_payed2',1));
		}
	
	
	}
	
	
	if(isset($_GET['only_not_payed2'])&&($_GET['only_not_payed2']==1)){
		$only_not_payed2=1;
		$decorator->AddEntry(new UriEntry('only_not_payed2',1));
	}elseif(isset($_GET['only_not_payed2'])&&($_GET['only_not_payed2']==0)){
		$only_not_payed2=0;
		$decorator->AddEntry(new UriEntry('only_not_payed2',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub2'])||isset($_GET['doSub2_x']))) {
		
			$only_not_payed2=0;
			$decorator->AddEntry(new UriEntry('only_not_payed2',0));
	
		}else{
			$only_not_payed2=1;
			$decorator->AddEntry(new UriEntry('only_not_payed2',1));
		}
	
	
	}
	
	if(isset($_GET['only_semi_payed2'])&&($_GET['only_semi_payed2']==1)){
		$only_semi_payed2=1;
		$decorator->AddEntry(new UriEntry('only_semi_payed2',1));
	}elseif(isset($_GET['only_semi_payed2'])&&($_GET['only_semi_payed2']==0)){
		$only_semi_payed2=0;
		$decorator->AddEntry(new UriEntry('only_semi_payed2',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub2'])||isset($_GET['doSub2_x']))) {
		
			$only_semi_payed2=0;
			$decorator->AddEntry(new UriEntry('only_semi_payed2',0));
	
		}else{
			$only_semi_payed2=1;
			$decorator->AddEntry(new UriEntry('only_semi_payed2',1));
		}
	
	
	}
	
	
	
	if(isset($_GET['supplier_name2'])&&(strlen($_GET['supplier_name2'])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name2']));
			 $decorator->AddEntry(new UriEntry('supplier_name2',iconv("utf-8","windows-1251",$_GET['supplier_name2'])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name2']);
			 $decorator->AddEntry(new UriEntry('supplier_name2',$_GET['supplier_name2']));
		}
	}else $supplier_name='';
	
	
	if(isset($_GET['given_no_2'])&&(strlen($_GET['given_no_2'])>0)){
		$decorator->AddEntry(new UriEntry('given_no_2',$_GET['given_no_2']));
		$given_no_2=SecStr($_GET['given_no_2']);
	}else $given_no_2=NULL;
	
	
	if(isset($_GET['supplier_bill_no_2'])&&(strlen($_GET['supplier_bill_no_2'])>0)){
		$decorator->AddEntry(new UriEntry('supplier_bill_no_2',$_GET['supplier_bill_no_2']));
		$decorator->AddEntry(new SqlEntry('b.supplier_bill_no',SecStr($_GET['supplier_bill_no_2']), SqlEntry::LIKE));
		
		$supplier_bill_no_2=SecStr($_GET['supplier_bill_no_2']);
	}else $supplier_bill_no_2='';
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	
	$decorator->AddEntry(new UriEntry('sortmode2',$sortmode2));
	
	switch($sortmode2){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::DESC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('b.pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('b.pdate',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::DESC));
			
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::ASC));
			
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('b.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('b.id',SqlOrdEntry::ASC));
	
	
	$as1=new AnPm;
	
	$filetext=$as1->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2),'an_pm/an_pm_given_list'.$print_add.'.html',$decorator,'an_pm.php', isset($_GET['doSub2'])||isset($_GET['doSub2_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3)),$au->user_rights->CheckAccess('w',364)||$au->user_rights->CheckAccess('w',547),DEC_SEP,$alls, false,$only_payed2, $only_semi_payed2, $only_not_payed2 , $given_no_2, $au->user_rights->CheckAccess('w',363),$limited_supplier);
	
	//$filetext='<em>Раздел находится в разработке.</em>';
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log2',$filetext);	
	
	
	//фиксировать открытие отчета
	if(  isset($_GET['doSub2'])||isset($_GET['doSub2_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3))){
		$log->PutEntry($result['id'],'открыл отчет Отчет +/-: выдано',NULL,546,NULL, NULL);	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_pm/an_pm_form'.$print_add.'.html');
	
	
	
	
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