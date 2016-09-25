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

 
require_once('../classes/cash_in_codegroup.php');
require_once('../classes/cash_in_notesgroup.php');
require_once('../classes/cash_in_notesitem.php');
require_once('../classes/cash_in_item.php');
require_once('../classes/user_s_item.php');

require_once('../classes/cash_in_group.php');


require_once('../classes/cash_in_codeitem.php');

require_once('../classes/supcontract_item.php');
require_once('../classes/supcontract_group.php');


require_once('../classes/paycodeitem.php');
require_once('../classes/paycodegroup.php');


require_once('../classes/usersgroup.php');


require_once('../classes/cash_in_creator.php');
require_once('../classes/billitem.php');

require_once('../classes/cash_to_bill_item.php');

require_once('../classes/cash_percent_item.php');


require_once('../classes/billgroup.php');


require_once('../classes/cash_in_view.class.php');


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
	
	$rg=new CashInNotesGroup;
	
	$sm->assign('items', $rg->GetItemsByIdArr($user_id,0,0, false,$au->user_rights->CheckAccess('w',899), $au->user_rights->CheckAccess('w',899),$result['id']));  //$rg->GetItemsByIdArr($user_id,0,0,false,false,false,$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',887));
	
	
	$ret=$sm->fetch('cash_in/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',887)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new CashInNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания к приходу наличных', NULL,887, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',887)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new CashInNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания к приходу наличных', NULL,887,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',887)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new CashInNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания к приходу наличных', NULL,887,NULL,NULL,$user_id);
	
}
//utv- razutv
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$id=abs((int)$_POST['id']);
	$_ti=new CashInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	if($trust['confirm_pdate']==0) $trust['confirm_pdate']='-';
	else $trust['confirm_pdate']=date("d.m.Y H:i:s",$trust['confirm_pdate']);
	
	
	 
	
	if($trust['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',893))){
			if(($trust['status_id']==2)){
				$_ti->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true, $result);
				
				$log->PutEntry($result['id'],'снял утверждение заполнения',NULL,893, NULL, NULL,$id);
				
					
			}
		} 
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',892)){
			if(($trust['status_id']==1)){
				$_ti->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил заполнение',NULL,892, NULL, NULL,$id);	
				
				 
			}
		} 
	}
	
	
	
	
	$shorter=abs((int)$_POST['shorter']);
	 
	 
	
		   $prefix='_cash_in';
		 $template='cash_in/all_cash_list.html';
	 
	
		 $acg=new CashInGroup;
	$acg->prefix=$prefix;
	 $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  $acg->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
		$acg->SetAuthResult($result);
		$ret=$acg->ShowAllPos('cash_in/all_cash_list.html', $dec, 
		
		$au->user_rights->CheckAccess('w',886)||$au->user_rights->CheckAccess('w',898),  
		$au->user_rights->CheckAccess('w',896) ,0, 1000,
		$au->user_rights->CheckAccess('w',892) ,  
		false , false,true, 
		$au->user_rights->CheckAccess('w',897) ,  
		$au->user_rights->CheckAccess('w',893),
		
		$au->user_rights->CheckAccess('w',885),
		$au->user_rights->CheckAccess('w',894),
		$au->user_rights->CheckAccess('w',895),
		$some,
		$au->user_rights->CheckAccess('w',898),
		$au->user_rights->CheckAccess('w',898)
	
		);

		
}

elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm_given")){
	$id=abs((int)$_POST['id']);
	$_ti=new CashInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	 
	 
	
	if($trust['is_confirmed_given']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',895))){
			if(($trust['status_id']==19)){
			if($_ti->DocCanUnconfirmGiven($id, $reas)){
			
				$_ti->Edit($id,array('is_confirmed_given'=>0, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение прихода',NULL,895, NULL, NULL,$id);
				
			}
				
			}
		} 
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',894)){
			if(($trust['status_id']==2)){
			if($_ti->DocCanConfirmGiven($id,$reas)){
				$_ti->Edit($id,array('is_confirmed_given'=>1, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил приход',NULL,894, NULL, NULL,$bill_id);	
				//	echo 'zzzzzzzzzzzzzzzzzzzzzzz';	
			}
			}
		} 
	}
	
	
	
		   $prefix='_cash_in';
		 $template='cash_in/all_cash_list.html';
	 
	
		 $acg=new CashInGroup;
	$acg->prefix=$prefix;
	 $dec=new  DBDecorator;
	  
	  $dec->AddEntry(new SqlEntry('p.id',$id, SqlEntry::E));
	  
	  $acg->SetPageName('all_pay.php');
	
	//$_pp->AutoAnnul();
		$acg->SetAuthResult($result);
		$ret=$acg->ShowAllPos('cash_in/all_cash_list.html', $dec, 
		
		$au->user_rights->CheckAccess('w',886)||$au->user_rights->CheckAccess('w',898),  
		$au->user_rights->CheckAccess('w',896) ,0, 1000,
		$au->user_rights->CheckAccess('w',892) ,  
		false , false,true, 
		$au->user_rights->CheckAccess('w',897) ,  
		$au->user_rights->CheckAccess('w',893),
		
		$au->user_rights->CheckAccess('w',885),
		$au->user_rights->CheckAccess('w',894),
		$au->user_rights->CheckAccess('w',895),
		$some,
		$au->user_rights->CheckAccess('w',898),
		$au->user_rights->CheckAccess('w',898)
		);

	 
		
}


//udalenie-annulirovabie
elseif(isset($_POST['action'])&&($_POST['action']=="toggle_annul")){
	$id=abs((int)$_POST['id']);
	$bill_id=abs((int)$_POST['bill_id']);
	
	$shorter=abs((int)$_POST['shorter']);
	 
		   $prefix='_cash_in';
		 $template='cash_in/all_cash_list.html';
	 
	
	 $acg=new CashInGroup;
	$acg->prefix=$prefix;
	
	$note=SecStr(iconv("utf-8","windows-1251",$_POST['note']));
	
	
	if(isset($_POST['from_card'])&&($_POST['from_card']==1)) $from_card=1;
	else $from_card=0;
	
	$_ti=new CashInItem;
	
	$_si=new UserSItem;
	
	$trust=$_ti->getitembyid($id);
	
	$log=new ActionLog;
	$_stat=new DocStatusItem;
	
	
	if(($trust['status_id']==1)&&($trust['is_confirmed']==0)){
		//удаление	
		if($au->user_rights->CheckAccess('w',896)){
			$_ti->Edit($id,array('status_id'=>3, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));	
			
			$stat=$_stat->GetItemById(3);
			 
			
			$log->PutEntry($result['id'],'аннулирование прихода наличных',NULL,896,NULL,'приход наличных № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);
			
			
			//внести примечание
			$_ni=new CashInNotesItem;
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
		if($au->user_rights->CheckAccess('w',897)){
			$_ti->Edit($id,array('status_id'=>1, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));
			
			$stat=$_stat->GetItemById(1);
			 	
			
			$log->PutEntry($result['id'],'восстановление прихода наличных',NULL,897,NULL,'приход наличных № '.$trust['code'].': установлен статус '.$stat['name'],$trust['id']);	
			
			//внести примечание
			$_ni=new CashInNotesItem;
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
		$ret=$acg->ShowAllPos('cash_in/all_cash_list.html', $dec, 
		
		$au->user_rights->CheckAccess('w',886)||$au->user_rights->CheckAccess('w',898),  
		$au->user_rights->CheckAccess('w',896) ,0, 1000,
		$au->user_rights->CheckAccess('w',892) ,  
		false , false,true, 
		$au->user_rights->CheckAccess('w',897) ,  
		$au->user_rights->CheckAccess('w',893),
		
		$au->user_rights->CheckAccess('w',885),
		$au->user_rights->CheckAccess('w',894),
		$au->user_rights->CheckAccess('w',895),
		$some,
		$au->user_rights->CheckAccess('w',898),
		$au->user_rights->CheckAccess('w',898)
	
		);
			
	}else{
		$editing_user=$_ti->getitembyid($id);
		$sm=new SmartyAj;
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_ti->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',896);
		if(!$au->user_rights->CheckAccess('w',896)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['can_restore']=$_ti->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',897);
			if(!$au->user_rights->CheckAccess('w',897)) $reason='недостаточно прав для данной операции';
		
		//var_dump($editing_user);
		 
		$sm->assign('ship',$editing_user);
		$ret=$sm->fetch('cash_in/toggle_annul_card.html');	
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
	
	
	
	$ret=$_pg->GetItemsForBill('cash_in/suppliers_list.html',  $dec,true,$all7,$result);
	

	
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
		
	
		
		$_ki=new CashInItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirm($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashInItem;
		
		
		if(!$_ki->DocCanUnConfirm($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_confirm_given")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashInItem;
		
		 //CanConfirmByPositions($id,$rss)) $ret=$rss;
		if(!$_ki->DocCanConfirmGiven($id,$rss12)) $ret=$rss12;
		else $ret=0;
		
		
		//если ноль - то все хорошо
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_given")){
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new CashInItem;
		
		
		if(!$_ki->DocCanUnConfirmGiven($id,$rss13)) $ret=$rss13;
		else $ret=0;
		
		
		//если ноль - то все хорошо
}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_codes")){
	$sm=new SmartyAj;
	if(isset($_POST['current_id'])) $current_id=abs((int)$_POST['current_id']);
	else $current_id=0;
	
	$opg=new CashInCodeGroup;
	$pos= $opg->GetItemsArr(0, $current_id);
	$sm->assign('codespos',$pos);
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',272));
	
	
	$ret=$sm->fetch('cash_in/code_list.html');
	
	
}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_code")){
	$_si=new CashInCodeItem;
	
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

    

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_resp_users")){
	$_ug=new usersgroup;
	
	$ret=$_ug-> GetItemsOpt(0,'name_s', true, '-выберите-');
	
}

   
//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Cash_In_ViewGroup;
	$_view=new Cash_In_ViewItem;
	
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
	$_views=new Cash_In_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>