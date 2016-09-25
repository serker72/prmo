<?
require_once('abstractgroup.php');
require_once('suppliercontactdatagroup.php');

//контакты контрагента
class SupplierContactGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact';
		$this->pagename='view.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0, $can_edit_spec=false){
		$arr=array();
		
                // KSK - 24.03.2016
                // добавляем условие выбора записей для отображения is_shown=1
		//$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by name asc, id asc');
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'=1 order by name asc, id asc');
		
		$_email_kinds=array(5);
		$_phone_kinds=array(1,3,4);
		$was_email=false;
		$was_phone=false;  
		
		
		$_sdg=new SupplierContactDataGroup;
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($f['birthdate']!="") $f['birthdate']=date('d.m.Y', $f['birthdate']);
			
			
			
			$f['data']=$_sdg->GetItemsByIdArr($f['id']);
			
			$f['first_email']=false;
			$f['first_phone']=false;
			
			foreach($f['data'] as $k=>$v){
				if(!$was_email&&in_array($v['kind_id'],$_email_kinds)){
					$f['first_email']=true;
					$was_email=true;	
				}
				
				if(!$was_phone&&in_array($v['kind_id'],$_phone_kinds)){
					$f['first_phone']=true;
					$was_phone=true;	
				}
			}
			
			$f['can_edit_spec']=$can_edit_spec;
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
}
?>