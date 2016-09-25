<?
require_once('abstractgroup.php');
 

require_once('cashnotesgroup.php');
require_once('cashnotesitem.php');

require_once('cashitem.php');

require_once('cash_to_bill_item.php');
require_once('user_s_item.php');

// группа
class CashBillPositionGroup extends AbstractGroup {
	 
	 
	 
	//установка всех имен
	protected function init(){
		$this->tablename='cash_bill_position';
		$this->pagename='view.php';		
		 
		$this->vis_name='is_confirmed';		
	 
	 
	}
	
	
	//сколько выдано по позиции счета
	public function CalcGiven($bill_position_id){
		$sql='select sum(given_value) from cash_bill_position where bill_position_id="'.$bill_position_id.'" and cash_id in(select id from cash where is_confirmed_given=1)';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=mysqli_fetch_array($rs);
		
		
		return round((float)$f[0],2);	
		
	}
	
	
	//какие расходы (док-ты) по позиции счета
	public function GetCashesByBillPosition($bill_position_id){
		$arr=array();
		$sql='select c.*, p.given_value from cash as c inner join cash_bill_position as p on c.id=p.cash_id  where c.is_confirmed_given=1 and p.bill_position_id="'.$bill_position_id.'"';
		
		//echo $sql.'<br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//сколько выдано по позиции счета
	public function CalcSemiGiven($bill_position_id){
		$sql='select sum(given_value) from cash_bill_position where bill_position_id="'.$bill_position_id.'" and cash_id in(select id from cash where is_confirmed=1)';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=mysqli_fetch_array($rs);
		
		
		return round((float)$f[0],2);	
		
	}
	
	
	//какие расходы (док-ты) по позиции счета
	public function GetCashesSemiByBillPosition($bill_position_id){
		$arr=array();
		$sql='select c.*, p.given_value, st.name as status_name
		 from cash as c 
		 inner join cash_bill_position as p on c.id=p.cash_id  
		 left join document_status as st on st.id=c.status_id
		 where c.is_confirmed=1 and p.bill_position_id="'.$bill_position_id.'"';
		
		//echo $sql.'<br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
}
?>