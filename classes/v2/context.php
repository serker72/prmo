<?
require_once('context_item.php');

class Context{

	protected $items;	
	
	
	function __construct(){
		$this->items=array();
	
	}
	
	public function AddContext(ContextItem $cont){
		
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