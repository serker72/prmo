<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');

require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('posonas.php');

require_once('sectoritem.php');
require_once('sectorgroup.php');


class PositionsOnAssortimentMod extends PositionsOnAssortiment{
	protected static $uslugi;
	
	
	function __construct(){
		if(self::$uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		  self::$uslugi/*$this->uslugi*/=array();
		  self::$uslugi/*$this->uslugi*/[]=SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$uslugi/*$this->uslugi*/)) self::$uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$uslugi/*$this->uslugi*/))  self::$uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  
		}	
	}
	
	public function ShowData($pdate1,$pdate2, $sector_id,   $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$can_print=false,&$alls, $do_it=true, $only_period_2=0){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		$supplier=$_si->GetItemById($supplier_id);
		
		
		//print_r(self::$uslugi);
		
		$sm=new SmartyAdm;
		$alls=array();
		
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2)+24*60*60-1;
		
		$storage_flt='';
		$sector_flt='';
		
		//пользовательские фильтры
		
		if(is_array($sector_id)&&(count($sector_id)>0)){
			$sector_flt.=' and sector_id in('.implode(', ',$sector_id).') ';	
			 
		}
		
		
		$is_storage_flt='';
		$is_sector_flt='';
		 
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		//все товары на конец периода: это все товары, по кот было поступление либо реализация
		$sql='
		
		
		select distinct p.id, p.*, d.name as dim_name,
				ap.position_id 
				from 
					acceptance_position as ap
					inner join catalog_position as p on p.id=ap.position_id
					 
					left join catalog_dimension as d on p.dimension_id=d.id
					
			    where ap.acceptance_id in(select id from acceptance where is_confirmed=1  and org_id="'.$org_id.'" and (given_pdate<="'.$pdate2.'") '.$storage_flt.' '.$sector_flt.') and p.group_id not in('.implode(', ',self::$uslugi).') '.$db_flt.'  order by name asc
		
			';
			
		if($do_it){
			
		  //echo $sql;
		  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  $_gi=new PosGroupItem;
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			// echo 'tovar: '.$f['id'].' ';
			  
			  
			  
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
			  $sql='select sum(quantity) from acceptance_position 
			  where 
			  position_id="'.$f['id'].'" 
			  
			  and acceptance_id in(select id from acceptance where is_incoming=1 and is_confirmed=1 and given_pdate<="'.$pdate2.'" and org_id="'.$org_id.'"  '.$storage_flt.' '.$sector_flt.')';
			  $set1=new mysqlSet($sql);
			  //echo $sql;
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  
			  //echo 'po acc: '.$h['0'].'<br />';
			  $final_ost+=(float)$h['0'];
			  
			  
			  
			  //остаток на конец периода
			  //сколько поступило - final_q
			  
			  //echo $final_ost;
			  
			 
			  
			  //получим сколько реализаций по данной позиции
			  $sql='select sum(quantity) from acceptance_position 
			  where 
			  position_id="'.$f['id'].'" 
			 
			  and acceptance_id in(select id from acceptance where is_incoming=0 and is_confirmed=1 and given_pdate<="'.$pdate2.'" and org_id="'.$org_id.'"  '.$storage_flt.' '.$sector_flt.')';
			  
			  $set1=new mysqlSet($sql);
			  //echo $sql;
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  
			  //echo 'spisali: '.$h['0'].'<br />';
			  $final_ost-=(float)$h['0'];
			  //echo '-'.$h[0];
			  //echo $final_ost;
  
			  
			  
			  
			  //остаток на начало периода
			  $begin_ost=0;
			  $sql1='select sum(ap.quantity) as s_q
				  from 
					  acceptance_position as ap
				  where ap.position_id="'.$f['id'].'" 
				
				  and ap.acceptance_id in(select id from acceptance where is_incoming=1 and is_confirmed=1 and org_id="'.$org_id.'" and given_pdate<"'.$pdate1.'"  '.$storage_flt.' '.$sector_flt.') group by ap.position_id
			  ';
			  $set1=new mysqlSet($sql1);
			  //echo $sql1;
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  $begin_ost+=(float)$h['0'];
			  
		 
			  
			  //получим сколько всего реализаций
			  $sql1='select sum(ap.quantity) as s_q
				  from 
					  acceptance_position as ap
				  where ap.position_id="'.$f['id'].'" 
				
				  and ap.acceptance_id in(select id from acceptance where is_incoming=0 and is_confirmed=1 and org_id="'.$org_id.'" and given_pdate<"'.$pdate1.'"  '.$storage_flt.' '.$sector_flt.') group by ap.position_id 
			  ';
			  
			  $set1=new mysqlSet($sql1);
			  //echo $sql1;
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  
			  $begin_ost-=(float)$h['0'];
			  
			  
			  
			  
			  //итого пришло - поступления за период
			  $sql1='select  sum(ap.quantity) as final_q
				  from 
					  acceptance_position as ap
					  
					  
				  where ap.position_id="'.$f['id'].'" 
				 
				  and ap.acceptance_id in(select id from acceptance where is_incoming=1 and is_confirmed=1 and org_id="'.$org_id.'" and( given_pdate>="'.$pdate1.'" and given_pdate<="'.$pdate2.'")  '.$storage_flt.' '.$sector_flt.') group by ap.position_id order by name asc
			  ';
			  
			 // echo $sql1.date('d.m.Y', $pdate2).'<br>';
			  
			  $set1=new mysqlSet($sql1);
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  $prihod=(float)$h[0];
			  
			//  echo $prihod.'<br>';
			  
			  //итого ушло - реализации за период
			  
			  $sql1='select  sum(ap.quantity) as final_q
				  from 
					  acceptance_position as ap
					  
					  
				  where ap.position_id="'.$f['id'].'" 
				  
				  and ap.acceptance_id in(select id from acceptance where is_incoming=0 and is_confirmed=1 and org_id="'.$org_id.'" and( given_pdate>="'.$pdate1.'" and given_pdate<="'.$pdate2.'")  '.$storage_flt.' '.$sector_flt.') group by ap.position_id order by name asc
			  ';
			  
			  $set1=new mysqlSet($sql1);
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  $rashod=(float)$h[0];
			  
			  
			  $f['prihod']=$prihod;
			  $f['rashod']=$rashod;
			  $f['begin_ost']=$begin_ost;
			  $f['final_ost']=$final_ost;
			  
			  if(($prihod==0)&&($rashod==0)&&($begin_ost==0)&&($final_ost==0)) continue; 
			  
			  
			  if(($only_period_2==1)&&($begin_ost>0)){
				continue;  
			  }
			  $alls[]=$f;
		  }
				
		}
		
		//заполним шаблон полями
		
		$current_supplier='';
		$current_user_confirm_price='';
	
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		$current_dimension_id='';
		$current_sector='';
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		
				if($v->GetName()=='sector_id2') $current_sector=$v->GetValue();
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
		
		
	
		//участки
		$_storages=new SectorGroup;
		$sgs=$_storages->GetItemsArr(0);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$sector_names_selected=array();
		
		$sector_html='';
		foreach($sgs as $k=>$v){
			if(($limited_sector!==NULL)&&(!in_array($v['id'],$limited_sector))) continue;
			
			$sender_storage_ids[]=$v['id'];
			$sender_storage_names[]=$v['name'];	
			if(in_array($v['id'],$sector_id)) $sector_names_selected[]=$v['name'];
			
			
			$class='';
			if($v['is_active']==0) $class='class="inactive"';
		
		
		
			if(in_array($v['id'],$sector_id)) $sector_html.='<option value="'.$v['id'].'" selected="selected"  '.$class.'>'.$v['name'].'</option>';	
			else $sector_html.='<option value="'.$v['id'].'" '.$class.'>'.$v['name'].'</option>';	
		}
		$sm->assign('sector_ids',$sender_storage_ids);
		$sm->assign('sector_html', $sector_html);
		$sm->assign('sector_names',$sender_storage_names);
		$sm->assign('sector_names_selected',$sector_names_selected);
		
		$sm->assign('sector_id2',$current_sector);
		
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('do_it',$do_it);
		
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	//детализация поступлений по позиции за период
	public function InAccByPos($position_id,  $pdate1,$pdate2,$template,$org_id,$is_ajax=true,$sector_id,$storage_id,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
			
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2)+24*60*60-1;
		
		
		$sec_flt=''; $sto_flt='';
		$is_sec_flt='';
		
		
		//echo date('d.m.Y', $pdate2);
		
		$sql='select distinct s.id, s.pdate, s.given_pdate, s.bill_id, b.code, b.pdate as bill_pdate, bp.quantity,
			
			sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
	
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		where 
		s.is_incoming=1 and
		s.is_confirmed=1 and (s.given_pdate between "'.$pdate1.'" and  "'.$pdate2.'") 
		and s.org_id="'.$org_id.'"
		and bp.position_id="'.$position_id.'"
		 
		 '.$sec_flt.' '.$sto_flt;
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			
			
		
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}
	
	
	//детализация реализаций позиции за период
	public function InWfByPos($position_id,  $pdate1,$pdate2,$template,$org_id,$is_ajax=true,$sector_id,$storage_id,$limited_sector=NULL,$_extended_limited_sector=NULL){ 
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
			
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2)+24*60*60-1;
		
			
		$sec_flt='';
		$is_sec_flt=''; $sto_flt=''; $is_sto_flt='';
		
			
		
			
		/*$sql='select distinct i.id, i.pdate, ip.quantity, i.is_or_writeoff, i.is_confirmed, i.is_confirmed_wf
		
			
		 from 
		 interstore as i
		 inner join interstore_position as ip on ip.interstore_id=i.id
		
		 where i.is_confirmed=1 
		 and (i.pdate>="'.$pdate1.'" and i.pdate<="'.$pdate2.'") 
		 and ip.position_id="'.$position_id.'" 
		 and ip.pl_position_id="'.$pl_position_id.'"
		 and i.org_id="'.$org_id.'" '.$is_sec_flt.' '.$is_sto_flt;*/
		 
		$sql='select distinct s.id, s.pdate, s.given_pdate, s.bill_id, b.code, b.pdate as bill_pdate, sum(bp.quantity) as quantity,
			
			sup.full_name as supplier_name, sup.id as supplier_id, supo.name as supplier_opf_name
		from acceptance as s 
		inner join acceptance_position as bp on s.id=bp.acceptance_id 
		inner join bill as b on s.bill_id=b.id 
	
		left join supplier as sup on sup.id=b.supplier_id
		left join opf as supo on supo.id=sup.opf_id
		where 
		s.is_incoming=0 and
		s.is_confirmed=1 and (s.given_pdate>="'.$pdate1.'" and s.given_pdate<="'.$pdate2.'") 
		and s.org_id="'.$org_id.'"
		and bp.position_id="'.$position_id.'"
	 
		 '.$sec_flt.' '.$sto_flt.'
		 group by bp.acceptance_id'
		 ;
		
		
		//echo $sql;
		
		$set=new mysqlset($sql);			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		//	$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			$f['bill_pdate']=date("d.m.Y",$f['bill_pdate']);
			
		
			$arr[]=$f;
		}		
		$sm->assign('items',$arr);
		
		return $sm->fetch($template);
	}	
	
	
		//укороченный метод получения остатка на дату определенной позиции
	public function FindOstByDate($position_id,  $pdate2, $org_id){
			$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$pdate2=datefromdmy($pdate2)+24*60*60;
		
		//var_dump($_extended_limited_sector);
		
		
		
		
		
		 //позиция, для нее найдем все значения
			  $f['final_q']=0;
			  $final_ost=$f['final_q'];
			  
			  //найдем $f['final_q'] - по поступлениям
			  $sql='select sum(quantity) from acceptance_position 
			  where 
			  	position_id="'.$position_id.'" 
				
				and acceptance_id in(
						select id from acceptance where is_incoming=1 and is_confirmed=1 and given_pdate<="'.$pdate2.'" and org_id="'.$org_id.'"  '.$storage_flt.' '.$sector_flt.')';
						
			  $set1=new mysqlSet($sql);
			  //echo $sql;
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  
			  //echo 'po acc: '.$h['0'].'<br />';
			  $final_ost+=(float)$h['0'];
			  
			  
			  
			  //остаток на конец периода
			  //сколько поступило - final_q
			  
			  //echo $final_ost;
			  
			  //получим всего списано по данной позиции
			 
			 
			  
			  //всего реализаций по позиции:
			   $sql='select sum(quantity) from acceptance_position 
			  where 
			  	position_id="'.$position_id.'" 
			
				and acceptance_id in(
						select id from acceptance where is_incoming=0 and is_confirmed=1 and given_pdate<="'.$pdate2.'" and org_id="'.$org_id.'"  '.$storage_flt.' '.$sector_flt.')';
			  
			  
			  
			  $set1=new mysqlSet($sql);
			  //echo $sql;
			  $rs1=$set1->GetResult();
			  $h=mysqli_fetch_array($rs1);
			  
			  //echo 'spisali: '.$h['0'].'<br />';
			  $final_ost-=(float)$h['0'];	
			  
			  return round($final_ost,3);
		
	}
}
?>