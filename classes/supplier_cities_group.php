<?

require_once('abstractgroup.php');

// абстрактная группа
class SupplierCitiesGroup extends AbstractGroup {
	protected $tablename;//='mmenu';
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_sprav_city';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	
	
	
	
	//список позиций
	public function GetItemsByIdTemplate($id, $template, $is_ajax=false){
		
		$txt='';
		$arr=$this->GetItemsByIdArr($id, $current_id, $is_shown);
		if($is_ajax) $sm=new SmartyAj();
		else $sm=new SmartyAdm;
		
		$sm->assign('items', $arr);
		$sm->assign('id', $id);
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		
		$sql='select ct.*, c.name as name, r.name as region_name, o.name as okrug_name, 
			sc.name as country_name,
		c.id as city_id
		
		 from '.$this->tablename.' as ct 
		 left join sprav_city as c on ct.city_id=c.id
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on sc.id=c.country_id
		
		where ct.supplier_id="'.$id.'" order by c.name asc, r.name asc, o.name asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);

			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['is_current']=($f['city_id']==$current_id);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
}
?>