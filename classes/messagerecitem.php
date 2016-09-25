<?
require_once('abstractitem.php');

//получатель сообщения
class MessageReceiverItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='message_receiver';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
}
?>