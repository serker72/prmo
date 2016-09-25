<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');



require_once('../classes/filedocfolderitem.php');
require_once('../classes/filedocfolderlist.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

require_once('../classes/supplier_akt_item.php');
//require_once('../classes/supplieritem.php');
	

setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
if(!$au->user_rights->CheckAccess('w',319)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$_fi=new Supplier_Akt_Item;
	
$ret='';

//РАБОТА С ОПФ
if(isset($_POST['action'])&&($_POST['action']=="edit_txt")){
	
	
	
	$txt=SecStr(iconv('utf-8','windows-1251',$_POST['txt']), 9);
	
	
	
	$sm=new SmartyAj;
	if(isset($_POST['id'])) $id=abs((int)$_POST['id']);
	else{
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
	}	
	
	$_fi->Edit($id, array('txt'=>$txt));
	$log->PutEntry($result['id'],'дал акту описание',NULL,319,NULL,$txt);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_txt_chk")){
	
	//редактирование описания файла с проверкой прав!
	
	
	$txt=SecStr(iconv('utf-8','windows-1251',$_POST['txt']), 9);
	
	
	
	$sm=new SmartyAj;
	if(isset($_POST['id'])) $id=abs((int)$_POST['id']);
	else{
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
	}	
	
	
	if(!$au->user_rights->CheckAccess('w',319)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}else{

	
		$_fi->Edit($id, array('txt'=>$txt));
		$log->PutEntry($result['id'],'дал акту описание',NULL,319,NULL,$txt);
	}
}



//работа с папками
elseif(isset($_POST['action'])&&($_POST['action']=="make_folder")){
	if(!$au->user_rights->CheckAccess('w',318)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}else{
		$_ff=new FileDocFolderItem(4,abs((int)$_POST['sup_id']), new Supplier_Akt_Item(4));
		$_ff->SetTablename('supplier_shema_file_folder');
		$_ff->SetDocIdName('sup_id');
		
		$params=array();
		$params['doc_id']=abs((int)$_POST['sup_id']);
		$params['filename']=SecStr(iconv('utf-8','windows-1251',$_POST['filename']));
		$params['txt']=SecStr(iconv('utf-8','windows-1251',$_POST['txt']));
		$params['storage_id']=abs((int)$_POST['storage_id']);
		$params['parent_id']=abs((int)$_POST['parent_id']);
		$params['pdate']=time();
		$params['org_id']=$result['org_id'];
		$params['user_id']=$result['id'];
		
		if(isset($params['filename'])) $params['filename']= strtoupper(substr($params['filename'], 0, 1)).substr($params['filename'],1,strlen($params['filename']));
		
		
		$code=$_ff->Add($params);
		
		$log->PutEntry($result['id'],'создал папку',NULL,318,NULL,'папка '.$params['filename'].', описание: '.$params['txt'],$code);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_folder")){
	if(!$au->user_rights->CheckAccess('w',319)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}else{
		$_ff=new FileDocFolderItem(4,abs((int)$_POST['sup_id']), new Supplier_Akt_Item(4));
		$_ff->SetTablename('supplier_shema_file_folder');
		$_ff->SetDocIdName('sup_id');
		
		$params=array();
		$params['filename']=SecStr(iconv('utf-8','windows-1251',$_POST['filename']));
		$params['txt']=SecStr(iconv('utf-8','windows-1251',$_POST['txt']));
		$id=abs((int)$_POST['id']);
		
		if(isset($params['filename'])) $params['filename']= strtoupper(substr($params['filename'], 0, 1)).substr($params['filename'],1,strlen($params['filename']));
		
		$_ff->Edit($id, $params,NULL,$result,319);
		
		$log->PutEntry($result['id'],'редактировал папку',NULL,319,NULL,'папка '.$params['filename'].', описание: '.$params['txt'],$id);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_folders")){
	$storage_id=abs((int)$_POST['storage_id']);
	$selected_folders=$_POST['selected_folders'];
	
	
	
	$_ff=new FileDocFolderList($storage_id, abs((int)$_POST['sup_id']), new Supplier_Akt_Item(4), new FileDocFolderItem(4,abs((int)$_POST['sup_id']), new Supplier_Akt_Item(4)));   //FileFolderList($storage_id);
	$_ff->setTableName('supplier_shema_file_folder');
	
	$fld=$_ff->GetItemsArr($selected_folders);
	
	
	$sm=new SmartyAj;
	
	$sm->assign('items',$fld);
	$ret=$sm->fetch('doc_file/move_folders.html');
	
	
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>