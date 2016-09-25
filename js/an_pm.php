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
require_once('../classes/billgroup.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/billnotesgroup.php');
require_once('../classes/billnotesitem.php');
require_once('../classes/billpositem.php');
require_once('../classes/billpospmitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');

require_once('../classes/user_s_item.php');
require_once('../classes/an_pm.php');

require_once('../classes/cashitem.php');

require_once('../classes/cash_bill_position_item.php');
require_once('../classes/cashnotesitem.php');
require_once('../classes/cashcreator.php');
require_once('../classes/cash_to_bill_item.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

function FindIndex($value, $array){
	$r=-1;
	if(count($array)>0) foreach($array as $k=>$v){
		if($v==$value){
			$r=$k;
			break;	
		}
	}
	return $r;
}
function FindIndex2($value, $value2, $array, $array2){
	$r=-1;
	if(count($array)>0) foreach($array as $k=>$v){
		if(($v==$value)&&($array2[$k]==$value2)){
			$r=$k;
			break;	
		}
	}
	return $r;
}


$ret='';
if(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций к.в. для счета
	$dec_sep=' ';
	
	$row_ids=$_POST['row_ids'];
	
	
	$an=new AnPm;
	
	$an->ShowData('',$result['org_id'],DateFromDmy(date('d.m.Y',0)),DateFromDmy('31.12.2030'),'an_pm/an_pm_list.html',new DBDecorator,'an_pm.php',true,false,DEC_SEP,$alls,true,1,1,1,NULL, $au->user_rights->CheckAccess('w',363));
	
	$items=array();
	$total_quantity=0; $total_pm=0; $total_marja=0; $total_summa=0; $total_semi_marja=0;
	foreach($alls as $k=>$v){
		$bill=array();
		
		$subs=array();
		foreach($v['subs'] as $kk=>$vv){
			if(!in_array($vv['p_id'],$row_ids)) continue;
			$subs[]=$vv;
				$total_quantity+=$vv['quantity'];
						
						$total_pm+=$vv['unf_vydacha'];
						$total_marja+=$vv['unf_discount_given'];
						$total_semi_marja+=$vv['unf_semi_discount_given'];
						
			//var_dump($vv['price']);			
		}
		
		$bill=$v;
		$bill['subs']=$subs;
		
		if(count($subs)>0){
			 $items[]=$bill;
			 
			  $total_summa+=round($v['total_unf'],2);
		}
		
	}
	
	//var_dump($row_ids);
	//var_dump($alls);
	//var_dump($items);
	
	$sm=new SmartyAj;
	//$sm->assign('do_it',true);
	$sm->assign('items',$items);
	
	 $sm->assign('total_summa',number_format($total_summa,2,'.',$dec_sep));
	  $sm->assign('total_quantity',number_format($total_quantity,2,'.',$dec_sep));
		  $sm->assign('total_pm',number_format($total_pm,2,'.',$dec_sep));
		  $sm->assign('total_marja',number_format($total_marja,2,'.',$dec_sep));
		  $sm->assign('total_semi_marja',number_format($total_semi_marja,2,'.',$dec_sep));
		  
		  
		  
	 //сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}
		$sm->assign('responsible_user_id_ids',$_ids);
		$sm->assign('responsible_user_id_vals',$_vals);
		$sm->assign('responsible_user_id',$result['id']);
	
			  
		  
	$sm->assign('view_full_version',$au->user_rights->CheckAccess('w',363));
	
	$ret.=$sm->fetch("an_pm/an_pm_edit_list.html");
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	
	$_ci=new CashItem;
	$_cpi=new CashBillPositionItem;
	$_cni=new CashNotesItem;
	
	$_cbi=new CashToBillItem;
	
	$_cc=new CashCreator;
	$_bill=new BillItem;
	$_bpi=new BillPosItem;
	
	
	if($au->user_rights->CheckAccess('w',365)||$au->user_rights->CheckAccess('w',363)){
		
		$complex_positions=$_POST['complex_positions'];
		$responsible_user_id=abs((int)$_POST['responsible_user_id']);
		$value=(float)$_POST['value'];
		
		$params=array();
		$params['code']=$_cc->GenLogin($result['id']);
		$params['org_id']=$result['org_id'];
		$params['pdate']=time();
		$params['manager_id']=$result['id'];
		$params['user_confirm_id']=$result['id'];
		$params['confirm_pdate']=time();
		$params['is_confirmed']=1;
		$params['status_id']=2;
		
		$params['bill_id']=0; 
		
		$params['supplier_id']=abs((int)$_POST['supplier_id']); 
		
		$params['kind_id']=4;
		$params['responsible_user_id']=$responsible_user_id;
		$params['code_id']=38;
		
		$params['value']=$value;
		
		$code=$_ci->add($params);	
		
		//журнал
		
		$log->PutEntry($result['id'], 'создал комиссионное вознаграждение по исходящим счетам', NULL, 835, NULL, 'расход № '.$params['code'].'', $code);
		
		
		//позиции
		$complex_positions=$_POST['complex_positions'];
		
		$was_bill_ids=array();
		foreach($complex_positions as $k=>$v){
			$valar=explode(';',$v);
			$bill_position_id=abs((int)$valar[0]);
			$given_value=(float)$valar[1];
			$bill_id=abs((int)$valar[2]);
			
			$cparams=array();
			
			$cparams['cash_id']=$code;
			$cparams['bill_position_id']=$bill_position_id;
			$cparams['given_value']=$given_value;
			
			
			
			$_cpi->Add($cparams);
			
			$bill=$_bill->GetItemById($bill_id);
			$bpi=$_bpi->GetItemById($bill_position_id);
			
			$log->PutEntry($result['id'], 'создал комиссионное вознаграждение по исходящему счету', NULL, 93, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'].', позиция '.SecStr($bpi['name']).', сумма по позиции '.$cparams['given_value'].' руб.', $bill['id']);
			
			$log->PutEntry($result['id'], 'создал комиссионное вознаграждение по исходящему счету', NULL, 835, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'].', позиция '.SecStr($bpi['name']).', сумма по позиции '.$cparams['given_value'].' руб.', $code);
			
			//создать примечания по позиции в карту расхода
			 
			$_cni->Add(array(
				'note'=>'Создано комиссионное вознаграждение по исходящему счету № '.$bill['code'].', позиция '.SecStr($bpi['name']).', сумма по позиции '.$cparams['given_value'].' руб.',
				'pdate'=>time(),
				'user_id'=>$code,
				'posted_user_id'=>$result['id']
			));
			
			
			
			$bparams=array();
			$bparams['bill_id']=$bill_id;
			$bparams['cash_id']=$code;
			
			$cd=$_cbi->Add($bparams);
			
			
			
			if(!in_array($bill_id, $was_bill_ids)) $log->PutEntry($result['id'], 'создал комиссионное вознаграждение по исходящему счету', NULL, 93, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'], $bill['id']);
			
			$was_bill_ids[]=$bill_id;
		
	 
			
			//журнал, примечания
		}
		
	}
	
	
	
	
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>