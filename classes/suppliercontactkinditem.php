<?
require_once('abstractitem.php');

//������� ��������
class SupplierContactKindItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';	
	}
	
	
	
	
}
?>