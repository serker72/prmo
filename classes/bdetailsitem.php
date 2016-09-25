<?
require_once('abstractitem.php');

//абстрактный элемент
class BDetailsItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='banking_details';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	//добавить 
	public function Add($params){
		if(isset($params['is_basic'])&&($params['is_basic']==1)&&isset($params['user_id'])){
			$ns=new NonSet('update '.$this->tablename.' set is_basic=0 where user_id="'.$params['user_id'].'" ');	
		}
		parent::Add($params);
	}
	
	//править
	public function Edit($id,$params){
		$item=$this->GetItemById($id);
		
		
		if(($item!==false)&&isset($params['is_basic'])&&($params['is_basic']==1)){
			$ns=new NonSet('update '.$this->tablename.' set is_basic=0 where user_id="'.$item['user_id'].'" and id<>"'.$id.'"');
		}
		
		parent::Edit($id,$params);
	}
	
	public function GetBasic($id){
		$itm=$this->GetItemByFields(array('user_id'=>$id,'is_basic'=>1));
		if($itm==false){
			$itm=$this->GetItemById($id);	
			
		}
		return $itm;
	}
	
}
?>