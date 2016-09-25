<?
require_once('abstractgroup.php');
require_once('db_decorator.php');
require_once('billitem.php');
require_once('bill_in_item.php');
require_once('invcalcitem.php');
require_once('paygroup.php');
require_once('abstract_payforbillgroup.php');
require_once('paygroup_simple.php');

// абстрактная группа
class PayForBillInGroup extends AbstractPayForBillGroup {
	
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
		$this->_bill_fact_pays_template='bills/fact_pays.html';
		$this->_invcalc_fact_pays_template='invcalc/fact_pays.html';
		
		$this->_is_incoming=1;
		$this->_bill_is_incoming=0;
		
		
	}
	
	
	
}
?>