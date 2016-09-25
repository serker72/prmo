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

require_once('../classes/acc_in_posgroup.php');

require_once('../classes/acc_in_item.php');
require_once('../classes/acc_in_group.php');

require_once('../classes/acc_in_view.class.php');



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


$ret='';
if(isset($_POST['action'])&&(($_POST['action']=="calc_new_total")||($_POST['action']=="calc_new_nds"))){
	//подсчет нового итого
		$supplier_id=abs((int)$_POST['supplier_id']);
	$_supplier=new SupplierItem;
	$supplier=$_supplier->GetItemById($supplier_id);
	
	
	
	$alls=array();
	$complex_positions=$_POST['complex_positions'];
	
	//print_r($complex_positions);
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
			
			
			$f['price_pm']=$v[10];			
			
		}else $f['price_pm']=$f['price'];
		
		//st-t'
		$f['cost']=$f['price']*$f['quantity'];
		
		
		//vsego
		//$f['total']=$f['price_pm']*$f['quantity'];
		$f['total']=$v[11];
		
		
		$alls[]=$f;
		
		/*echo '<pre>';
		print_r($f);
		echo '</pre>';*/
		
	}
	
	
	$_bpf=new BillPosPMFormer;
	if($_POST['action']=="calc_new_total") $ret=$_bpf->CalcCost($alls);
	
	if($_POST['action']=="calc_new_nds") $ret=$_bpf->CalcNDS($alls,true,$supplier_id);
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
	
	$_acc=new AccInItem;
	
	$rg=new AccNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',666), $au->user_rights->CheckAccess('w',667), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',665));
	
	
	$ret=$sm->fetch('acc_in/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_acc=new AccInItem;
	
	if(!$au->user_rights->CheckAccess('w',665)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new AccNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания поступлению товара', NULL,665, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_acc=new AccInItem;
	
	if(!$au->user_rights->CheckAccess('w',665)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new AccNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по поступлению товара', NULL,665,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_acc=new AccInItem;
	
	if(!$au->user_rights->CheckAccess('w',665)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new AccNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по поступлению товара', NULL,665,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new AccInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	$si=$_si->getitembyid($trust['user_confirm_id']);
	$trust['confirmed_price_name']=$si['name_s'];
	$trust['confirmed_price_login']=$si['login'];
	
	$bill_id=$trust['bill_id'];
	
	if($trust['is_confirmed']==1){
		if($_ti->DocCanUnConfirm($id,$reason)){
		
		//есть права:
		$can=true;
		if(!$_ti->ParentBillHasPms($trust['id'], $trust)||($trust['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',672);
		else $can=$can&&$au->user_rights->CheckAccess('w',722);
				
		  if($can){
			  if($trust['status_id']==5){
				  $_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение поступления товара',NULL,613, NULL, NULL,$bill_id);
				  
				  
				  $log->PutEntry($result['id'],'снял утверждение поступления товара',NULL,722, NULL, NULL,$id);
				  
				  
					  
			  }
		  }else{
			  //нет прав	
		  }
		}
	}else{
		
		
		//есть права
		if($_ti->DocCanConfirm($id,$reason)){
		
				
		
		  if($au->user_rights->CheckAccess('w',671)||$au->user_rights->CheckAccess('w',96)){
			 if($trust['status_id']==4){
				  $_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил поступление товара',NULL,613, NULL, NULL,$bill_id);	
				  
				     $log->PutEntry($result['id'],'утвердил поступление товара',NULL,671, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
		}else{
			//echo $reason; die();	
		}
	}
	
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) {
		$template='acc_in/all_accs_list.html';
		$prefix='_1';
	}else{
		 $template='acc_in/acc_list.html';
		 $prefix='_acc';
	}
	
	
	$acg=new AccInGroup;
	$acg->SetAuthResult($result);
	$acg->prefix=$prefix;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowAllPos(
		$template, //0
		$dec, //1
		$au->user_rights->CheckAccess('w',664)||$au->user_rights->CheckAccess('w',673), //2
		$au->user_rights->CheckAccess('w',674), //3
		0, //4
		100, //5
		$au->user_rights->CheckAccess('w',671),   //6
		$au->user_rights->CheckAccess('w',96),  //7
		false, //8
		true,  //9
		$au->user_rights->CheckAccess('w',675),//10
		NULL, //11
		$au->user_rights->CheckAccess('w',672), //12
		$au->user_rights->CheckAccess('w',673), //13
		false, //14
	NULL, //15
	$au->user_rights->CheckAccess('w',930) //16
		);
		
}//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0){
		 $template='acc_in/all_accs_list.html';
		 $prefix='_1';
	}else{
		// echo 'zzzzz';
		  $prefix='_acc';
		 $template='acc_in/acc_list.html';
	}
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new AccInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==4)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',674)){
			$_ti->Edit($id,array('status_id'=>6, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);	
			
			$stat=$_stat->GetItemById(6);
			$log->PutEntry($result['id'],'аннулирование поступления',NULL,613,NULL,'поступление № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);
		 
			
			$log->PutEntry($result['id'],'аннулирование поступления',NULL,674,NULL,'поступление № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new AccNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был аннулирован пользователем '.SecStr($result['name_s']).' ('.$result['login'].'), причина: '.$note,
				'is_auto'=>1,
				'pdate'=>time()
					));	
		}
	}elseif($trust['status_id']==6){
		//разудаление
		if($au->user_rights->CheckAccess('w',675)){
			$_ti->Edit($id,array('status_id'=>4, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);	
			
			$stat=$_stat->GetItemById(4);
			$log->PutEntry($result['id'],'восстановление поступления',NULL,613,NULL,'поступление № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);	
		 
			
			$log->PutEntry($result['id'],'восстановление поступления',NULL,675,NULL,'поступление № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);
			
			//внести примечание
			$_ni=new AccNotesItem;
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
		$acg=new AccInGroup;
		$acg->prefix=$prefix;
		
		$acg->SetAuthResult($result);
		$dec=new  DBDecorator;
		
		$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		
		$ret=$acg->ShowAllPos($template,$dec, $au->user_rights->CheckAccess('w',664)||$au->user_rights->CheckAccess('w',673), $au->user_rights->CheckAccess('w',674), 0,100,  $au->user_rights->CheckAccess('w',671),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',675),NULL,$au->user_rights->CheckAccess('w',672), $au->user_rights->CheckAccess('w',673),
		false, //14
	NULL, //15
	$au->user_rights->CheckAccess('w',930) );
			
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',674);
		if(!$au->user_rights->CheckAccess('w',674)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',675);
			if(!$au->user_rights->CheckAccess('w',675)) $reason='недостаточно прав для данной операции';
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('acc',$editing_user);
		$ret=$sm->fetch('acc_in/toggle_annul_card.html');	
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_shs_pos")){
	//dostup
	$_kr=new AccReports;
	
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	
		$pl_position_id=abs((int)$_POST['pl_position_id']);
	$pl_discount_id=abs((int)$_POST['pl_discount_id']);
	$pl_discount_value=abs((int)$_POST['pl_discount_value']);
	$pl_discount_rub_or_percent=abs((int)$_POST['pl_discount_rub_or_percent']);
	
	$out_bill_id=abs((int)$_POST['out_bill_id']);
	
	$ret=$_kr->InSh($id,$bill_id,'acc_in/in_shs.html',$result['org_id'],true,$sh_i_id, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent, $out_bill_id );
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_acc_pos")){
	//dostup
	$_kr=new AccReports;
	
	$id=abs((int)$_POST['id']);
	$except_id=abs((int)$_POST['except_id']);
	$bill_id=abs((int)$_POST['bill_id']);
	$sh_i_id=abs((int)$_POST['sh_i_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	$sector_id=abs((int)$_POST['sector_id']);
	$komplekt_ved_id=abs((int)$_POST['komplekt_ved_id']);
	
	
	$ret=$_kr->InAcc($id, $except_id, $bill_id,'acc_in/in_accs.html',$result['org_id'],true,$sh_i_id,$storage_id,$sector_id,$komplekt_ved_id);
	
	

}
elseif(isset($_POST['action'])&&($_POST['action']=="has_nakl_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',672))&&($trust['is_confirmed']==1)&&($trust['has_nakl']==1)){
			
				$_ti->Edit($id,array('has_nakl'=>0, 'has_nakl_confirm_user_id'=>$result['id'], 'has_nakl_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала товарной накладной',NULL,672, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',672))&&($trust['is_confirmed']==1)&&($trust['has_nakl']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_nakl'=>1, 'has_nakl_confirm_user_id'=>$result['id'], 'has_nakl_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала товарной накладной',NULL,671, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_fakt_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',672))&&($trust['is_confirmed']==1)&&($trust['has_fakt']==1)){
			
				$_ti->Edit($id,array('has_fakt'=>0, 'has_fakt_confirm_user_id'=>$result['id'], 'has_fakt_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала товарной накладной',NULL,672, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',671))&&($trust['is_confirmed']==1)&&($trust['has_fakt']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_fakt'=>1, 'has_fakt_confirm_user_id'=>$result['id'], 'has_fakt_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала товарной накладной',NULL,671, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_akt_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',672))&&($trust['is_confirmed']==1)&&($trust['has_akt']==1)){
			
				$_ti->Edit($id,array('has_akt'=>0, 'has_akt_confirm_user_id'=>$result['id'], 'has_akt_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала акта',NULL,672, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',671))&&($trust['is_confirmed']==1)&&($trust['has_akt']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_akt'=>1, 'has_akt_confirm_user_id'=>$result['id'], 'has_akt_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала акта',NULL,671, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccInItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_by_pos")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccInItem;
		
		 
		if(!$_ki->CanConfirmByPositions($id,$rss)) $ret=$rss;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccInItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_binded_docs")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccInItem;
		
		$rr=$_ki->GetBindedDocumentsToUnconfirm($id);
		
		if(strlen($rr)==0) $ret=0;
		else $ret=$rr;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="refuse_to_confirm")){
	
	
		$id=abs((int)$_POST['id']);
		$refuse=abs((int)$_POST['refuse']);
		
		
		$description='отказ от утверждения поступления';
		
		if($refuse==1){
			$description.=' при первом подтверждении ';	
		}else{
			$description.=' при втором подтверждении ';	
		}
		
		//$_ki=new AccItem;
		
		$log->PutEntry($result['id'], $description,NULL,671, NULL, NULL,$id);	
					  	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_akt")){
	
	
		$id=abs((int)$_POST['id']);
		 
		$_acc=new AccInItem;
		if($_acc->HasUsl($id)) $ret=1;
		else $ret=0; 
					  	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_tov")){
	
	
		$id=abs((int)$_POST['id']);
		 
		$_acc=new AccInItem;
		if($_acc->HasTov($id)) $ret=1;
		else $ret=0; 
					  	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_sf")){
	
	
		$id=abs((int)$_POST['id']);
		 
		$_acc=new AccInItem;
		$acc=$_acc->GetItemById($id);
		$_bill=new BillInItem;
		
		
		
		$bill=$_bill->GetItemById($acc['bill_id']);
		
		$_supplier=new SupplierItem;
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		
		if($supplier['is_upr_nalog']==1) $ret=0;
		else $ret=1;
		
		 
	
	/*	if($_acc->HasTov($id)) $ret=1;
		else $ret=0; 
		*/			  	
	
}



//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new  Acc_In_ViewGroup;
	$_view=new acc_In_ViewItem;
	
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
	$_views=new Acc_In_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>