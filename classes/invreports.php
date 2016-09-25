<?
require_once('abstractgroup.php');

class InvReports extends AbstractGroup{


	
	public function InAcc($kvid,  $template,$org_id,$is_ajax=true,$inventory_id){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select distinct s.id, s.pdate, s.bill_id, s.inventory_id, b.inventory_pdate,  bp.quantity , bb.pdate as bill_pdate, bb.code  
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join inventory as b on s.inventory_id=b.id 
		left join bill as bb on s.bill_id=bb.id
		where 
		s.is_incoming=1 
		and bp.position_id="'.$kvid.'" 
	 
		and b.id="'.$inventory_id.'" 
		and s.is_confirmed=1  
		and s.org_id="'.$org_id.'"';
	
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
			
			$f['inventory_pdate']=date("d.m.Y",$f['inventory_pdate']);
			
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	public function InWf($kvid, $template,$org_id,$is_ajax=true,$inventory_id){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
	 
		
		$sql='select distinct s.id, s.pdate, s.bill_id, s.inventory_id, b.inventory_pdate,  bp.quantity , bb.pdate as bill_pdate, bb.code  
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join inventory as b on s.inventory_id=b.id 
		left join bill as bb on s.bill_id=bb.id
		where
		s.is_incoming=0 
		and bp.position_id="'.$kvid.'" 
	 
		and b.id="'.$inventory_id.'" 
		and s.is_confirmed=1  
		and s.org_id="'.$org_id.'"';
	
	
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
			
			$f['inventory_pdate']=date("d.m.Y",$f['inventory_pdate']);
			
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	public function CountInAcc($kvid,  $org_id,$inventory_id){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select sum(bp.quantity) as s_q
		from acceptance as s
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join inventory as b on s.inventory_id=b.id 
		where bp.position_id="'.$kvid.'" 
		and s.is_incoming=1
	 
		and b.id="'.$inventory_id.'" and s.is_confirmed=1  and s.org_id="'.$org_id.'"
		group by s.inventory_id
		';
	
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		return (float)$f['s_q'];
	}
	
	public function CountInWf($kvid,   $org_id,$inventory_id){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		 
		
		$sql='select sum(bp.quantity) as s_q
		from acceptance as s
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join inventory as b on s.inventory_id=b.id 
		where bp.position_id="'.$kvid.'" 
		and s.is_incoming=0
		 
		and b.id="'.$inventory_id.'" and s.is_confirmed=1  and s.org_id="'.$org_id.'"
		group by s.inventory_id
		';
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		
		return (float)$f['s_q'];
	}
}
?>