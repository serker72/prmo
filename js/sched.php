<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
 

require_once('../classes/user_s_item.php');


require_once('../classes/posgroupgroup.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');

require_once('../classes/schednotesgroup.php');
require_once('../classes/schednotesitem.php');


require_once('../classes/user_s_group.php');

require_once('../classes/suppliersgroup.php');
require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');
require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');

require_once('../classes/supplier_district_group.php');
require_once('../classes/supplier_cities_group.php');
require_once('../classes/suppliercontactgroup.php');


require_once('../classes/supplier_to_user.php');



require_once('../classes/sched.class.php');
require_once('../classes/sched_history_fileitem.php');
require_once('../classes/sched_history_item.php');
require_once('../classes/sched_history_group.php');


require_once('../classes/quick_suppliers_group.php');
require_once('../classes/sched_fileitem.php');
 
require_once('../classes/filecontents.php');

require_once('../classes/faitem.php');
 
 

 
 

$au=new AuthUser();
$result=$au->Auth(false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
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
	
}elseif(isset($_POST['action'])&&(($_POST['action']=="find_suppliers")||($_POST['action']=="find_suppliers_ship")||($_POST['action']=="find_many_suppliers")||($_POST['action']=="find_many_suppliers_15"))){
	
	
	
	
	$_pg=new Quick_SupplierGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	 
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['opf'])))>0){
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['opf'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
			$dec->AddEntry(new SqlEntry('p.opf_id','select id from opf where '.implode(' or ', $names), SqlEntry::IN_SQL));
	}

	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0){
	 
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['code'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0){
		 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['full_name'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0){
		 
		
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['inn'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.inn', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) {
 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['kpp'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.kpp', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	 
 
 
 
	 if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['city'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['city'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
			 
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_sprav_city where city_id in( select id from sprav_city where '.implode(' or ',$names).')', SqlEntry::IN_SQL));
		
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['contact'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
		
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  '.implode(' or ',$names).'', SqlEntry::IN_SQL));
		
	}
	
		if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	//ограничений нет
 		
	if($_POST['action']=="find_suppliers_ship") $ret=$_pg->GetItemsForBill('plan/ship_suppliers_list.html',  $dec,true,$all7,$result,  0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	elseif($_POST['action']=="find_many_suppliers") $ret=$_pg->GetItemsForBill('plan/suppliers_many_list.html',  $dec,true,$all7,$result,  0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	elseif($_POST['action']=="find_many_suppliers_15") $ret=$_pg->GetItemsForBill('plan/suppliers_15_list.html',  $dec,true,$all7,$result,  0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0));  
	else $ret=$_pg->GetItemsForBill('plan/suppliers_list.html',  $dec,true,$all7,$result,  0, (strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0)); 
	

	

	
	
	//получим список позиций по фильтру
	/*$_pg=new Sched_SupplierGroup;
	
	$dec=new DBDecorator;
	
	//$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0) $dec->AddEntry(new SqlEntry('p.code',SecStr(iconv("utf-8","windows-1251",$_POST['code'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0) $dec->AddEntry(new SqlEntry('p.full_name',SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0) $dec->AddEntry(new SqlEntry('p.inn',SecStr(iconv("utf-8","windows-1251",$_POST['inn'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) $dec->AddEntry(new SqlEntry('p.kpp',SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])), SqlEntry::LIKE));
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])))>0) $dec->AddEntry(new SqlEntry('p.legal_address',SecStr(iconv("utf-8","windows-1251",$_POST['legal_address'])), SqlEntry::LIKE));
	
	if(isset($_POST['already_loaded'])&&is_array($_POST['already_loaded'])) $dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['already_loaded']));	
	
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
	}
	
	
	
	
	
	
	if($_POST['action']=="find_suppliers_ship") $ret=$_pg->GetItemsForBill('plan/ship_suppliers_list.html',  $dec,true,$all7,$result);
	elseif($_POST['action']=="find_many_suppliers") $ret=$_pg->GetItemsForBill('plan/suppliers_many_list.html',  $dec,true,$all7,$result);
	else $ret=$_pg->GetItemsForBill('plan/suppliers_list.html',  $dec,true,$all7,$result);
	*/
	
} 
elseif(isset($_POST['action'])&&(($_POST['action']=="retrieve_contacts")||($_POST['action']=="retrieve_only_contacts"))){
	$_sc=new Sched_SupplierContactGroup;
	
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$current_id=abs((int)$_POST['current_id']);
	$current_k_id=abs((int)$_POST['current_k_id']);
	
	
	
	
	$alls=$_sc->GetItemsByIdArr($supplier_id,$current_id, $current_k_id); 
	$sm=new SmartyAj;
	
	
	$sm->assign('supplier_id', $supplier_id);
	$sm->assign('items', $alls);
	
	if($_POST['action']=="retrieve_only_contacts") $ret=$sm->fetch('plan/suppliers_only_contacts.html');
	else $ret=$sm->fetch('plan/suppliers_contacts.html');

}


//подгрузка пол-лей для деления заметкой
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	
	 
	
	$sched_id=abs((int)$_POST['sched_id']);
	$_bi1=new Sched_AbstractItem;
	$bi1=$_bi1->GetItemById($sched_id);
	
	
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	
	$except_ids=$_POST['except_ids'];
	
	
/*	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$already_in_bill[]=array('user_id'=>$valarr[0],'right_id'=>$valarr[1]);	
	}*/
	
	//print_r($complex_positions);
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new Sched_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	if(is_array($except_ids)&&(count($except_ids)>0)){
			$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_ids));
	}
	
	$alls=$_kpg->GetItemsForBill($dec); //GetItemsByIdArr($komplekt_id,$except_id,false,$already_in_bill);
	
	//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	
	/*echo '<pre>';
	print_r($alls);
	echo '</pre>';
	*/
	

	 
	
	
	
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				/*($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])	*/
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  $v['right_id']=$valarr[1];
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			  $v['right_id']=1;
		  }
		  
		   
		  
		  
		  
		  
		  $v['hash']=md5($v['user_id'].'_'.$v['right_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("plan/share_edit_set.html");
	
	 
	
	


}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_users")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$shed_id=abs((int)$_POST['shed_id']);
	 $complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	$_user=new UserSItem;
	 

	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		
		
		
		$user=$_user->GetItemById($v[0]);
		if($user===false) continue;
		
		 
		$f['id']=$v[0];
		$f['user_id']=$v[0];
		
		$f['right_id']=$v[1];
		
		$f['name_s']=$user['name_s'];
		$f['login']=$user['login'];
		
		$f['is_active']=$user['is_active'];
		
		$f['hash']=md5($v[0].'_'.$v[1]);
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	 
	$ret=$sm->fetch("plan/share_on_page_set.html");
	
	

}elseif(isset($_POST['action'])&&($_POST['action']=="calc_new_plans")){
	$_rem=new SchedRemindGroup;
	
	$ret=$_rem->CalcNewPlans($result['id']);
	
	//добавим добавление автонапоминаний о просроченных событиях
	$_rem->PutAutoReminds($result['id']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="load_new_plans")){
	$_rem=new SchedRemindGroup;
	
	$res=$_rem->LoadNewPlans($result['id']);
	
	$sm=new SmartyAj;
	
	$sm->assign('items', $res);
	$ret=$sm->fetch('plan/plan_head_data.html');
	
}elseif(isset($_POST['action'])&&($_POST['action']=="put_read_plans")){
	
	$_rem=new SchedRemindItem;
	$data=$_POST['data'];
	foreach($data as $k=>$v){
		//$_rem->Edit(abs((int)$v), array('is_viewed'=>1));	
		//$_rem->Del(abs((int)$v));
		
		$vv=explode(';',$v);
		
		if($vv[1]==0){
			//передвинуть напоминание
			switch($vv[2]){
				case 1:
					$delta=15*60;
				break;
				case 2:
					$delta=30*60;
				break;
				case 3:
					$delta=60*60;
				break;
				case 4:
					$delta=24*60*60;
				break;
				default:
					$delta=15*60;
				break;
			}
			$params=array();
			//$rem=$_rem->GetItemById($vv[0]);
			$params['action_time']=time()+$delta;
			$params['is_viewed']=1;
			$_rem->Edit($vv[0], $params);
		}
		else{
			//больше не напоминать
			//$_rem->Del($vv[0]);
			//внести маркер "БОЛЬШЕ НЕ НАПОМИНАТЬ"
			
			$params=array();
			  
			$params['is_viewed']=2;
			$_rem->Edit($vv[0], $params);
			

		}
	}
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	    
		$_dem=new Sched_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Sched_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_byresults")){
		$id=abs((int)$_POST['id']);
		
	    
		$_dem=new Sched_MissionItem;
		 
		
		
		if(!$_dem->DocCanConfirmShipByResults($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



 
	



	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new Sched_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Sched_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_dem=new Sched_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Sched_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	





}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fulfil")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_ki=new Sched_TaskItem;
		 
		
		
		if(!$_ki->DocCanUnconfirmFulfil($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fulfil")){
		$id=abs((int)$_POST['id']);
		
	
		
	  
		$_ki=new Sched_TaskItem;
		 
		
		
		if(!$_ki->DocCanConfirmFulfil($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	



}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fulfil_23")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$id=abs((int)$_POST['id']);
		
		$_ki=new Sched_AbstractItem;
		$f=$_ki->GetItemById($id);
		
	  
		//$_ki=new Sched_TaskItem;
		$_res=new Sched_Resolver($f['kind_id']);
		 
		
		
		if(!$_res->instance->DocCanUnconfirmFulfil($id,$rss55,$f)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fulfil_23")){
		$id=abs((int)$_POST['id']);
		
		$_ki=new Sched_AbstractItem;
		$f=$_ki->GetItemById($id);
		
	  
		//$_ki=new Sched_TaskItem;
		$_res=new Sched_Resolver($f['kind_id']);
		 
		
		
		if(!$_res->instance->DocCanConfirmFulfil($id,$rss55,$f)) $ret=$rss55;
		else $ret=0;
		
		//если ноль - то все хорошо
	

}



elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_price")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new Sched_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Sched_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanUnconfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_price")){
		$id=abs((int)$_POST['id']);
		
	
		
		  
		$_dem=new Sched_AbstractItem;
		$dem=$_dem->Getitembyid($id);
		
		$_res=new Sched_Resolver($dem['kind_id']);
		
		
		$_ki=$_res->instance;
		
		
		
		if(!$_ki->DocCanConfirmPrice($id,$rss55)) $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	//$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==18)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',905)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование записи планировщика',NULL,905,NULL,'звонок № '.$trust['id'].': установлен статус '.$stat['name'],$id);	
			
			 
			//внести примечание
			/*$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	*/
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',905)){
			$_ti->Edit($id,array('status_id'=>18,  'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление записи планировщика',NULL,905,NULL,'звонок № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			/*$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));	*/	
			
		}
		
	}
	
	if($from_card==0){
	  $shorter=abs((int)$_POST['shorter']);
	/*  if($shorter==0) $template='plan/table.html';
	  else $template='plan/table.html';
	  */
	  switch($trust['kind_id']){
		case 2:
			$template='plan/table_2.html';
		break;
		case 3:
			$template='plan/table_3.html';
		break;
		case 4:
			$template='plan/table.html';
		break;
		default:
			$template='plan/table.html';
		break;  
	  }
	  
	  $acg=new Sched_Group;
	  $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	  $ret=$acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905) ); 
	  
	 
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',905);
		if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',905);
			if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		
		$stat=$_stat->Getitembyid($editing_user['status_id']);
		$editing_user['status_name']=$stat['name'];
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('plan/toggle_annul_card.html');		
	}
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul1")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ki=new Sched_AbstractItem;
	
	$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	

	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==18)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',905)){
			$_ti->Edit($id,array('status_id'=>3),false,$result);
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование записи планировщика',NULL,905,NULL,'звонок № '.$trust['id'].': установлен статус '.$stat['name'],$id);	
			
			 
			//внести примечание
			 
			//внести примечание
			$_ni=new Sched_HistoryItem;
			 
			$_ni->Add(array(
				'sched_id'=>$id,
				'user_id'=>0,
				'txt'=>'Автоматический комментарий: документ был аннулирован пользователем '.SecStr($result['name_s']).' , причина: '.$note,
				 
				'pdate'=>time()
					));	
		}
	}elseif($trust['status_id']==3){
		//разудаление
		if($au->user_rights->CheckAccess('w',905)){
			$_ti->Edit($id,array('status_id'=>18,  'restore_pdate'=>time()),false,$result);
			
			$stat=$_stat->GetItemById(18);
			$log->PutEntry($result['id'],'восстановление записи планировщика',NULL,905,NULL,'звонок № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
			//внести примечание
			/*$_ni=new BillNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));	*/	
			
		}
		
	}
	
 
	  $shorter=abs((int)$_POST['shorter']);
	 
	  
			$template='plan/table_1.html';
	 
	  
	  $acg=new Sched_TaskGroup;
	  $acg->setauthresult($result);
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	   $ret=$acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  
	  
	   $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',905), //14
			  $au->user_rights->CheckAccess('w',905), //15
			  false, //16
			  false, //17
			  $au->user_rights->CheckAccess('w',946) //18
	 
	  
	  ); 

	 
	 
		
}


//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	$_si=new UserSItem;
	 
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения записи планировщика',NULL,905, NULL, NULL,$bill_id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения записи планировщика',NULL,905, NULL, NULL,$bill_id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=new Sched_Group;
	
	$shorter=abs((int)$_POST['shorter']);
	 switch($trust['kind_id']){
		case 2:
			$template='plan/table_2.html';
		break;
		case 3:
			$template='plan/table_3.html';
		break;
		case 4:
			$template='plan/table.html';
		break;
		default:
			$template='plan/table.html';
		break;  
	  }
	
	$acg->setauthresult($result);
	
	
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	//$ret= $acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905) ); 
	
	
	$ret= $acg->ShowPos(
		$trust['kind_id'], //0
		$template,  //1
		$dec, //2
		$au->user_rights->CheckAccess('w',905), //3
		0, //4
		1000, //5
		false, //6
		true,  //7
		 $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
		      $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20

			  );

		
}




//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price1")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	$_si=new UserSItem;
	 
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights=$_roles->GetFieldsRoles($trust, $result['id']);	
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanUnconfirmPrice($id, $rss)&&($field_rights['can_unconfirm'])){
			    
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()), true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения записи планировщика',NULL,905, NULL, NULL,$bill_id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanConfirmPrice($id, $rss)&&($field_rights['can_confirm'])){
			 
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнения записи планировщика',NULL,905, NULL, NULL,$bill_id);	
				
			 	
			 
		} 
	}
	
	
	
	$acg=new Sched_TaskGroup;
	$acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	 
			$template='plan/table_1.html';
	 
	
	
	
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret= $acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true, 
	  $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',905), //14
			  $au->user_rights->CheckAccess('w',905), //15
			  false, //16
			  false, //17
			  $au->user_rights->CheckAccess('w',946) //18
	  ); 

	
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_done']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',905))){
			 
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение выполнения записи планировщика',NULL,905, NULL, 'результат звонка: '.$note,$bill_id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)){
			 
			if($_ti->DocCanConfirmShip($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time(), 'report'=>$note),true,$result);
				
				$log->PutEntry($result['id'],'утвердил выполнение записи планировщика',NULL,905, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
				 
				if(in_array($trust['kind_id'], array(2,3))&&$au->user_rights->CheckAccess('w',916)&&($trust['manager_id']==$result['id'])){
					$_ti->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
			  
					$log->PutEntry($result['id'],'утвердил прием работы',NULL,916, NULL, 'автоматическое утверждение приема работы собственной встречи/командировки при утверждении выполнения при наличии прав на утверждение приема работы',$id);
			  
				   
			   }	

			}
			 
		} 
	}
	
	
		
	$acg=new Sched_Group;
	
	$acg->setauthresult($result);
	$shorter=abs((int)$_POST['shorter']);
	switch($trust['kind_id']){
		case 2:
			$template='plan/table_2.html';
		break;
		case 3:
			$template='plan/table_3.html';
		break;
		case 4:
			$template='plan/table.html';
		break;
		default:
			$template='plan/table.html';
		break;  
	  }
	
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
//	$ret= $acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905) ); 
	
	
	$ret= $acg->ShowPos(
		$trust['kind_id'], //0
		$template,  //1
		$dec, //2
		$au->user_rights->CheckAccess('w',905), //3
		0, //4
		1000, //5
		false, //6
		true,  //7
		 $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
		      $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20

			  );

	
		
}


//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price5")){
	$id=abs((int)$_POST['id']);
	 
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	$_si=new UserSItem;
	 
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$id;
	
	$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights=$_roles->GetFieldsRoles($trust, $result['id']);	
	
	if($trust['note_is_actual']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanUnconfirmPrice($id, $rss)){
			    
				$_ti->Edit($id,array('note_is_actual'=>0));
				
				$log->PutEntry($result['id'],'отметил заметку неактуальной',NULL,905, NULL, NULL,$bill_id);
				 
					
			 
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)&&$_ti->DocCanConfirmPrice($id, $rss)){
			 
				$_ti->Edit($id,array('note_is_actual'=>1));
				
				$log->PutEntry($result['id'],'отметил заметку актуальной',NULL,905, NULL, NULL,$bill_id);	
				
			 	
			 
		} 
	}
	
	
	
$acg=new Sched_Group;
	$acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	switch($trust['kind_id']){
		case 2:
			$template='plan/table_2.html';
		break;
		case 3:
			$template='plan/table_3.html';
		break;
		case 4:
			$template='plan/table.html';
		break;
		case 5:
			$template='plan/table_5.html';
		break;
		default:
			$template='plan/table.html';
		break;  
	  }
	
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	 
	
	$ret= $acg->ShowPos(
		$trust['kind_id'], //0
		$template,  //1
		$dec, //2
		$au->user_rights->CheckAccess('w',905), //3
		0, //4
		1000, //5
		false, //6
		true,  //7
		 $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
		      $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20
			  );
	
		
}





elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping1")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights=$_roles->GetFieldsRoles($trust, $result['id']);	
	
	
	if($trust['is_confirmed_done']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',905))){
			 
			if($_ti->DocCanUnconfirmShip($id,$reas)&&($field_rights['can_unconfirm_done'])){
			
				$_ti->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение выполнения записи планировщика',NULL,905, NULL, 'результат звонка: '.$note,$bill_id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)){
			 
			if($_ti->DocCanConfirmShip($id,$reas)&&($field_rights['can_confirm_done'])){
				$_ti->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time(), 'report'=>$note),true,$result);
				
				$log->PutEntry($result['id'],'утвердил выполнение записи планировщика',NULL,905, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
				
				$note=SecStr(iconv('utf-8','windows-1251',$_POST['note']));
				if(strlen($note)>0){
					$_hi=new Sched_HistoryItem;
					 
					
					$params=array();
					$params['sched_id']=$id;
					$params['txt']=$note;
					$params['user_id']=$result['id'];
					$params['pdate']=time();
					
					$code=$_hi->Add($params);
					
					$log->PutEntry($result['id'],'добавлен комментарий к задаче планировщика', NULL,905,NULL, $params['txt'],$id);	
				}
			}
			 
		} 
	}
	
	
		
	$acg=new Sched_TaskGroup;
	$acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	 
	$template='plan/table_1.html';
	 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	$ret= $acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,   $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',905), //14
			  $au->user_rights->CheckAccess('w',905), //15
			  false, //16
			  false, //17
			  $au->user_rights->CheckAccess('w',946) //18
	  ); 

	
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_fulfil")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights=$_roles->GetFieldsRoles($trust, $result['id']);	
	
	
	if($trust['is_fulfiled']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',905))){
			 
			if($_ti->DocCanUnconfirmFulfil($id,$reas)&&($field_rights['can_unconfirm_fulfil'])){
			
				$_ti->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение работы записи планировщика',NULL,905, NULL, 'результат звонка: '.$note,$bill_id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',905)){
			 
			if($_ti->DocCanConfirmFulfil($id,$reas)&&($field_rights['can_confirm_fulfil'])){
				$_ti->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил работу записи планировщика',NULL,905, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		
	$acg=new Sched_TaskGroup;
	$acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	 
	$template='plan/table_1.html';
	 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	$ret= $acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  
	 $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',905), //14
			  $au->user_rights->CheckAccess('w',905), //15
			  false, //16
			  false, //17
			  $au->user_rights->CheckAccess('w',946) //18
	  ); 
	

	
		
}



elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_fulfil_23")){
	$id=abs((int)$_POST['id']);
	  
	$note=SecStr(iconv('utf-8', 'windows-1251', $_POST['note']));
	
		$_ki=new Sched_AbstractItem;
	
		$trust=$_ki->getitembyid($id);
		
	$_res=new Sched_Resolver($trust['kind_id']);
	$_ti=$_res->instance;	
	
	$_si=new UserSItem;
	
	
	 
	 
	if($trust['confirm_done_pdate']==0) $trust['confirm_done_pdate']='-';
	else $trust['confirm_done_pdate']=date("d.m.Y H:i:s",$trust['confirm_done_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_done_id']);
	$trust['confirmed_shipping_name']=$si['name_s'];
	$trust['confirmed_shipping_login']=$si['login'];
	
	$bill_id=$id;
	
	 
	
	if($trust['is_fulfiled']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',916))){
			 
			if($_ti->DocCanUnconfirmFulfil($id,$reas)){
			
				$_ti->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение работы записи планировщика',NULL,905, NULL, 'результат : '.$note,$bill_id);
				
			}
				
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',916)){
			 
			if($_ti->DocCanConfirmFulfil($id,$reas)){
				$_ti->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил работу записи планировщика',NULL,905, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			 
		} 
	}
	
	
		
	$acg=new Sched_Group;
	$acg->setauthresult($result);
	
	$shorter=abs((int)$_POST['shorter']);
	 
	switch($trust['kind_id']){
		case 2:
			$template='plan/table_2.html';
		break;
		case 3:
			$template='plan/table_3.html';
		break;
		case 4:
			$template='plan/table.html';
		break;
		default:
			$template='plan/table.html';
		break;  
	  }
	
	 
	
	

	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	
	//$ret= $acg->ShowPos($trust['kind_id'], $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905),  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905)  ); 
	
	$ret= $acg->ShowPos(
		$trust['kind_id'], //0
		$template,  //1
		$dec, //2
		$au->user_rights->CheckAccess('w',905), //3
		0, //4
		1000, //5
		false, //6
		true,  //7
		 $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
		      $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20

			  );
	
	
	
		
}



elseif(isset($_POST['action'])&&($_POST['action']=="add_city_to_supplier")){
	//добавка города к командировке
	
	$city_id=abs((int)$_POST['city_id']);
	$sm=new SmartyAj;
	
	$_city=new SupplierCityItem;
	
	$city=$_city->GetFullCity($city_id);
	
	$sm->assign('cities', array($city));
	$sm->assign('has_header', false);
	$sm->assign('can_modify', true);
	
	$ret=$sm->fetch('plan/cities_table.html');
	
}

elseif(isset($_POST['action'])&&(($_POST['action']=="add_supplier")||($_POST['action']=="add_supplier_15"))){

	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	
	$si['opf_name']= $opf['name'];
	
	//var_dump($_POST['already_loaded']);
	
	if((is_array($_POST['already_loaded'])&&!in_array( $_POST['supplier_id'], $_POST['already_loaded']))||!is_array($_POST['already_loaded'])){
	
		$sm=new SmartyAj;
		
		$_csg=new SupplierCitiesGroup;
		$csg=$_csg->GetItemsByIdArr($_POST['supplier_id']);
		$si['cities']= $csg;	
		
		//загрузить выбранные контакты
		//contact_ids
		$contact_ids=$_POST['contact_ids'];
		$_sg=new SupplierContactGroup;
		//$si['contacts']=$_sg->GetItemsByIdArr($_POST['supplier_id']);
		$contacts1=$_sg->GetItemsByIdArr($_POST['supplier_id']);
		$contacts=array();
		foreach($contacts1 as $k=>$v){
			if(in_array($v['id'], $contact_ids)) $contacts[]=$v;	
		}
		$si['contacts']=$contacts;
		
			
		$sm->assign('suppliers', array($si));
		$sm->assign('has_header', false);
		$sm->assign('can_modify', true);
		
		
		
		$sm->assign('many', (int)$_POST['many']);	
		
		$sm->assign('can_modify_result',true);	
	
		
		if($_POST['action']=="add_supplier_15") $ret=$sm->fetch('plan/suppliers_15_table.html');
		else $ret=$sm->fetch('plan/suppliers_many_table.html');

	}
}





//подгрузка пол-лей для соисполнителей
elseif(isset($_POST['action'])&&($_POST['action']=="load_user3")){
	
	 
	
	$sched_id=abs((int)$_POST['sched_id']);
	$_bi1=new Sched_AbstractItem;
	$bi1=$_bi1->GetItemById($sched_id);
	
	
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_users'];
	
 
	$_kpg=new Sched_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	
	if(is_array($except_users)&&(count($except_users)>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_users));
	}
	
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				/*($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])	*/
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			 
		  }
		  
		   
		  
		  
		  
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("plan/user3_edit_set.html");
	
	 
	
	


}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_user3")){
	//перенос выбранных позиций  на страницу  
		
	$shed_id=abs((int)$_POST['shed_id']);
	 $complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	$_user=new UserSItem;
	 

	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		
		
		
		$user=$_user->GetItemById($v[0]);
		if($user===false) continue;
		
		 
		$f['id']=$v[0];
		$f['user_id']=$v[0];
		
		 
		
		$f['name_s']=$user['name_s'];
		$f['login']=$user['login'];
		
		$f['is_active']=$user['is_active'];
		
		$f['hash']=md5($v[0]);
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	 
	$ret=$sm->fetch("plan/user3_on_page_set.html");
	
	

}


//подгрузка пол-лей для наблюдателей
elseif(isset($_POST['action'])&&($_POST['action']=="load_user4")){
	
	 
	
	$sched_id=abs((int)$_POST['sched_id']);
	$_bi1=new Sched_AbstractItem;
	$bi1=$_bi1->GetItemById($sched_id);
	
	
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	
	$except_users=$_POST['except_users'];
	
	
 
	$_kpg=new Sched_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	if(is_array($except_users)&&(count($except_users)>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_users));
	}
	
	
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				/*($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])	*/
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			 
		  }
		  
		   
		  
		  
		  
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("plan/user4_edit_set.html");
	
	 
	
	


}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_user4")){
	//перенос выбранных позиций  на страницу  
		
	$shed_id=abs((int)$_POST['shed_id']);
	 $complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	$_user=new UserSItem;
	 

	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		
		
		
		$user=$_user->GetItemById($v[0]);
		if($user===false) continue;
		
		 
		$f['id']=$v[0];
		$f['user_id']=$v[0];
		
		 
		
		$f['name_s']=$user['name_s'];
		$f['login']=$user['login'];
		
		$f['is_active']=$user['is_active'];
		
		$f['hash']=md5($v[0]);
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	 
	$ret=$sm->fetch("plan/user4_on_page_set.html");
	
	

}




//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	//$_acc=new AccItem;
	
	$rg=new SchedNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',905));
	
	
	$ret=$sm->fetch('plan/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	//$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',905)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new SchedNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания к задаче планировщика', NULL,905, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	//$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',905)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SchedNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания к задаче планировщика', NULL,905,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	//$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',905)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SchedNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания к задаче планировщика', NULL,905,NULL,NULL,$user_id);
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="toggle_subs")){
	$id=abs((int)$_POST['id']);
	 
	 
	
	
	$acg=new Sched_TaskGroup;
	
 $acg->setauthresult($result);
	 
			$template='plan/table_1.html';
	 
	
	
	
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.task_id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	$ret= $acg->ShowPos(1, $template,  $dec, $au->user_rights->CheckAccess('w',905), 0, 1000, false, true,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905),$au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), false,   true); 
	
		
}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_price_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.date("d.m.Y H:i:s",time());	
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_comment")){
	$id=abs((int)$_POST['id']);
	
	$_hi=new Sched_HistoryItem; $_hg=new Sched_HistoryGroup; $_dsi=new DocStatusItem; 
	$_file=new Sched_HistoryFileItem;
	
	$_sch=new Sched_TaskItem;
	$sch=$_sch->GetItemById($id);
	$count_hi=$_hg->CountHistory($id);
	
	
	$params=array();
	$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	$params['user_id']=$result['id'];
	$params['pdate']=time();
	
	$code=$_hi->Add($params);
	
	$log->PutEntry($result['id'],'добавлен комментарий к задаче планировщика', NULL,905,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$code,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию к задаче планировщика', NULL,905,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		
		$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", $files_client[$k])), $_file->GetStoragePath().$file_server);
		
		$contents='';
		
		try {
    		$contents=$_ct->GetContents();
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
		
		$_file->Edit($file_id, array('text_contents'=>SecStr($contents)));
	}
	
	
	
	//отправить сообщения всем имеющим права 922 участникам задачи (кроме автора)
		
		
	$users_to_send=array();
	$sql='select * from user where is_active=1 and id<>"'.$params['user_id'].'" and id in( select distinct user_id from sched_task_users where  sched_id="'.$params['sched_id'].'") and id in(select distinct user_id from user_rights where right_id=2 and object_id=922)';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	
	
	$users_to_send=array();
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		
		$users_to_send[]=$f;
	}
	$topic='Новый комментарий в задаче GYDEX.Планировщик';
	$_mi=new MessageItem;  
	
	$_fi=new SchedFileItem;
	$_user_item=new UserSItem;
	if(count($users_to_send)>0){
		$_item=new Sched_TaskItem();
		$item=$_item->GetItemById($params['sched_id']);	
	}
	foreach($users_to_send as $k1=>$user){
		
		$txt='<div>';
		$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
		$txt.=' </div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='Уважаемый(ая) '.$user['name_s'].'!';
		$txt.='</div>';
		$txt.='<div>&nbsp;</div>';
		
		
		$txt.='<div>';
		$txt.='<strong>В доступной Вам задаче GYDEX.Планировщик </strong>';
		 
		 
		$txt.='<strong><a href="ed_sched_task.php?action=1&id='.$params['sched_id'].'#lenta_commment_'.$code.'" target="_blank">'.$_item->ConstructFullName($params['sched_id'], $item).'</a></strong>';
		if($item['pdate_beg']!="") $txt.=',<strong> крайний срок:</strong> <em>'.DateFromYmd($item['pdate_beg']).' '.$item['ptime_beg'].'</em>';
		$txt.=', <strong>ваша роль:</strong> <em>';
		
		//найдем роли...
		$sql2=' select distinct k.kind_id, p.name 
		from sched_task_users as k
		inner join sched_task_users_kind as p on p.id=k.kind_id
		where k.sched_id="'.$params['sched_id'].'" and k.user_id="'.$user['id'].'"
		order by k.kind_id';
		
		//echo $sql2;
		
		$set2=new mysqlset($sql2);
		$rs2=$set2->GetResult();
		$rc2=$set2->GetResultNumRows();
		
		
		$roles=array();
		for($k=0; $k<$rc2; $k++){
			$h=mysqli_fetch_array($rs2);
			$roles[]=$h['name'];
			
			
			
		}
		
		$txt.=implode(', ', $roles);
		
		$from_user=$_user_item->GetItemById($params['user_id']);
		
		
		$txt.='</em><strong>, появился новый комментарий  от пользователя '.SecStr($from_user['name_s']).':</strong></div> ';
		
		$txt.=' <div>&nbsp;</div>';
		
		$txt.=$params['txt'];
		$txt.=' <div>&nbsp;</div>';

	//	$txt.='<div>Для просмотра комментария просьба перейти в карту задачи по ссылке.</div>';
		//найдем файлы
		 
		$sql2=' select id, orig_name
		from  sched_history_file
		where history_id="'.$code.'" 
		order by orig_name';
		
		//echo $sql2;
		
		$set2=new mysqlset($sql2);
		$rs2=$set2->GetResult();
		$files_count=$set2->GetResultNumRows();
		
		//_file
		$files=array();
		for($k=0; $k<$files_count; $k++){
			$h=mysqli_fetch_array($rs2);
			$files[]='<a href="sched_lenta_file.html?id='.$h['id'].'" class="sched_report_file_link" target="_blank">'.$h['orig_name'].'</a>';
			
			
		}
		
		if($files_count>0){
			$txt.='<div>К комментарию прикреплено '.$files_count.' файлов: '.implode(', ',$files).'.</div>';
		}
		
		$txt.='<div>&nbsp;</div>';
	
		$txt.='<div>';
		$txt.='C уважением, программа "'.SITETITLE.'".';
		$txt.='</div>';
		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}
	
	
	

	

	
	//
	if(($sch['status_id']==23)&&($count_hi==0)){
		$_sch->Edit($id,array('status_id'=>24));
					  
			$log->PutEntry($result['id'],'начал выполнение задачи',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById(24);
			$log->PutEntry($result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$id);	
		
	}
	
	
	//вывести что получилось
	$_hr=new Sched_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$code, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'plan/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',906),
			 $au->user_rights->CheckAccess('w',907));
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new Sched_HistoryItem;
	$_file=new Sched_HistoryFileItem;
	
	$params=array();
	//$params['sched_id']=$id;
	$params['txt']=SecStr(iconv("utf-8","windows-1251",$_POST['comment']));
	//$params['user_id']=$result['id'];
	//$params['pdate']=time();
	
	 $_hi->Edit($comment_id, $params);
	
	$log->PutEntry($result['id'],'редактирован комментарий к задаче планировщика', NULL,905,NULL, $params['txt'],$id);
	
	
	$files_server=$_POST['files_server'];
	$files_client=$_POST['files_client'];
	
	foreach($files_server as $k=>$file_server){
		$file_id=$_file->Add(array(
			'history_id'=>$comment_id,
			'filename'=>SecStr(iconv("utf-8","windows-1251",$file_server)),
			'orig_name'=>SecStr(iconv("utf-8","windows-1251",$files_client[$k])),
		));	
		
		$log->PutEntry($result['id'],'прикреплен файл к комментарию к задаче планировщика', NULL,905,NULL, 'Комментарий '.$params['txt'].',  файл '.SecStr(iconv("utf-8","windows-1251",$files_client[$k])),$id);
		
		$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", $files_client[$k])), $_file->GetStoragePath().$file_server);
		
		$contents='';
		
		try {
    		$contents=$_ct->GetContents();
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
		
		$_file->Edit($file_id, array('text_contents'=>SecStr($contents)));
	}
	
	//вывести что получилось
	$_hr=new Sched_HistoryGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlEntry('o.id',$comment_id, SqlEntry::E));
	
	$ret=$_hr->ShowHistory($id, 'plan/lenta.html', $dec, true, false, true,  $result,
			 $au->user_rights->CheckAccess('w',906),
			 $au->user_rights->CheckAccess('w',907));
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_comment")){
	$id=abs((int)$_POST['id']);
	$comment_id=abs((int)$_POST['comment_id']);
	
	$_hi=new Sched_HistoryItem;
	$hi=$_hi->GetItemById($comment_id);
	
	if($hi['is_shown']==1){
		$_hi->Edit($comment_id, array('is_shown'=>0));
		$log->PutEntry($result['id'],'скрыт комментарий к задаче планировщика', NULL,905,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=0;
	}else{
		$_hi->Edit($comment_id, array('is_shown'=>1));
		$log->PutEntry($result['id'],'показан комментарий к задаче планировщика', NULL,905,NULL, 'Комментарий '.$hi['txt'].' ',$id);
		$ret=1;
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	
	$id=abs((int)$_POST['id']);
	
	
	
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
		/*(select "0" as kind, name as name_s, "" as login, position as position_s, id, "" as email_s
			from supplier_contact
			where ( supplier_id in(select distinct supplier_id from sched_suppliers where  sched_id="'.$id.'") or  supplier_id in(select distinct supplier_id from sched_contacts where  sched_id="'.$id.'"))
			and id in(select distinct contact_id from supplier_contact_data where kind_id=5)
			)
		UNION ALL*/
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
	$ret=$sm->fetch('plan/pdf_addresses.html');

}

elseif(isset($_POST['action'])&&($_POST['action']=="has_files")){
//есть ли файлы по записи план-ка?
	$count_of_files=0;	
	$id=abs((int)$_POST['id']);
	
	$sql='select count(*) from sched_file where bill_id="'.$id.'" ';
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	
	$count_of_files+=(int)$f[0];
	
	
	$sql='select count(*) from sched_history_file where history_id in(select id from sched_history where sched_id="'.$id.'" )';
	
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	
	$f=mysqli_fetch_array($rs);
	$count_of_files+=(int)$f[0];
	
	$ret=$count_of_files;
}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_manager")){
	$_si=new UserSItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	 
 
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}



//проверка доступности контрагента сотруднику
elseif(isset($_POST['action'])&&($_POST['action']=="check_managers_to_supplier")){
	//0 - все ОК
	//не 0 - нет доступа
	$supplier_ids=$_POST['supplier_ids'];
		
	$_s_to=new SupplierToUser;
	$manager_id=abs((int)$_POST['manager_id']);
	
	$res=true; $output=array();
	foreach($supplier_ids as $k=>$supplier_id){
		$supplier_id=abs((int)$supplier_id);
		
	
		
		$data=$_s_to->GetExtendedViewedUserIdsArr($manager_id, $result);
		if(!in_array($supplier_id, $data['sector_ids'])) {
			$res=$res&&false;
			$output[]=$supplier_id;
		}
		/*echo $manager_id.' '; echo $supplier_id.' ';
		var_dump($data['sector_ids']);*/
	}
	
	
	if($res) $ret= 0;
	else $ret=implode(';',$output);
	
	 
}


//автонахождение города офиса органинзации
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_office_city")){
	$_fa=new FaItem;
	$_city=new SupplierCityItem;
	
	$id=0;
	$fa=$_fa->GetItemByFields(array('user_id'=>$result['org_id'],'form_id'=>1));
	
	if($fa!==false){
		$city=$_city->GetItemById($fa['city_id']);
		if($city!==false) $id=$city['id'];	
	}
	
	$ret=$id;
}

//добавление нового срока задачи
elseif(isset($_POST['action'])&&($_POST['action']=="apply_srok")){
	$_dem=new Sched_AbstractItem; $log=new ActionLog;
	
	
	$id=abs((int)$_POST['id']);
	$res=abs((int)$_POST['res']);
	
	$task=$_dem->getitembyid($id);
	
	$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights=$_roles->GetFieldsRoles($task, $result['id']);
	
	
	
	if($field_rights['can_apply_srok']){
				
		
		$comment=SecStr(iconv('utf-8', 'windows-1251', $_POST['comment']));
		 
		$cmt=SecStr('старый срок: '.DatefromYMD($task['pdate_beg']).' '.$task['ptime_beg'].', новый срок: '.$_POST['new_pdate_beg'].' '.SecStr($_POST['new_ptime_beg_h']).':'.SecStr($_POST['new_ptime_beg_m']).':00');
		if(strlen($comment)>0) $cmt.=', комментарий: '.$comment;
		
		if($res==1){
			$_dem->Edit($id,array('is_waiting_new_pdate'=>0, 'pdate_beg'=>date('Y-m-d', DateFromdmY($_POST['new_pdate_beg'])), 'ptime_beg'=>SecStr($_POST['new_ptime_beg_h']).':'.SecStr($_POST['new_ptime_beg_m']).':00'));
			 
			
			
			$log->PutEntry($result['id'],'утвердил заявку на смену крайнего срока задачи',NULL,905, NULL, $cmt,  $id);
			
			
			//создадим запись в ленту
			 
				$_len=new Sched_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=  SecStr('<div>Автоматический комментарий: сотрудник  '.$result['name_s'].' утвердил заявку на смену крайнего срока задачи, '.$cmt.'</div>');
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			 
			
			
		}elseif($res==0){
			$_dem->Edit($id,array('is_waiting_new_pdate'=>0));
			
			
			$log->PutEntry($result['id'],'не утвердил заявку на смену крайнего срока задачи',NULL,905, NULL, $cmt,  $id);
			
			
			//создадим запись в ленту
			 
				$_len=new Sched_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=  SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' не утвердил заявку на смену крайнего срока задачи, '.$cmt.'</div>');
				$len_params['user_id']=0;
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			 
			
		}
		
	}
	
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>