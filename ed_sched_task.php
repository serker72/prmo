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
require_once('classes/docstatusitem.php');

require_once('classes/sched_history_item.php');
require_once('classes/sched_history_group.php');

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
$available_users=$_plan->GetAvailableUserIds($result['id'], false, 1);


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
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	//видим:
	//пол-ль - создал
	//или пол-ль - в списке видящих
	/*$is_shown= in_array($editing_user['manager_id'], $available_users)||
	
 
	(($editing_user['kind_id']==5)&&in_array($result['id'], $_dem->GetUserIdsArr($id)));
	
	if(!$is_shown){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}*/
	
	
 
}

 
 


//обработка данных

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	 
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['created_id']=$result['id'];
	
	$params['kind_id']=abs((int)$_POST['kind_id']);
	
	$params['task_id']=abs((int)$_POST['task_id']);
	
	
	 
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	
	 
	
	$params['manager_id']=$result['id'];
	
	$params['description']= SecStr($_POST['description']);
	$params['topic']= SecStr($_POST['topic']);
	if(isset($_POST['has_exp_pdate'])){
		$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['exp_pdate']));
		$params['ptime_beg']= SecStr($_POST['exp_ptime_h']).':'.SecStr($_POST['exp_ptime_m']).':00';
	}
	
	$params['contact_mode']=0;
	
	$params['priority']=abs((int)$_POST['priority']);
	
	
	
	if(isset($_POST['do_check'])) $params['do_check']=1; else $params['do_check']=0;
		
	if(isset($_POST['wo_supplier'])) $params['wo_supplier']=1;
		else $params['wo_supplier']=0;
	
	
	
	
	
	$params['status_id']=18;
			
	 
	$_res=new Sched_Resolver($params['kind_id']);
		
		
	$code=	$_res->instance->Add($params);
	 
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				
				$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	  
	
	
	
	
	 
	//добавим отнесенных пользователей
	//ответственный
	$_tu=new Sched_TaskUserItem;
	$test_tu=$_tu->GetItemByFields(array('sched_id'=>$code, 'kind_id'=>2));
	if($test_tu===false){
		$_tu->Add(array('sched_id'=>$code, 'user_id'=>abs((int)$_POST['user_2']), 'kind_id'=>2, 'was_informed'=>0));
	}else{
		$_tu->Edit($test_tu['id'], array('sched_id'=>$code, 'user_id'=>abs((int)$_POST['user_2']), 'kind_id'=>2, 'was_informed'=>0));
	}
	$_ui=new UserSItem; $user=$_ui->getitembyid(abs((int)$_POST['user_2']));
	$description=SecStr($user['name_s'].' '.$user['login']);
	$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'назначен ответственный сотрудник: '.$description,$code);	
	
	//постановщик
	$test_tu=$_tu->GetItemByFields(array('sched_id'=>$code, 'kind_id'=>1));
	if($test_tu===false){
		$_tu->Add(array('sched_id'=>$code, 'user_id'=>abs((int)$_POST['user_1']), 'kind_id'=>1));
	}else{
		$_tu->Edit($test_tu['id'], array('sched_id'=>$code, 'user_id'=>abs((int)$_POST['user_1']), 'kind_id'=>1));
	}
	$_ui=new UserSItem; $user=$_ui->getitembyid(abs((int)$_POST['user_1']));
	$description=SecStr($user['name_s'].' '.$user['login']);
	$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'назначен постановщик: '.$description,$code);	
	
	//соисполнители 
	 
	$_user=new UserSItem;
	$positions=array();
	
	foreach($_POST as $k=>$v){
	  if(eregi("^new_user3_hash_([0-9a-z]+)",$k)){
		  
		  $hash=eregi_replace("^new_user3_hash_","",$k);
		  
		  $user_id=abs((int)$_POST['new_user3_id_'.$hash]);
		  $kind_id=3;
		  
		  
		 
		  $positions[]=array(
			  'sched_id'=>$code,
			   
			  'user_id'=>$user_id,
			  
			  'kind_id'=>$kind_id
		  );
		  
	  }
	}
			
	
	 
	/*echo '<pre>';
	print_r($_POST);
	print_r($positions);
	echo '</pre>';
	die(); */
	//внесем позиции
	$log_entries=$_res->instance->AddKindUsers($code, 3, $positions); 
	//die();
	//запишем в журнал
	 foreach($log_entries as $k=>$v){
		  $user=$_user->GetItemById($v['user_id']);
		  
		 
		  $description=SecStr($user['name_s'].' '.$user['login']).',  ';
		   
		  
		  
		  if($v['action']==0){
			  $log->PutEntry($result['id'],'добавил соисполнителя в задачу',NULL,904,NULL,$description,$code);	
		  }elseif($v['action']==1){
			  $log->PutEntry($result['id'],'редактировал соисполнителя в задаче',NULL,904,NULL,$description,$code);
		  }elseif($v['action']==2){
			  $log->PutEntry($result['id'],'удалил соисполнителя из задачи',NULL,904,NULL,$description,$code);
		  }
		  
	  }
	  
	  
	  
	  
	  //наблюдатели 
	 
	$_user=new UserSItem;
	$positions=array();
	
	foreach($_POST as $k=>$v){
	  if(eregi("^new_user4_hash_([0-9a-z]+)",$k)){
		  
		  $hash=eregi_replace("^new_user4_hash_","",$k);
		  
		  $user_id=abs((int)$_POST['new_user4_id_'.$hash]);
		  $kind_id=4;
		  
		  
		 
		  $positions[]=array(
			  'sched_id'=>$code,
			   
			  'user_id'=>$user_id,
			  
			  'kind_id'=>$kind_id
		  );
		  
	  }
	}
	
	//добавить наблюдателем в задачу: 
	// текущего пользователя, если он НЕ админ, и не постановщик задачи.
	//и его не было в наблюдателях ранее
	$_roles=new Sched_FieldRules($result); 
	if(!in_array($result['id'], Sched_FieldRules::$_viewed_ids)){
		//не админ
		if(abs((int)$_POST['user_1'])!=$result['id']){
			//не постановщик
			if(!in_array(array('sched_id'=>$code,
			   
			  'user_id'=>$result['id'],
			  
			  'kind_id'=>4), $positions)){
					//не наблюдатель - добавить к наблюдателям
					  $positions[]=array('sched_id'=>$code,
			   
						  'user_id'=>$result['id'],
						  
						  'kind_id'=>4);
			  }
		}
	}
			
	
	 
	/*echo '<pre>';
	print_r($_POST);
	print_r($positions);
	echo '</pre>';
	die(); */
	//внесем позиции
	$log_entries=$_res->instance->AddKindUsers($code, 4, $positions); 
	//die();
	//запишем в журнал
	 foreach($log_entries as $k=>$v){
		  $user=$_user->GetItemById($v['user_id']);
		  
		 
		  $description=SecStr($user['name_s'].' '.$user['login']).',  ';
		   
		  
		  
		  if($v['action']==0){
			  $log->PutEntry($result['id'],'добавил наблюдателя в задачу',NULL,904,NULL,$description,$code);	
		  }elseif($v['action']==1){
			  $log->PutEntry($result['id'],'редактировал наблюдателя в задаче',NULL,904,NULL,$description,$code);
		  }elseif($v['action']==2){
			  $log->PutEntry($result['id'],'удалил наблюдателя из задачи',NULL,904,NULL,$description,$code);
		  }
		  
	  }
	 
	 
	 //создадим напоминания!
	$_rem=new SchedRemindItem;
	if(isset($_POST['remind_do'])){
		
		 //рассылаем всем связанным пользователям
		 $sql='select distinct user_id from sched_task_users where sched_id="'.$code.'"';
		 $set=new mysqlset($sql);
		 $rs=$set->GetResult();
		 $rc=$set->GetResultNumRows();
		 
		 for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
				
			$r_params=array();
			$r_params['user_id']=$f['user_id'];
			$r_params['sched_id']=$code;
			$r_params['action_time']=Datefromdmy($_POST['remind_pdate'])+60*60*(int)($_POST['remind_ptime_h']) + 60*(int)($_POST['remind_ptime_m']);
			$r_params['is_viewed']=0;
			
			
			$_rem->Add($r_params);
			
			$log->PutEntry($result['id'],'создал задачу планировщика',NULL,904, NULL, 'установлено напоминание на '.date('d.m.Y H:i:s', $r_params['action_time']),$code);		

		 }
			
	}
	 
	 
	 //в отдельный блок - утверждение заполнения задачи, чтобы сработал его перехват и отправились сообщения
	 
	 $new_params=array();
	 $new_params['is_confirmed']=1;
	$new_params['user_confirm_id']=$result['id'];
	$new_params['confirm_pdate']=time();
	//$params['status_id']=23;
	
	$_res->instance->Edit($code, $new_params, true, $result);
	$log->PutEntry($result['id'],'автоматическое утверждение при создании задачи планировщика',NULL,904, NULL, '',$code);		 
	 
	// die();
	 
	 
	//приложим файлы!
	//upload_file_6A83_tmp" value="_ZpaGsu91PI.jpg" 
	$fmi=new SchedFileItem;
	foreach($_POST as $k=>$v){
	  if(eregi("^upload_file_",$k)){
		    $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
		  $fmi->Add(array('bill_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_name'=>SecStr($v), 'user_id'=>$result['id'], 'pdate'=>time()));
		  
		   $log->PutEntry($result['id'], 'прикрепил файл к задаче', NULL, 904, NULL,'Служебное имя файла: '.SecStr(basename($filename)).' Имя файла: '.SecStr($v),$code);
		   
		   
	  }
	}
	 
	 
		
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
			  $log->PutEntry($result['id'],'добавил контрагента к задаче',NULL,904,NULL,$description,$code);	
		  }elseif($v['action']==1){
			  $log->PutEntry($result['id'],'редактировал контрагента в задаче',NULL,904,NULL,$description,$code);
		  }elseif($v['action']==2){
			  $log->PutEntry($result['id'],'удалил контрагента из задачи',NULL,904,NULL,$description,$code);
		  }
		  
	  }
	
	
 
	 
	
	//перенаправления
	if(isset($_POST['doNew'])){
		if($params['task_id']>0) header("Location: ed_sched_task.php?action=1&id=".$params['task_id'].'&from_begin='.$from_begin);
		
		else header("Location: shedule.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		 
		header("Location: ed_sched_task.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: shedule.php");
		die();
	}
	 
	
	die();
	


}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doRemake'])||isset($_POST['doDo'])||isset($_POST['doDefer'])||isset($_POST['doEditStay'])||isset($_POST['doStop'])||isset($_POST['doMoveSrok']))){
	 


	 
	
	//редактирование возможно, если позволяет статус
	$_res=new Sched_Resolver($editing_user['kind_id']);
		
		
	
	$params=array();

	
	//поля формируем в зависимости от их активности в текущем статусе
	$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
	$field_rights0=$_roles->GetFieldsRoles($editing_user, $result['id']);	
	$field_rights1=$_roles->GetFieldsRoles($editing_user, $result['id'], $_POST['current_status_id']);
	$field_rights=array(); 
	foreach($field_rights0 as $k=>$v) $field_rights[$k]=$v&&$field_rights1[$k];
	
	
	if($field_rights['can_modify_suppliers']) if(isset($_POST['wo_supplier'])) $params['wo_supplier']=1; else $params['wo_supplier']=0;
	
	
	if($field_rights['description']) $params['description']= SecStr($_POST['description']);
	if($field_rights['topic']) $params['topic']= SecStr($_POST['topic']);
	
	if($field_rights['can_exp_date']){
		
		if(isset($_POST['has_exp_pdate'])){
			$params['pdate_beg']= date('Y-m-d', DateFromdmY($_POST['exp_pdate']));
			$params['ptime_beg']= SecStr($_POST['exp_ptime_h']).':'.SecStr($_POST['exp_ptime_m']).':00';
		}else{
			$params['pdate_beg']= NULL;
			$params['ptime_beg']= NULL;
		}
	} 
	
	
	if($field_rights['priority']) $params['priority']=abs((int)$_POST['priority']);
	
	if($field_rights['report']) $params['report']= SecStr($_POST['report']);
	
	if($field_rights['can_do_check']){
		
		if(isset($_POST['do_check'])) $params['do_check']=1; else $params['do_check']=0;
	}
	
	$_res->instance->Edit($id, $params);
	
		
	//$_dem->Edit($id, $params);
	//die();
	//запись в журнале
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			$log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			
					
		}	
		
	}
	
	
	
	//добавим отнесенных пользователей
	//ответственный
	if($field_rights['can_modify_2']){
		$_tu=new Sched_TaskUserItem;
		$test_tu=$_tu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>2));
		if($test_tu===false){
			$_tu->Add(array('sched_id'=>$id, 'user_id'=>abs((int)$_POST['user_2']), 'kind_id'=>2));
		}else{
			
			
			if($test_tu['user_id']!=abs((int)$_POST['user_2'])) $was_informed=0;
			else $was_informed=1;
			
			$_tu->Edit($test_tu['id'], array('sched_id'=>$id, 'user_id'=>abs((int)$_POST['user_2']), 'kind_id'=>2, 'was_informed'=>$was_informed));
		}
		$_ui=new UserSItem; $user=$_ui->getitembyid(abs((int)$_POST['user_2']));
		$description=SecStr($user['name_s'].' '.$user['login']);
		
		
		
		if($test_tu['user_id']!=$_POST['user_2']) $log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'назначен ответственный сотрудник: '.$description,$id);	
	}
	
	if($field_rights['can_delegate']){
		
		if(isset($_POST['delegate'])){
			//die();
			
			$_tu=new Sched_TaskUserItem;
			$test_tu=$_tu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>2));
			if($test_tu===false){
				$_tu->Add(array('sched_id'=>$id, 'user_id'=>abs((int)$_POST['delegate']), 'kind_id'=>2));
			}else{
				$_tu->Edit($test_tu['id'], array('sched_id'=>$id, 'user_id'=>abs((int)$_POST['delegate']), 'kind_id'=>2));
			}
			$_ui=new UserSItem; $user=$_ui->getitembyid(abs((int)$_POST['delegate']));
			$description=SecStr($user['name_s'].' '.$user['login']);
			$log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'задача делегирована сотруднику: '.$description,$id);
			
			
			//добавить старого отвественного в наблюдатели
			if($test_tu!==false){
				$has_obs=$_tu->GetItemByFields(array('sched_id'=>$id, 'user_id'=>$test_tu['user_id'], 'kind_id'=>4));
				if($has_obs===false) $_tu->Add( array( 'kind_id'=>4, 'sched_id'=>$id, 'user_id'=>$test_tu['user_id']));
				
				$_ui=new UserSItem; $user=$_ui->getitembyid($test_tu['user_id']);
				$description1=SecStr($user['name_s'].' '.$user['login']);
				$log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'при делегировании задачи сотруднику: '.$description.' сотрудник '.$description1.' автоматически добавлен в наблюдатели',$id);	
			}
	
		}
	}
	
	//постановщик
	if($field_rights['topic']){
		$test_tu=$_tu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>1));
		if($test_tu===false){
			$_tu->Add(array('sched_id'=>$id, 'user_id'=>abs((int)$_POST['user_1']), 'kind_id'=>1));
		}else{
			$_tu->Edit($test_tu['id'], array('sched_id'=>$id, 'user_id'=>abs((int)$_POST['user_1']), 'kind_id'=>1));
		}
		$_ui=new UserSItem; $user=$_ui->getitembyid(abs((int)$_POST['user_1']));
		$description=SecStr($user['name_s'].' '.$user['login']);
		if($test_tu['user_id']!=$_POST['user_1']) $log->PutEntry($result['id'],'редактировал задачу планировщика',NULL,905, NULL, 'назначен постановщик: '.$description,$id);	
	}
	
	//соисполнители 
	if($field_rights['can_modify_3']){ 
		$_user=new UserSItem;
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^new_user3_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_user3_hash_","",$k);
			  
			  $user_id=abs((int)$_POST['new_user3_id_'.$hash]);
			  $kind_id=3;
			  
			  
			 
			  $positions[]=array(
				  'sched_id'=>$id,
				   
				  'user_id'=>$user_id,
				  
				  'kind_id'=>$kind_id
			  );
			  
		  }
		}
			
		
		 
		/*echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die(); */
		//внесем позиции
		$log_entries=$_res->instance->AddKindUsers($id, 3, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			  $user=$_user->GetItemById($v['user_id']);
			  
			 
			  $description=SecStr($user['name_s'].' '.$user['login']).',  ';
			   
			  
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил соисполнителя в задачу',NULL,905,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал соисполнителя в задаче',NULL,905,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил соисполнителя из задачи',NULL,905,NULL,$description,$id);
			  }
			  
		  }
	
	}
		
	//наблюдатели 
	if($field_rights['can_modify_4']){  
		$_user=new UserSItem;
		$positions=array();
		
		foreach($_POST as $k=>$v){
		  if(eregi("^new_user4_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_user4_hash_","",$k);
			  
			  $user_id=abs((int)$_POST['new_user4_id_'.$hash]);
			  $kind_id=4;
			  
			  
			 
			  $positions[]=array(
				  'sched_id'=>$id,
				   
				  'user_id'=>$user_id,
				  
				  'kind_id'=>$kind_id
			  );
			  
		  }
		}
			
		
		 
		/*echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die(); */
		//внесем позиции
		$log_entries=$_res->instance->AddKindUsers($id, 4, $positions); 
		//die();
		//запишем в журнал
		 foreach($log_entries as $k=>$v){
			  $user=$_user->GetItemById($v['user_id']);
			  
			 
			  $description=SecStr($user['name_s'].' '.$user['login']).',  ';
			   
			  
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил наблюдателя в задачу',NULL,905,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал наблюдателя в задаче',NULL,905,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил наблюдателя из задачи',NULL,905,NULL,$description,$id);
			  }
			  
		  }
	
	}
	
		//контрагенты
	if($field_rights['can_modify_suppliers']){  
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
				  $log->PutEntry($result['id'],'добавил контрагента к задаче',NULL,904,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал контрагента в задаче',NULL,904,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил контрагента из задачи',NULL,904,NULL,$description,$id);
			  }
			  
		  }
	  
	}
	

	
	
	//установка напоминаний
	if(
		  in_array($editing_user['status_id'],array(18,23,24,25,26))	 
		)
	
	{
		//создадим напоминания!
		$_rem=new SchedRemindItem;
		$_test_rem=$_rem->GetItemByFields(array('sched_id'=>$id, 'user_id'=>$result['id']));
		if(isset($_POST['remind_do'])){
			
			
			$r_params=array();
			
			
			 	$r_params['action_time']= Datefromdmy($_POST['remind_pdate'])+(int)($_POST['remind_ptime_h'])*60*60+ +(int)($_POST['remind_ptime_m'])*60;
		
			 
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
	
	
	$_dsi=new DocStatusItem; 
	//обработка выделенных кнопок
	if(isset($_POST['doRemake'])){
		
		if($field_rights['can_remake']){
			
			$_res->instance->Edit($id,array('is_confirmed_done'=>0, 'status_id'=>24));
			
					  
			$log->PutEntry($result['id'],'отправил задачу на доработку',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById(24);
			$log->PutEntry($result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$id);
			
			//создадим запись в ленту
			if(strlen($_POST['status_change_comment'])>0){
				$_len=new Sched_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$_POST['status_change_comment'].'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			}
					
		}		
	}
	
	if(isset($_POST['doDo'])){
		
		if($field_rights['can_begin']){
			
			$_res->instance->Edit($id,array('status_id'=>24));
					  
			$log->PutEntry($result['id'],'начал выполнение задачи',NULL,905, NULL, NULL,$id);
			
			$stat=$_dsi->GetItemById(24);
			$log->PutEntry($result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$id);		
		}		
	}
	
	
	if(isset($_POST['doDefer'])){
		
		if($field_rights['can_defer']){
			
			$_res->instance->Edit($id,array('status_id'=>25));
					  
			$log->PutEntry($result['id'],'отложил выполнение задачи',NULL,905, NULL, NULL, $id);
			
			$stat=$_dsi->GetItemById(25);
			$log->PutEntry($result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$id);		
		}		
	}
	 
	if(isset($_POST['doStop'])){
		
		if($field_rights['can_stop']){
			
			$_res->instance->Edit($id,array('status_id'=>23));
					  
			$log->PutEntry($result['id'],'приостановил выполнение задачи',NULL,905, NULL, NULL, $id);
			
			$stat=$_dsi->GetItemById(25);
			$log->PutEntry($result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$id);		
			
			//создадим запись в ленту
			if(strlen($_POST['status_change_comment'])>0){
				$_len=new Sched_HistoryItem;
				$len_params=array();
				$len_params['sched_id']=$id;
				$len_params['txt']=SecStr('<div>'.$_POST['status_change_comment'].'</div>');
				$len_params['user_id']=$result['id'];
				$len_params['pdate']=time();
				
				 $_len->Add($len_params);
			}
		}		
	}
	 
	 
		  
	 if(isset($_POST['doMoveSrok'])){
		
		if($field_rights['can_move_srok']){
			
			 
			
			$_res->instance->Edit($id,array('is_waiting_new_pdate'=>1, 'new_pdate_beg'=>date('Y-m-d', DateFromdmY($_POST['new_pdate_beg'])), 'new_ptime_beg'=>SecStr($_POST['new_ptime_beg_h']).':'.SecStr($_POST['new_ptime_beg_m']).':00'));
			
			$log->PutEntry($result['id'],'отправил заявку на смену крайнего срока задачи',NULL,905, NULL, SecStr('старый срок: '.DatefromYMD($editing_user['pdate_beg']).' '.$editing_user['ptime_beg'].', новый срок: '.$_POST['new_pdate_beg'].' '.SecStr($_POST['new_ptime_beg_h']).':'.SecStr($_POST['new_ptime_beg_m']).':00'),  $id);
		}		
	}
	 

	 
		//утверждение заполнения

		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		if($editing_user['is_confirmed_done']==0){
		  
		  
		  	
		  if($editing_user['is_confirmed']==1){
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			  if(($au->user_rights->CheckAccess('w',905))&&($field_rights['can_unconfirm'])){
				  if((!isset($_POST['is_confirmed']))&&($_res->instance->DocCanUnconfirmPrice($id,$rss32))){
					  
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,905, NULL, NULL,$id);	
					  
				  }
			  } 
			  
		  }else{
			  //есть права
			  if($au->user_rights->CheckAccess('w',905)&&($field_rights['can_confirm'])){
				  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&($_res->instance->DocCanConfirmPrice($id,$rss32))){
					  
					  $_res->instance->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил заполнение',NULL,905, NULL, NULL,$id);	
					  
					   
					  //die();
				  }
			  } 
		  }
		}
		
		
		//утверждение отгрузки
		if($editing_user['is_confirmed']==1){
		  if($editing_user['is_confirmed_done']==1){
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 
			  if(($au->user_rights->CheckAccess('w',905))&&($field_rights['can_unconfirm_done'])){
				  if((!isset($_POST['is_confirmed_done'])) &&($_res->instance->DocCanUnconfirmShip($id, $rss32))){
					  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_confirmed_done'=>0, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение выполнения',NULL,905, NULL, NULL,$id);	
				  }
			  }
			  
		  }else{
			   
			  //есть права
			  if($au->user_rights->CheckAccess('w',905)&&($field_rights['can_confirm_done'])){
				  if(isset($_POST['is_confirmed_done'])&&($_res->instance->DocCanConfirmShip($id, $rss32))){
					 
					 $_res->instance->Edit($id,array('is_confirmed_done'=>1, 'user_confirm_done_id'=>$result['id'], 'confirm_done_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил выполнение',NULL,905, NULL, NULL,$id);	
						  
				  }
			  } 
		  }
		}
		
		
		
		//утверждение приема работы
		if($editing_user['is_confirmed_done']==1){
		  if($editing_user['is_fulfiled']==1){
			  //есть права: либо сам утв.+есть права, либо есть искл. права:
			 
			 
			  if(($au->user_rights->CheckAccess('w',905))&&($field_rights['can_unconfirm_fulfil'])){
				  if((!isset($_POST['is_fulfiled'])) &&($_res->instance->DocCanUnconfirmFulfil($id, $rss32))){
					  
					  //echo 'zzzzzzzzzzzz';
					  $_res->instance->Edit($id,array('is_fulfiled'=>0, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'снял утверждение принятия работы',NULL,905, NULL, NULL,$id);	
				  }
			  }
			  
		  }else{
			   
			  //есть права
			  if($au->user_rights->CheckAccess('w',905)&&($field_rights['can_confirm_fulfil'])){
				  if(isset($_POST['is_fulfiled'])&&($_res->instance->DocCanConfirmFulfil($id, $rss32))){
					 
					 $_res->instance->Edit($id,array('is_fulfiled'=>1, 'user_fulfiled_id'=>$result['id'], 'fulfiled_pdate'=>time()),true,$result);
					  
					  $log->PutEntry($result['id'],'утвердил принятие работы',NULL,905, NULL, NULL,$id);	
						  
				  }
			  } 
		  }
		}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		//header("Location: shedule.php#user_".$id);
		
		if($editing_user['task_id']>0) header("Location: ed_sched_task.php?action=1&id=".$editing_user['task_id'].'&from_begin='.$from_begin);
		
		else header("Location: shedule.php#user_".$code);
		die();
	}elseif(isset($_POST['doEditStay'])||isset($_POST['doRemake'])||isset($_POST['doDefer'])||isset($_POST['doDo'])||isset($_POST['doStop'])||isset($_POST['doMoveSrok'])){
	
	 
		header("Location: ed_sched_task.php?action=1&id=".$id.'&from_begin='.$from_begin);
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
		 
		$from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('exp_ptime_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('exp_ptime_m',$from_ms);
		
		
		
		//копируем данные
		if(isset($_GET['copyfrom'])){
			$old_doc=$_dem->getitembyid(abs((int)$_GET['copyfrom']));
			$old_doc['has_exp_pdate']=($old_doc['pdate_beg']!='');
			if($old_doc['pdate_beg']!='')  $old_doc['pdate_beg']=datefromYmd($old_doc['pdate_beg']);
			
			if($old_doc['has_exp_pdate']){
				$sm1->assign('ptime_beg_hr',substr($old_doc['ptime_beg'],  0,2 ));
				$sm1->assign('ptime_beg_mr',substr($old_doc['ptime_beg'],  3,2 )); 
			}
			
		 
			
			foreach($old_doc as $k=>$v) $old_doc[$k]=stripslashes($v);	
			
			$_res=new Sched_Resolver($old_doc['kind_id']);
			
			
			$sm1->assign('old_doc', $old_doc);
			//название +
			//приоритет +
			
			$sm1->assign('priority', $old_doc['priority']);
			//крайний срок (есть/нет, дата) +
		 
			//принять после выполнения +
			//описание +
			//контакты
			
			//контрагенты
			$_suppliers=new Sched_SupplierGroup;
			$sup=$_suppliers->GetItemsByIdArr($old_doc['id']);
			$sm1->assign('suppliers', $sup);
		 
		
			
			 
		}else{
			
			$sm1->assign('priority', 1);

		 
		 
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
				
				
			 
				
				$sm1->assign('plan_or_fact' ,1);
			}
		}
			
		 
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
			/*$_cg=new SupplierCitiesGroup;
			$cg=$_cg->GetItemsByIdArr($supplier_id); $cg1=array();
			if(count($cg)>0){
				 $cg1[0]=$cg[0];
				 $cg1[0]['id']=$cg1[0]['city_id'];
			}
			
			$sm1->assign('cities', $cg1);
			*/
			
		 
		} 
		 

		 
		//подставить время напоминания -15 мин
		/*$remind_time=time()-15*60;
		$sm1->assign('remind_beg_hr',date('H',$remind_time) );
		$sm1->assign('remind_beg_mr', date('i',$remind_time)); 
		*/
		
		$from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		
		$remind_time=time()+15*60;
		$sm1->assign('remind_beg_hr',date('H',$remind_time) );
		$sm1->assign('remind_beg_mr', date('i',$remind_time)); 
	
		 $sm1->assign('task_id', $task_id); 
		 
		$sm1->assign('now',  DateFromYmd($datetime)); 
		
		
		$sm1->assign('now_time',  date('d.m.Y H:i:s')); 
		$sm1->assign('now_date',  date('d.m.Y')); 
		 
	 
		//var_dump($meets); 
		
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
		
		
		$sm1->assign('users', $_ug->GetItemsForTask($dec));
		$sm1->assign('user_1_id', $result['id']);
		 
	  	
		$sm1->assign('can_change_user1', $au->user_rights->CheckAccess('w',908));
		
		$sm1->assign('session_id', session_id());
		
		$user_form=$sm1->fetch('plan/create_kind_1.html');
		 
	
	 }elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		//построим доступы
		$_roles=new Sched_FieldRules($result); //var_dump($_roles->GetTable());
		$field_rights=$_roles->GetFieldsRoles($editing_user, $result['id']);
		//var_dump($field_rights);
		$sm1->assign('field_rights', $field_rights);
		
		//$_fields_access=$_roles->HasAccess($editing_user, $result
		
		if(isset($_GET['move_srok'])&&($_GET['move_srok']==1)&&($editing_user['is_waiting_new_pdate']==1)&&$field_rights['can_apply_srok']) $sm1->assign('move_srok', true);
		

		
		
		$_res=new Sched_Resolver($editing_user['kind_id']);
		
		$editing_user['pdate']=date('d.m.Y H:i:s', $editing_user['pdate']);
		
		
		if($editing_user['pdate_beg']!='')  $editing_user['pdate_beg']=datefromYmd($editing_user['pdate_beg']);
		
		

		if($editing_user['new_pdate_beg']!=''){
			  $editing_user['new_pdate_beg']=datefromYmd($editing_user['new_pdate_beg']);
			$sm1->assign('new_ptime_beg_hr',substr($editing_user['new_ptime_beg'],  0,2 ));
			$sm1->assign('new_ptime_beg_mr',substr($editing_user['new_ptime_beg'],  3,2 )); 
			
		}
		

		
		if($editing_user['pdate_status_change']!=0)  $editing_user['pdate_status_change']=date('d.m.Y H:i:s', $editing_user['pdate_status_change']); else $editing_user['pdate_status_change']='-';
		
		
		$_wg=new Sched_WorkingGroup;
		$working_time_unf=$_wg->CalcWorkingTime($id, $zz, $times);
		
		$editing_user['times']=$times;
		$editing_user['working_time_unf']=$working_time_unf;
		
		
		if($editing_user['pdate_end']!='')  $editing_user['pdate_end']=datefromYmd($editing_user['pdate_end']);
		
		$editing_user['has_exp_pdate']=($editing_user['pdate_beg']!='');
		
		
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
		
		
		$users=$_ug->GetItemsForTask($dec);
		$sm1->assign('users', $users);
		

		
		//отв
		$_bu=new Sched_TaskUserItem;
		$bu=$_bu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>2));
		$editing_user['user_2']=$bu['user_id'];
		 
		
		//постан-к
		$bu=$_bu->GetItemByFields(array('sched_id'=>$id, 'kind_id'=>1));
		$editing_user['user_1']=$bu['user_id'];
		
		
		$sm1->assign('users1', $_ug->GetItemsForTask($dec, $editing_user['user_1']));
		$sm1->assign('users2', $_ug->GetItemsForTask($dec, $editing_user['user_2']));
		
		
		
		//соисполнители
		$_bg=new Sched_TaskUserGroup;
		$bg=$_bg->GetItemsByIdArr($id,3);
		$sm1->assign('soisp', $bg);
		
		//сотрудники для делегирования. исключить соисполнителей
		$delegates=array();
		foreach($users as $k=>$user){
			
			$was=false;
			foreach($bg as $kk=>$v){
				if($v['user_id']==$user['user_id']) {
					$was=true;
					break;
				}
			}
			if(!$was) $delegates[]=$user;	
		}
		$sm1->assign('delegates', $delegates);
		

		
		
		//наблюдатели
		$bg=$_bg->GetItemsByIdArr($id,4);
		$sm1->assign('nablud', $bg);
		
		//возможность РЕДАКТИРОВАНИЯ - 
			//пол-ль - создал
	//или пол-ль - в списке видящих
	 	
		//$can_modify=true;
		
		$can_modify=in_array($editing_user['status_id'],$_editable_status_id);
		
		 
		//var_dump(in_array($result['id'], $_dem->GetUserIdsArr($id,2)));
		
	    $from_hrs=array();
		$from_hrs[]='';
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_h',$from_hrs);
		
				
		$from_ms=array();
		$from_ms[]='';
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('ptime_beg_m',$from_ms);
		
		
			$sm1->assign('ptime_beg_hr',substr($editing_user['ptime_beg'],  0,2 ));
			$sm1->assign('ptime_beg_mr',substr($editing_user['ptime_beg'],  3,2 )); 
			
			$sm1->assign('ptime_end_hr',substr($editing_user['ptime_end'],  0,2 ));
			$sm1->assign('ptime_end_mr',substr($editing_user['ptime_end'],  3,2 )); 
		
		
		//возможность редактировать  напоминание
		$can_modify_rep=in_array($editing_user['status_id'],array(18,23,24,25,26));
				
			$sm1->assign('can_modify_rep', $can_modify_rep);  
		
		
		
		
		
		
	
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_res->instance->DocCanAnnul($editing_user['id'],$reason,$editing_user, $result)&&$au->user_rights->CheckAccess('w',905)&&($field_rights['can_annul']);
		if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		if(!$field_rights['can_annul']) $reason='недостаточно прав для данной операции';
		
		$editing_user['can_annul_reason']=$reason;
		
		 
		 
		
		$editing_user['can_restore']=$_res->instance->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',905);
			if(!$au->user_rights->CheckAccess('w',905)) $reason='недостаточно прав для данной операции';
		
		
		
		
		 
		
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
		if(($editing_user['is_fulfiled']==1)&&($editing_user['user_fulfiled_id']!=0)){
					$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_fulfiled_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.date("d.m.Y H:i:s",$editing_user['fulfiled_pdate']);
			
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
		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_res->instance->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
		
		
		
		
		
		//лента задачи
		$len_dec=new DBDecorator();
		$len_dec->AddEntry(new SqlOrdEntry('o.pdate',SqlOrdEntry::ASC));
		$_hg=new Sched_HistoryGroup;
		$history= $_hg->ShowHistory(
			$editing_user['id'],
			 'plan/lenta'.$print_add.'.html', 
			 $len_dec, 
			 $field_rights['can_ed_notes'],
			 true,
			 false,
			 $result,
			 $au->user_rights->CheckAccess('w',906),
			 $au->user_rights->CheckAccess('w',907),$history_data,true,true
			 );
		$sm1->assign('lenta',$history);
		$sm1->assign('lenta_len',count($history_data));
		
		
		
		 
		//контрагенты
		$_suppliers=new Sched_SupplierGroup;
		$sup=$_suppliers->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('suppliers', $sup);
		 
		
		
		
		

		
		
		
		
		
		
		$sm1->assign('can_change_user1', $au->user_rights->CheckAccess('w',908));
		
		
		$sm1->assign('can_modify', $can_modify);  
		
		
		$sm1->assign('can_create', $au->user_rights->CheckAccess('w',904));  
		 
		
		$sm1->assign('can_modify_suppliers', ($field_rights['can_modify_suppliers']&&($editing_user['wo_supplier']==0)) );
		
		
		
		
		
		$_dsi=new docstatusitem; $dsi=$_dsi->GetItemById($editing_user['status_id']);
		$editing_user['status_name']=$dsi['name'];
		$sm1->assign('bill', $editing_user);
		
		
		
		
		
		//реестр прикрепленных файлов
		$folder_id=0;
			 
			  $decorator=new DBDecorator;
			  
			  $decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
			 
			$decorator->AddEntry(new UriEntry('id',$id));
			  
			  $decorator->AddEntry(new SqlEntry('folder_id',$folder_id, SqlEntry::E));
			 $decorator->AddEntry(new UriEntry('folder_id',$folder_id));
		
			  $navi_dec=new DBDecorator;
			  $navi_dec->AddEntry(new UriEntry('action',1));
			  
			  
			  
			  
			  $ffg=new SchedFileGroup(1,  $id,  new FileDocFolderItem(1,  $id, new SchedFileItem(1)));;
			  
			  $filetext=$ffg->ShowFiles('plan/task_files_list.html', $decorator,0,10000,'ed_sched.php', 'sched_file.html', 'swfupl-js/sched_files.php',  
			  false,  
			 false, 
			 false , 
			  $folder_id, 
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,  
			   $result, 
			   $navi_dec, 'file_' 
			   );
		
		
		$sm1->assign('files', $filetext);
		 
		 
		$user_form=$sm1->fetch('plan/edit_kind_1'.$print_add.'.html');
		 
		
	 
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(903,904,905)));
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_sched_task.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		} 
		
		
	}
	
	//вкладка Подзадачи
	$sm1=new smartyadm;
	$sm1->assign('field_rights', $field_rights);
	$sm1->assign('bill', $editing_user);
	
	//подзадачи
	if($action==1){
		$_tasks=new Sched_TaskGroup;
		$prefix=1;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=ITEMS_PER_PAGE;
		  
		$decorator=new DBDecorator;
		
		//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		//видимые сотрудники
		
		if(isset($_GET['viewmode'.$prefix])) $viewmode=abs((int)$_GET['viewmode'.$prefix]);
		else $viewmode=0;
		
		 
		 
	
		
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		}
		
	 	
				
	   
		  
		
		//блок фильтров статуса
	   $status_ids=array();
	  $cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^'.$prefix.'status_id_', $k)) $cou_stat++;
	  if($cou_stat>0){
		  //есть гет-запросы	
		  
		  foreach($_GET as $k=>$v) if(eregi('^'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'status_id_','',$k);
	  }else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
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
			  }
		  } 
		
		   
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		
		 
		$decorator->AddEntry(new SqlEntry('p.task_id',$id, SqlEntry::E));
		  
		
		$docs1=$_tasks->ShowPos($prefix, 'plan/table_1.html',  $decorator, $au->user_rights->CheckAccess('w',905), $from, $to_page, true, false,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), true  );

		
	}else{
		$docs1='В текущем режиме просмотр подзадач невозможен. Сохраните эту задачу для работы с ее подзадачами.';	
	}
		
		$sm1->assign('subtasks', $docs1);
		
	$sm->assign('subtasks', $sm1->fetch('plan/subtasks.html'));	
		 
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('plan/ed_task_page'.$print_add.'.html');
	
	
	
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