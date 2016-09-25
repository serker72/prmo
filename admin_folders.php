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
require_once('classes/discr_table.php');

require_once('classes/discr_man.php');
require_once('classes/actionlog.php');
require_once('classes/rl/rl_overall_folders.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Управление правами на папки');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['group_id'])){
		if(!isset($_POST['group_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $group_id=abs((int)$_POST['group_id']);	
	}else $group_id=abs((int)$_GET['group_id']);
	
	if(!isset($_GET['storage_id'])){
		if(!isset($_POST['storage_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $storage_id=abs((int)$_POST['storage_id']);	
	}else $storage_id=abs((int)$_GET['storage_id']);
	
/*
if(isset($_POST['doInp'])){
	if(!$au->user_rights->CheckAccess('w',1)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
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
				
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					//PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL){
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
			}
		}
	}
	
	header("Location: admin_users.php?gr_id=".abs((int)$_POST['gr_id']));	
	die();
}

*/
if(!$au->user_rights->CheckAccess('w',1)){
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

$smarty->display('top.html');
unset($smarty);

$_menu_id=13;

	include('inc/menu.php');
	
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	
	
	
	if(isset($_GET['username'])) $username=SecStr($_GET['username']);
	elseif(isset($_POST['username'])) $username=SecStr($_POST['username']);
	else $username=''; 
	
	$usernames=array(); $usernames=explode(';', $username);
	foreach($usernames as $k=>$v) if(strlen(trim($v))==0) unset($usernames[$k]);
	
	$_rlo=new RLOverallFolders;
	
	$content=$_rlo->BuildTable($group_id, $storage_id, $usernames, 'rl/rl_overall.html', isset($_GET['doShow']));
	
	
	 
	
	
	
	
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