<?
require_once('notesitem.php');

//абстрактный элемент
class PaymentNotesItem extends NotesItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment_notes';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>