<?
require_once('discr_table.php');
require_once('discr_man_group.php');

//отображение таблицы прав групп
class DiscrTableGroup extends DiscrTable{
	protected $objects;
	protected $right_letters;
	protected $discr_man;
	
	//массив всех прав 
	protected $rights_array;
	
	function __construct(){
		$this->discr_man=new DiscrManGroup;
		
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
		$set=new mysqlSet('select ru.object_id, ru.group_id, r.name from group_rights_template as ru inner join rights as r on ru.right_id=r.id ');
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
		$sm=new SmartyAdm;
		
		$sm->assign('obg',$this->GetObgArr());
		$sm->assign('obj',$this->objects);
		
		$sm->assign('u',$this->GetUsersArr());
		
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	
	public function GetUsersArr(){
		$arr=array();
		
		 
		$set=new mysqlSet('select g.name as g_name, g.description as g_description, g.id as id from groups as g order by g.id asc'); 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			/*if($grp_id!=$f['grp_id']) {
				$f['to_change']=true;
				$cc=0;
				$set1=new mysqlSet('select count(*) from user where group_id='.$f['grp_id'].'');
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$g=mysqli_fetch_array($rs1);
				$f['users_count']=$g[0];
			}
			else $f['to_change']=false;*/
			
			/*
			if($cc==1) {
				$f['add_h']=true;
				$cc=0;
			}
			else $f['add_h']=false;
			*/
			
			
			$rights_arr=array();
			
			//получим объекты и права на них
			foreach($this->objects as $kk=>$vv){
				
				$rights=array();
				foreach($this->right_letters as $kkk=>$vvv){
					//$access=$this->discr_man->CheckAccess($f['id'], $vvv['name'], $vv['id']);
					
					$access=$this->FindInRights($f['id'], $vv['id'], $vvv['name']);
					$rights[]=array(
										'has_access'=>$access,
										'letter'=>$vvv['name'],
										'object_id'=>$vv['id'],
										'object_name'=>$vv['name']
										);
				}
				
				$rights_arr[]=array('object_id'=>$vv['id'], 'object_name'=>$vv['name'], 'rights'=>$rights);
			}
			
			
			//echo 'zzz';
			$f['rights_arr']=$rights_arr;
			$arr[]=$f;
			
		}
		

		return $arr;	
	}
	
	
	
	public function GetObgArr(){
		$this->objects=array();
		$arr=array();
		$set=new mysqlSet('select * from object_group order by ord desc, id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		
			
			$set1=new mysqlSet('select * from object where group_id="'.$f['id'].'" order by ord desc, id asc');
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			for($j=0; $j<$rc1; $j++){
				
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				if($j==($rc1-1)) $g['to_change']=true;
				else $g['to_change']=false;
				
				$this->objects[]=$g;
				
			}
			
			$f['objects_count']=$rc1;
			
			
			$arr[]=$f;
		}
		
		return $arr;	
	}
	
	
	protected function FindInRights($group_id, $object_id, $right_letter){
		$res=false;
		
		foreach($this->rights_array as $k=>$v){
			if(($v['group_id']==$group_id)&&($v['name']==$right_letter)&&($v['object_id']==$object_id)) return true;	
		}
		
		return $res;
	}
	
	
}
?>