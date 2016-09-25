<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');




$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

require_once('../classes/supplier_sh_item.php');
//require_once('../classes/supplieritem.php');
	


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


$_fi=new Supplier_Sh_Item;
	
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
	$log->PutEntry($result['id'],'дал схеме проезда описание',NULL,319,NULL,$txt);
	
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
		$log->PutEntry($result['id'],'дал схеме проезда описание',NULL,319,NULL,$txt);
	}
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>