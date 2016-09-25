<?
 
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
 
require_once('sched.class.php');
require_once('sched_history_group.php');
require_once('sched_filegroup.php');
require_once('sched_fileitem.php');


require_once('supplier_cities_group.php');
require_once('suppliercontactgroup.php');
require_once('suppliercontactkindgroup.php');
require_once('supplier_responsible_user_group.php');


//Отчет Новый клиент
class AnSchedNewCli{

	public function ShowData( $supplier_ids,  $user_ids,  $viewed_ids, $pdate1, $pdate2,  $template, DBDecorator $dec2,$pagename='files.php',  $do_it=false, $can_print=false, $can_edit=false, &$alls, $result=NULL, $supplier_kinds=NULL){
		
		 
		 
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
			$_cg=new SupplierContactGroup;
		$_sg=new SupplierCitiesGroup;
		
			$_rg=new SupplierResponsibleUserGroup;
		
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0; $has_holdings=0;
			
			$fields=$dec2->GetUris(); 
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_extended') $has_content=$v->GetValue();
				
				 if($v->GetName()=='print') $print=$v->GetValue();
				  if($v->GetName()=='prefix') $prefix=$v->GetValue();
				  
				   if($v->GetName()=='has_holdings') $has_holdings=$v->GetValue(); 
				  
				  
			}
		 
				
				
				$sql='select distinct p.*, po.name as opf_name,
				sb.name as branch_name, ssb.name as subbranch_name
				
				 from supplier as p 
			left join opf as po on p.opf_id=po.id 
		    left join supplier_responsible_user as sr on sr.supplier_id=p.id
			left join supplier_sprav_city as sc on sc.supplier_id=p.id
			left join sprav_city as c on c.id=sc.city_id
			
			left join supplier_branches as sb on p.branch_id=sb.id
			left join supplier_branches as ssb on p.subbranch_id=ssb.id
			 
			 ';
				//фильтр по дате
				$date_flt='
				where 
				
				 (p.is_active=1 and p.active_first_was_set=1 and p.active_first_pdate between "'. DateFromdmY($pdate1).'" and "'.DateFromdmY($pdate2).'")
				
				
				';
				$sql.=$date_flt;
				
				
				//фильтр по вовлеченным сотрудникам
				if(count($user_ids)>0){
					$sort_flt='';
					$sort_flt='
					and 
			 
					p.id in(select distinct supplier_id from  supplier_responsible_user where user_id in ('.implode(', ',$user_ids).') )
					 
					';
					
					$sql.=$sort_flt;	
					 
				}
				
				//фильтр по вовлеченным контрагентам
				if(count($supplier_ids)>0){
					$su_flt='';	
					
					
					$_for_nn=array();
					$_for_nn[]='p.id in('.implode(', ',$supplier_ids).') ';
					if($has_holdings){
						//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
						$_for_nn[]='p.id in(select distinct ss.id from supplier as ss where ss.is_active=1 and ss.holding_id in( '.implode(', ',$supplier_ids).'))';
						 
						//2. найти все субхолдинги заданного к-та (у кого он холдинг, связь через контрагентов)
						$_for_nn[]='p.id in(select distinct ss.id from supplier as ss  where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$supplier_ids).')))';
						 
						//3. найти все дочерние предприятия субхолдингов заданного предприятия
						$_for_nn[]='p.id in(select distinct ss.id from supplier as ss  /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерняя компания субхолдинга */
			where   ss.is_active=1 and ss.holding_id in(  '.implode(', ',$supplier_ids).')  )';
			
						 
						//4. найти всех контрагентов, у кого субхолдинг = заданному
						$_for_nn[]='p.id in(select distinct ss.id from supplier as ss where ss.is_active=1 and ss.subholding_id in( '.implode(', ',$supplier_ids).'))';
						 
					}
					
					$su_flt='
					and  
					('.implode(' or ',$_for_nn).') 
					';
					
					/*
					$su_flt='
					and(
					p.id in ('.implode(', ',$supplier_ids).')
					)
					';*/
					$sql.=$su_flt;
				}
				

				
				//фильтр по видам контрагентов
				if(($supplier_kinds!==NULL)&&is_array($supplier_kinds)){
					$_flt=array();
					foreach($supplier_kinds as $k=>$v){
						if($v!='none') $_flt[]= 'p.'.$v.'=1 ';
						else $_flt[]= ' (p.is_customer=0 and p.is_supplier=0 and p.is_partner=0) ';
							
					}
					
					$su_flt='';	
					$su_flt='
					and(
					'.implode(' OR ',$_flt).'
					)
					';
					$sql.=$su_flt;
						
				}
				
				//фильтр по видимости
				if(count($viewed_ids)>0){
					
					$su_flt='';	
					$su_flt='
					and(
					p.id in ('.implode(', ',$viewed_ids).')
					)
					';
					$sql.=$su_flt;
				}
				 
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' and '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				
				
				
				$ord_flt=$dec2->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.=' order by '.$ord_flt;
				}			  
				  
				//echo  $sql.'<br><br>';  
				  
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
			 
				
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
				 
					
					if($f['active_first_pdate']!=0) $f['active_first_pdate']=date('d.m.Y H:i:s',$f['active_first_pdate']);
					else $f['active_first_pdate']="";
					
				 
					
				 	if($has_content){
						//подтянуть контакты
						//$_cg=new SupplierContactGroup;
						
						$f['contacts']=$_cg->GetItemsByIdArr($f['id']);	
						
					}
					
					//города
					 
					$f['cities']=$_sg->GetItemsByIdArr($f['id']);
					 
					 //отв сотруд-ки
				
					$f['resp_users']= $_rg->GetUsersArr($f['id']);
					
					$alls[]=$f;
				}
				
				//var_dump($alls);
				 
		  //разбивка на страницы
		  /*if($print==1){
				if($has_content){
					$per_one=3;
					$per_other=10;	
				}else{
				 
					$per_one=26;
					$per_other=38;	
				 
				}
				$was_one=false; $cter=0;
				if(!$has_content){
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
				
			  
		  }
			*/
			 
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec2->GetUris();
	    $user=''; $supplier=''; $city=''; $share_user=''; $branch='';  $country=''; $fo='';
		
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
		 
				
		 
			if($v->GetName()=='user') $user=$v->GetValue();
			if($v->GetName()=='supplier') $supplier=$v->GetValue();
		 	if($v->GetName()=='city') $city=$v->GetValue();
			
			if($v->GetName()=='branch') $branch=$v->GetValue();
			
			if($v->GetName()=='country') $country=$v->GetValue();
			if($v->GetName()=='fo') $fo=$v->GetValue();
			

			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		 
		//сотрудник
		if(strlen($user)>0){
				$_ids=explode(';', $user);
				
				$sql='select p.*, up.name as position_s from user as p
				left join user_position as up on up.id=p.position_id
				 where p.id in('.implode(', ', $_ids).') order by name_s';
				
				 
				 
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
		
		//города
		 
		if(strlen($city)>0){
			$_ids=explode(';', $city);
			
			$sql='select c.*, r.name as region_name, o.name as okrug_name, sc.name as country_name
		
		 from sprav_city as c
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on c.country_id=sc.id
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					$f['fullname']=$f['name'];
				if(strlen($f['okrug_name'])>0) $f['fullname'].=', '.$f['okrug_name'];
				if(strlen($f['region_name'])>0) $f['fullname'].=', '.$f['region_name'];
				if(strlen($f['country_name'])>0) $f['fullname'].=', '.$f['country_name'];
				
				
				$our_users[]=$f;
			}
			$sm->assign("our_cities", $our_users);
		 	 
		}
		
		
		//страна
		if(strlen($country)>0){
			$_ids=explode(';', $country);
			
			$sql='select c.* 
		
		 from sprav_country as c
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					 
				$our_users[]=$f;
			}
			$sm->assign("our_countries", $our_users);
		 	 
		}
		
		//фед. округ
		if(strlen($fo)>0){
			$_ids=explode(';', $fo);
			
			$sql='select c.* 
		 from sprav_district as c
		
		where  c.id in('.implode(', ', $_ids).') order by c.name';
			
		//	 echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
					 
				$our_users[]=$f;
			}
			$sm->assign("our_fos", $our_users);
		 	 
		}
		
		
		

		
		//отрасли
		if(strlen($branch)>0){
			$_ids=explode(';', $branch);
			
			$sql='select p.*, r.name as parent_name from supplier_branches as p left join supplier_branches as r on p.parent_id=r.id where p.id in('.implode(', ', $_ids).') order by r.name, p.name ';
		//	echo $sql;
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				
				$val='';
			if($f['parent_name']!="") $val.=$f['parent_name'].' - ';
			$val.=$f['name'];
				
				$f['name']=$val;
				
				$our_users[]=$f;
			}
			$sm->assign("our_branches", $our_users);
		 
		}
		
		  
	  // echo $prefix;
	   
	    $link=$dec2->GenFltUri('&', $prefix);
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub'.$prefix.'=1';
		$sm->assign('link',$link);
		//$sm->assign('sortmode',$sortmode);
	   
	   
		
		
		
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>