<?
require_once('abstractitem.php');



//абстрактный элемент
class IsToKItem extends AbstractItem{
	
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='interstore_to_komplekt';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';	
	}
	
	
}
?>