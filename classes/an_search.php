<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('bdetailsgroup.php');

class AnSearch{

	public function ShowData( $org_id, $template, DBDecorator $dec2,$pagename='files.php',  $do_it=false, $can_print=false, $dec_sep=DEC_SEP,&$alls){
		
		 
		 
		//$_au=new AuthUser;
		//$_res=$_au->Auth();
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
		
		
		if($do_it){
			//позиции любых заявок
			$sql='select kp.komplekt_ved_id, kp.position_id, kp.quantity_confirmed, kp.quantity_initial,
					pos.*,
					k.pdate, k.supplier_id, k.begin_pdate, k.end_pdate, k.status_id,
					st.name as status_name,
					sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id,
					dim.name as dimension
					
				from 
				 	komplekt_ved_pos as kp
					left join catalog_position as pos on kp.position_id=pos.id
					left join catalog_dimension as dim on pos.dimension_id=dim.id
					inner join komplekt_ved	as k on k.id=kp.komplekt_ved_id
					left join document_status as st on st.id=k.status_id
					left join supplier as sup on k.supplier_id=sup.id
					left join opf on opf.id=sup.opf_id
				where k.org_id="'.$org_id.'"
				';	
				
					 
			$db_flt=$dec2->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$sql.=' and '.$db_flt;
			//	$sql_count.=' where '.$db_flt;	
			}
			
			
			
			$ord_flt=$dec2->GenFltOrd();
			if(strlen($ord_flt)>0){
				$sql.=' order by '.$ord_flt;
			}			  
			  
			//echo $sql.'<br>';  
			  
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$f['begin_pdate']=date('d.m.Y', $f['begin_pdate']);
				$f['end_pdate']=date('d.m.Y', $f['end_pdate']);
				
				
				//в каких исх. счетах с каким статусом????
				$bills=array();
				$sql1='select bp.*,
					bill.code, bill.supplier_bill_no, bill.pdate, bill.supplier_bill_no,
					st.name as status_name
				from bill_position as bp
					inner join bill as bill on bill.id=bp.bill_id
					left join document_status as st on st.id=bill.status_id
				where 
					bill.is_incoming=0 and
					bp.position_id="'.$f['position_id'].'" 
					and bp.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				 order by
				 	bill.pdate asc
				';
				
				//echo $sql1.'<br><br>';
				$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				for($i1=0; $i1<$rc1; $i1++){
					
					$f1=mysqli_fetch_array($rs1);
					foreach($f1 as $k=>$v) $f1[$k]=stripslashes($v);
					$f1['pdate']=date('d.m.Y', $f1['pdate']);
					
					
					$bills[]=$f1;
				}
				$f['bills']=$bills;
				
				
				//поступления
				$acc_ins=array();
				$sql1='select bp.*,
					a.given_pdate, a.given_no,
					st.name as status_name,
					sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id
					
				from acceptance_position as bp
					inner join acceptance as a on a.id=bp.acceptance_id
					left join document_status as st on st.id=a.status_id
					left join bill as bill on bill.id=a.bill_id
					left join supplier as sup on bill.supplier_id=sup.id
					left join opf on opf.id=sup.opf_id
				where 
					a.is_incoming=1 and
					bp.position_id="'.$f['position_id'].'" 
					and bp.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				 order by
				 	a.given_pdate asc
				';
				
				//echo $sql1.'<br><br>';
				$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				for($i1=0; $i1<$rc1; $i1++){
					
					$f1=mysqli_fetch_array($rs1);
					foreach($f1 as $k=>$v) $f1[$k]=stripslashes($v);
					$f1['given_pdate']=date('d.m.Y', $f1['given_pdate']);
					
					
					$acc_ins[]=$f1;
				}
				$f['acc_ins']=$acc_ins;
				
				
				
				
				//в каких вх. счетах с каким статусом????
				$bills=array();
				$sql1='select bp.*,
					bill.code, bill.supplier_bill_no, bill.pdate, bill.supplier_bill_no,
					st.name as status_name
				from bill_position as bp
					inner join bill as bill on bill.id=bp.bill_id
					left join document_status as st on st.id=bill.status_id
				where 
					bill.is_incoming=1 and
					bp.position_id="'.$f['position_id'].'" 
					and bp.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				 order by
				 	bill.pdate asc
				';
				
				//echo $sql1.'<br><br>';
				$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				for($i1=0; $i1<$rc1; $i1++){
					
					$f1=mysqli_fetch_array($rs1);
					foreach($f1 as $k=>$v) $f1[$k]=stripslashes($v);
					$f1['pdate']=date('d.m.Y', $f1['pdate']);
					
					
					$bills[]=$f1;
				}
				$f['bills_in']=$bills;
				
				
				
				//реализации
				$accs=array();
				$sql1='select bp.*,
					a.given_pdate, a.given_no,
					st.name as status_name 
					 
					
				from acceptance_position as bp
					inner join acceptance as a on a.id=bp.acceptance_id
					left join document_status as st on st.id=a.status_id
					 
				where 
					a.is_incoming=0 and
					bp.position_id="'.$f['position_id'].'" 
					and bp.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				 order by
				 	a.given_pdate asc
				';
				
				//echo $sql1.'<br><br>';
				$set1=new mysqlSet($sql1);//,$to_page, $from,$sql_count);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				for($i1=0; $i1<$rc1; $i1++){
					
					$f1=mysqli_fetch_array($rs1);
					foreach($f1 as $k=>$v) $f1[$k]=stripslashes($v);
					$f1['given_pdate']=date('d.m.Y', $f1['given_pdate']);
					
					
					$accs[]=$f1;
				}
				$f['accs']=$accs;
				
				
			   	$alls[]=$f;  
			}
		}
		
		
		 
	   
	   
	    $sm->assign('items',$alls);
		
	   
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		$sortmode=0;
		
		$fields=$dec2->GetUris();
		foreach($fields as $k=>$v){
			
		
			if($v->GetName()=='dimension_id2') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group=$v->GetValue();
			if($v->GetName()=='three_group_id') $current_three_group=$v->GetValue();
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	   
	   //единицы изм
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_dimension_id==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('dim',$acts);		
		
		
		//тов группы
		$as=new mysqlSet('select * from catalog_group where parent_group_id=0 order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		$gr_ids=array(); $gr_names=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_group_id==$f[0]); 
			$acts[]=$f;
			
			$gr_ids[]=$f['id'];
			$gr_names[]=$f['name'];
		}
		$sm->assign('group',$acts);
		
		
		
		//группы
		
		$sm->assign('group_ids',$gr_ids);
		$sm->assign('group_names',$gr_names);
		
		//подгруппы
		if($current_group_id>0){
			$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_group_id.'" order by name asc');
			$rs=$as->GetResult();
			$rc=$as->GetResultNumRows();
			$acts=array();
			$acts[]=array('name'=>'');
			$gr_ids=array(); $gr_names=array();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$f['is_current']=($current_two_group==$f[0]); 
				$acts[]=$f;
				
				$gr_ids[]=$f['id'];
				$gr_names[]=$f['name'];
			}
			$sm->assign('two_group',$acts);
			
			$sm->assign('two_group_ids',$gr_ids);
			$sm->assign('two_group_names',$gr_names);
			
			
			if($current_two_group>0){
				$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_two_group.'" order by name asc');
				$rs=$as->GetResult();
				$rc=$as->GetResultNumRows();
				$acts=array();
				$acts[]=array('name'=>'');
				$gr_ids=array(); $gr_names=array();
				
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					$f['is_current']=($current_three_group==$f[0]); 
					$acts[]=$f;
					
					$gr_ids[]=$f['id'];
					$gr_names[]=$f['name'];
				}
				$sm->assign('three_group',$acts);
				
				$sm->assign('three_group_ids',$gr_ids);
				$sm->assign('three_group_names',$gr_names);
			}
		}
		
	   
	   
	   
	   
	   
	   
	   
	   
	   $link=$dec2->GenFltUri();
	  $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
	   
	   
		
		
		
		
		$sm->assign('can_print',$can_print);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>