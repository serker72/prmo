<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/an_supplier.php');

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

require_once('../classes/invcalcitem.php');
require_once('../classes/invcalcgroup.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/maxformer.php');
require_once('../classes/opfitem.php');


require_once('../classes/invcalcnotesgroup.php');
require_once('../classes/invcalcnotesitem.php');
require_once('../classes/posdimitem.php');

require_once('../classes/billdates.php');
require_once('../classes/billreports.php');
require_once('../classes/billprepare.php');
require_once('../classes/invpreparegroup.php');
require_once('../classes/invreports.php');

require_once('../classes/user_s_item.php');

require_once('../classes/invcalcitem.php');

require_once('../classes/invcalc_view.class.php');


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

//РАБОТА С ПРИМЕЧАНИЯМИ
if(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new InvCalcNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',346), $au->user_rights->CheckAccess('w',346),$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',453));
	
	
	$ret=$sm->fetch('invcalc/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',453)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new InvCalcNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по распоряжению на инвентаризацию', NULL,453, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',453)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new InvCalcNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по распоряжению на инвентаризацию', NULL,453,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',453)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new InvCalcNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по распоряжению на инвентаризацию', NULL,453,NULL,NULL,$user_id);
	
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
	
	$_ti=new InvCalcItem;
	
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
		if(($au->user_rights->CheckAccess('w',459))){
			if($trust['status_id']==1){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,false,$result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения',NULL,459, NULL, NULL,$bill_id);
				//$_ti->FreeBindedPayments($bill_id);
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',458)){
			if(($_ti->CheckInventoryPdate($trust['invcalc_pdate'],$trust['supplier_id'], $result['org_id'], $rss,$trust['id']))&&($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,false,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнение',NULL,458, NULL, NULL,$bill_id);	
				
				//if($flag_to_payments==1) $_ti->BindPayments($bill_id,$result['org_id']);		
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='invcalc/invcalcs_list.html';
	else $template='invcalc/bills_list_komplekt.html';
	
	
	$acg=new InvCalcGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),false, true,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461),  $au->user_rights->CheckAccess('w',462));
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_inv")){
	$id=abs((int)$_POST['id']);
	$_ti=new InvCalcItem;
	
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
		if(($au->user_rights->CheckAccess('w',461))){
			if($trust['status_id']==16){
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_inv'=>0, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,false,$result);
				
				$log->PutEntry($result['id'],'снял утверждение коррекции',NULL,461, NULL, NULL,$bill_id);
				
			}
				
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',460)){
			if($trust['status_id']==1){
				$_ti->Edit($id,array('is_confirmed_inv'=>1, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,true,$result);
				
				$log->PutEntry($result['id'],'утвердил коррекцию',NULL,460, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	if($shorter==0) $template='invcalc/invcalcs_list.html';
	else $template='inv/bills_list_komplekt.html';
	
	
	$acg=new InvCalcGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	//if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	
	
	$ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),false, true,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461),  $au->user_rights->CheckAccess('w',462));
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	
	$_ti=new InvCalcItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',463)){
			$_ti->Edit($id,array('status_id'=>3));
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование распоряжения на инвентаризацию',NULL,463,NULL,'распоряжение на инвентаризацию № '.$trust['id'].': установлен статус '.$stat['name'],$id);	
			
			//уд-ть связанные документы
			$_ti->AnnulBindedDocuments($id);
			
			//внести примечание
			$_ni=new InvCalcNotesItem;
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
		if($au->user_rights->CheckAccess('w',464)){
			$_ti->Edit($id,array('status_id'=>1));
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление распоряжения на инвентаризацию',NULL,464,NULL,'распоряжение на инвентаризацию № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
			//внести примечание
			$_ni=new InvCalcNotesItem;
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
	  if($shorter==0) $template='invcalc/invcalcs_list.html';
	  else $template='inv/bills_list_komplekt.html';
	  
	  
	  $acg=new InvCalcGroup;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  //if($shorter!=0) $dec->AddEntry(new SqlEntry('p.komplekt_ved_id',$trust['komplekt_ved_id'], SqlEntry::E));
	  
	  
	  $ret=$acg->ShowPos($template,$dec,0,100, $au->user_rights->CheckAccess('w',451),  $au->user_rights->CheckAccess('w',452)||$au->user_rights->CheckAccess('w',462), $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',458), $au->user_rights->CheckAccess('w',458),false, true,$au->user_rights->CheckAccess('w',464),$limited_sector, $au->user_rights->CheckAccess('w',463), $au->user_rights->CheckAccess('w',459), $au->user_rights->CheckAccess('w',461),  $au->user_rights->CheckAccess('w',462));
	  
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',463);
		if(!$au->user_rights->CheckAccess('w',463)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('bill',$editing_user);
		$ret=$sm->fetch('invcalc/toggle_annul_card.html');		
	}
		
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_invcalc_pdate")){
	//dostup
	$_kr=new InvCalcItem;
	
	$id=abs((int)$_POST['id']);
	$supplier_id=abs((int)$_POST['supplier_id']);
	
	$pdate=DateFromDmy($_POST['pdate']);
	
	
	$res=$_kr->CheckInventoryPdate($pdate,$supplier_id, $result['org_id'], $rss,$id);
	
	if($res===true) $ret=0;
	else{
		$ret=$rss;	
	}
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="HasNotDifference")){
	//dostup
	$_kr=new InvCalcItem;
	
	$id=abs((int)$_POST['id']);
	
	$test=$_kr->sync->HasNotDifference($id, $rss);
	
	if($test===true) $ret=0;
	else{
		$ret=$rss;	
	}
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvCalcItem;
		
		
		if(!$_ki->DocCanUnconfirmShip($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvCalcItem;
		
		
		if(!$_ki->DocCanConfirmShip($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_fill")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvCalcItem;
		
		
		if(!$_ki->DocCanUnconfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_fill")){
	
		$id=abs((int)$_POST['id']);
		
	
		//echo 'zzzzzzzzzzzzzzz';
		$_ki=new InvCalcItem;
		
		
		if(!$_ki->DocCanConfirm($id,$rss)) $ret=$rss;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_debt")){
	//проверить, есть ли заявки с таким номером для такого уч.
	
		$supplier_id=abs((int)$_POST['supplier_id']);
		$invcalc_pdate=SecStr($_POST['invcalc_pdate']);
	
		$id=abs((int)$_POST['id']);
		
		$an=new AnSupplier;
		$ret=$an->OstBySup($supplier_id,Datefromdmy($invcalc_pdate)+24*60*60-1,$result['org_id'], $id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_binded_docs")){
	//перечисление связанных документов
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new InvCalcItem;
		
		$ret=$_ki->GetBindedDocuments($id);
		
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unpayed_bills")){
	//нахождение неоплаченных счетов
	
		$id=abs((int)$_POST['id']);
		$is_incoming=abs((int)$_POST['is_incoming']);
	
		
		$_ki=new InvCalcItem;
		$ki=$_ki->GetItemById($id);
		
		$res=$_ki->CheckUpayedBills($ki['supplier_id'],$result['org_id'],$bills, $is_incoming);
		
		$_t_a=array();
		foreach($bills as $k=>$v){
			$_t_a[]=$v['code'];	
		}
		
		if($res) $ret=implode(', ',$_t_a);;
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Invcalc_ViewGroup;
	$_view=new Invcalc_ViewItem;
	
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
	$_views=new Invcalc_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>