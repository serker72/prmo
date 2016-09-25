<?
require_once('abstractitem.php');

//элемент каталога
class SupplierContactKindItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';	
	}
	
	
	
	
}
?>