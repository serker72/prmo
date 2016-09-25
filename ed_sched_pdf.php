<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

 

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

 

require_once('classes/user_s_item.php');

 

 
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

 

require_once('classes/suppliercontactitem.php');
require_once('classes/supcontract_group.php');

require_once('classes/sched.class.php');

 
require_once('classes/sched_filegroup.php');
require_once('classes/sched_fileitem.php');

require_once('classes/supplier_country_group.php');
require_once('classes/supplier_city_item.php');
require_once('classes/schednotesgroup.php');

require_once('classes/sched_history_group.php');

require_once('classes/supplier_ruk_item.php');

require_once('classes/suppliercontactitem.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactdataitem.php');
require_once('classes/suppliercontactitem.php');
require_once('classes/usercontactdataitem.php');
require_once('classes/phpmailer/class.phpmailer.php');



require_once('classes/sched_history_fileitem.php');


$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



 

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


$_dem=new Sched_AbstractItem;

$_plan=new Sched_Group;
 

$_supplier=new SupplierItem;
 $log=new ActionLog;
 $_supgroup=new SuppliersGroup;

 
$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

$object_id=array();
switch($action){
	case 0:
	$object_id[]=904;
	break;
	case 1:
	$object_id[]=905;
	break;
	case 2:
	$object_id[]=905;
	break;
	default:
	$object_id[]=905;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;
$_editable_status_id[]=9;
$_editable_status_id[]=18;

if(isset($_GET['addresses'])){
	$addresses=$_GET['addresses'];
}else $addresses='';

//массив адресатов
$_addresses=explode(',',$addresses);


 
//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
} 

$print=1;

$print_add='_print';

 
 

	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_dem->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	$available_users=$_plan->GetAvailableUserIds($result['id'],false,$editing_user['kind_id']);	 
 
	
	 
 
 	$log=new ActionLog;
	 
	
	 
 
 
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	 
		
		
		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		if($editing_user['pdate_beg']!='')  $editing_user['pdate_beg']=datefromYmd($editing_user['pdate_beg']);
		
		if($editing_user['pdate_end']!='')  $editing_user['pdate_end']=datefromYmd($editing_user['pdate_end']);
		
		$editing_user['has_exp_pdate']=($editing_user['pdate_beg']!='');
		
		$_uis=new UserSItem; $uis=$_uis->getitembyid($editing_user['manager_id']);
		$sm1->assign('manager_string', $uis['name_s']);
		
		
		//подтянуть: напоминание, адресатов
		$_rem=new SchedRemindItem;
		$rem=$_rem->GetItemByFields(array('sched_id'=>$id, 'user_id'=>$result['id']));
		
		$editing_user['remind_do']=(int)($rem!==false);
		if($rem!==false){
			$editing_user['remind_pdate']=date('d.m.Y', $rem['action_time']);
			$editing_user['remind_ptime']=date('H:i', $rem['action_time']);
			
			$sm1->assign('remind_ptime_hr',date('H', $rem['action_time']));
			$sm1->assign('remind_ptime_mr',date('i', $rem['action_time'])); 
		}
		
		//адресат
		$_addr=new SchedContactItem;
		$addr=$_addr->GetItemByFields(array('sched_id'=>$id));
		
		//var_dump($addr);
		if($addr!==false){
		 
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($addr['contact_id']);
			 
			
			
			$editing_user['supplier_id']=$addr['supplier_id'];
			$editing_user['contact_id']=$addr['contact_id'];
			$editing_user['ccontact_value']=$addr['value'];
			$editing_user['supplier_string']=$opf['name'].' '.$si['full_name'];
			$editing_user['contact_string']=$sci['name'].', '.$sci['position'];
			$editing_user['contact_value_string']=$addr['value'];
		}
		
	 
	    $from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		
		//возможность редактировать результаты и напоминание
		if(($editing_user['kind_id']==1)||($editing_user['kind_id']==4)||($editing_user['kind_id']==2)||($editing_user['kind_id']==3) ){
			$sm1->assign('ptime_beg_hr',substr($editing_user['ptime_beg'],  0,2 ));
			$sm1->assign('ptime_beg_mr',substr($editing_user['ptime_beg'],  3,2 )); 
			
			$sm1->assign('ptime_end_hr',substr($editing_user['ptime_end'],  0,2 ));
			$sm1->assign('ptime_end_mr',substr($editing_user['ptime_end'],  3,2 )); 
			
			
			 
		}
		
		
		if(($editing_user['kind_id']==2)||($editing_user['kind_id']==3) ){
			//города
			$_csg=new Sched_CityGroup;
			$csg=$_csg->GetItemsByIdArr($editing_user['id']);
			$sm1->assign('cities', $csg);
			
			//контрагенты
			$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
			$sm1->assign('suppliers', $sup);
		}
	
		if($editing_user['kind_id']==3){
			//места встречи
			$_meets=new Sched_MeetGroup;
			$meets=$_meets->GetItemsArr();
			$meet_ids=array(); $meet_names=array();
			foreach($meets as $k=>$v){ $meet_ids[]=$v['id']; $meet_names[]=$v['name']; }
			$sm1->assign('meet_ids', $meet_ids); $sm1->assign('meet_names', $meet_names); 
			
			$_meet=new Sched_KindMeetItem;
			$meet=$_meet->getitembyid($editing_user['meet_id']);
			$editing_user['meet_name']= $meet['name'];	
			 	
		}
		
		
		if(($editing_user['kind_id']==5) ||($editing_user['kind_id']==1)){
			//контрагенты
			$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
			$sm1->assign('suppliers', $sup);
		}
		

		
		
			
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm1->assign('cous', $cous); 
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_done']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',905)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',905)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//блок утв. отгрузки
		if(($editing_user['is_confirmed_done']==1)&&($editing_user['user_confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_done_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['confirm_done_pdate']);
			
			$sm1->assign('is_confirmed_done_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_confirmed_done']==1){
			  if($au->user_rights->CheckAccess('w',905)){
				  //есть права + сам утвердил
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_done',$can_confirm_shipping);
		
		
		//блок утв. принятия
		if(($editing_user['is_fulfiled']==1)&&($editing_user['user_confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_fulfiled_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['fulfiled_pdate']);
			
			$sm1->assign('is_fulfiled_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed_done']==1){
		
		  if($editing_user['is_fulfiled']==1){
			  if($au->user_rights->CheckAccess('w',905)){
				  //есть права + сам утвердил
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed_done']==1);
		
		
		$sm1->assign('can_confirm_done',$can_confirm_shipping);
		
		
	
	
		
		
		
		
		
		//лента задачи
		$len_dec=new DBDecorator();
		$len_dec->AddEntry(new SqlOrdEntry('o.pdate',SqlOrdEntry::ASC));
		$_hg=new Sched_HistoryGroup;
		$sm1->assign('lenta', $_hg->ShowHistory(
			$editing_user['id'],
			 'plan/lenta'.$print_add.'.html', 
			$len_dec, 
			 $field_rights['can_ed_notes'],
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',906),
			 $au->user_rights->CheckAccess('w',907)
			 ));
		
		
		//отв., пост-к,
		
		$_ug=new Sched_UsersSGroup;
		$dec=new DBDecorator;
	
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
		}
		
		
		$sm1->assign('users', $_ug->GetItemsForBill($dec));
		
		
	 //отв
		$_bu=new Sched_TaskUserItem;
		$bu=$_bu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>2));
		$editing_user['user_2']=$bu['user_id'];
		 
		
		//постан-к
		$bu=$_bu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>1));
		$editing_user['user_1']=$bu['user_id'];
		
		
		//соисполнители
		$_bg=new Sched_TaskUserGroup;
		$bg=$_bg->GetItemsByIdArr($id,3);
		$sm1->assign('soisp', $bg);
		
		//наблюдатели
		$bg=$_bg->GetItemsByIdArr($id,4);
		$sm1->assign('nablud', $bg);
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		$sm1->assign('can_modify', $can_modify);  
		
		$sm1->assign('can_share', ($editing_user['manager_id']==$result['id']));
		
		
		$sm1->assign('statuses', $_dem->GetStatuses($editing_user['status_id']));
		
		
		//группа пол-лей, с кем делимся:
		$sm1->assign('shares', $_dem->GetUsersArr($id, $editing_user));
		
		//прикрепленные файлы
		if(($editing_user['kind_id']==5)||($editing_user['kind_id']==1)){
			 //файлы 
			 $can_modify_files=$can_modify;
			 
			  if(isset($_GET['folder_id'])) $folder_id=abs((int)$_GET['folder_id']);
			  else $folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 // $decorator->AddEntry(new SqlEntry('id',$id, SqlEntry::E));
				$decorator->AddEntry(new UriEntry('id',$id));
			  //$decorator->AddEntry(new SqlEntry('user_d_id',$user_id, SqlEntry::E));
			  
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			  else $from=0;
			  
			  if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			  else $to_page=ITEMS_PER_PAGE;
			  
			  $ffg=new SchedFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new SchedFileItem(1)));;
			  
			  if(($editing_user['kind_id']==5)) $template='doc_file/incard_list_print.html';
			  else  $template='plan/task_files_list_print.html';
			  
			  
			  $filetext=$ffg->ShowFiles($template, $decorator,$from,$to_page,'ed_sched.php', 'sched_file.html', 'swfupl-js/sched_files.php',  
			  $can_modify_files,  
			  $can_modify_files, 
			 $can_modify_files , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 $can_modify_files,  
			   $result, 
			   $navi_dec, 'file_' 
			   );
				
			
				
			$sm1->assign('files', $filetext);
		}
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		
		$sm1->assign('bill', $editing_user);
		
		
		$sm1->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm1->assign('print_username',$username);
		
		
		
		switch($editing_user['kind_id']){
			case 1:	
				$html=$sm1->fetch('plan/edit_kind_1'.$print_add.'.html');
			break;
			case 2:	
				$html=$sm1->fetch('plan/edit_kind_2'.$print_add.'.html');
			break;
			
			case 3:	
				$html=$sm1->fetch('plan/edit_kind_3'.$print_add.'.html');
			break;
			case 4:	
				$html=$sm1->fetch('plan/edit_kind_4'.$print_add.'.html');
			break;
			case 5:	
				$html=$sm1->fetch('plan/edit_kind_5'.$print_add.'.html');
			break;
		
		}
	
	
	  
	/*	
	 echo $html; 
	die(); 
  */
	
	$tmp=time();
	
	$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
	fputs($f, $html);
	fclose($f);
	
	$cd = "cd ".ABSPATH.'/tmp';
	exec($cd);
	
	
	//скомпилируем подвал
	$sm=new SmartyAdm;
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
		//$username=$result['login'];
		$username=stripslashes($result['name_s']); //.' '.$username;	
		$sm->assign('print_username',$username);
	$foot=$sm->fetch('plan_pdf/pdf_footer.html');
	$ftmp='f'.time();
	
	$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
	fputs($f, $foot);
	fclose($f);
	
	
	$comand = "wkhtmltopdf-i386 --encoding windows-1251 --page-size A4 --margin-top 73mm --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html --header-html ".SITEURL."/tpl-sm/plan_pdf/pdf_header.html  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
	
 

exec($comand);
	
	$_ki=new SchedKindItem;
	$ki=$_ki->GetItemById($editing_user['kind_id']);
		 
	if(isset($_GET['send_email'])&&($_GET['send_email']==1)&&isset($_GET['email'])){
		$emails=$_GET['email'];
		$_addresses=explode(',',$emails);
		
		 
		$filename=ABSPATH.'/tmp/'."$tmp.pdf";
		 
		
		$filenames_to_send=array();
		$filenames_to_send[]=array(
			'fullname'=>$filename,
			'name'=>'GYDEX_Планировщик_'.$ki['name'].'_'.$editing_user['code'].'.pdf'
		
		);
		
		
		if($_GET['with_files']==1){
			//получим файлы, прикрепленные к задаче
			$_ree_fi=new SchedFileItem;
			$_hi_fi=new Sched_HistoryFileItem;
			
			$sql='select * from sched_file where bill_id="'.$id.'" ';
	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();	
			$rc=$set->GetResultnumrows();
		
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$filenames_to_send[]=array(
					'fullname'=>$_ree_fi->GetStoragePath().$f['filename'],
					'name'=>$f['orig_name']
				
				);
			}
			
			$sql='select * from sched_history_file where history_id in(select id from sched_history where sched_id="'.$id.'" )';
	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();	
			$rc=$set->GetResultnumrows();
		
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$filenames_to_send[]=array(
					'fullname'=>$_hi_fi->GetStoragePath().$f['filename'],
					'name'=>$f['orig_name']
				
				);
			}
		}
		 
		
		 $_filenames=array();
		foreach($filenames_to_send as $k=>$v) $_filenames[]=$v['name'];
		
		//var_dump($_filenames); 
		 
		$org=$_orgitem->Getitembyid($result['org_id']);
		$opf=$_opf->getitembyid($org['opf_id']);
		//использовать класс отправки сообщения
		foreach($_addresses as $k=>$email){
			
			//найти ФИО по адресу эл.почты...
			//1) в карте к-та
			$has_cont=false; $user_name='контрагент';
			/*$_sdi=new SupplierContactDataItem;
			$sdi=$_sdi->GetItemByFields(array('value'=>$email));
			if($sdi!==false){
				$_sci=new SupplierContactItem;
				$sci=$_sci->GetItemById($sdi['contact_id']);
				if($sci!==false){
					$user_name=$sci['name'];
					$has_cont=true;
				}
			}*/
			
			//2) в карте сотр-ка
			if(!$has_cont){
				$_uci=new UserContactDataItem;
				$_ui=new UserItem;
				$uci=$_uci->GetItemByFields(array('value'=>$email));
				$ui=$_ui->GetItemById($uci['user_id']);
				if($ui!==false) $user_name=$ui['name_s'];
				
			}
			
			
			
			
			
			$mail = new PHPMailer();
			$body = "<div>Уважаемый(ая) %contact_name%!</div> <div>&nbsp;</div> <div><i>Это сообщение сформировано автоматически, просьба не отвечать на него.</i></div> <div>&nbsp;</div> <div>Отправляем Вам следующие документы: %docs%.</div> <div>&nbsp;</div> <div>Благодарим Вас за то, что Вы обратились к нам!</div> <div>С уважением, компания %opf_name% %company_name% .</div>
 "; 
 			
			$body=str_replace('%contact_name%',  $user_name,$body);
			$body=str_replace('%docs%', implode(', ',$_filenames),  $body);
			$body=str_replace('%company_name%', $org['full_name'],  $body);
			$body=str_replace('%opf_name%', $opf['name'],  $body);
			
			
		
			$mail->SetFrom($org['feedback_email'], $opf['name'].' '.$org['full_name']);
		
			  
		
			$mail->AddAddress(trim($email),  $email);
		
			$mail->Subject = "документы для Вас!"; 
			$mail->Body=$body;
			
			//echo $body;
			
			foreach($filenames_to_send as $k=>$v) $mail->AddAttachment($v['fullname'], $v['name']);  
			 
			$mail->CharSet = "windows-1251";
			$mail->IsHTML(true);  
			
			if(!$mail->Send())
			{
				//echo "Ошибка отправки письма: " . $mail->ErrorInfo;
			}
			else 
			{
				// echo "Письмо отправленно!";
			} 
			
		 
		 	$log->PutEntry($result['id'],'отправил на электронную почту pdf-версию записи планировщика',NULL,905, NULL, 'запись планировщика № '.$editing_user['code'].', адрес эл. почты '.$email,$id);
			
		}	
		 
		
		 
		//перейти в карту  
		/*if(!isset($_GET['doClose'])){
			header("Location: ed_sched.php?action=1&id=".$editing_user['id'].'&from_begin=1');
		}else{*/
			/*echo '<script type="text/javascript"> alert("Запись планировщика была отправлена на адреса электронной почты: '.$_GET['email'].'"); window.close();</script>';	*/
			
			$sm=new SmartyAdm;
			
			$txt='';
			$txt.='<div><strong>Запись планировщика была отправлена на следующие адреса:</strong></div>';
			$txt.='<ul>';
			
			foreach($_addresses as $k=>$email){
				$txt.='<li>'.$email.'</li>';
			}
			$txt.='</ul>';
			
			if(count($_filenames)>0){
				$txt.='<div>&nbsp;</div>';
				$txt.='<div><strong>Были приложены следующие файлы:</strong></div>';
				$txt.='<ul>';
				foreach($_filenames as $k=>$file){
					$txt.='<li>'.$file.'</li>';
				}
				$txt.='</ul>';
			}
			
		 
			//$txt.='<p></p>';			
			
			$sm->assign('message', $txt);
			
			$sm->display('page_email.html');
			
		//}
		//die();	
		
		
	}else{
		
	
		$log->PutEntry($result['id'],'получил pdf-версию записи планировщика',NULL,905, NULL, 'запись планировщика № '.$editing_user['code'],$id);
		 
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="GYDEX_Планировщик_'.$ki['name'].'_'.$editing_user['code'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	}
	


	unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
	unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	unlink(ABSPATH.'/tmp/'.$ftmp.'.html');
	
	exit;


?>