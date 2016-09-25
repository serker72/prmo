<?
require_once('abstractgroup.php');

// абстрактная группа
class KomplConfRoleGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_confirm_role';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	
	
	
}
?>