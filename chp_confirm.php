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
require_once('classes/actionlog.php');

//������� ������ ������
$us=new UserSession; $us->ClearOldSessions();

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'������������� ����� ������');

$au=new AuthUser();
$result=$au->Auth();

//������ � �������
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
	<h1>������ ����� ������</h1>
	�� ����� ��� ������������� ��� ����� ������.
	
	';
}else{
	//�������� ���
	$_ui=new UserItem;
	$user=$_ui->GetItemByFields(array('change_wait'=>1, 'change_password_confirm'=>SecStr($_GET['confirm'])));
	
	if($user===false){
		$content='
		<h1>������ ����� ������</h1>
		�������� ��� ������������� ��� ����� ������.
		';	
	}else{
		$_ui->Edit($user['id'], array('change_wait'=>0, 'password'=>$user['new_password']));
		
		$log=new ActionLog;
		$log->PutEntry($user['id'], '����� ������',NULL,NULL,NULL,'������ ������ � ������� ����� �����/�������������� ������',NULL);
		$content='
		<h1>C���� ������</h1>
		������ ������� �������.<br />
		��� ����������� ������, ����������, ������������� �� ����� ������.
		';	
	}
	
	
	
	
}

if($result===NULL){
	
	
	echo $content;
	
}else{
	
	
	
	include('inc/menu.php');
	
	
	//������������ ��������� ��������
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

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>