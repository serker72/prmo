<?

require_once('abstractitem.php');

//абстрактный элемент
class SupplierCountryItem extends AbstractItem{

	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sprav_country';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	

}
?>