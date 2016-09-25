<?
require_once('abstractgroup.php');

//группа фактических адресов
class FaGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='fact_address';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		 
		$set=new MysqlSet('select a.*,
		
		 c.name as name, r.name as region_name, o.name as okrug_name, 
		 sc.name as country_name,
		 
		 fa.name as form_name
		 
		 from '.$this->tablename.' as a
		 
		 left join fact_address_form as fa on fa.id=a.form_id
		
		 left join sprav_city as c on a.city_id=c.id
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on sc.id=c.country_id
		
		 where '.$this->subkeyname.'="'.$id.'" order by id asc');
		 
		 
		 
		 
		 
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			
			$f['current_id']=$current_id;
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
				$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
				if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
			
			//$f['address']=nl2br($f['address']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//список позиций
	public function GetFormsArr($current_id=0,  $is_shown=0){
		$arr=array();
		
		
		return $arr;
	}
	
}


//группа форм фактических адресов
class FaFormGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='fact_address_form';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	 
	
}
?>