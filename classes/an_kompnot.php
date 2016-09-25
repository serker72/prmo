<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('sectoritem.php');
require_once('sectorgroup.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('komplitem.php');
require_once('suppliersgroup.php');

class AnKompnot{
	
	public function ShowData($supplier_name,$sector_id, $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$limited_sector=NULL,$can_print=false,$do_show_data=true, DBDecorator $dec2, $not_fulfiled=true, $pdate1=NULL, $pdate2=NULL, $extended_limited_sector_pairs=NULL, $limited_supplier=NULL, &$alls, $can_excel=false){
		
		$_sg=new SuppliersGroup;
		$sg=$_sg->GetItemsWithOpfArr( true,$org_id);
		
			
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
	 
		$sm=new SmartyAdm;
		$alls=array();
		
		$_ai=new KomplItem;
		
	
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$mode_flt='';
		
		if($limited_sector!==NULL){
			$sector_flt=' and p.sector_id in('.implode(', ',$limited_sector).')';
			
		}else{
			if(is_array($sector_id)&&(count($sector_id)>0)){
			$sector_flt=' and p.sector_id in('.implode(', ',$sector_id).') ';	
		
		}
			
		}
		 
		
		 $supplier_names=array(); $supplier_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where is_org=0 and org_id="'.$org_id.'" and is_active=1  and id in (';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				if(strlen(trim($v))>0){
					//if($similar_firms==0) $_supplier_names[]=' (BINARY full_name ="'.trim($v).'") ';
					//else $_supplier_names[]=' (full_name LIKE "%'.trim($v).'%") ';
					$_supplier_names[]= trim($v);
				}
			}
			
			$sql.= implode(', ',$_supplier_names).') order by full_name desc';
			
			//echo $sql;
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$supplier_id[]=$f['id'];	
			}
		}
		
		
		
		
		
		
		if(is_array($supplier_id)&&(count($supplier_id)>0)) {
			$supplier_filter=implode(', ',$supplier_id);
			if(strlen($supplier_filter)>0) $supplier_filter=' and p.supplier_id in('.$supplier_filter.') ';
			
		}
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.='  p.supplier_id in ('.implode(', ',$limited_supplier).')';
			
		}
		
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if($not_fulfiled){
			$status_filter=' and (p.status_id=2 or p.status_id=12)	';
		}else{
			$status_filter='';	
		}
		
		
		//фильтр секр. участка
		$ss_filter='';
		 
		
		$sql='select p.*,
				 sup.full_name as supplier_name, opf.name as opf_name,
					se.name as sector_name, se.id as sector_id,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					stat.name as status_name
				from komplekt_ved as p
				 left join supplier as sup on p.supplier_id=sup.id 
					left join opf on opf.id=sup.opf_id
					left join sector as se on p.sector_id=se.id
					left join user as mn on p.manager_id=mn.id
					left join document_status as stat on p.status_id=stat.id
					
				where p.org_id="'.$org_id.'"
					';
		
		$sql.=' '.$status_filter.'  '.$storage_flt.' '.$sector_flt.' '.$db_flt.'  '.$ss_filter.' '.$supplier_filter;
		
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		
		
		
		if($do_show_data){
		
		//echo $sql;
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
	
		
		if($do_show_data) for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		    
			
			$f['begin_pdate']=date("d.m.Y",$f['begin_pdate']);
			$f['end_pdate']=date("d.m.Y",$f['end_pdate']);
			
			if($f['pdate']==0) $f['pdate']='-';
			else $f['pdate']=date("d.m.Y",$f['pdate']);
			
			
			
			
			
				
			//ввести фильтр по позициям...
		
			$positions=$_ai->GetPositionsArr($f['id'],true,$dec2);
			
			//$f['in_pol']=$mf->InAcc($id,$f['position_id']);
			$rep_positions=array();
			foreach($positions as $k=>$v){
				if($not_fulfiled){
						//добавить только те позиции, которые не завезли
						//если вдруг завезли все позиции - выкинуть заявку из списка
					if(((float)$v['in_pol_in']!=(float)$v['in_pol'])||((float)$v['quantity_confirmed']!=(float)$v['in_pol'])){
					
					
						$rep_positions[]=$v;	
					}
				}else{
					//завезенные или перезавезенные позиции...
					//применить фильтр по заданной дате поступления
					
					
					
					
					
					
					if((float)$v['quantity_confirmed']<=(float)$v['in_pol']){
						
						$_pos_date_flt='';
						if(($pdate1!==NULL)||($pdate2!==NULL)){
							//проверить поступления по позиции, хотя бы одна из их фактических дат должна укладываться в диапазон
							
							if($pdate1!==NULL){
								$_pos_date_flt.=' and s.given_pdate>="'.$pdate1.'" ';
							}
							if($pdate2!==NULL){
								$_pos_date_flt.=' and s.given_pdate<="'.($pdate2+24*60*60-1).'" ';
							}
							$sql2='select count(*) from acceptance_position as sp
							inner join acceptance as s on sp.acceptance_id=s.id
							where 
							s.is_incoming=1 and
							sp.position_id="'.$v['position_id'].'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$f['id'].'" '.$_pos_date_flt;
							
							//echo $sql2.'<br />';
							$set2=new mysqlSet($sql2);
							$rs2=$set2->GetResult();
							$g=mysqli_fetch_array($rs2);
							if((int)$g[0]==0) continue;
						}
						
						
						//добавить фактическую дату
						$sql2='select s.given_pdate, 
							sup.id as supplier_id, sup.full_name as supplier_name, opf.name as opf_name
							
						 from acceptance_position as sp
							inner join acceptance as s on sp.acceptance_id=s.id
							left join bill as b on b.id=s.bill_id
							left join supplier as sup on sup.id=b.supplier_id
							left join opf on opf.id=sup.opf_id
							
							where
							s.is_incoming=1 and
							 sp.position_id="'.$v['position_id'].'" and s.is_confirmed=1 and sp.komplekt_ved_id="'.$f['id'].'" '.$_pos_date_flt.' order by s.given_pdate desc limit 1';
							
						//echo $sql2.'<br />';
						$set2=new mysqlSet($sql2);
						$rs2=$set2->GetResult();
						$g=mysqli_fetch_array($rs2);
						
						foreach($g as $kkk=>$vvv) $v[$kkk]=$vvv;
						
						if((int)$g['given_pdate']==0) {
							$v['given_pdate']='-';	
						}else{
							$v['given_pdate']=date('d.m.Y',$g['given_pdate']);
						}
						
						$rep_positions[]=$v;	
					}
				}
			}
			
			if(count($rep_positions)==0) continue;
			
			$f['positions']=$rep_positions;
			
			/*echo '<pre>';
			var_dump($rep_positions);
			echo '</pre>';
		
			*/
			
			
			
			
			
			
			$alls[]=$f;
		}
		//var_dump($alls);		
		
		
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		
		$sortmode=0;
		$sortmode2=0;
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			
			if($v->GetName()=='sector_id2') $current_sector=$v->GetValue();
			
			//if($v->GetName()=='position_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			if($v->GetName()=='storage_id2') $current_storage=$v->GetValue();
			
		//	if($v->GetName()=='mode') $current_mode=$v->GetValue();
			
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			if($v->GetName()=='sortmode2') $sortmode2=$v->GetValue();
			
			if($v->GetName()=='supplier_name') $supplier_name=$v->GetValue();
			elseif($v->GetName()=='supplier_name2') $supplier_name=$v->GetValue();
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
			//сформируем список контрагентов для Select2
		//возможно, придется исправить...
		$our_ids=array(); $our_suppliers=array();
		$our_ids=explode(';',$supplier_name); //это коды!!!
		
		$sql='select p.id, p.full_name, p.code, opf.name as opf_name from supplier as p left join opf on opf.id=p.opf_id where p.id in('.implode(', ',$our_ids).')';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['full_name']=$f['code'].' '.$f['full_name'].', '.$f['opf_name'];
			$our_suppliers[]=$f;
		}
		$sm->assign('our_suppliers', $our_suppliers);
		 
		
		//участки
		$_storages=new SectorGroup;
		$sgs=$_storages->GetItemsArr(0,1);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$sector_names_selected=array();
		foreach($sgs as $k=>$v){
			if(($limited_sector!==NULL)&&(!in_array($v['id'],$limited_sector))) continue;
			
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
			if(in_array($v['id'],$sector_id)) $sector_names_selected[]=$v['name'];
		}
		$sm->assign('sector_ids',$sender_storage_ids);
		$sm->assign('sector_names',$sender_storage_names);
		$sm->assign('sector_names_selected',$sector_names_selected);
		
		$sm->assign('sector_id',$current_sector);
		$sm->assign('sector_id2',$current_sector);
		
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_excel', $can_excel);
		
		
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		
		
		$fields=$dec2->GetUris();
		foreach($fields as $k=>$v){
			
		
			if($v->GetName()=='dimension_id2') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group=$v->GetValue();
			if($v->GetName()=='three_group_id') $current_three_group=$v->GetValue();
			
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
		
		
		
			//kontragent
		$supplier_names=array();  $supplier_ids=array();
		foreach($sg as $k=>$v){
			$supplier_ids[]=$v['id'];
			
			$supplier_names[]=$v['opf_name'].' '.$v['full_name'];
			
		
			
			if(in_array($v['id'],$supplier_id)) $supplier_names_selected[]=$v['opf_name'].' '.$v['full_name'];
		}
		
	 
		
		$sm->assign('supplier_names_selected',$supplier_names_selected);
		
	 
		
		
		//ссылка для кнопок сортировки
		/*$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);*/
		
		$link=$dec->GenFltUri();
		if($not_fulfiled) $link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';
		else  $link=$this->pagename.'?'.eregi_replace('&sortmode2=[[:digit:]]+','',$link).'&doSub2=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		$sm->assign('sortmode2',$sortmode);
		
		
		$sm->assign('do_it',$do_show_data);
		
		$sm->assign('not_fulfiled',$not_fulfiled);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
}
?>