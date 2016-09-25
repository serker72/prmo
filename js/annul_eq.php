<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');


require_once('../classes/billgroup.php');
/*require_once('../classes/sh_i_group.php');*/
require_once('../classes/acc_group.php');
require_once('../classes/trust_group.php');
require_once('../classes/paygroup.php');

require_once('../classes/wfgroup.php');

require_once('../classes/invgroup.php');
require_once('../classes/invcalcgroup.php');

require_once('../classes/bill_in_group.php');
 


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

/*if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
*/

$_timeout=36000; //36000; //12*60*60

$ret='';
if(isset($_GET['action'])&&($_GET['action']=="try_annul")){
	//аннулирование
	
	$kind=abs((int)$_GET['kind']);
	
	$s1=new mysqlSet('select * from eq_time_markers where kind="'.$kind.'" order by ptime desc limit 1');
	//echo mysql_error();
	$rc1=$s1->getResultNumRows();
	$rs1=$s1->getResult();
	
	$do_sync=true;
	
	//проверяем временные маркеры
	 if($rc1>0){
		$f1=mysqli_fetch_array($rs1);
		if(((int)$f1['ptime']+(int)$f1['expiration'])<=time()){
			$s2=new NonSet('delete from eq_time_markers where id="'.$f1['id'].'"');
			$s3=new NonSet('insert into eq_time_markers (ptime, expiration, kind) values('.time().', '.$_timeout.', '.$kind.')');
			
		}else $do_sync=false;
	
	}else{
		$s3=new NonSet('insert into eq_time_markers (ptime, expiration, kind) values('.time().', '.$_timeout.', '.$kind.')');
	}
	 
	
	if($do_sync){
	//echo 'zzzzzzzzzzzz';
	$class=NULL;
	 
	switch($kind){
		case 1:
			$class=new BillGroup;
		break;
		case 2:
			$class=new ShIGroup;
		break;
		case 3:
			$class=new KomplGroup;
			
		break;
		case 4:
			$class=new BillInGroup;
		break;
	/*	case 5:
			$class=new ShIInGroup;
		break;*/
		
		default:
			$class=new BillGroup;
		break;
	};
	
	$class->AutoEq();
	}
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>