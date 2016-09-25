<?
require_once('abstractgroup.php');
require_once('discr_man.php');
require_once('komplitem.php');

// ����������� ������
class KomplConfGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved_confirm';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
		
	//������ ����������� ������
	public function GetItemsByIdArr($id,$current_id=0,$object_id=84,$komplekt_ved=NULL,$sector_ss=NULL, $storage_ss=NULL){
		$arr=array();
		
		//���� �� �������������, �� ����� � ������� � ��-�� 84
		
		$_ki=new KomplItem;
		if($komplekt_ved===NULL)
			$komplekt_ved=$_ki->GetItemById($id);
		
		
		
		//echo $sql;
		
		$sql='select role.*,
		kc.pdate, 
		u.id as u_id, u.name_s as u_name_s, u.login as u_login, u.position_s as position_s
		
		from komplekt_ved_confirm_roles as role
		left join '.$this->tablename.' as kc on (role.id=kc.role_id  and kc.'.$this->subkeyname.'="'.$id.'")
		left join user as u on kc.user_id=u.id
		
		order by role.ord desc, role.id';
		
		
		$set=new MysqlSet($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//is_selected
			if($f['u_id']=="") $f['is_selected']=false;
			else $f['is_selected']=true;
			
			$is_active=false;
			$man=new DiscrMan;
			if($f['is_selected']){
				//����� �� ������ ���-��
			 
				
				$mode=$man->CheckAccess($current_id,'w',$f[$_ki->rd->FindRId(NULL,NULL,NULL,NULL,$sector_ss, $storage_ss,array('unconfirm_object_id','ss_unconfirm_object_id'))]); 
				
				/*
				//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ����������� (7) - ��������� ������ � ������� 551 (555)
				if(($f['id']==5)&&($komplekt_ved['sector_id']==7)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 551,555)));
				
				
				
				//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ���. ��� (9) - ��������� ������ � ������� 550 (554)		
				if(($f['id']==5)&&($komplekt_ved['sector_id']==9)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 550,554)));
				
				//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� �������������� (18) - ��������� ������ � ������� 591 (593)		
				if(($f['id']==5)&&($komplekt_ved['sector_id']==18)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 591,593)));*/
				
				
				$is_active=$mode;
				
				//var_dump($man->CheckAccess($current_id,'w',$f[$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 'unconfirm_object_id','ss_unconfirm_object_id'))])); 
			}else{
				
				//����� �� ��������� ���-��
				
				//���� ��� �������� ����, � ���� � ������ ������� - ����������� ���� (11), �� ��������� ������ � �����-������� 304
				
				$mode=$man->CheckAccess($current_id,'w',$f[$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 'confirm_object_id','ss_confirm_object_id'))]);
				
				//if(($f['is_primary']==1)&&($komplekt_ved['sector_id']==11)) $mode=$mode||$man->CheckAccess($current_id,'w',$f['super_object_id']);
				
				
				//���� ��� �������� ����, � ���� � ������ ������ - ���������� ���� (20), �� ��������� ������ �  ������� 532 (533)
				/*if(($f['is_primary']==1)&&($komplekt_ved['storage_id']==20)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 532,533)));
				
				
				//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ����������� (7) - ��������� ������ � ������� 549 (553)
				if(($f['id']==5)&&($komplekt_ved['sector_id']==7)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 549,553)));
				
				
				
				//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ���. ��� (9) - ��������� ������ � ������� 548 (552)		
				if(($f['id']==5)&&($komplekt_ved['sector_id']==9)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 548,552)));
				
				
				//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� �������������� (18) - ��������� ������ � ������� 590 (592)
				if(($f['id']==5)&&($komplekt_ved['sector_id']==18)) $mode=$mode||$man->CheckAccess($current_id,'w',$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $sector_ss, $storage_ss,array( 590,592)));
				*/
				
				
				$is_active=$mode;
				 //||$man->CheckAccess($current_id,'w',85)||$man->CheckAccess($current_id,'w',$f['super_object_id']);
				
			}
			
			//��������� ��������, ���������� �� �� ��������� ����:
			$is_active=$is_active&&$this->CheckPrimaryRole($id, $f['id']);
			
			
			//���� ��� ���� - ��������� �������:
			//������ ���� ������ �������� - ���� ��������� ��������� - �� ������ ����� ���������� �������
			if($f['is_primary']==1){
				
				$set1=new mysqlset('select count(*) from komplekt_ved_confirm where role_id<>"'.$f['id'].'" and komplekt_ved_id="'.$id.'"');	
				
				$rs1=$set1->GetResult();
				$g1=mysqli_fetch_array($rs1);
				if((int)$g1[0]>0) $is_active=$is_active&&false;
						
			}
			
			
			$f['is_active']=$is_active;
			
			
			$f['pdate']=date("d.m.Y H:i:s", $f['pdate']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//������� ������� ����������� �� ������
	public function CountUtv($id){
		$res=0;
		$sql='select count(*)
		
		from '.$this->tablename.' as kc where kc.'.$this->subkeyname.'="'.$id.'"';
		
		
		$set=new MysqlSet($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$f=mysqli_fetch_array($rs);
		
		$res=(int)$f[0];
		return $res;
	}
	
	//��������� ���������� �����������: �������� �� ��������� �������
	public function CheckPrimaryRole($komplekt_id, $current_role_id=0){
		$res=true;
		
		//���� �� ������ primary ����
		$set=new mysqlset('select * from komplekt_ved_confirm_roles where is_primary=1 limit 1');	
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		if($rc>0){
			//����
			$f=mysqli_fetch_array($rs);
			
			//���� ������� ���� � ���� primary - �� ���� ���������
			if($current_role_id!=$f['id']){
			   
			  //�������������: ������ ����� ����������� ��������� ����, ���� ���� ����������� ���������?
			
			   
			  //���������, ���� �� ����������� �� ��������� ��������� ���� � ������ ������	
			  $set1=new mysqlset('select count(*) from komplekt_ved_confirm where role_id="'.$f['id'].'" and komplekt_ved_id="'.$komplekt_id.'"');	
			  $rs1=$set1->GetResult();
			  $rc1=$set1->GetResultNumRows();
			  $g=mysqli_fetch_array($rs1);
			  
			  if((int)$g[0]>0) $res=true;
			  else $res=false;
			  
			  //���� ����� ����������� ��������� ����, ���� �� ������ ���� ���� ����������� - ���� ����� ���...
			   $set1=new mysqlset('select count(*) from komplekt_ved_confirm where role_id="'.$current_role_id.'" and komplekt_ved_id="'.$komplekt_id.'"');	
			  $rs1=$set1->GetResult();
			  $rc1=$set1->GetResultNumRows();
			  $g=mysqli_fetch_array($rs1);
			  
			   if((int)$g[0]>0) $res=true;
			 
			  
			}
		}
			
			
		return $res;
	}
	
	
	//������ ��������, ���� �� �������� ����
	public function HasPrimaryRole($komplekt_id){
		$res=true;
		
		
		
		//���� �� ������ primary ����
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where role_id in(select id from komplekt_ved_confirm_roles where is_primary=1) and komplekt_ved_id="'.$komplekt_id.'"');	
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	
	//������ ��������, ���� �� �� �������� ����
	public function HasUnPrimaryRole($komplekt_id){
		$res=true;
		
		
		
		//���� �� ������ primary ����
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where role_id in(select id from komplekt_ved_confirm_roles where is_primary=0) and komplekt_ved_id="'.$komplekt_id.'"');	
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	//��������, ���� �� ���� 1 �����������
	public function HasAnyConfirm($komplekt_id){
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"');	
		
		//echo 'select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"';
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	//��������, ���� �� ��� �����������
	public function HasAllConfirm($komplekt_id){
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"');	
		
		//echo 'select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_id.'"';
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$conf=((int)$f[0]);
		
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm_roles');
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$roles=((int)$f[0]);
		
		return ($conf==$roles);
	}
	
	
	//��������, ���� �� ��� ������ ������ ������ ���-��� � ������ ����
	public function HasConfirmByUserRole($komplekt_ved_id, $user_id, $role_id){
		
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_ved_id.'" and user_id="'.$user_id.'" and role_id="'.$role_id.'"');	
		
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	//��������, ���� �� ��� ������ ������ ������ ����
	public function HasConfirmByRole($komplekt_ved_id,  $role_id){
		
		$res=true;
		
		$set=new mysqlset('select count(*) from komplekt_ved_confirm where komplekt_ved_id="'.$komplekt_ved_id.'"  and role_id="'.$role_id.'"');	
		
		
		$rs=$set->GetResult();

		$f=mysqli_fetch_array($rs);
		
		$res=((int)$f[0]>0);
		
		return $res;
	}
	
	
	//������ ���� ����������� ������
	public function GetPointsArr($komplekt_ved_id){
		$arr=array();
		
		$sql='select * from '.$this->tablename.' where komplekt_ved_id="'.$komplekt_ved_id.'" ';
		$set=new MysqlSet($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$arr[]=$f;
		}
		
		return $arr;	
	}
	
}
?>