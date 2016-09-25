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


require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');


require_once('classes/posdimitem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

require_once('classes/payitem.php');

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/paynotesgroup.php');
require_once('classes/paynotesitem.php');

require_once('classes/payforbillgroup.php');
require_once('classes/payforbillitem.php');
require_once('classes/invcalcitem.php');


require_once('classes/paycreator.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/paycodeitem.php');
require_once('classes/paycodegroup.php');



require_once('classes/demandnotesgroup.php');
require_once('classes/demanditem.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование требования');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new DemandItem;
$_pay=new PayItem;

$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PosItem;
$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;
$_posgroupgroup=new PosGroupGroup;

$_supgroup=new SuppliersGroup;

$lc=new PayCreator;

$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

$object_id=array();
switch($action){
	case 0:
	$object_id[]=855;
	break;
	case 1:
	$object_id[]=855;
	$object_id[]=855;
	break;
	case 2:
	$object_id[]=855;
	break;
	default:
	$object_id[]=855;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=14;


//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',281)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

	


if($action==0){
	 header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
	
	
	
}elseif(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
 
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту требования',NULL,855, NULL, 'требование № '.$editing_user['id'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту требования: версия для печати',NULL,855, NULL, 'требование № '.$editing_user['id'],$id);
				
}



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=34;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	 if($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		
		
		
		$sm1=new SmartyAdm;
		
		//организация
		
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($orgitem['opf_id']); 
		$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		//реквизиты по организации
		$_bi=new BDetailsItem; $selected_bd_id=$editing_user['org_bdetails_id'];
		$bi=$_bi->GetItemById($editing_user['org_bdetails_id']);
		if($bi!==false){
				$editing_user['org_bdetails_id_string']=' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'';
				
				
				
			}
		//все реквизиты организации - для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($orgitem['id'],$selected_bd_id);
		$sm1->assign('orgpos',$arr);
		
		
		
		//коды оплаты
		$_pcg=new PayCodeGroup;
		$arr=$_pcg->GetItemsArr(0, $editing_user['code_id']);
		$sm1->assign('codespos',$arr);
		$_pci=new PayCodeItem;
		$pci=$_pci->GetItemById($editing_user['code_id']);
		if($pci!==false){
				$editing_user['code_id_string']=''.stripslashes($pci['code']).' '.stripslashes($pci['name'].'. '.$pci['descr'].'').'';
		}
		
		
		
		
		$sm1->assign('bill_id',$bill_id);
		
		
		
		//договор оплаты
		$_supcontract=new SupContractItem;
		$supcontract=$_supcontract->GetItemById($editing_user['contract_id']);
		//$editing_user['org_bdetails_id_string']=$supcontract['id'];
		$editing_user['contract_no']=$supcontract['contract_no'];
		$editing_user['contract_pdate']=$supcontract['contract_pdate'];
		
		
		//все договора поставщика
		$_scg=new SupContractGroup;
		$scg=$_scg->GetItemsByIdArr($supplier['id'],$bill['contract_id'],1);
		//print_r($scg);
		$sm1->assign('pos2',$scg);
		
		
		if($editing_user['given_pdate']>0) $editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
		else $editing_user['given_pdate']='-';
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		
		
	
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$sopfitem=$_opfitem->getItemById($si['opf_id']); 
		
		
		$editing_user['supplier_id_string']= $sopfitem['name'].' '.$si['full_name'];
		
		
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$_supgroup->GetItemsForPay('pay/suppliers_list.html', new DBDecorator, false, $supgroup,$result);
		$sm1->assign('suppliers',$supgroup);
		
		
		//банк. реквизиты
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetItemById($editing_user['supplier_bdetails_id']);
		$editing_user['bdetails_id_string']='р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city'];
		
		//все реквизиты п-ка для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($editing_user['supplier_id'],$editing_user['supplier_bdetails_id']);
		//print_r($arr);
		$sm1->assign('pos',$arr);
		
		
	 
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		
	 
		
		//Примечания
		$rg=new DemandNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,false,false,false,$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',false);
		
		
		
		$sm1->assign('org_id', $result['org_id']);
		
		
		$sm1->assign('ship',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', false);  
		
		
		
		$sm1->assign('current_user_id', $result['id']);
		
		 //сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}
		$sm1->assign('inner_user_id_ids',$_ids);
		$sm1->assign('inner_user_id_vals',$_vals);
		
		
		
		
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
	 
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',281)); 
		
		 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		$user_form=$sm1->fetch('demands/dem_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',855)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			
			
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(855)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('do_show_log',1));
			if(!isset($_GET['do_show_log'])){
				$do_show_log=false;
			}else{
				$do_show_log=true;
			}
			$sm->assign('do_show_log',$do_show_log);
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_pay.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('demands/ed_dem_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>