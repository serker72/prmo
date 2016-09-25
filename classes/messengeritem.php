<?
require_once('abstractitem.php');
 
require_once('user_s_item.php');
require_once('authuser.php');
require_once('actionlog.php');


//����� ��������� �����������
class MessengerItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='messenger_message';
		
	}
	
	public function SetRead($id, $message_item=NULL, $result=NULL){
		$au=new AuthUser;
		$log=new ActionLog;
		
		if($message_item===NULL) $message_item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		//���������, � ���� �� ��� ������ �� ���������.
		
		
		if(($message_item['unread']==1)&&($message_item['from_id']>0)){
			//���� ���������
			$log->PutEntry($result['id'],'������� ���������� ���������',$message_item['from_id'],NULL,NULL,'����� ���������: '.SecStr($message_item['txt']),$id);
		}
		
		
		
		//���� ���� - ������� ������ � ������
		
		$ns=new NonSet('update '.$this->tablename.' set unread=0 where id="'.$id.'" ');	
	}

	//��������
	public function Send($message_id, $user_id, $params, $do_check_vacation=true){
		if($message_id==0){
			//������� ������
			$message_id=$this->Add($params);
			
			//�������� ������������, ���� ������������ � �������
			if(($do_check_vacation)&&isset($params['from_id'])&&($params['from_id']>0)&&isset($params['to_id'])){
				$_ui=new UserSItem;
				$__ui=$_ui->getitembyid($params['to_id']);
				if(($__ui!==false)&&($__ui['is_in_vacation']==1)&&(($__ui['vacation_till_pdate']+24*60*60)>time())){
					//� �������
					
					$_txt="
					 <div><em>������ ��������� ������������� �������������.</em></div>
						  <div>��������� ������������!</div>
						  <div>�� ��������� ��������� ����������: ".stripslashes($__ui['name_s'])." (".$__ui['login'].") </div>
						  <div>� ��������� ����� ������ ��������� ��������� � ������� �� ".date("d.m.Y",$__ui['vacation_till_pdate']).".</div>
						  <div>����������, ���������� � ������� ����������.</div>
					
					";
					
					$this->Send(0,0,array('from_id'=>-1, 'to_id'=>$params['from_id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>$_txt, 'topic'=>'��������� '.stripslashes($__ui['name_s'])." (".$__ui['login'].') � ������� �� '.date("d.m.Y",$__ui['vacation_till_pdate'])),false);	
				}
			}
			
		}else{
			 
			$message_id=$this->Add($params);
			 
			
		}
		 
		
		return $message_id;
	}
	
	

	//�������
	public function Del($id){
		//������� ���!!!!
		
		//�� ������� �����
		$query = 'delete from '.$this->mf_tablename.' where message_id='.$id.';';
		$it=new nonSet($query);
		
		AbstractItem::Del($id);
	}	
	
	//��������� ������� ����� �� ������ �����
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