<?

require_once('abstractitem.php');

//город в справочнике
class SupplierCityItem extends AbstractItem{

	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sprav_city';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	public function GetFullCity($id){
	 
	 
			$sql='select c.*, r.name as region_name, o.name as okrug_name, sc.name as country_name
		
		 from '.$this->tablename.' as c
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on c.country_id=sc.id
		
		where c.id="'.$id.'"';
		
		//echo $sql; 
		 
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			
			$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
				if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
			
			$result=$f;
		}else $result=false;
		 
		return $result;
	}
}
?>