<?
require_once('abstractitem.php');
require_once('useritem.php');
 
//pol'zovatel i u4astki
class UserToUser extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_to_user'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//�������� ������������� �� ���.
	public function AddViewedUsersToUserArray($user_id, array $questions){
		
		$log_entries=array();
		
		//���������� ������ ������ �������
		$old_positions=array();
		//$old_positions=$this->GetQuestionsArr($user_id);
		$sql='select * from '.$this->tablename.' where user_id="'.$user_id.'" ';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$old_positions[]=$f;
		}
		
		
		
		foreach($questions as $k=>$v){
			$kpi=$this->GetItemByFields(array('user_id'=>$user_id,'viewed_user_id'=>$v));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['viewed_user_id']=$v;
				$add_array['user_id']=$user_id;
				
				
				
				
				$this->Add($add_array);//, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$user_id,
					'viewed_user_id'=>$v
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
				if($vv==$v['viewed_user_id']){
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
					'viewed_user_id'=>$v['viewed_user_id'],
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
	public function GetAllUserViewedUsersArr($id){
		$arr=array();
		$sql='
		
		(select distinct c.id as id, c.name_s as name, c.login as login, c.is_active as is_active, "0" as is_in from user as c where (c.id in(select distinct viewed_user_id from '.$this->tablename.' where user_id<>"'.$id.'") or c.id not in (select distinct viewed_user_id from '.$this->tablename.')) and c.id not in(select distinct viewed_user_id from '.$this->tablename.' where user_id="'.$id.'") and c.id<>"'.$id.'")
		
		UNION
		(select distinct c.id as id, c.name_s as name,  c.login as login, c.is_active as is_active, "1" as is_in from user as c inner join '.$this->tablename.' as bc on c.id=bc.viewed_user_id where bc.user_id="'.$id.'"  and c.id<>"'.$id.'") order by name asc';
		
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
	public function GetViewedUsersIdsArr($user_id){
		$arr=array();
		$set=new mysqlSet('select distinct u.id from '.$this->tablename.' as uc inner join user as u on uc.viewed_user_id=u.id where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc');
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			
			
			$arr[]=$f['id'];
		}
		
		 
		
		return $arr;	
	}
	
	//������ �������� ������������
	public function GetViewedUsersArr($user_id){
		$arr=array();
		$set=new mysqlSet('select u.* from '.$this->tablename.' as uc inner join user as u on uc.viewed_user_id=u.id where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc');
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
	
	//������ ���� �������� ������������
	public function GetExtendedViewedUserIdsArr($user_id){
		
		 
		
		$arr=array(); $pairs=array();
		
		//���� �� ���������� ��� ���
		$sql='select * from user as u
		where u.is_active=1  and u.id="'.$user_id.'" ';
		
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$arr[]=$user_id;
	
		
		if(( $f['has_restricted_users']==0)){
			//�.�.	��� ������ ��� �������� - ������ ��� �������
			//echo 'zzz';
			$set=new mysqlSet('select u.* from user as u
			where u.is_active=1 order by u.id asc');
			$tc=$set->GetResultNumRows();
			
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				  
				$arr[]=$f['id'];
			}
			
		}else{
			//�� �.�. - ������ ������� �������� ���������� � �����
			//echo 'uu';
			$sql='select distinct u.id from '.$this->tablename.' as uc 
			inner join user as u on uc.viewed_user_id=u.id 
			where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc';
			//echo $sql;
			
			$set=new mysqlSet($sql);
			$tc=$set->GetResultNumRows();
			
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				 
				$arr[]=$f['id'];
			}
			if(count($arr)==0) $arr[]='-1';
			
		}
		
		
		
		
		
		 
		
		$result=array(
			'sector_ids'=>$arr			
		);
		
		
		return $result;	
	}
	
	 
	
}
?>