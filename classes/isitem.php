<?
require_once('billitem.php');

require_once('acc_item.php');
require_once('ispositem.php');
require_once('billpospmformer.php');
require_once('isposgroup.php');
require_once('is_custom_item.php');
require_once('isnotesitem.php');
require_once('iswfpositem.php');

require_once('iswfposgroup.php');
require_once('iswf_group.php');
require_once('komplgroup.php');
require_once('komplitem.php');
require_once('maxformer.php');
require_once('authuser.php');
require_once('billcreator.php');
require_once('bdetailsitem.php');
require_once('actionlog.php');

require_once('billpositem.php');

require_once('acc_positem.php');

require_once('is_k_binder.php');

require_once('is_to_k_group.php');
require_once('wfitem.php');
require_once('messageitem.php');


require_once('user_s_item.php');
require_once('period_checker.php');

require_once('billnotesitem.php');

require_once('acc_notesitem.php');
require_once('isblink.php');

//����������� �������
class IsItem extends IsCustomItem{
	protected $is_or_writeoff;
	public $binder;
	
	public $isblink;
	
	
	public function __construct($is_or_writeoff=0){
		$this->init($is_or_writeoff);
		$this->isblink= new IsBlink;
		
	}
	
	
	//��������� ���� ����
	protected function init($is_or_writeoff=0){
		$this->tablename='interstore';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->is_or_writeoff=$is_or_writeoff;
		
		$this->binder=new IsToKBinder;
	}
	
	
	
	public function Edit($id,$params,$scan_status=false,$can_auto_annul=true,$result=NULL){
		$item=$this->GetItemById($id);
		
		
		$log=new ActionLog();
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		$_stat=new DocStatusItem;
		$_wi=new WfItem;
		
		$_wfpg=new IsPosGroup; $_wfp=new IsPosItem;
		
		//�� ������������� ����������� 1 ���.
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		
		AbstractItem::Edit($id, $params);
		
		//���� ������� ���. �������� - �������� ����������������� (���� ��������)
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==0)&&($item['is_confirmed']==1)&&$can_auto_annul){
			
			//if($this->DocCanAnnul($id,$rs)){
			//	echo 'zz'; die();
			
			$stat=$_stat->GetItemById(3);
				AbstractItem::Edit($id, array('status_id'=>3, 'confirm_pdate'=>time(), 'user_confirm_id'=>$result['id']));
				//������ � ������, ����������
				$log->PutEntry($result['id'],'������������� ������������ �� ��������',NULL,102,NULL, '������������� ������������ ������������ �� �������� � '.$id.', �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ����� ����������� ��������; ���������� ������ '.$stat['name'],$id);	
			
			//������ ����������
			$_ni=new IsNotesItem;
			$_ni->Add(array(
				'user_id'=>$id,
				'posted_user_id'=>$result['id'],
				'note'=>'�������������� ����������: �������� ��� ������������� �����������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ����� ����������� ��������.',
				'is_auto'=>1,
				'pdate'=>time()
					));	
			
			
				//���������������� ��� ��������� ���� �������� ��������� ???	
			$sql='select * from interstore where interstore_id="'.$id.'" and is_or_writeoff=1';
			$set1=new mysqlSet($sql);
			$rs1=$set1->getResult();
			$rc1=$set1->getResultNumRows();
			$_wi=new WfItem;
			for($i1=0; $i1<$rc1; $i1++){
				$f1=mysqli_fetch_array($rs1);
				//var_dump($f1);
				
				
				$_wi->Edit($f1['id'], array('status_id'=>3, 'is_confirmed_fill_wf'=>0, 'is_confirmed'=>0));	
				//��������������
				
				
				$log->PutEntry($result['id'],'�������������� ������������� ���� ��������',NULL,107,NULL,NULL,$f1['id']);
							   //������ ����������
								$_ni=new IsNotesItem;
								$_ni->Add(array(
									'user_id'=>$f1['id'],
									'posted_user_id'=>$result['id'],
									'note'=>'�������������� ����������: �������� ��� ������������� �����������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ������������ ������������ �� �������� �'.$f1['interstore_id'].'.',
									'is_auto'=>1,
									'pdate'=>time()
								));
				
			}
			
				
				//die();
				return;
			/*}else{
				echo $rs; die();	
			}*/
			
		}
		
		//���������� �������� - ��������� ������� �������� � ������
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$this->binder->BindKomplekt($id,$item['org_id'],'quantity');
		
		}
		
		//���� ������� ��� �������� - �� ����������� �������
		if(isset($params['is_confirmed_wf'])&&($params['is_confirmed_wf']==0)){
			$this->binder->BindKomplekt($id,$item['org_id'],'quantity');
			$this->RestorePositions($id);
		}
		
		//���� ���������� - �� ����������� �������
		if(isset($params['is_confirmed_wf'])&&($params['is_confirmed_wf']==1)){
			
			if($item!==false){
				 $this->ResyncPositions($id,$item['change_low_mode']);
				 $this->binder->BindKomplekt($id,$item['org_id'],'fact_quantity');
				 
				 
				 
				 //�������� �����, ������������, �����������...
				 if($item['is_confirmed_wf']==0){
					
					
					//�������� ���������... ���� ���� ��������� - ��������/�������� ��� ��������.
				 	/*
					
					
					$no_lacks=$this->CheckLack($id,$lss,$lacks,'quantity_initial');
					if(!$no_lacks){
						
						$check_wf=$_wi->GetItemByFields(array('interstore_id'=>$id));
						
						$wf_params=array();
						$wf_params['sender_storage_id']=$item['sender_storage_id'];
						$wf_params['sender_sector_id']=$item['sender_sector_id'];
						
						$wf_params['is_or_writeoff']=1;
						$wf_params['pdate']=time();
						$wf_params['org_id']=$result['org_id'];
						$wf_params['manager_id']=$result['id'];
						$wf_params['status_id']=2;
						$wf_params['is_confirmed_fill_wf']=1;
						$wf_params['confirm_fill_wf_pdate']=time();
						$wf_params['user_confirm_fill_wf_id']=$result['id'];
						$wf_params['interstore_id']=$id;
						$wf_params['is_j']=1;
						
						if($check_wf===false){
							//���� ��� - �������� ���
							$wf_id=$_wi->Add($wf_params);
							
							//�����. ����������, ������ � ������
							$log->PutEntry($result['id'],'�������������� �������� ���� ��������  ��������� � ����� � ������������ �������� �� ���������',NULL,105,NULL,NULL,$wf_id);
						   //������ ����������
							$_ni=new IsNotesItem;
							$_ni->Add(array(
								'user_id'=>$wf_id,
								'posted_user_id'=>$result['id'],
								'note'=>'�������������� ����������: �������� ��� ������������� ������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ���� ��������� ��������� �� ��������.',
								'is_auto'=>1,
								'pdate'=>time()
							));	
							
						}else{
							$wf_id=$check_wf['id'];
							//��� ���� - ����������� ���
							$_wi->Edit($wf_id, $wf_params);
							//�����. ����������, ������ � ������	
							$log->PutEntry($result['id'],'�������������� �������������� ���� ��������  ��������� � ����� � ������������ �������� �� ���������',NULL,106,NULL,NULL,$wf_id);
							
							$_ni=new IsNotesItem;
							$_ni->Add(array(
								'user_id'=>$wf_id,
								'posted_user_id'=>$result['id'],
								'note'=>'�������������� ����������: �������� ��� ������������� ������������/��������������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ���� ��������� ��������� �� ��������.',
								'is_auto'=>1,
								'pdate'=>time()
							));	
							
						}
						
						//������������� ������� ����������� ���� � ����� ����������...
						//���������� ������� ����... ���� ����� ���� � ��������� - ������ ���-�� ��� � ���������
						//���� ����� ��� � ��������� - �������.
						//���������� ������� ���������
						//���� � ��������� ���� �������, ����� ��� � ���� - �������.
					
						$wfpositions=$_wfpg->GetItemsByIdArr($wf_id);
						foreach($wfpositions as $wk=>$wv){
							$in_lack=false; $lack_index=0;
							foreach($lacks as $kk=>$vv){
								if($vv['id']==$wv['id']){
									$in_lack=true; $lack_index=$kk;
									break;	
								}
							}
							
							if($in_lack){
								$_wfp->Edit($wv['p_id'],array('quantity'=>$lacks[$lack_index]['lack']));	
								//�����. ����������, ������ � ������
								if($wv['quantity']!=$lacks[$lack_index]['lack']){
								  $descr=SecStr($wv['position_name']).', ���-�� '.$lacks[$lack_index]['lack'].' '.SecStr($wv['dim_name']);
								  $log->PutEntry($result['id'],'�������������� �������������� ������� ���� �������� ��������� � ����� � ������������ �������� �� ���������',NULL,257,NULL,$descr,$wf_id);
							  
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$wf_id,
									  'posted_user_id'=>$result['id'],
									  'note'=>'�������������� ����������: ������� '.$descr.' ���������������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ���� ���������� ��������� �� ������ �������.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));		
								}
							}else{
								$_wfp->Del($wv['p_id']);
								//�����. ����������, ������ � ������
								
								 $descr=SecStr($wv['position_name']).', ���-�� '.$wv['quantity'].' '.SecStr($wv['dim_name']);
								 $log->PutEntry($result['id'],'�������������� �������� ������� ���� �������� ��������� � ����� � ������������ �������� �� ���������',NULL,258,NULL,$descr,$wf_id);
								 //������ ����������
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$wf_id,
									  'posted_user_id'=>$result['id'],
									  'note'=>'�������������� ����������: ������� '.$descr.' �������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ������ ������� �� ����������.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));		
							}
						}
						//���� � ��������� ���� �������, ����� ��� � ���� - �������.
						foreach($lacks as $kk=>$vv){
							$in_wf=false; $wf_index=0;
							foreach($wfpositions as $kk=>$vv){
								if($vv['id']==$wv['id']){
									$in_wf=true; $wf_index=$kk;
									break;	
								}
							}
							
							if(!$in_wf){
								$_wfp->Add(array(
									'interstore_id'=>$wf_id,
									'position_id'=>$vv['id'],
									'name'=>SecStr($vv['position_name']),
									'dimension'=>SecStr($vv['dim_name']),
									'quantity'=>$vv['lack']
								));	
								
								//�����. ����������, ������ � ������	
								$descr=SecStr($vv['position_name']).', ���-�� '.$vv['lack'].' '.SecStr($wv['dim_name']);
								$log->PutEntry($result['id'],'�������������� �������� ������� ���� �������� ��������� � ����� � ������������ �������� �� ���������',NULL,257,NULL,$descr,$wf_id);
							
								$_ni=new IsNotesItem;
								$_ni->Add(array(
									'user_id'=>$wf_id,
									'posted_user_id'=>$result['id'],
									'note'=>'�������������� ����������: ������� '.$descr.' ���������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ���� ���������� ��������� �� ������ �������.',
									'is_auto'=>1,
									'pdate'=>time()
								));		
							}
						}
						
						//��������� ��������� ���� ����������� ��
						$usql='select u.* from user as u where u.is_active=1 and u.is_supply_user=1 and u.id in(select distinct user_id from supplier_to_user where org_id="'.$result['org_id'].'")';
						
						$uset=new mysqlSet($usql);
						$urs=$uset->GetResult();
						$urc=$uset->GetResultNumRows();
						$_mi=new MessageItem;
						
						$_ui=new UserSItem;
						$_si=new SectorItem;
						$_sti=new StorageItem;
						$ssi=$_si->GetItemById($item['sender_sector_id']);
						$ssti=$_sti->getitembyid($item['sender_storage_id']);
						
						$rsi=$_si->GetItemById($item['receiver_sector_id']);
						$rsti=$_sti->getitembyid($item['receiver_storage_id']);
						
						
						$ui=$_ui->getitembyid($params['user_confirm_wf_id']);
						
						$message_text='<div><em>������ ��������� ������������� �������������.</em></div>
						<div><strong>��������! ����� ���������!</strong></div>
						<br />
						<div>��� ����������� �������� �� ��������� � '.$id.' �� '.date('d.m.Y', $item['pdate']).' ������������� '.SecStr($ui['name_s']).' (����� '.SecStr($ui['login']).') 
						� ������� '.SecStr($ssi['name']).', ������� '.SecStr($ssti['name']).' �� ������� '.SecStr($rsi['name']).', ������ '.SecStr($rsti['name']).'
						���� ������� ��������� ����������:</div>
						';
						
						$message_text.='
						<br />';
						
						$ispositions=$this->GetPositionsArr($id);
						foreach($ispositions as $pk=>$pv){
							$message_text.='<div>'.sprintf("%05d", $pv['id']).' '.SecStr($pv['position_name']).' '.SecStr($pv['quantity']).' '.SecStr($pv['dim_name']).'</div>';	
							
						}
						
						$message_text.='
						<br />';
						
						$message_text.='<div>��������� ��� �������� � ������� '.SecStr($ssi['name']).', ������� '.SecStr($ssti['name']).':</div>';
						
						$test_new_pos=$this->GetPositionsArr($wf_id);
						foreach($test_new_pos as $pk=>$pv){
							$message_text.='<div>'.sprintf("%05d", $pv['id']).' '.SecStr($pv['position_name']).' '.SecStr($pv['quantity']).' '.SecStr($pv['dim_name']).'</div>';	
							
						}
						
						$message_text.='
						<br />
						<div>��� ������������� ������� ��� �� �������� ��������� � '.$wf_id.'.</div>';
						
						$message_text.='
						<br />
						
						
						<div>��� ����������� �������� �� ��������� ���������  '.SecStr($ui['name_s']).' (����� '.SecStr($ui['login']).') ��� ������������.</div>
						<div>����������, ������� ���� ��� ������������� ������ ���������.</div>
						';
						
						for($ui=0; $ui<$urc; $ui++){
							$uf=mysqli_fetch_array($urs);
							
							
							
							$params1=array();
							$params1['topic']='��������! ����� ���������!';
							$params1['txt']=$message_text;
							$params1['to_id']= $uf['id'];
							$params1['from_id']=-1;
							$params1['pdate']=time();	
							$_mi->Send(0,0,$params1,false);	
							
							//echo $message_text;
						}
						
						
						//����� ������������� ������� ���� ��������
						
						//���� ��� �������� ���� ������� ��� ������ �� �� �������� - ������������� ��� ����������
						
						$in_act_count=0;
						foreach($test_new_pos as $tk=>$tv){
							$in_act_count+=$tv['quantity'];	
						}
						if($in_act_count==0){
							//���������� ���.
							$check_wf=$_wi->GetItemById($wf_id);
							if(($check_wf!==false)&&($check_wf['status_id']<>3)){
								 //������� ���. 2
								 if($check_wf['is_confirmed']==1){
									 
									 $log->PutEntry($result['id'],'�������������� ������ ����������� �������� ���� ��������',NULL,265,NULL,NULL,$wf_id);
									 //������ ����������
									  $_ni=new IsNotesItem;
									  $_ni->Add(array(
										  'user_id'=>$wf_id,
										  'posted_user_id'=>$result['id'],
										  'note'=>'�������������� ����������: ����������� �������� ��������� ���� ������������� �����, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ���� �������� �� ����������.',
										  'is_auto'=>1,
										  'pdate'=>time()
									  ));	
								 }
								
								//������� ���. 1
								if($check_wf['is_confirmed_fill_wf']==1){
									 
									 $log->PutEntry($result['id'],'�������������� ������ ����������� ���������� ���� ��������',NULL,263,NULL,NULL,$wf_id);
									 //������ ����������
									  $_ni=new IsNotesItem;
									  $_ni->Add(array(
										  'user_id'=>$wf_id,
										  'posted_user_id'=>$result['id'],
										  'note'=>'�������������� ����������: ����������� ���������� ��������� ���� ������������� �����, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ���� �������� �� ����������.',
										  'is_auto'=>1,
										  'pdate'=>time()
									  ));	
								 }
							
								//���������� ���
								
								 $_wi->Edit($wf_id, array(
									'status_id'=>3,
									'is_confirmed_fill_wf'=>0,
									'confirm_fill_wf_pdate'=>time(),
									'user_confirm_fill_wf_id'=>$result['id'],
									'is_confirmed'=>0,
									'confirm_pdate'=>time(),
									'user_confirm_id'=>$result['id']								
								));
								
								$log->PutEntry($result['id'],'�������������� ������������� ���� ��������',NULL,107,NULL,NULL,$wf_id);
							   //������ ����������
								$_ni=new IsNotesItem;
								$_ni->Add(array(
									'user_id'=>$wf_id,
									'posted_user_id'=>$result['id'],
									'note'=>'�������������� ����������: �������� ��� ������������� �����������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ���� �������� �� ����������.',
									'is_auto'=>1,
									'pdate'=>time()
								));
							}
						}
						
					}else{
						//�������� �� �������, �������� ������� ���� � ���������� ���
						
						//
						$check_wf=$_wi->GetItemByFields(array('interstore_id'=>$id));
						if(($check_wf!==false)&&($check_wf['status_id']<>3)){
							//echo 'akt';
							
							//���������� ���
							 
							 //������� ������� ����
							 $wfpositions=$_wfpg->GetItemsByIdArr($check_wf['id']);
							 foreach($wfpositions as $wk=>$wv){
								 $_wfp->Del($wv['p_id']);
								 //������ � ������, �������������� ����������...
								 $descr=SecStr($wv['position_name']).', ���-�� '.$wv['quantity'].' '.SecStr($wv['dim_name']);
								 $log->PutEntry($result['id'],'�������������� �������� ������� ���� �������� ��������� � ����� � ������������ �������� �� ���������',NULL,258,NULL,$descr,$check_wf['id']);
								 //������ ����������
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$check_wf['id'],
									  'posted_user_id'=>$result['id'],
									  'note'=>'�������������� ����������: ������� '.$descr.' �������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ������ ������� �� ����������.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));	
								 
							 }
							 
							 //������� ���. 2
							 if($check_wf['is_confirmed']==1){
								 
								 $log->PutEntry($result['id'],'�������������� ������ ����������� �������� ���� ��������',NULL,265,NULL,NULL,$check_wf['id']);
								 //������ ����������
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$check_wf['id'],
									  'posted_user_id'=>$result['id'],
									  'note'=>'�������������� ����������: ����������� �������� ��������� ���� ������������� �����, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ���� �������� �� ����������.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));	
							 }
							
							//������� ���. 1
							if($check_wf['is_confirmed_fill_wf']==1){
								 
								 $log->PutEntry($result['id'],'�������������� ������ ����������� ���������� ���� ��������',NULL,263,NULL,NULL,$check_wf['id']);
								 //������ ����������
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$check_wf['id'],
									  'posted_user_id'=>$result['id'],
									  'note'=>'�������������� ����������: ����������� ���������� ��������� ���� ������������� �����, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ���� �������� �� ����������.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));	
							 }
							 
							 $_wi->Edit($check_wf['id'], array(
								'status_id'=>3,
								'is_confirmed_fill_wf'=>0,
								'confirm_fill_wf_pdate'=>time(),
								'user_confirm_fill_wf_id'=>$result['id'],
								'is_confirmed'=>0,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>$result['id']								
							));
							
							$log->PutEntry($result['id'],'�������������� ������������� ���� ��������',NULL,107,NULL,NULL,$check_wf['id']);
						   //������ ����������
							$_ni=new IsNotesItem;
							$_ni->Add(array(
								'user_id'=>$check_wf['id'],
								'posted_user_id'=>$result['id'],
								'note'=>'�������������� ����������: �������� ��� ������������� �����������, �������: ������������� '.SecStr($result['name_s']).' ('.$result['login'].') ���� ���������� �������� �� ��������� �'.$id.', ��������� �� ���� �������� �� ����������.',
								'is_auto'=>1,
								'pdate'=>time()
							));	
							
							
							
							
						}
						//echo 'zzzz'; die();
					
					}
					
					*/ 
					 
//*****************************������ �� ���������� ������, ������, ����. //
					 
					 
					 //���������, ������ �� ����, ���� ������ - �����
					$set1=new mysqlset('select count(*) from bill where org_id="'.$result['org_id'].'" and interstore_id="'.$id.'"');
					
					
					$rs1=$set1->getResult();
					$f1=mysqli_fetch_array($rs1);
					
					
					$ts=$this->binder->CheckKomplekt($id, $result['org_id'],'fact_quantity', $komplekt_ved_ids);
					//� ������, ���� ������ �� ����������� - ������� � komplekt_ved_ids ����
					if(count($komplekt_ved_ids)==0) $komplekt_ved_ids=array(0);
					if($ts&&((int)$f1[0]==0)){
						//������� ����, ������������, �����������
						$_ki=new KomplItem;
					  	$_bi=new BillItem;
					  	$_shi=new ShIItem;
					  	$_acc=new AccItem;
						$_ikg=new IsToKGroup;
						
						//������� ���������
						$positions=$this->GetPositionsArr($id);
						
						//������� �������� � ������
						$binded_positions=$_ikg->GetItemsByIdArr($id);
						
						 //������� ����
						$inner_params=array();
						$inner_params['komplekt_ved_id']=$komplekt_ved_ids[0];
						$inner_params['interstore_id']=$id;
						$inner_params['supplier_id']=$result['org_id'];
						$inner_params['org_id']=$result['org_id'];
						$lc=new BillCreator;
						$lc->ses->ClearOldSessions();
						$inner_params['code']=$lc->GenLogin($result['id']);
						
					   
						//����� ���������
						$_bdi=new BDetailsItem;
						$bdi=$_bdi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$result['org_id']));
						$inner_params['bdetails_id']=$bdi['id'];
						
						
						$inner_params['storage_id']=$item['receiver_storage_id'];
						$inner_params['sector_id']=$item['receiver_sector_id'];
						$inner_params['manager_id']=$result['id'];
						$inner_params['pdate']=time();
						$inner_params['status_id']=2;
						$inner_params['is_confirmed_price']=1;
						$inner_params['is_confirmed_shipping']=1;
						
						$inner_params['user_confirm_shipping_id']=$result['id'];
						$inner_params['user_confirm_price_id']=$result['id'];
						$inner_params['confirm_price_pdate']=time();
						$inner_params['confirm_shipping_pdate']=time();
						
						$bill_id=$_bi->Add($inner_params);
						  
						$log->PutEntry($result['id'],'������ �������� ���� �� ����������� �������� ������������ �� ���������',NULL,92,NULL,NULL,$bill_id);
						$log->PutEntry($result['id'],'������ �������� ���� �� ����������� �������� ������������ �� ���������',NULL,103,NULL,NULL,$id);
						
						//���������� � ����
						//������ ����������
						$_ni=new BillNotesItem;
						$_ni->Add(array(
							'user_id'=>$bill_id,
							'posted_user_id'=>$result['id'],
							'note'=>'�������������� ����������: �������� ���� ��� ������ �� ��������� ����������� �������� �� ��������� �'.$id.'.',
							'is_auto'=>1,
							'pdate'=>time()
						));	
						
						
						
						//���������� ������� �����
						$bill_positions=array();
					  	foreach($komplekt_ved_ids as $kh=>$kvid){
							
							//������� ������� ��������. ��� ������ ������� �������� ����������� �� ������� ��������� �������� � �� ��� ������
							//���� ������� �������� ��� - �� �������� �� ������� ���������
							if(count($binded_positions)>0) {
								foreach($binded_positions as $k=>$v){
								  if($v['komplekt_ved_id']!=$kvid) continue;
								  if($v['quantity']==0) continue;
								  
								  $name=''; $dimension='';
								  foreach($positions as $kk=>$vv){	
									  if($vv['id']==$v['position_id']){
										  $name=$vv['position_name']; $dimension=$vv['dim_name'];
										  break;	
									  }
								  }
								  
								  
								  $bill_positions[]=array(
									  'bill_id'=>$bill_id,
									  'komplekt_ved_pos_id'=>0,
									  'position_id'=>$v['position_id'],
									  'name'=>SecStr($name),
									  'dimension'=>SecStr($dimension),
									  'quantity'=>$v['quantity'],
									  'price'=>0,
									  'storage_id'=>$item['receiver_storage_id'],
									  'sector_id'=>$item['receiver_sector_id'],
									  'komplekt_ved_id'=>$kvid,
									  'pms'=>NULL
								  );
							  }
							}else{
								
								//�������� �� ������� ���-��
								foreach($positions as $k=>$v){
								  
								  if($v['fact_quantity']==0) continue;	
								  //������� � �������� ���������� �� ����������� ����������
								  $bill_positions[]=array(
										'bill_id'=>$bill_id,
										'komplekt_ved_pos_id'=>0,
										'position_id'=>$v['id'],
										'name'=>SecStr($v['position_name']),
										'dimension'=>SecStr($v['dim_name']),
										'quantity'=>$v['fact_quantity'],
										'price'=>0,
										'storage_id'=>$item['receiver_storage_id'],
										'sector_id'=>$item['receiver_sector_id'],
										'komplekt_ved_id'=>$komplekt_ved_ids[0],
										'pms'=>NULL
									);
								}
							}
							
						}
						
						//������� ������������, ������ �� � ����
						$log_entries=$_bi->AddPositions($bill_id,$bill_positions);
					  
						foreach($log_entries as $kk=>$vv){
							$description=$vv['name'].' <br /> ���-��: '.$vv['quantity'].'<br /> '.'���� '.$vv['price'].' ���. <br />';
							
							if($vv['action']==0){
								$log->PutEntry($result['id'],'������� ������� ��������� �����',NULL,93,NULL,$description,$bill_id);
								$log->PutEntry($result['id'],'������� ������� ��������� �����',NULL,103,NULL,$description,$id);	
							}
						}
						
						
						
						
						
						
						
						//������������ �� �������...
						//������������ ����! �������� ��� �� ������!
						
						$inner_params=array();
						$inner_params['bill_id']=$bill_id;
						$inner_params['user_confirm_id']=$result['id'];
						$inner_params['confirm_pdate']=time();
						$inner_params['pdate']=time();
						$inner_params['is_confirmed']=1;
						
						$inner_params['org_id']=$result['org_id'];
						$inner_params['manager_id']=$result['id'];
						$inner_params['status_id']=2;
						$inner_params['storage_id']=$item['receiver_storage_id'];
						$inner_params['sector_id']=$item['receiver_sector_id'];
						$inner_params['komplekt_ved_id']=$komplekt_ved_ids[0];
						
						$sh_i_id=$_shi->Add($inner_params);
						$log->PutEntry($result['id'],'������ ������������ �� ������� �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,93,NULL,NULL,$bill_id);
						
						$log->PutEntry($result['id'],'������ ������������ �� ������� �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,215,NULL,NULL,$sh_i_id);
						  
						
						 $log->PutEntry($result['id'],'������ ������������ �� ������� �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,103,NULL,NULL,$id);
						
						$_ni=new ShINotesItem;
						$_ni->Add(array(
							'user_id'=>$sh_i_id,
							'posted_user_id'=>$result['id'],
							'note'=>'�������������� ����������: ������������ �� ������� ���� ������� �� ��������� ����������� �������� �� ��������� �'.$id.'.',
							'is_auto'=>1,
							'pdate'=>time()
						));	
						
						
						
						//������� ������������
						$bill_positions=array();
						foreach($komplekt_ved_ids as $kh=>$kvid){
						//���� ������� �������� ��� - �� �������� �� ������� ���������
							if(count($binded_positions)>0) {	
							//����� ���-�
								 
								 // var_dump($binded_positions);
								  foreach($binded_positions as $k=>$v){
									if($v['komplekt_ved_id']!=$kvid) continue;
									if($v['quantity']==0) continue;
									
									$name=''; $dimension='';
									foreach($positions as $kk=>$vv){	
										if($vv['id']==$v['position_id']){
											$name=$vv['position_name']; $dimension=$vv['dim_name'];
											break;	
										}
									}
									
									//echo 'zzz ';
									$bill_positions[]=array(
										'sh_i_id'=>$sh_i_id,
										'komplekt_ved_pos_id'=>0,
										'position_id'=>$v['position_id'],
										'name'=>SecStr($name),
										'dimension'=>SecStr($dimension),
										'quantity'=>$v['quantity'],
										'komplekt_ved_id'=>$kvid,
										'price'=>0,
										
										'pms'=>NULL
									);
								}
								
								//print_r($bill_positions);
								
								
								
							}else{
							//������ ���-�	
								 
								  //�������� ������� ������������
								  
								  foreach($positions as $k=>$v){
									if($v['fact_quantity']==0) continue;
									//������� � �������� ���������� �� ����������� ����������
									$bill_positions[]=array(
										  'sh_i_id'=>$sh_i_id,
										  'komplekt_ved_pos_id'=>0,
										  'position_id'=>$v['id'],
										  'name'=>SecStr($v['position_name']),
										  'dimension'=>SecStr($v['dim_name']),
										  'quantity'=>$v['fact_quantity'],
										  'komplekt_ved_id'=>$komplekt_ved_ids[0],
										  'price'=>0,
										  
										  'pms'=>NULL
									  );
								  }
								  
								 
							}
							
							
							
								
						}
						
						
						$log_entries=$_shi->AddPositions($sh_i_id,$bill_positions);
								  
						foreach($log_entries as $kk=>$vv){
							  $description=$vv['name'].' <br /> ���-��: '.$vv['quantity'].'<br /> '.'���� '.$vv['price'].' ���. <br />';
							  
							  
							  if($vv['action']==0){
								  $log->PutEntry($result['id'],'������� ������� ������������ �� �������',NULL,93,NULL,$description,$bill_id);	
								  
								  $log->PutEntry($result['id'],'������� ������� ������������ �� �������',NULL,216,NULL,$description,$sh_i_id);	
								  
								  
								  $log->PutEntry($result['id'],'������� ������� ������������ �� �������',NULL,103,NULL,$description,$id);	
							  }
						  }
						
						
						
						
						//�����������
						$inner_params=array();
						$inner_params['bill_id']=$bill_id;
						$inner_params['sh_i_id']=$sh_i_id;
						$inner_params['interstore_id']=$id;
						$inner_params['user_confirm_id']=$result['id'];
						$inner_params['confirm_pdate']=time();
						$inner_params['pdate']=time();
						$inner_params['is_confirmed']=0;
						
						$inner_params['org_id']=$result['org_id'];
						$inner_params['manager_id']=$result['id'];
						$inner_params['status_id']=4;
						$inner_params['storage_id']=$item['receiver_storage_id'];
						$inner_params['sector_id']=$item['receiver_sector_id'];
						$inner_params['komplekt_ved_id']=$komplekt_ved_ids[0];
						
						$acc_id=$_acc->Add($inner_params);
						
						$log->PutEntry($result['id'],'������ ����������� ������ �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,93,NULL,NULL,$bill_id);
					   
					   $log->PutEntry($result['id'],'������ ����������� ������ �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,219,NULL,NULL,$sh_i_id);
					   
					   $log->PutEntry($result['id'],'������ ����������� ������ �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,229,NULL,NULL,$acc_id);
					   
					   
						$log->PutEntry($result['id'],'������ ����������� ������ �� ��������� ����� �� ����������� �������� ������������ �� ���������',NULL,103,NULL,NULL,$id);
					  
					  	$_ni=new AccNotesItem;
						$_ni->Add(array(
							'user_id'=>$acc_id,
							'posted_user_id'=>$result['id'],
							'note'=>'�������������� ����������: ����������� ���� ������� �� ��������� ����������� �������� �� ��������� �'.$id.'.',
							'is_auto'=>1,
							'pdate'=>time()
						));	
					  
					  
					  
					  //�������� ������� �����������
					   $bill_positions=array();
						foreach($komplekt_ved_ids as $kh=>$kvid){
						//���� ������� �������� ��� - �� �������� �� ������� ���������
							if(count($binded_positions)>0) {	
							//����� ���-�
								 	
								  
								  //�������� ������� ������������
								 
								  
								  foreach($binded_positions as $k=>$v){
									if($v['komplekt_ved_id']!=$kvid) continue;
									if($v['quantity']==0) continue;
									
									$name=''; $dimension='';
									foreach($positions as $kk=>$vv){	
										if($vv['id']==$v['position_id']){
											$name=$vv['position_name']; $dimension=$vv['dim_name'];
											break;	
										}
									}
									
									$bill_positions[]=array(
											  'acceptance_id'=>$acc_id,
											  'komplekt_ved_pos_id'=>0,
											  'position_id'=>$v['position_id'],
											  'name'=>SecStr($name),
											  'dimension'=>SecStr($dimension),
											  'quantity'=>$v['quantity'],
											  'komplekt_ved_id'=>$kvid,
											  'price'=>0,
											  
											  'pms'=>NULL
										  );
									
									
									
								}
								
								
								
							}else{
							//������ ���-�	
								 
								  //�������� ������� �����������
								  
								  foreach($positions as $k=>$v){
										if($v['fact_quantity']==0) continue;
										//������� � �������� ���������� �� ����������� ����������
										$bill_positions[]=array(
											  'acceptance_id'=>$acc_id,
											  'komplekt_ved_pos_id'=>0,
											  'position_id'=>$v['id'],
											  'name'=>SecStr($v['position_name']),
											  'dimension'=>SecStr($v['dim_name']),
											  'quantity'=>$v['fact_quantity'],
											  'komplekt_ved_id'=>$komplekt_ved_ids[0],
											  'price'=>0,
											  
											  'pms'=>NULL
										  );
									  }
									  
									 
							}
						}
						
						
						$log_entries=$_acc->AddPositions($acc_id,$bill_positions);
						foreach($log_entries as $kk=>$vv){
							$description=$vv['name'].' <br /> ���-��: '.$vv['quantity'].'<br /> '.'���� '.$vv['price'].' ���. <br />';
							
							
							if($vv['action']==0){
								$log->PutEntry($result['id'],'������� ������� ����������� ������',NULL,93,NULL,$description,$bill_id);	
								
								$log->PutEntry($result['id'],'������� ������� ����������� ������',NULL,103,NULL,$description,$id);
								
								
								
								$log->PutEntry($result['id'],'������� ������� ����������� ������',NULL,219,NULL,$description,$sh_i_id);
								
								$log->PutEntry($result['id'],'������� ������� ����������� ������',NULL,230,NULL,$description,$acc_id);	
								
								
							}
						}
						
					}
					 
					 
					//����� �������� ����������
				 }
				 
			}
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params, $result);
		
	}
	
	//������������������ ������� 
	protected function ResyncPositions($id,$change_low_mode=0){
		$positions=$this->GetPositionsArr($id);
		$_kpi=new IswfPosItem;
		$_kpg=new IsWfPosGroup;
		$_pos=new IsPosItem;
		foreach($positions as $k=>$v){
			//echo $v['quantity'];
			
			$set=new mysqlset('select sum(quantity) from interstore_wf_position where position_id="'.$v['id'].'" and iwf_id in(select id from interstore_wf where interstore_id="'.$id.'")');
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			//for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				//print_r($f);
			//}
			
			
			//if($change_low_mode==1){
				$new_qua=(float)$f[0];
				$_pos->Edit($v['p_id'],array('quantity'=>$new_qua),NULL,false);
			//}
		}
			
		//die();
	}
	
	//������� � ������ ���-��� ������� ����� ������ ���. ��������...
	
	protected function RestorePositions($id){
		$positions=$this->GetPositionsArr($id);
		
		$_pos=new IsPosItem;
		foreach($positions as $k=>$v){
			$new_qua=(float)$v['quantity_initial'];
			$_pos->Edit($v['p_id'],array('quantity'=>$new_qua),NULL,false);
			
		}
			
	}
	
	
	//���������� ������, ��������
	public function CheckKomplekt($id,$org_id,&$komplekt_ved_id){
		$res=false;
		
		$komplekt_ved_id=NULL;
		$item=$this->getitembyid($id);
		$sector_id=$item['receiver_sector_id'];
		
		
		if($item['sender_sector_id']==$item['receiver_sector_id']){
			$komplekt_ved_id=0;
			return true;
				
		}
		
		
		
		
		$positions=$this->GetPositionsArr($id);
		
		$_kg=new KomplGroup; $_ki=new KomplItem; $_mf=new MaxFormer; $_iswf=new IswfGroup;
		
		$kg=$_kg->ShowActiveArr(0,$org_id);
		foreach($kg as $k=>$v){
			$kpositions=$_ki->GetPositionsArr($v['id']);
			
			
			$match_for_komplekt=false;
			if($v['sector_id']==$sector_id){
				//�������� �������
				//echo 'zayavka '.$v['id'];
				$positions_match=true;
				foreach($positions as $pk=>$pv){	
					
					$position_match=false;
					foreach($kpositions as $kk=>$kv){	
						if($pv['id']==$kv['position_id']){
							
							//��������� ��������� ����������
							$fact_quantity=$pv['fact_quantity']; //$_iswf->FactKol($id,$kv['position_id']);
							$kquantity=$_mf->MaxForBill($v['id'],$kv['position_id']);	
							
							
							if($kquantity>=$fact_quantity){
								//echo 'sravnivau pozicii '.$pv['id'].'('.$fact_quantity.') s '.$kv['position_id'].'('.$kquantity.')';
								
								$position_match=$position_match||true;
								break;	
							}
						}
					}
					$positions_match=$positions_match&&$position_match;
				}
				$match_for_komplekt=$match_for_komplekt||$positions_match;
				if($match_for_komplekt){
					$komplekt_ved_id=$v['id'];
					$res=true;
					break;	
				}
			}
		}
		return $res;
	}
	
	
	
	//������ � ����������� ����������� � ���������� �������, ������ ������ ���������
	public function DocCanConfirm($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
			$au=new AuthUser;
		$result=$au->Auth();
		$_pch=new PeriodChecker;
		
		$_dsi=new DocStatusItem;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='�������� ���������';
		}
		
		//��������� �������. ���� ���, ���� ������� ���-�� - ������ ���.
		$positions=$this->GetSimplePositions($id);
		
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
		
		
		//��������� ����������� ������
		$chk=$this->binder->CheckKomplekt($id,$result['org_id'],'quantity',$kvs);
		if(!$chk){
			$can=$can&&false;
			
			$reasons[]='�� ������� ������ �� �������-���������� � ������-���������� �� ������� ������������ �� ��������';	
		}
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� ������������ �� �������� '.$rss23;	
		}
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//������ � ����������� �������������� � ���������� �������, ������ ������ ������������
	public function DocCanUnConfirm($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
			$au=new AuthUser;
		$result=$au->Auth();
		$_pch=new PeriodChecker;
		
		$_dsi=new DocStatusItem;
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ���������';
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
		$positions=$this->GetSimplePositions($id);
		
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
	
	
	//��������� ����������� �������������� ���������...
	public function DocCanRestore($id,&$reason, &$items_exceeds, $_extended_limited_sector=NULL, $item=NULL, $new_values=NULL, $pos_template='is/positions_toggle_restore.html'){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_su=new SectorToUser;
		
			$au=new AuthUser;
		$result=$au->Auth();
		
		
		$storage_flt='';
		$sector_flt='';
		
		$is_storage_flt='';
		$is_sector_flt='';
		
		if($item['status_id']!=3){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� �������� ��������������';
		}else{
		
		  //����������� �������
		  if($_extended_limited_sector!==NULL){
			  $sector_flt.=$_su->buildQuery($_extended_limited_sector,'a.storage_id','a.sector_id');	
			  $is_sector_flt.=$_su->buildQuery($_extended_limited_sector, 'sender_storage_id','sender_sector_id');	
		  }
		  
		  
		  //�������� ��������� ����������... ���������� ������.
		  $vals=array();
		  if(is_array($new_values)){
			  foreach($new_values as $k=>$v){
			  	$valarr=explode(';',$v);
				$vals[$valarr[0]]=$valarr[1];  
			  }
		  }
		  
		  
		  
		  //��������� �������... ����� ���������� ���-�� ������ ������� �� ��, ��
		  //���� �� ������� - �������� �� ����...
		  $positions=$this->GetPositionsArr($id);
		  
		 // print_r($positions);
		  
		  foreach($positions as $k=>$v){
			  //v['id'] v['quantity'] item['sender_sector_id'] item['sender_storage_id']
			  //$id - ������� ��������, �� ������� ���	
			  
			   //���-�� ��� ���������: ��� �������� ��� ������������� ��������
			  $quantity_to_compare=round($v['quantity'],3);
			  if(isset($vals[$v['id']])) $quantity_to_compare=round((float)$vals[$v['id']],3);
			  
			  
			  
			  $sql2='select sum(ap.quantity) as quantity, 
			  ap.position_id, ap.name as position_name, ap.dimension as dim_name, dim.id as dimension_id 
				  from acceptance_position as ap
				  inner join acceptance as a on a.id=ap.acceptance_id
				   inner join bill as b on b.id=a.bill_id
				   inner join catalog_position as cat on cat.id=ap.position_id
				   left join catalog_dimension as dim on ap.dimension=dim.name 
				   where 
					a.storage_id="'.$item['sender_storage_id'].'" and a.is_confirmed=1 and a.org_id="'.$result['org_id'].'"
				   and a.sector_id="'.$item['sender_sector_id'].'" '.$sector_flt.' '.$db_flt.'
				   and ap.position_id="'.$v['id'].'" 
				   order by position_name asc, ap.position_id asc
				   ';
				   
				   
			  //echo $sql2.'<br />';	   
			  $set=new mysqlSet($sql2);		
			  $rc=$set->GetResultNumRows();
			  $rs=$set->GetResult();
			  if($rc==0){
				  //$reasons[]=' ������� '.$v['position_name'].': �������� 0 '.$v['dim_name'];
				  
				  $items_exceeds[]=array('id'=>$v['id'], 'name'=>$v['position_name'], 'dim_name'=>$v['dim_name'], 'max'=>0, 'needed'=>$quantity_to_compare);
				  
				  $can=$can&&false;
				  continue;	
			  }
			  
			  $f=mysqli_fetch_array($rs);
			  
			 
			  
			  $max_quantity=(float)$f['quantity'];
			  
			  //������� ����� ������� �� ������ �������
			  
			  $sql1='select sum(quantity) from interstore_position where position_id="'.$v['id'].'" and interstore_id in(select id from interstore where is_confirmed=1 and sender_storage_id="'.$item['sender_storage_id'].'" and sender_sector_id="'.$item['sender_sector_id'].'" '.$is_sector_flt.' and org_id="'.$result['org_id'].'" and id<>"'.$id.'")';
			  //echo $sql1.'<br />';	
			  
			  $set1=new mysqlSet($sql1);
			  
			  
			  $rs1=$set1->GetResult();
			  
			  $g=mysqli_fetch_array($rs1);
			  
			  //������ �� ������
			  $max_quantity-=(float)$g[0];
			  
			 // echo ' ������� '.$v['position_name'].': � ���������'.$v['quantity'].', � ������� '.$max_quantity.' <br />';
			  if($max_quantity<0) $max_quantity=0;
			  
			 
			  
			  
			 
			  if(round($max_quantity,3)<$quantity_to_compare){
				  //$reasons[]=' ������� '.$v['position_name'].': �������� '.round($max_quantity,3).' '.$v['dim_name'].', ���������� '.round($v['quantity'],3);
				  $items_exceeds[]=array('id'=>$v['id'], 'name'=>$v['position_name'], 'dim_name'=>$v['dim_name'], 'max'=>round($max_quantity,3), 'needed'=>$quantity_to_compare);
				  $can=$can&&false;
				  
				  
			  }
			  
		  }
		}
		
		$reason=implode('<br /> ',$reasons);
		
		$sm=new SmartyAj;
		$sm->assign('items',$items_exceeds);
		
		
		$reason.=$sm->fetch($pos_template);
		
		return $can;	
	}
	
	//������� �������������� ���-��
	public function DocRestore($id, $confirm_pdate, $user_confirm_id, $changed_positions=NULL){
		$log=new ActionLog();
		$_isp=new IsPosItem;
		$_iswp=new IsWfPosItem;
		$_bi=new BillItem;
		$_bp=new BillPosItem;
		$_shp=new ShIPosItem;
		$_acp=new AccPosItem;
		
		
		if(count($changed_positions)>0){
			foreach($changed_positions as $k=>$v){
			  $new_value=explode(';',$v);	
			  
			 // print_r($new_value);
			  
			  //��������� ������� ���������, ������ ����� ���-��
			  $isp=$_isp->GetItemByFields(array('interstore_id'=>$id, 'position_id'=>$new_value[0]));
			  if($isp!==false){
				if($new_value[1]==0){
					$_isp->Del($isp['id']);
					$log->PutEntry($user_confirm_id,'������ ������� ������������ �� �������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,101,NULL,SecStr($isp['name']),$id);
				}else{
					$_isp->Edit($isp['id'],array('quantity'=>$new_value[1]));
					$log->PutEntry($user_confirm_id,'������������ ������� ������������ �� �������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,101,NULL,SecStr($isp['name']).' <br /> ���-��: '.$new_value[1],$id);
				}
			  }
		  
		  
			  //��������� ���� �� ��������, ��������� ��������
		  	  $sql='select sum(quantity) from interstore_wf_position where position_id="'.$new_value[0].'" and iwf_id in(select id from interstore_wf where interstore_id="'.$id.'") ';
			  $set=new mysqlset($sql);
			  $rs=$set->getResult();
			  $f=mysqli_fetch_array($rs);
			  //������� ����� �������
			  $spisano=(float)$f[0];
			  
			  if($spisano>$new_value[1]){
			  
				$sql='select * from interstore_wf_position where position_id="'.$new_value[0].'" and iwf_id in(select id from interstore_wf where interstore_id="'.$id.'")';
				$delta=$spisano-$new_value[1];
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					 
					if($delta<=0) break;  
					  
					if($f['quantity']>$delta){
						$_iswp->Edit($f['id'], array('quantity'=>($f['quantity']-$delta)));
						$delta=0;
						
						$log->PutEntry($user_confirm_id,'������������ ������� ������������ �� �������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,101,NULL,SecStr($f['name']).' <br /> ���-��: '.($f['quantity']-$delta),$id);
			
						
					}elseif($f['quantity']<=$delta){
						$_iswp->Del($f['id']); 
						$delta-=$f['quantity'];
						
						$log->PutEntry($user_confirm_id,'������ ������� ������������ �� �������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,101,NULL,SecStr($f['name']),$id);
						
					}
				}
			  }
			  	
			  //��������� ������� �����
		  	  $sql='select * from bill_position where position_id="'.$new_value[0].'" and bill_id in(select id from bill where interstore_id="'.$id.'")';
			  $set=new mysqlset($sql);
			  $rs=$set->getResult();
			  $rc=$set->getResultNumRows();
			  if($rc>0){
				 $f=mysqli_fetch_array($rs);
				 if($new_value[1]==0){
					$_bp->Del($f['id']);
					
					$log->PutEntry($user_confirm_id,'������ ������� ��������� ����� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,93,NULL,SecStr($f['name']),$f['bill_id']);
					$log->PutEntry($user_confirm_id,'������ ������� ��������� ����� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,103,NULL,SecStr($f['name']),$id);
					
				}else{
					$_bp->Edit($f['id'],array('quantity'=>$new_value[1]));
					
					$log->PutEntry($user_confirm_id,'������������ ������� ��������� ����� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,93,NULL,SecStr($f['name']).' <br /> ���-��: '.$new_value[1],$f['bill_id']);
					$log->PutEntry($user_confirm_id,'������������ ������� ��������� ����� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,103,NULL,SecStr($f['name']).' <br /> ���-��: '.$new_value[1],$id);
				}
			  }
			  
			  //��������� ������� ���� �� ��
		  	  $sql='select * from sh_i_position where position_id="'.$new_value[0].'" and sh_i_id in(select id from sh_i where bill_id in(select id from bill where interstore_id="'.$id.'"))';
			  $set=new mysqlset($sql);
			  $rs=$set->getResult();
			  $rc=$set->getResultNumRows();
			  if($rc>0){
				 $f=mysqli_fetch_array($rs);
				 if($new_value[1]==0){
					$_shp->Del($f['id']);
					
					$log->PutEntry($user_confirm_id,'������ ������� ������������ �� ������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,216,NULL,SecStr($f['name']),$f['sh_i_id']);	
					
					
					$log->PutEntry($user_confirm_id,'������ ������� ������������ �� ������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,103,NULL,SecStr($f['name']),$id);
					
				}else{
					$_shp->Edit($f['id'],array('quantity'=>$new_value[1]));
					
					
								  
					$log->PutEntry($user_confirm_id,'������������ ������� ������������ �� ������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,216,NULL,SecStr($f['name']).' <br /> ���-��: '.$new_value[1],$f['sh_i_id']);	
					
					
					$log->PutEntry($user_confirm_id,'������������ ������� ������������ �� ������� ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,103,NULL,SecStr($f['name']).' <br /> ���-��: '.$new_value[1],$id);
				}
			  }
			  
			  
			  //��������� ������� �����������
		  	  $sql='select * from acceptance_position where position_id="'.$new_value[0].'" and acceptance_id in(select id from acceptance where sh_i_id in(select id from sh_i where bill_id in(select id from bill where interstore_id="'.$id.'")))';
			  $set=new mysqlset($sql);
			  $rs=$set->getResult();
			  $rc=$set->getResultNumRows();
			  if($rc>0){
				 $f=mysqli_fetch_array($rs);
				 if($new_value[1]==0){
					$_acp->Del($f['id']);
					
					
					$log->PutEntry($user_confirm_id,'������ ������� ����������� ������  ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,103,NULL,SecStr($f['name']),$id);
					
					$log->PutEntry($user_confirm_id,'������ ������� ����������� ������  ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,230,NULL,SecStr($f['name']),$f['acceptance_id']);	
				}else{
					$_acp->Edit($f['id'],array('quantity'=>$new_value[1]));
					
					
					$log->PutEntry($user_confirm_id,'������������ ������� ����������� ������  ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,103,NULL,SecStr($f['name']).' <br /> ���-��: '.$new_value[1],$id);
					
					$log->PutEntry($user_confirm_id,'������������ ������� ����������� ������  ��� ��������� ������� ��� �������������� ��������������� ������������ �� ��������',NULL,230,NULL,SecStr($f['name']).' <br /> ���-��: '.$new_value[1],$f['acceptance_id']);	
				}
			  }
			}
		}
		
		
		$this->Edit($id,array('status_id'=>1, 'confirm_pdate'=>$confirm_pdate, 'user_confirm_id'=>$user_confirm_id));		
		
		
		
		
	}
	
	//�������� ���������...
	public function CheckLack($id, &$rss, &$lack, $field_name='quantity'){
		$res=true;
		$rss='';
		$lack=array();
		
		$positions=$this->GetPositionsArr($id);
		foreach($positions as $k=>$v){
			$delta=round(round($v[$field_name],3)-round($v['fact_quantity'],3),3);
			if($delta>0){
				$res=$res&&false;
				
				$rss.='�� ������� '.sprintf("%05d",$v['id']).' '.$v['position_name'].' ��������� ���������� '.$delta.' '.$v['dim_name']."\n";
				$lack[]=array(
					'id'=>$v['id'],
					'position_name'=>$v['position_name'],
					'dim_name'=>$v['dim_name'],
					'lack'=>$delta,
					'dimension_id'=>$v['dimension_id']
				);
			}
		}
		
		
		return $res;
	}
	
	
}
?>