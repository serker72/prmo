<?
require_once('NonSet.php');
require_once('abstractitem.php');

//абстрактный элемент
class SpecDelItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='spec_delivery';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	public function Clear(){
		$ns=new nonSet('truncate '.$this->tablename);	
	}
}
?>