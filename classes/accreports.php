<?
require_once('abstractgroup.php');

class AccReports extends AbstractGroup{


	/*public function InBills($kvid,$template,$org_id,$is_ajax=true){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select distinct b.id, b.code, b.pdate, bp.quantity from bill as b inner join bill_position as bp on b.id=bp.bill_id where  bp.komplekt_ved_pos_id="'.$kvid.'"  and b.org_id="'.$org_id.'"';
	
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	*/
	
	public function InSh($kvid,$bill_id,$template,$org_id,$is_ajax=true, $sh_i_id=NULL,$storage_id=NULL,$sector_id=NULL,$komplekt_ved_id=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$flt='';
		if($sh_i_id!==NULL) $flt.=' and s.id="'.$sh_i_id.'" ';
		
		if($storage_id!==NULL) $flt.=' and s.storage_id="'.$storage_id.'" ';
		if($sector_id!==NULL) $flt.=' and s.sector_id="'.$sector_id.'" ';
		if($komplekt_ved_id!==NULL) $flt.=' and bp.komplekt_ved_id="'.$komplekt_ved_id.'" ';
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity  
		from sh_i as s  
		inner join sh_i_position as bp on s.id=bp.sh_i_id 
		inner join bill as b on s.bill_id=b.id    
		 where bp.position_id="'.$kvid.'" and b.id="'.$bill_id.'" and s.is_confirmed=1 '.$flt.' and s.org_id="'.$org_id.'"';
	
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	public function InAcc($kvid,$except_acc_id,$bill_id,$template,$org_id,$is_ajax=true, $sh_i_id=NULL,$storage_id=NULL,$sector_id=NULL,$komplekt_ved_id=NULL){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$flt='';
		//if($sh_i_id!==NULL) $flt.=' and s.sh_i_id="'.$sh_i_id.'" ';
		
		//if($storage_id!==NULL) $flt.=' and s.storage_id="'.$storage_id.'" ';
		//if($sector_id!==NULL) $flt.=' and s.sector_id="'.$sector_id.'" ';
		if($komplekt_ved_id!==NULL) $flt.=' and bp.komplekt_ved_id="'.$komplekt_ved_id.'" ';
		
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity   from acceptance as s inner join acceptance_position as bp on s.id=bp.acceptance_id inner join bill as b on s.bill_id=b.id where s.id<>"'.$except_acc_id.'" and bp.position_id="'.$kvid.'" and b.id="'.$bill_id.'" and s.is_confirmed=1 '.$flt.'  and s.org_id="'.$org_id.'"';
	
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		
		return $sm->fetch($template);
	}
}
?>