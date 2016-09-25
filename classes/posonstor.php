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
//require_once('sectortouser.php');

class PositionsOnStorage extends AbstractGroup{
	public $_su;
	
	function __construct(){
		$this->_su=new SectorToUser;	
	}
	
	
	public function ShowData($storage_id, $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$limited_sector,$can_print=false,$_extended_limited_sector=NULL, $do_it=true, $only_active_storages=0, $only_active_sectors=0){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		//$supplier=$_si->GetItemById($supplier_id);
		$_sg=new StorageGroup;
		$_ssgr=new StorageSector;
		$sm=new SmartyAdm;
		$alls=array();
		
		$_secgr=new SectorGroup;
		$sectrs=$_secgr->GetItemsArr(0,$only_active_sectors);
		$sects_count=count($sectrs);
		
		
		$sg=$_sg->GetItemsArr(0,$only_active_storages);
		
		
		if($do_it) foreach($sg as $k=>$v){
			if(count($storage_id)>0){
				
				if(!in_array($v['id'],$storage_id)) continue;
				
				
			}
			
			
			//склады... их мы должны пропустить, если есть расширенный лимитем-сектор
				
			$_e_storages=$this->_su->buildStorages($_extended_limited_sector);
				
			if(($_extended_limited_sector!==NULL)&&!in_array($v['id'],$_e_storages)) continue;
			
			//участки по складам...
			$ssrg=$_ssgr->GetBookCategsArr($v['id'], $only_active_sectors);
			$sectors=array();
			foreach($ssrg as $kk=>$vv){
				//if(($limited_sector!==NULL)&&(!in_array($vv['id'],$limited_sector))) continue;
				if(($_extended_limited_sector!==NULL)&&!$this->_su->IsInPair($_extended_limited_sector,$vv['id'], $v['id'])){
					continue;	
				}
				
				$sectors[]=$vv;
			}
			if(count($sectors)==0) continue;
			
			$sector_span=$sects_count-count($sectors);
			if($sector_span<1) $sector_span=1;
			
			//echo $sector_span;
			
			//позиции на складе
			
			if(($limited_sector!==NULL)) $sec_flt=' and sector_id in('.implode(', ',$limited_sector).')';
			else $sec_flt='';
			
			
			
			
			$sec_flt=$this->_su->buildQueryByStorage($_extended_limited_sector,$v['id'],'sector_id');
			
			$sql='
			
			select distinct t1.position_id, t1.name, t1.dimension 
			from acceptance_position as t1 
			where t1.acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'" and org_id="'.$org_id.'" '.$sec_flt.') 
			
			order by 2 asc
			';
			
			//echo $sql.'<br />';
			
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$positions=array();
			for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $kk=>$vv) $f[$kk]=stripslashes($vv);
			  
			  //найдем f[s_q]
			  //все поступления по товару
			  $f['s_q']=0;
			  if($limited_sector===NULL) $sql0='select sum(quantity) from acceptance_position where position_id="'.$f['position_id'].'" and acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'"   and org_id="'.$org_id.'")';
			  else $sql0='select sum(quantity) from acceptance_position where position_id="'.$f['position_id'].'" and acceptance_id in(select id from acceptance where is_confirmed=1 and storage_id="'.$v['id'].'" and sector_id in('.implode(', ',$this->_su->buildSectorsByStorage($_extended_limited_sector,$v['id'])).') and org_id="'.$org_id.'")';
			  
			 
			 	//echo $sql0.'<br />';
			 
			  $set1=new mysqlSet($sql0);
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  
			   $f['s_q']+=(float)$g[0];
			  
			  
			  //для f[s_q] - вычесть межсклад
			  //получим всего списано по данной позиции
			  if($limited_sector===NULL) $sql0='select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_storage_id="'.$v['id'].'"  and org_id="'.$org_id.'")';
			  else $sql0='select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_storage_id="'.$v['id'].'" and sender_sector_id in('.implode(', ',$this->_su->buildSectorsByStorage($_extended_limited_sector,$v['id'])).') and org_id="'.$org_id.'")';
			  
			  //echo $sql0.'<br />';
			  
			  
			  $set1=new mysqlSet($sql0);
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  
			  //вычтем из склада
			  $f['s_q']-=(float)$g[0];
			  
			  
			 
			  
			  
			  
			  	//перебор по участкам, подсчет числа позиций
			  $pos_by_sectors=array();
			  foreach($sectors as $kk=>$vv){
			  	//сколько данной позиции пошло на данный участок
				//из позиции - поступление
				//из поступления - айди счета - счет
				//из счета - участок
				$sql2='select sum(ap.quantity) as sum_sector from acceptance_position as ap
				 inner join acceptance as a on a.id=ap.acceptance_id
				
				 
				 where ap.position_id="'.$f['position_id'].'" 
				  and a.is_confirmed=1 
				 
				 and a.storage_id="'.$v['id'].'"
				
				 and a.sector_id="'.$vv['id'].'"
				 and a.org_id="'.$org_id.'"
				 
				 group by ap.position_id ';
				 
				/* $sql2='select sum(ap.quantity) as sum_sector from acceptance_position as ap
				  where ap.position_id="'.$f['position_id'].'"
				  and ap.acceptance_id in(
				  select id from acceptance as a
				  where
				  a.storage_id="'.$v['id'].'" and a.is_confirmed=1 and a.org_id="'.$org_id.'"
				 
				 and a.sector_id="'.$vv['id'].'"
				  )';*/
				 
				//echo ' '.$sql2.'<br /> ';
				
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$g=mysqli_fetch_array($rs2);
				
				
				//для позиции на уч-ке - учтем межсклад
				//получим всего списано по данной позиции
				$sql2='select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore  where is_confirmed=1 and sender_storage_id="'.$v['id'].'" and sender_sector_id="'.$vv['id'].'" and org_id="'.$org_id.'")';
				// echo ' '.$sql2.'<br /> ';
				
				$set1=new mysqlSet($sql2);
				
				$rs1=$set1->GetResult();
				
				$h=mysqli_fetch_array($rs1);
				
				//вычтем из склада
				$g[0]-=$h[0];
				
				
				
				
				
				//if($g[0]>0) echo $g[0].' - '.$sql2.'<br>';
				$pos_by_sectors[]=array('sector_id'=>$vv['id'],'sum_sector'=>(float)$g[0]);
			  
			  }
			  $f['pos_by_sectors']=$pos_by_sectors;
			  $f['sector_span']=$sector_span;
			  if(count($sectors)>0){
			  	
				$f['td_width']=80;
				//$f['td_width']=(round(100/count($sectors))).'%';
			  }else $f['td_width']='*';
			  
			  
			  
			  
			  
			  if($f['s_q']<=0) continue;
			  
			  $positions[]=$f;
			}
			$v['positions']=$positions;
			$v['sectors']=$sectors;
			$v['sector_span']=$sector_span;
			
			 if(count($sectors)>0){
			  	
				$v['td_width']=80;
				//$v['td_width']=(round(100/count($sectors))).'%';
			  }else $v['td_width']='*';
			
			//$v[s_q]- itogo
			
			if($rc>0) $alls[]=$v;
		}
		
		
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			//if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			//if($v->GetName()=='position_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//склады
		$_storages=new StorageGroup;
		$sgs=$_storages->GetItemsArr(0,$only_active_storages);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$storage_names_selected=array();
		
		$storage_html='';
		foreach($sgs as $k=>$v){
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];
			if(in_array($v['id'],$storage_id)) $storage_names_selected[]=$v['name'];
			
			$class='';
			if($v['is_active']==0) $class='class="inactive"';
		
		
		
			if(in_array($v['id'],$storage_id)) $storage_html.='<option value="'.$v['id'].'" selected="selected"  '.$class.'>'.$v['name'].'</option>';	
			else $storage_html.='<option value="'.$v['id'].'" '.$class.'>'.$v['name'].'</option>';		
		}
		$sm->assign('storage_ids',$sender_storage_ids);
		$sm->assign('storage_names',$sender_storage_names);
			$sm->assign('storage_html',$storage_html);		
		$sm->assign('storage_names_selected',$storage_names_selected);
		
		$sm->assign('storage_id',$current_storage);
		
		$sm->assign('can_print',$can_print);
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('do_it',$do_it);
		
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	//детализация поступлений позиции на склад
	public function InAccBySec($storage_id,$position_id,$template,$org_id,$is_ajax=true,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$flt='';
		if($_extended_limited_sector!==NULL) $flt.=' and s.sector_id in('.implode(', ',$this->_su->buildSectorsByStorage($_extended_limited_sector,$storage_id)).')';
		
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sec.name as sec_name, sec.id as sector_id,
			sto.name as sto_name, sto.id as storage_id,
		sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name 
		   from 
		acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		inner join storage as sto on sto.id=s.storage_id
		inner join sector as sec on sec.id=s.sector_id
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		where 
		s.is_confirmed=1 and s.storage_id="'.$storage_id.'" and s.org_id="'.$org_id.'" and bp.position_id="'.$position_id.'" '.$flt;
		
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
	
	public function InWfBySec($storage_id,$position_id,$template,$org_id,$is_ajax=true,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
			
		$flt='';
		if($limited_sector!==NULL) $flt.=' and i.sender_sector_id in('.implode(', ',$this->_su->buildSectorsByStorage($_extended_limited_sector,$storage_id)).')';
		
			
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff, i.is_confirmed, i.is_confirmed_wf,
		sec.name as sec_name, sec.id as sector_id,
			sto.name as sto_name, sto.id as storage_id,
			sec_rec.name as sec_rec_name,
			sto_rec.name as sto_rec_name
		 from 
		interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		
		 inner join storage as sto on sto.id=i.sender_storage_id
		 inner join sector as sec on sec.id=i.sender_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 
		 where i.is_confirmed=1 and i.sender_storage_id="'.$storage_id.'" and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" '.$flt;
		
		
		
		
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
	
	public function InIsBySec($storage_id,$position_id,$template,$org_id,$is_ajax=true,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		//неверно, ушло в поступления
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
				
		$flt='';
		if($limited_sector!==NULL) $flt.=' and i.receiver_sector_id in('.implode(', ',$limited_sector).')';
		
		
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff, i.is_confirmed, i.is_confirmed_wf,
		sec.name as sec_name,
		sto.name as sto_name,
			sec_rec.name as sec_rec_name,
			sto_rec.name as sto_rec_name
		 from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		
		 inner join storage as sto on sto.id=i.sender_storage_id
		 inner join sector as sec on sec.id=i.sender_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 
		 where i.is_confirmed=1 and i.receiver_storage_id="'.$storage_id.'" and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.is_or_writeoff="0" '.$flt;
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
	
	
	//детализация поступлений позиции на участок склада
	public function InAccBySecPos($storage_id,$sector_id,$position_id,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, sp.quantity,
		sec.name as sec_name,
		sto.name as sto_name,
		sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name 
		   from acceptance as s
			 inner join acceptance_position as sp on s.id=sp.acceptance_id 
			 inner join bill as b on s.bill_id=b.id 
			 inner join storage as sto on sto.id=s.storage_id
		inner join sector as sec on sec.id=s.sector_id
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
			 where 
		s.is_confirmed=1 and s.storage_id="'.$storage_id.'" and s.org_id="'.$org_id.'" and sp.position_id="'.$position_id.'"
			and s.sector_id="'.$sector_id.'" and b.org_id="'.$org_id.'"
		';
		
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
	
	public function InWfBySecPos($storage_id,$sector_id,$position_id,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$sql='select distinct i.id, i.pdate, ip.quantity , i.is_or_writeoff, i.is_confirmed, i.is_confirmed_wf,
		sec.name as sec_name,
		sto.name as sto_name,
			sec_rec.name as sec_rec_name,
			sto_rec.name as sto_rec_name
		
		from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		inner join storage as sto on sto.id=i.sender_storage_id
		 inner join sector as sec on sec.id=i.sender_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 
		 where i.is_confirmed=1 and i.sender_storage_id="'.$storage_id.'" and i.sender_sector_id="'.$sector_id.'" and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" ';
		
		
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
	
	public function InIsBySecPos($storage_id,$sector_id,$position_id,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		//неверно, ушло в поступления
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff, i.is_confirmed, i.is_confirmed_wf,
		sec.name as sec_name,
		sto.name as sto_name,
			sec_rec.name as sec_rec_name,
			sto_rec.name as sto_rec_name
			 from interstore as i
		inner join interstore_position as ip on ip.interstore_id=i.id
		inner join storage as sto on sto.id=i.sender_storage_id
		 inner join sector as sec on sec.id=i.sender_sector_id
		 left join storage as sto_rec on sto_rec.id=i.receiver_storage_id
		 left join sector as sec_rec on sec_rec.id=i.receiver_sector_id
		 where i.is_confirmed=1 and i.receiver_storage_id="'.$storage_id.'" and i.receiver_sector_id="'.$sector_id.'" and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.is_or_writeoff="0" ';
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

}
?>