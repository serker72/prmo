<?
require_once('billitem.php');
require_once('ispositem.php');
require_once('billpospmformer.php');
require_once('isposgroup.php');
require_once('is_custom_item.php');
require_once('period_checker.php');

//����������� �������
class WfItem extends IsCustomItem{
	protected $is_or_writeoff;
	
	public function __construct($is_or_writeoff=1){
		$this->init($is_or_writeoff);
	}
	
	
	//��������� ���� ����
	protected function init($is_or_writeoff=1){
		$this->tablename='interstore';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->is_or_writeoff=$is_or_writeoff;	
	}
	
	public function Edit($id,$params,$scan_status=false){
		$item=$this->GetItemById($id);
		
		
		//�� ������������� ����������� 1 ���.
		if(isset($params['is_confirmed_fill_wf'])&&($params['is_confirmed_fill_wf']==1)&&($item['is_confirmed_fill_wf']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params);
		
	}
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params){
		$log=new ActionLog();
			$au=new AuthUser;
			$_result=$au->Auth();
			$_stat=new DocStatusItem;
			$item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed_fill_wf'])&&isset($old_params['is_confirmed_fill_wf'])){
			
			
			
			if(($new_params['is_confirmed_fill_wf']==1)&&($old_params['is_confirmed_fill_wf']==0)&&($old_params['status_id']==1)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,106,NULL,'���������� ������ '.$stat['name'],$id);
				
			}elseif(($new_params['is_confirmed_fill_wf']==0)&&($old_params['is_confirmed_fill_wf']==1)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,106,NULL,'���������� ������ '.$stat['name'],$id);
			}
		}
		
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==2)){
				//����� ������� � 2 �� 17
				$this->Edit($id,array('status_id'=>17));
				
				$stat=$_stat->GetItemById(17);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,106,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&(($old_params['status_id']==17))){
				//17 => 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,106,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}	
			
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
		
		if($item['is_j']!=0){
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='��� ������ ������������� �� ������� ��������� �� ��������� � '.$item['interstore_id'].', ������������� ���� ���������� ������������� ��� ���������� ��������� �� ��������� � '.$item['interstore_id'].'';
		}
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	//������ � ����������� �������������� � ����������� �������, ������ ������ ������������
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
		
		if($item['is_j']!=0){
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='��� ��� ����������� ������������� ��� ���������� ��������� �� ��������� � '.$item['interstore_id'].', �������������� ���� ���������� ������������� ��� ��������� ��������� �� ��������� � '.$item['interstore_id'].'';
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
	
	//������ � ����������� ����������� ���������� � ������� �������, ������ ������ ���������
	public function DocCanConfirmFillWf($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_fill_wf']==1){
			
			$can=$can&&false;
			
			$reasons[]='���������� ����������';
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//����������� ���. ��������!!!!
	public function DocCanConfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� ����������';
		}
		
		if($item['is_confirmed_fill_wf']==0){
			
			$can=$can&&false;
			
			$reasons[]='���������� �� ����������';
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//������ � ����������� ������������� ���������� � ������� �������, ������ ������ ���������
	public function DocCanUnConfirmFillWf($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_fill_wf']==0){
			
			$can=$can&&false;
			
			$reasons[]='���������� �� ����������';
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//����������� �����. ��������!!!!
	public function DocCanUnConfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ����������';
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
}
?>