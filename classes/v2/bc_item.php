<?
class BcItem{
	 
	 
	
	public $name;
	public $url;
	 
	
	function __construct(   $name, $url){
		 
		 
		 
		$this->name=$name;
	 
		$this->url=$url;
	 
	 
		
	}
	
	
	protected function CodeItem(){
		
		 
		
		$res=array(
					 
					 
					 
					 'name'=>$this->name,
					 'url'=>$this->url 
					 
					 
					 
					 );
					 
		/*echo '<pre>';			 
		print_r($res);	
		echo '</pre>';		 */
					 	
		return $res;
	}
	
	
	public function DeployItem(){
		$arr=NULL;
		
		$has=true; 
		
		
		/*$rights_man=new DistrRightsManager;
		if(($this->object_id!="")&&($this->right_kind)){
			$has=$has&&($rights_man->CheckAccess($this->_auth_result['login'],  $this->_auth_result['passw'], $this->right_kind, $this->object_id)) ;
		
		}
		 
		 if(($this->module_constant!="")) {
		 	  $has=$has&&(constant($this->module_constant)==1);
		 }
		*/
		 
		 
		if( $has ){
			 
			$arr=$this->CodeItem();	
		}
		
		return $arr;
	}

	
	 


}
?>