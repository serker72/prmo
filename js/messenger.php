<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_s_item.php');
require_once('../classes/questionitem.php');
require_once('../classes/messenger_group.php');
require_once('../classes/messengeritem.php');
require_once('../classes/usersession.php');


$au=new AuthUser();
$result=$au->Auth(false,false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}



if(isset($_POST['action'])&&($_POST['action']=="create_messenger")){
	//создание окна мессенджера
	$sm=new SmartyAj;
	
	$_SESSION['messenger_state']=1;
	/*
	1 - развернут
	2 - свернут
	
	*/
	
	
	
	/*if(isset($_COOKIE['messenger_sort_mode'])){
		$sort_mode=abs((int)$_COOKIE['messenger_sort_mode']);
		if(($sort_mode<1)||($sort_mode>2)) $sort_mode=2;

	}else $sort_mode=2;*/
	$sort_mode=2;
	
	if(isset($_SESSION['selected_users'])&&is_array($_SESSION['selected_users'])) $selected_users=$_SESSION['selected_users'];
	else $selected_users=array();
		
		
	$_mg=new MessengerGroup;
	//адресаты
	$addr=$_mg->DrawAdrArr($result['id'], true, $sort_mode, $selected_users, $result); //DrawAdrArr
	$sm->assign('has_new_messages', $_mg->CalcNew($result['id'])>0);
	
	$sm->assign('adr', $addr);
	$sm->assign('sort_mode', $sort_mode);
	
	$ret=$sm->fetch('messenger/messenger.html');
}

elseif(isset($_POST['action'])&&($_POST['action']=="put_state")){
	
	$_SESSION['messenger_state']=abs((int)$_POST['state']);
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="show_addresses")||($_POST['action']=="refresh_addresses"))){
	
	$sort_mode=abs((int)$_POST['sort_mode']);
	if(($sort_mode<1)||($sort_mode>2)) $sort_mode=2;

	
	$selected_users=$_POST['selected_users'];
	if($_POST['action']=="show_addresses") $_SESSION['selected_users']=$_POST['selected_users'];
	
	$string_filter=SecStr(iconv('utf-8', 'windows-1251', SecStr($_POST['string_filter'])));
	
	
	$sm=new SmartyAj;
	
	$_mg=new MessengerGroup;
	$addr=$_mg->DrawAdrArr($result['id'], true, $sort_mode, $selected_users, $result, $string_filter);
	$sm->assign('adr', $addr);
	$sm->assign('sort_mode', $sort_mode);
	
	$ret=$sm->fetch('messenger/addresses.html');
	/*
	$arr1=array();
	if($_POST['action']=="refresh_addresses"){
		foreach($addr as $k=>$v) if($v['is_selected']) $arr1[]=$v['name_s'];	
		print_r($arr1);
		//$ret='';
	}*/
	
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="put_selected_users")){
	
	$_SESSION['selected_users']=$_POST['selected_users'];
	
}
//отправка сообщения
elseif(isset($_POST['action'])&&($_POST['action']=="send_message")){
	
	$selected_users =$_POST['selected_users'];
	
	$message=SecStr(iconv('utf-8', 'windows-1251', $_POST['message']));
	
	$_mi=new MessengerItem;
	
	foreach($selected_users as $k=>$v){
		
		$params=array('from_id'=>$result['id'], 'to_id'=>abs((int)$v), 'txt'=>$message, 'pdate'=>time(), 'unread'=>1);
		
		$_mi->Send(0, $result['id'], $params);	
		
		$log->PutEntry($result['id'], "отправил мгновенное сообщение",$params['to_id'],NULL,NULL,"текст сообщения: ".$params['txt']);
	}
	
	
}
//подгрузка чата
elseif(isset($_POST['action'])&&($_POST['action']=="load_chat")){
	$days =abs((int)$_POST['days']);
	$selected_users =$_POST['selected_users'];
	
	$_mg=new MessengerGroup;
	if(count($selected_users)==1){
		
		
		$ret=$_mg->LoadChat(0, $days, $result['id'], abs((int)$selected_users[0]),  'messenger/chat.html', true, $result);
		//$ret='zz';
	}else{
		$ret=$_mg->LoadChat(0, $days, $result['id'],  $selected_users,  'messenger/chat.html', true, $result);
	}
}
//частичная подгрузка чата
elseif(isset($_POST['action'])&&($_POST['action']=="partial_load_chat")){
	$days =abs((int)$_POST['days']);
	$selected_users =$_POST['selected_users'];
	$last_message_id =abs((int)$_POST['last_message_id']);
	
	$_mg=new MessengerGroup;
	
	if(count($selected_users)==1){
		
		
		$ret=$_mg->LoadChat($last_message_id, $days, $result['id'], abs((int)$selected_users[0]),  'messenger/chat.html', true, $result);
		//$ret='zz';
	}else{
		$ret=$_mg->LoadChat($last_message_id, $days, $result['id'],  $selected_users,  'messenger/chat.html', true, $result);	
	}
}

//проверка контакта
elseif(isset($_GET['action'])&&($_GET['action']=="check_contact")){
	$contact_id =abs((int)$_GET['contact_id']);
	
	$rret=array();
	
	$_mg=new MessengerGroup;
	
	$is_new_messages=$_mg->CalcNewFrom($contact_id, $result['id']);
	
	$rret[]='"is_new_messages":"'.(int)($is_new_messages>0).'"';
	//$rret[]='"is_new_messages":"'.(int)($is_new_messages>0).'"';
	
	$_us=new UserSession;
	
	$test=$_us->GetItemByFields(array('user_id'=>$contact_id));
	$rret[]='"is_online":"'.(int)($test!==false).'"';
	
	$ret='{'.implode(', ',$rret).'}';
}

//заносим в лог закрытие-открытие
elseif(isset($_POST['action'])&&($_POST['action']=="log")){
	$mode =abs((int)$_POST['mode']);
	if($mode==0) $description='закрыл окно мгновенных сообщений';
	else  $description='открыл окно мгновенных сообщений';
	
	$log->PutEntry($result['id'], $description, NULL, NULL, NULL, '');
	
}
//заносим в лог прочтение сообщений
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_read")){
	/*$mode =abs((int)$_POST['mode']);
	if($mode==0) $description='закрыл окно мгновенных сообщений';
	else  $description='открыл окно мгновенных сообщений';
	
	$log->PutEntry($result['id'], $description, NULL, NULL, NULL, '');*/
	
	$_mi=new MessengerItem;
	foreach($_POST['messages'] as $k=>$v){
		$_mi->SetRead($v,NULL,$result);	
	}
	
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>