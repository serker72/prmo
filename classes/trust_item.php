<?
require_once('bill_in_item.php');
require_once('trust_positem.php');
require_once('billpospmformer.php');
require_once('trust_posgroup.php');
require_once('docstatusitem.php');

require_once('actionlog.php');
require_once('authuser.php');
require_once('period_checker.php');

//����������� �������
class TrustItem extends BillInItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='trust';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';	
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_auth_result=NULL){
		$item=$this->GetItemById($id);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$_auth_result);
		
	}
	
	//�������
	public function Del($id){
		
		$query = 'delete from trust_position_pm where trust_position_id in(select id from trust_position where trust_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from trust_position where trust_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//������� �������
	public function AddPositions($current_id, array $positions){
		$_kpi=new trustPosItem;
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
			'trust_id'=>$v['trust_id'],
			'position_id'=>$v['position_id'],
			
			'bill_id'=>$v['bill_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				
				$add_array=array();
				$add_array['trust_id']=$v['trust_id'];
				
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['bill_id']=$v['bill_id'];
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$v['pms']
				);
				//echo 'aaa';
				//print_r($add_array);
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['trust_id']=$v['trust_id'];
				
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['bill_id']=$v['bill_id'];
				
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
				if(($vv['position_id']==$v['position_id'])
			
				&&($vv['bill_id']==$v['bill_id'])){
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
		$kpg=new TrustPosGroup;
		$arr=$kpg->GetItemsByIdArr($id);
		
		return $arr;		
		
	}
	
	
	
	
	//������ ��������� �� ������
	public function CalcCost($id){
		$positions=$this->GetPositionsArr($id);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions);
		return $total_cost;
	}
	
	
	//�����, ��������� ��� ������� � ������������
	public function GetRelatedBillsArr($current_id, $current_bill_id, $supplier_id, $contract_id){
		$alls=array();
			
		$sql='select * from bill where is_incoming=1 and supplier_id="'.$supplier_id.'" and contract_id="'.$contract_id.'" and id<>"'.$current_bill_id.'" and is_confirmed_shipping=1';
		//echo $sql;
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//$_mf=new MaxFormer; $_bi=new BillItem;
		for($i=0; $i<$rc; $i++){
			//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
			
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			$sql1='select sum(quantity) from bill_position where bill_id="'.$f['id'].'"';
			$set1=new MysqlSet($sql1);
			
			//echo $f['code'];
			
		//	echo $sql1;
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			//var_dump($g[0]);
			
			
			$sql1='select sum(quantity) from trust_position as tp 
			inner join trust as t on t.id=tp.trust_id
			where 
			tp.bill_id="'.$f['id'].'" 
			and tp.bill_id<>"'.$current_bill_id.'" 
			and tp.trust_id<>"'.$current_id.'"
			and t.is_confirmed=1
			';
			$set1=new MysqlSet($sql1);
		//echo $sql1;
			$rs1=$set1->GetResult();
			$h=mysqli_fetch_array($rs1);
			
			
			// var_dump((float)$h[0]);
			
			if(($current_id==0)&&((float)$g[0]>(float)$h[0])){
				
				//echo 'uu';
				
				$sql1='select count(*) from trust_position where bill_id="'.$f['id'].'" and trust_id="'.$current_id.'"';
				$set1=new MysqlSet($sql1);
		//	echo $sql1;
				$rs1=$set1->GetResult();
				$j=mysqli_fetch_array($rs1);
				if((int)$j[0]>0) $f['is_checked']=true;
				else $f['is_checked']=false;
				$f['can_be_checked']=true;
				$alls[]=$f;	
			}elseif(($current_id!=0)){
				//echo 'zzzzzzzzzzzzzzzzzzz';
				$sql1='select count(*) from trust_position where bill_id="'.$f['id'].'" and trust_id="'.$current_id.'"';
				$set1=new MysqlSet($sql1);
		//	echo $sql1;
				$rs1=$set1->GetResult();
				$j=mysqli_fetch_array($rs1);
				if((int)$j[0]>0) $f['is_checked']=true;
				else $f['is_checked']=false;
				
				
				if(((float)$g[0]>(float)$h[0])) $f['can_be_checked']=true;
				else $f['can_be_checked']=false;
				
				$alls[]=$f;	
			}
			
		}
		return $alls;
	}
	
	
	
	
	//�������� � ��������� ������� (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL){
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			$log=new ActionLog();
			$au=new AuthUser;
			if($_result===NULL) $_result=$au->Auth();
			$_stat=new DocStatusItem;
			$item=$this->GetItemById($id);
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==1)){
				//����� ������� � 1 �� 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'����� ������� ������������',NULL,93,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ������������',NULL,208,NULL,'���������� ������ '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'����� ������� ������������',NULL,93,NULL,'���������� ������ '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'����� ������� ������������',NULL,208,NULL,'���������� ������ '.$stat['name'],$item['id']);
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
		$reason=implode(', ',$reasons);
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
		
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� '.$rss23;	
		}
		
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	
	//������ � ����������� ������ ����������� � ���������� �������, ������ ������ �� ���������
	public function DocCanUnConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='�������� �� ���������';
		}
		
		
		//�������� ��������� ������� 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss1,$periods)){
			$can=$can&&false;
			$reasons[]='���� �������� '.$rss1;	
		}
		
		
		
		$reason=implode(', ',$reasons);
		
		return $can;	
	
	}
}
?>