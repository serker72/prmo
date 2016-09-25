<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

 
require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/user_s_item.php');

require_once('../classes/posonstor.php');
require_once('../classes/posonas.php');
require_once('../classes/posonsec.php');

require_once('../classes/posonas_mod.php');




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

if(isset($_POST['action'])&&($_POST['action']=="as_by_pos_prihod")){
	$position_id=abs((int)$_POST['position_id']);
	 
	
	$pdate1=($_POST['pdate1']);	
	$pdate2=($_POST['pdate2']);
	
	
	if($_POST['storage_id']!=0) $storage_id=array($_POST['storage_id']);
	else $storage_id=array();
	if($_POST['sector_id']!=0) $sector_id=array($_POST['sector_id']);
	else $sector_id=array();
	
	
	
	
	$_kr=new PositionsOnAssortimentMod;
	
	
	
	$ret=$_kr->InAccByPos($position_id,  $pdate1,$pdate2,'goods_on_stor/in_accs_in.html',$result['org_id'],true,$sector_id,$storage_id,$limited_sector,$_extended_limited_sector);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="as_by_pos_rashod")){
	$position_id=abs((int)$_POST['position_id']);
	 
	$pdate1=($_POST['pdate1']);	
	$pdate2=($_POST['pdate2']);
	$_kr=new PositionsOnAssortimentMod;
	
	
	if($_POST['storage_id']!=0) $storage_id=array($_POST['storage_id']);
	else $storage_id=array();
	if($_POST['sector_id']!=0) $sector_id=array($_POST['sector_id']);
	else $sector_id=array();
	
	
	
	$ret.=$_kr->InWfByPos($position_id,  $pdate1,$pdate2,'goods_on_stor/in_accs.html',$result['org_id'],true,$sector_id,$storage_id,$limited_sector,$_extended_limited_sector);
	
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>