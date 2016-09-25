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
require_once('classes/pl_posgroup.php');

require_once('classes/posgroupgroup.php');
require_once('classes/posgroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Прайс-лист');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;

	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['catalog_from'])){
		$from=abs((int)$_SESSION['catalog_from']);
	}else $from=0;
	$_SESSION['catalog_from']=$from;

*/

if(!$au->user_rights->CheckAccess('w',600)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//пропишем удаление
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',603)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	$id=abs((int)$_GET['id']);
	
	
	$_pi=new plpositem;
	$pi=$_pi->GetItemById($id);
	if($_pi->CanDelete($id)){
		$_pi->Del($id);
	
		$log->PutEntry($result['id'],'удалил позицию прайс-листа',NULL,603, NULL,  'позиция '.SecStr($pi['name']),$id);	
	
	}
	header("Location: pricelist.php");
	die();
}




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
	
	
	
	
	//покажем лог
	$log=new PlPosGroup;
	$prefix='_1';
	
	//Разбор переменных запроса
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',abs((int)$_GET['id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
	}
	
	if(isset($_GET['pl_id'.$prefix])&&(strlen($_GET['pl_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.id',abs((int)$_GET['pl_id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('pl_id',$_GET['pl_id'.$prefix]));
	}
	
	if(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id'.$prefix]));
	}
	
	
	if(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id'.$prefix]));
	}
	
	if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('three_group_id',$_GET['three_group_id'.$prefix]));
	}
	
	if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id'.$prefix]), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
		
		
		
		//добавить обработку подгрупп...
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id'.$prefix]), SqlEntry::E));
		
		//найти подгруппы
		//
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
	}elseif(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id'.$prefix]), SqlEntry::E));
		
		//добавить обработку подгрупп...
		//найти подгруппы
		//
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id'.$prefix]));
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
	
	
	if(isset($_GET['name'.$prefix])&&(strlen($_GET['name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name'.$prefix]));
	}
	
	if(isset($_GET['gost_tu'.$prefix])&&(strlen($_GET['gost_tu'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.gost_tu',SecStr($_GET['gost_tu'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('gost_tu',$_GET['gost_tu'.$prefix]));
	}
	
	
	
	if(isset($_GET['length'.$prefix])&&(strlen($_GET['length'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.length',SecStr($_GET['length'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('length',$_GET['length'.$prefix]));
	}
	
	if(isset($_GET['width'.$prefix])&&(strlen($_GET['width'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.width',SecStr($_GET['width'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('width',$_GET['width'.$prefix]));
	}
	
	if(isset($_GET['height'.$prefix])&&(strlen($_GET['height'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.height',SecStr($_GET['height'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('height',$_GET['height'.$prefix]));
	}
	
	if(isset($_GET['diametr'.$prefix])&&(strlen($_GET['diametr'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.diametr',SecStr($_GET['diametr'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('diametr',$_GET['diametr'.$prefix]));
	}
	
	
	
	
	if(isset($_GET['dimension_id'.$prefix])&&(strlen($_GET['dimension_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_GET['dimension_id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('dimension_id',$_GET['dimension_id'.$prefix]));
	}
	
	
	
	if(isset($_GET['price'.$prefix])&&(strlen($_GET['price'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.price',SecStr($_GET['price'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('price',$_GET['price'.$prefix]));
	}
	
	
	/*if(isset($_GET['discount_m'.$prefix])&&(strlen($_GET['discount_m'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.discount_m',SecStr($_GET['discount_m'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('discount_m',$_GET['discount_m'.$prefix]));
	}
	
	if(isset($_GET['discount_r'.$prefix])&&(strlen($_GET['discount_r'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.discount_r',SecStr($_GET['discount_r'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('discount_r',$_GET['discount_r'.$prefix]));
	}
	*/
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('pl.id',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('pl.id',SqlOrdEntry::DESC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('pl.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	//if(isset($_GET['do_open_newgroup'])) $decorator->AddEntry(new UriEntry('do_open_newgroup',1));
	
	
	$llg=$log->ShowPos('pl/list.html',$decorator,$from,$to_page, 
	$au->user_rights->CheckAccess('w',602), 
	 $au->user_rights->CheckAccess('w',603),
	 false,
	 $au->user_rights->CheckAccess('w',70)||$au->user_rights->CheckAccess('w',150)||$au->user_rights->CheckAccess('w',151), 
	 $au->user_rights->CheckAccess('w',70), 
	 $au->user_rights->CheckAccess('w',150),
	  $au->user_rights->CheckAccess('w',151), 
	  $au->user_rights->CheckAccess('w',601),
	  $au->user_rights->CheckAccess('w',605),
	  $au->user_rights->CheckAccess('w',92),
	  $prefix,
	   $au->user_rights->CheckAccess('w',606)
	  );
	
	
	$sm->assign('log',$llg);
	$content=$sm->fetch('pl/pl.html');
	
	
	/*$pi=new PlPosItem;
	$ppi=$pi->GetItemById(2);
	print_r($ppi);
*/
	
	
	
	
//******************************************************************************************************
//вкладка ном-ра не в прайс-листе



	
	//покажем лог
	$log=new PosGroup;
	$log->SetPageName('pricelist.php');
	
	$prefix='_2';
	//Разбор переменных запроса
	if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
	else $from=0;
	
	
	
	if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	if(isset($_GET['id'.$prefix])&&(strlen($_GET['id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',abs((int)$_GET['id'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id'.$prefix]));
	}
	
	if(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id'.$prefix]));
	}
	
	
	if(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id'.$prefix]));
	}
	
	if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('three_group_id',$_GET['three_group_id'.$prefix]));
	}
	
	if(isset($_GET['three_group_id'.$prefix])&&(strlen($_GET['three_group_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id'.$prefix]), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'.$prefix])&&(strlen($_GET['two_group_id'.$prefix])>0)){
		
		
		
		//добавить обработку подгрупп...
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id'.$prefix]), SqlEntry::E));
		
		//найти подгруппы
		//
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id'.$prefix]));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$decorator->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
	}elseif(isset($_GET['group_id'.$prefix])&&(strlen($_GET['group_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id'.$prefix]), SqlEntry::E));
		
		//добавить обработку подгрупп...
		//найти подгруппы
		//
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id'.$prefix]));
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
	
	
	if(isset($_GET['name'.$prefix])&&(strlen($_GET['name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name'.$prefix]));
	}
	
	if(isset($_GET['gost_tu'.$prefix])&&(strlen($_GET['gost_tu'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.gost_tu',SecStr($_GET['gost_tu'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('gost_tu',$_GET['gost_tu'.$prefix]));
	}
	
	
	
	if(isset($_GET['length'.$prefix])&&(strlen($_GET['length'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.length',SecStr($_GET['length'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('length',$_GET['length'.$prefix]));
	}
	
	if(isset($_GET['width'.$prefix])&&(strlen($_GET['width'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.width',SecStr($_GET['width'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('width',$_GET['width'.$prefix]));
	}
	
	if(isset($_GET['height'.$prefix])&&(strlen($_GET['height'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.height',SecStr($_GET['height'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('height',$_GET['height'.$prefix]));
	}
	
	if(isset($_GET['diametr'.$prefix])&&(strlen($_GET['diametr'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.diametr',SecStr($_GET['diametr'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('diametr',$_GET['diametr'.$prefix]));
	}
	
	
	
	
	if(isset($_GET['dimension_id'.$prefix])&&(strlen($_GET['dimension_id'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_GET['dimension_id'.$prefix]), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('dimension_id',$_GET['dimension_id'.$prefix]));
	}
	
	
	
	if(isset($_GET['price'.$prefix])&&(strlen($_GET['price'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.price',SecStr($_GET['price'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('price',$_GET['price'.$prefix]));
	}
	
	
	/*if(isset($_GET['discount_m'.$prefix])&&(strlen($_GET['discount_m'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.discount_m',SecStr($_GET['discount_m'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('discount_m',$_GET['discount_m'.$prefix]));
	}
	
	if(isset($_GET['discount_r'.$prefix])&&(strlen($_GET['discount_r'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('pl.discount_r',SecStr($_GET['discount_r'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('discount_r',$_GET['discount_r'.$prefix]));
	}
	*/
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	
	switch($sortmode){
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
		
	
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	//if(isset($_GET['do_open_newgroup'])) $decorator->AddEntry(new UriEntry('do_open_newgroup',1));
	
	//исключить позиции в прайсе
	$decorator->AddEntry(new SqlEntry('p.id','select distinct position_id from pl_position', SqlEntry::NOT_IN_SQL));
	
	
	
	$llg=$log->ShowPos('pl/list_non.html',$decorator,$from,$to_page, 
	$au->user_rights->CheckAccess('w',68),
	  $au->user_rights->CheckAccess('w',69),false,$au->user_rights->CheckAccess('w',70)||$au->user_rights->CheckAccess('w',150)||$au->user_rights->CheckAccess('w',151), $au->user_rights->CheckAccess('w',70), $au->user_rights->CheckAccess('w',150), $au->user_rights->CheckAccess('w',151), 
	  $au->user_rights->CheckAccess('w',601),   '',
	  $prefix, 
	   $au->user_rights->CheckAccess('w',605)
	  );
	
	
	$sm->assign('log3',$llg);
	
	
	
	$content=$sm->fetch('pl/pl.html');	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
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