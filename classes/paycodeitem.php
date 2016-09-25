<?
require_once('abstractitem.php');

//код исх оплаты
class PayCodeItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment_code';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>