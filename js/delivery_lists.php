<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/supplieritem.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

 
 
require_once('../classes/v2/delivery_lists.class.php');
 
	

//setlocale(LC_ALL, 'ru_RU.CP1251', 'rus_RUS.CP1251', 'Russian_Russia.1251');

 

	
$ret='';

// 
if(isset($_POST['action'])&&($_POST['action']=="check_email_user")){
	
	$current_id=abs((int)$_POST['current_id']);
	$list_id=abs((int)$_POST['list_id']);
	
	$email=SecStr($_POST['email']);
	
	$_ui=new Delivery_UserItem;
	
	$ui=$_ui->getitembyfields(array('list_id'=>$list_id, 'email'=>$email), array('id'=>$current_id));
	if($ui!==false) $ret=1;
	else $ret=0; 
	 
	
} 
//получить список пользователей с отметкой, если они есть в выбранном сегменете
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	$current_id=abs((int)$_POST['current_id']);
	$list_id=abs((int)$_POST['list_id']);
	
	$_se=new Delivery_SegmentItem;
	
	$arr=$_se->LoadUsersArr($list_id, $current_id);
	
	$sm=new SmartyAj;
	$sm->assign('items', $arr);
	$ret=$sm->fetch('delivery/users_in_segment.html');
	
}

echo $ret;	
?>