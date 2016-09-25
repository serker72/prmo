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
require_once('classes/billfileitem.php');
require_once('classes/billfilegroup.php');

require_once('classes/supplieritem.php');

require_once('classes/billitem.php');

require_once('classes/trust_group.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Реестр доверенностей');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['trust_from'])){
		$from=abs((int)$_SESSION['trust_from']);
	}else $from=0;
	$_SESSION['trust_from']=$from;

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',198)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Реестр доверенностей',NULL,198);



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=36;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('bill',$user);
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	 
 
	//блок фильтров статуса
	/*$status_ids=array();
	$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $cou_stat++;
	if($cou_stat>0){
		//есть гет-запросы	
		
		foreach($_GET as $k=>$v) if(eregi('^status_id_', $k)) $status_ids[]=(int)eregi_replace('^status_id_','',$k);
	}else{
		$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^trust_status_id_', $k)) $cou_stat++;
		
		if($cou_stat>0){
			//есть кукисы
			foreach($_COOKIE as $k=>$v) if(eregi('^trust_status_id_', $k)) $status_ids[]=(int)eregi_replace('^trust_status_id_','',$k);
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
	
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['statuses'])&&is_array($_GET['statuses'])) $cou_stat=count($_GET['statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^trust_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^trust_status_id_', $k)) $status_ids[]=(int)eregi_replace('^trust_status_id_','',$k);
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
		
	
	 
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	$decorator->AddEntry(new SqlEntry('p.pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)+60*60*24));
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	if(isset($_GET['given_no'])&&(strlen($_GET['given_no'])>0)){
		$decorator->AddEntry(new SqlEntry('p.given_no',SecStr($_GET['given_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('given_no',$_GET['given_no']));
	}
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	if(isset($_GET['id'])&&(strlen($_GET['id'])>0)){
		$decorator->AddEntry(new SqlEntry('p.id',SecStr($_GET['id']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('id',$_GET['id']));
	}
	
	/*if(isset($_GET['supplier_id'])&&(strlen($_GET['supplier_id'])>0)){
		$decorator->AddEntry(new SqlEntry('o.supplier_id',abs((int)$_GET['supplier_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('supplier_id',$_GET['supplier_id']));
	}*/
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
	}
	
	if(isset($_GET['manager_name'])&&(strlen($_GET['manager_name'])>0)){
		$decorator->AddEntry(new SqlEntry('mn.name_s',SecStr($_GET['manager_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name']));
	}
	
	if(isset($_GET['name_s'])&&(strlen($_GET['name_s'])>0)){
		$decorator->AddEntry(new SqlEntry('uu.name_s',SecStr($_GET['name_s']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('name_s',$_GET['name_s']));
	}
	
	if(isset($_GET['storage_id'])&&(strlen($_GET['storage_id'])>0)){
		$decorator->AddEntry(new SqlEntry('o.storage_id',abs((int)$_GET['storage_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('storage_id',$_GET['storage_id']));
	}
	
	if(isset($_GET['sector_id'])&&(strlen($_GET['sector_id'])>0)){
		$decorator->AddEntry(new SqlEntry('o.sector_id',abs((int)$_GET['sector_id']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('sector_id',$_GET['sector_id']));
	}
	
	
	
	
	
	$_pp=new TrustGroup;
	$_pp->SetPageName('all_trusts.php');
	
	//$_pp->AutoAnnul();
	
	$_pp->SetAuthResult($result);
	$ships=$_pp->ShowAllPos('trust/all_trusts_list.html', $decorator, $au->user_rights->CheckAccess('w',208)||$au->user_rights->CheckAccess('w',284), $au->user_rights->CheckAccess('w',212),$from, $to_page,$au->user_rights->CheckAccess('w',210), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',213), $au->user_rights->CheckAccess('w',284)  );
			
	$sm->assign('log',$ships); 	
	$content=$sm->fetch('trust/all_trusts_page.html');
	
	
	
	
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