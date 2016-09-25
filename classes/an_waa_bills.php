<?
require_once('abstractgroup.php');
require_once('billitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('billnotesgroup.php');
require_once('billnotesitem.php');
require_once('payforbillgroup.php');

require_once('period_checker.php');

require_once('an_waa_bills_abstract.php');

//исход счета без а/а 
class AnWaaBills extends AnWaaBillsAsbtract {
	protected $_auth_result;
	
	
	public $prefix='_1';
	public $url_prefix='';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill';
		$this->pagename='an_waa.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		
		
		
		$this->_auth_result=NULL;
	}
	
	
	
	
}
?>