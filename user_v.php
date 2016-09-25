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
require_once('classes/user_v_group.php');
require_once('classes/user_s_group.php');
require_once('classes/user_v_item.php');
require_once('classes/discr_man_group.php');
require_once('classes/logincreator.php');
require_once('classes/questionitem.php');
require_once('classes/rolesgroup.php');




require_once('classes/usercontactdatagroup.php');
require_once('classes/suppliercontactkindgroup.php');



require_once('classes/user_int_item.php');
require_once('classes/user_int_group.php');

 


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Карта виртуального сотрудника');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new UserVItem;
$_usersgroup=new UsersVGroup;
$lc=new LoginCreator;
$log=new ActionLog;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=771;
	break;
	case 1:
	$object_id=772;
	break;
	case 2:
	$object_id=778;
	break;
	default:
	$object_id=771;
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

if(($action==1)&&($editing_user['id']==$result['id'])){
	
}else{
  if(!$au->user_rights->CheckAccess('w',$object_id)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
  }
}


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
	
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',771)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
	$quests=NULL;
	foreach($_POST as $k=>$v){
		if(eregi("^quest_",$k)){
			
			if($quests===NULL) $quests=array();			
			$quests[]=abs((int)$v);
			
		}
		
	}
   
 
    //обычная загрузка прочих параметров
	
	//$params['group_id']=abs((int)$_POST['group_id']);
	
	$params['group_id']=2;
	
	$params['login']=SecStr($_POST['login']);
	$params['password']=md5($_POST['password']);
	$params['name_s']=SecStr($_POST['name_s']);
 	$params['position_s']=SecStr($_POST['position_s']);

	 
	
	/*$params['time_from_h_s']=SecStr($_POST['time_from_h_s']);
	$params['time_from_m_s']=SecStr($_POST['time_from_m_s']);
	$params['time_to_h_s']=SecStr($_POST['time_to_h_s']);
	$params['time_to_m_s']=SecStr($_POST['time_to_m_s']);*/
	
	//паспортные данные
	/*$params['pasp_ser']=SecStr($_POST['pasp_ser']);
	$params['pasp_no']=SecStr($_POST['pasp_no']);
	$params['pasp_kogda']=SecStr($_POST['pasp_kogda']);
	if(trim($_POST['pasp_bithday'])!="") $params['pasp_bithday']=DateFromdmY($_POST['pasp_bithday']);
	
	$params['pasp_kem']=SecStr($_POST['pasp_kem']);
	$params['pasp_reg']=SecStr($_POST['pasp_reg']);*/
   	
	
	if(isset($_POST['is_active'])) $params['is_active']=1;
		else $params['is_active']=0;
		
	 
		
	 
	
	$code=$ui->Add($params, $quests);
	$lc->ses->DelSession($result['id']);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал пользователя V',$code,771,NULL,$params['login'],$code);	
	}
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location:".$_usersgroup->pagename."?tab_page=3#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',772)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ".$ui->pagename."?action=1&id=".$code.'&tab=rights');
		die();	
		
	}else{
		header("Location: ".$_usersgroup->pagename."?tab_page=3");
		die();
	}
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование пользователя
	
	if(/*($editing_user['id']!=$result['id'])&&*/!$au->user_rights->CheckAccess('w',772)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
	
	if($editing_user['is_active']==1){
		if(!isset($_POST['is_active'])&&$au->user_rights->CheckAccess('w',782)) $params['is_active']=0;
		
	}else{
		//
		if(isset($_POST['is_active'])&&$au->user_rights->CheckAccess('w',781)) $params['is_active']=1;
	}
	
	//проверка объекта 13 - круг вопросов пользователя
	if((!$au->user_rights->CheckAccess('w',772))){
		
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
	//загрузка прочих параметров - Объект 56
	 
	  
	  //if(isset($_POST['group_id'])) $params['group_id']=abs((int)$_POST['group_id']);
	  
	  
	  //editing_user
	  
	  
	  if(isset($_POST['password'])&&(strlen($_POST['password'])>0)) $params['password']=md5($_POST['password']);
	  $params['name_s']=SecStr($_POST['name_s']);
	 
	 
	  
 	$params['position_s']=SecStr($_POST['position_s']);

	
	 /* $params['time_from_h_s']=SecStr($_POST['time_from_h_s']);
	  $params['time_from_m_s']=SecStr($_POST['time_from_m_s']);
	  $params['time_to_h_s']=SecStr($_POST['time_to_h_s']);
	  $params['time_to_m_s']=SecStr($_POST['time_to_m_s']);
	  
	  
	  //паспортные данные
	  $params['pasp_ser']=SecStr($_POST['pasp_ser']);
	  $params['pasp_no']=SecStr($_POST['pasp_no']);
	  $params['pasp_kogda']=SecStr($_POST['pasp_kogda']);
	  $params['pasp_kem']=SecStr($_POST['pasp_kem']);
	  $params['pasp_reg']=SecStr($_POST['pasp_reg']);*/
	  
	 /* if(($_POST['pasp_bithday']!="-")&&($_POST['pasp_bithday']!="")&&($_POST['pasp_bithday']!="0")) $params['pasp_bithday']=DateFromdmY($_POST['pasp_bithday']);
	  
	  if(($_POST['vacation_till_pdate']!="-")&&($_POST['vacation_till_pdate']!="")&&($_POST['vacation_till_pdate']!="0")) $params['vacation_till_pdate']=DateFromdmY($_POST['vacation_till_pdate']);
	   
	   if(isset($_POST['is_in_vacation'])) $params['is_in_vacation']=1;
		else $params['is_in_vacation']=0;*/
		
		
	    
	
	
	
	//правка фото
	if(/*($editing_user['id']==$result['id'])||*/$au->user_rights->CheckAccess('w',774)){
		$params['photo']=SecStr($_POST['photo']);	
	}
	
	
	$log_entries=$ui->Edit($id,$params,$quests);
	
	
	
	
	//die();
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		
		if($k=='password'){
			if($question!==false) $log->PutEntry($result['id'],'установил пользователю V новый пароль',$id,772, NULL,NULL,$id);	
			continue;	
		}
		if($k=='is_active'){
			if(addslashes($editing_user[$k])!=$v){
			  if($v==0) $log->PutEntry($result['id'],'блокировал пользователя V',$id,782, NULL,NULL,$id);
			  elseif($v==1) $log->PutEntry($result['id'],'разблокировал пользователя V',$id,781, NULL,NULL,$id);
			}
			continue;	
		}
		
		
		if(addslashes($editing_user[$k])!=$v){
			
			if($k=='is_in_vacation'){
			
				if($v==0) $log->PutEntry($result['id'],'редактировал пользователя V',$id,772, NULL, 'снял флаг В Отпуске',$id);	
				elseif($v==1) $log->PutEntry($result['id'],'редактировал пользователя V',$id,772, NULL, 'установил флаг В Отпуске',$id);	
				
				continue;	
			}
			
			if($k=='vacation_till_pdate'){
				$log->PutEntry($result['id'],'редактировал пользователя V',$id,772, NULL, 'в поле Отпуск До установлено значение '.date("d.m.Y",$v),$id);	
				continue;	
			}
			
			
			if($k=='pasp_bithday'){
				$log->PutEntry($result['id'],'редактировал пользователя V',$id,772, NULL, 'в поле Дата рождения установлено значение '.date("d.m.Y",$v),$id);	
				continue;	
			}
			
			 
			
			 
			$log->PutEntry($result['id'],'редактировал пользователя V',$id,772, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
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
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: ".$_usersgroup->pagename."?tab_page=3#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',772)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ".$ui->pagename."?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: ".$_usersgroup->pagename."?tab_page=3");
		die();
	}
	
	die();
}elseif(($action==1)&&isset($_POST['doDelete'])){
	if(!$au->user_rights->CheckAccess('w',778)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$ui->Del($id);
	
	$log->PutEntry($result['id'],'удалил пользователя V',NULL,778, NULL, $editing_user['login'],$id);	
	
	header("Location: ".$_usersgroup->pagename."?tab_page=3");
	die();
}


//установка прав данного пользователя
/*	
*/



if(isset($_POST['doUserRights'])||isset($_POST['doUserRightsStay'])){
	

	
	
	if(!$au->user_rights->CheckAccess('w',772)){
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
		header("Location: ".$ui->pagename."?action=1&id=".$id);	
	}else header("Location: ".$_usersgroup->pagename."?tab_page=3");	
	die();
}






//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



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
		$sm1->assign('qpp',ceil(count($questions)/2));
		
		 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',771)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',772)); 
		$sm1->assign('can_block',$au->user_rights->CheckAccess('w',771)); 
			$sm1->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
		$sm1->assign('can_edit_questions',$au->user_rights->CheckAccess('w',772)); 
		 
		
		$sm1->assign('login', $lc->GenLogin(2,$result['id']));
	
		 
		
		$uu_ids=array(); $uu_names=array();
		
		foreach($uug as $k=>$v){
			$uu_ids[]=$v['id'];
			$uu_names[]=$v['name'];	
		}
		
		$sm1->assign('group_ids',$uu_ids);
		$sm1->assign('group_names',$uu_names);
		
	
	
	 
	  

		
		$sm1->assign('pagename', $ui->pagename);
		
		$user_form=$sm1->fetch('users_v/v_create.html');
	}elseif($action==1){
		//редактирование пользователя
		
		//строим вкладку администрирования
		/*$sm->assign('has_admin',$au->user_rights->CheckAccess('x',12)||
								$au->user_rights->CheckAccess('x',13)||
								$au->user_rights->CheckAccess('x',14)||
								$au->user_rights->CheckAccess('x',11)||
								$au->user_rights->CheckAccess('x',56)||
								$au->user_rights->CheckAccess('x',62)||
								$au->user_rights->CheckAccess('x',119)
								);
		$dto=new DiscrTableObjects($result['id'],array('11','12','13','14','56','62','119'));
		$admin=$dto->Draw('user_s.php','admin/admin_objects.html');
		$sm->assign('admin',$admin);
		*/
		
		//строим вкладку редактирования прав данного сотрудника
		$sm->assign('has_rights',$au->user_rights->CheckAccess('w',1));
		//таблица прав по данному пользователю
		$dtu=new DiscrTableUser($editing_user['id']);
		/*echo '<pre>';
		print_r($dtu->DrawArr());
		echo '</pre>';
		*/
		$rights=$dtu->Draw( $ui->pagename,'admin/admin_user_rights.html');
		$sm->assign('rights',$rights);
		
		
		$sm1=new SmartyAdm;
	
		
		//d.r.
		if($editing_user['pasp_bithday']==0) $editing_user['pasp_bithday']='-';
		else $editing_user['pasp_bithday']=date("d.m.Y",$editing_user['pasp_bithday']);
		
		//vacation
		if($editing_user['vacation_till_pdate']==0) $editing_user['vacation_till_pdate']='-';
		else $editing_user['vacation_till_pdate']=date("d.m.Y",$editing_user['vacation_till_pdate']);
		
		
		
		$sm1->assign('user',$editing_user);
		
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
		$sm1->assign('qpp',ceil(count($questions)/2));
		
	 
		
		//инт-лы работы:
		/*$_uints=new UserIntGroup;
		$uints=$_uints->GetItemsByIdArr($editing_user['id']);
		$sm1->assign('ints',$uints);*/
		
		
		$sm1->assign('can_common',$au->user_rights->CheckAccess('w',772)/*||($editing_user['id']==$result['id'])*/);
		
		$sm1->assign('can_photo',$au->user_rights->CheckAccess('w',774)/*||($editing_user['id']==$result['id'])*/);
		$sm1->assign('session_id', session_id());
		
		$sm1->assign('can_block',$au->user_rights->CheckAccess('w',782));
		$sm1->assign('can_unblock',$au->user_rights->CheckAccess('w',781));
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',772)/*||($editing_user['id']==$result['id'])*/); 
		$sm1->assign('can_delete',$au->user_rights->CheckAccess('w',778)); 
		
		$sm1->assign('can_delete_user',$ui->CanDelete($editing_user['id'])); 
		
			$sm1->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
		$sm1->assign('can_edit_questions',$au->user_rights->CheckAccess('w',772)); 
		
		
		//кнопка документов
		$sm1->assign('can_pasp_button',$au->user_rights->CheckAccess('w',775));
		
		
		if((!$au->user_rights->CheckAccess('w',156))&&($editing_user['group_id']==1)) $cannot_change_password=true;
		else $cannot_change_password=false;
		$sm1->assign('cannot_change_password',$cannot_change_password); 
		
		 
		
		
		 
		
		
		//контакты
		$rg=new UserContactDataGroup;
		$sm1->assign('contacts',$rg->GetItemsByIdArr($editing_user['id']));
		$sm1->assign('can_cont',true);
		$sm1->assign('can_cont_edit', $au->user_rights->CheckAccess('w',773)/*||($editing_user['id']==$result['id'])*/);
		$rrg=new SupplierContactKindGroup;
		$sm1->assign('kinds',$rrg->GetItemsArr());
		
		 
		
		 
		
		 
		
		$sm1->assign('pagename', $ui->pagename);
		
		$user_form=$sm1->fetch('users_v/v_edit.html');
		
		if(isset($_GET['tab'])&&($_GET['tab']=='rights')) $sm->assign('is_current_rights',$user_form);
	}
	
	
	$sm->assign('users',$user_form);
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