<?

require_once('discr_rightitem.php');
require_once('discr_useritem.php');
require_once('discr_rightgroupitem.php');
require_once('discr_rightuseritem.php');

require_once('usersgroup.php');


require_once('discr_man.php');
class DiscrManGroup extends DiscrMan{
	protected $rights_table;
	
	
	function __construct(){
		$this->user=new DiscrUserItem;
		$this->right=new DiscrRightItem;
		$this->userright=new DiscrRightGroupItem;	
		
	}
	
	//применение таблицы прав к юзеру
	public function ApplyTableToUser($user_id){
		new NonSet('delete from user_rights where user_id="'.$user_id.'"');
		
		$ri=new DiscrRightUserItem;
		foreach($this->rights_table as $k=>$v){
		  	//print_r($v);	  
		  foreach($v['rights'] as $kk=>$vv){
			  $right=$this->right->GetItemByFields(array('name'=>$vv));
			  $ri->Add(array('user_id'=>$user_id, 'right_id'=>$right['id'], 'object_id'=>$v['object_id']  ));	
		  }
		}
		
			
	}
	
	
	
	public function BuildRightsTable($user_id){
		
		$this->ClearRightsTable();
		
		
		//получим объекты, на которые у юзера есть какие-либо права
		$obs=new mysqlSet('select distinct object_id from group_rights_template where group_id="'.$user_id.'"');
		$objects=$obs->GetResult();
		$obc=$obs->GetResultNumRows();
		
		for($i=0; $i<$obc; $i++){
			$f=mysqli_fetch_array($objects);
			
			//получим права
			$rights=array();
			
			$rset=new mysqlSet('select distinct ur.right_id, r.name as name from group_rights_template as ur inner join rights as r on ur.right_id=r.id where group_id="'.$user_id.'" and object_id="'.$f['object_id'].'"');
			$rs=$rset->GetResult();
			$rc=$rset->GetResultNumRows();
			for($j=0; $j<$rc; $j++){
				$g=mysqli_fetch_array($rs);
				$rights[]=$g['name'];
			}
			
			
			$this->rights_table[]=array(
			//self::$rights_table[]=array(
				'object_id'=>$f['object_id'],
				'rights'=>$rights
			);
			
		}
		
		
		/*
		echo '<pre>';
		print_r(self::$rights_table);
		echo '</pre>';
		*/
		//echo ' <strong>BUILD!</strong> ';	
	}
	
	
	public function GetRightsTable(){
		return $this->rights_table;	
		//return self::$rights_table;	
	}
	
	public function ClearRightsTable(){
		$this->rights_table=NULL;	
		//self::$rights_table=NULL;	
	}
	
	
	//проверить у пользователя право на объект
	public function CheckAccess($user_id, $right_letter, $object_id){
		$result=false;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		
		if($right_id!==false){
			$userright=$this->userright->GetItemByFields(array('group_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
			
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
				$userright=$this->userright->GetItemByFields(array('group_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
				
				if($userright!==false) $result=$result&&true;
				else $result=$result&&false;
			}else $result=$result&&false;	
		}
		
		return $result;
	}
	
	
	//дать пользователю право на объект
	public function GrantAccess($user_id, $right_letter, $object_id,$change_users=false){
		$result=false;
		
		$dm=new DiscrMan;
		
		$right_id=$this->GetRightIdByLetter($right_letter);
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('group_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
			
			if($userright===false) {
				$params=array();
				$params['group_id']=$user_id;
				$params['object_id']=$object_id;
				$params['right_id']=$right_id;
				$result=$this->userright->Add($params);	
				
			}
			
			if($change_users){
				//получить список айди пользователей группы
				//пройти по ним в цикле - добавить такое право
				$ug=new UsersGroup;
				$users=$ug->GetItemsByFieldsArr(array('group_id'=>$user_id));
				foreach($users as $k=>$v){
					$dm->GrantAccess($v['id'],$right_letter,$object_id);	
				}
					
			}
			
		}
		return $result;
	}
	
	//снять у пользователя право на объект
	public function RevokeAccess($user_id, $right_letter, $object_id,$change_users=false){
		$right_id=$this->GetRightIdByLetter($right_letter);
		
		$dm=new DiscrMan;
		
		
		if($right_id!==false) {
			$userright=$this->userright->GetItemByFields(array('group_id'=>$user_id, 'right_id'=>$right_id, 'object_id'=>$object_id));
			
			if($userright!==false) $this->userright->Del($userright['id']);
			
			if($change_users){
				//получить список айди пользователей группы
				//пройти по ним в цикле - удалить такое право
				$ug=new UsersGroup;
				$users=$ug->GetItemsByFieldsArr(array('group_id'=>$user_id));
				foreach($users as $k=>$v){
					$dm->RevokeAccess($v['id'],$right_letter,$object_id);	
				}
					
			}
			
		}
	}
	
	//нахождение прав, которых нет в новой таблице
	public function NotInNewTable($old_table, $new_table){
		$away_table=array();
		
		foreach($old_table as $k=>$v){
		  	//print_r($v);	 
			/*
			$this->rights_table[]=array(
			
				'object_id'=>$f['object_id'],
				'rights'=>$rights //просто массив из букв
			);*/
		  
		  $has_object=$this->FindObjectId($v['object_id'],$new_table);
		  
		  $away_righst=array();
		  if(!$has_object){
			  //в старой таблице есть, в новой нет объекта
			  //занесем права по объекту
			  
			   foreach($v['rights'] as $kk=>$vv){
			    	$away_righst[]=$vv;	   
			   }
			      
		  }else{
			 //объект есть и в старой и в новой, проверим права
			  foreach($v['rights'] as $kk=>$vv){
			    	if(!$this->FindRightLetter($v['object_id'],$vv,$new_table)) $away_righst[]=$vv;	   
			   }
			  
		  }
		  
		  if(count($away_righst)>0){
			 $away_table[]=array(
			 	'object_id'=>$v['object_id'],
				'rights'=>$away_righst
				 );  
		  }
		 
		}
		
		return $away_table;	
	}
	
	
	protected function FindObjectId($object_id, $table){
		$res=false;
		foreach($table as $k=>$v){
			if($v['object_id']==$object_id){
				$res=true;
				break;	
			}
		}
		
		return $res;
	}
	
	protected function FindRightLetter($object_id, $right_letter, $table){
		$res=false;
		foreach($table as $k=>$v){
			if($v['object_id']==$object_id){
				
				foreach($v['rights'] as $kk=>$vv){
					if($vv==$right_letter){
						$res=true;
						return $res;	
					}
				}
			}
		}
		
		return $res;
	}
}

?>