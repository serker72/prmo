<?
require_once('obitem.php');

//отображение таблицы прав пользователей
class DiscrTable{
	protected $objects;
	protected $right_letters;
	protected $discr_man;
	
	//массив всех прав 
	protected $rights_array;
	protected $gr_id=NULL;
	
	function __construct($gr_id=NULL){
		$this->discr_man=new DiscrMan;
		$this->gr_id=$gr_id;
		
		
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
		$sql='select ru.object_id, ru.user_id, r.name from user_rights as ru left join rights as r  on ru.right_id=r.id inner join object as o on ru.object_id=o.id ';
		
		if($this->gr_id!==NULL) $sql.=' where o.group_id="'.$this->gr_id.'"';
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
		$sm=new SmartyAdm;
		
		$sm->assign('obg',$this->GetObgArr());
		$sm->assign('obj',$this->objects);
		
		$sm->assign('u',$this->GetUsersArr());
		
		
		
		$set=new mysqlSet('select * from object_group order by name asc, id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$p=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$p[]=$f;
		}
		
		$sm->assign('p',$p);
		$sm->assign('gr_id',$this->gr_id);
		
		
		
		$txt=$sm->fetch($template);
		return $txt;
	}
	
	
	public function GetUsersArr(){
		$arr=array();
		
		$grp_id=0;
		$set=new mysqlSet('select u.*, g.name as g_name, g.description as g_description, g.id as grp_id from groups as g right join user as u on u.group_id=g.id order by  u.login asc, g.id asc'); 
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
			
			/*
			
			
			$rights_arr=array();
			if($f['id']!=''){
			//получим объекты и права на них
			foreach($this->objects as $kk=>$vv){
				
				$rights=array();
				foreach($this->right_letters as $kkk=>$vvv){
					
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
			}
			
			//echo 'zzz';
			$f['rights_arr']=$rights_arr;
			
			
			*/
			
			$grp_id=$f['grp_id'];
			$cc++;
			
			$arr[]=$f;
			
		}
		

		return $arr;	
	}
	
	
	
	public function GetObgArr(){
		$this->objects=array();
		$arr=array();
		if($this->gr_id!==NULL){
			$set=new mysqlSet('select * from object_group where id="'.$this->gr_id.'" order by name asc, id asc');
		}else $set=new mysqlSet('select * from object_group order by name asc, id asc');
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
			
			//print_r($f);
			$arr[]=$f;
		}
		
		return $arr;	
	}
	
	
	protected function FindInRights($user_id, $object_id, $right_letter){
		$res=false;
		
		foreach($this->rights_array as $k=>$v){
			if(($v['user_id']==$user_id)&&($v['name']==$right_letter)&&($v['object_id']==$object_id)) return true;	
		}
		
		return $res;
	}
	
	
	//получим €чейку таблицы прав
	public function GetTableCellArr($user_id, $object_id){
		
		
		$rights=array();
		foreach($this->right_letters as $kkk=>$vvv){
			
			$access=$this->FindInRights($user_id, $object_id, $vvv['name']);
			
			$_ob=new ObjectItem;
			$ob=$_ob->getitembyid($object_id);
			
			$rights[]=array(
								'has_access'=>$access,
								'letter'=>$vvv['name'],
								'object_id'=>$object_id,
								'object_name'=>$ob['name']
								);
		}
		
		return $rights;
	}
	
	//получим р€д таблицы прав
	public function GetTableRowArr($user_id){
		$rights_arr=array();
		
		$this->GetObgArr();
			
			//получим объекты и права на них
			foreach($this->objects as $kk=>$vv){
				
				//var_dump($vv); echo "ZZZZZZZZZZZZZZ";
				//if(($this->gr_id!==NULL)&&($this->gr_id!==$vv['group_id'])) continue;
				
				$rights=array();
				foreach($this->right_letters as $kkk=>$vvv){
					
					$access=$this->FindInRights($user_id, $vv['id'], $vvv['name']);
					$rights[]=array(
										'has_access'=>$access,
										'letter'=>$vvv['name'],
										'object_id'=>$vv['id'],
										'object_name'=>$vv['name'],
										'anti_id'=>$vv['anti_id']
										);
				}
				
				$rights_arr[]=array('object_id'=>$vv['id'], 'object_name'=>$vv['name'] ,
										'anti_id'=>$vv['anti_id'], 'rights'=>$rights);
			
			}
			//echo 'z';
			
			return $rights_arr;
	}
	
}
?>