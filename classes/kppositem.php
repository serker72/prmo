<?
require_once('abstractitem.php');
require_once('kpitem.php');
require_once('kppospmitem.php');

require_once('authuser.php');

require_once('actionlog.php');

//абстрактный элемент
class KpPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='kp_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='kp_id';	
	}
	
	
	
	//добавить 
	public function Add($params, $pms=NULL){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//создать +/- для позиции
			$bpm=new KpPosPMItem;
			
			if($code>0){
				$pms['kp_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		
		return $code;
	}
	
	
	//редактировать
	public function Edit($id,$params,$pms=NULL,$can_change_cascade=false, $check_delta_summ=false, $result=NULL){
		$_log=new ActionLog;
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		if(!isset($params['total'])){
		  $item=$this->GetItemById($id);
		  
		  
		  
		  if(isset($params['quantity'])&&($params['quantity']!=$item['quantity'])){
			   if(isset($params['price_pm'])&&($params['price_pm']!=$item['price_pm'])) $price=$params['price_pm'];
			   else $price=$item['price_pm'];
			   
			   $params['total']=$params['quantity']*$price;
		  }
		}
		
		
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//если уже есть пм, то найти иобработать его
			//если нет - то создать
			$_bpm=new KpPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('kp_position_id'=>$id));
			if($bpm===false){
				$pms['kp_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['kp_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}else{
			$_bpm=new KpPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('kp_position_id'=>$id));
			if($bpm!==false){
				$_bpm->Del($bpm['id']);
			}
		}
		
		
	}
	
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from kp_position_pm where kp_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	
	
}
?>