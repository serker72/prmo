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

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');


require_once('../classes/paycodeitem.php');
require_once('../classes/paycodegroup.php');

require_once('../classes/pay_view.class.php');


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

//РАБОТА С ПРИМЕЧАНИЯМИ
if(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new PaymentNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,false,false,$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',273));
	
	
	$ret=$sm->fetch('pay/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',273)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new PaymentNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания к исходящей оплате', NULL,273, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',273)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new PaymentNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по исходящей оплате', NULL,273,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',273)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new PaymentNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по исходящей оплате', NULL,273,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new PayItem;
	
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
			if($_ti->DocCanUnConfirm($id,$rss)){
			//if(!isset($_POST['is_confirmed'])){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение исходящей оплаты',NULL,613, NULL, NULL,$bill_id);
				
				$log->PutEntry($result['id'],'снял утверждение исходящей оплаты',NULL,278, NULL, NULL,$id);
				
				
					
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($_ti->DocCanConfirm($id,$reason)){
		  if($au->user_rights->CheckAccess('w',277)||$au->user_rights->CheckAccess('w',96)){
			  //if(isset($_POST['is_confirmed'])){
				  $_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'утвердил исходящую оплату',NULL,613, NULL, NULL,$bill_id);	
				  $log->PutEntry($result['id'],'утвердил исходящую оплату',NULL,277, NULL, NULL,$id);	
					  
			  //}
		  }else{
			  //do nothing
		  }
		}
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	/*if($shorter==0) $template='pay/all_pays_list.html';
	else $template='pay/pays_list.html';
	*/
	
	if($shorter==0){
		 $template='pay/all_pays_list.html';
		  $prefix='';
	}else{
		   $prefix='_pay';
		 $template='pay/pays_list.html';
	}
	
	
	$acg=new PayGroup;
	$acg->prefix=$prefix;
	
	$dec=new  DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	
	if($shorter==0) $ret=$acg->ShowAllPos(
		$template, //0
		$dec, //1
		$au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), //2
		$au->user_rights->CheckAccess('w',279) ,//3
		0,//4
		100, //5
		$au->user_rights->CheckAccess('w',277),  //6
		$au->user_rights->CheckAccess('w',96),//7
		false,//8
		true,//9
		$au->user_rights->CheckAccess('w',280),//10
		$au->user_rights->CheckAccess('w',278), //11
		$au->user_rights->CheckAccess('w',281),//12
		NULL, //13
		$au->user_rights->CheckAccess('w',877)
		);
	else $ret=$acg->ShowAllPos(
		$template, 
		$dec, 
		$au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), 
		$au->user_rights->CheckAccess('w',279),
		0,
		100,
		$au->user_rights->CheckAccess('w',277),  
		$au->user_rights->CheckAccess('w',96),
		false,
		true, 
		$au->user_rights->CheckAccess('w',280),
		$au->user_rights->CheckAccess('w',278), 
		$au->user_rights->CheckAccess('w',281),
		NULL, //13
		$au->user_rights->CheckAccess('w',877));
	
	
	

		
}
//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	
	$shorter=abs((int)$_POST['shorter']);
	/*if($shorter==0) $template='pay/all_pays_list.html';
	else $template='pay/pays_list.html';*/
	if($shorter==0){
		 $template='pay/all_pays_list.html';
		  $prefix='';
	}else{
		   $prefix='_pay';
		 $template='pay/pays_list.html';
	}
	
	 $acg=new PayGroup;
	$acg->prefix=$prefix;
	
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
			$log->PutEntry($result['id'],'аннулирование исходящей оплаты',NULL,613,NULL,'исходящая оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['bill_id']);	
			
			$log->PutEntry($result['id'],'аннулирование исходящей оплаты',NULL,279,NULL,'исходящая оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);
			
			
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
			$log->PutEntry($result['id'],'восстановление исходящей оплаты',NULL,613,NULL,'исходящая оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['bill_id']);		
			
			$log->PutEntry($result['id'],'восстановление исходящей оплаты',NULL,280,NULL,'исходящая оплата № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);	
			
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
	
	if($from_card==0){
	
	 // $acg=new PayGroup;
	  
	  $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  if($shorter==0) $ret=$acg->ShowAllPos($template, $dec, $au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), $au->user_rights->CheckAccess('w',279),0,100,$au->user_rights->CheckAccess('w',277),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',280), $au->user_rights->CheckAccess('w',278), $au->user_rights->CheckAccess('w',281));
	  else {
		  //$_bi=new BillItem;
		  //$bill=$_bi->GetItemById($trust['bill_id']);
		 // echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		  //print_r($trust); die();
		   $dec2=new  DBDecorator;
		   $dec2->AddEntry(new UriEntry('bill_id',$bill_id));
		   $dec2->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
		  
		  $ret=$acg->ShowPos($bill_id, $trust['supplier_id'], $template, $dec2, $au->user_rights->CheckAccess('w',272)||$au->user_rights->CheckAccess('w',281), $au->user_rights->CheckAccess('w',279),$au->user_rights->CheckAccess('w',277),  $au->user_rights->CheckAccess('w',96),false,true,$au->user_rights->CheckAccess('w',280),$au->user_rights->CheckAccess('w',278), $au->user_rights->CheckAccess('w',281));
		  
	  }
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',279);
		if(!$au->user_rights->CheckAccess('w',279)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',280);
			if(!$au->user_rights->CheckAccess('w',280)) $reason='недостаточно прав для данной операции';
		
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('ship',$editing_user);
		$ret=$sm->fetch('pay/toggle_annul_card.html');	
	}
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}


//работа с поставщиком	
}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
	$_bi=new BDetailsItem;
	if($_GET['id']==$result['org_id']) $bi=$_bi->GetItemByFields(array('is_basic'=>0, 'user_id'=>$si['id']));
	else $bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>1));
	
	if($si!==false){
		/*$rret=array();
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
	
	
	
	$ret=$_pg->GetItemsForPay('pay/suppliers_list.html',   $dec,true,$all7,$result);
	

	
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
	$arr=$_bd->GetItemsByIdArr($supplier_id, $current_id/*, 1*/);
	
	//print_r($arr);
	
	$sm=new SmartyAj;
	$sm->assign('pos2',$arr);
	
	$ret=$sm->fetch('bills/contracts_list.html');
	

	
}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_bdetails")){
	//получим bdetails из списка
	$_si=new BDetailsItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	/*if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			$rret[]='"'.$k.'":"'.addslashes($v).'"';
		}
		
		$ret='{'.implode(', ',$rret).'}';
	}*/
		foreach($si as $k=>$v){
			$si[$k]=iconv('windows-1251', 'utf-8', $v);
		}
		
		$ret=json_encode($si);

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
	
	$pay_id=abs((int)$_POST['pay_id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);

	
	$complex_positions=$_POST['complex_positions'];
	
	
	$pay_mode=abs((int)$_POST['pay_mode']);
	$sort_mode=abs((int)$_POST['sort_mode']);
	
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new PayForBillGroup;
	$_bi=new BillItem;
	$_inv=new InvCalcItem;
		
	$alls=$_kpg->GetBillsBySupplierArr($supplier_id,$result['org_id'], NULL, NULL, $sort_mode, $contract_id);
	
	
	$arr=array();
	$joined_positions=array();
	foreach($complex_positions as $kk=>$vv){
		$valarr=explode(';',$vv);
		
		$joined_positions[]=array('kind'=>$valarr[0],'position_id'=>$valarr[1]);	
	}
	
	
	foreach($alls as $k=>$v){
		
		
		if(!in_array(array('kind'=>$v['kind'],'position_id'=>$v['position_id']),$joined_positions)){
			$joined_positions[]=array('kind'=>$v['kind'],'position_id'=>$v['position_id']);
		}
		
		
	}
	
	
	
	foreach($joined_positions as $kk=>$vv){
		//foreach($alls as $k=>$v){
		  
		 $v=array();
		 
		  
		  //print_r($v);
		 
		  $in_alls=false;
		  //подгрузка названия и прочих параметров из списка позиций заявки
		  //уч-к, склад, заявка - могут быть изменены потом
		  foreach($alls as $ck=>$cv){
		  		//echo $cv['position_id'].' vs '.$vv['position_id'].'<br />';
				//echo $cv['komplekt_ved_id'].'va '.$vv['komplekt_ved_id'].'<br />';
				if(
				($cv['kind']==$vv['kind'])
				&&
				($cv['position_id']==$vv['position_id'])				
				
				){
					$v=$cv;
					$in_alls=true;
					
					break;
				}
		  
		  }
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$vv['kind'])&&
				
				($cv[1]==$vv['position_id'])	
				){
					$index=$ck;
					//echo 'nashli';
					break;	
				}
		  	
		  }
	
		
		if($index>-1){
			  //echo 'nn';
			  $valarr=explode(';',$complex_positions[$index]);
			  
			  $v['value']=$valarr[2];
			  
			
		}else{
			  $v['value']=0;
			
		}
		 
		 
		 if($v['kind']==0){
			$v['summ']=$_bi->CalcCost($v['id']);
		
			$v['payed']=$_bi->CalcPayed($v['id'],$pay_id);
		}elseif($v['kind']==1){
			//echo $v['id'];
			$v['summ']=$_inv->CalcCost($v['id']);
			//echo $v['summ'];
		
			$v['payed']=$_inv->CalcPayed($v['id'],$pay_id);
		}
		  
		  $v['hash']=md5($v['kind_id'].'_'.$v['id']);
		  
		  $arr[]=$v;
	
	}
	
	
	
	
	
	
	
	
	//print_r($alls);
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$arr);
	$filter_from=time()-24*60*60*30*3;
	$filter_to=time()+24*60*60;
	$sm->assign('filter_from', date('d.m.Y',$filter_from));
	$sm->assign('filter_to', date('d.m.Y',$filter_to));   
	
	$sm->assign('pay_mode',$pay_mode);
	
	if($pay_mode==1){ 
		$ret.=$sm->fetch("pay/positions_editbybill_set.html");
	}else $ret.=$sm->fetch("pay/positions_edit_set.html");
		
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_positions")){
	//перенос выбранных позиций к.в. на страницу счет
		
	$id=abs((int)$_POST['id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);
	
	
	
	$complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	
	$_position=new BillItem;
	$_inv=new InvCalcItem;
	
	foreach($complex_positions as $k=>$kv){
		
		$f=array();	
		$v=explode(';',$kv);
		
		//$do_add=true;
		if($v[2]<=0) continue;
		
		if($v[0]==0){
			$position=$_position->GetItemById($v[1]);
			if($position===false) continue;	
		}elseif($v[0]==1){
			$position=$_inv->GetItemById($v[1]);
			if($position===false) continue;	
		}
		
		//$f['quantity']=$v;
		$f['kind']=$v[0];
		
		$f['id']=$v[1];
		
		
		$f['value']=$v[2];
		
		$f['code']=$position['code'];
		
		
		
		if($position['pdate']!=0) $f['pdate']=date("d.m.Y",$position['pdate']);
		else $f['pdate']='-';
		
		
		if($v[0]==0){
		  $f['supplier_bill_no']=$position['supplier_bill_no'];
		  
		  if($position['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$position['supplier_bill_pdate']);
		  else $f['supplier_bill_pdate']='-';
		  $f['summ']=$_position->CalcCost($position['id']);
			$f['payed']=$_position->CalcPayed($position['id'], $id);
		  
		}elseif($v[0]==1){
			
			 $f['given_no']=$position['given_no'];
		  
		 	 if($position['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$position['invcalc_pdate']);
		  	 else $f['invcalc_pdate']='-';
			 $f['summ']=$_inv->CalcCost($position['id']);
			$f['payed']=$_inv->CalcPayed($position['id'], $id);
		}
		
		
		
		
		$f['hash']=md5($f['kind'].'_'.$f['id']);
		
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
	
	$ret=$sm->fetch("pay/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="find_all_bills")){
	//вывод позиций к.в. для счета
	
	$pay_id=abs((int)$_POST['pay_id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);
	$except_bills=$_POST['except_bills'];
	$except_invs=$_POST['except_invs'];
	$pay_mode=abs((int)$_POST['pay_mode']);
	$sort_mode=abs((int)$_POST['sort_mode']);
	
	
	
	
	//GetItemsByIdArr($id,$current_id=0)
	$_kpg=new PayForBillGroup;
	$_bi=new BillItem;
	$_inv=new InvCalcItem;
	
	$alls=$_kpg->GetBillsBySupplierArr($supplier_id,$result['org_id'],$except_bills,$except_invs, $sort_mode, $contract_id);
	
	//print_r($alls);
	
	foreach($alls as $k=>$v){
		//подставим значения, если они заданы ранее
		
		
			$v['value']=0;
			
		if($v['kind']==0){
		  $v['summ']=$_bi->CalcCost($v['id']);
		  $v['payed']=$_bi->CalcPayed($v['id'],$pay_id);
		}else{
			 $v['summ']=$_inv->CalcCost($v['id']);
		  $v['payed']=$_inv->CalcPayed($v['id'],$pay_id);
		}
				
		$alls[$k]=$v;
		
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('pay_mode',$pay_mode);
	//$filter_from=time()-24*60*60*30*3;
	//$filter_to=time()+24*60*60;
	//$sm->assign('filter_from', date('d.m.Y',$filter_from));
	//$sm->assign('filter_to', date('d.m.Y',$filter_to));   
	 
	
	$ret.=$sm->fetch("pay/positions_edit_rows_filter.html");
		
	
}elseif(isset($_POST['action'])&&($_POST['action']=="find_custom_bills")){
	//вывод позиций к.в. для счета
	
	$pay_id=abs((int)$_POST['pay_id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);
	
	$except_bills=$_POST['except_bills'];
	$except_invs=$_POST['except_invs'];
	
	$not_payed=($_POST['not_payed']==1);
	$filter_from=datefromdmy($_POST['filter_from']);
	$filter_to=datefromdmy($_POST['filter_to'])+24*60*60;
	$pay_mode=abs((int)$_POST['pay_mode']);
	
	$sort_mode=abs((int)$_POST['sort_mode']);
	
	
	
	$_kpg=new PayForBillGroup;
	$_bi=new BillItem;
	$_inv=new InvCalcItem;
	
	$alls=$_kpg->GetBillsBySupplierFilterArr($supplier_id,$result['org_id'],$except_bills,$not_payed,$filter_from,$filter_to,$except_invs,$sort_mode, $contract_id);
	
	//print_r($alls);
	
	foreach($alls as $k=>$v){
		//подставим значения, если они заданы ранее
		
		
			$v['value']=0;
			
			
		
		
		if($v['kind']==0){
		  $v['summ']=$_bi->CalcCost($v['id']);
		  $v['payed']=$_bi->CalcPayed($v['id'],$pay_id);
		}else{
			 $v['summ']=$_inv->CalcCost($v['id']);
		  $v['payed']=$_inv->CalcPayed($v['id'],$pay_id);
		}
		
		
		$alls[$k]=$v;
		
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	//$filter_from=time()-24*60*60*30*3;
	//$filter_to=time()+24*60*60;
	//$sm->assign('filter_from', date('d.m.Y',$filter_from));
	//$sm->assign('filter_to', date('d.m.Y',$filter_to));   
	 $sm->assign('pay_mode',$pay_mode);
	
	$ret.=$sm->fetch("pay/positions_edit_rows_filter.html");
		
	
}elseif(isset($_POST['action'])&&($_POST['action']=="auto_find_bills")){
	
		
	$pay_id=abs((int)$_POST['pay_id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$except_bills=$_POST['except_bills'];
	$contract_id=abs((int)$_POST['contract_id']);
	
	$value=((float)$_POST['value']);
	
	$alls=array();
	
	$_position=new BillItem;
	$_kpg=new PayForBillGroup;
	$alls=$_kpg->GetBillsAuto($supplier_id,$result['org_id'],$value,$except_bills,NULL,$contract_id);
	
	foreach($finded_values as $k=>$v){
		
		$f=$v;	
		//$do_add=true;
		
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
	
	
	$ret=$sm->fetch("pay/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="find_bills_pos")){
	
	$_kr=new PayReports;
	
	$id=abs((int)$_POST['id']);
	$kind=abs((int)$_POST['kind']);
	
	$except_id=abs((int)$_POST['except_id']);
	
	$ret=$_kr->InPays($id,'pay/in_pays.html',$result['org_id'],true,$except_id, $kind);
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="auto_find_known_bill")){
	//находим заданный счет поставщика, если он не оплачен - вернем его на страницу
		
	$pay_id=abs((int)$_POST['pay_id']);
	$bill_id=abs((int)$_POST['bill_id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	//$except_bills=$_POST['except_bills'];
	
	$value=((float)$_POST['value']);
	
	
	
	$alls=array();
	
	$_position=new BillItem;
	$_kpg=new PayForBillGroup;
	$alls=$_kpg->GetBillAutoKnown($bill_id,$supplier_id,$result['org_id'],$value);
	
	foreach($finded_values as $k=>$v){
		
		$f=$v;	
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
	
	$ret=$sm->fetch("pay/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="auto_find_old_bill")){
	//находим самый старый неоплаченный счет поставщика,  вернем его на страницу
		
	$pay_id=abs((int)$_POST['pay_id']);
	//$bill_id=abs((int)$_POST['bill_id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);
	//$except_bills=$_POST['except_bills'];
	
	$value=((float)$_POST['value']);
	
	
	
	$alls=array();
	
	$_position=new BillItem;
	$_kpg=new PayForBillGroup;
	$alls=$_kpg->GetBillOld($supplier_id,$result['org_id'],$value,NULL, $contract_id);
	
	foreach($finded_values as $k=>$v){
		
		$f=$v;	
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
	
	$ret=$sm->fetch("pay/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="find_unpayed_positions")){
	//найдем счета из выбранных, которые не будут оплачены
		
	$id=abs((int)$_POST['id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);

	
	
	$complex_positions=$_POST['complex_positions'];
	
	$value=$_POST['value'];
	$delta=$_POST['delta'];
	
	$alls=array();
	
	$_position=new BillItem;
	$_inv=new InvCalcItem;
	
	$sum_delta=$delta;
	
	$names=array();
	
	foreach($complex_positions as $k=>$vv){
		$v=explode(';',$vv);
		
		$f=array();	
		//$do_add=true;
		if($v[2]<=0) continue;
		
		if($v[0]==0){
		  $position=$_position->GetItemById($v[1]);
		  if($position===false) continue;
		  $f['supplier_bill_no']=$position['supplier_bill_no'];
		  
		  if($position['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$position['supplier_bill_pdate']);
		  else $f['supplier_bill_pdate']='-';
		  
		  
		  $f['summ']=$_position->CalcCost($position['id']);
		  $f['payed']=$_position->CalcPayed($position['id'], $id);
		}else{
			$position=$_inv->GetItemById($v[1]);
		  if($position===false) continue;
		  
		  $f['given_no']=$position['given_no'];
		  
		  if($position['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$position['invcalc_pdate']);
		  else $f['invcalc_pdate']='-';
		  
		  
		  $f['summ']=$_inv->CalcCost($position['id']);
		  $f['payed']=$_inv->CalcPayed($position['id'], $id);
			
		}
		
		
		$f['kind']=$v[0];
		
		$f['id']=$v[1];
		
		
		$f['value']=$v[2];
		
		
		
		
		$f['code']=$position['code'];
		
		
		
		if($position['pdate']!=0) $f['pdate']=date("d.m.Y",$position['pdate']);
		else $f['pdate']='-';
		
		
	
		
	//	$ret.=$v.' ';
		$alls[]=$f;
		
		if($sum_delta==0) break;
		
		if($f['value']>$sum_delta){
			$names[]=$f['code'];
			
			$sum_delta=0;
				
		}else{
			//$f['value']<=$sum_delta)
			$names[]=$f['code'];
			$sum_delta-=$f['value'];
		}
		
	}
	
	
	
	
	$ret=implode(', ',$names);
}elseif(isset($_POST['action'])&&($_POST['action']=="apply_unpayed_positions")){
	//поместим на страницу счета из выбранных, которые будут оплачены
		
	$id=abs((int)$_POST['id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);

	
	$complex_positions=$_POST['complex_positions'];
	
	
	$value=$_POST['value'];
	$delta=$_POST['delta'];
	
	$alls=array();
	
	$_position=new BillItem;
	$_inv=new InvCalcItem;
	
	$sum_delta=$delta;
	
	$names=array();
	
	
	foreach($complex_positions as $k=>$vv){
		$v=explode(';',$vv);
		$f=array();	
		//$do_add=true;
		
		if($v[2]<=0) continue;
		
		if($v[0]==0){
		
			$position=$_position->GetItemById($v[1]);
			if($position===false) continue;
			
			$f['supplier_bill_no']=$position['supplier_bill_no'];
			
			if($position['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$position['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			
			$f['summ']=$_position->CalcCost($position['id']);
			$f['payed']=$_position->CalcPayed($position['id'], $id);
			
		}else{
			$position=$_inv->GetItemById($v[1]);
			if($position===false) continue;
			
			$f['given_no']=$position['given_no'];
			
			if($position['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$position['invcalc_pdate']);
			else $f['invcalc_pdate']='-';
			
			$f['summ']=$_inv->CalcCost($position['id']);
			$f['payed']=$_inv->CalcPayed($position['id'], $id);
				
		}
		
		$f['kind']=$v[0];
		
		$f['id']=$v[1];
		
		
		$f['value']=$v[2];
		
		
		if($f['value']>$sum_delta){
			//$names[]=$f['code'];
			
			$f['value']-=$sum_delta;
			$sum_delta=0;
				
		}else{
			//$f['value']<=$sum_delta)
			//$names[]=$f['code'];
			
			
			$sum_delta-=$f['value'];
			continue;
		}
		
		
		
		$f['code']=$position['code'];
		
	
		
		if($position['pdate']!=0) $f['pdate']=date("d.m.Y",$position['pdate']);
		else $f['pdate']='-';
		
		
		$f['hash']=md5($f['kind'].'_'.$f['id']);
		
		
		$alls[]=$f;
	}
	
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
	
	//$ret='1';
	$ret=$sm->fetch("pay/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="apply_new_positions")){
	//перенос выбранных позиций + добавка новых (обработка превышения суммы)
		
	$id=abs((int)$_POST['id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);
	
	
	$complex_positions=$_POST['complex_positions'];
	
	$value=$_POST['value'];
	$delta=$_POST['delta'];
	
	
	$alls=array();
	
	$_position=new BillItem;
	$_inv=new InvCalcItem;
	$except_bills=array(); $except_invs=array();
	
	
	foreach($complex_positions as $k=>$vv){
		$f=array();	
		
		$v=explode(';',$vv);
		//$do_add=true;
		if($v[2]<=0) continue;
		
		if($v[0]==0){
		  $position=$_position->GetItemById($v[1]);
		  if($position===false) continue;
		}else{
			 $position=$_inv->GetItemById($v[1]);
		  if($position===false) continue;
		}
		
		$f['kind']=$v[0];
		$f['id']=$v[1];
		
		
		$f['value']=$v[2];
		
		$f['code']=$position['code'];
		
		
		
		if($position['pdate']!=0) $f['pdate']=date("d.m.Y",$position['pdate']);
		else $f['pdate']='-';
		
		if($v[0]==0){
		  $f['supplier_bill_no']=$position['supplier_bill_no'];
		  if($position['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$position['supplier_bill_pdate']);
		  else $f['supplier_bill_pdate']='-';
		  
		  
		  $f['summ']=$_position->CalcCost($position['id']);
		  $f['payed']=$_position->CalcPayed($position['id'], $id);
		  
		  $except_bills[]=$f['id'];
		  
		}else{
			 $f['given_no']=$position['given_no'];
		  if($position['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$position['invcalc_pdate']);
		  else $f['invcalc_pdate']='-';
		  
		  
		  $f['summ']=$_inv->CalcCost($position['id']);
		  $f['payed']=$_inv->CalcPayed($position['id'], $id);
		  
		  $except_invs[]=$f['id'];
			
		}
		
		$f['hash']=md5($f['kind'].'_'.$f['id']);
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	
	//найдем счета по дельте
	$_position=new BillItem;
	$_kpg=new PayForBillGroup;
	$alls1=$_kpg->GetBillsAuto($supplier_id,$result['org_id'],$delta,$except_bills,$except_invs, $contract_id);
	
	
	
	foreach($alls1 as $k=>$v){
		$alls[]=$v;	
	}
	
	//echo $supplier_id.' '.$result['org_id'].' '.$delta;
	
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	
	$sm->assign('can_add_positions',$au->user_rights->CheckAccess('w',270)); 
	$sm->assign('can_del_positions',$au->user_rights->CheckAccess('w',271)); 
	
	//print_r($alls1);
	$ret=$sm->fetch("pay/positions_on_page_set.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="check_new_positions")){
	//(обработка превышения суммы - добавим позиций, проверим остаток по сумме, вернем его
		
	$id=abs((int)$_POST['id']);
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contract_id=abs((int)$_POST['contract_id']);
	
	
	
	$complex_positions=$_POST['complex_positions'];
	//var_dump($complex_positions);
	
	
	$value=$_POST['value'];
	$delta=$_POST['delta'];
	
	
	$alls=array();
	
	$_position=new BillItem;
	$_inv=new InvCalcItem;
	
	$except_bills=array(); $except_invs=array();
	
	foreach($complex_positions as $k=>$vv){
		$f=array();	
		$v=explode(';',$vv);
		
		
		//$do_add=true;
		if($v[2]<=0) continue;
		
		if($v[0]==0){
			$position=$_position->GetItemById($v[1]);
			if($position===false) continue;
		}else{
			$position=$_inv->GetItemById($v[1]);
			if($position===false) continue;
		}
		
		//$f['quantity']=$v;
		$f['kind']=$v[0];
		
		$f['id']=$v[1];
		
		
		$f['value']=$v[2];
		
		$f['code']=$position['code'];
		
		if($v[0]==0){
			$f['supplier_bill_no']=$position['supplier_bill_no'];
			
				
			if($position['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$position['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			
			$f['summ']=$_position->CalcCost($position['id']);
			$f['payed']=$_position->CalcPayed($position['id'], $id);
			
			$except_bills[]=$f['id'];
		}else{
			$f['given_no']=$position['given_no'];
			
			if($position['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$position['invcalc_pdate']);
			else $f['invcalc_pdate']='-';
			
			$f['summ']=$_inv->CalcCost($position['id']);
			$f['payed']=$_inv->CalcPayed($position['id'], $id);
			$except_invs[]=$f['id'];
			
		}
		
		if($position['pdate']!=0) $f['pdate']=date("d.m.Y",$position['pdate']);
		else $f['pdate']='-';
		
		$f['hash']=md5($f['kind'].'_'.$f['id']);
	
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	//var_dump($except_invs); var_dump($except_bills); 
		
	
	//echo $delta;
	
	//найдем счета по дельте
	$_position=new BillItem;
	$_kpg=new PayForBillGroup;
	$alls1=$_kpg->GetBillsAuto($supplier_id,$result['org_id'],$delta,$except_bills,$except_invs, $contract_id);
	
	
	
	foreach($alls1 as $k=>$v){
		$alls[]=$v;	
	}
	
	
	
	$new_summ=0;
	foreach($alls as $k=>$v){
		$new_summ+=$v['value'];
	}
	
	$ret=round($value-$new_summ,2);
}elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new PayItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new PayItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_codes")){
	$sm=new SmartyAj;
	if(isset($_POST['current_id'])) $current_id=abs((int)$_POST['current_id']);
	else $current_id=0;
	
	$opg=new PayCodeGroup;
	$pos= $opg->GetItemsArr();
	$sm->assign('pos',$pos);
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',272));
	
	
	$ret=$sm->fetch('pay/code_list.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_code")){
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',272)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PayCodeItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	$params['descr']=SecStr(iconv("utf-8","windows-1251",$_POST['descr']));
	$params['in_report']=abs((int)$_POST['in_report']);
	
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_code")){
	if(!$au->user_rights->CheckAccess('w',272)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	
	$qi=new PayCodeItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	$params['descr']=SecStr(iconv("utf-8","windows-1251",$_POST['descr']));
	$params['in_report']=abs((int)$_POST['in_report']);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_code")){
	
	if(!$au->user_rights->CheckAccess('w',272)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new PayCodeItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_code")){
	$_si=new PayCodeItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			/*if(
			($k=='contract_no')||
			($k=='contract_pdate')||
			($k=='contract_pdate')) continue;
			
			*/
			//$rret[]='"'.$k.'":"<![CDATA['.htmlspecialchars($v).']]>"';
			$si[$k]=iconv('windows-1251', 'utf-8', $v);
			
		}
		
		$ret=json_encode($si);
		
		
		//$ret='{'.implode(', ',$rret).'}';
	}
}

//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Pay_ViewGroup;
	$_view=new Pay_ViewItem;
	
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
	$_views=new Pay_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>