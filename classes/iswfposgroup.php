<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');

// абстрактная группа
class IsWfPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='interstore_wf_position';
		$this->pagename='view.php';		
		$this->subkeyname='iwf_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=Array();
		
		//$_bpf=new BillPosPMFormer;
		$sql='select p.id as p_id, p.iwf_id, p.komplekt_ved_pos_id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price,
					 pd.id as dimension_id		 
		
		from '.$this->tablename.' as p 
			
			left join catalog_dimension as pd on pd.name=p.dimension
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, id asc';
		$set=new MysqlSet($sql);
		
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
	
	public function FactKol($is_id,$position_id){
		$set1=new mysqlSet('select sum(quantity) from interstore_wf_position 
			where position_id="'.$position_id.'" and iwf_id in(select id from interstore_wf where interstore_id="'.$is_id.'")');
			
			$rs1=$set1->GetResult();
			
			$g=mysqli_fetch_array($rs1);
			return (float)$g[0];
			
	}
	
}
?>