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
require_once('classes/user_v_paspitem.php');
require_once('classes/user_v_paspgroup.php');

require_once('classes/user_v_item.php');
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


$_user=new UserVItem;
$user=$_user->GetItemById($user_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

$log=new ActionLog;

//

if(!$au->user_rights->CheckAccess('w',775)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//удаление файлов
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',777)){
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
	
	$_file=new UserVPasportItem(2);
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'удалил документы сотрудника',$user_id,777,NULL,'им€ файла '.SecStr($file['orig_name']),$user_id);
	}
	
	header("Location: user_v_pasp.php?user_id=".$user_id);
	die();
}


//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',777)){
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
	
	$_ff=new FileDocFolderItem(2, $user_id, new UserVPasportItem(2));
	$_ff->SetTablename('user_pasport_file_folder');
	$_ff->SetDocIdName('user_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,777);
		
		$log->PutEntry($result['id'],'удалил папку',NULL,777,NULL,'им€ папки '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:user_v_pasp.php?user_id=".$user_id.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',777)){
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
	
	
		  $_file=new UserVPasportItem(2);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //записи в журнал, сообщени€...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(2, $user_id, new UserVPasportItem(2));
	$_ff->SetTablename('user_pasport_file_folder');
	$_ff->SetDocIdName('user_id');
		
		
		  
		 
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 777);
			
			//записи в журнал, сообщени€...
		}
	}
	
	
	
	
	
	header("Location:user_v_pasp.php?user_id=".$user_id.'&folder_id='.$folder_id);
	die();
}



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	
	
	//демонстраци€ страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
	
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
	$ffg=new UserVPassportGroup(2,  $user_id,  new FileDocFolderItem(2,  $user_id, new UserVPasportItem(2)));
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page,'user_v_pasp.php', 'document_v.html', 'swfupl-js/user_v_pasp.php', $au->user_rights->CheckAccess('w',775), $au->user_rights->CheckAccess('w',777), $au->user_rights->CheckAccess('w',776), $folder_id,  $au->user_rights->CheckAccess('w',775),  $au->user_rights->CheckAccess('w',776),  $au->user_rights->CheckAccess('w',777),  $au->user_rights->CheckAccess('w',777) ,
	
	
	'',
	 $au->user_rights->CheckAccess('w',797),
	 $result
	
	);
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	/*$_opf=new OpfItem;
	$opf=$_opf->GetItemById($user['opf_id']);
	$user['opf_name']=stripslashes($opf['name']);
	*/
	$sm->assign('user',$user);
	$content=$sm->fetch('users_v/pasp.html');
	
	
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