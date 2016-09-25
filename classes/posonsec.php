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
//require_once('sectortouser.php');

class PositionsOnSector extends PositionsOnStorage{
	
	public $_su;
	
	function __construct(){
		$this->_su=new SectorToUser;	
	}
	
	
	public function ShowData($sector_id, $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$limited_sector,$can_print=false,$_extended_limited_sector=NULL, $do_it=true,$only_active_storages=0,$only_active_sectors=0){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
		$_sg=new SectorGroup;// StorageGroup;
		$_ssgr=new StorageSector;
		$sm=new SmartyAdm;
		$alls=array();
		
		$_secgr=new SectorGroup;
		$sectrs=$_secgr->GetItemsArr(0,$only_active_sectors);
		$sects_count=count($sectrs);
		
		$sg=$_sg->GetItemsArr(0,$only_active_sectors);
		
		if($do_it) foreach($sg as $k=>$v){
			if(($_extended_limited_sector!==NULL)&&(!in_array($v['id'],$_extended_limited_sector['sector_ids']))) continue;
			
			if(count($sector_id)>0){
				
				if(!in_array($v['id'],$sector_id)) continue;
			}
			
			
			
			
			//склады по участкам
			$ssrg=$_ssgr->GetCategsBookArr($v['id'],$only_active_storages);
			$sectors=array();
			foreach($ssrg as $kk=>$vv){
				//склады только своего участка, или выбранный склад по правам...
				if(($_extended_limited_sector!==NULL)&&!$this->_su->IsInPair($_extended_limited_sector,$v['id'],$vv['id'])) continue; 
				//&&!in_array($vv['id'],$this->_su->buildStoragesBySector($_extended_limited_sector,$v['id']))) continue;
				$sectors[]=$vv;
			}
			
			$sector_span=$sects_count-count($sectors);
			if($sector_span<1) $sector_span=1;
			
			//echo $sector_span;
			
			
			
			$sto_flt=$this->_su->buildQueryBySector($_extended_limited_sector,$v['id'],'storage_id');
			
			//позиции на складе
			$sql='
			
			select distinct t1.position_id, t1.name, t1.dimension 
			from acceptance_position as t1 
			where t1.acceptance_id in(select id from acceptance where is_confirmed=1 and sector_id="'.$v['id'].'" '.$sto_flt.' and org_id="'.$org_id.'") 
			
			
			order by 2 asc
			';
			
			//echo '<br />'.$sql.'<br />';
			
			$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$positions=array();
			for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  foreach($f as $kk=>$vv) $f[$kk]=stripslashes($vv);
			  
			 //if($v['name']=='Центральный склад') echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
			  
			  //найдем f[s_q]
			  //все поступления по товару
			  $f['s_q']=0;
			  $sql1='select sum(quantity) from acceptance_position where position_id="'.$f['position_id'].'" and acceptance_id in(select id from acceptance where is_confirmed=1 and sector_id="'.$v['id'].'"  '.$this->_su->buildQueryBySector($_extended_limited_sector,$v['id'],'storage_id').'  and org_id="'.$org_id.'")';
			  //echo $sql1.'<br />';
			  $set1=new mysqlSet($sql1);
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  
			   $f['s_q']+=(float)$g[0];
			  
			  
			  //для f[s_q] - вычесть межсклад
			  //получим всего списано по данной позиции
			  $sql1='select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_sector_id="'.$v['id'].'" '.$this->_su->buildQueryBySector($_extended_limited_sector,$v['id'],'sender_storage_id').'  and org_id="'.$org_id.'")';
			   //echo $sql1.'<br />';
			  $set1=new mysqlSet($sql1);
			  
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
				 and a.sector_id="'.$v['id'].'" and a.is_confirmed=1 and a.org_id="'.$org_id.'"
				
				 and a.storage_id="'.$vv['id'].'"
				 group by ap.position_id ';
				 
				/* $sql2='select sum(ap.quantity) as sum_sector from acceptance_position as ap
				  where ap.position_id="'.$f['position_id'].'"
				  and ap.acceptance_id in(
				  select id from acceptance as a
				  where
				  a.is_confirmed=1 and
				  a.storage_id="'.$vv['id'].'" 
				 
				 and a.sector_id="'.$v['id'].'"
				 and a.org_id="'.$org_id.'"
				  )';*/
				 
				// echo ' '.$sql2.'<br /> ';
				$set2=new mysqlSet($sql2);//,$to_page, $from,$sql_count);
				$rs2=$set2->GetResult();
				$g=mysqli_fetch_array($rs2);
				
				
				//для позиции на уч-ке - учтем межсклад
				//получим всего списано по данной позиции
				$sql2='select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore use index (rep4) where is_confirmed=1 and sender_sector_id="'.$v['id'].'" and sender_storage_id="'.$vv['id'].'" and org_id="'.$org_id.'")';
				
				
				$set1=new mysqlSet($sql2);
				
				$rs1=$set1->GetResult();
				
				$h=mysqli_fetch_array($rs1);
				//if($h[0]>0) echo ' '.$sql2.'<br /> ';
				//вычтем из склада
				$g[0]-=$h[0];
				
				
				
				
				//if($g[0]>0) echo $g[0].' - '.$sql2.'<br>';
				$pos_by_sectors[]=array('sector_id'=>$vv['id'],'sum_sector'=>(float)$g[0]);
			  
			  }
			  $f['pos_by_sectors']=$pos_by_sectors;
			  $f['sector_span']=$sector_span;
			  if(count($sectors)>0){
			  	
				$f['td_width']='80';
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
			if($v->GetName()=='sector_id3') $current_sector=$v->GetValue();
			
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$_storages=new SectorGroup;
		$sgs=$_storages->GetItemsArr(0,$only_active_sectors);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$sector_names_selected=array();
		
		$storage_html='';
		foreach($sgs as $k=>$v){
			if(($limited_sector!==NULL)&&(!in_array($v['id'],$limited_sector))) continue;
			
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
			if(in_array($v['id'],$sector_id)) $sector_names_selected[]=$v['name'];
			
			$class='';
			if($v['is_active']==0) $class='class="inactive"';
		
		
		
			if(in_array($v['id'],$sector_id)) $storage_html.='<option value="'.$v['id'].'" selected="selected"  '.$class.'>'.$v['name'].'</option>';	
			else $storage_html.='<option value="'.$v['id'].'" '.$class.'>'.$v['name'].'</option>';		
		}
		$sm->assign('sector_ids',$sender_storage_ids);
		$sm->assign('sector_names',$sender_storage_names);
		$sm->assign('sector_html',$storage_html);		
		$sm->assign('sector_names_selected',$sector_names_selected);
		//print_r($sector_names_selected);
		//$sm->assign('sector_id3',$current_sector);
		
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);		
		
		
		$sm->assign('do_it',$do_it);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	//детализация поступлений позиции на склад
	public function InAccBySec($storage_id,$position_id,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$flt='';		
		if($_extended_limited_sector!==NULL) $flt.=' and s.storage_id in('.implode(', ',$this->_su->buildStoragesBySector($_extended_limited_sector,$storage_id)).')';
		
		
		$sql='select distinct s.id, s.pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
		sec.name as sec_name,
		sto.name as sto_name,
		sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name 
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
		inner join storage as sto on s.storage_id=sto.id
		inner join sector as sec on s.sector_id=sec.id
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		where 
		s.is_confirmed=1 and s.sector_id="'.$storage_id.'" and s.org_id="'.$org_id.'" and bp.position_id="'.$position_id.'" '.$flt;
		
		//var_dump($_extended_limited_sector);
		//echo $flt;
		//echo $storage_id;
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
	
	public function InWfBySec($storage_id,$position_id,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
			
		$flt='';	
		if($_extended_limited_sector!==NULL) $flt.=' and i.sender_storage_id in('.implode(', ',$this->_su->buildStoragesBySector($_extended_limited_sector,$storage_id)).')';
			
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
		 where i.is_confirmed=1 and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" '.$flt;
		
		
		
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
	
	public function InIsBySec($storage_id,$position_id,$template,$org_id,$is_ajax=true,$_extended_limited_sector=NULL){ 
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
		 where i.is_confirmed=1 and i.receiver_sector_id="'.$storage_id.'" and ip.position_id="'.$position_id.'" and i.org_id="'.$org_id.'" and i.is_or_writeoff="0" ';
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