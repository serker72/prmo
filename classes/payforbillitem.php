<?
require_once('abstractitem.php');
require_once('billpaysync.php');


//����������� �������
class PayForBillItem extends AbstractItem{
	
	public $billpaysync;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='payment_for_bill';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		
		$this->billpaysync=new BillPaySync;	
	}
	
	
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		if(isset($params['bill_id'])){
			 $this->billpaysync->CatchStatus($params['bill_id']);
			 //������� ������������ ����� ������� �����
		}
		return $code;
	}
	
	//�������
	public function Edit($id,$params){
		AbstractItem::Edit($id, $params);
		
		if(isset($params['bill_id'])){
			 $this->billpaysync->CatchStatus($params['bill_id']);
			 //������� ������������ ����� ������� �����
		}
	}
	
	//�������
	public function Del($id){
		$item=$this->GetItemById($id);
		AbstractItem::Del($id);
		
		if(isset($item['bill_id'])&&($item['bill_id']!=0)){
			 $this->billpaysync->CatchStatus($item['bill_id']);
			 //������� ������������ ����� ������� �����
		}
	}	
}
?>