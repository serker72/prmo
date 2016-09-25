<?
 require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');

require_once('array_sorter.php');
 
//отчеты для главной страницы
class AnIndex{
	
	//заявки за период
	public function ShowDataKomplekt($user_id=NULL, $pdate1, $pdate2, $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Число док-тов'){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		$user_flt='';
		
		if($user_id!==NULL) $user_flt=' and p.manager_id="'.$user_id.'" ';
		
		
		
		$sql='select count(*) from komplekt_ved as p where p.is_active=1 and p.org_id="'.$org_id.'" '.$user_flt.'  and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		
		//echo $sql.'<br>';	
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		
		//$sm->assign('total', $total);
		
		
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='s.full_name';
				$field_value_name='s.full_name';
			break;
		/*	case 1:
				$field_name='cat.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='cat.id';
				$field_value_name='cat.name';
			break;*/
			
			case 3:
				$field_name='p.manager_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		
		$sql='select distinct '.$field_value_name.' from
			komplekt_ved as p
			left join user as u on u.id=p.manager_id
		 
			left join supplier as s on p.supplier_id=s.id
			left join opf as opf on s.opf_id=opf.id
			
		where p.is_active=1 and p.org_id="'.$org_id.'" '.$user_flt.'  and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$alls['total']=$total;
		
		$_si=new abstractitem;
		$_si->SetTableName('supplier');
		$_opf=new OpfItem;
		
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select count(distinct p.id)  from 
			komplekt_ved as p
			left join user as u on u.id=p.manager_id
			left join supplier as s on p.supplier_id=s.id
			left join opf as opf on s.opf_id=opf.id
			
			
		where p.is_active=1 and p.org_id="'.$org_id.'" '.$user_flt.'  and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
		    
			$name=$f[0];
			if($divide==0){
				$si=$_si->GetItemByFields(array('full_name'=>$f[0]));
				//var_dump($si);
				$opf=$_opf->GetItemById($si['opf_id']);
				 
				$name.=', '.$opf['name'];	
			}
			
			$items[]=array(
				'name'=>$name,
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	
		}
		
		
		 $items=ArraySorter::SortArr($items, 'value',1);
		
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		 
		$sm->assign('title', $title);	
		return $sm->fetch($template);
		
	}
	
	
	//реализации за период
	public function ShowDataAcc($user_id=NULL, $pdate1, $pdate2, $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Сумма, руб.'){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		$user_flt='';
		
		if($user_id!==NULL) $user_flt=' and p.manager_id="'.$user_id.'" ';
		
		
		
		$sql='select sum(ap.total) from acceptance as p left join acceptance_position as ap on p.id=ap.acceptance_id where is_confirmed=1  and is_incoming=0 and p.org_id="'.$org_id.'" '.$user_flt.'  and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		
		//echo $sql.'<br>';	
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		
		//$sm->assign('total', $total);
		
		
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='s.full_name';
				$field_value_name='s.full_name';
			break;
		/*	case 1:
				$field_name='cat.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='cat.id';
				$field_value_name='cat.name';
			break;*/
			
			case 3:
				$field_name='p.manager_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		
		$sql='select distinct '.$field_value_name.' from acceptance as p
			left join user as u on u.id=p.manager_id
		    left join bill as b on b.id=p.bill_id
			left join supplier as s on b.supplier_id=s.id
			left join opf as opf on s.opf_id=opf.id
			
		where p.is_confirmed=1  and p.is_incoming=0 and p.org_id="'.$org_id.'" '.$user_flt.'  and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'")	
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$alls['total']=$total;
		
		$_si=new abstractitem;
		$_si->SetTableName('supplier');
		$_opf=new OpfItem;
		
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select sum(ap.total) from acceptance as p left join acceptance_position as ap on p.id=ap.acceptance_id 
			left join user as u on u.id=p.manager_id
			left join bill as b on b.id=p.bill_id
			left join supplier as s on b.supplier_id=s.id
			left join opf as opf on s.opf_id=opf.id
			
			
		where p.is_confirmed=1  and p.is_incoming=0 and p.org_id="'.$org_id.'" '.$user_flt.'  and (given_pdate  between "'.$pdate1.'" and "'.$pdate2.'")	and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
		    
			$name=$f[0];
			if($divide==0){
				$si=$_si->GetItemByFields(array('full_name'=>$f[0]));
				//var_dump($si);
				$opf=$_opf->GetItemById($si['opf_id']);
				 
				$name.=', '.$opf['name'];	
			}
			
			$items[]=array(
				'name'=>$name,
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	
		}
		
		
		 $items=ArraySorter::SortArr($items, 'value',1);
		
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		 
		$sm->assign('title', $title);	
		return $sm->fetch($template);
		
	}
	
	
	
	public function ShowDataBills($user_id=NULL, $pdate1, $pdate2, $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Число док-тов', $is_incoming=0){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		$user_flt='';
		
		if($user_id!==NULL) $user_flt=' and p.manager_id="'.$user_id.'" ';
		if($is_incoming==0) $incoming=' and p.is_incoming=0 ';
		else  $incoming=' and p.is_incoming=1 ';
		
		
		$sql='select count(*) from bill as p where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" '.$user_flt.' '.$incoming.'  and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		
		//echo $sql.'<br>';	
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		
		//$sm->assign('total', $total);
		
		
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='s.full_name';
				$field_value_name='s.full_name';
			break;
		/*	case 1:
				$field_name='cat.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='cat.id';
				$field_value_name='cat.name';
			break;*/
			
			case 3:
				$field_name='p.manager_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		
		$sql='select distinct '.$field_value_name.' from
			bill as p
			left join user as u on u.id=p.manager_id
		 
			left join supplier as s on p.supplier_id=s.id
			left join opf as opf on s.opf_id=opf.id
			
		where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" '.$user_flt.'  '.$incoming.'  and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$alls['total']=$total;
		
		$_si=new abstractitem;
		$_si->SetTableName('supplier');
		$_opf=new OpfItem;
		
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select count(distinct p.id)  from 
			bill as p
			left join user as u on u.id=p.manager_id
			left join supplier as s on p.supplier_id=s.id
			left join opf as opf on s.opf_id=opf.id
			
			
		where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" '.$user_flt.'  '.$incoming.'  and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
		    
			$name=$f[0];
			if($divide==0){
				$si=$_si->GetItemByFields(array('full_name'=>$f[0]));
				//var_dump($si);
				$opf=$_opf->GetItemById($si['opf_id']);
				 
				$name.=', '.$opf['name'];	
			}
			
			$items[]=array(
				'name'=>$name,
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	
		}
		
		
		 $items=ArraySorter::SortArr($items, 'value',1);
		
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		 
		$sm->assign('title', $title);	
		return $sm->fetch($template);
		
	}
	
	
	/*

	public function ShowDataKP($pdate1, $pdate2, $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Число док-тов'){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		
		$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		
		//$sm->assign('total', $total);
		
		
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='p.supplier_name';
				$field_value_name='p.supplier_name';
			break;
			case 1:
				$field_name='cat.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='cat.id';
				$field_value_name='cat.name';
			break;
			
			case 3:
				$field_name='p.user_manager_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		
		$sql='select distinct '.$field_value_name.' from
			kp as p
			left join user as u on u.id=p.user_manager_id
			left join kp_position as pp on pp.kp_id=p.id
			
			left join catalog_position as cat on pp.position_id=cat.id and cat.parent_id=0
			left join pl_producer as prod on prod.id=cat.producer_id
			
		where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$alls['total']=$total;
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select count(distinct p.id)  from 
			kp as p
			left join user as u on u.id=p.user_manager_id
			left join kp_position as pp on pp.kp_id=p.id
			
			left join catalog_position as cat on pp.position_id=cat.id and cat.parent_id=0
			left join pl_producer as prod on prod.id=cat.producer_id
			
		where p.is_confirmed_price=1 and p.org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")	and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
				
			
			$items[]=array(
				'name'=>$f[0],
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	
		}
		
		
		 $items=ArraySorter::SortArr($items, 'value',1);
		
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		 
		$sm->assign('title', $title);	
		return $sm->fetch($template);
	}
	
	
	
	
	public function ShowDataDog($pdate11,  $pdate12, $period,  $divide,  $org_id, $template,  $is_ajax=true, &$alls, $title='Число док-тов'){
		if($is_ajax) {
			$sm=new SmartyAj;
			 
		}else $sm=new SmartyAdm;
		
		
		
		 // and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $_pdate).'"'
		
		
		
		
		
		//$sql='select count(*) from kp where is_confirmed_price=1 and org_id="'.$org_id.'" and (pdate between "'.$pdate1.'" and "'.$pdate2.'")';
		$sql='select count(*) from  plan_fact_fact where is_confirmed=1 and org_id="'.$org_id.'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $pdate12).'"';
	
	//echo $sql.'<br>';
	
	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$total=(int)$f[0];
		$alls['total']=$total;
		
		  
		//выполняем разбивку...
		$field_name=''; $field_value_name='';
		
		switch($divide){
			case 0:
				$field_name='p.supplier_name';
				$field_value_name='p.supplier_name';
			break;
			case 1:
				$field_name='p.producer_id';
				$field_value_name='prod.name';
			break;
			case 2:
				$field_name='p.eq_name';
				$field_value_name='p.eq_name';
			break;
			
			case 3:
				$field_name='p.user_id';
				$field_value_name='u.name_s';
			break;
			
		};
		
		 
		$sql='select distinct '.$field_value_name.' from
			 plan_fact_fact as p
			left join user as u on u.id=p.user_id
			  
			left join pl_producer as prod on prod.id=p.producer_id
			
		where p.is_confirmed=1 and p.org_id="'.$org_id.'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $pdate12).'"
		order by '.$field_value_name.' asc';
		
		//echo $sql.'<br>';	
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$items=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//запрос на расчет
			$sql1='select count(distinct p.id)  from 
			 plan_fact_fact as p
			left join user as u on u.id=p.user_id
			  
			left join pl_producer as prod on prod.id=p.producer_id
			
		where p.is_confirmed=1 and p.org_id="'.$org_id.'" and (month between "'.date('m',$pdate11).'" and "'.date('m',$pdate12).'") and year="'.date('Y', $pdate12).'" and '.$field_value_name.' ="'.SecStr($f[0]).'" ';
			
			//echo $sql1.'<br>';
			
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			
			$f['value']=(int)$g[0];
			
			$f['percentage']=round(100*$f['value']/$total,2);
				
			
			$items[]=array(
				'name'=>$f[0],
				'value'=>$f['value'],
				'percentage'=>$f['percentage']
			);	 
		}
		
		$items=ArraySorter::SortArr($items, 'value',1);
		
		$alls['items']=$items;
		
		$sm->assign('alls', $alls);
		$sm->assign('title',$title);
		 
			
		return $sm->fetch($template);
	}
	*/
	
}
?>