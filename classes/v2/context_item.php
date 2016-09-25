<?
class ContextItem{
	 
	public $object_id; 
	public $right_kind;
	public $module_constant;
	public $_auth_result;
	
	public $name;
	public $url;
	public $is_help;
	
	
	function __construct(  $object_id, $right_kind, $module_constant, $name, $url, $is_help , $_auth_result){
		 
		 
		$this->object_id=$object_id;
		
		$this->right_kind=$right_kind;
		$this->name=$name;
	 
		$this->url=$url;
	 
		$this->module_constant=$module_constant 	; 
	
	 
		$this->is_help=$is_help;
		 
		$this->_auth_result=$_auth_result;
		
		
	}
	
	
	protected function CodeItem(){
		
		 
		
		$res=array(
					 
					 
					 'object_id'=>$this->object_id, 
					 'right_kind'=>$this->right_kind, 
					 'module_constant'=>$this->module_constant,
					 'name'=>$this->name,
					 'url'=>$this->url,
					 'is_help'=>$this->is_help 
					 
					 
					 );
					 
		/*echo '<pre>';			 
		print_r($res);	
		echo '</pre>';		 */
					 	
		return $res;
	}
	
	
	public function DeployItem(){
		$arr=NULL;
		
		$has=true; 
		$au=new AuthUser;
		 
		
		//$rights_man=new DiscrMan;
		if(($this->object_id!="")&&($this->right_kind)){
			$has=$has&&($au->user_rights->CheckAccess('w', $this->object_id)); //($rights_man->CheckAccess($this->_auth_result['id'], $this->right_kind, $this->object_id)) ;
			
			 
		
		}
		 
		 if(($this->module_constant!="")) {
		 	  $has=$has&&(constant($this->module_constant)==1);
		 }
		
		 
		 
		if( $has ){
			 
			$arr=$this->CodeItem();	
		}
		
		return $arr;
	}

	
	public function SetAuthResult($result){
		$this->_auth_result=$result;	
	}

	


}
?>