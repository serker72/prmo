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
require_once('classes/actionlog.php');
require_once('classes/suppliersgroup.php');

require_once('classes/supplier_to_user.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр контрагентов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['suppliers_from'])){
		$from=abs((int)$_SESSION['suppliers_from']);
	}else $from=0;
	$_SESSION['suppliers_from']=$from;




if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(isset($_POST['doInp'])){
	if(!$au->user_rights->CheckAccess('x',91)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - object_id
			//3rd figure - user_id
			eregi("^do_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			if(($regs!==NULL)&&isset($_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					//PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL){
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
			}
		}
	}
	
	header("Location: suppliers.php");	
	die();
}


if(!$au->user_rights->CheckAccess('w',91)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$log=new ActionLog;
if($print==0){
	$log->PutEntry($result['id'],'открыл раздел Контрагенты',NULL,91);
}else{
	$log->PutEntry($result['id'],'открыл раздел Контрагенты: версия для печати',NULL,91);
}



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=27;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',86)||
							$au->user_rights->CheckAccess('x',87)||
							$au->user_rights->CheckAccess('x',88)||
							$au->user_rights->CheckAccess('x',89)||
							$au->user_rights->CheckAccess('x',90)||
							$au->user_rights->CheckAccess('x',91)
							);
	$dto=new DiscrTableObjects($result['id'],array('86','87','88','89','90','91'));
	$admin=$dto->Draw('suppliers.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	//Разбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	
	//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	
	
	if(isset($_GET['is_active'])&&($_GET['is_active']==1)){
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}elseif(isset($_GET['is_active'])&&($_GET['is_active']==0)){
		 $decorator->AddEntry(new UriEntry('is_active',0));	
	}else{
		if(count($_GET)>1){
			 $decorator->AddEntry(new UriEntry('is_active',0));	
			 //echo 'ZZZZZZZZZZZZzz';
		}else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		}
	}
	
	
	
	
	if(isset($_GET['legal_address'])&&(strlen($_GET['legal_address'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['legal_address']));
		else $crit=SecStr(($_GET['legal_address']));
		
		$decorator->AddEntry(new SqlEntry('p.legal_address',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('legal_address',$crit));
	}
	
	if(isset($_GET['inn'])&&(strlen($_GET['inn'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['inn']));
		else $crit=SecStr(($_GET['inn']));
		
		$decorator->AddEntry(new SqlEntry('p.inn',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('inn',$crit));
	}
	
	if(isset($_GET['kpp'])&&(strlen($_GET['kpp'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['kpp']));
		else $crit=SecStr(($_GET['kpp']));
		
		$decorator->AddEntry(new SqlEntry('p.kpp',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('kpp',$crit));
	}
	
	if(isset($_GET['full_name'])&&(strlen($_GET['full_name'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['full_name']));
		else $crit=SecStr(($_GET['full_name']));
		
		$decorator->AddEntry(new SqlEntry('p.full_name',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('full_name',$crit));
	}
	
	
	if(isset($_GET['resp'])&&(strlen($_GET['resp'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['resp']));
		else $crit=SecStr(($_GET['resp']));
		
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_responsible_user as sr inner join user as u on u.id=sr.user_id where u.name_s LIKE "%'.$crit.'%" ', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('resp',$crit));
	}
	
	if(isset($_GET['country'])&&(strlen($_GET['country'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['country']));
		else $crit=SecStr(($_GET['country']));
		
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_country as sc on sc.id=u.country_id where sc.name LIKE "%'.$crit.'%" ', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('country',$crit));
	}
	
	if(isset($_GET['city'])&&(strlen($_GET['city'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['city']));
		else $crit=SecStr(($_GET['city']));
		
		
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id  where u.name LIKE "%'.$crit.'%" ', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('city',$crit));
	}
	
	
	if(isset($_GET['crea_name_s'])&&(strlen($_GET['crea_name_s'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['crea_name_s']));
		else $crit=SecStr(($_GET['crea_name_s']));
		
		$decorator->AddEntry(new SqlEntry('crea.name_s',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('crea_name_s',$crit));
	}
	
	if(isset($_GET['holding'])&&(strlen($_GET['holding'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['holding']));
		else $crit=SecStr(($_GET['holding']));
		
		$decorator->AddEntry(new SqlEntry('holding.full_name',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('holding',$crit));
	}
	
	if(isset($_GET['subholding'])&&(strlen($_GET['subholding'])>0)){
		
		if($print==1) $crit=SecStr(iconv("utf-8","windows-1251",$_GET['subholding']));
		else $crit=SecStr(($_GET['subholding']));
		
		$decorator->AddEntry(new SqlEntry('subholding.full_name',$crit, SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('subholding',$crit));
	}
	

	

	 //фильтры по виду контрагента
	$supplier_kinds=NULL;
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET['supplier_kinds'])&&is_array($_GET['supplier_kinds'])) $cou_stat=count($_GET['supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET['supplier_kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			
			foreach($kinds as $k=>$v) {
				$decorator->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator->AddEntry(new UriEntry('supplier_kinds[]',$v));
				
				if($v==1) $supplier_kinds[]='p.is_customer'; 
				elseif($v==2) $supplier_kinds[]='p.is_supplier';
				elseif($v==3) $supplier_kinds[]='p.is_partner';
				elseif($v==4) $supplier_kinds[]='none';	
			
			}
			
			//если выбраны вообще все виды - то блок исключаем!
			if(count($supplier_kinds)>=4) $supplier_kinds=NULL; 
			elseif(count($supplier_kinds)>0){
				$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
				
				foreach($supplier_kinds as $k=>$v) {
					
					if($v=='none'){
						$decorator->AddEntry(new SqlEntry('p.is_customer',0, SqlEntry::E));	
						$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_AND));	
						$decorator->AddEntry(new SqlEntry('p.is_supplier',0, SqlEntry::E));
						$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_AND));	
						$decorator->AddEntry(new SqlEntry('p.is_partner',0, SqlEntry::E));
					}
					else $decorator->AddEntry(new SqlEntry($v,1, SqlEntry::E));
					if($k+1<count($supplier_kinds)) $decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));	
				}
				
				$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));	
			}
			
			
		}
	} 
	
	
	if(isset($_GET['quick_find'])){
		$decorator->AddEntry(new UriEntry('quick_find',1));
		
	}

	
	
	
	if(!isset($_GET['sortmode'])){
		$sortmode=1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.inn',SqlOrdEntry::ASC));
		break;	
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.legal_address',SqlOrdEntry::ASC));
		break;
		
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.kpp',SqlOrdEntry::ASC));
		break;
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::DESC));
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
		break;
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('sc.name',SqlOrdEntry::DESC));
		break;
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('sc.name',SqlOrdEntry::ASC));
		break;
		
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('sc1.name',SqlOrdEntry::DESC));
		break;
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('sc1.name',SqlOrdEntry::ASC));
		break;
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('crea.name_s',SqlOrdEntry::DESC));
		break;
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('crea.name_s',SqlOrdEntry::ASC));
		break;
		

		case 20:
			$decorator->AddEntry(new SqlOrdEntry('holding.full_name',SqlOrdEntry::DESC));
		break;
		case 21:
			$decorator->AddEntry(new SqlOrdEntry('holding.full_name',SqlOrdEntry::ASC));
		break;
		
		case 22:
			$decorator->AddEntry(new SqlOrdEntry('subholding.full_name',SqlOrdEntry::DESC));
		break;
		case 23:
			$decorator->AddEntry(new SqlOrdEntry('subholding.full_name',SqlOrdEntry::ASC));
		break;

		

		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);


 
	
	$ug=new SuppliersGroup;
	$ug->SetAuthResult($result);
	
	if($print==0) $uug= $ug->GetItems('suppliers/suppliers'.$print_add.'.html',$decorator,$from,$to_page,false,$au->user_rights->CheckAccess('w',543), $limited_supplier, $result, $au->user_rights->CheckAccess('w',914));
	else $uug= $ug->GetItems('suppliers/suppliers'.$print_add.'.html',$decorator,0,1000000,false,$au->user_rights->CheckAccess('w',543), $limited_supplier, $result, $au->user_rights->CheckAccess('w',914));
	
	
	$sm->assign('users',$uug);
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('users',$uug);
	
	$content=$sm->fetch('suppliers/suppliers_page'.$print_add.'.html');
	
	
	
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