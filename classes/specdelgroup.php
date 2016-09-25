<?
require_once('NonSet.php');
require_once('abstractgroup.php');

// абстрактная группа
class SpecDelGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='spec_delivery';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArr(){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		$set=new MysqlSet('select u.*, ua.user_id as user_id from user as u left join  '.$this->tablename.' as ua  on ua.user_id=u.id where  u.group_id<>2 and u.is_active=1 order by u.login asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//список позиций
	public function GetItemsInArr(){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		$set=new MysqlSet('select distinct user_id from '.$this->tablename.' where user_id in(select id from user where is_active=1)');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//список позиций
	/*public function GetNotInItemsArr(){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		$set=new MysqlSet('select u.* from user as u where id not in(select distinct user_id from '.$this->tablename.') u.group_id=1 or u.group_id=2 order by u.login asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}*/
	
}
?>