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

require_once('classes/useritem.php');
require_once('classes/user_s_item.php');
//require_once('classes/user_d_item.php');

require_once('classes/user_card.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Карта пользователя');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$ui=new UserItem;
//по наличию или отсутствию логина определяем, мы это или не мы
$work_mode=0; //0 - покажем нас;  1 - чужая карта
if(isset($_GET['name'])){
	$name=SecStr($_GET['name']);
	$user=$ui->GetItemByFields(array('login'=>$name));
	if($user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
	}
	
	
	if($user['id']==$result['id']) $work_mode=0;
	else $work_mode=1;
		
}else{
	$work_mode=0;
}



if(($work_mode==0)&&isset($_GET['doChBd'])){
	$_temp_us=new UserSItem;
	
	
	if(($_GET['pasp_bithday']!="-")&&($_GET['pasp_bithday']!="")&&($_GET['pasp_bithday']!="0")) $_temp_us->Edit($result['id'], array('pasp_bithday'=>Datefromdmy($_GET['pasp_bithday'])));	
	header("Location: info.html?name=".$result['login']);
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
	
	//die();
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	
	if($work_mode==0) $user=$result;
	
	$uc=new UserCard();
	
	
		//echo $work_mode;
		$content=$uc->Deploy($user['id'],new UserSItem,$work_mode,'users/s_show.html');
	
	
	
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