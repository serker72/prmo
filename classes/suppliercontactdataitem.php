<?
require_once('abstractitem.php');

//������� ��������
class SupplierContactDataItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='contact_id';	
	}
	
	
	
	
}
?>