<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
 

require_once('../classes/user_s_item.php');


require_once('../classes/posgroupgroup.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');

require_once('../classes/schednotesgroup.php');
require_once('../classes/schednotesitem.php');


require_once('../classes/user_s_group.php');

require_once('../classes/suppliersgroup.php');
require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');
require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');

require_once('../classes/supplier_district_group.php');
require_once('../classes/supplier_cities_group.php');
require_once('../classes/suppliercontactgroup.php');


require_once('../classes/supplier_to_user.php');



require_once('../classes/sched.class.php');
require_once('../classes/sched_history_fileitem.php');
require_once('../classes/sched_history_item.php');
require_once('../classes/sched_history_group.php');

require_once('../classes/filecontents.php');
 
require_once('../classes/quick_suppliers_group.php');
require_once('../classes/sched_fileitem.php');

require_once('../classes/sched_view1.class.php');
require_once('../classes/sched_view2.class.php');
require_once('../classes/sched_view3.class.php');
require_once('../classes/sched_view4.class.php');
require_once('../classes/sched_view5.class.php');
 

$au=new AuthUser();
$result=$au->Auth(false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 

$ret='';


//настройка реестра
if(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Sched_View3Group;
	$_view=new Sched_View3Item;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new Sched_View3Group;
	 
	 
	
	$_views->Clear($result['id']);
	 
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>