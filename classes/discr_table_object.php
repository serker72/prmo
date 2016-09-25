<?
require_once('discr_objectitem.php');
require_once('discr_table.php');
require_once('discr_man_group.php');


//отображение таблицы прав на объект
class DiscrTableObject extends DiscrTable{
	protected $right_letters;
	protected $discr_man;
	
	protected $object_id;
	protected $obj;
	protected $object;
	
	//массив всех прав на объект
	protected $rights_array;
	
	
	
	function __construct($object_id){
		$this->discr_man=new DiscrMan;
		$this->object_id=$object_id;
		$this->obj=new DiscrObjectItem;
		$this->object=$this->obj->GetItemById($this->object_id);
		
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
		$set=new mysqlSet('select ru.user_id, r.name from user_rights as ru inner join rights as r on ru.right_id=r.id where ru.object_id="'.$this->object_id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);	
			
			$this->rights_array[]=$f;
		}
		
		/*echo '<pre>';
		print_r($this->rights_array);
		echo '</pre>';*/
	}
	
	
	public function Draw($template){
		$txt='';
		$sm=new SmartyAdm;
		
		
		$sm->assign('obj',$this->object);
		
		$sm->assign('u',$this->GetUsersArr());
		
		
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	public function DrawArr(){
		$arr=array();
		
		$arr['obj']=$this->object;
		
		$arr['u']=$this->GetUsersArr();
		
		
		return $arr;
	}
	
	
	
	
	public function GetUsersArr(){
		$arr=array();
		
		$grp_id=0;
		$set=new mysqlSet('select u.*, g.name as g_name, g.description as g_description, g.id as grp_id from groups as g left join user as u on u.group_id=g.id order by g.id asc, u.login asc'); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$cc=0;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($grp_id!=$f['grp_id']) {
				$f['to_change']=true;
				$cc=0;
				$set1=new mysqlSet('select count(*) from user where group_id='.$f['grp_id'].'');
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$g=mysqli_fetch_array($rs1);
				$f['users_count']=$g[0];
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
				$access=$this->FindInRights($f['id'], $vvv['name']);
				$rights[]=array(
									'has_access'=>$access,
									'letter'=>$vvv['name'],
									'object_id'=>$this->object_id,
									'object_name'=>$this->object['name']
									);
				
			}
			}
			
			
			
			$rights_arr[]=array('object_id'=>$this->object_id, 'object_name'=>$this->object['name'], 'rights'=>$rights);
			
			
			
			//echo 'zzz';
			$f['rights_arr']=$rights_arr;
			$arr[]=$f;
			$grp_id=$f['grp_id'];
			$cc++;
		}
			
			
			
			
		/*
		$grp_id=0;
		$set=new mysqlSet('select u.*, g.name as g_name, g.description as g_description, g.id as grp_id from groups as g left join user as u on u.group_id=g.id order by g.id asc, u.login asc'); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$cc=0;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($grp_id!=$f['grp_id']) {
				$f['to_change']=true;
				$cc=0;
				$set1=new mysqlSet('select count(*) from user where group_id='.$f['grp_id'].'');
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$g=mysqli_fetch_array($rs1);
				$f['users_count']=$g[0];
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
				$access=$this->discr_man->CheckAccess($f['id'], $vvv['name'], $this->object_id);
				$rights[]=array(
									'has_access'=>$access,
									'letter'=>$vvv['name'],
									'object_id'=>$this->object_id,
									'object_name'=>$this->object['name']
									);
			}
			}
			
			$rights_arr[]=array('object_id'=>$this->object_id, 'object_name'=>$this->object['name'], 'rights'=>$rights);
			
			
			
			//echo 'zzz';
			$f['rights_arr']=$rights_arr;
			$arr[]=$f;
			$grp_id=$f['grp_id'];
			$cc++;
		}
		*/		

		return $arr;	
	}
	
	
	protected function FindInRights($user_id, $right_letter){
		$res=false;
		
		foreach($this->rights_array as $k=>$v){
			if(($v['name']==$right_letter)&&($v['user_id']==$user_id)) return true;	
		}
		
		return $res;
	}
	
	
}
?>