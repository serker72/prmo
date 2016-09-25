<?
require_once('useritem.php');


class DiscountFind{
	protected $user=NULL;
	
	public function FindDiscount($user_id){
		$_user=new UserItem;
		$user=$_user->GetItemById($user_id);
		if($user!==false){
			return $this->Find();	
		}else return 0;
	}
	
	public function FindDiscountByUser(array $user){
		$this->user=$user;
		
		return $this->Find();	
	}
	
	
	protected function Find(){
		//сюда встроим алгоритм вычисления скидки
		return $this->user['discount_d'];	
	}
}
?>