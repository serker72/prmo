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

require_once('classes/bill_in_item.php');

require_once('classes/trust_group.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Доверенности входящего счета');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['bill_id'])){
	if(!isset($_POST['bill_id'])){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();		
	}else $bill_id=abs((int)$_POST['bill_id']);
	
}else $bill_id=abs((int)$_GET['bill_id']); 

$_user=new BillInItem;
$user=$_user->GetItemById($bill_id);
if(($user===false)){
	header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();	
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',202)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}





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
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',(
							$au->user_rights->CheckAccess('x',93)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('93'));
	$admin=$dto->Draw('trust.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	foreach($user as $k=>$v){
		$user[$k]=stripslashes($v);
	}
	
	
	$sm->assign('bill',$user);
	
	$decorator=new DBDecorator;
	
	$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
	
	
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	
		$_acg=new TrustGroup;
			$dec2=new DBDecorator;
			
	//блок фильтров статуса
	/*$status_ids=array();
	$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^trust_status_id_', $k)) $cou_stat++;
	if($cou_stat>0){
		//есть гет-запросы	
		
		foreach($_GET as $k=>$v) if(eregi('^trust_status_id_', $k)) $status_ids[]=(int)eregi_replace('^trust_status_id_','',$k);
	}else{
		$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^trust_trust_status_id_', $k)) $cou_stat++;
		
		if($cou_stat>0){
			//есть кукисы
			foreach($_COOKIE as $k=>$v) if(eregi('^trust_trust_status_id_', $k)) $status_ids[]=(int)eregi_replace('^trust_trust_status_id_','',$k);
		}else{
			//ничего нет - выбираем ВСЕ!	
			$dec2->AddEntry(new UriEntry('all_statuses',1));
		}
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$dec2->AddEntry(new UriEntry('all_statuses',1));
		}else{
			foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('trust_status_id_'.$v,1));
			$dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
		}
	}		
	*/
	
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['trust_statuses'])&&is_array($_GET['trust_statuses'])) $cou_stat=count($_GET['trust_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['trust_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^trust_trust_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^trust_trust_status_id_', $k)) $status_ids[]=(int)eregi_replace('^trust_trust_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $dec2->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $dec2->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('status_id_'.$v,1));
				  $dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('trust_statuses[]',$v));
			  }
		  } 
		
	
			
			$_acg->SetAuthResult($result);
			$ships=$_acg->ShowPos($bill_id,'trust/trust_list.html', $dec2,  $au->user_rights->CheckAccess('w',208)||$au->user_rights->CheckAccess('w',284), $au->user_rights->CheckAccess('w',212), $au->user_rights->CheckAccess('w',210), $au->user_rights->CheckAccess('w',96),true,false, $au->user_rights->CheckAccess('w',213));
			
			$sm->assign('log',$ships); 	
	$content=$sm->fetch('trust/trust_page.html');
	
	
	
	
	
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