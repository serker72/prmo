<?
require_once('notesgroup.php');

// ����������� ������
class InvCalcNotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='invcalc_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>