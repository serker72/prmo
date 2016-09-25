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
require_once('classes/orgsgroup.php');
require_once('classes/orgitem.php');
require_once('classes/discr_man_group.php');

require_once('classes/opfgroup.php');
require_once('classes/bdetailsgroup.php');
require_once('classes/bdetailsitem.php');
require_once('classes/user_s_item.php');

require_once('classes/fagroup.php');
require_once('classes/faitem.php');

require_once('classes/supplierphonegroup.php');
require_once('classes/supplierphonekindgroup.php');
require_once('classes/supplierphoneitem.php');

require_once('classes/suppliertouser.php');
require_once('classes/suporgcreator.php');

require_once('classes/suppliercontactgroup.php');
require_once('classes/suppliercontactkindgroup.php');

require_once('classes/supplier_district_group.php');
require_once('classes/supplier_cities_group.php');
require_once('classes/supplier_country_group.php');


require_once('classes/supcontract_group.php');
require_once('classes/supplier_country_group.php');

require_once('classes/supplier_ruk_item.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Карта организации');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new OrgItem; //SupplierItem;
$lc=new SupOrgCreator;
$log=new ActionLog;

$ssgr=new SupplierToUser1;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=120;
	break;
	case 1:
	$object_id=121;
	break;
	case 2:
	$object_id=122;
	break;
	default:
	$object_id=120;
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
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
}

//журнал событий 
if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту организации',NULL,121, NULL, $editing_user['code'].' '.$editing_user['full_name'],$id);			
}

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',120)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
	
   
    //обычная загрузка прочих параметров
	
	//$params['name']=SecStr($_POST['name']);
	$params['full_name']=SecStr($_POST['full_name']);
	
	$params['print_name']=SecStr($_POST['print_name']);
	
	
	$params['ur_or_fiz']=abs((int)$_POST['ur_or_fiz']);
	$params['opf_id']=abs((int)$_POST['opf_id']);
	
	$params['inn']=SecStr($_POST['inn']);
	$params['kpp']=SecStr($_POST['kpp']);
	$params['okpo']=SecStr($_POST['okpo']);
	$params['legal_address']=SecStr($_POST['legal_address']);
	
//	$params['phone_work']=SecStr($_POST['phone_work']);
	//$params['phone_cell']=SecStr($_POST['phone_cell']);

	//$params['email']=SecStr($_POST['email']);
	
	//$params['contract_no']=SecStr($_POST['contract_no']);
	//$params['contract_pdate']=SecStr($_POST['contract_pdate']);
	
	$params['time_from_h']=SecStr($_POST['time_from_h']);
	$params['time_from_m']=SecStr($_POST['time_from_m']);
	$params['time_to_h']=SecStr($_POST['time_to_h']);
	$params['time_to_m']=SecStr($_POST['time_to_m']);
	
	
	/*$params['chief']=SecStr($_POST['chief']);
	$params['main_accountant']=SecStr($_POST['main_accountant']);*/
	
	//$params['code']=SecStr($_POST['code']);
		$lc->ses->ClearOldSessions();
	$params['code']=SecStr($lc->GenLogin($result['id'])); //SecStr($_POST['code']);

   	
	$code=$ui->Add($params); //, $quests);
	$lc->ses->DelSession($result['id']);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал организацию',NULL,120,NULL,$params['name'],$code);
		
		foreach($params as $k=>$v){
			
			//if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'редактировал организацию',NULL,120, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			//}
		}	
	}
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: organizations.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',121)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_organization.php?action=1&id=".$code.'#req');
		die();	
		
	}else{
		header("Location: organizations.php");
		die();
	}
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование организации
	
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
    //if(($editing_user['is_confirmed']==0)&&!isset($_POST['is_confirmed'])){
	$condition=true;
	$condition=$condition&&($editing_user['is_confirmed']==0);
	$condition=$condition&&(!isset($_POST['is_confirmed'])||(isset($_POST['is_confirmed'])&&(count($_POST)>2)));
	
	
	if($condition){	
			
	
	
	
	
   // if(($editing_user['is_confirmed']==0)&&!isset($_POST['is_confirmed'])){
	  //обычная загрузка прочих параметров
	  $params=array();
	 // $params['name']=SecStr($_POST['name']);
	  $params['full_name']=SecStr($_POST['full_name']);
	  
	  $params['print_name']=SecStr($_POST['print_name']);
	  
	  $params['ur_or_fiz']=abs((int)$_POST['ur_or_fiz']);
	  $params['opf_id']=abs((int)$_POST['opf_id']);
	  
	  $params['inn']=SecStr($_POST['inn']);
	  $params['kpp']=SecStr($_POST['kpp']);
	  $params['okpo']=SecStr($_POST['okpo']);
	  $params['legal_address']=SecStr($_POST['legal_address']);
	  
//	  $params['phone_work']=SecStr($_POST['phone_work']);
	//  $params['phone_cell']=SecStr($_POST['phone_cell']);
  
	 // $params['email']=SecStr($_POST['email']);
	  
	//  $params['contract_no']=SecStr($_POST['contract_no']);
	//  $params['contract_pdate']=SecStr($_POST['contract_pdate']);
	  
	  $params['time_from_h']=SecStr($_POST['time_from_h']);
	  $params['time_from_m']=SecStr($_POST['time_from_m']);
	  $params['time_to_h']=SecStr($_POST['time_to_h']);
	  $params['time_to_m']=SecStr($_POST['time_to_m']);
	  
/*	  $params['chief']=SecStr($_POST['chief']);
	$params['main_accountant']=SecStr($_POST['main_accountant']);*/
	
	
	  if(isset($_POST['is_upr_nalog'])) $params['is_upr_nalog']=1;
	  else  $params['is_upr_nalog']=0;
	  
	  $params['upr_nalog_no']=SecStr($_POST['upr_nalog_no']);
	  
	  
	  
	  
	  
	  if(isset($_POST['is_customer'])) $params['is_customer']=1;
	  else  $params['is_customer']=0;
	  
	   if(isset($_POST['is_supplier'])) $params['is_supplier']=1;
	  else  $params['is_supplier']=0;
	  
	
	
	  
	  $ui->Edit($id,$params);
	  
	  //записи в лог. сравнить старые и новые записи
	  foreach($params as $k=>$v){
		  
		  if(addslashes($editing_user[$k])!=$v){
			  $log->PutEntry($result['id'],'редактировал организацию',NULL,121, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		  }
	  }
	  
	}
	
	
	//утверждение заполнения
	if($editing_user['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',148))){
			if(!isset($_POST['is_confirmed'])){
				$ui->Edit($id,array('is_confirmed'=>0));
				
				$log->PutEntry($result['id'],'снял утверждение заполнения организации',NULL,148, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',147)){
			if(isset($_POST['is_confirmed'])){
				$ui->Edit($id,array('is_confirmed'=>1, 'confirm_user_id'=>$result['id'], 'confirm_pdate'=>time()));
				
				$log->PutEntry($result['id'],'утвердил заполнение организации',NULL,147, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	
	
	//утверждение активности
	if($editing_user['is_active']==1){
		
		$cnt=$ui->CalcOtherActive($editing_user['id']);
		if($cnt==0){
			$can_confirm_active=$au->user_rights->CheckAccess('w',814);
			
		}else $can_confirm_active=$au->user_rights->CheckAccess('w',149);
		
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if($can_confirm_active){
			if(!isset($_POST['is_active'])){
				$ui->Edit($id,array('is_active'=>0));
				
				$log->PutEntry($result['id'],'снял утверждение организации',NULL,149, NULL, NULL,$id);	
			}
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',123)){
			if(isset($_POST['is_active'])){
				$ui->Edit($id,array('is_active'=>1, 'user_id'=>$result['id'], 'active_pdate'=>time()));
				
				$log->PutEntry($result['id'],'утвердил организацию',NULL,123, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	 
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: organizations.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 16 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',121)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_organization.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: organizations.php");
		die();
	}
	
	die();
}elseif(($action==1)&&(isset($_POST['doEditUsers'])||isset($_POST['doEditUsersStay']))){
	if(!$au->user_rights->CheckAccess('w',146)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//сотрудники организации
	$sectors=array();
	foreach($_POST as $k=>$v){
		if(eregi("^user_id_",$k)&&($v==1)){
			$sectors[]=	abs((int)eregi_replace("^user_id_","",$k));
		}
	}
	
	
	
	
	 if(($editing_user['is_confirmed']==0)&&isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==0)){ //???
	  	$log_entries=$ssgr->AddUsersToSupplierArray($id,$sectors);
		
		/*echo '<pre>';
		print_r($log_entries); 
		echo '</pre>';
		die();
		*/
		/*$ssgr->AddUsersToSupplierArray($id,$sectors); //AddSectorsToStorageArray($id,$sectors);
		$log->PutEntry($result['id'],'очистил пользователей организации',NULL,121,NULL,NULL,$id);	
		foreach($sectors as $k=>$v){
			//$_sklad=$sector->GetItemById($v);
			//if($_sklad!==false) {
			   // $log->PutEntry($result['id'],'назначил складу участок',NULL,76,NULL,$_sklad['name'],$id);	
				$log->PutEntry($result['id'],'назначил пользователя организации',$v,121,NULL,$params['name'],$id);	
			//}
		}*/
		
		foreach($log_entries as $k=>$v){
			
			if($v['action']==0){
				$log->PutEntry($result['id'],'назначил пользователя организации',$v['user_id'],121,NULL,NULL,$id);	
			}elseif($v['action']==2){
				$log->PutEntry($result['id'],'удалил пользователя организации',$v['user_id'],121,NULL,NULL,$id);
			}
			
		}
		
		
	  }
	  
    //}
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doEditUsers'])){
		header("Location: organizations.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditUsersStay'])){
		//если есть доступ к объекту 16 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',121)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_organization.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: organizations.php");
		die();
	}
	
	die();
	
	
}elseif(($action==2)){
	if(!$au->user_rights->CheckAccess('w',122)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$ui->Del($id);
	
	$log->PutEntry($result['id'],'удалил организацию',NULL,122, NULL, $editing_user['name'],$id);	
	
	header("Location: organizations.php");
	die();
}




//установка прав на работу с формой
if(isset($_POST['doInp'])){
	
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - object_id
			//3rd figure - user_id
			eregi("^do_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			
			if(($regs!==NULL)&&isset($_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					header("HTTP/1.1 403 Forbidden");
					header("Status: 403 Forbidden");
					include("403.php");
					die();	
				}
				
				
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry( $pro['id'], "установил доступ ".$regs[1],$regs[3], $regs[2]);
					//PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL){
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
			}
		}
	}
	
	header("Location: organizations.php");	
	die();
}




//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


	$_menu_id=31;
	
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
			//opf
		$opg=new OpfGroup;
		$opfs=$opg->GetItemsArr();
		$ops=array(); $op_ids=array();
		foreach($opfs as $k=>$v){
			$ops[]=$v['name'];
			$op_ids[]=$v['id'];
		}
		$sm1->assign('opfs_total',$opfs);
		$sm1->assign('opfs',$ops);
		$sm1->assign('opf_ids',$op_ids);
		$sm1->assign('can_expand_opf',$au->user_rights->CheckAccess('w',139)); 
		
		$lc->ses->ClearOldSessions();
		
		$sm1->assign('code', $lc->GenLogin($result['id']));
		//echo $lc->GenLogin($result['id']);
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',120)); 
		
	
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',121)); 
	
		
		
		$user_form=$sm1->fetch('org/org_create.html');
	}elseif($action==1){
		//редактирование пользователя
		
		
		//строим вкладку администрирования
		/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',117)||
								$au->user_rights->CheckAccess('x',120)||
								$au->user_rights->CheckAccess('x',121)||
								$au->user_rights->CheckAccess('x',122)||
								$au->user_rights->CheckAccess('x',123)||
								$au->user_rights->CheckAccess('x',124)
								);
		$dto=new DiscrTableObjects($result['id'],array('117','120','121','122','123','124'));
		$admin=$dto->Draw('ed_organization.php','admin/admin_objects.html');
		$sm->assign('admin',$admin);
		*/
		
		//таблица прав по данному пользователю
		/*$dtu=new DiscrTableUser($editing_user['id']);
		$rights=$dtu->Draw('supplier.php','admin/admin_user_rights.html');
		$sm->assign('rights',$rights);
		*/
		
		$sm->assign('has_org_users',true);
		
		
		$sm1=new SmartyAdm;
		
		
		//возможность РЕДАКТИРОВАНИЯ - только если Is_confirmed==0
		$sm1->assign('can_modify', $editing_user['is_confirmed']==0);    //$au->user_rights->CheckAccess('w',121)); //   $editing_user['is_active']==0);
		
		
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
		
			//opf
		$opg=new OpfGroup;
		$opfs=$opg->GetItemsArr();
		$ops=array(); $op_ids=array();
		foreach($opfs as $k=>$v){
			$ops[]=$v['name'];
			$op_ids[]=$v['id'];
		}
		$sm1->assign('opfs_total',$opfs);
		$sm1->assign('opfs',$ops);
		$sm1->assign('opf_ids',$op_ids);
		$sm1->assign('can_expand_opf',$au->user_rights->CheckAccess('w',139)); 
		
		
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
			

		
		//города
		$_csg=new SupplierCitiesGroup;
		$csg=$_csg->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('cities', $csg);
		
		/*$_dists=new SupplierDistrictGroup;
		$dists=$_dists->GetItemsArr();
		$sm1->assign('dis', $dists);*/
		$sm1->assign('can_expand_cities',$au->user_rights->CheckAccess('w',584)); //добавить права для городов
		
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm1->assign('cous', $cous);
		
		
		
		
		
		//реквизиты
		$rg=new BDetailsGroup;
		$sm1->assign('rekviz',$rg->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('can_req',true);
		$sm1->assign('can_req_edit',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',142))); //$editing_user['is_active']==0);
		
		//договоры
		$scg=new SupContractGroup;
		$sm1->assign('contracts',$scg->GetItemsByIdArr($editing_user['id']));
		
		
		//адреса
		$ag=new FaGroup;
		
		//print_r($ag->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('fact_addrs',$ag->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('form_ids',$a_ids);
		$a_forms=$ag->GetFormsArr();
		$a_ids=array(); $a_names=array(); 
		foreach($a_forms as $k=>$v){
			$a_ids[]=$v['id']; $a_names[]=$v['name'];	
		}
		
		$sm1->assign('forms',$a_names);
		$sm1->assign('can_fa',true);
		$sm1->assign('can_fa_edit', ($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',141)));//$au->user_rights->CheckAccess('w',121)); //$editing_user['is_active']==0);
			
			
		//контакты
		$rg=new SupplierContactGroup;
		$sm1->assign('contacts',$rg->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('can_cont',true);
		$sm1->assign('can_add_contact',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',140)));
		$sm1->assign('can_cont_edit',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',140)));
		$rrg=new SupplierContactKindGroup;
		$sm1->assign('kinds',$rrg->GetItemsArr());
		
			
		
		//руководитель, гл.бух.
		$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActual($editing_user['id'], 1);
		$sri_2=$_sri->GetActual($editing_user['id'], 2);
		
			
		$editing_user['chief']=	$sri_1['fio'];
		$editing_user['main_accountant']=	$sri_2['fio'];
		
				
					
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',121)); 
		$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',122)); 
		
		
		//блок утверждения
		
		
		
		//блок утверждения 
		//кто утвердил активность
		if(($editing_user['is_active']==1) ){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['active_pdate']);
			
			$sm1->assign('confirmer',$confirmer);
		}
		
		
		//доступность нажатия утверждения активности
		$can_confirm_active=false;
		if($editing_user['is_active']==1){
			//права на снятие 
			$cnt=$ui->CalcOtherActive($editing_user['id']);
			if($cnt==0){
				$can_confirm_active=$au->user_rights->CheckAccess('w',814);
				
			}else $can_confirm_active=$au->user_rights->CheckAccess('w',149);
			
			$sm1->assign('is_only', true);
		
		}else{
			//89
			$can_confirm_active=$au->user_rights->CheckAccess('w',123);
		}
		
		
		
		//кто утвердил заполнение
		if(($editing_user['is_confirmed']==1) ){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['confirm_user_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('confirmer_is_confirmed',$confirmer);
		}
		
		//доступность нажатия утверждения
		$can_confirm=false;
		if($editing_user['is_confirmed']==1){
			 if($au->user_rights->CheckAccess('w',148)){
				//есть права + сам утвердил
				$can_confirm=true;	
			}else{
				$can_confirm=false;
			}
		}else{
			//89
			$can_confirm=$au->user_rights->CheckAccess('w',147);
		}
		
		
		$sm1->assign('can_confirm',$can_confirm);
		$sm1->assign('can_confirm_active',$can_confirm_active);
		
		
		
		
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
		
		
		
		
		$sm1->assign('user',$editing_user);
		
		$user_form=$sm1->fetch('org/org_edit.html');
		
		
		
		
		
		
		$sm1=new SmartyAdm;
		
		$sm1->assign('user',$editing_user);
		
		
		
		//пользователи, работающие с организацией:
		$sm1->assign('can_modify_users',($editing_user['is_confirmed']==0)&&($au->user_rights->CheckAccess('w',146)));
		$storages=$ssgr->GetAllOrgUsersArr($editing_user['id']); //->GetAllStorageSectsArr($id);
		$sm1->assign('storages',$storages);
		$sm1->assign('div',round(count($storages)/2));
		
		
		
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',121)); 
		$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',122)); 
		
		
		
		
		$sm1->assign('can_confirm',$can_confirm);
		
		$user_to_org=$sm1->fetch('org/org_users_edit.html');
		
		$sm->assign('org_users', $user_to_org);
		
		
		
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',516)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(117,
120,
139,
121,
140,
141,
142,
143,
144,
145,
146,
147,
123,
148,
149,
122)));
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_organization.php',true,true,true);
			
			$sm->assign('syslog',$llg);
		
		}
	}
	
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('org/org_form_page.html');
	
	
	
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