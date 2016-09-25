<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

 
 
require_once('../classes/v2/delivery_lists.class.php');
 
require_once('../classes/v2/delivery_templates.class.php'); 
	

//setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');

 

	
$ret='';

// 
if(isset($_POST['action'])&&($_POST['action']=="save_data")){
	
	$id=abs((int)$_POST['id']);
	 
	$data=SecStr(iconv('utf-8', 'windows-1251',$_POST['data']));
	
	$_ui=new Delivery_TemplateItem;
	
	$_ui->Edit($id, array('html_content'=>$data)); 
	 
	
} 
elseif(isset($_POST['action'])&&($_POST['action']=="save_new_template")){
	
//	$id=abs((int)$_POST['id']);
	 
	$html_content=SecStr(iconv('utf-8', 'windows-1251',$_POST['html_content']));
	$name=SecStr(iconv('utf-8', 'windows-1251',$_POST['name']));
	
	$_ui=new Delivery_TemplateItem;
	
	$_ui->Add(array('name'=>$name, 'html_content'=>$html_content)); 
	 
	
} 


echo $ret;	
?>