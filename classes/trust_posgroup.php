<?
require_once('billpospmformer.php');
require_once('maxformer.php');
require_once('bill_in_item.php');
require_once('trust_positem.php');

// абстрактная группа
class TrustPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='trust_position';
		$this->pagename='view.php';		
		$this->subkeyname='trust_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=Array();
		
		$_bpf=new BillPosPMFormer;
		$sql='select p.id as p_id, p.trust_id, p.position_id as id, p.position_id as position_id, p.bill_id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent			 
		
		from '.$this->tablename.' as p 
			left join trust_position_pm as pm on pm.trust_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, p_id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_mf=new MaxFormer; $_bi=new BillInItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
			
			
			$bill=$_bi->GetItemById($f['bill_id']);
			
			$f['bill_code']=$bill['code'];
			$f['bill_pdate']=date("d.m.Y",$bill['pdate']);
			
			$f['supplier_bill_no']=$bill['supplier_bill_no'];
			$f['supplier_bill_pdate']=date('d.m.Y',$bill['supplier_bill_pdate']);
			
			$f['hash']=md5($f['position_id'].'_'.$f['bill_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	public function GetItemsByIdPrintArr($id, $current_id=0){
		$arr=Array();
		
		$_bpf=new BillPosPMFormer;
		$sql='select p.id as p_id, p.trust_id, p.position_id as id,    p.position_id as position_id, sum(p.quantity) as summa,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent			 
		
		from '.$this->tablename.' as p 
			left join trust_position_pm as pm on pm.trust_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
		where p.'.$this->subkeyname.'="'.$id.'" group by p.position_id order by position_name asc, p_id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_mf=new MaxFormer; $_bi=new BillInItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
			
			
			
		/*	$bill=$_bi->GetItemById($f['bill_id']);
			
			$f['bill_code']=$bill['code'];
			$f['bill_pdate']=date("d.m.Y",$bill['pdate']);*/
			
			
			$f['hash']=md5($f['position_id'].'_'.$f['bill_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//список позиций
	public function GetOtherItemsByIdArr($id, $current_id=0){
		$arr=Array();
		
		$_bpf=new BillPosPMFormer;
		$sql='select p.id as p_id, p.trust_id, p.position_id as id, p.position_id as position_id,   p.bill_id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent			 
		
		from '.$this->tablename.' as p 
			left join trust_position_pm as pm on pm.trust_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, p_id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_mf=new MaxFormer; $_bi=new BillInItem;
		$_tpi=new TrustPosItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		
			$f['hash']=md5($f['position_id'].'_'.$f['bill_id']);
			
			$itms=$_tpi->GetOtherPosArr($f['id'], $f['bill_id'], $id);
			foreach($itms as $ok=>$ov) $arr[]=$ov;
			
			//$arr[]=$f;
		}
		
		return $arr;
	}
}
?>