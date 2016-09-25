<?
require_once('abstractitem.php');
require_once('posdimitem.php');

//элемент каталога
class PosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='catalog_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//удалить
	public function Del($id){
		
		
		if($this->CanDelete($id)){
		  $query = 'delete from catalog_position_in_group where position_id="'.$id.'";';
		  $it=new nonSet($query);
		  /*
		  unset($it);				
		  
		  $this->item=NULL;*/
		  
		  
		  AbstractItem::Del($id);
		}
	}	
	
	
	//править
	public function Edit($id,$params){
		$item=$this->GetItemById($id);
		
		AbstractItem::Edit($id,$params);
		
		if(isset($params['name'])){
			$_tables=array();
			
			$_tables[]='acceptance_position';
			$_tables[]='bill_position';
			$_tables[]='interstore_position';
			$_tables[]='interstore_wf_position';
			
			$_tables[]='inventory_position';
			
			//$_tables[]='komplekt_ved_pos';
			$_tables[]='sh_i_position';
			$_tables[]='trust_position';
			
			
			
			
			foreach($_tables as $k=>$v){
				new NonSet('update '.$v.' set name="'.$params['name'].'" where position_id="'.$id.'"');
			
			}
			
				
		}
		
		//изменились единицы измерения
		if(isset($params['dimension_id'])&&($params['dimension_id']!=$item['dimension_id'])){
			$_dim=new PosDimItem;
			$dim=$_dim->GetItemById($params['dimension_id']);
			
			$_tables=array();
			
			$_tables[]='acceptance_position';
			$_tables[]='bill_position';
			$_tables[]='interstore_position';
			$_tables[]='interstore_wf_position';
			
			$_tables[]='inventory_position';
			
			//$_tables[]='komplekt_ved_pos';
			$_tables[]='sh_i_position';
			$_tables[]='trust_position';
			
			
			
			
			foreach($_tables as $k=>$v){
				new NonSet('update '.$v.' set dimension="'.SecStr($dim['name']).'" where position_id="'.$id.'"');
			
			}
				
		}
	}
	
	
	//контроль возможности удаления
	public function CanDelete($id){
		$can_delete=true;
		
		
		$set=new mysqlSet('select count(*) from komplekt_ved_pos as p inner join komplekt_ved as b on b.id=p.komplekt_ved_id  where b.status_id in(2, 12, 13) and p.position_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		$set=new mysqlSet('select count(*) from bill_position as p inner join bill as b on b.id=p.bill_id where p.position_id="'.$id.'" and (b.is_confirmed_price=1 or b.is_confirmed_shipping=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		/*$set=new mysqlSet('select count(*) from sh_i_position as p inner join sh_i as b on b.id=p.sh_i_id where p.position_id="'.$id.'" and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;*/
		
		$set=new mysqlSet('select count(*) from acceptance_position as p inner join acceptance as b on b.id=p.acceptance_id where p.position_id="'.$id.'"  and b.is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		
		
		return $can_delete;
	}
	//
	
}
?>