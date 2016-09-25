<?
require_once('abstractitem.php');

//абстрактный элемент
class SupplierPhoneItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_phone_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
}
?>