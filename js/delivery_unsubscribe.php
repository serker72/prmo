<?
session_start();
require_once('../classes/global.php');
require_once('../classes/supplieritem.php');
require_once('../classes/v2/delivery.class.php');
require_once('../classes/v2/delivery_lists.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/phpmailer/class.phpmailer.php');


if(isset($_POST['action'])&&($_POST['action']=="unsubscribe")){
	$_di=new Delivery_Item;
	$_du=new Delivery_UserItem;
	$_dl=new Delivery_ListItem;
	
	//проверка корректности параметров
	$id=abs((int)$_POST['id']); $list_id=abs((int)$_POST['list_id']);
	$du=$_du->GetItemByFields(array('id'=>$id, 'list_id'=>$list_id, 'is_subscribed'=>1));
	$dl=$_dl->GetItemById($list_id);
	
	if(($du!==false)&&($dl!==false)){
		$_du->Edit($id, array('is_subscribed'=>0,
							  'unsubscribe_way'=>SecStr(iconv('utf-8', 'windows-1251', $_POST['reason'])),
							  'unsubscribe_reason'=>SecStr(iconv('utf-8', 'windows-1251', $_POST['reason_txt'])),
							  'unsubscribed_delivery_id'=>abs((int)$_POST['delivery_id'])
							  ));
	}
	
}


?>