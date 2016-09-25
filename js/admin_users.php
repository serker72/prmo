<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_s_item.php');



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
if(isset($_POST['action'])&&($_POST['action']=="draw_rights")){
	//вывод позиций вх. счета для распоряжения
	
	$user_id=abs((int)$_POST['user_id']);
	
	$object_id=abs((int)$_POST['object_id']);
	
	$sm=new SmartyAj;
	
	$dt=new DiscrTable;
	
	$rights=$dt->GetTableCellArr($user_id, $object_id);
	
	$sm->assign('user_id',$user_id);
	$sm->assign('rights',$rights);
	
	
	$ret.=$sm->fetch("admin/admin_users_cell.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="draw_row_rights")){
	//вывод позиций вх. счета для распоряжения
	
	$user_id=abs((int)$_POST['user_id']);
	$gr_id=abs((int)$_POST['gr_id']);
	if($gr_id===0) $gr_id=NULL;
	
	$_ui=new UserSItem;
	$user=$_ui->Getitembyid($user_id);
	//$object_id=abs((int)$_POST['object_id']);
	
	//echo $gr_id;
	$sm=new SmartyAj;
	
	$dt=new DiscrTable($gr_id);
	
	$rights=$dt->GetTableRowArr($user_id);
	
	$sm->assign('user',$user);
	$sm->assign('rights_arr',$rights);
	
	
	$ret.=$sm->fetch("admin/admin_users_row.html");
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>