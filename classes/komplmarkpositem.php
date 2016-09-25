<?
require_once('abstractitem.php');


//Маркер заявки
class KomplMarkPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker_pos';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='marker_id';	
	}
	
	
	
}
?>