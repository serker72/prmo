<?
require_once('abstractgroup.php');

// абстрактная группа
class SupplierPhoneGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_phone';
		$this->pagename='view.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=Array();
		 $set=new MysqlSet('select p.*, u.name as form_name from '.$this->tablename.' as p left join supplier_phone_kind as u on p.kind_id=u.id where p.'.$this->subkeyname.'="'.$id.'" order by p.id asc');
		
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
	public function GetFormsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		$set=new MysqlSet('select * from supplier_phone_kind order by  id asc');
		
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