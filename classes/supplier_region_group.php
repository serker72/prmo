<?

require_once('abstractgroup.php');

// абстрактная группа
class SupplierRegionGroup extends AbstractGroup {
	protected $tablename;//='mmenu';
	
	//установка всех имен
	protected function init(){
		$this->tablename='sprav_region';
		$this->pagename='view.php';		
		$this->subkeyname='district_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $country_id, $current_id=0, $is_shown=0){
		$arr=array();
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and country_id="'.$country_id.'" order by name asc, id asc');
		
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
	
	
	//итемы в тегах option
	public function GetItemsOptById($id, $country_id, $current_id=0,$fieldname='name', $do_no=false, $no_caption='-выберите-'){
		$txt='';
		$sql='select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and country_id="'.$country_id.'" order by '.$fieldname.' asc';
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
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname]))."</option>";
			}
		}
		return $txt;
	}
	
}
?>