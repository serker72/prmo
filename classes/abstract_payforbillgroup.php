<?
require_once('abstractgroup.php');
require_once('db_decorator.php');
require_once('billitem.php');
require_once('invcalcitem.php');
require_once('abstract_paygroup.php');
require_once('paygroup.php');
require_once('paygroup_simple.php');

// абстрактная группа
class AbstractPayForBillGroup extends AbstractGroup {
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
		$this->tablename='payment_for_bill';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_bill_item=new BillItem;
		$this->_pay_item=new PayItem;
		$this->_pay_group=new PayGroup_Simple;
		$this->_invcalc_item=new InvCalcItem;
		$this->_bill_pagename='ed_bill.php';
		$this->_bill_fact_pays_template='bills_in/fact_pays.html';
		$this->_invcalc_fact_pays_template='invcalc/fact_pays.html';
		$this->_is_incoming=0;
		$this->_bill_is_incoming=1;
		
	}
	
	//получить все утв. счета контрагента для подстановки в форму выбора
	public function GetBillsBySupplierArr($supplier_id, $org_id, array $except_bills=NULL, array $except_invcalcs=NULL, $sort_mode=0, $contract_id=0){
		$arr=array();
		//$_bi=new BillItem;
		
		$_null_pdate=$this->GetNullPdate($supplier_id,$org_id);
		
		
		$_bill_flt='';
		if($except_bills!==NULL){
			
			$_bill_flt=implode(', ',$except_bills);
			if(count($except_bills>0)) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		
		$srt='order by id desc';
		if($sort_mode==1) $srt='order by id asc'; 
		elseif($sort_mode==3) $srt='order by supplier_bill_no asc, id desc';
		elseif($sort_mode==2) $srt='order by supplier_bill_no desc, id desc';
		
		$sql='select *, bill.id as position_id from bill 
		where 
			is_confirmed_price=1 
			and supplier_id="'.$supplier_id.'" 
			and org_id="'.$org_id.'" 
			and is_incoming="'.$this->_bill_is_incoming.'"
			and contract_id="'.$contract_id.'" 
			'.$_bill_flt.' '.$srt;
		$set=new MysqlSet($sql);
		
		//echo $sql;
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$f['kind']=0; //вид документа - счет
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			if($f['pdate_payment_contract']!=0) $f['pdate_payment_contract']=date("d.m.Y",$f['pdate_payment_contract']);
			else $f['pdate_payment_contract']='-';
			
			
			
			$this->_pay_group->SetPagename($this->_bill_pagename);
			$dec2=new DBDecorator;
			$dec2->AddEntry(new SqlEntry('p.status_id',15, SqlEntry::E));
			$pays=$this->_pay_group->ShowPos($f['id'], $f['supplier_id'], $this->_bill_fact_pays_template, $dec2);
			
			//var_dump($this->_bill_fact_pays_template);
			$f['fact_pays']=$pays;
			
			
			$f['summ']=$this->_bill_item->CalcCost($f['id']);
			$f['payed']=$this->_bill_item->CalcPayed($f['id']);
			
			if((float)$f['summ']<=(float)$f['payed']) continue;
			
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			$arr[]=$f;
		}
		
		
		
		//добавим к общему списку - инв акты, по которым мы должны
		//$_bi=new InvCalcItem;
		/*$_bill_flt='';
		if($except_invcalcs!==NULL){
			
			$_bill_flt=implode(', ',$except_invcalcs);
			if(count($except_invcalcs>0)) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		
		
		$srt='order by id desc';
		if($sort_mode==1) $srt='order by id asc'; 
		elseif($sort_mode==3) $srt='order by given_no asc, id desc';
		elseif($sort_mode==2) $srt='order by given_no desc, id desc';
		
		
		$sql='select *, invcalc.id as position_id from invcalc 
		where 
			is_confirmed_inv=1 
			and supplier_id="'.$supplier_id.'" 
			and org_id="'.$org_id.'" 
			and contract_id="'.$contract_id.'" 
			'.$_bill_flt.' '.$srt;
		
		
		$set=new MysqlSet($sql);
		
		//echo $sql;
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$real_debt_stru=$this->_invcalc_item->FindRealDebt($f['id'],$f);
			$f['real_debt']=$real_debt_stru['real_debt'];
			$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			
			//условие по оплате
			if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			
			
			$f['kind']=1; //вид документа - инв взр
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$f['invcalc_pdate']);
			else $f['invcalc_pdate']='-';
			
			
			
			
			
			$this->_pay_group->SetSubkeyTable('invcalc');
			$this->_pay_group->SetIdName('invcalc_id');
			$this->_pay_group->SetPagename($this->_bill_pagename);
			//$sm2=new SmartyAdm;
			$dec2=new DBDecorator;
			$dec2->AddEntry(new SqlEntry('p.status_id',15, SqlEntry::E));
			$pays=$this->_pay_group->ShowPos($f['id'], $f['supplier_id'], $this->_invcalc_fact_pays_template, $dec2);
			$f['fact_pays']=$pays;
			
			
			
			$f['summ']=$this->_invcalc_item->CalcCost($f['id']);
			$f['payed']=$this->_invcalc_item->CalcPayed($f['id'],$f);
			
			
			if((float)$f['summ']<=(float)$f['payed']) continue;
			
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			$arr[]=$f;
		}
		
		
		
		*/
		
		
		
		
		
		
		return $arr;
		
	}
	
	//список счетов по форме с фильтром
	public function GetBillsBySupplierFilterArr($supplier_id, $org_id, array $except_bills=NULL,$not_payed,$filter_from,$filter_to, array $except_invs=NULL, $sort_mode=0, $contract_id=0){
		$arr=array();
		
		
		$_null_pdate=$this->GetNullPdate($supplier_id,$org_id);
		
		$_bill_flt='';
		if($except_bills!==NULL){
			
			$_bill_flt=implode(', ',$except_bills);
			if(count($except_bills>0)) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		
		$srt='order by id desc';
		if($sort_mode==1) $srt='order by id asc'; 
		elseif($sort_mode==3) $srt='order by supplier_bill_no asc, id desc';
		elseif($sort_mode==2) $srt='order by supplier_bill_no desc, id desc';
		
		$sql='select *, bill.id as position_id from bill 
		where 
		is_confirmed_price=1 
		and supplier_id="'.$supplier_id.'" 
		and org_id="'.$org_id.'" 
		and is_incoming="'.$this->_bill_is_incoming.'"
		and contract_id="'.$contract_id.'"
		and (pdate between "'.$filter_from.'" and "'.$filter_to.'") '.$_bill_flt.' '.$srt;
		
		//echo $sql;
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
			
			
			if($f['pdate_payment_contract']!=0) $f['pdate_payment_contract']=date("d.m.Y",$f['pdate_payment_contract']);
			else $f['pdate_payment_contract']='-';
			
			
			
			$this->_pay_group->SetPagename($this->_bill_pagename);
			//$sm2=new SmartyAdm;
			$dec2=new DBDecorator;
			$dec2->AddEntry(new SqlEntry('p.status_id',15, SqlEntry::E));
			$pays=$this->_pay_group->ShowPos($f['id'], $f['supplier_id'], $this->_bill_fact_pays_template,  $dec2);
			$f['fact_pays']=$pays;
			
			
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			if($not_payed){
				if($f['payed']==0) $arr[]=$f;
				
				
			}else{
				if((float)$f['summ']>(float)$f['payed'])  $arr[]=$f;
			}
		}
		
		
		
		//работаем с инв актами
		/*
		$_bill_flt='';
		if($except_invs!==NULL){
			
			$_bill_flt=implode(', ',$except_invs);
			if(count($except_invs>0)) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		
		$srt='order by id desc';
		if($sort_mode==1) $srt='order by id asc'; 
		elseif($sort_mode==3) $srt='order by given_no asc, id desc';
		elseif($sort_mode==2) $srt='order by given_no desc, id desc';
		
		$sql='select *, invcalc.id as position_id from invcalc where is_confirmed_inv=1 and supplier_id="'.$supplier_id.'" and org_id="'.$org_id.'" and (invcalc_pdate between "'.$filter_from.'" and "'.$filter_to.'") '.$_bill_flt.' '.$srt;
		
		//echo $sql;
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$real_debt_stru=$this->_invcalc_item->FindRealDebt($f['id'],$f);
			$f['real_debt']=$real_debt_stru['real_debt'];
			$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			
			//условие по оплате
			if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			
			
			$f['kind']=1;
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			if($f['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$f['invcalc_pdate']);
			else $f['invcalc_pdate']='-';
			
			$f['summ']=$this->_invcalc_item->CalcCost($f['id']);
			$f['payed']=$this->_invcalc_item->CalcPayed($f['id']);
			
			
			$this->_pay_group->SetSubkeyTable('invcalc');
			$this->_pay_group->SetIdName('invcalc_id');
			$this->_pay_group->SetPagename($this->_bill_pagename);
			//$sm2=new SmartyAdm;
			$dec2=new DBDecorator;
			$dec2->AddEntry(new SqlEntry('p.status_id',15, SqlEntry::E));
			$pays=$this->_pay_group->ShowPos($f['id'], $f['supplier_id'], $this->_invcalc_fact_pays_template, $dec2);
			$f['fact_pays']=$pays;
			
			
			
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			if($not_payed){
				if($f['payed']==0) $arr[]=$f;
				
				
			}else{
				if((float)$f['summ']>(float)$f['payed'])  $arr[]=$f;
			}
		}
		
		*/
		
		
		
		return $arr;
		
	}
	
	
	
	
	
	public function GetBillsAuto($supplier_id, $org_id, $value, $except_bills=NULL,$except_invs=NULL, $contract_id=0){
		$arr=array();
		
		
		$_null_pdate=$this->GetNullPdate($supplier_id,$org_id);
		
		$rest_value=$value;
		//var_dump($except_invs); var_dump($except_bills); 
		
		/*
		$_bill_flt='';
		if($except_invs!==NULL){
			
			$_bill_flt=implode(', ',$except_invs);
			if(count($except_invs)>0) $_bill_flt=' and id not in('.$_bill_flt.') ';
		}
		$sql='select *, invcalc.id as position_id from invcalc where
		 is_confirmed_inv=1 
		 and supplier_id="'.$supplier_id.'"  
		 and org_id="'.$org_id.'" 
		 and contract_id="'.$contract_id.'"
		 '.$_bill_flt.' order by id asc';
		$set=new MysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			$real_debt_stru=$this->_invcalc_item->FindRealDebt($f['id'],$f);
			$f['real_debt']=$real_debt_stru['real_debt'];
			$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			
			//условие по оплате
			if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			
			//echo ' z ';
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['kind']=1;
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$f['invcalc_pdate']);
			else $f['invcalc_pdate']='-';
			
			
			$f['summ']=$this->_invcalc_item->CalcCost($f['id']);
			$f['payed']=$this->_invcalc_item->CalcPayed($f['id']);
			
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
		*/
		
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
	
	
	
	
	
	
	
	//найдем заданный счет контрагента, если он не оплачен - вернем его на страницу
	public function GetBillAutoKnown($bill_id,$supplier_id, $org_id, $value, array $except_bills=NULL){
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
			and id="'.$bill_id.'" 
			and is_incoming="'.$this->_bill_is_incoming.'"
			'.$_bill_flt.' order by id asc';
		$set=new MysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$rest_value=$value;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			
			//if(!$this->FilterBills($f['supplier_bill_pdate'],$_null_pdate)) continue;
			
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
	
	
	//список позиций для показа на странице оплаты
	public function GetItemsByIdForPage($id,$only_bill_id=NULL,$only_invcalc_id=NULL){
		/*$_bi=new BillItem;
		$_inv=new InvCalcItem;
		*/
		$arr=array();
		
		$flt_bill='';
		if($only_bill_id!==NULL) $flt_bill=' and b.id="'.$only_bill_id.'"';
		
		$sql='select b.code, b.id, b.pdate, b.supplier_bill_no, b.supplier_bill_pdate, p.value 
		from 
			'.$this->tablename.' as p inner join bill as b on p.bill_id=b.id 
		where 
			p.'.$this->subkeyname.'="'.$id.'" 
			and b.is_incoming="'.$this->_bill_is_incoming.'"
			'.$flt_bill.'  order by b.id desc';
		
		
		//echo $sql;
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['kind']=0; //тип документа - счет
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			$f['summ']=$this->_bill_item->CalcCost($f['id']);
			$f['payed']=$this->_bill_item->CalcPayed($f['id'],$id);
			
			
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			
			$arr[]=$f;
		}
		
		
		//добавим также прикрепленные акты
		$flt_bill='';
		if($only_invcalc_id!==NULL) $flt_bill.=' and b.id="'.$only_invcalc_id.'"';
		if($only_bill_id!==NULL) $flt_bill.=' and p.bill_id="'.$only_bill_id.'"';
		
		
		$sql='select b.code, b.id, b.pdate, b.given_no, b.invcalc_pdate, p.value from payment_for_bill as p inner join invcalc as b on p.invcalc_id=b.id where p.invcalc_id="'.$id.'" '.$flt_bill.'  order by b.id desc';
		
		
		//echo $sql;
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['kind']=1; //тип документа - акт взр.
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['invcalc_pdate']!=0) $f['invcalc_pdate']=date("d.m.Y",$f['invcalc_pdate']);
			else $f['invcalc_pdate']='-';
			
			
			
			$f['summ']=$this->_invcalc_item->CalcCost($f['id']);
			$f['payed']=$this->_invcalc_item->CalcPayed($f['id'],$id);
			
			//var_dump($f['payed']);
			$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			$arr[]=$f;
		}
		
		
		return $arr;
	}
	
	
	
	//найти все оплаты по контрагенту с авансом и сам аванс
	public function GetAvans($supplier_id, $org_id, $except_bill_id, &$avans, &$raw_ids, &$raw_inv_ids, $contract_id=0){
		$avans=0;
		$names=array();	 $raw_ids=array();
		
		
		
		//нужно найти самую позднюю дату нулевых оборотов по данному контрагенту
		//если ее нет - не фильтровать оплаты из аванса
		//если она есть - сравнивать given_pdate оплаты и invcalc_pdate акта взр
		//если они раньше или = этой дате - не включать документ в расчет.
		$_null_pdate=$this->GetNullPdate($supplier_id, $org_id);
			
		
		
		$sql='select p.*, sum(pv.value) as s_q from payment as p
		left join payment_for_bill as pv on p.id=pv.payment_id
		where 
			p.is_confirmed=1 
			and p.is_incoming="'.$this->_is_incoming.'"
			and p.org_id="'.$org_id.'" 
			and p.contract_id="'.$contract_id.'"
			and p.supplier_id="'.$supplier_id.'"
		group by pv.payment_id	
			';
		
		//echo $sql.'<br>';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			/*			
			$set1=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'"');
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			*/
			/*echo $f['s_q'].' vs '.$g[0].' <br>';*/
			$g[0]=$f['s_q'];
			
			if((float)$f['value']>(float)$g[0]){
				
				$avans+=((float)$f['value']-(float)$g[0]);
				
				//echo ((float)$f['value']-(float)$g[0]).'  ';
				
				$names[]=$f['code'];
				$raw_ids[]=$f['id'];	
			}
		}
		
		
		//также найти неоплаченные инв. акты с debt_id=2 - оплата по вх счету
		//также найти неоплаченные инв. акты с debt_id=3 - оплата по исх счету
		//при этом дата счета п-ка д.быть > invcalc_pdate => invcalc_pdate<$bill[supplier_bill_pdate]
		$bill=$this->_bill_item->GetItemById($except_bill_id);
		//$_inv=new InvCalcItem;
		$sql='select p.*, sum(pv.value) as s_q from invcalc as p
		left join payment_for_bill as pv on p.id=pv.invcalc_id
		where 
			p.is_confirmed_inv=1 
			and p.org_id="'.$org_id.'" 
			and p.supplier_id="'.$supplier_id.'"  
			and p.invcalc_pdate<"'.$bill['supplier_bill_pdate'].'"';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$real_debt_stru=$this->_invcalc_item->FindRealDebt($f['id'],$f);
			$f['real_debt']=$real_debt_stru['real_debt'];
			$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			
			//условие по оплате
			if(($this->_is_incoming==0)&&!(($real_debt_stru['real_debt_id']==2)&&($real_debt_stru['real_debt']!=0))) continue;
			if(($this->_is_incoming==1)&&!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			
			/*$set1=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'"');
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			*/
			$g[0]=$f['s_q'];
			//echo $f['s_q']. ' vs '.$g[0].' <br>';
			
			
			if((float)$f['real_debt']>(float)$g[0]){
				
				$avans+=((float)$f['real_debt']-(float)$g[0]);
				$names[]=$f['code'];
				$raw_inv_ids[]=$f['id'];	
			}	
		}
		
		
		
		
		return implode(', ',$names);
	}
	
	//найти сумму платежей по счету
	public function SumByBill($bill_id){
		$res=0;
		
		$set1=new mysqlset('select sum(pb.value) from payment_for_bill as pb inner join payment as p on pb.payment_id=p.id where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 group by pb.bill_id');
		$rs1=$set1->GetResult();	
		$g=mysqli_fetch_array($rs1);
		
		$res+=(float)$g[0];
			
		$set1=new mysqlset('select sum(pb.value) from payment_for_bill as pb inner join invcalc as p on pb.invcalc_id_id=p.id where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 group by pb.bill_id');
		$rs1=$set1->GetResult();	
		$g=mysqli_fetch_array($rs1);
		
		$res+=(float)$g[0];
		
		return $res;
	}
	
	
	//найдем дату последнего выравнивания взр данного контрагента данной организации
	public function GetNullPdate($supplier_id, $org_id){
		
		$_null_pdate=NULL;
		$sql='select invcalc_pdate from invcalc where org_id="'.$org_id.'" and supplier_id="'.$supplier_id.'" and is_confirmed_inv=1 and debt_id=1 and invcalc_pdate>0 order by invcalc_pdate desc limit 1';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$_null_pdate=$f['invcalc_pdate'];	
		}
		
		return $_null_pdate;
	}
	
	public function FilterBills($supplier_bill_pdate, $_null_pdate){
		$go=true;
		
		if(($_null_pdate!==NULL)&&($_null_pdate>0)&&($supplier_bill_pdate!=0)&&($supplier_bill_pdate<=$_null_pdate)) $go=false;
		
		return $go;	
	}
	
	
	//найдем документ последнего выравнивания взр данного контрагента данной организации
	public function GetNullDoc($supplier_id, $org_id){
		
		$_null_doc=NULL;
		$sql='select * from invcalc where org_id="'.$org_id.'" and supplier_id="'.$supplier_id.'" and is_confirmed_inv=1 and debt_id=1 and invcalc_pdate>0 order by invcalc_pdate desc limit 1';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$_null_doc=$f;	
		}
		
		return $_null_doc;
	}
	
	
}
?>