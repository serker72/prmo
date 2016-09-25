<?

class  GYDEX_MenuItem{
	public $id;
	public $parent_id;
	public $object_id;
	public $name;
	public $description;
	public $url;
	public $ord;
 	
	public $module_constant ;
	
	 
	public $user_id;
	
	public $is_pic;
 
	protected $_auth_result;
	
	function __construct($id, $parent_id, $object_id, $name, $description, $url, $ord,  $is_pic, $pic, $module_constant ){
		$this->id=$id;
		$this->parent_id=$parent_id;
		$this->object_id=$object_id;
		$this->name=$name;
		$this->description=$description;
		$this->url=$url;
		$this->ord=$ord;
		$this->module_constant=$module_constant 	; 
	
	 
		$this->is_pic=$is_pic;
		$this->pic=$pic;
		 
		$this->_auth_result=NULL;
		
		
	}
	
	public function DeployItem(){}
	
	protected function CodeItem($current_id=0){
		
		 
		
		$res=array(
					'is_active'=>(int)($current_id==$this->id), 
					'id'=>$this->id, 
					 'parent_id'=>$this->parent_id, 
					 'object_id'=>$this->object_id, 
					 'name'=>$this->name,
					 'description'=>$this->description,
					 'url'=>$this->url,
					 'ord'=>$this->ord,
					 
			 
					 'is_pic'=>$this->is_pic,
					 'pic'=>$this->pic 
					 
					 
					 );
					 
		/*echo '<pre>';			 
		print_r($res);	
		echo '</pre>';		 */
					 	
		return $res;
	}
	
	public function SetAuthResult($result){
		$this->_auth_result=$result;	
	}

}
?>