<?
require_once('abstractitem.php');
require_once('kppositem.php');
require_once('billpospmformer.php');
require_once('kpposgroup.php');
require_once('docstatusitem.php');

require_once('trust_group.php');

require_once('acc_group.php');
require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('payforbillitem.php');
require_once('payitem.php');
require_once('pay_in_item.php');
require_once('invcalcitem.php');
require_once('payforbillgroup.php');
require_once('payforbill_in_group.php');

//require_once('komplitem.php');
require_once('period_checker.php');
require_once('kpnotesitem.php');


require_once('invcalcitem.php');

require_once('invcalcnotesitem.php');
require_once('paynotesitem.php');

//������������ �����������
class KpItem extends AbstractItem{

	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
	}
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from kp_position_pm where kp_position_id in(select id from kp_position where kp_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from kp_position where kp_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		//�� ������������� ����������� 1 ���.
		if(isset($params['is_confirmed_price'])&&($params['is_confirmed_price']==1)&&($item['is_confirmed_price']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		
		AbstractItem::Edit($id, $params);
		
		//$this->billpaysync->CatchStatus($
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	
	//������� �������
	public function AddPositions($current_id, array $positions,$can_change_cascade=false, $check_delta_summ=false, $result=NULL,$bill=NULL){
		$_kpi=new KpPosItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		if($bill===NULL) $bill=$this->getitembyid($current_id);
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id,true,$bill);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'kp_id'=>$v['kp_id'],
			'position_id'=>$v['position_id'], 
			'pl_position_id'=>$v['pl_position_id'],
			'pl_discount_id'=>$v['pl_discount_id'],
			'pl_discount_value'=>$v['pl_discount_value'],
			'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent']
			
			));
			
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['kp_id']=$v['kp_id'];
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'price_f'=>$v['price_f'],
					'price_pm'=>$v['price_pm'],
					'pl_position_id'=>$v['pl_position_id'],
					'pl_discount_id'=>$v['pl_discount_id'],
					'pl_discount_value'=>$v['pl_discount_value'],
					'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['pl_position_id']=$v['pl_position_id'];
				$add_array['pl_discount_id']=$v['pl_discount_id'];
				$add_array['pl_discount_value']=$v['pl_discount_value'];
				$add_array['pl_discount_rub_or_percent']=$v['pl_discount_rub_or_percent'];
				
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_f']=$v['price_f'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				
				
				
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��, ����, +/-, 
				
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['pl_discount_id']!=$add_array['pl_discount_id']) $to_log=$to_log||true;
				if($kpi['pl_discount_value']!=$add_array['pl_discount_value']) $to_log=$to_log||true;
				if($kpi['pl_discount_rub_or_percent']!=$add_array['pl_discount_rub_or_percent']) $to_log=$to_log||true;
				
				if($kpi['price_f']!=$add_array['price_f']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				if($kpi['price_pm']!=$add_array['price_pm']) $to_log=$to_log||true;
				if($kpi['total']!=$add_array['total']) $to_log=$to_log||true;
				
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
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
			//$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent']);
			
			foreach($positions as $kk=>$vv){
				if(($vv['pl_position_id']==$v['pl_position_id'])
				&&($vv['position_id']==$v['position_id'])
				&&($vv['pl_discount_id']==$v['pl_discount_id'])
				&&($vv['pl_discount_value']==$v['pl_discount_value'])
				&&($vv['pl_discount_rub_or_percent']==$v['pl_discount_rub_or_percent'])
				){
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
					 'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'price_f'=>$v['price_f'],
					  'price_pm'=>$v['price_pm'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'pl_discount_id'=>$v['pl_discount_id'],
					  'pl_discount_value'=>$v['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>$v['pl_discount_rub_or_percent'],
					'pms'=>$pms
			);
			
			//������� �������
			$_kpi->Del($v['p_id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	//������� �������
	public function GetPositionsArr($id,$show_statistics=true, $bill=NULL){
		$kpg=new KpPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$show_statistics,$bill);
		
		return $arr;		
		
	}
	
	
	
	//������ ��������� �� ������
	public function CalcCost($id, $positions=NULL){
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions);
		return round($total_cost,2);
	}
	
	
	
	//�������� ����������� ��������
	public function CanDelete($id, &$reason){
		$can_delete=true;
		
		$reason='';
		
		$itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0))) {
			$reason.='������������ ����������� ����������';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		
		return $can_delete;
	}
	
	
	
	
	
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed_price'])&&isset($old_params['is_confirmed_price'])){
			
			
			
			if(($new_params['is_confirmed_price']==1)&&($old_params['is_confirmed_price']==0)&&($old_params['status_id']==1)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������� �����������',NULL,701,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
			}elseif(($new_params['is_confirmed_price']==0)&&($old_params['is_confirmed_price']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==9)||($old_params['status_id']==10))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� ������������� �����������',NULL,701,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
			}
		}else{
			//��������� �������� �� 2-9, 9-2, 9-10, 10-9
		  
		 
			
		}
		
		//�������� �������� ����� ������� ������
		//�������� ��� ������ �� �����
		//��������� �� � ��� ������ - ������� ��������
		/*$_ki=new KomplItem;
		$sql='select distinct komplekt_ved_id from bill_position where bill_id="'.$id.'"';
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//print_r($f);
			$_ki->ScanDocStatus($f['komplekt_ved_id'],array(),array(), NULL,$_result);	
		}*/
		//die();
	}
	
	
	
	
	//������ � ���������� ������� �� ����������� ������������
	public function CheckDeltaPositions($id){
		$res=true;
		
		
	
		
		return $res;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
		
		 
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	//������������� ���������
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//������ ��������� �� �������������� �� ��� ���������� ��� �����������������
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		
	}
	
	
	
	//�������� ������ ��������� �����
	public function GetBindedPayments($bill_id,&$summ){
		$summ=0;
		$names=array();	
		
		
		return implode(', ',$names);
	}
	
	public function GetBindedPaymentsFull($bill_id){
		$summ=0;
		$alls=array();	
		
		
		
		
		return $alls;
	}
	
	//������� ������� �� ��������� �����
	public function FreeBindedPayments($bill_id, $is_auto=0, $_result=NULL){
		
		
		
	}
	
	//��������� ���� � ������� � �������
	public function BindPayments($bill_id,$org_id, $_result=NULL){
		
		
	}
	
	
	
	
	
	
	
	
	//�������� ���� �� ��������� � �������� ������
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		//var_dump($item);
		//echo $item['supplier_bill_pdate'];
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $rss=' �������� ���� ����. ����������� '.$rss23;
			  //echo'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';	
		  }
		  
		
		return $can;			
	}
	
	
	//������ � ���� ������ ��� ���
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_price']!=1){
			
			$can=$can&&false;
			$reasons[]='������������ ����������� �� ����������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  
		   //�������� ��������� ������� 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' �������� ���� ����. ����������� '.$rss23;	
		  }
		   $reason.=implode(', ',$reasons);
		
		  	
		  
		}
		
		return $can;
	}
	
	//������ � ���� ��� ���
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_price']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������������ ����������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
			//�������� ��������� ������� 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]=' �������� ���� ����. ����������� '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 
			 
			 //�������� �� ����� ������� 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} 
		}
		
		return $can;
	}
	
	
	
	//��������, �������� �� ������� ����� ��� ��������������/�����������
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//���� �� ������ ������� � ����. �� ��������. ���� - ������� ���. ���� �� ��, ��� - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if(($v['position_id']==$acc_position['id'])&&($v['komplekt_ved_id']==$acc_position['komplekt_ved_id'])){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	
	
	
	public function HasShsorAccs($id){
		 
		  $can=false;
		 
		
		  
		  return $can;	
	}
	
	
	
	
	
	//����� ��� ������������ �� ������������
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		
	}
	
	
	
	
	//������������ ������������ ����������� �����-��� � ��������
	public function ScanEq($id, array $args, &$output, $sh=NULL, $express_scan=false, $continue_message=".\n���������� ������������ ������ �������?"){
		
		
		return array();
	}
	
	
	
	
	
	
	
	
	
	
	//�������� ����������� �������������� kol-va �������
	public function CanEditQuantities($id, &$reason, $itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='������������ ����������� ����������';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
	}
	
	
	
	
		
		
	
	
	//���������� ����� ����������� � ����� ����� ��� ���������� ������ �����
	public function LowPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL, $calc_payed=NULL, $actor_id=NULL){
		
		
	}
	
	
}
?>