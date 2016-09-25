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


require_once('classes/an_search.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Поиск товара');

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
	if(!$au->user_rights->CheckAccess('w',856)&&!$au->user_rights->CheckAccess('w',857)){
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
 $log->PutEntry($result['id'],'перешел в Отчет Поиск товара',NULL,856,NULL,NULL);
 
//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);

	
	$_menu_id=65;

	if($print==0) include('inc/menu.php');
	
	

	 
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	//вкладка Не выполненные заявки
	
	
	
	$sm=new SmartyAdm;
	
	
	 
	//Вкладка
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	//$decorator->AddEntry(new SqlEntry('p.storage_id',abs((int)$_GET['storage_id']), Sq//объект
	 
	 
	
	
	//поиск по позициям
	$dec2=new DBDecorator;
	
	if(isset($_GET['id2'])&&(strlen($_GET['id2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.id',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['id2']))));
		$dec2->AddEntry(new UriEntry('id2',$_GET['id2']));
	}
	
	if(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('group_id',abs((int)$_GET['group_id'])));
	}
	
	if(isset($_GET['was_ext_search'])&&(abs((int)$_GET['was_ext_search'])>0)){
		$dec2->AddEntry(new UriEntry('was_ext_search',1));
	}else $dec2->AddEntry(new UriEntry('was_ext_search',0));
	
	
	
	if(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('two_group_id',abs((int)$_GET['two_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('three_group_id',abs((int)$_GET['three_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		
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
			$dec2->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
	}elseif(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		
		
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
			$dec2->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_GET['name2'])&&(strlen($_GET['name2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['name2']))));//SqlEntry::LIKE));
		$dec2->AddEntry(new UriEntry('name2',$_GET['name2']));
	}
	
	if(isset($_GET['gost_tu2'])&&(strlen($_GET['gost_tu2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.gost_tu',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['gost_tu2']))));
		$dec2->AddEntry(new UriEntry('gost_tu2',$_GET['gost_tu2']));
	}
	
	
	
	if(isset($_GET['length2'])&&(strlen($_GET['length2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.length',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['length2']))));
		$dec2->AddEntry(new UriEntry('length2',$_GET['length2']));
	}
	
	if(isset($_GET['width2'])&&(strlen($_GET['width2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.width',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['width2']))));
		$dec2->AddEntry(new UriEntry('width2',$_GET['width2']));
	}
	
	if(isset($_GET['height2'])&&(strlen($_GET['height2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.height',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['height2']))));
		$dec2->AddEntry(new UriEntry('height2',$_GET['height2']));
	}
	
	if(isset($_GET['diametr2'])&&(strlen($_GET['diametr2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.diametr',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['diametr2']))));
		$dec2->AddEntry(new UriEntry('diametr2',$_GET['diametr2']));
	}
	
	
	
	
	if(isset($_GET['dimension_id2'])&&(strlen($_GET['dimension_id2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.dimension_id',abs((int)$_GET['dimension_id2']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('dimension_id2',$_GET['dimension_id2']));
	}
	
	
	//$dec2->AddEntry(new SqlOrdEntry('pos.name',SqlOrdEntry::ASC));
	
	
	
	
	$dec2->AddEntry(new UriEntry('print',$_GET['print']));
	 
	$dec2->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$dec2->AddEntry(new SqlOrdEntry('pos.name',SqlOrdEntry::DESC));
		break;
		case 1:
			$dec2->AddEntry(new SqlOrdEntry('pos.name',SqlOrdEntry::ASC));
		break;
		
		 
		
		default:
			$dec2->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	
	
	
	
	
	
	
	$as=new AnSearch;
	$filetext=$as->ShowData(  $result['org_id'], 'an_search/an_search'.$print_add.'.html',$dec2,'an_search.php',  isset($_GET['doSub'])||isset($_GET['doSub_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',857), ' ', $alls);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log',$filetext);
	
	 //фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])/*||($print==1)*/){
		$log->PutEntry($result['id'],'открыл отчет Поиск товара',NULL,856,NULL, NULL);	
	}
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('an_search/an_search_form'.$print_add.'.html');
	
	
	
	
	
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