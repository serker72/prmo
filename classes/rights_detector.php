<?
require_once('abstractitem.php');

//require_once('storageitem.php');
//require_once('sectoritem.php');

class RightsDetector{
	protected $instance;
	
	function __construct($instance){
		$this->instance=$instance;	
		
	}
	
	
	//функция определения идентификатора прав
	public function FindRId( $id=NULL, array $item=NULL,  $sector_id=NULL,  $storage_id=NULL,  $sector_ss=NULL,  $storage_ss=NULL,  array $right_ids){
		$rid=$right_ids[0];
		/*
		
		if(($id!==NULL)&&($this->instance!==NULL)){
			//определим документ, найдем слкда-сектор
			$doc=$this->instance->GetItemById($id);
			
			$_si=new SectorItem;
			$si=$_si->GetItemById($doc['sector_id']);
			
			$_sti=new StorageItem;
			$sti=$_sti->GetItemById($doc['storage_id']);
			
			if(($sti['s_s']==1)&&($si['s_s']==1)) $rid=$right_ids[1];
			
			
				
		}elseif($item!==NULL){
			
			
			$_si=new SectorItem;
			$si=$_si->GetItemById($item['sector_id']);
			
			$_sti=new StorageItem;
			$sti=$_sti->GetItemById($item['storage_id']);
			
			
			if(($sti['s_s']==1)&&($si['s_s']==1)) $rid=$right_ids[1];
			
			
		}elseif(($sector_id!==NULL)&&($storage_id!==NULL)){
			
			$_si=new SectorItem;
			$si=$_si->GetItemById($sector_id);
			
			$_sti=new StorageItem;
			$sti=$_sti->GetItemById($storage_id);
			
			if(($sti['s_s']==1)&&($si['s_s']==1)) $rid=$right_ids[1];
			
		}elseif(($sector_ss!==NULL)&&($storage_ss!==NULL)){
			if(($sector_ss==1)&&($storage_ss==1)) $rid=$right_ids[1];
			
			
		}
			*/
		return $rid;
	}
	
}

?>