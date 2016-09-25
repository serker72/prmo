<?
require_once('abstractitem.php');

//������ ������������
class LoginCreationSession extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='login_creation_session';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		//$this->ClearOldSessions();	
	}
	
	//������� ����� ������� �� ���� �������, ����� ������� 
	public function CountLogins($login,$user_id){
		$this->DelSession($user_id);
		
		$set=new mysqlSet('select count(*) from '.$this->tablename.' where login="'.$login.'" and user_id<>"'.$user_id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);	
		
		return $f[0];
	}
	
	
	public function AddSession($user_id, $login){
		$this->Add(array('user_id'=>$user_id, 'login'=>$login, 'pdate'=>time(), 'SID'=>session_id(), 'ip'=>getenv('REMOTE_ADDR')));
	}
	
	
	//������ ������
	public function DelSession($user_id){
		new NonSet('delete from '.$this->tablename.' where user_id="'.$user_id.'"');
	}
	
	
	/*//���������� ������ ������������� �� �����
	public function GetUsersOnline($usermode){
		$arr=array();
		
		switch($usermode){
			case self::Ss:
				$sql='select distinct us.id, u.name_s, u.login, u.name_d, u.group_id from user_session as us inner join user as u on us.user_id=u.id order by u.group_id asc, u.login asc';
			break;
			case self::S:
				$sql='select distinct us.id, u.name_s, u.login, u.name_d, u.group_id from user_session as us inner join user as u on us.user_id=u.id order by u.group_id asc, u.login asc';
			break;
			case self::D:
				$sql='select distinct us.id, u.name_s, u.login, u.name_d, u.group_id from user_session as us inner join user as u on us.user_id=u.id where u.group_id=1 order by u.group_id asc, u.login asc';
			break;
			default: //as D
				$sql='select distinct us.id, u.name_s, u.login, u.name_d, u.group_id from user_session as us inner join user as u on us.user_id=u.id where u.group_id=1 order by u.group_id asc, u.login asc';
			break;
		};
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f;
		}
		return $arr;
	}*/
	
	
	
	//��������, ���� �� ������� ������. ��������� ������� ��� ������ �����
	/*public function UpdateSession($user_id){
		$ses=$this->GetItemByFields(array('user_id'=>$user_id,'ip'=>getenv('REMOTE_ADDR'),'sid'=>session_id()));
		if($ses!==false){
			//����
			$this->Edit($ses['id'],array('ttime'=>time(),'ip'=>getenv('REMOTE_ADDR')));	
		}else{
			//���
			$this->DelSession($user_id);
			$this->AddSession($user_id);
		}
	}
	
	
	//������ user_id, time, sid
	protected function AddSession($user_id){
		$this->Add(array('user_id'=>$user_id, 'ttime'=>time(), 'sid'=>session_id(), 'ip'=>getenv('REMOTE_ADDR')));
	}
	*/
	
	//������� ������ ������
	public function ClearOldSessions($time_interval=900){
		//���� ����� �� ������� ����� ���� ����� - �������� ������� ������!
		$etalon=time()-(2*60*60);
		$set=new mysqlSet('select count(*) from '.$this->tablename.' where pdate>='.$etalon);
		$rs=$set->getResult(); 
		$f=mysqli_fetch_array($rs);
		if($f[0]==0){
			new NonSet('truncate '.$this->tablename);	
		}
		
		
		//������� ������, �� ����������� 15 ����� � �����
		new NonSet('delete from '.$this->tablename.' where pdate<'.(time()-$time_interval).'');	
	}
	
}
?>