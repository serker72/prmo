<?
require_once('abstractitem.php');
 

 
class CashKindItem extends AbstractItem{

	
	//установка всех имен
	protected function init(){
		$this->tablename='cash_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
	}
	
	
	
}
?>