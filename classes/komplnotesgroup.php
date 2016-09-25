<?
require_once('notesgroup.php');

// абстрактная группа
class KomplNotesGroup extends NotesGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_notes';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0, $document_confirmed=false, $can_delete_confirmed=false, $can_delete_auto=false,$user_id=0, $can_delete_everybody=false,$has_right_to_delete_of_all_users=NULL ){
		
		$dm=new DiscrMan;
		
		if($has_right_to_delete_of_all_users===NULL) $has_right_to_delete=$dm->CheckAccess($user_id,'w',357);
		else $has_right_to_delete=$has_right_to_delete_of_all_users;
		
		
		$arr=array();
		$set=new MysqlSet('select p.*, u.name_s as user_name_s, u.login as user_login from '.$this->tablename.' as p left join user as u on p.posted_user_id=u.id where p.'.$this->subkeyname.'="'.$id.'" order by p.pdate desc, p.id desc');
		/*else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'="1" order by ord desc, id asc');*/
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$can_delete=true;
			if($document_confirmed&&(!$can_delete_confirmed)) $can_delete=$can_delete&&false;
			
			if(isset($f['is_auto'])&&($f['is_auto']==1)){
				if(!$can_delete_auto) $can_delete=$can_delete&&false;
			}
			
			if(($user_id!=$f['posted_user_id'])&&($user_id>0)){
				
				if(!$can_delete_everybody)
					if(!$has_right_to_delete) $can_delete=$can_delete&&false;	
				//echo $user_id.'<br />';
				//var_dump();
			}
			
			
			$f['can_delete']=$can_delete;
			
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>