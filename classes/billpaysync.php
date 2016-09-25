<?
require_once('abstractitem.php');

require_once('billitem.php');
require_once('bill_in_item.php');
require_once('billnotesitem.php');
require_once('payitem.php');

require_once('actionlog.php');
require_once('authuser.php');



//синхронизация галочки "счет в бухгалтерии" в зависимости от оплаты
class BillPaySync extends AbstractItem{
	
	//установка всех имен
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
		
		//счет утв-н?
		if(!(($bill_item['is_confirmed_price']==1)&&($bill_item['is_confirmed_shipping']==1))){
			$perfom_check=$perfom_check&&false;
				
		}
		
		
		if($bill_item['is_confirmed_price']==1){
			//меняем статус счета в связи с прикреплением-откреплением оплаты
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
				//если галочки не было - проставить ее	
				if($bill_item['is_in_buh']==0){
					$params=array();
					$params['is_in_buh']=1;
					$params['in_buh_pdate']=time();
					$params['user_in_buh_id']=-1;
					
					$_bi->Edit($bill_id, $params);
					
					$log->PutEntry(NULL,'автоматическое утверждение наличия счета в бухгалтерии на основании 100% оплаты',NULL,480,NULL,' счет № '.$bill_item['code'].': утверждено наличие счета в бухгалтерии на основании 100% оплаты',$bill_id);
					
					
					$params=array(
					'note'=>'Автоматическое примечание: утверждено наличие счета в бухгалтерии на основании 100% оплаты',
					'is_auto'=>1,
				'pdate'=>time(),
				'user_id'=>$bill_id,
				'posted_user_id'=>0
					);
					
					$bni->Add($params);
					
				}
			}else{
				//если галочка была - снять ее
				if($bill_item['is_in_buh']==1){
					$params=array();
					$params['is_in_buh']=0;
					$params['in_buh_pdate']=0;
					$params['user_in_buh_id']=0;
					
					$_bi->Edit($bill_id, $params);
					
					$log->PutEntry(NULL,'автоматическое снятие наличия счета в бухгалтерии на основании остутствия 100% оплаты',NULL,481,NULL,' счет № '.$bill_item['code'].': снято наличие счета в бухгалтерии на основании остутствия 100% оплаты',$bill_id);
					
					
					$params=array(
					'note'=>'Автоматическое примечание: снято наличие счета в бухгалтерии на основании остутствия 100% оплаты',
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