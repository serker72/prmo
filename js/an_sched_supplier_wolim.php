<?
session_start();
//header('Content-type: text/html; charset=windows-1251');



require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

 
require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');

require_once('../classes/billitem.php');
require_once('../classes/billgroup.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/billnotesgroup.php');
require_once('../classes/billnotesitem.php');
require_once('../classes/billpositem.php');
require_once('../classes/billpospmitem.php');
require_once('../classes/posdimitem.php');
require_once('../classes/suppliersgroup.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');

require_once('../classes/user_s_item.php');


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

//выборка к-тов 
if($_GET['term']) 	{
 
	
	//ограничения по к-ту
	$limited_supplier=NULL;
 
	
	
	
	
	$sql='(select p.id, p.full_name as full_name,
	
	opf.name as opf_name,
	p.code,
	"" as login
	
	 from supplier as p 
	 left join opf on opf.id=p.opf_id
	 where ((p.is_org=0 and p.org_id="'.$result['org_id'].'") or (p.is_org=1 and p.id<>"'.$result['org_id'].'")) and p.is_active=1 and p.full_name like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" '.$supplier_flt.')
	 
	 order by 1
	';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		//$ret_arrs[]='{"id":"'.$v['full_name'].'","label":"'.$v['full_name'].'","value":"'.$v['full_name'].'"}';
		$vv=array();
		$vv['id']=$v['id'];
		
		$vv['text']=iconv('windows-1251','utf-8',$v['code'].' '.$v['full_name'].', '.$v['opf_name']);
		
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