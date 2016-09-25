<?
require_once('abstractitem.php');
//require_once('sh_i_pospmitem.php');

//абстрактный элемент
class IsPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='interstore_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='interstore_id';	
	}
	
	
	
	//добавить 
	public function Add($params, $pms=NULL){
		if(isset($params['quantity'])) $params['quantity_initial']=$params['quantity'];
		
		
		$code=AbstractItem::Add($params);
		
		/*if($pms!==NULL){
			//создать +/- для позиции
			$bpm=new ShIPosPMItem;
			
			if($code>0){
				$pms['sh_i_position_id']=$code;
				$bpm->Add($pms);	
			}
		}*/
		
		return $code;
	}
	
	
	//редактировать
	public function Edit($id,$params,$pms=NULL, $change_quantity_initial=true){
		if($change_quantity_initial&&isset($params['quantity'])) {
			$itm=$this->GetItemById($id);
			if($itm['quantity']!=$params['quantity']){
				$params['quantity_initial']=$params['quantity'];	
			}
		
		}
		
		AbstractItem::Edit($id,$params);
		
		
	}
	
	
	
	//удалить
	public function Del($id){
		
	/*	$query = 'delete from sh_i_position_pm where sh_i_position_id='.$id.';';
		$it=new nonSet($query);
		*/
		
		parent::Del($id);
	}	
	
	
	
}
?>