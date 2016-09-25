<?
require_once('abstractfileitem.php');

require_once('messageitem.php');
require_once('usersgroup.php');

require_once('fileitem.php'); //1
require_once('spitem.php'); //2
require_once('spsitem.php'); //3
require_once('filelitem.php'); //4
require_once('filepmitem.php'); //5

require_once('authuser.php');
require_once('actionlog.php');

require_once('filefolderitem.php');

require_once('rl/rl_recorditem.php');
require_once('rl/rl_connitem.php');
require_once('rl/rl_grobjectitem.php');
require_once('rl/rl_objectitem.php');
require_once('rl/rl_man.php');
require_once('rl/rl_table.php');

//����� ��������� ������� c� ���������� rl-�������
class FileRLFolderItem extends FileFolderItem{
	 
	
	
	//�������� 
	public function Add($params){
		
	 
		$mid=FileFolderItem::Add($params);
		
		
		/*
		1)������������� ������ rl_record ��� ���� �����
		2)������������� ������ ����� ������� (���)
		  ������������� ������ ����� ��������� (���)
		
		*/
		
		//1)������������� ������ rl_record ��� ���� �����
		/*
		record_id 	���� ��������� �����
		rl_group_id - ����� ������ � rl_group
			��� ��� �����? 
			-�� ������� rl_connections:
				- 	object_id - ����� ���!
				-����� rl_object_id - �� ���� �����  	rl_group_id 
		*/
		$_ob=NULL; $name=NULL;
		
		$_rl_record=new RLRecordItem; $_rl_conn=new RLConnItem; $_rl_group=new RLGrObjectItem;  		$_rl_object=new RLObjectItem; $_rl_man=new RLMan; $log=new ActionLog; 
		/*switch($this->storage_id){
			case 1:
				$_ob=840;
				 
				$name='����� � ���������';
			break;
			case 2:
				$_ob=841;
			 
				$name='���������� ����������';
			break;
			case 3:
				$_ob=476;
				 
				$name='���������� ���������� - �������������';
			break;
			case 4:
				$_ob=556;
				 
				$name='����� � ��������� - ������';
				
			break;
			case 5:
				$_ob=560;
				 
				$name='����� � ��������� - ����� +/-';
			break;	
				
			default:
				$_ob=840;
				 
				$name='����� � ���������';
			;	
		}
		if($_ob!==NULL){
			$rl_conn=$_rl_conn->GetItemByFields(array('object_id'=>$_ob));
			
			
			//$rl_conn=$_rl_conn->GetItemByFields(array('tablename'=>'file', 'additional_id'=>$this->storage_id));
			if($rl_conn!==false){
				$rl_object_id=$rl_conn['rl_object_id'];
				
				$rl_object=$_rl_object->GetItemById($rl_object_id);
				
				if($rl_object!==false){
					$rl_group_id=$rl_object['rl_group_id'];
					*/
					
				$rl_group=$_rl_group->GetItemByFields(array('tablename'=>'file', 'additional_id'=>$this->storage_id));
				if($rl_group!==false){	
					$rl_group_id=$rl_group['id'];
					
					//������ ������������� �������
					$rl_record_id=$_rl_record->Add(array('record_id'=>$mid,'rl_group_id'=>$rl_group_id));
					
					$log->PutEntry($params['user_id'],'�������������� ��������� ������������� �������  � ����� ��� ��������',NULL, 1,NULL,'����� '.$params['filename'],$mid);
					
					//������ ����� �������, ���������
					//������ - ��� � ���� ����� 116
					//�������� ������ rl_object_id - �������� ������ ������
					
					$sql='select * from rl_object where rl_group_id="'.$rl_group_id.'" order by ord desc, id asc';
	//	echo $sql;
			
					$set1=new mysqlSet($sql);
					$rs1=$set1->GetResult();
					$rc1=$set1->GetResultNumRows();
					for($j=0; $j<$rc1; $j++){
						
						$g=mysqli_fetch_array($rs1);
						foreach($g as $k=>$v) $g[$k]=stripslashes($v);
						 
						$rl_object_ids[]=$g['id'];
						
						
					}
					
					foreach($rl_object_ids as $k=>$v){
						$_rl_man->GrantAccess($rl_record_id, $params['user_id'],'w',$v);
						$rlo=$_rl_object->GetItemById($v);
						$value1=SecStr($rlo['name']);
			
						$log->PutEntry($params['user_id'],'�������������� ��������� ������� � ����� ��� ��������',$params['user_id'], 1,NULL,'����� '.$params['filename'].': '.$value1,$mid);	
					}
					//�������� �������...
					 
					$sql='select u.* from user as u
					inner join user_rights as ur on ur.user_id=u.id
					where u.is_active=1 and ur.right_id=2 and ur.object_id=118';
					 
					$set=new mysqlSet($sql);
					$rs=$set->GetResult();
					$rc=$set->GetResultNumRows();
					for($i=0; $i<$rc; $i++){
						$f=mysqli_fetch_array($rs);
						foreach($f as $k=>$v) $f[$k]=stripslashes($v);	
						
						foreach($rl_object_ids as $k=>$v){ 
							$_rl_man->GrantAccess($rl_record_id, $f['id'],'w',$v);
							
							$rlo=$_rl_object->GetItemById($v);
							$value1=SecStr($rlo['name']);
				
							$log->PutEntry($params['user_id'],'�������������� ��������� ������� � ����� ��� ��������', $f['id'], 1,NULL,'����� '.$params['filename'].': '.$value1,$mid);		 
						}
					}
					
					
					
						
				}
				 
			//}
		//}
		
		return $mid;
	}
	
	
	 
}
?>