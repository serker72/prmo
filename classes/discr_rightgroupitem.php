<?

require_once('abstractitem.php');

//абстрактный элемент
class DiscrRightGroupItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='group_rights_template';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>