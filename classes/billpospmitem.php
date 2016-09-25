<?
require_once('abstractitem.php');

//абстрактный элемент
class BillPosPMItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_position_id';	
	}
	
	
	//удалить
	public function Del($id){
		
		
		
		parent::Del($id);
	}	
	
	
	
}
?>