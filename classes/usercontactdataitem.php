<?
require_once('abstractitem.php');

//������� ��������
class UserContactDataItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_contact_data';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='contact_id';	
	}
	
	
	
	
}
?>