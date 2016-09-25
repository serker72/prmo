<?
require_once('abstractitem.php');
require_once('actionlog.php');

//сессия пользователя
class UserSession extends AbstractItem{
	const Ss=1;
	const S=2;
	const D=3;
	
	//установка всех имен
	protected function init(){
		$this->tablename='user_session';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		//$this->ClearOldSessions();	
	}
	
	//сформируем список пользователей на сайте
	public function GetUsersOnline($usermode){
		$arr=array();
		
		
				$sql='select distinct us.id, u.name_s, u.position_s as position, u.login, u.group_id, g.name  from user_session as us inner join user as u on us.user_id=u.id left join groups as g on u.group_id=g.id order by  u.login asc, u.group_id asc';
			
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//print_r($f);
			$arr[]=$f;
		}
		return $arr;
	}
	
	//удалим сессии
	public function DelSession($user_id){
		new NonSet('delete from '.$this->tablename.' where user_id="'.$user_id.'"');
	}
	
	//проверка, есть ли текущая сессия. продление текущей или запись новой
	public function UpdateSession($user_id){
		$ses=$this->GetItemByFields(array('user_id'=>$user_id,'ip'=>getenv('REMOTE_ADDR'),'sid'=>session_id()));
		if($ses!==false){
			//есть
			$this->Edit($ses['id'],array('ttime'=>time(),'ip'=>getenv('REMOTE_ADDR')));	
		}else{
			//нет
			$this->DelSession($user_id);
			$this->AddSession($user_id);
		}
	}
	
	
	//запись user_id, time, sid
	protected function AddSession($user_id){
		$this->Add(array('user_id'=>$user_id, 'ttime'=>time(), 'sid'=>session_id(), 'ip'=>getenv('REMOTE_ADDR')));
	}
	
	//очистка старых сессий
	public function ClearOldSessions($time_interval=3900){
		//если никто не заходил более 4 часов - очистить таблицу сессий!
		
		$log=new ActionLog;
		
		$etalon=time()-($time_interval-60);
		$set=new mysqlSet('select count(*) from '.$this->tablename.' where ttime>='.$etalon);
		$rs=$set->getResult(); 
		$f=mysqli_fetch_array($rs);
		if($f[0]==0){
			//
			$set1=new mysqlSet('select * from '.$this->tablename.' ');
			$rs1=$set1->getResult(); 
			$rc1=$set1->GetResultNumRows();
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1);
				
				$log->PutEntry($g['user_id'], "выход из системы",NULL,NULL,NULL,'выход из системы произведен автоматически по истечении '.($time_interval/60).' мин. бездействия');	
				
			}
			new NonSet('truncate '.$this->tablename);	
		}
		
		
		//очистим сессии, не обновляемые 4 4asa и более
		//new NonSet('delete from '.$this->tablename.' where ttime<'.(time()-$time_interval).'');
		$set=new mysqlSet('select * from '.$this->tablename.' where ttime<'.(time()-$time_interval).'');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($f['user_id'], "выход из системы",NULL,NULL,NULL,'выход из системы произведен автоматически по истечении '.($time_interval/60).' мин. бездействия');	
			new NonSet('delete from '.$this->tablename.' where id="'.$f['id'].'"');
		}
			
	}
	
}
?>