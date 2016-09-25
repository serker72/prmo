<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');

require_once('../classes/billitem.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/billposgroup.php');

require_once('../classes/maxformer.php');


require_once('../classes/acc_notesgroup.php');
require_once('../classes/acc_notesitem.php');

require_once('../classes/acc_posgroup.php');
require_once('../classes/acc_notesitem.php');

require_once('../classes/acc_item.php');
require_once('../classes/acc_group.php');
require_once('../classes/user_s_item.php');
require_once('../classes/accreports.php');

require_once('../classes/acc_item.php');
require_once('../classes/period_checker.php');

$result=array('id'=>0);


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
		$sql='select id, org_id from supplier where is_org=0 and     id in(
				select distinct b.supplier_id from
				acceptance as a inner join bill as b on b.id=a.bill_id
				where
				a.is_confirmed=1
				and a.is_incoming=0
				 
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
				$acc_ids=$_acc->GetLatestAccs($f['id'], $f['org_id'], $v['pdate_beg_unf'], NULL, $v['pdate_beg_unf'], $v['pdate_end_unf']	);
				
				//print_r($acc_ids);
				if(count($acc_ids)>0){
				//	if($f['id']!=13) continue;
					$_acc->FreeBindedPayments(NULL,$acc_ids, 1, $result);
					//$_acc->AutoBind($f['id'], $f['org_id'],$v['pdate_beg_unf'], $result, NULL,  $v['pdate_beg_unf'], $v['pdate_end_unf']); 	
				}
			}
		
	}
	
	//блок прикрепления
	foreach($final_quarts as $k=>$v){
		$sql='select id, org_id from supplier where is_org=0 and     id in(
				select distinct b.supplier_id from
				acceptance as a inner join bill as b on b.id=a.bill_id
				where
				a.is_confirmed=1
				and a.is_incoming=0
				 
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
				$acc_ids=$_acc->GetLatestAccs($f['id'], $f['org_id'], $v['pdate_beg_unf'], NULL, $v['pdate_beg_unf'], $v['pdate_end_unf']	);
				
				//print_r($acc_ids);
				if(count($acc_ids)>0){
				//	if($f['id']!=13) continue;
					//$_acc->FreeBindedPayments(NULL,$acc_ids, 1, $result);
					$_acc->AutoBind($f['id'], $f['org_id'],$v['pdate_beg_unf'], $result, NULL,  $v['pdate_beg_unf'], $v['pdate_end_unf']); 	
				}
			}
		
	}
	
	
?>