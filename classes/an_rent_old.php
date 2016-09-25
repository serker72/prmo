<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('authuser.php');
require_once('user_s_item.php');
require_once('suppliersgroup.php');
require_once('invcalcitem.php');
require_once('acc_item.php');

require_once('payitem.php');
require_once('acc_in_item.php');

require_once('supcontract_item.php');
require_once('supcontract_group.php');


require_once('orgsgroup.php');
class AnRent{

	public function ShowData($pdate1, $pdate2, $extended_an=0, $by_org=0, $template, DBDecorator $dec,$pagename='files.php', $do_show_data=false, $can_print=false, $dec_sep=DEC_SEP){
		
		$alls=array();
		
		/* структура выходного массива:
		$org_docs[]=array(
			'org_id'
			'full_name'
			'opf_name'
			'before_ost'
			'after_ost'
			
			'docs' список док-тов по организации, без разибвки на даты
			'dates_docs' - список дат из периода, шаг 1 день, и документов по датаам организации:
				=>array(
					'pdate' - дата
					'docs_on_date' - список документов
				)
		
		);
		*/
		
		$_ai=new AccItem;
		$_ai_in=new AccInItem;
		$_pay=new PayItem;
		
		$_orgs=new OrgsGroup;
		
		$_bpm=new BillPosPMFormer;
		
		
		
		
		$sm=new SmartyAdm;
		
		if($do_show_data){
			//найти общий нач и кон остатки по всем организациям...
			$before_ost=$this->Ost($pdate1,$overal_plus, $overal_minus);
			
			$after_ost=$this->Ost($pdate2+24*60*60-1, $overal_plus, $overal_minus);
			
			$sm->assign('before_ost',number_format($before_ost,2,'.',$dec_sep));
			$sm->assign('after_ost',number_format($after_ost,2,'.',$dec_sep)); 
			$sm->assign('total_plus',number_format($overal_plus,2,'.',$dec_sep));
			$sm->assign('total_dolg',number_format($overal_minus,2,'.',$dec_sep)); 
			
			
			$orgs_docs=array();
			
			if($by_org==1){
				$orgs=$_orgs->GetItemsArr(0,1);	
				//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
				
				$before_ost=0; $after_ost=0;
				foreach($orgs as $org_k=>$org_v){
					$before_ost=$this->OstByOrgId($pdate1, $org_v['id'],$org_plus, $org_minus);
					$after_ost=$this->OstByOrgId($pdate2+24*60*60-1, $org_v['id'],$org_plus, $org_minus);
					
					$docs=$this->Docs($pdate1, $pdate2, $org_v['id']);
					//echo count($docs);	
					
					$orgs_docs[]=array(
					  'org_id'=>$org_v['id'],
					  'full_name'=>$org_v['full_name'],
					  'opf_name'=>$org_v['opf_name'],
					  'before_ost'=>number_format($before_ost,2,'.',$dec_sep),
					  'after_ost'=>number_format($after_ost,2,'.',$dec_sep),
					  'plus'=>number_format($org_plus,2,'.',$dec_sep),
					  'dolg'=>number_format($org_minus,2,'.',$dec_sep),
					  
					  'before_ost_unf'=> $before_ost ,
					  'after_ost_unf'=> $after_ost,
					  'plus_unf'=> $org_plus,
					  'dolg_unf'=>$org_minus,
					  
					  'docs'=>$docs
					);	
						
				}
				
				
			}else{
				$docs=$this->Docs($pdate1, $pdate2);
				//echo count($docs);	
				
				$orgs_docs[]=array(
					'org_id'=>0,
					'full_name'=>'',
					'opf_name'=>'',
					'before_ost'=>number_format($before_ost,2,'.',$dec_sep),
					'after_ost'=>number_format($after_ost,2,'.',$dec_sep),
					
					'before_ost_unf'=> $before_ost ,
					'after_ost_unf'=> $after_ost ,
					'docs'=>$docs
					);	
			}
			//получить все документы в период или получить все документы в период по организации
			//в массиве $docs
			
			//построить массив дат
			$current_date=$pdate1;
			  
			$dates=array();
			$pdate_begin_ost=0;
			do{
				
				 $dates[]=date('d.m.Y',$current_date);
					 
					
				//echo $after_ost;
				$current_date+=24*60*60;
				 
			}while($current_date<$pdate2);
			
			//перебрать блок док-тов и организаций, соотнести с датами
			//нужны остаток на дату, остаток на дату по организации
			foreach($dates as $date_k=>$date_v){
				//$before_pdate_ost=$org_v['before_ost'];
				
				foreach($orgs_docs as $org_k=>$org_v) {
				  
				 // print_r($org_v['docs']);
				  
				  $docs_on_date=array();
				  
				 // $after_pdate_ost=*/
				  $plus=0;
				  $dolg=0;
				  //найдем документы на дату
				  foreach($org_v['docs'] as $doc_k=>$doc_v){
					 
						//var_dump($v); echo '<br>';
						//echo "$doc_v[given_pdate] vs. $date_v<br>";
						if($doc_v['given_pdate']==$date_v){
							 $docs_on_date[]=$doc_v;
							 if($doc_v['kind']==1){
								$dolg+=(float)$doc_v['value']; 
							 }elseif($doc_v['kind']==2){
								 $plus-=(float)$doc_v['value']; 
								 
								 //echo 'zzzzzzzzzzzzzzz ';
							 }elseif($doc_v['kind']==5){
								  $plus+=(float)$doc_v['value'];
							 }
							 
						}
					  
						
				  }
				  $orgs_docs[$org_k]['dates_docs'][]=array(
					  	'pdate'=>$date_v,
						'plus'=>number_format($plus, 2,'.',$dec_sep),
						'dolg'=>number_format($dolg, 2,'.',$dec_sep),
						
						
						'plus_unf'=> $plus, 
						'dolg_unf'=>$dolg, 
						'docs_on_date'=>$docs_on_date
						);
				}
			}
			
			
			//найти нач и кон остатки по датам каждой организации
			foreach($orgs_docs as $org_k=>$org_v) {
				$before_ost=$org_v['before_ost_unf'];
				$after_ost=$before_ost;
				foreach( $org_v['dates_docs'] as $date_k=>$date_v){ // $after_ost+=($v[
					
					
					$after_ost+=$date_v['plus_unf']-$date_v['dolg_unf'];
					$orgs_docs[$org_k]['dates_docs'][$date_k]['before_ost_unf']=$before_ost;
					$orgs_docs[$org_k]['dates_docs'][$date_k]['after_ost_unf']=$after_ost;
					
					$orgs_docs[$org_k]['dates_docs'][$date_k]['before_ost']=number_format($before_ost, 2,'.',$dec_sep);
					$orgs_docs[$org_k]['dates_docs'][$date_k]['after_ost']=number_format($after_ost, 2,'.',$dec_sep);
					
					$before_ost=$after_ost;
				}
			}
			
			//найти нач и кон остатки для каждого документа по датам каждой организации
			foreach($orgs_docs as $org_k=>$org_v) foreach( $org_v['dates_docs'] as $date_k=>$date_v){
				$before_ost=$date_v['before_ost_unf'];
				$after_ost=$before_ost;	
				foreach($date_v['docs_on_date'] as $doc_k=>$doc_v){
					//$after_ost+=$doc_v['plus_unf']-$doc_v['dolg_unf'];
					if($doc_v['kind']==1){
						$after_ost=$after_ost-$doc_v['value'];
							
					}elseif($doc_v['kind']==2){
						$after_ost=$after_ost-$doc_v['value'];
					}elseif($doc_v['kind']==5){
						$after_ost=$after_ost+$doc_v['value'];
					}
					
					$orgs_docs[$org_k]['dates_docs'][$date_k]['docs_on_date'][$doc_k]['before_ost_unf']=$before_ost;
					$orgs_docs[$org_k]['dates_docs'][$date_k]['docs_on_date'][$doc_k]['after_ost_unf']=$after_ost;
					
					$orgs_docs[$org_k]['dates_docs'][$date_k]['docs_on_date'][$doc_k]['before_ost']=number_format($before_ost, 2,'.',$dec_sep);
					$orgs_docs[$org_k]['dates_docs'][$date_k]['docs_on_date'][$doc_k]['after_ost']=number_format($after_ost, 2,'.',$dec_sep);
					
					
					$before_ost=$after_ost;
				}
			}
			
			
			
			/*foreach($orgs_docs as $org_k=>$org_v) {
				echo '<pre>';
				echo "<h1> $org_v[full_name]</h1>";
				print_r($org_v['dates_docs']);	
				echo '</pre>';				
			}*/
			
		}
		
		
		
		
	
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$orgs_docs);
		$sm->assign('do_it',$do_show_data);
		
		
		//заполним шаблон полями
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		
		
	
		$sm->assign('pagename',$pagename);
		
		//var_dump($do_show_data);
		
		
		$sm->assign('can_print',$can_print);	
		
			
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	//остаток на дату общий
	public function Ost($pdate, &$plus, &$minus){
		$before_ost=0;
		$_bpm=new BillPosPMFormer;
		
		
		
		//нам нужно перебрать реализации, поступления, исход. оплаты на дату
			$sql='
			(select id, pdate, "0" as code, "2" as kind, "0" as value, given_pdate, given_no, "0" as debt_id
			 from acceptance
			where is_confirmed=1 and is_incoming=1 and given_pdate<"'.$pdate.'" 
			)
			UNION ALL
			(select p.id, p.pdate, p.code, "1" as kind, p.value, p.given_pdate, p.given_no, "0" as debt_id from payment as p
			left join payment_code as pc on pc.id=p.code_id
			where p.is_confirmed=1 and p.is_incoming=0 and p.given_pdate<"'.$pdate.'" and pc.in_report=1
			)
			
			UNION ALL
			(select id, pdate, "0" as code, "5" as kind, "0" as value, given_pdate, given_no, "0" as debt_id
			 from acceptance
			where is_confirmed=1 and is_incoming=0 and given_pdate<"'.$pdate.'" 
			)
			
			order by 6 asc, 4 asc,  1 asc
			
			';
		
		//echo $sql;
		/*
		1 - исходящие оплаты
		2 - поступления		
		5 - реализации
		*/
		
		$plus=0; $minus=0;
		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();		
		for($i=0;$i<$rc;$i++){	
			$g=mysqli_fetch_array($rs);
			
			
			if($g['kind']==1){
				$before_ost-=$g['value'];
				$minus+=$g['value'];
				
			}elseif($g['kind']==2){
				//пройдем позиции поступления
				$total_by_pos=0;
				$sql2='select p.*, pm.plus_or_minus, pm.rub_or_percent, pm.value from
				acceptance_position as p left join acceptance_position_pm as pm on p.id=pm.acceptance_position_id
				where p.acceptance_id="'.$g['id'].'"';
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();		
				for($k=0;$k<$rc2;$k++){	
					$h=mysqli_fetch_array($rs2);
					
					$vv=$_bpm->Form($h['price'], $h['quantity'], ($h['value']!=''), $h['plus_or_miuns'], $h['value'], $h['rub_or_percent'], $h['price_pm'],$h['total']);
					$total_by_pos+=(float)$vv['total'];
				}
				
				
				$before_ost-=$total_by_pos;
				$plus-=$total_by_pos;
		
				
			}elseif($g['kind']==5){
				//пройдем позиции реализации
				$total_by_pos=0;
				$sql2='select p.*, pm.plus_or_minus, pm.rub_or_percent, pm.value from
				acceptance_position as p left join acceptance_position_pm as pm on p.id=pm.acceptance_position_id
				where p.acceptance_id="'.$g['id'].'"';
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();		
				for($k=0;$k<$rc2;$k++){	
					$h=mysqli_fetch_array($rs2);
					
					$vv=$_bpm->Form($h['price'], $h['quantity'], ($h['value']!=''), $h['plus_or_miuns'], $h['value'], $h['rub_or_percent'], $h['price_pm'],$h['total']);
					$total_by_pos+=(float)$vv['total'];
				}
				
				
				$before_ost+=$total_by_pos;
				$plus+=$total_by_pos;
				
			}
			
		}
		
		return round($before_ost,2);
		
	}
	
	
	//остаток на дату по организации
	public function OstByOrgId($pdate, $org_id, &$plus, &$minus){
		$before_ost=0;
		$_bpm=new BillPosPMFormer;
		
		
		
		//нам нужно перебрать реализации, поступления, исход. оплаты на дату
			$sql='
			(select id, pdate, "0" as code, "2" as kind, "0" as value, given_pdate, given_no, "0" as debt_id
			 from acceptance
			where is_confirmed=1 and is_incoming=1 and given_pdate<"'.$pdate.'" and org_id="'.$org_id.'" 
			)
			UNION ALL
			(select p.id, p.pdate, p.code, "1" as kind, p.value, p.given_pdate, p.given_no, "0" as debt_id from payment as p
			left join payment_code as pc on pc.id=p.code_id
			where p.is_confirmed=1 and p.is_incoming=0 and p.given_pdate<"'.$pdate.'" and  p.org_id="'.$org_id.'" and pc.in_report=1
			)
			
			UNION ALL
			(select id, pdate, "0" as code, "5" as kind, "0" as value, given_pdate, given_no, "0" as debt_id
			 from acceptance
			where is_confirmed=1 and is_incoming=0 and given_pdate<"'.$pdate.'"  and org_id="'.$org_id.'" 
			)
			
			order by 6 asc, 4 asc,  1 asc
			
			';
		
		//echo $sql;
		/*
		1 - исходящие оплаты
		2 - поступления		
		5 - реализации
		*/
		
		//echo $sql.'<br>';
		$plus=0; $minus=0;
		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();		
		for($i=0;$i<$rc;$i++){	
			$g=mysqli_fetch_array($rs);
			
			
			if($g['kind']==1){
				$before_ost-=$g['value'];
				
				$minus+=$g['value'];
				
			}elseif($g['kind']==2){
				//пройдем позиции поступления
				$total_by_pos=0;
				$sql2='select p.*, pm.plus_or_minus, pm.rub_or_percent, pm.value from
				acceptance_position as p left join acceptance_position_pm as pm on p.id=pm.acceptance_position_id
				where p.acceptance_id="'.$g['id'].'"';
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();		
				for($k=0;$k<$rc2;$k++){	
					$h=mysqli_fetch_array($rs2);
					
					$vv=$_bpm->Form($h['price'], $h['quantity'], ($h['value']!=''), $h['plus_or_miuns'], $h['value'], $h['rub_or_percent'], $h['price_pm'],$h['total']);
					$total_by_pos+=(float)$vv['total'];
				}
				
				$plus-=$total_by_pos;
				$before_ost-=$total_by_pos;
				
		
				
			}elseif($g['kind']==5){
				//пройдем позиции реализации
				$total_by_pos=0;
				$sql2='select p.*, pm.plus_or_minus, pm.rub_or_percent, pm.value from
				acceptance_position as p left join acceptance_position_pm as pm on p.id=pm.acceptance_position_id
				where p.acceptance_id="'.$g['id'].'"';
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();		
				for($k=0;$k<$rc2;$k++){	
					$h=mysqli_fetch_array($rs2);
					
					$vv=$_bpm->Form($h['price'], $h['quantity'], ($h['value']!=''), $h['plus_or_miuns'], $h['value'], $h['rub_or_percent'], $h['price_pm'],$h['total']);
					$total_by_pos+=(float)$vv['total'];
				}
				
				
				$before_ost+=$total_by_pos;
				$plus+=$total_by_pos;
				
			}
			
		}
		
		return round($before_ost,2);
		
	}
	
	
	//получить все документы за период (с возможным фильтром по орг-ии)
	public function Docs($pdate1, $pdate2, $org_id=NULL, $dec_sep=DEC_SEP){
			$_ai=new AccItem;
		$_ai_in=new AccInItem;
		
		/*
		1 - исходящие оплаты
		2 - поступления		
		5 - реализации
		*/
		
		$org_filter='';
		if($org_id!==NULL) $org_filter=' and org_id="'.$org_id.'" ';
		
		
		 $sql='
		(select id, pdate, "0" as code, "2" as kind, "0" as value, given_pdate, given_no, "0" as debt_id from acceptance
		where is_confirmed=1 and is_incoming=1 and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'") '.$org_filter.' and bill_id in(select id from bill where is_confirmed_price=1  '.$org_filter.')
		)
		UNION ALL
		(select p.id, p.pdate, p.code, "1" as kind, p.value, p.given_pdate, p.given_no, "0" as debt_id from payment as p
		left join payment_code as pc on pc.id=p.code_id
		where p.is_confirmed=1 and p.is_incoming=0 and (p.given_pdate between "'.$pdate1.'" and "'.$pdate2.'") and pc.in_report=1 '.$org_filter.'
		)
		
		UNION ALL
		(select id, pdate, "0" as code, "5" as kind, "0" as value, given_pdate, given_no, "0" as debt_id from acceptance
		where is_confirmed=1 and is_incoming=0 and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'") '.$org_filter.' and bill_id in(select id from bill where is_confirmed_price=1  '.$org_filter.' )
		)
		
		order by 6 asc, 4 asc,  1 asc
		
		';
		
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
	   
		
		$between_before_ost=$v['before_ost_unf'];
		$between_after_ost=$v['before_ost_unf'];
		
		
		$contract_dolg=0; $contract_plus=0; 
		$docs_array=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
		//	$f['given_pdate_unf']=$f['given_pdate'];
			$f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			
			if($f['kind']==1){
				//исх оплата
				
				
				$f['dolg_unf']=$f['value'];
				$f['dolg']=number_format(round($f['value'],2),2,'.',$dec_sep);
				$f['plus']=0;
				
				
				//if(!in_array($f['id'],$_pay_ids)) $_pay_ids[]=$f['id'];	
				
			}elseif($f['kind']==2){
				//пройдем позиции поступления
				$total_by_pos=0;
				
				$total_by_pos=$_ai->CalcCost($f['id']);
				
				$f['value']=$total_by_pos; 
				$f['plus_unf']=$total_by_pos;
				$f['plus']=number_format(round($total_by_pos,2),2,'.',$dec_sep);
				$f['dolg']=0;
			
				
				
				
				//if(!in_array($f['id'],$_acc_in_ids)) $_acc_in_ids[]=$f['id'];
				
			}elseif($f['kind']==5){
				//пройдем позиции реализации
				$total_by_pos=0;
				
				$total_by_pos=$_ai_in->CalcCost($f['id']);
				
				
				$f['value']=$total_by_pos; 
				$f['plus_unf']=$total_by_pos;
				$f['plus']=number_format(round($total_by_pos,2),2,'.',$dec_sep);
				$f['dolg']=0;
			
				
				
				
				//if(!in_array($f['id'],$_acc_ids)) $_acc_ids[]=$f['id'];
				
			}
			
			
			$alls[]=$f;
		}
		
		return $alls;
	}
		
}
?>