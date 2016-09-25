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
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/fileitem.php');
require_once('classes/filegroup.php');

require_once('classes/an_waa_komplekt.php');
require_once('classes/an_waa_bills.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Без автоаннулирования');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;

//внесение изменений в права
if(isset($_POST['doInp'])){
	
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
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location: an_waa.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',80)){
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

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);

if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',80)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);



	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',($au->user_rights->CheckAccess('x',113)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('113'));
	$admin=$dto->Draw('an_pm.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	/***********************************************************/
	//Вкладка Заявки
	$log=new AnWaaKomplekt;
	//Разбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=0;//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	
	/*$decorator->AddEntry(new SqlEntry('p.status_id',12, SqlEntry::E));
	$decorator->AddEntry(new UriEntry('status_id',12));*/
	
	$decorator->AddEntry(new SqlEntry('p.cannot_an',1, SqlEntry::E));
	//$decorator->AddEntry(new UriEntry('cannot_eq',1));
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	//var_dump($au->FltSector($result));
	if(!$au->FltSector($result,379)){
	  $limited_sector=NULL;
	  if(isset($_GET['sector_id'])&&(strlen($_GET['sector_id'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id']), SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id']));
	  }
	}else{
		//накладываем фильтр...
		$_sectors_to_user=new SectorToUser();
		
		$sectors_to_user=$_sectors_to_user->GetSectorArr($result['id']);
		
		$_extended_limited_sector=$_sectors_to_user->GetExtendedSectorIdsArr($result['id'],false,379);
		$sectors_to_user_ids=$_extended_limited_sector['sector_ids'];
		$limited_sector=$sectors_to_user_ids;
		
		
		if(is_array($_extended_limited_sector['pairs'])){
			
		 	 $decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			 
			 $cter=0;
			 foreach($_extended_limited_sector['pairs'] as $k=>$v){
				if($cter!=0) $decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR)); 
				$decorator->AddEntry(new SqlEntry('p.sector_id',NULL, SqlEntry::IN_VALUES,NULL,array($v[0])));
				
				$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_AND));
				$decorator->AddEntry(new SqlEntry('p.storage_id',NULL, SqlEntry::IN_VALUES,NULL,array($v[1])));
				$cter++;
			 }
			 $decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}
		
		if(isset($_GET['sector_id'])&&(strlen($_GET['sector_id'])>0)&&(in_array($_GET['sector_id'],$sectors_to_user_ids))){
			$decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id']), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id']));	
		}
		
	}
	
	
	if(isset($_GET['only_active_sectors'])&&($_GET['only_active_sectors']==1)){
		$only_active_sectors=1;
		$decorator->AddEntry(new UriEntry('only_active_sectors',1));
	}elseif(isset($_GET['only_active_sectors'])&&($_GET['only_active_sectors']==0)){
		$only_active_sectors=0;
		$decorator->AddEntry(new UriEntry('only_active_sectors',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doFilter'])||isset($_GET['doFilter_x']))) {
		
		
			$only_active_sectors=0;
			$decorator->AddEntry(new UriEntry('only_active_sectors',0));
	
		}else{
			$only_active_sectors=1;
			$decorator->AddEntry(new UriEntry('only_active_sectors',1));
		}
	
	
	}
	
	if(isset($_GET['only_active_storages'])&&($_GET['only_active_storages']==1)){
		$only_active_storages=1;
		$decorator->AddEntry(new UriEntry('only_active_storages',1));
	}elseif(isset($_GET['only_active_storages'])&&($_GET['only_active_storages']==1)){
		$only_active_storages=0;
		$decorator->AddEntry(new UriEntry('only_active_storages',0));
	}else{
		if((count($_GET)>1)&&(isset($_GET['doFilter'])||isset($_GET['doFilter_x']))) {
		
			$only_active_storages=0;
			$decorator->AddEntry(new UriEntry('only_active_storages',0));
	
		}else{
			$only_active_storages=1;
			$decorator->AddEntry(new UriEntry('only_active_storages',1));
		}
	
	
	}
	
	
	if(isset($_GET['storage_id'])&&(strlen($_GET['storage_id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.storage_id',SecStr($_GET['storage_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('storage_id',$_GET['storage_id']));
	}
	
	if(isset($_GET['name'])&&(strlen($_GET['name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name']));
	}
	
	
	
	if(isset($_GET['fact_address'])&&(strlen($_GET['fact_address'])>0)){
		$decorator->AddEntry(new SqlEntry('p.fact_address',SecStr($_GET['fact_address']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('fact_address',$_GET['fact_address']));
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	if(!isset($_GET['sortmode'])){
		$sortmode=0;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
	
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.end_pdate',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.begin_pdate',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.end_pdate',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('p.begin_pdate',SqlOrdEntry::ASC));
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('p.sector_name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.sector_name',SqlOrdEntry::DESC));
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('p.sector_name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('p.sector_name',SqlOrdEntry::ASC));
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	$log->SetAuthResult($result);
	$llg=$log->ShowPos('an_waa/komplekt_list'.$print_add.'.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',82)||$au->user_rights->CheckAccess('w',282),  $au->user_rights->CheckAccess('w',83),DateFromdmY($pdate1), DateFromdmY($pdate2),true,false, $au->user_rights->CheckAccess('w',132),$limited_sector, $au->user_rights->CheckAccess('w',81),$au->user_rights->CheckAccess('w',291),  
	isset($_GET['doFilter'])||isset($_GET['doFilter_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))
	);
	
	
	$sm->assign('log',$llg);
	
	
/********************************************************************************************************/	
//счета без ав	
	
	$log=new AnWaaBills;
	
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	
	if(!isset($_GET['sortmode2'])){
		$sortmode2=0;	
	}else{
		$sortmode2=abs((int)$_GET['sortmode2']);
	}
	
	
	
	
	
	
//	$decorator->AddEntry(new SqlEntry('p.status_id',NULL, SqlEntry::IN_VALUES,NULL, array(2,9)));
	
	
	
	
			$_pdate1=0;//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	
	$decorator->AddEntry(new SqlEntry('p.cannot_an',1, SqlEntry::E));
	
	if(isset($_GET['code2'])&&(strlen($_GET['code2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code2']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code2',$_GET['code2']));
	}
	
	if(isset($_GET['supplier_bill_no2'])&&(strlen($_GET['supplier_bill_no2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no2']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no2',$_GET['supplier_bill_no2']));
	}
	
	if(isset($_GET['name2'])&&(strlen($_GET['name2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name2']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name',$_GET['name2']));
	}
	
	
	if(isset($_GET['supplier_name2'])&&(strlen($_GET['supplier_name2'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name2']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name2',$_GET['supplier_name2']));
		//$sortmode=5;
	}
	
	if(isset($_GET['manager_name2'])&&(strlen($_GET['manager_name2'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name2']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name2',$_GET['manager_name2']));
	}
	
	if(isset($_GET['storage_id2'])&&(strlen($_GET['storage_id2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.storage_id',abs((int)$_GET['storage_id2']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('storage_id2',$_GET['storage_id2']));
	}
	
	
	if(!$au->FltSector($result)){
	  $limited_sector=NULL;
	  if(isset($_GET['sector_id2'])&&(strlen($_GET['sector_id2'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id2']), SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('sector_id2',$_GET['sector_id2']));
	  }
	}else{
		//накладываем фильтр...
		$_sectors_to_user=new SectorToUser();
		
		$sectors_to_user=$_sectors_to_user->GetSectorArr($result['id']);
		$sectors_to_user_ids=$_sectors_to_user->GetSectorIdsArr($result['id']);
		
		$limited_sector=$sectors_to_user_ids;
		
		$decorator->AddEntry(new SqlEntry('p.sector_id',NULL, SqlEntry::IN_VALUES,NULL,$sectors_to_user_ids ));
		
		if(isset($_GET['sector_id2'])&&(strlen($_GET['sector_id2'])>0)&&(in_array($_GET['sector_id2'],$sectors_to_user_ids))){
			$decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id2']), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('sector_id2',$_GET['sector_id2']));	
		}
		
	}
	
	if(isset($_GET['user_confirm_price_id2'])&&(strlen($_GET['user_confirm_price_id2'])>0)){
		$decorator->AddEntry(new SqlEntry('p.user_confirm_price_id2',abs((int)$_GET['user_confirm_price_id2']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('user_confirm_price_id2',$_GET['user_confirm_price_id2']));
	}
	
	
	//сортировку можно подписать как дополнительный параметр для UriEntry
	
	
	switch($sortmode2){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		
	}
	
	$decorator->AddEntry(new UriEntry('sortmode2',$sortmode2));
	
	
	
	
	$log->SetAuthResult($result);
	
	$llg=$log->ShowPos('an_waa/bills_list'.$print_add.'.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94),  '2', $au->user_rights->CheckAccess('w',95), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',131),$limited_sector,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197), $au->user_rights->CheckAccess('w',292),	isset($_GET['doFilter2'])||isset($_GET['doFilter2_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))	);
	
	
	$sm->assign('log2',$llg);
	
	
	$sm->assign('has_2',  $au->user_rights->CheckAccess('w',97));
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_waa/an_waa_form'.$print_add.'.html');
	
	
	
	
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