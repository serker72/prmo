<?
require_once('notesgroup.php');

// ����������� ������
class InvoiceNotesGroup extends NotesGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='invoice_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>