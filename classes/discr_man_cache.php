<?
require_once('discr_man.php');


class DiscrManCache extends DiscrMan{
	//protected $rights_table=NULL;
	protected static $rights_table;
	protected $user_id;
	
	
	public function BuildRightsTable($user_id){
		
		
		$this->user_id=$user_id;
		
		$this->ClearRightsTable();
		
		
		
		//ПЕРЕДЕЛАТЬ ПОД ОДИН БОЛЬШОЙ ЗАПРОС
		$big=new  mysqlSet('select 
			ur.object_id as object_id,
			r.name as right_name
			
			from user_rights as ur
			inner join rights as r on ur.right_id=r.id
			 where ur.user_id="'.$this->user_id.'"');
		$objects=$big->GetResult();
		$obc=$big->GetResultNumRows();
		
		for($i=0; $i<$obc; $i++){
			$f=mysqli_fetch_array($objects);
			
			//проверим, есть ли такой объект в таблице прав. 
			//если нет - добавим
			$has_object=false;
			foreach(self::$rights_table as $k=>$v){
				if($v['object_id']==$f['object_id']){
					$has_object=true;
					break;	
				}
			}
			
			if(!$has_object){
				  self::$rights_table[]=array(
				  'object_id'=>$f['object_id'],
				  'rights'=>array()
			  );
			}
			
			//получим массив прав для данного объекта
			//если права нет - добавим право
			$has_right=false;
			foreach(self::$rights_table as $k=>$v){
				$_rights=array();
				$_rights=$v['rights'];
				
				if(($f['object_id']==$v['object_id'])&&(!in_array($f['right_name'],$_rights))){
					$_rights[]=$f['right_name'];
					self::$rights_table[$k]['rights']=$_rights;	
				}
			}
		}
		
	
		
		
		/*
		echo '<pre>';
		print_r(self::$rights_table);
		echo '</pre>';
		*/
		//echo ' <strong>BUILD!</strong> ';	
	}
	
	
	public function GetRightsTable(){
		//return $this->rights_table;	
		return self::$rights_table;	
	}
	
	public function ClearRightsTable(){
		//$this->rights_table=NULL;	
		self::$rights_table=NULL;	
	}
	
	
	//проверить у пользователя право на объект
	public function CheckAccess($right_letter, $object_id){
		$result=false;
		
		
		//foreach($this->rights_table as $k=>$v){
		foreach(self::$rights_table as $k=>$v){
			if($v['object_id']==$object_id){
				if(in_array($right_letter,$v['rights'])){
					 $result=true;
					 break;
				}
			}
		}
		
		
		return $result;
	}
	
	//проверить у пользователя многие права на объект
	public function CheckAccessArr(array $right_letters, $object_id){
		$result=true;
		
		$count_of_obs=0;
		//foreach($this->rights_table as $k=>$v){
		foreach(self::$rights_table as $k=>$v){
			if($v['object_id']==$object_id){
				$count_of_obs++;
				
				foreach($right_letters as $kk=>$vv){
					if(in_array($vv,$v['rights'])) $result=$result&&true;
					else $result=$result&&false;
				}
				
			}
		}
		
		if($count_of_obs==0) $result=$result&&false;
		
		return $result;
	}
	
	
	//дать пользователю право на объект
	public function GrantAccess($user_id, $right_letter, $object_id){
		$result=parent::GrantAccess($user_id,$right_letter,$object_id);
		$this->BuildRightsTable();
		return $result;
	}
	
	//снять у пользователя право на объект
	public function RevokeAccess($user_id, $right_letter, $object_id){
		parent::RevokeAccess($user_id, $right_letter, $object_id);
		$this->BuildRightsTable();
	}
	
}

?>