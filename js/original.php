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

require_once('../classes/storagesector.php');

require_once('../classes/komplnotesgroup.php');
require_once('../classes/komplnotesitem.php');
require_once('../classes/komplreports.php');
require_once('../classes/komplgroup.php');
require_once('../classes/komplitem.php');
require_once('../classes/user_s_item.php');
require_once('../classes/storageitem.php');
require_once('../classes/storagegroup.php');
require_once('../classes/sectorgroup.php');

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
if(isset($_POST['action'])&&($_POST['action']=="load_storages")){
	
	if(is_array($_POST['sector_id'])){
		$sector_id=$_POST['sector_id'];
		
	}else{
		$sector_id=array();
		
	}
	
	if(is_array($_POST['current_id'])){
		$current_id=$_POST['current_id'];
		
	}else{
		$current_id=array();
		
	}
	
	//$sector_id=abs((int)$_POST['sector_id']);
	//$current_id=abs((int)$_POST['current_id']);
	
	$do_limit_sector=$au->FltSector($result);
	$_sectors_to_user=new SectorToUser();
	
	if($do_limit_sector) {
		$sectors_to_user_ids=$_sectors_to_user->GetSectorIdsArr($result['id']);
		
		$limited_sector=$sectors_to_user_ids;
	}
	
	
	if((count($sector_id)>0)&&(!in_array(0,$sector_id))){
		$_bd=new StorageSector();
		//$arr=$_bd->GetCategsBookArr($sector_id, 1);
		$arr=$_bd->GetStoragesBySectors($sector_id);
	 
	}else{
		if(!$do_limit_sector){
			$_bd=new StorageGroup;
			$arr=$_bd->GetItemsArr();
		}else{
			$_bd=new StorageSector;
			$arr=$_bd->GetLimitedStorages($limited_sector);	
		}
	}
	
	
	$ret='';
	//if($current_id==0) $ret.='<option value="0" selected="selected">-все объекты-</option>';
	/*if((count($current_id)==0)||in_array(0,$current_id)) $ret.='<option value="0" selected="selected">-все объекты-</option>';
	else */$ret.='<option value="0">-все объекты-</option>';
	foreach($arr as $k=>$v){
		
		if(in_array($v['id'],$current_id)) $ret.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';	
		else $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';	
	}
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_sectors")){
	//$storage_id=abs((int)$_POST['storage_id']);
	if(is_array($_POST['storage_id'])){
		$storage_id=$_POST['storage_id'];
		
	}else{
		$storage_id=array();
		
	}
	
	if(is_array($_POST['current_id'])){
		$current_id=$_POST['current_id'];
		
	}else{
		$current_id=array();
		
	}
	
	
	if((count($storage_id)>0)&&(!in_array(0,$storage_id))){
		$_bd=new StorageSector();
	
		//$arr=$_bd->GetBookCategsArr($storage_id, 1);
		$arr=$_bd->GetSectorsByStorages($storage_id);
	
	}else{
		$_bd=new SectorGroup();
		$arr=$_bd->GetItemsArr();
	}
	$_sectors_to_user=new SectorToUser();
	
	$do_limit_sector=$au->FltSector($result);
	
	if($do_limit_sector) {
		$sectors_to_user_ids=$_sectors_to_user->GetSectorIdsArr($result['id']);
		
		$limited_sector=$sectors_to_user_ids;
	}
	
	
	$ret='';
	/*if((in_array(0,$current_id))||(count($current_id)==0)) $ret.='<option value="0" selected="selected">-все участки-</option>';
	else */$ret.='<option value="0">-все участки-</option>';
	foreach($arr as $k=>$v){
		if($do_limit_sector&&(!in_array($v['id'],$sectors_to_user_ids))) continue;
		
		if(in_array($v['id'],$current_id)) $ret.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';	
		else $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';	
	}
	
	
	
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>