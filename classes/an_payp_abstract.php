<?
require_once('billpospmformer.php');
require_once('payforbillgroup.php');
require_once('billitem.php');
require_once('acc_group.php');
require_once('acc_item.php');
require_once('supplieritem.php');
require_once('suppliersgroup.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('invcalcitem.php');

class AnPayUnivAbstract{
	protected $bydates=array();
	
	
	public $prefix='_2';
	public $url_prefix='_in';
	protected $is_incoming=1;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	protected $_acc_item;
	protected $_acc_group;
	
	
	protected function init(){
		
				
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		$this->_acc_item=new AccItem;
		$this->_acc_group=new AccGroup;
		
		
	}
	
	
	
	
	function __construct(){
		$this->init();	
	}
	
	

	public function ShowData($supplier_name, $org_id, $pdate1, $pdate2, $only_vyp, $only_not_vyp, $only_not_payed, $only_payed=false, $show_payed_report=false,  $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $dec_sep=DEC_SEP,&$alls, $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $only_semi_payed=false){
		$_bpm=new BillPosPMFormer;
		
		//$show_payed_report - флаг отчета Факт оплаты
		
		
		
		$pdate2+=24*60*60-1;
		
		$sm=new SmartyAdm;
		$_org=new OrgItem;
		$_opf=new OpfItem;
		
		
		
		$_sg=new SuppliersGroup;
		$sg=$_sg->GetItemsWithOpfArr();
		
		
		$supplier_names=array(); $supplier_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where  is_active=1 and id in(';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				$_supplier_names[]=trim($v);
			}
			
			$sql.= implode(', ',$_supplier_names).') order by full_name desc';
			
			//echo $sql;
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$supplier_id[]=$f['id'];	
			}
		}
		
		
		
		
		
		
		if(is_array($supplier_id)&&(count($supplier_id)>0)) {
			$supplier_filter=implode(', ',$supplier_id);
			if(strlen($supplier_filter)>0) $supplier_filter=' and b.supplier_id in('.$supplier_filter.') ';
			
		}
		
		
		
		$db_flt=''; $db_flt_inv='';
		if($only_vyp==1){
			$db_flt.=' and b.status_id=10 ';
			$db_flt_inv.=' and b.status_id=16 ';
		}elseif($only_not_vyp==1){
			$db_flt.=' and b.status_id in(9, 20, 21, 2) ';
			$db_flt_inv.=' and b.status_id=10 '; //такого нет
		}
		
		if($show_payed_report){
			$db_flt.=' and b.status_id in(10, 21, 9, 2) ';
			$db_flt_inv.=' and b.status_id=16 ';
		}
		
		$period_filter=''; $period_filter_inv='';
		if($show_payed_report){
			//фильтр в самом цикле...
		}else{
			//$period_filter=' and (b.pdate_shipping_plan between "'.$pdate1.'" and "'.$pdate2.'")';
			$period_filter=' and (b.pdate_payment_contract >="'.$pdate1.'" and  b.pdate_payment_contract<="'.$pdate2.'")';
			
			$period_filter_inv=' and (b.invcalc_pdate >= "'.$pdate1.'" and  b.invcalc_pdate<="'.$pdate2.'")';
		}
		
		//найти все счета в период по п-ку
		
		$sql_all='
		
		(select 
			distinct b.id, b.code, b.pdate_payment_contract as pdate_payment_contract, b.supplier_bill_no, b.status_id,
			b.supplier_id, b.pdate_shipping_plan, "0" as kind, b.pdate, b.is_in_buh as is_in_buh, b.supplier_bill_pdate as supplier_bill_pdate,
			
			ds.name as status_name,
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from bill as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id
			left join document_status as ds on b.status_id=ds.id
		
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_price=1
			and b.is_incoming="'.$this->is_incoming.'"  
			'.$period_filter.'
			'.$supplier_filter.'
			'.$db_flt.'
			and b.supplier_id<>1) 
		/*UNION ALL	
		(
		select 
			distinct b.id, b.code, b.invcalc_pdate as pdate_payment_contract, b.given_no as supplier_bill_no, b.status_id,
			b.supplier_id, b.invcalc_pdate as pdate_shipping_plan, "1" as kind, b.pdate, "0" as is_in_buh, b.given_pdate as supplier_bill_pdate,
			
			ds.name as status_name,
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from invcalc as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id
			left join document_status as ds on b.status_id=ds.id
			
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_inv=1 
			'.$period_filter_inv.'
			'.$supplier_filter.'
			'.$db_flt_inv.'
			and b.supplier_id<>1) 	
		
			*/
		';
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql_all.=' order by '.$ord_flt;
		}
		
		
		$alls=array();
		
		
		if($do_it){
			//echo $supplier_filter;
		//echo $sql_all;
		$set=new mysqlSet($sql_all);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$this->bydates=array();
		
		
		
		
		//echo $rc1.' счетов до отчета '; echo $rc.' счетов в отчете';
		
		$vhod_by_bill=0; $vhod_by_acc=0;
		$vhod_by_pays=0; $t_arr1=array(); $t_arr2=array(); $t_arr1_1=array(); $t_arr2_1=array();
		
		
		//хэш дат нулевой коррекции счетов п-ка
		$_null_pdates=array(); $_null_docs=array();
		
		
		$itogo_plat_por=array(); //Итого по платежным поручениям
		$itogo_plat_inv=array(); //Итого по платежным актам инв
		
		$check_by_acc=0;
		
/******* ОСНОВНОЙ ЦИКЛ ****************************************************/
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($f['kind']==0){
				$_bi=$this->_item;
			}else{
				$_bi=new InvCalcItem;
				$real=$_bi->FindRealDebt($f['id']);
				if($real['real_debt_id']!=3) continue;
			}
			
			
			
			$sum_by_bill=$_bi->CalcCost($f['id']);
			$sum_by_payed=$_bi->CalcPayed($f['id']);
			
			
		
			//$f['confirm_shipping_pdate_unf']=$f['confirm_shipping_pdate'];
			
			
			$f['pdate_shipping_plan_unf']=$f['pdate_shipping_plan'];
			if($f['pdate_shipping_plan']!=0)
				$f['pdate_shipping_plan']=date("d.m.Y",$f['pdate_shipping_plan']);
			else $f['pdate_shipping_plan']='-';
			
			$f['pdate_unf']=$f['pdate'];
			$f['pdate_payment_contract_unf']=$f['pdate_payment_contract'];
			if($f['pdate_payment_contract']!=0)
				$f['pdate_payment_contract']=date("d.m.Y",$f['pdate_payment_contract']);
			else $f['pdate_payment_contract']='-';
			
			$f['supplier_bill_pdate_unf']=$f['supplier_bill_pdate'];
			if($f['supplier_bill_pdate']!=0)
				$f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);	
			else $f['supplier_bill_pdate']='-';
			
			
			//$bill=$_bi->getitembyid($f['id']);
			
			
			//оплаты по счету...
			$pays=$_bi->GetBindedPaymentsFull($f['id']);
			
			
			
			//поступления по счету...
			$sum_by_acc=0;
			$accs=array();
			
			if($f['kind']==0){
			  $sql3='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1 order by given_pdate asc';
			  
			  $set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
			  $rs3=$set3->GetResult();
			  $rc3=$set3->GetResultNumRows();
			  
			  
			  
			  
			  for($j=0; $j<$rc3; $j++){
				  $g=mysqli_fetch_array($rs3);
				  $g['given_pdate_unf']=$g['given_pdate'];	
				  $g['given_pdate']=date('d.m.Y',$g['given_pdate']);
				  
				  $g['summ']=$this->_acc_item->CalcCost($g['id']);
				  $sum_by_acc+=$g['summ'];
				  $g['summ']=number_format($g['summ'],2,'.',DEC_SEP);
				  $accs[]=$g;
			  }
			  
			  $f['sum_by_acc']=$sum_by_acc;
			  $f['accs']=$accs;
			}
			
			
			//!!!!!!!!!! ФАКТ ОПЛАТЫ !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			if($show_payed_report){
				//
				 
				
				//нет поступлений, нет оплат - исключить
				if((count($accs)==0)&&(count($pays)==0)) continue;
				
				
				
				//если только оплаченные счета
				if($only_payed){
					
					
					if(((round((float)$sum_by_acc,2)>round((float)$sum_by_payed,2))&&((float)$sum_by_acc>0))
					||
					((round((float)$sum_by_bill,2)>round((float)$sum_by_payed,2))&&((float)$sum_by_acc==0))
					)
					
					continue;	
				}elseif($only_semi_payed){
				   
				   
				  if(((round((float)$sum_by_acc,2)<=round((float)$sum_by_payed,2))&&((float)$sum_by_acc>0))
					||
					((round((float)$sum_by_bill,2)<=round((float)$sum_by_payed,2))&&((float)$sum_by_acc==0))
					)
					
					continue;
				}
				
				  
				if(round($sum_by_payed,2)==0) continue;
			  
				  
				//пропускать счета с фактическими датами оплаты не в заданный период
				
				if(!isset($pays[0]['given_payment_pdate'])||(($pays[0]['given_payment_pdate']<$pdate1)||($pays[0]['given_payment_pdate']>$pdate2))) continue;
					
					
				
			 
			}else{
			///!!!! ПЛАН ОПЛАТЫ !!!!! 	
			  //нет поступлений, нет оплат - исключить
		 	  
			  //var_dump($only_semi_payed);	
				
			  if($only_not_payed){
				  if(round($sum_by_payed,2)>0) continue;
			  }elseif($only_semi_payed){
				  if(round($sum_by_payed,2)==0) continue;
				   
				  if(((round($sum_by_acc,2)<=round($sum_by_payed,2))&&($sum_by_acc>0))
				  ||
				  ((round($sum_by_bill,2)<=round($sum_by_payed,2))&&($sum_by_acc==0))) continue;	
				  
			
			  }else{
				 //  if($f['id']==2064) echo 'zzz';
				  
				  if(((round($sum_by_acc,2)<=round($sum_by_payed,2))&&($sum_by_acc>0))
				  ||
				  ((round($sum_by_bill,2)<=round($sum_by_payed,2))&&($sum_by_acc==0))) continue;	
			  }
			  
			  
			  
			  
			}
			
			
			//счет в бухгалтерии
			$f['can_in_buh']=$_bi->CanIsInBuh($f['id'], $rss23,  NULL, $can_confirm_in_buh, $can_unconfirm_in_buh, $sum_by_bill, $sum_by_payed);
			$f['can_in_buh_reason']=$rss23;
			
			
			
			$check_by_acc+=$sum_by_acc;
			
			$f['sum_by_bill_unf']=$sum_by_bill;
			$f['sum_by_payed_unf']=$sum_by_payed;
			
			$f['sum_by_bill']=number_format($sum_by_bill,2,'.',$dec_sep);
			$f['sum_by_payed']=number_format($sum_by_payed,2,'.',$dec_sep);
			
			foreach($pays as $k=>$v){
				if(($v['kind']==0)&&!in_array($v['payment_id'], $itogo_plat_por)) $itogo_plat_por[]=$v['payment_id'];	
				
				if(($v['kind']==1)&&!in_array($v['invcalc_id'], $itogo_plat_inv)) $itogo_plat_inv[]=$v['invcalc_id'];	
			}
			
			
			
			//var_dump($pays);
			foreach($pays as $k=>$v){
				$pays[$k]['value_unf']=$v['value'];	
				$pays[$k]['value']=number_format($v['value'],2,'.',$dec_sep);	
				
				$pays[$k]['given_pdate_unf']=$v['given_pdate'];	
				$pays[$k]['given_pdate']=date('d.m.Y',$v['given_pdate']);
				
				$pays[$k]['invcalc_pdate_unf']=$v['invcalc_pdate'];	
				$pays[$k]['invcalc_pdate']=date('d.m.Y',$v['invcalc_pdate']);
				
				if($v['kind']==1){
					
				}
			}
			
			//if($f['code']=='СЧ02070') var_dump($pays);
			
			$f['pays']=$pays;
			
			if(isset($pays[0]['given_pdate_unf'])) $f['pdate_payment_fact_unf']=$pays[0]['given_pdate_unf'];
			else $f['pdate_payment_fact_unf']=0;
			
			
			
			
			
			
			//добавим суммы счета
			if($show_payed_report){
			
			
				if(isset($pays[0]['given_payment_pdate'])) $paying_pdate=$pays[0]['given_payment_pdate'];
				else{ $paying_pdate=$f['pdate'];
				
					//echo date('d.m.Y H:i:s',$paying_pdate);
				}
				
				//$this->PutPay($paying_pdate, $sum_by_bill, $sum_by_payed);
				if($sum_by_acc>0) $this->PutPay($paying_pdate, $sum_by_acc, $sum_by_payed);
				else $this->PutPay($paying_pdate, $sum_by_bill, $sum_by_payed);
				//в цикле оплат
			}else{
			 //в мини-отчет по платежам
			  if($f['pdate_payment_contract_unf']!=0){ 
				 if($sum_by_acc>0) $this->PutPay($f['pdate_payment_contract_unf'], $sum_by_acc, $sum_by_payed);
				 else $this->PutPay($f['pdate_payment_contract_unf'], $sum_by_bill, $sum_by_payed);
			  }else{ 
				  if($sum_by_acc>0) $this->PutPay(datefromdmy(date('d.m.Y',$f['pdate_unf'])), $sum_by_acc, $sum_by_payed);
				  else $this->PutPay(datefromdmy(date('d.m.Y',$f['pdate_unf'])), $sum_by_bill, $sum_by_payed);
			  }
			  
			}
			
			
			
			if($f['kind']==0) $t_arr2[]=$f['id'];
			else if($f['kind']==1) $t_arr2_1[]=$f['id'];
			
			$alls[]=$f;
		}		
		
		//echo $check_by_acc;
		
		//var_dump($t_arr2);
		
		//echo implode(', ',$t_arr2);
		
		
		
		
		
		//промежуточные сводки
		
		$sql='select sum(value) from payment where id in('.implode(', ',$itogo_plat_por).')';
		//echo $sql;
		$sett=new mysqlset($sql);
		$rs=$sett->GetResult();
		$g=mysqli_fetch_array($rs);
		
		//echo $g[0];
		$sum_plat_por=(float)$g[0];
		
		//print_r($itogo_plat_inv);
		
		$sql='select * from invcalc where id in('.implode(', ',$itogo_plat_inv).')';
		//echo $sql;
		$sett=new mysqlset($sql);
		$rs=$sett->GetResult();
		$rc=$sett->GetResultNumRows();
		$_inv=new InvCalcItem;
		for($i=0; $i<$rc; $i++){
			$g=mysqli_fetch_array($rs);
			$real=$_inv->FindRealDebt($g['id'],$g);
			
			
			if(($real['real_debt_id']==2)||($real['real_debt_id']==3)) $sum_plat_por+=(float)$real['real_debt'];
		}
		
		//
		//foreach($_null_pdates as $k=>$v) $_null_pdates[$k]=date('d.m.Y H:i:s',$v);
		//print_r($_null_pdates);
		
		//вывести массив платежей
		
		$summ_struct=$this->FindSums($vhod_by_acc, $vhod_by_pays);
		
		//print_r($summ_struct);
		
		$sm->assign('to_pay_per',number_format($summ_struct['to_pay'],2,'.',DEC_SEP));
		$sm->assign('payed_per',number_format($summ_struct['payed'],2,'.',DEC_SEP));
		$sm->assign('to_pay_summ_per',number_format($summ_struct['to_pay_summ'],2,'.',DEC_SEP));
		$sm->assign('sum_plat_por_per',number_format($sum_plat_por,2,'.',DEC_SEP));
		
		
		
		
		
		
		
		
		
		
		
		
		$vhod_by_acc=0;
			
		$vhod_by_bill=0;
		$vhod_by_pays=0;
		
		
		
		
		
		
		
		
		//найти входящий остаток: 
		//а) по долгам (сумма счета)
		//б) по оплатам (сумма оплат по счетам)
		
		
		
		
		
		$period_filter1=''; $period_filter1_inv='';
		if($show_payed_report){
			//$period_filter=' and (b.pdate_payment_contract between "'.$pdate1.'" and "'.$pdate2.'")';
			//фильтр в самом цикле...
		}else{
			
			$period_filter1=' and b.pdate_payment_contract < "'.$pdate1.'" ';
			
			$period_filter1_inv=' and b.invcalc_pdate < "'.$pdate1.'" ';
		}
		
		
		$sql1='
		
		(select 
			distinct b.id, b.code, b.pdate_payment_contract as pdate_payment_contract, b.supplier_bill_no, b.status_id,
			b.supplier_id, b.pdate_shipping_plan, "0" as kind, b.pdate, b.is_in_buh as is_in_buh, b.supplier_bill_pdate as supplier_bill_pdate,
			
			ds.name as status_name,
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from bill as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id
			left join document_status as ds on b.status_id=ds.id
		
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_price=1 
			and b.is_incoming="'.$this->is_incoming.'"  
			'.$period_filter1.'
			'.$supplier_filter.'
			'.$db_flt.'
			and b.supplier_id<>1) 
		/*UNION ALL	
		(
		select 
			distinct b.id, b.code, b.invcalc_pdate as pdate_payment_contract, b.given_no as supplier_bill_no, b.status_id,
			b.supplier_id, b.invcalc_pdate as pdate_shipping_plan, "1" as kind, b.pdate, "0" as is_in_buh, b.supplier_bill_pdate as supplier_bill_pdate,
			
			ds.name as status_name,
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from invcalc as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id
			left join document_status as ds on b.status_id=ds.id
			
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_inv=1 
			'.$period_filter1_inv.'
			'.$supplier_filter.'
			'.$db_flt_inv.'
			and b.supplier_id<>1) 	*/
		
		order by id asc
		';
		
		//echo $sql1.'<br>';
		
		$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		
//*************** ЦИКЛ ВХОДЯЩИХ ОСТАТКОВ ************************		
		
		for($i1=0; $i1<$rc1; $i1++){
			$f1=mysqli_fetch_array($rs1);
			
			//echo $f1['id'].'<br />';
			if($f1['kind']==0){
				$_bi=$this->_item;
			}else{
				$_bi=new InvCalcItem;
				$real=$_bi->FindRealDebt($f1['id']);
				if($real['real_debt_id']!=3) continue;
			}
			
			
			//если счет уже был в основном цикле - не обрабатывать его здесь
			if(($f1['kind']==0)&&(in_array($f1['id'],$t_arr2))) continue;
			if(($f1['kind']==1)&&(in_array($f1['id'],$t_arr2_1))) continue;
			
			//
			
			
			
			$sum_by_bill=$_bi->CalcCost($f1['id']);
			$sum_by_payed=$_bi->CalcPayed($f1['id']);
			
			//оплаты по счету...
			$pays=$_bi->GetBindedPaymentsFull($f1['id']);
			
			
			
			
			$sum_by_acc=0;
			$accs=array();
			
			//поступления по счету...
			if($f1['kind']==0){
			  $sql3='select * from acceptance where bill_id="'.$f1['id'].'" and is_confirmed=1 order by given_pdate asc';
			  
			  $set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
			  $rs3=$set3->GetResult();
			  $rc3=$set3->GetResultNumRows();
			  
			  
			  
			  
			  for($j=0; $j<$rc3; $j++){
				  $g=mysqli_fetch_array($rs3);
				  $sum_by_acc+=$this->_acc_item->CalcCost($g['id']);
				  $accs[]=$g;
			  }
			
			}
			
			//!!!! ФАКТ ОПЛАТЫ 
			if($show_payed_report){
				//нет поступлений, нет оплат - исключить
				if((count($accs)==0)&&(count($pays)==0)) continue;
				
				//если только оплаченные счета
				if($only_payed){
					//if((round($sum_by_acc,2)>round($sum_by_payed,2))&&($sum_by_acc>0)) continue;	
					if(((round((float)$sum_by_acc,2)>round((float)$sum_by_payed,2))&&((float)$sum_by_acc>0))
					||
					((round((float)$sum_by_bill,2)>round((float)$sum_by_payed,2))&&((float)$sum_by_acc==0))
					) continue;
					
				}elseif($only_semi_payed){
				   
				   
				  if(((round((float)$sum_by_acc,2)<=round((float)$sum_by_payed,2))&&((float)$sum_by_acc>0))
					||
					((round((float)$sum_by_bill,2)<=round((float)$sum_by_payed,2))&&((float)$sum_by_acc==0))
					)
					
					continue;	
					
				}
				
				
			
				  if(round($sum_by_payed,2)==0) continue;
				
					//пропускать счета с фактическими датами оплаты после этой даты..
					if(!isset($pays[0]['given_payment_pdate'])||($pays[0]['given_payment_pdate']>=$pdate1)) continue;
			 
			}else{
				///!!!! ПЛАН ОПЛАТЫ !!!!! 
					
			  //нет поступлений, нет оплат - исключить
				//if((count($accs)==0)&&(count($pays)==0)) continue;
			  
			  
			  
			  
			  if($only_not_payed){
				  if(round($sum_by_payed,2)>0) continue;
			  }elseif($only_semi_payed){
				  if(round($sum_by_payed,2)==0) continue;
				  
				   if(((round($sum_by_acc,2)<=round($sum_by_payed,2))&&($sum_by_acc>0))
				  ||
				  ((round($sum_by_bill,2)<=round($sum_by_payed,2))&&($sum_by_acc==0))) continue;
			   }else{
				   
				   
				  if(((round($sum_by_acc,2)<=round($sum_by_payed,2))&&($sum_by_acc>0))
				  ||
				  ((round($sum_by_bill,2)<=round($sum_by_payed,2))&&($sum_by_acc==0))) continue;	
			  }
			  
			   
			  
			  
			}
			
			
			
			
			foreach($pays as $k=>$v){
				if(($v['kind']==0)&&!in_array($v['payment_id'], $itogo_plat_por)) $itogo_plat_por[]=$v['payment_id'];	
				
				if(($v['kind']==1)&&!in_array($v['invcalc_id'], $itogo_plat_inv)) $itogo_plat_inv[]=$v['invcalc_id'];	
			}
			
			$vhod_by_acc+=$sum_by_acc;
			
			$vhod_by_bill+=$sum_by_bill;
			$vhod_by_pays+=$sum_by_payed;
			 
			 
		   if($f1['kind']==0) $t_arr1[]=$f1['id'];
		   if($f1['kind']==1) $t_arr1_1[]=$f1['id'];
			
		}
		
		
		//echo $vhod_by_pays;
		//print_r($t_arr1);
		
		
		//окончательные сводки
		
		$sql='select sum(value) from payment where id in('.implode(', ',$itogo_plat_por).')';
		//echo $sql;
		$sett=new mysqlset($sql);
		$rs=$sett->GetResult();
		$g=mysqli_fetch_array($rs);
		
		//echo $g[0];
		$sum_plat_por=(float)$g[0];
		
		//print_r($itogo_plat_inv);
		
		$sql='select * from invcalc where id in('.implode(', ',$itogo_plat_inv).')';
		//echo $sql;
		$sett=new mysqlset($sql);
		$rs=$sett->GetResult();
		$rc=$sett->GetResultNumRows();
		$_inv=new InvCalcItem;
		for($i=0; $i<$rc; $i++){
			$g=mysqli_fetch_array($rs);
			$real=$_inv->FindRealDebt($g['id'],$g);
			
			
			if(($real['real_debt_id']==2)||($real['real_debt_id']==3)) $sum_plat_por+=(float)$real['real_debt'];
		}
		
		//
		//foreach($_null_pdates as $k=>$v) $_null_pdates[$k]=date('d.m.Y H:i:s',$v);
		//print_r($_null_pdates);
		
		//вывести массив платежей
		
		$summ_struct=$this->FindSums($vhod_by_acc, $vhod_by_pays);
		
		//print_r($summ_struct);
		
		$sm->assign('to_pay',number_format($summ_struct['to_pay'],2,'.',DEC_SEP));
		$sm->assign('payed',number_format($summ_struct['payed'],2,'.',DEC_SEP));
		$sm->assign('to_pay_summ',number_format($summ_struct['to_pay_summ'],2,'.',DEC_SEP));
		$sm->assign('sum_plat_por',number_format($sum_plat_por,2,'.',DEC_SEP));
		
		
		$sm->assign('to_arr1',$t_arr1);
		$sm->assign('to_arr1_1',$t_arr1_1);
		$sm->assign('to_arr2',$t_arr2);
		$sm->assign('to_arr2_1',$t_arr2_1);
		
		
		
		$by_date_table=$this->DeployBydates($begin_bydates_pdate,$end_bydates_pdate,$vhod_by_acc, $vhod_by_pays);
		
		if($begin_bydates_pdate!=0)
				$sm->assign('begin_bydates_pdate', date("d.m.Y",$begin_bydates_pdate));	
		else $sm->assign('begin_bydates_pdate','-');
		
		if($end_bydates_pdate!=0)
				$sm->assign('end_bydates_pdate', date("d.m.Y",$end_bydates_pdate));	
		else $sm->assign('end_bydates_pdate','-');
		
		
		$sm->assign('by_date_table',$by_date_table);
		
		}
		
		
		
		
		
		
		$sortmode3=0;
		$sortmode2=0;
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//сформируем список контрагентов для Select2
		//возможно, придется исправить...
		$our_ids=array(); $our_suppliers=array();
		$our_ids=explode(';',$supplier_name); //это коды!!!
		
		$sql='select p.id, p.full_name, p.code, opf.name as opf_name from supplier as p left join opf on opf.id=p.opf_id where p.id in('.implode(', ',$our_ids).')';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['full_name']=$f['code'].' '.$f['full_name'].', '.$f['opf_name'];
			$our_suppliers[]=$f;
		}
		$sm->assign('our_suppliers', $our_suppliers);
		
		//сортировка по нетабличным полям.
		 $custom_sort_mode=$sortmode;
		
		if($custom_sort_mode>0){
			switch($custom_sort_mode){
				case 2:
					$alls=$this->SortArr($alls,'sum_by_bill_unf',1);
				break;
				case 3:
					$alls=$this->SortArr($alls,'sum_by_bill_unf',0);
				break;	
				
				case 10:
					$alls=$this->SortArr($alls,'pdate_payment_fact_unf',1);
				break;
				case 11:
					$alls=$this->SortArr($alls,'pdate_payment_fact_unf',0);
				break;	
				
			}
				
		}
		
		
		$sm->assign('items',$alls);
	
		$sm->assign('pagename',$pagename);
		
		
			
		$sm->assign('prefix',$this->prefix);
		$sm->assign('url_prefix',$this->url_prefix);
		$sm->assign('is_incoming',$this->is_incoming);
		
		
		
	
	
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';;
		$link=eregi_replace('tab_page'.$this->prefix, 'tab_page', $link);
		$sm->assign('link',$link);
		
		
		$sm->assign('sortmode',$sortmode);
		
		
			
		$sm->assign('can_print',$can_print);
		$sm->assign('do_it',$do_it);
		$sm->assign('show_payed_report',$show_payed_report);
				
			
		return $sm->fetch($template);
	}
	
	
	
	//вставка в массив
	protected function PutPay($pdate, $to_pay, $payed){
			if(isset($this->bydates[$pdate])){
				$v=$this->bydates[$pdate];
				
				
				
				$this->bydates[$pdate]=array('to_pay'=>$to_pay+$v['to_pay'], 'payed'=>$payed+$v['payed']);	
				
			}else{
				$this->bydates[$pdate]=array('to_pay'=>$to_pay, 'payed'=>$payed);
				
			}
			
	}
	
	//найти суммы по массиву
	protected function FindSums($vhod_by_bill, $vhod_by_pays){
		
		//var_dump($this->bydates);
		
		
		$vv=array();	
		
		$to_pay=$vhod_by_bill; $payed=$vhod_by_pays; 
		//$to_pay=0; $payed=0; 
		$to_pay_summ=0;
		
		//echo $vhod_by_bill.' '.$vhod_by_pays;
		foreach($this->bydates as $k=>$v){
			$to_pay+=$v['to_pay'];
			$payed+=$v['payed'];
		}
		
		
		$to_pay_summ=$to_pay-$payed;
		
		$vv=array(
			'to_pay'=>$to_pay,
			'payed'=>$payed,
			'to_pay_summ'=>$to_pay_summ
		);
		
		return $vv;
	}
	
	protected function DeployBydates(&$begin_bydates_pdate,&$end_bydates_pdate,$vhod_by_bill, $vhod_by_pays){
		$final_table=array();
		
		ksort($this->bydates);
		
		
		
		
		
		$cter=0;
		
		$begin_bydates_pdate=0;
		$end_bydates_pdate=0;
		
		
		
		foreach($this->bydates as $k=>$v){
			if($cter==0){
				$begin_bydates_pdate=$k;
				
			
				
				$this->bydates[$k]['to_pay']+=$vhod_by_bill;
				$this->bydates[$k]['payed']+=$vhod_by_pays;
			}
			
			if($cter==(count($this->bydates)-1)){
				$end_bydates_pdate=$k;	
			}
			
			$cter++;
		}
		
		
		
		
		$to_pay=0; $payed=0;
		$begin_bydates_pdate=DateFromDmy(date('d.m.Y',$begin_bydates_pdate));
		
		//echo date('d.m.Y',$begin_bydates_pdate);
		//echo $begin_bydates_pdate;
		
		//echo date('d.m.Y',$end_bydates_pdate); 
		
		for($i=$begin_bydates_pdate; $i<=$end_bydates_pdate; $i+=24*60*60){
			 if($i<=0) continue;
		//foreach($this->bydates as $i=>$v){
			
			$ii=DateFromDmy(date('d.m.Y',$i)); //откидываем часы и минуты от летнего-зимнего времени
			if(isset($this->bydates[$ii])){
				$to_pay+=$this->bydates[$ii]['to_pay'];
				$payed+=$this->bydates[$ii]['payed'];
				
				//echo 'zz ';
				
				
			}else{
				
			}
			
			$final_table[]=array('pdate'=>date('d.m.Y',$ii),
			'to_pay'=>number_format($to_pay,2,'.',DEC_SEP), 
			'payed'=>number_format($payed,2,'.',DEC_SEP),
			'to_pay_summ'=>number_format($to_pay-$payed,2,'.',DEC_SEP)
			
			);
		}
		
		return $final_table;	
	}
	
	
	//сортировка выходного массива
	protected function SortArr($arr, $fieldname, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min	
				$a=$this->FindMin($arr, $fieldname, $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}else{
				//max
				$a=$this->FindMax($arr, $fieldname, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	protected function FindMin($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$minval=999999999999999999999999999999999999999;
		foreach($arr as $k=>$v){
			if($v[$fieldname]<$minval){
				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$maxval=-999999999999999999999;
		foreach($arr as $k=>$v){
			
			if($v[$fieldname]>$maxval){
				
				$maxval=$v[$fieldname];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}
}
?>