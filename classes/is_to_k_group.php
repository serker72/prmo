<?

require_once('abstractgroup.php');

// абстрактная группа
class IsToKGroup extends AbstractGroup {
	
	//установка всех имен
	protected function init(){
		$this->tablename='interstore_to_komplekt';
		$this->pagename='view.php';		
		$this->subkeyname='interstore_id';	
		$this->vis_name='interstore_id';		
		
		
		
	}
	
	
}
?>