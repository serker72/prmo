<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/storageitem.php');
require_once('classes/storagegroup.php');
require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');

require_once('classes/storagesector.php');
require_once('classes/komplitem.php');
require_once('classes/komplpositem.php');
require_once('classes/posdimitem.php');
require_once('classes/komplgroup.php');
require_once('classes/komplscanconfgroup.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');
require_once('classes/storageitem.php');
require_once('classes/sectoritem.php');
require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

require_once('classes/sh_i_group.php');

require_once('classes/paygroup.php');

require_once('classes/propisun.php');

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/bdetailsitem.php');



/*$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование входящего счета');*/

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}



$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PosItem;
$ui=new KomplItem;
$_storage=new StorageItem;
$_storages=new StorageGroup;
//$lc=new LoginCreator;
$log=new ActionLog;
$ssgr=new StorageSector;
$_posgroupgroup=new PosGroupGroup;
$_scanconf=new KomplScanConf;

$_supgroup=new SuppliersGroup;

$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);

$_opfitem=new OpfItem;
$opfitem=$_opfitem->GetItemById($orgitem['opf_id']);

/*switch($action){
	case 0:*/
	$object_id=92;
	/*break;
	case 1:
	$object_id=93;
	break;
	case 2:
	$object_id=94;
	break;
	default:
	$object_id=92;
	break;
}*/
//echo $object_id;
//die();

	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_bill->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	$sm=new SmartyAdm;
	
	
	$_bpg=new BillPosGroup;
	$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
	//print_r($bpg);
	$_pn=new PropisUn;
	foreach($bpg as $k=>$v){
		$bpg[$k]['quantity_p']=trim($_pn->propis($v['quantity']));
	}
	$sm->assign('positions',$bpg);
	
	$sm->assign('org',$orgitem['name']);
	$sm->assign('order',$editing_user);
	
	$_si=new SupplierItem;
	$si=$_si->GetItemById($editing_user['supplier_id']);
	
	
	$_opf_sup=new OpfItem;
	$opfsup=$_opf_sup->getItemById($si['opf_id']);
	
	$letter=array();
	$letter['id']=$editing_user['id'];
	$letter['pdate']=date("d.m.Y");
	$letter['fio']=$result['name_s'];
	$letter['supplier_name']=$opfsup['name'].' '.$si['full_name'];
	$letter['organization']=$opfitem['name'].' '.$orgitem['full_name'].', ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
	
	$_bd=new BDetailsItem;
	$bank=$_bd->getitembyfields(array('user_id'=>$orgitem['id'],'is_basic'=>1));
	
	if($bank===false){
		$bank=$_bd->getitembyfields(array('user_id'=>$orgitem['id']));
	}
	
	$letter['rs']=$bank['rs'];
	$letter['bank']=$bank['bank'];
	
	$letter['city']=$bank['city'];
	$letter['bik']=$bank['bik'];
	
	$letter['ks']=$bank['ks'];
	
	/*$letter['rs']=$orgitem['rs'];
	$letter['bank']=$orgitem['bank'];
	
	$letter['city']=$orgitem['city'];
	$letter['bik']=$orgitem['bik'];
	
	$letter['ks']=$orgitem['ks'];*/
	
	$letter['pasp_ser']=$result['pasp_ser'];
	$letter['pasp_no']=$result['pasp_no'];
	$letter['pasp_kem']=$result['pasp_kem'];
	$letter['pasp_kogda']=$result['pasp_kogda'];
	
	
	$letter['chief']=$orgitem['chief'];
	$letter['main_accountant']=$orgitem['main_accountant'];
	
	
	$sm->assign('letter',$letter);
	
	$sm->display('letter.html');



	
	

?>