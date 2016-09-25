<?
require_once('discr_rightitem.php');
require_once('discr_useritem.php');
require_once('discr_rightuseritem.php');



class DiscrMan{
	protected $user;
	protected $right;
	protected $userright;
	
	function __construct(){
		$this->user=new DiscrUserItem;
		$this->right=new DiscrRightItem;
		$this->userright=new DiscrRightUserItem;	
		
	}
	
	//проверить у пользователя право на объект
	public function CheckAccess($user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		
		if($right_id!==false){
			$userright=$this->userright->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
			
			if($userright!==false) $result=true;
		}
		
		
		return $result;
	}
	
	//проверить у пользователя многие права на объект
	public function CheckAccessArr($user_id, array $right_letters, $object_id){
		$result=true;
		
		foreach($right_letters as $k=>$v){
			$right_id=$this->GetRightIdByLetter($v);
		
			if($right_id!==false){
				$userright=$this->userright->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
				
				if($userright!==false) $result=$result&&true;
				else $result=$result&&false;
			}else $result=$result&&false;	
		}
		
		return $result;
	}
	
	
	//дать пользователю право на объект
	public function GrantAccess($user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
			
			if($userright===false) {
				$params=array();
				$params['user_id']=$user_id;
				$params['object_id']=$object_id;
				$params['right_id']=$right_id;
				$result=$this->userright->Add($params);	
				
			}
		}
		return $result;
	}
	
	//снять у пользователя право на объект
	public function RevokeAccess($user_id, $right_letter, $object_id){
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('user_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
			
			if($userright!==false) $this->userright->Del($userright['id']);
		}
	}
	
		
	
	
	//получить вид прав по букве
	protected function GetRightIdByLetter($right_letter){
		$result=false;
		
		$right=$this->right->GetItemByFields(array('name'=>$right_letter));
		if($right!==false){
			$result=$right['id'];
		}
		
		return $result;
	}
}

?>