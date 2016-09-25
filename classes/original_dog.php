<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('supplieritem.php');

require_once('orgitem.php');
require_once('opfitem.php');
require_once('posonstor.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('acc_item.php');

require_once('supplieritem.php');

require_once('supcontract_group.php');

class OriginalDog{
	
	
	public $prefix='3';
	
	public function ShowData($has_dog3, $has_uch3, $has_dog_in3, $has_no_dog3, $has_no_uch3, $has_no_dog_in3, DBDecorator $dec, $template, $pagename='original.php',$can_print=false,$do_show_data=true, $limited_supplier=NULL){
		
			
		$_si=new SupplierItem;
		$sm=new SmartyAdm;
		$alls=array();
		
		$_scg=new SupContractGroup;
	
		
		$was_suppliers_arr=array();
		$count_of_docs=0;
		$count_of_accs=0;
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$mode_flt='';
		
		
		
		
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.='  p.id in ('.implode(', ',$limited_supplier).')';
			
		}
		
		
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		
			$sql='select p.*,
					spo.name as opf_name
					
				from supplier as p 
					left join opf as spo on spo.id=p.opf_id
					where true
					';
					
			$sql.=' '.$db_flt.' '.$mode_flt.' ';
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzz';
		
		
		if($do_show_data){
		
		//echo $sql;
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
	
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		    
			$to_continue=true;
			
			//проверка наличия исход договоров
			if($f['is_customer']==1){
				if($has_no_dog3==1){
					$dogs=$_scg->GetItemsByIdArr($f['id'],0,0);	
					
					//echo 'zozozoz';
				//var_dump($dogs);
					$total_dogs_count=count($dogs);
					$count_original_dogs=0;
					foreach($dogs as $k=>$v){
						if($v['has_dog']==1){
							$count_original_dogs++;
						}
					}
					
					
					if($total_dogs_count==0){
						//echo 'zozozoz';
						$f['has_dog']=0;
						if($has_no_dog3==1) $count_of_docs++;	
					}elseif($count_original_dogs<$total_dogs_count){
						$f['has_dog']=0;
						if($has_no_dog3==1) $count_of_docs=$count_of_docs+($total_dogs_count-$count_original_dogs);
						//echo $total_dogs_count-$count_original_dogs;	
					}else{ 
						$f['has_dog']=1; 
					}
					
					if(($f['has_dog']==0)&&($has_no_dog3==1)) $to_continue=$to_continue&&false;
					//echo $f['has_dog'];
				}else $f['has_dog']=1;
			}
			
			
			//проверка наличия вход договоров
			if($f['is_supplier']==1){
				if($has_no_dog_in3==1){
					$dogs=$_scg->GetItemsByIdArr($f['id'],0,1);	
					
					//echo 'zozozoz';
				//if($f['id']==46) var_dump($dogs);
					$total_dogs_count=count($dogs);
					$count_original_dogs=0;
					foreach($dogs as $k=>$v){
						if($v['has_dog']==1){
							$count_original_dogs++;
						}
					}
					
					
					if($total_dogs_count==0){
						//echo 'zozozoz';
						$f['has_dog_in']=0;
						if($has_no_dog_in3==1) $count_of_docs++;	
					}elseif($count_original_dogs<$total_dogs_count){
						$f['has_dog_in']=0;
						if($has_no_dog_in3==1) $count_of_docs=$count_of_docs+($total_dogs_count-$count_original_dogs);
						//if($f['id']==46) echo $count_original_dogs;	
					}else{
						$f['has_dog_in']=1;
					}
					if(($f['has_dog_in']==0)&&($has_no_dog_in3==1))  $to_continue=$to_continue&&false;
				}else $f['has_dog_in']=1;
			}
			
			
		
		 
			 
			
			if(($has_no_uch3==1)&&($f['has_uch']==0)){
				$count_of_docs++;
				$to_continue=$to_continue&&false;	
			}elseif($has_no_uch3==0) $f['has_uch']=1;
			
			if($to_continue) continue;
			
			if(!in_array($f['id'], $was_suppliers_arr)){
				 $was_suppliers_arr[]=$f['id'];
			}
			$count_of_accs++;
			
			
			//echo "$f[full_name] -> $count_of_docs <br>";
			
			
			$alls[]=$f;
		}
				
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
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
		if($v->GetName()=='mode3') $current_mode=$v->GetValue();
			
			if($v->GetName()=='sortmode3') $sortmode=$v->GetValue();
			if($v->GetName()=='supplier_name') $supplier_name=$v->GetValue();
			
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
		
		 
		
		
		$sm->assign('current_mode3',$current_mode);
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode3',$sortmode);
		
		$sm->assign('do_it',$do_show_data);
		
		$sm->assign('count_of_suppliers',count($was_suppliers_arr));
		$sm->assign('count_of_docs',$count_of_docs);
		
		$sm->assign('prefix',$this->prefix);
		
		/*echo '<pre>';
		print_r($alls);
		echo '</pre>';*/
		return $sm->fetch($template);
	}
	
	
	
}
?>