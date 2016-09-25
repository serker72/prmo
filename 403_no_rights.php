<?
header("HTTP/1.1 403 Forbidden", true, 403);
header("Status: 403 Forbidden");
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","” ¬ас нет прав дл€ просмотра этой страницы");

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
	<h1>” ¬ас нет прав дл€ просмотра этой страницы</h1>

<p><strong>” ¬ас недостаточно прав дл€ просмотра этой страницы.</strong></p>
<p>ѕопробуйте <a href="#" onclick="location.reload(); return false;">обновить страницу</a>, или <a href="#" onclick="history.back(); return false;">вернитесь назад</a> и запросите какую-либо другую страницу.</p>
	
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