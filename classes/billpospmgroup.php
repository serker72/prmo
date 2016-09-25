<?
require_once('abstractgroup.php');

// абстрактная группа
class BillPosPMGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='bill_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>