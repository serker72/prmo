<?
require_once('abstractitem.php');

 

require_once('actionlog.php');
require_once('authuser.php');
 

require_once('cashitem.php');
require_once('billitem.php');
 


//
class CashToBillItem extends AbstractItem{
	
 
	
	//установка всех имен
	protected function init(){
		$this->tablename='cash_to_bill';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		 
		 
	}
	
	
	public function GetBillsbyCashArr($cash_id, $org_id){
		$alls=array();		
		
		$sql='select * from bill where org_id="'.$org_id.'" and id in(select distinct bill_id from '.$this->tablename.' where cash_id="'.$cash_id.'") order by code';
		
		//echo $sql.'<br>';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			
				$alls[]=$f;
		}
		
		return $alls;
	}
	
}
?>