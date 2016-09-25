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

//if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
if($_GET['term']) 	{
	
	
	$flt='';
	if(strlen($_GET['branch_id'])>0){
		$flt=' and p.parent_id="'.(int)$_GET['branch_id'].'" ';	
	}
	
	$sql='select p.*, r.name as parent_name from supplier_branches as p left join supplier_branches as r on p.parent_id=r.id where p.name LIKE "%'.SecStr(iconv("utf-8","windows-1251",$_GET['term'])).'%" '.$flt.' order by r.name, p.name';
	$set=new MysqlSet($sql);
		
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();	
	
	$ret_arrs=array();
	
	for($i=0; $i<$rc; $i++){
			$v=mysqli_fetch_array($rs);
			foreach($v as $k=>$v1) $v[$k]=stripslashes($v1);
			
			
			$val='';
			if($v['parent_name']!="") $val.=$v['parent_name'].' - ';
			$val.=$v['name'];
			
			 
			$vv=array();
		$vv['id']=$v['id'];
		
		$vv['text']=iconv('windows-1251','utf-8',$val);
		
		array_push($ret_arrs, $vv);
	
	 
	
	}
		$ret = array();
	 
	 
	$ret['results'] = $ret_arrs;
	 
	echo json_encode($ret);
	
	
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
 

?>