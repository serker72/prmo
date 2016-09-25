<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

//очистим старые сессии
$us=new UserSession; $us->ClearOldSessions();

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'—мена парол€');

$au=new AuthUser();
$result=$au->Auth();

if(($result===NULL)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	
	include('inc/menu.php');
	
	
	//демонстраци€ стартовой страницы
	$smarty = new SmartyAdm;
	
	if(isset($_POST['new_password'])){
		$new_password=$_POST['new_password'];
		$new_password_md5=md5($new_password);
		
		$change_password_confirm=md5( time().$result['login'].$new_password.time() );
		
		$_ui=new UserItem;
		$_ui->Edit($result['id'], array('new_password'=>$new_password_md5, 'change_wait'=>1, 'change_password_confirm'=>$change_password_confirm));
		
		/*if($result['group_id']==3) $email=$result['email_d'];
		else $email=$result['email_s'];*/
		
		
		$tsq='select value from user_contact_data where kind_id=5 and user_id="'.$result['id'].'"';
					 
				
		$ts1=new mysqlset($tsq);
		$rts=$ts1->GetResult();
		$rtsc=$ts1->GetResultNumRows();
		
		if($rtsc>0){
			$rf=mysqli_fetch_array($rts);
			$email=$rf[0];
		
			$mail_txt="<html><body>
			<p>¬ы заказали смену ¬ашего парол€ в системе ".SITETITLE."</p>
			
			<p>¬аш новый пароль: ".$new_password."</p>
			
			<p>ƒл€ подтвеждени€ смены парол€ пройдите по ссылке: <a href=\"".SITEURL.'/confirm.html?confirm='.$change_password_confirm."\">".SITEURL.'/confirm.html?confirm='.$change_password_confirm."</a></p>
			
			<p>≈сли ¬ы не хотите мен€ть пароль, то проигнорируйте данное сообщение.</p>
			<p>—пасибо.</p>
			</body></html>";
			
			//print_r($mail_txt);
			@mail($email,'подтверждение смены парол€',$mail_txt,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
			$sm=new SmartyAdm;
		
			$content=$sm->fetch('chp/change_p_sent.html');	
			
		}else{
					//сообщени€ ,что емайл не найден
					$sm1=new SmartyAdm;
			
					$content=$sm1->fetch('chp/restore2_noemail.html');	
					
				}
		
		
		
		
	}else{
		$sm=new SmartyAdm;
		
		$content=$sm->fetch('chp/change_p.html');	
	}
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