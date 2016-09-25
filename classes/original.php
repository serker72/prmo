<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('acc_item.php');

require_once('original_abstract.php');


//оригиналы отгруз док-тов - исход
class Original extends OriginalAbstract{
	
	public $prefix='2';
	public $is_incoming=0;
	
	protected function init(){
		$this->_item=new AccItem;
	}
	
}
?>