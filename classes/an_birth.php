<?
 
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
 
require_once('sched.class.php');
require_once('sched_history_group.php');
require_once('sched_filegroup.php');
require_once('sched_fileitem.php');
require_once('array_sorter.php');


class AnBirth{
	
	protected $yub;
	function __construct(){
		$this->yub=array(
			40, 45, 50, 55, 60, 65,70,75,80,85,90,95,100		
		);	
	}

	public function ShowData($org_id, $supplier_ids,  $user_ids, $limited_user,  $limited_supplier, $pdate1, $pdate2,  $template, DBDecorator $dec2,$pagename='files.php',  $do_it=false, $can_print=false, $can_view_all_suppliers=false, $can_edit_user=false, $can_edit_supplier=false,   &$alls1, &$alls2, $result=NULL){
		
		 
		 
		
		
		$sm=new SmartyAdm;
		$alls1=array(); $alls2=array();
		
		 
		
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0; $check_year=0; $has_holdings=0;
			
			$fields=$dec2->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_content') $has_content=$v->GetValue();
				
				if($v->GetName()=='print') $print=$v->GetValue();
				if($v->GetName()=='prefix') $prefix=$v->GetValue();
				
				if($v->GetName()=='check_year') $check_year=$v->GetValue();
				if($v->GetName()=='has_holdings') $has_holdings=$v->GetValue();
				
			}
			 
		 
		 
				
				
				//–аботаем со справочником контрагентов
				$sql='select c.name, c.position, c.birthdate, c.supplier_id, 
			s.full_name, s.code, s.org_id,
			opf.name as opf_name,
			
			org.full_name as org_full_name, org_opf.name as org_opf_name
		
		 from  supplier_contact as c
			inner join supplier as s on s.id=c.supplier_id
			left join opf as opf on opf.id=s.opf_id
			left join supplier as org on org.id=s.org_id
			left join opf as org_opf on org_opf.id=org.opf_id		
		
		  where s.is_active=1 and c.birthdate is not NULL and s.is_org=0 and s.org_id="'.$org_id.'" '; 
				
				if($supplier_ids!==NULL) {
					
					//$sql.=' and supplier_id in('.implode(', ', $supplier_ids).') ';
					
					$_for_nn=array();
					$_for_nn[]='supplier_id in('.implode(', ',$supplier_ids).') ';
					if($has_holdings){
						//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
						$_for_nn[]='supplier_id in(select distinct ss.id from supplier as ss where ss.is_active=1 and ss.holding_id in( '.implode(', ',$supplier_ids).'))';
						 
						//2. найти все субхолдинги заданного к-та (у кого он холдинг, св€зь через контрагентов)
						$_for_nn[]='supplier_id in(select distinct ss.id from supplier as ss  where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$supplier_ids).')))';
						 
						//3. найти все дочерние предпри€ти€ субхолдингов заданного предпри€ти€
						$_for_nn[]='supplier_id in(select distinct ss.id from supplier as ss  /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерн€€ компани€ субхолдинга */
			where   ss.is_active=1 and ss.holding_id in(  '.implode(', ',$supplier_ids).')  )';
			
						 
						//4. найти всех контрагентов, у кого субхолдинг = заданному
						$_for_nn[]='supplier_id in(select distinct ss.id from supplier as ss where ss.is_active=1 and ss.subholding_id in( '.implode(', ',$supplier_ids).'))';
						 
					}
					
					$su_flt='
					and  
					('.implode(' or ',$_for_nn).') 
					';
				 
					$sql.=$su_flt;
					
						
				}
				

				
				if($limited_supplier!==NULL) $sql.=' and supplier_id in('.implode(', ', $limited_supplier).') ';
				
				
				//фильтр по ответственным
				if(!$can_view_all_suppliers) $sql.=' and supplier_id in(select distinct supplier_id from supplier_responsible_user where user_id="'.$result['id'].'") ';
				
				//фильтр по дате
				if($check_year){
					$sql.=' and c.birthdate between "'.datefromdmy($pdate1).'"  and "'.datefromdmy($pdate2).'" ';	
				}else{
					
				}
				 
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				
				
				
				/*$ord_flt=$dec2->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.=' order by '.$ord_flt;
				}*/
				
				//сортировка по дате
				if($check_year){
					$sql.=' order by  c.birthdate asc';	
				}else{
					
				}			  
				  
				 
				 
				//запрос по справочнику сотрудников 
				$sql2='select * from user where is_active=1 and pasp_bithday<>0 ';
				
				
				if($user_ids!==NULL) $sql2.=' and id in('.implode(', ', $user_ids).') ';
				
				if($limited_user!==NULL) $sql2.=' and id in('.implode(', ', $limited_user).') ';
				
				//фильтр по дате
				if($check_year){
					$sql2.=' and pasp_bithday between "'.datefromdmy($pdate1).'"  and "'.datefromdmy($pdate2).'" ';	
				}else{
					
				} 
				
				//сортировка по дате
				if($check_year){
					$sql2.=' order by  pasp_bithday asc';	
				}else{
					
				}	
				 
				//echo  $sql.'<br><br>';  
				//echo  $sql2.'<br><br>';   
				
				  
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows(); 
				 
				//разбор контрагентов
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					$f['birthdate_unf']=$f['birthdate'];
					
					if($f['birthdate']!=="") $f['birthdate']=date("d.m.Y",$f['birthdate']);
					
					//найти юбил€ров
					if( in_array(  (int)date('Y') - (int)date('Y', $f['birthdate_unf']), $this->yub)) $f['has_yub']=true;
					
					if(!$check_year){
						//фильтраци€ по попаданию даты рождени€ в выбранные даты текущего года
						$int_begin=datefromdmy($pdate1); $int_begin=datefromdmy(date('d.m.', $int_begin).date('Y'));
						
						$int_end=datefromdmy($pdate2);  $int_end=datefromdmy(date('d.m.', $int_end).date('Y'));
						
						$our_date=$f['birthdate_unf']; $our_date=datefromdmy(date('d.m.', $our_date).date('Y'));
						
						
						 
						
						//echo date('d.m.Y', $int_begin).' '.date('d.m.Y', $int_end).' '.date('d.m.Y', $our_date).'<br>';
						
						
						if(($our_date<$int_begin)||($our_date>$int_end)) continue;
						
						$f['sortable_date']=$our_date;
					}else{
						$f['sortable_date']=$f['birthdate_unf'];
					}
					
					$alls1[]=$f;
				}
				
				
				//разбор сотрудников
				for($i=0; $i<$rc2; $i++){
					
					$f=mysqli_fetch_array($rs2);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					//$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
					$f['pasp_bithday_unf']=$f['pasp_bithday'];
					
					if($f['pasp_bithday']!="0") $f['pasp_bithday']=date("d.m.Y",$f['pasp_bithday']);
					
					
					//найти юбил€ров
					if( in_array(  (int)date('Y') - (int)date('Y', $f['pasp_bithday_unf']), $this->yub)) $f['has_yub']=true;
					
					if(!$check_year){
						//фильтраци€ по попаданию даты рождени€ в выбранные даты текущего года
						$int_begin=datefromdmy($pdate1); $int_begin=datefromdmy(date('d.m.', $int_begin).date('Y'));
						
						$int_end=datefromdmy($pdate2);  $int_end=datefromdmy(date('d.m.', $int_end).date('Y'));
						
						$our_date=$f['pasp_bithday_unf']; $our_date=datefromdmy(date('d.m.', $our_date).date('Y'));
						 
						//echo date('d.m.Y', $int_begin).' '.date('d.m.Y', $int_end).' '.date('d.m.Y', $our_date).'<br>'; 
						if(($our_date<$int_begin)||($our_date>$int_end)) continue;	
						
						$f['sortable_date']=$our_date;
					}else{
						$f['sortable_date']=$f['pasp_bithday_unf'];
					}
					
					$alls2[]=$f;
				}
				
				//сортировка по дате
				if(!$check_year){
					$alls1=ArraySorter::SortArr($alls1, 'sortable_date', 0);
					$alls2=ArraySorter::SortArr($alls2, 'sortable_date', 0);
				}
				 
		  //разбивка на страницы
		 /* if($print==1){
				if($has_content&&($prefix==1)){
					$per_one=1;
					$per_other=1;	
				}else{
				if($prefix!=6){
					$per_one=7;
					$per_other=9;	
				}else{
					$per_one=3;
					$per_other=7;	
				}
				}
				$was_one=false; $cter=0;
				foreach($alls as $k=>$v){
					$cter++;	
					
					
					if((!$was_one)&&($cter>=$per_one)){
						$v['break']=true;
						$was_one=true;
						$cter=0;	
					}elseif($was_one&&($cter>=$per_other)){
						$v['break']=true;
						
						$cter=0;	
					}
					
					$alls[$k]=$v;
				}
				
			  
		  }
			*/
			
			
			 
		  $sm->assign('items1',$alls1);
		  
		  $sm->assign('items2',$alls2);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec2->GetUris();
	   $user=''; $supplier=''; $city=''; $share_user='';
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
				
		 
			if($v->GetName()=='user') $user=$v->GetValue();
			if($v->GetName()=='supplier') $supplier=$v->GetValue();
		 
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		 
		//сотрудник
		if(strlen($user)>0){
				$_ids=explode(';', $user);
				
				$sql='select * from user where id in('.implode(', ', $_ids).') order by name_s';
				
				 
				 
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$our_users=array();
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					$our_users[]=$f;
				}
				$sm->assign("our_users", $our_users);
			 
			}
		//контрагент
		if(strlen($supplier)>0){
			$_ids=explode(';', $supplier);
			
			$sql='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id where s.id in('.implode(', ', $_ids).') order by s.full_name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f;
			}
			$sm->assign("our_suppliers", $our_users);
		 
		}
		
		  
	   
	   
	    $link=$dec2->GenFltUri();
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
	   
	   
		
		
		
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_edit_user',$can_edit_user);
		$sm->assign('can_edit_supplier',$can_edit_supplier);
		
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>