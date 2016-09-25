<?

require_once('classes/abstractitem.php');
 

//echo mktime(0,0,0,02,28,2014);

//var_dump('шт.'>'я');

$supplier_id=10;

$sql=' select pc.value, pc.payment_id, pc.acceptance_id, pc.invcalc_id, pc.is_shown,
	
	a.given_no as a_given_no, a.given_pdate as a_given_pdate,
	p.code as p_code, p.given_no as p_given_no, p.given_pdate as p_given_pdate, p.supplier_id as p_supplier_id,
	i.code as i_code, i.invcalc_pdate as i_invcalc_pdate, i.supplier_id as i_supplier_id
	
	
	from
	payment_for_acceptance as pc
	left join acceptance as a on a.id=pc.acceptance_id
	left join payment as p on p.id=pc.payment_id
	left join invcalc as i on i.id=pc.invcalc_id
	
	 
	where
	(i.supplier_id is not null and i.supplier_id='.$supplier_id.') or
		(p.supplier_id is not null and p.supplier_id='.$supplier_id.') 
	 	
	
	order by  pc.invcalc_id, pc.payment_id
';

//echo $sql;	
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				echo $f['i_supplier_id'];
				
				$f['p_given_pdate']=date('d.m.Y', $f['p_given_pdate']);
				$f['i_invcalc_pdate']=date('d.m.Y', $f['i_invcalc_pdate']);
				$f['a_given_pdate']=date('d.m.Y', $f['a_given_pdate']);
				
				?>
                <? if($f['payment_id']>0){?>
                Оплата № <?=$f['p_code']?> заданный номер <?=$f['p_given_no']?> от <?=$f['p_given_pdate']?> 
                <? }?>
                 <? if($f['invcalc_id']>0){?>
                Акт № <?=$f['invcalc_id']?> заданный номер <?=$f['i_code']?> от <?=$f['i_invcalc_pdate']?> 
                <? }?>
                
                к Реализации   № <?=$f['acceptance_id']?> заданный номер <?=$f['a_given_no']?> от <?=$f['a_given_pdate']?> 
                на сумму <?=$f['value']?>  
                
                <? if (($f['is_shown']==1)&&($f['invcalc_id']==0)){?> <strong>показано в с/ф</strong>
                <? }else{ ?>
                <em>не показывать</em>
                <? }?>
                
                <br />
<br />

                
                <?	
			}



?>