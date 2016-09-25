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

class AnPayUniv{
	protected $bydates=array();
	

	public function ShowData($supplier_name, $org_id, $pdate1, $pdate2, $only_vyp, $only_not_vyp, $only_not_payed, $only_payed=false, $show_payed_report=false,  $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $dec_sep=DEC_SEP,&$alls){
		$_bpm=new BillPosPMFormer;
		//$_si=new SupplierItem;
		//$supplier=$_si->GetItemById($supplier_id);
		
		//$show_payed_report - флаг отчета ‘акт оплаты
		$_pfg=new PayForBillGroup;
		
		
		$pdate2+=24*60*60-1;
		
		$sm=new SmartyAdm;
		$_org=new OrgItem;
		$_opf=new OpfItem;
		$_bi=new BillItem;
		$_acg=new AccGroup;
		$_ai=new AccItem;
		
		
		$_sg=new SuppliersGroup;
		$sg=$_sg->GetItemsWithOpfArr();
		
		
		$supplier_names=array(); $supplier_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where is_org=0 and is_active=1 and org_id="'.$org_id.'" and (';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				$_supplier_names[]=' (full_name="'.trim($v).'") ';
			}
			
			$sql.= implode(' OR ',$_supplier_names).') order by full_name desc';
			
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
		
		/*if(is_array($_supplier_names)&&(count($_supplier_names)>0)){
			$supplier_filter=implode(' and ',$_supplier_names);
		}
		if(strlen($supplier_filter)>0) $supplier_filter=' and '.$supplier_filter;*/
		
		$db_flt='';
		if($only_vyp==1){
			$db_flt.=' and b.status_id=10 ';
		}elseif($only_not_vyp==1){
			$db_flt.=' and b.status_id in(9, 2) ';
		}
		
		if($show_payed_report){
			$db_flt.=' and b.status_id in(10, 9, 2) ';
		}
		
		$period_filter='';
		if($show_payed_report){
			//$period_filter=' and (b.pdate_payment_contract between "'.$pdate1.'" and "'.$pdate2.'")';
			//фильтр в самом цикле...
		}else{
			//$period_filter=' and (b.pdate_shipping_plan between "'.$pdate1.'" and "'.$pdate2.'")';
			$period_filter=' and (b.pdate_payment_contract between "'.$pdate1.'" and "'.$pdate2.'")';
		}
		
		//найти все счета в период по п-ку
		
		$sql='select distinct b.*, 
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from bill as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id

		
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_price=1 
			'.$period_filter.'
			'.$supplier_filter.'
			'.$db_flt.'
			and b.supplier_id<>1
		';
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		$alls=array();
		
		
		if($do_it){
//		echo $sql;
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$this->bydates=array();
		
		
		
		
		//echo $rc1.' счетов до отчета '; echo $rc.' счетов в отчете';
		
		$vhod_by_bill=0; $vhod_by_acc=0;
		$vhod_by_pays=0; $t_arr1=array(); $t_arr2=array();
		
		
		//хэш дат нулевой коррекции счетов п-ка
		$_null_pdates=array(); $_null_docs=array();
		
		
		$itogo_plat_por=array(); //»того по платежным поручени€м
		
		$check_by_acc=0;
		
/******* ќ—Ќќ¬Ќќ… ÷» Ћ ****************************************************/
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			/*if(!isset($_null_pdates[$f['supplier_id']])){
				 $_null_pdates[$f['supplier_id']]=$_pfg->GetNullPdate($f['supplier_id'],$org_id);
				 $_null_docs[$f['supplier_id']]=$_pfg->GetNullDoc($f['supplier_id'],$org_id);
				 
			}*/
			
			
			
			
			$sum_by_bill=$_bi->CalcCost($f['id']);
			$sum_by_payed=$_bi->CalcPayed($f['id']);
			
			
				
		
			$f['confirm_shipping_pdate_unf']=$f['confirm_shipping_pdate'];
			
			
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
			
			
			
			//поступлени€ по счету...
			
			$sql3='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1 order by given_pdate asc';
			
			$set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
			$rs3=$set3->GetResult();
			$rc3=$set3->GetResultNumRows();
			
			
			$accs=array();
			$sum_by_acc=0;
			for($j=0; $j<$rc3; $j++){
				$g=mysqli_fetch_array($rs3);
				$g['given_pdate_unf']=$g['given_pdate'];	
				$g['given_pdate']=date('d.m.Y',$g['given_pdate']);
				
				$g['summ']=$_ai->CalcCost($g['id']);
				$sum_by_acc+=$g['summ'];
				$g['summ']=number_format($g['summ'],2,'.',DEC_SEP);
				$accs[]=$g;
			}
			
			$f['sum_by_acc']=$sum_by_acc;
			//$vhod_by_acc+=$sum_by_acc;
			$f['accs']=$accs;
			
			
			
			if($show_payed_report){
				//если только оплаченные счета
				if($only_payed){
					
					
					if((round((float)$sum_by_acc,2)>round((float)$sum_by_payed,2))&&((float)$sum_by_acc>0)) continue;	
				}
				
				
				  
				if(/*round($sum_by_acc,2)!=*/round($sum_by_payed,2)==0) continue;
			  
				  
				//пропускать счета с фактическими датами оплаты не в заданный период
				
				if(!isset($pays[0]['given_payment_pdate'])||(($pays[0]['given_payment_pdate']<$pdate1)||($pays[0]['given_payment_pdate']>$pdate2))) continue;
					
					
				
			 
			}else{
			 
			  
			  if($only_not_payed){
				  if(round($sum_by_payed,2)>0) continue;
			  }else{
				 //  if($f['id']==2064) echo 'zzz';
				  
				  if(((round($sum_by_acc,2)<=round($sum_by_payed,2))&&($sum_by_acc>0))
				  ||
				  ((round($sum_by_bill,2)<=round($sum_by_payed,2))&&($sum_by_acc==0))) continue;	
			  }
			  
			  
			  
			  
			}
			
			
			
			$check_by_acc+=$sum_by_acc;
			
			$f['sum_by_bill_unf']=$sum_by_bill;
			$f['sum_by_payed_unf']=$sum_by_payed;
			
			$f['sum_by_bill']=number_format($sum_by_bill,2,'.',$dec_sep);
			$f['sum_by_payed']=number_format($sum_by_payed,2,'.',$dec_sep);
			
			foreach($pays as $k=>$v){
				if(!in_array($v['payment_id'], $itogo_plat_por)) $itogo_plat_por[]=$v['payment_id'];	
			}
			
			
			
			//var_dump($pays);
			foreach($pays as $k=>$v){
				$pays[$k]['value_unf']=$v['value'];	
				$pays[$k]['value']=number_format($v['value'],2,'.',$dec_sep);	
				
				$pays[$k]['given_pdate_unf']=$v['given_pdate'];	
				$pays[$k]['given_pdate']=date('d.m.Y',$v['given_pdate']);
				
				$pays[$k]['invcalc_pdate_unf']=$v['invcalc_pdate'];	
				$pays[$k]['invcalc_pdate']=date('d.m.Y',$v['invcalc_pdate']);
				
				
			}
			
			//if($f['code']=='—„02070') var_dump($pays);
			
			$f['pays']=$pays;
			
			if(isset($pays[0]['given_pdate_unf'])) $f['pdate_payment_fact_unf']=$pays[0]['given_pdate_unf'];
			else $f['pdate_payment_fact_unf']=0;
			
			
			
			
			
			
			//добавим суммы счета
			if($show_payed_report){
				//$pays[$k]['given_pdate_unf']
				if(isset($pays[0]['given_pdate_unf'])) $paying_pdate=$pays[0]['given_pdate_unf'];
				else $paying_pdate=$f['pdate'];
				
				
				
				
				//$this->PutPay($paying_pdate, $sum_by_bill, $sum_by_payed);
				$this->PutPay($paying_pdate, $sum_by_acc, $sum_by_payed);
				//в цикле оплат
			}else{
			//в мини-отчет по платежам
			  if($f['pdate_payment_contract_unf']!=0){ 
				  $this->PutPay($f['pdate_payment_contract_unf'], $sum_by_acc, $sum_by_payed);
			  }else{ 
				  $this->PutPay(datefromdmy(date('d.m.Y',$f['pdate_unf'])), $sum_by_acc, $sum_by_payed);
			  }
			  
			}
			
			
			
			
			$t_arr2[]=$f['id'];
			$alls[]=$f;
		}		
		
		//echo $check_by_acc;
		
		//var_dump($t_arr2);
		
		//echo implode(', ',$t_arr2);
		
		
		//найти вход€щий остаток: 
		//а) по долгам (сумма счета)
		//б) по оплатам (сумма оплат по счетам)
		
		
		$period_filter1='';
		if($show_payed_report){
			//$period_filter1=' and b.pdate_payment_contract < "'.$pdate1.'" ';
			
		}else{
			//$period_filter1=' and b.pdate_shipping_plan < "'.$pdate1.'" ';
			$period_filter1=' and b.pdate_payment_contract < "'.$pdate1.'" ';
		}
		$sql1='select distinct b.*, 
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from bill as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id

		
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_price=1 
			'.$period_filter1.'
			'.$supplier_filter.'
			'.$db_flt.'
			and b.supplier_id<>1
		order by b.id asc
		';
		
		//echo $sql.' vs.<br> '.$sql1;
		$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		
//*************** ÷» Ћ ¬’ќƒяў»’ ќ—“ј“ ќ¬ ************************		
		
		for($i1=0; $i1<$rc1; $i1++){
			$f1=mysqli_fetch_array($rs1);
			
			//echo $f1['id'].'<br />';
			
			
			//если счет уже был в основном цикле - не обрабатывать его здесь
			if(in_array($f1['id'],$t_arr2)) continue;
			
			//
			
			//массив крайних нулевых коррекций по поставщикам
			if(!isset($_null_pdates[$f1['supplier_id']])){
				 $_null_pdates[$f1['supplier_id']]=$_pfg->GetNullPdate($f1['supplier_id'],$org_id);
				 $_null_docs[$f1['supplier_id']]=$_pfg->GetNullDoc($f1['supplier_id'],$org_id);
				 
				 
			}
			
			$sum_by_bill=$_bi->CalcCost($f1['id']);
			$sum_by_payed=$_bi->CalcPayed($f1['id']);
			
			//оплаты по счету...
			$pays=$_bi->GetBindedPaymentsFull($f['id']);
			
			
			
			//поступлени€ по счету...
			
			$sql3='select * from acceptance where bill_id="'.$f1['id'].'" and is_confirmed=1 order by given_pdate asc';
			
			$set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
			$rs3=$set3->GetResult();
			$rc3=$set3->GetResultNumRows();
			
			
			$accs=array();
			
			
			
			
			
			if($show_payed_report){
				
				//если только оплаченные счета
				if($only_payed){
					if((round($sum_by_acc,2)>round($sum_by_payed,2))&&($sum_by_acc>0)) continue;	
				}
				
				//если по счету была дата нулевой коррекции и дата утв отгр счета раньше или равна ей - включить счет в отчет
				if(
			  isset($_null_pdates[$f1['supplier_id']])&&
			  ($_null_pdates[$f1['supplier_id']]!==NULL)&&
			  ($f1['confirm_shipping_pdate']!=0)&&
			  ($f1['confirm_shipping_pdate']<=($_null_pdates[$f1['supplier_id']]+24*60*60-1))
			  ){
				  
				  //пропускать счета с датами 0 корректировки после этой даты..
					if($_null_pdates[$f1['supplier_id']]>=$pdate1) continue;
			  }else{
				  if(/*round($sum_by_acc,2)!=*/round($sum_by_payed,2)==0) continue;
				
					//пропускать счета с фактическими датами оплаты после этой даты..
					if(!isset($pays[0]['given_payment_pdate'])||($pays[0]['given_payment_pdate']>=$pdate1)) continue;
			  }
			}else{
			  if($only_not_payed){
				  if(round($sum_by_payed,2)>0) continue;
			  }else{
				 // if((round($sum_by_acc,2)<=round($sum_by_payed,2))/*&&($sum_by_acc>0)*/) continue;	
				 
				   if(((round($sum_by_acc,2)<=round($sum_by_payed,2))&&($sum_by_acc>0))
				  ||
				  ((round($sum_by_bill,2)<=round($sum_by_payed,2))&&($sum_by_acc==0))) continue;	
			  }
			  
			  //если по счету была дата нулевой коррекции и дата утв отгр счета счета раньше или равна ей - выкинуть счет
			  if(
			  isset($_null_pdates[$f1['supplier_id']])&&
			  ($_null_pdates[$f1['supplier_id']]!==NULL)&&
			  ($f1['confirm_shipping_pdate']!=0)&&
			  ($f1['confirm_shipping_pdate']<=($_null_pdates[$f1['supplier_id']]+24*60*60-1))
			  ) continue;
			  
			  
			}
			
			
			$sum_by_acc=0;
			
			for($j=0; $j<$rc3; $j++){
				$g=mysqli_fetch_array($rs3);
				$sum_by_acc+=$_ai->CalcCost($g['id']);
			}
			
			foreach($pays as $k=>$v){
				if(!in_array($v['payment_id'], $itogo_plat_por)) $itogo_plat_por[]=$v['payment_id'];	
			}
			
			$vhod_by_acc+=$sum_by_acc;
			
			$vhod_by_bill+=$sum_by_bill;
			$vhod_by_pays+=$sum_by_payed;
			
			
			 $t_arr1[]=$f1['id'];
		}
		
		
		//echo $vhod_by_pays;
		//print_r($t_arr1);
		
		

		
		$sql='select sum(value) from payment where id in('.implode(', ',$itogo_plat_por).')';
		//echo $sql;
		$sett=new mysqlset($sql);
		$rs=$sett->GetResult();
		$g=mysqli_fetch_array($rs);
		
		//echo $g[0];
		$sum_plat_por=(float)$g[0];
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
		
		
		
		//заполним шаблон пол€ми
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sortmode3') $sortmode3=$v->GetValue();
			if($v->GetName()=='sortmode2') $sortmode2=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//сортировка по нетабличным пол€м.
		$custom_sort_mode=0;
		if(($sortmode2>0)) $custom_sort_mode=$sortmode2;
		elseif(($sortmode3>0)) $custom_sort_mode=$sortmode3;
		
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
		
		$link=$dec->GenFltUri();
		if($show_payed_report) $link=$this->pagename.'?'.eregi_replace('&sortmode3=[[:digit:]]+','',$link).'&doSub3=1';
		else  $link=$this->pagename.'?'.eregi_replace('&sortmode2=[[:digit:]]+','',$link).'&doSub2=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode3',$sortmode3);
		$sm->assign('sortmode2',$sortmode2);
		
		
			
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
		
		//echo $to_pay.' '.$payed.' '.$to_pay_summ;
		
		
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
		
		//echo count($this->bydates);
		/*echo '<pre>';
		print_r($this->bydates);
		echo '</pre>';*/
		
		$cter=0;
		
		$begin_bydates_pdate=0;
		$end_bydates_pdate=0;
		
		
		
		foreach($this->bydates as $k=>$v){
			if($cter==0){
				$begin_bydates_pdate=$k;
				
				//было 
				/*echo 'было ';
				echo $this->bydates[$k]['to_pay'].' ';
				echo $this->bydates[$k]['payed'].' ';
				
				
				
				
				echo ' стало ';
				echo $this->bydates[$k]['to_pay'].' ';
				echo $this->bydates[$k]['payed'].' ';*/
				
				/*echo ' дата '.date('d.m.Y', $k).' ƒобавл€ю ';
					echo 'вход по сч='.$vhod_by_bill.' ';
		echo 'вход опл по сч '.$vhod_by_pays.' ';*/
				
				$this->bydates[$k]['to_pay']+=$vhod_by_bill;
				$this->bydates[$k]['payed']+=$vhod_by_pays;
			}
			
			if($cter==(count($this->bydates)-1)){
				$end_bydates_pdate=$k;	
			}
			
			$cter++;
		}
		
		/*echo $begin_bydates_pdate;
		echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		echo $end_bydates_pdate;*/
		
		
		$to_pay=0; $payed=0;
		$begin_bydates_pdate=DateFromDmy(date('d.m.Y',$begin_bydates_pdate));
		
		//echo date('d.m.Y',$begin_bydates_pdate);
		//echo $begin_bydates_pdate;
		
		for($i=$begin_bydates_pdate; $i<=$end_bydates_pdate; $i+=24*60*60){
			 if($i<=0) continue;
		//foreach($this->bydates as $i=>$v){
			
			$ii=DateFromDmy(date('d.m.Y',$i)); //откидываем часы и минуты от летнего-зимнего времени
			if(isset($this->bydates[$ii])){
				$to_pay+=$this->bydates[$ii]['to_pay'];
				$payed+=$this->bydates[$ii]['payed'];
				
				
				
				
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