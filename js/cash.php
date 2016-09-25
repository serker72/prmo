<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

 
require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');

 

require_once('../classes/cashnotesgroup.php');
require_once('../classes/cashnotesitem.php');
require_once('../classes/cashitem.php');
require_once('../classes/user_s_item.php');

require_once('../classes/cashgroup.php');


require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');


require_once('../classes/paycodeitem.php');
require_once('../classes/paycodegroup.php');


require_once('../classes/usersgroup.php');


require_once('../classes/cashcreator.php');
require_once('../classes/billitem.php');

require_once('../classes/cash_to_bill_item.php');

require_once('../classes/cash_percent_item.php');


require_once('../classes/billgroup.php');

require_once('../classes/cash_view.class.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

 

//РАБОТА С ПРИМЕЧАНИЯМИ
if(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new CashNotesGroup;
	
	$sm->assign('items', $rg->GetItemsByIdArr($user_id,0,0, false,$au->user_rights->CheckAccess('w',849), $au->user_rights->CheckAccess('w',849),$result['id']));  //$rg->GetItemsByIdArr($user_id,0,0,false,false,false,$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',837));
	
	
	$ret=$sm->fetch('cash/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',837)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new CashNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания к расходу наличных', NULL,837, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',837)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new CashNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания к расходу наличных', NULL,837,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',837)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new CashNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания к расходу наличных', NULL,837,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new CashItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	 
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',843))){
			if(($trust['status_id']==2)){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true, $result);
				
				$log->PutEntry($result['id'],'снял подтверждение затрат',NULL,843, NULL, NULL,$id);
				
					
			}
		} 
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',842)){
			if(($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил затрату',NULL,842, NULL, NULL,$id);	
				
				 
			}
		} 
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	 
	 
	
		   $prefix='_cash';
		 $template='cash/all_cash_list.html';
	 
	
		 $acg=new CashGroup;
	$acg->prefix=$prefix;
	 $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  $acg->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
		$acg->SetAuthResult($result);
		$ret=$acg->ShowAllPos('cash/all_cash_list.html', $dec, 
		
		$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
		$au->user_rights->CheckAccess('w',846) ,0, 1000,
		$au->user_rights->CheckAccess('w',842) ,  
		false , false,true, 
		$au->user_rights->CheckAccess('w',847) ,  
		$au->user_rights->CheckAccess('w',843),
		
		$au->user_rights->CheckAccess('w',835),
		$au->user_rights->CheckAccess('w',844),
		$au->user_rights->CheckAccess('w',845),
		$some,
		$au->user_rights->CheckAccess('w',851)
	
		);

		
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_given")){
	$id=abs((int)$_POST['id']);
	$_ti=new CashItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	 
	
	if($trust['is_confirmed_given']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',845))){
			if(($trust['status_id']==19)){
			if($_ti->DocCanUnconfirmGiven($id, $reas)){
			
				$_ti->Edit($id,array('is_confirmed_given'=>0, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение выдачи суммы',NULL,845, NULL, NULL,$id);
				
			}
				
			}
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',844)){
			if(($trust['status_id']==2)){
			if($_ti->DocCanConfirmGiven($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_given'=>1, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил выдачу суммы',NULL,844, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			}
		} 
	}
	
	
	
		   $prefix='_cash';
		 $template='cash/all_cash_list.html';
	 
	
		 $acg=new CashGroup;
	$acg->prefix=$prefix;
	 $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  $acg->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
		$acg->SetAuthResult($result);
		$ret=$acg->ShowAllPos('cash/all_cash_list.html', $dec, 
		
		$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
		$au->user_rights->CheckAccess('w',846) ,0, 1000,
		$au->user_rights->CheckAccess('w',842) ,  
		false , false,true, 
		$au->user_rights->CheckAccess('w',847) ,  
		$au->user_rights->CheckAccess('w',843),
		
		$au->user_rights->CheckAccess('w',835),
		$au->user_rights->CheckAccess('w',844),
		$au->user_rights->CheckAccess('w',845),
		$some,
		$au->user_rights->CheckAccess('w',851)
		);

	 
		
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	
	$shorter=abs((int)$_POST['shorter']);
	 
		   $prefix='_cash';
		 $template='cash/all_cash_list.html';
	 
	
	 $acg=new CashGroup;
	$acg->prefix=$prefix;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new CashItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',846)){
			$_ti->Edit($id,array('status_id'=>3, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));	
			
			$stat=$_stat->GetItemById(3);
			 
			
			$log->PutEntry($result['id'],'аннулирование расхода наличных',NULL,846,NULL,'расход наличных № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);
			
			
			//внести примечание
			$_ni=new CashNotesItem;
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
		if($au->user_rights->CheckAccess('w',847)){
			$_ti->Edit($id,array('status_id'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));
			
			$stat=$_stat->GetItemById(1);
			 	
			
			$log->PutEntry($result['id'],'восстановление расхода наличных',NULL,847,NULL,'расход наличных № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new CashNotesItem;
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
	  
	  $acg->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
		$acg->SetAuthResult($result);
		$ret=$acg->ShowAllPos('cash/all_cash_list.html', $dec, 
		
		$au->user_rights->CheckAccess('w',836)||$au->user_rights->CheckAccess('w',848),  
		$au->user_rights->CheckAccess('w',846) ,0, 1000,
		$au->user_rights->CheckAccess('w',842) ,  
		false , false,true, 
		$au->user_rights->CheckAccess('w',847) ,  
		$au->user_rights->CheckAccess('w',843),
		
		$au->user_rights->CheckAccess('w',835),
		$au->user_rights->CheckAccess('w',844),
		$au->user_rights->CheckAccess('w',845),
		$some,
		$au->user_rights->CheckAccess('w',851)
	
		);
			
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',846);
		if(!$au->user_rights->CheckAccess('w',846)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',847);
			if(!$au->user_rights->CheckAccess('w',847)) $reason='недостаточно прав для данной операции';
		
		
		//$editing_user['binded_to_annul']=$_ti->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$sm->assign('ship',$editing_user);
		$ret=$sm->fetch('cash/toggle_annul_card.html');	
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
	$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id']));
	
	$_sci=new SupContractItem;
	$sci=$_sci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$si['id'], 'is_incoming'=>1));
	
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
		/*
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
			
			
		}*/
		
		
		
		$ret='{'.implode(', ',$rret).'}';
		/*foreach($rret as $k=>$v){
			$rret[$k]=iconv('windows-1251', 'utf-8', $v);	
		}
		$ret=json_encode($rret);*/
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
	
	
	
	$ret=$_pg->GetItemsForBill('bills/suppliers_list.html',  $dec,true,$all7,$result);
	

	
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
	$arr=$_bd->GetItemsByIdArr($supplier_id, $current_id,  1);
	
	//print_r($arr);
	
	$sm=new SmartyAj;
	$sm->assign('pos2',$arr);
	
	$ret=$sm->fetch('cash/contracts_list.html');
	

	
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
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_given")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirmGiven($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_given")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashItem;
		
		
		if(!$_ki->DocCanUnConfirmGiven($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_codes")){
	$sm=new SmartyAj;
	if(isset($_POST['current_id'])) $current_id=abs((int)$_POST['current_id']);
	else $current_id=0;
	
	$opg=new PayCodeGroup;
	$pos= $opg->GetItemsArr(0, $current_id);
	$sm->assign('codespos',$pos);
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',272));
	
	
	$ret=$sm->fetch('cash/code_list.html');
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_code")){
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
			
			$si['current_user_id']=''.$result['id'].'';
			
			$si[$k]=iconv('windows-1251', 'utf-8', $v);
			
		}
		
		$ret=json_encode($si);
		
		
		//$ret='{'.implode(', ',$rret).'}';
	}
}

//список водителей для формы выбора
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_drivers")){
	$sm=new SmartyAj;
	if(isset($_POST['current_id'])) $current_id=abs((int)$_POST['current_id']);
	else $current_id=0;
	
	/*$opg=new PayCodeGroup;
	$pos= $opg->GetItemsArr(0, $current_id);
	$sm->assign('codespos',$pos);
	*/
	$pos=array();
	
	$sql='select * from user where is_active=1 and id in(select distinct qu.user_id from question_user as qu inner join question as q on q.id=qu.question_id where q.name like "%водитель%")';
	
	$set=new mysqlset($sql);
	$rs=$set->getresult();
	$rc=$set->getresultnumrows();
	
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
		$pos[]=$f;	
	}
	
	 
	$sm->assign('codespos',$pos);
	
	$ret=$sm->fetch('cash/driver_list.html');
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_driver")){
	$_si=new useritem;
	
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
//список экспедиторов для формы выбора
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_exped")){
	$sm=new SmartyAj;
	if(isset($_POST['current_id'])) $current_id=abs((int)$_POST['current_id']);
	else $current_id=0;
	
	/*$opg=new PayCodeGroup;
	$pos= $opg->GetItemsArr(0, $current_id);
	$sm->assign('codespos',$pos);
	*/
	$pos=array();
	
	$sql='select * from user where is_active=1 and id in(select distinct qu.user_id from question_user as qu inner join question as q on q.id=qu.question_id where q.name like "%экспедитор%")';
	
	$set=new mysqlset($sql);
	$rs=$set->getresult();
	$rc=$set->getresultnumrows();
	
	for($i=0; $i<$rc; $i++){
		$f=mysqli_fetch_array($rs);
		
		foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
		$pos[]=$f;	
	}
	
	 
	$sm->assign('codespos',$pos);
	
	$ret=$sm->fetch('cash/driver_list.html');
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_exped")){
	$_si=new useritem;
	
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

elseif(isset($_POST['action'])&&($_POST['action']=="add_cash")){
	//добавка расхода из счета
	$_ci=new CashItem; $_bill=new billitem;
	
	$params=array();
	
	$_cc=new CashCreator;
	$params['code']=$_cc->GenLogin($result['id']);
	/*
	"bill_id":$("#id").val(),
				"kind_id":2,
				"rout":$("#cash_delivery_route").val(),
				"weight":$("#cash_delivery_weight").val(),
				"number_pieces":$("#cash_delivery_number_pieces").val(),
				"distance_bonus":$("input[id^=cash_delivery_distance_bonus_]:checked").val(),
				"has_chief_bonus":has_cash_delivery_chief_bonus,
				"chief_bonus":cash_delivery_chief_bonus,
				"chief_bonus_reason":cash_delivery_chief_bonus_reason,
				"driver_id":$("#cash_delivery_driver_id").val(),
				
				"code_id":$("#cash_delivery_code_id").val(),
				"value":$("#cash_delivery_value").val(),
				
				"selected_bills":selected_bills
				*/
	
	$params['org_id']=$result['org_id'];
	$params['pdate']=time();
	$params['manager_id']=$result['id'];
	$params['user_confirm_id']=$result['id'];
	$params['confirm_pdate']=time();
	$params['is_confirmed']=1;
	$params['status_id']=2;
	
	$params['bill_id']=0; //abs((int)$_POST['bill_id']);
	
	$bill=$_bill->getitembyid($params['bill_id']);
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	
	$params['rout']=SecStr(iconv('utf-8', 'windows-1251', $_POST['rout']));
	$params['weight']=abs((float)$_POST['weight']);	
	
	$params['number_pieces']=abs((int)$_POST['number_pieces']);	
	$params['distance_bonus']=abs((float)$_POST['distance_bonus']);	
	$params['has_chief_bonus']=abs((int)$_POST['has_chief_bonus']);
	$params['chief_bonus']=abs((float)$_POST['chief_bonus']);
	
	$params['chief_bonus_reason']=SecStr(iconv('utf-8', 'windows-1251', $_POST['chief_bonus_reason']));
	
	$params['driver_id']=abs((int)$_POST['driver_id']);
	$params['code_id']=abs((int)$_POST['code_id']);
	$params['responsible_user_id']=abs((int)$_POST['responsible_user_id']);
	
	$params['value']=abs((float)$_POST['value']);	
	
	$code=$_ci->add($params);
	
	$_cbi=new CashToBillItem;
	
	foreach($_POST['selected_bills'] as $k=>$v){
		$cparams=array();
		$cparams['bill_id']=abs((int)$v);
		$cparams['cash_id']=$code;
		
		$_cbi->Add($cparams);
		
		
		$bill=$_bill->getitembyid($cparams['bill_id']);
		
		
		$log->PutEntry($result['id'], 'создал доставку по исходящему счету', NULL, 93, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'], $bill['id']);
		$log->PutEntry($result['id'], 'создал доставку по исходящему счету', NULL, 835, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'], $code);
		
		
		foreach($params as $k=>$v){
			
		   
				$log->PutEntry($result['id'],'создал доставку по исходящему счету',NULL,835, NULL, 'в поле '.$k.' установлено значение '.$v,$code);	
				
				$log->PutEntry($result['id'],'создал доставку по исходящему счету',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v, $bill['id']);		
			 
		}
	}
	
	
	
	
	
	
	
		
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_cash_exped")){
	//добавка экспедирования из счета
	$_ci=new CashItem; $_bill=new billitem;
	
	$params=array();
	
	$_cc=new CashCreator;
	$params['code']=$_cc->GenLogin($result['id']);
	/*
	"action":"add_cash_exped",
				"bill_id":$("#id").val(),
				"kind_id":3,
				 
				"number_pieces":$("#cash_exped_number_pieces").val(),
				
				"driver_id":$("#cash_exped_driver_id").val(),
				
				"responsible_user_id":$("#cash_exped_responsible_user_id").val(),
				"code_id":$("#cash_exped_code_id").val(),
				"value":$("#cash_exped_value").val(),*/
	
	$params['org_id']=$result['org_id'];
	$params['pdate']=time();
	$params['manager_id']=$result['id'];
	$params['user_confirm_id']=$result['id'];
	$params['confirm_pdate']=time();
	$params['is_confirmed']=1;
	$params['status_id']=2;
	
	$params['bill_id']=0; //abs((int)$_POST['bill_id']);
	
	//$bill=$_bill->getitembyid($params['bill_id']);
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	
 	
	$params['has_chief_bonus']=abs((int)$_POST['has_chief_bonus']);
	$params['chief_bonus']=abs((float)$_POST['chief_bonus']);
	
	$params['chief_bonus_reason']=SecStr(iconv('utf-8', 'windows-1251', $_POST['chief_bonus_reason']));
	
	
	$params['number_pieces']=abs((int)$_POST['number_pieces']);	
	 
	
	$params['driver_id']=abs((int)$_POST['driver_id']);
	$params['code_id']=abs((int)$_POST['code_id']);
	$params['responsible_user_id']=abs((int)$_POST['responsible_user_id']);
	
	$params['value']=abs((float)$_POST['value']);	
	
	
	$params['time_from_h']=abs((int)$_POST['time_from_h']);
	$params['time_from_m']=abs((int)$_POST['time_from_m']);
	$params['time_to_h']=abs((int)$_POST['time_to_h']);
	$params['time_to_m']=abs((int)$_POST['time_to_m']);
	
	
	$code=$_ci->add($params);
	
	
	$_cbi=new CashToBillItem;
	
	foreach($_POST['selected_bills'] as $k=>$v){
		$cparams=array();
		$cparams['bill_id']=abs((int)$v);
		$cparams['cash_id']=$code;
		
		$_cbi->Add($cparams);
		
		
		$bill=$_bill->getitembyid($cparams['bill_id']);
		
		
		$log->PutEntry($result['id'], 'создал экспедирование по исходящему счету', NULL, 835, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'], $code);
	
		$log->PutEntry($result['id'], 'создал экспедирование по исходящему счету', NULL, 93, NULL, 'расход № '.$params['code'].', счет № '.$bill['code'], $bill['id']);
		
		
		foreach($params as $k=>$v){
			
		   
				$log->PutEntry($result['id'],'создал экспедирование по исходящему счету',NULL,835, NULL, 'в поле '.$k.' установлено значение '.$v,$code);	
				
				$log->PutEntry($result['id'],'создал экспедирование по исходящему счету',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v, $bill['id']);		
			 
		}
	}
	
	
	
		
}


elseif(isset($_POST['action'])&&($_POST['action']=="redraw_resp_users")){
	$_ug=new usersgroup;
	
	$ret=$_ug-> GetItemsOpt(0,'name_s', true, '-выберите-');
	
}


// РАБОТА С %
elseif(isset($_POST['action'])&&($_POST['action']=="add_cash_percent")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',851)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$_cpi=new CashPercentItem;
	
	$params=array();
	$params['org_id']=$result['org_id'];
	$params['begin_pdate']=Datefromdmy($_POST['begin_pdate']);
	
	 
	$params['percent']=abs((float)str_replace(',','.', $_POST['percent']));	
	$params['notes']=SecStr(iconv('utf-8', 'windows-1251', $_POST['notes']));
	
	
	
	$code=$_cpi->add($params);
	
	 
		
		 
	$log->PutEntry($result['id'], 'создал % вывода наличных', NULL, 851, NULL, 'действие с '.$_POST['begin_pdate'].', '.$params['percent'].'%', $code);
	 
	
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="del_cash_percent")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',851)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$id=abs((int)$_POST['id']);	
	
	
	$_cpi=new CashPercentItem;
	
	$cpi=$_cpi->GetItemById($id);
	
	$_cpi->Del($id);
	
	$log->PutEntry($result['id'], 'удалил % вывода наличных', NULL, 851, NULL, 'действие с '.date('d.m.Y',$cpi['begin_pdate']).', '.$cpi['percent'].'%', $id);
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_cash_percent")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',851)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$_cpi=new CashPercentItem;
	
	$id=abs((int)$_POST['id']);	
	
	
	$params=array();
	$params['org_id']=$result['org_id'];
	$params['begin_pdate']=Datefromdmy($_POST['begin_pdate']);
	
	 
	$params['percent']=abs((float)str_replace(',','.', $_POST['percent']));	
	$params['notes']=SecStr(iconv('utf-8', 'windows-1251', $_POST['notes']));
	
	
	
	$code=$_cpi->Edit($id, $params);
	
	 
		
		 
	$log->PutEntry($result['id'], 'редактировал % вывода наличных', NULL, 851, NULL, 'действие с '.$_POST['begin_pdate'].', '.$params['percent'].'%', $code);
	 
	
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="get_avail_bills")){
 
		$sm1=new SmartyAj;
		
		$_bills= new CashGroup;
		$dec_bills=new DBDecorator();
		
		$dec_bills->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		$dec_bills->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		$dec_bills->AddEntry(new SqlEntry('p.is_confirmed_shipping',1, SqlEntry::E));
		
		/*$bills_to_cash1=$_bills->ShowPos('bills/bills_list.html', 
			$dec_bills,
			0,
			100000, 
			false, 
			false, 
			false, 
			'', 
			false,
			false, 
			true, 
			false, 
			false,
			NULL,
			NULL, 
			false, 
			false, 
			false, 
		$bills_to_cash);*/
		$bills_to_cash=$_bills->ShowBillsForCash($dec_bills, abs((int)$_POST['kind_id']), $_POST['checked']);
		
		//наложить уже выбранные счета и исключить счета, занятые в других расходах данного вида
		
		
		$sm1->assign('fieldname',$_POST['fieldname']);
		$sm1->assign('bills_to_cash',$bills_to_cash);
		//$sm1->assign('
		
		$ret=$sm1->fetch('cash/bills_for_cash.html');
		 
}
elseif(isset($_POST['action'])&&($_POST['action']=="put_bills_to_cash")){
 
		$sm1=new SmartyAj;
		
		$_bills= new billgroup;
		$dec_bills=new DBDecorator();
		
		$dec_bills->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		$dec_bills->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
		$dec_bills->AddEntry(new SqlEntry('p.is_confirmed_shipping',1, SqlEntry::E));
		
		$dec_bills->AddEntry(new SqlEntry('p.id',NULL, SqlEntry::IN_VALUES,NULL,$_POST['checked']));
		
		 $bills_to_cash1=$_bills->ShowPos('bills/bills_list.html', 
			$dec_bills,
			0,
			100000, 
			false, 
			false, 
			false, 
			'', 
			false,
			false, 
			true, 
			false, 
			false,
			NULL,
			NULL, 
			false, 
			false, 
			false, 
		$bills_to_cash); 
		 
		//наложить уже выбранные счета и исключить счета, занятые в других расходах данного вида
		
		
		$sm1->assign('fieldname',$_POST['fieldname']);
		$sm1->assign('bills',$bills_to_cash);
		//$sm1->assign('
		
		$ret=$sm1->fetch('cash/bills_in_cash.html');
		 
}


   
//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Cash_ViewGroup;
	$_view=new Cash_ViewItem;
	
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
	$_views=new Cash_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>