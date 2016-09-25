<?
require_once('abstractitem.php');

 

require_once('actionlog.php');
require_once('authuser.php');
 

require_once('period_checker.php');
require_once('billitem.php');

require_once('cash_to_bill_item.php');
 


//rashod nali4nyh
class CashPercentItem extends AbstractItem{
	
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='cash_percent';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		 
		 
	}
	
	//найти актуалный процент: дата не более сегодн€шней, последний из
	public function GetActual($org_id){
		$sql='select p.*,
					 
					pk.code, pk.name
					
					
				from '.$this->tablename.' as p
					
					left join payment_code as pk on p.code_id=pk.id
				where p.begin_pdate<="'.mktime(0,0,0,date('m'),date('d'), date('Y')).'"	
				and p.org_id="'.$org_id.'"
				order by begin_pdate desc limit 1
					'; 	
		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		if($rc==0){
			return false;	
		}else{
			$f=mysqli_fetch_array($rs);
			
			return $f;	
		}
	}
	
	//найти  процент на дату: дата не более заданной, последний из
	public function GetActualByPdate($org_id, $pdate){
		$sql='select p.*,
					 
					pk.code, pk.name
					
					
				from '.$this->tablename.' as p
					
					left join payment_code as pk on p.code_id=pk.id
				where p.begin_pdate<="'.datefromdmy($pdate).'"	
				and p.org_id="'.$org_id.'"
				order by begin_pdate desc limit 1
					'; 	
		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		if($rc==0){
			return false;	
		}else{
			$f=mysqli_fetch_array($rs);
			
			return $f;	
		}
	}
	
}
?>