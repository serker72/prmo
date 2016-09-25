<?
require_once('abstractgroup.php');


// абстрактная группа
class KomplMarkGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
}
?>