<?
require_once('abstractitem.php');

 

require_once('actionlog.php');
require_once('authuser.php');
 

require_once('period_checker.php');
require_once('billitem.php');

require_once('cash_to_bill_item.php');
require_once('cash_percent_item.php'); 

require_once('messageitem.php'); 
require_once('user_s_item.php'); 
require_once('paycodeitem.php'); 


//rashod nali4nyh
class CashItem extends AbstractItem{
	
	public $billpaysync;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='cash';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		 
		 
	}
	
	public function Edit($id,$params,$scan_status=false, $_result=NULL){
		$item=$this->GetItemById($id);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		if(isset($params['is_confirmed_given'])&&($params['is_confirmed_given']==1)&&($item['is_confirmed_given']==0)){
		//	$params['restore_pdate']=0;	
			//������������� ��������� % �������
			$_cpi=new CashPercentItem;
			
			/*if(($item['code_id']>=8)&&($item['code_id']<=12)){
				//������ 1� ����� ������ ����
				$datestr='01.'.sprintf("%02d",$item['month']).'.'.$item['year'];
				$cpi=$_cpi->GetActualByPdate($item['org_id'], $datestr);
			}elseif(($item['code_id']==17)||($item['code_id']==18)||($item['code_id']==62)){
				switch($item['quarter']){
					case 1:
						$month='01';
					break;
					case 2:
						$month='04';
					break;
					case 3:
						$month='07';
					break;
					case 4:
						$month='10';
					break;
					default:
						$month='01';
					break;	
				}
				
				$datestr='01.'.$month.'.'.$item['year'];
				$cpi=$_cpi->GetActualByPdate($item['org_id'], $datestr);
			
			}else{*/
				$cpi=$_cpi->GetActual($item['org_id']);	
			//}
			
			
			if($cpi!==false){
				$params['percent_id']=$cpi['id'];
				$params['percent_percent']=$cpi['percent'];	
				$params['percent_value']= $item['value']*($cpi['percent']/100);  //$cpi['percent'];	
				$params['value_total']=$item['value']+$params['percent_value'];
				
			}else{
				//����� �� �����	
				$params['percent_id']=0;
				$params['percent_percent']=0;	
				$params['percent_value']= 0;  //$cpi['percent'];	
				$params['value_total']=$item['value'];
			}
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		
		
		 
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params, $_result);
		
		//���� ���� ���. ������ - ��������� �������. ��������� 
		/*��������� (������� ����� ���������, �����, �� ����� ������ �������)
����� ����� ������: ����������, ��������� ����������� �������� ������� �� ���� ���������� ��������, ���� �������� �������� � ����� �����������.*/

		if(isset($params['is_confirmed_given'])&&($params['is_confirmed_given']==1)&&($item['is_confirmed_given']==0)){
			//require_once('messageitem.php'); 
			$_mi=new MessageItem;
			$_ui=new UserSItem;
			$_pci=new PayCodeItem;
			
			$pci=$_pci->GetItemById($item['code_id']);
			$ui=$_ui->getitembyid( $item['responsible_user_id']);
			
			
			$params1=array();
			
			$message ='<div><em>������ ��������� ������������� �������������.</em></div>
								  <div><br /></div>
								  <div>���������/�� '.stripslashes($ui['name_s']).'!</div>
<div><br /></div>		
<div>��� ���� ���������� ������ �������� �� ������� � '.$item['code'].' �� ������ '.SecStr($pci['code'].' '.$pci['name']).' �� ����� '.$item['value'].'&nbsp;���.</div>
<div>����������, ��������� ����������� �������� ������� �� ���� ���������� ��������, ���� �������� �������� � ����� �����������.</div>
<div><br /></div>
<div>� ���������, ��������� &laquo;'.SITETITLE.'&raquo;.</div>
								  
								  
								  ';
								
									
								$params1['topic']='��������� ����������� �������� �������!';
								$params1['txt']=$message;
								$params1['to_id']= $item['responsible_user_id'];
								$params1['from_id']=-1; //�������������� ������� �������� ���������
								$params1['pdate']=time();
								
								$_mi->Send(0,0, $params1, false);	
		}
		
	}
	 
	
	//�������� � ��������� ������� 
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL){
		 	
			$log=new ActionLog();
			$au=new AuthUser;
			if($_result===NULL) $_result=$au->Auth();
			$_stat=new DocStatusItem;
			$item=$this->GetItemById($id);
			
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])&&($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==1)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				 
				
				$log->PutEntry($_result['id'],'����� ������� ������� ��������',NULL,836,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])&&($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				 
				
				$log->PutEntry($_result['id'],'����� ������� ������� ��������',NULL,836,NULL,'���������� ������ '.$stat['name'],$item['id']);
			 
			}elseif(isset($new_params['is_confirmed_given'])&&isset($old_params['is_confirmed_given'])&&($new_params['is_confirmed_given']==1)&&($old_params['is_confirmed_given']==0)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>19));
				
				$stat=$_stat->GetItemById(19);
				
				
				$log->PutEntry($_result['id'],'����� ������� ������� ��������',NULL,836,NULL,'���������� ������ '.$stat['name'],$item['id']);
			
			}elseif(isset($new_params['is_confirmed_given'])&&isset($old_params['is_confirmed_given'])&&($new_params['is_confirmed_given']==0)&&($old_params['is_confirmed_given']==1)&&($old_params['status_id']==19)){
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				 
				
				$log->PutEntry($_result['id'],'����� ������� ������� ��������',NULL,836,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}
		 
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
		
		
		if($item['status_id']==19){
			
			$can=$can&&false;
			
			$reasons[]='�������� ��������';
		}
		
		if($item['status_id']==2){
			
			$can=$can&&false;
			
			$reasons[]='�������� ���������';
		}
		
		
		
		if($item['status_id']==3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �����������';
		}
		
		/*if($item['given_pdate']==0){
			$can=$can&&false;
			$reasons[]='�� ������� �������� ����';	
			
		}elseif($item['given_pdate']>DateFromdmY(date('d.m.Y'))){
			$can=$can&&false;
			$reasons[]='�������� ���� '.date('d.m.Y',$item['given_pdate']).' ��������� �������';	
		}
		*/
		/*if($item['given_no']==''){
			$can=$can&&false;
			$reasons[]='�� ������ �������� �����';	
			
		}*/
		
		
		if(($item['code_id']==0)){
			$can=$can&&false;
			$reasons[]='�� ������ ��� ��������� ������';	
			
		}
		
		if(($item['responsible_user_id']==0)){
			$can=$can&&false;
			$reasons[]='�� ������ ���������-���������� �������';	
			
		}
		
		
		//�������� ��������� ������� 
		/*if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� '.$rss23;	
		}
		*/
		
		
		//��������� �������������� ������������� ����� ��� ������ � ��������
		if(($item['kind_id']==2)||($item['kind_id']==3)){
			/*$_bill=new BillItem;
			$bill=$_bill->GetItemById($item['bill_id']);
			if($bill['is_confirmed_shipping']==0){
				$can=$can&&false;
				$reasons[]='�� ���������� �������� ������������� ����� � '.$bill['code'];
			}*/
			$_cbi=new CashToBillItem;
			$bills=$_cbi->GetBillsbyCashArr($id, $item['org_id']);
			foreach($bills as $k=>$v){
				if($v['is_confirmed_shipping']==0){
					$can=$can&&false;
					$reasons[]='�� ���������� �������� ������������� ����� � '.$v['code'];
				}
			}
			
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
		
		
		if($item['status_id']==19){
			
			$can=$can&&false;
			
			$reasons[]='�������� ��������';
		}
		
		if($item['status_id']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ���������';
		}
		
		if($item['status_id']==3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �����������';
		}
		
		
		
		//�������� ��������� ������� 
		/*if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� '.$rss23;	
		}
		*/
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	
		//������ � ����������� ����������� � ���������� �������, ������ ������ ���������
	public function DocCanConfirmGiven($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['status_id']==19){
			
			$can=$can&&false;
			
			$reasons[]='�������� ��������';
		}
		
		 
		if($item['status_id']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ���������';
		}
		
		
		
		if($item['status_id']==3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �����������';
		}
		
		
		
		
		//�������� ��������� ������� 
		/*if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� '.$rss23;	
		}
		*/
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//������ � ����������� �������������� � ���������� �������, ������ ������ ������������
	public function DocCanUnConfirmGiven($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		 
		if($item['status_id']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ���������';
		}
		
		
		 
		if($item['status_id']==2){
			
			$can=$can&&false;
			
			$reasons[]='�������� ���������';
		}
		
		if($item['status_id']==3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �����������';
		}
		
		
		
		//�������� ��������� ������� 
		/*if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='�������� ���� '.$rss23;	
		}*/
		
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	
	
	
	//������� �������
	public function AddBills($current_id, array $positions, $org_id, $result=NULL){
		$_kpi=new CashToBillItem;
		 
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$_kpi->GetBillsbyCashArr($current_id, $org_id); 
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array( 'bill_id'=>$v['bill_id'], 'cash_id'=>$current_id ));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['bill_id']=$v['bill_id'];
				$add_array['cash_id']=$current_id;
				 
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'bill_id'=>$v['bill_id'] 
				);
				
			}else{
				//++ pozicii
				/*
				$add_array=array();
				$add_array['bill_id']=$v['bill_id'];
				$add_array['cash_id']=$current_id;
				 
				 
				$_kpi->Edit($kpi['id'],$add_array );
				
			 
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��, ����, +/-, 
				
				$to_log=false;
				 
				if($kpi['bill_id']!=$add_array['bill_id']) $to_log=$to_log||true;
			 
				
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'bill_id'=>$v['bill_id'] 
				  );
				}*/
				
			}
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['bill_id']==$v['id']) 
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
			
			 $kpi=$_kpi->GetItemByFields(array( 'bill_id'=>$v['id'], 'cash_id'=>$current_id ));
			
			$log_entries[]=array(
					'action'=>2,
					'bill_id'=>$v['id'] 
			);
			
			//������� �������
			 $_kpi->Del($kpi['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
}
?>