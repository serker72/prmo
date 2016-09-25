<?
require_once('abstractgroup.php');

// абстрактная группа
class BDetailsGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='banking_details';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=Array();
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'="1" order by id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($current_id==0){
				$f['is_current']=(bool)($f['is_basic']==1);
			}else $f['is_current']=(bool)($f['id']==$current_id);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>