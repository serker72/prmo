<?
require_once('abstractitem.php');

//���������� ���������
class MessageReceiverItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='message_receiver';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
}
?>