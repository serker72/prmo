<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');


/*require_once('../classes/billgroup.php');
require_once('../classes/sh_i_group.php');
require_once('../classes/acc_group.php');
require_once('../classes/trust_group.php');
require_once('../classes/paygroup.php');
require_once('../classes/isgroup.php');
require_once('../classes/wfgroup.php');
require_once('../classes/komplgroup.php');
require_once('../classes/invgroup.php');
require_once('../classes/invcalcgroup.php');
*/
//require_once('../classes/mes.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}


$_timeout=86400;

$ret='';
if(isset($_GET['action'])&&($_GET['action']=="try_del")){
	//аннулирование
	
	$kind=abs((int)$_GET['kind']);
	
	$s1=new mysqlSet('select * from  message_del_markers where kind="'.$kind.'" order by ptime desc limit 1');
	//echo mysql_error();
	$rc1=$s1->getResultNumRows();
	$rs1=$s1->getResult();
	
	$do_sync=true;
	
	//проверяем временные маркеры
	if($rc1>0){
		$f1=mysqli_fetch_array($rs1);
		if(((int)$f1['ptime']+(int)$f1['expiration'])<=time()){
			$s2=new NonSet('delete from message_del_markers where id="'.$f1['id'].'"');
			$s3=new NonSet('insert into message_del_markers (ptime, expiration, kind) values('.time().', '.$_timeout.', '.$kind.')');
			
		}else $do_sync=false;
	
	}else{
		$s3=new NonSet('insert into message_del_markers (ptime, expiration, kind) values('.time().', '.$_timeout.', '.$kind.')');
	}
	
	
	if($do_sync){
	//echo 'zzzzzzzzzzzz';
	//$class=NULL;
	/*
	
	require_once('../classes/billgroup.php');
require_once('../classes/sh_i_group.php');
require_once('../classes/acc_group.php');
require_once('../classes/trust_group.php');
require_once('../classes/paygroup.php');
require_once('../classes/isgroup.php');
require_once('../classes/wfgroup.php');
require_once('../classes/komplgroup.php');
require_once('../classes/invgroup.php');
	*/
			$sql='delete from message_in_folder where folder_id=1 /*and unread=0*/ and pdate>0 and pdate<'.(time()-7*24*60*60).' and message_id in(select id from message where from_id=-1)';
			$ns=new NonSet($sql);
			
			//$ret=$sql;
	}
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>