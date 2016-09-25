<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей


require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/sched_fileitem.php');
require_once('classes/sched_filegroup.php');

 

require_once('classes/sched.class.php');



if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Файлы планировщика');

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

$_user=new Sched_AbstractItem;
$user=$_user->GetItemById($bill_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',905)){ //705)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//журнал событий 
$_folder_desrc='';
if($folder_id==0){
	$_folder_desrc='';
}else{
	$_ff=new FileDocFolderItem(1, $bill_id, new SchedOtherFileItem(1));
	$_ff->SetTablename('sched_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
	$_folder_desrc=' папка: '.$ff;
}
	
$log->PutEntry($result['id'],'открыл реестр файлов планировщика',NULL,905, NULL, $user['code'].$_folder_desrc,$bill_id);	




//удаление файлов
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',907)){
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
	
	$_file=new SchedOtherFileItem;
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'удалил файл',NULL,907,NULL,'имя файла '.SecStr($file['orig_name']),$bill_id);
	}
	
	header("Location: sched_files.php?bill_id=".$bill_id);
	die();
}



//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',907)){
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
	
	$_ff=new FileDocFolderItem(1, $bill_id, new SchedOtherFileItem(1));
	$_ff->SetTablename('sched_file_folder');
	$_ff->SetDocIdName('doc_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,707);
		
		$log->PutEntry($result['id'],'удалил папку',NULL,907,NULL,'имя папки '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:sched_files.php?bill_id=".$bill_id.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',907)){
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
	
	
		  $_file=new SchedOtherFileItem(1);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //записи в журнал, сообщения...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(1, $bill_id, new SchedOtherFileItem(1));
	$_ff->SetTablename('sched_file_folder');
	$_ff->SetDocIdName('doc_id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 907);
			
			//записи в журнал, сообщения...
		}
	}
	
	
	header("Location:sched_files.php?bill_id=".$bill_id.'&folder_id='.$folder_id);
	die();
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


//$_menu_id=NULL;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
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
	
	$decorator->AddEntry(new UriEntry('bill_id',$bill_id));
	
	$ffg=new SchedOtherFileGroup(1, $bill_id,  new FileDocFolderItem(1,  $bill_id, new SchedOtherFileItem(1)));
	
	 
	
	$filetext=$ffg->ShowFiles('doc_file/list.html', $decorator,$from,$to_page, 'sched_files.php', 'sched_file.html', 'swfupl-js/sched_other_files.php', 
	
	/*
	$au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',707), $au->user_rights->CheckAccess('w',907), $folder_id, $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',907), $au->user_rights->CheckAccess('w',907), $au->user_rights->CheckAccess('w',907),
	*/
	  $au->user_rights->CheckAccess('w',905),
	 $au->user_rights->CheckAccess('w',918),
	 $au->user_rights->CheckAccess('w',905),
	$folder_id, 
	 $au->user_rights->CheckAccess('w',905), 
	 $au->user_rights->CheckAccess('w',905),
	  $au->user_rights->CheckAccess('w',918),
	  $au->user_rights->CheckAccess('w',905),
	 
	
	'',
	 $au->user_rights->CheckAccess('w',905),
	 $result
	
	 );
	
	
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('bill',$user);
	$content=$sm->fetch('plan/files.html');
	
	
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