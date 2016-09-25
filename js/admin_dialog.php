<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
require_once('../classes/user_s_item.php');


require_once('../classes/rl/rl_recorditem.php');
require_once('../classes/rl/rl_grobjectitem.php');
require_once('../classes/rl/rl_objectitem.php');
require_once('../classes/rl/rl_man.php');
require_once('../classes/rl/rl_table.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
if(!$au->user_rights->CheckAccess('w',1)){
	
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}
 


$ret='';
if(isset($_POST['action'])&&($_POST['action']=="retrive_has_record")){
	
	//������ ����� �������� ��� ����� �������
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>SecStr($_POST['tablename']),
										'additional_id'=>abs((int)$_POST['additional_id'])));
							
	//������ ������ ��� �����������
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>abs((int)$_POST['record_id']), 'rl_group_id'=>$rlg['id']));
											
	
	if($rlr!==false) $ret=1;
	
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="save_data")){
	//���������� ������	
	$is_checked=abs((int)$_POST['is_checked']);
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	
	$record_name=SecStr(iconv('utf-8','windows-1251',$_POST['record_name']));
	$record_description=SecStr(iconv('utf-8','windows-1251',$_POST['record_description']));
	
	
	$users=($_POST['users']);
	$objects=($_POST['objects']);
	$letters=($_POST['letters']);
	$actions=($_POST['actions']);
	
	
	//������ ����� �������� ��� ����� �������
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>$tablename,
										'additional_id'=>$additional_id));
	$value='������ '.SecStr($rlg['name']).', ������ '.SecStr($record_name).', �������� ������: '.SecStr($record_description);
	
	
	
							
	//������ ������ ��� �����������
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
	
	
	
	
	if($rlr!==false){
		//������ ����!
		$rl_record_id=$rlr['id'];
		if($is_checked==0){
			$_rlr->Del($rl_record_id);
			
			$log->PutEntry($result['id'],'�������� ������������� ������� � ������', NULL, 1,NULL,$value,$record_id);
		}
	}else{
		//������ ���!
		if($is_checked==1) {
			$rl_record_id=$_rlr->Add(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
			$log->PutEntry($result['id'],'������� ������������� �������  � ������',NULL, 1,NULL,$value,$record_id);
		}
	}
	
	
	$_rlm=new RLMan;
	$_rlo=new RLObjectItem;
	if($is_checked==1) {
		//��������� � �������� �����!
		
		foreach($users as $k=>$user){
			
			$letter=$letters[$k];
			$object=$objects[$k];
			$action=$actions[$k];
			
			$has=$_rlm->CheckAccess($rl_record_id,$user,$letter,$object);
			
			
			$rlo=$_rlo->GetItemById($object);
			$value1=$value.', '.SecStr($rlo['name']);
			
			if(($action==0)&&($has)){
				//������ �����
				echo 'away '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->RevokeAccess($rl_record_id, $user, $letter,$object);	
				
				$log->PutEntry($result['id'],'������ ������ � ������',$user, 1,NULL,$value1,$record_id);
				
				
			}
			elseif(($action==1)&&(!$has)){
				//���� �����
				echo 'to '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->GrantAccess($rl_record_id, $user, $letter,  $object);
				
				$log->PutEntry($result['id'],'��������� ������ � ������',$user, 1,NULL,$value1,$record_id);
			}
			
		}
			
		
	}
	
}
//retrive_table

elseif(isset($_POST['action'])&&($_POST['action']=="retrive_table")){
	 
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	
	
	//������ ����� �������� ��� ����� �������
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>$tablename,
										'additional_id'=>$additional_id));
	$value='������ '.SecStr($rlg['name']);
							
	//������ ������ ��� �����������
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
	
	
	if($rlr!==false){
		
		$_tab=new RlTable($rlg['id'], $rlr['id']);
		$ret=$_tab->Draw('admin_records/admin_users_byrow.html');
			
	}else $ret='��� ���������� ������� �� �������� ������������� �������. ����������, �������� ������� "�������� ������������� �������" � ������� "��������� � ��������" ��� ��������� ������������� ������� � ����������� �������������� ����.';
}

elseif(isset($_POST['action'])&&($_POST['action']=="draw_row_rights")){
	//����� ������� ��. ����� ��� ������������
	
	$user_id=abs((int)$_POST['user_id']);
	$gr_id=abs((int)$_POST['gr_id']);
	$record_id=abs((int)$_POST['record_id']);
	
	$_ui=new UserSItem;
	$user=$_ui->Getitembyid($user_id);
	//$object_id=abs((int)$_POST['object_id']);
	
	//echo $gr_id;
	$sm=new SmartyAj;
	
	//������ ������ ��� �����������
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$gr_id));
	
	
	$dt=new RLTable($gr_id,  $rlr['id']);
	
	
	
	
	$rights=$dt->GetTableRowArr($user_id);
	
	$sm->assign('user',$user);
	$sm->assign('rights_arr',$rights);
	
	
	$ret.=$sm->fetch("admin_records/admin_users_row.html");
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>