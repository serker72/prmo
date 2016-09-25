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
require_once('classes/actionlog.php');

//очистим старые сессии
$us=new UserSession; $us->ClearOldSessions();

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Востановление пароля');

//$au=new AuthUser();
//$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

?>

<?
function GenSql($params,$email=NULL){
	$_sql=array();
		foreach($params as $k=>$v){
			if(!is_array($v)) $_sql[]='('.$k.'="'.$v.'")';
			else{
				 $_tsql=array();
				 foreach($v as $kk=>$vv){
					 $_tsql[]=''.$kk.'="'.$vv.'"'; 
				 }
				  $_sql[]='('.implode(' or ',$_tsql).')';
			}
		}
		$sql1=implode(' AND ',$_sql);
		$sql=' select * from user where '.$sql1;
		if($email!==NULL){
			
			$sql.=' and( id in (select user_id from user_contact_data where kind_id=5 and value="'.$email.'" ))'; 
		}
		
		//echo $sql;
		return $sql;	
}


$content='';
if(!isset($_POST['doFind'])&&!isset($_POST['doRestore'])){
	//форма восстановления пароля
	//ввод логина или имейла	
	
	
	$sm1=new SmartyAdm;
	
	
	$content=$sm1->fetch('chp/restore1.html');
}elseif(isset($_POST['doFind'])&&isset($_POST['login'])&&isset($_POST['email'])){
	
	if((strlen($_POST['login'])==0)&&(strlen($_POST['email'])==0)){
		$content='<h1>Для восстановления пароля укажите логин или email!</h1>';	
	}else{
	
		$params=array();
		if(strlen($_POST['login'])>0){
			$params['login']=SecStr($_POST['login']);	
		}
		$email=NULL;
		if(strlen($_POST['email'])>0){
			/*$params['email']=array('email_s'=>SecStr($_POST['email']),
								   'email_d'=>	SecStr($_POST['email']));*/
								   $email=SecStr($_POST['email']);	
		}
		$params['is_active']=1;
		
		//echo  GenSql($params);
		$ts=new mysqlSet( GenSql($params,$email));
		$tc=$ts->GetResultNumRows();
		if($tc==0){
			$sm1=new SmartyAdm;
			$content=$sm1->fetch('chp/restore1_error.html');	
		}else{
			
			$sm1=new SmartyAdm;
			$sm1->assign('login',$_POST['login']);
			$sm1->assign('email',$_POST['email']);
			$content=$sm1->fetch('chp/restore2.html');
		}
	}
}elseif(isset($_POST['doRestore'])){
	
	if((strlen($_POST['login'])==0)&&(strlen($_POST['email'])==0)){
		$content='<h1>Для восстановления пароля укажите логин или email!</h1>';	
	}elseif(strlen($_POST['password'])<4){
		$content='<h1>Заполните поле Пароль!</h1>';	

	}else{
		$params=array();
		if(strlen($_POST['login'])>0){
			$params['login']=SecStr($_POST['login']);	
		}
		$email=NULL;
		if(strlen($_POST['email'])>0){
			/*$params['email']=array('email_s'=>SecStr($_POST['email']),
								   'email_d'=>	SecStr($_POST['email']));*/
								   $email=SecStr($_POST['email']);
		}
		$params['is_active']=1;
		
		//echo  GenSql($params);
		$ts=new mysqlSet( GenSql($params,$email));
		$tc=$ts->GetResultNumRows();
		if($tc==0){
			$sm1=new SmartyAdm;
			$content=$sm1->fetch('chp/restore1_error.html');	
		}else{
			$rts=$ts->GetResult();
			
			for($i=0; $i<$tc; $i++){
				$f=mysqli_fetch_array($rts);
				
				
				
				$new_password=$_POST['password'];
				$new_password_md5=md5($new_password);
				$change_password_confirm=md5( time().$result['login'].$new_password.time() );
				
				
				$params=array();
				$params['new_password']=$new_password_md5;
				$params['change_password_confirm']=$change_password_confirm;
				$params['change_wait']=1;
				
				
				$_ui=new UserItem;
				$_ui->Edit($f['id'], $params);
				
				
				/*if($f['group_id']==3) $email=$result['email_d'];
				else $email=$f['email_s'];*/
			$tsq='select value from user_contact_data where kind_id=5 and user_id="'.$f['id'].'"';
				
				
				$ts1=new mysqlset($tsq);
				$rts=$ts1->GetResult();
				$rtsc=$ts1->GetResultNumRows();
				
				if($rtsc>0){
					$rf=mysqli_fetch_array($rts);
					$email=$rf[0];
				
					$mail_txt="<html><body>
					<p>Вы заказали смену Вашего пароля в системе ".SITETITLE."</p>
					
					<p>Ваш новый пароль: ".$new_password."</p>
					
					<p>Для подтвеждения смены пароля пройдите по ссылке: <a href=\"".SITEURL.'/confirm.html?confirm='.$change_password_confirm."\">".SITEURL.'/confirm.html?confirm='.$change_password_confirm."</a></p>
					
					<p>Если Вы не хотите менять пароль, то проигнорируйте данное сообщение.</p>
					<p>Спасибо.</p>
					</body></html>";
					
					//var_dump( $rf);
					//print_r($mail_txt);
					
					
					
					
					@mail($email,'подтверждение смены пароля',$mail_txt,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
					
					@usleep(50);
					
				  $sm1=new SmartyAdm;
				  
				  $content=$sm1->fetch('chp/restore3.html');
				}else{
					//сообщения ,что емайл не найден
					$sm1=new SmartyAdm;
			
					$content=$sm1->fetch('chp/restore2_noemail.html');	
					
				}
				
			}
			
			
		}
	}
	
}

echo $content;


?>

<?
$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>