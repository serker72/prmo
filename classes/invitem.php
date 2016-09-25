<?
require_once('abstractitem.php');

require_once('docstatusitem.php');

require_once('acc_group.php');
require_once('paygroup.php');


require_once('acc_in_item.php');

require_once('acc_group.php');



require_once('actionlog.php');
require_once('authuser.php');


require_once('billitem.php');
//require_once('sh_i_item.php');
require_once('acc_item.php');


require_once('billpospmformer.php');

require_once('bill_in_item.php');
 
require_once('acc_in_item.php');


require_once('bill_in_group.php');
 
require_once('acc_in_group.php');


require_once('maxformer.php');
require_once('authuser.php');
require_once('billcreator.php');
require_once('bdetailsitem.php');
require_once('actionlog.php');
require_once('billgroup.php');

require_once('invsync.php');
require_once('period_checker.php');




//����������� �������
class InvItem extends AbstractItem{
	
	public $sync;
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='inventory';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->sync=new InvSync;	
	}
	
	
	//�������
	public function Del($id){
		
		
		
		$query = 'delete from inventory_position where inventory_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	public function Edit($id,$params,$scan_status=false,$result=NULL){
		$item=$this->GetItemById($id);
		
		
		
		//�� ������������� ����������� 1 ���.
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		//die();
		
		//���� ���������� - �� ����������� �������
		if(isset($params['is_confirmed_inv'])&&($params['is_confirmed_inv']==1)){
			
			if($item!==false){
				 
				 
				 $this->sync->PutChanges($id,$result);
				 
				 
			}
			
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$result);
		
		//die();
	}
	
	
	
	//������� �������
	public function AddPositions($current_id, array $positions,$can_change_cascade=false){
		$_kpi=new InvPosItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
					'inventory_id'=>$v['inventory_id'],
					'position_id'=>$v['position_id'])
					 );
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['inventory_id']=$v['inventory_id'];
				
				$add_array['position_id']=$v['position_id'];
				 
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity_as_is']=$v['quantity_as_is'];
				$add_array['quantity_initial']=$v['quantity_as_is'];
				$add_array['quantity_fact']=$v['quantity_fact'];
				
				$_kpi->Add($add_array);
				
				$log_entries[]=array(
					'action'=>0,
					'position_id'=>$v['position_id'],
					 
					'quantity_as_is'=>$v['quantity_as_is'],
					'quantity_fact'=>$v['quantity_fact']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['inventory_id']=$v['inventory_id'];
				
				$add_array['position_id']=$v['position_id'];
				 
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity_as_is']=$v['quantity_as_is'];
				$add_array['quantity_fact']=$v['quantity_fact'];
			
			
				$_kpi->Edit($kpi['id'],$add_array); //, $add_pms,$can_change_cascade);
				
				//���� ���� ���������
				
				//��� ����������? ���������� ���-��
				
				$to_log=false;
				if($kpi['quantity_as_is']!=$add_array['quantity_as_is']) $to_log=$to_log||true;
				if($kpi['quantity_fact']!=$add_array['quantity_fact']) $to_log=$to_log||true;
				/*if($kpi['storage_id']!=$add_array['storage_id']) $to_log=$to_log||true;
				if($kpi['sector_id']!=$add_array['sector_id']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				*/
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'position_id'=>$v['position_id'],
					  
					'quantity_as_is'=>$v['quantity_as_is'],
					'quantity_fact'=>$v['quantity_fact']
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
				if(($vv['position_id']==$v['position_id'])
			 
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
			
			
			$log_entries[]=array(
					'action'=>2,
					'position_id'=>$v['position_id'],
				 
					'quantity_as_is'=>$v['quantity_as_is'],
					'quantity_fact'=>$v['quantity_fact']
			);
			
			//������� �������
			$_kpi->Del($v['p_id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	//������� �������
	public function GetPositionsArr($id,$result=NULL){
		$kpg=new InvPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$result);
		
		return $arr;		
		
	}
	
	
	
	
	//�������� ����������� ��������
	public function CanDelete($id, &$reason,$itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed']!=0)||($itm['is_confirmed_inv']!=0))) {
			$reason.='������������ �� �������������� ����������';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
	}
	
	
	
	
	
	
	
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		$item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==1)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������������� ����',NULL,326,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&(($old_params['status_id']==2))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� ������������������� ����',NULL,326,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}
		}else{
			//��������� �������� �� 2-9, 9-2, 9-10, 10-9
		  
		  //������� 2-9
		 /* if($item['status_id']==2){
			  //��������� ���������� �	
			  //����� ����� ��������� ������� 2-10
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,93,NULL,'���������� ������ '.$stat['name'],$id);
			  }else{
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,93,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		  
		  //������� 9-10 - ��� ������� ��������, ��� ��������� (���� ��������� ������������)
		  if($item['status_id']==9){
			  //��������� ���������� �	
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,93,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		  
		  //������� 10-9 - �� ��� ������� ���������
		  if($item['status_id']==10){
			  //��������� ���������� �	
			  if(!$this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'����� ������� �����',NULL,93,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }	
			
			*/
			
			
		}
		
		if(isset($new_params['is_confirmed_inv'])&&isset($old_params['is_confirmed_inv'])){
			if(($new_params['is_confirmed_inv']==1)&&($old_params['is_confirmed_inv']==0)&&($old_params['status_id']==2)){
				//����� ������� � 2 �� 16
				$this->Edit($id,array('status_id'=>16));
				
				$stat=$_stat->GetItemById(16);
				$log->PutEntry($_result['id'],'����� ������� ������������������� ����',NULL,326,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_inv']==0)&&($old_params['is_confirmed_inv']==1)&&(($old_params['status_id']==16))){
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������������� ����',NULL,326,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}	
			
		}
		
		
		//die();
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
		
		  //��������� ��������� �����������
		  $_accg=new AccGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ���������� � '.$v['id'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />�� ������������ �� �������������� ������� ������������ ����������: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		   $_accg=new AccInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />�� ������������ �� �������������� ������� ������������ �����������: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  
		
		  
		  
		  
		  $_accg=new BillGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' ���� � '.$v['code'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />�� ������������ �� �������������� ������� ������������ ��������� �����: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  $_accg=new BillInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' ���� � '.$v['code'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />�� ������������ �� �������������� ������� ������������ �������� �����: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  
	
		  
		  
		
		}
		
		
		
		return $can;
	}
	
	
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
		
		$_dsi=new DocStatusItem;
		
		$can=true;
		//��������� ��������� �����������
		  $_accg=new AccGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==4) {
				  
				  $can=$can&&false;
				  $reasons[]=' ���������� � '.$v['id'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\n�� ����� ������� �������������� ����������: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		   $_accg=new AccInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==4) {
				  
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\n�� ����� ������� �������������� �����������: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		
		  
		  //��������� ��������� �����������
		  $_accg=new BillGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==1) {
				  
				  $can=$can&&false;
				  $reasons[]=' ��������� ���� � '.$v['code'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\n�� ����� ������� �������������� ��������� �����: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		   //��������� ��������� �����������
		  $_accg=new BillInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==1) {
				  
				  $can=$can&&false;
				  $reasons[]=' �������� ���� � '.$v['code'].' ������ ���������: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\n�� ����� ������� �������������� �������� �����: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		  
		  
		
		return $reason;
	}
	
	public function AnnulBindedDocuments($id, $_result=NULL){
		
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		$set=new MysqlSet('select * from acceptance where is_incoming=1 and inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ����������� � ����� � �������������� ������������������� ����',NULL,336,NULL,'����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['inventory_id']);
			
			$log->PutEntry($_result['id'],'������������� ����������� � ����� � �������������� ������������������� ����',NULL,674,NULL,'����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where is_incoming=1 and inventory_id="'.$id.'"');
		
		$set=new MysqlSet('select * from acceptance where is_incoming=0 and inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ���������� � ����� � �������������� ������������������� ����',NULL,336,NULL,'���������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['inventory_id']);
			
			$log->PutEntry($_result['id'],'������������� ���������� � ����� � �������������� ������������������� ����',NULL,242,NULL,'���������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where is_incoming=0 and inventory_id="'.$id.'"');
		
		
		
		
		
		
		
		
		
		$set=new MysqlSet('select * from bill where  is_incoming=1 and  inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ��������� ����� � ����� � �������������� ������������������� ����',NULL,626,NULL,'������������ � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
			
			
		}	
		
		$ns=new NonSet('update bill set status_id=3 where  is_incoming=1 and   inventory_id="'.$id.'"');
		
		
		
		$set=new MysqlSet('select * from bill where is_incoming=0 and   inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'������������� ���������� ����� � ����� � �������������� ������������������� ����',NULL,94,NULL,'������������ � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
			
			
		}	
		
		$ns=new NonSet('update bill set status_id=3 where  is_incoming=0 and  inventory_id="'.$id.'"');
		
		
	}
	
	
	
	public function DocCanConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			$reasons[]='� ���� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //�������� ��������� ������� 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='���� �������������� '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	public function DocCanUnConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='� ���� �� ���������� ����������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //�������� ��������� ������� 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='���� �������������� '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	
	
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_inv']!=0){
			
			$can=$can&&false;
			$reasons[]='� ���� ���������� ��������� ���������� �������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //�������� ��������� ������� 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='���� �������������� '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	
	//������ � ����������� ������ ���. ��������� � ����������� �������, ������ ������ 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_inv']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='� ���� �� ���������� ��������� ���������� �������';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //��������� ��������� �����������
		  $_accg=new AccInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' �� ���� ������� ������������ �����������: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  //��������� ��������� �����������
		  $_accg=new AccGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' ���������� � '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' �� ���� ������� ������������ ����������: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		 
		  
		
		  
		  
		   //��������� ��������� �����
		  $_accg=new BillInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' �������� ���� � '.$v['code'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' �� ���� ������� ������������ �������� �����: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		    //��������� ��������� �����
		  $_accg=new BillGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' ��������� ���� � '.$v['code'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' �� ���� ������� ������������ ��������� �����: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		 $reasons=array();
		  //�������� ��������� ������� 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' ���� �������������� '.$rss23;	
		  }
		   if(strlen($reason)!=0) $reason.=', ';
		   $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	
	//�������� ���� ��������������!!!
	public function CheckInventoryPdate($pdate, $sector_id, &$rss, $except_id=0){
		$res=true; //��� ��
		$_dsi=new DocStatusItem;
		
		$sql='select * from '.$this->tablename.' where inventory_pdate>="'.$pdate.'" and sector_id="'.$sector_id.'" and id<>"'.$except_id.'" and status_id in(2,16)';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0) $res=false;
		
		$rss=''; $_rss=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$dsi=$_dsi->GetItemById($f['status_id']);
			$_rss[]='������������������ ��� � '.$f['code'].', ���� �������������� '.date('d.m.Y', $f['inventory_pdate']).', ������ '.$dsi['name'] ;
		}
		
		if(count($_rss)>0) $rss="���������� ���� ��������������: ".implode('\n',$_rss);
		
		return $res;
	}
	
	
	
	
	
	
}
?>