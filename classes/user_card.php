<?

class UserCard{
	//protected $user_item;
	
	
	function __construct(){
		//$this->user_item=$item;	
	}
	
	
	public function Deploy($id, AbstractItem $item, $work_mode, $template){
		$sm=new SmartyAdm;
		
		//print_r($item->Deploy($id));
		
		$sm->assign('user',$item->Deploy($id));
		
		$sm->assign('work_mode',$work_mode);
		return $sm->fetch($template);
	}
}

?>