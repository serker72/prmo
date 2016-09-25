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

require_once('classes/invcalcitem.php');

require_once('classes/user_s_item.php');

require_once('classes/paygroup.php');

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/invcalcnotesgroup.php');
require_once('classes/invcalcnotesitem.php');
require_once('classes/invcalcreasgroup.php');
require_once('classes/invcalcbindedgroup.php');

require_once('classes/invcalccreator.php');

require_once('classes/an_supplier.php');

//require_once('classes/payforbillgroup.php');
require_once('classes/period_checker.php');

require_once('classes/pergroup.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование инвентаризационного акта');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new InvCalcItem;


$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;


$lc=new InvCalcCreator;

$_supgroup=new SuppliersGroup;
$_icreasons=new InvCalcReasGroup;

$_opf=new OpfItem;
if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',462)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;


$object_id=array();
switch($action){
	case 0:
	$object_id[]=451;
	break;
	case 1:
	$object_id[]=451;
	$object_id[]=452;
	break;
	case 2:
	$object_id[]=452;
	break;
	default:
	$object_id[]=451;
	break;
}
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


$_editable_status_id=array();
$_editable_status_id[]=1;


if($action==0){
	$orgitem=$_orgitem->getitembyid($result['org_id']);	
}

if(($action==1)||($action==2)){
	
	
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_bill->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
}


//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту инвентаризационного акта',NULL,451, NULL, $editing_user['code'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту инвентаризационного акта: версия для печати',NULL,452, NULL, $editing_user['code'],$id);
				
}




if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',451)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	
	$params['invcalc_pdate']=  DateFromdmY($_POST['invcalc_pdate']);
	$params['pdate']=time();
	
	
	
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	//$params['notes']=SecStr($_POST['notes']);
	$params['code']=SecStr($_POST['code']);
	
	
	$params['is_confirmed']=0;
	$params['is_confirmed_inv']=0;
	
	
	$params['manager_id']=$result['id'];
	
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	
	
	$params['reason_id']=abs((int)$_POST['reason_id']);
	if(isset($_POST['reason_txt'])) $params['reason_txt']=SecStr($_POST['reason_txt']);
	
	if(isset($_POST['akt_given_pdate'])) $params['akt_given_pdate']=DateFromdmY($_POST['akt_given_pdate']);
	if(isset($_POST['akt_given_no'])) $params['akt_given_no']=SecStr($_POST['akt_given_no']);
	
	
	if(isset($_POST['debt'])) $params['debt']=round((float)str_replace(",",".", $_POST['debt']),2);
	if(isset($_POST['debt_id'])) $params['debt_id']=abs((int)$_POST['debt_id']);
	
	
	$code=$_bill->Add($params);
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал инвентаризационный акт',NULL,451,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		 
				if($k=='supplier_id'){
					$_si=new SupplierItem; $_opf=new OpfItem;
					$si=$_si->GetItemById($v); $opf=$_opf->GetItemById($si['opf_id']);
					
					
					$log->PutEntry($result['id'],'создал инвентаризационный акт',NULL,451, NULL, SecStr('установлен контрагент '.$si['code'].' '.$opf['name'].' '.$si['full_name']),$code);			
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'создал инвентаризационный акт',NULL,451, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
	}
	
	
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: invent.php?tab_page=2#invcalc_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',452)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_invcalc.php?action=1&id=".$code.'&from_begin='.$from_begin.'&tab_page=1');
		die();	
		
	}else{
		header("Location: invent.php?tab_page=2");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',452)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//редактирование возможно, если is_confirmed==0
	
	
	$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id)&&($editing_user['is_confirmed']==0);
	
	if($condition){
		$params=array();
		//обычная загрузка прочих параметров
		
		
		
		if(isset($_POST['invcalc_pdate'])) $params['invcalc_pdate']=  DateFromdmY($_POST['invcalc_pdate']);
	
	
	
		if(isset($_POST['supplier_id'])) $params['supplier_id']=abs((int)$_POST['supplier_id']);		
		
		if(isset($_POST['given_no'])) $params['given_no']=SecStr($_POST['given_no']);
		
		
		if(isset($_POST['reason_id'])) $params['reason_id']=abs((int)$_POST['reason_id']);
		if(isset($_POST['reason_txt'])) $params['reason_txt']=SecStr($_POST['reason_txt']);
		
		if(isset($_POST['akt_given_pdate'])) $params['akt_given_pdate']=DateFromdmY($_POST['akt_given_pdate']);
		if(isset($_POST['akt_given_no'])) $params['akt_given_no']=SecStr($_POST['akt_given_no']);
		
		
		if(isset($_POST['debt'])) $params['debt']=round((float)str_replace(",",".", $_POST['debt']),2);
		if(isset($_POST['debt_id'])) $params['debt_id']=abs((int)$_POST['debt_id']);
		
		$_bill->Edit($id, $params);
		
	
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				if($k=='invcalc_pdate'){
					$log->PutEntry($result['id'],'редактировал дату проведения корректировки взаиморасчетов',NULL,452,NULL,'дата: '.$_POST['invcalc_pdate'],$id);
					continue;	
				}
				
			
				
				$log->PutEntry($result['id'],'редактировал инвентаризационный акт',NULL,452, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
		
	}
	
	
	
	//утверждение заполнения
	
	if($editing_user['is_confirmed_inv']==0){
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',459))){
			  if((!isset($_POST['is_confirmed']))&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1)) ){
				  $_bill->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,false,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,459, NULL, NULL,$id);	
				 // $_bill->FreeBindedPayments($id);
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',458)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))){
				  $_bill->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,false,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение',NULL,458, NULL, NULL,$id);	
				  
				  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	//утверждение коррекции
	if($editing_user['is_confirmed']==1){
	  if($editing_user['is_confirmed_inv']==1){
		  
		  
		 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',461))){
			  
			  if((!isset($_POST['is_confirmed_inv']))&&in_array($editing_user['status_id'], array(1,16))&&in_array($_POST['current_status_id'], array(1,16)) ){
				if($_bill->DocCanUnconfirmShip($id,$rss)){  
				  
				  $_bill->Edit($id,array('is_confirmed_inv'=>0, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,false,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение коррекции',NULL,461, NULL, NULL,$id);	
				}
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',460)){
			  if(isset($_POST['is_confirmed_inv'])&&in_array($editing_user['status_id'], array(1,16))&&in_array($_POST['current_status_id'], array(1,16))){
				  
				  $bind_to_bills=isset($_POST['bind_to_bills'])&&($_POST['bind_to_bills']==1);
				  
				  $_bill->Edit($id,array('is_confirmed_inv'=>1, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true, $bind_to_bills,$result);
				  
				  $log->PutEntry($result['id'],'утвердил коррекцию',NULL,460, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: invent.php?tab_page=2#invcalc_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',452)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_invcalc.php?action=1&id=".$id.'&from_begin='.$from_begin.'&tab_page=1');
		die();	
		
	}else{
		header("Location: invent.php?tab_page=2");
		die();
	}
	
	die();
}






//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=39;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	$opf=$_opf->GetItemById($orgitem['opf_id']);
	
	
	
	if($action==0){
		//создание инвентаризацц
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		//поставщики
		$suppliers=$_supgroup->GetItemsWithOpfArr(false, $result['org_id']);
		$_s_ids=array(0); $_s_names=array('-выберите-');
		foreach($suppliers as $k=>$v){
			$_s_ids[]=$v['id'];
			$_s_names[]=$v['full_name'].', '.$v['opf_name'];
		}
		$sm1->assign('supplier_names',$_s_names);
		$sm1->assign('supplier_ids',$_s_ids); 
		
		//причина
		$sm1->assign('reasons',$_icreasons->GetItemsArr());
		
		
		
		$lc->ses->ClearOldSessions();
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		$sm1->assign('can_modify',true); 
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',451)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',452)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		$user_form=$sm1->fetch('invcalc/invcalc_create.html');
		
		
		
		
 
 		$sm->assign('has_acc', true); //($editing_user['is_confirmed_shipping']==1));
		$sm->assign('accs','В данном режиме просмотр связанных документов по инвентаризационному акту недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать инвентаризационный акт и перейти к утверждению" на вкладке "Инвентаризация расчетов с поставщиками" для получения возможности просмотра связанных документов.');		
 		
 
		
			
		
		
		if($au->user_rights->CheckAccess('w',531)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','В данном режиме просмотр журнала событий по инвентаризационному акту недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать инвентаризационный акт и перейти к утверждению" на вкладке "Инвентаризация расчетов с поставщиками" для получения возможности просмотра журнала событий.');		
		}
		
		//строим вкладку администрирования
		/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',92)||
								$au->user_rights->CheckAccess('x',93)||
								$au->user_rights->CheckAccess('x',94)||
								$au->user_rights->CheckAccess('x',95)||
								$au->user_rights->CheckAccess('x',96)||
								$au->user_rights->CheckAccess('x',97)
								);
		$dto=new DiscrTableObjects($result['id'],array('92','93','94','95','96','97'));
		$admin=$dto->Draw('ed_bill.php','admin/admin_objects.html');
		$sm->assign('admin',$admin);*/
		
		
	}elseif($action==1){
		//редактирование позиции
		
		
		//строим вкладку администрирования
		/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',92)||
								$au->user_rights->CheckAccess('x',93)||
								$au->user_rights->CheckAccess('x',94)||
								$au->user_rights->CheckAccess('x',95)||
								$au->user_rights->CheckAccess('x',96)||
								$au->user_rights->CheckAccess('x',97)
								);
		$dto=new DiscrTableObjects($result['id'],array('92','93','94','95','96','97'));
		$admin=$dto->Draw('ed_bill.php','admin/admin_objects.html');
		$sm->assign('admin',$admin);*/
		
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$sm1=new SmartyAdm;
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		$editing_user['invcalc_pdate_unf']=$editing_user['invcalc_pdate'];
		$editing_user['invcalc_pdate']=date("d.m.Y",$editing_user['invcalc_pdate']);
		
		if($editing_user['akt_given_pdate']==0) $editing_user['akt_given_pdate']='-';
		else $editing_user['akt_given_pdate']=date("d.m.Y", $editing_user['akt_given_pdate']);
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		
		//поставщики
		$suppliers=$_supgroup->GetItemsWithOpfArr(false, $result['org_id']);
		$current_supplier=''; $current_supplier_for_print='';
		$_s_ids=array(0); $_s_names=array('-выберите-');
		foreach($suppliers as $k=>$v){
			$_s_ids[]=$v['id'];
			$_s_names[]=$v['full_name'].', '.$v['opf_name'];
			if($v['id']==$editing_user['supplier_id']){
				$current_supplier=	$v['full_name'].', '.$v['opf_name'];
				$current_supplier_for_print=$v['full_name'];
			}
		}
		$sm1->assign('supplier_names',$_s_names);
		$sm1->assign('supplier_ids',$_s_ids); 
		
		
		//$current_supplier=$_supplier->GetItemById($editing_user['supplier_id']);
		//$opfn=$_opf->GetItemById($current_supplier['opf_id']);
		$sm1->assign('current_supplier',$current_supplier);
		
		
		//причина
		$sm1->assign('reasons',$_icreasons->GetItemsArr());
		
		
		//задолженности
		$_ansup=new AnSupplier;
		$current_debt=$_ansup->OstBySup($editing_user['supplier_id'],$editing_user['invcalc_pdate_unf']+24*60*60-1,$result['org_id'],$editing_user['id']);
		//кому должны
		if($current_debt>0){
			$debt_to='(в пользу '.stripslashes($opf['name'].' '.$orgitem['full_name']).')';
				
		}elseif($current_debt<0){
			$debt_to='(в пользу '.$current_supplier.')';
		}else{
			$debt_to='(нулевые обороты)';
		}
		$editing_user['current_debt']=abs($current_debt);
		$editing_user['current_debt_to']=$debt_to;
		
		
		//echo date('d.m.Y H:i:s',1349035199);
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',463);
		if(!$au->user_rights->CheckAccess('w',463)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',464);
			if(!$au->user_rights->CheckAccess('w',464)) $reason='недостаточно прав для данной операции';
		
		
		
		//$sm1->assign('org',$orgitem['name']);
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id)&&($editing_user['is_confirmed']==0)); //$editing_user['is_confirmed']==0);
		
	
	
	
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
	
		//Примечания
		$rg=new InvCalcNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',454), $au->user_rights->CheckAccess('w',349),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',453));
		
		
		
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',462)); 
		//переменные для печати
		$sm1->assign('current_supplier_for_print',$current_supplier_for_print);
		$sm1->assign('begin_pdate_for_print',date('d.m.Y',$editing_user['invcalc_pdate_unf']-30*24*60*60));
		
		
		//блок утверждения заполнения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm=false;
		if($editing_user['is_confirmed_inv']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',458)){
				  //полные права
				  $can_confirm=true;	
			  }else{
				  $can_confirm=false;
			  }
		  }else{
			  //95
			  $can_confirm=$au->user_rights->CheckAccess('w',459)&&in_array($editing_user['status_id'],$_editable_status_id);
		  }
		}
		$sm1->assign('can_confirm',$can_confirm);
		
		
		//блок утв. отгрузки
		if(($editing_user['is_confirmed_inv']==1)&&($editing_user['user_confirm_inv_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_inv_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_inv_pdate']);
			
			$sm1->assign('is_confirmed_inv_confirmer',$confirmer);
		}
		
		$can_confirm_inv=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_confirmed_inv']==1){
			  if($au->user_rights->CheckAccess('w',461)){
				  //полные права
				  $can_confirm_inv=true;	
			  }else{
				  $can_confirm_inv=false;
			  }
		  }else{
			  //95
			  $can_confirm_inv=$au->user_rights->CheckAccess('w',460);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_inv=$can_confirm_inv&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_inv',$can_confirm_inv);
		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_bill->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
	/*	$ig=$_bill->sync->HasNotDifference($editing_user['id'],$reasons);
		
		var_dump($ig);
		echo $reasons;*/
	
		
		
		if($editing_user['is_confirmed_inv']==1){
			
			
			
			
			
			 
			
			//исход счета...
			$bg=new InvCalcBindedGroup;
			$bg->SetPageName('ed_invcalc.php');
			//Разбор переменных запроса
			if(isset($_GET['from_out'])) $from_bill=abs((int)$_GET['from_out']);
			else $from_bill=0;
			
			if(isset($_GET['to_page_out'])) $to_page_bill=abs((int)$_GET['to_page_out']);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			/*
			
			//блок фильтров статуса
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_bill_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}
*/
			$status_ids=array();
			$cou_stat=0;   
			if(isset($_GET['bill_statuses'])&&is_array($_GET['bill_statuses'])) $cou_stat=count($_GET['bill_statuses']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $status_ids=$_GET['bill_statuses'];
			  
			}else{
			  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $cou_stat++;
			  
			  if($cou_stat>0){
				  //есть кукисы
				  foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_bill_status_id_','',$k);
			  }else{
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }
		  }
		   
			 if(count($status_ids)>0){
				  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				  
				  if($of_zero){
					  //ничего нет - выбираем ВСЕ!	
					  $decorator->AddEntry(new UriEntry('all_statuses',1));
				  }else{
				  
					  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
					   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_statuses[]',$v));
				  }
			  } 
			
			
			if(!isset($_GET['pdate_out1'])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_out1'];
			
			
			
			if(!isset($_GET['pdate_out2'])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_out2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_out1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_out2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_out'])&&(strlen($_GET['code_out'])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_out']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_out',$_GET['code_out']));
			}
			
			
			
			if(isset($_GET['supplier_name_out'])&&(strlen($_GET['supplier_name_out'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_out']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name_out',$_GET['supplier_name_out']));
	}
			
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode_out'])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_out']);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new UriEntry('sortmode_out',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_out',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			
			
			
			$pays=$bg->ShowBills($editing_user['id'], 
			'bills/bills_list_komplekt.html',
			$decorator,$from,$to_page, 
			$au->user_rights->CheckAccess('w',92), 
			$au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), 
			$au->user_rights->CheckAccess('w',94),
			'_out',
			$au->user_rights->CheckAccess('w',95),
			$au->user_rights->CheckAccess('w',96),true,false,
			$au->user_rights->CheckAccess('w',131),
			$limited_sector, NULL,
			 $au->user_rights->CheckAccess('w',195),
			 $au->user_rights->CheckAccess('w',196), 
			 $au->user_rights->CheckAccess('w',197),$result, 0,
			$au->user_rights->CheckAccess('w',283) );
			
			
			
			
			
			
			//вход счета...
			$bg=new InvCalcBindedGroup;
			$bg->SetPageName('ed_invcalc.php');
			//Разбор переменных запроса
			if(isset($_GET['from_bill'])) $from_bill=abs((int)$_GET['from_bill']);
			else $from_bill=0;
			
			if(isset($_GET['to_page_bill'])) $to_page_bill=abs((int)$_GET['to_page_bill']);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			
			
			//блок фильтров статуса
			/*$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_in_bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
				$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['bill_in_bill_statuses'])&&is_array($_GET['bill_in_bill_statuses'])) $cou_stat=count($_GET['bill_in_bill_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['bill_in_bill_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_in_bill_statuses[]',$v));
			  }
		  } 
		  
		  
		
			
			if(!isset($_GET['pdate_bill1'])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_bill1'];
			
			
			
			if(!isset($_GET['pdate_bill2'])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_bill2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_bill1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_bill2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_bill'])&&(strlen($_GET['code_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_bill',$_GET['code_bill']));
			}
			
			
			
			if(isset($_GET['supplier_name_bill'])&&(strlen($_GET['supplier_name_bill'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_bill']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name_bill',$_GET['supplier_name_bill']));
	}
			
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode_bill'])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_bill']);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new UriEntry('sortmode_bill',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			
			
			
			$bills=$bg->ShowBills($editing_user['id'], 
			'bills_in/bills_list_komplekt.html',
			$decorator,$from,$to_page, 
			$au->user_rights->CheckAccess('w',92), 
			$au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), 
			$au->user_rights->CheckAccess('w',94),
			'_bill',
			$au->user_rights->CheckAccess('w',95),
			$au->user_rights->CheckAccess('w',96),true,false,
			$au->user_rights->CheckAccess('w',131),
			$limited_sector, NULL,
			 $au->user_rights->CheckAccess('w',195),
			 $au->user_rights->CheckAccess('w',196), 
			 $au->user_rights->CheckAccess('w',197),$result, 1,
			 $au->user_rights->CheckAccess('w',625));
			
			
			
			
			$llg='<div style="color:red; font-weight:bold;">Данный акт регулирует конечный остаток взаиморасчетов по текущей организации с выбранным контрагентом '.$current_supplier.' на '.$editing_user['invcalc_pdate'].' на сумму '.number_format($editing_user['real_debt'],2,'.',' ').' руб. Данная сумма считается в программе ';
			
			if($editing_user['real_debt_id']==2){
			   $llg.=" Исходящей оплатой."; 
			}elseif($editing_user['real_debt_id']==3){
			   $llg.=" Входящей оплатой.";  
			}elseif($editing_user['real_debt_id']==1){
			   $llg.=" нулевой корректировкой.";  
			}
			
			$llg.='</div>';
			
			$llg.='<h2>Связанные входящие счета:</h2>'.$bills.'<h2>Связанные исходящие счета:</h2>'.$pays;
			$sm->assign('accs',$llg);	
			
			
			
			
			
			
			
		}else{
			
		
			
			
			
			$sm->assign('accs','В данном режиме просмотр связанных документов по инвентаризационному акту недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю коррекцию задолженности" и нажмите кнопку "Сохранить и остаться" на вкладке "Инвентаризация расчетов с поставщиками" для получения возможности просмотра связанных документов.');
			
		}
		
		if(!isset($_GET['do_show_log'])){
			$do_show_log=false;
		}else{
			$do_show_log=true;
		}
		$sm->assign('do_show_log',$do_show_log);
		
		
		if(!isset($_GET['do_show_acc'])){
			$do_show_acc=false;
		}else{
			$do_show_acc=true;
		}
		if(isset($_GET['do_show_pay'])){
			$do_show_acc=true;
		}
		if(isset($_GET['do_show_bills'])){
			$do_show_acc=true;
		}
		
		$sm->assign('do_show_acc',$do_show_acc);
		
		
			
		
		
		//$sm->assign('has_is', true); //($editing_user['is_confirmed_shipping']==1));
		
		
		
		$sm->assign('has_acc', true);	
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',451)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',452)); 
		
		
		$user_form=$sm1->fetch('invcalc/invcalc_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',531)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			 
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(450,
451,
452,
453,
454,
455,
456,
457,
458,
459,
460,
461,
462,
463,
464)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('do_show_log',1));
			
			if(!isset($_GET['do_show_log'])){
				$do_show_log=false;
			}else{
				$do_show_log=true;
			}
			$sm->assign('do_show_log',$do_show_log);
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_invcalc.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$content=$sm->fetch('invcalc/ed_invcalc_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>