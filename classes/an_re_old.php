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

require_once('payitem.php');
require_once('acc_in_item.php');

require_once('supcontract_item.php');
require_once('supcontract_group.php');


require_once('orgsgroup.php');
require_once('cash_percent_item.php');
require_once('cash_bill_position_group.php');

class AnRe{
	protected $cash_array=array();
	protected static $uslugi;
	/*
	'bill_id'=>
	'kind_id'=>
	'sum'=>
	'was_seen'=>
	*/
	
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
		  //var_dump(self::$uslugi);
		}
	}
	

	public function ShowData($pdate1, $pdate2,  $supplier_name='', $template, DBDecorator $dec,$pagename='files.php', $do_show_data=false, $can_print=false, $dec_sep=DEC_SEP, &$alls){
		
		$alls=array();
		
	 
		$_ai=new AccItem;
		$_ai_in=new AccInItem;
		$_pay=new PayItem;
		
		$_orgs=new OrgsGroup;
		
		$_bpm=new BillPosPMFormer;
		
		$_cpi=new CashPercentItem;
		$_cpg=new CashBillPositionGroup;
		
		
		$total_in=0;
		$total_out=0;
		$total_pm=0;
		$total_to_give_pm=0;
		$total_pribyl=0;
		
		$supplier_names=array(); $supplier_id=array();
		
		$supplier_filter='';
		if(strlen($supplier_name)>0){
			$supplier_names=explode(';',$supplier_name);	
			
			
			$sql='select * from supplier where is_org=0 and is_active=1  and (';
			
			$_supplier_names=array();
			foreach($supplier_names as $k=>$v){
				if(strlen(trim($v))>0){
					if($similar_firms==0) $_supplier_names[]=' (full_name ="'.trim($v).'") ';
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
		}
		
		
		
		
		
		
		if(is_array($supplier_id)&&(count($supplier_id)>0)) {
			$supplier_filter=implode(', ',$supplier_id);
			if(strlen($supplier_filter)>0) $supplier_filter=' and b.supplier_id in('.$supplier_filter.') ';
			
		}
		
		
		
		
		
		$sm=new SmartyAdm;
		
		if($do_show_data){
			
			$db_flt=$dec->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$db_flt=' and '.$db_flt;
			//	$sql_count.=' where '.$db_flt;	
			}
			
			 
			//берем позиции исходящих счетов, по которым есть реализации и поступления (утв.)!!!!
			 
			
			$sql='select bp.*,
				b.id as out_bill_id, b.code as out_bill_code, b.pdate as out_bill_pdate, b.supplier_bill_no,
				
				
				
				bpm.plus_or_minus, bpm.rub_or_percent, bpm.value as pm_value, bpm.discount_value, bpm.discount_rub_or_percent,
				pos.group_id
				
				
			from bill_position as bp
			left join bill_position_pm as bpm on bp.id=bpm.bill_position_id
			inner join bill as b on b.id=bp.bill_id and b.is_incoming=0
			
			left join catalog_position as pos on bp.position_id=pos.id
			
			
			
			
			where 
				b.pdate between "'.$pdate1.'" and "'.$pdate2.'"
				and b.is_confirmed_shipping=1
			
			
				
				
				'.$supplier_filter. '
				'.$db_flt.'
			
		 	
			
				
			order by b.pdate asc, bp.name asc	';
			
			
			
			//echo $sql.'<br>';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			$this->BuildCashArray($pdate1, $pdate2, $supplier_filter, $db_flt);
			
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$f['out_bill_pdate']=date('d.m.Y', $f['out_bill_pdate']);
				
				
				//найти суммы кол-в и расходов по реализации
				$sql1='select sum(ap.quantity) as out_ap_quantity, sum(ap.total) as out_ap_total
					  
				from 
					acceptance_position as ap
					inner join acceptance as a on a.id=ap.acceptance_id
				where
					a.is_incoming=0 and a.is_confirmed=1 and a.bill_id="'.$f['out_bill_id'].'"
					and ap.position_id="'.$f['position_id'].'"
					and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				group by 
					ap.position_id	
					';
				
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$f1=mysqli_fetch_array($rs1);
				
				$f['out_ap_quantity']=(float)$f1['out_ap_quantity'];
				$f['out_ap_total']=(float)$f1['out_ap_total'];
				
				
					
				//найти суммы кол-в и расходов по поступлению
				$sql1='select sum(ap.quantity) as in_ap_quantity, sum(ap.total) as in_ap_total
					  
				from 
					acceptance_position as ap
					inner join acceptance as a on a.id=ap.acceptance_id
				where
					a.is_incoming=1 and a.is_confirmed=1 
					and (ap.out_bill_id="'.$f['out_bill_id'].'" or ap.out_bill_id=0)
					and ap.position_id="'.$f['position_id'].'"
					and (ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'" or ap.komplekt_ved_id=0)
				group by 
					ap.position_id	
					';
				
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				$f1=mysqli_fetch_array($rs1);
				
				$f['in_ap_quantity']=(float)$f1['in_ap_quantity'];
				$f['in_ap_total']=(float)$f1['in_ap_total'];
				
				
				if(!$this->IsUsl($f['group_id'])){
					if(($f['in_ap_quantity']==0)||($f['out_ap_quantity']==0)) continue;
				} else {
					if(($f['in_ap_quantity']==0)&&($f['out_ap_quantity']==0)) continue;
					//есть кто-нибудь один: реализация или поступление
				}
				
				
				$total_in+=$f['in_ap_total'];
				$total_out+=$f['out_ap_total'];
				
				
				
				
				
				
				//развернуть все реализации по позиции
				$sql1='select ap.*,
					a.given_no, a.given_pdate, a.org_id,
					sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id
				from 
					acceptance_position as ap
					inner join acceptance as a on a.id=ap.acceptance_id
					
					left join bill as b on a.bill_id=b.id
					left join supplier as sup on b.supplier_id=sup.id
					left join opf on opf.id=sup.opf_id
				where
					a.is_incoming=0 and a.is_confirmed=1 and a.bill_id="'.$f['out_bill_id'].'"
					and ap.position_id="'.$f['position_id'].'"
					and ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'"
				order by  a.given_pdate	
					';
					
				$outs=array();	
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				for($i1=0; $i1<$rc1; $i1++){
					$f1=mysqli_fetch_array($rs1);
					$f1['given_pdate_unf']=$f1['given_pdate'];
					$f1['given_pdate']=date('d.m.Y', $f1['given_pdate']);
					
					
					$outs[]=$f1;
				}
				
				$f['outs']=$outs;
				
				
				//поля для сотрировки
				if(count($outs)>0){
					 $f['out_supplier_name']=$outs[count($outs)-1]['supplier_name'];
					 $f['out_price_pm']=$outs[count($outs)-1]['price_pm'];
					 $f['out_given_pdate']=$outs[count($outs)-1]['given_pdate_unf'];
					 $f['out_given_no']=$outs[count($outs)-1]['given_no'];
				}else{
					 $f['out_supplier_name']='';
					 $f['out_price_pm']=0;
					 $f['out_given_pdate']=0;
					 $f['out_given_no']='';
					 
				}
				
				//развернуть все поступления
				$sql1='select ap.*,
					a.given_no, a.given_pdate, a.org_id,
					sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id
				from 
					acceptance_position as ap
					inner join acceptance as a on a.id=ap.acceptance_id
					
					left join bill as b on a.bill_id=b.id
					left join supplier as sup on b.supplier_id=sup.id
					left join opf on opf.id=sup.opf_id
				where
					a.is_incoming=1 and a.is_confirmed=1 
					and (ap.out_bill_id="'.$f['out_bill_id'].'" or ap.out_bill_id=0)
					and ap.position_id="'.$f['position_id'].'"
					and (ap.komplekt_ved_id="'.$f['komplekt_ved_id'].'" or ap.komplekt_ved_id=0)
				order by  a.given_pdate		
					';
					
				$ins=array();	
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				for($i1=0; $i1<$rc1; $i1++){
					$f1=mysqli_fetch_array($rs1);
					$f1['given_pdate_unf']=$f1['given_pdate'];
					$f1['given_pdate']=date('d.m.Y', $f1['given_pdate']);
					
					
					$ins[]=$f1;
				}
				
				$f['ins']=$ins;
				
				//поля для сотрировки
				if(count($ins)>0){
					 $f['in_supplier_name']=$ins[count($ins)-1]['supplier_name'];
					 $f['in_price_pm']=$ins[count($ins)-1]['price_pm'];
					 $f['in_given_pdate']=$ins[count($ins)-1]['given_pdate_unf'];
					 $f['in_given_no']=$ins[count($outs)-1]['given_no'];
				}else{
					 $f['in_supplier_name']='';
					 $f['in_price_pm']=0;
					 $f['in_given_pdate']=0;
					 $f['in_given_no']='';
					 
				}
				
			
				
				//учесть расходы!
				$sum_2=0; $sum_3=0;
				$pos=$this->IsByBill($f['out_bill_id'],2);
				if(($pos!=-1)&&!$this->IsSeen($f['out_bill_id'],2)){
						$sum_2=$this->cash_array[$pos]['sum'];
						 $this->SetSeen($f['out_bill_id'],2);
				}
				
				$pos=$this->IsByBill($f['out_bill_id'],3);
				if(($pos!=-1)&&!$this->IsSeen($f['out_bill_id'],3)){
						$sum_3=$this->cash_array[$pos]['sum'];
						 $this->SetSeen($f['out_bill_id'],3);
				}
				
				$f['sum_2']=$sum_2; $f['sum_3']=$sum_3;
				
				//процент на дату последней реализации
				if(count($outs)==0){
					// echo 'zzzzzz '. count($outs) ;	
					 $f['percent']=$_cpi->GetActualByPdate($ins[count($ins)-1]['org_id'], $ins[count($ins)-1]['given_pdate']);
				}else $f['percent']=$_cpi->GetActualByPdate($outs[count($outs)-1]['org_id'], $outs[count($outs)-1]['given_pdate']);
								
				$f['percent_percent']=$f['percent']['percent']; //для сортировки
			
				
				
				//обработка +/-
				$f['pm_res']=0; $f['pm_to_give']=0; $f['pm_given']=0;
				$pm_to_give=0; //в расчет
				//bpm.plus_or_minus, bpm.rub_or_percent, bpm.value as pm_value 
				if($f['pm_value']!==NULL){
					 
					$f['pm_res']=round(($f['price_pm']-$f['price'])*$f['quantity'],2);
					
					
					if($f['discount_rub_or_percent']==1)  $f['discount_value']=round(($f['pm_res']*$f['discount_value'])/100,2);
					
					$f['pm_to_give']=$f['pm_res']-$f['discount_value'];
					
					if($f['pm_to_give']>=0) $pm_to_give=$f['pm_to_give'];
					
					$f['pm_given']=round($_cpg->CalcGiven($f['id']),2); 
				}
				
				
				$f['cash']=round(((float)$f['sum_2']+(float)$f['sum_3']+$pm_to_give)*($f['percent']['percent'])/100,2);
				
				
				$f['pribyl']=$f['out_ap_total']- $f['in_ap_total']-$f['cash']-$f['pm_to_give']-$f['sum_2']-$f['sum_3'];
				
				
				
				$total_pm+=$f['pm_res'];
				$total_to_give_pm+=$f['pm_to_give'];
				$total_pribyl+=$f['pribyl'];
				
				$alls[]=$f;	
			}
			
			/*echo '<pre>';
			print_r($this->cash_array);
			echo '</pre>'; */
			
		}
		
		
	
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		
		$sm->assign('do_it',$do_show_data);
		
		
		//заполним шаблон полями
	
		$fields=$dec->GetUris();
		$sortmode=0;
		foreach($fields as $k=>$v){
			if($v->GetName()=='sortmode') $sortmode=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		$sm->assign('total_in', $total_in);
		$sm->assign('total_out', $total_out);
		$sm->assign('total_pm', $total_pm);
		$sm->assign('total_to_give_pm', $total_to_give_pm);
		$sm->assign('total_pribyl',$total_pribyl);
		
		
		
	
		$sm->assign('pagename',$pagename);
		
		//var_dump($do_show_data);
		
		
		$link=$dec->GenFltUri('&' );
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link).'&doSub=1';;
		
		$sm->assign('link',$link);
		
		
		$sm->assign('sortmode',$sortmode);
		
		
		
		//сортировка
		if($sortmode>=0){
			$sorts=$dec->GenFltOrdArr();
			$fieldname=''; $direction=0;
			foreach($sorts as $k=>$v){
				$fieldname=$v->GetName();
				$_direction=$v->GetValue();
				if($_direction==SqlOrdEntry::ASC) $direction=0;
				else $direction=1;	
			}
			
			//echo $fieldname; echo $direction;
			$alls=$this->SortArr($alls,$fieldname,$direction);
		}elseif($sortmode==-1){
			//echo 'double sort';	
			$alls=$this->SortArr($alls,'out_given_pdate',0);
			//придумать алгоритм второй сортировки...
			//весь массив данных разбивать на массивы с одинаковой  out_given_pdate
			//к каждому из подмассивов применять $this->SortArr($alls,'supplier_bill_no',0);
			//затем склеить все обратно!
			
			$sub_alls=array();
			/*$old_out_given_pdate='';
			
			$sub=array(); $date_was_changed=false; $cter=0;
			foreach($alls as $k=>$v){
				
				//дата изменилась, новый подмассив
				if($v['out_given_pdate']!=$old_out_given_pdate){
					$sub_alls[]=$sub;
					$sub=array();
					if($cter>0) $date_was_changed=$date_was_changed||true;
				}
				
				$sub[]=$v;
				
				$old_out_given_pdate=$v['out_given_pdate'];	
				$cter++;
			}
			//если дата ни разу не менялась - занести весь подмассив
			if(!$date_was_changed){
				//echo 'zzzzzzzzzzzz';
				
				$sub_alls[]=$sub;
			}*/
			
			//построим массив дат...
			$pdates_array=array();
			foreach($alls as $k=>$v){
				if(!in_array($v['out_given_pdate'], $pdates_array)) $pdates_array[]=$v['out_given_pdate'];
			}
			
			//разобьем общий массив на подмассивы по датам
			foreach($pdates_array as $k=>$v){
				$sub=array();
				
				foreach($alls as $kk=>$vv) if($vv['out_given_pdate']==$v) $sub[]=$vv;
				
				$sub_alls[]=$sub;	
			}
			
			
			//применим сортировку к каждому из подмассивов $sub_alls
			foreach($sub_alls as $k=>$v){
				$v=	$this->SortArr($v,'supplier_bill_no',0);
				$sub_alls[$k]=$v;
			}
			
			//echo count($sub_alls);
			
			//склеим подмассивы в сплошной массив
			$alls1=array();
			foreach($sub_alls as $k=>$v){
				//echo count($v);
				
				foreach($v as $kk=>$vv) $alls1[]=$vv;	
			}
			
			//переопределим выходной массив
			$alls=$alls1;
		}
		
		
		$sm->assign('items',$alls);
		
		
		
		
		$sm->assign('can_print',$can_print);	
		
			
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	protected function BuildCashArray($pdate1, $pdate2, $supplier_filter,  $db_flt){
		$sql='select bp.*,
				b.id as out_bill_id, b.code as out_bill_code, b.pdate as out_bill_pdate, b.supplier_bill_no,
				
				count(out_ap.id) as cou_out_ap_id,
				count(in_ap.id) as cou_in_ap_id 
				
				 
				
				
			from bill_position as bp
			inner join bill as b on b.id=bp.bill_id and b.is_incoming=0
			
			
			left join acceptance_position as out_ap on  out_ap.position_id=bp.position_id and out_ap.komplekt_ved_id=bp.komplekt_ved_id
			left join acceptance as out_a on out_a.bill_id=b.id and out_ap.acceptance_id=out_a.id and out_a.is_confirmed=1 and out_a.is_incoming=0
			
			
			left join acceptance_position as in_ap on in_ap.position_id=bp.position_id and in_ap.komplekt_ved_id=bp.komplekt_ved_id and in_ap.out_bill_id=b.id
			left join acceptance as in_a on in_ap.acceptance_id=in_a.id and in_a.is_confirmed=1 and in_a.is_incoming=1
			
			
			
			where 
				(out_a.given_pdate between "'.$pdate1.'" and "'.$pdate2.'")
				
				
				'.$supplier_filter. '
				'.$db_flt.'
			
		 	
			group by out_ap.position_id, in_ap.position_id
			having cou_out_ap_id>0 and cou_in_ap_id>0
				
			order by out_a.given_pdate asc, bp.name asc	';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			$docs2=array(); $docs3=array();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				if($this->IsByBill($f['out_bill_id'],2)==-1) {
					 
					$not_flt='';
					if(count($docs2)>0) $not_flt=' and id not in ('.implode(', ',$docs2).')';
					 
					 
											//найдем сумму расходов по счету
					$sql1='select sum(value) as s_q from cash where is_confirmed=1 and kind_id=2 and id in(select cash_id from cash_to_bill where bill_id="'.$f['out_bill_id'].'") '.$not_flt;
					
					//echo $sql1.'<br>';
					
					$set1=new mysqlset($sql1);
					$rs1=$set1->GetResult();
					$rc1=$set1->GetResultNumRows();
					$f1=mysqli_fetch_array($rs1);
					
					$this->cash_array[]=array(
						'bill_id'=>$f['out_bill_id'],
						'kind_id'=>2,
						'sum'=>(float)$f1['s_q'],
						'was_seen'=>false	
					);
					 
					
					$sql1='select distinct c.*
					
					from cash as c
					 where c.is_confirmed=1 and c.kind_id=2 and c.id in(select cash_id from cash_to_bill where bill_id="'.$f['out_bill_id'].'")';
					$set1=new mysqlset($sql1);
					$rs1=$set1->GetResult();
					$rc1=$set1->GetResultNumRows();
					for($i1=0; $i1<$rc1; $i1++){	
						$f1=mysqli_fetch_array($rs1);
						if(!in_array($f[0], $docs2)) $docs2[]=$f1[0];
					}
					
					
					
					
				}
				
				 
				if($this->IsByBill($f['out_bill_id'],3)==-1) {
					 
					$not_flt='';
					if(count($docs3)>0) $not_flt=' and id not in ('.implode(', ',$docs3).')';
					 
					 
											//найдем сумму расходов по счету
					$sql1='select sum(value) as s_q from cash where is_confirmed=1 and kind_id=3 and id in(select cash_id from cash_to_bill where bill_id="'.$f['out_bill_id'].'") '.$not_flt;
					
					//echo $sql1.'<br>';
					
					$set1=new mysqlset($sql1);
					$rs1=$set1->GetResult();
					$rc1=$set1->GetResultNumRows();
					$f1=mysqli_fetch_array($rs1);
					
					$this->cash_array[]=array(
						'bill_id'=>$f['out_bill_id'],
						'kind_id'=>3,
						'sum'=>(float)$f1['s_q'],
						'was_seen'=>false	
					);
					 
					
					$sql1='select distinct c.*
					
					from cash as c
					 where c.is_confirmed=1 and c.kind_id=3 and c.id in(select cash_id from cash_to_bill where bill_id="'.$f['out_bill_id'].'")';
					$set1=new mysqlset($sql1);
					$rs1=$set1->GetResult();
					$rc1=$set1->GetResultNumRows();
					for($i1=0; $i1<$rc1; $i1++){	
						$f1=mysqli_fetch_array($rs1);
						if(!in_array($f[0], $docs3)) $docs3[]=$f1[0];
					}
					
					
				}
			}
			
			
	}
	
	protected function IsByBill($bill_id, $kind_id){
		$res=-1;
		
		foreach($this->cash_array as $k=>$v){
			if(($v['bill_id']==$bill_id)&&($v['kind_id']==$kind_id)) return $k;
		}
		
		return $res;
	}
	
	protected function IsSeen($bill_id, $kind_id){
		$res=false;
		foreach($this->cash_array as $k=>$v){
			if(($v['bill_id']==$bill_id)&&($v['kind_id']==$kind_id)&&($v['was_seen'])) return true;
		}
		return $res;
	}
	
	protected function SetSeen($bill_id, $kind_id){
		foreach($this->cash_array as $k=>$v){
			if(($v['bill_id']==$bill_id)&&($v['kind_id']==$kind_id)){
				$v['was_seen']=true;
				$this->cash_array[$k]=$v;
			}
		}
	}
	
	
		
	//принадлежит ли данная категория категории услуг
	protected function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
	
	
	
	
	
	
	//временная функция печати
	
	
	
	
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
				}else{
				 	$a=array_pop($arr);
					$result[]=$a;
					//echo $a[$fieldname].' - no minimum, use as minimal<br>';
				}
			}else{
				//max
				$a=$this->FindMax($arr, $fieldname, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else{
				 $a=array_pop($arr);
					$result[]=$a;
					//echo $a[$fieldname].' - no maximum, use as maximal<br>';
				}
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	protected function FindMin($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		
		$minval=999999999999999999999999999999999999999;
		$minval_str='я';
		
		
		//строка или цифра?
		$is_number=true;
		foreach($arr as $k=>$v){
			if(!is_numeric($v[$fieldname])) $is_number=$is_number&&false;
		}
		
		if($is_number)  $crit=$minval;
		else $crit=$minval_str;
		
		//var_dump($is_number);
		
		/*
		if(count($arr)>0) {
			$res=$arr[0]; $index=0;	
		}
		*/
		
		foreach($arr as $k=>$v){
			 
		
			
			
			if($v[$fieldname]<$crit){
				
				$crit=$v[$fieldname]; 
//				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		
		$maxval=-999999999999999999999999999999999999999;
		$maxval_str='А';
		
		
		
		//строка или цифра?
		$is_number=true;
		foreach($arr as $k=>$v){
			if(!is_numeric($v[$fieldname])) $is_number=$is_number&&false;
		}
		
		if($is_number)  $crit=$maxval;
		else $crit=$maxval_str;
		
		//var_dump($is_number);
		
		/*
		if(count($arr)>0) {
			$res=$arr[0]; $index=0;	
		}
		*/
		
		foreach($arr as $k=>$v){
			
			 
			
			
			if($v[$fieldname]>$crit){
				
				 $crit=$v[$fieldname];
				 
				
				//$maxval=$v[$fieldname];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}
}
?>