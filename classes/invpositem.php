<?
require_once('abstractitem.php');
require_once('invitem.php');


//абстрактный элемент
class InvPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='inventory_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='inventory_id';	
	}
	
	
	
	//добавить 
/*	public function Add($params, $pms=NULL){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//создать +/- для позиции
			$bpm=new BillPosPMItem;
			
			if($code>0){
				$pms['bill_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		
		return $code;
	}
	
	
	//редактировать
	public function Edit($id,$params,$pms=NULL,$can_change_cascade=false){
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//если уже есть пм, то найти иобработать его
			//если нет - то создать
			$_bpm=new BillPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$id));
			if($bpm===false){
				$pms['bill_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['bill_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}else{
			$_bpm=new BillPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$id));
			if($bpm!==false){
				$_bpm->Del($bpm['id']);
			}
		}
		
		if($can_change_cascade){
		 
		  
		  //найти все такие же позиции в распоряжениях и поступлениях, сменить и у них все
		  //сменить цену, и пмс
		  
		  //позиции распоряжений
		  
		  //28.03.2012 !!!!! добавить в фильтр по распоряжениям также storage_id, sector_id
		  //как быть, если у нас позиции из разных заявок? добавить фильтр по komplekt_ved_pos_id
		  
		  //знаем: айди позиции счета. найдем - айди счета, по нему - айди всех распоряжений
		  //select id from sh_i_position where position_id=$position_id and sh_i_id in(select id from sh_i where bill_id=$bill_id)
		  
		  $itm=$this->GetItemById($id);
		  if($itm===false) return;
		  
		  $_shi=new ShIPosItem;
		  $_shipm=new ShIPosPMItem;
		  
		  $sql1='select * from sh_i_position 
		  where position_id="'.$itm['position_id'].'" and komplekt_ved_id="'.$itm['komplekt_ved_id'].'"
		        and sh_i_id in(select id from sh_i where bill_id="'.$itm['bill_id'].'" and storage_id="'.$itm['storage_id'].'" and sector_id="'.$itm['sector_id'].'" ) 
				';
				
		  //echo $sql1.'<br>';
		  $set=new mysqlSet($sql1);
		  
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		 
		  
		  if($pms!==NULL){
			  unset($pms['bill_position_id']);
		  }
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			 
			  
			  if(isset($params['price'])) $_shi->Edit($f['id'],array('price'=>$params['price']));
			  
			  new NonSet('delete from sh_i_position_pm where sh_i_position_id="'.$f['id'].'"');
			  
			  
			  if($pms!==NULL){
				  $pms['sh_i_position_id']=$f['id'];
					  
				  $_shipm->Add($pms);	
				  
				  
				 
			  }
			  
		  }
		  
		  
		  //28.03.2012 !!!!! добавить в фильтр по поступлениям также storage_id, sector_id
		  
		  
		   
		  $_shi=new AccPosItem;
		  $_shipm=new AccPosPMItem;
		  
		  $sql1='select * from acceptance_position 
		  where 
		  	position_id="'.$itm['position_id'].'" and komplekt_ved_id="'.$itm['komplekt_ved_id'].'"
			and acceptance_id in(select id from acceptance where bill_id="'.$itm['bill_id'].'" and storage_id="'.$itm['storage_id'].'"  and sector_id="'.$itm['sector_id'].'" ) 
			';
		  $set=new mysqlSet($sql1);
		  
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  if($pms!==NULL){
			  unset($pms['bill_position_id']);
			  unset($pms['sh_i_position_id']);
		  }
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			
			  if(isset($params['price'])) $_shi->Edit($f['id'],array('price'=>$params['price']));
			  
			  new NonSet('delete from acceptance_position_pm where acceptance_position_id="'.$f['id'].'"');
			  
			  
			  if($pms!==NULL){
				  $pms['acceptance_position_id']=$f['id'];
					  
				  $_shipm->Add($pms);	
				  
				  
				
			  }
			  
		  }
		  
		  //die();
		  
		}
	}
	
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from bill_position_pm where bill_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	
	*/
}
?>