<?
require_once('abstractitem.php');
require_once('acc_item.php');

class IsBlink{
	
	
	
	//обобщенная функция цвета и мигания
	public function OverallBlink($is, &$color){
		$res=false;
		
		$color='black';
		
		$_acc=new AccItem;
		if($is['status_id']==17){
			$acc=$_acc->getitembyfields(array('interstore_id'=>$is['id']));
			
			if($acc['status_id']==5){
				//проверить наличие услуг
				$has_all=true;
				
				$has_usl=false;//$_acc->HasUsl($acc['id']);	
				
				if($acc['has_nakl']==0) $has_all=$has_all&&false;
				if($acc['has_fakt']==0) $has_all=$has_all&&false;
				if(($acc['has_akt']==0)&&$has_usl) $has_all=$has_all&&false;
				
				if($has_all) $color='#417641';
			}
				
		}
		
		
		
		
		
		return $res;
	}
}
?>