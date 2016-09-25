<?
require_once('abstractgroup.php');
require_once('isitem.php');
require_once('is_custom_group.php');

require_once('iswfposgroup.php');

// абстрактная группа
class IswfGroup extends AbstractGroup {
	protected $wfpos;
	
	//установка всех имен
	protected function init(){
		$this->tablename='interstore_wf';
		$this->pagename='interstore.php';		
		$this->subkeyname='interstore_id';	
		$this->vis_name='is_shown';		
		$this->wfpos=new IsWfPosGroup;
		
	}
	
	public function FactKol($is_id,$position_id){
		//$_iswfp=new IsWfPosGroup;
		return $this->wfpos->FactKol($is_id,$position_id);	
	}
	
	public function GetItemsByIdArr($id){
		$arr=array();
		$set=new MysqlSet('select p.*,
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
		
		 from '.$this->tablename.' as p 
		 left join user as mn on p.manager_id=mn.id
		 
		 where p.'.$this->subkeyname.'="'.$id.'" order by p.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$_pos=new IsWfPosGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			$pos=$_pos->GetItemsByIdArr($f['id']);
			
			$f['positions']=$pos;
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>