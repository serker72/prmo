<?
require_once('billpospmformer.php');
require_once('bill_in_item.php');
require_once('acc_in_group.php');
require_once('acc_in_item.php');
require_once('supplieritem.php');
require_once('suppliersgroup.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('authuser.php');

require_once('an_paybuh_abstract.php');

class AnPayBuhIn extends AnPayBuhAbstract{
	protected $bydates=array();
	

	public $prefix='_8';
	public $url_prefix='_in';
	protected $is_incoming=1;
	
	
	
	protected function init(){
		
				
		$this->_item=new BillInItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		$this->_acc_item=new AccInItem;
		$this->_acc_group=new AccInGroup;
		
		
	}
}
?>