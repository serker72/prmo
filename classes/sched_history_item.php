<?
require_once('abstractitem.php');
/*
require_once('claimitem.php');
require_once('user_s_item.php');
require_once('user_d_item.php');
require_once('messageitem.php');

require_once('specdelgroup.php');*/

//абстрактный элемент
class Sched_HistoryItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_history';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	 
	//добавить 
	/*public function Add($params){
		 
		
		
		$code=parent::Add($params);
		
		
	 
		
		
		return $code;
	}*/
}
?>