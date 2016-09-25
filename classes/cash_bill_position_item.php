<?
require_once('abstractitem.php');

 

require_once('actionlog.php');
require_once('authuser.php');
 

require_once('cashitem.php');
require_once('billitem.php');
 


//
class CashBillPositionItem extends AbstractItem{
	
 
	
	//установка всех имен
	protected function init(){
		$this->tablename='cash_bill_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		 
		 
	}
	
	

	
}
?>