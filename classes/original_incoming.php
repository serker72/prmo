<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('acc_in_item.php');

require_once('original_abstract.php');


//оригиналы отгруз док-тов - входящие
class OriginalIncoming extends OriginalAbstract{
	
	public $prefix='1';
	public $is_incoming=1;
	
	
	protected function init(){
		$this->_item=new AccInItem;
	}
}
?>