<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_v_item.php');
require_once('../classes/questionitem.php');


require_once('../classes/usercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/usercontactdataitem.php');

require_once('../classes/user_int_item.php');

require_once('../classes/user_int_group.php');


require_once('../classes/user_pos_item.php');
require_once('../classes/user_pos_group.php');

require_once('../classes/upos_direction.php');

require_once('../classes/user_dep_item.php');
require_once('../classes/user_dep_group.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';

$ui=new UserVItem;


//работа с должностями
if(isset($_POST['action'])&&($_POST['action']=="redraw_userpos_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserPosGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	$sm->assign('word', 'userpos');
	$sm->assign('named', 'Должность');
	
	
	$ret=$sm->fetch('users_v/userpos.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_userpos_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserPosGroup;
	$ret=$opg->GetItemsOpt($user_id, 'name', true);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_userpos")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new UserPosItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['userpos']),9);
	
	$params['can_sign_as_dir_pr']=abs((int)$_POST['can_sign_as_dir_pr']);
	$params['can_sign_as_manager']=abs((int)$_POST['can_sign_as_manager']);
	$params['is_ruk_otd']=abs((int)$_POST['is_ruk_otd']);
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_userpos")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserPosItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	
	$params['can_sign_as_dir_pr']=abs((int)$_POST['can_sign_as_dir_pr']);
	$params['can_sign_as_manager']=abs((int)$_POST['can_sign_as_manager']);
	$params['is_ruk_otd']=abs((int)$_POST['is_ruk_otd']);
	
	
	$qi->Edit($id,$params);	
	
	/*
	
	$dirs=$_POST['dirs'];
	$_upd=new UposDirection;
	
	$_upd->AddDirsToPositionArray($id,$dirs);
	*/
	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_userpos")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserPosItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
}



//работа с отделами
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_deps_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserDepGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	$sm->assign('word', 'deps');
	$sm->assign('named', 'Отдел');
	
	
	$ret=$sm->fetch('users_v/deps.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_deps_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new UserDepGroup;
	$ret=$opg->GetItemsOpt($user_id, 'name', true);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_deps")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new UserDepItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['deps']),9);
	//$params['is_in_plan_fact_sales']=abs((int)$_POST['is_in_plan_fact_sales']);
	
	 
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_deps")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserDepItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	//$params['is_in_plan_fact_sales']=abs((int)$_POST['is_in_plan_fact_sales']);
	 
	$qi->Edit($id,$params);	
	
	//print_r($params);
	
	 	
	$dirs=$_POST['dirs'];
	$_upd=new UposDirection;
	
	$_upd->AddDirsToPositionArray($id,$dirs);
	
	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_deps")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new UserDepItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>