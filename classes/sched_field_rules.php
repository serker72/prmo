<?

class Sched_FieldRules{
	protected $table;
	
	public static $_viewed_ids;
	
	
	
	//основной метод, работает везде!
	public function GetFieldsRoles(array $plan, $user_id, $current_status_id=NULL){
		
		//$user_id - администратор. как это обработать???
		/*нужно получить список подчиненных пользователей
		если супердоступ есть - то и текущий пользователь среди них, т.е. на любую свою задачу распространится супердоступ администратора
		иначе - только ДРУГИЕ подчиненные пользователи
		на задачи этих подчиненных пользователей распространяется роль 1 - постановщик
		если эта задача относится к задачам подчиненных пользователей (так или иначе,
		по любой роли)
		Иначе - по общей логике
		 -НЕВЕРНО, получается в роли постановщика
		
		
		ВЕРНО:
		
		-админ - т.е. сам есть в списке своих подчиненных - роль 1
		-по общей логике - роль по задаче (в порядке возрастанния номера роли)
		-подчиненные пол-ли - по роли подчиненного пользователя (в порядке возрастания номера роли)
				
		*/
		
		if(in_array($user_id, self::$_viewed_ids)){
		
			$role_id=1;
			//echo "Задача ".$plan['code']." - доступ СУПЕР, роль 1<br>";
		}
		else{
			
			$sql='select distinct kind_id from  sched_task_users where  	sched_id="'.$plan['id'].'" and user_id="'.$user_id.'" order by kind_id asc';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			if($rc>0) 
			{	
				$f=mysqli_fetch_array($rs);
				$role_id=(int)$f[0];
				
				//echo "Задача ".$plan['code']." - доступ согласно полям задачи, роль $role_id<br>";
				 
			}else{
			
			
				$sql='select * from  sched_task_users where  sched_id="'.$plan['id'].'" and user_id in ('.implode(', ', self::$_viewed_ids).') order by kind_id asc';
				//echo $sql;
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				
				if((int)$rc>0){
					 
					$f=mysqli_fetch_array($rs);
					$role_id=$f['kind_id'];
					//echo "Задача ".$plan['code']." - доступ согласно подчиненным сотруднкам,  роль $role_id<br>";
				}
			}
		}
		
		//$viewed_ids=$_plans->GetAvailableUserIds($result['id']);
		
		//var_dump(self::$_viewed_ids);
		/*$sql='select count(*)  from  sched_task_users where  sched_id="'.$plan['id'].'" and user_id in ('.implode(', ', self::$_viewed_ids).') ';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$f=mysqli_fetch_array($rs);
		if((int)$f[0]>0){
			//если >0 - то есть супердоступ
			
			$role_id=1;
			//echo "Задача ".$plan['code']." - доступ СУПЕР, роль 1<br>";
		}else{
		
			
			//иначе - проверяем по обычной логике
			
			
			//если все пусто  - то проверяем по обычной логике
			
			$sql='select distinct kind_id from  sched_task_users where  	sched_id="'.$plan['id'].'" and user_id="'.$user_id.'" order by kind_id asc';
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			if($rc>0) 
			{	
				$f=mysqli_fetch_array($rs);
				$role_id=(int)$f[0];
				
				//echo "Задача ".$plan['code']." - доступ согласно полям задачи, роль $role_id<br>";
				 
			}
			
		}*/
		
		//echo $role_id;
		
		if($current_status_id===NULL) $status_id=$plan['status_id'];
		else $status_id=$current_status_id;
		
		
		
		
		
		
		if(isset($this->table[$status_id][$role_id])){
			$result=	$this->table[$status_id][$role_id];
		}else $result=false;
		
		return $result;
	}
	
	
	public function HasAccess(array $plan, $user_id, $fieldname){
		/*
		$status_id --?
		*/
		/*role_id*/
		
		//как найти role_id>???
		
		$sql='select distinct kind_id from  sched_task_users where  	sched_id="'.$plan['id'].'" and user_id="'.$user_id.'" order by kind_id asc';
		
		$set=new mysqlset($sql);
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		if($rc>0) 
		{	
			$f=mysqli_fetch_array($rs);
			$role_id=(int)$f[0];
		}
		
		if(isset($this->table[$plan['status_id']][$role_id][$fieldname])){
			$result=	$this->table[$plan['status_id']][$role_id][$fieldname];
		}else $result=false;
		
		return $result;
	}
	
	
	public function GetTable(){ return $this->table; }
	
	
	
	function __construct($result=NULL){
		
		if($result===NULL){
			$_result=new AuthUser();
			$result=$_result->Auth();	
		}
		
		//заполним массив подчин. пол-лей
		if(self::$_viewed_ids===NULL){
			$_pls=new Sched_Group;
			self::$_viewed_ids=$_pls->GetAvailableUserIds($result['id'], true, 1);	
		}
		
		
		$this->table=array();
		
		//for($i=1; $i<=4; $i++){
			
		$data=array();
		$data[23]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=true;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=true;
$u['can_modify_3']=true;
$u['can_modify_4']=true;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=true;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=true;
$u['can_begin']=true;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;



		 

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=true;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=true;
$u['can_begin']=true;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;





		
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=false;
$u['can_ed_files']=false;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=true;
$u['can_begin']=true;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;



		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;


		
		$status_data[4]=$u;
		
		
			
		$data[23]=$status_data;	
		
		
		
		
/********************************status*************************************************/

$data[18]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=true;
$u['description']=true;
$u['priority']=true;
$u['report']=true;
$u['can_exp_date']=true;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=true;
$u['can_modify_3']=true;
$u['can_modify_4']=true;
$u['can_delegate']=false;
$u['can_confirm']=true;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=true;
$u['can_deannul']=false;
$u['can_do_check']=true;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=true;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;



		 

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=true;
$u['can_modify_4']=false;
$u['can_delegate']=true;
$u['can_confirm']=true;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=true;

$u['can_move_srok']=false;
$u['can_apply_srok']=false;



		
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
	$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=true;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=true;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;




		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;


		
		$status_data[4]=$u;
		
		
			
		$data[18]=$status_data;			
		


/********************************status*************************************************/

$data[24]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=true;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=true;
$u['can_modify_3']=true;
$u['can_modify_4']=true;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=true;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=true;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=true;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=true;
$u['can_apply_srok']=true;

		 

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=true;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=true;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=true;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=true;
$u['can_apply_srok']=false;



		
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=true;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=true;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=true;
$u['can_apply_srok']=false;

		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;


		
		$status_data[4]=$u;
		
		
			
		$data[24]=$status_data;	
		
		
/********************************status*************************************************/

$data[25]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=true;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=true;
$u['can_modify_3']=true;
$u['can_modify_4']=true;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=true;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=true;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		 

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=true;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=true;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=false;
$u['can_apply_srok']=false;


		
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=true;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=true;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=true;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;


		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		
		$status_data[4]=$u;
		
		
			
		$data[25]=$status_data;			
		
/********************************status*************************************************/

$data[10]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=true;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=true;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		 

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=true;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=true;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
	$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=true;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		
		$status_data[4]=$u;
		
		
			
		$data[10]=$status_data;			
		
		
/********************************status*************************************************/

$data[26]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=true;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=true;
$u['can_modify_3']=true;
$u['can_modify_4']=true;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=true;
$u['can_confirm_fulfil']=true;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=true;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
		 
$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=true;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=true;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=true;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;


		
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;
$u['can_move_srok']=false;
$u['can_apply_srok']=false;




		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=true;
$u['can_ed_notes']=true;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

	$u['can_move_srok']=false;
$u['can_apply_srok']=false;
	
		$status_data[4]=$u;
		
		
			
		$data[26]=$status_data;			
		
/********************************status*************************************************/

$data[3]=array();
		
		$status_data[1]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=false;
$u['can_ed_notes']=false;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=true;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

	$u['can_move_srok']=false;
$u['can_apply_srok']=false;
	 

		
		$status_data[1]=$u;
		
		
		
		$status_data[2]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=false;
$u['can_ed_notes']=false;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=true;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

	$u['can_move_srok']=false;
$u['can_apply_srok']=false;
	
		
		$status_data[2]=$u;
		
		
		$status_data[3]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=false;
$u['can_ed_notes']=false;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=true;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

$u['can_move_srok']=false;
$u['can_apply_srok']=false;

		$status_data[3]=$u;
		
		
		
		$status_data[4]=array();
		
		$u=array();
		
		$u['topic']=false;
$u['description']=false;
$u['priority']=false;
$u['report']=false;
$u['can_exp_date']=false;
$u['can_ed_files']=false;
$u['can_ed_notes']=false;
$u['can_modify_2']=false;
$u['can_modify_3']=false;
$u['can_modify_4']=false;
$u['can_delegate']=false;
$u['can_confirm']=false;
$u['can_unconfirm']=false;
$u['can_confirm_done']=false;
$u['can_unconfirm_done']=false;
$u['can_confirm_fulfil']=false;
$u['can_unconfirm_fulfil']=false;
$u['can_defer']=false;
$u['can_begin']=false;
$u['can_annul']=false;
$u['can_deannul']=false;
$u['can_do_check']=false;
$u['can_remake']=false;
$u['can_subtask']=false;
$u['can_stop']=false;
$u['can_modify_suppliers']=false;

	$u['can_move_srok']=false;
$u['can_apply_srok']=false;
	
		$status_data[4]=$u;
		
		
			
		$data[3]=$status_data;			
		
			
			
		$this->table=$data;
		//}
		
		
		
	}
	
}

?>