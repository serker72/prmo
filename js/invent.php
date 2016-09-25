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

require_once('../classes/invitem.php');
require_once('../classes/invgroup.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/invnotesgroup.php');
require_once('../classes/invnotesitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');
require_once('../classes/invpreparegroup.php');
require_once('../classes/invreports.php');

require_once('../classes/user_s_item.php');

require_once('../classes/inventory_view.class.php');


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
	
	
	$current_id=abs((int)$_POST['current_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	
	
	$pdate=datefromdmy($_POST['pdate']);
	
	$_ipg=new InvPrepareGroup;
	$pos=$_ipg->GetItemsByDateStorSec($pdate, 0, $sector_id, $result['org_id'], $current_id);
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$pos);
	
	
	$sm->assign('can_modify',true);
		
	$sm->assign('can_del_positions',false); //$au->user_rights->CheckAccess('w',325)); 
	
	
	$cannot_select_positions=true;
	
	$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',324);
	
	
	
	$sm->assign('cannot_select_positions', $cannot_select_positions);
	
	//echo date('d.m.Y',$pdate);
	$ret=$sm->fetch('inv/positions_on_page_rows.html');	
}

//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new InvNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',346), $au->user_rights->CheckAccess('w',346),$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',327));
	
	
	$ret=$sm->fetch('inv/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',327)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new InvNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по распоряжению на инвентаризацию', NULL,327, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',327)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new InvNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по распоряжению на инвентаризацию', NULL,327,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',327)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new InvNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по распоряжению на инвентаризацию', NULL,327,NULL,NULL,$user_id);
	
}
//работа с датами
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_ethalon_pdate_payment_contract")){
	
	$_si=new SupplierItem;
	$_bd=new BillDates;
	
	$supplier=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	if($supplier!==false){
		$ethalon=$_bd->FindEthalon(datefromdmy($_POST['pdate_shipping_plan']),$supplier['contract_prolongation'], $supplier['contract_prolongation_mode']);
		
		$ret=$ethalon;  //date("d.m.Y",$ethalon);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="retrieve_ethalon_full_pdate_payment_contract")){
	
	$_si=new SupplierItem;
	$_bd=new BillDates;
	
	$supplier=$_si->GetItemById(abs((int)$_POST['supplier_id']));
	if($supplier!==false){
		$ethalon=$_bd->FindEthalon(datefromdmy($_POST['pdate_shipping_plan']),$supplier['contract_prolongation'], $supplier['contract_prolongation_mode']);
		
		$ret=date("d.m.Y",$ethalon);
		
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="compare_pdate_payment")){
	$ethalon_pdate_payment_contract=abs((int)$_POST['ethalon_pdate_payment_contract']);
	$pdate_payment_contract=datefromdmy($_POST['pdate_payment_contract']);
	
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
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$flag_to_payments=abs((int)$_POST['flag_to_payments']);
	
	$_ti=new InvItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_name']=$si['name_s'];
	$trust['confirmed_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',332))){
			if($trust['status_id']==2){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения',NULL,332, NULL, NULL,$bill_id);
				//$_ti->FreeBindedPayments($bill_id);
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',331)){
			if(($_ti->CheckInventoryPdate($trust['inventory_pdate'],$trust['sector_id'], $rss,$trust['id']))&&($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнение',NULL,331, NULL, NULL,$bill_id);	
				
				//if($flag_to_payments==1) $_ti->BindPayments($bill_id,$result['org_id']);		
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='inv/invs_list.html';
	else $template='invs/bills_list_komplekt.html';
	
	
	$acg=new InvGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',322),  $au->user_rights->CheckAccess('w',326)||$au->user_rights->CheckAccess('w',335), $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',331), $au->user_rights->CheckAccess('w',331),false, true,$au->user_rights->CheckAccess('w',337),$limited_sector, $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',332), $au->user_rights->CheckAccess('w',334), $au->user_rights->CheckAccess('w',335));
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_inv")){
	$id=abs((int)$_POST['id']);
	$_ti=new InvItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_inv_pdate']==0) $trust['confirm_inv_pdate']='-';
	else $trust['confirm_inv_pdate']=date("d.m.Y H:i:s",$trust['confirm_inv_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_inv_id']);
	$trust['confirmed_inv_name']=$si['name_s'];
	$trust['confirmed_inv_login']=$si['login'];
	
	$bill_id=$id;
	
	if($trust['is_confirmed_inv']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',334))){
			if($trust['status_id']==16){
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_inv'=>0, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение коррекции',NULL,334, NULL, NULL,$bill_id);
				
			}
				
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',333)){
			if($trust['status_id']==2){
				$_ti->Edit($id,array('is_confirmed_inv'=>1, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил коррекцию',NULL,333, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='inv/invs_list.html';
	else $template='inv/bills_list_komplekt.html';
	
	
	$acg=new InvGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',322),  $au->user_rights->CheckAccess('w',326)||$au->user_rights->CheckAccess('w',335), $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',331), $au->user_rights->CheckAccess('w',331),false, true,$au->user_rights->CheckAccess('w',337),$limited_sector, $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',332), $au->user_rights->CheckAccess('w',334), $au->user_rights->CheckAccess('w',335));
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	
	$_ti=new InvItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',336)){
			$_ti->Edit($id,array('status_id'=>3));
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование распоряжения на инвентаризацию',NULL,336,NULL,'распоряжение на инвентаризацию № '.$trust['id'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);
			
			//внести примечание
			$_ni=new InvNotesItem;
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
		if($au->user_rights->CheckAccess('w',337)){
			$_ti->Edit($id,array('status_id'=>1));
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление распоряжения на инвентаризацию',NULL,337,NULL,'распоряжение на инвентаризацию № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//внести примечание
			$_ni=new InvNotesItem;
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
	  if($shorter==0) $template='inv/invs_list.html';
	  else $template='inv/bills_list_komplekt.html';
	  
	  
	  $acg=new InvGroup;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  //if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	  
	 
	  $ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',322),  $au->user_rights->CheckAccess('w',326)||$au->user_rights->CheckAccess('w',335), $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',331), $au->user_rights->CheckAccess('w',331),false, true,$au->user_rights->CheckAccess('w',337),$limited_sector, $au->user_rights->CheckAccess('w',336), $au->user_rights->CheckAccess('w',332), $au->user_rights->CheckAccess('w',334), $au->user_rights->CheckAccess('w',335));
	  
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',336);
		if(!$au->user_rights->CheckAccess('w',336)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('inv/toggle_annul_card.html');		
	}
		
		
}elseif(isset($_POST['action'])&&(($_POST['action']=="find_pos")||($_POST['action']=="pcg_find_pos"))){
	//получим список позиций по фильтру
	$_pg=new PosGroup();
	
	$dec=new DBDecorator;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['qry']));
	$group_id=abs((int)$_POST['group_id']);
	
	$except_ids=$_POST['except_ids'];
	
	
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
	
	
	//не включать позиции, которые уже включены:
	if(is_array($except_ids)&&(count($except_ids)>0)){
			$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_ids));	
	}
	
	
	
	if($_POST['action']=="find_pos") $ret=$_pg->ShowPos('komplekt/pos_in_filter.html', $dec,0,100,false,false,true);
	else {
			
		$ret=$_pg->ShowPos('komplekt/pos_pcg_in_filter.html', $dec,0,1000000,false,false,true,false,false,false,false,false,abs((float)$_POST['kol']));
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="put_to_form")){
	$_positem=new PosItem;
	
	$_pdi=new PosDimItem;
	
	$_pm=new PositionsOnAssortimentMod;
		
	
	//$tempid=time();
	$res=array();
	foreach($_POST['ids'] as $k=>$v){
		//$ret.=$v.'=>'.$_POST['kols'][$k].'<br>';
		$f=array();
		
		
		$positem=$_positem->GetItemById($v);
		$f['id']=$v;
		$f['pl_position_id']=$v;
		$f['position_id']=$positem['position_id'];
		
		$f['position_name']=$positem['name'];
		
		$pdi=$_pdi->GetItemById($positem['dimension_id']);
		$f['dim_name']=$pdi['name'];
		$f['dimension_id']=$positem['dimension_id'];
		$f['quantity_as_is']=0;
		$f['quantity_fact']=$_POST['kols'][$k];
		$f['quantity_initial']=$_POST['kols'][$k];
		
		$f['quantity_by_program']=$f['quantity_as_is'];
			$f['in_acc']=0;
			$f['in_wf']=0;
		
		
		
		$res[]=$f;
		
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$res);
	$sm->assign('can_modify',true);
		
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',325)); 
	
	
	$cannot_select_positions=true;
	
	$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',324);
	
	
	
	$sm->assign('cannot_select_positions', $cannot_select_positions);
	
	$ret.=$sm->fetch('inv/positions_on_page_rows.html');
}elseif(isset($_POST['action'])&&($_POST['action']=="find_acc_pos")){
	//dostup
	$_kr=new InvReports;
	
	$id=abs((int)$_POST['position_id']);
	 
	
	$inventory_id=abs((int)$_POST['inventory_id']);
	
	$ret=$_kr->InAcc($id,  'inv/in_accs_in.html',$result['org_id'],true, $inventory_id);
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_wf_pos")){
	//dostup
	$_kr=new InvReports;
	
	$id=abs((int)$_POST['position_id']);
	 
	$inventory_id=abs((int)$_POST['inventory_id']);
	
	$ret=$_kr->InWf($id, 'inv/in_accs.html',$result['org_id'],true, $inventory_id);
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="check_inventory_pdate")){
	//dostup
	$_kr=new InvItem;
	
	$id=abs((int)$_POST['id']);
		$sector_id=abs((int)$_POST['sector_id']);
	
	$pdate=DateFromDmy($_POST['pdate']);
	
	//$ret=$_kr->InWf($id,'inv/in_wf.html',$result['org_id'],true, $inventory_id);
	$res=$_kr->CheckInventoryPdate($pdate, $sector_id, $rss,$id);
	
	if($res===true) $ret=0;
	else{
		$ret=$rss;	
	}
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="HasNotDifference")){
	//dostup
	$_kr=new InvItem;
	
	$id=abs((int)$_POST['id']);
	
	
	$test=$_kr->sync->HasNotDifference($id, $rss);
	
	if($test===true) $ret=0;
	else{
		$ret=$rss;	
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvItem;
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
		
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fill")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fill")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvItem;
		
		
		if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
		
}



//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Inventory_ViewGroup;
	$_view=new Inventory_ViewItem;
	
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
	$_views=new Inventory_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>