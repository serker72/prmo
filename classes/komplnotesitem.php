<?
require_once('notesitem.php');

//����������� �������
class KomplNotesItem extends NotesItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>