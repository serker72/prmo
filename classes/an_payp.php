<?
require_once('billpospmformer.php');
require_once('payforbillgroup.php');
require_once('billitem.php');
require_once('acc_group.php');
require_once('acc_item.php');
require_once('supplieritem.php');
require_once('suppliersgroup.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('invcalcitem.php');

require_once('an_payp_abstract.php');

class AnPayUniv extends AnPayUnivAbstract{
	protected $bydates=array();
	
	
	
	public $prefix='_3';
	public $url_prefix='';
	protected $is_incoming=0;
	
	
	protected function init(){
		
				
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		$this->_acc_item=new AccItem;
		$this->_acc_group=new AccGroup;
		
		
	}
	
	
	

}
?>