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

require_once('../classes/acc_view.class.php');

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
	
	$_acc=new AccItem;
	
	$rg=new AccNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',342), $au->user_rights->CheckAccess('w',351), $result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',236));
	
	
	$ret=$sm->fetch('acc/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',236)){
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
	
	$log->PutEntry($result['id'],'добавил примечания реализации товара', NULL,236, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',236)){
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
	
	$log->PutEntry($result['id'],'редактировал примечания по реализации товара', NULL,236,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_acc=new AccItem;
	
	if(!$au->user_rights->CheckAccess('w',236)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new AccNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по реализации товара', NULL,236,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new AccItem;
	
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
		if(!$_ti->ParentBillHasPms($trust['id'], $trust)||($trust['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',241);
		else $can=$can&&$au->user_rights->CheckAccess('w',721);
				
		  if($can){
			  if($trust['status_id']==5){
				  $_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,93, NULL, NULL,$bill_id);
				  
				  $log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,219, NULL, NULL,$trust['sh_i_id']);
				  
				  $log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,721, NULL, NULL,$id);
				  
				  
					  
			  }
		  }else{
			  //нет прав	
		  }
		}
	}else{
		
		
		//есть права
		if($_ti->DocCanConfirm($id,$reason)){
		
				
		
		  if($au->user_rights->CheckAccess('w',240)||$au->user_rights->CheckAccess('w',96)){
			 if($trust['status_id']==4){
				  $_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил реализации товара',NULL,93, NULL, NULL,$bill_id);	
				  
				   $log->PutEntry($result['id'],'утвердил реализации товара',NULL,219, NULL, NULL,$trust['sh_i_id']);	
				    $log->PutEntry($result['id'],'утвердил реализации товара',NULL,240, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
		}else{
			//echo $reason; die();	
		}
	}
	
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='acc/all_accs_list.html';
	else $template='acc/acc_list.html';
	
	
	$acg=new AccGroup;
	$acg->SetAuthResult($result);
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowAllPos($template,$dec, $au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',242),0,100, $au->user_rights->CheckAccess('w',240),  $au->user_rights->CheckAccess('w',96),false,true, $au->user_rights->CheckAccess('w',243),NULL,$au->user_rights->CheckAccess('w',241),$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',861));
		
}//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='acc/all_accs_list.html';
	else $template='acc/acc_list.html';
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==4)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',242)){
			$_ti->Edit($id,array('status_id'=>6, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);	
			
			$stat=$_stat->GetItemById(6);
			$log->PutEntry($result['id'],'аннулирование реализации',NULL,93,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);
			
			$log->PutEntry($result['id'],'аннулирование реализации',NULL,219,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['sh_i_id']);
			
			$log->PutEntry($result['id'],'аннулирование реализации',NULL,242,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);	
			
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
		if($au->user_rights->CheckAccess('w',243)){
			$_ti->Edit($id,array('status_id'=>4, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']),false,$result);	
			
			$stat=$_stat->GetItemById(4);
			$log->PutEntry($result['id'],'восстановление реализации',NULL,93,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['bill_id']);	
			
			$log->PutEntry($result['id'],'восстановление реализации',NULL,219,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['sh_i_id']);	
			
			$log->PutEntry($result['id'],'восстановление реализации',NULL,243,NULL,'реализация № '.$trust['id'].': установлен статус '.$stat['name'],$trust['id']);
			
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
		$acg=new AccGroup;
		$acg->SetAuthResult($result);
		
		$dec=new  DBDecorator;
		
		$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		
		$ret=$acg->ShowAllPos($template,$dec, $au->user_rights->CheckAccess('w',235)||$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',242), 0,100,  $au->user_rights->CheckAccess('w',240),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',243),NULL,$au->user_rights->CheckAccess('w',241),$au->user_rights->CheckAccess('w',286), $au->user_rights->CheckAccess('w',861));
			
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',242);
		if(!$au->user_rights->CheckAccess('w',242)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',243);
			if(!$au->user_rights->CheckAccess('w',243)) $reason='недостаточно прав для данной операции';
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('acc',$editing_user);
		$ret=$sm->fetch('acc/toggle_annul_card.html');	
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
	$kp_id=abs((int)$_POST['kp_id']);
	
	
	
	
	$ret=$_kr->InSh($id,$bill_id,'acc/in_shs.html',$result['org_id'],true,$sh_i_id, $pl_position_id, $pl_discount_id, $pl_discount_value,  $pl_discount_rub_or_percent, NULL, $kp_id );
	
	
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
	
	
	$ret=$_kr->InAcc($id, $except_id, $bill_id,'acc/in_accs.html',$result['org_id'],true,$sh_i_id,$storage_id,$sector_id,$komplekt_ved_id);
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_nakl_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_nakl']==1)){
			
				$_ti->Edit($id,array('has_nakl'=>0, 'has_nakl_confirm_user_id'=>$result['id'], 'has_nakl_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала товарной накладной',NULL,241, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_nakl']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_nakl'=>1, 'has_nakl_confirm_user_id'=>$result['id'], 'has_nakl_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала товарной накладной',NULL,240, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_fakt_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_fakt']==1)){
			
				$_ti->Edit($id,array('has_fakt'=>0, 'has_fakt_confirm_user_id'=>$result['id'], 'has_fakt_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала товарной накладной',NULL,241, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',240))&&($trust['is_confirmed']==1)&&($trust['has_fakt']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_fakt'=>1, 'has_fakt_confirm_user_id'=>$result['id'], 'has_fakt_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала товарной накладной',NULL,240, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="has_akt_save")){
	$id=abs((int)$_POST['id']);
	$state=abs((int)$_POST['state']);
	
	$_ti=new AccItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	
	if($state==0){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',241))&&($trust['is_confirmed']==1)&&($trust['has_akt']==1)){
			
				$_ti->Edit($id,array('has_akt'=>0, 'has_akt_confirm_user_id'=>$result['id'], 'has_akt_confirm_pdate'=>time()));
				
				
				
				$log->PutEntry($result['id'],'снял наличие оригинала акта',NULL,241, NULL, NULL,$id);
				
				
			
		}
		
	}else{
		//есть права
		
		
		  if(($au->user_rights->CheckAccess('w',240))&&($trust['is_confirmed']==1)&&($trust['has_akt']==0)){
				//$ret.='zzzzzzzzzzzzzzzzzzzzzzz';
				  $_ti->Edit($id,array('has_akt'=>1, 'has_akt_confirm_user_id'=>$result['id'], 'has_akt_confirm_pdate'=>time()));
				  
				 
				    $log->PutEntry($result['id'],'утвердил наличие оригинала акта',NULL,240, NULL, NULL,$id);	
					  
			 
		  }
	}
	
	
	$ret='';
		
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_by_pos")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		 
		if(!$_ki->CanConfirmByPositions($id,$rss)) $ret=$rss;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_by_pdate")){
	
	
		$id=abs((int)$_POST['id']);
		
		$pdate=$_POST['pdate'];
		$_pdate=DateFromDmy($pdate);
	
		
		$_ki=new AccItem;
		 
		
		 
		if(!$_ki->CanConfirmByPdates($id, $_pdate, $rss)) $ret=$rss;
		else $ret=0;
		//if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		
		//echo $id;
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="refuse_to_confirm")){
	
	
		$id=abs((int)$_POST['id']);
		$refuse=abs((int)$_POST['refuse']);
		
		
		$description='отказ от утверждения реализации';
		
		if($refuse==1){
			$description.=' при первом подтверждении ';	
		}else{
			$description.=' при втором подтверждении ';	
		}
		
		//$_ki=new AccItem;
		
		$log->PutEntry($result['id'], $description,NULL,721, NULL, NULL,$id);	
					  	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_akt")){
	
	
		$id=abs((int)$_POST['id']);
		 
		$_acc=new AccItem;
		if($_acc->HasUsl($id)) $ret=1;
		else $ret=0; 
					  	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_tov")){
	
	
		$id=abs((int)$_POST['id']);
		 
		$_acc=new AccItem;
		if($_acc->HasTov($id)) $ret=1;
		else $ret=0; 
					  	
	
}

//проверка, есть ли более поздние реализации
elseif(isset($_POST['action'])&&($_POST['action']=="check_accs_by_given_pdate")){
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$given_pdate=datefromdmy($_POST['given_pdate']);
	
	$_acc=new AccItem;
	$acc_ids=$_acc->GetLatestAccs($supplier_id, $result['org_id'], $given_pdate);
	
	if(count($acc_ids)>0) $ret=implode(', ',$acc_ids);
	else $ret=0;
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_binded_docs")){
	
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new AccItem;
		
		$rr=$_ki->GetBindedDocumentsToUnconfirm($id);
		
		if(strlen($rr)==0) $ret=0;
		else $ret=$rr;
		
		
		//если ноль - то все хорошо
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new  Acc_ViewGroup;
	$_view=new acc_ViewItem;
	
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
	$_views=new Acc_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>