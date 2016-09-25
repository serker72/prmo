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

require_once('../classes/catalog_view.class.php');

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
	
	$current_id=abs((int)$_POST['current_id']);
	
	$pgg=new PosGroupGroup;
	$arr=$pgg->GetItemsArr($current_id);
	
	//$sm->assign('items',$arr);	
	$ids=array(); $names=array(); $current=0;
	foreach($arr as $k=>$v){
		$ids[]=$v['id'];
		$names[]=$v['name'];
		if($v['is_current']==true) $current=$v['id'];	
	}
	
	$sm->assign('group_ids',$ids);	
	$sm->assign('group_id',$current);	
	$sm->assign('group_values',$names);	
	
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
	$ret=$code;
	
	$log->PutEntry($result['id'],'добавил товарную группу',NULL,70,NULL,$params['name'],$code);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_group")){
	if(!$au->user_rights->CheckAccess('w',150)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosGroupItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['group']),9);
	$qi->Edit($id,$params);	
	
	$ret=$id;
	$log->PutEntry($result['id'],'редактировал товарную группу',NULL,150,NULL,$params['name'],$id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="can_delete_group")){
	
	
	$qi=new PosGroupItem;
	
	$id=abs((int)$_POST['id']);
	
	if($qi->CanDelete($id)) $ret=1;
	else $ret=0;
}
elseif(isset($_POST['action'])&&($_POST['action']=="delete_group")){
	
	if(!$au->user_rights->CheckAccess('w',151)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosGroupItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	$log->PutEntry($result['id'],'удалил товарную группу',NULL,151,NULL,$params['name'],$id);
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_dims")){
	$sm=new SmartyAj;
	
	
	$pgg=new PosDimGroup;
	$arr=$pgg->GetItemsArr();
	
	$sm->assign('items',$arr);	
	$ret=$sm->fetch('catalog/dims_dic.html');
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_two_groups")){
	$sm=new SmartyAj;
	/*if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;*/
	
	if(isset($_POST['current_id'])) $current_id=abs((int)$_POST['current_id']);
	else $current_id=0;
	
	$group_id=abs((int)$_POST['group_id']);
	
	$pgg=new PosGroupGroup;
	$arr=$pgg->GetItemsByIdArr($group_id,$current_id); //>GetItemsArr($current_id);
	
	//$sm->assign('items',$arr);	
	$ids=array(); $names=array(); $current=0;
	foreach($arr as $k=>$v){
		$ids[]=$v['id'];
		$names[]=$v['name'];
		if($v['is_current']==true) $current=$v['id'];	
	}
	
	$sm->assign('group_ids',$ids);	
	$sm->assign('group_id',$current);	
	$sm->assign('group_values',$names);	
	
	$ret=$sm->fetch('catalog/groups_opt.html');

}elseif(isset($_POST['action'])&&($_POST['action']=="add_two_group")){
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',70)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PosGroupItem;
	$params=array();
	$params['parent_group_id']=abs((int)$_POST['parent_id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$code=$qi->Add($params);
	$ret=$code;
	
	$log->PutEntry($result['id'],'добавил товарную группу',NULL,70,NULL,$params['name'],$code);
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Catalog_ViewGroup;
	$_view=new Catalog_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new  Catalog_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>