<?
require_once('abstractgroup.php');
 
 

// ������ 
class SupplierRukKindGroup extends AbstractGroup {
	 
	protected $_auth_result;
	
	
	
	public $prefix='_cash';
 
	protected $_item;
	protected $_notes_group;
	 
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_ruk_kind';
		$this->pagename='cash_percents.php';		
		 
		$this->vis_name='is_confirmed';		
		 
	}
	
	
	
	
	
	
	 
}
?>