<?
require_once('abstractgroup.php');

// абстрактная группа
class TrustPosPMGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='trust_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='trust_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>