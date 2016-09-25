<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('posonas.php');


class PositionsOnAssortimentMoney extends PositionsOnAssortiment{
	
	public $_su;
	
	function __construct(){
		$this->_su=new SectorToUser;	
	}
	
	public function ShowData($pdate1,$pdate2, $storage_id,$sector_id, $org_id, DBDecorator $dec, $template, $pagename='goods_on_stor.php',$limited_sector=NULL,$can_print=false,&$alls,$_extended_limited_sector=NULL, $do_it=true, $only_active_sectors=1, $only_active_storages=1){
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		//$supplier=$_si->GetItemById($supplier_id);
		$_sg=new StorageGroup;
		$_ssgr=new StorageSector;
		$sm=new SmartyAdm;
		$alls=array();
		
		$_sector=new SectorItem;
		$_storage=new StorageItem;
		
		$pdate1=datefromdmy($pdate1);
		$pdate2=datefromdmy($pdate2)+24*60*60;
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		
		
		//var_dump($_extended_limited_sector);
		
		//программные фильтры
		if($_extended_limited_sector!==NULL){
			$sector_flt.=$this->_su->buildQuery($_extended_limited_sector,'storage_id','sector_id');	
		//	$is_sector_flt.=$this->_su->buildQuery($_extended_limited_sector,'sender_storage_id','sender_sector_id');	
		}
		
		
		//пользовательские фильтры
		
		if(is_array($sector_id)&&(count($sector_id)>0)){
			$sector_flt.=' and sector_id in('.implode(', ',$sector_id).') ';	
		//	$is_sector_flt.=' and sender_sector_id in('.implode(', ',$sector_id).') ';	
		}
		
		
		if(is_array($storage_id)&&(count($storage_id)>0)){
			$storage_flt.=' and storage_id in('.implode(', ',$storage_id).') ';	
		//	$is_storage_flt.=' and (sender_storage_id in('.implode(', ',$storage_id).') ) ';	
		}
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		$multiples=array();
		$keyname='';
		$keyvals=array();
		$titlename='';
		$titlename2='';
		
		if((is_array($sector_id)&&(count($sector_id)>0))&&
		(is_array($storage_id)&&(count($storage_id)==0))
		){
			//echo '<h1>перебор объектов</h1>';
			
			$keyname='storage_id';
			$_bd=new StorageSector();
			$arr=$_bd->GetCategsBookArr($sector_id[0], $only_active_storages);
			$sector=$_sector->GetItemById($sector_id[0]);
			$titlename=' участок '.$sector['name'];
			$titlename2=$titlename;
			if($only_active_storages==1) $titlename.=', все активные объекты, ';
			else $titlename.=', все объекты ';
			
			foreach($arr as $k=>$v){
				$multiples[]=array('someid'=>$v['id'], 'name'=>$v['name'], 'full_name'=>'Объект '.$v['name']);
			}
			
				
		}elseif((is_array($storage_id)&&(count($storage_id)>0))&&
		(is_array($sector_id)&&(count($sector_id)==0))
		){
			//echo '<h1>перебор участков</h1>';
			
			$keyname='sector_id';
			$_bd=new StorageSector();
			$arr=$_bd->GetBookCategsArr($storage_id[0], $only_active_sectors);
			
			
			$storage=$_storage->GetItemById($storage_id[0]);
			$titlename=' объект '.$storage['name'];
			$titlename2=$titlename;
			
			if($only_active_sectors==1) $titlename.=', все активные участки, ';
			else $titlename.=', все участки ';
			
			
			
			foreach($arr as $k=>$v){
				$multiples[]=array('someid'=>$v['id'], 'name'=>$v['name'], 'full_name'=>'Участок '.$v['name']);
			}
				
		}else{
			//echo '<h1>один об-т, один уч-к</h1>';
			
			$sector=$_sector->GetItemById($sector_id[0]);
			$titlename=' участок '.$sector['name'];
			
			$storage=$_storage->GetItemById($storage_id[0]);
			$titlename.=', объект '.$storage['name'].' ';
			
			$titlename2=$titlename;
			
			$multiples[]=array('someid'=>0, 'name'=>'', 'full_name'=>'');
		}
		
		//print_r($multiples);
		
		
		//все товары на конец периода: это все товары, по кот было поступление + вход межсклад
		
		
		
		
		
			
		if($do_it){
			
			$overal_itogo=0;
			$overal_sum=0;
			foreach($multiples as $k=>$v){
			  $mult_filter='';
			  
			  if($keyname=='sector_id'){
					$mult_filter=' and sector_id="'.$v['someid'].'"';  
			  }elseif($keyname=='storage_id'){
				  $mult_filter=' and storage_id="'.$v['someid'].'"';  
			  }
			  
			  $sql='
		  
		  
		  select  p.id, p.*, d.name as dim_name, 
		  		  ap.quantity, ap.total,
				  ap.price_pm, ap.komplekt_ved_id,
				  a.given_no, a.given_pdate, a.id as acceptance_id,
				  
				  sup.full_name as supplier_name, sup.id as supplier_id,
				  opf.name as opf_name
					  
				  from 
					  acceptance_position as ap
					  inner join catalog_position as p on p.id=ap.position_id
					  left join catalog_dimension as d on p.dimension_id=d.id
					  left join acceptance as a on ap.acceptance_id=a.id
					  left join bill as b on a.bill_id=b.id
					  left join supplier as sup on b.supplier_id=sup.id
					  left join opf on sup.opf_id=opf.id
					  
				  where ap.acceptance_id in(select id from acceptance where is_confirmed=1 and org_id="'.$org_id.'" and (given_pdate>="'.$pdate1.'" and given_pdate<="'.$pdate2.'") '.$storage_flt.' '.$sector_flt.' '.$mult_filter.') '.$db_flt.'  
		  
			  ';
				
			 $ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
			
					  
			  //echo $sql.'<p>';	
				
				//echo $sql;
			  $set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  
			  //$_gi=new PosGroupItem;
			  $alls=array(); $itogo=0; $itogo_price=0;
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  
				  //echo 'tovar: '.$f['id'].' ';
				  
				  $f['given_pdate_unf']=$f['given_pdate'];
				  
				  $f['given_pdate']=date('d.m.Y',$f['given_pdate']);
				  
				  $itogo+=$f['quantity'];
				  $itogo_price+=$f['total'];
				  
				 
				 
				  
				  $alls[]=$f;
			  }
			  
			  $multiples[$k]['itogo_kol']=$itogo;
			  $multiples[$k]['itogo_price']=number_format($itogo_price,2,'.',DEC_SEP);
			  $multiples[$k]['items']=$alls;	
			  $overal_itogo+=$itogo;
			  $overal_sum+=$itogo_price;
			}
			
			
			
		  	
		}
		
		$sm->assign('itogo_kol',$overal_itogo);
		$sm->assign('itogo_price',number_format($overal_sum,2,'.',DEC_SEP));
		$sm->assign('titlename',$titlename);
		$sm->assign('titlename2',$titlename2);
		
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
			
			if($v->GetName()=='sector_id4') $current_sector=$v->GetValue();
			//if($v->GetName()=='position_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id4') $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='dimension_id4') $current_dimension_id=$v->GetValue();
			
			
			if($v->GetName()=='sortmode4') $sortmode4=$v->GetValue();
			
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode4=[[:digit:]]+','',$link).'&doSub4=1';
		$sm->assign('link',$link);
		
		
		
		//сортировка по нетабличным полям.
		/*$custom_sort_mode=0;
		if(($sortmode2>0)) $custom_sort_mode=$sortmode2;
		elseif(($sortmode3>0)) $custom_sort_mode=$sortmode3;
		
		if($custom_sort_mode>0){
			switch($custom_sort_mode){
				case 2:
					$alls=$this->SortArr($alls,'sum_by_bill_unf',1);
				break;
				case 3:
					$alls=$this->SortArr($alls,'sum_by_bill_unf',0);
				break;	
				
				case 10:
					$alls=$this->SortArr($alls,'pdate_payment_fact_unf',1);
				break;
				case 11:
					$alls=$this->SortArr($alls,'pdate_payment_fact_unf',0);
				break;	
				
			}
				
		}*/
		
		
		
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
		
		
	
		
		
		//склады
		if($limited_sector===NULL){
			$_storages=new StorageGroup;
			$sgs=$_storages->GetItemsArr(0,$only_active_storages);
		}else{
			$_storages=new StorageSector;
			$sgs=$_storages->GetLimitedStorages($limited_sector);	
		}
		
		$sender_storage_ids=array();
		$sender_storage_names=array();
		$storage_names_selected=array(); //massiv dlya pe4atnoy versii
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
		$sm->assign('storage_html',$storage_html);
		$sm->assign('storage_names',$sender_storage_names);
		$sm->assign('storage_names_selected',$storage_names_selected);
		
			
		
		$sm->assign('storage_id4',$current_storage);
		
		//участки
		$_storages=new SectorGroup;
		$sgs=$_storages->GetItemsArr(0,$only_active_sectors);
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
		
		$sm->assign('sector_id4',$current_sector);
		
		
		$sm->assign('some',$multiples);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('do_it',$do_it);
		
		//var_dump($do_it);
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	
	
	//сортировка выходного массива
	protected function SortArr($arr, $fieldname, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min	
				$a=$this->FindMin($arr, $fieldname, $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}else{
				//max
				$a=$this->FindMax($arr, $fieldname, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	protected function FindMin($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$minval=999999999999999999999999999999999999999;
		foreach($arr as $k=>$v){
			if($v[$fieldname]<$minval){
				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$maxval=-999999999999999999999;
		foreach($arr as $k=>$v){
			
			if($v[$fieldname]>$maxval){
				
				$maxval=$v[$fieldname];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}
}
?>