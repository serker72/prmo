<?
require_once('abstractgroup.php');
//require_once('billpospmformer.php');
//require_once('maxformer.php');
require_once('invitem.php');
require_once('invreports.php');

require_once('posonas_mod.php');
require_once('authuser.php');

// абстрактная группа
class InvPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='inventory_position';
		$this->pagename='view.php';		
		$this->subkeyname='inventory_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0,$result=NULL){
		$arr=array();
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		$_pm=new PositionsOnAssortimentMod;
		
		
		$_bill=new InvItem;
		$bill=$_bill->GetItemById($id);
		//$_bpf=new BillPosPMFormer;
		$_ir=new InvReports;
		
		
		$_mf=new MaxFormer;
		$sql='select p.id as p_id, p.inventory_id,  p.position_id as id, p.position_id as position_id,
					 
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity_as_is, p.quantity_fact, p.quantity_initial,
					 pd.id as dimension_id
					
		
		from '.$this->tablename.' as p 
			
			left join catalog_dimension as pd on pd.name=p.dimension
			
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			
			
			
			$f['quantity_by_program']=$_pm->FindOstByDate($f['id'],   date('d.m.Y',$bill['inventory_pdate']),$result['org_id']);
			
			$f['in_acc']=$_ir->CountInAcc($f['id'],   $result['org_id'],$id);
			$f['in_wf']=$_ir->CountInWf($f['id'],   $result['org_id'],$id);
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
}
?>