<?

class GYDEX_MenuAllItem extends  GYDEX_MenuItem{
	public function DeployItem($current_id=0){
		$arr=NULL;
		
		 
		 
		
		$has=true; 
		 if(($this->module_constant!="")) {
		 	  $has=$has&&(constant($this->module_constant)==1);
		 }
		
		 
		 
		if( $has ){
			 
			$arr=$this->CodeItem($current_id);	
		}
		
		return $arr;
	}

}
?>