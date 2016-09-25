<?
require_once('billpospmformer.php');
require_once('iswf_group.php');

// абстрактная группа
class IsPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='interstore_position';
		$this->pagename='view.php';		
		$this->subkeyname='interstore_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0){
		$arr=array();
		//echo ' called <br> ';
		$_iwg=new IswfGroup;
		
		$_bpf=new BillPosPMFormer;
		$sql='select p.id as p_id, p.interstore_id, p.komplekt_ved_pos_id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.quantity_initial,
					 pd.id as dimension_id		 
		
		from '.$this->tablename.' as p 
			
			left join catalog_dimension as pd on pd.name=p.dimension
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		//найдем связ пост и связ акт списания для позиций...
		$acc_id=$this->GetAccId($id);
		
		$wf_id=$this->GetWfId($id);
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$f['fact_quantity']=$_iwg->FactKol($id,$f['id']);
			
			//найти количества в связ утв поступлениях
			//и в связ не аннул актах списания
			
			//$f['fact_received']=
			
			
			$f['fact_received']=$this->GetInAcc($acc_id, $f['id']);
			
			$f['fact_received_id']=$acc_id;
			
			
			
			$f['fact_writeoff']=$this->GetInWf($wf_id,$f['id']);
			//$f['fact_writeoff']=
			$f['fact_writeoff_id']=$wf_id;
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	public function GetAccId($id){
		$sql1='select id from acceptance where interstore_id='.$id.' and is_confirmed=1';
		$set1=new MysqlSet($sql1);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		$acc_id=0;
		if($rc1>0){
			$g=mysqli_fetch_array($rs1);
			$acc_id=$g[0];	
		}
		
		return $acc_id;
	}
	
	public function GetWfId($id){
		$sql1='select id from interstore where status_id<>3 and interstore_id='.$id.'';
		$set1=new MysqlSet($sql1);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		$wf_id=0;
		if($rc1>0){
			$g=mysqli_fetch_array($rs1);
			$wf_id=$g[0];	
		}
		
		return $wf_id;
	}
	
	public function GetInAcc($acc_id, $position_id){
		$sql1='select sum(quantity) from acceptance_position where acceptance_id='.$acc_id.' and position_id='.$position_id.'';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			$g=mysqli_fetch_array($rs1);
			
			return (float)$g[0];
	}
	
	public function GetInWf($wf_id, $position_id){
		$sql1='select sum(quantity) from interstore_position where interstore_id='.$wf_id.' and position_id='.$position_id.'';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			$g=mysqli_fetch_array($rs1);
			
			return (float)$g[0];
	}
	
}
?>