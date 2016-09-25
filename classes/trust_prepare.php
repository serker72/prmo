<?
require_once('trust_posgroup.php');
require_once('trust_group.php');
require_once('billitem.php');
require_once('maxformer.php');

// класс для редактирования позиций раcпоряжения на основе позиций счета по заданным объектам и участкам
class TrustPrepare extends BillPosGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill_position';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		
		$_bill=new BillItem;
		$bill=$_bill->GetItemById($id);
		$_bpf=new BillPosPMFormer;
		
		$_mf=new MaxFormer;
		
		
		$sql='select sum(p.quantity) as quantity,  p.bill_id,  p.position_id as id, p.position_id as position_id,  
					 p.name as position_name, p.dimension as dim_name, 
					  p.price,
					 pd.id as dimension_id,
					
					 b.id, b.code, b.pdate as bill_pdate			 
		
		from '.$this->tablename.'  as p 
			inner join bill as b on p.bill_id=b.id
			left join catalog_dimension as pd on pd.name=p.dimension
			
		where p.'.$this->subkeyname.'="'.$id.'"
		group by p.position_id
		order by p.name asc
		';
		
	
		//echo $sql.'<br>';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['hash']=md5($f['position_id'].'_'.$f['bill_id']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}
?>