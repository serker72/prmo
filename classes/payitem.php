<?
require_once('abstractitem.php');

require_once('payforbillitem.php');
require_once('billitem.php');
require_once('acc_item.php');
require_once('payforbillgroup.php');

require_once('actionlog.php');
require_once('authuser.php');
require_once('invcalcitem.php');

require_once('period_checker.php');
require_once('billpaysync.php');

require_once('pay_in_item.php');
require_once('paynotesitem.php');
require_once('pay_in_creator.php');
require_once('demanditem.php');
require_once('actionlog.php');
require_once('demandnotesitem.php');
require_once('messageitem.php');
require_once('user_s_item.php');
require_once('paycodeitem.php');

//��������� ������
class PayItem extends AbstractItem{
	
	public $billpaysync;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='payment';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		$this->subkeyname='bill_id';
		$this->billpaysync=new BillPaySync;	
	}
	
	public function Edit($id,$params,$scan_status=false){
		$item=$this->GetItemById($id);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		$positions=$this->GetPositionsArr($id);
		foreach($positions as $k=>$v){
			
			if($v['kind']==0){
				$this->billpaysync->CatchStatus($v['bill_id']);
			}
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
		//	$params['restore_pdate']=0;	
			//����������. ���� �-�=�����������, �� ���������, ���� �� ���� ���� ������, ���� ����-������� � ���-�
			$log=new ActionLog;
			if($item['supplier_id']==$item['org_id']){
				$_pay_in=new PayInItem;
				$_pni=new PaymentNotesItem;
				$_pc=new PayInCreator;
				
				$test_pay_in=$_pay_in->GetItemByFields(array('out_pay_id'=>$id, 'org_id'=>$item['org_id']));
				
				$pparams=array();
				$pparams['supplier_id']=$item['supplier_id'];
				$pparams['is_incoming']=1;
				 
				$pparams['code_id']=$item['code_id'];
				$pparams['contract_id']=$item['contract_id'];
				$pparams['bill_id']=0; //$item['supplier_id'];
				$pparams['user_confirm_id']=$params['user_confirm_id'];
				$pparams['confirm_pdate']=time();
				$pparams['pdate']=time();
				$pparams['value']=$item['value'];
				$pparams['is_confirmed']=1;
				$pparams['manager_id']=$params['user_confirm_id'];
				$pparams['supplier_bdetails_id']=$item['org_bdetails_id'];
				$pparams['org_bdetails_id']=$item['supplier_bdetails_id'];
				$pparams['pay_for_dogovor']=0;
				$pparams['pay_for_bill']=0;
				$pparams['given_no']=$item['given_no'];
				$pparams['given_pdate']=$item['given_pdate'];
				$pparams['status_id']=15; //$item['supplier_id'];
				$pparams['invcalc_id']=0; //$item['supplier_id'];
				$pparams['restore_pdate']=0; //$item['supplier_id'];
				$pparams['out_pay_id']=$id;
				
				
				
				if($test_pay_in!==false){
					
					$pay_in_id=$test_pay_in['id'];
					$_pay_in->Edit($pay_in_id, $pparams,true);
					
					$in_code=$test_pay_in['code'];
				}else{
					$pparams['code']=$_pc->GenLogin(0);	
					$pparams['org_id']=	$item['org_id'];
					
					$pay_in_id=$_pay_in->add($pparams);	
					
					$in_code=$pparams['code'];
				}
				
				$ri=new PaymentNotesItem;
				$ri->Add(array(
					'note'=>'�������� ������-������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������ - �������� �'.$item['code'],
					'pdate'=>time(),
					'user_id'=>$pay_in_id,
					'posted_user_id'=>$pparams['manager_id']
				));
				
				
				$ri->Add(array(
					'note'=>'�������� ������-������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������ - �������� �'.$item['code'],
					'pdate'=>time(),
					'user_id'=>$id,
					'posted_user_id'=>$pparams['manager_id']
				));
				
				$log->PutEntry($pparams['manager_id'], '�������� ������-�������', NULL, 689, NULL, '�������� ������-������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������ - �������� �'.$item['code'], $pay_in_id);
				
				$log->PutEntry($pparams['manager_id'], '�������� ������-�������', NULL, 277, NULL, '�������� ������-������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������ - �������� �'.$item['code'], $id);
				
			}
			
			//��� �����. ������ - ������� ����������
			if($item['is_inner_pay']==1){
				$_pni=new PaymentNotesItem;
				$_dni=new DemandNotesItem;
				$_dem=new DemandItem;
				
				$test_dem=$_dem->GetItemByFields(array('pay_id'=>$id, 'org_id'=>$item['org_id']));
				
				
				$pparams=array();
				$pparams['supplier_id']=$item['supplier_id'];
			 
				 
				//$pparams['code_id']=$item['code_id'];
				 
				$pparams['user_confirm_id']=$params['user_confirm_id'];
				$pparams['confirm_pdate']=time();
				$pparams['pdate']=time();
				$pparams['value']=$item['value'];
				$pparams['is_confirmed']=1;
				$pparams['manager_id']=$params['user_confirm_id'];
				$pparams['supplier_bdetails_id']=$item['supplier_bdetails_id'];
				$pparams['org_bdetails_id']=$item['org_bdetails_id'];
				 
				$pparams['given_no']=$item['given_no'];
				$pparams['given_pdate']=$item['given_pdate'];
				$pparams['status_id']=15; //$item['supplier_id'];
				 
				$pparams['restore_pdate']=0; //$item['supplier_id'];
				$pparams['pay_id']=$id;
				
				$pparams['inner_user_id']=$item['inner_user_id'];
				
				if($test_dem!==false){
					
					$dem_in_id=$test_dem['id'];
					$_dem->Edit($dem_in_id, $pparams,true);
					$in_code=$dem_in_id;
					 
				}else{
					 
					$pparams['org_id']=	$item['org_id'];
					
					$dem_in_id=$_dem->add($pparams);	
					$in_code=$dem_in_id;
					 
				}
				
				$di=new DemandNotesItem;
				$di->Add(array(
					'note'=>'���������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������  �'.$item['code'],
					'pdate'=>time(),
					'user_id'=>$dem_in_id,
					'posted_user_id'=>$pparams['manager_id']
				));
				
				
				$ri=new PaymentNotesItem;
				$ri->Add(array(
					'note'=>'���������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������  �'.$item['code'],
					'pdate'=>time(),
					'user_id'=>$id,
					'posted_user_id'=>$pparams['manager_id']
				));
				
				$log->PutEntry($pparams['manager_id'], '�������� ����������', NULL, 855, NULL, '���������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������  �'.$item['code'], $dem_in_id);
				
				$log->PutEntry($pparams['manager_id'], '�������� ����������', NULL, 277, NULL, '���������� �'.$in_code.' ������������� ���������� ��� ����������� ��������� ������  �'.$item['code'], $id);
			
				
				//��� ����� 1.1, 1.2, 1.3, 1.4 (8,9,10,11) - ��������� ��������� �����������-�����������
				if(
				(($item['code_id']==8)||($item['code_id']==9)||($item['code_id']==10)||($item['code_id']==11))&&
				($item['inner_user_id']!=0)
				
				){
						$_mi=new MessageItem;
						$_ui=new UserSItem;
						$_pci=new PayCodeItem;
						
						$pci=$_pci->GetItemById($item['code_id']);
						$ui=$_ui->getitembyid( $item['inner_user_id']);
						
						
						$params1=array();
						
						$message ='<div><em>������ ��������� ������������� �������������.</em></div>
											  <div><br /></div>
											  <div>���������/�� '.stripslashes($ui['name_s']).'!</div>
			<div><br /></div>		
			<div>��� ���� ��������� ��������� ������ � '.$item['code'].' �� ������ '.SecStr($pci['code'].' '.$pci['name']).' �� ����� '.$item['value'].'&nbsp;���.</div>
			<div>����������, ��������� ����������� �������� ������� �� ���� ���������� ��������, ���� �������� �������� � ����� �����������.</div>
			<div><br /></div>
			<div>� ���������, ��������� &laquo;'.SITETITLE.'&raquo;.</div>
											  
											  
											  ';
								
									
								$params1['topic']='��������� ����������� �������� �������!';
								$params1['txt']=$message;
								$params1['to_id']= $item['inner_user_id'];
								$params1['from_id']=-1; //�������������� ������� �������� ���������
								$params1['pdate']=time();
								
								$_mi->Send(0,0, $params1, false);	
				
				}
			}
			
			 
		}
		
		//������� �����������
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==0)&&($item['is_confirmed']==1)){
			$log=new ActionLog;
			if($item['supplier_id']==$item['org_id']){
				//����� ����������� ��������� ����. ������
				$_pay_in=new PayInItem;
				$_pni=new PaymentNotesItem;
				
				$test_pay_in=$_pay_in->GetItemByFields(array('out_pay_id'=>$id, 'org_id'=>$item['org_id'], 'is_confirmed'=>1));	
				if($test_pay_in!==false){
					$pparams=array();
					$pparams['user_confirm_id']=$params['user_confirm_id'];
					$pparams['confirm_pdate']=time();
					$pparams['is_confirmed']=0;
					
					$_pay_in->Edit($test_pay_in['id'], $pparams, true);
					
					$ri=new PaymentNotesItem;
					$ri->Add(array(
						'note'=>'������������� ����� ����������� �������� ������-�������� �'.$test_pay_in['code'].' ��� ������ ����������� ��������� ������ - �������� �'.$item['code'],
						'pdate'=>time(),
						'user_id'=>$test_pay_in['id'],
						'posted_user_id'=>$pparams['user_confirm_id']
					));
					
					$log->PutEntry($pparams['user_confirm_id'], '���� ����������� ������-��������', NULL, 690, NULL, '������������� ����� ����������� �������� ������-�������� �'.$test_pay_in['code'].' ��� ������ ����������� ��������� ������ - �������� �'.$item['code'], $test_pay_in['id']);
				
					$log->PutEntry($pparams['user_confirm_id'], '���� ����������� ������-��������', NULL, 278, NULL, '������������� ����� ����������� �������� ������-�������� �'.$test_pay_in['code'].' ��� ������ ����������� ��������� ������ - �������� �'.$item['code'], $id);
				}
			}
			
			//��� �����. ������ - ������. ����������
			if($item['is_inner_pay']==1){
				
				$_pni=new PaymentNotesItem;
				$_dni=new DemandNotesItem;
				$_dem=new DemandItem;
				
				$test_pay_in=$_dem->GetItemByFields(array('pay_id'=>$id, 'org_id'=>$item['org_id'], 'is_confirmed'=>1));	
				if($test_pay_in!==false){
					$pparams=array();
					$pparams['user_confirm_id']=$params['user_confirm_id'];
					$pparams['confirm_pdate']=time();
					$pparams['is_confirmed']=0;
					
					$_dem->Edit($test_pay_in['id'], $pparams, true);
					
					$ri=new DemandNotesItem;
					$ri->Add(array(
						'note'=>'������������� ����� ����������� ���������� �'.$test_pay_in['id'].' ��� ������ ����������� ��������� ������ �'.$item['code'],
						'pdate'=>time(),
						'user_id'=>$test_pay_in['id'],
						'posted_user_id'=>$pparams['user_confirm_id']
					));
					
					$log->PutEntry($pparams['user_confirm_id'], '���� ����������� ����������', NULL, 855, NULL, '������������� ����� ����������� ���������� �'.$test_pay_in['id'].' ��� ������ ����������� ��������� ������ �'.$item['code'], $test_pay_in['id']);
				
					$log->PutEntry($pparams['user_confirm_id'], '���� ����������� ����������', NULL, 278, NULL, '������������� ����� ����������� ���������� �'.$test_pay_in['id'].' ��� ������ ����������� ��������� ������  �'.$item['code'], $id);
				}
				
			}
		}
		
	}
	
	//������� �������
	public function AddPositions($current_id, array $positions){
		$_kpi=new PayForBillItem;
		
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		//var_dump($positions);
		
		foreach($positions as $k=>$v){
			
			//echo 'zzz1';
			if($v['kind']==0){
				$document_field='bill_id';	
				$_pi=new BillItem;
			}else{
				$document_field='invcalc_id';	
				$_pi=new InvCalcItem;
			}
			
			
			$kpi=$_kpi->GetItemByFields(array('payment_id'=>$v['payment_id'],$document_field=>$v[$document_field]));
			$pi=$_pi->getitembyid($v[$document_field]);
			
			//var_dump(array('payment_id'=>$v['payment_id'],$document_field=>$v[$document_field]));
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['payment_id']=$v['payment_id'];
				$add_array[$document_field]=$v[$document_field];
				
				$add_array['value']=$v['value'];
				$_kpi->Add($add_array);
				
				
				
				$log_entries[]=array(
					'action'=>0,
					'code'=>$pi['code'],
					'kind'=>$v['kind'],
					'value'=>$v['value']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array[$document_field]=$v[$document_field];
				$add_array['payment_id']=$v['payment_id'];
				
				$add_array['value']=$v['value'];
				
				$_kpi->Edit($kpi['id'],$add_array);
				
				//���� ���� ���������
				$log_entries[]=array(
					'action'=>1,
					'code'=>$pi['code'],
					'kind'=>$v['kind'],
					'value'=>$v['value']
				);
				
			}
		}
		
		
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(
				(($vv['bill_id']==$v['bill_id'])||($vv['invcalc_id']==$v['invcalc_id']))&&
				($vv['payment_id']=$v['payment_id'])
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		/*echo '<pre>';
		print_r($old_positions);
		print_r($positions);
		print_r($_to_delete_positions);
		echo '</pre>';*/
		//die();
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			
			if($v['kind']==0){
				$document_field='bill_id';	
				$_pi=new BillItem;
			}else{
				$document_field='invcalc_id';	
				$_pi=new InvCalcItem;
			}
			
			$pi=$_pi->getitembyid($v[$document_field]);
			
			$log_entries[]=array(
					'action'=>2,
					'code'=>$pi['code'],
					'kind'=>$v['kind'],
					'value'=>$v['value']
			);
			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	
	//������� �������
	public function GetPositionsArr($id){
		$kpg=new PayForBillGroup;
		$kpg->SetIdName('payment_id');
		$arr=$kpg->GetItemsByIdArr($id);
		
		return $arr;		
		
	}
	
	//�������� � ��������� ������� (14-15)
	public function ScanDocStatus($id, $old_params, $new_params){
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			$log=new ActionLog();
			$au=new AuthUser;
			$_result=$au->Auth();
			$_stat=new DocStatusItem;
			$item=$this->GetItemById($id);
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==14)){
				//����� ������� � 14 �� 15
				$this->Edit($id,array('status_id'=>15));
				
				$stat=$_stat->GetItemById(15);
				$log->PutEntry($_result['id'],'����� ������� ��������� ������',NULL,613,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ��������� ������',NULL,272,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==15)){
				$this->Edit($id,array('status_id'=>14));
				
				$stat=$_stat->GetItemById(14);
				$log->PutEntry($_result['id'],'����� ������� ��������� ������',NULL,613,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ��������� ������',NULL,272,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}
		}
	}
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=14){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
		}
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//������������� ���������
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//������ � ����������� �������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
		}
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	
	//������ � ����������� �����������
	//������ � ����������� ����������� � ���������� �������, ������ ������ ���������
	public function DocCanConfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['status_id']==15){
			
			$can=$can&&false;
			
			$reasons[]='�������� ��������';
		}
		
		if($item['status_id']==3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �����������';
		}
		
		if($item['given_pdate']==0){
			$can=$can&&false;
			$reasons[]='�� ������� �������� ����';	
			
		}elseif($item['given_pdate']>DateFromdmY(date('d.m.Y'))){
			$can=$can&&false;
			$reasons[]='�������� ���� '.date('d.m.Y',$item['given_pdate']).' ��������� �������';	
		}
		
		if($item['given_no']==''){
			$can=$can&&false;
			$reasons[]='�� ������ �������� �����';	
			
		}
		
		if(($item['pay_for_dogovor']=='')||($item['pay_for_bill']=='')){
			$can=$can&&false;
			$reasons[]='�� ������� ����� ������ �� ����� ��� �� ��������';	
			
		}
		
		if(($item['code_id']==0)){
			$can=$can&&false;
			$reasons[]='�� ������ ��� ��������� ������';	
			
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� '.$rss23;	
		}
		
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//������ � ����������� �������������� � ���������� �������, ������ ������ ������������
	public function DocCanUnConfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['status_id']==14){
			
			$can=$can&&false;
			
			$reasons[]='�������� ��������';
		}
		
		if($item['status_id']==3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �����������';
		}
		
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� '.$rss23;	
		}
		
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//�������� ������ �� ������ � �����. ������ ����������
	public function BindPayments($pay_id, $org_id){
		$item=$this->GetItemById($pay_id);
		
		if($item===false) return;
		
		$_bi=new BillItem;
		$_ai=new AccItem;
		$_bpi=new PayForBillItem;
		$log=new ActionLog;
		$au=new AuthUser;
		$_result=$au->Auth();
		
		$_pfg=new PayForBillGroup;
		$_null_pdate=$_pfg->GetNullPdate($item['supplier_id'],$org_id);
		
		//������ ����� �� ������
		$avans=0;
		$set1=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$pay_id.'"');
		$rs1=$set1->GetResult();
		$g=mysqli_fetch_array($rs1);	
		
		if((float)$item['value']>(float)$g[0]){
				
				$avans+=((float)$item['value']-(float)$g[0]);
		
		
		}
		
		if($avans>0){
			//������������ ����� �� �����. ������...
			$delta=$avans;
			
			//echo 'zzz'; die();
			$sql='select * from bill 
			where 
				is_confirmed_price=1 
				and is_incoming=1
				and status_id!=3  
				and status_id in(10, 20, 9, 2) 
				and supplier_id="'.$item['supplier_id'].'" 
				and org_id="'.$org_id.'" 
				and contract_id="'.$item['contract_id'].'"
				order by status_id desc, id asc';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();	
			for($i=0; $i<$rc; $i++){
				if($delta<=0) break;
				$f=mysqli_fetch_array($rs);		
				
				if(!$_pfg->FilterBills($f['supplier_bill_pdate'],$_null_pdate)) continue;
				
				$summ=$_bi->CalcCost($f['id']);
				$payed=$_bi->CalcPayed($f['id']); //,$pay_id);
				
				$delta_local=$summ-$payed;
				if($delta_local>0){
					if($delta_local>$delta){
						$delta_local=$delta;
					}else{
						
					}
					
					//����� �����������(/�) �� �����
					//����� ����� �� ���
					$set2=new mysqlset('select * from acceptance where is_confirmed=1 and status_id!=3 and bill_id="'.$f['id'].'" order by id asc');
					$rs2=$set2->GetResult();
					$rc2=$set2->GetResultNumRows();	
					
					$summ_by_acceptances=0;
					for($j=0; $j<$rc2; $j++){
						$g=mysqli_fetch_array($rs2);		
						$summ_by_acceptances+=$_ai->CalcCost($g['id']);
						
					}
					
					
					//���� ����� �� ������������ ��������� - �� ��������� ����� � �������� �� �����������...
					if($summ_by_acceptances>0){
						if($delta_local>$summ_by_acceptances) $delta_local=$summ_by_acceptances;	
					}
					
					
					
					
					$test=$_bpi->GetItemByFields(array('payment_id'=>$pay_id,'bill_id'=>$f['id']));
					//�������� �������� ������ � ����� ����� � ������ �����_�����
					if($test===false){
						$_bpi->Add(array('payment_id'=>$pay_id,'bill_id'=>$f['id'],'value'=>$delta_local, 'is_auto'=>1));	
					}else{
						//$delta_local-=$test['value'];
						$_bpi->Edit($test['id'],array('payment_id'=>$pay_id,'bill_id'=>$f['id'],'value'=>$delta_local, 'is_auto'=>1));	
					}
					
					$log->PutEntry($_result['id'],'���������� ������� �� ����� � ��������� ������',NULL,613,NULL,'��������� ������ � '.$item['code'].': �������� ������ �� ����� '.$f['code'].' �� ����� '.$delta_local.' ���. ',$f['id']);
					
					
					$log->PutEntry($_result['id'],'���������� ������� �� ����� � ��������� ������',NULL,272,NULL,'��������� ������ � '.$item['code'].': �������� ������ �� ����� '.$f['code'].' �� ����� '.$delta_local.' ���. ',$pay_id);
					
					
					$delta-=$delta_local;
				}
			}
			
		}
		
	}
}
?>