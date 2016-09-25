<?
require_once('abstractitem.php');

//элемент каталога
class SupplierContactDataItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_confirm_roles';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';	
	}
	
	
	
	
}
?>