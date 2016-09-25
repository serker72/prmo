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
require_once('actionlog.php');

//��������� ������
class DemandItem extends AbstractItem{
	
	public $billpaysync;
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='demand';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_confirmed';	
		$this->subkeyname='bill_id';
		 
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
		
		 
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params);
		 
		
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
				 
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,855,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==15)){
				$this->Edit($id,array('status_id'=>14));
				
				$stat=$_stat->GetItemById(14);
				 
				$log->PutEntry($_result['id'],'����� ������� ����������',NULL,855,NULL,'���������� ������ '.$stat['name'],$item['id']);
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
	
	 
}
?>