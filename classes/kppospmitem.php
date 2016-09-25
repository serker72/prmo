<?
require_once('abstractitem.php');

//абстрактный элемент
class KpPosPMItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='kp_position_id';	
	}
	
	
	//удалить
	public function Del($id){
		
		
		
		parent::Del($id);
	}	
	
	
	
}
?>