<?
require_once('abstractgroup.php');
require_once('abstractbillgroup.php');
require_once('billitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('billnotesgroup.php');
require_once('billnotesitem.php');
require_once('payforbillgroup.php');

require_once('period_checker.php');

// ������ �� ������
class BillInGroup extends AbstractBillGroup {
	protected $_auth_result;
	
	//��������� ���� ����
	protected function init(){
		
		$this->tablename='bill';
		$this->pagename='bills.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->prefix='_1';
		$this->is_incoming=1;
		
		
		$this->_item=new BillInItem;
		
		$this->_notes_group=new BillNotesGroup;
		
		$this->_payforbillgroup=new PayForBillGroup;
				
		
		$this->_auth_result=NULL;
		
		$this->_view=new Bill_In_ViewGroup;
		
	}

	
	
	//�������������� �������������
	public function AutoAnnul($days=45, $days_after_restore=45, $annul_status_id=3){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
		 $_itm=new BillItem;
		
		$_ni=new BillNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_incoming="'.$this->is_incoming.'" and status_id<>'.$annul_status_id.' and cannot_an=0 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			//��������� ������� ����. ���������� ���
			
			/*$sql1='select count(id) from sh_i_id where bill_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;*/
			
			$sql1='select count(id) from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			
			
			//������ 1 - ��� ������ �������:
			if($f['is_confirmed_price']==0){
				
				
					
				//�������� ���� ��������������
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='������ ����� '.$days_after_restore.' ���� � ���� �������������� ��������� �����, ��� ������������ ��������� ����������, �������� �� ���������';
					}
				}else{
					//�������� � ����� ��������	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� �������� ��������� �����, ��� ������������ ��������� ����������, �������� �� ���������';
					}
				}
			}elseif(($f['is_confirmed_price']==1)&&($f['is_confirmed_shipping']==0)){
				//�������� � ����� ����������� 1 �������	
					if(($f['confirm_price_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� ����������� ��� ��������� �����, ��� ������������ ��������� ����������, �� ���������� ��������';
					}
				
			}elseif(($f['is_confirmed_price']==1)&&($f['is_confirmed_shipping']==1)){
				//�������� � ����� ����������� 2 �������	
					if(($f['confirm_shipping_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='������ ����� '.$days.' ���� � ���� ����������� ������� ��������� �����, ��� ������������ ��������� ����������';
					}
				
			}
			
			
			
			
			
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed_price'=>0, 'is_confirmed_shipping'=>0, 'status_id'=>$annul_status_id));
				
				$_itm->FreeBindedPayments($f['id']);
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'�������������� ������������� ��������� �����',NULL,626,NULL,'� ���������: '.$f['code'].' ���������� ������ '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'�������������� ����������: �������� ���� ��� ������������� �����������, �������: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	//�������������� ������������
	public function AutoEq($days=30, $days_no_acc=45){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new BillNotesItem;
		$_itm=new BillInItem;
		
		//��������� ��� ����� � ������� "���" ��� "�� ���"
		
		 
		
		$now=time();
		
		$sql='select * from bill where is_incoming="'.$this->is_incoming.'" and  status_id in(2,9,20,21) and is_confirmed_price=1 and is_confirmed_shipping=1 and cannot_eq=0 order by id desc';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$can_annul=false;
		
			//�������� �� ������
			$checked_time=$now-$days*24*60*60;
			$checked_time_noacc=$now-$days_no_acc*24*60*60;
			
			$sql1='select * from acceptance where bill_id="'.$f['id'].'" and is_confirmed=1  order by given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$can_annul=true;	
				$g=mysqli_fetch_array($rs1);
				//print_r($g);
			}
			
			
			
			//�������� �� ������, ������ ���� ���� ����� (������� ����� ����)
			if($can_annul){
				//����� ������� �����
				
				$posset=new mysqlset('select * from bill_position where bill_id='.$f['id'].'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				
				
				$was_eqed=false;
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					$args=array();
					$can_annul_position=false;
					//�� ������ �� ������� - ������� ��������� ����������
					//���� ���� ���������� ����������� (� ��� ����) ������ 30 ���� - ������. �������
					//����� - �������� ���� ���������� �����������...
					
					$sql3='select * 
					from acceptance as a inner join acceptance_position as ap on a.id=ap.acceptance_id 
					where 
						a.is_confirmed=1 
						and a.is_incoming='.$this->is_incoming.'
						and a.bill_id='.$f['id'].' 
						 
						and ap.position_id='.$h['position_id'].' 
					 	and ap.komplekt_ved_id='.$h['komplekt_ved_id'].' 
						and ap.out_bill_id='.$h['out_bill_id'].' 
						
					order by a.given_pdate desc limit 1';
					
					$acset=new mysqlset($sql3);
					$rs3=$acset->GetResult();
					$rc3=$acset->GetResultNumRows();
					if($rc3>0){
						//����� ����, ��������� ���� ������
						$hh=mysqli_fetch_array($rs3);
						if($hh['given_pdate']<$checked_time){
							$can_annul_position=true;
							//echo '������� '.$h['name'].' �������� ������������, ���� ���������� ����������� '.date('d.m.Y',$hh['given_pdate']).' '.$hh['id'].' �� ������� ����� 30 ���� �����<br>';
						}
					}else{
						//��� ������ - �������� �� ���� ���������� ������
						
						if($g['given_pdate']<$checked_time_noacc){
							$can_annul_position=true;
							//echo '������� '.$h['name'].' �������� ���������, ���� ���������� ����������� '.date('d.m.Y',$g['given_pdate']).' '.$g['id'].' �� ����� ����� 45 ���� �����<br>';
						}
						
					}
					
					//���� �����������...
					if($can_annul_position){
						
						$args[]=$h['position_id'].';'.$h['quantity'].';0;'.$h['sector_id'].';'.$h['komplekt_ved_id'].';'.$h['out_bill_id'];	
					//	echo '���������� ������� '.$h['name'].' <br>';
						
						$_itm->DoEq($f['id'],$args,$some_output, 1, $f, $_result,true);
						$was_eqed=$was_eqed||true;
					}else{
					//	echo '�� ���������� ������� '.$h['name'].' <br>';
					}
				}// of bill positions
				if($was_eqed){
					//echo '���� '.$f['code'].' '.$f['id'].' �������� ������������ <br><br>';
					$_itm->ScanDocStatus($f['id'],array(),array(),$f,$_result);		
				}
				
			}//of bill
		}
		 
		
	}
}
?>