<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������



require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

;
require_once('classes/sectorgroup.php');
require_once('classes/supplieritem.php');
require_once('classes/opfitem.php');
require_once('classes/sectoritem.php');
require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');


require_once('classes/komplitem.php');
require_once('classes/komplconfitem.php');
require_once('classes/komplconfgroup.php');
require_once('classes/komplscanconfgroup.php');

require_once('classes/komplnotesgroup.php');
require_once('classes/komplnotesitem.php');
require_once('classes/billgroup.php');

require_once('classes/bill_in_group.php');

require_once('classes/komplmarkitem.php');
require_once('classes/komplmarkpositem.php');
require_once('classes/komplmarkposgroup.php');
require_once('classes/komplmarkgroup.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/messageitem.php');
require_once('classes/user_s_group.php');

require_once('classes/supplier_to_user.php');
require_once('classes/orgitem.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_position=new PosItem;
$ui=new KomplItem;

$_sectors=new SectorGroup;
//$lc=new LoginCreator;
$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;
$_scanconf=new KomplScanConf;

	$_supgroup=new SuppliersGroup;
		$_opf=new OpfItem;



$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();
$_pch_pergroup=new PerGroup;
$pch_periods=$_pch_pergroup->GetItemsByIdArr($result['org_id'],0,1);

//$ui->CheckClosePdate($editing_user['id'], $rss27, $editing_user, $pch_periods)

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);



if(!isset($_GET['printmode'])){
	if(!isset($_POST['printmode'])){
		$printmode=0;
	}else $printmode=abs((int)$_POST['printmode']); 
}else $printmode=abs((int)$_GET['printmode']);


if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


if(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//�������� ������� ������������
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	 
	//����������� �� �-��
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	if($limited_supplier!==NULL){
		if(!in_array($editing_user['supplier_id'], $limited_supplier)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
	}
	
	
}





$object_id=array();
switch($action){
	case 0:
	$object_id[]=81;
	break;
	case 1:
	$object_id[]=$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(82,382)); //82;
	$object_id[]=$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(282,400));
	break;
	case 2:
	$object_id[]=$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(83,401));
	break;
	default:
	$object_id[]=81;
	break;
}

//echo $ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(82,382)); die();

$_editable_status_id=array();
$_editable_status_id[]=11;
$_editable_status_id[]=1;

//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}





if($print!=0){
	if(!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(282,400)))){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}



//������ ������� 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'������ ����� ������',NULL,$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(82,382)), NULL, $editing_user['id'],$id);
	else
	$log->PutEntry($result['id'],'������ ����� ������: ������ ��� ������',NULL,$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(282,400)), NULL, $editing_user['id'],$id);						
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',81)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//������� �������� ������ ����������
	
	//$params['name']=SecStr($_POST['name']);
	$params['org_id']=abs((int)$result['org_id']);
	if($au->user_rights->CheckAccess('w',317)) $params['code']=SecStr($_POST['code']);
	//$params['notes']=SecStr($_POST['notes']);
	
	$params['is_active']=0;
	
	$params['begin_pdate']=DateFromdmY($_POST['begin_pdate']);
	
	$params['pdate']=DateFromdmY(date('d.m.Y'));
	
	
	$params['end_pdate']=DateFromdmY($_POST['end_pdate'])+60*60*24-1;
	 
	$params['supplier_id']=abs((int)$_POST['supplier_id']);
	
	$params['manager_id']=$result['id'];
	
	$params['status_id']=11;
	
	
	if($au->user_rights->CheckAccess('w',473)){
		if(isset($_POST['cannot_eq'])){
			 $params['cannot_eq']=1;
			 $params['cannot_eq_id']=$result['id'];
			 $params['cannot_eq_pdate']=time();
		}else $params['cannot_eq']=0;
		
						
	}	
	
	
	if($au->user_rights->CheckAccess('w',536)){
		if(isset($_POST['cannot_an'])){
			 $params['cannot_an']=1;
			  $params['cannot_an_id']=$result['id'];
			 $params['cannot_an_pdate']=time();
			 
		}else $params['cannot_an']=0;
		
						
	}	
	
	
	$code=0;
	
	
	$code=$ui->Add($params);
	
	//������ � �������
	if($code>0){
		$log->PutEntry($result['id'],'������ ������',NULL,81,NULL,$params['name'],$code);	
		
		
		foreach($params as $k=>$v){
			
		 
				if($k=='supplier_id'){
					$_si=new SupplierItem; $_opf=new OpfItem;
					$si=$_si->GetItemById($v); $opf=$_opf->GetItemById($si['opf_id']);
					
					
					$log->PutEntry($result['id'],'������ ������',NULL,82, NULL, SecStr('���������� ���������� '.$si['code'].' '.$opf['name'].' '.$si['full_name']),$code);			
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'������ ������',NULL,82, NULL, '� ���� '.$k.' ����������� �������� '.$v,$code);		
			 
		}
		
		
		if($au->user_rights->CheckAccess('w',473)&&($params['cannot_eq']==1)){
			//������� ��������������
			$_kni=new KomplNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$code;
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=$result['id'];
		
		
			$notes_params['note']='�������������� ����������: ���� ��������� �������������� ������������ ������.';
			$_kni->Add($notes_params);
		}
		
		
		if($au->user_rights->CheckAccess('w',536)&&($params['cannot_an']==1)){
			//������� ��������������
			$_kni=new KomplNotesItem;
			$notes_params=array();
			$notes_params['is_auto']=1;
			$notes_params['user_id']=$code;
			$notes_params['pdate']=time();
			$notes_params['posted_user_id']=$result['id'];
		
		
			$notes_params['note']='�������������� ����������: ���� ��������� �������������� ������������� ������.';
			$_kni->Add($notes_params);
		}
		
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',177))){
		//�������
		$positions=array();
		foreach($_POST as $k=>$v){
			if(eregi("^pos_([0-9]+)_",$k)){
				$positions[]=array(
					'komplekt_ved_id'=>$code,
					'position_id'=>abs((int)eregi_replace("^pos_([0-9]+)_","",$k)),
					'quantity_confirmed'=>((float)str_replace(",",".",$v)),
					'storage_id'=>abs((int)$_POST["storage_id_".eregi_replace("^pos_","",$k)])
				);
			}
		}	
		
		/*echo '<pre>';
		print_r($positions);
		echo '</pre>';
		*/
		//������ �������
		$ui->AddPositions($code,$positions);
		//die();
		//������� � ������
		foreach($positions as $k=>$v){
			$pos=$_position->GetItemById($v['position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> ���-�� '.$v['quantity_confirmed'].'';
				
				
				$log->PutEntry($result['id'],'������� ������� � ������', NULL, 82,NULL,$descr,$code);	
				
			}
		}	
		
	}
	
	
	//����������
	if(($code>0)&&($au->user_rights->CheckAccess('w',179))){
		$_kni=new KomplNotesItem;
		
		$can_edit_notes=true;
		if($params['sector_id']!=6) $can_edit_notes=$can_edit_notes&&$au->user_rights->CheckAccess('w',179);
		if($params['sector_id']==6) $can_edit_notes=$can_edit_notes&&$au->user_rights->CheckAccess('w',594);
		
		if($can_edit_notes) foreach($_POST as $k=>$v){
		
			if(eregi("^notes_textarea_([0-9]+)",$k)){
				//echo 'zzzzzzzzzzzzzzzz';
				
				
				$notes_params=array();
				$notes_params['user_id']=$code;
				$notes_params['pdate']=time();
				$notes_params['posted_user_id']=$result['id'];
				
				$notes_params['note']=SecStr($_POST[$k]);
				
				
				$_kni->Add($notes_params);
				$log->PutEntry($result['id'],'������� ���������� �� ������', NULL,179, NULL,$notes_params['note'],$code);
			}
		}	
	
	}
	/*
	echo '<pre>';
	print_r($_POST);
	echo '</pre>';
	
	die();*/
	
	//���������������
	if(isset($_POST['doNew'])){
		header("Location: komplekt.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//���� ���� ������ � ������� 11 - ������ ������������ - �� ������� ����		
		if(!$au->user_rights->CheckAccess('w',82)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_komplekt.php?action=1&id=".$code.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: komplekt.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//�������������� pozicii
	if(!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(82,382)))){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//�������������� ��������, ���� is_confirmed==0
	$_kcg=new KomplConfGroup;
	$condition=true;
	
	//$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	$condition=$condition&&($editing_user['is_active']==0)&&(!$_kcg->HasAnyConfirm($id));
	
	$condition=$condition&&(!isset($_POST['has_any_confirm'])); 
	//print_r($_POST); die();
	
	if($condition){
		$params=array();
		//������� �������� ������ ����������
		
		//$params['name']=SecStr($_POST['name']);
		if($au->user_rights->CheckAccess('w',317)) $params['code']=SecStr($_POST['code']);
		//$params['notes']=SecStr($_POST['notes']);
		
		//$params['is_active']=0;
		
		$params['begin_pdate']=DateFromdmY($_POST['begin_pdate']);
		
		$params['end_pdate']=DateFromdmY($_POST['end_pdate'])+60*60*24-1;
		$params['supplier_id']=abs((int)$_POST['supplier_id']);
		
		$ui->Edit($id, $params, false, $result);
		
		//������ � �������
		//������ � ���. �������� ������ � ����� ������
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'������������ ������',NULL,82, NULL, '� ���� '.$k.' ����������� �������� '.$v,$id);		
			}
		}
		
		
	}
	
	/*��������� ������ - ������ ���� ����������������*/
	$test_ui=$ui->GetItemById($id);
	$test_has_primary_confirm=$_kcg->HasPrimaryRole($id);
	if(
	($test_ui['is_active']==0)&&($test_has_primary_confirm)&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(473,475)))
	
	||
		
		($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(484,486))))
	
	){
		
		$params1=array();
		if(isset($_POST['cannot_eq'])){
			 $params1['cannot_eq']=1;
			  $params1['cannot_eq_id']=$result['id'];
			 $params1['cannot_eq_pdate']=time();
		}else $params1['cannot_eq']=0;
		
		$ui->Edit($id, $params1, false, $result);
		
		//������ � �������
		//������ � ���. �������� ������ � ����� ������
		foreach($params1 as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'������������ ������',NULL,$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(473,475)), NULL, '� ���� '.$k.' ����������� �������� '.$v,$id);	
				
				//������� ��������������
				if($k=='cannot_eq'){
				$_kni=new KomplNotesItem;
				$notes_params=array();
				$notes_params['is_auto']=1;
				$notes_params['user_id']=$id;
				$notes_params['pdate']=time();
				$notes_params['posted_user_id']=$result['id'];
				
				if($params1['cannot_eq']==1){
					$notes_params['note']='�������������� ����������: ���� ��������� �������������� ������������ ������.';
				}else{
					$notes_params['note']='�������������� ����������: ���� �������� �������������� ������������ ������.';
				}
				
				$_kni->Add($notes_params);
				}
					
			}
		}
	}
	
	
	/*��������� ������ - ������ ���� �����������������*/
	if(
	($test_ui['is_active']==0)&&($test_has_primary_confirm)&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(536,540)))
	
	||
		
		($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(537,541))))
	
	){
		
		$params1=array();
		if(isset($_POST['cannot_an'])){
			 $params1['cannot_an']=1;
			  $params1['cannot_an_id']=$result['id'];
			 $params1['cannot_an_pdate']=time();
		}else $params1['cannot_an']=0;
		
		$ui->Edit($id, $params1, false, $result);
		
		//������ � �������
		//������ � ���. �������� ������ � ����� ������
		foreach($params1 as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'������������ ������',NULL,$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(536,540)), NULL, '� ���� '.$k.' ����������� �������� '.$v,$id);	
				
				//������� ��������������
				if($k=='cannot_an'){
				$_kni=new KomplNotesItem;
				$notes_params=array();
				$notes_params['is_auto']=1;
				$notes_params['user_id']=$id;
				$notes_params['pdate']=time();
				$notes_params['posted_user_id']=$result['id'];
				
				if($params1['cannot_an']==1){
					$notes_params['note']='�������������� ����������: ���� ��������� �������������� ������������� ������.';
				}else{
					$notes_params['note']='�������������� ����������: ���� �������� �������������� ������������� ������.';
				}
				
				$_kni->Add($notes_params);
				}
					
			}
		}
	}
	
	
	
	
	//������� ������
	if(($condition&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(177,381))))||
	(($ui->CanEditQuantities($id,$rss,$editing_user,$editing_user_sector['s_s'],$editing_user_storage['s_s'],$result))&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(301,406))))
	||
	($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(446,447))))
	||
	($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(359,392)))
		&&$_kcg->HasConfirmByRole($editing_user['id'], 3)
		&&!$_kcg->HasConfirmByRole($editing_user['id'], 6))
	){		
		  //������� �.�.
		  $positions=array();
		  foreach($_POST as $k=>$v){
			  if(eregi("^pos_([0-9]+)_",$k)){
				  $positions[]=array(
					  'komplekt_ved_id'=>$id,
					  'position_id'=>abs((int)eregi_replace("^pos_([0-9]+)_","",$k)),
					  'quantity_confirmed'=>((float)str_replace(",",".",$v)),
					  'storage_id'=>abs((int)$_POST["storage_id_".eregi_replace("^pos_","",$k)])
				  );
			  }
		  }	
		  
		 /* echo '<pre>';
		  print_r($positions);
		  echo '</pre>';
		  die();
		  */
		  
		  //������ �������
		  if(count($positions)>0){
			$log_entries=$ui->AddPositions($id,$positions);
			
			//������������� ���� �������� ������ ��������� � ������ �������!
			// ���������� � ������� AddPositions!!!
			
			
			//������� � ������ �������� � �������������� �������
			$_komplnoteitem=new KomplNotesItem;
			foreach($log_entries as $k=>$v){
				$description=SecStr($v['name']).' <br /> ���-��: '.$v['quantity_confirmed'].' <br /> ������������� �� ������: '.$v['quantity_initial'].' ';
				
				
				if($v['action']==0){
					$log->PutEntry($result['id'],'������� ������� � ������',NULL,82,NULL,$description,$id);
					//����������� � ����������� � ��������� ������� ���������� ������� � ���. ������ (��� �������, ��� ������ ���������� � ���� ��� ��-�� � � ���  ���� ����� �� ���. � ���� ���
					if($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(359,392)))&&$_kcg->HasConfirmByRole($editing_user['id'], 3)){
							
						$_komplnoteitem->Add(array(
							'is_auto'=>1,
							'posted_user_id'=>$result['id'],
							'note'=>'�������������� ����������: '.$result['name_s'].' ('.$result['login'].') �������(�) ������� � ������������ ������: '.str_replace('<br />',', ', $description),
							'pdate'=>time(),
							'user_id'=>$editing_user['id']
						));
					}
						
				}elseif($v['action']==1){
					$log->PutEntry($result['id'],'������������ ������� ������',NULL,82,NULL,$description,$id);
					
					//����������� � ����������� ���-�� ���-�� ������� �� ����������� �������....
					
					
					//����� ��������������, ���� � ��� �������� ����������� � ��� �� ��������� - ��� �������������� ���-��!
					if($_kcg->HasPrimaryRole($editing_user['id'])&&
					
					!($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(446,447))))
					
					){
						/*$v['quantity_confirmed_was']
						$v['quantity_confirmed']	*/
						$_komplnoteitem->Add(array(
							'is_auto'=>1,
							'posted_user_id'=>$result['id'],
							'note'=>'�������������� ����������: '.$result['name_s'].' ('.$result['login'].') ������������(�) ���������� ������� '.SecStr($v['name']).', ���� '.$v['quantity_confirmed_was'].', ����� '.$v['quantity_confirmed'],
							'pdate'=>time(),
							'user_id'=>$editing_user['id']
						));
					}
					
					
				}elseif($v['action']==2){
					$log->PutEntry($result['id'],'������ ������� ������',NULL,82,NULL,$description,$id);
				}
				
			}
		  }
		
		}
	
	
	
	
	
	
	if(
	($editing_user['status_id']!=3)&&
	(
	($au->user_rights->CheckAccess('w',305)||
	$au->user_rights->CheckAccess('w',85)||
	$au->user_rights->CheckAccess('w',84)||
	$au->user_rights->CheckAccess('w',359)||
	$au->user_rights->CheckAccess('w',360)
	/*$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(180,386)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(181,387)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(182,388)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(183,390)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(296,391)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(184,393)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(185,394)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(186,395)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(187,396)))||
	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(297,397)))*/)
	))
	{
		
			$_kci=new komplconfitem;
		$_kcri=new KomplConfRoleItem;
		
		//���� ��������� ������� ����������� ������ ���������
		
		$make_kompl_marker=false;
		//���� ���� ���. ���-�� + ������ � �������� (2, 13, 12) + �� ����� ������ ������� 6 - �������� ������!
		
		
		//var_dump($ui->DocCanUnconfirm($id,$rss2,$editing_user));
		if(!$ui->DocCanUnconfirm($id,$rss2,$editing_user)&&(($editing_user['status_id']==2)||($editing_user['status_id']==12)||($editing_user['status_id']==13))){
			$count_of_actions=0;
			foreach($_POST as $k=>$v){
				
			
			  if(eregi("^ethalon_",$k)){
			  
				  
				  $key=abs((int)eregi_replace("^ethalon_",'',$k));
				  
				  
				  //key - id ����
				  if(isset($_POST['conf_'.$key])){
					  //���� ����		
					  //echo 'zzzzzzzzzzzz'; die();
					  $test=$_kci->GetItemByFields(array('komplekt_ved_id'=>$id, 'role_id'=>$key   ));
					  if(($test===false)){
						//������ �����-�� �����������...  
						break;
					  }
				  }else{
					  //��� ����
					  $test=$_kci->GetItemByFields(array('komplekt_ved_id'=>$id, 'role_id'=>$key   ));
					  if(($test!==false)){
						//������� �����-�� �����������  
						 //echo $key;
			  	
						$count_of_actions++;
						if($key==6){
							$make_kompl_marker=true;	
						}
					  }
					  
				  }
			  }
			}
			
			if($count_of_actions>1) $make_kompl_marker=false;
			
		}
	
		//var_dump($make_kompl_marker);
		//die();
	
	//���� ����������� 	
	
		
		
		
		foreach($_POST as $k=>$v){
			 
			if(eregi("^ethalon_",$k)){
				
				
				$key=abs((int)eregi_replace("^ethalon_",'',$k));
				//key - id ����
				if(isset($_POST['conf_'.$key])){
					//���� ����		
					
					//���������, ���� �� ��� ����������
					$test=$_kci->GetItemByFields(array('komplekt_ved_id'=>$id, 'role_id'=>$key   ));
					
					 
					$kcri=$_kcri->GetItemById($key);
					$test_kompl_ved=$ui->GetItemById($id);
					$krit=true;
					 
					if(($test===false)&&$krit){
						$_kci->Add(array('komplekt_ved_id'=>$id, 'user_id'=>$result['id'], 'pdate'=>time(), 'role_id'=>$key));
						$role=$_kcri->GetItemById($key);
						$log->PutEntry($result['id'],'�������� ������',NULL,82,NULL,'����: '.$role['name'],$id);	
						
					}
					
				}else{
					//��� ����
					//���� ������� ���� - ���� ���������� ������� - �� �� ����� �����,
					//������ ���� ��� ������ �����!
					$kcri=$_kcri->GetItemById($key);
					$has_unprimary=$_kcg->HasUnPrimaryRole($id);
					
				
					
					$krit=true;
					/*if(($kcri['is_primary']==1)){
						if($has_unprimary) $krit=false;
					
					}*/
					$test=$_kci->GetItemByFields(array('komplekt_ved_id'=>$id, 'role_id'=>$key   ));
					
					//���� �� ����� ��������� ������� ���???
					//������� ����
					if(($test!==false)&&$krit){
						$_kci->Del($test['id']);
						$role=$_kcri->GetItemById($key);
						$log->PutEntry($result['id'],'����� ����������� ������',NULL,82, NULL, '����: '.$role['name'],$id);
					}
				}
			}
		}
		
		
		
		
		
		
		
		
		//��������� ����� �������
		$is_confirmed=(int)$_scanconf->ScanConfirm($id);
		$ui->ScanDocStatus($id, array(),array(),NULL,$result);
		
		
		
		//print_r($is_confirmed); die();
		if($editing_user['is_active']!=$is_confirmed){
			//�������� ������
			$params=array();
			$params['is_active']=$is_confirmed;
			$ui->Edit($id, $params, true);
			if($is_confirmed==0) $log->PutEntry($result['id'],'����� ����������� ������',NULL,82, NULL, NULL,$id);
			elseif($is_confirmed==1){
				  $log->PutEntry($result['id'],'������ ����������',NULL,82, NULL, NULL,$id);
				 
				 
				 
				  
			}
			
		}
		
		
		if($make_kompl_marker){
			
			//������ ������ ��������������� �������������� �����������...
			
			$_kmi=new KomplMarkItem;
			$_kmpi=new KomplMarkPosItem;
			$test_mark=$_kmi->GetItemByFields(array('komplekt_ved_id'=>$editing_user['id']));
			if($test_mark===false){
				$marker_code=$_kmi->Add(array('komplekt_ved_id'=>$editing_user['id'], 'old_status_id'=>$editing_user['status_id'], 'ptime'=>time(), 'expiration'=>600, 'user_id'=>$result['id']));
				$positions_to_marker=$ui->GetPositionsArr($editing_user['id'],false);
				
				foreach($positions_to_marker as $k=>$v){
					$test_array=array();
					$test_array['position_id']=$v['position_id'];
					$test_array['marker_id']=$marker_code;
					//$test_array['storage_id']=$v['storage_id'];
					$test_mp=$_kmpi->GetItemByFields($test_array);
					if($test_mp===false){
						$test_array['quantity']=$v['quantity_confirmed'];
						$_kmpi->Add($test_array);
					}
				}
			}
			
		}
		
	}
	
	
	
	
	//���������������
	if(isset($_POST['doEdit'])){
		header("Location: komplekt.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//���� ���� ������ � ������� 11 - ������ ������������ - �� ������� ����		
		if(!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(82,382)))){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_komplekt.php?action=1&id=".$id.'&from_begin='.$from_begin);
		die();	
		
	}else{
		header("Location: komplekt.php");
		die();
	}
	
	die();
}






//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


	$_menu_id=14;
		if($print==0) include('inc/menu.php');
	
	
	//������������ ��������� ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//�������� �������
		
		$sm1=new SmartyAdm;
		
		$sm1->assign('pdate', date('d.m.Y'));
		
		//�������
		/*$do_limit_sector=$au->FltSector($result);
		if($do_limit_sector){
			$_sectors_to_user=new SectorToUser();
		
	
			$sectors_to_user_ids=$_sectors_to_user->GetSectorIdsArr($result['id']);
		}
		
		
		$sectors=$_sectors->GetItemsArr(0,1);
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-��������-';
		foreach($sectors as $k=>$v){
			if($do_limit_sector&&(!in_array($v['id'],$sectors_to_user_ids))) continue;
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		
		 
			$selected_sector_id=0;
		 
		$sm1->assign('group_id', $selected_sector_id); 
		
		$sm1->assign('group_ids', $st_ids);
		$sm1->assign('group_names', $st_names);*/
		
			//����������
			
		$dec=new DBDecorator();
		
		 
			
		$_supgroup->GetItemsForKomplekt('kp/suppliers_list.html', $dec, false, $supgroup, $result);
		
		$sm1->assign('suppliers',$supgroup);
		
		
		
		 
		
		//��� ������
		$posgroupgroup=$_posgroupgroup->GetItemsArr(); // >GetItemsTreeArr();
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-��������-';
		foreach($posgroupgroup as $k=>$v){
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		$sm1->assign('tov_group_ids', $st_ids);
		$sm1->assign('tov_group_names', $st_names);
		
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$acts[]=$f;
		}
		$sm1->assign('dim',$acts);
		
		//������ ����������
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',179));
		
		//����� �� ������ ������������?
		$sm1->assign('can_edit_position_names',$au->user_rights->CheckAccess('w',347));
		
		
		//��������, ���� �� �������� �����������
		
		$sm1->assign('has_primary_confirm', false);
		
		//����� �� ��������� ����������������?
		$sm1->assign('can_cannot_eq', $au->user_rights->CheckAccess('w',473));
		
		//����� �� ��������� �����������������?
		$sm1->assign('can_cannot_an', $au->user_rights->CheckAccess('w',536));
		
		
		
		//����� �� ������� ����������?
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',301));
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',177));
		$sm1->assign('can_delete_positions',true); //$au->user_rights->CheckAccess('w',178)); 
		
		$sm1->assign('can_edit_given_no',$au->user_rights->CheckAccess('w',317)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',81)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',82)); 
		
		
		//�������� ������� ���-��
		$sm1->assign('can_create_position',$au->user_rights->CheckAccess('w',67)); 
		
		
		$sm1->assign('can_re', $au->user_rights->CheckAccess('w',862));
		
		
		
		//���� ������ �������
		$sm1->assign('pch_date', $pch_date);
		
		$sm1->assign('now', mktime(0,0,0,date('n'),date('j'),date('Y')));
	
		$user_form=$sm1->fetch('komplekt/komplekt_create.html');
		
		
		
		 
		
		//������� ��������� �����
		if($au->user_rights->CheckAccess('w',97)){
			$sm->assign('has_sv_sch',true);
			
			$sm->assign('sv_sch','� ������ ������ �������� ��������� ������ ������ ����������.<br />
 ����������, ������� ������ "������� ������ � ������� � �����������" �� ������� "������" ��� ��������� ����������� ��������� ��������� ������.');	
		
		}
		
		
		//������� ��������� �����
		if($au->user_rights->CheckAccess('w',606)){
			$sm->assign('has_sv_sch_in',true);
			
			$sm->assign('sv_sch_in','� ������ ������ �������� ��������� ������ ������ ����������.<br />
 ����������, ������� ������ "������� ������ � ������� � �����������" �� ������� "������" ��� ��������� ����������� ��������� ��������� ������.');	
		
		}
		
		//������� ������ ������� �� �������
		if($au->user_rights->CheckAccess('w',521)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','� ������ ������ �������� ������� ������� ������ ����������.<br />
 ����������, ������� ������ "������� ������ � ������� � �����������" �� ������� "������" ��� ��������� ����������� ��������� ������� �������.');		
		}
		
		
	}elseif($action==1){
		//�������������� �������
	
		 
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		$_kcg=new KomplConfGroup;
		$sm1=new SmartyAdm;
		
		
		//echo date('d.m.Y',$ui->GetClosePdate($editing_user['id']));
		
		//����
		$editing_user['begin_pdate_unf']=$editing_user['begin_pdate'];
		$editing_user['end_pdate_unf']=$editing_user['end_pdate'];
		
		$editing_user['begin_pdate']=date("d.m.Y",$editing_user['begin_pdate']);
		$editing_user['end_pdate']=date("d.m.Y",$editing_user['end_pdate']);
		
		
		if($editing_user['pdate']==0) $editing_user['pdate']='-';
		else $editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		//�������, ����
		$color='black';
		$editing_user['blink']=$ui->kompl_blink->OverallBlink($editing_user['id'], $editing_user['status_id'], $result['id'], $result['is_supply_user'], $color,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s']);
			
		
		
		
		//��� �������
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		//���������
		$_si=new SupplierItem;
		$si=$_si->GetItemById($editing_user['supplier_id']);
		$_opfitem=new OpfItem;
		
		$opfitem=$_opfitem->getItemById($si['opf_id']); 
		$editing_user['supplier_id_string']=$opfitem['name'].' '.$si['full_name'];
		
		$sm1->assign('supplier', $si);
		
		//�����������
		$_org_item=new OrgItem;
		$org_item=$_org_item->GetItemById($result['org_id']);
		$sm1->assign('org', $org_item);
		
		
		/*$supgroup=*/
		/*$dec=new DBDecorator;
		$dec->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		
		$_supgroup->GetItemsForBill('komplekt/suppliers_list.html', $dec, false, $supgroup, $result, $editing_user['supplier_id']); //GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0,'is_active'=>1));
		$sm1->assign('suppliers',$supgroup);
		*/
		$dec=new DBDecorator();
			
		$_supgroup->GetItemsForKomplekt('kp/suppliers_list.html', $dec, false, $supgroup, $result);
		
		$sm1->assign('suppliers',$supgroup);
		
		
		
		//���� �������������
		
		$editing_user['can_annul']=$ui->DocCanAnnul($editing_user['id'],$reason,$result['id'],$editing_user,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'])&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(83,401)));
		
		
		
		if(!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(83,401)))) $reason='������������ ���� ��� ������ ��������';
		$editing_user['can_annul_reason']=$reason;
		
		
		$editing_user['binded_to_annul']=$ui->GetBindedDocumentsToAnnul($editing_user['id']);
		
		
		$editing_user['can_restore']=$ui->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(132,402)));
			if(!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(132,402)))) $reason='������������ ���� ��� ������ ��������';
		
		
		$sm1->assign('now', mktime(0,0,0,date('n'),date('j'),date('Y')));
		
		$sm1->assign('komplekt_ved',$editing_user);
		
		
		
		
		//��� ������
		$posgroupgroup=$_posgroupgroup->GetItemsArr(); // //>GetItemsTreeArr();
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-��������-';
		foreach($posgroupgroup as $k=>$v){
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		$sm1->assign('tov_group_ids', $st_ids);
		$sm1->assign('tov_group_names', $st_names);
		
		
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$acts[]=$f;
		}
		$sm1->assign('dim',$acts);
		
		
		
		
		//����������� �������������� - ������ ���� Is_confirmed==0
		//var_dump(in_array($editing_user['status_id'],$_editable_status_id));
		$sm1->assign('can_modify',    !$_kcg->HasAnyConfirm($editing_user['id'])&&in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		//�������� ������� ���-��
		$sm1->assign('can_create_position',$au->user_rights->CheckAccess('w',67)); 
		
		
		//������� �,�,
		$some_positions=$ui->GetPositionsArr($editing_user['id'],true);
		$sm1->assign('pos',$some_positions);
		
		
		//var_dump($ui->CheckDeltaPositions($editing_user['id']));
		
				//����������
		$rg=new KomplNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, 
			($_kcg->HasAnyConfirm($editing_user['id'])||$editing_user['is_active']==1) ,
			$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(338,384))), 
			$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(348,385))), 
			$result['id'],
			 
		 	$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(534,535)))
		));
		$sm1->assign('can_notes',true);
		
		$can_notes_edit=true;
		
		/*if($editing_user['sector_id']!=6) $can_notes_edit=$can_notes_edit&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(179,383)));
		if($editing_user['sector_id']==6) $can_notes_edit=$can_notes_edit&&$au->user_rights->CheckAccess('w',594);
		*/
		 $can_notes_edit=$can_notes_edit&&$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(179,383)))&&($editing_user['status_id']!=3)&&($editing_user['status_id']!=13);
		
		$sm1->assign('can_notes_edit',
		$can_notes_edit
		
		);
		
		
		
		//���� �����������
		
		$some_confirming=$ui->GetConfirmingArr($editing_user['id'],$result['id'],84,$editing_user,$editing_user_sector['s_s'],$editing_user_storage['s_s']);
		
		$sm1->assign('conf',$some_confirming);
		//var_dump($some_confirming);
		
		//����� �� ������� ����������� �������� �� ������� ���� �����-���
		$sm1->assign('can_unconrim_inspite_of_binded_docs',$au->user_rights->CheckAccess('w',356));
		
		
		
		
		
		//����� �� ��������� ����������������?
		
		$sm1->assign('can_super_neq',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(484,486))));
		
		$sm1->assign('can_neq', 
			$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(473,475)))
		);
		
		
		//����� �� ��������� �����������������?
		$sm1->assign('can_super_an',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(537,541))));
		
		$sm1->assign('can_an', 
			$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(536,540)))
		);
		
		
		//��������, ���� �� �������� �����������
		
		$sm1->assign('has_primary_confirm',$_kcg->HasPrimaryRole($editing_user['id']));
		
		//��������, ���� �� ���. ���. ��-��
		$sm1->assign('has_confirm_by_nach_uch',$_kcg->HasConfirmByRole($editing_user['id'], 3));
		
		//����� �� ������ ������������?
		$sm1->assign('can_edit_position_names',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(347,408))));
		
		//����� �� ������� ����������?
		$sm1->assign('can_edit_quantities',$ui->CanEditQuantities($editing_user['id'],$cannot_edit_quantities_reason,$editing_user,$editing_user_sector['s_s'],$editing_user_storage['s_s'],$result));
		$sm1->assign('cannot_edit_quantities_reason',$cannot_edit_quantities_reason);
		
		
		$sm1->assign('has_any_confirm',$_kcg->HasAnyConfirm($editing_user['id']));
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(177,381))));
		
		//����� �� ��������� ������� �� ����������?
		$sm1->assign('can_over_positions',
		$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(446,447)))
		||
		($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(359,392)))
		&&$_kcg->HasConfirmByRole($editing_user['id'], 3)
		&&!$_kcg->HasConfirmByRole($editing_user['id'], 6)
		)
		);
		
		
		$sm1->assign('can_delete_positions', true); //$au->user_rights->CheckAccess('w',178)); 
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(282,400)))); 
		
		$sm1->assign('can_print_full', $au->user_rights->CheckAccess('w',97)&&$au->user_rights->CheckAccess('w',199)); 
		
		
		$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(291,399)))); 
		
		$sm1->assign('can_make_bill', $au->user_rights->CheckAccess('w',92));
		
		$sm1->assign('can_re', $au->user_rights->CheckAccess('w',862));
		$sm1->assign('can_sync', $au->user_rights->CheckAccess('w',876));
		
		
		//�������� ���������� �������
		$not_in_closed_period=$ui->CheckClosePdate($editing_user['id'], $closed_period_reason,$editing_user);
		$sm1->assign('not_in_closed_period', $not_in_closed_period);
		$sm1->assign('closed_period_reason', $closed_period_reason);
		
		$cannot_select_positions=true;
		
		$cannot_select_positions=$cannot_select_positions&&!($au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(301,406)))&&$ui->CanEditQuantities($editing_user['id'],$cannot_edit_quantities_reason,$editing_user,$editing_user_sector['s_s'],$editing_user_storage['s_s'],$result));
		
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(291,399)));
		
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',92);
		
		$cannot_select_positions=$cannot_select_positions&&!$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(347,408)));
		
		
		
		
		$sm1->assign('cannot_select_positions', $cannot_select_positions);
		
		$sm1->assign('can_edit_given_no',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(317,407)))); 
		$sm1->assign('printmode', $printmode);
		//echo $ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(83,401));
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(81,380)))); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(82,382)))); 
		
		//���� ������ �������
		$sm1->assign('pch_date', $pch_date);
		
		
		$sm1->assign('komplekt_check_code',$ui->CheckCode($editing_user['code'],$editing_user['sector_id'],$editing_user['id']));
		
		
		
		
		
		
		//������� ������� �� �������������� �� ������
		$_mg=new KomplMarkGroup;
		$markers=$_mg->GetItemsByIdArr($editing_user['id']);
		if(count($markers)>0){
			//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
			$markers[0]['expire_ptime']=date('d.m.Y H:i:s',($markers[0]['ptime']+$markers[0]['expiration']));
			
			$sm1->assign('marker', $markers[0]);	
			$sm1->assign('has_marker',true);
		}else $sm1->assign('has_marker',false);
		
		$sm1->assign('PPUP', PPUP);
		
		$user_form=$sm1->fetch('komplekt/komplekt_edit'.$print_add.'.html');
		
		
		//������� ��������� �����
		
		if($au->user_rights->CheckAccess('w',97)){
			$sm->assign('has_sv_sch',true);
			
			
			
			$bg=new BillGroup; 
			$bg->SetPageName('ed_komplekt.php');
			
			
		 
			
			//������ ���������� �������
			if(isset($_GET['from_bill'])) $from_bill=abs((int)$_GET['from_bill']);
			else $from_bill=0;
			
			if(isset($_GET['to_page_bill'])) $to_page_bill=abs((int)$_GET['to_page_bill']);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			$decorator->AddEntry(new SqlEntry('p.komplekt_ved_id',$editing_user['id'], SqlEntry::E)); //-- ��������� � ���������
			
			
			
			
			//���� �������� �������
			/*$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//���� ���-�������	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//���� ������
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_bill_status_id_','',$k);
				}else{
					//������ ��� - �������� ���!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
			$status_ids=array();
			$cou_stat=0;   
			if(isset($_GET['bill_statuses'])&&is_array($_GET['bill_statuses'])) $cou_stat=count($_GET['bill_statuses']);
			if($cou_stat>0){
			  //���� ���-�������	
			  $status_ids=$_GET['bill_statuses'];
			  
			}else{
			  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $cou_stat++;
			  
			  if($cou_stat>0){
				  //���� ������
				  foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_bill_status_id_','',$k);
			  }else{
				  //������ ��� - �������� ���!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }
		  }
		   
			 if(count($status_ids)>0){
				  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				  
				  if($of_zero){
					  //������ ��� - �������� ���!	
					  $decorator->AddEntry(new UriEntry('all_statuses',1));
				  }else{
				  
					  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
					   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_statuses[]',$v));
				  }
			  } 
			

			
			
			
			
			
			if(!isset($_GET['pdate_bill1'])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012');//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_bill1'];
			
			
			
			if(!isset($_GET['pdate_bill2'])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_bill2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_bill1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_bill2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_bill'])&&(strlen($_GET['code_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_bill',$_GET['code_bill']));
			}
			
			if(isset($_GET['name_bill'])&&(strlen($_GET['name_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('name_bill',$_GET['name_bill']));
			}
			
			if(isset($_GET['supplier_name_bill'])&&(strlen($_GET['supplier_name_bill'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_bill']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name_bill',$_GET['supplier_name_bill']));
	}
	
			
			
				if(isset($_GET['supplier_bill_no'])&&(strlen($_GET['supplier_bill_no'])>0)){
		$decorator->AddEntry(new SqlEntry('p.supplier_bill_no',SecStr($_GET['supplier_bill_no']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_bill_no',$_GET['supplier_bill_no']));
	}
	
			
			
			if(isset($_GET['storage_id_bill'])&&(strlen($_GET['storage_id_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.storage_id',abs((int)$_GET['storage_id_bill']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('storage_id_bill',$_GET['storage_id_bill']));
			}
			
			
			
			 
			
			
			if(isset($_GET['user_confirm_price_id_bill'])&&(strlen($_GET['user_confirm_price_id_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.user_confirm_price_id',abs((int)$_GET['user_confirm_price_id_bill']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_confirm_price_id_bill',$_GET['user_confirm_price_id_bill']));
			}
			
			
			//���������� ����� ��������� ��� �������������� �������� ��� UriEntry
			if(!isset($_GET['sortmode_bill'])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_bill']);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode_bill',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			
			$bg->SetAuthResult($result);
			
			$llg=$bg->ShowPos(
				'bills/bills_list_komplekt.html', //0
				$decorator, //1
				$from, //2
				$to_page, //3
				$au->user_rights->CheckAccess('w',92),  //4
				$au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), //5
				$au->user_rights->CheckAccess('w',94), //6
				'_bill', //7
				$au->user_rights->CheckAccess('w',95), //8
				$au->user_rights->CheckAccess('w',96), //9
				true, ///10
				false, //11
				$au->user_rights->CheckAccess('w',131), //12
				$limited_sector, //13
				$editing_user['id'],  //14
				$au->user_rights->CheckAccess('w',195), //15
				$au->user_rights->CheckAccess('w',196), //16
				$au->user_rights->CheckAccess('w',197),  //17
				$temp_alls, //18
				$au->user_rights->CheckAccess('w',283), //19
		$au->user_rights->CheckAccess('w',860), //20
		$au->user_rights->CheckAccess('w',835), //21
				$limited_supplier,
				false, //23
		$au->user_rights->CheckAccess('w',873) //24
				);
			
			
			
			$sm->assign('sv_sch',$llg);	
		
		}
		
		
		
		
		
		
		//������� ��������� �����
		
		if($au->user_rights->CheckAccess('w',606)){
			$sm->assign('has_sv_sch_in',true);
			
			
			
			$bg=new BillInGroup; 
			$bg->prefix='_in_bill';
			$bg->SetPageName('ed_komplekt.php');
			
			
		 
			
			//������ ���������� �������
			if(isset($_GET['from_bill'])) $from_bill=abs((int)$_GET['from_bill']);
			else $from_bill=0;
			
			if(isset($_GET['to_page_bill'])) $to_page_bill=abs((int)$_GET['to_page_bill']);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			//$decorator->AddEntry(new SqlEntry('p.komplekt_ved_id',$editing_user['id'], SqlEntry::E)); //-- ��������� � ���������
			$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from bill_position where komplekt_ved_id="'.$editing_user['id'].'"', SqlEntry::IN_SQL));
			
		 
			
			/*
			//���� �������� �������
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//���� ���-�������	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_in_bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//���� ������
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
				}else{
					//������ ��� - �������� ���!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
			$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['bill_in_bill_statuses'])&&is_array($_GET['bill_in_bill_statuses'])) $cou_stat=count($_GET['bill_in_bill_statuses']);
		if($cou_stat>0){
		  //���� ���-�������	
		  $status_ids=$_GET['bill_in_bill_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //���� ������
			  foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
		  }else{
			  //������ ��� - �������� ���!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //������ ��� - �������� ���!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_in_bill_statuses[]',$v));
			  }
		  } 
		

			
			
			if(!isset($_GET['pdate_bill1_in_bill'])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012');//DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_bill1_in_bill'];
			
			
			
			if(!isset($_GET['pdate_bill2_in_bill'])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_bill2_in_bill'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_bill1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_bill2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_bill_in_bill'])&&(strlen($_GET['code_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_bill_in_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_bill',$_GET['code_bill_in_bill']));
			}
			
			if(isset($_GET['name_bill_in_bill'])&&(strlen($_GET['name_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name_bill_in_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('name_bill',$_GET['name_bill_in_bill']));
			}
			
			if(isset($_GET['supplier_name_bill_in_bill'])&&(strlen($_GET['supplier_name_bill_in_bill'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_bill_in_bill']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name_bill',$_GET['supplier_name_bill_in_bill']));
	}
			
			 
			
			
			if(isset($_GET['user_confirm_price_id_bill_in_bill'])&&(strlen($_GET['user_confirm_price_id_bill_in_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.user_confirm_price_id',abs((int)$_GET['user_confirm_price_id_bill_in_bill']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_confirm_price_id_bill',$_GET['user_confirm_price_id_bill_in_bill']));
			}
			
			
			//���������� ����� ��������� ��� �������������� �������� ��� UriEntry
			if(!isset($_GET['sortmode_bill_in_bill'])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_bill_in_bill']);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode_bill',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			
			$bg->SetAuthResult($result);
			
			$llg=$bg->ShowPos(
				'bills_in/bills_list_komplekt.html',  //0
				$decorator,	//1
				$from,	//2
				$to_page, 	//3
				$au->user_rights->CheckAccess('w',607), //4
				$au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), //5
				$au->user_rights->CheckAccess('w',626),	//6
				'_in_bill', //7
				$au->user_rights->CheckAccess('w',620),	//8
				$au->user_rights->CheckAccess('w',96),	//9
				true,	//10
				false,	//11
				$au->user_rights->CheckAccess('w',627),	//12
				$limited_sector, //13
				$editing_user['id'],	//14
				$au->user_rights->CheckAccess('w',621),	//15
				$au->user_rights->CheckAccess('w',622), //16
				$au->user_rights->CheckAccess('w',623),	//17
				$temp_alls, //18
				$au->user_rights->CheckAccess('w',625), //19
				false,
				false,
				$limited_supplier,
				$au->user_rights->CheckAccess('w',865)
				
				);
			
			
			
			$sm->assign('sv_sch_in',$llg);	
		
		}
		
		
		
		
		
		
		
		
		
		//������� ������ ������� �� �������
		if($au->user_rights->CheckAccess('w',521)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			
			if(isset($_GET['user_subj_login'])&&(strlen($_GET['user_subj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('s.login',SecStr($_GET['user_subj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_subj_login',$_GET['user_subj_login']));
			}
			
			if(isset($_GET['description'])&&(strlen($_GET['description'])>0)){
				$decorator->AddEntry(new SqlEntry('l.description',SecStr($_GET['description']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('description',$_GET['description']));
			}
			
			if(isset($_GET['object_id'])&&(strlen($_GET['object_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.object_id',SecStr($_GET['object_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('object_id',$_GET['object_id']));
			}
			
			if(isset($_GET['user_obj_login'])&&(strlen($_GET['user_obj_login'])>0)){
				$decorator->AddEntry(new SqlEntry('o.login',SecStr($_GET['user_obj_login']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('user_obj_login',$_GET['user_obj_login']));
			}
			
			if(isset($_GET['user_group_id'])&&(strlen($_GET['user_group_id'])>0)){
				$decorator->AddEntry(new SqlEntry('l.user_group_id',SecStr($_GET['user_group_id']), SqlEntry::E));
				$decorator->AddEntry(new UriEntry('user_group_id',$_GET['user_group_id']));
			}
			
			if(isset($_GET['ip'])&&(strlen($_GET['ip'])>0)){
				$decorator->AddEntry(new SqlEntry('ip',SecStr($_GET['ip']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('ip',$_GET['ip']));
			}
			
			
			
			//���������� ����� ��������� ��� �������������� �������� ��� UriEntry
			if(!isset($_GET['sortmode'])){
				$sortmode=0;	
			}else{
				$sortmode=abs((int)$_GET['sortmode']);
			}
			
			
			switch($sortmode){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('s.login',SqlOrdEntry::ASC));
				break;
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::DESC));
				break;
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('l.description',SqlOrdEntry::ASC));
				break;	
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::DESC));
				break;
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('ob.name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::DESC));
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('o.login',SqlOrdEntry::ASC));
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::DESC));
				break;
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('gr.name',SqlOrdEntry::ASC));
				break;	
				case 12:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::DESC));
				break;
				case 13:
					$decorator->AddEntry(new SqlOrdEntry('ip',SqlOrdEntry::ASC));
				break;	
				default:
					$decorator->AddEntry(new SqlOrdEntry('pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
			
			
			
			if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
			else $from=0;
			
			if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
			else $to_page=ITEMS_PER_PAGE;
			$decorator->AddEntry(new UriEntry('to_page',$to_page));
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(/*'81','82','83','84','85',298,299,300*/80,
81,
177,
178,
82,
179,
180,
181,
182,
183,
296,
184,
185,
186,
187,
297,
291,
282,
83,
132,
298,
299,
300,
301,
84,
85,
338,
347,
446,
473,
536,
537,
540,
541
)));
			$decorator->AddEntry(new SqlEntry('affected_object_id',$id, SqlEntry::E));
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$id));
			$decorator->AddEntry(new UriEntry('do_show_log',1));
			if(!isset($_GET['do_show_log'])){
				$do_show_log=false;
			}else{
				$do_show_log=true;
			}
			$sm->assign('do_show_log',$do_show_log);
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_komplekt.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	
	$content=$sm->fetch('komplekt/ed_komplekt_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	
	
	
	$smarty->assign('content',$content);
	//$smarty->display('page.html');
	if($print==0) $smarty->display('page.html');
	else echo $content;
	
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);

?>