<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

//������� ������ ������
$us=new UserSession; $us->ClearOldSessions();

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� ������');

$au=new AuthUser();
$result=$au->Auth();

if(($result===NULL)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	
	include('inc/menu.php');
	
	
	//������������ ��������� ��������
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
			<p>�� �������� ����� ������ ������ � ������� ".SITETITLE."</p>
			
			<p>��� ����� ������: ".$new_password."</p>
			
			<p>��� ������������ ����� ������ �������� �� ������: <a href=\"".SITEURL.'/confirm.html?confirm='.$change_password_confirm."\">".SITEURL.'/confirm.html?confirm='.$change_password_confirm."</a></p>
			
			<p>���� �� �� ������ ������ ������, �� �������������� ������ ���������.</p>
			<p>�������.</p>
			</body></html>";
			
			//print_r($mail_txt);
			@mail($email,'������������� ����� ������',$mail_txt,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
			$sm=new SmartyAdm;
		
			$content=$sm->fetch('chp/change_p_sent.html');	
			
		}else{
					//��������� ,��� ����� �� ������
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

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>