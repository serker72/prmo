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
require_once('classes/invgroup.php');
require_once('classes/invcalcgroup.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Инвентаризация');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['invent_from'])){
		$from=abs((int)$_SESSION['invent_from']);
	}else $from=0;
	$_SESSION['invent_from']=$from;



if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);



if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);


if(!($au->user_rights->CheckAccess('w',321)||$au->user_rights->CheckAccess('w',450))){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//журнал событий 
$log=new ActionLog;
if($au->user_rights->CheckAccess('w',321)) $log->PutEntry($result['id'],'открыл раздел Инвентаризация складских остатков',NULL,321);
if($au->user_rights->CheckAccess('w',450)) $log->PutEntry($result['id'],'открыл раздел Инвентаризация расчетов с контрагентам',NULL,450);




//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=39;

	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
//***************************************Инвентаризация складкских остатков**************
	
	
	if($au->user_rights->CheckAccess('w',321)){
	  $log=new InvGroup;
	  //Разбор переменных запроса
	  /*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	  else $from=0;*/
	  
	  if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	  else $to_page=ITEMS_PER_PAGE;
	  
	  $decorator=new DBDecorator;
	  
	  $decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	  
	  
	  
	  //блок фильтров статуса
	  
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^inv_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^inv_status_id_', $k)) $status_ids[]=(int)eregi_replace('^inv_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('statuses[]',$v));
			  }
		  } 
		
	
		/*$status_ids=array();
		$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $cou_stat++;
		if($cou_stat>0){
			//есть гет-запросы	
			
			foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $status_ids[]=(int)eregi_replace('^status_id_','',$k);
		}else{
			$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^inv_status_id_', $k)) $cou_stat++;
			
			if($cou_stat>0){
				//есть кукисы
				foreach($_COOKIE as $k=>$v) if(eregi('^inv_status_id_', $k)) $status_ids[]=(int)eregi_replace('^inv_status_id_','',$k);
			}else{
				//ничего нет - выбираем ВСЕ!	
				$decorator->AddEntry(new UriEntry('all_statuses',1));
			}
		}
		
		if(count($status_ids)>0){
			$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			
			if($of_zero){
				//ничего нет - выбираем ВСЕ!	
				$decorator->AddEntry(new UriEntry('all_statuses',1));
			}else{
			
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}
		}
	  */
	  
	  if(!isset($_GET['pdate1'])){
	  
			  $_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			  $pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		  
	  }else $pdate1 = $_GET['pdate1'];
	  
	  
	  
	  if(!isset($_GET['pdate2'])){
			  
			  $_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			  $pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	  }else $pdate2 = $_GET['pdate2'];
	  
	  $decorator->AddEntry(new SqlEntry('inventory_pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	  $decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	  $decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	  
	  
	  
	  if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('id',$_GET['id']));
	  }
	  
	   if(isset($_GET['given_no'])&&(strlen($_GET['given_no'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('given_no',$_GET['given_no']));
	  }
	  
	  if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code']));
	  }
	  
	  
	  
	  if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		  $decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	  }
	  
	  if(isset($_GET['storage_id'])&&(strlen($_GET['storage_id'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.storage_id',abs((int)$_GET['storage_id']), SqlEntry::E));
		  $decorator->AddEntry(new UriEntry('storage_id',$_GET['storage_id']));
	  }
	  
	  
	  
	 
		if(isset($_GET['sector_id'])&&(strlen($_GET['sector_id'])>0)){
			$decorator->AddEntry(new SqlEntry('p.sector_id',SecStr($_GET['sector_id']), SqlEntry::E));
			$decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id']));
		}
	 
	  
	  
	  //сортировку можно подписать как дополнительный параметр для UriEntry
	  if(!isset($_GET['sortmode'])){
		  $sortmode=0;	
	  }else{
		  $sortmode=abs((int)$_GET['sortmode']);
	  }
	  
	  
	  switch($sortmode){
		  case 0:
			  $decorator->AddEntry(new SqlOrdEntry('p.inventory_pdate',SqlOrdEntry::DESC));
		  break;
		  case 1:
			  $decorator->AddEntry(new SqlOrdEntry('p.inventory_pdate',SqlOrdEntry::ASC));
		  break;
		  case 2:
			  $decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		  break;	
		  case 3:
			  $decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		  break;
				  
		  /*case 4:
			  $decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		  break;	
		  case 5:
			  $decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		  break;*/
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
	  /*	case 10:
			  $decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			  
		  break;	
		  case 11:
			  $decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			  
		  break;*/
		  
		  default:
			  $decorator->AddEntry(new SqlOrdEntry('p.inventory_pdate',SqlOrdEntry::DESC));
		  break;	
		  
	  }
	  //$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
	  
	  $decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	  
	  $decorator->AddEntry(new UriEntry('to_page',$to_page));
	  
	  $decorator->AddEntry(new UriEntry('tab_page',1));
	  
	  
	  
	  //echo mktime(0,0,0,6,10,2012);
	  
	  //$log->AutoAnnul();
	  $log->SetAuthResult($result);
	  $llg=$log->ShowPos('inv/invs_list.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',322),  $au->user_rights->CheckAccess('w',326)||$au->user_rights->CheckAccess('w',335), $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',331), $au->user_rights->CheckAccess('w',331),true,false,$au->user_rights->CheckAccess('w',337),$limited_sector, $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',332), $au->user_rights->CheckAccess('w',334),  $au->user_rights->CheckAccess('w',335));
	  
	  
	  $sm->assign('log',$llg);
	  
	}
	//
	$sm->assign('has_1',  $au->user_rights->CheckAccess('w',321));
	
	
	
	
	
//******************************инвентаризация расчетов с поставщиками****************
	if($au->user_rights->CheckAccess('w',450)){
	  $log=new InvCalcGroup;
	  //Разбор переменных запроса
	 
	  if(isset($_GET['to_page2'])) $to_page=abs((int)$_GET['to_page2']);
	  else $to_page=ITEMS_PER_PAGE;
	  
	  $decorator=new DBDecorator;
	  
	  $decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
	  
	  //блок фильтров статуса
	/*$status_ids=array();
	$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^invcalc_status_id_', $k)) $cou_stat++;
	if($cou_stat>0){
		//есть гет-запросы	
		
		foreach($_GET as $k=>$v) if(eregi('^invcalc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^invcalc_status_id_','',$k);
	}else{
		$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^invcalc_invcalc_status_id_', $k)) $cou_stat++;
		
		if($cou_stat>0){
			//есть кукисы
			foreach($_COOKIE as $k=>$v) if(eregi('^invcalc_invcalc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^invcalc_invcalc_status_id_','',$k);
		}else{
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('invcalc_status_id_'.$v,1));
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
		}
	}
	  */
	  
	  $status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['invcalc_statuses'])&&is_array($_GET['invcalc_statuses'])) $cou_stat=count($_GET['invcalc_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['invcalc_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^invcalc_invcalc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^invcalc_invcalc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^invcalc_invcalc_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('invcalc_statuses[]',$v));
			  }
		  } 
		
	
	  
	  
	  if(!isset($_GET['pdate12'])){
	  
			  $_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			  $pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		  
	  }else $pdate1 = $_GET['pdate12'];
	  
	  
	  
	  if(!isset($_GET['pdate22'])){
			  
			  $_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			  $pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	  }else $pdate2 = $_GET['pdate22'];
	  
	  $decorator->AddEntry(new SqlEntry('invcalc_pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	  $decorator->AddEntry(new UriEntry('pdate12',$pdate1));
	  $decorator->AddEntry(new UriEntry('pdate22',$pdate2));
	  
	  
	 
	  
	   if(isset($_GET['given_no2'])&&(strlen($_GET['given_no2'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no2']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('given_no2',$_GET['given_no2']));
	  }
	  
	  if(isset($_GET['code2'])&&(strlen($_GET['code2'])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code2']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code2',$_GET['code2']));
	  }
	  
	  
	  
	  if(isset($_GET['supplier_name2'])&&(strlen($_GET['supplier_name2'])>0)){
		  $decorator->AddEntry(new SqlEntry('sup.full_name',SecStr($_GET['supplier_name2']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('supplier_name2',$_GET['supplier_name2']));
	  }
	  
	  
	  if(isset($_GET['manager_name2'])&&(strlen($_GET['manager_name2'])>0)){
		  $decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name2']), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('manager_name2',$_GET['manager_name2']));
	  }
	  
	  
	  
	 
	  
	  //сортировку можно подписать как дополнительный параметр для UriEntry
	  if(!isset($_GET['sortmode2'])){
		  $sortmode=0;	
	  }else{
		  $sortmode=abs((int)$_GET['sortmode2']);
	  }
	  
	  
	  switch($sortmode){
		  case 0:
			  $decorator->AddEntry(new SqlOrdEntry('p.invcalc_pdate',SqlOrdEntry::DESC));
		  break;
		  case 1:
			  $decorator->AddEntry(new SqlOrdEntry('p.invcalc_pdate',SqlOrdEntry::ASC));
		  break;
		  case 2:
			  $decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		  break;	
		  case 3:
			  $decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		  break;
				  
		  case 4:
			  $decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
		  break;	
		  case 5:
			  $decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
		  break;
		  /*
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
			  
		  break;*/
	  /*	case 10:
			  $decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
			  
		  break;	
		  case 11:
			  $decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
			  
		  break;*/
		  
		  default:
			  $decorator->AddEntry(new SqlOrdEntry('p.invcalc_pdate',SqlOrdEntry::DESC));
		  break;	
		  
	  }
	  
	  $decorator->AddEntry(new UriEntry('sortmode2',$sortmode));
	  
	  $decorator->AddEntry(new UriEntry('to_page2',$to_page));
	  
	  $decorator->AddEntry(new UriEntry('tab_page',2));
	  
	 $log->SetAuthResult($result); 
	  
	  $llg=$log->ShowPos('invcalc/invcalcs_list.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),true,false,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461), $au->user_rights->CheckAccess('w',462));
	  
	  
	  $sm->assign('log2',$llg);
	  
	}
	
	
	
	
	
	
	
	
	
	$sm->assign('has_2',  $au->user_rights->CheckAccess('w',450));
	
	
	
	
	
	$sm->assign('tab_page',$tab_page);
	$content=$sm->fetch('inv/invs.html');
	
	
	

	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>