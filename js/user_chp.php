<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/usersgroup.php');
require_once('../classes/useritem.php');
require_once('../classes/user_s_item.php');
require_once('../classes/questionitem.php');


require_once('../classes/usercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/usercontactdataitem.php');

require_once('../classes/user_int_item.php');

require_once('../classes/user_int_group.php');

 
 




$ret='';


function GetMessageText($user){
	$text='';
	
	$text.='<div>';
	
	$text.='<em>Данное сообщение сформировано автоматически, просьба не отвечать на него.</em>';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	$text.='<div>';
	
	$text.='Уважаемый(ая) '.$user['name_s'].'!';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';	
	
	
	$text.='<div>';
	
	$text.='Ваш доступ к программе "'.SITETITLE.'" <a href="'.SITEURL.'">'.SITEURL.'</a>:';
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='Логин: '.$user['email_s'];
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='Пароль: '.$user['setted_password'];
	
	
	
	$text.='</div>';
	
	
	
		$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	
	
	$text.='<div>';
	
	$text.='С уважением, программа "'.SITETITLE.'".';
	
	
	
	$text.='</div>';
	
	return $text;	
	
}

$topic='Доступ к программе '.SITETITLE;

if(isset($_POST['action'])&&($_POST['action']=="do_chp")){
	$log=new ActionLog;
	$au=new AuthUser();
	$result=$au->Auth();
	if($result===NULL){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();		
	}
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',882)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}

	
	$id=abs((int)$_POST['id']);
	
	$setted_password=iconv('utf-8', 'windows-1251', $_POST['setted_password']);
	
	$_ui=new UserItem;
		
	$_ui->Edit($id, array('setted_password'=>SecStr($setted_password), 'password'=>md5($setted_password), 'password_expired'=>1));
	
	$log->PutEntry($result['id'], 'сбросил пароль сотруднику', $id, 882, NULL, '', $id);
	
	
	$user=$_ui->GetItemById($id);
	$text=GetMessageText($user);
	
	$ret=$text;
	
	if($user['is_active']==1) $ret=@mail($user['email_s'],$topic,$text,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
	
	
}else{
	//периодическая отправка уведомлений
	$_usg=new UsersGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
	$dec->AddEntry(new SqlEntry('setted_password','', SqlEntry::NE));
	$dec->AddEntry(new SqlEntry('password_expired',1, SqlEntry::E));
	
	
	$users=$_usg->GetItemsByDecArr($dec);
	
	//print_r($users);
	
	foreach($users as $k=>$user){
		$text=GetMessageText($user);
		echo $user['email_s']; //echo $topic; echo $text;
		//echo $text;	
		 @mail($user['email_s'],$topic,$text,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
		usleep(200);
	}
	
	
}


 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>