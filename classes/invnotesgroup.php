<?
require_once('notesgroup.php');

// ����������� ������
class InvNotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='inventory_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>