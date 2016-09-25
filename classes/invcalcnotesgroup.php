<?
require_once('notesgroup.php');

// абстрактная группа
class InvCalcNotesGroup extends NotesGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='invcalc_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>