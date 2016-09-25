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

require_once('../classes/bill_in_item.php');

require_once('../classes/billgroup.php');

require_once('../classes/bill_in_posgroup.php');
require_once('../classes/bill_in_prepare.php');

require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');
require_once('../classes/bill_in_group.php');

require_once('../classes/billnotesgroup.php');
require_once('../classes/billnotesitem.php');
require_once('../classes/billpositem.php');
require_once('../classes/billpospmitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');

require_once('../classes/user_s_item.php');
require_once('../classes/sectoritem.php');

 
require_once('../classes/posgroupgroup.php');

 

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');
require_once('../classes/acc_in_item.php');

require_once('../classes/bill_view.class.php');

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
function InRestrictedPair($pl_position_id, $out_bill_id, $pairs){
	$res=false;	
	
	foreach($pairs as $k=>$vv){
		$v=explode(';',$vv);
		if(($v[0]==$pl_position_id)&&($v[1]==$out_bill_id)){
			$res=true;
			break;	
		}
	}
	
	return $res;
}

$ret='';

if(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$_bi=new BDetailsItem;
	$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>1));
	
	if($si!==false){
		$rret=array();
		/*foreach($si as $k=>$v){
			if(
			($k=='contract_no')||
			($k=='contract_pdate')||
			($k=='contract_pdate')) continue;
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		$rret[]='"opf_name":"'.htmlspecialchars($opf['name']).'"';
		
		if($bi!==false){
			$rret[]='"bdetails_id_string":" р/с '.addslashes($bi['rs'].', '.$bi['bank']).', '.$bi['city'].'"';
			$rret[]='"bdetails_id":"'.htmlspecialchars($bi['id']).'"';
		}
		
		if($sci!==false){
			$rret[]='"contract_no_string":"'.addslashes($sci['contract_no']).'"';
			$rret[]='"contract_no":"'.addslashes($sci['contract_no']).'"';
			$rret[]='"contract_id":"'.addslashes($sci['id']).'"';
		
			$rret[]='"contract_pdate_string":"'.addslashes($sci['contract_pdate']).'"';
			$rret[]='"contract_pdate":"'.addslashes($sci['contract_pdate']).'"';
			
			
		}
		
		$ret='{'.implode(', ',$rret).'}';*/
		$si['opf_name']=htmlspecialchars($opf['name']);
		$si['bdetails_id_string']=' р/с '.($bi['rs'].', '.$bi['bank']).', '.$bi['city'];
		$si['bdetails_id']=htmlspecialchars($bi['id']);
		$si['contract_no_string']=addslashes($sci['contract_no']);
		$si['contract_no']=($sci['contract_no']);
		$si['contract_id']=($sci['id']);
		$si['contract_pdate_string']=($sci['contract_pdate']);
		$si['contract_pdate']=($sci['contract_pdate']);
		
		
		foreach($si as $k=>$v) $si[$k]=iconv( 'windows-1251', 'utf-8', $v);
		$ret=json_encode($si);
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_suppliers")){
	
	
	//получим список позиций по фильтру
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	
	
	$ret=$_pg->GetItemsForBill('bills/suppliers_list.html',  $dec,true,$all7,$result);
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_bdetails")){
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new BDetailsGroup;
	$arr=$_bd->GetItemsByIdArr($supplier_id,$current_id);
	
	$sm=new SmartyAj;
	$sm->assign('pos',$arr);
	
	$ret=$sm->fetch('bills/bdetails_list.html');

}elseif(isset($_POST['action'])&&($_POST['action']=="load_condetails")){
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new SupContractGroup();
	$arr=$_bd->GetItemsByIdArr($supplier_id, $current_id,  1);
	
	//print_r($arr);
	
	$sm=new SmartyAj;
	$sm->assign('pos2',$arr);
	
	$ret=$sm->fetch('bills/contracts_list.html');
	

	
}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_bdetails")){
	//получим bdetails из списка
	$_si=new BDetailsItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			$rret[]='"'.$k.'":"'.addslashes($v).'"';
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}

}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_contracts")){
	//получим bdetails из списка
	$_si=new SupContractItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			$rret[]='"'.$k.'":"'.addslashes($v).'"';
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}


}elseif(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций к.в. для счета
	
	
	
	
	$except_id=abs((int)$_POST['bill_id']);
	$out_bill_id=abs((int)$_POST['out_bill_id']);
	$_bi1=new BillInItem;
	$bi1=$_bi1->GetItemById($except_id);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$_supplier=new SupplierItem;
	$supplier=$_supplier->GetItemById($supplier_id);
	
	
	
	
	$_out_bill=new BillItem;
	$out_bill=$_out_bill->Getitembyid($out_bill_id);
	
	//$komplekt_id=abs((int)$_POST['komplekt_id']);
	
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	
	
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$already_in_bill[]=array('position_id'=>$valarr[0],'storage_id'=>0/*$valarr[7]*/,'sector_id'=>0/*$valarr[8]*/,'komplekt_ved_id'=>$valarr[9], 'out_bill_id'=>$valarr[14]);	
	}
	
	//var_dump($complex_positions);
	
	 
	$_kpg=new BillInPrepare;
	
	$_mf=new MaxFormer;
	
	$alls=$_kpg->GetItemsByIdArr($out_bill_id, $except_id, false, $already_in_bill);
	
	//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	/*
	echo '<pre>';
	print_r($alls);
	echo '</pre>';
	*/
	
	

	$_se=new SectorItem;
	$_pi=new PosItem;
	
	
	
	$arr=array();
	$joined_positions=array();
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$joined_positions[]=array('position_id'=>$valarr[0],'storage_id'=>0/*$valarr[7]*/,'sector_id'=>0/*$valarr[8]*/,'komplekt_ved_id'=>$valarr[9], 'out_bill_id'=>$valarr[14]);	
		
		//print_r($valarr);
	}
	/*echo '<pre>';
	print_r(($joined_positions));
	echo '</pre>';*/
	foreach($alls as $k=>$v){
		//echo $v['position_id'].'<br>';
		
		//откуда взять sector_id???? iz komplet_ved
		
		if(!in_array(array('position_id'=>$v['position_id'],'storage_id'=>0,'sector_id'=>0,'komplekt_ved_id'=>$v['komplekt_ved_id'], 'out_bill_id'=>(int)$v['out_bill_id']),$joined_positions)){
			$joined_positions[]=array('position_id'=>$v['position_id'],'storage_id'=>0,'sector_id'=>0,'komplekt_ved_id'=>$v['komplekt_ved_id'], 'out_bill_id'=>(int)$v['out_bill_id'] );
		}
		
		//$storage_id; d
	}
	/*echo '<pre>';
	print_r($joined_positions);
	echo '</pre>';*/
	//print_r(count($joined_positions));
	$_kpi=new KomplPosItem;
	foreach($joined_positions as $kk=>$vv){
				  
		 $v=array();
		 
		  
		 //print_r($vv);
		 
		  $in_alls=false;
		  //подгрузка названия и прочих параметров из списка позиций заявки
		  //уч-к, склад, заявка - могут быть изменены потом
		  foreach($alls as $ck=>$cv){
		  		//echo $cv['position_id'].' vs '.$vv['position_id'].'<br />';
				//echo $cv['komplekt_ved_id'].'va '.$vv['komplekt_ved_id'].'<br />';
				if(
				($cv['position_id']==$vv['position_id'])
				&&
				($cv['komplekt_ved_id']==$vv['komplekt_ved_id'])		
				&&
				($cv['out_bill_id']==$vv['out_bill_id'])				
				
				){
					$v=$cv;
					$in_alls=true;
					//echo 'est: '.$v['position_id'].'<br />';	
					break;
				}
		  
		  }
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$vv['position_id'])&&
				($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])&&	
				($cv[14]==$vv['out_bill_id'])	
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  //$ret.=$v['position_id'];
		  //$ret.=$index;
		  
		  //echo $v['position_id'].'<br />';
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  if($v['position_id']===NULL){
				
				$tt=$complex_positions[$index];
				$ta=explode(';',$tt);
				$v['position_id']=  $ta[0];
				
				$_pi=new PosItem;
				$pi=$_pi->GetItemById($v['position_id']);
				$v['position_name']=$pi['name'];
				$_pdi=new PosDimItem;
				$pdi=$_pdi->GetItemById($pi['dimension_id']);  
				
				$v['dimension_id']=$pdi['id'];
				$v['dim_name']=$pdi['name'];
			  }
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  
			  
			  $v['quantity']=$valarr[1];
			  
			  $v['price']=$valarr[3];
			  $v['rub_or_percent']=$valarr[4];
			  $v['plus_or_minus']=$valarr[5];
			  $v['value']=$valarr[6];
			  
			   $v['discount_rub_or_percent']=$valarr[12];
			 // $v['discount_plus_or_minus']=$valarr[13];
			  $v['discount_value']=$valarr[13];
			  
			  $v['has_pm']=$valarr[2];
			  
			  $v['storage_id']=$valarr[7]; //$vv['storage_id'];//$selected_storage_ids[$index];
 
			  if($v['has_pm']){
				 
				  $v['price_pm']=$valarr[10];
			  }else $v['price_pm']=$v['price'];
			  
			  $v['cost']=$v['price']*$v['quantity'];
			  $v['total']=$valarr[11];//$v['price_pm']*$v['quantity'];
		  
			  
			  $v['nds_proc']=$_supplier->FindNDS($supplier_id, $supplier); //NDS;
			  $v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+$_supplier->FindNDS($supplier_id, $supplier))/100)));
			  
			  $v['sector_id']=$valarr[8];
			  $v['komplekt_ved_id']=$valarr[9];
			  
			  $v['out_bill_id']=$valarr[14];
		  }else{
			  //echo 'no no ';
			  
			  $v['quantity']=0;
			  $v['price']=0;
			  $v['rub_or_percent']=0;
			  $v['plus_or_minus']=0;
			  $v['value']=0;
			  
			  $v['discount_rub_or_percent']=0;
			//  $v['discount_plus_or_minus']=0;
			  $v['discount_value']=0;
			  
			  $v['has_pm']=0;
			  
			  $v['price_pm']=0;
			  $v['cost']=0;
			  $v['total']=0;
			  
			  $v['nds_proc']=$_supplier->FindNDS($supplier_id, $supplier);;
			  $v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+$_supplier->FindNDS($supplier_id, $supplier))/100)));
			  
			  
			  $v['storage_id']= $vv['storage_id'];//$storage_id;
 
			  
			  
			    $v['sector_id']=$vv['sector_id']; //$valarr[8];
			  $v['komplekt_ved_id']=$vv['komplekt_ved_id']; //$valarr[9];
			   $v['out_bill_id']=$vv['out_bill_id'];
		  }
		  
		  $v['quantity_confirmed']=$_mf->MaxInBill($out_bill_id,$v['position_id'],NULL,NULL,$v['komplekt_ved_id']);//$v['quantity_confirmed'];
		  
		  $v['max_quantity']=$_mf->MaxForIncomingBill($out_bill_id,$v['position_id'],$v['komplekt_ved_id'], $except_id);  //>MaxForBill($v['komplekt_ved_id'], $v['position_id'],$except_id);  // СДЕЛАТЬ ПОЗЖЕ!!!
		  $v['in_rasp']=$_mf->MaxInInAcc($except_id, $v['position_id'], NULL, NULL,NULL,NULL,$v['komplekt_ved_id']);  //>
		  
		  
		  
		  $se=$_se->GetItemById($vv['sector_id']);
		  $v['sector_name']=$se['name'];
		  if($vv['komplekt_ved_id']!=0) $v['komplekt_ved_name']='Заявка № '.$vv['komplekt_ved_id'];
		  else $v['komplekt_ved_name']='-';
		  
		  $oub=$_out_bill->Getitembyid($v['out_bill_id']);
		  if($oub===false) $v['out_bill_code']='';
		  else $v['out_bill_code']=$oub['code'];
		  
		  
		 
		  
		  
		  $v['hash']=md5($v['position_id'].'_'.$v['storage_id'].'_'.$v['sector_id'].'_'.$v['komplekt_ved_id'].'_'.$v['out_bill_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 $sm->assign('BILLUP',BILLUP);
	
	$sm->assign('pospos',$arr);
	//$sm->assign('komplekt_id',$komplekt_id);
	
	
	if($bi1['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
	
	
	
	$sm->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129));
	
	
	
	//утверждены ли цены
	$can_mod_object_only=false;
	if(isset($bi1['is_confirmed_price'])&&($bi1['is_confirmed_price']==1)&&($bi1['is_confirmed_shipping']==0)){
		$can_mod_object_only=true;
	}
	$sm->assign('can_mod_object_only',$can_mod_object_only);
	
	
	$can_mod_pm_only=false;
	if(isset($bi1['is_confirmed_price'])&&($bi1['is_confirmed_price']==1)&&($bi1['is_confirmed_shipping']==1)&&($_bi1->HasShsorAccs($bi1['id']))&&
		$au->user_rights->CheckAccess('w',637)){
		$can_mod_pm_only=true;
	}
	$sm->assign('can_mod_pm_only',$can_mod_pm_only);
	
	
	$sm->assign('out_bill_id', $out_bill_id);
	
	$sm->assign('NDS', $_supplier->FindNDS($supplier_id, $supplier));
	
	//$sm->assign('bill', $bi1);
	if(isset($bi1['is_leading'])) $is_leading=$bi1['is_leading'];
	else $is_leading=-1; 
	$sm->assign('is_leading', $is_leading);
	
	
	$ret.=$sm->fetch("bills_in/positions_edit_set.html");
	
	/*$ret.= mysqlSet::$inst_count.' запросов к БД на выборку<br />';
$ret.=  nonSet::$inst_count.' запросов на обновление БД<br />';
$ret.=  mysqlSet::$inst_count+nonSet::$inst_count.' всего запросов к БД<br />';


$ret.=  (time()-$_big_time_marker_begin).' сек. <br />';*/
	
	

	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$id=abs((int)$_POST['id']);
	
	$out_bill_id=abs((int)$_POST['out_bill_id']);
	
	$komplekt_id=abs((int)$_POST['komplekt_id']);
	
	$complex_positions=$_POST['complex_positions'];
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$_supplier=new SupplierItem;
	$supplier=$_supplier->GetItemById($supplier_id);
	
	
	
	$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;
 
	$_se=new SectorItem;
	
	$_out_bill=new BillItem;
	
	//var_dump($complex_positions);
	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		if($v[1]<=0) continue;
		$position=$_position->GetItemById($v[0]);
		if($position===false) continue;
		
		$f['quantity']=$v[1];
		$f['id']=$v[0];
		
		$f['position_name']=$position['name'];
		$f['dimension_id']=$position['dimension_id'];
		
		$dim=$_dim->GetItemById($f['dimension_id']);
		$f['dim_name']=$dim['name'];
		
		$f['price']=$v[3];
		
		//+/-
		$f['has_pm']=$v[2];
		$f['rub_or_percent']=$v[4];
		$f['plus_or_minus']=$v[5];
		$f['value']=$v[6];
		$f['discount_rub_or_percent']=$v[12];
		//$f['discount_plus_or_minus']=$v[13];
		$f['discount_value']=$v[13];
		
		
		$f['storage_id']=$v[7];
	//	$si=$_si->getItemById($f['storage_id']);
		//$f['storage_name']=$si['name'];
		
		$f['sector_id']=$v[8];
		$se=$_se->getItemById($f['sector_id']);
		$f['sector_name']=$se['name'];
		
		$f['komplekt_ved_id']=$v[9];
		
		if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
		else $f['komplekt_ved_name']='-';
		
		
		$f['out_bill_id']=$v[14];
		$out_bill=$_out_bill->getitembyid($v[14]);
		if($f['out_bill_id']!=0) $f['out_bill_code']=$out_bill['code'];
		else $f['out_bill_code']='';
		
		
		//cena +-
		if($v[2]==1){
		
			$f['price_pm']=$v[10];
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=round($f['price']*$f['quantity'],2);;
		
		
		//vsego
		$f['total']=$v[11]; //round($f['price_pm']*$f['quantity'],2);
		
		
		
		if(($f['has_pm']==1)&&($f['rub_or_percent']==1)&&($f['value']>0)){
				$f['value_from_percent']=((float)$f['price_pm']-(float)$f['price']);
			}
			
			if(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==1)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=round(($f['price_pm']-$f['price'])*$f['discount_value']/100,2);
			}
		
		
		/*
		$f['quantity_confirmed']=$_mf->MaxInKomplekt($komplekt_id, $f['id']); //!!!!!! SDLEAT POZJE
		$f['max_quantity']=$_mf->MaxForBill($komplekt_id, $f['id']); 
		$f['in_rasp']=$_mf->MaxInAcc($id,  $f['id'], NULL, NULL,NULL,NULL,$f['komplekt_ved_id']); 
		*/
		$f['quantity_confirmed']=$_mf->MaxInBill($f['out_bill_id'],$f['id'],NULL,NULL,$f['komplekt_ved_id']); //сколько в род. исход. счете вообще?
		$f['max_quantity']=$_mf->MaxForIncomingBill($f['out_bill_id'],$f['id'],$f['komplekt_ved_id'], $id);
		$f['in_rasp']=$_mf->MaxInInAcc($id,  $f['id'], NULL, NULL,NULL,NULL,$f['komplekt_ved_id']); 
		
		
		$f['nds_proc']=$_supplier->FindNDS($supplier_id, $supplier);
		$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+$_supplier->FindNDS($supplier_id, $supplier))/100)));
		
		$f['hash']=md5($f['id'].'_'.$f['storage_id'].'_'.$f['sector_id'].'_'.$f['komplekt_ved_id'].'_'.$f['out_bill_id']);
		
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$_bill=new BillInItem;
	$bill=$_bill->getItemById($id);
	$sm->assign('bill',$bill);
	
	
	if($bill['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
	
	
	$sm->assign('NDS', $_supplier->FindNDS($supplier_id, $supplier));
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',609)); 
	$sm->assign('can_delete_positions',$au->user_rights->CheckAccess('w',611)); 
		
	$sm->assign('BILLUP',BILLUP);
	$ret=$sm->fetch("bills_in/positions_on_page_set.html");
	
	
}

elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		
	$supplier_id=abs((int)$_POST['supplier_id']);
	$_supplier=new SupplierItem;
	$supplier=$_supplier->GetItemById($supplier_id);
	
	
	$alls=array();
	$complex_positions=$_POST['complex_positions'];
	
	foreach($complex_positions as $k=>$valarr){
		$f=array();	
		
		$v=explode(';',$valarr);
		
		$f['quantity']=$v[1];
		$f['id']=$v[0];
		

		
		$f['price']=$v[3];
		
		//+/-
		$f['has_pm']=$v[2];
		$f['rub_or_percent']=$v[4];
		$f['plus_or_minus']=$v[5];
		$f['value']=$v[6];
		
		
		//cena +-
		if($f['has_pm']==1){
			/*$slag=0;
			if($f['rub_or_percent']==0){
				$slag=$f['value'];
			}else{
				$slag=$f['price']*$f['value']/100.0;
			}
			
			if($f['plus_or_minus']==1) $slag=-1.0*$slag;
			$f['price_pm']=$f['price']+$slag;*/
			$f['price_pm']=$v[10];
			
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		$f['total']=$v[11];//$f['price_pm']*$f['quantity'];
		
		
		$alls[]=$f;
		
		/*echo '<pre>';
		print_r($f);
		echo '</pre>';*/
		
	}
	
	
	$_bpf=new BillPosPMFormer;
	if($_POST['action']=="calc_new_total") $ret=$_bpf->CalcCost($alls);
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls,true,$supplier_id);
	
}

//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new BillNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',615), $au->user_rights->CheckAccess('w',616), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',614));
	
	
	$ret=$sm->fetch('bills_in/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',614)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new BillNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по входящему счету', NULL,614, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',614)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BillNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по входящему счету', NULL,614,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',614)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BillNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по входящему счету', NULL,614,NULL,NULL,$user_id);
	
}
//работа с датами
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_ethalon_pdate_payment_contract")){
	
	$_si=new SupplierItem;
	$_bd=new BillDates;
	
	$contract_id=abs((int)$_POST['contract_id']);
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($contract_id);
	
	$supplier=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	if($supplier!==false){
		$ethalon=$_bd->FindEthalon(datefromdmy($_POST['pdate_shipping_plan']),$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
		
		$ret=$ethalon;  //date("d.m.Y",$ethalon);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_ethalon_full_pdate_payment_contract")){
	
	$_si=new SupplierItem;
	$_bd=new BillDates;
	
	$contract_id=abs((int)$_POST['contract_id']);
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($contract_id);
	
	$supplier=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	if($supplier!==false){
		$ethalon=$_bd->FindEthalon(datefromdmy($_POST['pdate_shipping_plan']),$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
		
		$ret=date("d.m.Y",$ethalon);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="compare_pdate_payment")){
	$ethalon_pdate_payment_contract=abs((int)$_POST['ethalon_pdate_payment_contract']);
	$pdate_payment_contract=datefromdmy($_POST['pdate_payment_contract']);
	
	$contract_id=abs((int)$_POST['contract_id']);
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemById($contract_id);
	
	if($pdate_payment_contract<$ethalon_pdate_payment_contract){
		$ret="Вы выбрали дату оплаты по договору раньше отсрочки по договору с данным поставщиком. \nДата отсрочки: ".date('d.m.Y',$ethalon_pdate_payment_contract);	
	}elseif($pdate_payment_contract>$ethalon_pdate_payment_contract){
		$ret="Вы выбрали дату оплаты по договору позднее отсрочки по договору с данным поставщиком. \nДата отсрочки: ".date('d.m.Y',$ethalon_pdate_payment_contract);
	}else $ret="";
	
}
//подсветка утверждений карты
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_shipping_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	$flag_to_payments=abs((int)$_POST['flag_to_payments']);
	
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_price_pdate']==0) $trust['confirm_price_pdate']='-';
	else $trust['confirm_price_pdate']=date("d.m.Y H:i:s",$trust['confirm_price_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_price_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_price']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',622))||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==20)||($trust['status_id']==21)){
				$_ti->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение цен',NULL,622, NULL, NULL,$bill_id);
				$_ti->FreeBindedPayments($bill_id);
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',620)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил цены',NULL,620, NULL, NULL,$bill_id);	
				
				if($flag_to_payments==1) $_ti->BindPayments($bill_id,$result['org_id']);		
			}
		}else{
			//do nothing
		}
	}
	
	
	
	$acg=new BillInGroup;
	
	$acg->SetAuthResult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='bills_in/bills_list.html';
	else {
		$template='bills_in/bills_list_komplekt.html';
		$acg->prefix='_in_bill';
	}
	
	
	
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret=$acg->ShowPos(
		$template,	//0
		$dec,	//1
		0,	//2
		100, //3
		$au->user_rights->CheckAccess('w',128), //4
		$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), //5
		$au->user_rights->CheckAccess('w',626), //6
		'', //7
		$au->user_rights->CheckAccess('w',620),//8
		$au->user_rights->CheckAccess('w',96),	//9
		false,	//10
		true,	//11
		$au->user_rights->CheckAccess('w',627),	//12
		NULL,	//13
		NULL,	//14
		$au->user_rights->CheckAccess('w',621),	//15
		$au->user_rights->CheckAccess('w',622), //16
		$au->user_rights->CheckAccess('w',623), //17
		$bills_list, //18
		$au->user_rights->CheckAccess('w',625),	//19
		false, //20
		false, //21
		NULL, //22
		$au->user_rights->CheckAccess('w',865) //23
	);
	
		
}elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_price")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_price_pdate']==0) $trust['confirm_price_pdate']='-';
	else $trust['confirm_price_pdate']=date("d.m.Y H:i:s",$trust['confirm_price_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_price_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_price_pdate']==0) $trust['confirm_price_pdate']='-';
	else $trust['confirm_price_pdate']=date("d.m.Y H:i:s",$trust['confirm_price_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_price_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	
	$sm=new SmartyAj;
	
	
	$sm->assign('can_confirm_price', $au->user_rights->CheckAccess('w',620));
	$sm->assign('can_super_confirm_price', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill_in.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('bills_in/toggle_confirm_price.html');
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_shipping_pdate']==0) $trust['confirm_shipping_pdate']='-';
	else $trust['confirm_shipping_pdate']=date("d.m.Y H:i:s",$trust['confirm_shipping_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_shipping_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_shipping']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',623))||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==20)||($trust['status_id']==21)){
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_shipping'=>0, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение приемки',NULL,623, NULL, NULL,$bill_id);
				
			}
				
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',621)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==20)||($trust['status_id']==21)){
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_shipping'=>1, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил приемку',NULL,621, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			}
		}else{
			//do nothing
		}
	}
	
	
		
	$acg=new BillInGroup;
	
	$acg->SetAuthResult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='bills_in/bills_list.html';
	else {
		$template='bills_in/bills_list_komplekt.html';
		$acg->prefix='_in_bill';
	}
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	//$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), $au->user_rights->CheckAccess('w',626), '', $au->user_rights->CheckAccess('w',620),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',627),NULL,NULL,$au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623), $bills_list, $au->user_rights->CheckAccess('w',625));
	
	$ret=$acg->ShowPos(
		$template,	//0
		$dec,	//1
		0,	//2
		100, //3
		$au->user_rights->CheckAccess('w',128), //4
		$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), //5
		$au->user_rights->CheckAccess('w',626), //6
		'', //7
		$au->user_rights->CheckAccess('w',620),//8
		$au->user_rights->CheckAccess('w',96),	//9
		false,	//10
		true,	//11
		$au->user_rights->CheckAccess('w',627),	//12
		NULL,	//13
		NULL,	//14
		$au->user_rights->CheckAccess('w',621),	//15
		$au->user_rights->CheckAccess('w',622), //16
		$au->user_rights->CheckAccess('w',623), //17
		$bills_list, //18
		$au->user_rights->CheckAccess('w',625),	//19
		false, //20
		false, //21
		NULL, //22
		$au->user_rights->CheckAccess('w',865) //23
	);
	
	
	
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',626)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование входящего счета',NULL,626,NULL,'входящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);	
			
			//внести примечание
			$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',627)){
			$_ti->Edit($id,array('status_id'=>1),false,$result);
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление входящего счета',NULL,627,NULL,'входящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));		
			
		}
		
	}
	
	if($from_card==0){
	  $shorter=abs((int)$_POST['shorter']);
	  if($shorter==0) $template='bills_in/bills_list.html';
	  else $template='bills_in/bills_list_komplekt.html';
	  
	  
	  $acg=new BillInGroup;
	  
	  $acg->SetAuthResult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	  
	  
	  $ret=$acg->ShowPos(
		$template,	//0
		$dec,	//1
		0,	//2
		100, //3
		$au->user_rights->CheckAccess('w',128), //4
		$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), //5
		$au->user_rights->CheckAccess('w',626), //6
		'', //7
		$au->user_rights->CheckAccess('w',620),//8
		$au->user_rights->CheckAccess('w',96),	//9
		false,	//10
		true,	//11
		$au->user_rights->CheckAccess('w',627),	//12
		NULL,	//13
		NULL,	//14
		$au->user_rights->CheckAccess('w',621),	//15
		$au->user_rights->CheckAccess('w',622), //16
		$au->user_rights->CheckAccess('w',623), //17
		$bills_list, //18
		$au->user_rights->CheckAccess('w',625),	//19
		false, //20
		false, //21
		NULL, //22
		$au->user_rights->CheckAccess('w',865) //23
	);
	
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',626);
		if(!$au->user_rights->CheckAccess('w',626)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',627);
			if(!$au->user_rights->CheckAccess('w',627)) $reason='недостаточно прав для данной операции';
		
		
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('bills_in/toggle_annul_card.html');		
	}
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_shipping_pdate']==0) $trust['confirm_shipping_pdate']='-';
	else $trust['confirm_shipping_pdate']=date("d.m.Y H:i:s",$trust['confirm_shipping_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_shipping_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	
	
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_shipping_pdate']==0) $trust['confirm_shipping_pdate']='-';
	else $trust['confirm_shipping_pdate']=date("d.m.Y H:i:s",$trust['confirm_shipping_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_shipping_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	
	$sm=new SmartyAj;
	
	
	$sm->assign('can_confirm_shipping', $au->user_rights->CheckAccess('w',620));
	$sm->assign('can_super_confirm_shipping', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill_in.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('bills_in/toggle_confirm_ship.html');
		
}elseif(isset($_POST['action'])&&($_POST['action']=="find_sh_pos")){
	//dostup
	$_kr=new BillReports;
	
	//	//link_in_sh("%{$pospos[pospossec].position_id}%", "%{$pospos[pospossec].pl_position_id}%", "%{$pospos[pospossec].pl_discount_id}%", "%{$pospos[pospossec].pl_discount_value}%", "%{$pospos[pospossec].pl_discount_rub_or_percent}%");
	
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$ret=$_kr->InAcc($id, $bill_id,'bills_in/in_accs.html',$result['org_id'],true, $storage_id, $komplekt_ved_id);
	
	
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="draw_positions")){
	$id=abs((int)$_POST['id']);
	
	$_bill=new BillInItem;
	
	$bill=$_bill->GetItemById($id);
	
	//bills_in/position_actions.html" bill=$bill action=1}%
	$sm=new SmartyAj;
	
	$sm->assign('filename','bill_in.php');
	
	$_bpg=new BillInPosGroup;
	$bpg=$_bpg->GetItemsByIdArr($bill['id']);
	//print_r($bpg);
	$sm->assign('positions',$bpg);
	$sm->assign('has_positions',true);
	$_bpf=new BillPosPMFormer;
	

	
	$total_cost=$_bpf->CalcCost($bpg);
	$total_nds=$_bpf->CalcNDS($bpg);
	$sm->assign('total_cost',$total_cost);
	$sm->assign('total_nds',$total_nds);
	
	$sm->assign('action',1);
	$sm->assign('bill',$bill);
	
	
	
	
	
	$ret=$sm->fetch('bills_in/position_actions.html');
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',624)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillInItem;
		
		$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_scan_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',624)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillInItem;
		
		$_sh->ScanEq($id,$args,$output);
		
		if(!isset($_POST['not_cut_html'])) $output=strip_tags($output);
		
		
		$ret=$output;
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
 
}
elseif(isset($_POST['action'])&&($_POST['action']=="togglemass_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',624)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillInItem;
		
		/*$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';*/
		
		foreach($args as $k=>$arg){
		
			$_sh->DoEq($id,array($arg),$output);
		
		}
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="togglemass_scan_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',624)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillInItem;
		
		/*$_sh->ScanEq($id,$args,$output);
		
		if(!isset($_POST['not_cut_html'])) $output=strip_tags($output);
		
		
		$ret=$output;*/
		
		$alls=array();
		
		 
		$_pos=new PosItem;
		foreach($args as $k=>$arg){
			
			$eq_items=array();
			$eq_items=$_sh->ScanEq($id,array( $arg),$output, NULL, false,"");
		
			if(!isset($_POST['not_cut_html'])) $output=strip_tags($output);
			
			
			foreach($eq_items as $kk=>$eq_item){
				$pos=$_pos->GetItemById($eq_item['position_id']);
				
				$alls[]=array(
					'code'=>$eq_item['position_id'],
					'name'=>$pos['name'],
					'delta'=>$eq_item['delta'],
					'quantity'=>$eq_item['quantity'],
					'comments'=>$output
				);
			}
		}
		$sm=new SmartyAj;
		
		$sm->assign('items', $alls);
			
		$ret=$sm->fetch('komplekt/scan_eq.html');	
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_bigger_price")){
		$id=abs((int)$_POST['id']);
		
	
		$positions=$_POST['positions'];
		if(($positions===NULL)||(count($positions)==0)) $checked_positions=NULL;
		else{
			$checked_positions=array();
			// komplekt_ved_id position_id  out_bill_id price_pm
			foreach($positions as $k=>$v){
				$valarr=explode(';', $v);
				$arr=array(
					'komplekt_ved_id'=>$valarr[0],
					'position_id'=>$valarr[1],
					'out_bill_id'=>$valarr[2],
					'price_pm'=>$valarr[3]
				);
				
				$checked_positions[]=$arr;
			}
		}
		
		
		$_ki=new BillInItem;
		
		
		if(!$_ki->CanConfirmByPricePositions($id,$checked_positions, $rss55)){
			 $ret=$rss55; //>DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
			// echo $rss5;
		}
		else $ret=0;
		 
		
		//если ноль - то все хорошо
	
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="update_discount_given")){
	//проверить, есть ли заявки с таким номером для такого уч.
		//header('Content-type: text/html; charset=windows-1251');
		
		$bill_id=abs((int)$_POST['bill_id']);
		$table_id=abs((int)$_POST['table_id']);
		
		$discount_given=(float)$_POST['discount_given'];
		
		if($au->user_rights->CheckAccess('w',629)||$au->user_rights->CheckAccess('w',363)){
			$_ki=new BillInItem;
			$bill=$_ki->GetItemById($bill_id);
			
			if($bill['status_id']==10){
			  $_bpi=new BillPosItem;
			  $_bpm=new BillPosPMItem;
			  
			  $bpi=$_bpi->GetItemById($table_id);
			  if($bpi!==false){
				  $bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$bpi['id']));
				  
				  if($bpm!==false){
					  $_bpm->Edit($bpm['id'], array('discount_given'=>$discount_given,
					  	'discount_given_pdate'=>time(),
						'discount_given_user_id'=>$result['id']
					  
					  ));	
					  
					  //запись в журнал по счету
					  $log->PutEntry($result['id'],'задал полученный +/- позиции входящего счета', NULL,629, NULL,'позиция '.SecStr($bpi['name']).', старая сумма полученного +/- '.$bpm['discount_given'].' руб. '.', новая сумма полученного +/- '.$discount_given.' руб.',$bill_id);
				  }
			  }
			  
			  $sm=new SmartyAj;
			  
			  $item=array(
			  	'manager_name'=>$result['name_s'],
				'manager_login'=>$result['login'],
				'discount_given_pdate'=>date('d.m.Y H:i:s')
			  );
			  $sm->assign('item',$item);
			  
			  $ret=$sm->fetch('bills_in/positions_pm_saver.html');
			}else{
				$ret='Статус данного счета не позволяет вносить суммы выданных +/-. Для работы с выданными +/- необходимо, чтобы статус счета был "Выполнен".';	
			}
		}else{
			$ret='У Вас недостаточно прав для даннго действия.';
		}
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="is_in_buh_save")){
	
		//header('Content-type: text/html; charset=windows-1251');
		
		$id=abs((int)$_POST['id']);
		$state=abs((int)$_POST['state']);
		$_bi=new BillInItem;
		
		$bill=$_bi->GetItemById($id);
		
		$can=$_bi->CanIsInBuh($id, $rss22, $bill, $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635));
		
		if($can){
			
			
			if($state==1){
				$_bi->Edit($id, array(
					'is_in_buh'=>$state,
					'in_buh_pdate'=>time(),
					'user_in_buh_id'=>$result['id']
				));	
				
				 $log->PutEntry($result['id'],SecStr('установил флаг "счет в бухгалтерии"'), NULL,634, NULL,'счет № '.$bill['code'],$id);
			}else{
				
				$_bi->Edit($id, array(
					'is_in_buh'=>$state,
					'in_buh_pdate'=>0,
					'user_in_buh_id'=>0
				));
				
				$log->PutEntry($result['id'],SecStr('снял флаг "счет в бухгалтерии"'), NULL,635, NULL,'счет № '.$bill['code'],$id);
			}
		}
		
		$ret='';
}elseif(isset($_POST['action'])&&($_POST['action']=="mass_is_in_buh_update")){
	
		//header('Content-type: text/html; charset=windows-1251');
		
		$id=abs((int)$_POST['id']);
		
		$_bi=new BillInItem;
		
		$marked_as_not_in=$_POST['marked_as_not_in'];
		$marked_as_in=$_POST['marked_as_in'];
		
		if(is_array($marked_as_not_in)) foreach($marked_as_not_in as $k=>$v){
			$bill=$_bi->GetItemById($v);
		
			$can=$_bi->CanIsInBuh($v, $rss22, $bill, $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635));
			
			if($can){
				$_bi->Edit($v, array(
					'is_in_buh'=>0,
					'in_buh_pdate'=>0,
					'user_in_buh_id'=>0
				));
				
				$log->PutEntry($result['id'],SecStr('снял флаг "счет в бухгалтерии"'), NULL,634, NULL,'счет № '.$bill['code'],$v);	
			}
			
		}
		
		
		if(is_array($marked_as_in)) foreach($marked_as_in as $k=>$v){
			$bill=$_bi->GetItemById($v);
		
			$can=$_bi->CanIsInBuh($v, $rss22, $bill, $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635));
			
			if($can){
				$_bi->Edit($v, array(
					'is_in_buh'=>1,
					'in_buh_pdate'=>time(),
					'user_in_buh_id'=>$result['id']
				));	
				
				 $log->PutEntry($result['id'],SecStr('установил флаг "счет в бухгалтерии"'), NULL,634, NULL,'счет № '.$bill['code'],$v);
			}
			
		}
		
		
		
		$ret='';
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_in_buh_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_changed_accs")){
	//формируем сообщение об изменении сумм связ поступлений
	$message='';
		
	$id=abs((int)$_POST['id']);
	
	$bill_id=abs((int)$_POST['bill_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	
	$changed_positions=$_POST['changed_positions'];
	
	//$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;
	//$_si=new StorageItem;
	$_se=new SectorItem;
	$pairs1=array(); $pairs2=array(); $changed_totals=array();
	
	$_ai=new AccItem;
	
	
	if(count($changed_positions)>0){
	
		foreach($changed_positions as $k=>$kv){
			$v=explode(';',$kv);
			
			$pairs1[]=' (bill_id="'.$bill_id.'"  ) ';
			$pairs2[]=' (position_id="'.$v[0].'" and komplekt_ved_id="'.$v[2].'") ';
			
			$changed_totals[]=array(
				'position_id'=>$v[0],
				'komplekt_ved_id'=>$v[2],
				'price_pm'=>$v[3] 
			);
		}
		//print_r($changed_totals);
		
		$sql='select id, given_pdate from acceptance where ('.implode(' or ', $pairs1).') and id in(select distinct acceptance_id from acceptance_position where '.implode(' or ',$pairs2).' )';
		
		//$message=$sql;
		$set=new mysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$old_summ=$_ai->CalcCost($f['id']);
			
			//как рассчитывать сумму измененного поступления????
			$new_summ=round($_ai->CalcCost($f['id'], NULL,$changed_totals),2);
			
			
			$message.='№ '.$f['id'].' от '.date('d.m.Y',$f['given_pdate']).' с '.$old_summ.' руб. на '.$new_summ.' руб.'."\n";
		}
		if(strlen($message)>0) $message="Изменится сумма поступлений: \n".$message."\n";
	}
	
	$ret=$message;
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_pos_in")){

//получим список позиций по фильтру
	$_pg=new PlPosGroupForBill;
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	//$except_id=abs((int)$_POST['except_id']);
	//$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	$except_ids=$_POST['except_ids'];
	if(count($except_ids)>0){
		$dec->AddEntry(new SqlEntry('pl.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_ids));		
		
	}
	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	
	//if($group_id>0) $dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('p.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('p.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('p.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('p.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('p.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('p.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('p.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('p.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('p.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	$_pg->itemsname='pospos';
	
	//нужен другой метод, который бы возвращал данные в нужном формате
	
	
	$_pg->ShowPos('bills_in/position_edit_finded.html', $dec,0,1000,false,false,true);
	
	$items=$_pg->items;
	
	//добавим стоимости, кол-во
	foreach($items as $k=>$v){
		
		$items[$k]['quantity']=0;	
		$items[$k]['price_pm']=$items[$k]['price_f'];
		$items[$k]['cost']=0;
		$items[$k]['total']=0;
		$items[$k]['nds_proc']=NDS;
		$items[$k]['nds_summ']=0;
		$items[$k]['nds_summ']=0;
		$items[$k]['value']=0;
		$items[$k]['discount_value']=0;
		$items[$k]['out_bill_id']=0;
		
		$items[$k]['in_rasp']=0;
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent'].'_'.$items[$k]['out_bill_id']);
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
	$ret=$sm->fetch('bills_in/position_edit_finded.html');
	
	
	
}

//список доступных позиций по исх счетам
elseif(isset($_POST['action'])&&($_POST['action']=="find_pos_other_bills")){
	$_pg=new BillPosGroupForBill;
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	//$except_id=abs((int)$_POST['except_id']);
	//$dec->AddEntry(new SqlEntry('p.id',$except_id, SqlEntry::NE));
	
	
	$dec->AddEntry(new SqlEntry('b.org_id',$result['org_id'], SqlEntry::E));
	$dec->AddEntry(new SqlEntry('b.inventory_id',0, SqlEntry::E));
	
	$except_ids=$_POST['except_pairs'];
	

	
	if(strlen($name)>0) $dec->AddEntry(new SqlEntry('p.name',$name, SqlEntry::LIKE));
	

	if($group_id>0) {
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$dec->AddEntry(new SqlEntry('cat.group_id',$group_id, SqlEntry::E));
		
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr($group_id);
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec->AddEntry(new SqlEntry('cat.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	if(abs((int)$_POST['dimension_id'])>0) $dec->AddEntry(new SqlEntry('cat.dimension_id',abs((int)$_POST['dimension_id']), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['length'])))>0) $dec->AddEntry(new SqlEntry('cat.length',SecStr(iconv("utf-8","windows-1251",$_POST['length'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['width'])))>0) $dec->AddEntry(new SqlEntry('cat.width',SecStr(iconv("utf-8","windows-1251",$_POST['width'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['height'])))>0) $dec->AddEntry(new SqlEntry('cat.height',SecStr(iconv("utf-8","windows-1251",$_POST['height'])), SqlEntry::E));
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])))>0) $dec->AddEntry(new SqlEntry('cat.diametr',SecStr(iconv("utf-8","windows-1251",$_POST['diametr'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['weight'])))>0) $dec->AddEntry(new SqlEntry('cat.weight',SecStr(iconv("utf-8","windows-1251",$_POST['weight'])), SqlEntry::E));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['volume'])))>0) $dec->AddEntry(new SqlEntry('cat.volume',SecStr(iconv("utf-8","windows-1251",$_POST['volume'])), SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['out_bill_code'])))>0) $dec->AddEntry(new SqlEntry('b.code',SecStr(iconv("utf-8","windows-1251",$_POST['out_bill_code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['supplier_name'])))>0){
		$suppliers=explode(';', SecStr(iconv("utf-8","windows-1251",$_POST['supplier_name'])));
		$our_suppliers=array();
		foreach($suppliers as $k=>$v) if(strlen(trim($v))>0) $our_suppliers[]='"'.trim($v).'"';
		if(count($our_suppliers)) $dec->AddEntry(new SqlEntry('sup.full_name', NULL, SqlEntry::IN_VALUES, NULL,$our_suppliers));		
	}
	
	$_pg->itemsname='pospos';
	
	
	$dec->AddEntry(new SqlOrdEntry('position_name',SqlOrdEntry::ASC));
	
	$dec->AddEntry(new SqlOrdEntry('out_bill_code',SqlOrdEntry::DESC));
	
	
	$_pg->ShowPos('bills_in/position_edit_finded.html',  $dec, true);
	
	$items=$_pg->items;
	
	//добавим стоимости, кол-во
	$items2=array();
	foreach($items as $k=>$v){
		
		if(InRestrictedPair($v['pl_position_id'], $v['out_bill_id'], $except_ids)) continue;
		
		//$items[$k]['quantity']=0;	
		$items[$k]['price_f']=0; 
		$items[$k]['price']=0; 
		$items[$k]['price_pm']=0; 
		$items[$k]['has_pm']=0;
		$items[$k]['cost']=0;
		$items[$k]['total']=0;
		$items[$k]['nds_proc']=NDS;
		$items[$k]['nds_summ']=0;
		$items[$k]['nds_summ']=0;
		$items[$k]['value']=0;
		$items[$k]['discount_value']=0;
		//$items[$k]['out_bill_id']=$v['bill_id'];
		
		$items[$k]['in_rasp']=0;
		
		$items[$k]['hash']=md5($items[$k]['pl_position_id'].'_'.$items[$k]['position_id'].'_'.$items[$k]['pl_discount_id'].'_'.$items[$k]['pl_discount_value'].'_'.$items[$k]['pl_discount_rub_or_percent'].'_'.$items[$k]['out_bill_id']);
		
		$items2[]=$items[$k];
	}
	
	//print_r($items);
	
	$sm=new SmartyAj;
	
	$sm->assign('pospos', $items2);
	$sm->assign('by_bill',true);
	$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',612));
		
	$ret=$sm->fetch('bills_in/position_edit_finded.html');
	
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Bill_In_ViewGroup;
	$_view=new Bill_In_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new Bill_In_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>