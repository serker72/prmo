<?
require_once('bc_item.php');

class Bc{

	protected $items;	
	
	
	function __construct(){
		$this->items=array();
	
	}
	
	public function AddContext(BcItem $cont){
		
		$this->items[]=$cont;
			
	}
	
	public function BuildContext( ){
		$arr=array();
		
	 
		foreach($this->items as $k=>$v){
			
		 
				 $point=array();
				 $point['item']=$v->DeployItem($current_id);
				 if($point['item']!==NULL){
				 	
				 
					
					$arr[]=$point;
				 }
			 
		}
		
		/*echo '<pre>';
		print_r($arr);
		echo '</pre>';*/		
	 
	
		return $arr;
	}
	
		
	
}
?>