<?
require_once('abstractitem.php');

//����������� �������
class NotesItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>