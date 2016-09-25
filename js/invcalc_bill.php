<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/pl_positem.php');
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

require_once('../classes/invcalcbindedgroup.php');
require_once('../classes/invcalcitem.php');


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

//utv- razutv
if(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_price")){
	$id=abs((int)$_POST['id']);
	
	$invcalc_id=abs((int)$_POST['invcalc_id']);
	
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
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)){
				$_ti->Edit($id,array('is_confirmed_price'=>0, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true);
				
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
				$_ti->Edit($id,array('is_confirmed_price'=>1, 'user_confirm_price_id'=>$result['id'], 'confirm_price_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'утвердил цены',NULL,95, NULL, NULL,$bill_id);	
				
				if($flag_to_payments==1) $_ti->BindPayments($bill_id,$result['org_id']);		
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	$template='invcalc/bills_list_komplekt.html';
	
	
	$acg=new InvCalcBindedGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	$ret=$acg->ShowBills($invcalc_id, $template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94), '', $au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',131),NULL,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197));
	
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_shipping")){
	$id=abs((int)$_POST['id']);
	$_ti=new BillItem;
	
	$invcalc_id=abs((int)$_POST['invcalc_id']);
	
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
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)){
			if($_ti->DocCanUnconfirmShip($id,$reas)){
			
				$_ti->Edit($id,array('is_confirmed_shipping'=>0, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение отгрузки',NULL,197, NULL, NULL,$bill_id);
				
			}
				
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',195)||$au->user_rights->CheckAccess('w',96)){
			if(($trust['status_id']==2)||($trust['status_id']==9)||($trust['status_id']==10)){
				$_ti->Edit($id,array('is_confirmed_shipping'=>1, 'user_confirm_shipping_id'=>$result['id'], 'confirm_shipping_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'утвердил отгрузку',NULL,195, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	$template='invcalc/bills_list_komplekt.html';
	
	
	$acg=new InvCalcBindedGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	
	$ret=$acg->ShowBills($invcalc_id, $template,$dec,0,100, $au->user_rights->CheckAccess('w',128),$au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94), '', $au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',131),NULL,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197));
	
	
		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	$invcalc_id=abs((int)$_POST['invcalc_id']);
	
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
			$_ti->Edit($id,array('status_id'=>3));
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование входящего счета',NULL,94,NULL,'входящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);	
			
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
		if($au->user_rights->CheckAccess('w',131)){
			$_ti->Edit($id,array('status_id'=>1));
			
			$stat=$_stat->GetItemById(1);
			$log->PutEntry($result['id'],'восстановление входящего счета',NULL,131,NULL,'входящий счет № '.$trust['code'].': установлен статус '.$stat['name'],$id);
			
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
	
	$template='invcalc/bills_list_komplekt.html';
	  
	  
	  $acg=new InvCalcBindedGroup;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	 
	  
	  $ret=$acg->ShowBills($invcalc_id,$template,$dec,0,100, $au->user_rights->CheckAccess('w',128), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94), '', $au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',131),NULL,NULL,$au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197));
	
		
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>