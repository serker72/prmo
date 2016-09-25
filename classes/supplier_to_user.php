<?
require_once('abstractitem.php');
require_once('useritem.php');
 
require_once('user_s_item.php');
require_once('discr_man.php');
 
//����������� ������� ������������ � ������������
class SupplierToUser extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_to_user_r'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//�������� ������������� �� ���.
	public function AddSuppliersToUserArray($user_id, array $questions, $org_id){
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		//$old_positions=$this->GetQuestionsArr($user_id);
		$sql='select * from '.$this->tablename.' where org_id="'.$org_id.'" and user_id="'.$user_id.'" ';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$old_positions[]=$f;
		}
		
		
		
		foreach($questions as $k=>$v){
			$kpi=$this->GetItemByFields(array('user_id'=>$user_id,'supplier_id'=>$v['supplier_id'], 'org_id'=>$org_id));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['supplier_id']=$v['supplier_id'];
				$add_array['user_id']=$user_id;
				$add_array['org_id']=$org_id;
				
				
				
				
				$this->Add($add_array);//, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$user_id,
					'supplier_id'=>$v['supplier_id']
				);
				
			}/*
			������ �������������� �� �����!
			*/
		}
		
		//����� � ������� ��������� �������:
		//����. ���. - ��� �������, ������� ��� � ������� $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($questions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['org_id']==$v['org_id'])){
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
					'supplier_id'=>$v['supplier_id'],
					'user_id'=>$user_id
			);
			
			//������� �������
			$this->Del($v['id']);
		}
		
		
		//���������� ������� ������ ���������� ������� ��� �������
		return $log_entries;
		
		
		
		
		
	}
	
	
	
	
	
	
	
	
	
	
	//������ ��������� �� ���� �����
	public function GetBookCategsArr($id, $is_active=0){
		$arr=array();
		if($is_active==1){
			$sql='
		select distinct c.id as id, c.name_s as name, "1" as is_in, c.is_active as is_active from user as c inner join '.$this->tablename.' as bc on c.id=bc.user_id where bc.sector_id="'.$id.'" and c.is_active=1 order by name asc';
		}else $sql='
		select distinct c.id as id, c.name_s as name, "1" as is_in, c.is_active as is_active from user as c inner join '.$this->tablename.' as bc on c.id=bc.user_id where bc.sector_id="'.$id.'" order by name asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//������ ���-��� �� ���� sector.
	public function GetAllSectorUsersArr($id){
		$arr=array();
		$sql='
		
		(select distinct c.id as id, c.name_s as name, c.login, c.position_s, c.is_active as is_active, "0" as is_in from user as c where (c.id in(select distinct user_id from '.$this->tablename.' where sector_id<>"'.$id.'") or c.id not in (select distinct user_id from '.$this->tablename.')) and c.id not in(select distinct user_id from '.$this->tablename.' where sector_id="'.$id.'"))
		
		UNION
		(select distinct c.id as id, c.name_s as name, c.login, c.position_s, c.is_active as is_active, "1" as is_in from user as c inner join '.$this->tablename.' as bc on c.id=bc.user_id where bc.sector_id="'.$id.'") order by name asc';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//������ sector �� ���� ���-��
	public function GetAllUserSuppliersArr($id, $result){
		$arr=array();
		/*$sql='
		
		(select distinct c.id as id, c.full_name as name,  c.is_active as is_active, "0" as is_in, opf.name as opf_name from supplier as c left join opf on opf.id=c.opf_id where c.is_active=1 and c.is_org=0 and c.org_id="'.$result['org_id'].'" and (c.id in(select distinct supplier_id from '.$this->tablename.' where user_id<>"'.$id.'") or c.id not in (select distinct supplier_id from '.$this->tablename.')) and c.id not in(select distinct supplier_id from '.$this->tablename.' where user_id="'.$id.'"))
		
		UNION
		(select distinct c.id as id, c.full_name as name,   c.is_active as is_active, "1" as is_in, opf.name as opf_name from supplier as c left join opf on opf.id=c.opf_id inner join '.$this->tablename.' as bc on c.id=bc.supplier_id where c.is_active=1 and c.is_org=0  and c.org_id="'.$result['org_id'].'" and bc.user_id="'.$id.'") order by name asc';*/
		
		$sql='
		
		select distinct c.id as id, c.full_name as name,   c.is_active as is_active, "1" as is_in, opf.name as opf_name from supplier as c left join opf on opf.id=c.opf_id inner join '.$this->tablename.' as bc on c.id=bc.supplier_id where c.is_active=1 and c.is_org=0  and c.org_id="'.$result['org_id'].'" and bc.user_id="'.$id.'" order by name asc';
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//������ ���� �������� ������������
	public function GetSupplierIdsArr($user_id, $result){
		$arr=array();
		$set=new mysqlSet('select distinct u.id from '.$this->tablename.' as uc inner join supplier as u on uc.supplier_id=u.id where u.is_active=1 and u.is_org=0 and u.org_id="'.$result['org_id'].'"  and uc.user_id="'.$user_id.'" order by u.id asc');
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			
			
			$arr[]=$f['id'];
		}
		
		 
		
		return $arr;	
	}
	
	//������ �������� ������������
	public function GetSuppliersArr($user_id, $result){
		$arr=array();
		$set=new mysqlSet('select u.* from '.$this->tablename.' as uc inner join user as u on uc.viewed_user_id=u.id where u.is_active=1 and u.is_org=1 and u.org_id="'.$result['org_id'].'" and uc.user_id="'.$user_id.'" order by u.id asc');
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$arr[]=$f['id'];
		}
		
		return $arr;	
	}
	
	
//******************************************************************************************
	//����������� �������	
	
	//������ ���� ������������ ������������
	public function GetExtendedViewedUserIdsArr($user_id, $_result){
		
		 
		
		$arr=array(); $pairs=array();
		$_ui=new UserSItem; $_dm=new DiscrMan;
		
		//���� �� ���������� ��� ���
		 
		
		$set=new mysqlSet('select * from user as u
		where u.is_active=1  and u.id="'.$user_id.'" ');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
	
	
		
		if(( $f['has_restricted_suppliers']==0)){
			
			//����� �������� ���� ������� �� ���� ������������. ���� ��� ���� 909:
			//������� ��������� ����� 919, 920, 921 - ���������, ����������, �������
			//���� ���� ������� �� ���� ������ - �� ������ � ����� ������ ��������������� ������������
			//�������� ������������, � ������� f[id] - ������������� ���������, ���� ������� �� ������, ���� ������������� ����������, ��� ������� - ����������� ����� ����������
						
			
			
			//echo 'zzz';
			//echo 'ccccccc ';
			
			
			if(!$_dm->CheckAccess($f['id'],'w',909)){
				
				
				//���������
				if($_dm->CheckAccess($f['id'],'w',919)){
					$sql='select distinct id from supplier where is_supplier=1';
				
					//echo $sql;
					
					$set=new mysqlSet($sql);
					$tc=$set->GetResultNumRows();
					
					$rs=$set->GetResult();
					for($i=0;$i<$tc;$i++){
						$g=mysqli_fetch_array($rs);
						  
						if(!in_array($g['id'], $arr)) $arr[]=$g['id'];
					}
				}
				
				//����������
				if($_dm->CheckAccess($f['id'],'w',920)){
					$sql='select distinct id from supplier where is_customer=1';
				
					//echo $sql;
					
					$set=new mysqlSet($sql);
					$tc=$set->GetResultNumRows();
					
					$rs=$set->GetResult();
					for($i=0;$i<$tc;$i++){
						$g=mysqli_fetch_array($rs);
						  
						if(!in_array($g['id'], $arr)) $arr[]=$g['id'];
					}
				}
				
				//�������
				if($_dm->CheckAccess($f['id'],'w',921)){
					$sql='select distinct id from supplier where is_partner=1';
				
					//echo $sql;
					
					$set=new mysqlSet($sql);
					$tc=$set->GetResultNumRows();
					
					$rs=$set->GetResult();
					for($i=0;$i<$tc;$i++){
						$g=mysqli_fetch_array($rs);
						  
						if(!in_array($g['id'], $arr)) $arr[]=$g['id'];
					}
				}
				
				
				
				//�������� ������ ���� ����������� �����������
				
				
				$_user_ids=array();
				$_user_ids[]=$f['id'];
				
				$pod=$_ui->GetSubsArr($f['id']);
				foreach($pod as $k=>$v) if(!in_array($v['id'], $_user_ids)) $_user_ids[]=$v['id'];
				
				//������� ������ ������������ �� ���� �����������
				$sql='select distinct id from supplier where created_id in('.implode(', ', $_user_ids).') or id in( select distinct supplier_id from supplier_responsible_user where user_id in('.implode(', ', $_user_ids).'))';
				
				//echo $sql;
				
				$set=new mysqlSet($sql);
				$tc=$set->GetResultNumRows();
				
				$rs=$set->GetResult();
				for($i=0;$i<$tc;$i++){
					$g=mysqli_fetch_array($rs);
					  
					if(!in_array($g['id'], $arr)) $arr[]=$g['id'];
				}
				
			}else{
				//�� ������� ������������� ������� � �����, ��� ���� 909 - �������� ���� ������������	
				$sql='select u.* from supplier as u
				where u.is_active=1 and u.is_org=0 and u.org_id="'.$_result['org_id'].'" order by u.id asc';
				$set=new mysqlSet($sql);
				$tc=$set->GetResultNumRows();
				
				$rs=$set->GetResult();
				for($i=0;$i<$tc;$i++){
					$g=mysqli_fetch_array($rs);
					  
					$arr[]=$g['id'];
				}
			}
			
		}else{
			
			
			//�� �.�. - ������ ������� �������� ���������� � �����
			
			$sql='select distinct u.id  from '.$this->tablename.' as uc 
			inner join supplier as u on uc.supplier_id=u.id 
			where u.is_active=1 and u.is_org=0 and u.org_id="'.$_result['org_id'].'" and uc.user_id="'.$user_id.'" order by u.id asc';
			
			//echo $sql.'<br>';
			$set=new mysqlSet($sql);
			$tc=$set->GetResultNumRows();
			
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$g=mysqli_fetch_array($rs);
				 
				$arr[]=$g['id'];
			}
			
			
		}
		
		
		if(count($arr)==0) $arr[]='-1';
		
		
		 
		
		$result=array(
			'sector_ids'=>$arr			
		);
		
		
		return $result;	
	}
	
	 
	
}
?>