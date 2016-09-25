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

require_once('classes/messagegroup.php');
require_once('classes/messageitem.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Сообщения');

$au=new AuthUser();
$result=$au->Auth();

$log=new ActionLog;

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
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
	
	header("Location: messages.php");	
	die();
}


//удаление сообщений
if(isset($_GET['doDelete'])||isset($_GET['doDelete_x'])){
	//из входящих
	$mi=new MessageItem();
	$mi->DropMissMessages($result['id']);
	
	foreach($_GET as $k=>$v){
		if(eregi('^select_',$k)){
			//$mail=
			//echo "$k=>$v";	
			//$mi->DelChain(abs((int)$v),$result['id']);
			$message=$mi->GetItemById(abs((int)$v));
			if($message['to_id']==$result['id']){
				$mi->DelFromFolder(abs((int)$v), 1)	;
				
				if($message['from_id']!=-1) $log->PutEntry($result['id'], "удалил сообщение",NULL,NULL,NULL,"папка: Входящие тема сообщения: ".$message['topic']." текст сообщения: ".$message['txt']);
				
			}
		}
	}
	
	
	$_uri_arr=array();
	foreach($_GET as $k=>$v){
		if(!eregi('select_',$k)&&!eregi('doDelete',$k)&&!eregi('doInp',$k)) $_uri_arr[]=$k.'='.urlencode($v);
			
	}
	
	header("Location: messages.php?".implode('&',$_uri_arr).'&show_message_deleted=1');	
	die();
	
	
}

if(isset($_GET['doDelete_2'])||isset($_GET['doDelete_2_x'])){
	//из исходящих
	$mi=new MessageItem();
	
	$mi->DropMissMessages($result['id']);
	foreach($_GET as $k=>$v){
		if(eregi('^select_',$k)){
			//$mail=
			//echo "$k=>$v";	
			//$mi->DelChain(abs((int)$v),$result['id']);
			$message=$mi->GetItemById(abs((int)$v));
			if($message['from_id']==$result['id']){
				$mi->DelFromFolder(abs((int)$v), 2)	;
				
				if($message['from_id']!=-1) $log->PutEntry($result['id'], "удалил сообщение",NULL,NULL,NULL,"папка: Исходящие тема сообщения: ".$message['topic']." текст сообщения: ".$message['txt']);
			}
		}
	}
	
	
	$_uri_arr=array();
	foreach($_GET as $k=>$v){
		if(!eregi('select_',$k)&&!eregi('doDelete_2',$k)&&!eregi('doInp',$k)) $_uri_arr[]=$k.'='.urlencode($v);
			
	}
	
	header("Location: messages.php?".implode('&',$_uri_arr).'&show_message_deleted=1');	
	die();	
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=4;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',5));
	$dto=new DiscrTableObjects($result['id'],array('5'));
	$admin=$dto->Draw('messages.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	
	
	$mg=new MessageGroup;
	$mg->SetAuthResult($result);
	$sm1=new SmartyAdm;
	
	
	//для входящих
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*12*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	
	$decorator->AddEntry(new SqlEntry('tf.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
		
	
	
	
	
	if(isset($_GET['sender_login'])&&(strlen($_GET['sender_login'])>0)){
		$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['sender_login']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('sender_login',$_GET['sender_login']));
	}
	
	if(isset($_GET['sender_login'])&&(strlen($_GET['receiver_login'])>0)){
		$decorator->AddEntry(new SqlEntry('r.login',SecStr($_GET['receiver_login']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('receiver_login',$_GET['receiver_login']));
	}
	
	if(isset($_GET['topic'])&&(strlen($_GET['topic'])>0)){
		$decorator->AddEntry(new SqlEntry('m.topic',SecStr($_GET['topic']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('topic',$_GET['topic']));
	}
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('tf.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('tf.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('r.login',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('r.login',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('m.topic',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('m.topic',SqlOrdEntry::ASC));
		break;
			
		default:
			$decorator->AddEntry(new SqlOrdEntry('tf.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	//построим список адресатов
	$addresses=$mg->DrawAdrArr($result['id'],$au->user_rights->CheckAccess('x',5));
	$sm1->assign('has_admin',$au->user_rights->CheckAccess('x',5));
	$sm1->assign('adr',$addresses);
	
	
	$mails=$mg->GetMessagesArr($result['id'], 1, $decorator,$from, $to_page); //GetMailsByUserId($result['id'],$decorator,$from, $to_page);
	
	//ссылка для кнопок сортировки
	$link=$decorator->GenFltUri();
	$link='messages.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
	$sm1->assign('link',$link);
	
	//заполним шаблон полями
	$fields=$decorator->GetUris();
	foreach($fields as $k=>$v){
		$sm1->assign($v->GetName(),$v->GetValue());	
	}
	
	
	//выведем сообщение об отправке
	if(isset($_GET['show_message'])&&($_GET['show_message']==1)){
		$sm1->assign('show_message',true);
	}
	
	//выведем сообщение об удалении
	if(isset($_GET['show_message_deleted'])&&($_GET['show_message_deleted']==1)){
		$sm1->assign('show_message_deleted',true);
	}
	
	
	$sm1->assign('from',$from);
	$sm1->assign('to_page',$to_page);
	$sm1->assign('items',$mails['items']);
	$sm1->assign('pages',$mails['pages']);
	$sm1->assign('prefix','');
	$sm1->assign('can_del_auto_now', $au->user_rights->CheckAccess('w',513));
	
	
	$sm1->assign('view',$mails['view']);
	$sm1->assign('unview',$mails['unview']);
	
	$sm->assign('mess', $sm1->fetch('messages1/header.html'));
	unset($sm1);
	
/***********************************************************************************/

	//для исходящих!
	$sm1=new SmartyAdm;
	
	if(isset($_GET['from_2'])) $from_2=abs((int)$_GET['from_2']);
	else $from_2=0;
	
	if(isset($_GET['to_page_2'])) $to_page_2=abs((int)$_GET['to_page_2']);
	else $to_page_2=ITEMS_PER_PAGE;
	
	$decorator_2=new DBDecorator;
	
	if(!isset($_GET['pdate1_2'])){
	
			$_pdate1_2=DateFromdmY(date("d.m.Y"))-60*60*24*30*12*3;
			$pdate1_2=date("d.m.Y", $_pdate1_2);//"01.01.2006";
		
	}else $pdate1_2 = $_GET['pdate1_2'];
	
	
	
	if(!isset($_GET['pdate2_2'])){
			
			$_pdate2_2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2_2=date("d.m.Y", $_pdate2_2);//"01.01.2006";	
	}else $pdate2_2 = $_GET['pdate2_2'];
	
	
	
	$decorator_2->AddEntry(new SqlEntry('tf.pdate',DateFromdmY($pdate1_2), SqlEntry::BETWEEN,DateFromdmY($pdate2_2)));
	$decorator_2->AddEntry(new UriEntry('pdate1_2',$pdate1_2));
	$decorator_2->AddEntry(new UriEntry('pdate2_2',$pdate2_2));
		
	
	
	
	
	if(isset($_GET['sender_login_2'])&&(strlen($_GET['sender_login_2'])>0)){
		$decorator_2->AddEntry(new SqlEntry('s.login',SecStr($_GET['sender_login_2']), SqlEntry::LIKE));
		$decorator_2->AddEntry(new UriEntry('sender_login_2',$_GET['sender_login_2']));
	}
	
	if(isset($_GET['sender_login_2'])&&(strlen($_GET['receiver_login_2'])>0)){
		$decorator_2->AddEntry(new SqlEntry('r.login',SecStr($_GET['receiver_login_2']), SqlEntry::LIKE));
		$decorator_2->AddEntry(new UriEntry('receiver_login_2',$_GET['receiver_login_2']));
	}
	
	if(isset($_GET['topic_2'])&&(strlen($_GET['topic_2'])>0)){
		$decorator_2->AddEntry(new SqlEntry('m.topic',SecStr($_GET['topic_2']), SqlEntry::LIKE));
		$decorator_2->AddEntry(new UriEntry('topic_2',$_GET['topic_2']));
	}
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode_2'])){
		$sortmode_2=0;	
	}else{
		$sortmode_2=abs((int)$_GET['sortmode_2']);
	}
	switch($sortmode_2){
		case 0:
			$decorator_2->AddEntry(new SqlOrdEntry('tf.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator_2->AddEntry(new SqlOrdEntry('tf.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator_2->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator_2->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator_2->AddEntry(new SqlOrdEntry('r.login',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator_2->AddEntry(new SqlOrdEntry('r.login',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator_2->AddEntry(new SqlOrdEntry('m.topic',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator_2->AddEntry(new SqlOrdEntry('m.topic',SqlOrdEntry::ASC));
		break;
			
		default:
			$decorator_2->AddEntry(new SqlOrdEntry('tf.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator_2->AddEntry(new UriEntry('sortmode_2',$sortmode_2));
	
	$decorator_2->AddEntry(new UriEntry('to_page_2',$to_page_2));
	
	
	$mails=$mg->GetMessagesArr($result['id'], 2, $decorator_2,$from_2, $to_page_2); 
	//$mails=$mg->GetMailsByUserId($result['id'],$decorator_2,$from_2, $to_page_2);
	
	//ссылка для кнопок сортировки
	$link=$decorator_2->GenFltUri();
	$link='messages.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
	$sm1->assign('link',$link);
	
	//заполним шаблон полями
	$fields=$decorator_2->GetUris();
	foreach($fields as $k=>$v){
		//echo eregi_replace("_2$","",$v->GetName()).' = '.$v->GetValue();
		$sm1->assign(eregi_replace("_2$","",$v->GetName()),$v->GetValue());	
	}
	
	
	//выведем сообщение об отправке
	if(isset($_GET['show_message'])&&($_GET['show_message']==1)){
		$sm1->assign('show_message',true);
	}
	
	//выведем сообщение об удалении
	if(isset($_GET['show_message_deleted'])&&($_GET['show_message_deleted']==1)){
		$sm1->assign('show_message_deleted_2',true);
	}
	
	
	
	$sm1->assign('from',$from);
	$sm1->assign('to_page',$to_page);
	$sm1->assign('items',$mails['items']);
	$sm1->assign('pages',$mails['pages']);
	$sm1->assign('prefix','_2');
	$sm1->assign('can_del_auto_now', $au->user_rights->CheckAccess('w',513));
	
	$sm1->assign('view',$mails['view']);
	$sm1->assign('unview',$mails['unview']);
	
	//$sm->assign('prefix','_2');
	$sm->assign('mess_2', $sm1->fetch('messages1/header.html'));	
	
	unset($sm1);
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	$content=$sm->fetch('messages1/messages.html');
	
	
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