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

require_once('classes/posonstor.php');
require_once('classes/posonsec.php');
require_once('classes/posonas_mod.php');
require_once('classes/original.php');
require_once('classes/original_incoming.php');

require_once('classes/original_dog.php');


require_once('classes/supplier_to_user.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Оригиналы документов');

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



if($print!=0){
	if(!$au->user_rights->CheckAccess('w',353)&&!$au->user_rights->CheckAccess('w',488)&&!$au->user_rights->CheckAccess('w',867)){
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


if(!$au->user_rights->CheckAccess('w',352)&&!$au->user_rights->CheckAccess('w',487)&&!$au->user_rights->CheckAccess('w',866)){
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

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

	$_menu_id=40;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	
	//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчеты Оригиналы документов',NULL,352,NULL,NULL);	
	

/*********************вкладка оригиналы отгруз докум - входящие********************************************/
	
	
	
	$sm=new SmartyAdm;
	
	
	$as=new OriginalIncoming;
	$prefix=$as->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';


	
	
	//документы
	if(isset($_GET['mode'.$prefix])&&((int)($_GET['mode'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('mode',$_GET['mode'.$prefix]));
		//
		
		$mode=array();
		$mode[]=abs((int)$_GET['mode'.$prefix]);
		
		
	}else{
		$mode=array(1,2,3);
	}
	
	
	//поставщик
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.id',NULL, SqlEntry::IN_VALUES, NULL, explode(';',SecStr($_GET['supplier_name'.$prefix]))));//SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);

	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::ASC));
		break;
		
		/*case 4:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;*/
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
		break;
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$prefix));
	
	//var_dump(isset($_GET['doSub'])||isset($_GET['doSub_x']));
	
	
	$filetext=$as->ShowData(NULL,NULL,$mode, $result['org_id'],$decorator,'original/original'.$print_add.'.html','original.php', $au->user_rights->CheckAccess('w',867), isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1), $limited_supplier);
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log',$filetext);
	
	$sm->assign('has_1',$au->user_rights->CheckAccess('w',866));
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Оригиналы отгрузочных документов - Входящие',NULL,866,NULL, NULL);	
	}



/*********************вкладка оригиналы отгруз докум - ИСХОДЯЩИЕ********************************************/
	
	
	
	
	
	
	$as=new Original;
	$prefix=$as->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';


	
	
	//документы
	if(isset($_GET['mode'.$prefix])&&((int)($_GET['mode'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('mode',$_GET['mode'.$prefix]));
		//
		
		$mode=array();
		$mode[]=abs((int)$_GET['mode'.$prefix]);
		
		
	}else{
		$mode=array(1,3);
	}
	
	
	//поставщик
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.id',NULL, SqlEntry::IN_VALUES, NULL, explode(';',SecStr($_GET['supplier_name'.$prefix]))));//SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
	}
	
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);

	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('sp.full_name',SqlOrdEntry::ASC));
		break;
		
		/*case 4:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;*/
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::DESC));
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('p.given_pdate',SqlOrdEntry::ASC));
		break;
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$prefix));
	
	//var_dump(isset($_GET['doSub'])||isset($_GET['doSub_x']));
	 
	
	$filetext=$as->ShowData(NULL,NULL,$mode, $result['org_id'],$decorator,'original/original'.$print_add.'.html','original.php', $au->user_rights->CheckAccess('w',353),isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1), $limited_supplier);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log2',$filetext);
	
	$sm->assign('has_3',$au->user_rights->CheckAccess('w',352));
	


//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Оригиналы отгрузочных документов - Исходящие',NULL,352,NULL, NULL);	
	}
















	

/*********************вкладка оригиналы договоров и учредит докум********************************************/	
	
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	
	$as=new OriginalDog;
	$prefix=$as->prefix;
	
	//документы
	
	
	
	
	if(isset($_GET['has_no_dog'.$prefix])){
		
		$has_no_dog3=1;
		
		$decorator->AddEntry(new UriEntry('has_no_dog',1));		
	}elseif(!(isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1))){
		$decorator->AddEntry(new UriEntry('has_no_dog',1));
		//$has_no_dog3=0;		
	}
	
	
	
	if(isset($_GET['has_no_dog_in'.$prefix])){
		
		$has_no_dog_in3=1;
		
		$decorator->AddEntry(new UriEntry('has_no_dog_in',1));		
	}elseif(!(isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1))){
		$decorator->AddEntry(new UriEntry('has_no_dog_in',1));
		//$has_no_dog3=0;		
	}
	
	
	
	
	if(isset($_GET['has_no_uch'.$prefix])){
		
		$has_no_uch3=1;
		
		$decorator->AddEntry(new SqlEntry('p.has_uch',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('has_no_uch',1));		
	}elseif(!(isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1))){
		$decorator->AddEntry(new UriEntry('has_no_uch',1));
	}
	
	//поставщик
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
	
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]));
			
		}else{
			 $supplier_name=SecStr($_GET['supplier_name'.$prefix]);
			
		}
		$decorator->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::IN_VALUES, NULL, explode(';',$supplier_name)));//SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$supplier_name));
	}
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);

	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$prefix));
	
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	
	$filetext=$as->ShowData($has_dog3, $has_uch3, $has_dog_in3, $has_no_dog3, $has_no_uch3, $has_no_dog_in3,  $decorator, 'original/original_dog'.$print_add.'.html','original.php', $au->user_rights->CheckAccess('w',488),  isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1), $limited_supplier);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log3',$filetext);
	
	
	
	$sm->assign('has_2',$au->user_rights->CheckAccess('w',487));
	
	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Оригиналы оригиналы договоров и учредительных документов',NULL,487,NULL, NULL);	
	}
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('original/orignal_form'.$print_add.'.html');
	
	
	
	
	
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