<?
require_once('notesgroup.php');

// ����������� ������
class KpNotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>