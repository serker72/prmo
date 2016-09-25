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
require_once('invoice_notesitem.php');

//����������� �������
class InvoiceItem extends AbstractItem {
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='invoice';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='acceptance_id';
	}

	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if(!in_array($item['status_id'], array(1,18))){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
		}
		$reason=implode(', ',$reasons);
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
        
}
?>