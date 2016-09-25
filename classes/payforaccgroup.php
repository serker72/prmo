<?
require_once('abstractgroup.php');
require_once('db_decorator.php');
require_once('billitem.php');
require_once('invcalcitem.php');
require_once('abstract_paygroup.php');
require_once('paygroup.php');
require_once('paygroup_simple.php');

require_once('invcalcitem.php');

// абстрактная группа
class PayForAccGroup extends AbstractGroup {
	public $_bill_item;
	public $_pay_item;
	public $_pay_group;
	public $_invcalc_item;
	public $_bill_pagename;
	public $_bill_fact_pays_template;
	public $_invcalc_fact_pays_template;
	protected $_is_incoming=0;
	protected $_bill_is_incoming=0;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment_for_acceptance';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_bill_item=new BillItem;
		$this->_pay_item=new PayInItem;
/*		$this->_pay_group=new PayGroup_Simple;*/
		$this->_invcalc_item=new InvCalcItem;
		$this->_bill_pagename='ed_bill.php';
		$this->_bill_fact_pays_template='bills_in/fact_pays.html';
		$this->_invcalc_fact_pays_template='invcalc/fact_pays.html';
		$this->_is_incoming=1;
		$this->_bill_is_incoming=1;
		
	}
	
	  
	
	
	
	
	//найти сумму нераспределенных оплат по к-ту, довогору на дату + сами эти оплаты
	public function GetAvans($supplier_id, $org_id, $given_pdate, $except_acc_id, &$avans, &$raw_ids, &$raw_inv_ids,  $contract_id=NULL, $period_begin=NULL, $period_end=NULL, $do_reverse_pdate=false){
		
		//параметр $do_reverse_pdate нужен для расчета "обратного аванса" - для прикрепления более поздних оплат к реализации
		//в том случае, если мы уже ранее проверяли нормальный аванс и он оказался равен нулю.
		
		$avans=0;
		$names=array();	 $raw_ids=array();
		
		$period_flt='';
	//	if(($period_begin!==NULL)&&($period_end!==NULL)) $period_flt=' and  (given_pdate between "'.$period_begin.'" and   "'.$period_end.'")';
	 
	 
	 	$pdate_flt='';
		if($do_reverse_pdate) $pdate_flt=' and given_pdate>="'.$given_pdate.'" ';
		else $pdate_flt=' and given_pdate<"'.$given_pdate.'" ';
	 
		$sql='select * from payment
		where 
			is_confirmed=1 
			and is_incoming="'.$this->_is_incoming.'"
			and org_id="'.$org_id.'" 
			/*and contract_id="'.$contract_id.'"*/
			and supplier_id="'.$supplier_id.'"
			'.$pdate_flt.'
			'.$period_flt.'
		order by given_pdate asc, given_no  
			';
		
		//echo $sql." vs given_pdate ".date('d.m.Y',$given_pdate)."<br>";
		//echo date('d.m.Y', 1395691200);
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
						
			$set1=new mysqlset('select sum(value) from payment_for_acceptance where payment_id="'.$f['id'].'"');
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			//echo '<br>оплата '.$f['code'].' остаток '.((float)$f['value']-(float)$g[0]).'  ';
			if((float)$f['value']>(float)$g[0]){
				
				$avans+=((float)$f['value']-(float)$g[0]);
				
				
				
				$names[]='вх. оплата № '.$f['code'].' от '.date('d.m.Y',$f['given_pdate']).' сумма '.$f['value'];
				$raw_ids[]=$f['id'];	
			}
		}
		
		
		$period_flt='';
		//if(($period_begin!==NULL)&&($period_end!==NULL)) $period_flt=' and  (invcalc_pdate between "'.$period_begin.'" and   "'.$period_end.'")';
	 	
		
		$pdate_flt='';
		if($do_reverse_pdate) $pdate_flt=' and invcalc_pdate>="'.$given_pdate.'" ';
		else $pdate_flt=' and invcalc_pdate<"'.$given_pdate.'" ';
		
		$sql='select * from invcalc 
		where 
			is_confirmed_inv=1 
			and org_id="'.$org_id.'" 
			and supplier_id="'.$supplier_id.'"  
			'.$pdate_flt.'
			'.$period_flt.'
			
			';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$real_debt_stru=$this->_invcalc_item->FindRealDebt($f['id'],$f);
			$f['real_debt']=$real_debt_stru['real_debt'];
			$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			
			//var_dump($real_debt_stru);
			
			//условие по оплате
			/*if(($this->_is_incoming==0)&&!(($real_debt_stru['real_debt_id']==2)&&($real_debt_stru['real_debt']!=0))) continue;
			if(($this->_is_incoming==1)&&!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			*/
			if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			
			
			//echo 'zz';
			
			$set1=new mysqlset('select sum(value) from payment_for_acceptance where invcalc_id="'.$f['id'].'"');
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			if((float)$f['real_debt']>(float)$g[0]){
				
				$avans+=((float)$f['real_debt']-(float)$g[0]);
				$names[]='Акт корректировки задолженности № '.$f['code'].' от '.date('d.m.Y',$f['invcalc_pdate']).' сумма '.$f['real_debt'];
				$raw_inv_ids[]=$f['id'];	
			}	
		}
		
		
	 
		
		
		return implode(', ',$names);
	}
	
	
	
	
	
	
	
	//найти сумму платежей по реализации
	public function SumByAcc($acc_id){
		$res=0;
		
		$set1=new mysqlset('select sum(pb.value) from payment_for_acceptance as pb inner join payment as p on pb.payment_id=p.id where pb.acceptance_id="'.$acc_id.'" and p.is_confirmed=1 group by pb.acceptance_id');
		$rs1=$set1->GetResult();	
		$g=mysqli_fetch_array($rs1);
		
		$res+=(float)$g[0];
			
			
		$set1=new mysqlset('select sum(pb.value) from payment_for_acceptance as pb inner join invcalc as p on pb.invcalc_id_id=p.id where pb.acceptance_id="'.$acc_id.'" and p.is_confirmed_inv=1 group by pb.acceptance_id');
		$rs1=$set1->GetResult();	
		$g=mysqli_fetch_array($rs1);
		
		$res+=(float)$g[0];	
	 
		return $res;
	}
	
	
	
	//список вход оплат, прикрепленных к реализации (для вывода в печ. версию с/ф)
	public function GetPayForSF($acc_id){
		$arr=array();
		
		$sql='select p.code, p.value, p.given_pdate, p.given_no from payment as p
		 where
		 	p.id in(select payment_id from payment_for_acceptance where acceptance_id="'.$acc_id.'" and is_shown=1)
			and p.is_incoming=1
			and p.is_confirmed=1 
			
			';	
		
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
/**********************************************************************************************************************/	
	
	
	
	
	
	
	
	public function GetBillsAuto($supplier_id, $org_id, $value, $except_bills=NULL,$except_invs=NULL, $contract_id=0){
		$arr=array();
		
		
		$_null_pdate=$this->GetNullPdate($supplier_id,$org_id);
		
		$rest_value=$value;
		//var_dump($except_invs); var_dump($except_bills); 
		 
		
		//echo $rest_value;
		
		
		$_bill_flt='';
		if($except_bills!==NULL){
			
			$_bill_flt=implode(', ',$except_bills);
			if(count($except_bills)>0) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		$sql='select *, bill.id as position_id from bill 
		where 
			is_confirmed_price=1 
			and supplier_id="'.$supplier_id.'" 
			and org_id="'.$org_id.'" 
			and is_incoming="'.$this->_bill_is_incoming.'"
			and contract_id="'.$contract_id.'"
			'.$_bill_flt.' order by id asc';
		$set=new MysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['kind']=0;
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			
			$f['summ']=$this->_bill_item->CalcCost($f['id']);
			$f['payed']=$this->_bill_item->CalcPayed($f['id']);
			
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			if($rest_value<=0) break;
			
			if((float)$f['summ']>(float)$f['payed']){ 
				$delta=(float)$f['summ']-(float)$f['payed'];
				
				if($delta>$rest_value){
					
					$f['value']=round($rest_value,2);
					$rest_value-=$f['value'];
					$arr[]=$f;
					break;	
				}else{
					$f['value']=round($delta,2);
					$rest_value-=$delta;
					$arr[]=$f;
				}
				
			
			}
			
		}
		
		
		
		
		
		
		
		return $arr;
		
	}
	
	
	
	
	
	 
	
	
	
	//найдем самый старый неоплаченный счет контрагента
	public function GetBillOld($supplier_id, $org_id, $value, array $except_bills=NULL, $contract_id=0){
		$arr=array();
		
		
		$_null_pdate=$this->GetNullPdate($supplier_id,$org_id);
		
		$_bill_flt='';
		if($except_bills!==NULL){
			
			$_bill_flt=implode(', ',$except_bills);
			if(count($except_bills)>0) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		$sql='select *, bill.id as position_id from bill 
		where 
			is_confirmed_price=1 
			and supplier_id="'.$supplier_id.'" 
			and org_id="'.$org_id.'" 
			and is_incoming="'.$this->_bill_is_incoming.'"
			and contract_id="'.$contract_id.'" 
			'.$_bill_flt.' order by id asc';
		
		//echo $value;
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$rest_value=$value;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			
			$f['summ']=$this->_bill_item->CalcCost($f['id']);
			$f['payed']=$this->_bill_item->CalcPayed($f['id']);
			
			
			$f['hash']=md5('0_'.$f['id']);
			
			if($rest_value==0) break;
			
			if((float)$f['summ']>(float)$f['payed']){ 
				$delta=(float)$f['summ']-(float)$f['payed'];
				
				if($delta>$rest_value){
					
					$f['value']=$rest_value;
					
					$arr[]=$f;
					break;	
				}else{
					$f['value']=$delta;
					$rest_value-=$delta;
					$arr[]=$f;
				}
				
				break;
			}
			
		}
		
		return $arr;
		
	}
	
	
	
	
	
	
	
	
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=Array();
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'="1" order by id asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			if($f['bill_id']!=0) $f['kind']=0;
			else $f['kind']=1;
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	 
	
	
}
?>