<?
require_once('abstractitem.php');

require_once('billitem.php');
require_once('bill_in_item.php');
require_once('billnotesitem.php');
require_once('payitem.php');

require_once('actionlog.php');
require_once('authuser.php');



//������������� ������� "���� � �����������" � ����������� �� ������
class BillPaySync extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='table';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	public function CatchStatus($bill_id,/* $payment_id, */$bill_item=NULL,/* $pay_item=NULL,*/ $result=NULL){
		//echo 'checking status of bill...<br>';
		
		
		$_pi=new PayItem;
		$_bi=new BillItem;
		
		$log=new ActionLog;
		$bni=new BillNotesItem();
		
		if($bill_item===NULL) $bill_item=$_bi->getitembyid($bill_id);
		//if($pay_item===NULL) $pay_item=$_pi->GetItemById($payment_id);
		
		
		
		
		
		$perfom_check=true;
		
		//���� ���-�?
		if(!(($bill_item['is_confirmed_price']==1)&&($bill_item['is_confirmed_shipping']==1))){
			$perfom_check=$perfom_check&&false;
				
		}
		
		
		if($bill_item['is_confirmed_price']==1){
			//������ ������ ����� � ����� � �������������-������������ ������
			//echo 'restatus';
			if($bill_item['is_incoming']==1){
				$_do_bill=new BillInItem;
				$_do_bill->ScanDocStatus($bill_id,array(),array(),NULL, $result);	
			}else{
				$_do_bill=new BillItem;
				$_do_bill->ScanDocStatus($bill_id,array(),array(),NULL, $result);	
			}
		}
		
		if(($bill_item['is_in_buh']==1)&&($bill_item['user_in_buh_id']!=-1)){
			$perfom_check=$perfom_check&&false;
		}
		
		
		
		if($perfom_check){
			$summ_by_payed=$_bi->CalcPayed($bill_id);
			$summ_by_cost=$_bi->CalcCost($bill_id);
			
			if($summ_by_payed>=$summ_by_cost){
				//���� ������� �� ���� - ���������� ��	
				if($bill_item['is_in_buh']==0){
					$params=array();
					$params['is_in_buh']=1;
					$params['in_buh_pdate']=time();
					$params['user_in_buh_id']=-1;
					
					$_bi->Edit($bill_id, $params);
					
					$log->PutEntry(NULL,'�������������� ����������� ������� ����� � ����������� �� ��������� 100% ������',NULL,480,NULL,' ���� � '.$bill_item['code'].': ���������� ������� ����� � ����������� �� ��������� 100% ������',$bill_id);
					
					
					$params=array(
					'note'=>'�������������� ����������: ���������� ������� ����� � ����������� �� ��������� 100% ������',
					'is_auto'=>1,
				'pdate'=>time(),
				'user_id'=>$bill_id,
				'posted_user_id'=>0
					);
					
					$bni->Add($params);
					
				}
			}else{
				//���� ������� ���� - ����� ��
				if($bill_item['is_in_buh']==1){
					$params=array();
					$params['is_in_buh']=0;
					$params['in_buh_pdate']=0;
					$params['user_in_buh_id']=0;
					
					$_bi->Edit($bill_id, $params);
					
					$log->PutEntry(NULL,'�������������� ������ ������� ����� � ����������� �� ��������� ���������� 100% ������',NULL,481,NULL,' ���� � '.$bill_item['code'].': ����� ������� ����� � ����������� �� ��������� ���������� 100% ������',$bill_id);
					
					
					$params=array(
					'note'=>'�������������� ����������: ����� ������� ����� � ����������� �� ��������� ���������� 100% ������',
					'is_auto'=>1,
				'pdate'=>time(),
				'user_id'=>$bill_id,
				'posted_user_id'=>0
					);
					
					$bni->Add($params);
				}
			}
			
		}
		
	}
}
?>