<?

require_once('abstractgroup.php');

// абстрактная группа
class SupplierCityGroup extends AbstractGroup {
	protected $tablename;//='mmenu';
	protected $subkeyname2;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sprav_city';
		$this->pagename='view.php';		
		$this->subkeyname='district_id';	
		$this->subkeyname2='region_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($name, $id1, $id2, $id3, $current_id=0, $is_shown=0){
		$arr=array();
		
		//$sql='select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by name asc, id asc';
		
			$sql='select c.*, r.name as region_name, o.name as okrug_name, sc.name as country_name
		
		 from '.$this->tablename.' as c
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on c.country_id=sc.id
		
		where ';
		
			$sql.=' c.name LIKE "%'.$name.'%" ';
		if($id1!=0) $sql.=' and c.district_id="'.$id1.'" ';
		if($id2!=0) $sql.=' and c.region_id="'.$id2.'" ';
		if($id3!=0) $sql.=' and c.country_id="'.$id3.'" ';
		
		$sql.=' order by c.name asc, r.name asc, o.name asc, id asc';
		//ct.supplier_id="'.$id.'" order by c.name asc, r.name asc, o.name asc';
		
		//echo $sql;
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
				if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
			
			//	$f['fullname'].=$sql;
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//итемы в тегах option
	public function GetItemsOptById($name, $id1, $id2, $id3, $current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-'){
		$txt='';
		//$sql='select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by '.$fieldname.' asc';
		
			$sql='select c.*, r.name as region_name, o.name as okrug_name, sc.name as country_name
		
		 from '.$this->tablename.' as c
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		left join sprav_country as sc on c.country_id=sc.id
		
		
		where ';
		
		$sql.=' c.name LIKE "%'.$name.'%" ';
		if($id1!=0) $sql.=' and c.district_id="'.$id1.'" ';
		if($id2!=0) $sql.=' and c.region_id="'.$id2.'" ';
		if($id3!=0) $sql.=' and c.country_id="'.$id3.'" ';
		
		$sql.=' order by '.$fieldname.' asc';
		
		
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"".$f['id']."\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
			if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
				
				
				$txt.=">".htmlspecialchars(stripslashes($f['fullname']))."</option>";
			}
		}
		return $txt;
	}
	
	
}
?>