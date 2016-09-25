<?
require_once('abstractitem.php');

//отчетный период
class PerItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='period';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='org_id';	
	}
	
	
	
	
}
?>