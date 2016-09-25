<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');


require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');


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
	
	
	$sql='select p.* from  sprav_country as p where p.name LIKE "%'.SecStr(iconv("utf-8","windows-1251",$_GET['term'])).'%" order by p.name';
	
	 
	
	$set=new MysqlSet($sql);
		
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();	
	
	$ret_arrs=array();
	
	for($i=0; $i<$rc; $i++){
			$v=mysqli_fetch_array($rs);
			foreach($v as $k=>$v1) $v[$k]=stripslashes($v1);
			
			 
		 
			$vv=array();
		$vv['id']=$v['id'];
		
		$vv['text']=iconv('windows-1251','utf-8',$v['name']);
		
		array_push($ret_arrs, $vv);
	
	 
	
	}
	
	
	$ret = array();
	 
	 
	$ret['results'] = $ret_arrs;
	 
	 
	 
	echo json_encode($ret);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
//echo $ret;	

?>