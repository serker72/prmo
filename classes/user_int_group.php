<?
require_once('abstractgroup.php');

// абстрактная группа
class UserIntGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='user_work_intervals';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GetItemsByIdArr($id){
		$arr=array();
		$set=new MysqlSet('select p.*, 
		u.name_s as user_name_s, u.login as user_login 
		from '.$this->tablename.' as p 
		left join user as u on p.posted_user_id=u.id 
		where p.'.$this->subkeyname.'="'.$id.'" order by p.time_from_h_s, p.pdate desc, p.id desc');
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>