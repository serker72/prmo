<?
require_once('abstractgroup.php');

// ����������� ������
class SupplierPhoneKindGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_phone_kind';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
}
?>