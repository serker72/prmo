<?

require_once('abstractitem.php');

//абстрактный элемент
class SupplierCitiesItem extends AbstractItem{

	public function __construct(){
		$this->init();
	}
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_sprav_city';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	public function GetOne($id){
		$sql='select t.*, r.name as region_name, d.name as okrug_name from sprav_city as t inner join supplier_sprav_city as ct on t.id=ct.city_id
		left join sprav_region as r on t.region_id=r.id
		left join sprav_district as d on t.district_id=d.id
		
		
		 where ct.supplier_id="'.$id.'" order by t.name asc limit 1';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		if($rc>0){
			return mysqli_fetch_array($rs);	
			
		}else return false;
		
	}

}
?>