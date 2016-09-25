<?
header("HTTP/1.1 403 Forbidden", true, 403);
header("Status: 403 Forbidden");
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","Ошибка 403");

$au=new AuthUser();
$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$smarty = new SmartyAdm;
	
	$content='
	<br />
	<br />
	<h1>Ошибка 403</h1>

<p><strong>Доступ запрещен.</strong></p>
<p>Попробуйте <a href="#" onclick="location.reload(); return false;">обновить страницу</a>, или <a href="#" onclick="history.back(); return false;">вернитесь назад</a> и запросите какую-либо другую страницу.</p>
	
	';
	
	$smarty->assign('main_menu','');
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);

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