<?
require_once('abstractgroup.php');

class KomplReports extends AbstractGroup{


	public function InBills($kvid,$template,$org_id,$is_ajax=true,$komplekt_ved_id, $can_view_supplier=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select sum(bp.quantity) as quantity, b.id, b.code, b.pdate,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name 
		from bill as b 
		inner join bill_position as bp on b.id=bp.bill_id 
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		where  bp.position_id="'.$kvid.'" and bp.komplekt_ved_id="'.$komplekt_ved_id.'" and b.is_confirmed_shipping=1  and b.org_id="'.$org_id.'"
		and b.is_incoming=0
		group by b.id
		';
			
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
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		
		return $sm->fetch($template);
	}
	
	
	
	public function InBillsIn($kvid,$template,$org_id,$is_ajax=true,$komplekt_ved_id, $can_view_supplier=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select sum(bp.quantity) as quantity, b.id, b.code, b.pdate,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name 
		from bill as b 
		inner join bill_position as bp on b.id=bp.bill_id 
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		where  bp.position_id="'.$kvid.'" and bp.komplekt_ved_id="'.$komplekt_ved_id.'" and b.is_confirmed_shipping=1  and b.org_id="'.$org_id.'"
		and b.is_incoming=1
		group by b.id
		';
			
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
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		
		return $sm->fetch($template);
	}
	
	
	
	public function CountInBills($kvid, $position_id){
		$res=0;	
		$sql='select sum(bp.quantity) from bill_position as bp
		inner join bill as b on b.id=bp.bill_id 
		
		where  bp.position_id="'.$position_id.'" and bp.komplekt_ved_id="'.$kvid.'" and (b.is_confirmed_shipping=1 or b.is_confirmed_price=1)  and b.is_incoming=0';
		
		//echo $sql;
		
		
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$res=round((float)$f[0], 3);
		
		
		return $res;
	}
	
	
	//в невывезенном межскладе
	public function InIs($kvid,$template,$org_id,$is_ajax=true,$komplekt_ved_id, $can_view_supplier=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
	 
		
		$sql='select sum(isk.quantity) as quantity, iss.id, iss.pdate, iss.is_confirmed, iss.is_confirmed_wf 
		from interstore as iss
		inner join interstore_to_komplekt as isk on isk.interstore_id=iss.id
		where iss.status_id<>3 and iss.is_or_writeoff=0 and iss.is_confirmed=1 and iss.is_confirmed_wf=0 and iss.org_id="'.$org_id.'"
		and isk.position_id="'.$kvid.'" and isk.komplekt_ved_id="'.$komplekt_ved_id.'"
		 
		group by iss.id';
			
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
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		
		return $sm->fetch($template);
	}
	
	
	
	
	public function InSh($kvid,$template,$org_id,$is_ajax=true,$komplekt_ved_id, $can_view_supplier=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name  
		from sh_i as s  
		inner join sh_i_position as bp on s.id=bp.sh_i_id 
		inner join bill as b on s.bill_id=b.id    
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id 
		where bp.position_id="'.$kvid.'" and bp.komplekt_ved_id="'.$komplekt_ved_id.'"  and b.is_confirmed_shipping=1 and s.is_confirmed=1  and s.org_id="'.$org_id.'"';
		
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
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		
		return $sm->fetch($template);
	}
	
	public function InAcc($kvid,$template,$org_id,$is_ajax=true,$komplekt_ved_id, $can_view_supplier=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, sum(bp.quantity) as quantity,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name
		   
		from acceptance as s inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		where bp.position_id="'.$kvid.'" 
		and bp.komplekt_ved_id="'.$komplekt_ved_id.'" 
		and b.is_confirmed_shipping=1 
		and s.is_confirmed=1  
		and s.is_incoming=0
		and b.is_incoming=0
		and s.org_id="'.$org_id.'"
		group by  bp.acceptance_id
		';
	
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
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		return $sm->fetch($template);
	}
	
	
	public function InAccIn($kvid,$template,$org_id,$is_ajax=true,$komplekt_ved_id, $can_view_supplier=false){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sup.full_name as supplier_name, sup.id as supplier_id, opf.name as opf_name
		   
		from acceptance as s inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		left join supplier as sup on b.supplier_id=sup.id
		left join opf on opf.id=sup.opf_id
		where bp.position_id="'.$kvid.'" 
		and bp.komplekt_ved_id="'.$komplekt_ved_id.'" 
		and b.is_confirmed_shipping=1 
		and s.is_confirmed=1  
		and s.is_incoming=1
		and b.is_incoming=1
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
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		$sm->assign('can_view_supplier',$can_view_supplier);
		
		return $sm->fetch($template);
	}
}
?>