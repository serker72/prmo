<?
require_once('abstractgroup.php');

class PayReports extends AbstractGroup{
	
	public function InPays($bill_id,$template,$org_id,$is_ajax=true,$except_id=NULL,$kind=0){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		//$sql='select sum(bp.value) from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.bill_id="'.$id.'" ';
		
		$flt='';
		if($except_id!==NULL) $flt.=' and p.id<>"'.$except_id.'"'; 
		//echo $kind;		
		if($kind==0){
			$sql='select distinct p.id, p.code, p.pdate, p.given_pdate, p.given_no, p.value from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.bill_id="'.$bill_id.'" and  p.org_id="'.$org_id.'" '.$flt;
		}else{
			$sql='select distinct p.id, p.code, p.pdate, p.given_pdate, p.given_no, p.value from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.invcalc_id="'.$bill_id.'" and  p.org_id="'.$org_id.'" '.$flt;
		}
		
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
			$f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
}
?>