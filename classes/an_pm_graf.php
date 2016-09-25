<?
require_once('billpospmformer.php');
require_once('billitem.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('suppliersgroup.php');
require_once('acc_group.php');
require_once('cash_bill_position_group.php');

require_once('maxformer.php');
//версия отчета для графиков
//показывает полностью +/- к выдаче, не фильтрует выданный и частично выданный
class AnPmGraf{
	public function ShowData($supplier_name, $org_id, $pdate1, $pdate2, $template, DBDecorator $dec,$pagename='files.php', $do_it=false, $can_print=false, $dec_sep=DEC_SEP,&$alls, $not_given=true, $only_payed=0, $only_semi_payed=0, $only_not_payed=0, $given_no=NULL, $view_full_version=false, $limited_supplier=NULL, &$total_pm, &$total_marja){
		
		$pdate2+=24*60*60-1;
		
		$_bpm=new BillPosPMFormer;  $_acg=new AccGroup; $_mf=new MaxFormer;
		
		$_bi=new BillItem;
		
		$_cpg=new CashBillPositionGroup;
		
		$_sg=new SuppliersGroup;
		$sg=$_sg->GetItemsWithOpfArr();
		
		
		$supplier_names=array(); $supplier_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where is_org=0 and is_active=1 /*and org_id="'.$org_id.'"*/ and id in(';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				//$_supplier_names[]=' (BINARY full_name="'.trim($v).'") ';
				$_supplier_names[]= trim($v) ;
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
		
		if($limited_supplier!==NULL) {
			 
			$supplier_filter.=' and  b.supplier_id in ('.implode(', ',$limited_supplier).')';
			
		}
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			
			$db_flt=' and '.$db_flt;
		}
		
		
		
		$sm=new SmartyAdm;
		
		//
		
		$sql='select b.*, 
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from bill as b
			inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.manager_id
		where 
			b.is_confirmed_price=1 and b.is_confirmed_shipping=1
			and b.org_id="'.$org_id.'"
			and b.is_incoming=0
			'.$supplier_filter.'
			'.$db_flt.'
			';
		
		//echo $sql;
			
		//$sql.=' order by pdate asc, id asc';
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		$alls=array();
		  $total_quantity=0; $total_pm=0; $total_marja=0; $total_semi_marja=0;
		
		//echo $sql;
		if($do_it){  
		 $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		//  echo $sql.'<br>';
		 // echo ' результат: '.$rc;
		  
		  
		   for($i=0; $i<$rc; $i++){
			
			  
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  $f['pdate']=date("d.m.Y",$f['pdate']);
			  
			  
			  if($not_given&&($pdate1!=datefromdmy(date('d.m.Y',0)))||($pdate2!=datefromdmy('31.12.2030') )){
				  //фильтруем по заданной дате поступления  
				 //сделать проверку, крайние ли значения фильтра. если крайние - то не проверять... 
				 
				
				
				$sql2='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1';
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
		  		$rc2=$set2->GetResultNumRows();
		  //хотя бы одна из этих дат должна попадать в интервал  
		 		$fulfill=false;
		 		for($j=0; $j<$rc2; $j++){
			  
			  		$g=mysqli_fetch_array($rs2);
				
					
					if(($g['given_pdate']>=$pdate1)&&($g['given_pdate']<=$pdate2)){
						$fulfill=$fulfill||true;
						 // echo $f['id'].' <br>';
					}
				}
				
				 
				if(!$fulfill) continue;
				  
			  }else{
				  //фильтруем по дате платежа  
				  //ниже, в позициях
			  }
			  
			  
			  			   //подгружать ту номенклатуру, где есть +/-
			  $pos=$_bi->GetPositionsArr($f['id'],false);
 

			  $cost=$_bi->CalcCost($f['id'],$pos);
			  $payed=$_bi->CalcPayed($f['id'],$pos);
			//	echo $payed;	
			  
			 // echo "<h1>$f[code] $cost vs $payed X</h1>";
			  //провреить оплату счета
			  //если стоит флаг only_payed==1, но если оплата не полная - выкидывать счет
			  if(($only_payed==0)&&($cost<=$payed)){
					//echo "<h1>$f[code] $cost vs $payed X</h1>";
					continue;  
			  }
			  
			  
			  if(($only_semi_payed==0)&&(($cost>$payed)&&($payed>0))){
					//echo "<h1>$f[code] Y</h1>";
					continue;  
			  }
			  
			  if(($only_not_payed==0)&&($payed==0)){
					//echo "<h1>$f[code] Z</h1>";
					continue;  
			  }
			  
			  
			  
			  $has_pm=false;
			  foreach($pos as $k=>$v){
					if($v['has_pm']) $has_pm=$has_pm||true; 
			  }
			  
			  if(!$has_pm) continue;
			  
			 
			  
			 // $f['total']=number_format(round($_bi->CalcCost($f['id'],$pos),2),2,'.',$dec_sep);
			  // $f['total']=number_format(round($_bi->CalcCost($f['id'],$pos),2),2,'.',$dec_sep);
			  $f['total']=$_bi->CalcCost($f['id'],$pos);
			   //number_format(round(,2),2,'.',$dec_sep);
			  $f['total_unf']=$f['total'];
			  
			  $f['total']=number_format(round($f['total'],2),2,'.',$dec_sep);
			  
			  $subs=array();
			  
			  $vydacha_by_bill=0; $given_by_bill=0;
			 
			  foreach($pos as $k=>$v){
			  		if($v['has_pm']){
			  			$sub=array();
						
					
						
						
						
						$sub['p_id']=$v['p_id'];
						$sub['name']=$v['position_name'];
						$sub['dimension']=$v['dim_name'];
						
						$sub['quantity']=$v['quantity'];
						
						
						$sub['price']=number_format($v['price'],2,'.',$dec_sep);
						
						
						$sub['price_pm']=number_format($v['price_pm'],2,'.',$dec_sep);
						
						$sub['price_f']=number_format($v['price_f'],2,'.',$dec_sep);
						
						 $sub['plus_or_minus']= $v['plus_or_minus'];
						 $sub['value']=number_format($v['value'],2,'.',$dec_sep);
						 $sub['rub_or_percent']=$v['rub_or_percent'];
						 
						 						 
						 $vv=$_bpm->Form($v['price_f'], $v['quantity'], $v['has_pm'], $v['plus_or_minus'], $v['value'], $v['rub_or_percent'],$v['price_pm'],$v['total']);
						
						
						$sub['pm_per_unit']=number_format($v['price_pm']-$v['price'],2,'.',$dec_sep);
						$sub['pm_per_cost']=number_format(($v['price_pm']-$v['price'])*$v['quantity'],2,'.',$dec_sep);
						
						
						
						
						
						$disc=$_bpm->FormDiscount($v['price_f'], $v['quantity'], $v['has_pm'], $v['plus_or_minus'], $v['value'], $v['rub_or_percent'],$v['price_pm'],$v['total'],$v['discount_value'],$v['discount_rub_or_percent']);
						
						
						 $sub['discount_value']=number_format($v['discount_value'],2,'.',$dec_sep);
						 $sub['discount_rub_or_percent']=$v['discount_rub_or_percent'];
						 
						 //найдем дисконт
						 $sub['discount_amount']=number_format($disc['discount_amount'],2,'.',$dec_sep);
						
						 $sub['discount_total_amount']=number_format($disc['discount_amount']*$v['quantity'],2,'.',$dec_sep);
						
						  $v['vydacha']= ($v['price_pm']-$v['price'])*$v['quantity'] -  $disc['discount_amount']*$v['quantity'];  //$v['total']-$vv['cost'];
						  $v['discount_given']=$v['discount_given'];
						
						
						
						
						$sub['unf_vydacha']= round($v['vydacha'],2); 
						$sub['vydacha']=number_format($v['vydacha'],2,'.',$dec_sep);  
						
						//получить сумму Выдано из документов 
						
						$v['discount_given']=$_cpg->CalcGiven($v['p_id']);
						
						$sub['discount_given']=number_format($v['discount_given'],2,'.',$dec_sep);
						$sub['unf_discount_given']=round($v['discount_given'],2);
						
						//какие документы на выдачу???
						$sub['docs']=$_cpg->GetCashesByBillPosition($v['p_id']);
						
						
						//получить все документы,
						//получить сумму, выданную по документам с утв. затратами, но не утв. выдачей!
						$sub['semi_docs']=$_cpg->GetCashesSemiByBillPosition($v['p_id']);
						$v['semi_discount_given']=$_cpg->CalcSemiGiven($v['p_id']);
						//if($f['id']==206) print_r($sub['semi_docs']);
						$sub['unf_semi_discount_given']=round($v['semi_discount_given'],2);
						
							//если отчет о выданных платежах - то фильтруем по дате выдачи
						if(!$not_given){
							//даты выдачи получим из документов
							//если хотя бы одна из дат попадает в наш интервал - то выводить этот счет.
							$given_pdates=array();
							foreach($sub['docs'] as $sk=>$sv) $given_pdates[]=$sv['confirmed_given_pdate'];
							
							//print_r($given_pdates);
							$_was_pdate=false;
							foreach($given_pdates as $sk=>$sv){
								if(($sv>=$pdate1)&&($sv<=$pdate2)) $_was_pdate=$_was_pdate||true;
							}
							
							
							//if(($v['discount_given_pdate_unformatted']<$pdate1)||($v['discount_given_pdate_unformatted']>$pdate2)) {
							if(!$_was_pdate){	
								
							/*	echo '<pre>';
								print_r($sub['docs']);
								echo $v['discount_given_pdate_unformatted'].' vs '.$pdate1.' vs '.$pdate2.' <br>';
								echo '</pre>';*/
								continue;
								
							}
						}
						
						 
						  
						 $sub['discount_given_pdate']=$v['discount_given_pdate']; 
						 
						  $sub['discount_given_user_id']=$v['discount_given_user_id']; 
						  $sub['manager_name']=$v['manager_name']; 
						  $sub['manager_login']=$v['manager_login']; 
						 
						 
						$vydacha_by_bill+=$sub['unf_vydacha'];
						$given_by_bill+=$sub['unf_discount_given'];
						
						
						/*	
						фильтры отключены, показываем весь +/- 
						  
						if($not_given){
							//только не выдано, выдано частично
						 	if($sub['unf_vydacha']==$sub['unf_discount_given']){
								continue;
							} 
								
						}else{
							//выдано полностью	//или частично
							if(round((float)$sub['unf_discount_given'],2)==0){ //!=$sub['unf_discount_given']){
								continue;
							}
						}  
						*/
						
						
						//фильтр по реализованным позициям.
					  //подгружать только те позиции, которые полностью реализованы по счету
					  // $f['in_rasp']=round($_mf->MaxInAcc($id, $f['id'],NULL, NULL, $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id']),3);  
					 
						/*$qua= round($_mf->MaxInAcc( $v['bill_id'], $v['id'],NULL, NULL, $v['storage_id'],$v['sector_id'], $v['komplekt_ved_id']), 3);
						
						//echo (float)$v['quantity'].' vs '.$qua.'<br>';
						
						if((float)$v['quantity']>$qua) continue;
					 
						*/
						
						
						  
						$total_quantity+=$v['quantity'];
						
						$total_pm+=$v['vydacha'];
						$total_marja+=$v['discount_given'];
						$total_semi_marja+=$v['semi_discount_given'];
						
						$subs[]=$sub;
						
						//var_dump( $sub['price']);
					}
			  }
			  
			  
			  
			  // CalcCost($id)
			  $f['subs']=$subs;
			  
			 
			  if(count($subs)==0) continue;
			  
			  
			  
			  
			  //найдем все поступления по счету
			  $sql3='select * from acceptance where bill_id='.$f['id'].' and is_confirmed=1';
			  $set3=new mysqlSet($sql3);
				$rs3=$set3->GetResult();
		  		$rc3=$set3->GetResultNumRows();
			  
			  $acc_nos=array();
			  
			  if(($given_no!==NULL)&&(strlen($given_no)>0)) {
					$was_in=false;  
			  }
			  
			  for($k=0; $k<$rc3; $k++){
			  
			  		$h=mysqli_fetch_array($rs3);
					if(($given_no!==NULL)&&(strlen($given_no)>0)) {
						if(strpos($h['given_no'],$given_no)>-1) $was_in=$was_in||true;
						
						
					}
					
					$acc_nos[]=$h;
			  }
			  
			  if(($given_no!==NULL)&&(strlen($given_no)>0)) {
					if(!$was_in) continue;
			  }
				 
				 
			  $f['acc_nos']=$acc_nos;	 
			  $total_summ+=round($f['total_unf'],2);
			  
			  
			  $alls[]=$f;	
		  }
		  
		}
		  $sm->assign('total_quantity',number_format($total_quantity,2,'.',$dec_sep));
		  $sm->assign('total_pm',number_format($total_pm,2,'.',$dec_sep));
		  $sm->assign('total_marja',number_format($total_marja,2,'.',$dec_sep));
		   $sm->assign('total_semi_marja',number_format($total_semi_marja,2,'.',$dec_sep));
		  
		$sm->assign('total_summ',number_format($total_summ,2,'.',$dec_sep));
		 
		
		//echo ' счетов: '.count($alls);	
		$sm->assign('items',$alls);
		
		
		
		//краткая статистика по поставщикам
		$arr_of_suppliers=array();
		foreach($alls as $k=>$v){
			
			
			if(!in_array($v['supplier_id'],$arr_of_suppliers)) $arr_of_suppliers[]= $v['supplier_id']; 
			
			  	
		}
		
		$alfa_arr_of_suppliers=array();
		//получить список поставщиков в алф. порядке...
		foreach($arr_of_suppliers as $k=>$v){
			$capt_name='';
			foreach($alls as $kk=>$vv){
				if($v==$vv['supplier_id']){
					$capt_name=$vv['supplier_name'];//.', '.$vv['supplier_opf'];
					break;	
				}
			}
			
			$alfa_arr_of_suppliers[]=$capt_name;
		}
		
		sort($alfa_arr_of_suppliers);
		
		//var_dump($alfa_arr_of_suppliers);
		//к алф. списку подтянем его айди... затем по полученному списку найдем сумму выдачи и долга
		$full_arr_of_suppliers=array();
		foreach($alfa_arr_of_suppliers as $k=>$v){
			$capt_name=''; $capt_id=0;
			foreach($alls as $kk=>$vv){
				if($v==$vv['supplier_name']){
					$capt_name=$vv['supplier_name'].', '.$vv['supplier_opf'];
					$capt_id=$vv['supplier_id'];
					break;	
				}
			}
			$full_arr_of_suppliers[]=array('id'=>$capt_id, 'capt_name'=>$capt_name);
		}
		//var_dump($full_arr_of_suppliers);
		
		//найдем суммы долга и выдачи по поставщику
		$report_arr=array();
		foreach($full_arr_of_suppliers as $k=>$v){
			$report_dolg=0; $report_given=0;
			foreach($alls as $kk=>$vv){
				if($v['id']==$vv['supplier_id']){
					
					foreach($vv['subs'] as $kkk=>$vvv){
						$report_given+=$vvv['unf_discount_given'];
						$report_dolg+=($vvv['unf_vydacha']-$vvv['unf_discount_given']);
					}
					
				}
			}
			
			$report_arr[]=array('id'=>$v['id'], 'capt_name'=>$v['capt_name'],
			 'unf_dolg'=>$report_dolg, 'unf_given'=>$report_given ,
			  'dolg'=>number_format($report_dolg,2,'.',$dec_sep), 'given'=>number_format($report_given,2,'.',$dec_sep) 
			  );
		}
		
		
		//var_dump($report_arr);
		$sm->assign('by_suppliers', $report_arr);
		$sm->assign('number_of_suppliers',count($arr_of_suppliers));
		$sm->assign('amount_of_dolg',number_format($total_pm-$total_marja,2,'.',$dec_sep) );
		$sm->assign('amount_of_given',number_format($total_marja,2,'.',$dec_sep) );
		
		
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='supplier_name') $supplier_name=$v->GetValue();
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
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		if($not_given) $link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		else  $link=$this->pagename.'?'.eregi_replace('&sortmode2=[[:digit:]]+','',$link).'&doSub2=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
		
		
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('view_full_version', $view_full_version);
		$sm->assign('do_it',$do_it);	
		
		$sm->assign('not_given', $not_given);
		
		$sm->assign('pagename',$pagename);
		//$sm->assign('loadname',$loadname);		
			
		return $sm->fetch($template);
	}
}
?>