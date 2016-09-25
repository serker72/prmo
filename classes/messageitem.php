<?
require_once('abstractitem.php');
require_once('filemessagegroup.php');
require_once('user_s_item.php');
require_once('authuser.php');
require_once('actionlog.php');


//класс сообщение
class MessageItem extends AbstractItem{
	protected $mf_tablename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='message';
		$this->mf_tablename='message_in_folder';
	}
	
	public function SetRead($id, $message_item=NULL, $result=NULL){
		$au=new AuthUser;
		$log=new ActionLog;
		
		if($message_item===NULL) $message_item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		//проверить, а было ли оно вообше не прочитано.
		
		$set=new mysqlset('select count(*) from '.$this->mf_tablename.' where unread=1 and message_id="'.$id.'" and folder_id=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		if(((int)$f[0]>0)&&($message_item['from_id']>0)){
			//было прочитано
			$log->PutEntry($result['id'],'получил сообщение',$message_item['from_id'],NULL,NULL,'“ема сообщени€: '.SecStr($message_item['topic']).' <br>“екст сообщени€: '.SecStr($message_item['txt']),$id);
		}
		
		//внести дату прочтени€
		if(((int)$f[0]>0)){
			
			$set=new mysqlset('select * from '.$this->mf_tablename.' where unread=1 and message_id="'.$id.'" and folder_id=1');
			$rs=$set->GetResult();	
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$g=mysqli_fetch_array($rs);
				
				if($g['unread']==1){
					$ns=new NonSet('update '.$this->mf_tablename.' set read_pdate="'.time().'" where id="'.$g['id'].'" ');	
				}
			}
		}
		
		//если было - занести запись в журнал
		
		$ns=new NonSet('update '.$this->mf_tablename.' set unread=0 where message_id="'.$id.'" and folder_id=1');	
	}

	//отправка
	public function Send($message_id, $user_id, $params, $do_check_vacation=true){
		if($message_id==0){
			//создаем письмо
			$message_id=$this->Add($params);
			
			//выполним авторассылку, если пользователь в отпуске
			if(($do_check_vacation)&&isset($params['from_id'])&&($params['from_id']>0)&&isset($params['to_id'])){
				$_ui=new UserSItem;
				$__ui=$_ui->getitembyid($params['to_id']);
				if(($__ui!==false)&&($__ui['is_in_vacation']==1)&&(($__ui['vacation_till_pdate']+24*60*60)>time())){
					//в отпуске
					
					$_txt="
					 <div><em>ƒанное сообщение сгенерировано автоматически.</em></div>
						  <div>”важаемый пользователь!</div>
						  <div>¬ы отправили сообщение сотруднику: ".stripslashes($__ui['name_s'])." (".$__ui['login'].") </div>
						  <div>¬ насто€щее врем€ данный сотрудник находитс€ в отпуске до ".date("d.m.Y",$__ui['vacation_till_pdate']).".</div>
						  <div>ѕожалуйста, обратитесь к другому сотруднику.</div>
					
					";
					
					$this->Send(0,0,array('from_id'=>-1, 'to_id'=>$params['from_id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>$_txt, 'topic'=>'—отрудник '.stripslashes($__ui['name_s'])." (".$__ui['login'].') в отпуске до '.date("d.m.Y",$__ui['vacation_till_pdate'])),false);	
				}
			}
			
		}else{
			//проверим, принадлежит ли юзеру письмо и есть ли оно в неотправленных
			$mess=$this->GetItemByFields(Array('id'=>$message_id, 'from_id'=>$user_id), Array('folder_id'=>3));
			//если нет, то создаем новое
			if($mess==false)
				$message_id=$this->Add($params);
			else
				$this->Edit($message_id, $params);
			
			
		}
		//кладем его во вход€щие
		$this->PutToFolder($message_id, 1, Array('pdate'=>time(), 'unread'=>1));
		//кладем его в отправленные
		$this->PutToFolder($message_id, 2, Array('pdate'=>time(), 'unread'=>1));
		//удалим из неотправленных
		$this->DelFromFolder($message_id, 3);
		
		return $message_id;
	}
	
	public function ToDraft($message_id, $user_id, $params){
		if($message_id==0){
			//создаем письмо
			$message_id=$this->Add($params);
			
		}else{
			//проверим, принадлежит ли юзеру письмо и есть ли оно в неотправленных
			$mess=$this->GetItemByFields(Array('id'=>$message_id, 'from_id'=>$user_id), Array('folder_id'=>3));
			//если нет, то создаем новое
			if($mess==false)
				$message_id=$this->Add($params);
			else
				$this->Edit($message_id, $params);
			
		}
		//кладем его в неотправленные
		$this->PutToFolder($message_id, 3, Array('pdate'=>time(), 'unread'=>1));
		
	}
	
	//помещение письма в заданную папку
	public function PutToFolder($message_id, $folder_id, $params){
		//если такое письмо уже есть в такой папке- удалим
		$this->DelFromFolder($message_id, $folder_id);
		
		$qq1=''; $qq2='';
		foreach($params as $k=>$v){
			$qq1.=', '.$k;
			$qq2.=', "'.$v.'"';	
		}
		
		$ns=new NonSet('insert into '.$this->mf_tablename.' (folder_id, message_id'.$qq1.') values("'.$folder_id.'", "'.$message_id.'"'.$qq2.')'); 
	}
	
	//удаление письма из заданной папки
	public function DelFromFolder($message_id, $folder_id){
		//если такое письмо уже есть в такой папке- удалим
		$ns=new NonSet('delete from '.$this->mf_tablename.' where folder_id="'.$folder_id.'" and message_id="'.$message_id.'"');
	}
	
	//удаление писем пользовател€ вне папок
	public function DropMissMessages($user_id){
		//удалить вложени€
		$ns=new NonSet('delete from message_file where message_id in(from '.$this->tablename.' where (to_id="'.$user_id.'" or from_id="'.$user_id.'") and id not in(select distinct message_id from '.$this->mf_tablename.' ))');
		$ns=new NonSet('delete from '.$this->tablename.' where (to_id="'.$user_id.'" or from_id="'.$user_id.'") and id not in(select distinct message_id from '.$this->mf_tablename.' )');
		
		
	/*	if($folder_id==1){
			
		}else{
			$ns=new NonSet('delete from '.$this->tablename.' where from_id="'.$user_id.'" and id not in(select distinct message_id from '.$this->mf_tablename.' )');
		}
		*/
		
	}
	
	

	//удалить
	public function Del($id){
		//удал€ть ¬—≈!!!!
		
		//из средств св€зи
		$query = 'delete from '.$this->mf_tablename.' where message_id='.$id.';';
		$it=new nonSet($query);
		
		AbstractItem::Del($id);
	}	
	
	//получение первого итема по набору полей
	public function GetItemByFields($params, $extra=NULL){
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.='t.'.$key.'="'.$val.'" ';
			else $qq.=' and t.'.$key.'="'.$val.'" ';
		}
		
		if($extra===NULL) 
			$item=new mysqlSet('select * from '.$this->tablename.' as t  where '.$qq.';');
		else{
			
			foreach($extra as $key=>$val){
				if($qq=='') $qq.='tf.'.$key.'="'.$val.'" ';
				else $qq.=' and tf.'.$key.'="'.$val.'" ';
			}	
			
			$item=new mysqlSet('select * from '.$this->tablename.' as t inner join '.$this->mf_tablename.' as tf on (t.id=tf.message_id)  where '.$qq.';');
		}
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= Array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
			$this->item=NULL;
			return false;
		}	
		
	}
	
	
	
	//найти список пользователей - получателей заданного сообщени€
	public function FindRecepients($message_id, &$message=NULL){
		if($message===NULL) $message=$this->GetItemById($message_id);
		
		$arr=array();
		$user_ids=array();
		$user_ids[]=$message['to_id'];
		
		$sql='select distinct to_id from '.$this->tablename.' where 
		pdate="'.$message['pdate'].'" and
		topic="'.SecStr($message['topic']).'" and
		txt="'.SecStr($message['txt']).'" and
		from_id="'.$message['from_id'].'" and
		to_id<>"'.$message['to_id'].'"';
		
		$set=new mysqlset($sql);
		
		$result=$set->getResult();
		$rc=$set->getResultNumRows();
		 
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($result);	
			if(!in_array($f[0], $user_ids)) $user_ids[]=$f[0];
		}
		
		$sql='select * from user where is_active=1 and id in('.implode(', ',$user_ids).') order by name_s';
		$set=new mysqlset($sql);
		
		$result=$set->getResult();
		$rc=$set->getResultNumRows();
		 
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($result);	
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//найти список пар пользователь - айди сообщени€ -получателей заданного сообщени€
	public function FindRecepientsWithMesIds($message_id,  &$message=NULL){
		$arr=array();
		if($message===NULL) $message=$this->GetItemById($message_id);
		
		
		$sql='select to_id, id from '.$this->tablename.' where 
		pdate="'.$message['pdate'].'" and
		topic="'.SecStr($message['topic']).'" and
		txt="'.SecStr($message['txt']).'" and
		from_id="'.$message['from_id'].'" and
		to_id<>"'.$message['to_id'].'"';
		
		$set=new mysqlset($sql);
		
		$result=$set->getResult();
		$rc=$set->getResultNumRows();
		 
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($result);	
			
			
			$arr[]=array('user_id'=>$f['to_id'],
						'message_id'=>$f['id']);
		}
		
		return $arr;
	}
}
?>