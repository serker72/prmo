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
require_once('classes/kvfileitem.php');
require_once('classes/kvfilegroup.php');

require_once('classes/supplieritem.php');

require_once('classes/billitem.php');
require_once('classes/komplitem.php');





$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� ������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['komplekt_ved_id'])){
	if(!isset($_POST['komplekt_ved_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
}else $komplekt_ved_id=abs((int)$_GET['komplekt_ved_id']); 



if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	



$_user=new KomplItem;
$user=$_user->GetItemById($komplekt_ved_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}
//$bill_id=$user['bill_id'];

$log=new ActionLog;

//�������� ��������� � �����
if(isset($_POST['doInp'])){
	
	$man=new DiscrMan;
	$log=new ActionLog;
	
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
				
				//���������� ��������, ���� �� ����� �� ����������������� ������� ������� ������ �������������
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "��������� ������ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "������ ������ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: komplekt.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',82)){
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
	 
	$_ff=new FileDocFolderItem(4, $komplekt_ved_id, new KvFileItem(4));
	$_ff->SetTablename('komplekt_ved_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
	$_folder_desrc=' �����: '.$ff;
}
	
$log->PutEntry($result['id'],'������ ������ ������ ������',NULL,82, NULL, '������ '.$user['id'].$_folder_desrc,$komplekt_ved_id);	




//�������� ������
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',300)){
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
	
	$_file=new KvFileItem;
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'������ ����',NULL,300,NULL,'��� ����� '.SecStr($file['orig_name']),$komplekt_ved_id);
	}
	
	header("Location: komplekt_ved_files.php?komplekt_ved_id=".$komplekt_ved_id);
	die();
}


//�������� �����
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',300)){
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
	
	$_file=new FileDocFolderItem(4, $komplekt_ved_id, new KvFileItem(4));
	
	$file=$_file->GetItemById($id);
	
	
	
	if($file!==false){
		$_file->Del($id,$file,$result,300);
		
		$log->PutEntry($result['id'],'������ �����',NULL,300,NULL,'��� ����� '.SecStr($file['filename']));
		
	}
	
	header("Location: komplekt_ved_files.php?komplekt_ved_id=".$komplekt_ved_id.'&folder_id='.$folder_id);
	die();
}


//����������� ����� � ������
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',300)){
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
	
	
		  $_file=new KvFileItem(4);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //������ � ������, ���������...
	
		}
	}
	
	//���������� �����
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_file=new FileDocFolderItem(4, $komplekt_ved_id, new KvFileItem(4));
		  
		 
		    $_file->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 300);
			
			//������ � ������, ���������...
		}
	}
	
	
	
	
	
	header("Location: komplekt_ved_files.php?komplekt_ved_id=".$komplekt_ved_id.'&folder_id='.$folder_id);
	die();
}



//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=14;
	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//������ ������� �����������������
	/*$sm->assign('has_admin',(
							$au->user_rights->CheckAccess('x',101)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('101'));
	$admin=$dto->Draw('is_files.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	//$ffg=new KvFileGroup(4, $komplekt_ved_id, new FileDocFolderItem(4, $komplekt_ved_id, new KvFileItem(4)));
	$ffg=new KvFileGroup(4, $komplekt_ved_id, new FileDocFolderItem(4,  $komplekt_ved_id, new KvFileItem(4)));
	
	$ffg->SetDocIdName('komplekt_ved_id');
	
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$decorator->AddEntry(new UriEntry('komplekt_ved_id',$komplekt_ved_id));
	
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'komplekt_ved_files.php', 'komplekt_ved_file.html', 'swfupl-js/komplekt_ved_files.php', $au->user_rights->CheckAccess('w',298),   $au->user_rights->CheckAccess('w',300), $au->user_rights->CheckAccess('w',299), $folder_id,  $au->user_rights->CheckAccess('w',298), $au->user_rights->CheckAccess('w',299), $au->user_rights->CheckAccess('w',300), $au->user_rights->CheckAccess('w',300)  ,
	'',
	 $au->user_rights->CheckAccess('w',605),
	 $result
	
	);
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('ship',$user);
	$content=$sm->fetch('komplekt/files.html');
	
	
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