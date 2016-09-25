<?
require_once('abstractitem.php');
 


require_once('authuser.php');
require_once('actionlog.php');
 

//абстрактный элемент
class SupplierResponsibleUserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_responsible_user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	 
	
}
?>