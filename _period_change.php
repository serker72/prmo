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
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');


require_once('classes/user_s_item.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'����� ������');

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
		
		$log->PutEntry($result['id'], '����� ������',NULL,NULL,NULL,'������ ������ ��� ������������ ����� ������',NULL);
		 
		 $au->Authorize($result['login'],$params['password']);
	
	}else{
		header('Location: index.php');
		die();	
	}
}else{
	header('Location: index.php');
	die();	
	
}




//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	//include('inc/menu.php');
	
	
	
	//������������ ��������
	?>
    <script type="text/javascript">
$(function(){
  $("#tabs").tabs();
});
</script>

  <div class="content">

<div id="tabs">
  <ul>
    <li><a href="#tabs-1">����� ������</a>
    </li>
  </ul>
	 <div id="tabs-1">

   
    <h1>������ ������� �������!</h1>
    <br />
<br />
<br />
	<strong>�� ������� ������� ��� ������ � ��������� "<?=SITETITLE?>".<br />
	<br />
	
	��� ����������� ������ <a href="/">��������� �� ��������� �������� ���������</a>.<br />
	<br />
<input type="button" value="������� �� ��������� ��������" onclick="location.href='/';" />
    </strong>
 </div>
 </div>
 </div>   
    
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