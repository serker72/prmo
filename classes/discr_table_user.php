<?
require_once('discr_objectitem.php');
require_once('discr_useritem.php');
require_once('discr_table.php');
require_once('discr_man_group.php');


//отображение таблицы прав пользователя
class DiscrTableUser extends DiscrTable{
	protected $right_letters;
	protected $discr_man;
	
	protected $user_id;
	protected $us;
	protected $user;
	
	
	//массив всех прав пользователя
	protected $rights_array;
	
	function __construct($user_id){
		$this->discr_man=new DiscrMan;
		$this->user_id=$user_id;
		$this->us=new DiscrUserItem;
		$this->user=$this->us->GetItemById($this->user_id);
		
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
		$set=new mysqlSet('select ru.object_id, r.name from user_rights as ru inner join rights as r on ru.right_id=r.id where ru.user_id="'.$this->user_id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);	
			
			$this->rights_array[]=$f;
		}
		
	}
	
	
	public function Draw($pagename, $template, $skipping_groups=NULL, $skipping_objects=NULL){
		$txt='';
		$sm=new SmartyAdm;
		
		
		$sm->assign('filename',$pagename);
		$sm->assign('user',$this->user);
		
		$sm->assign('o',$this->GetObjectsArr($skipping_groups, $skipping_objects));
		
		
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	public function DrawArr(){
		$arr=array();
		
		$arr['user']=$this->user;
		
		$arr['o']=$this->GetObjectsArr( );
		
		
		return $arr;
	}
	
	public function GetObjectsArr($skipping_groups=NULL, $skipping_objects=NULL){
		$arr=array();
		 
		
		$grp_id=0;
		
		$flt=''; $_flt=array();
		if(($skipping_groups!==NULL)&&is_array($skipping_groups)){
			$_flt[]='  g.id not in('.implode(', ', $skipping_groups).') ';
		}
		
		if(($skipping_objects!==NULL)&&is_array($skipping_objects)){
			$_flt[]='  o.id not in('.implode(', ', $skipping_objects).') ';
		}
		if(count($_flt)>0) $flt=' where '.implode(' and ', $_flt);
		
		$sql='select o.*, g.name as g_name, g.description as g_description, g.id as grp_id from object_group as g left join object as o on o.group_id=g.id '.$flt.' order by g.ord desc, g.id asc, o.ord desc, o.id asc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$cc=0;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($grp_id!=$f['grp_id']) {
				$f['to_change']=true;
				$cc=0;
				
				
				$_flt=array();
				if(($skipping_objects!==NULL)&&is_array($skipping_objects)){
					$_flt[]='  id not in('.implode(', ', $skipping_objects).') ';
				}
						
				if(count($_flt)>0) $flt=' and '.implode(' and ', $_flt);
				
				$sql='select count(*) from object where group_id='.$f['grp_id'].' '.$flt;
				
				$set1=new mysqlSet($sql);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$g=mysqli_fetch_array($rs1);
				$f['objects_count']=$g[0];
			}
			else $f['to_change']=false;
			
			if($cc==1) {
				$f['add_h']=true;
				$cc=0;
			}
			else $f['add_h']=false;
			
			
			$rights_arr=array();
			
			
			$rights=array();
			if($f['id']!=''){
			foreach($this->right_letters as $kkk=>$vvv){
				//$access=$this->discr_man->CheckAccess($this->user_id, $vvv['name'], $f['id'] );
				$access=$this->FindInRights($f['id'],$vvv['name']);
				$rights[]=array(
									'has_access'=>$access,
									'letter'=>$vvv['name'],
									'object_id'=>$f['id'],
									'object_name'=>$f['name']
									);
			}
			}
			
			$rights_arr[]=array('user_id'=>$this->user_id, 'user_name'=>$this->user['login'], 'rights'=>$rights);
			
			
			
			//echo 'zzz';
			$f['rights_arr']=$rights_arr;
			$arr[]=$f;
			$grp_id=$f['grp_id'];
			$cc++;
		}
		
		
		/*
		$arr=array();
		
		$grp_id=0;
		$set=new mysqlSet('select o.*, g.name as g_name, g.description as g_description, g.id as grp_id from object_group as g left join object as o on o.group_id=g.id order by g.ord desc, g.id asc, o.ord desc, o.id asc'); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$cc=0;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($grp_id!=$f['grp_id']) {
				$f['to_change']=true;
				$cc=0;
				$set1=new mysqlSet('select count(*) from object where group_id='.$f['grp_id'].'');
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$g=mysqli_fetch_array($rs1);
				$f['objects_count']=$g[0];
			}
			else $f['to_change']=false;
			
			if($cc==1) {
				$f['add_h']=true;
				$cc=0;
			}
			else $f['add_h']=false;
			
			
			$rights_arr=array();
			
			
			$rights=array();
			if($f['id']!=''){
			foreach($this->right_letters as $kkk=>$vvv){
				$access=$this->discr_man->CheckAccess($this->user_id, $vvv['name'], $f['id'] );//$f['id'], $vvv['name'], $this->object_id);
				
				$rights[]=array(
									'has_access'=>$access,
									'letter'=>$vvv['name'],
									'object_id'=>$f['id'],
									'object_name'=>$f['name']
									);
			}
			}
			
			$rights_arr[]=array('user_id'=>$this->user_id, 'user_name'=>$this->user['login'], 'rights'=>$rights);
			
			
			
			//echo 'zzz';
			$f['rights_arr']=$rights_arr;
			$arr[]=$f;
			$grp_id=$f['grp_id'];
			$cc++;
		}
		
		*/
		return $arr;	
		
	}
	
	
	protected function FindInRights($object_id, $right_letter){
		$res=false;
		
		foreach($this->rights_array as $k=>$v){
			if(($v['name']==$right_letter)&&($v['object_id']==$object_id)) return true;	
		}
		
		return $res;
	}
	
	
	
}
?>