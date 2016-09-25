<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/acc_in_fileitem.php');
require_once('classes/acc_in_filegroup.php');

require_once('classes/supplieritem.php');

require_once('classes/bill_in_item.php');
require_once('classes/acc_in_item.php');




if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� �����������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['acc_id'])){
	if(!isset($_POST['acc_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $acc_id=abs((int)$_POST['acc_id']);
	
}else $acc_id=abs((int)$_GET['acc_id']); 

$_user=new AccInItem;
$user=$_user->GetItemById($acc_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}
$bill_id=$user['bill_id'];

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',659)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//������ ������� 
$_folder_desrc='';
if($folder_id==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(3, $acc_id, new AccInFileItem(3));
	$_ff->SetTablename('acceptance_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false));   
	$_folder_desrc=' �����: '.$ff;
}
	
$log->PutEntry($result['id'],'������ ������ ������ �����������',NULL,659, NULL, '����������� � '.$user['id'].$_folder_desrc,$acc_id);	


//�������� ������
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',670)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_file=new AccInFileItem;
	$file=$_file->GetItemById($id);
	
	$_itm=new AccInItem;
	$itm=$_itm->GetItemById($file['acceptance_id']);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'������ ���� �����������',NULL,613,NULL,'��� ����� '.SecStr($file['orig_name']),$bill_id);
		
		$log->PutEntry($result['id'],'������ ����',NULL,670,NULL,'��� ����� '.SecStr($file['orig_name']),$itm['id']);
	}
	
	header("Location: acc_in_files.php?acc_id=".$acc_id);
	die();
}


//�������� �����
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',670)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_ff=new FileDocFolderItem(3, $acc_id, new AccInFileItem(3));
	$_ff->SetTablename('acceptance_file_folder');
	$_ff->SetDocIdName('acc_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,670);
		
		$log->PutEntry($result['id'],'������ �����',NULL,670,NULL,'��� ����� '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:acc_in_files.php?acc_id=".$acc_id.'&folder_id='.$folder_id);
	die();
}


//����������� ����� � ������
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',670)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	//
	$move_folder_id=abs((int)$_GET['move_folder_id']);
	
	//���������� �����
	$files=$_GET['check_file'];
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		  $_file=new AccInFileItem(3);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //������ � ������, ���������...
	
		}
	}
	
	//���������� �����
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(3, $ship_id, new AccInFileItem(3));
	$_ff->SetTablename('acceptance_file_folder');
	$_ff->SetDocIdName('acc_id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 670);
			
			//������ � ������, ���������...
		}
	}
	
	
	header("Location:acc_in_files.php?acc_id=".$acc_id.'&folder_id='.$folder_id);
	die();
}


//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=34;
	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	

	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$decorator->AddEntry(new UriEntry('acc_id',$acc_id));
	
	
	$ffg=new AccInFileGroup(3, $acc_id, new FileDocFolderItem(3,  $bill_id, new AccInFileItem(3)));
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'acc_in_files.php', 'acc_in_file.html', 'swfupl-js/acc_in_files.php',
	 $au->user_rights->CheckAccess('w',668), 
	 $au->user_rights->CheckAccess('w',670), 
	 $au->user_rights->CheckAccess('w',669), $folder_id, 
	 $au->user_rights->CheckAccess('w',668), 
	 $au->user_rights->CheckAccess('w',669), 
	 $au->user_rights->CheckAccess('w',670), 
	 $au->user_rights->CheckAccess('w',670),
	 
	 '',
	 $au->user_rights->CheckAccess('w',825),
	 $result
	
	  );
	

	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('acc',$user);
	$content=$sm->fetch('acc_in/files.html');
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>