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


//оригиналы отгруз док-тов
class OriginalAbstract{

	public $prefix='';
	public $is_incoming=0;
	public $_item;
	
	
	function __construct(){
		$this->init();	
	}
	
	protected function init(){
		$this->_item=new AccItem;
	}

	
	public function ShowData($pdate1,$pdate2,$mode, $org_id, DBDecorator $dec, $template, $pagename='original.php',$can_print=false,$do_show_data=true, $limited_supplier=NULL){
		
			
		$_bpm=new BillPosPMFormer;
		$_si=new SupplierItem;
		//$supplier=$_si->GetItemById($supplier_id);
		
		
		$sm=new SmartyAdm;
		$alls=array();
		
		
		
		
		$was_suppliers_arr=array();
		$count_of_docs=0;
		$count_of_accs=0;
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		$mode_flt='';
		
		
		if(is_array($mode)&&(count($mode)>0)){
			
		
			/*
			1 - нет тов нак
			2 - нет с/ф
			3 - нет акт (проверять, есть ли услуги!!!!)
			*/
			if(in_array(1,$mode)) $mode_flt.=' and p.has_nakl=0 ';
			if(in_array(2,$mode)) $mode_flt.=' and p.has_fakt=0 ';
		}
		
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.='  o.supplier_id in ('.implode(', ',$limited_supplier).')';
			
		}
		
		if(strlen($db_flt)>0){
			$db_flt=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		
		
			$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id, sp.is_upr_nalog,
					spo.name as opf_name,
					
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from acceptance as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					
					left join user as u on p.user_confirm_id=u.id
					left join user as mn on p.manager_id=mn.id
					
					
				where p.is_confirmed=1 and p.is_incoming="'.$this->is_incoming.'" 
					and p.org_id="'.$org_id.'"	
					';
					
			$sql.='  '.$storage_flt.' '.$sector_flt.' '.$db_flt.' '.$mode_flt.' ';
		
			$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		
		
		
		if($do_show_data){
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
	
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
		    
			$f['has_usl']=$this->_item->HasUsl($f['id']);
			$f['has_tov']=$this->_item->HasTov($f['id']);
			
			$to_continue=true; //пропускать документ, т.к. соблюдены все критерии
			
			//единственный критерий - наличие акта
			//сразу пропуск - если а) нет акта, нет услуг - что тогда проверять?
								// б) есть акт, есть услуги - удовлетворяет критерию	
			if(in_array(3, $mode)&&(count($mode)==1)){
				//пропустить док-ты без актов
				
				if(($f['has_akt']==0)&&!$f['has_usl']){
					//var_dump($mode);
					 continue;
					 
				}elseif(($f['has_akt']==1)&&$f['has_usl']){
					continue;
				}
			}
			
			/// проверить, странно работает
			//единственный критерий - наличие накладной
			//сразу пропуск - если а) нет накл, нет товаров - что тогда проверять?
			//					   б) есть накл, есть услуги - удовлетворяет критерию
			if(in_array(1,$mode)&&(count($mode)==1)){
				//пропустить док-ты без накл
				
				if(($f['has_nakl']==0)&&!$f['has_tov']){
					//var_dump($mode);
					 continue;
					 
				}elseif(($f['has_nakl']==1)&&$f['has_tov']){
					continue;
				}
			} 
			
			//единственный критерий - наличие с/ф
			//сразу пропуск - если а) нет с/ф, упр.налог - что тогда проверять?
			//					   б) есть с/ф, не упр. налог. - удовлетворяет критерию
			if($this->is_incoming==1) {
			if(in_array(1,$mode)&&(count($mode)==1)){
				//пропустить док-ты без накл
				
				if(($f['has_fakt']==0)&&($f['is_upr_nalog']==1)){
					//var_dump($mode);
					 continue;
					 
				}elseif(($f['has_fakt']==1)&&($f['is_upr_nalog']==0)){
					continue;
				}
			} 
			}
			
			
			
			if(($f['has_nakl']==0)&&($f['has_tov'])&&in_array(1,$mode)) $to_continue=$to_continue&&false;
			
			if($this->is_incoming==1) if(($f['has_fakt']==0)&&($f['is_upr_nalog']==0)&&in_array(2,$mode)) $to_continue=$to_continue&&false;
			
			 if(($f['has_akt']==0)&&($f['has_usl'])&&in_array(3,$mode)) $to_continue=$to_continue&&false;
			
			//пропускать документ, т.к. соблюдены все критерии
			if($to_continue) continue;
			
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			//print_r($f);	
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			if(!in_array($f['supplier_id'], $was_suppliers_arr)) $was_suppliers_arr[]=$f['supplier_id'];
			$count_of_accs++;
			
			if(($f['has_akt']==0)&&$f['has_usl']){
				$count_of_docs++;	
			}
			if($this->is_incoming==1) if(($f['has_fakt']==0)&&($f['is_upr_nalog']==0)) $count_of_docs++;
			
			//if($f['has_nakl']==0) $count_of_docs++;
			
			if(($f['has_nakl']==0)&&$f['has_tov']){
				$count_of_docs++;	
			}
			
			
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
			
		
			if($v->GetName()=='mode') $current_mode=$v->GetValue();
			
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			
			if($v->GetName()=='supplier_name') $supplier_name=$v->GetValue();
			
			//if($v->GetName()=='user_confirm_price_id') $current_user_confirm_price_id=$v->GetValue();
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
		
		if($this->is_incoming==0){
		
			$mode_ids=array(1, 3);
			$mode_names=array('товарной накладной', 'акта');
		
		}else{
						$mode_ids=array(1,2,3);
			$mode_names=array('товарной накладной','счета-фактуры','акта');

		}
		
		$sm->assign('mode_ids',$mode_ids);
		$sm->assign('mode_names',$mode_names);
		$sm->assign('current_mode',$current_mode);
		
		
		$sm->assign('items',$alls);
		$sm->assign('pagename',$pagename);
		
		$sm->assign('can_print',$can_print);
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		
		$sm->assign('prefix', $this->prefix);
		
		$sm->assign('do_it',$do_show_data);
		
		$sm->assign('count_of_suppliers',count($was_suppliers_arr));
		$sm->assign('count_of_accs',$count_of_accs);
		$sm->assign('count_of_docs',$count_of_docs);
		
		$sm->assign('is_incoming',$this->is_incoming);
		
		
		return $sm->fetch($template);
	}
	
	
	
}
?>