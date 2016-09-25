<?
require_once('abstractitem.php');
require_once('useritem.php');
require_once('storageitem.php');

require_once('storagetouser.php');
require_once('storagesector.php');

//pol'zovatel i u4astki
class SectorToUser extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sector_to_user'; //position - storage
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//�������� ������������� �� ���.
	public function AddSectorsToUserArray($user_id, array $questions){
		
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
			$kpi=$this->GetItemByFields(array('user_id'=>$user_id,'sector_id'=>$v));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['sector_id']=$v;
				$add_array['user_id']=$user_id;
				
				
				
				
				$this->Add($add_array);//, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$user_id,
					'sector_id'=>$v
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
				if($vv==$v['sector_id']){
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
					'sector_id'=>$v['sector_id'],
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
	public function GetAllUserSectorsArr($id){
		$arr=array();
		$sql='
		
		(select distinct c.id as id, c.name as name, c.is_active as is_active, "0" as is_in from sector as c where (c.id in(select distinct sector_id from '.$this->tablename.' where user_id<>"'.$id.'") or c.id not in (select distinct sector_id from '.$this->tablename.')) and c.id not in(select distinct sector_id from '.$this->tablename.' where user_id="'.$id.'"))
		
		UNION
		(select distinct c.id as id, c.name as name, c.is_active as is_active, "1" as is_in from sector as c inner join '.$this->tablename.' as bc on c.id=bc.sector_id where bc.user_id="'.$id.'") order by name asc';
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
	public function GetSectorIdsArr($user_id){
		$arr=array();
		$set=new mysqlSet('select distinct u.id from '.$this->tablename.' as uc inner join sector as u on uc.sector_id=u.id where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc');
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			
			
			$arr[]=$f['id'];
		}
		
		//���������, ��� �� ������������� �������, ���� ���-�� ��-�� - ������� ��� � ������� �������...
		
		
		
		return $arr;	
	}
	
	//������ �������� ������������
	public function GetSectorArr($user_id){
		$arr=array();
		$set=new mysqlSet('select u.* from '.$this->tablename.' as uc inner join sector as u on uc.sector_id=u.id where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc');
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
	public function GetExtendedSectorIdsArr($user_id, $do_additional_object=true,  $s_s_id=NULL){
		
		//����� ���������, ���� � ���-�� user_id ����� �� ������ � ��-�� s_s_id
		$user_has_rights_to_s_s=true;
		if($s_s_id!==NULL){
			$set=new mysqlSet('select count(*) from user_rights where user_id="'.$user_id.'" and right_id=2 and object_id="'.$s_s_id.'"');
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0) $user_has_rights_to_s_s=false;
		}
		
		$_ss=new StorageSector;
		
		$arr=array(); $pairs=array();
		
		//�����. ���� ��� �� �����. ����...
		//��� ���������, ��� ������� ������ �� �������...
		
		$set=new mysqlSet('select count(*) from '.$this->tablename.' as uc 
		inner join sector as u on uc.sector_id=u.id 
		where u.is_active=1 and u.is_central=1 and uc.user_id="'.$user_id.'" order by u.id asc');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		$set=new mysqlSet('select count(*) from '.$this->tablename.' as uc 
		inner join sector as u on uc.sector_id=u.id 
		where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		
		
		if(((int)$f[0]==1)||((int)$g[0]==0)){
			//�.�.	��� ������ ��� �������� - ������ ��� �������
			//echo 'zzz';
			$set=new mysqlSet('select u.* from sector as u
			where u.is_active=1 order by u.id asc');
			$tc=$set->GetResultNumRows();
			
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				//������������ ���� �� ��������� ��-��	
				$storages=$_ss->GetCategsBookArr($f['id'],1);
				foreach($storages as $k=>$v){
					$make_pair=true;
					
					if($s_s_id!==NULL){
						if(($v['s_s']==1)&&($f['s_s']==1)){
							if($user_has_rights_to_s_s==false) $make_pair=false;	
						}
					}
					if($make_pair) $pairs[]=array($f['id'],$v['id']);
				}
				
				$arr[]=$f['id'];
			}
			
		}else{
			//�� �.�. - ������ ������� �������� ���������� � �����
			
			$set=new mysqlSet('select distinct u.id, u.s_s from '.$this->tablename.' as uc 
			inner join sector as u on uc.sector_id=u.id 
			where u.is_active=1 and uc.user_id="'.$user_id.'" order by u.id asc');
			$tc=$set->GetResultNumRows();
			
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				//������������ ���� �� ��������� ��-��	
				$storages=$_ss->GetCategsBookArr($f['id'],1);
				foreach($storages as $k=>$v){
					$make_pair=true;
					
					if($s_s_id!==NULL){
						if(($v['s_s']==1)&&($f['s_s']==1)){
							if($user_has_rights_to_s_s==false) $make_pair=false;	
						}
					}
					if($make_pair) $pairs[]=array($f['id'],$v['id']);
				}
				
				$arr[]=$f['id'];
			}
			
		}
		
		
		
		
		
		
		
		if($do_additional_object){
		//���������� ����� ��������...
			
		
		  $sql='select distinct iss.storage_id from interstore_storage_to_object as iss
		  inner join storage as st on (st.id=iss.storage_id and st.post_hran=1)
		  inner join user_rights as ur on (ur.object_id=iss.object_id and ur.right_id=2 and ur.user_id="'.$user_id.'")';
		  
		  //echo $sql;
		  $set=new mysqlSet($sql);
		  $tc=$set->GetResultNumRows();
		  
		  $rs=$set->GetResult();
		  for($i=0;$i<$tc;$i++){
			  $f=mysqli_fetch_array($rs);
			  
			  //var_dump($f);
			  $secs=$_ss->GetBookCategsArr($f['storage_id'],1);
			  foreach($secs as $kk=>$vv){
				  if(!in_array($vv['id'],	$arr)) $arr[]=$vv['id'];
				  
				  if(!in_array(array($vv['id'],$f['storage_id']),$pairs)) $pairs[]=array($vv['id'],$f['storage_id']);
			  }
		  
		  
		  }
		  
		}
		
		$result=array(
			'sector_ids'=>$arr,
			'pairs'=>$pairs		
		);
		
		
		return $result;	
	}
	
	
	public function buildQuery($_extended_limited_sector, $stor_name, $sec_name){
		$qry='';
		
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if(strlen($qry)>0){
						$qry.=' or ';
					}
					
					$qry.=' ('.$sec_name.'="'.$ev[0].'" and '.$stor_name.'="'.$ev[1].'" )';
				}
				
				if(strlen($qry)>0){
						  $qry=' and ('.$qry.')';
					  }
			}
		
		return $qry;	
	}
	
	public function buildStorages($_extended_limited_sector){
		$res=array();
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if(!in_array($ev[1],$res)) $res[]=$ev[1];
					
				}
				
			}
		return $res;
	}
	
	public function IsInPair($_extended_limited_sector, $sector_id, $storage_id){
		$res=false;
		
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if(($ev[0]==$sector_id)&&($ev[1]==$storage_id)){
						$res=true;	
						break;
					}
					
				}
				
			}else $res=true;
		
		return $res;	
	}
	
	public function buildQueryByStorage($_extended_limited_sector, $storage_id, $sec_name){
		$qry='';
		
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if($ev[1]==$storage_id){
					  
					  if(strlen($qry)>0){
						  $qry.=' or ';
					  }
					  
					  $qry.=' ('.$sec_name.'="'.$ev[0].'" )';
					}
				}
				
				 if(strlen($qry)>0){
						  $qry=' and ('.$qry.')';
					  }
					  
			}
		
		return $qry;	
	}
	
	public function buildQueryBySector($_extended_limited_sector, $sector_id, $sto_name){
		$qry='';
		
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if($ev[0]==$sector_id){
					  
					  if(strlen($qry)>0){
						  $qry.=' or ';
					  }
					  
					  $qry.=' ('.$sto_name.'="'.$ev[1].'" )';
					}
				}
				
				 if(strlen($qry)>0){
						  $qry=' and ('.$qry.')';
					  }
					  
			}
		
		return $qry;	
	}
	
	public function buildSectorsByStorage($_extended_limited_sector,$storage_id){
		$res=array();
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if($ev[1]==$storage_id){
						if(!in_array($ev[0],$res)) $res[]=$ev[0];
					}
					
				}
				
			}else{
				//�������� ��� ob-ty
				$_ss=new StorageSector;
				$res=$_ss->GetSectorIdsByStorageId($storage_id);	
			}
		return $res;
	}
	
	public function buildStoragesBySector($_extended_limited_sector,$sector_id){
		$res=array();
		if($_extended_limited_sector!==NULL){
				foreach($_extended_limited_sector['pairs'] as $ek=>$ev){
					if($ev[0]==$sector_id){
						if(!in_array($ev[1],$res)) $res[]=$ev[1];
					}
					
				}
				
			}else{
				//�������� ��� �������
				$_ss=new StorageSector;
				$res=$_ss->GetStorageIdsBySectorId($sector_id);
			}
		return $res;
	}
	
	
}
?>