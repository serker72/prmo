<?
require_once('rl_rightitem.php');
require_once('rl_useritem.php');
require_once('rl_rightuseritem.php');
require_once('rl_recorditem.php');
require_once('rl_grobjectitem.php');



class RLMan{
	protected $user;
	protected $right;
	protected $userright;
	protected $record;
	protected $group;
	
	function __construct(){
		$this->user=new RLUserItem;
		$this->right=new RLRightItem;
		$this->userright=new RLRightUserItem;
		$this->record=new RLRecordItem;
		$this->group=new  RLGrObjectItem;
		
	}
	
	
	//проверить наличие контроля доступа к записи и затем - конкретные права
	/*public function CheckOperAccess($user_id, $record_id, $object_id, $right_letter, $tablename, $additional_id=0, &$has_control){
		$res=true;
		
		$has_control=$this->HasControl($record_id, $tablename, $additional_id, $group_id, $rl_record_id);
		
		if($has_control){
			//проверим доступ данного пол-ля к данному праву
			$res=$this->CheckAccess($rl_record_id, $user_id, $right_letter, $object_id);	
		}else $res=false;
		
		
		return $res;
	}*/
	
	
	//получить список айди объектов, к которым нет доступа у текущего пол-ля
	public function GetBlockedItemsArr($user_id, $object_id, $right_letter, $tablename, $additional_id=0){
		$arr=array();	
		
		$sql='select t2.record_id from  rl_record as t2
		inner join rl_group as t4 on t4.id=t2.rl_group_id
				
		where t2.id not in(
			select distinct t1.rl_record_id from  rl_user_rights as t1
			inner join rights as r on r.id=t1.right_id
			where user_id="'.$user_id.'" and r.name="'.$right_letter.'" and rl_object_id="'.$object_id.'"
			
		)
		and t4.tablename="'.$tablename.'" and t4.additional_id="'.$additional_id.'"
		';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$arr[]=$f['record_id'];	
		}
		
		//print_r($arr);
		
		return $arr;
	}
	
	
	
	//проверить наличие контроля доступа к записи и затем - конкретные права
	public function CheckFullAccess($user_id, $record_id, $object_id, $right_letter, $tablename, $additional_id=0, &$has_control){
		$res=true;
		
		$has_control=$this->HasControl($record_id, $tablename, $additional_id, $group_id, $rl_record_id);
		
		if($has_control){
			//проверим доступ данного пол-ля к данному праву
			$res=$this->CheckAccess($rl_record_id, $user_id, $right_letter, $object_id);	
		}
		
		
		return $res;
	}
	
	
	//проверить наличие контроля доступа к записи
	public function HasControl($record_id, $tablename, $additional_id, &$group_id, &$rl_record_id){
		$res=false; $group_id=false; $rl_record_id=false;
		//определен ли набор действий?
		$grp=$this->group->GetItemByFields(array('tablename'=>$tablename, 'additional_id'=>$additional_id));
		
		if($grp!==false) {
			$group_id=$grp['id'];
		
			//включен ли контроль для данной записи???
			$recrd=$this->record->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$group_id));
			
			if($recrd!==false){
				//контроль включен!
				$rl_record_id=$recrd['id'];
				$res=true;
			}
		}
		
		
		return $res;
	}
	
	
	
	
	//проверить у пользователя право на объект
	public function CheckAccess($record_id, $user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		
		if($right_id!==false){
			$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
			
			if($userright!==false) $result=true;
		}
		
		
		return $result;
	}
	
	//проверить у пользователя многие права на объект
	public function CheckAccessArr($record_id, $user_id, array $right_letters, $object_id){
		$result=true;
		
		foreach($right_letters as $k=>$v){
			$right_id=$this->GetRightIdByLetter($v);
		
			if($right_id!==false){
				$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
				
				if($userright!==false) $result=$result&&true;
				else $result=$result&&false;
			}else $result=$result&&false;	
		}
		
		return $result;
	}
	
	
	//дать пользователю право на объект
	public function GrantAccess($record_id, $user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
			
			if($userright===false) {
				$params=array();
				$params['user_id']=$user_id;
				$params['rl_object_id']=$object_id;
				$params['rl_record_id']=$record_id;
				$params['right_id']=$right_id;
				$result=$this->userright->Add($params);	
				
			}
		}
		return $result;
	}
	
	//снять у пользователя право на объект
	public function RevokeAccess($record_id, $user_id, $right_letter, $object_id){
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('rl_record_id'=>$record_id, 'user_id'=>$user_id, 'right_id'=>$right_id, 'rl_object_id'=>$object_id));
			
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