<?
/*
echo mktime(0,0,0,8,1,2014).'<br>';
echo mktime(23,59,59,8,31,2014).'<br>';
*/
//файлы поступлений и реализаций, загруженные пол-лем 
 

require_once('classes/abstractitem.php');
$user_id=42;

//сколько и какие файлы реализаций

$sql='select
f.pdate, f.orig_name, 
 a.id, a.given_no, a.given_pdate, a.pdate as a_pdate, org.full_name, opf.name from
 acceptance_file as f
 
left join acceptance as a on f.acceptance_id=a.id
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where (f.pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).')
 and a.is_incoming=0
and f.user_id="'.$user_id.'"
order by a.id asc
';


$sql1='select count(distinct a.id) from
 acceptance_file as f
 
left join acceptance as a on f.acceptance_id=a.id
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where (f.pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).')
 and a.is_incoming=0
and f.user_id="'.$user_id.'"
';

//echo $sql;
$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult(); $f1=mysqli_fetch_array($rs1);
			
			?>
            <h2>Файлов реализаций загружено: <?=$rc?>, реализаций: <?=(int)$f1[0]?></h2>
            <?
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
				$f['pdate']=date('d.m.Y', $f['pdate']);
				$f['a_pdate']=date('d.m.Y', $f['a_pdate']);
				?>
				Файл <?=$f['orig_name']?> загружен <?=$f['pdate']?>, Реализация № <?=$f['id']?> заданный № <?=$f['given_no']?> заданная дата <?=$f['given_pdate']?> от <?=$f['pdate']?>  
                       
                <br>

                <?
			}


//какие документы отмечены оригиналами в этот период???
$sql='select a.id, a.given_no, a.given_pdate, a.pdate, org.full_name, opf.name,
a.has_nakl, a.has_fakt, a.has_akt
 from
acceptance as a
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where 

((has_nakl=1 and  has_nakl_confirm_user_id="'.$user_id.'" and (a.has_nakl_confirm_pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).'))
or
 (has_fakt=1 and  has_fakt_confirm_user_id="'.$user_id.'" and (a.has_fakt_confirm_pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).'))
or

 (has_akt=1 and  has_akt_confirm_user_id="'.$user_id.'" and (a.has_akt_confirm_pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).'))
 )
 
and a.is_incoming=0
order by a.id asc
 
';


$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			?>
            <h2>Отмечено наличие оригиналов у реализаций: <?=$rc?></h2>
            <?
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
				$f['pdate']=date('d.m.Y', $f['pdate']);
				?>
				Реализация № <?=$f['id']?> заданный № <?=$f['given_no']?> заданная дата <?=$f['given_pdate']?> от <?=$f['pdate']?>:
               <? if($f['has_nakl']==1){?>
                оригинал накладной
                <? }?>
                      
               <? if($f['has_fakt']==1){?>
                оригинал с/ф
                <? }?>
                
                <? if($f['has_akt']==1){?>
                оригинал акта
                <? }?>
              
                <br>

                <?
			}







$sql='select
f.pdate, f.orig_name, 
 a.id, a.given_no, a.given_pdate, a.pdate as a_pdate, org.full_name, opf.name from
 acceptance_file as f
 
left join acceptance as a on f.acceptance_id=a.id
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where (f.pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).')
 and a.is_incoming=1
and f.user_id="'.$user_id.'"
order by a.id asc
';


$sql1='select count(distinct a.id) from
 acceptance_file as f
 
left join acceptance as a on f.acceptance_id=a.id
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where (f.pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).')
 and a.is_incoming=1
and f.user_id="'.$user_id.'"
';

//echo $sql;
$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult(); $f1=mysqli_fetch_array($rs1);
			
			
			?>
            <h2>Файлов поступлений загружено: <?=$rc?>, поступлений <?=(int)$f1[0]?></h2>
            <?
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
				$f['pdate']=date('d.m.Y', $f['pdate']);
				$f['a_pdate']=date('d.m.Y', $f['a_pdate']);
				?>
				Файл <?=$f['orig_name']?> загружен <?=$f['pdate']?>, поступление № <?=$f['id']?> заданный № <?=$f['given_no']?> заданная дата <?=$f['given_pdate']?> от <?=$f['pdate']?>                
                <br>

                <?
			}
			
			
			

//какие документы отмечены оригиналами в этот период???
$sql='select a.id, a.given_no, a.given_pdate, a.pdate, org.full_name, opf.name,
a.has_nakl, a.has_fakt, a.has_akt
 from
acceptance as a
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where 

((has_nakl=1 and  has_nakl_confirm_user_id="'.$user_id.'" and (a.has_nakl_confirm_pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).'))
or
 (has_fakt=1 and  has_fakt_confirm_user_id="'.$user_id.'" and (a.has_fakt_confirm_pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).'))
or

 (has_akt=1 and  has_akt_confirm_user_id="'.$user_id.'" and (a.has_akt_confirm_pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).'))
 )
 
and a.is_incoming=1
order by a.id asc
 
';


$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			?>
            <h2>Отмечено наличие оригиналов у поступлений: <?=$rc?></h2>
            <?
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
				$f['pdate']=date('d.m.Y', $f['pdate']);
				?>
				поступление № <?=$f['id']?> заданный № <?=$f['given_no']?> заданная дата <?=$f['given_pdate']?> от <?=$f['pdate']?>:
               <? if($f['has_nakl']==1){?>
                оригинал накладной
                <? }?>
                      
               <? if($f['has_fakt']==1){?>
                оригинал с/ф
                <? }?>
                
                <? if($f['has_akt']==1){?>
                оригинал акта
                <? }?>
              
                <br>

                <?
			}

			

/*

$sql='select a.id, a.given_no, a.given_pdate, a.pdate, org.full_name, opf.name from
acceptance as a
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where (a.pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).')
and a.is_confirmed=1
and a.is_incoming=0
and a.manager_id="'.$user_id.'"
';

//echo $sql;
$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			?>
            <h2>Реализаций создано: <?=$rc?></h2>
            <?
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
				$f['pdate']=date('d.m.Y', $f['pdate']);
				?>
				Реализация № <?=$f['id']?> заданный № <?=$f['given_no']?> заданная дата <?=$f['given_pdate']?> от <?=$f['pdate']?>                
                <br>

                <?
			}
*/			
			
//отмечено наличие оригиналов:
/*

			
			
$sql='select a.id, a.given_no, a.given_pdate, a.pdate, org.full_name, opf.name from
acceptance as a
left join supplier as org on a.org_id=org.id
left join opf as opf on opf.id=org.opf_id

where (a.pdate between '.mktime(0,0,0,9,22,2014).' and '.mktime(23,59,59,9,29,2014).')
and a.is_confirmed=1
and a.is_incoming=1
and a.manager_id="'.$user_id.'"
';

//echo $sql;
$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			?>
            <h2>Поступлений создано: <?=$rc?></h2>
            <?
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
				$f['pdate']=date('d.m.Y', $f['pdate']);
				?>
				Поступление № <?=$f['id']?> заданный № <?=$f['given_no']?> заданная дата <?=$f['given_pdate']?> от <?=$f['pdate']?>                
                <br>

                <?
			}
			
*/

die();


 

//echo mktime(0,0,0,02,28,2014);

//var_dump('шт.'>'я');

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
	(i.supplier_id is not null and i.supplier_id=10) or
		(p.supplier_id is not null and p.supplier_id=10) 
	 	
	
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