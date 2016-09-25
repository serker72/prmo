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
require_once('../classes/invcalcitem.php');
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


require_once('../classes/paynotesgroup.php');
require_once('../classes/paynotesitem.php');
require_once('../classes/payitem.php');
require_once('../classes/user_s_item.php');

require_once('../classes/payforbillgroup.php');
require_once('../classes/paygroup.php');

require_once('../classes/payreports.php');
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


//utv- razutv
if(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$invcalc_id=abs((int)$_POST['invcalc_id']);
	
	$_ti=new PayItem;
	$_inv=new InvCalcItem;
	$inv=$_inv->GetItemById($invcalc_id);
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
		if(($au->user_rights->CheckAccess('w',278))||$au->user_rights->CheckAccess('w',96)){
			//if(!isset($_POST['is_confirmed'])){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение оплаты',NULL,93, NULL, NULL,$bill_id);
				
				$log->PutEntry($result['id'],'снял утверждение оплаты',NULL,278, NULL, NULL,$id);
				
				
					
			//}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($_ti->DocCanConfirm($id,$reason)){
		  if($au->user_rights->CheckAccess('w',277)||$au->user_rights->CheckAccess('w',96)){
			  //if(isset($_POST['is_confirmed'])){
				  $_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'утвердил оплату',NULL,93, NULL, NULL,$bill_id);	
				  $log->PutEntry($result['id'],'утвердил оплату',NULL,277, NULL, NULL,$id);	
					  
			  //}
		  }else{
			  //do nothing
		  }
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	$template='invcalc/pays_list.html';
	
	
	$acg=new InvCalcBindedGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowPays($invcalc_id, $inv['supplier_id'], 'invcalc/pays_list.html', $dec,  $au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), $au->user_rights->CheckAccess('w',279),$au->user_rights->CheckAccess('w',277),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',280),$au->user_rights->CheckAccess('w',278) );
	
	
	

		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	//$bill_id=abs((int)$_POST['bill_id']);
	
	$invcalc_id=abs((int)$_POST['invcalc_id']);
	
	$_ti=new PayItem;
	$_inv=new InvCalcItem;
	$inv=$_inv->GetItemById($invcalc_id);
	
	
	$shorter=abs((int)$_POST['shorter']);
	$template='invcalc/pays_list.html';
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new PayItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==14)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',279)){
			$_ti->Edit($id,array('status_id'=>3, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));	
			
			$stat=$_stat->GetItemById(3);
			$log->PutEntry($result['id'],'аннулирование оплаты',NULL,93,NULL,'оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['bill_id']);	
			
			$log->PutEntry($result['id'],'аннулирование оплаты',NULL,279,NULL,'оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);
			
			
			//внести примечание
			$_ni=new PaymentNotesItem;
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
		if($au->user_rights->CheckAccess('w',280)){
			$_ti->Edit($id,array('status_id'=>14, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));
			
			$stat=$_stat->GetItemById(14);
			$log->PutEntry($result['id'],'восстановление оплаты',NULL,93,NULL,'оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['bill_id']);		
			
			$log->PutEntry($result['id'],'восстановление оплаты',NULL,280,NULL,'оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new PaymentNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'Автоматическое примечание: документ был восстановлен пользователем '.SecStr($result['name_s']).' ('.$result['login'].')',
				'is_auto'=>1,
				'pdate'=>time()
					));
				
		}
		
	}
	
	
	
	  $template='invcalc/pays_list.html';
	
	
	$acg=new InvCalcBindedGroup;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	$ret=$acg->ShowPays($invcalc_id, $inv['supplier_id'], 'invcalc/pays_list.html', $dec,  $au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), $au->user_rights->CheckAccess('w',279),$au->user_rights->CheckAccess('w',277),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',280),$au->user_rights->CheckAccess('w',278) );
	  
	
		
}



//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>