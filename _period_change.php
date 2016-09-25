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


require_once('classes/user_s_item.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Смена пароля');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;

if(isset($_POST['new_password'])){
	
	
	$can=true;
	if(strlen(SecStr($_POST['new_password']))<6) $can=$can&&false;
	/*if(!eregi("^[A-Za-z0-9]+$",SecStr($_POST['new_password']))){
		$can=$can&&false;	
	}*/
	
	if(md5(SecStr($_POST['new_password']))==$result['password']) $can=$can&&false;
	
	if($can){
		$params=array();
		$params['password']=md5(SecStr($_POST['new_password']));
		$params['password_expired']=0;
		
		$ui=new UserSItem;
		$ui->Edit($result['id'], $params);
		
		$log->PutEntry($result['id'], 'смена пароля',NULL,NULL,NULL,'сменил пароль при обязательной смене пароля',NULL);
		 
		 $au->Authorize($result['login'],$params['password']);
	
	}else{
		header('Location: index.php');
		die();	
	}
}else{
	header('Location: index.php');
	die();	
	
}




//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	//include('inc/menu.php');
	
	
	
	//демонстрация страницы
	?>
    <script type="text/javascript">
$(function(){
  $("#tabs").tabs();
});
</script>

  <div class="content">

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">Смена пароля</a>
    </li>
  </ul>
	 <div id="tabs-1">

   
    <h1>Пароль успешно изменен!</h1>
    <br />
<br />
<br />
	<strong>Вы успешно сменили Ваш пароль в программе "<?=SITETITLE?>".<br />
	<br />
	
	Для продолжения работы <a href="/">перейдите на стартовую страницу программы</a>.<br />
	<br />
<input type="button" value="Перейти на стартовую страницу" onclick="location.href='/';" />
    </strong>
 </div>
 </div>
 </div>   
    
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