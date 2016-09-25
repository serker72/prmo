<?
require_once('rl_man.php');
require_once('rl_grobjectitem.php');
require_once('rl_recorditem.php');

//require_once('discr_man.php');

//отображение таблицы прав пользователей
class RlShareTable{
	protected $objects;
	protected $right_letters;
	protected $discr_man;
	
	protected $common_discr_man;
	
	//массив всех прав 
	protected $rights_array;
	protected $gr_id=NULL;
	protected $rl_group;
	protected $rl_record_id=NULL;
	
	protected $record_id=NULL;
	
	protected $result;
	protected $result_is_admin;
	
	protected $parent_record_id;
	
	function __construct( $rl_group_id, $rl_record_id, $result, $parent_record_id){
		$this->discr_man=new RLMan;
		$this->common_discr_man=new DiscrMan;
		$this->result_is_admin=$this->common_discr_man->CheckAccess($result['id'],'w',118);
		
		$this->gr_id=$rl_group_id;
		$_rl_group=new RLGrObjectItem;
		$this->rl_group=$_rl_group->GetItemById($rl_group_id);
		$this->rl_record_id=$rl_record_id;
		
		$_rl_rec=new RLRecordItem;
		$rl_rec=$_rl_rec->GetItemById($rl_record_id);
		$this->record_id=$rl_rec['record_id'];
		
		$this->result=$result;
		$this->parent_record_id=$parent_record_id;
		
		$this->right_letters=array();
		$set=new mysqlSet('select * from rights order by id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);	
			
			$this->right_letters[]=$f;
		}
		
		 
		
		$this->rights_array=array();
		$sql='select ru.rl_object_id, ru.user_id, r.name 
		from rl_user_rights as ru 
		left join rights as r  on ru.right_id=r.id 
		inner join rl_object as o on ru.rl_object_id=o.id 
		
		where o.rl_group_id="'.$rl_group_id.'"
		and ru.rl_record_id="'.$rl_record_id.'"
		';
		
		 
		//echo $sql; 
		 
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);	
			
			$this->rights_array[]=$f;
		}
	}
	
	
	public function Draw($template){
		$txt='';
		$sm=new SmartyAj;
		
		$sm->assign('obg',$this->GetObgArr());
		
		
		$sm->assign('u',$this->GetUsersArr());
		
		
		
		
		
		
		$sm->assign('gr_id',$this->gr_id);
		
		
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	
	public function GetUsersArr(){
		$arr=array();
		
		$grp_id=0;
		$sql='select u.*, g.name as g_name, g.description as g_description, g.id as grp_id,
			ur.id as is_admin 
			
			
			from groups as g right join user as u on u.group_id=g.id 
			left join user_rights as ur on ur.user_id=u.id and ur.right_id=2 and ur.object_id=118
			
			where u.is_active=1  and u.group_id=1  /* and u.id<>"'.$this->result['id'].'"*/ order by  u.name_s asc, g.id asc';
		
		
		//echo $sql;
		
		$set=new mysqlSet($sql); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$cc=0;
		
		$objects=$this->GetObgArr();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			
			//строим массив прав
			$rights_arr=array();
			foreach($objects as $k=>$rl_object){
				$entry=array();
				
				//определим активность!
				$is_active=true;
				if(($f['is_admin']!="")&&(!$this->result_is_admin)) $is_active=$is_active&&false;
				if($f['id']==$this->result['id'])  $is_active=$is_active&&false;
				
				//проверим наличие прав!
				$has_rrights=$this->discr_man->CheckFullAccess($this->result['id'], $this->parent_record_id, $rl_object['id'],   'w',  $this->rl_group['tablename'], $this->rl_group['additional_id'], $has_control);
				//var_dump($has_rrights);
				   
				if($has_control){
					$is_active=$is_active&&($has_rrights);
				}else{
					//контроля нет - проверим базовые права
					$is_active=$is_active&&($this->common_discr_man->CheckAccess($this->result['id'],'w',$rl_object['parent_object_id']));
				}
				
				$entry['is_active']=$is_active;
				
				
				//определим наличие галочки
				$is_checked=true;
				
				$has_rrights1=$this->discr_man->CheckFullAccess($f['id'], $this->record_id, $rl_object['id'],   'w',  $this->rl_group['tablename'], $this->rl_group['additional_id'], $has_control1);
				if($has_control1){
					$is_checked=$is_checked&&$has_rrights1;
				}else{
					//контроля нет - проверим базовые права
					$is_checked=$is_checked&&$this->common_discr_man->CheckAccess($f['id'], 'w',$rl_object['parent_object_id']);
				}
				
				$entry['is_checked']=$is_checked;
				
				
				$entry['object_id']=$rl_object['id'];
				$entry['has_inherits']=$rl_object['has_inherits'];
				$entry['object_name']=$rl_object['name'];
				$rights_arr[]=$entry;	
			}
			
			
			$f['rights_arr']=$rights_arr;
			
			
			
			
			// //CheckFullAccess($user_id, $record_id, $object_id, $right_letter, $tablename, $additional_id=0, &$has_control)
			 
			
			
			$arr[]=$f;
			
		}
		

		return $arr;	
	}
	
	
	
	
	
	
	protected function FindInRights($record_id, $user_id, $object_id, $right_letter){
		$res=false;
		
		/*foreach($this->rights_array as $k=>$v){
			if(($v['user_id']==$user_id)&&($v['name']==$right_letter)&&($v['rl_object_id']==$object_id)&&($v['rl_record_id']==$record_id)) return true;	
		}
		*/
		$res=$this->discr_man->CheckAccess($record_id,$user_id,$right_letter,$object_id);
		
		return $res;
	}
	
	
	  
	
	
	public function GetObgArr(){
		$this->objects=array();
		$arr=array();
	 
		 
		$sql='select rl.*, rc.object_id as parent_object_id, rc.has_inherits
		 from
		 rl_object as rl
		 left join rl_connections as rc on rc.rl_object_id=rl.id
		 where rl.rl_group_id="'.$this->gr_id.'" order by rl.ord desc, rl.id asc';
	//	echo $sql;
			
			$set1=new mysqlSet($sql);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			for($j=0; $j<$rc1; $j++){
				
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				if($j==($rc1-1)) $g['to_change']=true;
				else $g['to_change']=false;
				
				$this->objects[]=$g;
				
			}
			
			 
		
		return $this->objects;	
	}
	
}
?>