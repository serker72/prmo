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

require_once('classes/supplier_cities_group.php');

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Запись планировщика');

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


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

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

 
	


if($action==0){
	 
	if(!isset($_GET['kind_id'])){
		if(!isset($_POST['kind_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $kind_id=abs((int)$_POST['kind_id']);	
	}else $kind_id=abs((int)$_GET['kind_id']);
	
	
	
	if(!isset($_GET['datetime'])){
		if(!isset($_POST['datetime'])){
			$datetime=date('Y-m-d');
		}else $datetime=($_POST['datetime']);	
	}else $datetime=($_GET['datetime']);
	
	
	if(!isset($_GET['task_id'])){
		if(!isset($_POST['task_id'])){
			$task_id=0;
		}else $task_id=($_POST['task_id']);	
	}else $task_id=($_GET['task_id']);
	
}elseif(($action==1)||($action==2)||($action==10)||($action==3)||($action==4)){

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
	
	//если задача - то перейти на страницу задачи
	if($editing_user['kind_id']==1){
		header("Location: ed_sched_task.php?action=1&id=".$id);
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	//видим:
	//пол-ль - создал
	//или пол-ль - в списке видящих
	
	$available_users=$_plan->GetAvailableUserIds($result['id'],false,$editing_user['kind_id']);
	
	
	$is_shown= 
	
	(($editing_user['kind_id']!=5)&&(
	in_array($editing_user['manager_id'], $available_users)||
	
	in_array($editing_user['created_id'], $available_users)))
	
	
	||
	
	 
	(($editing_user['kind_id']==5)&&
		(
		in_array($result['id'], $_dem->GetUserIdsArr($id))||
		in_array($editing_user['manager_id'], $available_users)||
	
		in_array($editing_user['created_id'], $available_users)
		));

	
	if(!$is_shown){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	if($editing_user['kind_id']==4){
		
		$_editable_status_id=array();
		$_editable_status_id[]=18;
	}
 
}


//файловый блок

if($action==2){
	if(!$au->user_rights->CheckAccess('w',905)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
		
	
	if(!isset($_GET['file_id'])){
		if(!isset($_POST['file_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();
		}else $file_idabs((int)$_POST['file_id']);
	}else $file_id=abs((int)$_GET['file_id']);
	
	$_pfi=new SchedFileItem;
	
	$file=$_pfi->GetItemById($file_id);
	
	if($file!==false){
		$_pfi->Del($file_id);
		
		$log->PutEntry($result['id'],'удалил файл заметки',NULL, 905,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: ed_sched.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}



//удаление папок
if(isset($_GET['action'])&&($_GET['action']==3)){
	
	if(!$au->user_rights->CheckAccess('w',905)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	if(isset($_GET['file_id'])) $file_id=abs((int)$_GET['file_id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_ff=new FileDocFolderItem(1, $id, new SchedFileItem(1));
	$_ff->SetTablename('sched_file_folder');
	$_ff->SetDocIdName('id');
		
	
	
	$file=$_ff->GetItemById($file_id);
	
	
	
	if($file!==false){
		$_ff->Del($file_id,$file,$result,905);
		
		$log->PutEntry($result['id'],'удалил папку',NULL,905,NULL,'имя папки '.SecStr($file['filename']));
		//echo 'zzz';
	}
	
	header("Location:ed_sched.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}


//перемещение папок и файлов
if(isset($_GET['action'])&&($_GET['action']==4)){
	 
	if(!$au->user_rights->CheckAccess('w',905)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	 
	
	
	//
	$move_folder_id=abs((int)$_GET['move_folder_id']);
	
	//обработать файлы
	$files=$_GET['check_file'];
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		  $_file=new SchedFileItem(1);
		  
		 
		  $_file->Edit($v, array('folder_id'=>$move_folder_id));
		  
		  //записи в журнал, сообщения...
	
		}
	}
	
	//обработать папки
	$files=$_GET['fcheck_file'];
	
	
	if(is_array($files)&&(count($files)>0)){
		foreach($files as $v){	
	
	
		 	
			$_ff=new FileDocFolderItem(1, $id, new SchedFileItem(1));
	$_ff->SetTablename('sched_file_folder');
	$_ff->SetDocIdName('id');
		
		    $_ff->Edit($v, array('parent_id'=>$move_folder_id), NULL,$result, 905);
			
			//записи в журнал, сообщения...
		}
	}
	
	
	header("Location:ed_sched.php?action=1&id=".$id.'&folder_id='.$folder_id);
	die();
}




//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	
	
	if($params['kind_id']==4){
		$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
		
		$params['incoming_or_outcoming']=abs((int)$_POST['incoming_or_outcoming']);
		$params['plan_or_fact']=abs((int)$_POST['plan_or_fact']);
		$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
		$params['ptime_beg']= SecStr($_POST['ptime_beg_h']).':'.SecStr($_POST['ptime_beg_m']).':00';
		
		
		$params['manager_id']=abs((int)$_POST['manager_id']); //$result['id'];
		
		$params['description']= SecStr($_POST['description']);
		$params['contact_mode']=0;
		
		$params['is_confirmed']=1;
		$params['user_confirm_id']=$result['id'];
		$params['confirm_pdate']=time();
		$params['report']= SecStr($_POST['report']);
			
		
		if($params['plan_or_fact']==1) {
			
			$params['is_confirmed_done']=1;
			$params['user_confirm_done_id']=$result['id'];
			$params['confirm_done_pdate']=time();
			
			$params['status_id']=10;
		}else{
			
			$params['status_id']=22;
			
		}
		
	}elseif(($params['kind_id']==2) ){
		$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
		
		//$params['incoming_or_outcoming']=abs((int)$_POST['incoming_or_outcoming']);
		
		$params['plan_or_fact']=abs((int)$_POST['plan_or_fact']);
		$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
		$params['ptime_beg']= SecStr($_POST['ptime_beg_h']).':'.SecStr($_POST['ptime_beg_m']).':00';
		
		$params['pdate_end']= date('Y-m-d', DateFromdmY($_POST['pdate_end']));
		$params['ptime_end']= SecStr($_POST['ptime_end_h']).':'.SecStr($_POST['ptime_end_m']).':00';
		
		//$params['manager_id']=$result['id'];
		$params['manager_id']=abs((int)$_POST['manager_id']); 
		
		$params['description']= SecStr($_POST['description']);
		$params['contact_mode']=0;
		
		$params['is_confirmed']=1;
		$params['user_confirm_id']=$result['id'];
		$params['confirm_pdate']=time();
		
		
		if($params['plan_or_fact']==1) {
			$params['report']= SecStr($_POST['report']);
			
			$params['is_confirmed_done']=1;
			$params['user_confirm_done_id']=$result['id'];
			$params['confirm_done_pdate']=time();
			
			$params['status_id']=10;
		}else{
			$params['report']= SecStr($_POST['report']);
			$params['status_id']=22;
			
		}
		
	}elseif( ($params['kind_id']==3)){
		$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
		
		//$params['incoming_or_outcoming']=abs((int)$_POST['incoming_or_outcoming']);
		
		$params['plan_or_fact']=abs((int)$_POST['plan_or_fact']);
		$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
		$params['ptime_beg']= SecStr($_POST['ptime_beg_h']).':'.SecStr($_POST['ptime_beg_m']).':00';
		
		$params['pdate_end']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
		$params['ptime_end']= SecStr($_POST['ptime_end_h']).':'.SecStr($_POST['ptime_end_m']).':00';
		
		//$params['manager_id']=$result['id'];
		$params['manager_id']=abs((int)$_POST['manager_id']); 
		
		$params['meet_value']= SecStr($_POST['meet_value']);
		$params['meet_id']= abs((int)$_POST['meet_id']);
		
		$params['description']= SecStr($_POST['description']);
		$params['contact_mode']=0;
		
		$params['is_confirmed']=1;
		$params['user_confirm_id']=$result['id'];
		$params['confirm_pdate']=time();
		
		
		if($params['plan_or_fact']==1) {
			$params['report']= SecStr($_POST['report']);
			
			$params['is_confirmed_done']=1;
			$params['user_confirm_done_id']=$result['id'];
			$params['confirm_done_pdate']=time();
			
			$params['status_id']=10;
		}else{
			
			$params['status_id']=22;
			
		}
		
	}else{
	
		$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
		$params['incoming_or_outcoming']=abs((int)$_POST['incoming_or_outcoming']);
		
		
		$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
		$params['ptime_beg']= SecStr($_POST['ptime_beg']).':00';
		$params['contact_mode']=abs((int)$_POST['contact_mode']);
		$params['contact_value']=SecStr($_POST['contact_value']);
		$params['contact_name']= SecStr($_POST['contact_name']);
		$params['manager_id']=$result['id'];
		
		$params['description']= SecStr($_POST['description']);
		$params['topic']= SecStr($_POST['topic']);
		
		$params['status_id']=9;
		
		
		//print_r($params);
	}
	
	
	if( ($params['kind_id']==5)){
		if(isset($_POST['wo_supplier'])) $params['wo_supplier']=1;
		else $params['wo_supplier']=0;
	}

	
	$code=$_dem->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	//установим контрагента - адресата
	if(($params['kind_id']==4)){
		$_ci=new SchedContactItem;
		
		$r_params=array();
		$r_params['sched_id']=$code;
		$r_params['supplier_id']=abs((int)$_POST['supplier_id']);
		$r_params['contact_id']=abs((int)$_POST['contact_id']);
		$r_params['value']=SecStr($_POST['ccontact_value']);
		
		
		
		$_ci->Add($r_params);
		
		
		$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
		
		$si=$_si->getitembyid($r_params['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
		$sci=$_sci->getitembyid($r_params['contact_id']);
		$res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$r_params['value'];
		
		$log->PutEntry($result['id'],'создал задачу планировщика',NULL, 904, NULL, 'установлен адресат из справочника контрагентов '.$res,$code);		
			
	}
	
	//для ком-ки: создадим контрагентов, города
	if(($params['kind_id']==2)||($params['kind_id']==3)){
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new Sched_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  //$right_id=abs((int)$_POST['new_share_right_id_'.$hash]);
			  
			  
			  
			  //найдем контакты
			  $contacts=array();
			  //supplier_contact_id_%{$suppliers[supsec].id}%_%{$contact.id}%
			  
			  foreach($_POST as $k1=>$v1) if(eregi("^supplier_contact_id_".$supplier_id."_([0-9]+)",$k1)){
			  	$contacts[]=abs((int)$v1);
			  }
			  
			  
			  if(isset($_POST['supplier_not_meet_'.$hash])) $not_meet=1;
			  else $not_meet=0;
			  
			 
			  $positions[]=array(
				  'sched_id'=>$code,
				   
				  'supplier_id'=>$supplier_id,
				  'contacts'=>$contacts,
				  'note'=> SecStr($_POST['supplier_note_'.$hash]),
				  'result'=> SecStr($_POST['supplier_result_'.$hash]),
				  'not_meet'=>$not_meet
			  );

			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($code, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			   $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			  if($params['kind_id']==2){
					if($v['not_meet']==1) $description.=', не встречался ';
					else $description.=' '.SecStr(' результат: '.$v['result'])  ;
			  }

			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил контрагента в запись планировщика',NULL,904,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в записи планировщика',NULL,904,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из записи планировщика',NULL,904,NULL,$description,$code);
			  }
			  
		  }
		
		
	
		
		//города
		$_cyg=new Sched_CityGroup;
		$_cyi=new SupplierCityItem;
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^city_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^city_id_","",$k));
			  
			  $city_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  //$right_id=abs((int)$_POST['new_share_right_id_'.$hash]);
			  
			  
			  //$kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id));
			 // $cyi=$_cyi->GetItemById($city_id);
			  $positions[]=array(
				  'sched_id'=>$code,
				   
				  'city_id'=>$city_id 
			  );
			  
		  }
		}
			
		
		 
		
		//внесем позиции
		$log_entries=$_cyg->AddCities($code, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $cyi=$_cyi->GetItemById($v['city_id']);
			  
			 
			  $description=SecStr($cyi['name'].' ');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил город в запись планировщика',NULL,904,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал город в записи планировщика',NULL,904,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил город из записи планировщика',NULL,904,NULL,$description,$code);
			  }
			  
		  }
		
	}
	
	
	//для заметки: создадим контрагентов
	if(($params['kind_id']==5)){
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new Sched_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  
			  
			 
			  $positions[]=array(
				  'sched_id'=>$code,
				   
				  'supplier_id'=>$supplier_id,
				  
				  'note'=> SecStr($_POST['supplier_note_'.$hash])
			  );
			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($code, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			  $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил контрагента в запись планировщика',NULL,904,NULL,$description,$code);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в записи планировщика',NULL,904,NULL,$description,$code);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из записи планировщика',NULL,904,NULL,$description,$code);
			  }
			  
		  }
		
	}
	
	

	
	//создадим напоминания!
	$_rem=new SchedRemindItem;
	if(isset($_POST['remind_do'])){
		
		if(($params['kind_id']==4)||($params['kind_id']==2)||($params['kind_id']==3)){
		
		
			$r_params=array();
			$r_params['user_id']=$params['manager_id']; // $result['id'];
			$r_params['sched_id']=$code;
			
			$remind_period=	abs((int)$_POST['remind_period']);
			$delta=0;
			
			switch($remind_period){
				case 1:
					$delta=15*60;
				break;
				case 2:
					$delta=30*60;
				break;
				case 3:
					$delta=60*60;
				break;
				case 4:
					$delta=24*60*60;
				break;
				
				case 5:
					$delta=3*24*60*60;
				break;
				case 6:
					$delta=7*24*60*60;
				break;
				case 7:
					$delta=14*24*60*60;
				break;
				case 8:
					$delta=30*24*60*60;
				break;
				default:
					$delta=15*60;
				break;
				
			}
			
			
			$r_params['action_time']=DateFromdmY($_POST['pdate_beg'])+(int)$_POST['ptime_beg_h']*60*60+(int)$_POST['ptime_beg_m']*60-$delta;
			
			$r_params['is_viewed']=0;
			
			
			$_rem->Add($r_params);
			
			$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'установлено напоминание на '.date('d.m.Y H:i:s', $r_params['action_time']),$code);		
		}else{
			//kind==5
				
						$r_params=array();
			$r_params['user_id']=$params['manager_id']; //$result['id'];
			$r_params['sched_id']=$code;
			$r_params['action_time']=Datefromdmy($_POST['remind_pdate'])+60*60*(int)substr($_POST['remind_ptime'],0,2) + 60*(int)substr($_POST['remind_ptime'],3,2);
			$r_params['is_viewed']=0;
			
			
			$_rem->Add($r_params);
			
			$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'установлено напоминание на '.date('d.m.Y H:i:s', $r_params['action_time']),$code);		

		}
			
	}
	
	//с кем делимся заметкой
	if(($params['kind_id']==5)){
			$_kpi=new Sched_UserItem;
			$_user=new UserSItem;
			$positions=array();
			
			foreach($_POST as $k=>$v){
			  if(eregi("^new_hash_([0-9a-z]+)",$k)){
				  
				  $hash=eregi_replace("^new_hash_","",$k);
				  
				  $user_id=abs((int)$_POST['new_share_user_id_'.$hash]);
				  $right_id=abs((int)$_POST['new_share_right_id_'.$hash]);
				  
				  
				  /*$kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id));
				  $user=$_user->GetItemById($user_id);*/
				  $positions[]=array(
					  'sched_id'=>$code,
					   
					  'user_id'=>$user_id,
					  
					  'right_id'=>$right_id
				  );
				  
			  }
			}
				
			
			 
			/*echo '<pre>';
			print_r($_POST);
			print_r($positions);
			echo '</pre>';
			die(); */
			//внесем позиции
			$log_entries=$_dem->AddUsers($code,$positions);
			//die();
			//запишем в журнал
			 foreach($log_entries as $k=>$v){
				  $user=$_user->GetItemById($v['user_id']);
				  
				 
				  $description=SecStr($user['name_s'].' '.$user['login']).', права: ';
				  if($v['right_id']==1) $description.='чтение';
				  else  $description.='чтение, запись';
				  
				  
				  if($v['action']==0){
					  $log->PutEntry($result['id'],'добавил сотрудника в заметку',NULL,905,NULL,$description,$code);	
				  }elseif($v['action']==1){
					  $log->PutEntry($result['id'],'редактировал сотрудника в заметке',NULL,905,NULL,$description,$code);
				  }elseif($v['action']==2){
					  $log->PutEntry($result['id'],'удалил сотрудника из заметки',NULL,905,NULL,$description,$code);
				  }
				  
			  }
		}
	 
	 
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: shedule.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		 
		header("Location: ed_sched.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: shedule.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay'])||isset($_POST['notActual'])||isset($_POST['doActual']))){

	 
	
	//редактирование возможно, если позволяет статус
	
	 
	$condition =in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	
	
	if($condition) {
		 
		$condition= (
			($editing_user['kind_id']!=5)||
			(($editing_user['kind_id']==5)&&( in_array($editing_user['manager_id'], $available_users)))||
		
			(($editing_user['kind_id']==5)&&($editing_user['manager_id']!=$result['id'])&&in_array($result['id'], $_dem->GetUserIdsArr($id,2)))
			
			);
	}
	
	
	if($condition){
		$params=array();
		//обычная загрузка прочих параметров
		
		//print_r($_POST); die();
		
		if($editing_user['kind_id']==4){
			$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
			
			//$params['ptime_beg']= SecStr($_POST['ptime_beg']).':00';
			
			$params['ptime_beg']= SecStr($_POST['ptime_beg_h']).':'.SecStr($_POST['ptime_beg_m']).':00';
		
		
				
			$params['description']= SecStr($_POST['description']);
			
			$params['report']= SecStr($_POST['report']);
			
			$params['manager_id']=abs((int)$_POST['manager_id']);
		}elseif($editing_user['kind_id']==2){
			$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
			$params['pdate_end']= date('Y-m-d', DateFromdmY($_POST['pdate_end']));
			
			
			$params['ptime_beg']= SecStr($_POST['ptime_beg_h']).':'.SecStr($_POST['ptime_beg_m']).':00';
			$params['ptime_end']= SecStr($_POST['ptime_end_h']).':'.SecStr($_POST['ptime_end_m']).':00';	
			$params['description']= SecStr($_POST['description']);
			
			//$params['report']= SecStr($_POST['report']);
			$params['manager_id']=abs((int)$_POST['manager_id']);
		}elseif($editing_user['kind_id']==3){
			$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
			$params['pdate_end']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));
			
			
			$params['ptime_beg']= SecStr($_POST['ptime_beg_h']).':'.SecStr($_POST['ptime_beg_m']).':00';
			$params['ptime_end']= SecStr($_POST['ptime_end_h']).':'.SecStr($_POST['ptime_end_m']).':00';	
			$params['description']= SecStr($_POST['description']);			
			$params['report']= SecStr($_POST['report']);			
			$params['meet_value']= SecStr($_POST['meet_value']);			
			$params['meet_id']= SecStr($_POST['meet_id']);
			
			$params['manager_id']=abs((int)$_POST['manager_id']);
			
			
		}elseif($editing_user['kind_id']==5){
			
			$params['incoming_or_outcoming']=abs((int)$_POST['incoming_or_outcoming']);		
			
			$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['pdate_beg']));			
			$params['ptime_beg']= SecStr($_POST['ptime_beg']).':00';
			
			
			$params['contact_mode']=abs((int)$_POST['contact_mode']);
			$params['contact_value']=SecStr($_POST['contact_value']);
						
			$params['contact_name']= SecStr($_POST['contact_name']);			
			$params['description']= SecStr($_POST['description']);
			
			$params['report']= SecStr($_POST['report']);
			
			
			$params['topic']= SecStr($_POST['topic']);
			
			
			//актуальность/неактуальность заметки
			if(isset($_POST['notActual'])) $params['note_is_actual']=0;
			elseif(isset($_POST['doActual'])) $params['note_is_actual']=1;
			
			if(isset($_POST['wo_supplier'])) $params['wo_supplier']=1;
			else $params['wo_supplier']=0;
		
		}
		
		
		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		
		$_res->instance->Edit($id, $params);
		
		
		//$_dem->Edit($id, $params);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if($k=='note_is_actual'){
					if($v==0) $log->PutEntry($result['id'],'редактировал запись планировщика',NULL,905, NULL, 'заметка отмечена неактуальной',$id);
					elseif($v==1) $log->PutEntry($result['id'],'редактировал запись планировщика',NULL,905, NULL, 'заметка отмечена актуальной',$id);
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'редактировал запись планировщика',NULL,905, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				

			
			
		}
		
		
		$_ci=new SchedContactItem;
		$_test_ci=$_ci->GetItemByFields(array('sched_id'=>$id));
			
		//установим контрагента - адресата
		if(($editing_user['kind_id']==4)){
			
			$r_params=array();
			
			$r_params['supplier_id']=abs((int)$_POST['supplier_id']);
			$r_params['contact_id']=abs((int)$_POST['contact_id']);
			$r_params['value']=SecStr($_POST['ccontact_value']);
			
			if($_test_ci===false){
				$r_params['sched_id']=$id;
				$_ci->Add($r_params);
			}else{
				$_ci->Edit($_test_ci['id'], $r_params);
			}
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($r_params['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($r_params['contact_id']);
			$res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$r_params['value'];
			
			$log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'установлен адресат из справочника контрагентов '.$res,$code);		
				
		}else{
			if($_test_ci!==false){
			 
				 
				$_ci->Del($_test_ci['id']);
			}
		}
		
		
		
		
			
		
		//для заметки: создадим контрагентов
	if(($editing_user['kind_id']==5)){
		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new Sched_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  
			  
			 
			  $positions[]=array(
				  'sched_id'=>$id,
				   
				  'supplier_id'=>$supplier_id,
				  
				  'note'=> SecStr($_POST['supplier_note_'.$hash])
			  );
			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($id, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			  $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил контрагента в запись планировщика',NULL,905,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в записи планировщика',NULL,905,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из записи планировщика',NULL,905,NULL,$description,$id);
			  }
			  
		  }
		
	}
		
		

		
		
		
		
		
		//с кем делимся заметкой
		if(($editing_user['kind_id']==5)&&(($editing_user['manager_id']==$result['id'])||$au->user_rights->CheckAccess('w',928))){
			$_kpi=new Sched_UserItem;
			$_user=new UserSItem;
			$positions=array();
			
			foreach($_POST as $k=>$v){
			  if(eregi("^new_hash_([0-9a-z]+)",$k)){
				  
				  $hash=eregi_replace("^new_hash_","",$k);
				  
				  $user_id=abs((int)$_POST['new_share_user_id_'.$hash]);
				  $right_id=abs((int)$_POST['new_share_right_id_'.$hash]);
				  
				  
				  $kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id));
				  $user=$_user->GetItemById($user_id);
				  $positions[]=array(
					  'sched_id'=>$id,
					   
					  'user_id'=>$user_id,
					  
					  'right_id'=>$right_id
				  );
				  
			  }
			}
				
			
			 
			/*echo '<pre>';
			print_r($_POST);
			print_r($positions);
			echo '</pre>';
			die(); */
			//внесем позиции
			$log_entries=$_dem->AddUsers($id,$positions);
			//die();
			//запишем в журнал
			 foreach($log_entries as $k=>$v){
				  $user=$_user->GetItemById($v['user_id']);
				  
				 
				  $description=SecStr($user['name_s'].' '.$user['login']).', права: ';
				  if($v['right_id']==1) $description.='чтение';
				  else  $description.='чтение, запись';
				  
				  
				  if($v['action']==0){
					  $log->PutEntry($result['id'],'добавил сотрудника в заметку',NULL,905,NULL,$description,$id);	
				  }elseif($v['action']==1){
					  $log->PutEntry($result['id'],'редактировал сотрудника в заметке',NULL,905,NULL,$description,$id);
				  }elseif($v['action']==2){
					  $log->PutEntry($result['id'],'удалил сотрудника из заметки',NULL,905,NULL,$description,$id);
				  }
				  
			  }
				
			
		}
		 
		
	}
	
	
	//для ком-ки: создадим контрагентов, города
	if( 
		(in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id)&& (($editing_user['kind_id']==2)||($editing_user['kind_id']==3))
		)
		||
		
		(
		($editing_user['kind_id']==2)&&($editing_user['is_confirmed_done']==0)&&!in_array($editing_user['status_id'], array(3))
		)
		
		){

		//контрагенты
		$_supplier=new SupplierItem;
		$_sg=new Sched_SupplierGroup;
		$_opf=new OpfItem;
		
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^supplier_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^supplier_id_","",$k));
			  
			  $supplier_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			  
			  
			  //найдем контакты
			  $contacts=array();
			  //supplier_contact_id_%{$suppliers[supsec].id}%_%{$contact.id}%
			  
			  foreach($_POST as $k1=>$v1) if(eregi("^supplier_contact_id_".$supplier_id."_([0-9]+)",$k1)){
			  	$contacts[]=abs((int)$v1);
			  }
			  
			    if(isset($_POST['supplier_not_meet_'.$hash])) $not_meet=1;
			  else $not_meet=0;
			  
			  if(isset($_POST['supplier_note_'.$hash])) $note=SecStr($_POST['supplier_note_'.$hash]);
			  else $note=NULL;
			  

			  
			  
			   $positions[]=array(
				  'sched_id'=>$id,
				   
				  'supplier_id'=>$supplier_id,
				  'contacts'=>$contacts,
				  'note'=> $note,
				  'result'=> SecStr($_POST['supplier_result_'.$hash]),
				  'not_meet'=>$not_meet
			  );

			  
		  }
		}
		
		$log_entries=$_sg->AddSuppliers($id, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $supplier=$_supplier->GetItemById($v['supplier_id']);
			  $opf=$_opf->GetItemById($supplier['opf_id']); 
			 
			  $description=SecStr($supplier['full_name'].' '.$opf['name'].', примечание: '.$v['note']);
			 
			    if($editing_user['kind_id']==2){
					if($v['not_meet']==1) $description.=', не встречался ';
					else $description.=' '.SecStr('результат: '.$v['result'])  ;
			  }
			  

			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил контрагента в запись планировщика',NULL,905,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в записи планировщика',NULL,905,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из записи планировщика',NULL,905,NULL,$description,$id);
			  }
			  
		  }
		
		
		
		
		//города
		$_cyg=new Sched_CityGroup;
		$_cyi=new SupplierCityItem;
		
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^city_id_([0-9]+)",$k)){
			  
			  $hash=abs((int)eregi_replace("^city_id_","",$k));
			  
			  $city_id=$hash; //abs((int)$_POST['new_share_user_id_'.$hash]);
			
			
			  $positions[]=array(
				  'sched_id'=>$id,
				   
				  'city_id'=>$city_id 
			  );
			  
		  }
		}
			
		
		 
		/*echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die(); */
		//внесем позиции
		$log_entries=$_cyg->AddCities($id, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			   $cyi=$_cyi->GetItemById($v['city_id']);
			  
			 
			  $description=SecStr($cyi['name'].' ');
			 
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил город в запись планировщика',NULL,905,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал город в записи планировщика',NULL,905,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил город из записи планировщика',NULL,905,NULL,$description,$id);
			  }
			  
		  }
		
	}
	
	
	
	
	
	//установка напоминаний
	if(
		((($editing_user['kind_id']==2)||($editing_user['kind_id']==3)||($editing_user['kind_id']==4))&&in_array($editing_user['status_id'], array(18,22,2)))||
		$condition
		)
	
	{
		//создадим напоминания!
		$_rem=new SchedRemindItem;
		$_test_rem=$_rem->GetItemByFields(array('sched_id'=>$id, 'user_id'=>$result['id']));
		if(isset($_POST['remind_do'])){
			
			
			$r_params=array();
			
			
			if(($editing_user['kind_id']==4)||($editing_user['kind_id']==2)||($editing_user['kind_id']==3)){
				$r_params['action_time']= Datefromdmy($_POST['remind_pdate'])+(int)($_POST['remind_ptime_h'])*60*60+ +(int)($_POST['remind_ptime_m'])*60;
		
			}else{
				$r_params['action_time']=Datefromdmy($_POST['remind_pdate'])+60*60*(int)substr($_POST['remind_ptime'],0,2) + 60*(int)substr($_POST['remind_ptime'],3,2);
			}
			$r_params['is_viewed']=0;
			
			if($_test_rem===false){
				$r_params['sched_id']=$id;
				$r_params['user_id']=$result['id'];
				$_rem->Add($r_params);
			}else{
				$_rem->Edit($_test_rem['id'], $r_params);
			}
			
			$log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'установлено напоминание на '.date('d.m.Y H:i:s', $r_params['action_time']),$code);		
				
		}else{
			if(($_test_rem!==false)&&($_test_rem['is_viewed']!=2)){
			
				$_rem->Del($_test_rem['id']);
			}
		}
			
		
	}
	
	//запись РЕЗУЛЬТАТА звонка
	if((($editing_user['kind_id']==4)||($editing_user['kind_id']==2)||($editing_user['kind_id']==3))&&in_array($editing_user['status_id'], array(18,22,2))){
		$params=array();
		$params['report']= SecStr($_POST['report']);
		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		
		$_res->instance->Edit($id, $params);
		
		
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
		}
	
	}
	
	
	
	if(($editing_user['kind_id']==4)||($editing_user['kind_id']==2)||($editing_user['kind_id']==3)){
		//утверждение заполнения
		
		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		if($editing_user['is_confirmed_done']==0){
		  
		  
		  	
		  if($editing_user['is_confirmed']==1){
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			  if(($au->user_rights->CheckAccess('w',905))||$au->user_rights->CheckAccess('w',96)){
				  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
					  
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,905, NULL, NULL,$id);	
					  
				  }
			  } 
			  
		  }else{
			  //есть права
			  if($au->user_rights->CheckAccess('w',905)){
				  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заполнение',NULL,905, NULL, NULL,$id);	
					  
					   
					  //die();
				  }
			  } 
		  }
		}
		
		
		 
		//утверждение выполнения
		if($editing_user['is_confirmed']==1){
		  
		 	/*
			//ком, встречи
			if(in_array($editing_user['kind_id'], array(2,3))){
				//снимаем утв
				if($editing_user['is_confirmed_done']==1){
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',923))||($editing_user['manager_id']==$result['id']));
				}else{
				//ставим утв	
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',915))||($editing_user['manager_id']==$result['id']));
				}
			//звонок	 
			}else{  
				
				//снимаем утв
				if($editing_user['is_confirmed_done']==1){
					//три подвида звонка, исходя из них - три разных объекта
					
					if(($editing_user['incoming_or_outcoming']==1)&&($editing_user['plan_or_fact']==0)) $our_object_id=925;
					elseif(($editing_user['incoming_or_outcoming']==1)&&($editing_user['plan_or_fact']==1)) $our_object_id=926;
					elseif(($editing_user['incoming_or_outcoming']==0)&&($editing_user['plan_or_fact']==1)) $our_object_id=927;
					else $our_object_id=927;
					
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',$our_object_id))||($editing_user['manager_id']==$result['id']));
				}else{
				//ставим утв.	
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
				}
			}
			
			*/
			
			
			  	
			
			
		  if($editing_user['is_confirmed_done']==1){
			    
				
				//снимаем утв
				//встреча, ком
			    if(in_array($editing_user['kind_id'], array(2,3))){
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',923))||($editing_user['manager_id']==$result['id']));
					
				}else {
					//звонок 
					//три подвида звонка, исходя из них - три разных объекта
						
						
						if(($editing_user['incoming_or_outcoming']==1)&&($editing_user['plan_or_fact']==0)) $our_object_id=925;
						elseif(($editing_user['incoming_or_outcoming']==1)&&($editing_user['plan_or_fact']==1)) $our_object_id=926;
						elseif(($editing_user['incoming_or_outcoming']==0)&&($editing_user['plan_or_fact']==1)) $our_object_id=927;
						else $our_object_id=927;
						
						$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',$our_object_id))||($editing_user['manager_id']==$result['id'])); 
					
				} 
			   
			 
			
			  if($can_confirm_shipping){
				  if((!isset($_POST['is_confirmed_done'])) &&($_res->instance->DocCanUnconfirmShip($id, $rss32))){
					  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение выполнения',NULL,905, NULL, NULL,$id);	
				  }
			  }
			  
		  }else{
		  //стаивим утв.
		  	  
			  //встреча, командировка	   
			  if(in_array($editing_user['kind_id'], array(2,3))){
				 $can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',915))||($editing_user['manager_id']==$result['id'])); 
			  }else{
			  //звонок	  
				 $can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
			  }
			   
			  //есть права
			  if($can_confirm_shipping){
				  if(isset($_POST['is_confirmed_done'])&&($_res->instance->DocCanConfirmShip($id, $rss32))){
					 
					 $_res->instance->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил выполнение',NULL,905, NULL, NULL,$id);	
					  
					  
					  
					   
					   if(in_array($editing_user['kind_id'], array(2,3))&&$au->user_rights->CheckAccess('w',916)&&($editing_user['manager_id']==$result['id'])){
							$_res->instance->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					 		$log->PutEntry($result['id'],'утвердил прием работы',NULL,916, NULL, 'автоматическое утверждение приема работы собственной встречи/командировки при утверждении выполнения при наличии прав на утверждение приема работы',$id);
					  
						   
					   }

						  
				  }
			  } 
		  }
		}
		
		//для встреч, командировок - блок утверждения приема работы
		if(($editing_user['kind_id']==2)||$editing_user['kind_id']==3){
			if($editing_user['is_confirmed_done']==1){
			  if($editing_user['is_fulfiled']==1){
				  //снятие утв.
				 
				 
				  $can_confirm_shipping=$au->user_rights->CheckAccess('w',924);
				  
				  if($can_confirm_shipping){
					  if((!isset($_POST['is_fulfiled'])) &&($_res->instance->DocCanUnconfirmFulfil($id, $rss32))){
						  
						  //echo 'zzzzzzzzzzzz';
						  $_res->instance->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
						  
						  $log->PutEntry($result['id'],'снял утверждение принятия работы',NULL,905, NULL, NULL,$id);	
					  }
				  }
				  
			  }else{
				  //установка утв. 
				 
				  
				   $can_confirm_shipping= $au->user_rights->CheckAccess('w',916);
				   
				  if($can_confirm_shipping){
					  if(isset($_POST['is_fulfiled'])&&($_res->instance->DocCanConfirmFulfil($id, $rss32))){
						 
						 $_res->instance->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
						  
						  $log->PutEntry($result['id'],'утвердил принятие работы',NULL,905, NULL, NULL,$id);	
							  
					  }
				  } 
			  }
			}
		}
	

			
		
	}

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: shedule.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])||isset($_POST['notActual'])||isset($_POST['doActual'])){
	
	 
		header("Location: ed_sched.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: shedule.php");
		die();
	}
	
	die();
}


 //журнал событий 
if($action==1){
	$log=new ActionLog;
	 
	$log->PutEntry($result['id'],'открыл запись планировщика',NULL,905, NULL, 'запись планировщика № '.$editing_user['id'],$id);
	 
				
} 



//работа с хедером
$stop_popup=true;


require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


	if($print==0) include('inc/menu.php');
	
	
	//демонстрация  страницы
	$smarty = new SmartyAdm;
	
	$sm1=new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//создание позиции
	 if($action==0){
		 
		$sm1->assign('manager_id', $result['id']);
		$sm1->assign('manager_string', $result['name_s']);
		
		//список сотрудников
		$_plans=new Sched_Group;
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, $kind_id);
		
		$_usg=new Sched_UsersSGroup; $dec_us=new DBDecorator;
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$dec_us->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
		}
		
		 // $dec_us->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		$managers=$_usg->GetItemsForBill($dec_us);
		$sm1->assign('managers', $managers);
		
		
		 
		$from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		
		
		
		//копируем данные
		if(isset($_GET['copyfrom'])){
			$old_doc=$_dem->getitembyid(abs((int)$_GET['copyfrom']));
			
			foreach($old_doc as $k=>$v) $old_doc[$k]=stripslashes($v);	
			
			$_res=new Sched_Resolver($old_doc['kind_id']);
			
			
			
			//для Заметки
			if($old_doc['kind_id']==5){
				$_suppliers=new Sched_SupplierGroup;
				$sup=$_suppliers->GetItemsByIdArr($old_doc['id']);
				$sm1->assign('suppliers', $sup);
			}
			//для встречи 
			if($old_doc['kind_id']==3){
				//план или факт - определить по дате!
				$datetime=$old_doc['pdate_beg'];
				$check=DateFromdmy(DateFromYmd($datetime))  +substr($time,  1,2 )*60*60+substr($time,  4,2 )*60;
				if($check>time()) $sm1->assign('plan_or_fact' ,0); 
				else  $sm1->assign('plan_or_fact' ,1); 
				
				//найти времена
				$sm1->assign('ptime_beg_hr',substr($old_doc['ptime_beg'],  0,2 ));
				$sm1->assign('ptime_beg_mr',substr($old_doc['ptime_beg'],  3,2 )); 
				
				$sm1->assign('ptime_end_hr',substr($old_doc['ptime_end'],  0,2 ));
				$sm1->assign('ptime_end_mr',substr($old_doc['ptime_end'],  3,2 )); 
				
				$sm1->assign('meet_id', $old_doc['meet_id']);
				
				 
				//города
				$_csg=new Sched_CityGroup;
				$csg=$_csg->GetItemsByIdArr($old_doc['id']);
				$sm1->assign('cities', $csg);
				
				//контрагенты
				$_suppliers=new Sched_SupplierGroup;
				$sup=$_suppliers->GetItemsByIdArr($old_doc['id']);
				$sm1->assign('suppliers', $sup);
					
			}
			//для командировки 
			if($old_doc['kind_id']==2){
				//план или факт - определить по дате!
				$datetime=$old_doc['pdate_beg'];
				$check=DateFromdmy(DateFromYmd($datetime))  +substr($time,  1,2 )*60*60+substr($time,  4,2 )*60;
				if($check>time()) $sm1->assign('plan_or_fact' ,0); 
				else  $sm1->assign('plan_or_fact' ,1); 
				
				$sm1->assign('pdate_end',  DateFromYmd($old_doc['pdate_end'])); 
				
				//найти времена
				$sm1->assign('ptime_beg_hr',substr($old_doc['ptime_beg'],  0,2 ));
				$sm1->assign('ptime_beg_mr',substr($old_doc['ptime_beg'],  3,2 )); 
				
				$sm1->assign('ptime_end_hr',substr($old_doc['ptime_end'],  0,2 ));
				$sm1->assign('ptime_end_mr',substr($old_doc['ptime_end'],  3,2 )); 
				
				
				//города
				$_csg=new Sched_CityGroup;
				$csg=$_csg->GetItemsByIdArr($old_doc['id']);
				$sm1->assign('cities', $csg);
				
				//контрагенты
				$_suppliers=new Sched_SupplierGroup;
				$sup=$_suppliers->GetItemsByIdArr($old_doc['id']);
				$sm1->assign('suppliers', $sup);
			}
			//для звонка
			if($old_doc['kind_id']==4){
				//вход, исход
				$sm1->assign('incoming_or_outcoming', $old_doc['incoming_or_outcoming']);
				
				if(($old_doc['plan_or_fact']==1)&&($old_doc['is_confirmed_done']==1)){
					//не копируем дату	
					
					$sm1->assign('ptime_beg_hr',date('H'));
					$sm1->assign('ptime_beg_mr',date('i')); 
					
					
					$sm1->assign('ptime_end_hr',date('H'));
					$sm1->assign('ptime_end_mr',date('i')); 
					
					$sm1->assign('plan_or_fact' ,1);
					
				}else{
					//план/факт - по текущей дате
					$datetime=$old_doc['pdate_beg'];
					$check=DateFromdmy(DateFromYmd($datetime))  +substr($time,  1,2 )*60*60+substr($time,  4,2 )*60;
					if($check>time()) $sm1->assign('plan_or_fact' ,0); 
					else  $sm1->assign('plan_or_fact' ,1); 
					
					//найти времена
					$sm1->assign('ptime_beg_hr',substr($old_doc['ptime_beg'],  0,2 ));
					$sm1->assign('ptime_beg_mr',substr($old_doc['ptime_beg'],  3,2 )); 
					
					$sm1->assign('ptime_end_hr',substr($old_doc['ptime_end'],  0,2 ));
					$sm1->assign('ptime_end_mr',substr($old_doc['ptime_end'],  3,2 )); 
					
					
					//видна ли цель????????
					//видна, если запланированный исходящий
					if(($old_doc['incoming_or_outcoming']==1)&&($check>time())){
						$sm1->assign('show_description', true);
					}else $sm1->assign('show_description', false);
				}
				
				//контакт, контрагент звонка
				
				$_addr=new SchedContactItem;
				$addr=$_addr->GetItemByFields(array('sched_id'=>$old_doc['id']));
				
				//var_dump($addr);
				if($addr!==false){
				 
					
					$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
					
					$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
					$sci=$_sci->getitembyid($addr['contact_id']);
					 
					
					
					$old_doc['supplier_id']=$addr['supplier_id'];
					$old_doc['contact_id']=$addr['contact_id'];
					$old_doc['ccontact_value']=$addr['value'];
					$old_doc['supplier_string']=$opf['name'].' '.$si['full_name'];
					$old_doc['contact_string']=$sci['name'].', '.$sci['position'];
					$old_doc['contact_value_string']=$addr['value'];
				}
				
				
			}
			
			$sm1->assign('old_doc', $old_doc);
			
			
		}else{
			//не копируем - форма создания
			$sm1->assign('incoming_or_outcoming', 1);
			
			$sm1->assign('show_description', false); 
			
			
			$sm1->assign('meet_id', 4);
			//$sm1->assign('pdate_beg', 

		 
			 
			if(strpos($datetime, 'T')!==false){
				$time=substr($datetime,  strpos( $datetime, 'T'), 6  );
				
				$sm1->assign('time',  $time); 
				$sm1->assign('ptime_beg_hr',substr($time,  1,2 ));
				$sm1->assign('ptime_beg_mr',substr($time,  4,2 )); 
				
				$check=DateFromdmy(DateFromYmd($datetime))  +substr($time,  1,2 )*60*60+substr($time,  4,2 )*60;
				
				
				if($check>time()) $sm1->assign('plan_or_fact' ,0); 
				else  $sm1->assign('plan_or_fact' ,1); 
				
				//$sm1->assign('plan_or_fact' ,1);
					
			}else{
				$sm1->assign('time', date('H:i'));
				
				$sm1->assign('ptime_beg_hr',date('H'));
				$sm1->assign('ptime_beg_mr',date('i')); 
				
				
				$sm1->assign('ptime_end_hr',date('H'));
				$sm1->assign('ptime_end_mr',date('i')); 
				
				$sm1->assign('plan_or_fact' ,1);
			}
			
			$sm1->assign('pdate_end',  DateFromYmd($datetime)); 
			 
		}
		 

		 
		//подставить время напоминания -15 мин
		$remind_time=time()-15*60;
		$sm1->assign('remind_beg_hr',date('H',$remind_time) );
		$sm1->assign('remind_beg_mr', date('i',$remind_time)); 
	
		 
		 
		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		
		
		$sm1->assign('now',  DateFromYmd($datetime)); 
		 
		//города
		/*$_csg=new SupplierCitiesGroup;
		$csg=$_csg->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('cities', $csg);
		*/
		
		if(isset($_GET['supplier_id'])&&($_GET['supplier_id']!=0)){
			//если задан КОНТРАГЕНТ:
			//подставить в карту город контрагента
			//вызвать диалог выбора контрагента, после чего в нем для:
			//случаев 2 -3- развернуть все контакты выбранного контрагента
			//случая 4 - развернуть все контакты с телефонами выбранного контрагента
			
			$supplier_id=abs((int)$_GET['supplier_id']);
			$sm1->assign('supplier_id', $supplier_id);
			
			$_si=new SupplierItem;
			$supplier=$_si->getitembyid($supplier_id);
			$sm1->assign('supplier', $supplier);
			
			//города
			//получить справочник городов контрагента, выбрать ПЕРВЫЙ город
			$_cg=new SupplierCitiesGroup;
			$cg=$_cg->GetItemsByIdArr($supplier_id); $cg1=array();
			if(count($cg)>0){
				 $cg1[0]=$cg[0];
				 $cg1[0]['id']=$cg1[0]['city_id'];
			}
			
			$sm1->assign('cities', $cg1);
			
			
		 
		}
			
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm1->assign('cous', $cous); 
		
		
		//места встречи
		$_meets=new Sched_MeetGroup;
		$meets=$_meets->GetItemsArr();
		$meet_ids=array(); $meet_names=array();
		foreach($meets as $k=>$v){ $meet_ids[]=$v['id']; $meet_names[]=$v['name']; }
		$sm1->assign('meet_ids', $meet_ids); $sm1->assign('meet_names', $meet_names); 
		//var_dump($meets); 
		 
		$sm1->assign('can_modify_result', true);
 
		 
	 	switch($kind_id){
			case 2:	
				$user_form=$sm1->fetch('plan/create_kind_2.html');
			break;
			
			case 3:	
				$user_form=$sm1->fetch('plan/create_kind_3.html');
			break;
			
			case 4:	
				$user_form=$sm1->fetch('plan/create_kind_4.html');
			break;
			
			case 5:	
				$user_form=$sm1->fetch('plan/create_kind_5.html');
			break;
		
		}
	
	
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		//$sm1->assign('manager_id', $result['id']);
		
		$sm1->assign('manager_id', $editing_user['manager_id']);
		$_uis=new UserSItem; $uis=$_uis->getitembyid($editing_user['manager_id']);
		$sm1->assign('manager_string', $uis['name_s']);
		
		//список сотрудников
		$_plans=new Sched_Group;
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, $editing_user['kind_id']);
		
		$_usg=new Sched_UsersSGroup; $dec_us=new DBDecorator; // $dec_us->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		$limited_user=NULL;
		if($au->FltUser($result)){
			//echo 'z';
			$_u_to_u=new UserToUser();
			$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
			$limited_user=$u_to_u['sector_ids'];
			$dec_us->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
		}
		
		$managers=$_usg->GetItemsForBill($dec_us);
		$sm1->assign('managers', $managers);
		
		
		
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		$editing_user['pdate_beg']=datefromYmd($editing_user['pdate_beg']);
		
		$editing_user['pdate_end']=datefromYmd($editing_user['pdate_end']);
		
		
		//подтянуть: напоминание, адресатов
		$_rem=new SchedRemindItem;
		$rem=$_rem->GetItemByFields(array('sched_id'=>$id, 'user_id'=>$result['id'], 'is_viewed'=>0));
		
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
		
		
		//возможность РЕДАКТИРОВАНИЯ - 
			//пол-ль - создал
	//или пол-ль - в списке видящих
	 	
		//$can_modify=true;
		
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		if($can_modify) $can_modify= (
			($editing_user['kind_id']!=5)||
			(($editing_user['kind_id']==5)&&( in_array($editing_user['manager_id'], $available_users)))||
			(($editing_user['kind_id']==5)&&($editing_user['manager_id']!=$result['id'])&&in_array($result['id'], $_dem->GetUserIdsArr($id,2)))
		);
		
		//var_dump(in_array($result['id'], $_dem->GetUserIdsArr($id,2)));
		
	    $from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		
		//возможность редактировать результаты и напоминание
		if(($editing_user['kind_id']==4)||($editing_user['kind_id']==2)||($editing_user['kind_id']==3) ){
			$sm1->assign('ptime_beg_hr',substr($editing_user['ptime_beg'],  0,2 ));
			$sm1->assign('ptime_beg_mr',substr($editing_user['ptime_beg'],  3,2 )); 
			
			$sm1->assign('ptime_end_hr',substr($editing_user['ptime_end'],  0,2 ));
			$sm1->assign('ptime_end_mr',substr($editing_user['ptime_end'],  3,2 )); 
			
			
			$can_modify_rep=in_array($editing_user['status_id'],array(18,2,22));
				
			$sm1->assign('can_modify_rep', $can_modify_rep);  
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
		
		if(($editing_user['kind_id']==5) ){
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
			
			
		}
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',905);
		if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',905);
			if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		
		
		
		
			
		$_cous=new SupplierCountryGroup;
		$cous=$_cous->GetItemsArr();
		$sm1->assign('cous', $cous); 
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			 
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
		
		
		//блок утв. выполнения
		if(($editing_user['is_confirmed_done']==1)&&($editing_user['user_confirm_done_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_done_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['confirm_done_pdate']);
			
			$sm1->assign('is_confirmed_done_confirmer',$confirmer);
		}
		
		
		//доступно - если $manager_id==result['id'] или есть спецправа
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed']==1){
			
			//ком, встречи
			if(in_array($editing_user['kind_id'], array(2,3))){
				//снимаем утв
				if($editing_user['is_confirmed_done']==1){
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',923))||($editing_user['manager_id']==$result['id']));
				}else{
				//ставим утв	
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',915))||($editing_user['manager_id']==$result['id']));
				}
			//звонок	 
			}else{  
				
				//снимаем утв
				if($editing_user['is_confirmed_done']==1){
					//три подвида звонка, исходя из них - три разных объекта
					
					if(($editing_user['incoming_or_outcoming']==1)&&($editing_user['plan_or_fact']==0)) $our_object_id=925;
					elseif(($editing_user['incoming_or_outcoming']==1)&&($editing_user['plan_or_fact']==1)) $our_object_id=926;
					elseif(($editing_user['incoming_or_outcoming']==0)&&($editing_user['plan_or_fact']==1)) $our_object_id=927;
					else $our_object_id=927;
					
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',$our_object_id))||($editing_user['manager_id']==$result['id']));
				}else{
				//ставим утв.	
					$can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
				}
			}
			
		 /* if($editing_user['is_confirmed_done']==1){
			  if($au->user_rights->CheckAccess('w',905)){
				  //есть права + сам утвердил
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
		  }*/
		  
		}
		// + есть галочка утв. цен
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed']==1);
		
		
	
		$sm1->assign('can_confirm_done',$can_confirm_shipping);
		
		
		
				
		
		//блок утв. принятия
		if(($editing_user['is_fulfiled']==1)&&($editing_user['user_fulfiled_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_fulfiled_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfiled_pdate']);
			
			$sm1->assign('is_fulfiled_confirmer',$confirmer);
		}
		
		$can_confirm_shipping=false;
		if($editing_user['is_confirmed_done']==1){
		
		  /*if($editing_user['is_fulfiled']==1){
			  if($au->user_rights->CheckAccess('w',905)){
				  //есть права + сам утвердил
				  $can_confirm_shipping=true;	
			  }else{
				  $can_confirm_shipping=false;
			  }
		  }else{
			  //95
			  $can_confirm_shipping=$au->user_rights->CheckAccess('w',905);
		  }*/
		  
		   
			if($editing_user['is_fulfiled']==1){
			   $can_confirm_shipping= $au->user_rights->CheckAccess('w',924);//$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',924))||($editing_user['manager_id']==$result['id']));
		   }else{
				$can_confirm_shipping=$au->user_rights->CheckAccess('w',916); //$au->user_rights->CheckAccess('w',905)&&(($au->user_rights->CheckAccess('w',916))||($editing_user['manager_id']==$result['id']));
		   } 
		   
		  
		}
		// + есть галочка утв. выполнения
		$can_confirm_shipping=$can_confirm_shipping&&($editing_user['is_confirmed_done']==1);
		
		
		
		$sm1->assign('can_confirm_fulfil',$can_confirm_shipping);
		
		
		

		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		
		
		 
		
		
		
		
		
		
		$sm1->assign('can_modify', $can_modify);
		
		//возможность указать результат!
		$sm1->assign('can_modify_result', ($editing_user['is_confirmed_done']==0)&&(!in_array($editing_user['status_id'], array(3))));
		
		
		
		//для заметки - возможность править контрагентов
		$sm1->assign('can_modify_suppliers', $can_modify&&($editing_user['wo_supplier']==0));
		  
		 $sm1->assign('can_create', $au->user_rights->CheckAccess('w',904));  
		 
		
		$sm1->assign('can_share', ($editing_user['manager_id']==$result['id'])||$au->user_rights->CheckAccess('w',928));
		
		
		$sm1->assign('statuses', $_dem->GetStatuses($editing_user['status_id']));
		
		
		//группа пол-лей, с кем делимся:
		$sm1->assign('shares', $_dem->GetUsersArr($id, $editing_user));
		
		//прикрепленные файлы
		if($editing_user['kind_id']==5){
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
			  
			  $filetext=$ffg->ShowFiles('doc_file/incard_list.html', $decorator,$from,$to_page,'ed_sched.php', 'sched_file.html', 'swfupl-js/sched_files.php',  
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
				
			/*public function ShowFiles($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$pagename='files.php', $loadname='load.html', $uploader_name='/swfupl-js/files.php', 
	$can_load=false, 
	$can_delete=false, 
	$can_edit=false, 
	$folder_id=0, 
	$can_create_folder=false, 
	$can_edit_folder=false, 
	$can_delete_folder=false, 
	$can_move_folder=false, 
	$id_prefix='',
	$can_edit_own=false,
	$result=NULL ,
	
	$navi_decorator=NULL, $elem_id_prefix=''
	){*/		
				
			$sm1->assign('files', $filetext);
		}
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		switch($editing_user['kind_id']){
			case 2:	
				$user_form=$sm1->fetch('plan/edit_kind_2'.$print_add.'.html');
			break;
			
			case 3:	
				$user_form=$sm1->fetch('plan/edit_kind_3'.$print_add.'.html');
			break;
			case 4:	
				$user_form=$sm1->fetch('plan/edit_kind_4'.$print_add.'.html');
			break;
			case 5:	
				$user_form=$sm1->fetch('plan/edit_kind_5'.$print_add.'.html');
			break;
		
		}
		
		
	 
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',905)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(903,
904,
905,
906,
907,
908,
915,
916,
918,
922,
923,
924,
925,
926,
927,
928,
946,
971
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_sched.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']); //.' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('plan/ed_plan_page'.$print_add.'.html');
	
	
	
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