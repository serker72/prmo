<?
require_once('notesgroup.php');

// абстрактная группа
class SchedNotesGroup extends NotesGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>