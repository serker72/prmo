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
require_once('classes/supplier_sh_item.php');
require_once('classes/supplier_sh_group.php');

require_once('classes/supplieritem.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Схема проезда к контрагенту');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['sup_id'])){
	if(!isset($_POST['sup_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $sup_id=abs((int)$_POST['sup_id']);
	
}else $sup_id=abs((int)$_GET['sup_id']); 


if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	

$_user=new SupplierItem;
$user=$_user->GetItemById($sup_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

require_once('classes/supplier_responsible_user_group.php');
if($user['is_org']==0){
/*проверка по ответственному сотруднику*/
	if(!$au->user_rights->CheckAccess('w',909)){
		$_sr=new SupplierResponsibleUserGroup;
		$_sr->GetUsersArr($sup_id, $ids);
		if(!in_array($result['id'], $ids)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
	}
}



$log=new ActionLog;


if(!$au->user_rights->CheckAccess('w',91)){
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
	$_ff=new FileDocFolderItem(3, $sup_id, new Supplier_Sh_Item(3));
	$_ff->SetTablename('supplier_shema_file_folder');
	
	$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false));   
	$_folder_desrc=' папка: '.$ff;
}
	
$log->PutEntry($result['id'],'открыл реестр схем проезда к контрагенту',NULL,91, NULL, $user['code'].' '.$user['full_name'].$_folder_desrc,$user_id);	




//удаление файлов
if(isset($_GET['action'])&&($_GET['action']==2)){
	if(!$au->user_rights->CheckAccess('w',320)){
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
	
	$_file=new Supplier_Sh_Item;
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'удалил схему проезда',NULL,320,NULL,'имя файла '.SecStr($file['orig_name']),$sup_id);
	}
	
	header("Location: supplier_shema.php?sup_id=".$sup_id);
	die();
}






//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',320)){
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
	
	$_ff=new FileDocFolderItem(3, $sup_id, new Supplier_Sh_Item(3));
	$_ff->SetTablename('supplier_shema_file_folder');
	$_ff->SetDocIdName('sup_id');
		
	
	
	$file=$_ff->GetItemById($id);
	
	
	
	if($file!==false){
		$_ff->Del($id,$file,$result,76);
		
		$log->PutEntry($result['id'],'удалил папку',NULL,320,NULL,'имя папки '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:supplier_shema.php?sup_id=".$sup_id.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	
	if(!$au->user_rights->CheckAccess('w',320)){
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
	
	
		  $_file=new Supplier_Sh_Item(3);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //записи в журнал, сообщения...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(3, $sup_id, new Supplier_Sh_Item(3));
	$_ff->SetTablename('supplier_shema_file_folder');
	$_ff->SetDocIdName('sup_id');
		
		
		
		  
		 
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 320);
			
			//записи в журнал, сообщения...
		}
	}
	
	
	
	
	
	header("Location:supplier_shema.php?sup_id=".$sup_id.'&folder_id='.$folder_id);
	die();
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=31;

	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',(
							$au->user_rights->CheckAccess('x',87)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('87'));
	$admin=$dto->Draw('contracts.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	//$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
	
	$decorator->AddEntry(new UriEntry('sup_id',$sup_id));
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$ffg=new Supplier_Sh_Group(3, $sup_id,  new FileDocFolderItem(3,  $sup_id, new Supplier_Sh_Item(3)));
	
	$filetext=$ffg->ShowFiles('doc_file/list_email.html', $decorator,$from,$to_page,'supplier_shema.php', 'shema.html', 'swfupl-js/shema.php', $au->user_rights->CheckAccess('w',318), $au->user_rights->CheckAccess('w',320), $au->user_rights->CheckAccess('w',319),  $folder_id, $au->user_rights->CheckAccess('w',318), $au->user_rights->CheckAccess('w',318), $au->user_rights->CheckAccess('w',320), $au->user_rights->CheckAccess('w',320));
	
	$sm->assign('log',$filetext);
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($user['opf_id']);
	$user['opf_name']=stripslashes($opf['name']);
	
	$sm->assign('user',$user);
	$content=$sm->fetch('suppliers/shema.html');
	
	
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