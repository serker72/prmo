<?
require_once('abstractitem.php');

//сообщение
class MessageItem extends AbstractItem{
	protected $receiver_tablename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='message';
		$this->receiver_tablename='message_receiver';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	public function Add(array $params, array $receiver_ids){
		$code=parent::Add($params);
		
		foreach($receiver_ids as $k=>$v){
			
		
		}
		return $code;
	}
}
?>