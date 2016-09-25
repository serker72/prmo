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
require_once('classes/discr_man_group.php');


require_once('classes/bdetailsgroup.php');
require_once('classes/suppliercontactgroup.php');
require_once('classes/suppliercontactkindgroup.php');

require_once('classes/bdetailsitem.php');
require_once('classes/user_s_item.php');

require_once('classes/fagroup.php');
require_once('classes/faitem.php');

require_once('classes/opfgroup.php');

require_once('classes/supcreator.php');
require_once('classes/supplier_district_group.php');
require_once('classes/supplier_cities_group.php');
require_once('classes/usersgroup.php');
require_once('classes/user_s_item.php');

require_once('classes/supplier_notesgroup.php');
require_once('classes/supplier_notesitem.php');

require_once('classes/supcontract_group.php');
require_once('classes/supplier_country_group.php');

require_once('classes/supplier_to_user.php');

require_once('classes/supplier_ruk_item.php');
require_once('classes/an_sched_su.php');


require_once('classes/supplier_responsible_user_group.php');

require_once('classes/supplier_branches_item.php');

require_once('classes/supplier_to_user.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Карта контрагента');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new SupplierItem;
$lc=new SupCreator;
$log=new ActionLog;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=87;
	break;
	case 1:
	$object_id=87;
	break;
	case 2:
	$object_id=88;
	break;
	default:
	$object_id=86;
	break;
}

if(!$au->user_rights->CheckAccess('w',$object_id)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
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
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	if($editing_user['is_org']==1){
		header("Location: ed_organization.php?action=1&id=".$id);
		die();
	}
	
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);

	



	if($limited_supplier!==NULL){
		if(!in_array($id, $limited_supplier)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
	}
	
	/*проверка по ответственному сотруднику - Лишнее, переехало в limited_supplier*/
	$_sr=new SupplierResponsibleUserGroup;
	/*if(!$au->user_rights->CheckAccess('w',909)){
		
		$_sr->GetUsersArr($id, $ids);
		if(!in_array($result['id'], $ids)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
	}*/




}


if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту контрагента',NULL,87, NULL, $editing_user['code'].' '.$editing_user['full_name'],$id);			
}



if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
	$params['not_in_holding']=0;
	

   
    //обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	//$params['name']=SecStr($_POST['name']);
	$params['full_name']=SecStr($_POST['full_name']);
	
	$params['print_name']=SecStr($_POST['print_name']);
	
	
	$params['ur_or_fiz']=abs((int)$_POST['ur_or_fiz']);
	$params['opf_id']=abs((int)$_POST['opf_id']);
	
	$params['inn']=SecStr($_POST['inn']);
	$params['kpp']=SecStr($_POST['kpp']);
	$params['okpo']=SecStr($_POST['okpo']);
	$params['legal_address']=SecStr($_POST['legal_address']);
	

/*	
	$params['contract_no']=SecStr($_POST['contract_no']);
	$params['contract_pdate']=SecStr($_POST['contract_pdate']);*/
	
	$params['time_from_h']=SecStr($_POST['time_from_h']);
	$params['time_from_m']=SecStr($_POST['time_from_m']);
	$params['time_to_h']=SecStr($_POST['time_to_h']);
	$params['time_to_m']=SecStr($_POST['time_to_m']);
	
	//$params['code']=SecStr($_POST['code']);
		$lc->ses->ClearOldSessions();
	$params['code']=SecStr($lc->GenLogin($result['id'])); //SecStr($_POST['code']);
	
	
	
	$params['created_id']=abs((int)$result['id']); 


	
	/*$params['contract_prolongation']=abs((int)$_POST['contract_prolongation']);
	$params['contract_prolongation_mode']=abs((int)$_POST['contract_prolongation_mode']);
	*/
	/*$params['chief']=SecStr($_POST['chief']);
	$params['main_accountant']=SecStr($_POST['main_accountant']);
	*/
	
	$params['curator_obor_id']=abs((int)$_POST['curator_obor_id']);
	$params['curator_instr_id']=abs((int)$_POST['curator_instr_id']);
	$params['curator_zap_id']=abs((int)$_POST['curator_zap_id']);
	
	if(strlen(trim($_POST['lim_deb_debt']))==0){
		$params['lim_deb_debt']=AbstractItem::SET_NULL;
	}else{
		$params['lim_deb_debt']=abs((float) str_replace(',','.',$_POST['lim_deb_debt']));
	}
	
   	
	$code=$ui->Add($params); //, $quests);
	
	$lc->ses->DelSession($result['id']);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал контрагента',NULL,86,NULL,$params['name'],$code);
		
		foreach($params as $k=>$v){
			
			 if((addslashes($editing_user[$k])!=$v)&&($k=='curator_obor_id')){
			   $_ui=new UserSItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name_s'].' ('.$_user['login'].')');
			 
			  $log->PutEntry($result['id'],'редактировал куратора по оборудованию',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
		  
		  if((addslashes($editing_user[$k])!=$v)&&($k=='curator_instr_id')){
			   $_ui=new UserSItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name_s'].' ('.$_user['login'].')');
			 
			  $log->PutEntry($result['id'],'редактировал куратора по инструменту',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
		  
		  if((addslashes($editing_user[$k])!=$v)&&($k=='curator_zap_id')){
			   $_ui=new UserSItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name_s'].' ('.$_user['login'].')');
			 
			  $log->PutEntry($result['id'],'редактировал куратора по запчастям',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
			
			
			$log->PutEntry($result['id'],'создал контрагента',NULL,86, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}	
	}
	
	
	
	//добавим отвестственных сотрудников.

	$_user=new UserSItem;
	$positions=array();
	
	 
	$positions[]=array(
		'supplier_id'=>$code,
		 
		'user_id'=>$result['id'] 
	);
	
	  
	
	 
	/*echo '<pre>';
	print_r($_POST);
	print_r($positions);
	echo '</pre>';
	die(); */
	//внесем позиции
	$_bg=new SupplierResponsibleUserGroup;
	$log_entries=$_bg->AddUsers($code, $positions, $result);
	//die();
	//запишем в журнал
	 foreach($log_entries as $k=>$v){
		  $user=$_user->GetItemById($v['user_id']);
		  
		 
		  $description=SecStr($user['name_s'].' '.$user['login']).',  ';
		   
		  
		  
		  if($v['action']==0){
			  $log->PutEntry($result['id'],'добавил ответственного сотрудника в карту контрагента',NULL,910,NULL,$description,$code);	
		  }elseif($v['action']==1){
			  $log->PutEntry($result['id'],'редактировал ответственного сотрудника в карте контрагента',NULL,910,NULL,$description,$code);
		  }elseif($v['action']==2){
			  $log->PutEntry($result['id'],'удалил ответственного сотрудника из карты контрагента',NULL,910,NULL,$description,$code);
		  }
		  
	  }
	 
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: suppliers.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',87)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: supplier.php?action=1&id=".$code.'#req');
		die();	
		
	}else{
		header("Location: suppliers.php");
		die();
	}
	
	die();
	
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])||isset($_POST['do_save']))){
		//редактирование пользователя
		
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
    $condition=true;
	$condition=$condition&&($editing_user['is_confirmed']==0);
	$condition=$condition&&(!isset($_POST['is_confirmed'])||(isset($_POST['is_confirmed'])&&(count($_POST)>2)));
	 
	 
	$params=array();
	
	if($condition){	
		
		
		
	  //обычная загрузка прочих параметров
	
	  
	  $params['org_id']=abs((int)$result['org_id']);
	  
	//  $params['name']=SecStr($_POST['name']);
	    if(isset($_POST['full_name'])) $params['full_name']=SecStr($_POST['full_name']);
	  
	  $params['print_name']=SecStr($_POST['print_name']);
	  
	  $params['ur_or_fiz']=abs((int)$_POST['ur_or_fiz']);
	  $params['opf_id']=abs((int)$_POST['opf_id']);
	  
	  $params['inn']=SecStr($_POST['inn']);
	  $params['kpp']=SecStr($_POST['kpp']);
	  $params['okpo']=SecStr($_POST['okpo']);
	  $params['legal_address']=SecStr($_POST['legal_address']);
	  
		  
	  if(isset($_POST['not_in_holding']))  $params['not_in_holding']=1; else $params['not_in_holding']=0;
   	  if(isset($_POST['holding_id'])) $params['holding_id']=abs((int)$_POST['holding_id']);
	  if(isset($_POST['subholding_id'])) $params['subholding_id']=abs((int)$_POST['subholding_id']);	
	  
	   
  
	  
	 
	  $params['curator_obor_id']=abs((int)$_POST['curator_obor_id']);
	$params['curator_instr_id']=abs((int)$_POST['curator_instr_id']);
	$params['curator_zap_id']=abs((int)$_POST['curator_zap_id']);
	
	
	  if(isset($_POST['is_upr_nalog'])) $params['is_upr_nalog']=1;
	  else  $params['is_upr_nalog']=0;
	  
	  $params['upr_nalog_no']=SecStr($_POST['upr_nalog_no']);
	  
	  
	  
	  
	
	  
		
	  	if(strlen(trim($_POST['lim_deb_debt']))==0){
			$params['lim_deb_debt']=AbstractItem::SET_NULL;
		}else{
			$params['lim_deb_debt']=abs((float) str_replace(',','.',$_POST['lim_deb_debt']));
		}
	
		 if(isset($_POST['branch_id'])) $params['branch_id']=abs((int)$_POST['branch_id']);
	 if(isset($_POST['subbranch_id'])) $params['subbranch_id']=abs((int)$_POST['subbranch_id']);
	if(isset($_POST['subbranch_id1'])) $params['subbranch_id1']=abs((int)$_POST['subbranch_id1']);
	
	

	}
	  
	  
	  
	  
	  $params['time_from_h']=SecStr($_POST['time_from_h']);
	  $params['time_from_m']=SecStr($_POST['time_from_m']);
	  $params['time_to_h']=SecStr($_POST['time_to_h']);
	  $params['time_to_m']=SecStr($_POST['time_to_m']);
	  
	    
	  if(isset($_POST['is_customer'])) $params['is_customer']=1;
	  else  $params['is_customer']=0;
	  
	   if(isset($_POST['is_supplier'])) $params['is_supplier']=1;
	  else  $params['is_supplier']=0;
	  
	  
	   
	  if(isset($_POST['is_partner'])) $params['is_partner']=1;
	  else  $params['is_partner']=0; 

	  
	  if(isset($_POST['wo_contract'])) $params['wo_contract']=1; else $params['wo_contract']=0;
		if($params['wo_contract']==1){
			$ri=new SupContractItem;
			$scg=new SupContractGroup;
			$contracts=$scg->GetItemsByIdArr($id);
			
			foreach($contracts as $k=>$v){
			
				$ri->Del($v['id']);
			
				$log->PutEntry($result['id'],'удалил договор контрагента при включении режима Без договора',NULL,87,NULL,NULL,$id);
			}	
		}
	  
	  $ui->Edit($id,$params);
	  
	  //записи в лог. сравнить старые и новые записи
	  foreach($params as $k=>$v){
		  
		    if((addslashes($editing_user[$k])!=$v)&&($k=='branch_id')){
			   $_ui=new SupplierBranchesItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name'].' ');
			 
			  $log->PutEntry($result['id'],'редактировал отрасль',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
		  
		  
		   if((addslashes($editing_user[$k])!=$v)&&(($k=='subbranch_id')||($k=='subbranch_id1') ) ){
			
			   $_ui=new SupplierBranchesItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name'].' ');
			 
			  $log->PutEntry($result['id'],'редактировал подотрасль',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
			
		
		if((addslashes($editing_user[$k])!=$v)&&(($k=='holding_id')||($k=='subholding_id') ) ){
			 $_ui=new SupplierItem;
			$_user=$_ui->GetItemById($v);
			
			$descr='-'; 
			if($_user!==false){
				 $descr=SecStr($_user['full_name'].' ');
			}
		   
		   	if($k=='holding_id') $hold=' холдингу';
			elseif($k=='subholding_id') $hold=' субхолдингу';
			
		   
			$log->PutEntry($result['id'],'редактировал принадлежность к '.$hold,NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
		   continue;	
		}
					
			
		
		  if((addslashes($editing_user[$k])!=$v)&&($k=='curator_obor_id')){
			   $_ui=new UserSItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name_s'].' ('.$_user['login'].')');
			 
			  $log->PutEntry($result['id'],'редактировал куратора по оборудованию',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
		  
		  if((addslashes($editing_user[$k])!=$v)&&($k=='curator_instr_id')){
			   $_ui=new UserSItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name_s'].' ('.$_user['login'].')');
			 
			  $log->PutEntry($result['id'],'редактировал куратора по инструменту',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
		  
		  if((addslashes($editing_user[$k])!=$v)&&($k=='curator_zap_id')){
			   $_ui=new UserSItem;
			  $_user=$_ui->GetItemById($v);
			  $descr='-'; 
			  if($_user!==false) $descr=SecStr($_user['name_s'].' ('.$_user['login'].')');
			 
			  $log->PutEntry($result['id'],'редактировал куратора по запчастям',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$descr,$id);
			 continue;	
		  }
		  
		  
		  
		  if(addslashes($editing_user[$k])!=$v){
			  $log->PutEntry($result['id'],'редактировал контрагента',NULL,87, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		  }
	  }
	  
     
	
	
	
	//добавим отвестственных сотрудников.
	if($au->user_rights->CheckAccess('w',910)){
		$_user=new UserSItem;
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^new_resp_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_resp_hash_","",$k);
			  
			  $user_id=abs((int)$_POST['new_resp_id_'.$hash]);
			  
			  
			 
			  $positions[]=array(
				  'supplier_id'=>$id,
				   
				  'user_id'=>$user_id 
			  );
			  
		  }
		}
				
		
		 
		/*echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die(); */
		//внесем позиции
		$_bg=new SupplierResponsibleUserGroup;
		$log_entries=$_bg->AddUsers($id, $positions, $result);
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			  $user=$_user->GetItemById($v['user_id']);
			  
			 
			  $description=SecStr($user['name_s'].' '.$user['login']).',  ';
			   
			  
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил ответственного сотрудника в карту контрагента',NULL,910,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал ответственного сотрудника в карте контрагента',NULL,910,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил ответственного сотрудника из карты контрагента',NULL,910,NULL,$description,$id);
			  }
			  
		  }
	}
	
	
	//утверждение заполнения
	if($editing_user['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',175))){
			if(!isset($_POST['is_confirmed'])){
				$ui->Edit($id,array('is_confirmed'=>0));
				
				$log->PutEntry($result['id'],'снял утверждение заполнения контрагента',NULL,175, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',174)){
			if(isset($_POST['is_confirmed'])){
				$ui->Edit($id,array('is_confirmed'=>1, 'confirm_user_id'=>$result['id'], 'confirm_pdate'=>time()));
				
				$log->PutEntry($result['id'],'утвердил заполнение контрагента',NULL,174, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	
		
		
	}
	
	//утверждение контрагента
	if($editing_user['is_active']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',176))&&($ui->CanUnConfirmActive($id))){
			if(!isset($_POST['is_active'])){
				$ui->Edit($id,array('is_active'=>0));
				
				$log->PutEntry($result['id'],'снял утверждение контрагента',NULL,176, NULL, NULL, $id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',89)){
			if(isset($_POST['is_active'])){
				$ui->Edit($id,array('is_active'=>1, 'user_id'=>$result['id'], 'active_pdate'=>time()));
				
				$log->PutEntry($result['id'],'утвердил контрагента',NULL,89, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}


	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: suppliers.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])||isset($_POST['do_save'])){
		//если есть доступ к объекту 16 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',87)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: supplier.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: suppliers.php");
		die();
	}
	

	
	die();
}elseif(($action==2)){
	if(!$au->user_rights->CheckAccess('w',88)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	$ui->Del($id);
	
	$log->PutEntry($result['id'],'удалил контрагента',NULL,88, NULL, $editing_user['name'],$id);	
	
	header("Location: suppliers.php");
	die();
}





//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=27;

	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание пользователя
		
		$sm1=new SmartyAdm;
		//тест
		
			$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',"09");
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',"00");
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',"18");
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',"00");
		
		
		//opf
		$opg=new OpfGroup;
		$opfs=$opg->GetItemsArr();
		$ops=array(); $op_ids=array();
		
		$op_ids[]=0; $ops[]='-выберите-';


		foreach($opfs as $k=>$v){
			$ops[]=$v['name'];
			$op_ids[]=$v['id'];
		}
		$sm1->assign('opfs_total',$opfs);
		$sm1->assign('opfs',$ops);
		$sm1->assign('opf_ids',$op_ids);
		$sm1->assign('can_expand_opf',$au->user_rights->CheckAccess('w',164)); 
		
		//кураторы
		$_ug=new UsersGroup;
		$kur_obor=$_ug->GetCuratorsArr(0, 'curator_obor_id');
		$sm1->assign('kur_obor',$kur_obor);
		
		$kur_instr=$_ug->GetCuratorsArr(0, 'curator_instr_id');
		$sm1->assign('kur_instr',$kur_instr);
		
		$kur_instr=$_ug->GetCuratorsArr(0, 'curator_zap_id');
		$sm1->assign('kur_zap',$kur_instr);
		
		
		
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		//echo $lc->GenLogin($result['id']);
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',87)); 
		
	
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',87)); 
	
		
		
		$user_form=$sm1->fetch('suppliers/supplier_create.html');
	}elseif($action==1){
		//редактирование пользователя
		
		
		//строим вкладку администрирования
		/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',86)||
								$au->user_rights->CheckAccess('x',87)||
								$au->user_rights->CheckAccess('x',88)||
								$au->user_rights->CheckAccess('x',89)||
								$au->user_rights->CheckAccess('x',90)||
								$au->user_rights->CheckAccess('x',91)
								);
		$dto=new DiscrTableObjects($result['id'],array('86','87','88','89','90','91'));
		$admin=$dto->Draw('supplier.php','admin/admin_objects.html');
		$sm->assign('admin',$admin);
		*/
		
		//таблица прав по данному пользователю
		/*$dtu=new DiscrTableUser($editing_user['id']);
		$rights=$dtu->Draw('supplier.php','admin/admin_user_rights.html');
		$sm->assign('rights',$rights);
		*/
		
		$sm1=new SmartyAdm;
		
		if($editing_user['contract_prolongation']==0) $editing_user['contract_prolongation']='';
		
		
		
		//возможность РЕДАКТИРОВАНИЯ - только если Is_confirmed==0
		$sm1->assign('can_modify',$editing_user['is_confirmed']==0);
		
		
			$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',"09");
		$sm1->assign('from_hr',$editing_user['time_from_h']);
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',"00");
		$sm1->assign('from_m',$editing_user['time_from_m']);
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',"18");
			$sm1->assign('to_hr',$editing_user['time_to_h']);
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',"00");
		$sm1->assign('to_m',$editing_user['time_to_m']);
		
		
		//контакты
		$rg=new SupplierContactGroup;
		$sm1->assign('contacts',$rg->GetItemsByIdArr($editing_user['id'],0,0,$au->user_rights->CheckAccess('w',917)));
		$sm1->assign('can_cont',true);
		$sm1->assign('can_cont_edit',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',165)));
		$rrg=new SupplierContactKindGroup;
		$sm1->assign('kinds',$rrg->GetItemsArr());
		
		$sm1->assign('can_add_contact',$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',165)); 
		
		
		//права на удаление контактов
		$can_del_contact=$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',165);
		if($editing_user['is_confirmed']==0){
			if($editing_user['is_active']==1) $can_del_contact=$can_del_contact&&$au->user_rights->CheckAccess('w',917);
		}else $can_del_contact=$can_del_contact&&false;
		$sm1->assign('can_del_contact', $can_del_contact);  
		

		

		
		//реквизиты
		$rg=new BDetailsGroup;
		$sm1->assign('rekviz',$rg->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('can_req',true);
		$sm1->assign('can_req_edit',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',167)));
		
		//договоры
		$scg=new SupContractGroup;
		$sm1->assign('contracts',$scg->GetItemsByIdArr($editing_user['id']));
		//$sm1->assign('can_req',true);
		//$sm1->assign('can_req_edit',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',167)));
		
				
		$sm1->assign('can_add_contract',$au->user_rights->CheckAccess('w',87)); 
		
		
		
		
		//адреса
		$ag=new FaGroup;
		$sm1->assign('fa',$ag->GetItemsByIdArr($editing_user['id']));
		$a_forms=$ag->GetFormsArr();
		$a_ids=array(); $a_names=array(); 
		foreach($a_forms as $k=>$v){
			$a_ids[]=$v['id']; $a_names[]=$v['name'];	
		}
		$sm1->assign('form_ids',$a_ids);
		$sm1->assign('forms',$a_names);
		$sm1->assign('can_fa',true);
		$sm1->assign('can_fa_edit',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',166)));
		
		
		//opf
		$opg=new OpfGroup;
		$opfs=$opg->GetItemsArr();
		$ops=array(); $op_ids=array();
		$op_ids[]=0; $ops[]='-выберите-';


		foreach($opfs as $k=>$v){
			$ops[]=$v['name'];
			$op_ids[]=$v['id'];
		}
		$sm1->assign('opfs_total',$opfs);
		$sm1->assign('opfs',$ops);
		$sm1->assign('opf_ids',$op_ids);
		$sm1->assign('can_expand_opf',$au->user_rights->CheckAccess('w',164)); 
		
		
		//города
		$_csg=new SupplierCitiesGroup;
		$csg=$_csg->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('cities', $csg);
		
	/*	$_dists=new SupplierDistrictGroup;
		$dists=$_dists->GetItemsArr();
		$sm1->assign('dis', $dists);*/
		$sm1->assign('can_expand_cities',$au->user_rights->CheckAccess('w',584)); //добавить права для городов
			
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm1->assign('cous', $cous);
		
		
		
				
		//виды фактич адресов
		$opg=new FaFormGroup;
		$opfs=$opg->GetItemsArr();
		$ops=array(); $op_ids=array();
		$op_ids[]=0; $ops[]='-выберите-';
		foreach($opfs as $k=>$v){
			$ops[]=$v['name'];
			$op_ids[]=$v['id'];
		}
		$sm1->assign('fas_total',$opfs);
		$sm1->assign('fas',$ops);
		$sm1->assign('fa_ids',$op_ids);
		
		

		
		
		//Примечания
		$rg=new SupplierNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,($editing_user['is_confirmed']==1),$au->user_rights->CheckAccess('w',87), $au->user_rights->CheckAccess('w',87),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',87));
		
		
		
		
		
		
		
		
		//кураторы
		$_ug=new UsersGroup;
		$kur_obor=$_ug->GetCuratorsArr($editing_user['curator_obor_id'], 'curator_obor_id');
		$sm1->assign('kur_obor',$kur_obor);
		
		$kur_instr=$_ug->GetCuratorsArr($editing_user['curator_instr_id'], 'curator_instr_id');
		$sm1->assign('kur_instr',$kur_instr);
		
		$kur_instr=$_ug->GetCuratorsArr($editing_user['curator_zap_id'], 'curator_zap_id');
		$sm1->assign('kur_zap',$kur_instr);
		
		
		//руководитель, гл.бух.
		$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActual($editing_user['id'], 1);
		$sri_2=$_sri->GetActual($editing_user['id'], 2);
		
			
		$editing_user['chief']=	$sri_1['fio'];
		$editing_user['main_accountant']=	$sri_2['fio'];
		
				
		
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',87)); 
		$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',88)); 
		
		
		 
		
		//блок утверждения 
		//кто утвердил активность
		if(($editing_user['is_active']==1)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['active_pdate']);
			
			$sm1->assign('confirmer',$confirmer);
		}
		//кто утвердил заполнение
		if(($editing_user['is_confirmed']==1)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['confirm_user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('confirmer_is_confirmed',$confirmer);
		}
		
		//доступность нажатия утверждения
		$can_confirm=false;
		if($editing_user['is_confirmed']==1){
			if($au->user_rights->CheckAccess('w',175)){
				//есть права + сам утвердил
				$can_confirm=true;	
			}else{
				$can_confirm=false;
			}
		}else{
			//89
			$can_confirm=$au->user_rights->CheckAccess('w',174);
		}
		
		//доступность нажатия утверждения активности
		$can_confirm_active=false;
		if($editing_user['is_active']==1){
			//$can_confirm_active=$au->user_rights->CheckAccess('w',176);
			$can_confirm_active=true; //галочка доступна, но снимается скриптом
			 

		}else{
			//89
			$can_confirm_active=$au->user_rights->CheckAccess('w',89);
		}
		
		
		$sm1->assign('can_confirm',$can_confirm);
		$sm1->assign('can_confirm_active',$can_confirm_active);
		
		
		//доступно ли снятие утв активности?
		$sm1->assign('can_unconfirm_active',$au->user_rights->CheckAccess('w',176));
		$_usg=new UsersSGroup;
		$unst=$_usg->GetUsersByRightArr('w', 176);
		//var_dump($unst);
		$sm1->assign('can_unconfirm_active_users',$unst); 
		
		//флаг доступности минимально необходимых полей для утверждения активности
		$can_modify_activefields=(($editing_user['is_confirmed']==0)&&($editing_user['is_active']==0));
		$sm1->assign('can_modify_activefields',$can_modify_activefields); 
		
		//блокировка первой записи в связ. спр-ке (города, контакты и т.п.)
		$sm1->assign('block_first',($editing_user['is_confirmed']==0)&&($editing_user['is_active']==1));
		 

		
		
		
			//список ответственных
		$_rg=new SupplierResponsibleUserGroup;
		$sm1->assign('resp_users', $_rg->GetUsersArr($id));		
		
		//может ли править состав отвествнных
		$sm1->assign('can_edit_resp', $au->user_rights->CheckAccess('w',910));
		
		//доступ к картам контрагентов всех сотрудников
		$sm1->assign('can_edit_all', $au->user_rights->CheckAccess('w',909));
		
		
		//может ли править словарь отраслей
		$sm1->assign('can_edit_branch', $au->user_rights->CheckAccess('w',911));
		
		//отрасль
		$_sbi=new SupplierBranchesItem; $sbi=$_sbi->GetItemById($editing_user['branch_id']);
		$sm1->assign('branch_string', $sbi['name']);
		$sm1->assign('branch_subbranch', $_sbi->CountSubs($editing_user['branch_id']));
		
		
		
		//подотрасль
		$_sbi=new SupplierBranchesItem; $sbi=$_sbi->GetItemById($editing_user['subbranch_id']);
		$sm1->assign('subbranch_string', $sbi['name']);
		
		
		//подотрасль1
		$_sbi=new SupplierBranchesItem; $sbi=$_sbi->GetItemById($editing_user['subbranch_id1']);
		$sm1->assign('subbranch_string1', $sbi['name']);
		

		
		//холдинг
		$_shi=new SupplierItem; $_opf=new opfitem;
		$holding=$_shi->getitembyid($editing_user['holding_id']);
		$hopf=$_opf->GetItemById($holding['opf_id']);
		$sm1->assign('holding_string', $hopf['name'].' '.$holding['full_name']);
		
		
		//субхолдинг
		$holding=$_shi->getitembyid($editing_user['subholding_id']);
		$hopf=$_opf->GetItemById($holding['opf_id']);
		$sm1->assign('subholding_string', $hopf['name'].' '.$holding['full_name']);
		
		
		
		

		
		//может ли утв наличие договоров,...
		$sm1->assign('can_has_dog', $au->user_rights->CheckAccess('w',87));
		if(!$au->user_rights->CheckAccess('w',87)) $sm1->assign('cannot_has_dog_reason', 'недостаточно прав для данного действия');
		
		//кто утв-л
		if($editing_user['has_dog']==1){
			$_uu=new UserSItem;
			$uu=$_uu->GetItemById($editing_user['has_dog_confirm_user_id']);
			$sm1->assign('user_has_dog', $uu['position_s'].' '.$uu['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['has_dog_confirm_pdate']));		
		}//
		
		if($editing_user['has_uch']==1){
		
			$_uu=new UserSItem;
			$uu=$_uu->GetItemById($editing_user['has_uch_confirm_user_id']);
			$sm1->assign('user_has_uch', $uu['position_s'].' '.$uu['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['has_uch_confirm_pdate']));	
		}
		
		
		$sm1->assign('user_id', $result['id']);
		
		$sm1->assign('user',$editing_user);
		
		
		$user_form=$sm1->fetch('suppliers/supplier_edit.html');
		
		
		
		
		
		
		
		
		
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',520)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
			
		
			if(!isset($_GET['pdate1'])){
			
					$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
					$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
				
			}else $pdate1 = $_GET['pdate1'];
			
			
			
			if(!isset($_GET['pdate2'])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			}else $pdate2 = $_GET['pdate2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
			$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
			
			
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(91,
86,
164,
87,
165,
166,
167,
168,
169,
170,
171,
172,
173,
174,
89,
175,
176,
88,
318,
319,
320,
910
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
			
			$llg=$log->ShowLog('syslog/log.html',$decorator,$from,$to_page,'supplier.php',true,true,true);
			
			$sm->assign('syslog',$llg);
		
		}
	}
	
	
	$sm->assign('users',$user_form);
	
	
	
	
/*************************************************************************************************/
//вкладка Действия по к-ту
	if($au->user_rights->CheckAccess('w',903)){
		if($action==1){
			$sm->assign('has_sched',true);	
	
			
			
			$prefix=6;
	
			$decorator=new DBDecorator;
			
			//активен или неактивен
			$decorator->AddEntry(new UriEntry('is_active',  $editing_user['is_active']));
			
			/*
			if($print==0) $print_add='';
			else $print_add='_print';*/
		
			//$decorator->AddEntry(new UriEntry('print',$print));
			$decorator->AddEntry(new UriEntry('prefix',$prefix));
			$decorator->AddEntry(new UriEntry('id',  $id));
			$decorator->AddEntry(new UriEntry('action',  $action));
			 
			
			//блок фильтров статуса
			$decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
			$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE));
			
			 
			$status_ids=array();
			$cou_stat=0;   
			if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $status_ids=$_GET[$prefix.'statuses'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_statuses',1));
			}
			
			if(count($status_ids)>0){
				$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}else{
				
					foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					
					foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
					
					$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
				 
				}
			} 
			
			
			
			
			 //выбрать виды действий
		 	$kinds=array();
			$cou_stat=0;   
			if(isset($_GET[$prefix.'kinds'])&&is_array($_GET[$prefix.'kinds'])) $cou_stat=count($_GET[$prefix.'kinds']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $kinds=$_GET[$prefix.'kinds'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_kinds',1));
			}
			
			if(count($kinds)>0){
				$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_kinds',1));
				}else{
				
					foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kind_id_'.$v,1));
					$decorator->AddEntry(new SqlEntry('p.kind_id', NULL, SqlEntry::IN_VALUES, NULL,$kinds));	
					foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'kinds[]',$v));
					
			
				}
			} 
			
			
			
	
			 //совершенные/несовершенные действия
			$is_fulfil=NULL;
			$kinds=array();
			$cou_stat=0;   
			if(isset($_GET[$prefix.'is_fulfil'])&&is_array($_GET[$prefix.'is_fulfil'])) $cou_stat=count($_GET[$prefix.'is_fulfil']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $kinds=$_GET[$prefix.'is_fulfil'];
			  
			}else{
				
				 $decorator->AddEntry(new UriEntry('all_is_fulfil',1));
			}
			
			if(count($kinds)>0){
				$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				
				if($of_zero){
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_is_fulfil',1));
				}else{
				
					foreach($kinds as $k=>$v) {
						$decorator->AddEntry(new UriEntry('is_fulfil_'.$v,1));
					//$decorator->AddEntry(new SqlHavingEntry('`document_type_id`', NULL, SqlHavingEntry::IN_VALUES, NULL,$kinds));	
						$decorator->AddEntry(new UriEntry($prefix.'is_fulfil[]',$v));
					
						if($v==1) $is_fulfil[]=1; 
						elseif($v==2) $is_fulfil[]=2;
					}
				}
			} 
			  
		  

		  
			

			
			if(!isset($_GET['pdate_1'.$prefix])){
			
					$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
					$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
				
			}else $pdate1 = $_GET['pdate_1'.$prefix];
			
			
			
			if(!isset($_GET['pdate_2'.$prefix])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24*30*3;
					$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			}else $pdate2 = $_GET['pdate_2'.$prefix];
			
			
			$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
			$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
			
		
			
			//фильтры по сотруднику
			 
			/*if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
				$_users=explode(';', $_GET['user'.$prefix]);
				$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));
		
				
			}else */
			$_users=NULL;

			 
			 
			
		
			
			 //сортировка
			 
			 //сортировка
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=-1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	 
	 
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.incoming_or_outcoming',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.incoming_or_outcoming',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('meet_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('meet_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('p.priority',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('p.priority',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
			
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::DESC));
			
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::ASC));
		break;
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
			
		break;	
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
			
		break;
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('manager_name',SqlOrdEntry::DESC));
			
		break;	
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('manager_name',SqlOrdEntry::ASC));
			
		break;
		
		case 20:
			$decorator->AddEntry(new SqlOrdEntry('user_name_1',SqlOrdEntry::DESC));
			
		break;	
		case 21:
			$decorator->AddEntry(new SqlOrdEntry('user_name_1',SqlOrdEntry::ASC));
			
		break;
		
		case 22:
			$decorator->AddEntry(new SqlOrdEntry('user_name_2',SqlOrdEntry::DESC));
			
		break;	
		case 23:
			$decorator->AddEntry(new SqlOrdEntry('user_name_2',SqlOrdEntry::ASC));
			
		break;
		
		case 24:
			$decorator->AddEntry(new SqlOrdEntry('cr_name',SqlOrdEntry::DESC));
			
		break;	
		case 25:
			$decorator->AddEntry(new SqlOrdEntry('cr_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
			 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
		break;	
		
	}

			
			
			$as1=new AnSchedSu;
			
			$_sched=new Sched_Group;
			 
			$filetext=$as1->ShowData(array($id), $_users,  $viewed_ids,  $pdate1, $pdate2, 'suppliers/an_sched'.$prefix.'.html',$decorator,'supplier.php',   true,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result,NULL, $is_fulfil);
		   

		   
		   
		   $sm->assign('actions', $filetext);
		}else{
			$sm->assign('actions', 'В текущем режиме просмотр действий по контрагенту недоступен');
		}
	}
	
	
	
	
	
	
	
	
	if(isset($_GET['show_actions'])) $sm->assign('show_actions', 1);
	
	
	
	$content=$sm->fetch('suppliers/supplier_form_page.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	$smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);



$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>