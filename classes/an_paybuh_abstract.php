<?
require_once('billpospmformer.php');
require_once('billitem.php');
require_once('acc_group.php');
require_once('supplieritem.php');
require_once('suppliersgroup.php');
require_once('orgitem.php');
require_once('opfitem.php');
require_once('authuser.php');

class AnPayBuhAbstract{
	protected $bydates=array();
	
	public $prefix='_2';
	public $url_prefix='_in';
	protected $is_incoming=1;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	protected $_acc_item;
	protected $_acc_group;
	
	
	
	protected function init(){
		
				
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		$this->_acc_item=new AccItem;
		$this->_acc_group=new AccGroup;
		
		
	}
	
	
	
	
	function __construct(){
		$this->init();	
	}
	

	public function ShowData($supplier_name, $org_id, $pdate1, $pdate2, $only_vyp, $only_not_vyp, $only_not_payed, $only_in_buh, $only_not_in_buh,  $template, DBDecorator $dec,$pagename='files.php',  $do_it=false, $can_print=false, $dec_sep=DEC_SEP,&$alls,  $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $result=NULL, $bills_payed=0, $bills_not_payed=0, $bills_semi_payed=0){
		$_bpm=new BillPosPMFormer;
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		
		
		$pdate2+=24*60*60-1;
		
		$sm=new SmartyAdm;
		$_org=new OrgItem;
		$_opf=new OpfItem;
		//$_bi=new BillItem;
		//$_acg=new AccGroup;
		
		
		$_sg=new SuppliersGroup;
		$sg=$_sg->GetItemsWithOpfArr();
		
		
		$supplier_names=array(); $supplier_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where is_org=0 and is_active=1  and (';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				$_supplier_names[]=' (full_name =  "'.trim($v).'") ';
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
		}
		
		
		
		
		
		
		if(is_array($supplier_id)&&(count($supplier_id)>0)) {
			$supplier_filter=implode(', ',$supplier_id);
			if(strlen($supplier_filter)>0) $supplier_filter=' and b.supplier_id in('.$supplier_filter.') ';
			
		}
		
		
		
		$db_flt='';
		if($only_vyp==1){
			$db_flt.=' and b.status_id=10 ';
		}elseif($only_not_vyp==1){
			$db_flt.=' and b.status_id in(9, 2) ';
		}
		
		if($only_in_buh==1){
			$db_flt.=' and b.is_in_buh=1 ';
		}elseif($only_not_in_buh==1){
			$db_flt.=' and b.is_in_buh=0 ';
		}
		
		
		$period_filter='';
		
		if(($only_not_in_buh==0)&&($only_in_buh==1)) $period_filter=' and (b.in_buh_pdate between "'.$pdate1.'" and "'.$pdate2.'")';
				
		//найти все счета в период по п-ку
		
		$sql='select distinct b.*, 
			
			mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					sp.full_name as supplier_name, opf.name as supplier_opf
		
		from bill as b
		inner join supplier as sp on b.supplier_id=sp.id
			left join opf on sp.opf_id=opf.id
			left join user as mn on mn.id=b.user_in_buh_id

		
		where 
			b.org_id="'.$org_id.'" and
			b.is_confirmed_price=1 and b.is_confirmed_shipping=1
			and b.is_incoming="'.$this->is_incoming.'"
			'.$period_filter.'
			'.$supplier_filter.'
			'.$db_flt.'
		
		';
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt.', b.id asc';
		}else{
			$sql.=' order by b.id asc';
		}
		
		
		$alls=array();
		
		$_docs=array();
		$_suppliers=array();
		
		if($do_it){
		//echo $sql;
		$set=new mysqlSet($sql);//,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$total_payed_buh=0; $total_bills_buh=0;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			
			$sum_by_bill=$this->_item->CalcCost($f['id']);
			$sum_by_payed=$this->_item->CalcPayed($f['id']);
			
			$f['can_in_buh']=$this->_item->CanIsInBuh($f['id'], $rss23, $f, $can_confirm_in_buh, $can_unconfirm_in_buh, $sum_by_bill, $sum_by_payed);
			
			$f['can_in_buh_reason']=$rss23;
		
			$f['in_buh_pdate_unf']=$f['in_buh_pdate'];
			if($f['in_buh_pdate']!=0)
				$f['in_buh_pdate']=date("d.m.Y",$f['in_buh_pdate']);
			else $f['in_buh_pdate']='-';
		
			
			$f['pdate_shipping_plan_unf']=$f['pdate_shipping_plan'];
			if($f['pdate_shipping_plan']!=0)
				$f['pdate_shipping_plan']=date("d.m.Y",$f['pdate_shipping_plan']);
			else $f['pdate_shipping_plan']='-';
			
			$f['pdate_unf']=$f['pdate'];
			$f['pdate_payment_contract_unf']=$f['pdate_payment_contract'];
			if($f['pdate_payment_contract']!=0)
				$f['pdate_payment_contract']=date("d.m.Y",$f['pdate_payment_contract']);
			else $f['pdate_payment_contract']='-';
			
			if($f['supplier_bill_pdate']!=0)
				$f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);	
			else $f['supplier_bill_pdate']='-';
			
			
			
			//оплаты по счету...
			$pays=$this->_item->GetBindedPaymentsFull($f['id']);
			
			
			  /*if($only_not_payed){
				  if(round($sum_by_payed,2)>0) continue;
			  }
			  
			  else{
				  if(round($sum_by_bill,2)<=round($sum_by_payed,2)) continue;	
			  }*/
			
			
			
			
			
			
			$f['sum_by_bill_unf']=$sum_by_bill;
			$f['sum_by_payed_unf']=$sum_by_payed;
			
			//фильтры по оплате
			if(($bills_payed==1)&&($bills_semi_payed==0)){
				//только опл	
				 if(round($sum_by_bill,2)>round($sum_by_payed,2)) continue;	
				
			}
			elseif(($bills_payed==1)&&($bills_semi_payed==1)){
				//опл. + част. опл.
				if(round($sum_by_payed,2)==0) continue;	
			}
			elseif(($bills_not_payed==1)&&($bills_semi_payed==0)){
				//только не опл.	
				if(round($sum_by_payed,2)>0) continue;	
			}
			elseif(($bills_not_payed==1)&&($bills_semi_payed==1)){
				//не опл.+ частично опл.
				//echo ' z ';
				if(round($sum_by_bill,2)<=round($sum_by_payed,2)) continue;	
			}
			elseif(($bills_semi_payed==1)&&($bills_payed==0)&&($bills_not_payed==0)){
				//только частично опл.
				if((round($sum_by_payed,2)==0)||(round($sum_by_bill,2)<=round($sum_by_payed,2))) continue;
			}
			
			
			$f['sum_by_bill']=number_format($sum_by_bill,2,'.',$dec_sep);
			$f['sum_by_payed']=number_format($sum_by_payed,2,'.',$dec_sep);
			
			$total_payed_buh+=$sum_by_payed;
			$total_bills_buh+=$sum_by_bill;
			
			//var_dump($pays);
			foreach($pays as $k=>$v){
				$pays[$k]['value_unf']=$v['value'];	
				$pays[$k]['value']=number_format($v['value'],2,'.',$dec_sep);	
				
				$pays[$k]['given_pdate_unf']=$v['given_pdate'];	
				$pays[$k]['given_pdate']=date('d.m.Y',$v['given_pdate']);
				
				
				
				
			}
			
			$f['pays']=$pays;
			
			if(isset($pays[0]['given_pdate_unf'])) $f['pdate_payment_fact_unf']=$pays[0]['given_pdate_unf'];
			else $f['pdate_payment_fact_unf']=0;
			//$f['pdate_payment_fact_unf']
			
			
			
			
			//поступления по счету...
			
			$sql3='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1 order by given_pdate asc';
			
			$set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
			$rs3=$set3->GetResult();
			$rc3=$set3->GetResultNumRows();
			
			
			$accs=array();
			
			for($j=0; $j<$rc3; $j++){
				$g=mysqli_fetch_array($rs3);
				$g['given_pdate_unf']=$g['given_pdate'];	
				$g['given_pdate']=date('d.m.Y',$g['given_pdate']);
				
				
				
				$accs[]=$g;
			}
			
			
			
			$f['accs']=$accs;
			
			
			if($f['is_in_buh']==1){
				$_docs[]=$f['id'];	
				
				if(!in_array(array('supplier_opf'=>$f['supplier_opf'], 'supplier_name'=>$f['supplier_name']), $_suppliers)){
					$_suppliers[]=array('supplier_opf'=>$f['supplier_opf'], 'supplier_name'=>$f['supplier_name']);
				}
			}
			
			
			
			//$t_arr2[]=$f['id'];
			$alls[]=$f;
		}
		
		
		
		//var_dump($_suppliers);
		
		}
		
		
		
		
		
		
		$sortmode5=0;
		
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//сортировка по нетабличным полям.
		$custom_sort_mode=$sortmode;
		
		if($custom_sort_mode>0){
			switch($custom_sort_mode){
				case 2:
					$alls=$this->SortArr($alls,'sum_by_bill_unf',1);
				break;
				case 3:
					$alls=$this->SortArr($alls,'sum_by_bill_unf',0);
				break;	
				
				
				
			}
				
		}
		
		
		$sm->assign('items',$alls);
	
		$sm->assign('pagename',$pagename);
		
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link).'&doSub'.$this->prefix.'=1';;
		$link=eregi_replace('tab_page'.$this->prefix, 'tab_page', $link);
		$sm->assign('link',$link);
		$sm->assign('sortmode',$sortmode);
		
		
		$sm->assign('prefix',$this->prefix);
		$sm->assign('url_prefix',$this->url_prefix);
		$sm->assign('is_incoming',$this->is_incoming);
		
		
		$sm->assign('count_docs',count($_docs));
		$sm->assign('count_suppliers',count($_suppliers));
		$sm->assign('total_payed_buh', number_format($total_payed_buh,2,'.',$dec_sep));
		$sm->assign('total_bills_buh', number_format($total_bills_buh,2,'.',$dec_sep));
		
		$sm->assign('suppliers',$this->SortArr($_suppliers, 'supplier_name',0));
		
		$sm->assign('pdate',date('d.m.Y'));
		$sm->assign('username',$result['name_s']);
		
		$_org=new OrgItem;
		$org=$_org->getitembyid($org_id);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($org['opf_id']);
		
		$sm->assign('org_name', $opf['name'].' '.$org['full_name']);
		
			
		$sm->assign('can_print',$can_print);
		$sm->assign('do_it',$do_it);
				
			
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