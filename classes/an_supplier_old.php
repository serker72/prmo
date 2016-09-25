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

require_once('acc_in_item.php');

require_once('supcontract_item.php');
require_once('supcontract_group.php');
require_once('usersgroup.php');
require_once('authuser.php');

class AnSupplier{
	
	public $can_view_all_pays=false;
	public $result=NULL;

	public function ShowData($supplier_name, $org_id, $pdate1, $pdate2, $extended_an=0, $template, DBDecorator $dec,$pagename='files.php', $do_show_data=false, $can_print=false, $dec_sep=DEC_SEP, $similar_firms=0, $by_contract=0, $limited_supplier=NULL, $can_view_all_pays=false ){
		
		$this->can_view_all_pays=$can_view_all_pays;
		
		$au=new AuthUser;
		$result=$au->Auth();
		$this->result=$result;
		
		$_sg=new SuppliersGroup;
		$sg=$_sg->GetItemsWithOpfArr( false,  $org_id);
		
		/*echo '<pre>';
		var_dump($sg);
		echo '</pre>';
		
		*/
		
		
		//исключим из списка недоступных к-тов
		if($limited_supplier!==NULL) {
			foreach($sg as $k=>$v){
				if(!in_array($v['id'], $limited_supplier)) unset($sg[$k]);	
			}
		}
		
		
		$_ug=new UsersGroup;
		$ug=$_ug->GetItemsArr(0, 1);
		
		//дополним цкил по п-кам сотрудниками
		
			foreach($sg as $k=>$v){
				$v['supplier_or_user']=0;
				$sg[$k]=$v;	
			}
		 
		if($limited_supplier===NULL) {
			foreach($ug as $k=>$v){
				$v['supplier_or_user']=1;
				$sg[]=$v;	
			}
		}
		
		
		//добавим к контрагентам себя, если есть ограничения по к-там
		if($limited_supplier!==NULL) {
			/*foreach($sg as $k=>$v){
				if(!in_array($v['id'], $limited_supplier)) unset($sg[$k]);	
			}*/
			$result['supplier_or_user']=1;
			$sg[]=$result;
		}
		
		
		
		$_ic=new InvCalcItem;
		
		$_ai=new AccItem;
		$_ai_in=new AccInItem;
		
		$supplier_names=array(); $supplier_id=array(); $user_names=array(); $user_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			 
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where ((is_org=0 and org_id="'.$org_id.'") or (is_org=1 and id<>"'.$org_id.'")) and is_active=1  and (';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				if(strlen(trim($v))>0){
					if($similar_firms==0) $_supplier_names[]=' (BINARY full_name ="'.trim($v).'") ';
					else $_supplier_names[]=' (full_name LIKE "%'.trim($v).'%") ';
				}
			}
			
			$sql.= implode(' OR ',$_supplier_names).') order by full_name desc';
			
			//echo $sql;
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$supplier_id[]=$f['id'];	
			}
			
			
			
			//составляем массив по пол-лям
			$sql='select * from user where is_active=1  and (';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				if(strlen(trim($v))>0){
					if($similar_firms==0) $_supplier_names[]=' (BINARY name_s ="'.trim($v).'") ';
					else $_supplier_names[]=' (name_s LIKE "%'.trim($v).'%") ';
				}
			}
			
			$sql.= implode(' OR ',$_supplier_names).') order by name_s asc';
			
			//echo $sql;
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$user_id[]=$f['id'];	
			}
			
		}
		
		
		
		
		
		//нигде не используется?????
		if(is_array($supplier_id)&&(count($supplier_id)>0)) {
			$supplier_filter=implode(', ',$supplier_id);
			if(strlen($supplier_filter)>0) $supplier_filter=' and supplier_id in('.$supplier_filter.') ';
			
		}
		
	/*	if(is_array($user_id)&&(count($user_id)>0)) {
			$user_filter=implode(', ',$user_id);
			if(strlen($user_filter)>0) $user_filter=' and inner_user_id in('.$user_filter.') ';
			
		}
		*/
		
		//var_dump($supplier_id);
		
		//var_dump($supplier_name);
		//echo 'zzzzzzzzzzzzzzzzzzzzzz';
		//echo "<h1>$supplier_name</h1>";
		
		$_bpm=new BillPosPMFormer;
		$_opf=new OpfItem;
		
	
		
		$_au=new AuthUser;
		//$_res=$_au->Auth();
		
		
		$sm=new SmartyAdm;
		
		$_org=new OrgItem;
		
		$org=$_org->getitembyid($org_id);
		$opf=$_opf->GetItemById($org['opf_id']);
		
		$sm->assign('org', $org);//ORGANIZATION_TITLE);
		$sm->assign('opf', $opf);
		
		//$sm->assign('manager',$_res);
		
		
		$sm->assign('period',date("d.m.Y H:i:s",$pdate1).' - '.date("d.m.Y H:i:s",$pdate2));
		
		if($do_show_data){
		 
		  
		  //var_dump($supplier_id);
		  
	 
		  
		  
		  
		  $total_dolg=0; $total_plus=0;
		  
		  $_acc_in_ids= array(); $_pay_ids=array();
		  $_acc_ids= array(); $_pay_in_ids=array(); $_demand_ids=array();
		  
		  //цикл по поставщикам
		  //print_r($user_id);
		 // var_dump($sg);
		  foreach($sg as $k=>$v){
			  
			 //  echo $v['id'].'<br>';
			  
			  if(is_array($supplier_id)&&(count($supplier_id)>0)){
				  //$v['supplier_or_user']=1;
				  if(($v['supplier_or_user']==0)&&!in_array($v['id'], $supplier_id)){
					   
					   //echo $v['id'];
					   continue;					
				  }else{
					  //echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
				  }
			  }
			  
			  //если есть хоть одно условие на пол-ля и ни одного на к-та = к-та пропускаем
			  if(($v['supplier_or_user']==0)&&(count($user_id)>0)&&(count($supplier_id)==0)) continue;
			  
			  if(is_array($user_id)&&(count($user_id)>0)){
				  //$v['supplier_or_user']=1;
				  if(($v['supplier_or_user']==1)&&!in_array($v['id'], $user_id)){
					   continue;					
				  }else{
					  //echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
				  }
			  }
			   //если есть хоть одно условие на к-та и ни одного на п-ля = п-ля пропускаем
			  if(($v['supplier_or_user']==1)&&(count($user_id)==0)&&(count($supplier_id)>0)) continue;
			  
			  
			 // echo $v['id'].'<br>';
			  
			  //нужны итоги по каждой организации
			  $v['before_ost_unf']=$this->OstBySup($v['id'],$pdate1,$org_id, NULL, $v['supplier_or_user']);
			  $v['after_ost_unf']=$this->OstBySup($v['id'],$pdate2+24*60*60-1,$org_id,NULL, $v['supplier_or_user']);
			  
			  $before_ost+=$v['before_ost_unf'];
			  $after_ost+=$v['after_ost_unf'];

			  
			  
			  $v['before_ost']=number_format($v['before_ost_unf'],2,'.',$dec_sep);
			  $v['after_ost']=number_format($v['after_ost_unf'],2,'.',$dec_sep);
			  
						  
			  //раскрутка всех документов по данной организации
			  
			  //если выбран режим  С разбивкой по договорам, то получить список договоров
			  //если нет такого режима - то показывать все документы к-та подряд
			  
			  
			 
			  
			  $_dog_arr=array();
			  
			  if(($by_contract==1)){
				  if(($v['supplier_or_user']==0)){
				//получаем список договоров
					$_scg=new SupContractGroup;
					$contracts=$_scg->GetItemsByIdArr($v['id']);
					//var_dump($contracts);
					$_dog_arr=$contracts;
					$_dog_arr[]=array('id'=>0); //подгрузим общие по всем дог-рам док-ты
				  }
				  if(count($_dog_arr)==0)  $_dog_arr[]=array('id'=>0);  //
				 
			  }else 
			  	 if(count($_dog_arr)==0)   $_dog_arr[]=NULL; // 
			  
			 // if(($v['id']==2)&&($v['supplier_or_user']==1))  var_dump( ($_dog_arr));
			  
			  //нам нужно перебрать поступления и оплаты, входящие в указанный период
			   $subs=array();
			  
			  $supplier_dolg=0; $supplier_plus=0; 	  
			  foreach($_dog_arr as $dk=>$dv){			
				 
				  
				  if($dv===NULL) $contract_flt='';
				  else $contract_flt=' and contract_id="'.$dv['id'].'" ';
				  
				  // if(($v['id']==2)&&($v['supplier_or_user']==1))   var_dump( $contract_flt);
				  
				  $query_flt=''; $query_flt1='';
				  if($v['supplier_or_user']==0) {
					  $query_flt=' supplier_id="'.$v['id'].'" ';
					  $query_flt1=' supplier_id="'.$v['id'].'" ';
				  }else{
					   $query_flt=' inner_user_id="'.$v['id'].'" ';
					   $query_flt1=' supplier_id="-1" ';
				  }
				  
				  //фильтр для исх. оплат
				  //если нет прав на просмотр всех исх оплат - фильтровать по коду А и получателлю
				  //$this->can_view_all_pays
				  $out_pay_flt='';
				  if(!$this->can_view_all_pays){
					  //$out_pay_flt=' and (code_id=59 or inner_user_id='.$this->result['id'].' )';
				  }
				  
				  
				  $sql='
				  (select a.id, a.pdate, "0" as code, "2" as kind, sum(ap.total) as value, a.given_pdate, a.given_no, "0" as debt_id from acceptance as a
				  left join acceptance_position as ap on a.id=ap.acceptance_id
				  where a.is_confirmed=1 and a.is_incoming=1 and (a.given_pdate between "'.$pdate1.'" and "'.$pdate2.'") and  a.org_id="'.$org_id.'" and a.bill_id in(select id from bill where '.$query_flt1.' and org_id="'.$org_id.'" and is_confirmed_price=1 '.$contract_flt.')
				  group by a.id
				  )
				  UNION ALL
				  (select id, pdate, code, "1" as kind, value, given_pdate, given_no, "0" as debt_id from payment
				  where is_confirmed=1 and is_incoming=0 and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'") and  org_id="'.$org_id.'" and '.$query_flt.' '.$contract_flt.' '.$out_pay_flt.'
				  )
				  UNION ALL
				  (select id, pdate, code, "6" as kind, debt as value, invcalc_pdate as given_pdate, given_no, debt_id
				  from invcalc where is_confirmed_inv=1 and  (invcalc_pdate between "'.$pdate1.'" and "'.$pdate2.'") and org_id="'.$org_id.'"  and '.$query_flt1.' '.$contract_flt.'
				  )
				  UNION ALL
				  (select a.id, a.pdate, "0" as code, "5" as kind, sum(ap.total) as value, a.given_pdate, a.given_no, "0" as debt_id from acceptance as a
				  left join acceptance_position as ap on a.id=ap.acceptance_id
				  where a.is_confirmed=1 and a.is_incoming=0 and (a.given_pdate between "'.$pdate1.'" and "'.$pdate2.'") and  a.org_id="'.$org_id.'" and a.bill_id in(select id from bill where '.$query_flt1.' and org_id="'.$org_id.'" and is_confirmed_price=1 '.$contract_flt.')
				  group by a.id
				  )
				  UNION ALL
				  (select id, pdate, code, "4" as kind, value, given_pdate, given_no, "0" as debt_id from payment
				  where is_confirmed=1 and is_incoming=1 and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'") and  org_id="'.$org_id.'" and '.$query_flt.' '.$contract_flt.'
				  )
				  
				  UNION ALL
				  (select id, pdate, id as code, "7" as kind, value, given_pdate, given_no, "0" as debt_id from demand
				  where is_confirmed=1 and (given_pdate between "'.$pdate1.'" and "'.$pdate2.'") and  org_id="'.$org_id.'" and '.$query_flt.' 
				  )
				  
				  order by 6 asc, 4 asc,  1 asc
				  
				  ';
				 
				  
				 // if($v['id']==2) echo $sql.'<br>';
				  
				// echo $sql;
				  
				  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
				  $rs=$set->GetResult();
				  $rc=$set->GetResultNumRows();
				  
				  
				 
				  
				  $between_before_ost=$v['before_ost_unf'];
				  $between_after_ost=$v['before_ost_unf'];
				  
				  
				  $contract_dolg=0; $contract_plus=0; 
				  $docs_array=array();
				  for($i=0; $i<$rc; $i++){
					  $f=mysqli_fetch_array($rs);
					  
					 
					  
					  $f['between_before_ost_unf']=$between_before_ost;
					  $f['between_before_ost']=number_format(round($between_before_ost,2),2,'.',$dec_sep);
					  
					  $f['pdate']=date("d.m.Y",$f['pdate']);
					  $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
					  
					  if($f['kind']==1){
						  $f['plus_unf']=$f['value'];
						  $f['plus']=number_format(round($f['value'],2),2,'.',$dec_sep);
						  $total_plus+=$f['plus_unf'];
						  
						  $f['dolg']=0;
						  
						  
						  if(!in_array($f['id'],$_pay_ids)) $_pay_ids[]=$f['id'];	
						  
					  }elseif($f['kind']==2){
						  //пройдем позиции поступления
						  $total_by_pos=0;
						  
						 // $total_by_pos=$_ai->CalcCost($f['id']);
						  $total_by_pos=$f['value'];
						  
						  
						  $f['dolg_unf']=$total_by_pos;
						  $f['dolg']=number_format(round($total_by_pos,2),2,'.',$dec_sep);
						  $f['plus']=0;
					  
						  
						  $total_dolg+=$f['dolg_unf'];	
						  
						  if(!in_array($f['id'],$_acc_in_ids)) $_acc_in_ids[]=$f['id'];
						  
					  }elseif($f['kind']==6){
						  $sign=1;
						  if($f['debt_id']==3){
							  //-
							  $sign=-1;
								  
						  }
						  
						  if($dv!==NULL) {
							 	//$between_after_ost Нужно считать функцией, если мы разбиваем по договорам
								//блок для учета актов инвентаризации при разбивке по договорам
								//акты инвентаризации не относятся к договорам
								$f['between_before_ost_unf']=$this->OstBySup($v['id'],datefromdmy($f['given_pdate'])+24*60*60-1,$org_id, $f['id'],$v['supplier_or_user']);
							  $between_after_ost=$f['between_before_ost_unf'];
								//echo date('d.m.Y', datefromdmy($f['given_pdate'])-24*60*60);							  
								$f['between_before_ost']=number_format(round($f['between_before_ost_unf'],2),2,'.',$dec_sep);
							  
							  $delta=$sign*$f['value']-$f['between_before_ost_unf'];
							  
							  
							   //echo $delta;
						  }else {
								$delta=$sign*$f['value']-$between_after_ost;
						    	
						  } 
						  	
							  if($delta<=0){
								  $f['dolg_unf']=abs($delta);
								  $f['dolg']=number_format(round(abs($delta),2),2,'.',$dec_sep);
								  $f['plus_unf']=0;
								  $f['plus']=0;
							  }else{
								  $f['dolg_unf']=0;
								  $f['dolg']=0;
								  $f['plus_unf']=$delta;
								  $f['plus']=number_format(round($delta,2),2,'.',$dec_sep);
							  }
							  
							  $total_plus+=$f['plus_unf'];
							  $total_dolg+=$f['dolg_unf'];	
							  
								
						  
						  $inv=$_ic->GetItemByid($f['id']);
						  
						  $f['reason_id']=$inv['reason_id'];
						  $f['reason_txt']=$inv['reason_txt'];
						  $f['akt_given_pdate']=date('d.m.Y',$inv['akt_given_pdate']);
						  $f['akt_given_no']=$inv['akt_given_no'];
					  }
					  elseif($f['kind']==4){
						  
						  //входящая оплата
						  
						  $f['dolg_unf']=$f['value'];
						  $f['dolg']=number_format(round($f['value'],2),2,'.',$dec_sep);
						  $total_dolg+=$f['dolg_unf'];
						  
						  $f['plus']=0;
						  
						  
						  if(!in_array($f['id'],$_pay_in_ids)) $_pay_in_ids[]=$f['id'];	
						  
					  }elseif($f['kind']==5){
						  //пройдем позиции реализации
						  $total_by_pos=0;
						  
						 // $total_by_pos=$_ai_in->CalcCost($f['id']);
						 $total_by_pos=$f['value'];
						  
						  
						  
						  $f['plus_unf']=$total_by_pos;
						  $f['plus']=number_format(round($total_by_pos,2),2,'.',$dec_sep);
						  $f['dolg']=0;
					  
						  
						  $total_plus+=$f['plus_unf'];	
						  
						  if(!in_array($f['id'],$_acc_ids)) $_acc_ids[]=$f['id'];
						  
					  }elseif($f['kind']==7){
						  //пройдем позиции требоваия
						  $total_by_pos=0;
						  
						 // $total_by_pos=$_ai->CalcCost($f['id']);
						  $total_by_pos=$f['value'];
						  
						  
						  $f['dolg_unf']=$total_by_pos;
						  $f['dolg']=number_format(round($total_by_pos,2),2,'.',$dec_sep);
						  $f['plus']=0;
					  
						  
						  $total_dolg+=$f['dolg_unf'];	
						  
						  if(!in_array($f['id'],$_demand_ids)) $_demand_ids[]=$f['id'];
						  
					  }
					  
					 // echo $f['dolg_unf'].' ';
					  $supplier_dolg+=$f['dolg_unf'];
					  $supplier_plus+=$f['plus_unf'];
					  
					  $contract_dolg+=$f['dolg_unf'];
					  $contract_plus+=$f['plus_unf'];
					  
					  $between_before_ost=$between_before_ost-$f['dolg_unf']+$f['plus_unf'];
					  $between_after_ost=$between_after_ost-$f['dolg_unf']+$f['plus_unf'];
					  
					  
					  
					  $f['between_after_ost_unf']=$f['between_after_ost'];
					  $f['between_after_ost']=number_format(round($between_after_ost,2),2,'.',$dec_sep);
					  
					  if($dv===NULL) $subs[]=$f;
					  else $docs_array[]=$f;
					  
					  
					
				  }
				  
				  if($dv!==NULL) {
					  $_dog_arr[$dk]['docs']=$docs_array;
					  
					  
					   
					  
					  if($dv['id']!=0){
						  
						$_dog_arr[$dk]['before_ost_unf']=$this->OstBySupContract($v['id'],$pdate1,$org_id, $dv['id'],NULL,$v['supplier_or_user']);
						$_dog_arr[$dk]['after_ost_unf']=$this->OstBySupContract($v['id'],$pdate2+24*60*60-1,$org_id,$dv['id'],NULL,$v['supplier_or_user']);
					  }else{
						// echo $this->OstBySup($v['id'],$pdate1, $org_id);
						//для общих док-тов - подсчет по всем договорам
						//блок для учета актов инвентаризации при разбивке по договорам
						//акты инвентаризации не относятся к договорам
						$_dog_arr[$dk]['before_ost_unf']=$this->OstBySup($v['id'],$pdate1,$org_id,NULL,$v['supplier_or_user']);
						$_dog_arr[$dk]['after_ost_unf']=$this->OstBySup($v['id'],$pdate2+24*60*60-1,$org_id,NULL,$v['supplier_or_user']);
  
					  }
					  
					  $_dog_arr[$dk]['before_ost']=number_format($_dog_arr[$dk]['before_ost_unf'],2,'.',$dec_sep);
					  $_dog_arr[$dk]['after_ost']= number_format($_dog_arr[$dk]['after_ost_unf'],2,'.',$dec_sep);
					  $_dog_arr[$dk]['dolg_unf']=$contract_dolg;
			  
					  $_dog_arr[$dk]['plus_unf']=$contract_plus;
					  $_dog_arr[$dk]['dolg']=number_format(round($contract_dolg,2),2,'.',$dec_sep);
					  $_dog_arr[$dk]['plus']=number_format(round($contract_plus,2),2,'.',$dec_sep);
					  
					  
					  if(($contract_dolg==0)&&($contract_plus==0)&&($_dog_arr[$dk]['before_ost_unf']==0)&&($_dog_arr[$dk]['after_ost_unf']==0)&&($dv['id']!=0)){
						   $_dog_arr[$dk]['no_turn']=true;
					  }
						
					  
					 
					  $subs[]=$_dog_arr[$dk];
				  }
			  }//конец цикла по договорам
				  
			  $v['dolg_unf']=$supplier_dolg;
			  
			  $v['plus_unf']=$supplier_plus;
			  $v['dolg']=number_format(round($supplier_dolg,2),2,'.',$dec_sep);
			  $v['plus']=number_format(round($supplier_plus,2),2,'.',$dec_sep);
			  
			  $v['subs']=$subs;
			  
			 
			  
			  if(($v['supplier_or_user']==0)&&!in_array($v['id'],$supplier_id)&&($supplier_dolg==0)&&($supplier_plus==0)&&($v['before_ost_unf']==0)&&($v['after_ost_unf']==0)) continue; 
			  
			  if(($v['supplier_or_user']==0)&&in_array($v['id'],$supplier_id)&&($supplier_dolg==0)&&($supplier_plus==0)&&($v['before_ost_unf']==0)&&($v['after_ost_unf']==0)){
				  $v['no_turn']=true;
			  }
			  
			  if(($v['supplier_or_user']==1)&&!in_array($v['id'],$user_id)&&($supplier_dolg==0)&&($supplier_plus==0)&&($v['before_ost_unf']==0)&&($v['after_ost_unf']==0)) continue; 
			  
			  if(($v['supplier_or_user']==1)&&in_array($v['id'],$user_id)&&($supplier_dolg==0)&&($supplier_plus==0)&&($v['before_ost_unf']==0)&&($v['after_ost_unf']==0)){
				  $v['no_turn']=true;
			  }
			  
				  
			  $alls[]=$v;
			  
			  sort($_acc_ids); sort($_pay_ids); sort($_acc_in_ids); sort($_pay_in_ids); sort($_demand_ids);
		  }//конец цикла по к-там
		
		}//конец видимого блока 
		
		
		//ИТОГ!!!
		
		$sm->assign('before_ost_unf',round($before_ost,2));
		$sm->assign('after_ost_unf',round($after_ost,2));
		$sm->assign('after_ost_unf_abs', abs(round($after_ost,2)));
		$sm->assign('after_ost_abs', (number_format(abs(round($after_ost,2)),2,'.',$dec_sep)));
		
		$sm->assign('before_ost',number_format(round($before_ost,2),2,'.',$dec_sep));
		$sm->assign('after_ost',number_format(round($after_ost,2),2,'.',$dec_sep));
		
		

		
		
		
		$sm->assign('_acc_ids', $_acc_ids);
		$sm->assign('_pay_ids', $_pay_ids);
		
		$sm->assign('_acc_in_ids', $_acc_in_ids);
		$sm->assign('_pay_in_ids', $_pay_in_ids);
		
		$sm->assign('_demand_ids', $_demand_ids);
		
		
		$sm->assign('total_plus',number_format(round($total_plus,2),2,'.',$dec_sep));
		$sm->assign('total_dolg',number_format(round($total_dolg,2),2,'.',$dec_sep));
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//имена для отчета
		$supplier_names=array();  $supplier_ids=array();
		foreach($sg as $k=>$v){
			$supplier_ids[]=$v['id'];
			
			$supplier_names[]=$v['opf_name'].' '.$v['full_name'];
			
			if(($v['supplier_or_user']==0)&&in_array($v['id'],$supplier_id)) $supplier_names_selected[]=$v['opf_name'].' '.$v['full_name'];
			if(($v['supplier_or_user']==1)&&in_array($v['id'],$user_id)) $supplier_names_selected[]=$v['name_s'].' '.$v['login'];
		}
		
		$sm->assign('supplier_ids',$supplier_ids);
		$sm->assign('supplier_id',$supplier_id);
		$sm->assign('supplier_names',$supplier_names);
		
		$sm->assign('supplier_names_selected',$supplier_names_selected);
		
		
		
	
		$sm->assign('pagename',$pagename);
		
		//var_dump($do_show_data);
		
		if($supplier_id==0) $sm->assign('do_it', false);
		else $sm->assign('do_it',$do_show_data);	
		
		$sm->assign('can_print',$can_print);	
		
		$sm->assign('pdate',date('d.m.Y'));	
			
		return $sm->fetch($template);
	}
	
	
	
	//остаток на дату
	public function OstBySup($supplier_id, $pdate, $org_id, $except_invcalc_id=NULL, $supplier_or_user=0){
		$_bpm=new BillPosPMFormer;
		
		
		$invcalc_flt='';
		if($except_invcalc_id!==NULL) $invcalc_flt.=' and id<>'.$except_invcalc_id;
		
		  $query_flt=''; $query_flt1='';
				  if($supplier_or_user==0) {
					  $query_flt=' supplier_id="'.$supplier_id.'" ';
					  $query_flt1=' supplier_id="'.$supplier_id.'" ';
				  }else{
					   $query_flt=' inner_user_id="'.$supplier_id.'" ';
					   $query_flt1=' supplier_id="-1" ';
					   
					   
				  }
		
		 //фильтр для исх. оплат
				  //если нет прав на просмотр всех исх оплат - фильтровать по коду А и получателлю
				  //$this->can_view_all_pays
				  $out_pay_flt='';
				  if(!$this->can_view_all_pays){
					  //$out_pay_flt=' and (code_id=59 or inner_user_id='.$this->result['id'].' )';
				  }
		
		
		$before_ost=0;
		//нам нужно перебрать поступления, инввзр, и оплаты, входящие в указанный период
			$sql='
			(select a.id, a.pdate, "0" as code, "2" as kind, sum(ap.total) as value, a.given_pdate, a.given_no, "0" as debt_id
			 from acceptance as a
			 left join acceptance_position as ap on a.id=ap.acceptance_id
			where a.is_confirmed=1 and a.is_incoming=1 and a.given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and a.bill_id in(select id from bill where org_id="'.$org_id.'" and '. $query_flt1.')
			group by a.id
			)
			UNION ALL
			(select id, pdate, code, "1" as kind, value, given_pdate, given_no, "0" as debt_id from payment
			where is_confirmed=1 and is_incoming=0 and given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '.$query_flt.' '.$out_pay_flt.'
			)
			UNION ALL
			(select id, pdate, code, "6" as kind, debt as value, invcalc_pdate as given_pdate, given_no, debt_id
			from invcalc where is_confirmed_inv=1 and invcalc_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '. $query_flt1.' '.$invcalc_flt.'
			)
			
			UNION ALL
			(select a.id, a.pdate, "0" as code, "5" as kind, sum(ap.total) as value, a.given_pdate, a.given_no, "0" as debt_id
			 from acceptance as a
			  left join acceptance_position as ap on a.id=ap.acceptance_id
			where a.is_confirmed=1 and a.is_incoming=0 and a.given_pdate<"'.$pdate.'" and  a.org_id="'.$org_id.'" and a.bill_id in(select id from bill where org_id="'.$org_id.'" and '. $query_flt1.')
			group by a.id
			)
			UNION ALL
			(select id, pdate, code, "4" as kind, value, given_pdate, given_no, "0" as debt_id from payment
			where is_confirmed=1 and is_incoming=1 and given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '.$query_flt.'
			)
			
			 UNION ALL
				  (select id, pdate, id as code, "7" as kind, value, given_pdate, given_no, "0" as debt_id from demand
				  where is_confirmed=1 and given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '.$query_flt.' 
				  )
			
			order by 6 asc, 4 asc,  1 asc
			
			';
		
		//echo $sql.'<p>';
		
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();		
		for($i=0;$i<$rc;$i++){	
			$g=mysqli_fetch_array($rs);
			
			
			if($g['kind']==1){
				$before_ost+=$g['value'];
				
			}elseif($g['kind']==2){
				//пройдем позиции поступления
				$total_by_pos=0;
				
				$total_by_pos=(float)$g['value'];
				
				
				$before_ost-=$total_by_pos;
				
				
			}elseif($g['kind']==6){
				//echo $g['value']-$before_ost;
				$sign=1;
				if($g['debt_id']==3) $sign=-1.0;
				
				$before_ost=$sign*$g['value'];  //-$before_ost;
			}elseif($g['kind']==4){
				$before_ost-=$g['value'];
				
			}elseif($g['kind']==5){
				//пройдем позиции поступления
				$total_by_pos=0;
				
				$total_by_pos=(float)$g['value'];
				
				
				$before_ost+=$total_by_pos;
				
				
			}elseif($g['kind']==7){
				//пройдем позиции требования
				$total_by_pos=0;
				
				$total_by_pos=(float)$g['value'];
				
				
				$before_ost-=$total_by_pos;
				
				
			}
			
		}
		
		return round($before_ost,2);
	}
	
	
	
	//остаток на дату по договогу
	public function OstBySupContract($supplier_id, $pdate, $org_id, $contract_id, $except_invcalc_id=NULL,  $supplier_or_user=0){
		$_bpm=new BillPosPMFormer;
		
		
		$invcalc_flt='';
		if($except_invcalc_id!==NULL) $invcalc_flt.=' and id<>'.$except_invcalc_id;
		
		  $query_flt=''; $query_flt1='';
				  if($supplier_or_user==0) {
					  $query_flt=' supplier_id="'.$supplier_id.'" ';
					  $query_flt1=' supplier_id="'.$supplier_id.'" ';
				  }else{
					   $query_flt=' inner_user_id="'.$supplier_id.'" ';
					   $query_flt1=' supplier_id="-1" ';
				  }
		
		 //фильтр для исх. оплат
				  //если нет прав на просмотр всех исх оплат - фильтровать по коду А и получателлю
				  //$this->can_view_all_pays
				  $out_pay_flt='';
				  if(!$this->can_view_all_pays){
					  //$out_pay_flt=' and (code_id=59 or inner_user_id='.$this->result['id'].' )';
				  }
		
		
		
		$before_ost=0;
		//нам нужно перебрать поступления, инввзр, и оплаты, входящие в указанный период
			$sql='
			(select a.id, a.pdate, "0" as code, "2" as kind, sum(ap.total) as value, a.given_pdate, a.given_no, "0" as debt_id
			 from acceptance as a
			 left join acceptance_position as ap on a.id=ap.acceptance_id
			where a.is_confirmed=1 and a.is_incoming=1 and a.given_pdate<"'.$pdate.'" and  a.org_id="'.$org_id.'" and a.bill_id in(select id from bill where org_id="'.$org_id.'" and '. $query_flt1.' and contract_id="'.$contract_id.'")
			group by a.id
			)
			UNION ALL
			(select id, pdate, code, "1" as kind, value, given_pdate, given_no, "0" as debt_id from payment
			where is_confirmed=1 and is_incoming=0 and given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '. $query_flt.'  and contract_id="'.$contract_id.'" '.$out_pay_flt.'
			)
			UNION ALL
			(select id, pdate, code, "6" as kind, debt as value, invcalc_pdate as given_pdate, given_no, debt_id
			from invcalc where is_confirmed_inv=1 and invcalc_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '. $query_flt1.' '.$invcalc_flt.' and contract_id="'.$contract_id.'"
			)
			
			UNION ALL
			(select a.id, a.pdate, "0" as code, "5" as kind, sum(ap.total) as value, a.given_pdate, a.given_no, "0" as debt_id
			 from acceptance as a
			 left join acceptance_position as ap on a.id=ap.acceptance_id
			where a.is_confirmed=1 and a.is_incoming=0 and a.given_pdate<"'.$pdate.'" and  a.org_id="'.$org_id.'" and a.bill_id in(select id from bill where org_id="'.$org_id.'" and '. $query_flt1.'  and contract_id="'.$contract_id.'")
			group by a.id
			)
			UNION ALL
			(select id, pdate, code, "4" as kind, value, given_pdate, given_no, "0" as debt_id from payment
			where is_confirmed=1 and is_incoming=1 and given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '. $query_flt.'  and contract_id="'.$contract_id.'"
			)
			
			 UNION ALL
				  (select id, pdate, id as code, "7" as kind, value, given_pdate, given_no, "0" as debt_id from demand
				  where is_confirmed=1 and given_pdate<"'.$pdate.'" and  org_id="'.$org_id.'" and '.$query_flt.' 
				  )
				  
			
			order by 6 asc, 4 asc,  1 asc
			
			';
		
		//echo $sql.'<p>';
		
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();		
		for($i=0;$i<$rc;$i++){	
			$g=mysqli_fetch_array($rs);
			
			
			if($g['kind']==1){
				$before_ost+=$g['value'];
				
			}elseif($g['kind']==2){
				//пройдем позиции поступления
				$total_by_pos=0;
				
				$total_by_pos=(float)$g['value'];
				
				
				$before_ost-=$total_by_pos;
				
				
			}elseif($g['kind']==6){
				//echo $g['value']-$before_ost;
				$sign=1;
				if($g['debt_id']==3) $sign=-1.0;
				
				$before_ost=$sign*$g['value'];  //-$before_ost;
			}elseif($g['kind']==4){
				$before_ost-=$g['value'];
				
			}elseif($g['kind']==7){
				//пройдем позиции требования
				$total_by_pos=0;
				
				$total_by_pos=(float)$g['value'];
				
				
				$before_ost-=$total_by_pos;
				
				
			}
			
		}
		
		return round($before_ost,2);
	}
	
}
?>