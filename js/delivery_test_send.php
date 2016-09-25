<?
session_start();
require_once('../classes/global.php');
require_once('../classes/supplieritem.php');
require_once('../classes/v2/delivery.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/phpmailer/class.phpmailer.php');

$_di=new Delivery_Item;
$_ds=new Delivery_SubscriberItem;

//отправка рассылки на тестовый адрес
if(isset($_POST['action'])&&($_POST['action']=="delivery_send_test_email")){
	$id=abs((int)$_POST['id']);
	
	$_di=new Delivery_Item;
	$email=SecStr(iconv('utf-8', 'windows-1251', $_POST['email']));
	
	$params['html_content']=SecStr(iconv('utf-8', 'windows-1251', $_POST['html_content']));
		$params['plain_text_content']=SecStr(iconv('utf-8', 'windows-1251', $_POST['html_content']),10);
	$_di->Edit($id, $params);
		
	$di=$_di->GetItembyId($id);
	
 
	
	$_fl=new Delivery_Fields;
	$_fl->ProcessFields(NULL, $di);
	
	 
	
	//print_r($di);
	
	$sm=new SmartyAj;
	$sm->assign('SITEURL', SITEURL);
	$html=$sm->fetch('page_email_top.html');
	
	
	$html.=$di['html_content'];
	
	//
	//if($di['has_tracking']) $html.='<img src="'.SITEURL.'/img/campaign_'.$f['id'].'_'.$f['delivery_id'].'.png" width="1" height="1" >';
	
	
	$sm=new SmartyAj;
	$html.=$sm->fetch('page_email_bottom.html');
	
	//echo htmlspecialchars($html);
	
	$mail = new PHPMailer();
	
	$mail->AddAddress($email,  $email);
	
	$mail->SetFrom($di['from_email'], $di['from_name']);
	
	$mail->Subject = $di['topic']; 
	$mail->Body=$html;
	$mail->AltBody=$di['plain_text_content'];
	 
	  
	$mail->CharSet = "windows-1251";
	$mail->IsHTML(true);
	
	$mail->Send();
	
	
	// echo $html;
}

exit();

 
/*
$res='';
if(isset($_POST['action'])&&($_POST['action']=="send_message")){
	
	$txt=htmlspecialchars(iconv('utf-8', 'windows-1251', $_POST['message']));
	
	
	mail(FEEDBACK_EMAIL, 'запрос с сайта nt-holding.ru', $txt, "From: \"nt-holding.ru\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
	
}
echo $res;*/
?>