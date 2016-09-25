<?
require_once('abstractitem.php');
require_once('positem.php');
require_once('komplblink.php');
require_once('komplpositem.php');
require_once('komplposgroup.php');
require_once('komplconfgroup.php');
require_once('komplconfitem.php');
require_once('komplconfrolegroup.php');
require_once('komplconfroleitem.php');

require_once('docstatusitem.php');

require_once('trust_group.php');

require_once('acc_group.php');
require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('komplmarkgroup.php');
require_once('komplmarkitem.php');


require_once('discr_man.php');
require_once('rights_detector.php');

require_once('period_checker.php');
require_once('komplnotesitem.php');
require_once('positem.php');

require_once('posgroupgroup.php');

require_once('bdetailsitem.php');
require_once('supcontract_item.php');
require_once('billitem.php');

require_once('supplieritem.php');
require_once('opfitem.php');
require_once('kompsync.php');
require_once('orgitem.php');

require_once('bill_in_item.php');


//������� ��������
class KomplItem extends AbstractItem{
	protected static $uslugi;
	protected static $position_uslugi;
	
	public $kompl_blink;
	
	public $rd;
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
		$this->kompl_blink=new KomplBlink;
		$this->rd=new RightsDetector($this);
		
		//������ ����� �����
		if(self::$uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // ������
		  self::$uslugi/*$this->uslugi*/=array();
		  self::$uslugi/*$this->uslugi*/[]=SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$uslugi/*$this->uslugi*/)) self::$uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$uslugi/*$this->uslugi*/))  self::$uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
		//������ ����� �����
		if(self::$position_uslugi===NULL){
			self::$position_uslugi=array();
			$sql='select id from catalog_position where group_id in('.implode(', ',self::$uslugi).')';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				self::$position_uslugi[]=$f['id'];	
			}
		}
	}
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from komplekt_ved_confirm where komplekt_ved_id='.$id.';';
		$it=new nonSet($query);
		
		$query = 'delete from komplekt_ved_pos where komplekt_ved_id='.$id.';';
		$it=new nonSet($query);
		
		parent::Del($id);
	}	
	
	public function Edit($id,$params,$scan_status=false, $_auth_result=NULL){
		$item=$this->GetItemById($id);
		
		
		//�� ������������� ����������� 1 ���.
		if(isset($params['status_id'])&&($params['status_id']!=11)&&($item['status_id']==11)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		//���� ������ ����������� - ������� ��� �� ���� �����������  ���. ������...
		//����� ������ ��������� ����������� � ����� ��������
		if(isset($params['supplier_id'])&&($params['supplier_id']!=$item['supplier_id'])){
			//������ ��� ����������� �����
			$sql='select * from bill where is_incoming=0 and komplekt_ved_id="'.$id.'"';
			
			
			$_bill=new BillItem; $_bdi=new BDetailsItem; $_ci=new SupContractItem; $log=new ActionLog;
			$_supplier=new SupplierItem; $_opf=new OpfItem;
			$supplier=$_supplier->GetItemById($params['supplier_id']);
			$opf=$_opf->GetItemById($supplier['opf_id']);
			
			$set=new mysqlset($sql);
			$rs=$set->getresult();
			$rc=$set->getresultnumrows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$bill_params=array();
				$bill_params['supplier_id']=$params['supplier_id'];
				
				$bdi=$_bdi->getitembyfields(array('is_basic'=>1, 'user_id'=>$params['supplier_id']));
				if($bdi!==false) $bill_params['bdetails_id']=$bdi['id'];
				else $bill_params['bdetails_id']=0;
				
				$ci=$_ci->GetItemByFields(array('is_basic'=>1, 'user_id'=>$params['supplier_id'], 'is_incoming'=>0));
				if($ci!==false) $bill_params['contract_id']=$ci['id'];
				else $bill_params['contract_id']=0;
				
				
				$_bill->Edit($f['id'], $bill_params);
				
				//� ������ �������
				$log->PutEntry($_auth_result['id'], '����� ����������� ���������� ����� ��� ����� ����������� ������', NULL, 93, NULL, '� ����� � '.$f['code'].' ���������� ���������� '.SecStr($opf['name'].' '.$supplier['full_name']), $f['id']);
				
				$log->PutEntry($_auth_result['id'], '����� ����������� ���������� ����� ��� ����� ����������� ������', NULL, 82, NULL, '� ����� � '.$f['code'].' ���������� ���������� '.SecStr($opf['name'].' '.$supplier['full_name']), $id);
					
			}
			
			//����� ������������ ����������� ��������� � ������ ����
			if($item['is_leading']==1){
				$_ks=new KompSync($id,0,$item['org_id'],0,$_auth_result);
				$_ks->AnnulLeadingDocs($id, '��� ����� ����������� ������');
			}
				
		}
		
		AbstractItem::Edit($id, $params);
		
		//���� ������ ���������� � ������ 2 12 13 - ������� ��� ������� �� ���!
		if(isset($params['status_id'])&&(($params['status_id']==2)||($params['status_id']==12)||($params['status_id']==13))){
			
			$_mg=new KomplMarkGroup;
			$markers=$_mg->GetItemsByIdArr($id);
			$_mi=new KomplMarkItem;
			foreach($markers as $k=>$v){
				$_mi->Del($v['id']);	
			}
		
		}
		
		//���� ���������� ������, ��������� � ������� � ��. ����������� - ������������ ��� ��������� ���������
		if(isset($params['status_id'])&&($params['status_id']==3)&&($item['status_id']!=3)){
			$_ks=new KompSync($id,0,$item['org_id'],0,$_auth_result);
			$_ks->AnnulLeadingDocs($id, '��� ������������� ������');
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL, $_auth_result);
	}
	
	
	
	//������� �������
	public function AddPositions($current_id, array $positions){
		$_kpi=new KomplPosItem;
		$_pi=new PosItem;
		$log_entries=array();
		$_kcg=new KomplConfGroup;
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id']));
			$pi=$_pi->getitembyid($v['position_id']);
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				$add_array['position_id']=$v['position_id'];
				
				$add_array['quantity_confirmed']=$v['quantity_confirmed'];
				$add_array['quantity_initial']=$v['quantity_confirmed'];
				$add_array['storage_id']=$v['storage_id'];
				$_kpi->Add($add_array);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$pi['name'],
					'quantity_confirmed'=>$v['quantity_confirmed'],
					'quantity_confirmed_was'=>0,
					'quantity_initial'=>$v['quantity_confirmed'],
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['position_id']=$v['position_id'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				
				$add_array['quantity_confirmed']=$v['quantity_confirmed'];
				
				//���������, ���� �� �� ������ ����������� �� ���� � is_primanry==1
				//���� ����, �� �� ������.
				if(!$_kcg->HasPrimaryRole($current_id)){
					$add_array['quantity_initial']=$v['quantity_confirmed'];
					$log_initial=$v['quantity_confirmed'];
				}else{
					$log_initial=$kpi['quantity_initial'];
				}
				
				$add_array['storage_id']=$v['storage_id'];
				$_kpi->Edit($kpi['id'],$add_array);
				
				//���� ���� ���������
				//��� ��� ����������?????
				//��������� ��������� �������-�� ��������
				$to_log=false;
				if($kpi['quantity_confirmed']!=$add_array['quantity_confirmed']) $to_log=$to_log||true;
				
				
				
				if($to_log){
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$pi['name'],
					  'quantity_confirmed'=>$v['quantity_confirmed'],
					  'quantity_confirmed_was'=>$kpi['quantity_confirmed'],
					  'quantity_initial'=>$log_initial
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
				if($vv['position_id']==$v['position_id']){
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
			$pi=$_pi->getitembyid($v['position_id']);
			
			$log_entries[]=array(
					'action'=>2,
					'name'=>$pi['name'],
					'quantity_confirmed'=>$v['quantity_confirmed']
			);
			
			//������� �������
			$_kpi->Del($v['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
	}
	
	
	//������� �������
	public function DelPositions($id){
		$query = 'delete from komplekt_ved_pos where komplekt_ved_id='.$id.';';
		$it=new nonSet($query);
		
	}
	
	
	//������� �������
	public function GetPositionsArr($id,$do_find_max=false, $dec2=NULL){
		$kpg=new KomplPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$do_find_max, $dec2);
		
		return $arr;		
		
	}
	
	//������� ������ ������������
	public function GetConfirmingArr($id, $current_id=0,$object_id=84, $komplekt_ved=NULL,$sector_ss=NULL, $storage_ss=NULL){
		$kpu=new KomplConfGroup();
		$arr=$kpu->GetItemsByIdArr($id,$current_id,$object_id, $komplekt_ved,$sector_ss, $storage_ss);
		
		return $arr;		
			
	}
	
	//������� ������ ����������� � ���������
	public function GetPointsArr($id){
		$kpu=new KomplConfGroup();
		$arr=$kpu->GetPointsArr($id);
		
		return $arr;	
	}
	
	//����������� �����������
	public function Confirm($id, $user_id, $role_id){
		$code=0;
		
		$_kpi=new KomplConfItem;
		/*$kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$id, 'user_id'=>$user_id));
		if($kpi===false){*/
			$code=$_kpi->Add(array('komplekt_ved_id'=>$id, 'user_id'=>$user_id, 'pdate'=>time(), 'role_id'=>$role_id));	
			
		//}
			
		return $code;
	}
	
	
	//�������� �����������
	public function RemoveConfirm($id, $user_id){
		$ns=new NonSet('delete from komplekt_ved_confirm where komplekt_ved_id="'.$id.'" and user_id="'.$user_id.'" ');	
	}
	
	//������� �����������
	public function ClearConfirms($id){
		$ns=new NonSet('delete from komplekt_ved_confirm where komplekt_ved_id="'.$id.'" ');	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		
		 //������� �� 11 - 1 ��� 11-2
		  if($item['status_id']==11){
		  		//���� ���� ���� �� 1 ���. - ������� � ������ �� ���.
			   
			   $_kcg=new KomplConfGroup; 
			   
			   if($_kcg->HasAllConfirm($id)){
				   $this->Edit($id,array('status_id'=>2));
				  
				  $stat=$_stat->GetItemById(2);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id); 
			   }elseif($_kcg->HasAnyConfirm($id)){
				  $this->Edit($id,array('status_id'=>1));
				  
				  $stat=$_stat->GetItemById(1);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		  
		  //������� �� 1 - 11
		  if($item['status_id']==1){
		  		//���� ���� ���� �� 1 ���. - ������� � ������ �� ���.
			   
			   $_kcg=new KomplConfGroup;
			   if(!$_kcg->HasAnyConfirm($id)){
				  $this->Edit($id,array('status_id'=>11));
				  
				  $stat=$_stat->GetItemById(11);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		
		
		if(isset($new_params['is_active'])&&isset($old_params['is_active'])){
			
			
			
			
			if(($new_params['is_active']==1)&&($old_params['is_active']==0)&&(($old_params['status_id']==1)||($old_params['status_id']==11))){
				//����� ������� � 11 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
				
				
				//���� ���� ���������� �� �������� - �� ������� ������ �� ��������� (13)
				
				 if($this->CheckDeltaPositions($id)){
					  $this->Edit($id,array('status_id'=>13));
					  
					  $stat=$_stat->GetItemById(13);
					  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
				 }
				
			}elseif(($new_params['is_active']==0)&&($old_params['is_active']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==12)||($old_params['status_id']==13))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			}
		}else{
			
		 
		  
			
			//������� 2-12
		  if($item['status_id']==2){
			  //��������� ���������� �	
			  //����� ����� ��������� ������� 2-13
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>13));
				  
				  $stat=$_stat->GetItemById(13);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			  }else{
				  $this->Edit($id,array('status_id'=>12));
				  
				  $stat=$_stat->GetItemById(12);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		  
		  //������� 12-13 - ��� ������� ��������, ��� ��������� (���� ��������� ������������)
		  if($item['status_id']==12){
			  //��������� ���������� �	
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>13));
				  
				  $stat=$_stat->GetItemById(13);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }
		  
		  //������� 13-12 - �� ��� ������� ���������
		  if($item['status_id']==13){
			  //��������� ���������� �	
			  if(!$this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>12));
				  
				  $stat=$_stat->GetItemById(12);
				  $log->PutEntry($_result['id'],'����� ������� ������',NULL,82,NULL,'���������� ������ '.$stat['name'],$id);
			  }
		  }		
			
			
			
		}
	}
	
	
	
	//������ � ���������� ������� �� ����������� ������������
	
	public function CheckDeltaPositions($id){
		$res=true;
		
		
		$positions=$this->GetPositionsArr($id);
		
		$delta=0;
		
		//�� ������� ������ ���� �����
		//�� ������� ������ ���� ����
		//����� ������ ��������� � ������ � � ����������� �������
		
		
		foreach($positions as $k=>$v){
			
			//�������� �����
			$sql='select sum(quantity) as s_q from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and is_incoming=0) and position_id="'.$v['position_id'].'" and komplekt_ved_id="'.$id.'" ';
			
			//echo $sql.' <br>'; 
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
		
			
			$out=round((float)$f['s_q'],3);
			
			
			//�������� �����
			$sql='select sum(quantity) as s_q from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and is_incoming=1) and position_id="'.$v['position_id'].'" and komplekt_ved_id="'.$id.'" ';
			
			//echo $sql.' <br>'; 
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
			
		
			
			$in=round((float)$f['s_q'],3);
			
			
			//�������� �� ���������� ���� ���� ���������
			if(!( ($in==$out)&&($in==round((float)$v['quantity_confirmed'],3))  )) $res=$res&&false;
			
			
		  
		}
		
		//print_r($delta);
		
		//$res=($delta==0);
		
		//die();
		
		//var_dump($res);
		
		return $res;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanAnnul($id,&$reason,$user_id=0,$item=NULL, $sector=NULL, $storage=NULL, $sector_ss=NULL, $storage_ss=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		
		if($item===NULL) $item=$this->GetItemById($id);
		
	 
		
		
		$_dsi=new DocStatusItem;
		//if(($item['status_id']!=1)&&($item['status_id']!=11)){
		if(($item['status_id']==3)){	
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode('<br /> ',$reasons);
		}
		elseif(($item['is_leading']==0)){	
			$_org=new OrgItem;
			$_opf=new OpfItem;
			
			$leading_item=$this->GetItemById($item['leading_komplekt_ved_id']);
			$org=$_org->GetItemById($leading_item['org_id']);
			$opf=$_opf->GetItemById($org['opf_id']);
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ������� � ������� �  '.$item['leading_komplekt_ved_id'].'  � ���� ����������� '.$opf['name'].' '.$org['full_name'].', ��� ������������� ������ ����������� ������ � '.$item['leading_komplekt_ved_id'] ;
			$reason.=implode('<br /> ',$reasons);
		}
		
		
		else{
		  
		 
		  
		  
		  
		  
		  
		  
		  
		  
		  //��������� ���� �����
		  //����� �� ������������ �������������! ��� ��������� ������ ����� ��������� ������������!
		 /* $set=new mysqlSet('select p.*, s.name  from bill as p inner join document_status as s on p.status_id=s.id where (is_confirmed_price=1 or is_confirmed_shipping=1)  and p.id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  
				  if($v['is_incoming']==1) $reasons[]=' <a target=_blank href=ed_bill_in.php?action=1&id='.$v['id'].'&from_begin=1>���� � '.$v['code'].'</a> ������ ���������: '.$v['name'];	
			 
				  else $reasons[]=' <a target=_blank href=ed_bill.php?action=1&id='.$v['id'].'&from_begin=1>���� � '.$v['code'].'</a> ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.="<br /> �� ������ ������� ������������ �����: ";
		  $reason.=implode('<br /> ',$reasons);*/
		  
		  
		  //��������� ��������� �����������
		 
		  
		   $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			 
				  $can=$can&&false;
				   if($v['is_incoming']==1)  $reasons[]=' ����������� <a target=_blank href=ed_acc_in.php?action=1&id='.$v['id'].'&from_begin=1>� '.$v['id'].'</a> ������ ���������: '.$v['name'];	
				   else $reasons[]=' ���������� <a target=_blank href=ed_acc.php?action=1&id='.$v['id'].'&from_begin=1>� '.$v['id'].'</a> ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.="<br /> �� ������ ������� ������������ �����������/����������: ";
		  $reason.=implode('<br /> ',$reasons);
		  
		
		   
		 
		  //��������, ���� �� ������� ������ � ������, � ������� ���� ������� ������ ������?
		  $can=$can&&$this->CanUncheckByPositions($id, $rss1, $item);
		  $reason.=$rss1; 
		 
		  
		  
		  //???
		  //�������� ������:
		   
		  //� ���� ��������� 359|392
		  //� ���� ��� ��������������� ������� - �� ������ ����������...
		  
		  //��� ���� � ������������� - �� ������� ��� �������, ������ ������ � ������ � �������������� ����������...
		  
		  //���� ��� ���� �� ����������� � ����� 180,296,359|386,391,392 - �� �������� ��� ������
		 /*
		   $_kcg=new KomplConfGroup;
		  $_dm=new DiscrMan;
		  if($_dm->CheckAccess($user_id,'w',$this->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss,$storage_ss,array(359,392)))){
			 
			 
		 
			 
		  	//� ���� ��� ��������������� 
			 
			  
			  $reasons=array();
			  //��� ���� ����� ��� ��������� �������... � ����, ����� ���� ����� ���� �� ����!
			  
			  
			  $can_annul=false;
		 
				  $reasons[]='������ ���������� � ��������� ��� ����  ��������, ������� ������ �����������';  
			 
			  
			  if(count($reasons)>0) $reason.='<br />';
			  $reason.=implode('<br /> ',$reasons);  
			  $can=$can&&$can_annul;
		  }else{
				  //��������� ������� ���� �� 1 �������
				//
				  $kcg=$_kcg->CountUtv($id);
				 // print_r($kcg);
				  if($kcg>0){
						$can=$can&&false;
						$reasons[]='������ ���������� ����� ��� ����� ������������� �����������';
						  
				  }
				  if(count($reasons)>0) $reason.='<br />';
				  $reason.=implode('<br /> ',$reasons);  
			  
			  
		  }
		  
		  */
		  
		  
		}
		
		return $can;
	}
	
	
	//��������, ���� �� ������� ������ � ������, � ������� ���� ������� ������ ������?
	public function CanUncheckByPositions($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		$sql='select b.id, b.code, b.is_incoming, bp1.name, bp1.position_id, bp1.quantity, bp1.dimension
			from
				bill as b
				inner join bill_position as bp1 on bp1.bill_id=b.id
			where 
				bp1.komplekt_ved_id="'.$id.'"
				and b.status_id<>3
				and b.id in(select distinct b2.id from bill as b2 inner join bill_position as bp2 on bp2.bill_id=b2.id where b2.status_id<>3 and bp2.komplekt_ved_id<>"'.$id.'" and bp2.komplekt_ved_id<>0)
			order by bp1.name
				';
		
		//echo $sql;		
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->GetResultNumRows();
		  
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($f['is_incoming']==1) $link='<a href="ed_bill_in.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>';
			else $link='<a href="ed_bill.php?action=1&id='.$f['id'].'" target="_blank">'.$f['code'].'</a>';
			
			$link=SecStr($link, 9);
			
			$reasons[]='������� '.SecStr($f['name'],9).' ���������� � ����� '.($link).', ���������� ������� ������ ������ ';	
			
			$can=$can&&false;	
		}
		//if(!$can) echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		
		if(count($reasons)>0) {
			$reason.='<br />';
			$reason.=implode('<br /> ',$reasons).'. <br />��� ������������� ������ ������� ��� ������� �� ��������� ������. '; 
		}
		
		return $can;
	}
	
	
	
	
	
	//������ � ����������� ������������� � ����������� �������, ������ ������ ������������
	public function DocCanRestore($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='������ ���������: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	//����� �� ������� ����������� �� ������ - ������� ��������� ���. ���-���
	public function DocCanUnconfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_dsi=new DocStatusItem;
		
		$sql='select p.*, s.name  from bill as p inner join document_status as s on p.status_id=s.id where is_confirmed_price=1 and p.id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")';
		//echo $sql;
		
		 $set=new mysqlSet($sql);
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' ���� � '.$v['code'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.="\n�� ������ ������� ������������ �����:\n";
		  $reason.=implode(",\n",$reasons);
		  
		  
		  //��������� ��������� �����������
		 
		   $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and p.id in(select distinct acceptance_id id from acceptance_position where komplekt_ved_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			 
				  $can=$can&&false;
				  if($v['is_incoming']==1) $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$v['name'];	
				  else $reasons[]=' ���������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.="\n�� ������ ������� ������������ �����������/����������:\n";
		 $reason.=implode(",\n",$reasons);
		  
		  
		 
		
		
		return $can;
	}
	
	
	
	//������������� ���������
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
		
		
	}
	
	
	
	
	
	
	//������ ��������� �� �������������� �� ��� ���������� ��� �����������������
	public function GetBindedDocumentsToAnnul($id, $item=NULL){
		$reason=''; $reasons=array();
		
		if($item===NULL) $item=$this->getitembyid($id);
		
		$_dsi=new DocStatusItem;
		
		
		 //��������� ���� �����
		 //����� ���� �����, ����� ��������������
		  $set=new mysqlSet('select p.*, s.name  from bill as p inner join document_status as s on p.status_id=s.id where status_id<>3 and p.id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
				  
				  $reasons[]=' ���� � '.$v['code'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.=" �� ������ ������� �����: ";
		  $reason.=implode(', ',$reasons);
		  
		  
		  //��������� ��������� �����������
		 
		   $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where status_id<>6 and bill_id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			//  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			 if($v['is_incoming']==1) $reasons[]=' ����������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 else $reasons[]=' ���������� � '.$v['id'].' ������ ���������: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.=" �� ������ ������� �������������� �����������/����������: ";
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		 
		//���� ���  ������� ������ - �������� ������ ������� ����������
		
		if($item['is_leading']==1){
			//��������� ������
			$sql='select p.*, s.name  from komplekt_ved as p  left join document_status as s on p.status_id=s.id where p.status_id<>3 and p.is_leading=0 and p.leading_komplekt_ved_id="'.$id.'"';
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->getResult();
			$rc=$set->GetResultNumRows();
			$reasons=array();
			for($i=0; $i<$rc; $i++){
				$v=mysqli_fetch_array($rs);
			  //  $dsi=$_dsi->GetItemById($v['status_id']);
				
				
			  
			   
			   $reasons[]=' ������ � '.$v['id'].' ������ ���������: '.$v['name'];	
			   
			   
			   //���� ����� ��/���
			    $set1=new mysqlSet('select p.*, s.name  from bill as p left join document_status as s on p.status_id=s.id where status_id<>3 and p.id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$v['id'].'")');
				  $rs1=$set1->getResult();
				  $rc1=$set1->GetResultNumRows();
				 
				  for($i1=0; $i1<$rc1; $i1++){
					  $vv=mysqli_fetch_array($rs1);
					  //$dsi=$_dsi->GetItemById($v['status_id']);
					  
						  
						  $reasons[]=' ���� � '.$vv['code'].' ������ ���������: '.$vv['name'];	
						  
				  }
					 
				 
			
				//���� ����/����		 
				
				 $set1=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where status_id<>6 and bill_id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$v['id'].'")');
				  $rs1=$set1->getResult();
				  $rc1=$set1->GetResultNumRows();
				   
				  for($i1=0; $i1<$rc1; $i1++){
					  $vv=mysqli_fetch_array($rs1);
					//  $dsi=$_dsi->GetItemById($v['status_id']);
					  
					  
					 if($vv['is_incoming']==1) $reasons[]=' ����������� � '.$vv['id'].' ������ ���������: '.$vv['name'];	
					 else $reasons[]=' ���������� � '.$vv['id'].' ������ ���������: '.$vv['name'];	
					 
					  
				  }
		 
		  		 
			   
				
			}
			if(count($reasons)>0) $reason.=" �� ������ ������� ��������� ��������� � ������ �����������: ";
			$reason.=implode(', ',$reasons);
			
			
		}
		 
		
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		//������ ������ �� ������������� ���������� � ������
		
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		$_bill=new BillItem;
		$_bill_in=new BillInItem;
		
		$set=new MysqlSet('select * from acceptance where bill_id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			if($f['is_incoming']==1){
				$log->PutEntry($_result['id'],'������������� ����������� � ����� � �������������� ������',NULL,626,NULL,'����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['bill_id']);
				$log->PutEntry($_result['id'],'������������� ����������� � ����� � �������������� ������',NULL,674,NULL,'����������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
			}else{
				$log->PutEntry($_result['id'],'������������� ���������� � ����� � �������������� ������',NULL,94,NULL,'���������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['bill_id']);
				$log->PutEntry($_result['id'],'������������� ���������� � ����� � �������������� ������',NULL,242,NULL,'���������� � '.$f['id'].': ���������� ������ '.$stat['name'],$f['id']);
			}
		}	
			
		
		$ns=new NonSet('update acceptance set status_id=6 where bill_id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		
		
 
	
		
		$set=new MysqlSet('select * from bill  where id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			
			if($f['is_incoming']==1){
				if($f['is_confirmed_price']==1)  $_bill_in->FreeBindedPayments($f['id'],0,$_result);
				
				$log->PutEntry($_result['id'],'������������� ����� � ����� � �������������� ������',NULL,626,NULL,'���� � '.$f['code'].': ���������� ������ '.$stat['name'],$f['id']);
			}else{
				if($f['is_confirmed_price']==1)  $_bill->FreeBindedPayments($f['id'],0,$_result);
				
				$log->PutEntry($_result['id'],'������������� ����� � ����� � �������������� ������',NULL,94,NULL,'���� � '.$f['code'].': ���������� ������ '.$stat['name'],$f['id']);
			}
					
		}
		
		$ns=new NonSet('update  bill set status_id=3, is_confirmed_price=0, is_confirmed_shipping=0  where id in(select distinct bill_id from bill_position where komplekt_ved_id="'.$id.'")');	
		
		
		//���� ���  ������� ������ - ������� ��������� ������������ � ������ �����
	}
	
	
	
	
	//����� ��� ������������ �� ������������
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		$output=''; $items=array();
		if($sh===NULL) $sh=$this->GetItemById($id);
		$_sh2=new BillItem;
		$_sh3=new BillInItem;
		$log=new ActionLog();
		$au=new AuthUser;
		
		$_ni=new KomplNotesItem;
		
		if($_result===NULL) $_result=$au->Auth();
		
		if($sh['is_active']==0){
			$output='������������ ������� ����������: �� ���������� ������.';
			return;
		}
		
		//��������� ����� �������. �����������
		$items=$this->ScanEq($id, $args, $output, $sh, $express_scan);
		
		
		
		
		
		
		//print_r($items);
		//die();
		
				
		//������� ��� ����������� ���. �����, ������� ��
		if($is_auto==0){
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  
			  $sql='select 
			  	sp.quantity, s.id, s.code 
			  from bill as s 
			  inner join bill_position as sp on s.id=sp.bill_id 
			  where 
			  	s.is_confirmed_shipping=1 
				and s.is_incoming=0
				and sp.sector_id="'.$_t_arr[3].'" 
				and sp.position_id="'.$_t_arr[0].'" 
				and sp.komplekt_ved_id="'.$_t_arr[4].'"';
			  //echo $sql;
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  //print_r($f);
				  //$vv=array($v);
				  $vv=array($_t_arr[0].';'.$f['quantity'].';'.$_t_arr[2].';'.$_t_arr[3].';'.$_t_arr[4]);
				   
				  $_sh2->DoEq($f['id'], $vv,$output);
				  
			  }
			  
			  
			  //��������� ����� ����. ������:
			  //��������� � ��������� �����.
			 
			 /*  $sql='select 
			  	sp.quantity, s.id, s.code, s.out_bill_id
			  from bill as s 
			  inner join bill_position as sp on s.id=sp.bill_id 
			  where 
			  	s.is_confirmed_shipping=1 
				and s.is_incoming=1
					
				and sp.sector_id="'.$_t_arr[3].'" 
				and sp.position_id="'.$_t_arr[0].'" 
				and sp.komplekt_ved_id="'.$_t_arr[4].'"';
			  //echo $sql;
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  //print_r($f);
				  //$vv=array($v);
				  
				  //$vv=array($v.';'.$f['out_bill_id']);
				 // position_id="'.$_t_arr[0].'"
				 //$summ_in_doc+=$_t_arr[1];
				 //'storage_id'=>$_t_arr[2], 'sector_id'=>$_t_arr[3], 'komplekt_ved_id'=>$_t_arr[4],
				 //'out_bill_id'=>$_t_arr[5]
				  $vv=array($_t_arr[0].';'.$f['quantity'].';'.$_t_arr[2].';'.$_t_arr[3].';'.$_t_arr[4].';'.$f['out_bill_id']);
				   
				  $_sh3->DoEq($f['id'], $vv,$output);
				  
			  }*/
		  }
		}
		
		
		//����������� ������� ������
		$_sh_p=new KomplPosItem;
		$_p_name=new PosItem;
		
		foreach($items as $k=>$v){
			if($v['delta']==0) continue;
			$sh_p=$_sh_p->GetItemByFields(array('storage_id'=>$v['storage_id'], 'position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id']));
			
			if($sh_p!==false){
				$params=array();
				
				if($v['delta']>=0){
					
					$params['quantity_confirmed']=round(($v['quantity']-$v['delta']),3);
					
					
					//echo 'zzzzzzzzzzzzzz';
					
					$_sh_p->Edit($sh_p['id'], $params);
				
					//$description=$sh_p['name'].' <br /> ���-��: '.$params['quantity_confirmed'].'<br /> ';
					$p_name=$_p_name->GetItemById($v['position_id']);
				
					 $description='������ � '.$id.': '.$p_name['name'].' <br /> ���-��: '.$v['quantity'].' ���� �������� ��:  '.round($params['quantity_confirmed'],3).'<br /> ';
					 
					 
					 if($is_auto==1){
						 $log->PutEntry(0,'�������������� �������������� ������� ������ � ����� � ������������� �������',NULL,82,NULL,SecStr($description.$extra_reason),$id);	 
						 $posted_user_id=0;
						 $note='�������������� ����������: ������� ������ '.$p_name['name'].' ���� ��������� ��� �������������� ������������, ���-�� '.$v['quantity'].' ���� �������� �� '.round($params['quantity_confirmed'],3).''.$extra_reason;
					 }else{
						 $log->PutEntry($_result['id'],'������������ ������� ������ � ����� � ������������� �������',NULL,82,NULL,SecStr($description.$extra_reason),$id);
						 
						 $posted_user_id=$_result['id'];
					 	 $note='�������������� ����������: ������� ������ '.$p_name['name'].' ���� ���������, ���-�� '.$v['quantity'].' ���� �������� �� '.round($params['quantity_confirmed'],3).''.$extra_reason; 
					 }
					
					
					
					 $_ni->Add(array(
						'user_id'=>$id,
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$posted_user_id,
						'note'=>SecStr($note)
						));	
				}
			}
			
		}
		
		
		$output='������������ ������� ���������.';
		if(!$express_scan) $this->ScanDocStatus($id,array(),array());		
	}
	
	
	
	//������������ ������������ ����������� �����-��� � ��������
	public function ScanEq($id, array $args, &$output, $sh=NULL, $express_scan=false, $continue_message=".\n���������� ������������ ������ �������?", &$items_incoming){
		if($sh===NULL) $sh=$this->GetItemById($id);
		$items=array(); $items_incoming=array();
		$total_summ=0; $summ_in_doc=0;
		
		$total_summ1=0; $summ_in_doc1=0;
		
		$total_summ_incoming=0; $sum_in_incoming=0;
		
		$output='<ul>';
		$docs=array();  $docs1=array();
		
		$docs_ext=array();  $docs_ext1=array();
		
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		
		//������� �� �������� 
		$count_acc=0;  $count_acc1=0; 
		foreach($args as $k=>$v){
			$_t_arr=explode(';',$v);
			$summ=0;
			
			$summ_in_doc+=$_t_arr[1];
			
			//�� ������ ������� ��������� ��� ������� ����������� ����������
			$sql='select * from acceptance_position where acceptance_id in
			(select id from acceptance where is_confirmed=1 and is_incoming=0) 
			and position_id="'.$_t_arr[0].'" and komplekt_ved_id="'.$_t_arr[4].'"';
			
			//echo $sql;
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$summ+=$f['quantity'];
				$count_acc+=$f['quantity'];
				if(!in_array('�'.$f['acceptance_id'],$docs)) {
					$docs[]='�'.$f['acceptance_id'];
					$docs_ext[]='<a href="ed_acc.php?action=1&id='.$f['acceptance_id'].'" target="_blank">'.'�'.$f['acceptance_id'].'</a>';
				}
			}
			
			//$items[]=array('position_id'=>$_t_arr[0], 'quantity'=>$_t_arr[1], 'storage_id'=>$_t_arr[2], 'sector_id'=>$_t_arr[3], 'komplekt_ved_id'=>$_t_arr[4], 'delta'=>round(($_t_arr[1]-$summ),3));
			
			$pos=$_pos->GetItemById($_t_arr[0]);
			$pdi=$_pdi->GetItemById($pos['dimension_id']);
			
			
			
			
			$total_summ+=$summ;	
			
			
			
			$summ1=0;
			
			//�� ������ ������� ��������� ��� ������� ����������� �����������
			$sql='select * from acceptance_position where acceptance_id in
			(select id from acceptance where is_confirmed=1 and is_incoming=1) 
			and position_id="'.$_t_arr[0].'" and komplekt_ved_id="'.$_t_arr[4].'"';
			
			//echo $sql;
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$summ1+=$f['quantity'];
				$count_acc1+=$f['quantity'];
				if(!in_array('�'.$f['acceptance_id'],$docs1)){
					 $docs1[]='�'.$f['acceptance_id'];
					$docs_ext1[]='<a href="ed_acc_in.php?action=1&id='.$f['acceptance_id'].'" target="_blank">'.'�'.$f['acceptance_id'].'</a>';	 
				}
			}
			
			$items[]=array('position_id'=>$_t_arr[0], 'quantity'=>$_t_arr[1], 'storage_id'=>$_t_arr[2], 'sector_id'=>$_t_arr[3], 'komplekt_ved_id'=>$_t_arr[4], 'delta'=>round(($_t_arr[1]-$summ1),3));
			
			$total_summ1+=$summ1;	 
			
		}
			
			
		
		
		$docs3=array(); $docs_ext3=array();
		$count_incoming_bills=0;
		$count_incoming_accs=0;
		
		if(!$express_scan){
		  //�����������  ����� �������� ����������!
		  
		}
		
		
		$docs2=array(); $docs_ext2=array();
		$count_blls=0;
		
		
		$docs3=array();
		$count_blls1=0; $total_blls_summ=0; $total_blls_summ1=0;
		if(!$express_scan){
		  //������� �� ��������
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  $summ=0;
			  
			  
			  //�� ������ ������� ��������� ��� ������� ����������� ��� ��
			  $sql='select bp.* from bill_position as bp 
			  inner join bill as b on bp.bill_id=b.id
			   where 
			   b.is_confirmed_shipping=1 and
			   b.is_incoming=0 and 
				bp.position_id="'.$_t_arr[0].'" and 
				bp.komplekt_ved_id="'.$_t_arr[4].'"';
			  
			  //echo "$sql <br>";
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $summ+=$f['quantity'];
				  $count_blls+=$f['quantity'];
				  $_bi=new BillItem;
				  $bi=$_bi->GetItemById($f['bill_id']);
				  if(!in_array('�'.$bi['code'],$docs2)){
					   $docs2[]='�'.$bi['code'];
					   $docs_ext2[]='<a href="ed_bill.php?action=1&id='.$f['bill_id'].'" target="_blank">'.'�'.$bi['code'].'</a>';	   
				  }
			  }
			  
		  
			  $total_blls_summ+=$summ;	
			  
			  $summ1=0;
			  
			  
			  //�� ������ ������� ��������� ��� ������� ����������� ���� ��
			  $sql='select bp.* from bill_position as bp 
			  inner join bill as b on bp.bill_id=b.id
			   where 
			   b.is_confirmed_shipping=1 and
			   b.is_incoming=1 and 
				bp.position_id="'.$_t_arr[0].'" and 
				bp.komplekt_ved_id="'.$_t_arr[4].'"';
			  
			//  echo "$sql <br>";
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $summ1+=$f['quantity'];
				  $count_blls1+=$f['quantity'];
				  $_bi=new BillInItem;
				  $bi=$_bi->GetItemById($f['bill_id']);
				  if(!in_array('�'.$bi['code'],$docs3)) {
					  $docs3[]='�'.$bi['code'];
					  $docs_ext3[]='<a href="ed_bill_in.php?action=1&id='.$f['bill_id'].'" target="_blank">'.'�'.$bi['code'].'</a>';	   
				  }
			  }
			  
			  $total_blls_summ1+=$summ1;
		  
			  
		  }
		}
		
		
		 
		
		if(($total_summ==0)&&($total_summ1==0)){
			$output.="<li>\n������� ".htmlspecialchars($pos["name"])." �� ������� �� � ����� ������������ ����������� ����������� � ����������. ���������� ����� ��������.</li>";
		}else{
			
			
			
			if(!$express_scan){
			    
			  
			  if(count($docs2)) {
				  $output.="<li>\n������� ".htmlspecialchars($pos["name"])." ������� � ������������ ��������� ������: ".implode(", ",$docs_ext2)." � ���������� ".$count_blls." ".htmlspecialchars($pdi["name"]);
				  if($count_blls>$summ_in_doc){
					   $output.=', ��� ��������� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				   }
				   
				   $output.='</li>';
			  }else $output.="<li>\n������� ".htmlspecialchars($pos["name"])." �� ������� � ������������ ��������� ������</li>";
			  
			  if(count($docs3)) {
				  $output.="<li>\n������� ".htmlspecialchars($pos["name"])." ������� � ������������ �������� ������: ".implode(", ",$docs_ext3)." � ���������� ".$count_blls1." ".htmlspecialchars($pdi["name"]);
				  if($count_blls1>$summ_in_doc){
					   $output.=', ��� ��������� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				   }
				   $output.='</li>';
				   
			  }else $output.="<li>\n������� ".htmlspecialchars($pos["name"])." �� ������� � ������������ �������� ������</li>";
			
			}
			
			 
			
			if(count($docs)){
				 $output.="<li>\n������� ".htmlspecialchars($pos["name"])." ������� � ������������ �����������: ".implode(", ",$docs_ext)." � ���������� ".$count_acc." ".htmlspecialchars($pdi["name"]);
				 if((float)$count_acc>(float)$summ_in_doc){
					 $output.=', ��� ��������� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }elseif((float)$count_acc<(float)$summ_in_doc){
					 $output.=', ��� ������, ��� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).''; 
				 }elseif((float)$count_acc==(float)$summ_in_doc){
					 $output.=', ��� ����� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).''; 
				 }
				 $output.='</li>';
			}else $output.="<li>\n������� ".htmlspecialchars($pos["name"])." �� ������� � ������������ �����������</li>";
			
			
			if(count($docs1)){
				 $output.="<li>\n������� ".htmlspecialchars($pos["name"])." ������� � ������������ ������������: ".implode(", ",$docs_ext1)." � ���������� ".$count_acc1." ".htmlspecialchars($pdi["name"]);
				 if((float)$count_acc1>(float)$summ_in_doc){
					 $output.=', ��� ��������� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }elseif((float)$count_acc1<(float)$summ_in_doc){
					 $output.=', ��� ������, ��� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).''; 
				 }elseif((float)$count_acc1==(float)$summ_in_doc){
					 $output.=', ��� ����� ���������� � ������ '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).''; 
				 }
				 $output.='</li>';
				 
			}else $output.="<li>\n������� ".htmlspecialchars($pos["name"])." �� ������� � ������������ ������������</li>";
			
			
			
			if(($count_acc>=$summ_in_doc)&&($count_acc1>=$summ_in_doc)){
				$output.='<li>';
				$output.="������� ������������ �� ��������.";
				$output.='</li>';
			}else $output.=$continue_message;
			
			
			
		}
		
		$output.='</ul>';		
		
		
		 
		
		return $items;
	}
	
	
	
	
	
	
	
	
	//����� ������ (�����.) �� ���� � �����. ������
	public function ScanUslEq($id,  &$output, $sh=NULL, $continue_message=".\n���������� ������������ ������ �������?", &$items_incoming, &$items){
		if($sh===NULL) $sh=$this->GetItemById($id);
		$items=array(); $items_incoming=array();
		$total_summ=0; $summ_in_doc=0;
		 
		
		$total_summ_incoming=0; $sum_in_incoming=0;
		
		$count=0;
		
		//������ ��. �����
		$sql='select distinct b.id from bill_position as bp 
			  inner join bill as b on bp.bill_id=b.id
			   where 
			   b.is_confirmed_shipping=1 and
			   b.is_incoming=1 and 
			   bp.komplekt_ved_id="'.$id.'"
		';
		
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//������ ������ ��. ������
			$sql1='select bp.* from bill_position as bp 
				  inner join bill as b on bp.bill_id=b.id
				   where 
				   b.is_confirmed_shipping=1 
				   and b.id="'.$f['id'].'"
				   and b.is_incoming=1  
				  /* and b.out_bill_id in(select id from bill where is_incoming=0 and b.komplekt_ved_id="'.$id.'")*/
				   and bp.position_id in('.implode(', ',self::$position_uslugi).')
				   ';
			   
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			$poss=array();
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1);
				
				//������� ������� ��������� �����������
				$sql2='select * from acceptance_position where acceptance_id in
				(select id from acceptance where is_confirmed=1 and is_incoming=1 and bill_id="'.$f['id'].'") 
				and position_id="'.$g['position_id'].'" and komplekt_ved_id="'.$g['komplekt_ved_id'].'"';
				
				
				$summ1=0;
				$set2=new MysqlSet($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($i2=0; $i2<$rc2; $i2++){
					$h=mysqli_fetch_array($rs2);
					$summ1+=$h['quantity'];
					//$count_acc1+=$f['quantity'];
					//if(!in_array('�'.$f['acceptance_id'],$docs1)) $docs1[]='�'.$f['acceptance_id'];
				}
				
				// $poss[]=array('position_id'=>$g['position_id'], 'quantity'=>$g['quantity'], 'storage_id'=>$g['storage_id'], 'sector_id'=>$g['sector_id'], 'komplekt_ved_id'=>$id, 'delta'=>round(($g['quantity']-$summ1),3), 'out_bill_id'=>$g['out_bill_id']);
				
				
				
				if(round(($g['quantity']-$summ1),3)>0) $poss[]=$g['position_id'].';'.$g['quantity'].';0;'.$g['sector_id'].';'.$g['komplekt_ved_id'].';'.$g['out_bill_id'];	
			}
			
			$count+=count($poss);
			$items_incoming[]=array(
				'bill_id'=>$f['id'],
				'poss'=>$poss
				);
		}
		
		
		
		
		
		
		//������ ���. �����
		$sql='select distinct b.id from bill_position as bp 
			  inner join bill as b on bp.bill_id=b.id
			   where 
			   b.is_confirmed_shipping=1 and
			   b.is_incoming=0 and 
			   bp.komplekt_ved_id="'.$id.'"
		';
		 
		 
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//������ ������ ���. ������
			$sql1='select bp.* from bill_position as bp 
				  inner join bill as b on bp.bill_id=b.id
				   where 
				   b.is_confirmed_shipping=1 
				   and b.id="'.$f['id'].'"
				   and b.is_incoming=0 
				  /* and b.komplekt_ved_id="'.$id.'"*/
				   and bp.position_id in('.implode(', ',self::$position_uslugi).')
				   ';
				   
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			$poss=array();
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1);
				
				//������� ������� ��������� ����������
				$sql2='select * from acceptance_position where acceptance_id in
				(select id from acceptance where is_confirmed=1 and is_incoming=0 and bill_id="'.$f['id'].'") 
				and position_id="'.$g['position_id'].'" and komplekt_ved_id="'.$g['komplekt_ved_id'].'"';
				
				//echo $sql."\n";	
				//echo $sql;
				$summ1=0;
				$set2=new MysqlSet($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				for($i2=0; $i2<$rc2; $i2++){
					$h=mysqli_fetch_array($rs2);
					$summ1+=$h['quantity'];
					 
				}
				
				if(round(($g['quantity']-$summ1),3)>0) $poss[]=$g['position_id'].';'.$g['quantity'].';0;'.$g['sector_id'].';'.$g['komplekt_ved_id'];	
				//echo $g['name'].' '.$g['quantity'].' '.$summ1;
				/*
				$poss[]=array('position_id'=>$g['position_id'], 'quantity'=>$g['quantity'], 'storage_id'=>$g['storage_id'], 'sector_id'=>$g['sector_id'], 'komplekt_ved_id'=>$id, 'delta'=>round(($g['quantity']-$summ1),3));	
				*/
				 
				
			}
			
			$count+=count($poss);
			
			$items[]=array(
				'bill_id'=>$f['id'],
				'poss'=>$poss
				);
		}
		
		return $count;	  
		
		
	}
	
	
	public function DoEqUsl($id, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		$output=''; $items=array();
		if($sh===NULL) $sh=$this->GetItemById($id);
		$_sh2=new BillItem;
		$_sh3=new BillInItem;
		$log=new ActionLog();
		$au=new AuthUser;
		
		$_ni=new KomplNotesItem;
		
		if($_result===NULL) $_result=$au->Auth();
		
		if($sh['is_active']==0){
			$output='������������ ������� ����������: �� ���������� ������.';
			return;
		}
		
		//��������� ����� �������. �����������
		$this->ScanUslEq($id, $output, $sh, '', $items_incoming, $items);  //ScanEq($id, $args, $output, $sh, $express_scan);
		
		foreach($items as $k=>$v){
			if(count($v['poss'])>0) $_sh2->DoEq($v['bill_id'],  $v['poss'],$output);
		}
		
		foreach($items_incoming as $k=>$v){
			if(count($v['poss'])>0) $_sh3->DoEq($v['bill_id'],   $v['poss'],$output);
		}
		
		 
		
		$output='������������ ������� ���������.';
		 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//��������, ���� �� ������ � ����� �������� � ����� �������� �������
	public function CheckCode($code, $sector_id, $except_id=0){
			
			if(strlen($code)==0) return 0;
			
			$sql='select count(*) from '.$this->tablename.' where sector_id="'.$sector_id.'" and code="'.$code.'" and id<>"'.$except_id.'"';
			
			
			//echo $sql;
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			$f=mysqli_fetch_array($rs);
			
			return (int)$f[0];
	}
	
	
	//�������� ����������� ������ ��������������
	public function CanEditQuantities($komplekt_id, &$reason,$ki=NULL,$sector_ss=NULL, $storage_ss=NULL, $result=NULL){
		$reason='';
		$res=false;
		
		if($ki===NULL)
			$ki=$this->getitembyid($komplekt_id);
		
		$_kcg=new KomplConfGroup;
		$au=new AuthUser();
		if($result===NULL) $result=$au->Auth();
		
		
		 
		 
		//$res=($ki['is_active']==0);	
		
		if($au->user_rights->CheckAccess('w',$this->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss,$storage_ss,array(446,447)))){
			//���� ���� ��������� �� ���-� ������� � ���-� ���-�� � ������ � ����� �������...
			return true;
		
		}elseif($au->user_rights->CheckAccess('w',$this->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss,$storage_ss,array(359,392)))&&$_kcg->HasConfirmByRole($komplekt_id, 3)&&!$_kcg->HasConfirmByRole($komplekt_id, 6)){
			//���� ���� ����� �� ��� � ���� ��� + ������ ���������� � ���� ��� ��-�� � �� ��� � ���� ���.
			return true;
		}elseif($ki['is_active']==1){
			 $reason='������ ����������';
			 return false; 
		
		
		
		
		}elseif($au->user_rights->CheckAccess('w',$this->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss,$storage_ss,array(301,406)))){
		
		 
			  //�������� ������ �� �����...
			  //������ ����� ����, ������� ���� � ������� ������������
			 $sql='select *
			  from komplekt_ved_confirm_roles 
			  
			  ';
			  
			  
			  $set=new MysqlSet($sql);
			  
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  
			 
			  
			  
			  $has_role=false; $role_ids=array(); $role_names=array(); 
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  
				  if($au->user_rights->CheckAccess('w',$f[$this->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss,$storage_ss,array('confirm_object_id','ss_confirm_object_id'))])){
						$has_role=true;
						$role_ids[]=$f['id'];  
						$role_names[]=$f['name'];
						//break;
				  }
			  }
			  
			  //���� ���� ������ �� ����� - �� ���-�� ����� ������ ���� ��� ���. �� ����� ����
			  if($has_role){
			  		//���� �� ���. �� ����� ����? ���� ����, �� �� ����� �������������...
					
					$sql1='select kc.*, c.name from komplekt_ved_confirm as kc left join komplekt_ved_confirm_roles as c on c.id=kc.role_id where kc.role_id in('.implode(',',$role_ids).') and kc.komplekt_ved_id="'.$komplekt_id.'" ';
					$set1=new MysqlSet($sql1);
			  		$rs1=$set1->GetResult();
					$rc1=$set1->GetResultNumrows();
					
					if((int)$rc1>0){
						
						//�������� ������ - ���� ���� ����������� �� ���� ��������� ����� - ������ �������������
						//���� ��� ����������� �� ���� �� ����� �� ��������� ����� - ���-�� �����!
						if((int)$rc1<count($role_ids)){
							$res=true;
						}else{
						
						  $res=false;
						  
						  $role_names=array();
						  for($i=0; $i<$rc1; $i++){
							  $g=mysqli_fetch_array($rs1);
							  $role_names[]=$g['name'];
						  }
						  
						  $reason='������ ���������� � ��������� ��� ����: '.implode(', ', $role_names).', ��������� ����� ���� �� ���� ����������� �� ��������� �����';
						}
					}else{
						$res=true;
					}
					
					if($res){
						
						//���� ������ ���-�� � ���� ���, ��:
						//���� ���� ��� ������� (������ ���������� � ���� ���)
						//� ���� ������������ - �� ���
						//�� �� ������ �������������
						if($_kcg->HasConfirmByRole($komplekt_id,6)&&!$au->user_rights->CheckAccess('w', $this->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss,$storage_ss,array(359,392)))	){
							$res=false;
							$reason='���������� ������� � ������ ���������� ����������� ���������� � ����������� ������ ���������';
									
						}
							
					}
					
			  }else{
			  //���� ��� ������� �� ����� - �� ������������� ����� ������ ���� ��� �� 1 �����������...
			  	if($_kcg->HasAnyConfirm($komplekt_id)){
					$res=false;
					$reason='������ ���������� ����� ��� ����� ������������� �����������';	
				}else{
					$res=true;
				}
			  }
			
		}else{
			
			$reason='������������ ���� ��� ������� ��������';
			$res=false;	 
		}
		
		return $res;
	}
	
	
	
	
	//���������� �������� ���� ���������� ���. ����������� �� ������...
	public function GetClosePdate($id, &$descr){
		$descr='';
		$sql='select distinct a.given_pdate, a.id, a.given_no from acceptance as a inner join acceptance_position as ap on ap.acceptance_id=a.id and ap.komplekt_ved_id='.$id.' 
		where a.is_confirmed=1 order by a.given_pdate desc limit 1';	
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			$descr='� '.$f[1].', �������� ����� '.$f[2].', �������� ���� '.date('d.m.Y',$f[0]);
			return $f[0];
		}else return 0;
	}
	
	//�������� ������� ���� �� ��������� � �������� ������
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		$_test_pdate=$this->GetClosePdate($id,$descr);
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($_test_pdate, $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$rss='���� ���������� ������������� ����������� �� ������ (����������� '.$descr.') '.$rss23;	
		}
		  
		
		return $can;			
	}
	
	
	
	//����������� �� ������ ��������� ��������� �����
	public function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
	//����������� �� ������ ������� ��������� �����
	public function IsPosUsl($position_id){
		return in_array($position_id,self::$position_uslugi/*$this->uslugi*/);
	}
}
?>