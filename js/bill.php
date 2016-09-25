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

require_once('../classes/sectoritem.php');

require_once('../classes/billpospmformer.php');
require_once('../classes/billposgroup.php');

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


require_once('../classes/posgroupgroup.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');

require_once('../classes/user_s_group.php');
require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');


require_once('../classes/komplitem.php');

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


$ret='';
if(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$_bi=new BDetailsItem;
	$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>0));
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
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
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}elseif(isset($_POST['action'])&&(($_POST['action']=="find_suppliers")||($_POST['action']=="find_suppliers_ship"))){
	
	
	//получим список позиций по фильтру
	$_pg=new SuppliersGroup;
	
	$dec=new DBDecorator;
	
	//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	
	if($_POST['action']=="find_suppliers_ship") $ret=$_pg->GetItemsForBill('bills/ship_suppliers_list.html',  $dec,true,$all7,$result);
	else $ret=$_pg->GetItemsForBill('bills/suppliers_list.html',  $dec,true,$all7,$result);
	

	
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
	$arr=$_bd->GetItemsByIdArr($supplier_id, $current_id, 0);
	
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
	
	
}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_komplekt")){
	//получим komplekt из списка
	$_si=new KomplItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	$rret=array();
	if($si!==false){
		
		foreach($si as $k=>$v){
			$rret[]='"'.$k.'":"'.htmlspecialchars($v).'"';
		}
		$_sto=new StorageItem;
		$sto=$_sto->GetItemById($si['storage_id']);
		if($sto!==false) $rret[]='"storage_name":"'.htmlspecialchars($sto['name']).'"';
		
	}
	
	
	
	$ret='{'.implode(', ',$rret).'}';
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_sectors")){
	$storage_id=abs((int)$_POST['storage_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new StorageSector();
	$arr=$_bd->GetBookCategsArr($storage_id, 1);
	 //GetItemsByIdArr($supplier_id,$current_id);
	
	$do_limit_sector=$au->FltSector($result);
	
	$ret='';
	if($current_id==0) $ret.='<option value="0" selected="selected">-выберите-</option>';
	else $ret.='<option value="0">-выберите-</option>';
	foreach($arr as $k=>$v){
		if($do_limit_sector&&($v['id']!=$result['sector_id'])) continue;
		
		if($current_id==$v['id']) $ret.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';	
		else $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';	
	}
	
	
	

}elseif(isset($_POST['action'])&&($_POST['action']=="load_storages")){
	$sector_id=abs((int)$_POST['sector_id']);
	$current_id=abs((int)$_POST['current_id']);
	
	$_bd=new StorageSector();
	$arr=$_bd->GetCategsBookArr($sector_id, 1);
	 //GetItemsByIdArr($supplier_id,$current_id);
	
	
	
	$ret='';
	if($current_id==0) $ret.='<option value="0" selected="selected">-выберите-</option>';
	else $ret.='<option value="0">-выберите-</option>';
	foreach($arr as $k=>$v){
		if($current_id==$v['id']) $ret.='<option value="'.$v['id'].'" selected="selected">'.$v['name'].'</option>';	
		else $ret.='<option value="'.$v['id'].'">'.$v['name'].'</option>';	
	}
	
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_positions")){
	//вывод позиций к.в. для счета
	
	$except_id=abs((int)$_POST['bill_id']);
	$_bi1=new BillItem;
	$bi1=$_bi1->GetItemById($except_id);
	
	
	$komplekt_id=abs((int)$_POST['komplekt_id']);
	
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	
	
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$already_in_bill[]=array('position_id'=>$valarr[0],'storage_id'=>0/*$valarr[7]*/,'sector_id'=>0/*$valarr[8]*/,'komplekt_ved_id'=>$valarr[9]);	
	}
	
	//print_r($complex_positions);
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new BillPrepare;
	
	$_mf=new MaxFormer;
	
	$alls=$_kpg->GetItemsByIdArr($komplekt_id,$except_id,false,$already_in_bill);
	
	//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	
	/*echo '<pre>';
	print_r($alls);
	echo '</pre>';
	*/
	
	

	$_se=new SectorItem;
	$_pi=new PosItem;
	
	
	
	$arr=array();
	$joined_positions=array();
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$joined_positions[]=array('position_id'=>$valarr[0],'storage_id'=>0/*$valarr[7]*/,'sector_id'=>0/*$valarr[8]*/,'komplekt_ved_id'=>$valarr[9]);	
	}
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	foreach($alls as $k=>$v){
		//echo $v['position_id'].'<br>';
		
		//откуда взять sector_id???? iz komplet_ved
		
		if(!in_array(array('position_id'=>$v['position_id'],'storage_id'=>0,'sector_id'=>0,'komplekt_ved_id'=>$v['komplekt_ved_id']),$joined_positions)){
			$joined_positions[]=array('position_id'=>$v['position_id'],'storage_id'=>0,'sector_id'=>0,'komplekt_ved_id'=>$v['komplekt_ved_id'] );
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
				($cv[9]==$vv['komplekt_ved_id'])	
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
//			  $si=$_si->getItemById($v['storage_id']);
			  $v['storage_name']=$si['name'];
			  if($v['has_pm']){
				 
				  $v['price_pm']=$valarr[10];
			  }else $v['price_pm']=$v['price'];
			  
			  $v['cost']=$v['price']*$v['quantity'];
			  $v['total']=$valarr[11];//$v['price_pm']*$v['quantity'];
		  
			  
			  $v['nds_proc']=NDS;
			  $v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+NDS)/100)));
			  
			  $v['sector_id']=$valarr[8];
			  $v['komplekt_ved_id']=$valarr[9];
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
			  
			  $v['nds_proc']=NDS;
			  $v['nds_summ']=sprintf("%.2f",($v['total']-$v['total']/((100+NDS)/100)));
			  $v['storage_id']= $vv['storage_id'];//$storage_id;
//			  $si=$_si->getItemById($v['storage_id']);
			  $v['storage_name']=$si['name'];
			  
			  
			    $v['sector_id']=$vv['sector_id']; //$valarr[8];
			  $v['komplekt_ved_id']=$vv['komplekt_ved_id']; //$valarr[9];
		  }
		  
		  $v['max_quantity']=$_mf->MaxForBill($v['komplekt_ved_id'], $v['position_id'],$except_id);  // СДЕЛАТЬ ПОЗЖЕ!!!
		 
		  $v['in_rasp']=$_mf->MaxInAcc($except_id, $v['position_id'], NULL, NULL,NULL,NULL,$v['komplekt_ved_id']);  //>MaxInShI($except_id, $v['position_id'],NULL, $v['storage_id'],$v['sector_id'],$v['komplekt_ved_id']); // СДЕЛАТЬ ПОЗЖЕ!!!
		  //echo $v['storage_id'];
		  
		  
		  $v['in_rasp_in']=$_mf->MaxInAccIn($except_id, $v['position_id'], NULL, NULL,NULL,NULL,$v['komplekt_ved_id']);  
		  
		  
		  
		  $in_free=$v['quantity']-$v['in_rasp'];
		  if( $in_free> ($v['in_rasp_in'] - $v['in_rasp'])) $in_free=$v['in_rasp_in'] - $v['in_rasp'];
		  
		  if($in_free<0) $in_free=0;
		  
		  $v['in_free']=$in_free;
		  
		  
		  $se=$_se->GetItemById($vv['sector_id']);
		  $v['sector_name']=$se['name'];
		  if($vv['komplekt_ved_id']!=0) $v['komplekt_ved_name']='Заявка № '.$vv['komplekt_ved_id'];
		  else $v['komplekt_ved_name']='-';
		  
		  
		/*  
		  $storage_names=array(); $storage_ids=array();
		  $sa=$_sscr->GetCategsBookArr($vv['sector_id'], 1); //$_ssg->GetItemsArr(0,1);
		  foreach($sa as $sk=>$sv){
			  $storage_ids[]=$sv['id'];
			  $storage_names[]=$sv['name'];
		  }
		  $v['storage_names']=$storage_names;
		  $v['storage_ids']=$storage_ids;
		  */
		  
		  
		  
		  
		  $v['hash']=md5($v['position_id'].'_'.$v['storage_id'].'_'.$v['sector_id'].'_'.$v['komplekt_ved_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 $sm->assign('BILLUP',BILLUP);
	
	$sm->assign('pospos',$arr);
	$sm->assign('komplekt_id',$komplekt_id);
	
	/*
	if($bi1['status_id']==10){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	
	*/
	if($bi1['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	
	
	
	$sm->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129));
	
	$sm->assign('can_lower_discount',$au->user_rights->CheckAccess('w',881));
	
	
	
	//утверждены ли цены
	$can_mod_object_only=false;
	if(isset($bi1['is_confirmed_price'])&&($bi1['is_confirmed_price']==1)&&($bi1['is_confirmed_shipping']==0)){
		$can_mod_object_only=true;
	}
	$sm->assign('can_mod_object_only',$can_mod_object_only);
	
	
	$can_mod_pm_only=false;
	if(isset($bi1['is_confirmed_price'])&&($bi1['is_confirmed_price']==1)&&($bi1['is_confirmed_shipping']==1)&&($_bi1->HasShsorAccs($bi1['id']))&&
		$au->user_rights->CheckAccess('w',523)){
		$can_mod_pm_only=true;
	}
	$sm->assign('can_mod_pm_only',$can_mod_pm_only);
	
	$sm->assign('can_change_storage',$au->user_rights->CheckAccess('w',133)&&(!$can_mod_pm_only));
	
	if(isset($bi1['is_leading'])) $is_leading=$bi1['is_leading'];
	else $is_leading=-1; 
	$sm->assign('is_leading', $is_leading);
	
	
	$ret.=$sm->fetch("bills/positions_edit_set.html");
	
	/*$ret.= mysqlSet::$inst_count.' запросов к БД на выборку<br />';
$ret.=  nonSet::$inst_count.' запросов на обновление БД<br />';
$ret.=  mysqlSet::$inst_count+nonSet::$inst_count.' всего запросов к БД<br />';


$ret.=  (time()-$_big_time_marker_begin).' сек. <br />';*/
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$id=abs((int)$_POST['id']);
	
	$komplekt_id=abs((int)$_POST['komplekt_id']);
	
	$complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;
 
	$_se=new SectorItem;
	$_posgr=new BillPosGroup;

	
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
		
		
		
		$f['quantity_confirmed']=$_mf->MaxInKomplekt($komplekt_id, $f['id']); //!!!!!! SDLEAT POZJE
		$f['max_quantity']=$_mf->MaxForBill($komplekt_id, $f['id']); 
		//$f['in_rasp']=$_mf->MaxInShI($id, $f['id'],NULL,$f['storage_id']);
		$f['in_rasp']=$_mf->MaxInAcc($id,  $f['id'], NULL,  NULL,NULL,NULL,$f['komplekt_ved_id']); 
		
		$f['in_rasp_in']=$_mf->MaxInAccIn($id,  $f['id'], NULL, NULL,NULL,NULL,$f['komplekt_ved_id']); 
		
		
		$in_free=$f['quantity']-$f['in_rasp'];
		if( $in_free> ($f['in_rasp_in'] - $f['in_rasp'])) $in_free=$f['in_rasp_in'] - $f['in_rasp'];
		
		if($in_free<0) $in_free=0;
		
		$f['in_free']=$in_free;
		
		
		 if(!$_posgr->IsUsl($position['group_id'])){
				 $f['in_free_complex']= $_mf->FreeInAccInComplex($id, $f['id'], 0,   NULL, NULL, NULL, $f['komplekt_ved_id']);
				  
				 //print_r( $f['in_free_complex']);
				 
				 //собрать вывод в удобный для формы вид:
				 $_in_free_complex=array();
				 foreach($f['in_free_complex'] as $vv){
					$_in_free_complex[]='('.$vv[0].','.$vv[1].')'; 
				 }
				 $f['in_free_complex']=implode(';',$_in_free_complex);
				 
		   }else{
				$f['in_free_complex']='(0,'. ($f['quantity']-$f['in_rasp']).')';
			  } 
			  
		
		
		
		$f['nds_proc']=NDS;
		$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
		
		$f['hash']=md5($f['id'].'_'.$f['storage_id'].'_'.$f['sector_id'].'_'.$f['komplekt_ved_id']);
		
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$_bill=new BillItem;
	$bill=$_bill->getItemById($id);
	$sm->assign('bill',$bill);
	
	
	if($bill['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',188)); 
	$sm->assign('can_delete_positions',$au->user_rights->CheckAccess('w',190)); 
		
	$sm->assign('BILLUP',BILLUP);
	$ret=$sm->fetch("bills/positions_on_page_set.html");
	
	
}

elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		
	
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
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="split_positions")){
	//копирование позиции
		
	$id=abs((int)$_POST['id']);
	
	$komplekt_id=abs((int)$_POST['komplekt_id']);
	
	$complex_positions=$_POST['complex_positions'];
	
	
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	
	$position_id=abs((int)$_POST['position_id']);
	
	$old_quantity=((float)$_POST['old_quantity']);
	
	$additional_quantity=((float)$_POST['additional_quantity']);
	$new_storage_id=abs((int)$_POST['new_storage_id']);
	$old_storage_id=abs((int)$_POST['old_storage_id']);
	$new_quantity=((float)$_POST['new_quantity']);
	
	
	//echo $additional_quantity.': '.$position_id.'_'.$storage_id.'_'.$sector_id.'_'.$komplekt_id;
	
	
	$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	
	$_mf=new MaxFormer;
	$_si=new StorageItem;
	$_se=new SectorItem;
	$was_splited=false;
	
	
	
	//foreach($selected_quantities as $k=>$v){
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);	
		
		$f=array();	
		//$do_add=true;
		if($v[1]<=0) continue;
		$position=$_position->GetItemById($v[0]);
		if($position===false) continue;
		
		
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
	//	$f['discount_plus_or_minus']=$v[13];
		$f['discount_value']=$v[13];
		
		
		$f['storage_id']=$v[7];
		$si=$_si->getItemById($f['storage_id']);
		$f['storage_name']=$si['name'];
		
		if((!$was_splited)&&($f['id']==$position_id)&&($f['storage_id']==$old_storage_id)){
			//добавить элемент	
			$f['quantity']=$old_quantity-$new_quantity; 
			
			//$f['storage_id']=$new_storage_id;
		}else {
			$f['quantity']=$v[1];
			//$f['storage_id']=$selected_storage_ids[$k];
			
		}
		
		
		$f['sector_id']=$v[8];
		$se=$_se->getItemById($f['sector_id']);
		$f['sector_name']=$se['name'];
		
		$f['komplekt_ved_id']=$v[9];
		
		if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
		else $f['komplekt_ved_name']='-';
		
		//cena +-
		if($v[2]==1){
			$slag=0;
			if($f['rub_or_percent']==0){
				$slag=$f['value'];
			}else{
				$slag=$f['price']*$f['value']/100.0;
			}
			
			if($f['plus_or_minus']==1) $slag=-1.0*$slag;
			$f['price_pm']=$f['price']+$slag;
			//$f['price_pm']=$v[10];
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		$f['total']=$f['price_pm']*$f['quantity'];
		
		$f['quantity_confirmed']=$_mf->MaxInKomplekt($komplekt_id, $f['id']); 
		$f['max_quantity']=$_mf->MaxForBill($komplekt_id, $f['id']); 
		$f['in_rasp']=$_mf->MaxInShI($id, $f['id']);
		
		$f['nds_proc']=NDS;
		$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
		
		
		$f['hash']=md5($f['id'].'_'.$f['storage_id'].'_'.$f['sector_id'].'_'.$f['komplekt_ved_id']);
		
		
		
	//	$ret.=$v.' ';
		//предусмотреть условие, когда еще не внесли в общий список
		
		$was_found=false;
		foreach($alls as $ck=>$cv){
			if(($cv['id']==$f['id'])&&($cv['storage_id']==$f['storage_id'])
			&&($cv['sector_id']==$f['sector_id'])
			&&($cv['komplekt_ved_id']==$f['komplekt_ved_id'])
			){
				 //unset($alls[$ck]);
				 $was_found=true;
				 break;
			}
		}
		
		if(!$was_found) $alls[]=$f;
		
		
		if((!$was_splited)
		&&($f['id']==$position_id)&&($f['storage_id']==$old_storage_id)
		&&($f['sector_id']==$sector_id)&&($f['komplekt_ved_id']==$komplekt_id)
		
		){
			//добавить элемент	- или изменить существующий?????
			
			$c=array();	
			//$do_add=true;
			if($v[1]<=0) continue;
			$position=$_position->GetItemById($v[0]);
			if($position===false) continue;
			
			
			$c['id']=$v[0];
			
			$c['position_name']=$position['name'];
			$c['dimension_id']=$position['dimension_id'];
			
			$dim=$_dim->GetItemById($c['dimension_id']);
			$c['dim_name']=$dim['name'];
			
			$c['price']=$v[3];
			
			//+/-
			$c['has_pm']=$v[2];
			$c['rub_or_percent']=$v[4];
			$c['plus_or_minus']=$v[5];
			$c['value']=$v[6];
			
			$c['discount_rub_or_percent']=$v[12];
			//$c['discount_plus_or_minus']=$v[13];
			$c['discount_value']=$v[13];
			
			$c['storage_id']=$v[7];
			$si=$_si->getItemById($c['storage_id']);
			$c['storage_name']=$si['name'];
				//добавить элемент	
			$c['quantity']=$new_quantity+$additional_quantity;
				
			$c['storage_id']=$new_storage_id;
			
			$si=$_si->getItemById($c['storage_id']);
			$c['storage_name']=$si['name'];
			
			//cena +-
			if($v[2]==1){
				$slag=0;
				if($c['rub_or_percent']==0){
					$slag=$c['value'];
				}else{
					$slag=$c['price']*$c['value']/100.0;
				}
				
				if($c['plus_or_minus']==1) $slag=-1.0*$slag;
				$c['price_pm']=$c['price']+$slag;
			}else $c['price_pm']=$c['price'];
			
			
			$c['sector_id']=$v[8];
			$se=$_se->getItemById($c['sector_id']);
			$c['sector_name']=$se['name'];
			
			$c['komplekt_ved_id']=$v[9];
			
			if($c['komplekt_ved_id']!=0) $c['komplekt_ved_name']='Заявка № '.$c['komplekt_ved_id'];
			else $c['komplekt_ved_name']='-';
			
			//st-t'
			$c['cost']=$c['price']*$c['quantity'];
			
			
			//vsego
			$c['total']=$c['price_pm']*$c['quantity'];
			
			$c['quantity_confirmed']=$_mf->MaxInKomplekt($komplekt_id, $c['id']); 
			$c['max_quantity']=$_mf->MaxForBill($komplekt_id, $c['id']); 
			$c['in_rasp']=$_mf->MaxInShI($id, $c['id']);
			
			$c['nds_proc']=NDS;
			$c['nds_summ']=sprintf("%.2f",($c['total']-$c['total']/((100+NDS)/100)));
			
			
			$c['hash']=md5($c['id'].'_'.$c['storage_id'].'_'.$c['sector_id'].'_'.$c['komplekt_ved_id']);
			
			//если такой position_id и такой storage_id уже был в alls - удалить его!
			$was_found=false;
			foreach($alls as $ck=>$cv){
				if(($cv['id']==$c['id'])&&($cv['storage_id']==$c['storage_id'])
				&&($cv['sector_id']==$c['sector_id'])
				&&($cv['komplekt_ved_id']==$c['komplekt_ved_id'])
				  ){
					 //unset($alls[$ck]);
					 $alls[$ck]=$c;
					 $was_found=true;
					 break;
				}
			}
			
			if(!$was_found) $alls[]=$c;
			//предусмотреть условие, когда еще не внесли в общий список
			
			
			$was_splited=true;
		}
	}
	
	
	
	
		
	
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$_bill=new BillItem;
	$bill=$_bill->getItemById($id);
	$sm->assign('bill',$bill);
	
	
	
	if($bill['is_confirmed_price']==1){
		$sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
	}else $sm->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',130));
	
	
	
	$sm->assign('BILLUP',BILLUP);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',188)); 
	$sm->assign('can_delete_positions',$au->user_rights->CheckAccess('w',190)); 
	
	$ret=$sm->fetch("bills/positions_on_page_set.html");
}	

//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new BillNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',339), $au->user_rights->CheckAccess('w',349), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',191));
	
	
	$ret=$sm->fetch('bills/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',191)){
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
	
	$log->PutEntry($result['id'],'добавил примечания по исходящему счету', NULL,191, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',191)){
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
	
	$log->PutEntry($result['id'],'редактировал примечания по исходящему счету', NULL,191,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',191)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BillNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по исходящему счету', NULL,191,NULL,NULL,$user_id);
	
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
	
	$_ti=new BillItem;
	
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
		if(($au->user_rights->CheckAccess('w',196))||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==20)||($trust['status_id']==21)){
				$_ti->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение цен',NULL,196, NULL, NULL,$bill_id);
				$_ti->FreeBindedPayments($bill_id);
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',95)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил цены',NULL,95, NULL, NULL,$bill_id);	
				
				if($flag_to_payments==1) $_ti->BindPayments($bill_id,$result['org_id']);		
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='bills/bills_list.html';
	else $template='bills/bills_list_komplekt.html';
	
	
	$acg=new BillGroup;
	
	$acg->SetAuthResult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94), '', $au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',131),NULL,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197),
	$bills_list,
	$au->user_rights->CheckAccess('w',283),
	$au->user_rights->CheckAccess('w',860),
		$au->user_rights->CheckAccess('w',835)
	);
	
		
}elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_price")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillItem;
	
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
	
	
	$sm->assign('can_confirm_price', $au->user_rights->CheckAccess('w',95));
	$sm->assign('can_super_confirm_price', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('bills/toggle_confirm_price.html');
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillItem;
	
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
		if(($au->user_rights->CheckAccess('w',197))||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==20)||($trust['status_id']==21)){
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_shipping'=>0, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение отгрузки',NULL,197, NULL, NULL,$bill_id);
				
			}
				
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',195)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)||($trust['status_id']==20)||($trust['status_id']==21)){
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_shipping'=>1, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил отгрузку',NULL,195, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='bills/bills_list.html';
	else $template='bills/bills_list_komplekt.html';
	
	
	$acg=new BillGroup;
	$acg->SetAuthResult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',128),$au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94), '', $au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',131),NULL,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197),
	$bills_list,
	$au->user_rights->CheckAccess('w',283),
	$au->user_rights->CheckAccess('w',860),
		$au->user_rights->CheckAccess('w',835)
	);
	
	
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new BillItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',94)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование исходящего счета',NULL,94,NULL,'исходящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);	
			
			//внести примечание
			$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).'  , причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',131)){
			$_ti->Edit($id,array('status_id'=>1),false,$result);
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление исходящего счета',NULL,131,NULL,'исходящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).'  ',
				'is_auto'=>1,
				'pdate'=>time()
					));		
			
		}
		
	}
	
	if($from_card==0){
	  $shorter=abs((int)$_POST['shorter']);
	  if($shorter==0) $template='bills/bills_list.html';
	  else $template='bills/bills_list_komplekt.html';
	  
	  
	  $acg=new BillGroup;
	  $acg->SetAuthResult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	  
	  $ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94), '', $au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',131),NULL,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197),
	  $bills_list,
	  $au->user_rights->CheckAccess('w',283),
	  $au->user_rights->CheckAccess('w',860),
		$au->user_rights->CheckAccess('w',835)
	  );
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',94);
		if(!$au->user_rights->CheckAccess('w',94)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',131);
			if(!$au->user_rights->CheckAccess('w',131)) $reason='недостаточно прав для данной операции';
		
		
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('bills/toggle_annul_card.html');		
	}
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="scan_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillItem;
	
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
	
	
	$sm->assign('can_confirm_shipping', $au->user_rights->CheckAccess('w',95));
	$sm->assign('can_super_confirm_shipping', $au->user_rights->CheckAccess('w',96));
	
	//$itm=array();
	
	$sm->assign('filename','bill.php');
	$sm->assign('item',$trust);
	$sm->assign('user_id',$result['id']);
	
	$ret=$sm->fetch('bills/toggle_confirm_ship.html');
		
}elseif(isset($_POST['action'])&&($_POST['action']=="find_sh_pos")){
	//dostup
	$_kr=new BillReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$ret=$_kr->InAcc($id, $bill_id,'bills/in_accs.html',$result['org_id'],true, $storage_id, $komplekt_ved_id);
	
	
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_sh_pos_in")){
	//dostup
	$_kr=new BillReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	$ret=$_kr->InAccIn($id, $bill_id,'bills/in_accs_in.html',$result['org_id'],true, $storage_id, $komplekt_ved_id);
	
	
	
	
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="draw_positions")){
	$id=abs((int)$_POST['id']);
	
	$_bill=new BillItem;
	
	$bill=$_bill->GetItemById($id);
	
	//bills/position_actions.html" bill=$bill action=1}%
	$sm=new SmartyAj;
	
	$sm->assign('filename','bill.php');
	
	$_bpg=new BillPosGroup;
	$bpg=$_bpg->GetItemsByIdArr($bill['id']);
	//print_r($bpg);
	$sm->assign('positions',$bpg);
	$sm->assign('has_positions',true);
	$_bpf=new BillPosPMFormer;
	
	$_pld=new PlDisGroup;
	$sm->assign('discs1',$_pld->GetItemsArr());
	
	$total_cost=$_bpf->CalcCost($bpg);
	$total_nds=$_bpf->CalcNDS($bpg);
	$sm->assign('total_cost',$total_cost);
	$sm->assign('total_nds',$total_nds);
	
	$sm->assign('action',1);
	$sm->assign('bill',$bill);
	
	
	
	$ret=$sm->fetch('bills/position_actions.html');
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',292)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillItem;
		
		$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_scan_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',292)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		
		//$_sh_p=new ShIPosItem();
		$_sh=new BillItem;
		
		$_sh->ScanEq($id,$args,$output);
		
		if(!isset($_POST['not_cut_html'])) $output=strip_tags($output);
			
		
		$ret=$output;
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}



}elseif(isset($_POST['action'])&&($_POST['action']=="togglemass_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',292)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		$_sh=new BillItem;
		
		
		//$_sh_p=new ShIPosItem();
		/*$_sh=new BillItem;
		
		$_sh->DoEq($id,$args,$output);
		
		$ret='<script>alert("'.$output.'"); location.reload();</script>';*/
		
		
		foreach($args as $k=>$arg){
		
			$_sh->DoEq($id,array($arg),$output);
		
		}
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="togglemass_scan_eq")){
	//выравнивание
	if($au->user_rights->CheckAccess('w',292)){
		$id=abs((int)$_POST['id']);
		$args=$_POST['args'];
		$_sh=new BillItem;
		
		
		$alls=array();
		
		 
		$_pos=new PosItem;
		foreach($args as $k=>$arg){
			
			$eq_items=array();
			$eq_items=$_sh->ScanEq($id,array( $arg),$output,NULL,false,"");
		
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
		//$_sh_p=new ShIPosItem();
		
		//$_sh->ScanEq($id,$args,$output);
		
		
		//$ret=$output;
		$sm=new SmartyAj;
		
		$sm->assign('items', $alls);
			
		$ret=$sm->fetch('komplekt/scan_eq.html');	
		
	}else{
		$ret='<script>alert("У Вас недостаточно прав для данного действия.");</script>';	
	}










}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_binded_bills")){
	//проверить, есть ли связанные вход. счета
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		$res=$_ki->DocGetBindedBills($id,$rss55);
		if($res!=="") $ret=$res;
		else $ret=0;
		
		
		//если ноль - то все хорошо
 
}elseif(isset($_POST['action'])&&($_POST['action']=="check_binded_bills_to_confirm")){
	//проверить, есть ли связанные вход. счета
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		$res=$_ki->DocGetBindedUnBills($id,$rss55);
		if($res!=="") $ret=$res;
		else $ret=0;
		
		
		//если ноль - то все хорошо
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_binded_cash")){
	//проверить, есть ли связанные вход. счета
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		$res=$_ki->DocGetBindedCash($id,$rss55);
		if($res!=="") $ret=$res;
		else $ret=0;
		
		
		//если ноль - то все хорошо
 
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	

}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new BillItem;
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="update_discount_given")){
	//проверить, есть ли заявки с таким номером для такого уч.
		//header('Content-type: text/html; charset=windows-1251');
		
		$bill_id=abs((int)$_POST['bill_id']);
		$table_id=abs((int)$_POST['table_id']);
		
		$discount_given=(float)$_POST['discount_given'];
		
		if($au->user_rights->CheckAccess('w',365)||$au->user_rights->CheckAccess('w',363)){
			$_ki=new BillItem;
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
					  $log->PutEntry($result['id'],'задал полученный +/- позиции исходящего счета', NULL,365, NULL,'позиция '.SecStr($bpi['name']).', старая сумма полученного +/- '.$bpm['discount_given'].' руб. '.', новая сумма полученного +/- '.$discount_given.' руб.',$bill_id);
				  }
			  }
			  
			  $sm=new SmartyAj;
			  
			  $item=array(
			  	'manager_name'=>$result['name_s'],
				'manager_login'=>$result['login'],
				'discount_given_pdate'=>date('d.m.Y H:i:s')
			  );
			  $sm->assign('item',$item);
			  
			  $ret=$sm->fetch('bills/positions_pm_saver.html');
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
		$_bi=new BillItem;
		
		$bill=$_bi->GetItemById($id);
		
		$can=$_bi->CanIsInBuh($id, $rss22, $bill, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481));
		
		if($can){
			
			
			if($state==1){
				$_bi->Edit($id, array(
					'is_in_buh'=>$state,
					'in_buh_pdate'=>time(),
					'user_in_buh_id'=>$result['id']
				));	
				
				 $log->PutEntry($result['id'],SecStr('установил флаг "счет в бухгалтерии"'), NULL,480, NULL,'счет № '.$bill['code'],$id);
			}else{
				
				$_bi->Edit($id, array(
					'is_in_buh'=>$state,
					'in_buh_pdate'=>0,
					'user_in_buh_id'=>0
				));
				
				$log->PutEntry($result['id'],SecStr('снял флаг "счет в бухгалтерии"'), NULL,481, NULL,'счет № '.$bill['code'],$id);
			}
		}
		
		$ret='';
}elseif(isset($_POST['action'])&&($_POST['action']=="mass_is_in_buh_update")){
	
		//header('Content-type: text/html; charset=windows-1251');
		
		$id=abs((int)$_POST['id']);
		
		$_bi=new BillItem;
		
		$marked_as_not_in=$_POST['marked_as_not_in'];
		$marked_as_in=$_POST['marked_as_in'];
		
		if(is_array($marked_as_not_in)) foreach($marked_as_not_in as $k=>$v){
			$bill=$_bi->GetItemById($v);
		
			$can=$_bi->CanIsInBuh($v, $rss22, $bill, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481));
			
			if($can){
				$_bi->Edit($v, array(
					'is_in_buh'=>0,
					'in_buh_pdate'=>0,
					'user_in_buh_id'=>0
				));
				
				$log->PutEntry($result['id'],SecStr('снял флаг "счет в бухгалтерии"'), NULL,481, NULL,'счет № '.$bill['code'],$v);	
			}
			
		}
		
		
		if(is_array($marked_as_in)) foreach($marked_as_in as $k=>$v){
			$bill=$_bi->GetItemById($v);
		
			$can=$_bi->CanIsInBuh($v, $rss22, $bill, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481));
			
			if($can){
				$_bi->Edit($v, array(
					'is_in_buh'=>1,
					'in_buh_pdate'=>time(),
					'user_in_buh_id'=>$result['id']
				));	
				
				 $log->PutEntry($result['id'],SecStr('установил флаг "счет в бухгалтерии"'), NULL,480, NULL,'счет № '.$bill['code'],$v);
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
		if(strlen($message)>0) $message="Изменится сумма реализаций: \n".$message."\n";
	}
	
	$ret=$message;
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	//получить список контактов к-та с эл. почтой (ее айди=5)
	//получить список сотр-ков с эл. почтой
	$_sdg=new SupplierContactDataGroup;
	$_udg=new UserContactDataGroup;
	
	//ограничения по сотруднику
	$limited='';
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$limited=' and id in('.implode(', ', $limited_user).') ';
	}
	
	
	
	
	$sql='
		(select "0" as kind, name as name_s, "" as login, position as position_s, id, "" as email_s
			from supplier_contact
			where supplier_id="'.abs((int)$_POST['supplier_id']).'"
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			)
		UNION ALL
		(select "1" as kind, name_s as name_s, login as login, position_s as position_s, id, email_s as email_s		
			from user
			where is_active=1 
			/*and id in(select distinct user_id from user_contact_data where kind_id=5)*/ '.$limited.'
			
		)		
		order by 1 asc, 2 asc';
		
	//echo $sql;	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultnumrows();
	$alls=array(); $old=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
		if($f['kind']==0) $data=$_sdg->GetItemsByIdArr($f['id']);
		else{
			 $data=$_udg->GetItemsByIdArr($f['id']);
			 
			 $was_in=false; foreach($data as $k=>$v) if(($v['kind_id']==5)&&($v['value']==$f['email_s'])) $was_in=$was_in||true;
			 //добавить адрес из карты
			 if(!$was_in) $data[]=array('id'=>0, 'kind_id'=>5, 'value'=>$f['email_s']);
		}
		
		$data1=array();
		foreach($data as $k=>$v){
			if($v['kind_id']==5) $data1[]=$v;	
		}
		
		
		$f['is_begin']=($i==0);
		$f['has_hr']=($f['kind']==1)&&($old['kind']==0);
		
		$f['data']=$data1;
		
		$alls[]=$f;	
		$old=$f;
	}
	
	//print_r($alls);
		
	$sm=new SmartyAj;
	
	$sm->assign('items', $alls);
	$ret=$sm->fetch('bills/pdf_addresses.html');

}
//check_acceptances
elseif(isset($_POST['action'])&&($_POST['action']=="check_acceptances")){

	$sql='
		select count(id) from acceptance where bill_id="'.abs((int)$_POST['id']).'" and is_confirmed=1 and is_incoming=0';
		
	//echo $sql;	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$f=mysqli_fetch_array($rs);
	
	if((int)$f[0]>0) $ret=1;
	else $ret=0;
}



//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Bill_ViewGroup;
	$_view=new Bill_ViewItem;
	
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
	$_views=new Bill_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>