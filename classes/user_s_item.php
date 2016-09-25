<?
require_once('abstractitem.php');
require_once('discr_man_group.php');
require_once('discr_rightuseritem.php');
require_once('questionuseritem.php');

require_once('usercontactdatagroup.php');
require_once('user_int_group.php');
require_once('messageitem.php');
require_once('suppliercontactkindgroup.php');

//сотрудник S итем
class UserSItem extends AbstractItem{
	public $pagename;

	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->item=NULL;
		$this->pagename='user_s.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	
	public function Add($params,$questions=NULL){
		//$params['group_id']=2;	
		
		$code=parent::Add($params);
		
		if($code!=0){
		  //использовать административные шаблоны
		  $dmg= new DiscrManGroup;
		  
		  $dmg->BuildRightsTable($params['group_id']);
		  $dmg->ApplyTableToUser($code);
		 
		  //добавим вопросы
		  $this->SetQuestions($code,$questions);
		}
		
		//времянка - всем даем доступ в СМУ-1 нет ничего более постоянного, чем временное
		new NonSet('insert into supplier_to_user (`org_id`, `user_id`) values("1", "'.$code.'")');
		
		
		//сообщение новому пользователю
		if($code!=0){
			$_mi=new MessageItem;	
			$mparams=array();
			
			$mparams['topic']='Добро пожаловать в программу «'.SITETITLE.'»';
			$mparams['txt']='
			<div><em>Данное сообщение сгенерировано автоматически.</em></div>
			<div>Уважаемый/ая '.$params['name_s'].'!</div>
<div><br /></div>
<div>Приветствуем Вас в программе «'.SITETITLE.'».</div>
<div>Вы являетесь новым сотрудником.</div>
<div><br /></div>
<div>Ваш логин: '.$params['login'].'</div>
<div>Ваш пароль был согласован с Вами при создании Вашей учетной записи.</div>
<div>Для обеспечения безопасности работы в программе просим сменить Ваш пароль. Это можно сделать в разделе «Мой профиль».</div>
<div><br /></div>
<div>Подробная инструкция по работе в программе доступна в разделе «Справочная информация».</div>
<div>Во всех разделах программы «'.SITETITLE.'» доступна справочная система.</div>
<div><br /></div>
<div>Желаем удачной работы!</div>
<div>С уважением, программа «'.SITETITLE.'» и команда разработчиков.</div>

			
			';
			
			
			$mparams['to_id']= $code;
			$mparams['from_id']=-1; //Автоматическая система рассылки сообщений
			$mparams['pdate']=time();
			
			$_mi->Send(0,0,$mparams,false);
			
		}
		
		
		return $code;
	}
	
	public function Edit($id,$params,$questions=NULL){
		//if(isset($params['group_id'])) unset($params['group_id']); //$params['group_id']=2;	
		
		$log_entries=array();
		
		if($questions!==NULL) $log_entries=$this->SetQuestions($id,$questions);
		
		parent::Edit($id,$params);
		
		return $log_entries;
	}
	
	//удалить
	public function Del($id){
		
		new NonSet('update user set manager_id=0 where manager_id="'.$id.'"');
		new NonSet('delete from user_rights where user_id="'.$id.'"');
		new NonSet('delete from question_user where user_id="'.$id.'"');
		
		new NonSet('delete from user_work_intervals where user_id="'.$id.'"');
		
		
		
		parent::Del($id);
	}	
	
	//создать функции генерации и фиксации логина
	
	
	//формирование списка курируемых вопросов пользователя
	public function SetQuestions($user_id, $questions){
		/*new NonSet('delete from question_user where user_id="'.$user_id.'"');
		$qui=new QuestionUserItem;
		foreach($questions as $k=>$v){
			$qui->Add(array('user_id'=>$user_id, 'question_id'=>$v));	
		}*/
		
		
		$_kpi=new QuestionUserItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetQuestionsArr($user_id);
		
		foreach($questions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('user_id'=>$user_id,'question_id'=>$v));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['question_id']=$v;
				$add_array['user_id']=$user_id;
				
				
				
				
				$_kpi->Add($add_array);//, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$user_id,
					'question_id'=>$v
				);
				
			}/*
			секция редактирования не нужна!
			else{
				//++ pozicii
				
				$add_array=array();
				$add_array['question_id']=$v['question_id'];
				$add_array['user_id']=$v['user_id'];
				
				$_kpi->Edit($kpi['id'],$add_array);
				
				//если есть изменения
				$log_entries[]=array(
					'action'=>1,
					'user_id'=>$user_id,
					'question_id'=>$v
				);
				
			}*/
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($questions as $kk=>$vv){
				if($vv==$v['question_id']){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			
			
			$log_entries[]=array(
					'action'=>2,
					'user_id'=>$user_id,
					'question_id'=>$v['question_id']
			);
			
			//удаляем позицию
			$_kpi->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
		
		
		
		
		
			
	}
	
	
	//получим позиции
	public function GetQuestionsArr($id){
		$arr=array();	
		
		$set=new mysqlSet('select * from question_user where user_id="'.$id.'" order by question_id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//
			$arr[]=$f;	
		}
		
		
		
		return $arr;		
		
	}
	
	
	//просмотр полного списка вопросов с отмеченными актуальными
	public function GetQuestionsAllArr($user_id=0){
		$arr=array();	
		
		$set=new mysqlSet('select q.*, qu.id as qu_id from question as q left join question_user as qu on q.id=qu.question_id and qu.user_id="'.$user_id.'" order by q.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//
			$arr[]=$f;	
		}
		
		return $arr;
	}
	
	
	
	//формирование массива с полями пользователя
	public function Deploy($id){
		$arr=array();
		
		$user=$this->GetItemById($id);
		if($user!==false){
			$arr['login']=$user['login'];
			$arr['name_s']=$user['name_s'];
			$arr['photo']=$user['photo'];
			
			
			$arr['position_s']=$user['position_s'];
			$arr['phone_work_s']=$user['phone_work_s'];
			$arr['phone_cell_s']=$user['phone_cell_s'];
			$arr['email_s']=$user['email_s'];
			$arr['time_from_h_s']=$user['time_from_h_s'];
			$arr['time_from_m_s']=$user['time_from_m_s'];
			$arr['time_to_h_s']=$user['time_to_h_s'];
			$arr['time_to_m_s']=$user['time_to_m_s'];
			
			$arr['pasp_ser']=$user['pasp_ser'];
			$arr['pasp_no']=$user['pasp_no'];
			$arr['pasp_kogda']=$user['pasp_kogda'];
			$arr['pasp_kem']=$user['pasp_kem'];
			$arr['pasp_reg']=$user['pasp_reg'];
			
			if($user['pasp_bithday']==0) $arr['pasp_bithday']='-';
			else $arr['pasp_bithday']=date("d.m.Y",$user['pasp_bithday']);
			
			$arr['is_in_vac']=($user['vacation_till_pdate']>=time())&&($user['is_in_vacation']==1);
			$arr['vacation_till_pdate']=date("d.m.Y",$user['vacation_till_pdate']);
			
			$quests=array();
			$set=new mysqlSet('select q.name from question as q inner join question_user as qu on q.id=qu.question_id where qu.user_id="'.$user['id'].'" order by q.id asc');
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$quests[]=$f;
			}
			
			
				//контакты
			$rg=new UserContactDataGroup;
		  	$arr['contacts']=$rg->GetItemsByIdArr($user['id']);
		
			
			$arr['questions']=$quests;
			
			//доп. время работы:
			$_uig=new UserIntGroup;
			$arr['ints']=$_uig->GetItemsByIdArr($user['id']);
			
			
			//может менять др
			$_dm=new DiscrMan;
			$arr['can_change_birthdate']=$_dm->CheckAccess($user['id'], 'w', 11);
		}
		return $arr;
	}
	
	
	
	
	
	
	//контроль возможности удаления
	public function CanDelete($id){
		$can_delete=true;
		
		$set=new mysqlSet('select count(*) from kp where (user_confirm_price_id="'.$id.'"  ) and is_confirmed_price=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from bill where (user_confirm_price_id="'.$id.'" or user_confirm_shipping_id="'.$id.'" ) and is_confirmed_shipping=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from sh_i where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from acceptance where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from trust where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from payment where user_confirm_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		return $can_delete;
	}
	
	
	
	//массив подчиненных
	public function GetSubsArr($id){
		$arr=array();	
		$sql='select u.*,   u.id user_id from '.$this->tablename.'  as u
		  
		
	 	where u.group_id="1"  and u.manager_id="'.$id.'" order by u.name_s asc';
	 
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		 
		 
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$f['hash']=md5($f['id']);
				
				$arr[]=$f;
			}
		 
		
	 
		
		return $arr;
	}
	
	public function AddSubs($current_id, array $positions){
		 
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetSubsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$this->GetItemByFields(array('id'=> $v['user_id'], 'manager_id'=>$current_id)) ;// $_kpi->GetItemByFields(array('user_id'=>$v['user_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				 
				$add_array['user_id']=$v['user_id'];
				
				 
			 
				
				 
				//$_kpi->Add($add_array);
				$this->Edit($v['user_id'], array('manager_id'=>$current_id));
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					 
					'user_id'=>$v['user_id'],
					 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				 
				$add_array['user_id']=$v['user_id'];
				
				 
				
				 
				$this->Edit($v['user_id'], array('manager_id'=>$current_id));
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				//if($kpi['manager_id']!=$add_array['right_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'user_id'=>$v['user_id'] 
					 
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['user_id']==$v['user_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
				
					'user_id'=>$v['user_id']
				
			);
			
			//удаляем позицию
			//$_kpi->Del($v['id']);
			$this->Edit($v['user_id'], array('manager_id'=>0));
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
}
?>