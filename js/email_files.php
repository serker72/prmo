<?
session_start();
header('Content-type: text/html; charset=windows-1251');

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
/*
require_once('../classes/kpitem.php');
require_once('../classes/kpgroup.php');



require_once('../classes/billpospmformer.php');
require_once('../classes/kp_pospmformer.php');

require_once('../classes/maxformer.php');*/
require_once('../classes/opfitem.php');

/*
require_once('../classes/kpnotesgroup.php');
require_once('../classes/kpnotesitem.php');
require_once('../classes/kppositem.php');
require_once('../classes/kppospmitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/kpprepare.php');
*/
require_once('../classes/user_s_item.php');
require_once('../classes/user_s_group.php');
/*
require_once('../classes/pl_disgroup.php');
require_once('../classes/pl_disitem.php');
require_once('../classes/pl_dismaxvalgroup.php');
require_once('../classes/pl_dismaxvalitem.php');

require_once('../classes/pl_posgroup.php');
require_once('../classes/pl_positem.php');*/

require_once('../classes/posgroupgroup.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');

/*
require_once('../classes/pl_posgroup_forkp.php');

require_once('../classes/pl_currgroup.php');
require_once('../classes/kp_supply_group.php');
require_once('../classes/kp_paymode_group.php');
require_once('../classes/kp_paymode_item.php');*/
require_once('../classes/supplier_cities_group.php');
require_once('../classes/supplier_city_group.php');
/*require_once('../classes/pl_posgroup.php');*/

require_once('../classes/user_s_group.php');
require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');



require_once('../classes/sched.class.php');

//require_once('../classes/kp_view.class.php');

require_once('../classes/email_files.class.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 

if(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	//получить список контактов к-та с эл. почтой (ее айди=5)
 
	//$ret=$sm->fetch('kp/pdf_addresses.html');
	
	$file_id=abs((int)$_POST['file_id']);
	$load_name=SecStr($_POST['load_name']);
	
	
	 
	
	$_former=new EmailFiles_Former;
	
	$ret=$_former->GetAbonents($file_id, $load_name, 'email_files/addresses.html', $result);

}
elseif(isset($_POST['action'])&&($_POST['action']=="email_document")){
	$file_id=abs((int)$_POST['file_id']);
	$load_name=SecStr($_POST['load_name']);
	$addresses=($_POST['addresses']);
	
	
	$_former=new EmailFiles_Former;
	
	$_former->SendFile($file_id, $load_name, $addresses, $result);
		
	
}
 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>