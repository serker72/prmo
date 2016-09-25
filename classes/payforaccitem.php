<?
require_once('abstractitem.php');
require_once('billpaysync.php');


//прикрепление реализаций и вход. оплат
class PayForAccItem extends AbstractItem{
	
 
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment_for_acceptance';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
		 
	}
	
	
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		//if(isset($params['bill_id'])) $this->billpaysync->CatchStatus($params['bill_id']);
		return $code;
	}
	
	//править
	public function Edit($id,$params){
		AbstractItem::Edit($id, $params);
		
		//if(isset($params['bill_id'])) $this->billpaysync->CatchStatus($params['bill_id']);
	}
	
	//удалить
	public function Del($id){
		$item=$this->GetItemById($id);
		AbstractItem::Del($id);
		
		//if(isset($item['bill_id'])&&($item['bill_id']!=0)) $this->billpaysync->CatchStatus($item['bill_id']);
	}	
}
?>