<?
require_once('abstractgroup.php');

// абстрактная группа
class SupplierContactDataGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->pagename='view.php';		
		$this->subkeyname='contact_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		$set=new MysqlSet('select p.*,
			pc.name as pc_name, pc.icon as pc_icon
		
		 from '.$this->tablename.' as p left join supplier_contact_kind as pc on pc.id=p.kind_id
		  where p.'.$this->subkeyname.'="'.$id.'" order by p.id asc');
		  
		$_email_kinds=array(5);
		$_phone_kinds=array(1,3,4);
		$was_email=false;
		$was_phone=false;  
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if(!$was_email&&in_array($f['kind_id'],$_email_kinds)){
				$f['first_email']=true;
				$was_email=true;	
			}else $f['first_email']=false;
			
			if(!$was_phone&&in_array($f['kind_id'],$_phone_kinds)){
				$f['first_phone']=true;
				$was_phone=true;	
			}else  $f['first_phone']=false;
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}
?>