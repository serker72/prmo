<?
require_once('abstractgroup.php');
require_once('abstractitem.php');
require_once('maxformer.php');

require_once('billitem.php');

require_once('cash_percent_item.php');
require_once('cash_bill_position_group.php');

// рент-ть по заявке
class KomplRent{
	
	
	 public function ShowData($id, $template, $is_ajax=false, $can_rub=false, &$rent_value, &$rent_percent){
		 $rent_value=0;
		 $rent_percent=0;
		 $alls=array();
		 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm(); 
		 
		 
		 $_bi=new BillItem; $_cpi=new CashPercentItem;
		 
		 $sql='select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id,  kp.sector_id
			from komplekt_ved_pos as p 
			inner join catalog_position as pos on p.position_id=pos.id 
			left join catalog_dimension as dim on pos.dimension_id=dim.id 
			 
			left join komplekt_ved as kp on kp.id=p.komplekt_ved_id
			where komplekt_ved_id="'.$id.'"  order by position_name asc, id asc';
		 $set=new MysqlSet($sql);
		 $rs=$set->GetResult();
		 $rc=$set->GetResultNumRows();
		 
		 $dohod=0; $rashod=0;
		 
		 for($i=0; $i<$rc; $i++){
			 
			$dohod_iter=0; $rashod_iter=0; 
			 
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		 	
			//данные по поступлениям
			$sql1='select sum(ap.total) as s_t, sum(ap.quantity) as s_q 
				from acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
			where
				a.is_confirmed=1 and a.is_incoming=1
				and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				and ap.position_id="'.$f['position_id'].'"
			';
			//echo $sql1.'<br>';
			$set1=new MysqlSet($sql1);
			 $rs1=$set1->GetResult();
			$f1=mysqli_fetch_array($rs1);
			
			$f['q_acc_in']=(float)$f1['s_q'];
			$f['sum_acc_in']=(float)$f1['s_t'];
			
			
			//данные по реализациям
			$sql1='select sum(ap.total) as s_t, sum(ap.quantity) as s_q 
				from acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
			where
				a.is_confirmed=1 and a.is_incoming=0
				and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				and ap.position_id="'.$f['position_id'].'"
			';
			//echo $sql1.'<br>';
			$set1=new MysqlSet($sql1);
			 $rs1=$set1->GetResult();
			$f1=mysqli_fetch_array($rs1);
			
			$f['q_acc']=(float)$f1['s_q'];
			$f['sum_acc']=(float)$f1['s_t'];
			
			
			//данные по вх. счетам
			$sql1='select sum(ap.total) as s_t, sum(ap.quantity) as s_q 
				from bill_position as ap
				inner join bill as a on a.id=ap.bill_id
			where
				a.is_confirmed_price=1 and a.is_incoming=1
				and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				and ap.position_id="'.$f['position_id'].'"
			';
			//echo $sql1.'<br>';
			$set1=new MysqlSet($sql1);
			 $rs1=$set1->GetResult();
			$f1=mysqli_fetch_array($rs1);
			
			$f['q_bill_in']=(float)$f1['s_q'];
			$f['sum_bill_in']=(float)$f1['s_t'];
			
			
			//данные по исх. счетам
			$sql1='select sum(ap.total) as s_t, sum(ap.quantity) as s_q 
				from bill_position as ap
				inner join bill as a on a.id=ap.bill_id
			where
				a.is_confirmed_price=1 and a.is_incoming=0
				and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				and ap.position_id="'.$f['position_id'].'"
			';
			//echo $sql1.'<br>';
			$set1=new MysqlSet($sql1);
			 $rs1=$set1->GetResult();
			$f1=mysqli_fetch_array($rs1);
			
			$f['q_bill']=(float)$f1['s_q'];
			$f['sum_bill']=(float)$f1['s_t'];
			
			
			//позиция оформлена:
			//а) есть реализации, есть поступления (к-во по обоим не равно 0)
			//б) есть вход счет, есть исход счет (к-во по обоим не равно 0)
			//иначе - не оформлена
			
			$f['is_ofor']=(
				(($f['q_acc_in']>0)&&($f['q_acc']>0))||
				(($f['q_bill_in']>0)&&($f['q_bill']>0))
			);
			
			if($f['is_ofor']){
				if(($f['q_acc_in']>0)&&($f['q_acc']>0)){
					
					$dohod+=$f['sum_acc'];
					$rashod+=$f['sum_acc_in'];
					$control_quantity=$f['q_acc'];
					
					//$f['profit']=
					
					$dohod_iter+=$f['sum_acc']; $rashod_iter+=$f['sum_acc_in']; 
										
				}elseif(($f['q_bill_in']>0)&&($f['q_bill']>0)){
					$dohod+=$f['sum_bill'];
					$rashod+=$f['sum_bill_in'];
					$control_quantity=$f['q_bill'];
					
					$dohod_iter+=$f['sum_bill']; $rashod_iter+=$f['sum_bill_in']; 
				}
				
				//занести в расход сумму по +/- и проценты по ней... на дату утв. счета!
				//pms_value
				
				//получить позиции исх. счетов, соотв. данной позиции заявки
				//по ним подгрузить все данные по +/-
				$sql1='select distinct a.id, a.confirm_price_pdate, a.org_id 
					from bill as a  
					inner join bill_position as ap on  a.id=ap.bill_id
				where
					a.is_confirmed_price=1 and a.is_incoming=0
					and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
					and ap.position_id="'.$f['position_id'].'"
				';
				
				//echo $sql1.'<br>';
				$set1=new MysqlSet($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				$pms=array();
				for($i1=0; $i1<$rc1; $i1++){
					$f1=mysqli_fetch_array($rs1);
					
					//echo $f1['id'];
					$pos=$_bi->GetPositionsArr($f1['id'], false);
					
					//найдем процент на дату счета
					/*
					echo '<pre>';
					print_r($pos);
					echo '</pre>';*/
					
					foreach($pos as $k=>$v){
						 //пропустим не наши позиции:
						 if(($v['komplekt_ved_id']!=$f['komplekt_ved_id'])||($v['position_id']!=$f['position_id'])) continue;
						 
						// $v['pm_to_give']= ($v['value_from_percent']-$v['discount_value_from_percent'])*$v['quantity'];
						
						$pm_to_give=($v['price_pm']-$v['price'])*$v['quantity'] - ($v['price_pm']-$v['price'])*$v['quantity']*$v['discount_value']/100;
						$v['pm_to_give']= round($pm_to_give ,2);  
						
						 
						 $percent=$_cpi->GetActualByPdate($f1['org_id'],date('d.m.Y',$f1['confirm_price_pdate']));
						 $v['percent_percent'] =$percent['percent'];
						 $v['percent']=round(((float)$percent['percent'])*$pm_to_give/100,2);
						 
						 $rashod+=$pm_to_give   +  ((float)$percent['percent'])*$pm_to_give/100;
						 
						 $rashod_iter+=$pm_to_give   +  ((float)$percent['percent'])*$pm_to_give/100;
						 
						 $pms[]=$v; 
					}
					
					//$pms[]=$pos;
				}
				
				
				$f['profit']=round($dohod_iter-$rashod_iter,2);
				$f['profit_percent']=0;
				if($dohod_iter>0) $f['profit_percent']=round(100*($dohod_iter-$rashod_iter)/$dohod_iter,2);
				
				$f['pms']=$pms;
				
				 
			}
			
			
			
			$alls[]=$f;
		 }
		 
		 
		 $rent_value=round($dohod-$rashod,2);
		 if($dohod!=0) $rent_percent=round(100*$rent_value/$dohod,2);
		 
		 
		 $sm->assign('items', $alls);
		 $sm->assign('can_rub', $can_rub);
		 
		 $sm->assign('rent_value', $rent_value);
		 
		 $sm->assign('rent_percent', $rent_percent);
		 
		 
		 
		 return $sm->fetch($template);
	 }
	
	
	 /*
	
	
	
	//список позиций
	public function GetItemsByIdArr($id,$current_id=0,$do_find_max=false, $dec2=NULL){
		$mf=new MaxFormer;
		
		$arr=array();
		
		$db_flt='';
		
		if($dec2!==NULL){
		  $db_flt=$dec2->GenFltSql(' and ');
		  if(strlen($db_flt)>0){
			  $db_flt=' and '.$db_flt;
		  //	$sql_count.=' and '.$db_flt;	
		  }
		}
		
		$sql='select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id,  kp.sector_id
		from '.$this->tablename.' as p 
		inner join catalog_position as pos on p.position_id=pos.id 
		left join catalog_dimension as dim on pos.dimension_id=dim.id 
		 
		left join komplekt_ved as kp on kp.id=p.komplekt_ved_id
		where '.$this->subkeyname.'="'.$id.'" '.$db_flt.' order by position_name asc, id asc';
		
		//echo $sql.' <br>';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($do_find_max){
				
				
				//входящие документы
				$_in_acc_in=$mf->InAccIn($id,$f['position_id']);
				 
				
				$f['in_bills_in']=round($mf->InBillsIn($id,$f['position_id'])-$_in_acc_in,3); //-$mf->InSh($id,$f['position_id']);
				 
				$f['in_free_in']=round($mf->InFreeIn($id, $f['position_id']),3);
				$f['in_pol_in']=$_in_acc_in;
				
				
				
				
				
				
				
				//исход. документы
				$_in_acc=$mf->InAcc($id,$f['position_id']);
				 
				
				$f['in_bills']=round($mf->InBills($id,$f['position_id'])-$_in_acc,3); //-$mf->InSh($id,$f['position_id']);
				 
				$f['in_free']=round($mf->InFree($id, $f['position_id']),3);
				$f['in_pol']=$_in_acc;
				
				
				
				//итог. рез-т 
				$f['itog']=$f['in_pol']-$f['in_pol_in']; //%{$in_pol-$in_pol_in}%
				
			}else{
				//echo 'zzzzzzzzzzz';
				$f['in_bills']='-';
				
				$f['in_free']='-';
				$f['in_pol']='-';
				
				
				$f['in_bills_in']='-';
				
				$f['in_free_in']='-';
				$f['in_pol_in']='-';
				
				$f['itog']='-';
			}
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	*/
	
	
	
}
?>