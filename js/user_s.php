<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_s_item.php');
require_once('../classes/questionitem.php');


require_once('../classes/usercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/usercontactdataitem.php');

require_once('../classes/user_int_item.php');

require_once('../classes/user_int_group.php');

require_once('../classes/sched.class.php');

require_once('../classes/user_view.class.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';

$ui=new UserSItem;
if(isset($_POST['action'])&&($_POST['action']=="redraw_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	
	
	$sm->assign('items',$ui->GetQuestionsAllArr($user_id));
	
	$sm->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
	$sm->assign('can_edit_questions',$au->user_rights->CheckAccess('w',13)); 
	
		
	$ret=$sm->fetch('users/s_user_questions_dic.html');
}if(isset($_POST['action'])&&($_POST['action']=="redraw_dics_page")){
	$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	
	$arr=$ui->GetQuestionsAllArr($user_id);
	$sm->assign('items',$arr);
	$sm->assign('qpp',ceil(count($arr)/5));	
	$sm->assign('can_expand_questions',$au->user_rights->CheckAccess('w',152)); 
		$sm->assign('can_edit_questions',$au->user_rights->CheckAccess('w',13)); 
	
	
	$ret=$sm->fetch('users/s_user_questions.html');
}elseif(isset($_POST['action'])&&($_POST['action']=="add_question")){
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new QuestionItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Add($params);
	
	$log->PutEntry($result['id'],'добавил вопрос, курируемый сотрудниками',NULL,13,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_question")){
	if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new QuestionItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	$log->PutEntry($result['id'],'редактировал вопрос, курируемый сотрудниками',NULL,13,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_question")){
	
	if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$qi=new QuestionItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	$log->PutEntry($result['id'],'удалил вопрос, курируемый сотрудниками',NULL,13,NULL,$params['name']);
}
//РАБОТА С КОНТАКТАМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_contact")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new UserContactDataGroup;
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','contact');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Контакты');
	
	$rrg=new SupplierContactKindGroup;
	$sm->assign('kinds',$rrg->GetItemsArr());
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11)||($uitem['id']==$result['id']));
	
	
	$ret=$sm->fetch('users/contacts.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_contact")){
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',11)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$user_id=abs((int)$_POST['user_id']);
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$ri=new UserContactDataItem;
	$ri->Add(array(
		'value'=>SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),
		'user_id'=>abs((int)$_POST['user_id']),
		'kind_id'=>abs((int)$_POST['kind_id'])
	));
	
	
	$log->PutEntry($result['id'],'добавил данные контакта сотруднику', $user_id,11,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_nest_contact")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$params=array();
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['value']=SecStr(iconv("utf-8","windows-1251",$_POST['value']),9);
	
	
	$ri=new UserContactDataItem;
	$ri->Edit($id, $params);
	
	$log->PutEntry($result['id'],'редактировал данные контакта сотрудника', $user_id,11,NULL,'установлено значение '.SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_contact")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$ri=new UserContactDataItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил данные контакта сотрудника', $user_id,11,NULL,NULL,$user_id);
	
}
//РАБОТА С ВРЕМЕНАМИ РАБОТЫ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_ints")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new UserIntGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	$sm->assign('word','ints');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','время работы');
	
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11)||($uitem['id']==$result['id']));
	
	
	$ret=$sm->fetch('users/userint.html');
	
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="add_ints")){
	
	
	$user_id=abs((int)$_POST['user_id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$ri=new UserIntItem;
	$ri->Add(array(
		'comments'=>SecStr(iconv("utf-8","windows-1251",$_POST['comments']),9),
		'user_id'=>abs((int)$_POST['user_id']),
		'time_from_h_s'=>abs((int)$_POST['time_from_h_s']),
		'time_from_m_s'=>abs((int)$_POST['time_from_m_s']),
		'time_to_h_s'=>abs((int)$_POST['time_to_h_s']),
		'time_to_m_s'=>abs((int)$_POST['time_to_m_s']),
		'pdate'=>time(),
		'posted_user_id'=>$result['id']
	));
	
	
	$log->PutEntry($result['id'],'добавил дополнительное время работы сотруднику', NULL,11,NULL, 'с '.sprintf("%02d",abs((int)$_POST['time_from_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_from_m_s'])).' по '.sprintf("%02d",abs((int)$_POST['time_to_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_to_m_s'])),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_nest_ints")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$params=array(
		'comments'=>SecStr(iconv("utf-8","windows-1251",$_POST['comments']),9),
		'time_from_h_s'=>abs((int)$_POST['time_from_h_s']),
		'time_from_m_s'=>abs((int)$_POST['time_from_m_s']),
		'time_to_h_s'=>abs((int)$_POST['time_to_h_s']),
		'time_to_m_s'=>abs((int)$_POST['time_to_m_s'])
	
	);
	
	
	
	$ri=new UserIntItem;
	$ri->Edit($id, $params);
	
	$log->PutEntry($result['id'],'редактировал время работы сотрудника', NULL,11,NULL,'с '.sprintf("%02d",abs((int)$_POST['time_from_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_from_m_s'])).' по '.sprintf("%02d",abs((int)$_POST['time_to_h_s'])).':'.sprintf("%02d",abs((int)$_POST['time_to_m_s'])),$user_id);
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_ints")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	$ui=new UserSItem;
	$uitem=$ui->getitembyid($user_id);
	
	
	//dostup
	if(!$au->user_rights->CheckAccess('w',11)&&($uitem['id']!=$result['id'])){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new UserIntItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил время работы сотрудника', NULL,11,NULL,NULL,$user_id);
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="check_primary_email")){
	
		$id=abs((int)$_POST['id']);
		$email_s=SecStr(iconv('utf-8', 'windows-1251', $_POST['email_s']));
		
		$sql='select * from user where email_s="'.$email_s.'"';
		if($id!=0) $sql.=' and id<>"'.$id.'" ';
		
		$sql.=' order by name_s asc';
		 
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getResultnumrows();
		
		if($rc==0) $ret=0;
		else{
			$rets=array();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$rets[]=$f['name_s'].' '.$f['login'];
			}
			$ret='Указанный основной электронный адрес уже задан у сотрудников: '.implode(', ', $rets);
		}
		
	
		//если ноль - то все хорошо
	
}



//правка доступов к планировщику других сотрудников
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	//$id=abs((int)$_POST['id']);
	
	
	$_svg=new Sched_ViewGroup;
	
	
	$ret=$_svg->ForWindow($user_id, 'users/sched_edit.html',true,true);
	
	
	 
	
	 
}

elseif(isset($_POST['action'])&&($_POST['action']=="transfer_users")){
	
	$user_id=abs((int)$_POST['user_id']);
	//$mode=abs((int)$_POST['mode']);
	
	$_svg=new Sched_ViewGroup;
	
	$data=$_POST['data'];
	
	//записи в журнал
	$_user=new UserSItem;
	$log=new ActionLog;
	
	$_kpi=new Sched_ViewItem;
	foreach($data as $k=>$v){
		$valarr=explode(";",$v);
		
		//$positions[]=array('user_id'=>$user_id, 'allowed_id'=>abs((int)$valarr[0]));	
		$test_kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id, 'kind_id'=>$valarr[1], 'allowed_id'=>$valarr[0]));
		
		
		$user=$_user->GetItemById($valarr[0]);
				  
		$description='Измененный сотрудник: ';
		if($valarr[0]==0){
		   $description.='Все сотрудники';  
		}else{
			$description.=SecStr($user['name_s'].' '.$user['login']).'';
		}
				  
		switch($valarr[1]){
			case 1:
				$description.=', Раздел Задачи';
			break;	
			case 2:
				$description.=', Раздел Командировки';
			break;	
			case 3:
				$description.=', Раздел Встречи';
			break;	
			case 4:
				$description.=', Раздел Звонки';
			break;	
			case 5:
				$description.=', Раздел Заметки';
			break;	
			
		}
		
		
		if($valarr[2]==0){
			$_kpi->Del($test_kpi['id']);
			
			 $log->PutEntry($result['id'],'удалил у сотрудника доступ к записям планировщика другого сотрудника',$user_id,11,NULL,$description,$user_id);	
		}else{
			if($test_kpi==false){
				$_kpi->Add(array('user_id'=>$user_id, 'kind_id'=>$valarr[1], 'allowed_id'=>$valarr[0]));
				
				$log->PutEntry($result['id'],'добавил сотруднику доступ к записям планировщика другого сотрудника', $user_id,11,NULL,$description,$user_id);	
			}
		}
		
	}
	
	/*$users=$_POST['users'];
	
	if($mode==0){
		$positions=array(
			array('user_id'=>$user_id, 'allowed_id'=>0)
			);
	}else{
		//$_users=explode(';',$users);
		$positions=array();
		foreach($users as $k=>$v) $positions[]=array('user_id'=>$user_id, 'allowed_id'=>abs((int)$v));
		
		//print_r($users);
	}
	
	$log_entries=$_svg->AddUsers($user_id, $positions);
	
	//записи в журнал
	$_user=new UserSItem;
	$log=new ActionLog;
	
	
	if($mode==0){
		 $log->PutEntry($result['id'],'предоставил сотруднику доступ к записям планировщика всех сотрудников', $user_id,11,NULL,'',$user_id);	
		 
	}else foreach($log_entries as $k=>$v){
				 // $user1=$_user->GetItemById($v['user_id']);
				  $user=$_user->GetItemById($v['allowed_id']);
				  
				 
				 
				  $description='Добавленный сотрудник: ';
				  if($v['allowed_id']==0){
					 $description.='Все сотрудники';  
				  }else{
					  $description.=SecStr($user['name_s'].' '.$user['login']).'';
				  }
				  
				  if($v['action']==0){
					  $log->PutEntry($result['id'],'добавил сотруднику доступ к записям планировщика другого сотрудника', $user_id,11,NULL,$description,$user_id);	
				  }elseif($v['action']==1){
					  $log->PutEntry($result['id'],'редактировал у сотрудника доступ к записям планировщика другого сотрудника',$user_id,11,NULL,$description,$user_id);
				  }elseif($v['action']==2){
					  $log->PutEntry($result['id'],'удалил у сотрудника доступ к записям планировщика другого сотрудника',$user_id,11,NULL,$description,$user_id);
				  }
				  
			  }
			  
			  */
}


elseif(isset($_POST['action'])&&($_POST['action']=="reload_users")){
	//dostup
	
	
	$user_id=abs((int)$_POST['user_id']);
	//$id=abs((int)$_POST['id']);
	
	
	$_svg=new Sched_ViewGroup;
	
	
	$ret=$_svg->ForCard($user_id, 'users/sched_view.html', $au->user_rights->CheckAccess('w',11), true); //>ForWindow($user_id, 'users/sched_edit.html',true,true);
	
	
	 
	
	 
}




//виджеты подчиненных
elseif(isset($_POST['action'])&&($_POST['action']=="find_managers")){
	
	$sm=new SmartyAj;
	//получим список позиций по фильтру
	$_pg=new Sched_UsersSGroup;
	 
	
	$dec=new DBDecorator;
	
	//except_ids
	if(is_array($_POST['except_ids'])&&(count($_POST['except_ids'])>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$_POST['except_ids']));	
	}
	
	 
	$items=$_pg->GetItemsForBill($dec);
	
	//$ret=$_pg->GetItemsForSelect('users/suppliers_list.html',  $dec,true,$all7,$result);
	$sm->assign('manager_id', (int)$_POST['manager_id']);
	$sm->assign('items', $items);
	$ret=$sm->fetch('users/managers_list.html');
	
}

elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_manager")){
	$_si=new UserSItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	 
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		$ret='{'.implode(', ',$rret).'}';
	}
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="load_submanagers")){
	
	 
	
	$user_id=abs((int)$_POST['user_id']);
	$_bi1=new UserSItem;
	$bi1=$_bi1->GetItemById($user_id);
	
	
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	$except_users=$_POST['except_ids'];
	
 
	$_kpg=new Sched_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	 
	
	if(is_array($except_users)&&(count($except_users)>0)){
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$except_users));
	}
	
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				/*($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])	*/
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			 
		  }
		  
		   
		  
		  //занести доступность/недоступность, и кто начальник
		  $is_avail=true;
		  if(($v['manager_id']!=0)&&($v['manager_id']!=$user_id)){
			  $is_avail=false;
			
			  $main=$_bi1->getItembyid($v['manager_id']);
			  
			  $v['manager_of_user']=$main['name_s'].', '.$main['position_s'];	   
		  }
		  
		  $v['is_avail']=$is_avail;
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 $sm->assign('can_edit_common',true); 
	
	
 
	
	$ret.=$sm->fetch("users/submanagers_edit_set.html");
	
	 
 
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="transfer_submanagers")){
	//перенос выбранных позиций  на страницу  
		
	$user_id=abs((int)$_POST['user_id']);
	 $complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	$_user=new UserSItem;
	 

	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		
		
		
		$user=$_user->GetItemById($v[0]);
		if($user===false) continue;
		
		 
		$f['id']=$v[0];
		$f['user_id']=$v[0];
		
		 
		
		$f['name_s']=$user['name_s'];
		$f['login']=$user['login'];
		$f['position_s']=$user['position_s'];
		
		$f['is_active']=$user['is_active'];
		
		$f['hash']=md5($v[0]);
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	$sm->assign('can_edit_common',true); 
	
	 
	$ret=$sm->fetch("users/submanagers_on_page_set.html");
	
	

}	


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new User_ViewGroup;
	$_view=new User_ViewItem;
	
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
	$_views=new User_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>