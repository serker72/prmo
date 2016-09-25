<?
require_once('billitem.php');
require_once('ispositem.php');
require_once('billpospmformer.php');
require_once('isposgroup.php');
require_once('authuser.php');
require_once('period_checker.php');

//����������� �������
class IsCustomItem extends BillItem{
	protected $is_or_writeoff;
	
	public function __construct($is_or_writeoff=0){
		$this->init($is_or_writeoff);
	}
	
	
	//��������� ���� ����
	protected function init($is_or_writeoff=0){
		$this->tablename='interstore';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->is_or_writeoff=$is_or_writeoff;	
	}
	
	
	//�������
	public function Del($id){
		
		
		
		
		$query = 'delete from interstore_position where interstore_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//������� �������
	public function AddPositions($current_id, array $positions){
		$_kpi=new IsPosItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('interstore_id'=>$v['interstore_id'],'position_id'=>$v['position_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['interstore_id']=$v['interstore_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['interstore_id']=$v['interstore_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms);
				
				//���� ���� ���������
				
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				
				if($to_log){
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
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
				if($vv['position_id']==$v['id']){
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
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$pms
			);
			
			//������� �������
			$_kpi->Del($v['p_id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	
	//������� �������
	public function GetPositionsArr($id){
		$kpg=new IsPosGroup;
		$arr=$kpg->GetItemsByIdArr($id);
		
		
		
		return $arr;		
		
	}
	
	//������� ������� (���������� �������)
	public function GetSimplePositions($id){
		//������ �������
	
		$arr=array();
		
		$sql='select p.id as p_id, p.interstore_id, p.komplekt_ved_pos_id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.quantity_initial
					 
		from  interstore_position as p 
			
			
		where p.interstore_id="'.$id.'" order by position_name asc, id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	public function Edit($id,$params,$scan_status=false){
		$item=$this->GetItemById($id);
		
		AbstractItem::Edit($id, $params);
		
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params);
		
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
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,101,NULL,'���������� ������ '.$stat['name'],$id);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,101,NULL,'���������� ������ '.$stat['name'],$id);
			}
			
			
			
		}
		
		if(isset($new_params['is_confirmed_wf'])&&isset($old_params['is_confirmed_wf'])){
				
			//echo 'zzzzzzzzzzz'; die();
			if(($new_params['is_confirmed_wf']==1)&&($old_params['is_confirmed_wf']==0)&&($old_params['status_id']==2)){
				//����� ������� � 2 �� 17
				$this->Edit($id,array('status_id'=>17));
				
				$stat=$_stat->GetItemById(17);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,101,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_wf']==0)&&($old_params['is_confirmed_wf']==1)&&(($old_params['status_id']==17))){
				//17 => 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������ �� ��������',NULL,101,NULL,'���������� ������ '.$stat['name'],$item['id']);
			}	
			
		}
	}
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason, $item=NULL){
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
	
	//������ � ����������� ����������� � ���������� �������, ������ ������ ���������
	public function DocCanConfirm($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� ���������';
		}
		
		//��������� �������. ���� ���, ���� ������� ���-�� - ������ ���.
		$positions=$this->GetPositionsArr($id);
		
		if(count($positions)==0){
			$can=$can&&false;
			
			$reasons[]='�� ������� ������� ������������ �� ��������';
		}
		$total_count=0;
		
		foreach($positions as $k=>$v){
			$total_count+=$v['quantity'];
		}
		if((count($positions)>0)&&($total_count==0)){
			$can=$can&&false;
			
			$reasons[]='������� ������� ���������� ������� ������������ �� ��������';
		}
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	public function DocCanConfirmWf($id,&$reason,$user_id=NULL, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed_wf']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� ����������';
		}
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ����������';
		}
		
		
		
		//��������� �������. ���� ���, ���� ������� ���-�� - ������ ���.
		$positions=$this->GetPositionsArr($id);
		
		if(count($positions)==0){
			$can=$can&&false;
			
			$reasons[]='�� ������� ������� ������������ �� ��������';
		}
		$total_count=0;
		
		foreach($positions as $k=>$v){
			$total_count+=$v['quantity'];
		}
		if((count($positions)>0)&&($total_count==0)){
			$can=$can&&false;
			
			$reasons[]='������� ������� ���������� ������� ������������ �� ��������';
		}
		
		
		
		//������� �������� �� ������� � ������������
		if(!$this->CheckWfUserSenderStorage($item['sender_storage_id'],$user_id, $item['sender_sector_id'])){
			$can=$can&&false;
			$_st=new StorageItem;
			$st=$_st->GetItemById($item['sender_storage_id']);
			$reasons[]='� ��� ������������ ���� ��� �������� ��������� � ������� '.$st['name'];
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//�������� ������ ��������
	public function DocCanUnconfirmWf($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		//��������� ��������� �����
		 $set=new mysqlSet('select p.*, s.name from bill as p inner join document_status as s on p.status_id=s.id where interstore_id="'.$id.'" and ( is_confirmed_price=1 or is_confirmed_shipping=1)');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			//  $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' ���� � '.$v['code'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) {
			if(strlen($reason)!=0) $reason.='; ';
			$reason.='������� ��������� �����: ';
			$reason.=implode(', ',$reasons);
		  }
		 
		
		//��������� ��������� ������������ �� �������
		  $set=new mysqlSet('select p.*, s.name from sh_i  as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id in(select  id from bill where interstore_id="'.$id.'" )');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' ������������ �� ������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) {
			if(strlen($reason)!=0) $reason.='; ';
			$reason.=' ������� ��������� ������������ �� �������: ';
			$reason.=implode(', ',$reasons);
		  
		  }
		
		
		//��������� ��������� �����������
		 //��������� ���� ������
		  $set=new mysqlSet('select p.*, s.name from acceptance  as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id in(select  id from bill where interstore_id="'.$id.'" )');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  
			  
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  //if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 // }
			  
		  }
		  if(strlen($reason)!=0) $reason.='; ';
		  if(count($reasons)>0) $reason.=' ������� ��������� �����������: ';
		  $reason.=implode(', ',$reasons);
		  
		  $reasons=array(); 
		  //�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		if(count($reasons)>0){
		 if(strlen($reason)!=0) $reason.='; ';
		 $reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	
	//�������� �� ������� � ������������
	public function CheckWfUserSenderStorage($storage_id, $user_id=NULL, $sector_id){
		$res=false;
		
		if($user_id===NULL){
			$au=new AuthUser();
			$result=$au->Auth();
			$user_id=$result['id'];
			
			//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		}
		
		//�������� ����������� ��-��, ��-��
		$_sc=new SectorItem;
		$_st=new StorageItem;
		$sc=$_sc->GetItemById($sector_id);
		$st=$_st->GetItemById($storage_id);
		
		if(($sc['s_s']==1)&&($st['s_s']==1)){
			$sql1='select count(*) from user_rights where object_id=448 and user_id="'.$user_id.'" and right_id=2';
			//echo $sql1;
			$set1=new mysqlset($sql1);
	    	$rs1=$set1->getResult();
			$g=mysqli_fetch_array($rs1);
			
			$res=((int)$g[0]>0);
			
		}else{
		
		
			//if(!$au->user_rights->CheckAccess('r',289)
		  
		  //������ ������� ��� �������
		  $sql='select object_id from interstore_storage_to_object where storage_id="'.$storage_id.'" limit 1';
		  //echo $sql;
		  
		  $set=new mysqlset($sql);
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		 
		  if($rc>0){
		  //for($i=0; $i<$rc; $i++){
		  //
			  $f=mysqli_fetch_array($rs);
			  //$res=$au->user_rights->CheckAccess('w',$f['object_id']);
			  $sql1='select count(*) from user_rights where object_id="'.$f['object_id'].'" and user_id="'.$user_id.'" and right_id=2';
			  //echo $sql1;
			  $set1=new mysqlset($sql1);
			  $rs1=$set1->getResult();
			  $g=mysqli_fetch_array($rs1);
			  $res=((int)$g[0]>0);
		
		  }/*else $res=true;*/
		  
		}
		return $res;
	}
	
	
	
	//������ ��������� �� �������������� �� ��� ���������� ��� �����������������
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		
		//��������� ��������� �����
		 $set=new mysqlSet('select p.*, s.name from bill  as p inner join document_status as s on p.status_id=s.id where interstore_id="'.$id.'" and status_id<>3');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' ���� � '.$v['code'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.='\n������� ��������� �����: ';
		  $reason.=implode(', ',$reasons);
		  
		 
		
		//��������� ��������� ������������ �� �������
		  $set=new mysqlSet('select p.*, s.name from sh_i  as p inner join document_status as s on p.status_id=s.id where status_id<>3 and bill_id in(select  id from bill where interstore_id="'.$id.'" and status_id<>3)');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' ������������ �� ������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.='\n������� ��������� ������������ �� �������: ';
		  $reason.=implode(', ',$reasons);
		  
		 
		
		
		//��������� ��������� �����������
		 //��������� ���� ������
		  $set=new mysqlSet('select p.*, s.name from acceptance  as p inner join document_status as s on p.status_id=s.id where status_id<>6 and bill_id in(select  id from bill where interstore_id="'.$id.'" and status_id<>3)');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  
			  
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  //if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 // }
			  
		  }
		  if(count($reasons)>0) $reason.='\n������� ��������� �����������: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		
	
		return $reason;
	}
	
}
?>