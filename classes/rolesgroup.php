<?
require_once('abstractgroup.php');

// абстрактная группа
class RolesGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='groups';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0, $can_admin=true){
		$arr=Array();
		
		if($can_admin) $set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where id<>1 order by id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
			
		}
		
		return $arr;
	}
	
	
	
}
?>