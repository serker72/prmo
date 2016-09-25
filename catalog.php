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
require_once('classes/posgroup.php');

require_once('classes/posgroupgroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Номенклатура');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['catalog_from'])){
		$from=abs((int)$_SESSION['catalog_from']);
	}else $from=0;
	$_SESSION['catalog_from']=$from;

//внесение изменений в права
if(isset($_POST['doInp'])){
	
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - object_id
			//3rd figure - user_id
			eregi("^do_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			if(($regs!==NULL)&&isset($_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: catalog.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',64)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Номенклатура',NULL,64);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=7;


	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',64)||
							$au->user_rights->CheckAccess('x',67)||
							$au->user_rights->CheckAccess('x',68)||
							$au->user_rights->CheckAccess('x',69)||
							$au->user_rights->CheckAccess('x',70)
	);
	$dto=new DiscrTableObjects($result['id'],array('64','67','68','69','70'));
	$admin=$dto->Draw('catalog.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	//покажем лог
	$log=new PosGroup; 
	$log->SetAuthResult($result);

	//Разбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',abs((int)$_GET['id']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	if(isset($_GET['group_id'])&&(strlen($_GET['group_id'])>0)){
		//$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('group_id',$_GET['group_id']));
	}
	
	
	if(isset($_GET['two_group_id'])&&(strlen($_GET['two_group_id'])>0)){
		//$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('two_group_id',$_GET['two_group_id']));
	}
	
	if(isset($_GET['three_group_id'])&&(strlen($_GET['three_group_id'])>0)){
		//$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('three_group_id',$_GET['three_group_id']));
	}
	
	if(isset($_GET['three_group_id'])&&(strlen($_GET['three_group_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'])&&(strlen($_GET['two_group_id'])>0)){
		
		
		
		//добавить обработку подгрупп...
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		
		//найти подгруппы
		//
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
		
		//добавить обработку подгрупп...
		//найти подгруппы
		//
		//найти подподгруппы
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
	
	
	if(isset($_GET['name'])&&(strlen($_GET['name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name']));
	}
	
	if(isset($_GET['gost_tu'])&&(strlen($_GET['gost_tu'])>0)){
		$decorator->AddEntry(new SqlEntry('p.gost_tu',SecStr($_GET['gost_tu']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('gost_tu',$_GET['gost_tu']));
	}
	
	
	
	if(isset($_GET['length'])&&(strlen($_GET['length'])>0)){
		$decorator->AddEntry(new SqlEntry('p.length',SecStr($_GET['length']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('length',$_GET['length']));
	}
	
	if(isset($_GET['width'])&&(strlen($_GET['width'])>0)){
		$decorator->AddEntry(new SqlEntry('p.width',SecStr($_GET['width']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('width',$_GET['width']));
	}
	
	if(isset($_GET['height'])&&(strlen($_GET['height'])>0)){
		$decorator->AddEntry(new SqlEntry('p.height',SecStr($_GET['height']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('height',$_GET['height']));
	}
	
	if(isset($_GET['diametr'])&&(strlen($_GET['diametr'])>0)){
		$decorator->AddEntry(new SqlEntry('p.diametr',SecStr($_GET['diametr']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('diametr',$_GET['diametr']));
	}
	
	
	
	
	if(isset($_GET['dimension_id'])&&(strlen($_GET['dimension_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_GET['dimension_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('dimension_id',$_GET['dimension_id']));
	}
	
	
	
	//просмотр неактивных позиций
	if($au->user_rights->CheckAccess('w',595)){
		
		if(isset($_GET['is_active'])&&($_GET['is_active']==1)){
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('is_active',1));
			
			//echo 'only a g';
		}elseif(isset($_GET['is_active'])&&($_GET['is_active']==0)){
			$decorator->AddEntry(new SqlEntry('p.is_active',0, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('is_active',0));
			
			//echo 'only n/a g';	
		}elseif(isset($_GET['is_active'])&&($_GET['is_active']=='-1')){		
			//все позиции
			$decorator->AddEntry(new UriEntry('is_active','-1'));	
			//echo 'all g';	
		
		}elseif(isset($_COOKIE['cat_is_active'])&&($_COOKIE['cat_is_active']==1)){
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('is_active',1));
			//echo 'only a c';	
			  
	  	}elseif(isset($_COOKIE['cat_is_active'])&&($_COOKIE['cat_is_active']==0)){
			$decorator->AddEntry(new SqlEntry('p.is_active',0, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('is_active',0));
			//echo 'only n/a c';	
			  
	  	}elseif(isset($_COOKIE['cat_is_active'])&&($_COOKIE['cat_is_active']=='-1')){
			//все позиции
			//все позиции
			$decorator->AddEntry(new UriEntry('is_active','-1'));	
			//echo 'all c';
		}else{
			//по умолчанию только активные
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('is_active',1));	
			//echo 'only a default';
		}
		
	}else{
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	//	echo 'only a norights';
	}
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
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
	
	if(isset($_GET['do_open_newgroup'])) $decorator->AddEntry(new UriEntry('do_open_newgroup',1));
	
	
	$llg=$log->ShowPos('catalog/cat.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',68),  $au->user_rights->CheckAccess('w',69),false,$au->user_rights->CheckAccess('w',70)||$au->user_rights->CheckAccess('w',150)||$au->user_rights->CheckAccess('w',151), $au->user_rights->CheckAccess('w',70), $au->user_rights->CheckAccess('w',150), $au->user_rights->CheckAccess('w',151), $au->user_rights->CheckAccess('w',67), '','',false,$au->user_rights->CheckAccess('w',595));
	
	
	$sm->assign('log',$llg);
	$content=$sm->fetch('catalog/catalog.html');
	
	
	

	
	
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