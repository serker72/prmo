<?
require_once('billpospmformer.php');
require_once('supplieritem.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('bdetailsgroup.php');
require_once('sched.class.php');
require_once('sched_history_group.php');
require_once('sched_filegroup.php');
require_once('sched_fileitem.php');


class AnSched{

	public function ShowData(  $template, DBDecorator $dec2,$pagename='files.php',  $do_it=false, $can_print=false, $can_edit=false, &$alls, $result=NULL, $supplier_kinds=NULL){
		
		 
		 
		//$_au=new AuthUser;
		//$_res=$_au->Auth();
		
		//сколько записей
		
		//сколько сотрудников
		$_sotr_arr=array();
		
		//сколько ответственных
		$_otv_arr=array();
		
		//сколько вход, исход звонков
		$_in_arr=array(); $_out_arr=array();
		
		//сколько контрагентов
		$_suppliers=array(); $_supplier_names=array();
		
		//сколько встреч по командировкам
		$_meets_by_koms=array();
		
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
			$_cg=new Sched_CityGroup;
		$_sg=new Sched_SupplierGroup;
		
		
		if($do_it){
			
			$has_content=false; $print=0; $prefix=0;
			$fields=$dec2->GetUris();
			foreach($fields as $k=>$v){
				
				 
				if($v->GetName()=='has_content') $has_content=$v->GetValue();
				
				 if($v->GetName()=='print') $print=$v->GetValue();
				  if($v->GetName()=='prefix') $prefix=$v->GetValue();
			}
			 
		 //	if( $print==1) echo 'zzzzzzzzzzzzzz<h1>zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz</h1>';
			  
				$sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			m.name as meet_name,
			
			u1.name_s as user_name_1, u1.login as user_login_1,
			u2.name_s as user_name_2, u2.login as user_login_2,
			
			uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
			par.code as parent_code, par.topic as parent_topic, ps.name as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active
					 
					 
				from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join sched as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
				 
				';
				
				$db_flt=$dec2->GenFltSql(' and ');
				if(strlen($db_flt)>0){
					$sql.=' where '.$db_flt;
				//	$sql_count.=' where '.$db_flt;	
				}
				
				
				
				//фильтр по типам контрагентов
				if(($supplier_kinds!==NULL)&&is_array($supplier_kinds)){
					
					$_flt=array();
					foreach($supplier_kinds as $k=>$v){
						if($v!='none') $_flt[]= $v.'=1 ';
						else $_flt[]= ' (is_customer=0 and is_supplier=0 and is_partner=0) ';
							
					}
					
					$su_flt='';	
					$su_flt='
					and(
					(p.kind_id in(1,2,3,5) and p.id in(select distinct sched_id from  sched_suppliers where supplier_id in (select id from supplier where '.implode(' OR ',$_flt).'))) or
				
					(p.kind_id=4 and  p.id in(select distinct sched_id from  sched_contacts where supplier_id in (select id from supplier where '.implode(' OR ',$_flt).')))
					)
					';
					$sql.=$su_flt;	
				}
				
				
				
				
				
				
				
				$ord_flt=$dec2->GenFltOrd();
				if(strlen($ord_flt)>0){
					$sql.=' order by '.$ord_flt;
				}			  
				  
				//echo  $sql.'<br><br>';  
				  
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$_hg=new Sched_HistoryGroup;
				
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					
					$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
					
					if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
					
					$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
					
					if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
					else $f['confirm_price_pdate']='-';
					
					 
					if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
					else $f['confirm_shipping_pdate']='-';
					 
						$_res=new Sched_Resolver($f['kind_id']);
						
						$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f, ($print===0));
					 
					
					if($f['kind_id']==1){
						
						
						if($has_content){
							
							$f['content']=$_hg->ShowHistory($f['id'],'',new DBDecorator, false,false,false,$result,false, false,$rr,  false);
							//var_dump($f['content']);
						}
						
						if(!in_array($f['user_login_2'], $_otv_arr)) $_otv_arr[]=$f['user_login_2'];
					}
					
					
					if(($f['kind_id']==2)||($f['kind_id']==3)){
						//города, контрагенты
						$f['cities']=$_cg->GetItemsByIdArr($f['id']);
						$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);
						
						foreach($f['suppliers'] as $k2=>$v2){
								if(!in_array($v2['supplier_id'], $_suppliers)) {
									$_suppliers[]=$v2['supplier_id'];
									$_supplier_names[]=$v2['full_name'].', '.$v2['opf_name'];
								}
								
								
								if($f['kind_id']==2){
									$t_arr=array($v2['supplier_id'], $f['id']);
									if(!in_array($t_arr, $_meets_by_koms)) $_meets_by_koms[]=$t_arr;
								}

						}
						
					}
					
					if(($f['kind_id']==1)||($f['kind_id']==5)){
						// контрагенты
						$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);
						foreach($f['suppliers'] as $k2=>$v2){
								if(!in_array($v2['supplier_id'], $_suppliers)) {
									$_suppliers[]=$v2['supplier_id'];
									$_supplier_names[]=$v2['full_name'].', '.$v2['opf_name'];
								}
						}	
						
					}
					
					
					if(($f['kind_id']==5)){
						$f['share_users']=$_res->instance->GetUsersArr($f['id'], $f);
					}
					
					if(($f['kind_id']==5)||($f['kind_id']==1)){	
						
						
						$filedec=new DBDEcorator;
					 
						$_fg=new SchedFileGroup(1,  $f['id'],  new FileDocFolderItem(1, $f['id'], new SchedFileItem(1)));;
						
						if($print==0) $_template='an_sched/incard_list.html';
						else $_template='an_sched/incard_list_print.html';
						$f['files']=$_fg->ShowFiles($_template, $filedec,0,100000,'ed_sched.php', 'sched_file.html', 'swfupl-js/sched_files.php',  
			 false, 
			 false,
			 false,
			0,
			  false, 
			false , 
			 false, 
			 false ,    
			  '',  
			  
			 false,
			   $result,  
			   new DBDecorator, 'file_' 
			   );
						
					}
					
					if($f['kind_id']==4){
						//if(!in_array($f[	
						if($f['incoming_or_outcoming']==0){
							//vh
							if(!in_array($f['id'], $_in_arr)) $_in_arr[]=$f['id'];	
						}else{
							if(!in_array($f['id'], $_out_arr)) $_out_arr[]=$f['id'];
						}
						
						
						$sql2='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id inner join sched_contacts as sc on sc.supplier_id=s.id where sc.sched_id="'.$f['id'].'"  order by s.full_name';
			
			 
			 
						$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
						$rs2=$set2->GetResult();
						$rc2=$set2->GetResultNumRows();
						
						 
						for($i2=0; $i2<$rc2; $i2++){
							
							$v2=mysqli_fetch_array($rs2);
							//$our_users[]=$f;
							
							if(!in_array($v2['id'], $_suppliers)) {
									$_suppliers[]=$v2['id'];
									$_supplier_names[]=$v2['full_name'].', '.$v2['opf_name'];
								}
							$f['call_suppliers'][]=$v2;
							
						}
						
						
					}
					
					if(!in_array($f['manager_id'], $_sotr_arr)) $_sotr_arr[]=$f['manager_id'];
					
					$alls[]=$f;
				}
				
				 
		  //разбивка на страницы
		  /*if($print==1){
				if($has_content&&($prefix==1)){
					$per_one=1;
					$per_other=1;	
				}else{
					$per_one=4;
					$per_other=8;	
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
				
			  
		  }*/
			
			 
		  $sm->assign('items',$alls);
		}
		
	   
	   
	   
	   
	  
	   $_user_ids=array('','','','');
	   $fields=$dec2->GetUris();
	    $user=''; $supplier=''; $city=''; $share_user=''; $country=''; $fo='';
		
		foreach($fields as $k=>$v){
			
			//echo $v->GetValue();
			
			if(eregi('^user1', $v->GetName())) $_user_ids[0]= $v->GetValue();
				if(eregi('^user2', $v->GetName())) $_user_ids[1]= $v->GetValue();
				if(eregi('^user3', $v->GetName())) $_user_ids[2]= $v->GetValue();
				if(eregi('^user4', $v->GetName())) $_user_ids[3]= $v->GetValue();
				
				
				
		 
			if($v->GetName()=='user') $user=$v->GetValue();
			if($v->GetName()=='supplier') $supplier=$v->GetValue();
			if($v->GetName()=='city') $city=$v->GetValue();
			
			if($v->GetName()=='share_user') $share_user=$v->GetValue();
			
			
			if($v->GetName()=='country') $country=$v->GetValue();
			if($v->GetName()=='fo') $fo=$v->GetValue();
			

			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		//подставим пользователей, если они есть
	 
		foreach($_user_ids as $k=>$v){
			$index=$k+1;
			if(strlen($v)>0){
				$_ids=explode(';', $v);
				
				//$sql='select * from user where id in('.implode(', ', $_ids).') order by name_s';
				$sql='select p.*, up.name as position_s from user as p
				left join user_position as up on up.id=p.position_id
				 where p.id in('.implode(', ', $_ids).') order by p.name_s';
				
				
				 
				 
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$our_users=array();
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					$our_users[]=$f;
				}
				$sm->assign("our_users".$index, $our_users);
			 
			}
				
			
		}
		//менеджер
		if(strlen($user)>0){
				$_ids=explode(';', $user);
				
				//$sql='select * from user where id in('.implode(', ', $_ids).') order by name_s';
				$sql='select p.*, up.name as position_s from user as p
				left join user_position as up on up.id=p.position_id
				 where p.id in('.implode(', ', $_ids).') order by p.name_s';
				
				
				 
				 
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
		
		//город
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
		



		//с кем поделился (заметкой)
		if(strlen($share_user)>0){
				$_ids=explode(';', $share_user);
				
				$sql='select p.*, up.name as position_s from user as p
				left join user_position as up on up.id=p.position_id
				 where p.id in('.implode(', ', $_ids).') order by p.name_s';
				
				 
				 
				$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$our_users=array();
				for($i=0; $i<$rc; $i++){
					
					$f=mysqli_fetch_array($rs);
					$our_users[]=$f;
				}
				$sm->assign("share_users", $our_users);
			 
			}
	   
	   
	    $link=$dec2->GenFltUri();
	    $link=$pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
	   
	   
		//сколько записей
		$sm->assign('no', count($alls));
		
		//сколько сотрудников
		$sm->assign('sotr_no', count($_sotr_arr));
		
		//сколько ответственных
		$sm->assign('otv_no',count($_otv_arr));
		
		//сколько вход, исход звонков
		$sm->assign('in_no', count($_in_arr)); $sm->assign('out_no', count($_out_arr));
		
		//skol'ko kontragetnov

		$sm->assign('supplier_no',count($_suppliers)); 
		
		//сколько встреч по командировкам
		$sm->assign('meets_by_koms', count($_meets_by_koms));
		
		
		$sql='select s.*, opf.name as opf_name from supplier as s left join opf as opf on s.opf_id=opf.id where s.id in('.implode(', ', $_suppliers).') order by s.full_name';
			
			 
			 
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$our_users=array();
			for($i=0; $i<$rc; $i++){
				
				$f=mysqli_fetch_array($rs);
				$our_users[]=$f['full_name'].', '.$f['opf_name'];
			}
		
		$sm->assign('supplier_list', implode('; ',$our_users));
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('do_it',$do_it);	
	
		$sm->assign('pagename',$pagename);
		//$sm->assign('extended_an',$extended_an);	
			
		return $sm->fetch($template);
	}
	
	
	
}
?>