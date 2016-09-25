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
require_once('classes/orgsgroup.php');
require_once('classes/pergroup.php');
require_once('classes/peritem.php');

require_once('classes/acc_item.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчетные периоды');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	

if(!$au->user_rights->CheckAccess('w',117)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


/*
if(isset($_GET['action'])&&($_GET['action']=='bind_pays_acc')){
	//получить даты, проверить, не закрыт ли период
	//если не закрыт...
	$_per=new PerItem;
	$per=$_per->GetItemByFields(array('org_id'=>$result['org_id'],'pdate_beg'=>(int)$_GET['pdate_beg'], 'pdate_end'=>(int)$_GET['pdate_end']));
	if(($per===false)||($per['is_confirmed']==0)){
		if( $au->user_rights->CheckAccess('w',872)){
			//получить список конт-тов по данной организации, у кого есть утв. реализации 	за этот период
			$sql='select id from supplier where is_org=0 and org_id="'.$result['org_id'].'" and id in(
				select distinct b.supplier_id from
				acceptance as a inner join bill as b on b.id=a.bill_id
				where
				a.is_confirmed=1
				and a.is_incoming=0
				and a.org_id="'.$result['org_id'].'"
				and (a.given_pdate between "'.(int)$_GET['pdate_beg'].'" and "'.(int)$_GET['pdate_end'].'")
				)
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$_acc=new AccItem;
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$acc_ids=$_acc->GetLatestAccs($f['id'], $result['org_id'], (int)$_GET['pdate_beg'], NULL, (int)$_GET['pdate_beg'], (int)$_GET['pdate_end']	);
				
				//print_r($acc_ids);
				if(count($acc_ids)>0){
					//if($f['id']!=13) continue;
					$_acc->FreeBindedPayments(NULL,$acc_ids, 1, $result);
					$_acc->AutoBind($f['id'], $result['org_id'],(int)$_GET['pdate_beg'], $result, NULL, (int)$_GET['pdate_beg'], (int)$_GET['pdate_end']); 	
				}
			}
			
		}
	}
	
	header("Location: periods.php");
	die();	
	
}*/




//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

	$_menu_id=31;
	

	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
	$ug=new PerGroup;
	
	
	$uug= $ug->DrawPeriods($result['org_id'],date('Y'),'periods/periods.html', $au->user_rights->CheckAccess('w',467),$au->user_rights->CheckAccess('w',468), $result['org_id'], false,true, NULL, $au->user_rights->CheckAccess('w',872));
	$sm->assign('users', $uug);
	
	
	$content=$sm->fetch('periods/periods_page.html');
	
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