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


 


require_once('classes/posdimitem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

 
require_once('classes/user_s_item.php');

require_once('classes/cashitem.php');

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/cashnotesgroup.php');
require_once('classes/cashnotesitem.php');
 
require_once('classes/cashcreator.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/supcontract_item.php');
require_once('classes/supcontract_group.php');

require_once('classes/paycodeitem.php');
require_once('classes/paycodegroup.php');
require_once('classes/billitem.php');
require_once('classes/cashkinditem.php');
require_once('classes/cash_to_bill_item.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование расхода наличных');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_pay=new CashItem;

 
$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;
 

$_supgroup=new SuppliersGroup;

$lc=new CashCreator;

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
	$object_id[]=835;
	break;
	case 1:
	$object_id[]=836;
	$object_id[]=848;
	break;
	case 2:
	$object_id[]=846;
	break;
	default:
	$object_id[]=835;
	break;
}

$_editable_status_id=array();
$_editable_status_id[]=1;


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

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',848)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

	


if($action==0){
	
	
	
	
}elseif(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$_pay->GetItemByFields(array('id'=>$id));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$available_kind_id=array(2,3);
	if(!$au->user_rights->CheckAccess('w',834)){
		if(($editing_user['manager_id']!=$result['id'])&&($editing_user['responsible_user_id']!=$result['id'])
		&&(!in_array($editing_user['kind_id'], $available_kind_id))
		  ){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();
		}
	}
}



//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту расхода наличных',NULL,836, NULL, $editing_user['code'], $id);
	else
	$log->PutEntry($result['id'],'открыл карту расхода наличных: версия для печати',NULL,848, NULL, $editing_user['code'],$id);
				
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',835)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	 
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	$params['supplier_bdetails_id']=abs((int)$_POST['bdetails_id']);
	$params['org_bdetails_id']=abs((int)$_POST['org_bdetails_id']);
	$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	
	$params['value']=((float)str_replace(",",".",$_POST['value']));
	$params['manager_id']=abs((int)$result['id']);
	$params['contract_id']=abs((int)$_POST['contract_id']);
	 
	
	//$params['notes']=SecStr($_POST['notes']);
	
	$params['is_confirmed']=0;
	$params['is_confirmed_given']=0;
	
	//$params['code']=SecStr($_POST['code']);
	
		
		$lc->ses->ClearOldSessions();
		
		$params['code']=$lc->GenLogin($result['id']);
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	
	$params['responsible_user_id']=abs((int)$_POST['responsible_user_id']); 
	
	$params['code_id']=abs((int)$_POST['code_id']);
	
	$params['month']=abs((int)$_POST['month']);
	$params['year']=abs((int)$_POST['year']);
	$params['quarter']=abs((int)$_POST['quarter']);
	
	
	$code=$_pay->Add($params);
	
	
	
	
	
	
	
	//запись в журнале
	if($code>0){
		
		$log->PutEntry($result['id'],'создал расход наличных',NULL,835,NULL,NULL, $code);
		
		
		foreach($params as $k=>$v){
			
		 
				 
				
				$log->PutEntry($result['id'],'создал  расход наличных',NULL,835, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}	
	}
	
	
	
	 
	
	//перенаправления
	if(isset($_POST['doNew'])){
		 header("Location: all_pay.php");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',836)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_cash.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: all_pay.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',836)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//редактирование возможно, если is_confirmed==0
	//if($editing_user['is_confirmed']==0){
		
		
	$condition=true;
	
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	
	if($condition){	
		$params=array();
		//обычная загрузка прочих параметров
		
		$params['supplier_id']=abs((int)$_POST['supplier_id']);
		$params['contract_id']=abs((int)$_POST['contract_id']);
		
		$params['supplier_bdetails_id']=abs((int)$_POST['bdetails_id']);
		$params['org_bdetails_id']=abs((int)$_POST['org_bdetails_id']);
	    
		if(strlen($_POST['given_pdate'])==10) $params['given_pdate']=DateFromdmY($_POST['given_pdate']);
		
		 
		$params['value']=((float)str_replace(",",".",$_POST['value']));
		
		$params['code_id']=SecStr($_POST['code_id']);
	
	 
		$params['given_no']=SecStr($_POST['given_no']);
		
		$params['responsible_user_id']=abs((int)$_POST['responsible_user_id']); 
		
		
		//поля от доставки
		$params['rout']=SecStr(($_POST['rout']));
		$params['weight']=abs((float)$_POST['weight']);	
		
		$params['number_pieces']=abs((int)$_POST['number_pieces']);	
		$params['distance_bonus']=abs((float)$_POST['distance_bonus']);	
		
		
		if(isset($_POST['has_chief_bonus'])){
			$params['has_chief_bonus']=1;
			$params['chief_bonus']=abs((float)$_POST['chief_bonus']);
			$params['chief_bonus_reason']=SecStr(( $_POST['chief_bonus_reason']));
		}else{
			$params['has_chief_bonus']=0;
			$params['chief_bonus']=0;
			$params['chief_bonus_reason']='';
		}
		
		
	$params['time_from_h']=abs((int)$_POST['time_from_h']);
	$params['time_from_m']=abs((int)$_POST['time_from_m']);
	$params['time_to_h']=abs((int)$_POST['time_to_h']);
	$params['time_to_m']=abs((int)$_POST['time_to_m']);
	
		
		
		$params['driver_id']=abs((int)$_POST['driver_id']);
		
		
		$params['month']=abs((int)$_POST['month']);
	$params['year']=abs((int)$_POST['year']);
	$params['quarter']=abs((int)$_POST['quarter']);
		
		
		$_pay->Edit($id, $params);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='given_pdate'){
					 
					$log->PutEntry($result['id'],'редактировал заданную дату',NULL,836,NULL,'дата: '.$_POST['given_pdate'],$id);
					continue;	
				}
				
				 
				$log->PutEntry($result['id'],'редактировал расход наличных',NULL,836, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
			
		}
		
		
		//обработаем счета, включенные в расход
		if(in_array($editing_user['kind_id'], array(2,3))){
			
			$nested_bills=array();
			
			foreach($_POST as $k=>$v){
				if(eregi('bill_', $k)){
					$nested_bills[]=array('cash_id'=>$id, 'bill_id'=>abs((int)$v));	
				}
			}
			
			
			$log_entries=$_pay->AddBills($id,$nested_bills, $result['org_id'], $result);
			 $_bi=new BillItem;
			foreach($log_entries as $k=>$v){
			 
			  $bi=$_bi->GetItemById($v['bill_id']);
			 
			  $description=SecStr('расход наличных '.$editing_user['code'].', исходящий счет '.$bi['code']);
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил исходящий счет в расход наличных',NULL,93,NULL,$description,$v['bill_id']);	
				  $log->PutEntry($result['id'],'добавил исходящий счет в расход наличных',NULL,836,NULL,$description,$id);	
			  
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил исходящий счет из расхода наличных',NULL,93,NULL,$description,$v['bill_id']);
				  
				   $log->PutEntry($result['id'],'удалил исходящий счет из расхода наличных',NULL,836,NULL,$description,$id);
			  }
			  
		  }	
		}
		
		
	}
	
	
	
	
	//утверждение цен
	
	if($editing_user['is_confirmed_given']==0){
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',843))){
			  if((!isset($_POST['is_confirmed']))&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2))){
				  
				 
				  $_pay->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'снял подтверждение затраты',NULL,843, NULL, NULL,$id);	
				  
			  }
		  } 
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',842)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))){
				  
				  $_pay->Edit($id, array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'подтвердил затраты',NULL,842, NULL, NULL,$id);	
				  
				   
				  //die();
			  }
		  } 
	  }
	}
	
	
	//утверждение выдачи суммы
	if($editing_user['is_confirmed']==1){
	  if($editing_user['is_confirmed_given']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',845))){
			 
			  if((!isset($_POST['is_confirmed_given'])) &&in_array($editing_user['status_id'], array(19))&&in_array($_POST['current_status_id'], array(19))){
				  $_pay->Edit($id,array('is_confirmed_given'=>0, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'снял утверждение выдачи суммы',NULL,845, NULL, NULL,$id);	
			  }
		  } 
	  }else{
		  //есть права
		  
		  if($au->user_rights->CheckAccess('w',844)){
			  
			  if(isset($_POST['is_confirmed_given'])&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2))){
				  
				  $_pay->Edit($id,array('is_confirmed_given'=>1, 'user_confirm_given_id'=>$result['id'], 'confirmed_given_pdate'=>time()),true, $result);
				  
				  $log->PutEntry($result['id'],'утвердил выдачу суммы',NULL,844, NULL, NULL,$id);	
					  
			  }
		  } 
	  }
	}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		 header("Location: all_pay.php");
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',836)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_cash.php?action=1&id=".$id);
		die();	
		
	}else{
		 header("Location: all_pay.php");
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


$_menu_id=35;
	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		
		//организация
		
		$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		//основные реквизиты по организации
		$_bi=new BDetailsItem; $selected_bd_id=0;
		$bi=$_bi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$orgitem['id']));
		if($bi!==false){
				$sm1->assign('org_bdetails_id_string',' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'');
				
				
				$sm1->assign('org_bdetails_id',$bi['id']);
				 $selected_bd_id=$bi['id'];
				
			}
		//все реквизиты организации - для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($orgitem['id'],$selected_bd_id);
		$sm1->assign('orgpos',$arr);
		
		$sm1->assign('bill_id',$bill_id);
		
		
		
		//коды оплаты
		$_pcg=new PayCodeGroup;
		$arr=$_pcg->GetItemsArr();
		$sm1->assign('codespos',$arr);
		
		
		
		//поставщики
		//$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$dec=new DBDecorator;
		$_supgroup->GetItemsForBill('bills_in/suppliers_list.html', $dec, false, $supgroup, $result); 
		$sm1->assign('suppliers',$supgroup);
		//print_r($supgroup);
		
		 
		 
		//сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		
		//ограничения по сотруднику
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
		}
		
		foreach($ug as $k=>$v){
			if(($limited_user!==NULL)&&in_array($v['id'],$limited_user)){
				$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
			}elseif($limited_user===NULL) {
				$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
			}
			
		}
		$sm1->assign('responsible_user_id_ids',$_ids);
		$sm1->assign('responsible_user_id_vals',$_vals);
		 
		 
		 
		//месяцы
		$_month_ids=array(0); $_month_names=array('-выберите-');
		$_month_ids[]=1; $_month_names[]='Январь';
		$_month_ids[]=2; $_month_names[]='Февраль';
		$_month_ids[]=3; $_month_names[]='Март';
		$_month_ids[]=4; $_month_names[]='Апрель';
		$_month_ids[]=5; $_month_names[]='Май';
		$_month_ids[]=6; $_month_names[]='Июнь';
		$_month_ids[]=7; $_month_names[]='Июль';
		$_month_ids[]=8; $_month_names[]='Август';
		$_month_ids[]=9; $_month_names[]='Сентябрь';
		$_month_ids[]=10; $_month_names[]='Октябрь';
		$_month_ids[]=11; $_month_names[]='Ноябрь';
		$_month_ids[]=12; $_month_names[]='Декабрь';
		$sm1->assign('_month_ids', $_month_ids); $sm1->assign('_month_names', $_month_names); 
		 
		//кварталы
		$_quart_ids=array(0); $_quart_names=array('-выберите-');
		$_quart_ids[]=1; $_quart_names[]='1 квартал';
		$_quart_ids[]=2; $_quart_names[]='2 квартал';
		$_quart_ids[]=3; $_quart_names[]='3 квартал';
		$_quart_ids[]=4; $_quart_names[]='4 квартал';
		$sm1->assign('_quart_ids', $_quart_ids); $sm1->assign('_quart_names', $_quart_names); 
		 
		//годы
		$_year_ids=array(0); $_year_names=array('-выберите-');
		//
		$last_year=date('Y'); if(date('m')>=11) $last_year++;
		for($i=2013; $i<=$last_year; $i++){
			$_year_ids[]=$i; $_year_names[]=$i;
		}
		$sm1->assign('_year_ids', $_year_ids); $sm1->assign('_year_names', $_year_names);  $sm1->assign('year', date('Y'));
		 
		 
		 
		
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		 
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',835)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',836)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$user_form=$sm1->fetch('cash/cash_create.html');
	}elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		
		
		
		$sm1=new SmartyAdm;
		
		//организация
		
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($orgitem['opf_id']); 
		$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		//реквизиты по организации
		$_bi=new BDetailsItem; $selected_bd_id=$editing_user['org_bdetails_id'];
		$bi=$_bi->GetItemById($editing_user['org_bdetails_id']);
		if($bi!==false){
				$editing_user['org_bdetails_id_string']=' р/с '.stripslashes($bi['rs'].', '.$bi['bank'].', '.$bi['city']).'';
				
				
				
			}
		//все реквизиты организации - для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($orgitem['id'],$selected_bd_id);
		$sm1->assign('orgpos',$arr);
		
		
		
		//коды оплаты
		$_pcg=new PayCodeGroup;
		$arr=$_pcg->GetItemsArr(0, $editing_user['code_id']);
		$sm1->assign('codespos',$arr);
		$_pci=new PayCodeItem;
		$pci=$_pci->GetItemById($editing_user['code_id']);
		if($pci!==false){
				$editing_user['code_id_string']=''.stripslashes($pci['code']).' '.stripslashes($pci['name'].'. '.$pci['descr'].'').'';
		}
		
		
		
		
		//месяцы
		$_month_ids=array(0); $_month_names=array('-выберите-');
		$_month_ids[]=1; $_month_names[]='Январь';
		$_month_ids[]=2; $_month_names[]='Февраль';
		$_month_ids[]=3; $_month_names[]='Март';
		$_month_ids[]=4; $_month_names[]='Апрель';
		$_month_ids[]=5; $_month_names[]='Май';
		$_month_ids[]=6; $_month_names[]='Июнь';
		$_month_ids[]=7; $_month_names[]='Июль';
		$_month_ids[]=8; $_month_names[]='Август';
		$_month_ids[]=9; $_month_names[]='Сентябрь';
		$_month_ids[]=10; $_month_names[]='Октябрь';
		$_month_ids[]=11; $_month_names[]='Ноябрь';
		$_month_ids[]=12; $_month_names[]='Декабрь';
		$sm1->assign('_month_ids', $_month_ids); $sm1->assign('_month_names', $_month_names); 
		$sm1->assign('month_shown', ($editing_user['code_id']>=8)&&($editing_user['code_id']<=12));
		 
		//кварталы
		$_quart_ids=array(0); $_quart_names=array('-выберите-');
		$_quart_ids[]=1; $_quart_names[]='1 квартал';
		$_quart_ids[]=2; $_quart_names[]='2 квартал';
		$_quart_ids[]=3; $_quart_names[]='3 квартал';
		$_quart_ids[]=4; $_quart_names[]='4 квартал';
		$sm1->assign('_quart_ids', $_quart_ids); $sm1->assign('_quart_names', $_quart_names); 
		$sm1->assign('quarter_shown', ($editing_user['code_id']==17)||($editing_user['code_id']==18)||($editing_user['code_id']==62)  );  
		 
		//годы
		$_year_ids=array(0); $_year_names=array('-выберите-');
		//
		$last_year=date('Y'); if(date('m')>=11) $last_year++;
		for($i=2013; $i<=$last_year; $i++){
			$_year_ids[]=$i; $_year_names[]=$i;
		}
		$sm1->assign('_year_ids', $_year_ids); $sm1->assign('_year_names', $_year_names); 
		$sm1->assign('year_shown', (($editing_user['code_id']>=8)&&($editing_user['code_id']<=12))||($editing_user['code_id']==17)||($editing_user['code_id']==18)||($editing_user['code_id']==62));  
		 
		
		
		
		
		
		
		
		
		
		 
		
		
		//договор оплаты
		$_supcontract=new SupContractItem;
		$supcontract=$_supcontract->GetItemById($editing_user['contract_id']);
		//$editing_user['org_bdetails_id_string']=$supcontract['id'];
		$editing_user['contract_no']=$supcontract['contract_no'];
		$editing_user['contract_pdate']=$supcontract['contract_pdate'];
		
		
		//все договора поставщика
		$_scg=new SupContractGroup;
		$scg=$_scg->GetItemsByIdArr($supplier['id'],$bill['contract_id'],1);
		//print_r($scg);
		$sm1->assign('pos2',$scg);
		
		
		if($editing_user['given_pdate']>0) $editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
		else $editing_user['given_pdate']='-';
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		
		
	
		
		//поставщик
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$sopfitem=$_opfitem->getItemById($si['opf_id']); 
		
		
		$editing_user['supplier_id_string']= $sopfitem['name'].' '.$si['full_name'];
		
		
		$supgroup=$_supgroup->GetItemsByFieldsArr(array('is_org'=>0,'is_active'=>1, 'org_id'=>$result['org_id']));
		$sm1->assign('suppliers',$supgroup);
		
		
		//банк. реквизиты
		$_bdi=new BDetailsItem;
		$bdi=$_bdi->GetItemById($editing_user['supplier_bdetails_id']);
		$editing_user['bdetails_id_string']='р/с '.$bdi['rs'].', '.$bdi['bank'].', '.$bdi['city'];
		
		//все реквизиты п-ка для выбора
		$_bd=new BDetailsGroup;
		$arr=$_bd->GetItemsByIdArr($editing_user['supplier_id'],$editing_user['supplier_bdetails_id']);
		//print_r($arr);
		$sm1->assign('pos',$arr);
		
		
		 //сотр.-получатели
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr($editing_user['responsible_user_id'], 1); //>GetUsersByPositionKeyArr('can_sign_as_dir_pr', 1);
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		
		
		//ограничения по сотруднику
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
		}
		
		foreach($ug as $k=>$v){
			if(($limited_user!==NULL)&&in_array($v['id'],$limited_user)){
				$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
			}elseif($limited_user===NULL) {
				$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
			}
			
		}
		/*foreach($ug as $k=>$v){
			$_ids[]=$v['id']; $_vals[]=$v['name_s'].' '.$v['position_s'];	
		}*/
		$sm1->assign('responsible_user_id_ids',$_ids);
		$sm1->assign('responsible_user_id_vals',$_vals);
		
		
		//водитель
		$sql='select * from user where is_active=1 and id in(select distinct qu.user_id from question_user as qu inner join question as q on q.id=qu.question_id where q.name like "%водитель%")';
		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 	$_ids[]=$f['id']; 
			$_vals[]=$f['name_s'].' '.$f['position_s'];		
		}
		$sm1->assign('driver_id_ids',$_ids);
		$sm1->assign('driver_id_vals',$_vals);
		
		//экспедиторы
		$sql='select * from user where is_active=1 and id in(select distinct qu.user_id from question_user as qu inner join question as q on q.id=qu.question_id where q.name like "%экспедитор%")';
		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		$_ids=array(); $_vals=array();
		$_ids[]=0; $_vals[]='-выберите-';
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 	$_ids[]=$f['id']; 
			$_vals[]=$f['name_s'].' '.$f['position_s'];		
		}
		$sm1->assign('exped_id_ids',$_ids);
		$sm1->assign('exped_id_vals',$_vals);
		
		
		//сделка
		/*$_bill=new BillItem;
		$bill=$_bill->getitembyid($editing_user['bill_id']);
		$sm1->assign('bill_code','Исходящий счет №'.$bill['code'].' от '.date('d.m.Y', $bill['pdate']));*/
		$_cbi=new CashToBillItem;
		$sm1->assign('bills', $_cbi->GetBillsbyCashArr($editing_user['id'], $result['org_id']));
		
		
		//времена работы (для экспед-ия)
		$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',$editing_user['time_from_h']);
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',$editing_user['time_from_m']);
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',$editing_user['time_to_h']);
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',$editing_user['time_to_m']);
		
		//вычислим время работы
		$date_to=mktime($editing_user['time_to_h'], $editing_user['time_to_m'], 0);
		$date_from=mktime($editing_user['time_from_h'], $editing_user['time_from_m'], 0);
		$date_res=$date_to-$date_from;
		
		$hours=floor($date_res/60/60);
		$mins=floor(($date_res - $hours*60*60)/60);
		if(strlen($mins)==1) $mins='0'.$mins;
		
		
		$sm1->assign('times', $hours.':'.$mins);
		
		
		
		//вид
		$_cki=new CashKindItem;
		$cki=$_cki->GetItemById($editing_user['kind_id']);
		$sm1->assign('kind_name',$cki['name']);
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		
		//блок аннулирования
			$editing_user['can_annul']=$_pay->DocCanAnnul($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',279);
			if(!$au->user_rights->CheckAccess('w',279)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
		
		$editing_user['can_restore']=$_pay->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',280);
			if(!$au->user_rights->CheckAccess('w',280)) $reason='недостаточно прав для данной операции';
		
		
		
		//Примечания
		$rg=new CashNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0, ($editing_user['is_confirmed']==0),$au->user_rights->CheckAccess('w',849), $au->user_rights->CheckAccess('w',849), $result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',837)/*&&($editing_user['is_confirmed']==0)*/);
		
		
		
		
		$sm1->assign('ship',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
	
		
		
		
		//блок утверждения затрат!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			 
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed_given']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',843)){
				  //есть права 
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',842)&&in_array($editing_user['status_id'],$_editable_status_id)  ;
		  }
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		
		//блок утв. выдачи суммы
		if(($editing_user['is_confirmed_given']==1)&&($editing_user['user_confirm_given_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_given_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirmed_given_pdate']);
			
			$sm1->assign('is_confirmed_given_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_confirmed_given']==1){
			  if($au->user_rights->CheckAccess('w',845)){
				  //есть права  
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',844);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_given',$can_confirm_shipping);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',848)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',835)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',836)); 
		
		$sm1->assign('can_percent',$au->user_rights->CheckAccess('w',851)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		//выбор формы для отображения
		if($editing_user['kind_id']==1) $formname='cash/cash_edit'.$print_add.'.html';
		elseif($editing_user['kind_id']==2) $formname='cash/cash_edit_2'.$print_add.'.html';
		elseif($editing_user['kind_id']==3) $formname='cash/cash_edit_3'.$print_add.'.html';
		elseif($editing_user['kind_id']==4) $formname='cash/cash_edit_4'.$print_add.'.html';
		
		$user_form=$sm1->fetch($formname);
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',850)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(833,
834,
835,
836,
837,
838,
839,
840,
841,
842,
843,
844,
845,
846,
847,
848,
849,
850
)));
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
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_cash.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('cash/ed_cash_page'.$print_add.'.html');
	
	
	
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