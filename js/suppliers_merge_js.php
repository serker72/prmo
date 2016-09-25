<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/supplieritem.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');

require_once('../classes/fagroup.php');
require_once('../classes/faitem.php');

require_once('../classes/opfgroup.php');
require_once('../classes/opfitem.php');

require_once('../classes/suppliercontactgroup.php');
require_once('../classes/suppliercontactitem.php');
require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/suppliercontactdataitem.php');


require_once('../classes/supplier_district_group.php');
require_once('../classes/supplier_district_item.php');

require_once('../classes/supplier_region_group.php');
require_once('../classes/supplier_region_item.php');

require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');

require_once('../classes/supplier_cities_group.php');
require_once('../classes/supplier_cities_item.php');

require_once('../classes/supcontract_group.php');
require_once('../classes/supcontract_item.php');

require_once('../classes/supplier_notesgroup.php');
require_once('../classes/supplier_notesitem.php');

require_once('../classes/sched.class.php');
require_once('../classes/supplier_branches_group.php');
require_once('../classes/supplier_branches_item.php');


require_once('../classes/supplier_responsible_user_item.php');

require_once('../classes/supplier_merge.class.php'); 

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
if(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$_bi=new BDetailsItem;
	$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>0));
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			if(
			($k=='contract_no')||
			($k=='contract_pdate')||
			($k=='contract_pdate')) continue;
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		$rret[]='"opf_name":"'.htmlspecialchars($opf['name']).'"';
		
		if($bi!==false){
			$rret[]='"bdetails_id_string":" р/с '.addslashes($bi['rs'].', '.$bi['bank']).', '.$bi['city'].'"';
			$rret[]='"bdetails_id":"'.htmlspecialchars($bi['id']).'"';
		}
		
		if($sci!==false){
			$rret[]='"contract_no_string":"'.addslashes($sci['contract_no']).'"';
			$rret[]='"contract_no":"'.addslashes($sci['contract_no']).'"';
			$rret[]='"contract_id":"'.addslashes($sci['id']).'"';
		
			$rret[]='"contract_pdate_string":"'.addslashes($sci['contract_pdate']).'"';
			$rret[]='"contract_pdate":"'.addslashes($sci['contract_pdate']).'"';
			
			
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}elseif(isset($_POST['action'])&&(($_POST['action']=="find_suppliers")||($_POST['action']=="find_suppliers_ship")||($_POST['action']=="find_many_suppliers"))){
	
	
	//получим список позиций по фильтру
	$_pg=new Merge_SupplierGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0){
	 
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['code'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0){
		// $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['full_name'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0){
		// $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
		
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['inn'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.inn', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) {
	//	$dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['kpp'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.kpp', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) {
		//$dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
		
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['legal_address'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.legal_address', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
 
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
	}
	
	
	if(!$au->user_rights->CheckAccess('w',909)){
	 
		
		$decorator->AddEntry(new SqlEntry('p.id','select supplier_id from supplier_responsible_user where user_id="'.$result['id'].'"', SqlEntry::IN_SQL));
	}
	
	
	
	 $ret=$_pg->GetItemsForBill('suppliers/merge_suppliers_list.html',  $dec,true,$all7,$result);
	


}elseif(isset($_POST['action'])&&($_POST['action']=="compare_suppliers")){
	$_merge=new SuppliersMerge;
	
	$ids=$_POST['ids'];
	
	
	$ret=$_merge->ShowCompareForm($ids, 'suppliers/merge_2.html');
	
	
} 
 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>