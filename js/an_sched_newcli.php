<?
session_start();
header('Content-type: text/html; charset=windows-1251');


require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/sched.class.php');


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
		$limited_user=NULL; $flt=''; $ret_arrs=array();
	 
	 
	
	$sql='select p.*/*, up.name as position_s*/ from 
	user as p
	/*left join user_position as up on up.id=p.position_id*/
	where p.is_active=1 and (p.name_s like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" or p.login like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%") '.$flt.' order by p.name_s asc ';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		$vv=array();
		$vv['id']=$v['id'];
		$vv['text']=iconv('windows-1251','utf-8',$v['name_s'].', '.$v['position_s']);
		
		 array_push($ret_arrs, $vv);
		 
	}
	
	 
	
//	$ret='['.implode(', ',$ret_arrs).']';
 
	$ret = array();
	 
	 
	$ret['results'] = $ret_arrs;
	 
	echo json_encode($ret);
	
	
	
}
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
//echo $ret;	

?>