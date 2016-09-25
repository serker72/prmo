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
require_once('classes/discr_table_group.php');
require_once('classes/discr_gruseritem.php');
require_once('classes/actionlog.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Роли прав доступа');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(isset($_POST['doInp'])){
	if(!$au->user_rights->CheckAccess('w',2)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	$man=new DiscrManGroup;
	$log=new ActionLog;
	
	if(isset($_POST['change_mode'])) $change_mode=abs((int)$_POST['change_mode']);
	else $change_mode=0;
	
	/*echo $change_mode;
	die();*/
	
	$edited_roles=array();
	
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
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					//group_id, right_letter, object_id
					if(!in_array($regs[3],$edited_roles)) $edited_roles[]=$regs[3];
					
					if($change_mode==1){
						//добавить всем пользователям права	
						$man->GrantAccess($regs[3], $regs[1], $regs[2],true);
						
					}else{
						$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					}
					
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил шаблон доступа ".$regs[1],NULL,$regs[2],$regs[3]);
					
				}else{
					if(!in_array($regs[3],$edited_roles)) $edited_roles[]=$regs[3];
					
					if($change_mode==2){
						//удалить у всех пользователей права	
						//echo 'zz1';
						$man->RevokeAccess($regs[3], $regs[1], $regs[2],true);
					}else{
						$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					}
					
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил шаблон доступа ".$regs[1],NULL,$regs[2], $regs[3]);
				}
				
				
			}
		}
	}
	
	if($change_mode==3){
		//запомнить ранее, какие роли мы меняли
		//по всем этим ролям провести полную синхронизацию прав
		//очистить все права пол-лей ролей, добавить им новые права из этих ролей
		$_dmg=new DiscrManGroup();
		
		//echo 'zzz';
		foreach($edited_roles as $k=>$v){
			$_dmg->BuildRightsTable($v);
			
			//echo $v;
			
			//найдем пользователей группы
			$_ug=new UsersGroup;
			$_users=$_ug->GetItemsByFieldsArr(array('group_id'=>$v));
			//print_r($_users);
			foreach($_users as $kk=>$vv){
			//обновим им права
				//var_dump($vv);
				//echo ' '.$vv['id'];
				$_dmg->ApplyTableToUser($vv['id']);
			}
		}
		
	}
	
	
	header("Location: admin_templates.php");	
	die();
}

if(isset($_GET['add_role'])){
	if(!$au->user_rights->CheckAccess('w',2)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	//добавим роль
	
	$gi=new DiscrGrUserItem;
	
	$gi->Add(array('name'=>SecStr($_GET['name'])));
	
	
	header("Location: admin_templates.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',2)){
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


$_menu_id=62;
	include('inc/menu.php');
	
	$dt=new DiscrTableGroup;
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$content=$dt->Draw('admin/admin_groups.html');
	
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