<?
require_once('abstractgroup.php');


//  группа  categoriy
class PosGroupGroup extends AbstractGroup {
	

	//установка всех имен
	protected function init(){
		$this->tablename='catalog_group';
		$this->pagename='view.php';		
		$this->subkeyname='parent_group_id';	
		$this->vis_name='is_shown';		
		$this->keyname='id';	
		
		
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0){
		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'=0 order by name asc, id asc');
		
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
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->subkeyname.'<>0 order by name asc, id asc');
		
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
	
	
	//список всех трех уровней
	public function GetItemsTreeArr($current_id=0){
		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'=0 order by name asc, id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f;
			$set1=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$f['id'].'" order by name asc, id asc');
		
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			for($j=0; $j<$rc1; $j++){
				$f1=mysqli_fetch_array($rs1);
				$f1['is_current']=(bool)($f1['id']==$current_id);
				foreach($f1 as $k1=>$v1) $f1[$k1]=stripslashes($v1);
				$f1['name']='&nbsp;&nbsp;'.$f1['name'];
				
				$arr[]=$f1;
				$set2=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$f1['id'].'" order by name asc, id asc');
		
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($k=0; $k<$rc2; $k++){
					$f2=mysqli_fetch_array($rs2);
					$f2['is_current']=(bool)($f2['id']==$current_id);
					foreach($f2 as $k2=>$v2) $f2[$k2]=stripslashes($v2);
					$f2['name']='&nbsp;&nbsp;&nbsp;&nbsp;'.$f2['name'];
					
					$arr[]=$f2;
					
				}
				
				
			}
			
			
		}
		
		return $arr;
	}	
	
}
?>