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

require_once('../classes/komplitem.php');


require_once('../classes/komplposgroup.php');
require_once('../classes/billpospmformer.php');

require_once('../classes/billposgroup.php');

require_once('../classes/maxformer.php');


require_once('../classes/trust_notesgroup.php');
require_once('../classes/trust_notesitem.php');
require_once('../classes/trust_item.php');
require_once('../classes/trust_group.php');
require_once('../classes/user_s_item.php');


require_once('../classes/trust_positem.php');
require_once('../classes/trust_prepare.php');

require_once('../classes/suppliercontactgroup.php');

require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/usercontactdatagroup.php');

require_once('../classes/trust_view.class.php');

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
	//вывод позиций вх. счета для распоряжения
	
	$_bi=new BillInItem;
	
	$except_id=abs((int)$_POST['trust_id']);
	
	$bill_id=abs((int)$_POST['bill_id']);
	$selected_positions=$_POST['selected_positions'];
	$selected_quantities=$_POST['selected_quantities'];
	$selected_bill_ids=$_POST['selected_bill_ids'];
	
	$another_bill_ids=$_POST['another_bill_ids'];
	
	
	
	
	$has_another_bills=abs((int)$_POST['has_another_bills']);
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new TrustPrepare; // BillPosGroup;
	$_mf=new MaxFormer;
	$arr=array();  
	$kkeys=array();  $kkeys[]=$bill_id;
	if(count($another_bill_ids)>0)  foreach($another_bill_ids as $k=>$v) {
		 $kkeys[]=$v;	
	}
	
	
	//позиции в др. доверенностях
	$intrust=array();
	$_tpi=new TrustPosItem;
	
	foreach($kkeys as $kk=>$vv){
	  $alls=$_kpg->GetItemsByIdArr($vv); //$bill_id);
	
	  foreach($alls as $k=>$v){
		  //$ret.=$v;
			//print_r($v);		 
		  
		
		  
		  $index=FindIndex2($v['position_id'], $v['bill_id'], $selected_positions, $selected_bill_ids);
		  
		   if($index>-1){
			  
			  $v['quantity']=$selected_quantities[$index];
			  
		  
			  
		  }else{
			  $v['quantity']=0;
			  
			  
		  }
		  
		  //$v['bill_id']=$selected_bill_ids[$k];
		  
		 
		  $v['bill_id']=$vv;
		  $bill=$_bi->GetItemById($v['bill_id']);
		  $v['bill_code']=$bill['code'];
		  $v['bill_pdate']=date("d.m.Y",$bill['pdate']);
		  
		  
		  $v['max_quantity']=$_mf->MaxForTrust( $vv, $v['position_id'],$except_id); 			
		 // $v['quantity']=0; //$v['max_quantity'];
		  
		  if($v['max_quantity']>0) $arr[]=$v;
		  
		  
		  if($v['quantity']>0){
				//найдем позиции в других доверенностях
				// v[position_id], v['bill_id'] , $except_id
				$others=$_tpi->GetOtherPosArr($v['position_id'], $v['bill_id'] , $except_id);
				foreach($others as $ok=>$ov) $intrust[]=$ov;
		  }
		  
	  }
	  
	 // foreach($alls as $k=>$v) $arr[]=$v;
	}
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$arr);
	$sm->assign('intrust',$intrust);
	
	$sm->assign('bill_id',$bill_id);
	//$ret.=implode(', ',$kkeys);
	
	$sm->assign('TRUSTUP',TRUSTUP);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',204));
		$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',206));
		$sm->assign('can_use_other_sup',$au->user_rights->CheckAccess('w',207)); 
	
	$ret.=$sm->fetch("trust/positions_edit_set.html");
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$bill_id=abs((int)$_POST['bill_id']);
	$selected_positions=$_POST['selected_positions'];
	$selected_quantities=$_POST['selected_quantities'];
	$selected_bill_ids=$_POST['selected_bill_ids'];
	$except_id=abs((int)$_POST['trust_id']);
	
	/*$selected_has_pms=$_POST['selected_has_pms'];
	$selected_prices=$_POST['selected_prices'];
	$selected_rub_or_percents=$_POST['selected_rub_or_percents'];
	$selected_plus_or_minuses=$_POST['selected_plus_or_minuses'];
	$selected_values=$_POST['selected_values'];*/
	
	$alls=array();
	
	$_position=new PosItem;
	$_dim=new PosDimItem;
	
	$_bi=new BillItem;
	$_tpi=new TrustPosItem; $intrust=array();
	
	foreach($selected_quantities as $k=>$v){
		$f=array();	
		//$do_add=true;
		if($v<=0) continue;
		$position=$_position->GetItemById($selected_positions[$k]);
		if($position===false) continue;
		
		$f['quantity']=$v;
		$f['id']=$selected_positions[$k];
		
		$f['position_name']=$position['name'];
		$f['dimension_id']=$position['dimension_id'];
		
		$dim=$_dim->GetItemById($f['dimension_id']);
		$f['dim_name']=$dim['name'];
		
		$f['bill_id']=$selected_bill_ids[$k];
		
		$bill=$_bi->GetItemById($f['bill_id']);
		
		$f['bill_code']=$bill['code'];
		$f['bill_pdate']=date("d.m.Y",$bill['pdate']);
		
		
		 if($f['quantity']>0){
				//найдем позиции в других доверенностях
				// v[position_id], v['bill_id'] , $except_id
				$others=$_tpi->GetOtherPosArr($selected_positions[$k], $f['bill_id'] , $except_id);
				foreach($others as $ok=>$ov) $intrust[]=$ov;
		  }
		
		 $f['hash']=md5($f['id'].'_'.$f['bill_id']);
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	$sm->assign('can_modify',true);
	$sm->assign('intrust',$intrust);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',204));
		$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',206));
		$sm->assign('can_use_other_sup',$au->user_rights->CheckAccess('w',207)); 
	
	
	$ret=$sm->fetch("trust/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		
	$bill_id=abs((int)$_POST['bill_id']);
	$selected_positions=$_POST['selected_positions'];
	$selected_quantities=$_POST['selected_quantities'];
	$selected_has_pms=$_POST['selected_has_pms'];
	$selected_prices=$_POST['selected_prices'];
	$selected_rub_or_percents=$_POST['selected_rub_or_percents'];
	$selected_plus_or_minuses=$_POST['selected_plus_or_minuses'];
	$selected_values=$_POST['selected_values'];
	
	$alls=array();
	

	
	foreach($selected_quantities as $k=>$v){
		$f=array();	
	
		if($v<=0) continue;
		
		$f['quantity']=$v;
		$f['id']=$selected_positions[$k];
		

		
		$f['price']=$selected_prices[$k];
		
		//+/-
		$f['has_pm']=$selected_has_pms[$k];
		$f['rub_or_percent']=$selected_rub_or_percents[$k];
		$f['plus_or_minus']=$selected_plus_or_minuses[$k];
		$f['value']=$selected_values[$k];
		
		
		//cena +-
		if($selected_has_pms[$k]==1){
			$slag=0;
			if($f['rub_or_percent']==0){
				$slag=$f['value'];
			}else{
				$slag=$f['price']*$f['value']/100.0;
			}
			
			if($f['plus_or_minus']==1) $slag=-1.0*$slag;
			$f['price_pm']=$f['price']+$slag;
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		$f['total']=$f['price_pm']*$f['quantity'];
		

		$alls[]=$f;
	}
	
	
	
	$_bpf=new BillPosPMFormer;
	if($_POST['action']=="calc_new_total") $ret=$_bpf->CalcCost($alls);
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls);
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new TrustNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,false,false,$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',209));
	
	
	$ret=$sm->fetch('trust/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',209)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new TrustNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания доверенности', NULL,209, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',209)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new TrustNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по доверенности', NULL,209,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',209)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new TrustNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по доверенности', NULL,209,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new TrustItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$trust['bill_id'];
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',211))||$au->user_rights->CheckAccess('w',96)){
			if($_ti->DocCanUnConfirm($id,$rss)){
			if($trust['status_id']==2){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение доверенности',NULL,93, NULL, NULL,$bill_id);
				
				$log->PutEntry($result['id'],'снял утверждение доверенности',NULL,211, NULL, NULL,$id);
				
					
			}
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',210)||$au->user_rights->CheckAccess('w',96)){
			if($_ti->DocCanConfirm($id, $rss)){
			if($trust['status_id']==1){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'утвердил доверенность',NULL,93, NULL, NULL,$bill_id);	
				
				$log->PutEntry($result['id'],'утвердил доверенность',NULL,210, NULL, NULL,$id);	
					
			}
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='trust/all_trusts_list.html';
	else $template='trust/trust_list.html';
	
	
	$acg=new TrustGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	if($shorter==0) $ret=$acg->ShowAllPos($template, $dec, $au->user_rights->CheckAccess('r',208)||$au->user_rights->CheckAccess('r',284), $au->user_rights->CheckAccess('w',212),0, 100,$au->user_rights->CheckAccess('w',210), $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',213), $au->user_rights->CheckAccess('w',284));	
	else $ret=$acg->ShowPos($bill_id,$template, $dec, $au->user_rights->CheckAccess('w',208), $au->user_rights->CheckAccess('w',212), $au->user_rights->CheckAccess('w',210), $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',213));
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='trust/all_trusts_list.html';
	else $template='trust/trust_list.html';
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	$_ti=new TrustItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
		
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',212)){
			$_ti->Edit($id,array('status_id'=>3, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование доверенности',NULL,93,NULL,'доверенность № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);	
			
			$log->PutEntry($result['id'],'аннулирование доверенности',NULL,212,NULL,'доверенность № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new TrustNotesItem;
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
		if($au->user_rights->CheckAccess('w',213)){
			$_ti->Edit($id,array('status_id'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление доверенности',NULL,93,NULL,'доверенность № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);	
			
			$log->PutEntry($result['id'],'восстановление доверенности',NULL,213,NULL,'доверенность № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new TrustNotesItem;
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
	
		$acg=new TrustGroup;
		
		$dec=new  DBDecorator;
		
		$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		
		//$ret=$acg->ShowAllPos($template,$dec, $au->user_rights->CheckAccess('w',93), $au->user_rights->CheckAccess('w',94),0,100,  $au->user_rights->CheckAccess('w',95),  $au->user_rights->CheckAccess('w',96),false,true);
			
		if($shorter==0) $ret=$acg->ShowAllPos($template, $dec, $au->user_rights->CheckAccess('r',208)||$au->user_rights->CheckAccess('r',284), $au->user_rights->CheckAccess('w',212),0, 100,$au->user_rights->CheckAccess('w',210), $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',213));	
		else $ret=$acg->ShowPos($trust['bill_id'],$template, $dec, $au->user_rights->CheckAccess('w',208), $au->user_rights->CheckAccess('w',212), $au->user_rights->CheckAccess('w',210), $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',213));
		
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',212);
		if(!$au->user_rights->CheckAccess('w',212)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('ship',$editing_user);
		$ret=$sm->fetch('trust/toggle_annul_card.html');		
	}

}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new TrustItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new TrustItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_pdf_addresses")){
	//получить список контактов к-та с эл. почтой (ее айди=5)
	//получить список сотр-ков с эл. почтой
	$_sdg=new SupplierContactDataGroup;
	$_udg=new UserContactDataGroup;
	
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
		}//$data=$_udg->GetItemsByIdArr($f['id']);
		
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
	$ret=$sm->fetch('trust/pdf_addresses.html');

}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Trust_ViewGroup;
	$_view=new Trust_ViewItem;
	
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
	$_views=new Trust_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>