<?
require_once('abstractitem.php');

//����������� �������
class FileMessageItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='message_file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>