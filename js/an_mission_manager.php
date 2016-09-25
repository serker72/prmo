<?
session_start();
header('Content-type: text/html; charset=windows-1251');


require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}




$ret='';


if($_GET['term']) 	{
	
	
	$sql='select * from user where is_active=1 and name_s like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" or login like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" order by name_s asc ';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		$ret_arrs[]='{"id":"'.$v['name_s'].'","label":"'.$v['name_s'].' ('.$v['login'].'), '.$v['position_s'].'","value":"'.$v['name_s'].'"}';
	}
	
	
	$ret='['.implode(', ',$ret_arrs).']';
	
	
}
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	

?>