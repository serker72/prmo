<?
require_once('abstractgroup.php');


// абстрактная группа
class KomplMarkPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker_pos';
		$this->pagename='view.php';		
		$this->subkeyname='marker_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
}
?>