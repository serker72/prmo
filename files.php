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
require_once('classes/fileitem.php');
require_once('classes/filegroup.php');

require_once('classes/abstractfilefoldergroup.php');

require_once('classes/filelitem.php');
require_once('classes/filelgroup.php');

require_once('classes/filepmitem.php');
require_once('classes/filepmgroup.php');

require_once('classes/filefolderitem.php');

require_once('classes/spitem.php');
require_once('classes/spgroup.php');

require_once('classes/spsitem.php');
require_once('classes/spsgroup.php');



require_once('classes/rl/rl_man.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Файлы и документы');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['file_from'])){
		$from=abs((int)$_SESSION['file_from']);
	}else $from=0;
	$_SESSION['file_from']=$from;
	
if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page='tabs-1';
	}else $tab_page=($_POST['tab_page']); 
}else $tab_page=($_GET['tab_page']);	


if(!isset($_GET['folder_id'])){
	if(!isset($_POST['folder_id'])){
		$folder_id=0;
	}else $folder_id=abs((int)$_POST['folder_id']); 
}else $folder_id=abs((int)$_GET['folder_id']);	

$log=new ActionLog;

 

if(!$au->user_rights->CheckAccess('w',28)&&!$au->user_rights->CheckAccess('w',556)&&!$au->user_rights->CheckAccess('w',560)&&!$au->user_rights->CheckAccess('w',29)&&!$au->user_rights->CheckAccess('w',476)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



/*есть ли доступ к папке по политике доступа к записи*/
//проверять, что за папка, к какому хранилищу относится.
//для этого хранилища проверять доступы
if($folder_id!=0){
	$_folder=new FileFolderItem;
	$folder=$_folder->GetItemById($folder_id);
	$_rl=new RLMan;
	$object_id=37;
	switch($folder['storage_id']){
		/*case 3:
			$object_id=37;
		break;
		case 4:
			$object_id=38;
		break;	
		case 5:
			$object_id=47;
		break;	*/
		
		 
		case 1:
			$object_id=37;
		break;
		
		 case 2:
			$object_id=35;
		break;
		case 3:
			$object_id=36;
		break;	
		
		case 4:
			$object_id=38;
		break;	
		case 5:
			$object_id=47;
		break;	
	 

	}
	
/*	echo '<pre>';
	print_r($folder); echo $object_id;
	echo '</pre>';
	*/
	
	if(!$_rl->CheckFullAccess($result['id'], $folder_id, $object_id, 'w', 'file', $folder['storage_id'])){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
}



//журнал событий 
if($tab_page=='tabs-1'){
	$_folder_desrc='';
	if($folder_id==0){
		$_folder_desrc='';
	}else{
		$_ff=new FileFolderItem(1);
		
		$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
		$_folder_desrc=' папка: '.$ff;
	}
		
	$log->PutEntry($result['id'],'открыл раздел Файлы и документы - Файлы и документы',NULL,28, NULL, $_folder_desrc);	
}elseif($tab_page=='tabs-3'){
	$_folder_desrc='';
	if($folder_id==0){
		$_folder_desrc='';
	}else{
		$_ff=$_file=new FileFolderItem(4);
		
		$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
		$_folder_desrc=' папка: '.$ff;
	}
		
	$log->PutEntry($result['id'],'открыл раздел Файлы и документы - Письма',NULL,556, NULL,  $_folder_desrc);	
}elseif($tab_page=='tabs-4'){
	$_folder_desrc='';
	if($folder_id==0){
		$_folder_desrc='';
	}else{
		$_ff=$_file=new FileFolderItem(5);
		
		$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
		$_folder_desrc=' папка: '.$ff;
	}
		
	$log->PutEntry($result['id'],'открыл раздел Файлы и документы - Файлы +/-',NULL,560, NULL,  $_folder_desrc);	
}elseif($tab_page=='tabs-5'){
	$_folder_desrc='';
	if($folder_id==0){
		$_folder_desrc='';
	}else{
		$_ff=new FileFolderItem(2);
		
		$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
		$_folder_desrc=' папка: '.$ff;
	}
		
	$log->PutEntry($result['id'],'открыл раздел Файлы и документы - Справочная информация',NULL,29, NULL, $_folder_desrc);	
}elseif($tab_page=='tabs-6'){
	$_folder_desrc='';
	if($folder_id==0){
		$_folder_desrc='';
	}else{
		$_ff=$_file=new FileFolderItem(3);
		
		$ff=strip_tags($_ff->DrawNavigCli($folder_id, '', '/', false)); 
		$_folder_desrc=' папка: '.$ff;
	}
		
	$log->PutEntry($result['id'],'открыл раздел  Файлы и документы - Спецдокументы',NULL,476, NULL,  $_folder_desrc);	
}











//удаление файлов
if(isset($_GET['action'])&&($_GET['action']==2)){
	 
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	 
	if($tab_page=='tabs-1') $_file=new FilePoItem;
	elseif($tab_page=='tabs-3')  $_file=new FileLetItem;
	elseif($tab_page=='tabs-5') $_file=new SpItem;
	elseif($tab_page=='tabs-6')  $_file=new SpsItem;

	else  $_file=new FilePmItem;
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		if($tab_page=='tabs-1') $log->PutEntry($result['id'],'удалил файл в разделе Файлы и документы',NULL,574,NULL,'имя файла '.SecStr($file['orig_name']));
		elseif($tab_page=='tabs-3')  $log->PutEntry($result['id'],'удалил файл в разделе Файлы и документы - Письма',NULL,578,NULL,'имя файла '.SecStr($file['orig_name']));
		elseif($tab_page=='tabs-5') $log->PutEntry($result['id'],'удалил файл в разделе  Файлы и документы - Справочная информация',NULL,33,NULL,'имя файла '.SecStr($file['orig_name']));
		elseif($tab_page=='tabs-6')  $log->PutEntry($result['id'],'удалил файл в разделе  Файлы и документы - Спецдокументы',NULL,479,NULL,'имя файла '.SecStr($file['orig_name']));
		

		else  $log->PutEntry($result['id'],'удалил файл в разделе Файлы и документы - Файлы +/-',NULL,582,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: files.php?tab_page=".$tab_page.'&folder_id='.$folder_id);
	die();
}

//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	 
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	if($tab_page=='tabs-1') $_file=new FileFolderItem(1);
	elseif($tab_page=='tabs-3')  $_file=new FileFolderItem(4);
	elseif($tab_page=='tabs-5') $_file=new FileFolderItem(2);
	elseif($tab_page=='tabs-6')  $_file=new FileFolderItem(3);

	else  $_file=new FileFolderItem(5);
	$file=$_file->GetItemById($id);
	
	
	
	if($file!==false){
		$_file->Del($id,$file,$result);
		
		
		if($tab_page=='tabs-1') $log->PutEntry($result['id'],'удалил папку в разделе Файлы и документы',NULL,32,NULL,'имя папки '.SecStr($file['filename']));
		elseif($tab_page=='tabs-3')  $log->PutEntry($result['id'],'удалил папку в разделе Файлы и документы - Письма',NULL,556,NULL,'имя папки '.SecStr($file['filename']));
		
		elseif($tab_page=='tabs-5') $log->PutEntry($result['id'],'удалил папку в разделе  Файлы и документы - Справочная информация',NULL,566,NULL,'имя папки '.SecStr($file['filename']));
		
		elseif($tab_page=='tabs-6')   $log->PutEntry($result['id'],'удалил папку в разделе  Файлы и документы - Спецдокументы',NULL,570,NULL,'имя папки '.SecStr($file['filename']));
		

		else  $log->PutEntry($result['id'],'удалил папку в разделе Файлы и документы - Файлы +/-',NULL,560,NULL,'имя папки '.SecStr($file['filename']));
	}
	
	header("Location: files.php?tab_page=".$tab_page.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	 
	
	//
	$move_folder_id=abs((int)$_GET['move_folder_id']);
	
	//обработать файлы
	$files=$_GET['check_file'];
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		  if($tab_page=='tabs-1') $_file=new FilePoItem;
		  elseif($tab_page=='tabs-3')  $_file=new FileLetItem;
		  elseif($tab_page=='tabs-5') $_file=new SpItem;
		   elseif($tab_page=='tabs-6')   $_file=new SpSItem;

		  else  $_file=new FilePmItem;
		  
		  //$file=$_file->GetItemById($v);
		  
		  $_file->Edit($v, array('folder_id'=>$move_folder_id),NULL,$result);
		  
		  //записи в журнал, сообщения...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	if($tab_page=='tabs-1') $_file=new FileFolderItem(1);
			elseif($tab_page=='tabs-3')  $_file=new FileFolderItem(4);
			elseif($tab_page=='tabs-5') $_file=new FileFolderItem(2);
			elseif($tab_page=='tabs-6')  $_file=new FileFolderItem(3);
			else  $_file=new FileFolderItem(5);
		  
		 
		    $_file->Edit($v, array('parent_id'=>$move_folder_id),NULL,$result);
			
			//записи в журнал, сообщения...
		}
	}
	
	
	
	
	
	header("Location: files.php?tab_page=".$tab_page.'&folder_id='.$folder_id);
	die();
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=13;

	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	 
	 
//*******************************************************************	
//раздел ФАЙЛЫ и Документы
	
	
	
	$decorator=new DBDecorator;
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	if($tab_page=='tabs-1'){
		$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		$folder_id_own=$folder_id;
		$from_own=$from;
		
	}else{
		$decorator->AddEntry(new SqlEntry('folder_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',0));
		$folder_id_own=0;
		$from_own=0;
	}
	//$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	$decorator->AddEntry(new UriEntry('tab_page','tabs-1'));
	
	
	 
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$ffg=new FilePoGroup(1);
	
	$ffg->SetPageName('files.php');
	
	$filetext=$ffg->ShowFiles('files/list.html', $decorator,$from_own,$to_page,'files.php', 'load.html', 'swfupl-js/files.php', $au->user_rights->CheckAccess('w',30), $au->user_rights->CheckAccess('w',32), $au->user_rights->CheckAccess('w',60),$folder_id_own,  $au->user_rights->CheckAccess('w',572), $au->user_rights->CheckAccess('w',573), $au->user_rights->CheckAccess('w',574), $au->user_rights->CheckAccess('w',575),
	
		
	'',
	 $result,
	$au->user_rights->CheckAccess('w',824),
	
	$au->user_rights->CheckAccess('w',1), //19
	 	
		
		37,
		18,
		19,
		20,
		21,
		22,
		23,
		24,
		25,
		
		5
	 );
	
	$sm->assign('log',$filetext);
	$sm->assign('has_files',  $au->user_rights->CheckAccess('w',28));
	
	
	
//************ письма *********************************************************************************************	
	
	
	$decorator=new DBDecorator;
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	//$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	if($tab_page=='tabs-3'){
		$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		$folder_id_own=$folder_id;
		$from_own=$from;
		
	}else{
		$decorator->AddEntry(new SqlEntry('folder_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',0));
		$folder_id_own=0;
		$from_own=0;
	}
	$decorator->AddEntry(new UriEntry('tab_page','tabs-3'));
	
	
	 
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$ffg=new FileLetGroup;
	
	$ffg->SetPageName('files.php');
	
	$filetext=$ffg->ShowFiles('files/list.html', $decorator,$from_own,$to_page,'filesl.php', 'load_l.html', 'swfupl-js/filesl.php',  $au->user_rights->CheckAccess('w',557),   $au->user_rights->CheckAccess('w',559),  $au->user_rights->CheckAccess('w',558),$folder_id_own,  $au->user_rights->CheckAccess('w',576), $au->user_rights->CheckAccess('w',577), $au->user_rights->CheckAccess('w',578), $au->user_rights->CheckAccess('w',579) , '4',
	$result,
	 $au->user_rights->CheckAccess('w',828),
	 
	  $au->user_rights->CheckAccess('w',1),
	   
	   38,
		26,
		27,
		28,
		29,
		30,
		31,
		32,
		33,
	 
	 	40
	  );
	
	$sm->assign('log3',$filetext);
	
	
	$sm->assign('has_letters',  $au->user_rights->CheckAccess('w',556));
	
	
	
	
/** файлы +/- ********************************************************************************************************/	
	
	$decorator=new DBDecorator;
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	//$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	if($tab_page=='tabs-4'){
		$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		$folder_id_own=$folder_id;
		$from_own=$from;
		
	}else{
		$decorator->AddEntry(new SqlEntry('folder_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',0));
		$folder_id_own=0;
		$from_own=0;
	}
	
	
	$decorator->AddEntry(new UriEntry('tab_page','tabs-4'));
	
	 
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$ffg=new FilePmGroup;
	
	$ffg->SetPageName('files.php');
	 
	
	$filetext=$ffg->ShowFiles(
	'files/list.html', //1
	$decorator,	//2
	$from_own,	//3
	$to_page,	//4
	'filespm.php', //5
	'load_pm.html', //6
	'swfupl-js/filespm.php',  //7
	$au->user_rights->CheckAccess('w',561),   //8
	$au->user_rights->CheckAccess('w',563),  //9
	$au->user_rights->CheckAccess('w',562),	//10
	$folder_id_own,  //11
	$au->user_rights->CheckAccess('w',580), //12
	$au->user_rights->CheckAccess('w',581), //13
	$au->user_rights->CheckAccess('w',582), //14
	$au->user_rights->CheckAccess('w',583), //15
	
	'5',	//16
	
	 $result,	//17
	$au->user_rights->CheckAccess('w',827),	//18
	 $au->user_rights->CheckAccess('w',1),	//19
	   
	  47,  
		39,  
		40, 
		41,	 
		42,	 
		43,	 
		44, 
		45,	 
		46, 
		
		41
		
	);
	
	$sm->assign('log4',$filetext);
	
	
	$sm->assign('has_pmfiles',  $au->user_rights->CheckAccess('w',560));
	
	
	
	
		
//*******************************************************************
// раздел справочная информация (FAQ)	
	
	$decorator=new DBDecorator;
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	if($tab_page=='tabs-5'){
		$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		$folder_id_own=$folder_id;
		$from_own=$from;
		
	}else{
		$decorator->AddEntry(new SqlEntry('folder_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',0));
		$folder_id_own=0;
		$from_own=0;
	}
	//$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	$decorator->AddEntry(new UriEntry('tab_page','tabs-5'));
	
	
	
 
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$ffg=new SpGroup(2);
	
	$ffg->SetPageName('files.php');
	
	 $filetext=$ffg->ShowFiles( 
		'files/list.html', //1
		$decorator,	//2
		$from_own,	//3
		$to_page,	//4
		'spravinfo.php', //5
		'load_pl.html', //6
		'swfupl-js/pricelists.php', //7
		$au->user_rights->CheckAccess('w',31), //8
		$au->user_rights->CheckAccess('w',33), //9
		$au->user_rights->CheckAccess('w',61), //10
		$folder_id_own,  //11
		$au->user_rights->CheckAccess('w',564), //12
		$au->user_rights->CheckAccess('w',565), //13
		$au->user_rights->CheckAccess('w',566), //14
		$au->user_rights->CheckAccess('w',567), //15
		'2', //16
		$result, //17
		$au->user_rights->CheckAccess('w',823), //18
		$au->user_rights->CheckAccess('w',1), //19
		35,
		2,
		3,
		4,
		5,
		6,
		7,
		8,
		9,
		
		6
	 	
	);
	 
	
	$sm->assign('log_faq',$filetext);	
	
	$sm->assign('has_faq', $au->user_rights->CheckAccess('w',29));
	
	
	
	
	
//************************************************************************
//раздел Спецдокументы
	$decorator=new DBDecorator;
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	
	if($tab_page=='tabs-6'){
		$decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		$folder_id_own=$folder_id;
		$from_own=$from;
		
	}else{
		$decorator->AddEntry(new SqlEntry('folder_id',0, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('folder_id',0));
		$folder_id_own=0;
		$from_own=0;
	}
	$decorator->AddEntry(new UriEntry('tab_page','tabs-6'));
	 
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$ffg=new SpsGroup(3);
	
	$ffg->SetPageName('files.php');
	
	$filetext=$ffg->ShowFiles('files/list.html', $decorator,$from_own,$to_page,'sps.php', 'load_spl.html', 'swfupl-js/sps.php', $au->user_rights->CheckAccess('w',477),  $au->user_rights->CheckAccess('w',479), $au->user_rights->CheckAccess('w',478), $folder_id_own,  $au->user_rights->CheckAccess('w',568), $au->user_rights->CheckAccess('w',569), $au->user_rights->CheckAccess('w',570), $au->user_rights->CheckAccess('w',571), '3',
	
	 $result,
	 $au->user_rights->CheckAccess('w',826),
	 $au->user_rights->CheckAccess('w',1),
	 
	  36, //20
		10,
		11,
		12,
		13,
		14,
		15,
		16,
		17,
		
		38
	
	 );
	
	$sm->assign('log_spec',$filetext);
	
	
	$sm->assign('has_spec',  $au->user_rights->CheckAccess('w',476));	
	
	
	
	
	
	
	

	
	
	
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('files/files.html');
	
	
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