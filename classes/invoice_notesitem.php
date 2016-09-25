<?
require_once('notesitem.php');

//абстрактный элемент
class InvoiceNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='invoice_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>