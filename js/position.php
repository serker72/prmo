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

//$ui=new UserSItem;
if(isset($_POST['action'])&&($_POST['action']=="redraw_groups")){
	$sm=new SmartyAj;
	/*if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;*/
	
	$pgg=new PosGroupGroup;
	$arr=$pgg->GetItemsArr();
	
	$sm->assign('items',$arr);	
	$ret=$sm->fetch('catalog/groups_dic.html');
}if(isset($_POST['action'])&&($_POST['action']=="redraw_groups_page")){
	$sm=new SmartyAj;
	
	/*if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	*/
	$_group_group=new PosGroupGroup;
	$dim_gr=$_group_group->GetItemsArr();
	$dim_ids=array(); $dim_vals=array();
	foreach($dim_gr as $k=>$v){
		$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
	}
	$sm->assign('group_ids',$dim_ids); 
	$sm->assign('group_values',$dim_vals);
	
	
	$ret=$sm->fetch('catalog/groups_opt.html');
}elseif(isset($_POST['action'])&&($_POST['action']=="add_group")){
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',70)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosGroupItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$code=$qi->Add($params);
	
	$log->PutEntry($result['id'],'добавил товарную группу',NULL,70,NULL,$params['name'],$code);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_group")){
	if(!$au->user_rights->CheckAccess('w',70)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosGroupItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['group']),9);
	$qi->Edit($id,$params);	
	
	$log->PutEntry($result['id'],'редактировал товарную группу',NULL,70,NULL,$params['name'],$id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_group")){
	
	if(!$au->user_rights->CheckAccess('w',70)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosGroupItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	$log->PutEntry($result['id'],'удалил товарную группу',NULL,70,NULL,$params['name'],$id);
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_dims")){
	$sm=new SmartyAj;
	
	
	$pgg=new PosDimGroup;
	$arr=$pgg->GetItemsArr();
	
	$sm->assign('items',$arr);	
	$ret=$sm->fetch('catalog/dims_dic.html');
}if(isset($_POST['action'])&&($_POST['action']=="redraw_dims_page")){
	$sm=new SmartyAj;
	
	/*if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	*/
	$_group_group=new PosDimGroup;
	$dim_gr=$_group_group->GetItemsArr();
	$dim_ids=array(); $dim_vals=array();
	foreach($dim_gr as $k=>$v){
		$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
	}
	$sm->assign('dim_ids',$dim_ids); 
	$sm->assign('dims',$dim_vals);
	
	
	$ret=$sm->fetch('catalog/dims_opt.html');
}elseif(isset($_POST['action'])&&($_POST['action']=="add_dim")){
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',71)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosDimItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$code=$qi->Add($params);
	
	$log->PutEntry($result['id'],'добавил товарную группу',NULL,71,NULL,$params['name'],$code);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_dim")){
	if(!$au->user_rights->CheckAccess('w',71)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosDimItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['dim']),9);
	$qi->Edit($id,$params);	
	
	$log->PutEntry($result['id'],'редактировал товарную группу',NULL,71,NULL,$params['name'],$id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_dim")){
	
	if(!$au->user_rights->CheckAccess('w',71)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosDimItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	$log->PutEntry($result['id'],'удалил товарную группу',NULL,71,NULL,$params['name'],$id);
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>