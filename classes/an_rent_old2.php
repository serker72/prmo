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

require_once('suppliertouser.php');
require_once('authuser.php');

require_once('orgsgroup.php');
class AnRent{
	
	protected $begin_ost; //������� �� 23.02.2014
	protected $begin_pdate; //=datefromdmy('23.02.2014'); 
	protected $restricted_codes=array(59,60,64); //���� ����� �, �, �
	
	protected $org_ids;
	protected $orgs;
	
	
	/*
	0 �����������
	1 ����������
	2 ���. ������
	3 ������ ��������
	4 �������� �� ��������
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
		
		//$this->orgs=$_stu->GetUserOrgs($result['id']);
		
		//print_r($this->orgs);
		
	}
	
	
	
	
	public function ShowData($pdate1, $pdate2, $extended_an=0, $org_id, $template, DBDecorator $dec,$pagename='files.php', $do_show_data=false, $can_print=false, $dec_sep=DEC_SEP){
		$alls=array();
		$sm=new SmartyAdm;
		
		
		$_pcg=new PayCodeGroup;
		$_pci=new PayCodeItem;
		
		
		if($do_show_data){
			//����� ����� ��� � ��� ������� 
			$before_ost=$this->Ost($pdate1, $org_id, $overal_plus, $overal_minus);
			
			$after_ost=$this->Ost($pdate2+24*60*60-1, $org_id,  $overal_plus, $overal_minus);
			
			$this->PrihodRashod($pdate1, $pdate2, $org_id,  $overal_plus, $overal_minus);
			
			//����������� ���. �������:
			// date2 >= ���� ��� ������� >= date1 - ������ ��������� - �������� �  $overal_plus
			if(($this->begin_pdate>=$pdate1)&&($this->begin_pdate<=($pdate2+24*60*60-1))) {
				$overal_plus+=$this->begin_ost;
				 $after_ost+=$this->begin_ost;
				
			}
			//���� ��� ������� < date1 - ����� ��������� - �������� � $before_ost, $after_ost
			elseif(($this->begin_pdate<$pdate1)){
				 $before_ost+=$this->begin_ost;
				 $after_ost+=$this->begin_ost;
			}
			
			//echo date('d.m.Y H:i:s',$pdate1).' '. date('d.m.Y H:i:s',$this->begin_pdate).' '.date('d.m.Y H:i:s',$pdate2);
			
			//���� ��� ������� > date2 - ������ ��������� - ������ �� ���������
			
			//$sm->assign('dohod',number_format($overal_plus-$overal_minus,2,'.',$dec_sep));
			
			$sm->assign('before_ost',number_format($before_ost,2,'.',$dec_sep));
			$sm->assign('after_ost',number_format($after_ost,2,'.',$dec_sep)); 
			$sm->assign('total_plus',number_format($overal_plus,2,'.',$dec_sep));
			$sm->assign('total_dolg',number_format($overal_minus,2,'.',$dec_sep)); 
			
			
			//echo $after_ost;
			
			//�������� �� ����� � ���������� - ���� ���������
			
			//1. ����������
			$doh_end_ost=0;
			
			/*$doh_begin_ost=$this->Ost($pdate1, $org_id, $op, $om, true,false);
			$doh_end_ost=$this->Ost($pdate2+24*60*60-1, $org_id, $op, $om, true,false);*/
			//����� ����� �� ������������ - ������
			$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					
				where ac.is_confirmed=1 and ac.is_incoming=1 and ac.org_id in('.implode(', ',$this->org_ids).')
					and ( ac.given_pdate between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
					';
						
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			 
			$f=mysqli_fetch_array($rs);	
			 
			
			
			$om=(float)$f[0];
			
			
			//����� ����� �� �����������
			$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					
				where ac.is_confirmed=1 and ac.is_incoming=0 and ac.org_id in('.implode(', ',$this->org_ids).')
					and ( ac.given_pdate between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
					';
						
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			 
			$f=mysqli_fetch_array($rs);	
			 
			
			
			$op=(float)$f[0];
			
			
			$doh_end_ost=$op-$om;
			
			
			
			
			
			
			//2. ������� (�������)
			$pcg=$_pcg->GetItemsArr(0);
			//print_r($pcg);
			
			$this->build_array($pcg, $pcg1);
			
			$pcg=$pcg1;
			
			foreach($pcg as $k=>$v){
				if(in_array($v['id'], $this->restricted_codes)) unset($pcg[$k]);	
			}
			
			//������ ����� �� ������ ������!
			$value_pays=0;
			foreach($pcg as $k=>$v){
				$value=0;
				
				//������ ���. ������ - ������
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
				
				//������ ����. �������� - ������
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
			//�������� �������� �� ������ 2,1,08
			
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
			
			
			//������� �������� � ������ 
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
					//�������� ����� ������_����
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
			
			
			
			
			
			//echo $doh_end_ost; ���������� �� ������������
			$sm->assign('doh_plus',number_format( $op,2,'.',$dec_sep));
			$sm->assign('doh_minus',number_format( $om,2,'.',$dec_sep));
			$sm->assign('doh_end_ost',number_format( $doh_end_ost,2,'.',$dec_sep));
			
			$sm->assign('doh_neat',number_format( $doh_end_ost-$value_pays,2,'.',$dec_sep));
			
			
			//3. ����� ����� - ��. ����!
			
			
			//4. ����������� ������
			if($extended_an){
					//������� 1: ��� �����������, ���������� �� ������
					//�������� �� ��������
					//� �����: ������� ���������� � ���-��� � ������ �� ����������� (�� ���� ����������)
					//� ������: ������ ������� ����������� (�� ���� �����������)
					
					
					
					
					$docs1=$this->GetDocsAcc($pdate1, $pdate2, $org_id);
					$org_docs1=array();
					
					$current_date=$pdate1;
					  
					$dates=array();
					$pdate_begin_ost=0;
					do{
						
						 $dates[]=date('d.m.Y',$current_date);
							 
							
						//echo $after_ost;
						$current_date+=24*60*60;
						 
					}while($current_date<$pdate2+24*60*60);
					
					//print_r($docs1);
					
					
					
					
					
					//��������� ���� ���-���   ��������� � ������
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
							
							$orgs_docs1[]=array(
							'pdate'=>$date_v,
							'plus'=>number_format($plus, 2,'.',$dec_sep),
							'dolg'=>number_format($dolg, 2,'.',$dec_sep),
							
							
							'plus_unf'=> $plus, 
							'dolg_unf'=>$dolg, 
							'docs_on_date'=>$docs_on_date
							);	 
					}// of date
					
					//������ �������� �� ����, �� ��������
					//�� ����
					$bo=0;
					foreach($orgs_docs1 as $k=>$rec){
						
						//echo 'zzzzzzzzzzzz';
						$rec['begin_ost_unf']=$bo;
						$rec['begin_ost']=number_format($bo, 2,'.',$dec_sep);
						
						
						
						$bo=$bo+$rec['plus_unf']-$rec['dolg_unf'];
						$rec['end_ost_unf']=$bo;
						$rec['end_ost']=number_format($bo, 2,'.',$dec_sep);
						
						
						//�� ��������
						$bbo=$rec['begin_ost_unf'];
						foreach($rec['docs_on_date'] as $kk=>$doc){
							$doc['begin_ost_unf']=$bbo;
							$doc['begin_ost']=number_format($bbo, 2,'.',$dec_sep);
							
							if(($doc['kind']==0)){
								$bbo=$bbo+$doc['total'];
							}else $bbo=$bbo-$doc['total'];
							
							$doc['end_ost_unf']=$bbo;
							$doc['end_ost']=number_format($bbo, 2,'.',$dec_sep);
							
							$rec['docs_on_date'][$kk]=$doc;	
						}
						
						
						
						$orgs_docs1[$k]=$rec;	
					}
					
					
					//print_r($orgs_docs1);
					
					
					
					
					
					
					//������� 2 - ����������� �� ����� �����, ��������
					 
					foreach($pcg as $k=>$v){
						//print_r($v); echo '<br>';	
						
						$v['docs']=$this->GetDocsPayCash($pdate1, $pdate2, $org_id, $v['id']);
						
						//print_r($v['docs']); echo '<br>';	
						
						$pcg[$k]=$v;
					}
					
					
					
					
					
					
					
					
					
					
					
					
					
					//������� 3: �������� ��� ���-�� �� ������:
					
					/*
					0 �����������
					1 ����������
					2 ���. ������
					3 ������ ��������
					4 - %
					*/
					 
	 				
					
			}
			
		}
		
		
		$sm->assign('pcg',$pcg);
		
		$sm->assign('items',$orgs_docs);
		
		$sm->assign('items1',$orgs_docs1);
		
		$sm->assign('do_it',$do_show_data);
		
		
		//�������� ������ ������
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		 
	
		$sm->assign('pagename',$pagename);
		
		//var_dump($do_show_data);
		
		
		$sm->assign('can_print',$can_print);	
		
			
		return $sm->fetch($template);
	}
	
	
	
	
	//������ ������� �����������, ���������� �� ������
	public function GetDocsAcc($pdate1, $pdate2, $org_id){
		$arr=array();
		
		
		$sql='
		(
		select "0" as kind, 
			a.given_no, a.given_pdate,
			 
			sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id,
			ap.name as position_name,
			 ap.*,
			 b.supplier_bill_no, b.id as bill_id,
			 org.full_name as org_name,  opf1.name as opf_name1, org.id as org_id

			 
			from 
				acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				
				left join bill as b on a.bill_id=b.id
				left join supplier as sup on b.supplier_id=sup.id
				left join opf on opf.id=sup.opf_id
				
				left join supplier as org on a.org_id=org.id
				 	left join opf as opf1 on opf1.id=org.opf_id
			where
				a.is_incoming=0 and a.is_confirmed=1 and a.org_id in('.implode(', ',$this->org_ids).')
				and (a.given_pdate  between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
		)
		UNION ALL
		(
		select "1" as kind, 
			a.given_no, a.given_pdate,
			 
			sup.full_name as supplier_name, opf.name as opf_name, sup.id as supplier_id,
			ap.name as position_name,
			 ap.*,
			 b.supplier_bill_no, b.id as bill_id,
			 org.full_name as org_name,  opf1.name as opf_name1, org.id as org_id

			 
			from 
				acceptance_position as ap
				inner join acceptance as a on a.id=ap.acceptance_id
				
				left join bill as b on a.bill_id=b.id
				left join supplier as sup on b.supplier_id=sup.id
				left join opf on opf.id=sup.opf_id
				
				left join supplier as org on a.org_id=org.id
			 	left join opf as opf1 on opf1.id=org.opf_id
				
			where
				a.is_incoming=1 and a.is_confirmed=1 and a.org_id in('.implode(', ',$this->org_ids).')
				and (a.given_pdate  between "'.$pdate1.'" and  "'.($pdate2+24*60*60-1).'" )
		)
		order by 3 asc, 7 asc 
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
	
	
	
	
	
	
	
	//������ �����, �������� ��� �� ������
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
	
	
	
	
	
	//������� �� ����!
	public function Ost($pdate, $org_id, &$plus, &$minus, $do_calc_acc=true,  $do_calc_cash=true, $do_calc_plus=true, $do_calc_minus=true){
		$ost=0;
		$plus=0; 
	 
		
		$minus=0;
		
		if($do_calc_acc){
			if($do_calc_minus){
				//������ ����������� - ������
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					
				where ac.is_confirmed=1 and ac.is_incoming=1 and ac.org_id in('.implode(', ',$this->org_ids).')
					and ac.given_pdate<"'.$pdate.'" 
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				 
				
				
				$minus+=(float)$f[0];
			}
			
			
			
			//������ ���������� - �����
			if($do_calc_plus){
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					
				where ac.is_confirmed=1 and ac.is_incoming=0 and ac.org_id  in('.implode(', ',$this->org_ids).')
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
				//������ ���. ������ - ������
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
				
				
				//������ ����. �������� - ������
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
				
				//������ �������� ������� �������� - ������
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
			}
		}
		
		$ost=$ost+$plus-$minus;
		
		return $ost;	
	}
	
	
	//������-������ �� ������!
	public function PrihodRashod($pdate1, $pdate2, $org_id, &$plus, &$minus){
		$ost=0;
		$plus=0; 
	 
		
		$minus=0;
		
		 
				//������ ����������� - ������
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					
				where ac.is_confirmed=1 and ac.is_incoming=1 and ac.org_id  in('.implode(', ',$this->org_ids).')
					and (ac.given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'") 
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				 
				
				
				$minus+=(float)$f[0];
			 
			
			
			
			//������ ���������� - �����
			 
				$sql='select sum(ap.total) 
					from acceptance_position as ap 
					inner join acceptance as ac on ac.id=ap.acceptance_id
					
				where ac.is_confirmed=1 and ac.is_incoming=0 and ac.org_id  in('.implode(', ',$this->org_ids).')
					and (ac.given_pdate between "'.$pdate1.'" and "'.($pdate2+24*60*60-1).'")
					';
						
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				 
				$f=mysqli_fetch_array($rs);	
				
				$plus+=(float)$f[0];
		 
				//������ ���. ������ - ������
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
				
				
				//������ ����. �������� - ������
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
				
				//������ �������� ������� �������� - ������
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
		 
		
		$ost=$ost+$plus-$minus;
		
		return $ost;	
	}
	
	
	
	//���������� ������ ������ �� ����������
	protected function build_array($pcg, &$arr){
		foreach($pcg as $k=>$v){
			
			if(count($v['codespos'])>0) $this->build_array($v['codespos'], $arr);
			$arr[]=$v;	
		}
		
	}
		
}
?>