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
require_once('paycodegroup.php');
require_once('paycodeitem.php');

require_once('cash_in_codegroup.php');
require_once('cash_in_codeitem.php');

require_once('suppliertouser.php');
require_once('authuser.php');

require_once('orgsgroup.php');
class AnRent{
	
	protected $begin_ost; //Остаток на 23.02.2014
	protected $begin_pdate; //=datefromdmy('23.02.2014'); 
	protected $restricted_codes=array(59,60,64); //коды оплат А, В, С
	protected $extra_codes=array(64); //расходы учредителей
	
	protected $org_ids;
	protected $orgs;
	
	
	/*
	0 поступление
	1 реализация
	2 исх. оплата
	3 расход наличных
	4 проценты от расходов
	*/
	
	
	function __construct( $begin_ost=200000, $begin_pdate='28.02.2014', $result=NULL){
		$this->begin_pdate=datefromdmy($begin_pdate); 
		$this->begin_ost=$begin_ost;
		
		if($result===NULL){
			$_au=new AuthUser;
			$result=$_au->Auth();
				
		}
		
		$_stu=new SupplierToUser1;
		$this->org_ids=$_stu->GetUserOrgIds($result['id']);
		//$this->org_ids=array(1);
		
		$this->orgs=$_stu->GetUserOrgs($result['id']);
		
		//print_r($this->orgs);
		
	}
	
	
	
	
	public function ShowData($pdate1, $pdate2, $extended_an=0, $org_id, $template, DBDecorator $dec,$pagename='files.php', $do_show_data=false, $can_print=false, $dec_sep=DEC_SEP){
		$alls=array();
		$sm=new SmartyAdm;
		
		
		$_pcg=new PayCodeGroup;
		$_pci=new PayCodeItem;
		
		$_ccg=new CashInCodeGroup;
		$_cci=new CashInCodeItem;
		
		
		if($do_show_data){
			//найти общий нач и кон остатки 
			$before_ost=$this->Ost($pdate1, $org_id, $overal_plus, $overal_minus);
			
			$after_ost=$this->Ost($pdate2+24*60*60-1, $org_id,  $overal_plus, $overal_minus);
			
			$this->PrihodRashod($pdate1, $pdate2, $org_id,  $overal_plus, $overal_minus);
			
			//прибавление нач. остатка:
			// date2 >= дата нач остатка >= date1 - внутри интервала - добавить к  $overal_plus
			if(($this->begin_pdate>=$pdate1)&&($this->begin_pdate<=($pdate2+24*60*60-1))) {
				$overal_plus+=$this->begin_ost;
				 $after_ost+=$this->begin_ost;
				
			}
			//дата нач остатка < date1 - слева интервала - добавить к $before_ost, $after_ost
			elseif(($this->begin_pdate<$pdate1)){
				 $before_ost+=$this->begin_ost;
				 $after_ost+=$this->begin_ost;
			}
			
			//echo date('d.m.Y H:i:s',$pdate1).' '. date('d.m.Y H:i:s',$this->begin_pdate).' '.date('d.m.Y H:i:s',$pdate2);
			
			//дата нач остатка > date2 - справа интервала - никуда не добавлять
			
			//$sm->assign('dohod',number_format($overal_plus-$overal_minus,2,'.',$dec_sep));
			
			$sm->assign('before_ost',number_format($before_ost,2,'.',$dec_sep));
			$sm->assign('after_ost',number_format($after_ost,2,'.',$dec_sep)); 
			$sm->assign('total_plus',number_format($overal_plus,2,'.',$dec_sep));
			$sm->assign('total_dolg',number_format($overal_minus,2,'.',$dec_sep)); 
			
			
			//echo $after_ost;
			
			//разбивка по датам и документам - пока неактивна
			
			//1. доходность
			$doh_end_ost=0;
			
			/*$doh_begin_ost=$this->Ost($pdate1, $org_id, $op, $om, true,false);
			$doh_end_ost=$this->Ost($pdate2+24*60*60-1, $org_id, $op, $om, true,false);*/
			//найти сумму по поступлениям - расход
			$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					left join bill as b on b.id=ac.bill_id
				where ac.is_confirmed=1 
				and ac.is_incoming=1 
				and b.out_bill_id<>0 
				and ac.org_id in('.implode(', ',$this->org_ids).')
					and ( ac.given_pdate between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
					';
						
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			 
			$f=mysqli_fetch_array($rs);	
			 
			
			
			$om=(float)$f[0];
			$total_acc_in=(float)$f[0];
			
			
			//найти сумму по реализациям
			$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					left join bill as b on b.id=ac.bill_id
				where ac.is_confirmed=1 
				and ac.is_incoming=0
				and b.komplekt_ved_id<>0 
				 and ac.org_id in('.implode(', ',$this->org_ids).')
					and ( ac.given_pdate between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
					';
						
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			 
			$f=mysqli_fetch_array($rs);	
			 
			
			
			$op=(float)$f[0];
			$total_acc=(float)$f[0];
			
			//найти сумму по приходам наличных - приход
			
			
			
			
			$doh_end_ost=$op-$om;
			$total_by_acc=$total_acc-$total_acc_in;
			
			
			//1.5 приходы наличных
			
			$ccg=$_ccg->GetItemsArr(0);
			//print_r($pcg);
			
			$this->build_array($ccg, $ccg1);
			
			$ccg=$ccg1;
			
			//найдем сумму по каждой статье!
			$value_cash_in=0;
			foreach($ccg as $k=>$v){
				$value=0;
				
				 
				
				//найдем приход. наличных - приход
				$sql='select sum(p.value) 
					from cash_in as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
					    and (p.given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id="'.$v['id'].'"
						';
				//echo $sql.'<br>';		
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$value+=(float)$f[0]; 
				
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				$v['value']=$value;
				
				$value_cash_in+=$value;
				
				$ccg[$k]=$v;	
			}
			
			$sm->assign('value_cash_in',number_format($value_cash_in,2,'.',$dec_sep));
			
			
			
			
			
			//2. расходы (платежи)
			$pcg=$_pcg->GetItemsArr(0);
			//print_r($pcg);
			
			$this->build_array($pcg, $pcg1);
			
			$pcg=$pcg1;
			
			foreach($pcg as $k=>$v){
				if(in_array($v['id'], $this->restricted_codes)) unset($pcg[$k]);	
			}
			
			
						
			//найдем сумму по каждой статье!
			$value_pays=0;
			foreach($pcg as $k=>$v){
				$value=0;
				
				//найдем исх. оплаты - расход
				$sql='select sum(p.value) 
					from payment as p
					where p.is_confirmed=1 and p.is_incoming=0 and p.org_id in('.implode(', ',$this->org_ids).')
						and (p.given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id="'.$v['id'].'"
						';
				
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$value+=(float)$f[0]; 
				
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				//найдем расх. наличных - расход
				$sql='select sum(p.value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
					    and (p.confirmed_given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id="'.$v['id'].'"
						';
				//echo $sql.'<br>';		
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$value+=(float)$f[0]; 
				
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				$v['value']=$value;
				
				$value_pays+=$value;
				
				$pcg[$k]=$v;	
			}
			//добавить проценты по статье 2,1,08
			
			$sql='select sum(p.percent_value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
					    and (p.confirmed_given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						
					';
			//echo $sql.'<br>';		
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			 
			$f=mysqli_fetch_array($rs);	
			$value=(float)$f[0]; 
			$value_pays+=$value;
			
			
			//добавим проценты в список 
			if($value>0){ 
				$pci=$_pci->GetItemByFields(array('code'=>'2.1.08.'));
				
				
				$was=false; $index=-1; $index_near=-1;
				foreach($pcg as $k=>$v){
					if($v['code']==$pci['code']){
						$was=true; $index=$k; break;	
					}elseif($v['code']<=$pci['code']) $index_near=$k;
				}
				if($was){
					$pcg[$index]['value']+=	$value;
				}else{
					//вставить после индекс_ниар
					$pcg1=array();
					foreach($pcg as $k=>$v){
						$pcg1[]=$v;
						if($k==$index_near){
							$pci['value']=$value;
							$pcg1[]=$pci;
						}
						
					}
					$pcg=$pcg1;
				}
			}
				
			$sm->assign('value_pays',number_format($value_pays,2,'.',$dec_sep));
			
			
			
			
			
			
			//2.5 расходы по коду C
			$pcg_c=$_pcg->GetItemsArr(0);
			//print_r($pcg);
			
			$this->build_array($pcg_c, $pcg_c1);
			
			$pcg_c=$pcg_c1;
			
			foreach($pcg_c as $k=>$v){
				if(!in_array($v['id'], $this->extra_codes)) unset($pcg_c[$k]);	
			}
			
			
						
			//найдем сумму по каждой статье!
			$value_cash_c=0;
			foreach($pcg_c as $k=>$v){
				$value=0;
				
				 
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				//найдем расх. наличных - расход
				$sql='select sum(p.value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
					    and (p.confirmed_given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id="'.$v['id'].'"
						';
				//echo $sql.'<br>';		
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$value+=(float)$f[0]; 
				
				//if((float)$f[0]>0) echo $sql.'<br>';
				
				$v['value']=$value;
				
				$value_cash_c+=$value;
				
				$pcg_c[$k]=$v;	
			}
			
			$sm->assign('value_cash_c_unf',$value_cash_c);	
			$sm->assign('value_cash_c',number_format($value_cash_c,2,'.',$dec_sep));
			
			
			
			
			
			
			//echo $doh_end_ost; Доходность по поступлениям
			$sm->assign('doh_plus',number_format( $op,2,'.',$dec_sep));
			$sm->assign('doh_minus',number_format( $om,2,'.',$dec_sep));
			$sm->assign('doh_end_ost',number_format( $doh_end_ost+$value_cash_in,2,'.',$dec_sep));
			
			$sm->assign('doh_neat',number_format( $doh_end_ost+$value_cash_in-$value_pays,2,'.',$dec_sep));
			
				//итого для ТАБЛИЧКИ ПОСТУПЛЕНИЙ-РЕАЛИЗАЦИЙ
			$sm->assign('total_acc',number_format( $total_acc,2,'.',$dec_sep));
			$sm->assign('total_acc_in',number_format( $total_acc_in,2,'.',$dec_sep));
			$sm->assign('total_by_acc',number_format( $total_by_acc,2,'.',$dec_sep));
			
			
			
			//3. общее итого - см. выше!
			
			
			
			
			
			
			
			
			
			
			
			
			
			//4. расширенная версия
			if($extended_an){
					//ТАБЛИЦА 1: все поступления, реализации за период
					//разбивка по позициям
					//в доход: позиции реализаций в кол-вах и суммах по реализациям (по дате реализации)
					//в расход: просто позиции поступлений (по дате поступления)
					
					
					
					
					$docs1=$this->GetDocsAcc($pdate1, $pdate2, $org_id);
					$org_docs1=array();
					$orgs_docs2=array();
					
					$current_date=$pdate1;
					  
					$dates=array();
					$pdate_begin_ost=0;
					do{
						
						 $dates[]=date('d.m.Y',$current_date);
							 
							
						//echo $after_ost;
						$current_date+=24*60*60;
						 
					}while($current_date<$pdate2+24*60*60);
					
					//print_r($docs1);
					
					
					
					 
					//перебрать блок док-тов   соотнести с датами
					foreach($dates as $date_k=>$date_v){
						$plus=0;
				 	 	$dolg=0;
						$docs_on_date=array();
						foreach($docs1 as $doc_k=>$doc_v) {
				  
				  			if($doc_v['given_pdate']==$date_v){
								$docs_on_date[]=$doc_v;
								
								
								
								 if($doc_v['kind']==0){
									$plus+=(float)$doc_v['total']; 
								 }elseif($doc_v['kind']==1){
									$dolg+=(float)$doc_v['total']; 
								 }
								 
								 
								 
							}// of eq
								 
						}// of docs
						
						
						//внутри даты - разбить документы на организации
						$date_orgs=array();
						//строим массив организаций на дату
						foreach($this->orgs as $orgk=>$orgv){
							foreach($docs_on_date as $ddk=>$doc_v){
								if(($doc_v['org_id']==$orgv['id'])&&(!in_array($orgv,$date_orgs))) $date_orgs[]=$orgv;
							}
						}
						
						/*echo '<pre>';
						if($date_v=='08.04.2014') print_r($date_orgs);
						echo '</pre>';*/
						
						//пройдем массив организаций на дату
						//сформируем для каждой организации ее документы
						
						foreach($date_orgs as $orgk=>$orgv){
							$docs_on_date_org=array();
							foreach($docs_on_date as $ddk=>$doc_v){
								if(($doc_v['org_id']==$orgv['id'])) $docs_on_date_org[]=$doc_v;
							}
							$date_orgs[$orgk]['docs']=$docs_on_date_org;
						}
						
						$orgs_docs2[]=array(
							'pdate'=>$date_v,
							'plus'=>number_format($plus, 2,'.',$dec_sep),
							'dolg'=>number_format($dolg, 2,'.',$dec_sep),
							
							
							'plus_unf'=> $plus, 
							'dolg_unf'=>$dolg, 
							'docs_on_date'=>$date_orgs
							
							);	 //$date_orgs;
							
							/*$orgs_docs1[]=array(
							'pdate'=>$date_v,
							'plus'=>number_format($plus, 2,'.',$dec_sep),
							'dolg'=>number_format($dolg, 2,'.',$dec_sep),
							
							
							'plus_unf'=> $plus, 
							'dolg_unf'=>$dolg, 
							'docs_on_date'=>$docs_on_date
							);	 */
					}// of date
					
					//расчет остатков на дату, на документ
					//на дату
					$bo=0;
					foreach($orgs_docs2 as $k=>$rec){
						$rec['begin_ost_unf']=$bo;
						$rec['begin_ost']=number_format($bo, 2,'.',$dec_sep);
						
						
						
						$bo=$bo+$rec['plus_unf']-$rec['dolg_unf'];
						$rec['end_ost_unf']=$bo;
						$rec['end_ost']=number_format($bo, 2,'.',$dec_sep);
						
						
						//на документ
						$bbo=$rec['begin_ost_unf'];
						foreach($rec['docs_on_date'] as $kk=>$org){
						  foreach($org['docs'] as $kkk=>$doc){
							  $doc['begin_ost_unf']=$bbo;
							  $doc['begin_ost']=number_format($bbo, 2,'.',$dec_sep);
							  
							  if(($doc['kind']==0)){
								  $bbo=$bbo+$doc['total'];
							  }else $bbo=$bbo-$doc['total'];
							  
							  $doc['end_ost_unf']=$bbo;
							  $doc['end_ost']=number_format($bbo, 2,'.',$dec_sep);
							  
							  $org['docs'][$kkk]=$doc;	
							  
						  }
						  
						  
						  
						  
						  $rec['docs_on_date'][$kk]=$org;
					  }
						
						
						
						$orgs_docs2[$k]=$rec;	
					}
					
					 
					
					
					//print_r($orgs_docs1);
					
					
					
					//print_r($orgs_docs1);
					
					
					
					
					
					
					//ТАБЛИЦА 1,5 - детализация по кодам статей доходов приходов наличных
					foreach($ccg as $k=>$v){
						//print_r($v); echo '<br>';	
						
						$v['docs']=$this->GetDocsCashIn($pdate1, $pdate2, $org_id, $v['id']);
						
						//print_r($v['docs']); echo '<br>';	
						
						$ccg[$k]=$v;
					}
					
					
					
					
					
					//ТАБЛИЦА 2 - детализация по кодам оплат, расходов
					 
					foreach($pcg as $k=>$v){
						//print_r($v); echo '<br>';	
						
						$v['docs']=$this->GetDocsPayCash($pdate1, $pdate2, $org_id, $v['id']);
						
						//print_r($v['docs']); echo '<br>';	
						
						$pcg[$k]=$v;
					}
					
					
					
					
					
					
					//таблица 2,5 - детализация по кодам расходов вида С
					//GetDocsCashC
					foreach($pcg_c as $k=>$v){
						//print_r($v); echo '<br>';	
						
						$v['docs']=$this->GetDocsCashC($pdate1, $pdate2, $org_id, $v['id']);
						
						//print_r($v['docs']); echo '<br>';	
						
						$pcg_c[$k]=$v;
					}
					
					
					
					
					
					
					//ТАБЛИЦА 3: получить все док-ты за период:
					
					/*
					0 поступление
					1 реализация
					2 исх. оплата
					3 расход наличных
					4 - %
					*/
					 
	 				
					
			}
			
		}
		
		
		$sm->assign('ccg',$ccg);
		
		
		
		$sm->assign('pcg',$pcg);
		
		$sm->assign('pcg_c',$pcg_c);
		
		$sm->assign('items',$orgs_docs);
		
		$sm->assign('items1',$orgs_docs2);
		//$sm->assign('items1',$orgs_docs2);
		//var_dump($orgs_docs2);
		
		$sm->assign('do_it',$do_show_data);
		
		
		//заполним шаблон полями
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		 
	
		$sm->assign('pagename',$pagename);
		
		//var_dump($do_show_data);
		
		
		$sm->assign('can_print',$can_print);	
		
			
		return $sm->fetch($template);
	}
	
	
	
	
	//список позиций поступлений, реализаций за период
	public function GetDocsAcc($pdate1, $pdate2, $org_id){
		$arr=array();
		
		
		$sql='
		(
		select "0" as kind, 
			a.given_no, a.given_pdate,
			 
			sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id,
			ap.name as position_name,
			
			
			 org.full_name as org_name,  opf1.name as opf_name1, org.id as org_id,
			 ap.*,
			 b.supplier_bill_no, b.id as bill_id

			 
			from 
				acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				
				left join bill as b on a.bill_id=b.id
				left join supplier as sup on b.supplier_id=sup.id
				left join opf on opf.id=sup.opf_id
				
				left join supplier as org on a.org_id=org.id
				 	left join opf as opf1 on opf1.id=org.opf_id
			where
				a.is_incoming=0 
				 and b.komplekt_ved_id<>0 
				
				and a.is_confirmed=1 and a.org_id in('.implode(', ',$this->org_ids).')
				and (a.given_pdate  between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
		)
		UNION ALL
		(
		select "1" as kind, 
			a.given_no, a.given_pdate,
			 
			sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id,
			ap.name as position_name,
			 org.full_name as org_name,  opf1.name as opf_name1, org.id as org_id,

			 ap.*,
			 b.supplier_bill_no, b.id as bill_id 
			
			 
			from 
				acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				
				left join bill as b on a.bill_id=b.id
				left join supplier as sup on b.supplier_id=sup.id
				left join opf on opf.id=sup.opf_id
				
				left join supplier as org on a.org_id=org.id
			 	left join opf as opf1 on opf1.id=org.opf_id
				
			where
				a.is_incoming=1 
				and b.out_bill_id<>0
				and a.is_confirmed=1 and a.org_id in('.implode(', ',$this->org_ids).')
				and (a.given_pdate  between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
		)
		order by 3 asc, 8 asc, 7 asc
		';
		
		//echo $sql.'<br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($rs);	
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			/*if($f['kind']==0) $f['value']=$_ai_in->CalcCost($f['id']);
			
			elseif($f['kind']==1)  $f['value']=$_ai->CalcCost($f['id']);
			*/
			
			$arr[]=$f;
		}	
		
		
		return $arr;	
	}
	
	
	
	
	
	
	
	//список оплат, расходов нал за период
	public function GetDocsPayCash($pdate1, $pdate2, $org_id, $code_id){
		$_ai=new AccItem;
		$_ai_in=new AccInItem;
		
		$alls=array();
		$sql='
			 
			 
			 (select p.id, p.code, value, p.given_pdate, p.given_no,
			"2" as kind,
			
			sp.full_name as supplier_name, opf.name as supplier_opf, sp.id as supplier_id,
			  inu.name_s as inu_name_s, inu.login as inu_login,
			  p.is_inner_pay as is_inner_pay,
			  
			   org.full_name as org_name, opf1.name as opf_name1, org.id as org_id
			
				from payment as p
				left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   left join user as inu on p.inner_user_id=inu.id
			   
			   left join supplier as org on p.org_id=org.id
				left join opf as opf1 on opf1.id=org.opf_id
				
				
			where p.is_confirmed=1 and p.is_incoming=0 and p.org_id in('.implode(', ',$this->org_ids).')
				and (p.given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )	
				and p.code_id ="'.$code_id.'"
			  )
			 
			  UNION ALL
			  (select p.id, p.code, value as value, p.confirmed_given_pdate as given_pdate, p.id as given_no,
			"3" as kind,
			sp.full_name as supplier_name, opf.name as supplier_opf, sp.id as supplier_id,
			"" as inu_name_s, "" as inu_login,
			 0 as is_inner_pay,
			   org.full_name as org_name, opf1.name as opf_name1, org.id as org_id
			
				from cash as p
				left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   
			  
			   left join supplier as org on p.org_id=org.id
				left join opf as opf1 on opf1.id=org.opf_id
				
			where p.is_confirmed_given=1 and p.org_id  in('.implode(', ',$this->org_ids).')
				and (p.confirmed_given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )	
				and p.code_id ="'.$code_id.'"
			  )
			 
			  UNION ALL
			  (
			  
			  
			  select p.id, p.code, percent_value as value, p.confirmed_given_pdate as given_pdate, p.id as given_no,
			"4" as kind,
			
				sp.full_name as supplier_name, opf.name as supplier_opf, sp.id as supplier_id,
				"" as inu_name_s, "" as inu_login,
				 0 as is_inner_pay,
				   org.full_name as org_name, opf1.name as opf_name1, org.id as org_id
			  
				from cash as p
				inner join payment_code as pc on pc.id=37 and "'.$code_id.'"=37 
				left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			  
				 left join supplier as org on p.org_id=org.id
				left join opf as opf1 on opf1.id=org.opf_id
				
			where p.is_confirmed_given=1 and p.org_id  in('.implode(', ',$this->org_ids).')
				and (p.confirmed_given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )	
			  
			  
				
			  )
			  
			  
			  
			  
			  
			  order by 4 asc,   6 asc, 1 asc
		';
		
		//echo $sql.'<br><br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($rs);	
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			if($f['kind']==0) $f['value']=$_ai_in->CalcCost($f['id']);
			
			elseif($f['kind']==1)  $f['value']=$_ai->CalcCost($f['id']);
			
			
			$alls[]=$f;
		}	
		
		
		 
		
		return $alls;
	}
	
	
	
	//список  расходов нал по коду С за период
	public function GetDocsCashC($pdate1, $pdate2, $org_id, $code_id){
		 
		
		$alls=array();
		$sql='
			 
			 
			  
			 
			  
			  (select p.id, p.code, value as value, p.confirmed_given_pdate as given_pdate, p.id as given_no,
			"3" as kind,
			sp.full_name as supplier_name, opf.name as supplier_opf, sp.id as supplier_id,
			"" as inu_name_s, "" as inu_login,
			 0 as is_inner_pay,
			   org.full_name as org_name, opf1.name as opf_name1, org.id as org_id
			
				from cash as p
				left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   
			  
			   left join supplier as org on p.org_id=org.id
				left join opf as opf1 on opf1.id=org.opf_id
				
			where p.is_confirmed_given=1 and p.org_id  in('.implode(', ',$this->org_ids).')
				and (p.confirmed_given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )	
				and p.code_id ="'.$code_id.'"
			  )
			 
			  
			  
			  
			  
			  
			  order by 4 asc,   6 asc, 1 asc
		';
		
		//echo $sql.'<br><br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($rs);	
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
		 
			
			$alls[]=$f;
		}	
		
		
		 
		
		return $alls;
	}
	
	
	
	
	//список приходов нал за период
	public function GetDocsCashIn($pdate1, $pdate2, $org_id, $code_id){
	 
		
		$alls=array();
		$sql='
			 
			 
			  (select p.id, p.code, value as value, p.given_pdate as given_pdate, p.id as given_no,
			"3" as kind,
			sp.full_name as supplier_name, opf.name as supplier_opf, sp.id as supplier_id,
			"" as inu_name_s, "" as inu_login,
			 0 as is_inner_pay,
			   org.full_name as org_name, opf1.name as opf_name1, org.id as org_id,
			   p.wo_supplier
			
				from cash_in as p
				left join supplier as sp on p.supplier_id=sp.id
			   left join opf on sp.opf_id=opf.id
			   
			  
			   left join supplier as org on p.org_id=org.id
				left join opf as opf1 on opf1.id=org.opf_id
				
			where p.is_confirmed_given=1 and p.org_id  in('.implode(', ',$this->org_ids).')
				and (p.given_pdate  between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )	
				and p.code_id ="'.$code_id.'"
			  )
			 
			 
			  
			  order by 4 asc,   6 asc, 1 asc
		';
		
		//echo $sql.'<br><br>';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){ 
			$f=mysqli_fetch_array($rs);	
			
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			 
			$alls[]=$f;
		}	
		
		
		 
		
		return $alls;
	}
	
	
	
	
	
	//остаток на дату!
	public function Ost($pdate, $org_id, &$plus, &$minus, $do_calc_acc=true,  $do_calc_cash=true, $do_calc_plus=true, $do_calc_minus=true){
		$ost=0;
		$plus=0; 
	 
		
		$minus=0;
		
		if($do_calc_acc){
			if($do_calc_minus){
				//найдем поступления - расход
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					left join bill as b on b.id=ac.bill_id
				where ac.is_confirmed=1 
				and ac.is_incoming=1 
				 and b.out_bill_id<>0 
				
				and ac.org_id in('.implode(', ',$this->org_ids).')
					and ac.given_pdate<"'.$pdate.'" 
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				 
				
				
				$minus+=(float)$f[0];
			}
			
			
			
			//найдем реализации - доход
			if($do_calc_plus){
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					left join bill as b on b.id=ac.bill_id
				where ac.is_confirmed=1 
				and ac.is_incoming=0 
				and b.komplekt_ved_id<>0 
				and ac.org_id  in('.implode(', ',$this->org_ids).')
					and ac.given_pdate<"'.$pdate.'" 
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$plus+=(float)$f[0];
			}
		}
		
		
		if( $do_calc_cash){
			
			if($do_calc_minus){
				//найдем исх. оплаты - расход
				$sql='select sum(p.value) 
					from payment as p
					where p.is_confirmed=1 and p.is_incoming=0 and p.org_id  in('.implode(', ',$this->org_ids).')
						and p.given_pdate<"'.$pdate.'"
						and p.code_id not in('.implode(', ',$this->restricted_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				//найдем расх. наличных - расход
				$sql='select sum(p.value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and p.confirmed_given_pdate<"'.$pdate.'"
						and p.code_id not in('.implode(', ',$this->restricted_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				//найдем проценты расхода наличных - расход
				$sql='select sum(p.percent_value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and p.confirmed_given_pdate<"'.$pdate.'"
						and p.code_id not in('.implode(', ',$this->restricted_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				//найдем расход наличных по коду С - расход
				$sql='select sum(p.value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and p.confirmed_given_pdate<"'.$pdate.'"
						and p.code_id  in('.implode(', ',$this->extra_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				//найдем проценты расхода наличных по коду С  - расход
				$sql='select sum(p.percent_value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and p.confirmed_given_pdate<"'.$pdate.'"
						and p.code_id  in('.implode(', ',$this->extra_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				
				
				//найдем приход наличных - приход
				$sql='select sum(p.value) 
					from cash_in as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and p.given_pdate<"'.$pdate.'"
						 
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$plus+=(float)$f[0]; 
			}
		}
		
		$ost=$ost+$plus-$minus;
		
		return $ost;	
	}
	
	
	//приход-расход на Период!
	public function PrihodRashod($pdate1, $pdate2, $org_id, &$plus, &$minus){
		$ost=0;
		$plus=0; 
	 
		
		$minus=0;
		
		 
				//найдем поступления - расход
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					left join bill as b on b.id=ac.bill_id
				where ac.is_confirmed=1 
				and ac.is_incoming=1 
				and b.out_bill_id<>0 
				
				and ac.org_id  in('.implode(', ',$this->org_ids).')
					and (ac.given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'") 
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				 
				
				
				$minus+=(float)$f[0];
			 
			
			
			
			//найдем реализации - доход
			 
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					left join bill as b on b.id=ac.bill_id
				where ac.is_confirmed=1 
				and ac.is_incoming=0 
				and b.komplekt_ved_id<>0 
				and ac.org_id  in('.implode(', ',$this->org_ids).')
					and (ac.given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'")
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$plus+=(float)$f[0];
		 
				//найдем исх. оплаты - расход
				$sql='select sum(p.value) 
					from payment as p
					where p.is_confirmed=1 and p.is_incoming=0 and p.org_id  in('.implode(', ',$this->org_ids).')
						and (p.given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'")
						and p.code_id not in('.implode(', ',$this->restricted_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				//найдем расх. наличных - расход
				$sql='select sum(p.value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and (p.confirmed_given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'")
						and p.code_id not in('.implode(', ',$this->restricted_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				//найдем проценты расхода наличных - расход
				$sql='select sum(p.percent_value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id in('.implode(', ',$this->org_ids).')
						and (p.confirmed_given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id not in('.implode(', ',$this->restricted_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				//найдем расх. наличных по коду С - расход
				$sql='select sum(p.value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and (p.confirmed_given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'")
						and p.code_id in('.implode(', ',$this->extra_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				//найдем проценты расхода наличных   по коду С - расход
				$sql='select sum(p.percent_value) 
					from cash as p
					where p.is_confirmed_given=1 and   p.org_id in('.implode(', ',$this->org_ids).')
						and (p.confirmed_given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'" )
						and p.code_id in('.implode(', ',$this->extra_codes).')
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$minus+=(float)$f[0]; 
				
				
				
				
				//найдем приход наличных - приход
				$sql='select sum(p.value) 
					from cash_in as p
					where p.is_confirmed_given=1 and   p.org_id  in('.implode(', ',$this->org_ids).')
						and (p.given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'")
						 
						';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$plus+=(float)$f[0]; 
		 
		
		$ost=$ost+$plus-$minus;
		
		return $ost;	
	}
	
	
	
	//постпроить прямой массив из вложенного
	protected function build_array($pcg, &$arr){
		foreach($pcg as $k=>$v){
			
			if(count($v['codespos'])>0) $this->build_array($v['codespos'], $arr);
			$arr[]=$v;	
		}
		
	}
		
}
?>