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
require_once('classes/actionlog.php');

//очистим старые сессии
$us=new UserSession; $us->ClearOldSessions();

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'ѕодтверждение смены парол€');

$au=new AuthUser();
$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

?>

<?
//var_dump($au->GetRightsTable());
$content='';
if(!isset($_GET['confirm'])||(strlen($_GET['confirm'])==0)){
	$content='
	<h1>ќшибка смены парол€</h1>
	Ќе задан код подтверждени€ дл€ смены парол€.
	
	';
}else{
	//проверим код
	$_ui=new UserItem;
	$user=$_ui->GetItemByFields(array('change_wait'=>1, 'change_password_confirm'=>SecStr($_GET['confirm'])));
	
	if($user===false){
		$content='
		<h1>ќшибка смены парол€</h1>
		Ќеверный код подтверждени€ дл€ смены парол€.
		';	
	}else{
		$_ui->Edit($user['id'], array('change_wait'=>0, 'password'=>$user['new_password']));
		
		$log=new ActionLog;
		$log->PutEntry($user['id'], 'смена парол€',NULL,NULL,NULL,'сменил пароль с помощью формы смены/восстановлени€ парол€',NULL);
		$content='
		<h1>Cмена парол€</h1>
		ѕароль успешно изменен.<br />
		ƒл€ продолжени€ работы, пожалуйста, авторизуйтесь на сайте заново.
		';	
	}
	
	
	
	
}

if($result===NULL){
	
	
	echo $content;
	
}else{
	
	
	
	include('inc/menu.php');
	
	
	//демонстраци€ стартовой страницы
	$smarty = new SmartyAdm;
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);
}
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