<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('maxformer.php');
require_once('billitem.php');


require_once('pl_dismaxvalgroup.php');

//группа позиций исх счетов для форм-я вх счета
class BillPosGroupForBill extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill_position';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GainSql(&$sql){
		$sql='select p.id as p_id, p.bill_id as out_bill_id,  p.position_id as id, p.position_id as position_id,
					 p.pl_position_id as pl_position_id,
					 p.pl_discount_id, p.pl_discount_value, p.pl_discount_rub_or_percent,
					 d.name,  b.code  as out_bill_code,
					 
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_f, p.price_pm, p.total,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 pm.discount_plus_or_minus, pm.discount_value, pm.discount_rub_or_percent, pm.discount_given, pm.discount_given_pdate, pm.discount_given_user_id, pm.discount_given_pdate as discount_given_pdate_unformatted,
					
					 m.name_s as manager_name, m.login as manager_login
		
		from '.$this->tablename.' as p 
			inner join bill as b on b.id=p.bill_id and b.is_incoming=0
			left join supplier as sup on b.supplier_id=sup.id
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join pl_discount as d on p.pl_discount_id=d.id
			left join user as m on pm.discount_given_user_id=m.id
			left join pl_position as pl on p.pl_position_id=pl.id
			left join catalog_position as cat on p.position_id=cat.id
		';
		
	}
	
	
	public function ShowPos($template, DBDecorator $dec, $is_ajax=false){
		
		$alls=array();
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$this->GainSql($sql);
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' where '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo '<tr><td>'.$sql.'</td></tr>';
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		$_bpf=new BillPosPMFormer;
		
		$_mf=new MaxFormer;
		$_pdm=new PlDisMaxValGroup;
	
		$alls=array();
	
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			$f['has_pm']=(int)($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			
			$f['price_pm']=$pm['price_pm'];
			$f['cost']=round($f['price_f']*$f['quantity'],2);
			$f['total']=$pm['total'];
			
			if(($f['has_pm']==1)&&($f['rub_or_percent']==1)&&($f['value']>0)){
				$f['value_from_percent']=round(((float)$f['price_pm']-(float)$f['price_f']),2);
			}elseif(($f['has_pm']==1)&&($f['rub_or_percent']==0)&&($f['value']>0)){
				$f['value_from_percent']=$f['value'];
			}else{
				$f['value_from_percent']=0;
			}
			
			if(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==1)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=round(($f['value_from_percent']*$f['discount_value'])/100,2);
			}elseif(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==0)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=$f['discount_value'];
			}else{
				$f['discount_value_from_percent']=0;
			}
			
			//обнулим незаполненный плюс/минус
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			//if($f['discount_plus_or_minus']=="") $f['discount_plus_or_minus']=0;
			if($f['discount_rub_or_percent']=="") $f['discount_rub_or_percent']=0;
			if($f['discount_value']=="") $f['discount_value']=0;
			
			
			//рассчитаем ндс
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			$f['quantity_by_bill']=$f['quantity'];
			$f['quantity']=0;
			
			
			//также получить набор макс. скидок для позиции
			/*$max_vals=array();
			$max_vals=$_pdm->GetItemsByIdArr($f['pl_position_id']);
			$f['discs1']=$max_vals;*/
			
			$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent'].'_'.$f['pl_discount_rub_or_percent']);
			
			
			if($f['discount_given_pdate']!=0) $f['discount_given_pdate']=date('d.m.Y H:i:s',$f['discount_given_pdate']);
			else $f['discount_given_pdate']='';
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='dimension_id') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group=$v->GetValue();
			if($v->GetName()=='three_group_id') $current_three_group=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		$sm->assign($this->itemsname,$alls);
		$this->items=$alls;
		
	
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
}
?>