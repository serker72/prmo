<?
//require_once('authuser.php');

class  GYDEX_MenuSecItem extends  GYDEX_MenuItem{
	public function DeployItem($current_id=0){
		$arr=NULL;
		
		 
		
		$au=new AuthUser;
		
		$has=$au->user_rights->CheckAccess('w', $this->object_id);
		 
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