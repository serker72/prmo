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


require_once('classes/an_onway.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Товары в пути');

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
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);


if($print!=0){
	if(!$au->user_rights->CheckAccess('w',362)){
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



if(!$au->user_rights->CheckAccess('w',361)){
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



	if($print==0) include('inc/menu.php');
	
	
	$decorator=new DBDecorator;
	
	
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	
	if(!isset($_GET['pdate_shipping_plan1'])){
	
		
			
			$pdate_shipping_plan1=date('d.m.Y',0);
			$pdate_shipping_plan_decor1='-';
		
	}else{
		 if(($_GET['pdate_shipping_plan1']=='-')||($_GET['pdate_shipping_plan1']=='')){
			 $pdate_shipping_plan1=date('d.m.Y',0);
		 }else {
			 $pdate_shipping_plan1 = $_GET['pdate_shipping_plan1'];
			 
		 }
		 $pdate_shipping_plan_decor1=$_GET['pdate_shipping_plan1'];
		 
	}
	

	
	if(!isset($_GET['pdate_shipping_plan2'])){
	
			
			$pdate_shipping_plan2='31.12.2030';
			$pdate_shipping_plan_decor2='-';
		
	}else{
		 if(($_GET['pdate_shipping_plan2']=='-')||($_GET['pdate_shipping_plan2']=='')){
			 $pdate_shipping_plan2='31.12.2030';
		 }else {
			 $pdate_shipping_plan2 = $_GET['pdate_shipping_plan2'];
			 
		 }
		 $pdate_shipping_plan_decor2=$_GET['pdate_shipping_plan2'];
		 
	}
	
	
	
	$decorator->AddEntry(new UriEntry('pdate_shipping_plan1',$pdate_shipping_plan_decor1));
	$decorator->AddEntry(new UriEntry('pdate_shipping_plan2',$pdate_shipping_plan_decor2));
	
	//echo $pdate_shipping_plan1.'q<br />';
	//echo $pdate_shipping_plan2.'w<br />';
	

	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_shipping_plan',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_shipping_plan',SqlOrdEntry::DESC));
		break;
		
		/*case 2:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;*/
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('manager_name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('manager_login',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('manager_name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('manager_login',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
	
	
	
	//поиск по позициям
	$dec2=new DBDecorator;
	
	if(isset($_GET['id2'])&&(strlen($_GET['id2'])>0)){
		$dec2->AddEntry(new SqlEntry('p.position_id',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['id2']))));
		$dec2->AddEntry(new UriEntry('id2',$_GET['id2']));
	}
	
	if(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('group_id',abs((int)$_GET['group_id'])));
		
	}
	
	
	if(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('two_group_id',abs((int)$_GET['two_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('three_group_id',abs((int)$_GET['three_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('cg.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		
		//найти подгруппы
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec2->AddEntry(new SqlEntry('cg.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
	}elseif(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('cg.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		
		
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec2->AddEntry(new SqlEntry('cg.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_GET['name2'])&&(strlen($_GET['name2'])>0)){
		$dec2->AddEntry(new SqlEntry('p.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['name2']))));//SqlEntry::LIKE));
		$dec2->AddEntry(new UriEntry('name2',$_GET['name2']));
	}
	
	if(isset($_GET['gost_tu2'])&&(strlen($_GET['gost_tu2'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.gost_tu',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['gost_tu2']))));
		$dec2->AddEntry(new UriEntry('gost_tu2',$_GET['gost_tu2']));
	}
	
	
	
	if(isset($_GET['length2'])&&(strlen($_GET['length2'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.length',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['length2']))));
		$dec2->AddEntry(new UriEntry('length2',$_GET['length2']));
	}
	
	if(isset($_GET['width2'])&&(strlen($_GET['width2'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.width',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['width2']))));
		$dec2->AddEntry(new UriEntry('width2',$_GET['width2']));
	}
	
	if(isset($_GET['height2'])&&(strlen($_GET['height2'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.height',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['height2']))));
		$dec2->AddEntry(new UriEntry('height2',$_GET['height2']));
	}
	
	if(isset($_GET['diametr2'])&&(strlen($_GET['diametr2'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.diametr',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['diametr2']))));
		$dec2->AddEntry(new UriEntry('diametr2',$_GET['diametr2']));
	}
	
	
	
	
	if(isset($_GET['dimension_id2'])&&(strlen($_GET['dimension_id2'])>0)){
		$dec2->AddEntry(new SqlEntry('cg.dimension_id',abs((int)$_GET['dimension_id2']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('dimension_id2',$_GET['dimension_id2']));
	}
	
	
	
	$as=new AnOnway;
	$filetext=$as->ShowData(DateFromdmY($pdate_shipping_plan1), DateFromdmY($pdate_shipping_plan2), $result['org_id'],$decorator,'an_onway/an_onway'.$print_add.'.html','an_onway.php', $au->user_rights->CheckAccess('w',362),isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1),$dec2);
	
	
	$sm->assign('log',$filetext);
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('an_onway/an_onway_form'.$print_add.'.html');
	
	
	
	
	
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