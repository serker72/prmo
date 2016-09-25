<?
require_once('authuser.php');

class MenuSecItem extends MenuItem{
	public function DeployItem($current_id=0){
		$arr=NULL;
		
		$au=new AuthUser;
		if($au->user_rights->CheckAccess('w', $this->object_id)){
			$arr=$this->CodeItem($current_id);	
		}else{
		
			//проверим доп. объекты
			$sql='select * from left_menu_new_objects where menu_id="'.$this->id.'"';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$can=false;
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$can=$can||$au->user_rights->CheckAccess('w', $f['object_id']);
				
			}
			if($can) $arr=$this->CodeItem($current_id);	
		}
		
		return $arr;
	}

}
?>