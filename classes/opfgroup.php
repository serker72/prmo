<?
require_once('NonSet.php');
require_once('abstractgroup.php');

// абстрактная группа
class OpfGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='opf';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by ord desc, id asc');
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