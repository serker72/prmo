<?
require_once('abstractitem.php');
 
require_once('user_s_item.php');
require_once('authuser.php');
require_once('actionlog.php');


//класс сообщение мессенджера
class MessengerItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='messenger_message';
		
	}
	
	public function SetRead($id, $message_item=NULL, $result=NULL){
		$au=new AuthUser;
		$log=new ActionLog;
		
		if($message_item===NULL) $message_item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		//проверить, а было ли оно вообше не прочитано.
		
		
		if(($message_item['unread']==1)&&($message_item['from_id']>0)){
			//было прочитано
			$log->PutEntry($result['id'],'получил мгновенное сообщение',$message_item['from_id'],NULL,NULL,'Текст сообщения: '.SecStr($message_item['txt']),$id);
		}
		
		
		
		//если было - занести запись в журнал
		
		$ns=new NonSet('update '.$this->tablename.' set unread=0 where id="'.$id.'" ');	
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
					 <div><em>Данное сообщение сгенерировано автоматически.</em></div>
						  <div>Уважаемый пользователь!</div>
						  <div>Вы отправили сообщение сотруднику: ".stripslashes($__ui['name_s'])." (".$__ui['login'].") </div>
						  <div>В настоящее время данный сотрудник находится в отпуске до ".date("d.m.Y",$__ui['vacation_till_pdate']).".</div>
						  <div>Пожалуйста, обратитесь к другому сотруднику.</div>
					
					";
					
					$this->Send(0,0,array('from_id'=>-1, 'to_id'=>$params['from_id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>$_txt, 'topic'=>'Сотрудник '.stripslashes($__ui['name_s'])." (".$__ui['login'].') в отпуске до '.date("d.m.Y",$__ui['vacation_till_pdate'])),false);	
				}
			}
			
		}else{
			 
			$message_id=$this->Add($params);
			 
			
		}
		 
		
		return $message_id;
	}
	
	

	//удалить
	public function Del($id){
		//удалять ВСЕ!!!!
		
		//из средств связи
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
}
?>