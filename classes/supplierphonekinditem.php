<?
require_once('abstractitem.php');

//����������� �������
class SupplierPhoneItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_phone_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
}
?>