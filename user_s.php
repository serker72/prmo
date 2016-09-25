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
require_once('classes/user_s_group.php');
require_once('classes/user_s_item.php');
require_once('classes/discr_man_group.php');
require_once('classes/logincreator.php');
require_once('classes/questionitem.php');
require_once('classes/rolesgroup.php');



require_once('classes/user_to_user.php');
require_once('classes/supplier_to_user.php');


require_once('classes/usercontactdatagroup.php');
require_once('classes/suppliercontactkindgroup.php');

require_once('classes/supplieritem.php');


require_once('classes/user_int_item.php');
require_once('classes/user_int_group.php');
require_once('classes/usercontactdataitem.php');

require_once('classes/sched.class.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Карта сотрудника');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new UserSItem;
$_usersgroup=new UsersSGroup;
$lc=new LoginCreator;
$log=new ActionLog;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=10;
	break;
	case 1:
	$object_id=11;
	break;
	case 2:
	$object_id=14;
	break;
	default:
	$object_id=10;
	break;
}
//echo $object_id;
//die();
/*if(!$au->user_rights->CheckAccess('w',$object_id)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}*/




if($action==1){
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
	
	//карты 1 и 2 - могут править только  1 и 2 b 3
	//1 - 42
	if((($editing_user['id']==1)&&($result['id']!=1)&&($result['id']!=2)&&($result['id']!=3)&&($result['id']!=42)) ||
	
		(($editing_user['id']==2)&&($result['id']!=1)&&($result['id']!=2)&&($result['id']!=3)) ||
		
		(($editing_user['id']==3)&&($result['id']!=1)&&($result['id']!=2)&&($result['id']!=3))  
		)
	{
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403_no_rights.php");
			die();	
	}
	
}

if(($action==1)&&($editing_user['id']==$result['id'])){
	
}else{
  if(!$au->user_rights->CheckAccess('w',$object_id)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
  }
}



//журнал событий 
if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту сотрудника',NULL,11, NULL, $editing_user['login'].' '.$editing_user['name_s'],$id);			
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',10)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
	
	//проверка объекта 13 - круг вопросов пользователя
	$quests=NULL;
	foreach($_POST as $k=>$v){
		if(eregi("^quest_",$k)){
			
			if($quests===NULL) $quests=array();			
			$quests[]=abs((int)$v);
			
		}
		
	}
   
    //обычная загрузка прочих параметров
	
	//$params['group_id']=abs((int)$_POST['group_id']);
	
		$params['group_id']=1;

	
	$params['login']=SecStr($_POST['login']);
	$params['password']=md5($_POST['password']);
	$params['name_s']=SecStr($_POST['name_s']);
	
	$params['email_s']=SecStr($_POST['email_s']);
	
	$params['position_s']=SecStr($_POST['position_s']);
	
	$params['time_from_h_s']=SecStr($_POST['time_from_h_s']);
	$params['time_from_m_s']=SecStr($_POST['time_from_m_s']);
	$params['time_to_h_s']=SecStr($_POST['time_to_h_s']);
	$params['time_to_m_s']=SecStr($_POST['time_to_m_s']);
	
	//паспортные данные
	$params['pasp_ser']=SecStr($_POST['pasp_ser']);
	$params['pasp_no']=SecStr($_POST['pasp_no']);
	$params['pasp_kogda']=SecStr($_POST['pasp_kogda']);
	if(trim($_POST['pasp_bithday'])!="") $params['pasp_bithday']=DateFromdmY($_POST['pasp_bithday']);
	
	$params['pasp_kem']=SecStr($_POST['pasp_kem']);
	$params['pasp_reg']=SecStr($_POST['pasp_reg']);
   	
	$params['manager_id']=abs((int)$_POST['manager_id']);	
	
	if(isset($_POST['is_active'])) $params['is_active']=1;
		else $params['is_active']=0;
		

	
	$code=$ui->Add($params, $quests);
	$lc->ses->DelSession($result['id']);
	
	
	
	$_uci=new UserContactDataItem;
	$_uci->Add(array('user_id'=>$code, 'kind_id'=>5, 'value'=>$params['email_s']));
	
	

	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал пользователя S',$code,10,NULL,$params['login'],$code);
		
		foreach($params as $k=>$v){
			
		 
				$log->PutEntry($result['id'],'создал пользователя S',NULL,10, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}	
	}
	
	
	
	
	//добавим подчиненных менеджеров
	$positions=array();
	foreach($_POST as $k=>$v){
	  if(eregi("^new_submanager_hash_([0-9a-z]+)",$k)){
		  
		  $hash=eregi_replace("^new_submanager_hash_","",$k);
		  
		  $user_id=abs((int)$_POST['new_submanager_id_'.$hash]);
		   
		  
		
		  $positions[]=array(
			  
			   
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
	$log_entries=$ui->AddSubs($code, $positions);
	//die();
	//запишем в журнал
	$_user=new UserSItem;
	 foreach($log_entries as $k=>$v){
		  $user=$_user->GetItemById($v['user_id']);
		  
		 
		  $description='Подчиненный: '.SecStr($user['name_s'].' '.$user['login']).' ';
		    
		  
		  if($v['action']==0){
			  $log->PutEntry($result['id'],'добавил подчиненного сотруднику', $code,10,NULL,$description,$code);	
		  }elseif($v['action']==1){
			  $log->PutEntry($result['id'],'редактировал подчиненного сотрудника',$code,10,NULL,$description,$code);
		  }elseif($v['action']==2){
			  $log->PutEntry($result['id'],'удалил подчиненного сотрудника',$code,10,NULL,$description,$code);
		  }
		  
	  }
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location:".$_usersgroup->pagename."#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',11)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ".$ui->pagename."?action=1&id=".$code.'&tab=rights');
		die();	
		
	}else{
		header("Location: ".$_usersgroup->pagename."");
		die();
	}

	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование пользователя
	
	if(($editing_user['id']!=$result['id'])&&!$au->user_rights->CheckAccess('w',11)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	//карты 1 и 2 - могут править только  1 и 2 b 3
	//1 - 42
	if((($editing_user['id']==1)&&($result['id']!=1)&&($result['id']!=2)&&($result['id']!=3)&&($result['id']!=42)) ||
	
		(($editing_user['id']==2)&&($result['id']!=1)&&($result['id']!=2)&&($result['id']!=3)) ||
		
		(($editing_user['id']==3)&&($result['id']!=1)&&($result['id']!=2)&&($result['id']!=3))  
		)
	{
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403_no_rights.php");
			die();	
	}
	
	$params=array();
	
	
	
	if($editing_user['is_active']==1){
		if(!isset($_POST['is_active'])&&$au->user_rights->CheckAccess('w',12)) $params['is_active']=0;
		
	}else{
		//
		if(isset($_POST['is_active'])&&$au->user_rights->CheckAccess('w',127)) $params['is_active']=1;
	}
	
	//проверка объекта 13 - круг вопросов пользователя
	if((!$au->user_rights->CheckAccess('w',13))){
		
	}else{
	  $quests=array();
	  foreach($_POST as $k=>$v){
		  if(eregi("^quest_",$k)){
			 
				  
			  if($quests===NULL) $quests=array();			
			  $quests[]=abs((int)$v);
		  
		  }
	  }
	}
	
	//обычная загрузка прочих параметров
	//загрузка прочих параметров - Объект 11
	if(/*($editing_user['id']==$result['id'])||*/$au->user_rights->CheckAccess('w',11)){
	  
	  //if(isset($_POST['group_id'])) $params['group_id']=abs((int)$_POST['group_id']);
	  
	  
	  //editing_user
	   if(isset($_POST['email_s'])&&$au->user_rights->CheckAccess('w',1)) $params['email_s']=SecStr($_POST['email_s']);
	 
	  
	  
	  if(isset($_POST['password'])&&(strlen($_POST['password'])>0)) $params['password']=md5($_POST['password']);
	  $params['name_s']=SecStr($_POST['name_s']);
	  $params['position_s']=SecStr($_POST['position_s']);
	
	  $params['time_from_h_s']=SecStr($_POST['time_from_h_s']);
	  $params['time_from_m_s']=SecStr($_POST['time_from_m_s']);
	  $params['time_to_h_s']=SecStr($_POST['time_to_h_s']);
	  $params['time_to_m_s']=SecStr($_POST['time_to_m_s']);
	  
	  
	  //паспортные данные
	  $params['pasp_ser']=SecStr($_POST['pasp_ser']);
	  $params['pasp_no']=SecStr($_POST['pasp_no']);
	  $params['pasp_kogda']=SecStr($_POST['pasp_kogda']);
	  $params['pasp_kem']=SecStr($_POST['pasp_kem']);
	  $params['pasp_reg']=SecStr($_POST['pasp_reg']);
	  
	  if(($_POST['pasp_bithday']!="-")&&($_POST['pasp_bithday']!="")&&($_POST['pasp_bithday']!="0")) $params['pasp_bithday']=DateFromdmY($_POST['pasp_bithday']);
	  
	  if(($_POST['vacation_till_pdate']!="-")&&($_POST['vacation_till_pdate']!="")&&($_POST['vacation_till_pdate']!="0")) $params['vacation_till_pdate']=DateFromdmY($_POST['vacation_till_pdate']);
	   
	   if(isset($_POST['is_in_vacation'])) $params['is_in_vacation']=1;
		else $params['is_in_vacation']=0;
		
		
	    $params['manager_id']=abs((int)$_POST['manager_id']);	
	
	}
	elseif(($editing_user['id']==$result['id'])){
		if(($_POST['vacation_till_pdate']!="-")&&($_POST['vacation_till_pdate']!="")&&($_POST['vacation_till_pdate']!="0")) $params['vacation_till_pdate']=DateFromdmY($_POST['vacation_till_pdate']);
	   
	   if(isset($_POST['is_in_vacation'])) $params['is_in_vacation']=1;
		else $params['is_in_vacation']=0;
	}

	
	
	
	//правка фото
	if(/*($editing_user['id']==$result['id'])||*/$au->user_rights->CheckAccess('w',62)){
		$params['photo']=SecStr($_POST['photo']);	
	}
	
	
	$log_entries=$ui->Edit($id,$params,$quests);
	
	
	if($au->user_rights->CheckAccess('w',1)){
	  //$params['group_id']=abs((int)$_POST['group_id']);
	 	if(isset($params['group_id'])&&($params['group_id']!=$editing_user['group_id'])){
			
			$_dmg=new DiscrManGroup;
			
			//проверим режим редактирование прав
			if(isset($_POST['change_mode'])) $change_mode=abs((int)$_POST['change_mode']);
			else $change_mode=0;
			
			if($change_mode==1){
				//добавим новые права
				//получить таблицу прав новой группы
				//добавить каждое право - пользователю	
				$_dmg->BuildRightsTable($params['group_id']);
				$table=$_dmg->GetRightsTable();
				$_right=new DiscrRightItem;
				$ri=new DiscrRightUserItem;
				foreach($table as $k=>$v){
				//	print_r($v);	  
				  foreach($v['rights'] as $kk=>$vv){
					  $right=$_right->GetItemByFields(array('name'=>$vv));
					  $ri->Add(array('user_id'=>$id, 'right_id'=>$right['id'], 'object_id'=>$v['object_id']  ));	
				  }
				}
				
			}elseif($change_mode==2){
				//удалим ушедшие права
				$_dmg->BuildRightsTable($params['group_id']);
				$new_table=$_dmg->GetRightsTable();
				
				$_dmg->BuildRightsTable($editing_user['group_id']);
				$old_table=$_dmg->GetRightsTable();
				
				$away_table=array();
				
				$away_table=$_dmg->NotInNewTable($old_table, $new_table);
				//print_r($away_table);
				
				$_dm=new DiscrMan;
				//$_right=new DiscrRightItem;
				$ri=new DiscrRightUserItem;
				
				foreach($away_table as $k=>$v){
					//print_r($v);	  
				  foreach($v['rights'] as $kk=>$vv){
					  //echo 'zzz';
					  $_dm->RevokeAccess($id, $vv, $v['object_id']);
				  }
				}
				
				
			}elseif($change_mode==3){
				//назначим новую группу
				$_dmg->BuildRightsTable($params['group_id']);
				$_dmg->ApplyTableToUser($id);
			}
		}
		
	}
	
	//die();
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		
		if($k=='password'){
			if($question!==false) $log->PutEntry($result['id'],'установил пользователю S новый пароль',$id,11, NULL,NULL,$id);	
			continue;	
		}
		if($k=='is_active'){
			if(addslashes($editing_user[$k])!=$v){
			  if($v==0) $log->PutEntry($result['id'],'блокировал пользователя S',$id,12, NULL,NULL,$id);
			  elseif($v==1) $log->PutEntry($result['id'],'разблокировал пользователя S',$id,12, NULL,NULL,$id);
			}
			continue;	
		}
		
		
		if(addslashes($editing_user[$k])!=$v){
			
			if($k=='is_in_vacation'){
			
				if($v==0) $log->PutEntry($result['id'],'редактировал пользователя S',$id,11, NULL, 'снял флаг В Отпуске',$id);	
				elseif($v==1) $log->PutEntry($result['id'],'редактировал пользователя S',$id,11, NULL, 'установил флаг В Отпуске',$id);	
				
				continue;	
			}
			
			if($k=='vacation_till_pdate'){
				$log->PutEntry($result['id'],'редактировал пользователя S',$id,11, NULL, 'в поле Отпуск До установлено значение '.date("d.m.Y",$v),$id);	
				continue;	
			}
			
			
			if($k=='pasp_bithday'){
				$log->PutEntry($result['id'],'редактировал пользователя S',$id,11, NULL, 'в поле Дата рождения установлено значение '.date("d.m.Y",$v),$id);	
				continue;	
			}
			
			$log->PutEntry($result['id'],'редактировал пользователя S',$id,11, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		}
	}
	
	$qi=new QuestionItem;
	//запишем в лог изменение состава вопросов
	
	
	foreach($log_entries as $k=>$v){
			
	  $question=$qi->GetItemById($v['question_id']);
	  
	  if($question!==false){
		  $description=$question['name'];
		  if($v['action']==0){
			  $log->PutEntry($result['id'],'установил пользователю S вопрос',$id,13,NULL,$description,$id);	
		  }elseif($v['action']==2){
			  $log->PutEntry($result['id'],'удалил у пользователя S вопрос',$id,13,NULL,$description,$id);
		  }
	  }
			
		
		
	}
	
	
	
	//добавим подчиненных менеджеров
	if($au->user_rights->CheckAccess('w',11)){

		$positions=array();
		foreach($_POST as $k=>$v){
		  if(eregi("^new_submanager_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_submanager_hash_","",$k);
			  
			  $user_id=abs((int)$_POST['new_submanager_id_'.$hash]);
			   
			  
			  /*$kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id));
			  $user=$_user->GetItemById($user_id);*/
			  $positions[]=array(
				  
				   
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
		$log_entries=$ui->AddSubs($id, $positions);
		//die();
		//запишем в журнал
		$_user=new UserSItem;
		 foreach($log_entries as $k=>$v){
			  $user=$_user->GetItemById($v['user_id']);
			  
			 
			  $description='Подчиненный: '.SecStr($user['name_s'].' '.$user['login']).' ';
				
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил подчиненного сотруднику', $id,11,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал подчиненного сотрудника',$id,11,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил подчиненного сотрудника',$id,11,NULL,$description,$id);
			  }
			  
		  }
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: ".$_usersgroup->pagename."#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		/*if(!$au->user_rights->CheckAccess('w',11)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}*/
		header("Location: ".$ui->pagename."?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: ".$_usersgroup->pagename);
		die();
	}

	die();
}elseif(($action==1)&&(isset($_POST['doEditVis'])||isset($_POST['doEditStayVis']))){
	//редактирование разграничения доступа пользователя
	
	if(/*($editing_user['id']!=$result['id'])&&*/!$au->user_rights->CheckAccess('w',11)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	if(isset($_POST['has_restricted_suppliers'])) $params['has_restricted_suppliers']=1;
	else  $params['has_restricted_suppliers']=0;
	
	if(isset($_POST['has_restricted_users'])) $params['has_restricted_users']=1;
	else  $params['has_restricted_users']=0;
	
	$ui->Edit($id,$params);
		
	
	foreach($params as $k=>$v){
		if(addslashes($editing_user[$k])!=$v){
		 $log->PutEntry($result['id'],'редактировал пользователя S',$id,11, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		}
		 
	}
	
	
	//контрагенты сотрудника
	if($params['has_restricted_suppliers']==1){
	$sectors=array();
	foreach($_POST as $k=>$v){
		if(eregi("^supplier_id_",$k)&&($v==1)){
			$sectors[]=	array('supplier_id'=>abs((int)eregi_replace("^supplier_id_","",$k)), 'org_id'=>$result['org_id']);
		}
	}
	
	$_sector=new SupplierItem;
	$ssgr=new SupplierToUser;
	  
	  	$log_entries=$ssgr->AddSuppliersToUserArray($id, $sectors, $result['org_id']); //>AddSectorsToUserArray($id,$sectors);
		
		/*echo '<pre>';
		print_r($log_entries); 
		echo '</pre>';
		die();
		*/
		
		foreach($log_entries as $k=>$v){
			$sector=$_sector->Getitembyid($v['supplier_id']);
			if($v['action']==0){
				$log->PutEntry($result['id'],'назначил контрагента сотруднику',$v['user_id'],11,NULL,$sector['full_name'],$id);	
			}elseif($v['action']==2){
				$log->PutEntry($result['id'],'удалил контрагента у сотрудника',$v['user_id'],11,NULL,$sector['full_name'],$id);
			}
			
		}
	}
	
	if($params['has_restricted_users']==1){
	//сотрудники сотрудника
	$sectors=array();
	foreach($_POST as $k=>$v){
		if(eregi("^user_id_",$k)&&($v==1)){
			$sectors[]=	abs((int)eregi_replace("^user_id_","",$k));
		}
	}
	
	$_sector=new UserSItem;
	$ssgr=new UserToUser;
	  
	  	$log_entries=$ssgr->AddViewedUsersToUserArray($id, $sectors); //>AddSuppliersToUserArray($id, $sectors); //>AddSectorsToUserArray($id,$sectors);
		
		/*echo '<pre>';
		print_r($log_entries); 
		echo '</pre>';
		die();
		*/
		
		foreach($log_entries as $k=>$v){
			$sector=$_sector->Getitembyid($v['viewed_user_id']);
			if($v['action']==0){
				$log->PutEntry($result['id'],'назначил доступного сотрудника сотруднику',$v['user_id'],11,NULL,$sector['name_s'].' '.$sector['login'],$id);	
			}elseif($v['action']==2){
				$log->PutEntry($result['id'],'удалил доступного сотрудника у сотрудника',$v['user_id'],11,NULL,$sector['name_s'].' '.$sector['login'],$id);
			}
			
		}
			
	}
	
	//перенаправления
	if(isset($_POST['doEditVis'])){
		header("Location: ".$_usersgroup->pagename."#user_".$id);
		die();
	}elseif(isset($_POST['doEditStayVis'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',11)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ".$ui->pagename."?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: ".$_usersgroup->pagename);
		die();
	}

	die();
	
}elseif(($action==1)&&isset($_POST['doDelete'])){
	if(!$au->user_rights->CheckAccess('w',14)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$ui->Del($id);
	
	$log->PutEntry($result['id'],'удалил пользователя S',NULL,14, NULL, $editing_user['login'],$id);	
	
	header("Location: ".$_usersgroup->pagename);
	die();
}


//установка прав данного пользователя
/*	
*/



if(isset($_POST['doUserRights'])||isset($_POST['doUserRightsStay'])){
	

	
	
	if(!$au->user_rights->CheckAccess('w',11)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_user_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - user_id
			//3rd figure - object_id
			eregi("^do_user_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			
			if(($regs!==NULL)&&isset($_POST['user_state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['user_state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				//echo $state;
				
				
				if($state==1){
					$man->GrantAccess($regs[2], $regs[1], $regs[3]);
					$pro=$au->GetProfile();
					$log->PutEntry( $pro['id'], "установил доступ ".$regs[1], $regs[2], $regs[3]);
					//PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL){
				}else{
					$man->RevokeAccess($regs[2], $regs[1], $regs[3]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1], $regs[2],$regs[3]);
				}
					
			}
		}
	}
	
	if(isset($_POST['doUserRightsStay'])){
		header("Location: user_s.php?action=1&id=".$id);	
	}else header("Location: users_s.php");	
	die();
}






//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=8;
	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание пользователя
		
		$sm1=new SmartyAdm;
		//тест
		
		//очистим сессии логинов
		$lc->ses->ClearOldSessions();
		
		
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
		
		$questions=$ui->GetQuestionsAllArr();
		$sm1->assign('items',$questions);
		$sm1->assign('qpp',ceil(count($questions)/5));
		
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',10)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',11)); 
		$sm1->assign('can_block',$au->user_rights->CheckAccess('w',10)); 
		$sm1->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
		$sm1->assign('can_edit_questions',$au->user_rights->CheckAccess('w',13)); 
		
		$sm1->assign('login', $lc->GenLogin(1,$result['id']));
	
		//роли в системе
		$ug=new RolesGroup;
		$uug=$ug->GetItemsArr(0,0,($au->user_rights->CheckAccess('w',153)));
		
		$uu_ids=array(); $uu_names=array();
		
		foreach($uug as $k=>$v){
			$uu_ids[]=$v['id'];
			$uu_names[]=$v['name'];	
		}
		
		$sm1->assign('group_ids',$uu_ids);
		$sm1->assign('group_names',$uu_names);
		
	
		
		
		$user_form=$sm1->fetch('users/s_create.html');
		
		$sup_users='Для разграничения доступа по контрагентам и сотрудникам сохраните сотрудника.';
	}elseif($action==1){
		//редактирование пользователя
		
		
		
		//вкладка разграничения по к-там, сотр-кам
		$sm2=new SmartyAdm;
		//контрагенты сотрудника
		$ssgr = new SupplierToUser;
		$sm2->assign('can_modify_sectors',$editing_user['has_restricted_suppliers']==1);
		$sm2->assign('user',$editing_user);
		$sectors=$ssgr->GetAllUserSuppliersArr($editing_user['id'], $result); //GetAllUserSectorsArr($editing_user['id']); 
		$sm2->assign('sectors',$sectors);
		$sm2->assign('div',round(count($sectors)/2));
		
		//сотрудники сотрудника
		$ssgr = new UserToUser;
		$sm2->assign('can_modify_users',$editing_user['has_restricted_users']==1);
		$sm2->assign('user',$editing_user);
		$sectors=$ssgr->GetAllUserViewedUsersArr($editing_user['id']); //>GetAllUserSuppliersArr($editing_user['id'], $result); //GetAllUserSectorsArr($editing_user['id']); 
		$sm2->assign('users',$sectors);
		$sm2->assign('div1',round(count($sectors)/2));
		
		
		
		
		$sup_users=$sm2->fetch('users/resrtr_users_suppliers.html');
		
		
		
		//строим вкладку редактирования прав данного сотрудника
		$sm->assign('has_rights',$au->user_rights->CheckAccess('w',1));
		//таблица прав по данному пользователю
		$dtu=new DiscrTableUser($editing_user['id']);
		/*echo '<pre>';
		print_r($dtu->DrawArr());
		echo '</pre>';
		*/
		$rights=$dtu->Draw('user_s.php','admin/admin_user_rights.html');
		$sm->assign('rights',$rights);
		
		
		$sm1=new SmartyAdm;
	
		
		//d.r.
		if($editing_user['pasp_bithday']==0) $editing_user['pasp_bithday']='-';
		else $editing_user['pasp_bithday']=date("d.m.Y",$editing_user['pasp_bithday']);
		
		//vacation
		if($editing_user['vacation_till_pdate']==0) $editing_user['vacation_till_pdate']='-';
		else $editing_user['vacation_till_pdate']=date("d.m.Y",$editing_user['vacation_till_pdate']);
		
		
		
		
		
		$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',$editing_user['time_from_h_s']);
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',$editing_user['time_from_m_s']);
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',$editing_user['time_to_h_s']);
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',$editing_user['time_to_m_s']);
		
		$questions=$ui->GetQuestionsAllArr($editing_user['id']);
		$sm1->assign('items',$questions);
		$sm1->assign('qpp',ceil(count($questions)/5));
		
		//инт-лы работы:
		$_uints=new UserIntGroup;
		$uints=$_uints->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('ints',$uints);
		
		
		$sm1->assign('can_common',$au->user_rights->CheckAccess('w',11)||($editing_user['id']==$result['id']));
		
		//возможность смены основного адреса эл.почты
		$sm1->assign('can_change_primary_email',$au->user_rights->CheckAccess('w',1));
		
		
		
		
		//другие сотрудники для планировщика
		$_svg=new Sched_ViewGroup;
		$sm1->assign('sched_view', $_svg->ForCard($editing_user['id'], 'users/sched_view.html', $au->user_rights->CheckAccess('w',1)));
		
		

	 
		
		
		$sm1->assign('can_photo',$au->user_rights->CheckAccess('w',62)/*||($editing_user['id']==$result['id'])*/);
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_block',$au->user_rights->CheckAccess('w',12));
		$sm1->assign('can_unblock',$au->user_rights->CheckAccess('w',127));
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',11)||($editing_user['id']==$result['id']));
		//особое разрешение для редактирования тех полей, которые должны быть видны только для просмотра текущему пол-лю в его карте
		$sm1->assign('can_edit_common',$au->user_rights->CheckAccess('w',11));
		
		 
		$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',14)); 
		
		$sm1->assign('can_delete_user',$ui->CanDelete($editing_user['id'])); 
		
		$sm1->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
		$sm1->assign('can_edit_questions',$au->user_rights->CheckAccess('w',13)); 
		
		$sm1->assign('can_chp',$au->user_rights->CheckAccess('w',882));
		
		
		//кнопка документов
		//$sm1->assign('can_pasp_button',$au->user_rights->CheckAccess('w',119));
		
		//34 и 38 не видят свои документы!!!
		if( (($editing_user['id']==34)&&(($result['id']==34))) ||
		
			(($editing_user['id']==38)&&(($result['id']==34)||($result['id']==38)))
			){
				$can_pasp_button=false;
			}else $can_pasp_button=$au->user_rights->CheckAccess('w',119);
		
		$sm1->assign('can_pasp_button',$can_pasp_button);
		
		
		if((!$au->user_rights->CheckAccess('w',156))&&($editing_user['group_id']==1)) $cannot_change_password=true;
		else $cannot_change_password=false;
		$sm1->assign('cannot_change_password',$cannot_change_password); 
		
		//роли в системе
		$ug=new RolesGroup;
		/*if($cannot_change_password) $uug=$ug->GetItemsArr(); 
		else */
		$uug=$ug->GetItemsArr(0,0,($au->user_rights->CheckAccess('w',155)));
		
		$uu_ids=array(); $uu_names=array();
		
		foreach($uug as $k=>$v){
			$uu_ids[]=$v['id'];
			$uu_names[]=$v['name'];	
		}
		
		$sm1->assign('group_ids',$uu_ids);
		$sm1->assign('group_names',$uu_names);
		
		$sm1->assign('can_edit_role',$au->user_rights->CheckAccess('w',154)); 
		
		
		//участки - место работы
		/*$sg=new SectorGroup;
		$ssg=$sg->GetItemsArr(0,1);
		
		$uu_ids=array(); $uu_names=array();
		$uu_ids[]=0; $uu_names[]='-выберите-';
		foreach($ssg as $k=>$v){
			$uu_ids[]=$v['id'];
			$uu_names[]=$v['name'];	
		}
		$sm1->assign('sector_id',$editing_user['sector_id']);
		$sm1->assign('sector_ids',$uu_ids);
		$sm1->assign('sector_names',$uu_names);
		
		*/
	
		
		
		//контакты
		$rg=new UserContactDataGroup;
		$sm1->assign('contacts',$rg->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('can_cont',true);
		$sm1->assign('can_cont_edit', $au->user_rights->CheckAccess('w',157)/*||($editing_user['id']==$result['id'])*/);
		$rrg=new SupplierContactKindGroup;
		$sm1->assign('kinds',$rrg->GetItemsArr());
		
		
		//участки сотрудника
		/*$ssgr = new SectorToUser;
		$sm1->assign('can_modify_sectors',$au->user_rights->CheckAccess('w',295));
		$sectors=$ssgr->GetAllUserSectorsArr($editing_user['id']); 
		$sm1->assign('sectors',$sectors);
		$sm1->assign('div',round(count($sectors)/2));
		*/
		//участки сотрудника
		/*$stgr = new StorageToUser;
		$sm1->assign('can_modify_sectors',$au->user_rights->CheckAccess('w',295));
		$storages=$stgr->GetAllUserStoragesArr($editing_user['id']); 
		$sm1->assign('storages',$storages);
		$sm1->assign('divs',round(count($storages)/2));
		*/
		
		//менеджеры
		$_usg=new UsersSGroup;
		$_ui=new UserSItem;
		$manager=$_ui->GetItemById($editing_user['manager_id']);
		if($manager!==false){
			$editing_user['manager_id_string']=$manager['name_s'].', '.$manager['position_s'];	
		}
		

		$pod=$ui->GetSubsArr($id);
		//var_dump($pod);
		$sm1->assign('pod', $pod);
		
		
		$sm1->assign('user',$editing_user);
		
		$user_form=$sm1->fetch('users/s_edit.html');
		
		if(isset($_GET['tab'])&&($_GET['tab']=='rights')) $sm->assign('is_current_rights',$user_form);
	}
	
	
	$sm->assign('users',$user_form);
	
	
	
	
	$sm->assign('sup_users', $sup_users);
	
	
	
	$content=$sm->fetch('users/user_s.html');
	
	
	
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