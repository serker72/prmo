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
require_once('classes/cashfileitem.php');
require_once('classes/cashfilegroup.php');

require_once('classes/supplieritem.php');

require_once('classes/billitem.php');
require_once('classes/cashitem.php');



if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� ������� ��������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['pay_id'])){
	if(!isset($_POST['pay_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $pay_id=abs((int)$_POST['pay_id']);
	
}else $pay_id=abs((int)$_GET['pay_id']); 

$_user=new CashItem;
$user=$_user->GetItemById($pay_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}
 

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',833)){
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
	$_ff=new FileDocFolderItem(1, $pay_id, new CashFileItem);
	$_ff->SetTablename('cash_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false));   
	$_folder_desrc=' �����: '.$ff;
}
	
$log->PutEntry($result['id'],'������ ������ ������ ������� ��������',NULL,833, NULL, $user['code'].$_folder_desrc,$pay_id);	



//�������� ������
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',841)){
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
	
	$_file=new CashFileItem;
	$file=$_file->GetItemById($id);
	
	 
	
	if($file!==false){
		$_file->Del($id);
		
		 
		
		$log->PutEntry($result['id'],'������ ����',NULL,841,NULL,'��� ����� '.SecStr($file['orig_name']),$itm['id']);
	}
	
	header("Location: cash_files.php?pay_id=".$pay_id);
	die();
}



//�������� �����
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',841)){
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
	
	$_ff=new FileDocFolderItem(1, $pay_id, new CashFileItem);
	$_ff->SetTablename('cash_file_folder');
	$_ff->SetDocIdName('pay_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,841);
		
		$log->PutEntry($result['id'],'������ �����',NULL,841,NULL,'��� ����� '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:cash_files.php?pay_id=".$pay_id.'&folder_id='.$folder_id);
	die();
}


//����������� ����� � ������
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',841)){
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
	
	
		  $_file=new CashFileItem;
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //������ � ������, ���������...
	
		}
	}
	
	//���������� �����
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(1, $pay_id, new CashFileItem);
	$_ff->SetTablename('cash_file_folder');
	$_ff->SetDocIdName('pay_id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 841);
			
			//������ � ������, ���������...
		}
	}
	
	
	header("Location:cash_files.php?pay_id=".$pay_id.'&folder_id='.$folder_id);
	die();
}



//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=35;
	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//������ ������� �����������������
	/*$sm->assign('has_admin',(
							$au->user_rights->CheckAccess('x',613)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('613'));
	$admin=$dto->Draw('acc_files.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$decorator->AddEntry(new UriEntry('pay_id',$pay_id));
	
	$ffg=new CashFileGroup(1, $pay_id,  new FileDocFolderItem(4,  $pay_id, new CashFileItem));
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'cash_files.php', 'cash_file.html', 'swfupl-js/cash_files.php', $au->user_rights->CheckAccess('w',838), $au->user_rights->CheckAccess('w',841), $au->user_rights->CheckAccess('w',839), $folder_id, $au->user_rights->CheckAccess('w',838), $au->user_rights->CheckAccess('w',839), $au->user_rights->CheckAccess('w',841), $au->user_rights->CheckAccess('w',841),
	
	'',
	 $au->user_rights->CheckAccess('w',820),
	 $result
	
	);
	
	 
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('pay',$user);
	$content=$sm->fetch('cash/files.html');
	
	
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