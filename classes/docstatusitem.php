<?
require_once('abstractitem.php');

//абстрактный элемент
class DocStatusItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='document_status';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>