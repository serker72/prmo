<?
require_once('billitem.php');
require_once('invcalcitem.php');
require_once('acc_positem.php');
require_once('billpospmformer.php');
require_once('acc_posgroup.php');

require_once('docstatusitem.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('maxformer.php');
require_once('rights_detector.php');
require_once('supplieritem.php');
require_once('billdates.php');

require_once('acc_sync.php');
require_once('period_checker.php');


require_once('supcontract_item.php');
require_once('supcontract_group.php');
require_once('period_checker.php');
require_once('acc_in_item.php');

require_once('payforaccgroup.php');
require_once('payforaccitem.php');

require_once('accsync.php');
require_once('acc_notesitem.php');

//����������� �������
class AccItem extends BillItem{
	public $rd;
	public $sync;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='acceptance';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
		
		$this->rd=new RightsDetector($this);
		$this->sync=new AccSync;	
	}
	
	
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		if(isset($params['given_pdate'])){
			$this->SyncPlanShipDate($code, $params['given_pdate']);	
		}
		
	/*	if(isset($params['sh_i_id'])){
			$_sh=new ShIItem;
			$_sh->ScanDocStatus($params['sh_i_id'],array(),array());	
		}
		*/
		return $code;
	}
	
	public function Edit($id,$params,$scan_status=false, $_auth_result=NULL){
		$item=$this->GetItemById($id); $log=new ActionLog;
		
		$_bi=new BillItem;
		$old_bill_summ=$_bi->CalcCost($item['bill_id']);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=6)&&($item['status_id']==6)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		
		//if(isset($params['given_pdate'])){
		
		//������������� ���� ������ �� �������� � �����	
		$this->SyncPlanShipDate($id);	
		//}
		
		//���� ���������� - �� ���������� �������� ������
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)){
			
			
			$item_actual=$this->GetItemById($id);
			$_bi=new BillItem;
			$bill=$_bi->GetItemById($item_actual['bill_id']);
			
			$to_bind_ids=$this->GetLatestAccs($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate']);
			if(count($to_bind_ids)>0){
				$this->FreeBindedPayments(NULL, $to_bind_ids, 1, $_auth_result);
			}
			
			
			$this->BindPayments($id, $item['org_id'], NULL, $_auth_result);
			
			
			$to_bind_ids=$this->GetLatestAccs($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'], array($id));
			if(count($to_bind_ids)>0){
				$this->AutoBind($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'], 	$_auth_result,array($id));
			}
			
			
			
			 
		}
		
		//���� ���������� � ���� ����� ����� ������ - ��������� �������/������������ ����������� � �������� ����
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			
			$_accsync=new AccSync2($id,  $item['org_id'],   $_auth_result);
			$_accsync->Sync();
			
		}
		
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==0)){
			//������� ����������� - �������� �������� ����� �� �������
			$this->FreeBindedPayments($id,   NULL, 1, $_auth_result);
			
			$item_actual=$this->GetItemById($id);
			$_bi=new BillItem;
			$bill=$_bi->GetItemById($item_actual['bill_id']);
			
			$to_bind_ids=$this->GetLatestAccs($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'],array($id));
			if(count($to_bind_ids)>0){
				$this->FreeBindedPayments(NULL, $to_bind_ids, 1, $_auth_result);
			}
			
			if(count($to_bind_ids)>0){
				$this->AutoBind($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'], 	$_auth_result);
			}
			//echo 'zzzzzzzzzz';
		//	var_dump($to_bind_ids);
			
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$_auth_result);
		
		//���� �� ���������� - ����� ����������� ��������� ����������
		if(($item['is_confirmed']==1)&&isset($params['is_confirmed'])&&($params['is_confirmed']==0)){
			$this->UnconfirmBindedDocuments($id);
		}
		
		
		if(isset($item['bill_id'])){
			$_sh=new BillItem;
			$_sh->ScanDocStatus($item['bill_id'],array(),array(),NULL,$_auth_result);	
		}
		
		//die();
		
	}
	
	
	//������������� ���� ������ �� �������� � �����
	public function SyncPlanShipDate($id, $pdate_shipping_plan, $item=NULL, $auth=NULL){
		if($item===NULL) $item=$this->getitembyid($id);
		
	
		
		$_log=new ActionLog;
		
		$_au=new AuthUser;
		if($auth===NULL) $auth=$_au->Auth();
		
		// var_dump($item); die();
		////������������� ���� ������ �� �������� � �����
		if(($item['is_confirmed']==1)&&($item['given_pdate']!=0)){
		  
		  $ts=new mysqlSet('select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and given_pdate>"'.$item['given_pdate'].'"');
		  //echo 'select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and given_pdate>"'.$item['given_pdate'].'"';
		  
		 // die();
		  
		  $rs=$ts->getResult();
		  $rc=$ts->getResultNumRows();
		  $f=mysqli_fetch_array($rs);
		  if($f[0]==0){
			  
			//  echo 'nu;';
			  
			  $_bi=new BillItem();
			  $bi=$_bi->GetItemById($item['bill_id']);
			  
			  $_si=new SupplierItem;
			  $supplier=$_si->GetItemById($bi['supplier_id']);
			  
			   $_sci=new SupContractItem;
			  $sci=$_sci->GetItemById($bi['contract_id']);
			  
			  
			  $_bd=new BillDates;
			  
			  $eth=$_bd->FindEthalon($item['given_pdate'],$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
			  
			  if($eth!=$bi['pdate_payment_contract']){
				$_bi->Edit($item['bill_id'], array('pdate_payment_contract'=>$eth),false,$auth);	
				
				$_log->PutEntry($auth['id'],'���������� ���� ������ ����� �� �������� ��� ���������� �������� ���� ����������',NULL, 93,NULL,'������ �������� '.date('d.m.Y', $bi['pdate_payment_contract']).', ����� �������� ���� ������ �� �������� '.date('d.m.Y', $eth), $item['bill_id']);
				 $_log->PutEntry($auth['id'],'���������� ���� ������ ����� �� �������� ��� ���������� �������� ���� ����������',NULL, 235,NULL,'������ �������� '.date('d.m.Y', $bi['pdate_payment_contract']).', ����� �������� ���� ������ �� �������� '.date('d.m.Y', $eth), $id);
			  
			  }
			 
			  
		  }
		}
	}
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from acceptance_position_pm where acceptance_position_id in(select id from acceptance_position where acceptance_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from acceptance_position where acceptance_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//������� �������
	public function AddPositions($current_id, array $positions,$change_high_mode=0,$change_low_mode=0){
		$_kpi=new AccPosItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('acceptance_id'=>$v['acceptance_id'],'position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id'], 'acceptance_in_id'=>$v['acceptance_in_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				
				$add_array=array();
				$add_array['acceptance_id']=$v['acceptance_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				$add_array['acceptance_in_id']=$v['acceptance_in_id'];
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms,$change_high_mode,$change_low_mode);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'old_quantity'=>0,
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['acceptance_id']=$v['acceptance_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				$add_array['acceptance_in_id']=$v['acceptance_in_id'];
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$change_high_mode,$change_low_mode);
				//���-��
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				
				if($to_log){
				  //���� ���� ���������
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'old_quantity'=>$kpi['quantity'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'pms'=>$v['pms']
				  );
				}
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['position_id']==$v['id'])&&($vv['komplekt_ved_id']==$v['komplekt_ved_id'])&&($vv['acceptance_in_id']==$v['acceptance_in_id'])){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//������� ��������� �������
		foreach($_to_delete_positions as $k=>$v){
			
			//��������� ������ ��� �������
			$pms=NULL;
			if($v['plus_or_minus']==1){
				$pms=array(
						'plus_or_minus'=>$v['plus_or_minus'],
						'rub_or_percent'=>$v['rub_or_percent'],
						'value'=>$v['value']
					);	
			}
			
			$log_entries[]=array(
					'action'=>2,
					'name'=>$v['position_name'],
					'old_quantity'=>$v['quantity'],
					
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$pms
			);
			
			//������� �������
			$_kpi->Del($v['p_id']);
		}
		
		
		$item=$this->getitembyid($current_id);
		if(isset($item['bill_id'])){
			$_sh=new BillItem;
			$_sh->ScanDocStatus($item['bill_id'], array(),array());	
		}
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
		//������������������ ������� �� ������ � �������������
	public function ResyncPositions($id,$change_high_mode=0,$change_low_mode=0, $_result=NULL){
		$positions=$this->GetPositionsArr($id);
		$_kpi=new AccPosItem;
		
		$item=$this->GetItemById($id);
		
		foreach($positions as $k=>$v){
			//$kpi=$_kpi->GetItemByFields(array('acceptance_id'=>$v['acceptance_id'],'position_id'=>$v['position_id']));
			
			//print_r($v); die();
			
			$_kpi->SetChainQuantity($v['acceptance_id'],$v['id'], $v['komplekt_ved_id'],$v['quantity'],$change_high_mode,$change_low_mode, $_result, $item);
		}
			
	}
	
	
	
	//��������������� �����
	public function ResyncPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL){
		
		
		$au=new AuthUser();
		if($_result===NULL) $_result=$au->Auth();
		
		$item=$this->getitembyid($id);
		$_bi=new BillItem;
		
		if($new_bill_summ===NULL) $new_bill_summ=$_bi->CalcCost($id);
		
		
		$calc_payed=$_bi->CalcPayed($item['bill_id']);
		
		//$summ_by_bill=$_bi->CalcCost($item['bill_id']); $summ_by_acc=$_bi->CalcAcc($item['bill_id']);
		
		if($new_bill_summ>$old_bill_summ) {
		  //���� ��� ���� ������ �� �����, � ����� �� ������������ ������ ����� ����� - �� ������������� ������.
		  if(($calc_payed>0)/*&&($summ_by_acc>$summ_by_bill)*/){
			  
			  //echo 'zzz'; die();
			  
			  $_bi->FreeBindedPayments($item['bill_id'], 1, $_result);
			  $_bi->BindPayments(	$item['bill_id'], $_result['org_id'], $_result);
		  }
				
		}elseif($new_bill_summ<$old_bill_summ){
		  	//��������� ����� �����, � �� ����� ���� ������
			//���� ����� ����� ������, ��� ����� ����� - ��������� ����� �����.
			if(($calc_payed>0)&&($calc_payed>$new_bill_summ)){
				$_bi->LowPayments($item['bill_id'], $_result, $old_bill_summ, $new_bill_summ,$calc_payed,$id);
			}
		}
		
	}
	
	
	//������� �������
	public function GetPositionsArr($id, $show_statiscits=true, $show_boundaries=true){
		$kpg=new AccPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,true,true,$show_statiscits, $show_boundaries);
		
		return $arr;		
		
	}
	
	
	
	//������ ��������� �� ������
	public function CalcCost($id, $positions=NULL, $changed_totals=NULL){
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions,$changed_totals);
		return $total_cost;
	}
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL, $item=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==4)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>5));
				
				$stat=$_stat->GetItemById(5);
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,93,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,219,NULL,'���������� ������ '.$stat['name'],$item['sh_i_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,235,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==5)){
				$this->Edit($id,array('status_id'=>4));
				
				$stat=$_stat->GetItemById(4);
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,93,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,219,NULL,'���������� ������ '.$stat['name'],$item['sh_i_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,235,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}
		}
		
		//$_sh=new 
	}
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=4){
			
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
			$this->Edit($id, array('status_id'=>6));	
		}
	}
	
	public function DocCanRestore($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=6){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
		}
		
		
		$can=$can&&$this->CanConfirmByPositions($id,$rss);
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		
		//$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	
	//������ � ����������� ����������� � ���������� �������, ������ ������ ���������
	public function DocCanConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� ���������';
		}
		
		if($item['given_pdate']==0){
			$can=$can&&false;
			$reasons[]='�� ������� �������� ���� �/� ';	
			
		}
		/*elseif($item['given_pdate']>DateFromdmY(date('d.m.Y'))){
			$can=$can&&false;
			$reasons[]='�������� ���� �/� '.date('d.m.Y',$item['given_pdate']).' ��������� �������';	
		}*/
		
		
		if($item['given_no']==''){
			$can=$can&&false;
			$reasons[]='�� ������ �������� ����� �/� ';	
			
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� �/� '.$rss23;	
		}
		
		
		//�������� �������� ���� - ������ ���� �� ������ ������ �������� ����������� �������� ��������
		//� ��������� ��������!!!
		$can=$can&&($this->CanConfirmByPdates($id, $item['given_pdate'], $rss3));  
		if($rss3!='') $reasons[]=$rss3;
		
		 
		
		 
		
		//�������� ����������� �����������: 1.1*(����� ��������� ������� �� ������) �.���� ������
		//��� ���-�� � ����������
		$can1=$this->CanConfirmByPositions($id,$rss,$item);
		
		$can=$can&&($this->CanConfirmByPositions($id,$rss,$item));
		
		if($rss!='') $reasons[]=$rss;
		
		
		//var_dump($this->CanConfirmByPositions($id,$rss,$item));
		
		$reason=implode(', ',$reasons);
		/*if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		} */
		
		return $can;	
	}
	
	
	
	//��������, ����� �� ��������� � ���� �������� �����
	public function CanConfirmByPdates($id, $given_pdate, &$wreason){
		$wreason=''; $reasons=array();
		$can=true;	
		
		
		
		$acc_ins=$this->GetAccInsbyPos($id);
		//print_r($acc_ins);
		//���� ����������� ��� - ������� ��� ������ ���������� - �� ��������� ������
		if(count($acc_ins)>0){
			//���� ����������� ���� - �� ����� ������ ������� - ��� ����� ������� ���� 
			//���� �������� ���� �.�. >= ���� ����
			 
			//echo date('d.m.Y', $check_pdate);
			
			
			if($acc_ins[0]['given_pdate']>$given_pdate){
				
				/*echo $acc_ins[0]['given_pdate'].' vs '.$given_pdate;
				echo date('d.m.Y',$acc_ins[0]['given_pdate']).' vs '.date('d.m.Y',$given_pdate);
			*/
			

				
				$can=$can&&false;
				
				$reasons[]='��������� �������� ���� �/� '.date('d.m.Y', $given_pdate).' ������ ���� �� ����� �������� ���� ������ �������� ����������� � '.$acc_ins[0]['id'].', �������� ����� '.$acc_ins[0]['given_no'].' �� '.date('d.m.Y', $acc_ins[0]['given_pdate']).'  ';	
			}
		}
		
		$wreason=implode(', ',$reasons);
		
		return $can;
	}
	
	
	//��������, �������� �� ������� ����������� ��� ��������������/�����������
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	

		if($item===NULL) $item=$this->getitembyid($id);
		$mf=new MaxFormer;
		
		
		  
		$_sh=new BillItem;
		$ship=$_sh->GetItemById($item['bill_id']);
		if(($ship['is_confirmed_shipping']==1)&&($ship!==false)){
		  
		  
		  $ship_positions=$_sh->GetPositionsArr($item['bill_id'],false,$ship);
		  $positions=$this->GetPositionsArr($id,false,false);
		  
		  //��������� ������� ����������
		  //������� �� ����������� ���. �����
		  //����� �������� �� ����������� �� ������������!
		  //���� ���������� - �� ������� � ������ ������
		  
		  foreach($positions as $k=>$v){
			  if(!$this->PosInSh($v,$ship_positions,$find_pos)){
				  $can=$can&&false;
				  $reasons[]='� ������������ ����� �� ������� ������� '.SecStr($v['position_name']);	
				  continue;	
			  }
			  
			 //�������.. ������� ����������
			  $vsego=$find_pos['quantity'];
			  
			  $free=$mf->MaxForAcc($item['bill_id'], $v['id'], $id, $v['komplekt_ved_id']) ;
			  
						  
			  if($v['quantity']>$free*PPUP){
				  //����������
				  $can=$can&&false;
				  $reasons[]='���������� ������� '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).', ������ �'.$v['komplekt_ved_id'].' ��������� ��������� �� ����� ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
				  continue;		
			  }
			  
			  
			  //������ ���� ��� �� ������!!!
			  if($v['is_usl']==0){
				  $free=$mf->MaxForAccByAccIn($item['bill_id'],  $v['id'],  $id,NULL,$v['storage_id'],$v['sector_id'], $v['komplekt_ved_id'], $v['acceptance_in_id']);
				  if($v['quantity']>$free*PPUP){
					  //����������
					  $can=$can&&false;
					  $reasons[]='���������� ������� '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).', ������ �'.$v['komplekt_ved_id'].' ��������� ��������� �� ��������� ������������ ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
					  continue;		
				  }
			  }
			  //$_sh_r->InAcc($v['id'],$item['bill_id'],'',
		  }
		}else{
			$can=$can&&false;
				  $reasons[]='�� ��������� ���� � '.$ship['code'];	
				  
		}
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	
	
	//������ � ����������� ������ ����������� � ���������� �������, ������ ������ �� ���������
	public function DocCanUnConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ���������';
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss1,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� �/� '.$rss1;	
		}
		
		
		
		//���� �� �������� ���������� ���� �������� +/- �� �� ������ ����� �����������,
		//���������� ������ �������� ��������, �� �������� � �������
		$sql='select distinct c.id, c.code, c.status_id, st.name as status_name from cash as c
			left join document_status as st on st.id=c.status_id			
			where c.is_confirmed_given=1
			and c.id in(
				select distinct cb.cash_id from cash_bill_position as cb
				inner join bill_position as bp on bp.id=cb.bill_position_id
				inner join bill as b on b.id=bp.bill_id
				
				inner join acceptance as a on a.bill_id=b.id
				
				inner join acceptance_position as ap on (ap.acceptance_id=a.id and bp.position_id=ap.position_id and bp.komplekt_ved_id=ap.komplekt_ved_id )
				 
				where  ap.acceptance_id="'.$id.'"	
						
				 
			)
			order by c.code asc
			  ';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$can=$can&&false;
			$rss2='�� �������� ������������� ���������� ����� ���� �������� +/- �������� ����������: ';
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$rss2.=' ������ �������� � '.$f['code'].', ������ '.$f['status_name'].'; ';
			}
			$rss2.=' ��� ������ ����������� ���������� ���������� ����� ����������� ������ ��������� �������� ��������';
			
			$reasons[]=$rss2;
		}
		
		
		
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		return $can;	
	
	}
	
	
	//���� �� ������ ������� � ����. �� �������. ���� - ������� ���. ���� �� ��, ��� - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if($v['id']==$acc_position['id']){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	//���� �� ������ � �����������
	public function HasUsl($id, $positions=NULL){
		
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false,false);
		
		$has=false;
		foreach($positions as $k=>$v){
			if(($v['is_usl']==1)||($v['is_semi_usl']==1)){
				$has=true;
				break;	
			}
		}
			
		return $has;
	}
	
	
	//���� �� ������ � �����������
	public function HasTov($id, $positions=NULL){
		
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false,false);
		
		$has=false;
		foreach($positions as $k=>$v){
			if(($v['is_usl']!=1)&&($v['is_semi_usl']!=1)){
				$has=true;
				break;	
			}
		}
			
		return $has;
	}
	
	
	//���� �� +/- � ������������ �����
	public function ParentBillHasPms($acc_id, $acc_item=NULL){
		
		if($acc_item===NULL) $acc_item=$this->getitembyid($acc_id);
		
		$sql='select count(*) from bill_position_pm where bill_position_id in(select id from  bill_position where bill_id="'.$acc_item['bill_id'].'")';	
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		//var_dump((int)$f[0]>0);
		
		return ((int)$f[0]>0);
			
	}
	
	
	
	
	//�������� ����� ������� �������� ���� �� ��������� ������������
	public function GetGivenPdate($bill_id,  $acc_ids=NULL){
		$flt='';
		if(($acc_ids!==NULL)&&(count($acc_ids)>0)) $flt=' and id in('.implode(', ',$acc_ids).') ';
		
		$sql='select given_pdate from acceptance where is_incoming=1 '.$flt.' and is_confirmed=1 and bill_id in(select id from bill where out_bill_id="'.$bill_id.'") limit 1';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		//$rc=$set->getresultnumrows();
		
		$f=mysqli_fetch_array($rs);
		
		return $f[0];
		
		
	}

	//�������� ������ ����������� �� ���. ����� (����. �����������)
	public function GetAccIns($bill_id, $acc_ids=NULL){
		$flt='';
		if(($acc_ids!==NULL)&&(count($acc_ids)>0)) $flt=' and id in('.implode(', ',$acc_ids).') ';
		
		$arr=array();
		$sql='select id, given_no, given_pdate from acceptance where is_incoming=1 and is_confirmed=1 and bill_id in(select id from bill where out_bill_id="'.$bill_id.'") '.$flt.' order by given_pdate';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//�������� ������ ����������� �� �������� (����� ��������)
	public function GetAccInsbyPos($id){
		$arr=array();	
		$sql='select distinct a.id, a.given_pdate, a.given_no from acceptance_position as p
		inner join acceptance as a on a.id=p.acceptance_in_id and p.acceptance_in_id<>0
		where a.is_confirmed=1 
		and p.acceptance_id="'.$id.'"
		order by a.given_pdate desc
		';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	
	
//****************************************** ���� ������������� ���� ����� ***************************************	
	
	
	
	//��������� ���������� � ������� � �������
	public function BindPayments($id,$org_id, $item=NULL, $_result=NULL,  $period_begin=NULL, $period_end=NULL){
		if($item===NULL) $acc=$this->GetItemById($id);
		else $acc=$item;
		
		if($acc===false) return;
		$_bpi=new PayForAccItem;
		$_pfg=new PayForAccGroup;
		//$_bpf=new BillPosPMFormer;
		
		$_bill=new BillItem;
		$bill=$_bill->GetItemById($acc['bill_id']);
		 $_inv=new InvCalcItem;
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		
		$opl_codes=$_pfg->GetAvans($bill['supplier_id'], $org_id, $acc['given_pdate'], $id, $avans, $opl_ids,  $inv_ids,NULL, $period_begin, $period_end);
		
		$do_reverse_pdate=false; 
		
		//echo '���������� '.$id.' '.$acc['given_no'].' ������ �����: '.$avans.'<br>';
		
		if(((count($opl_ids)==0)&&(count($inv_ids)==0))||($avans<=0)){
			//��������� �������� "��������" ����� �� ����� ������� ����������
			
			
				
			$opl_codes=$_pfg->GetAvans($bill['supplier_id'], $org_id, $acc['given_pdate'], $id, $avans, $opl_ids,  $inv_ids,NULL, $period_begin, $period_end,true);
			
			$do_reverse_pdate=true;
			
			//echo '���������� '.$id.' '.$acc['given_no'].' �������� �����: '.$avans.'<br>';
		}
		 
		 
		 
		  
		if((count($opl_ids)==0)&&(count($inv_ids)==0)) return;
		if($avans<=0) return;
		
		
		
		$total_cost=$this->CalcCost($id);   
		
		$sum_by_bill=$_pfg->SumByAcc($id);
		if($total_cost<=$sum_by_bill) return;
		
		$delta=$total_cost-$sum_by_bill;
		
		
		//����� �� �����
		if(count($inv_ids)>0){
			
			$period_flt='';
			//if(($period_begin!==NULL)&&($period_end!==NULL)) $period_flt=' and  (invcalc_pdate between "'.$period_begin.'" and   "'.$period_end.'")';
			
			
			$pdate_flt='';
			if($do_reverse_pdate) $pdate_flt=' and invcalc_pdate>="'.$acc['given_pdate'].'" ';
			else $pdate_flt=' and invcalc_pdate<"'.$acc['given_pdate'].'" ';
			
			$sql='select * from invcalc where 
				is_confirmed_inv=1 
				and supplier_id="'.$bill['supplier_id'].'" 
				and org_id="'.$org_id.'" 
				and id in('.implode(', ',$inv_ids).') 
				/*and invcalc_pdate<"'.$acc['given_pdate'].'"*/
				'.$pdate_flt.'
				'.$period_flt.'
				
				';
		 
			//echo $sql; die();
			
			$set=new mysqlset($sql);
			
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  $real_debt_stru=$_inv->FindRealDebt($f['id'],$f);
			  $f['real_debt']=$real_debt_stru['real_debt'];
			  $f['real_debt_id']=$real_debt_stru['real_debt_id'];
			  
			 // echo 'zz';
			  if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			  
			  $set1=new mysqlset('select sum(value) from payment_for_acceptance where invcalc_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			   //���� ��� ������ ��� � ����� - �� �� �������� ��������� � value (��� ������� �� ������)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_acceptance where invcalc_id="'.$f['id'].'" and acceptance_id="'.$id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  
			  if((float)$f['real_debt']>(float)$g[0]){
				  
				  $delta_local=((float)$f['real_debt']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('invcalc_id'=>$f['id'],'acceptance_id'=>$id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('invcalc_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('invcalc_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  if(($_result['id']!==NULL)&&($_result['id']!=0)){
					    //$log->PutEntry($_result['id'],'������������� ���� � ���� �������� ������ �� �����������',NULL,235,NULL,'��� � '.$f['code'].' ����������� �� ���������� '.$acc['id'].' �� ����� '.($delta_local+$by_bill).' ���. ',$id);
				  
				  
				  		//$log->PutEntry($_result['id'],'������������� ���� � ���� �������� ������ �� �����������',NULL,452,NULL,'��� � '.$f['code'].': ����������� �� ���������� '.$acc['id'].' �� ����� '.($delta_local+$by_bill).' ���. ',$f['id']);
						
				  }
				  
				  if($delta==0) break;	
			  }
			  
		  }	
			
		}
		
		
		
		if($delta==0) return;
		
		//����� �� �������
		if(count($opl_ids)>0){
			
			//echo '<br>�������� ������ '.implode(', ', $opl_ids);
			
			$period_flt=''; 
			
			
			$pdate_flt='';
			if($do_reverse_pdate) $pdate_flt=' and given_pdate>="'.$acc['given_pdate'].'" ';
			else $pdate_flt=' and given_pdate<"'.$acc['given_pdate'].'" ';
			
			$sql='select * from payment
		   where 
		   	is_confirmed=1 
			and is_incoming=1 
			/*and contract_id="'.$acc['contract_id'].'"*/
			and supplier_id="'.$bill['supplier_id'].'" 
			/*and given_pdate<"'.$acc['given_pdate'].'"*/
			'.$pdate_flt.'
			and org_id="'.$org_id.'" 
			and id in('.implode(', ',$opl_ids).')
			'.$period_flt.'
			';
		  
		//  echo $sql.'<br>'; die();	
			
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  //echo 'zz';
			  
			  $set1=new mysqlset('select sum(value) from payment_for_acceptance where payment_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			  
			  //���� ��� ������ ��� � ����� - �� �� �������� ��������� � value (��� ������� �� ������)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_acceptance where payment_id="'.$f['id'].'" and acceptance_id="'.$id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  if((float)$f['value']>(float)$g[0]){
				  
				  $delta_local=((float)$f['value']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('payment_id'=>$f['id'],'acceptance_id'=>$id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('payment_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1,'is_shown'=>(int)!$do_reverse_pdate));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('payment_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1,'is_shown'=>(int)!$do_reverse_pdate));	
				  }
				  
				  //echo '<br>���������� ���������� '.$id.' � ������ '.$f['id'];
				  if(($_result['id']!==NULL)&&($_result['id']!=0)){ 
					  //$log->PutEntry($_result['id'],'������������� �������� ������ �� �����������',NULL,235,NULL,'�������� ������ � '.$f['code'].': ������������ �� ���������� '.$acc['id'].' �� ����� '.($delta_local+$by_bill).' ���. ',$id);
					  
					  
					  //$log->PutEntry($_result['id'],'������������� �������� ������ �� �����������',NULL,683,NULL,'�������� ������ � '.$f['code'].': ������������ �� ���������� '.$acc['id'].' �� ����� '.($delta_local+$by_bill).' ���. ',$f['id']);
				   }
				  
				  if($delta==0) break;	
			  }
			  
		  }
		}
		
	}
	
	//�������� ������� ������������ ����� (��� ������ ����������� ����������, ��� ����������� ���������� ������ ������)
	public function AutoBind($supplier_id, $org_id, $given_pdate,  $_result=NULL, $except_ids=NULL, $period_begin=NULL, $period_end=NULL){
	//($id,$org_id, $item=NULL, $_result=NULL){
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		$acc_ids=$this->GetLatestAccs($supplier_id, $org_id, $given_pdate, $except_ids,  $period_begin, $period_end);
		foreach($acc_ids as $k=>$acc_id){
			//echo '<br>���������� � ����������� '.$acc_id;
			
			//� 2 �������
			$this->BindPayments($acc_id,$org_id,  NULL, $_result,$period_begin, $period_end);
			
			//$this->BindPayments($acc_id,$org_id,  NULL, $_result,$period_begin, $period_end);
		}
		
	}
	
	//��������� ������ ����� ������� ���������� �� ������� �����������
	public function GetLatestAccs($supplier_id, $org_id, $given_pdate, $except_ids=NULL, $period_begin=NULL, $period_end=NULL){
		$acc_ids=array();
		
		$period_flt='';
		if(($period_begin!==NULL)&&($period_end!==NULL)) $period_flt=' and  (a.given_pdate between "'.$period_begin.'" and   "'.$period_end.'")';
		
		$except_flt='';
		if($except_ids!==NULL) $except_flt=' and a.id not in('.implode(', ',$except_ids).')';
		
		$sql='select a.id from acceptance as a
		inner join bill as b on a.bill_id=b.id
		where 
			a.org_id="'.$org_id.'" 
			and a.given_pdate>="'.$given_pdate.'"
			and a.is_incoming=0
			and b.supplier_id="'.$supplier_id.'"
			and a.is_confirmed=1
			'.$period_flt.'
			'.$except_flt.'
		order by
			a.given_pdate asc, a.id asc';
		
		/*if($supplier_id==36) {
			echo  $sql."<br>";
			echo date('d.m.Y H:i:s ', $period_begin);
			echo date('d.m.Y H:i:s ', $period_end);
		}*/
			
		$set2=new mysqlset($sql);
		$rs2=$set2->GetResult();
		$rc2=$set2->GetResultNumRows();
		for($i=0; $i<$rc2; $i++){
			$f=mysqli_fetch_array($rs2);
			$acc_ids[]=$f['id'];	
		}
		
		return $acc_ids;
	}
	
	
	//������� �� ����������(��) ��������� �������� ������
	public function FreeBindedPayments($acc_id=NULL, $acc_ids=NULL, $is_auto=0, $_result=NULL){
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_pi=new PayInItem;
		
		$_pb=new PayForAccItem;
		 
		if(($acc_id!==NULL)||($acc_ids!==NULL)){
			
			$acc_flt='';
			if($acc_id!==NULL) $acc_flt=' pb.acceptance_id="'.$acc_id.'" ';
			elseif($acc_ids!==NULL) $acc_flt=' pb.acceptance_id in('.implode(', ',$acc_ids).') ';
			
			$auto_flt='';
			//if($is_auto==1) $auto_flt=' and pb.is_auto=1 ';
			
			$sql='select distinct pb.payment_id, pb.id, pb.acceptance_id,  p.code, pb.value
						 from payment_for_acceptance as pb 
							inner join payment as p on p.id=pb.payment_id
							inner join acceptance as b on pb.acceptance_id=b.id
			where  '.$acc_flt.'  /*and p.is_confirmed=1*/ '.$auto_flt;
			
			
		//	echo $sql.'<br>';
			
			$set=new mysqlSet($sql);
			
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				//echo '������ ������ '.$f['payment_id'].' '.$f['code'].'<br>';
				
				$_pb->Del($f['id']);
				
				 if(($_result['id']!==NULL)&&($_result['id']!=0)){ 
					//$log->PutEntry($_result['id'],'�������� ������������� �������� ������ �� �����������',NULL,235,NULL,'�������� ������ � '.$f['code'].': ������� �� ���������� '.$f['acceptance_id'].' �� ����� '.$f['value'].' ���. ',$f['acceptance_id']);
					
					//$log->PutEntry($_result['id'],'�������� ������������� �������� ������ �� �����������',NULL,683,NULL,'�������� ������ � '.$f['code'].': ������� �� ���������� '.$f['acceptance_id'].' �� ����� '.$f['value'].' ���. ',$f['payment_id']);
				 }
				
				
			}
			
			
			//������ ������� �� ���. �����
			$sql='select distinct pb.invcalc_id, pb.id, pb.acceptance_id, p.code, pb.value
						 from payment_for_acceptance as pb 
							inner join invcalc as p on p.id=pb.invcalc_id
							inner join acceptance as b on pb.acceptance_id=b.id
			where '.$acc_flt.'  /*and p.is_confirmed_inv=1 */'.$auto_flt;
			$set=new mysqlSet($sql);
			
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$_pb->Del($f['id']);
				
				 if(($_result['id']!==NULL)&&($_result['id']!=0)){ 
				
					//$log->PutEntry($_result['id'],'�������� ������������� ������������������� ���� �� �����������',NULL,235,NULL,'��� � '.$f['code'].': ������ �� ���������� '.$f['acceptance_id'].' �� ����� '.$f['value'].' ���. ',$f['acceptance_id']);
					
					//$log->PutEntry($_result['id'],'�������� ������������� ������������������� ���� �� �����������',NULL,452,NULL,'��� � '.$f['code'].': ������ �� ���������� '.$f['acceptance_id'].' �� ����� '.$f['value'].' ���. ',$f['invcalc_id']);
				 }
				 
			}	
		}
		
		
	}
	
	
		//������ ��������� ���. ���������� ��� ������ ���.
	public function GetBindedDocumentsToUnconfirm($id, $item=NULL){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		
		  
		
		 
		
		  //���� ��� ������� ����������� - ��������� ������� (�����������, ����������)
		  if($item===NULL) $item=$this->getitembyid($id);
		  if($item['is_leading']==1){
			  
			 $sql1='select  p.*, s.name from acceptance as p
						   left join document_status as s on p.status_id=s.id
				 where 
					   
					  p.is_incoming=1
					  and p.is_confirmed=1 
					  and is_leading=0
					  and leading_acceptance_id="'.$id.'"
				  order by id';
				
				
				// echo $sql1.'<br>';
				  $set1=new mysqlset($sql1);
				  $rs1=$set1->getResult();
				  $rc1=$set1->getResultNumRows();
				  
				  for($i=0; $i<$rc1; $i++){
					 $f=mysqli_fetch_array($rs1);  
					 $can=$can&&false;
					 
						  $reasons[]=' ����������� � '.$f['id'].' � ���� ��������� �����������, ������ ���������: '.$f['name'];	
						  
						  //����� ��� ���������� �����������	
						   
						  $sql2='select p.*, s.name from acceptance as p
						   left join document_status as s on p.status_id=s.id
						   where 
						   p.is_incoming=0 
						   and p.is_confirmed=1 
						   and p.bill_id in(select out_bill_id from acceptance where id="'.$f['id'].'")
						   ';
						   
						 //  echo $sql2;
						  $set2=new mysqlset($sql2);
						  $rs2=$set2->GetResult();
						  $rc2=$set2->GetResultNumRows();
						  for($i1=0; $i1<$rc2; $i1++){
							 $f1=mysqli_fetch_array($rs2);  
							 $can=$can&&false;
							 
								  $reasons[]=' ���������� � '.$f1['id'].' � ���� ��������� �����������, ������ ���������: '.$f1['name'];	
							  
						  }
					  
				  }
				  if(count($reasons)>0) $reason.=" ";
				  $reason.=implode(', ',$reasons);
				  
				  
				  
				  
				 
				  
		  }
		  
		  
		
	
		return $reason;
	}
	
	public function UnconfirmBindedDocuments($id, $item=NULL, $_result=NULL){
		
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		if($item===NULL) $item=$this->getitembyid($id);
	    
		$_ani=new AccNotesItem;
		
		 
		 
		if($item['is_leading']==1){
			$_opf=new OpfItem;
		
			$_org=new SupplierItem; 
			$org=$_org->GetItemById($item['org_id']); $opf=$_opf->GetItemById($org['opf_id']);
		
		//
			  
			 $_stat=new DocStatusItem;
			$stat=$_stat->GetItemById(4); 
			$_acc_in=new AccInItem;
			 $sql1='select * from acceptance where 
					   
					   is_incoming=1
					  and is_confirmed=1 
					  and is_leading=0
					  and leading_acceptance_id="'.$id.'"
				  order by id';
				  
				//echo $sql1;
				  
				  //echo $sql1;
				  $set1=new mysqlset($sql1);
				  $rs1=$set1->getResult();
				  $rc1=$set1->getResultNumRows();
				  
				  for($i=0; $i<$rc1; $i++){
					
						$f=mysqli_fetch_array($rs1);  
		  				
						$new_org=$_org->GetItemById($f['org_id']); $new_opf=$_opf->GetItemById($new_org['opf_id']);
						
		 				$_acc_in->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>4));
					
			
						$log->PutEntry($_result['id'],'������ ����������� ����������� � ��������� ����������� � ����� �� ������� ����������� ����������',NULL,672,NULL,'������������� ����� ����������� ����������� � '.$f['id'].' � ��������� ����������� '.$new_opf['name'].' '.$new_org['full_name'].' � ����� �� ������� ����������� ���������� � '.$id.' � ����������� '.$opf['name'].' '.$org['full_name'].', ����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
						
						$notes_params=array();
						$notes_params['pdate']=time();
						$notes_params['user_id']=$f['id'];
						$notes_params['note']=SecStr('������������� ����� ����������� ����������� � '.$f['id'].' � ��������� ����������� '.$new_opf['name'].' '.$new_org['full_name'].' � ����� �� ������� ����������� ���������� � '.$id.' � ����������� '.$opf['name'].' '.$org['full_name']);
						$notes_params['posted_user_id']=$_result['id'];
						$notes_params['is_auto']=1;
						$_ani->Add($notes_params);
					
					
					//����� ��� ���������� �����������	
					 //����� ��� ���������� �����������	
						   
						  $sql2='select p.*, s.name from acceptance as p
						   left join document_status as s on p.status_id=s.id
						   where p.is_incoming=0 and p.is_confirmed=1 and p.bill_id in(select out_bill_id from acceptance where id="'.$f['id'].'")
						   ';
						  $set2=new mysqlset($sql2);
						  $rs2=$set2->GetResult();
						  $rc2=$set2->GetResultNumRows();
						  for($i2=0; $i2<$rc2; $i2++){
							 $f1=mysqli_fetch_array($rs2);  
							 
							 $this->Edit($f1['id'], array('is_confirmed'=>0, 'status_id'=>4));
							 
							 $log->PutEntry($_result['id'],'������ ����������� ���������� � ��������� ����������� � ����� �� ������� ����������� ����������',NULL,241,NULL,'������������� ����� ����������� ���������� � '.$f1['id'].' � ��������� ����������� '.$new_opf['name'].' '.$new_org['full_name'].' � ����� �� ������� ����������� ���������� � '.$id.' � ����������� '.$opf['name'].' '.$org['full_name'].', ���������� � '.$f1['id'].': ���������� ������ '.$stat['name'],$f1['id']);
							 
							 $notes_params=array();
							$notes_params['pdate']=time();
							$notes_params['user_id']=$f1['id'];
							$notes_params['note']=SecStr('������������� ����� ����������� ���������� � '.$f1['id'].' � ��������� ����������� '.$new_opf['name'].' '.$new_org['full_name'].' � ����� �� ������� ����������� ���������� � '.$id.' � ����������� '.$opf['name'].' '.$org['full_name']);
							$notes_params['posted_user_id']=$_result['id'];
							$notes_params['is_auto']=1;
							$_ani->Add($notes_params);
						
							  
						  }
					  
				  }
				 
		  }
		 
		
		 
	}
	
	
}
?>