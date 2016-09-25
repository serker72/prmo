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


require_once('classes/posonas_mod.php');

//require_once('classes/posonas_money.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Товары на объектах');

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

if(!isset($_GET['print2'])){
	if(!isset($_POST['print2'])){
		$print2=0;
	}else $print2=abs((int)$_POST['print2']); 
}else $print2=abs((int)$_GET['print2']);


if($print!=0){
	if(!$au->user_rights->CheckAccess('w',288)&&!$au->user_rights->CheckAccess('w',515)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=2;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);


if(!isset($_GET['sortmode4'])){
	if(!isset($_POST['sortmode4'])){
		$sortmode4=3;
	}else $sortmode4=abs((int)$_POST['sortmode4']); 
}else $sortmode4=abs((int)$_GET['sortmode4']);


$log=new ActionLog;

if(!$au->user_rights->CheckAccess('w',135)&&!$au->user_rights->CheckAccess('w',514)){
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

	$_menu_id=37;

	if($print==0) include('inc/menu.php');
	
	

	
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	$sm=new SmartyAdm;
	
	
	//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчет Товары на объектах',NULL,135,NULL,NULL);	
	
	
		
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	//Вкладка Выбор по ассортименту
	$decorator=new DBDecorator;
	
	

	if(!isset($_GET['pdate2_1'])){
	
			$_pdate2_1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
			$pdate2_1=date("d.m.Y", $_pdate2_1);//"01.01.2006";
		
	}else $pdate2_1 = $_GET['pdate2_1'];
	
	
	
	if(!isset($_GET['pdate2_2'])){
			
			$_pdate2_2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2_2=date("d.m.Y", $_pdate2_2);//"01.01.2006";	
	}else $pdate2_2 = $_GET['pdate2_2'];
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate2_1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate2_2));
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print2']));
	$decorator->AddEntry(new UriEntry('tab_page',2));
	
	
	
	
	
	if(isset($_GET['only_period_2'])&&(abs((int)$_GET['only_period_2'])>0)){
		$only_period_2=1;
		$decorator->AddEntry(new UriEntry('only_period_2',1));
	}else{
		$only_period_2=0;
		$decorator->AddEntry(new UriEntry('only_period_2',0));
	}
	
	
	if(isset($_GET['id2'])&&(strlen($_GET['id2'])>0)){
		$decorator->AddEntry(new SqlEntry('pl.id',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['id2']))));
		$decorator->AddEntry(new UriEntry('id2',$_GET['id2']));
	}
	
	if(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		//$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('group_id',abs((int)$_GET['group_id'])));
	}
	
	
	if(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		//$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('two_group_id',abs((int)$_GET['two_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		//$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('three_group_id',abs((int)$_GET['three_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		
		//найти подгруппы
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
	}elseif(isset($_GET['group_id'])&&(strlen($_GET['group_id'])>0)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		
		
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
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_GET['name2'])&&(strlen($_GET['name2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['name2']))));//SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name2',$_GET['name2']));
	}
	
	if(isset($_GET['gost_tu2'])&&(strlen($_GET['gost_tu2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.gost_tu',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['gost_tu2']))));
		$decorator->AddEntry(new UriEntry('gost_tu2',$_GET['gost_tu2']));
	}
	
	
	
	if(isset($_GET['length2'])&&(strlen($_GET['length2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.length',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['length2']))));
		$decorator->AddEntry(new UriEntry('length2',$_GET['length2']));
	}
	
	if(isset($_GET['width2'])&&(strlen($_GET['width2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.width',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['width2']))));
		$decorator->AddEntry(new UriEntry('width2',$_GET['width2']));
	}
	
	if(isset($_GET['height2'])&&(strlen($_GET['height2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.height',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['height2']))));
		$decorator->AddEntry(new UriEntry('height2',$_GET['height2']));
	}
	
	if(isset($_GET['diametr2'])&&(strlen($_GET['diametr2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.diametr',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['diametr2']))));
		$decorator->AddEntry(new UriEntry('diametr2',$_GET['diametr2']));
	}
	
	
	
	
	if(isset($_GET['dimension_id2'])&&(strlen($_GET['dimension_id2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_GET['dimension_id2']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('dimension_id2',$_GET['dimension_id2']));
	}
	
	
	
	
	//участок
	if(isset($_GET['sector_id2'])&&((int)($_GET['sector_id2'])>0)){
		$decorator->AddEntry(new UriEntry('sector_id2',$_GET['sector_id2']));
		//
		
		$sector_id2=array();
		$sector_id2[]=abs((int)$_GET['sector_id2']);
		
		
	}else{
		$sector_id2=array();
	}
	
	
	$as=new PositionsOnAssortimentMod;
	$filetext2=$as->ShowData($pdate2_1,$pdate2_2, $sector_id2,  $result['org_id'],$decorator,'goods_on_as/goods_on_as'.$print_add.'.html','goods_on_stor.php',$au->user_rights->CheckAccess('w',288),$_alls, isset($_GET['doSub2'])||isset($_GET['doSub2_x'])||(isset($_GET['print'])&&($_GET['print']>0)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)),$only_period_2);
	
	//$filetext2='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	if(isset($_GET['doSub2'])||isset($_GET['doSub2_x'])||(isset($_GET['print'])&&($_GET['print']>0)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))){
		$log->PutEntry($result['id'],'открыл отчет Товары на объектах',NULL,354,NULL, NULL);	
	}
	
	$sm->assign('log2',$filetext2);
	
	
	
//*************************************************************************************************************************	
	//вкладка Деньги
	
	/*
	
	$decorator=new DBDecorator;
	
	

	if(!isset($_GET['pdate4_1'])){
	
			$_pdate4_1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
			$pdate4_1=date("d.m.Y", $_pdate4_1);//"01.01.2006";
		
	}else $pdate4_1 = $_GET['pdate4_1'];
	
	
	
	if(!isset($_GET['pdate4_2'])){
			
			$_pdate4_2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate4_2=date("d.m.Y", $_pdate4_2);//"01.01.2006";	
	}else $pdate4_2 = $_GET['pdate4_2'];
	
	$decorator->AddEntry(new UriEntry('pdate4_1',$pdate4_1));
	$decorator->AddEntry(new UriEntry('pdate4_2',$pdate4_2));
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print4']));
	$decorator->AddEntry(new UriEntry('tab_page',4));
	
	
	//объект
	if(isset($_GET['storage_id4'])&&((int)($_GET['storage_id4'])>0)){
		$decorator->AddEntry(new UriEntry('storage_id4',$_GET['storage_id4']));
		
		
			$storage_id4=array();
			$storage_id4[]=abs((int)$_GET['storage_id4']);
		
	}else{
		$storage_id4=array();
	}
	
	
	//участок
	if(isset($_GET['sector_id4'])&&((int)($_GET['sector_id4'])>0)){
		$decorator->AddEntry(new UriEntry('sector_id4',$_GET['sector_id4']));
		//
		
		$sector_id4=array();
		$sector_id4[]=abs((int)$_GET['sector_id4']);
		
		
	}else{
		$sector_id4=array();
	}
	
	if(isset($_GET['only_active_sectors_4'])&&($_GET['only_active_sectors_4']==1)){
		$only_active_sectors_4=1;
		$decorator->AddEntry(new UriEntry('only_active_sectors_4',1));
	}elseif(isset($_GET['only_active_sectors_4'])&&($_GET['only_active_sectors_4']==0)){
		$only_active_sectors_4=0;
		$decorator->AddEntry(new UriEntry('only_active_sectors_4',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub4'])||isset($_GET['doSub4_x']))) {
		
			$only_active_sectors_4=0;
			$decorator->AddEntry(new UriEntry('only_active_sectors_4',0));
	
		}else{
			$only_active_sectors_4=1;
			$decorator->AddEntry(new UriEntry('only_active_sectors_4',1));
		}
	
	
	}
	
	if(isset($_GET['only_active_storages_4'])&&($_GET['only_active_storages_4']==1)){
		$only_active_storages_4=1;
		$decorator->AddEntry(new UriEntry('only_active_storages_4',1));
	}elseif(isset($_GET['only_active_storages_4'])&&($_GET['only_active_storages_4']==1)){
		$only_active_storages_4=0;
		$decorator->AddEntry(new UriEntry('only_active_storages_4',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doSub4'])||isset($_GET['doSub4_x']))) {
		
			$only_active_storages_4=0;
			$decorator->AddEntry(new UriEntry('only_active_storages_4',0));
	
		}else{
			$only_active_storages_4=1;
			$decorator->AddEntry(new UriEntry('only_active_storages_4',1));
		}
	
	
	}
	
	
	$decorator->AddEntry(new UriEntry('sortmode4',$sortmode4));
	
	switch($sortmode4){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('a.given_pdate',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('a.given_pdate',SqlOrdEntry::ASC));
		break;
		
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	$as=new PositionsOnAssortimentMoney;
	$filetext4=$as->ShowData($pdate4_1,$pdate4_2, $storage_id4,$sector_id4, $result['org_id'],$decorator,'goods_on_money/goods_on_money'.$print_add.'.html','goods_on_stor.php',$limited_sector,  $au->user_rights->CheckAccess('w',515), $_alls,$_extended_limited_sector,  isset($_GET['doSub4'])||isset($_GET['doSub4_x'])||(isset($_GET['print'])&&($_GET['print']>0)&&isset($_GET['tab_page'])&&($_GET['tab_page']==4)),  $only_active_sectors_4,$only_active_storages_4 );
	
	//echo 'goods_on_money/goods_on_money'.$print_add.'.html';
	
	$sm->assign('log4',$filetext4);
	
	$sm->assign('has_4',  $au->user_rights->CheckAccess('w',514));
	
	
	
	*/
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('goods_on_stor/goods_on_stor_form'.$print_add.'.html');
	
	
	
	
	
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