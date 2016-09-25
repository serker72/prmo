<?
require_once('abstractitem.php');

//элемент каталога
class SupplierContactDataItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='contact_id';	
	}
	
	
	
	
}
?>