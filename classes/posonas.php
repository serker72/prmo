<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');
/*require_once('storageitem.php');
require_once('storagegroup.php');
require_once('sectoritem.php');
require_once('storagegroup.php');
require_once('sectorgroup.php');
require_once('storagesector.php');*/
require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');

class PositionsOnAssortiment extends PositionsOnStorage{
	
	public function ShowData($pdate1,$pdate2, $storage_id,$sector_id, $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$limited_sector=NULL,$can_print=false){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
		$_sg=new StorageGroup;
		$_ssgr=new StorageSector;
		$sm=new SmartyAdm;
		$alls=array();
		
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		if($limited_sector!==NULL){
			$sector_flt=' and sector_id in('.implode(', ',$limited_sector).')';
			$is_sector_flt=' and sender_sector_id in('.implode(', ',$limited_sector).')';
		}else{
			if(is_array($sector_id)&&(count($sector_id)>0)){
			$sector_flt=' and sector_id in('.implode(', ',$sector_id).') ';	
			$is_sector_flt=' and sender_sector_id in('.implode(', ',$sector_id).') ';	
		}
			
		}
		if(is_array($storage_id)&&(count($storage_id)>0)){
			$storage_flt=' and storage_id in('.implode(', ',$storage_id).') ';	
			$is_storage_flt=' and sender_storage_id in('.implode(', ',$storage_id).') ';	
		}
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		//все товары на конец периода: это все товары, по кот было поступление + вход межсклад
		$sql='
		
		
		select distinct p.id, p.*, d.name as dim_name
					
				from 
					acceptance_position as ap
					inner join catalog_position as p on p.id=ap.position_id
					left join catalog_dimension as d on p.dimension_id=d.id
					
			    where ap.acceptance_id in(select id from acceptance where is_confirmed=1 and org_id="'.$org_id.'" and(pdate<="'.$pdate2.'") '.$storage_flt.' '.$sector_flt.') '.$db_flt.'  order by name asc
		
			';
			
		
			
		//echo $sql;
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_gi=new PosGroupItem;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		    
			//echo 'tovar: '.$f['id'].' ';
			
			
			
			$gi=$_gi->GetItemById($f['group_id']);
			if($gi['parent_group_id']>0){
				$gi2=$_gi->GetItemById($gi['parent_group_id']);	
				if($gi2['parent_group_id']>0){
					$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
					
					$f['group_name']=stripslashes($gi3['name'].'->'.$gi2['name'].'->'.$gi['name']);
				}else{
					
					$f['group_name']=stripslashes($gi2['name'].'->'.$gi['name']);	
				}
			}else{
				$f['group_name']=stripslashes($gi['name']);	
			}
			
			
			//позиция, для нее найдем все значения
			$f['final_q']=0;
			$final_ost=$f['final_q'];
			
			//найдем $f['final_q'] - по поступлениям
			$set1=new mysqlSet('select sum(quantity) from acceptance_position where position_id="'.$f['id'].'" and acceptance_id in(select id from acceptance where is_confirmed=1 and pdate<="'.$pdate2.'" and org_id="'.$org_id.'"  '.$storage_flt.' '.$sector_flt.')');
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			
			//echo 'po acc: '.$h['0'].'<br />';
			$final_ost+=(float)$h['0'];
			
			
			
			//остаток на конец периода
			//сколько поступило - final_q
			
			//echo $final_ost;
			
			//получим всего списано по данной позиции
			$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and pdate<="'.$pdate2.'" and org_id="'.$org_id.'"   '.$is_storage_flt.' '.$is_sector_flt.')');
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			
			//echo 'spisali: '.$h['0'].'<br />';
			$final_ost-=(float)$h['0'];
			//echo '-'.$h[0];
			

			
			
			
			//остаток на начало периода
			$begin_ost=0;
			$sql1='select sum(ap.quantity) as s_q
				from 
					acceptance_position as ap
			    where ap.position_id="'.$f['id'].'" and ap.acceptance_id in(select id from acceptance where is_confirmed=1 and org_id="'.$org_id.'" and pdate<"'.$pdate1.'"  '.$storage_flt.' '.$sector_flt.') group by ap.position_id
			';
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			$begin_ost+=(float)$h['0'];
			
			//получим всего списано по данной позиции
			$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and pdate<"'.$pdate1.'" and org_id="'.$org_id.'"  '.$is_storage_flt.' '.$is_sector_flt.')');
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			
			$begin_ost-=(float)$h['0'];
			
			
			
			
			//итого пришло - поступления за период
			$sql1='select  sum(ap.quantity) as final_q
				from 
					acceptance_position as ap
					
					
			    where ap.position_id="'.$f['id'].'" and ap.acceptance_id in(select id from acceptance where is_confirmed=1 and org_id="'.$org_id.'" and( pdate>="'.$pdate1.'" and pdate<="'.$pdate2.'")  '.$storage_flt.' '.$sector_flt.') group by ap.position_id order by name asc
			';
			
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			$prihod=(float)$h[0];
			
			
			//итого ушло - списания за период
			$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and (pdate>="'.$pdate1.'" and pdate<="'.$pdate2.'") and org_id="'.$org_id.'"  '.$is_storage_flt.' '.$is_sector_flt.')');
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			$rashod=(float)$h[0];
			
			
			$f['prihod']=$prihod;
			$f['rashod']=$rashod;
			$f['begin_ost']=$begin_ost;
			$f['final_ost']=$final_ost;
			$alls[]=$f;
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
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sector_id2') $current_sector=$v->GetValue();
			//if($v->GetName()=='position_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id2') $current_storage=$v->GetValue();
			
			
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
		
		
		//склады
		$_storages=new StorageGroup;
		$sgs=$_storages->GetItemsArr(0,1);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$storage_names_selected=array(); //massiv dlya pe4atnoy versii
		foreach($sgs as $k=>$v){
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
			if(in_array($v['id'],$storage_id)) $storage_names_selected[]=$v['name'];
		}
		$sm->assign('storage_ids',$sender_storage_ids);
		$sm->assign('storage_names',$sender_storage_names);
		$sm->assign('storage_names_selected',$storage_names_selected);
		
		
		$sm->assign('storage_id2',$current_storage);
		
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
		
		$sm->assign('sector_id2',$current_sector);
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
	//детализация по позиции за период
	public function ShowPosByDate($position_id, $pdate1, $pdate2, $org_id, $template, $pagename='goods_on_stor.php',$sector_id,$storage_id,$limited_sector=NULL){
		//ДОДЕЛАТЬ ПОЗЖЕ!!!!!!!!!!!!!!!!!!!
		
		
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		
		$_sg=new StorageGroup;
		$_ssgr=new StorageSector;
		$sm=new SmartyAj;
		$alls=array();
		
		$_secgr=new SectorGroup;
		$sectrs=$_secgr->GetItemsArr(0,1);
		$sects_count=count($sectrs);
		
		$sg=$_sg->GetItemsArr(0,1);
		
		
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		$sec_flt='';
			$is_sec_flt='';
		if($limited_sector!==NULL){
			$sec_flt=' and a.sector_id in('.implode(', ',$limited_sector).')';
			$is_sec_flt=' and (i.sender_sector_id in('.implode(', ',$limited_sector).') or i.receiver_sector_id in('.implode(', ',$limited_sector).'))';	
		}
		
		//все склады, по которым проводились операции с позицией по орг за период
		
		$sql0='
		(select distinct s.id, s.name from storage as s
			inner join acceptance as a on a.storage_id=s.id
			inner join acceptance_position as ap on ap.acceptance_id=a.id
		where 
			a.is_confirmed=1 and a.org_id="'.$org_id.'" and a.pdate<="'.($pdate2).'" and ap.position_id="'.$position_id.'" '.$sec_flt.'
		)
		union(
		select distinct s.id, s.name from storage as s
		inner join interstore as i on (i.sender_storage_id=s.id )
			inner join interstore_position as ip on ip.interstore_id=i.id
		where i.is_confirmed=1 and i.org_id="'.$org_id.'" and i.pdate<="'.($pdate2).'" and ip.position_id="'.$position_id.'" '.$is_sec_flt.'
		)
		order by 2 asc
		';
			 
		//echo $sql0;
		$set0=new mysqlSet($sql0);//,$to_page, $from,$sql_count);
		$rs0=$set0->GetResult();
		$rc0=$set0->GetResultNumRows();
		
		//echo $rc0;
		
		for($ii=0;$ii<$rc0;$ii++){		
		//foreach($sg as $k=>$v){
			$v=mysqli_fetch_array($rs0);
			
			
			//участки по складам...
			$ssrg=$_ssgr->GetBookCategsArr($v['id'],1);
			$sectors=array();
			foreach($ssrg as $kk=>$vv){
				if($limited_sector!==NULL){
					if(!in_array($vv['id'],$limited_sector)) continue;
				}
				$sectors[]=$vv;
			}
			
			if(count($sectors)==0) continue;
			
			$sector_span=$sects_count-count($sectors);
			if($sector_span<1) $sector_span=1;
			
			//echo $sector_span;
			
			//данная позиция на складах
			$sql='
			
			select distinct t1.position_id, t1.name, t1.dimension 
			from acceptance_position as t1 
			where t1.acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'" and org_id="'.$org_id.'" and pdate<="'.$pdate2.'") 
			and t1.position_id="'.$position_id.'"
			
			
			';
			
			//echo $sql;
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$positions=array();
			//for($i=0; $i<$rc; $i++){
			if($rc>0){
			  $f=mysqli_fetch_array($rs);
			  
			  
			  //остаток по позиции на складе
			  $f['s_q']=0;
			  $final_ost=$f['s_q'];
			  //найдем по поступлениям
			   $set1=new mysqlSet('select sum(quantity) from acceptance_position where position_id="'.$f['position_id'].'" and acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'"  and org_id="'.$org_id.'" and pdate<="'.$pdate2.'")');
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  
			  $final_ost+=$g[0];
			  
			  
			  //для f[s_q] - вычесть межсклад
			  //получим всего списано по данной позиции
			  $set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_storage_id="'.$v['id'].'"  and org_id="'.$org_id.'" and pdate<="'.$pdate2.'")');
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  
			  $final_ost-=$g[0];
			  
			  
			  //добавим кол-во перенесенных на склад позиций
			  /*$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and receiver_storage_id="'.$v['id'].'" and is_or_writeoff="0" and org_id="'.$org_id.'" and pdate<="'.$pdate2.'")');
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  $final_ost+=$g[0];
			*/
			  
			  //начальный вход остаток
			  $begin_ost=0;
			  $sql1='select sum(ap.quantity) as s_q
				  from 
					  acceptance_position as ap
				  where ap.position_id="'.$f['position_id'].'" and ap.acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'" and org_id="'.$org_id.'" and pdate<"'.$pdate1.'") group by ap.position_id
			  ';
			  $set1=new mysqlSet($sql1);
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  $begin_ost+=(float)$h['0'];
			  
			  //получим всего списано по данной позиции
			  $set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and pdate<"'.$pdate1.'" and org_id="'.$org_id.'" and sender_storage_id="'.$v['id'].'")');
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  
			  $begin_ost-=(float)$h['0'];
			  
			  //сколько пришло по межскладу
			  /*$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and receiver_storage_id="'.$v['id'].'" and is_or_writeoff="0" and org_id="'.$org_id.'" and pdate<"'.$pdate1.'")');
			  
			  $rs1=$set1->GetResult();
			  
			  $h=mysqli_fetch_array($rs1);
			  $begin_ost+=(float)$h[0];
			  */
			  
			  
			  
			  //итого пришло - поступления + межсклад за период
			  $prihod=0;
			  $sql1='select  sum(ap.quantity) as final_q
				  from 
					  acceptance_position as ap
					  
					  
				  where ap.position_id="'.$f['position_id'].'" and ap.acceptance_id in(select id from acceptance where is_confirmed=1 and org_id="'.$org_id.'" and( pdate>="'.$pdate1.'" and pdate<="'.$pdate2.'") and storage_id="'.$v['id'].'") group by ap.position_id order by name asc
			  ';
			  
			  $set1=new mysqlSet($sql1);
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  $prihod+=(float)$h[0];
			 
			  //итого пришло - межсклад за период
			 /* $set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and receiver_storage_id="'.$v['id'].'" and is_or_writeoff="0" and org_id="'.$org_id.'")');
			  
			  $rs1=$set1->GetResult();
			  
			  $h=mysqli_fetch_array($rs1);
			  $prihod+=(float)$h[0];
			  */
			  
			  
			  //итого ушло - списания за период
			  $set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and (pdate>="'.$pdate1.'" and pdate<="'.$pdate2.'") and sender_storage_id="'.$v['id'].'" and org_id="'.$org_id.'")');
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  $rashod=(float)$h[0];
			  
			  
			  $f['prihod']=$prihod;
			  $f['rashod']=$rashod;
			  $f['begin_ost']=$begin_ost;
			  $f['final_ost']=$final_ost;
			  
			  
			  
			  
			  
			  foreach($f as $kk=>$vv) $f[$kk]=stripslashes($vv);
			  
			  //перебор по участкам, подсчет числа позиций
			  $pos_by_sectors=array();
			  foreach($sectors as $kk=>$vv){
			  	$prihod=0;
				$rashod=0;
				$begin_ost=0;
				$final_ost=0;
				
				//найдем фиинальный остаток по участку
				$final_ost=0;
			  //найдем по поступлениям
				 $set1=new mysqlSet('select sum(quantity) from acceptance_position where position_id="'.$f['position_id'].'" and acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'" and sector_id="'.$vv['id'].'"  and org_id="'.$org_id.'" and pdate<="'.$pdate2.'")');
				
				$rs1=$set1->GetResult();
				
				$g=mysqli_fetch_array($rs1);
				
				$final_ost+=(float)$g[0];
				
				
				//для f[s_q] - вычесть межсклад
				//получим всего списано по данной позиции
				$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_storage_id="'.$v['id'].'" and sender_sector_id="'.$vv['id'].'"  and org_id="'.$org_id.'" and pdate<="'.$pdate2.'")');
				
				$rs1=$set1->GetResult();
				
				$g=mysqli_fetch_array($rs1);
				
				//вычтем из склада
				$final_ost-=(float)$g[0];
				
				
				//добавим кол-во перенесенных на склад позиций
				/*$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and receiver_storage_id="'.$v['id'].'" and receiver_sector_id="'.$vv['id'].'" and is_or_writeoff="0" and org_id="'.$org_id.'" and pdate<="'.$pdate2.'")');
				
				$rs1=$set1->GetResult();
				
				$g=mysqli_fetch_array($rs1);
				$final_ost+=(float)$g[0];
				*/
				
				//найдем исходный ostatok
				$begin_ost=0;
				$sql1='select sum(ap.quantity) as s_q
					from 
						acceptance_position as ap
					where ap.position_id="'.$f['position_id'].'" and ap.acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'" and sector_id="'.$vv['id'].'" and org_id="'.$org_id.'" and pdate<"'.$pdate1.'") group by ap.position_id
				';
				$set1=new mysqlSet($sql1);
				$rs1=$set1->GetResult();
				$g=mysqli_fetch_array($rs1);
				$begin_ost+=(float)$g['0'];
				
				//получим всего списано по данной позиции
				$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and pdate<"'.$pdate1.'" and org_id="'.$org_id.'" and sender_storage_id="'.$v['id'].'" and sender_sector_id="'.$vv['id'].'")');
				$rs1=$set1->GetResult();
				$g=mysqli_fetch_array($rs1);
				
				$begin_ost-=(float)$g['0'];
				
				//сколько пришло по межскладу
				/*$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and receiver_storage_id="'.$v['id'].'" and receiver_sector_id="'.$vv['id'].'" and is_or_writeoff="0" and org_id="'.$org_id.'" and pdate<"'.$pdate1.'")');
				
				$rs1=$set1->GetResult();
				
				$g=mysqli_fetch_array($rs1);
				$begin_ost+=(float)$g[0];
				*/
				
				//сколько всего пришло
				 //итого пришло - поступления + межсклад за период
				$prihod=0;
				$sql1='select  sum(ap.quantity) as final_q
					from 
						acceptance_position as ap
						
						
					where ap.position_id="'.$f['position_id'].'" and ap.acceptance_id in(select id from acceptance where is_confirmed=1 and org_id="'.$org_id.'" and( pdate>="'.$pdate1.'" and pdate<="'.$pdate2.'") and storage_id="'.$v['id'].'" and sector_id="'.$vv['id'].'") group by ap.position_id order by name asc
				';
				
				$set1=new mysqlSet($sql1);
				$rs1=$set1->GetResult();
				$g=mysqli_fetch_array($rs1);
				$prihod+=(float)$g[0];
				//итого пришло - межсклад за период
				/*$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and receiver_storage_id="'.$v['id'].'" and receiver_sector_id="'.$vv['id'].'" and is_or_writeoff="0" and org_id="'.$org_id.'")');
				
				$rs1=$set1->GetResult();
				
				$g=mysqli_fetch_array($rs1);
				$prihod+=(float)$g[0];
				*/
				
				
				//сколько всего ушло
				$set1=new mysqlSet('select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and (pdate>="'.$pdate1.'" and pdate<="'.$pdate2.'") and sender_storage_id="'.$v['id'].'" and sender_sector_id="'.$vv['id'].'" and org_id="'.$org_id.'")');
				$rs1=$set1->GetResult();
				$g=mysqli_fetch_array($rs1);
				$rashod=(float)$g[0];
				
				$pos_by_sectors[]=array(
									'sector_id'=>$vv['id'],
									'begin_ost'=>$begin_ost,
									'prihod'=>$prihod,
									'rashod'=>$rashod,
									'final_ost'=>$final_ost);
			  
			  }
			 
			  $f['pos_by_sectors']=$pos_by_sectors;
			  $f['sector_span']=$sector_span;
			  if(count($sectors)>0){
			  	$f['td_width']=(round(100/count($sectors))).'%';
			  }else $f['td_width']='*';
			  
			  
			  
			  
			  $positions[]=$f;
			}
			
			
			$v['positions']=$positions;
			$v['sectors']=$sectors;
			$v['sector_span']=$sector_span;
			
			 if(count($sectors)>0){
			  	$v['td_width']=(round(100/count($sectors))).'%';
			  }else $v['td_width']='*';
			
			//$v[s_q]- itogo
			
			//if($rc>0) 
			$alls[]=$v;
		}
		
		
		
		
		
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
	//детализация поступлений по позиции за период
	public function InAccByPos($position_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true,$sector_id,$storage_id,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		
		$sec_flt='';
		$is_sec_flt='';
		if($limited_sector!==NULL){
			$sec_flt=' and s.sector_id in('.implode(', ',$limited_sector).')';
			//$is_sec_flt=' and (i.sender_sector_id="'.$limited_sector.'" or i.receiver_sector_id="'.$limited_sector.'")';	
		}
		
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
			sec.name as sec_name, sec.id as sector_id,
			sto.name as sto_name, sto.id as storage_id,
			sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		inner join storage as sto on s.storage_id=sto.id
		inner join sector as sec on s.sector_id=sec.id
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		where 
		s.is_confirmed=1 and (s.pdate>="'.$pdate1.'" and s.pdate<="'.$pdate2.'") and s.org_id="'.$org_id.'" and bp.position_id="'.$position_id.'" '.$sec_flt;
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			
			
			if((count($storage_id)>0)&&(!in_array($f['storage_id'],$storage_id))) continue;
			if((count($sector_id)>0)&&(!in_array($f['sector_id'],$sector_id))) continue;
			
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	public function InWfByPos($position_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true,$sector_id,$storage_id,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
			
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
			
			
		$sec_flt='';
		$is_sec_flt='';
		if($limited_sector!==NULL){
			//$sec_flt=' and s.sector_id="'.$limited_sector.'"';
			$is_sec_flt=' and (i.sender_sector_id in('.implode(', ',$limited_sector).') or i.receiver_sector_id in('.implode(', ',$limited_sector).'))';	
		}
			
			
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff,
			sec.name as sec_name, sec.id as sender_sector_id,
			sto.name as sto_name, sto.id as sender_storage_id,
			sec_rec.name as sec_rec_name, sec_rec.id as receiver_sector_id,
			sto_rec.name as sto_rec_name, sto_rec.id as receiver_storage_id
			
		 from 
		 interstore as i
		 inner join interstore_position as ip on ip.interstore_id=i.id
		 inner join storage as sto on sto.id=i.sender_storage_id
		 inner join sector as sec on sec.id=i.sender_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 where i.is_confirmed=1 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" '.$is_sec_flt;
		
		
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			
			if((count($storage_id)>0)&&!(in_array($f['sender_storage_id'],$storage_id)/*||in_array($f['receiver_storage_id'],$storage_id)*/)) continue;
			if((count($sector_id)>0)&&!(in_array($f['sender_sector_id'],$sector_id)/*||in_array($f['receiver_sector_id'],$sector_id)*/)) continue;
			
			
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}	
	
	public function InIsByPos($position_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true,$sector_id,$storage_id,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		//неверно, ушло в поступления
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		$sec_flt='';
		$is_sec_flt='';
		if($limited_sector!==NULL){
			//$sec_flt=' and s.sector_id="'.$limited_sector.'"';
			$is_sec_flt=' and i.sender_sector_id in('.implode(', ',$limited_sector).') ';	
		}
		
		
		
		$sql='select distinct i.id, i.pdate, ip.quantity , i.is_or_writeoff,
		sec.name as sec_name, sec.id as sender_sector_id,
			sto.name as sto_name, sto.id as sender_storage_id,
			sec_rec.name as sec_rec_name, sec_rec.id as receiver_sector_id,
			sto_rec.name as sto_rec_name, sto_rec.id as receiver_storage_id
		from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		inner join storage as sto on sto.id=i.receiver_storage_id
		inner join sector as sec on sec.id=i.receiver_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		where i.is_confirmed=1 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.is_or_writeoff="0" '.$is_sec_flt;
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
	//		$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	//детализация по товарам-складам
	public function InAccByPosSto($position_id,$storage_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sec.name as sec_name, sec.id as sector_id,
			sto.name as sto_name, sto.id as storage_id,
		sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name   
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		left join storage as sto on sto.id=s.storage_id
		left join sector as sec on sec.id=s.sector_id
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		where 
		s.is_confirmed=1 and (s.pdate>="'.$pdate1.'" and s.pdate<="'.$pdate2.'") and s.org_id="'.$org_id.'" and bp.position_id="'.$position_id.'" and s.storage_id="'.$storage_id.'"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	public function InWfByPosSto($position_id,$storage_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
			
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
			
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff,
		sec.name as sec_name, sec.id as sector_id,
		sto.name as sto_name, sto.id as storage_id,
		sec_rec.name as sec_rec_name, sec_rec.id as receiver_sector_id,
			sto_rec.name as sto_rec_name, sto_rec.id as receiver_storage_id
		 from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		left join storage as sto on sto.id=i.sender_storage_id
		left join sector as sec on sec.id=i.sender_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 where i.is_confirmed=1 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.sender_storage_id="'.$storage_id.'" ';
		
		
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}	
	
	public function InIsByPosSto($position_id,$storage_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		//неверно, ушло в поступления
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		 where i.is_confirmed=1 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.receiver_storage_id="'.$storage_id.'"';
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
	//		$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	//детализация по товарам-складам-участкам
	public function InAccByPosStoSec($position_id,$storage_id,$sector_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sec.name as sec_name, sec.id as sector_id,
			sto.name as sto_name, sto.id as storage_id,
		sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name
		
		   from acceptance as s 
		   inner join acceptance_position as bp on s.id=bp.acceptance_id 
		   inner join bill as b on s.bill_id=b.id 
		   left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		left join storage as sto on sto.id=s.storage_id
		left join sector as sec on sec.id=s.sector_id
		   where 
		s.is_confirmed=1 and (s.pdate>="'.$pdate1.'" and s.pdate<="'.$pdate2.'") and s.org_id="'.$org_id.'" and bp.position_id="'.$position_id.'" and s.storage_id="'.$storage_id.'" and s.sector_id="'.$sector_id.'"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	public function InWfByPosStoSec($position_id,$storage_id,$sector_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
			
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
			
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff, 
			sec.name as sec_name, sec.id as sector_id,
			sto.name as sto_name, sto.id as storage_id,
			sec_rec.name as sec_rec_name, sec_rec.id as receiver_sector_id,
			sto_rec.name as sto_rec_name, sto_rec.id as receiver_storage_id
		from interstore as i
		  inner join interstore_position as ip on ip.interstore_id=i.id
		  left join storage as sto on sto.id=i.sender_storage_id
		  left join sector as sec on sec.id=i.sender_sector_id
		  left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		  left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 where i.is_confirmed=1 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.sender_storage_id="'.$storage_id.'" and i.sender_sector_id="'.$sector_id.'"';
		
		
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}	
	
	public function InIsByPosStoSec($position_id,$storage_id,$sector_id,$pdate1,$pdate2,$template,$org_id,$is_ajax=true){ 
		//неверно, ушло в поступления
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2);
		
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		 where i.is_confirmed=1 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.is_or_writeoff="0" and i.receiver_storage_id="'.$storage_id.'"  and i.receiver_sector_id="'.$sector_id.'"';
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
	//		$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}

}
?>