<?

require_once('abstractitem.php');

//абстрактный элемент
class OrgItem extends AbstractItem{
	protected $is_org;
	protected $is_org_name;
	
	public function __construct($is_org=1){
		$this->init($is_org);
	}
	
	//установка всех имен
	protected function init($is_org){
		$this->is_org=$is_org;
		$this->is_org_name='is_org';
		
		$this->tablename='supplier';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
	
	
	//добавить 
	public function Add($params){
		$params[$this->is_org_name]=$this->is_org;
		
		
		return parent::Add($params);
	}
	
	//править
	public function Edit($id,$params){
		$params[$this->is_org_name]=$this->is_org;
		
		return parent::Edit($id,$params);
	}
	
	
	
	//удалить
	public function Del($id){
		
		
		if($this->CanDelete($id)){
		
		  AbstractItem::Del($id);
		}
	}	
	
	
	//получение первого итема по набору полей
	public function GetItemByFields($params){
		$params[$this->is_org_name]=$this->is_org;
		return parent::GetItemByFields($params);
	}
	
	
	
	
	public function GetOne(){
		
		$au=new AuthUser();
		$result=$au->Auth();
		
		$ts=new mysqlSet('select * from '.$this->tablename.' where id="'.$result['org_id'].'"');
		$rs=$ts->GetResult();
		$rc=$ts->GetResultNumRows();
		if($rc==0) return false;
		else{
		  $f=mysqli_fetch_array($rs);
		  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		  return $f;
		}
	}
	
	
	//контроль возможности удаления
	public function CanDelete($id){
		$can_delete=true;
		
		
		
		
		$set=new mysqlSet('select count(kv.*) from komplekt_ved as kv inner join komplekt_ved_confirm as kc on kv.id=kc.komplekt_ved_id where kv.status_id in(2, 12, 13) and kv.org_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
	
	
		$set=new mysqlSet('select count(*) from bill where (is_confirmed_price=1 or is_confirmed_shipping=1) and org_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		/*$set=new mysqlSet('select count(*) from sh_i where is_confirmed=1 and org_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;*/
		
		$set=new mysqlSet('select count(*) from acceptance where is_confirmed=1 and org_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		$set=new mysqlSet('select count(*) from trust where is_confirmed=1 and org_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		$set=new mysqlSet('select count(*) from payment where is_confirmed=1 and org_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		 
		
		
		
		
		return $can_delete;
	}
	
	
	
	//сколько еще в программе активных организаций?***
	public function CalcOtherActive($org_id){
		$sql='select count(*) from '.$this->tablename.' where is_org=1 and is_active=1 and id<>"'.$org_id.'" ';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
	}
}
?>