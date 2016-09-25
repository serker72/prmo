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
require_once('classes/invcalcfileitem.php');
require_once('classes/invcalcfilegroup.php');

require_once('classes/supplieritem.php');

require_once('classes/invcalcitem.php');


if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� ������������ �� ��������������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['bill_id'])){
	if(!isset($_POST['bill_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $bill_id=abs((int)$_POST['bill_id']);
	
}else $bill_id=abs((int)$_GET['bill_id']); 

$_user=new InvCalcItem;
$user=$_user->GetItemById($bill_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

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
	
	header("Location: invent.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',450)){ //192)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}




//�������� ������
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',457)){
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
	
	$_file=new InvCalcFileItem(3);
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'������ ����',NULL,457,NULL,'��� ����� '.SecStr($file['orig_name']),$bill_id);
	}
	
	header("Location: invcalc_akt_files.php?bill_id=".$bill_id);
	die();
}

//�������� �����
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',457)){
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
	
	$_ff=new FileDocFolderItem(3, $bill_id, new InvCalcFileItem(3));
	$_ff->SetTablename('invcalc_file_folder');
	$_ff->SetDocIdName('bill_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,457);
		
		$log->PutEntry($result['id'],'������ �����',NULL,457,NULL,'��� ����� '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:invcalc_akt_files.php?bill_id=".$bill_id.'&folder_id='.$folder_id);
	die();
}


//����������� ����� � ������
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',457)){
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
	
	
		  $_file=new InvCalcFileItem(3);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //������ � ������, ���������...
	
		}
	}
	
	//���������� �����
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(3, $bill_id, new InvCalcFileItem(3));
	$_ff->SetTablename('invcalc_file_folder');
	$_ff->SetDocIdName('bill_id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 457);
			
			//������ � ������, ���������...
		}
	}
	
	
	header("Location:invcalc_akt_files.php?bill_id=".$bill_id.'&folder_id='.$folder_id);
	die();
}




//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=39;
	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//������ ������� �����������������
	/*$sm->assign('has_admin',(
							$au->user_rights->CheckAccess('x',93)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('93'));
	$admin=$dto->Draw('bill_files.php','admin/admin_objects.html');
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
	
	$decorator->AddEntry(new UriEntry('bill_id',$bill_id));
	
	
	$_itm=new InvCalcFileItem(3);
	$_itm->setpagename('invcalc_akt_files.php');
	
	
	
	$ffg=new InvCalcFileGroup(3, $bill_id, new FileDocFolderItem(3,  $bill_id, $_itm));
	
	$ffg->setpagename('invcalc_akt_files.php');
	
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'invcalc_akt_files.php', 'invcalc_akt_file.html', 'swfupl-js/invcalc_akt_files.php', $au->user_rights->CheckAccess('w',455), $au->user_rights->CheckAccess('w',457)/*&&($user['is_confirmed_shipping']==0)*/, $au->user_rights->CheckAccess('w',456), $folder_id, $au->user_rights->CheckAccess('w',455), $au->user_rights->CheckAccess('w',456),$au->user_rights->CheckAccess('w',457),$au->user_rights->CheckAccess('w',457) );
	
	
	//$filetext=$ffg->ShowFiles($bill_id,'invcalc/files_list.html', $decorator,$from,$to_page,'invcalc_akt_files.php', 'invcalc_akt_file.html', 'swfupl-js/invcalc_akt_files.php', $au->user_rights->CheckAccess('w',455), $au->user_rights->CheckAccess('w',457)/*&&($user['is_confirmed_shipping']==0)*/, $au->user_rights->CheckAccess('w',456));
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('bill',$user);
	$content=$sm->fetch('invcalc/akt_files.html');
	
	
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