<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //дл€ протокола HTTP/1.1
Header("Pragma: no-cache"); // дл€ протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и врем€ генерации страницы
header("Expires: " . date("r")); // дата и врем€ врем€, когда страница будет считатьс€ устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/orgsgroup.php');

require_once('classes/period_checker.php');
require_once('classes/pergroup.php');
require_once('classes/peritem.php');

require_once('classes/acc_item.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'–еестр организаций');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	elseif(isset($_SESSION['org_from'])){
		$from=abs((int)$_SESSION['org_from']);
	}else $from=0;
	$_SESSION['org_from']=$from;


if(isset($_POST['doInp'])){
	if(!$au->user_rights->CheckAccess('x',117)){
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
	
	header("Location: organizations.php");	
	die();
}





if(!$au->user_rights->CheckAccess('w',117)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


//журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел ћои организации',NULL,117);


if(isset($_GET['action'])&&($_GET['action']=='bind_pays_acc')){
	if( $au->user_rights->CheckAccess('w',872)){
	//получить даты, проверить, не закрыт ли период
	//если не закрыт...
	$_pch=new PeriodChecker; $beg_date=$_pch->GetDate();
	$beg_year=date('Y',datefromdmy($beg_date));
	
	$end_year=date('Y');
	$_years=array(); for($i=$beg_year;$i<=$end_year; $i++) $_years[]=$i;
	
	$quarts=array(); $final_quarts=array();
	
	foreach($_years as $k=>$year){
		$quarts[]=array('number'=>'1', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,1,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,3,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,1,1,$year), 'pdate_end_unf'=>mktime(23,59,59,3,31,$year));
		
		$quarts[]=array('number'=>'2', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,4,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,6,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,4,1,$year), 'pdate_end_unf'=>mktime(23,59,59,6,30,$year));
			
			$quarts[]=array('number'=>'3', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,7,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,9,30,$year)), 'pdate_beg_unf'=>mktime(0,0,0,7,1,$year), 'pdate_end_unf'=>mktime(23,59,59,9,30,$year));
			
			$quarts[]=array('number'=>'4', 'year'=>$year, 'pdate_beg'=>date('d.m.Y', mktime(0,0,0,10,1,$year)), 'pdate_end'=>date('d.m.Y', mktime(23,59,59,12,31,$year)), 'pdate_beg_unf'=>mktime(0,0,0,10,1,$year), 'pdate_end_unf'=>mktime(23,59,59,12,31,$year));	
	}
	
	$_per=new PerItem;
	foreach($quarts as $k=>$v){
		if(time()<$v['pdate_beg_unf']) continue;
		$per=$_per->GetItemByFields(array('org_id'=>$result['org_id'],'pdate_beg'=>$v['pdate_beg'], 'pdate_end'=>$v['pdate_end']));
		if(($per===false)||($per['is_confirmed']==0)){
			$final_quarts[]=$v;
		}
	}
	
	//print_r($final_quarts);
	
	
	//блок очистки
	foreach($final_quarts as $k=>$v){
		$sql='select id from supplier where is_org=0 and org_id="'.$result['org_id'].'" and id in(
				select distinct b.supplier_id from
				acceptance as a inner join bill as b on b.id=a.bill_id
				where
				a.is_confirmed=1
				and a.is_incoming=0
				and a.org_id="'.$result['org_id'].'"
				and (a.given_pdate between "'.$v['pdate_beg_unf'].'" and "'.$v['pdate_end_unf'].'")
				)
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$_acc=new AccItem;
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$acc_ids=$_acc->GetLatestAccs($f['id'], $result['org_id'], $v['pdate_beg_unf'], NULL, $v['pdate_beg_unf'], $v['pdate_end_unf']	);
				
				//print_r($acc_ids);
				if(count($acc_ids)>0){
					//if($f['id']!=13) continue;
					//echo 'чищу прикреплени€ к реализаци€м: '.implode(', ',$acc_ids);
					
					$_acc->FreeBindedPayments(NULL,$acc_ids, 1, $result);
					
				//	echo ' прикрепл€ю... ';
					//$_acc->AutoBind($f['id'],  $result['org_id'],$v['pdate_beg_unf'], $result,  NULL, $v['pdate_beg_unf'], $v['pdate_end_unf']); 	
				}
			}	
		
	}
	
	//блок прикреплени€
	foreach($final_quarts as $k=>$v){
		$sql='select id from supplier where is_org=0 and org_id="'.$result['org_id'].'" and id in(
				select distinct b.supplier_id from
				acceptance as a inner join bill as b on b.id=a.bill_id
				where
				a.is_confirmed=1
				and a.is_incoming=0
				and a.org_id="'.$result['org_id'].'"
				and (a.given_pdate between "'.$v['pdate_beg_unf'].'" and "'.$v['pdate_end_unf'].'")
				)
				';
				
			//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$_acc=new AccItem;
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$acc_ids=$_acc->GetLatestAccs($f['id'], $result['org_id'], $v['pdate_beg_unf'], NULL, $v['pdate_beg_unf'], $v['pdate_end_unf']	);
				
				//print_r($acc_ids);
				if(count($acc_ids)>0){
					//if($f['id']!=10) continue;
					//echo 'чищу прикреплени€ к реализаци€м: '.implode(', ',$acc_ids);
					
					//$_acc->FreeBindedPayments(NULL,$acc_ids, 1, $result);
					
					//echo ' прикрепл€ю... ';
					$_acc->AutoBind($f['id'],  $result['org_id'],$v['pdate_beg_unf'], $result,  NULL, $v['pdate_beg_unf'], $v['pdate_end_unf']); 	
				}
			}	
		
	}
	
	$log->PutEntry($result['id'],'перераспределил вход€щие оплаты по реализаци€м',NULL,872);
	
	
	}
	 
	
	header("Location: organizations.php");
	die();	
	
}


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


	$_menu_id=31;
	
	include('inc/menu.php');
	
	
	//демонстраци€ стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	//строим вкладку администрировани€
	/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',120)||
							$au->user_rights->CheckAccess('x',121)||
							$au->user_rights->CheckAccess('x',122)||
							$au->user_rights->CheckAccess('x',123)||
							$au->user_rights->CheckAccess('x',124)||
							$au->user_rights->CheckAccess('x',117)
							);
	$dto=new DiscrTableObjects($result['id'],array('120','121','122','123','124','117'));
	$admin=$dto->Draw('organizations.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	//–азбор переменных запроса
	/*if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;*/
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=ITEMS_PER_PAGE;
	
	$decorator=new DBDecorator;
	
	if(isset($_GET['code'])&&(strlen($_GET['code'])>0)){
		$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code']), SqlEntry::E));
		$decorator->AddEntry(new UriEntry('code',$_GET['code']));
	}
	
	
	if(isset($_GET['is_active'])){
		$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('is_active',1));
	}else{
		if(count($_GET)>0) $decorator->AddEntry(new UriEntry('is_active',0));	
		else {
			$decorator->AddEntry(new UriEntry('is_active',1));	
			$decorator->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));
		}
	}
	
	
	if(isset($_GET['legal_address'])&&(strlen($_GET['legal_address'])>0)){
		$decorator->AddEntry(new SqlEntry('p.legal_address',SecStr($_GET['legal_address']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('legal_address',$_GET['legal_address']));
	}
	
	if(isset($_GET['inn'])&&(strlen($_GET['inn'])>0)){
		$decorator->AddEntry(new SqlEntry('p.inn',SecStr($_GET['inn']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('inn',$_GET['inn']));
	}
	
	if(isset($_GET['kpp'])&&(strlen($_GET['kpp'])>0)){
		$decorator->AddEntry(new SqlEntry('p.kpp',SecStr($_GET['kpp']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('kpp',$_GET['kpp']));
	}
	
	if(isset($_GET['full_name'])&&(strlen($_GET['full_name'])>0)){
		$decorator->AddEntry(new SqlEntry('p.full_name',SecStr($_GET['full_name']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('full_name',$_GET['full_name']));
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
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	$decorator->AddEntry(new UriEntry('to_page',$to_page));
	
	
	
	
	
	$ug=new OrgsGroup;
	$ug->SetAuthResult($result);

	$uug= $ug->GetItems('org/orgs.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',872),$au->user_rights->CheckAccess('w',467)||$au->user_rights->CheckAccess('w',468),
	$au->user_rights->CheckAccess('w',821));
	
	
	$sm->assign('users',$uug);
	$content=$sm->fetch('org/org_l_page.html');
	
	
	
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