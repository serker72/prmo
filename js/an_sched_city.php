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
	
	
	$_sc=new SupplierCityGroup;
	
	
	$sc=$_sc->GetItemsByIdArr(iconv("utf-8","windows-1251",SecStr($_GET['term'])));
	
	$ret_arrs=array();
	
	foreach($sc as $k=>$v){
		//$ret_arrs[]='{"id":"'.$v['name'].'","label":"'.$v['fullname'].'","value":"'.$v['name'].'"}';
		$vv=array();
		 $vv['id']=$v['id'];
		
		 $vv['text']=iconv('windows-1251','utf-8',$v['fullname']);
		
		array_push($ret_arrs, $vv);
		 
	}
	
	//$ret="Choice1|Choice1\n";
	
	//$ret='['.implode(', ',$ret_arrs).']';
	 
	$ret = array();
	 
	 
	$ret['results'] = $ret_arrs;
	 
	echo json_encode($ret);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
//echo $ret;	

?>