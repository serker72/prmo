<?
require_once('abstractitem.php');

//договор поставщика
class SupContractItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contract';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	//добавить 
	public function Add($params){
		if(isset($params['is_basic'])&&($params['is_basic']==1)&&isset($params['user_id'])){
			$ns=new NonSet('update '.$this->tablename.' set is_basic=0 where user_id="'.$params['user_id'].'" and is_incoming="'.$params['is_incoming'].'" ');	
		}
		parent::Add($params);
	}
	
	//править
	public function Edit($id,$params){
		$item=$this->GetItemById($id);
		
		
		if(($item!==false)&&isset($params['is_basic'])&&($params['is_basic']==1)){
			$ns=new NonSet('update '.$this->tablename.' set is_basic=0 where user_id="'.$item['user_id'].'" and is_incoming="'.$params['is_incoming'].'" and id<>"'.$id.'"');
		}
		
		parent::Edit($id,$params);
	}
	
	public function GetBasic($id, $is_incoming=0){
		$itm=$this->GetItemByFields(array('user_id'=>$id,'is_basic'=>1, 'is_incoming'=>$is_incoming));
		if($itm==false){
			$itm=$this->GetItemById($id);	
			
		}
		return $itm;
	}
	
	public function DocCanAnnul($id,&$reason){
		
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		
		
		$set=new mysqlSet('select count(*) from bill where contract_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных счетов: '.($g[0]);	
		}
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
}
?>