<?
require_once('abstractitem.php');

//��� ��� ������
class PayCodeItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='payment_code';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>