<?
require_once('abstractgroup.php');

// абстрактная группа
class SupplierPhoneKindGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_phone_kind';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
}
?>