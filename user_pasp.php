<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/userpaspitem.php');
require_once('classes/userpaspgroup.php');

require_once('classes/user_s_item.php');
//require_once('classes/opfitem.php');





$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'ƒокументы сотрудника');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['user_id'])){
	if(!isset($_POST['user_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $user_id=abs((int)$_POST['user_id']);
	
}else $user_id=abs((int)$_GET['user_id']); 

if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	


$_user=new UserSItem;
$user=$_user->GetItemById($user_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

$log=new ActionLog;

//34 и 38 не вид€т свои документы!!!
		if( (($user_id==34)&&(($result['id']==34)||($result['id']==38))) ||
		
			(($user_id==38)&&(($result['id']==38)))
			){
				header("HTTP/1.1 403 Forbidden");
				header("Status: 403 Forbidden");
				include("403.php");
				die();	
				
			}


//внесение изменений в права
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
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: users_s.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',119)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$_folder_desrc='';
if($folder_id==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(1,  $user_id, new UserPasportItem(1));
	$_ff->SetTablename('user_pasport_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
	$_folder_desrc=' папка: '.$ff;
}
	
$log->PutEntry($result['id'],'открыл реестр документов сотрудника',NULL,119, NULL, $user['login'].' '.$user['name_s'].$_folder_desrc,$user_id);	



//удаление файлов
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',159)){
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
	
	$_file=new UserPasportItem(1);
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'удалил документы сотрудника',$user_id,159,NULL,'им€ файла '.SecStr($file['orig_name']),$user_id);
	}
	
	header("Location: user_pasp.php?user_id=".$user_id);
	die();
}


//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',159)){
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
	
	$_ff=new FileDocFolderItem(1, $user_id, new UserPasportItem(1));
	$_ff->SetTablename('user_pasport_file_folder');
	$_ff->SetDocIdName('user_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,159);
		
		$log->PutEntry($result['id'],'удалил папку',NULL,159,NULL,'им€ папки '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:user_pasp.php?user_id=".$user_id.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',159)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	//
	$move_folder_id=abs((int)$_GET['move_folder_id']);
	
	//обработать файлы
	$files=$_GET['check_file'];
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		  $_file=new UserPasportItem(1);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //записи в журнал, сообщени€...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(1, $user_id, new UserPasportItem(1));
	$_ff->SetTablename('user_pasport_file_folder');
	$_ff->SetDocIdName('user_id');
		
		
		  
		 
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 159);
			
			//записи в журнал, сообщени€...
		}
	}
	
	
	
	
	
	header("Location:user_pasp.php?user_id=".$user_id.'&folder_id='.$folder_id);
	die();
}



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=8;

	include('inc/menu.php');
	
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрировани€
	/*$sm->assign('has_admin',(
							$au->user_rights->CheckAccess('x',119)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('119'));
	$admin=$dto->Draw('contracts.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	//$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$decorator->AddEntry(new UriEntry('user_id',$user_id));
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	//$ffg=new UserPassportGroup(1); //ContractGroup(3);
	$ffg=new UserPassportGroup(1,  $user_id,  new FileDocFolderItem(1,  $user_id, new UserPasportItem(1)));
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'user_pasp.php', 'document.html', 'swfupl-js/user_pasp.php', $au->user_rights->CheckAccess('w',119), $au->user_rights->CheckAccess('w',159), $au->user_rights->CheckAccess('w',158), $folder_id,  $au->user_rights->CheckAccess('w',119),  $au->user_rights->CheckAccess('w',158),  $au->user_rights->CheckAccess('w',159),  $au->user_rights->CheckAccess('w',159) );
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	/*$_opf=new OpfItem;
	$opf=$_opf->GetItemById($user['opf_id']);
	$user['opf_name']=stripslashes($opf['name']);
	*/
	$sm->assign('user',$user);
	$content=$sm->fetch('users/pasp.html');
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>