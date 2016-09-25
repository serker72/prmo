<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_s_item.php');


 
require_once('../classes/discr_man.php');
require_once('../classes/actionlog.php');
require_once('../classes/filefolderitem.php');
require_once('../classes/rl/rl_overall_folders.php');

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
if(isset($_POST['action'])&&($_POST['action']=="load_rights")){
	$user_id=abs((int)$_POST['user_id']);
	$folder_id=abs((int)$_POST['folder_id']);
	$group_id=abs((int)$_POST['group_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	
	$_dr=new RLOverallFolders;
	
	
	$ret=$_dr->BuildRow( $storage_id, $group_id, $user_id, $folder_id, 'rl/rl_overall_row.html');
	 //'<td>'.$user_id.' '.$folder_id.'</td>';

}
elseif(isset($_POST['action'])&&($_POST['action']=="draw_rights")){
	 
	$user_id=abs((int)$_POST['user_id']);
	
	$object_id=abs((int)$_POST['object_id']);
	
	$sm=new SmartyAj;
	
	$dt=new DiscrTable;
	
	$rights=$dt->GetTableCellArr($user_id, $object_id);
	
	$sm->assign('user_id',$user_id);
	$sm->assign('rights',$rights);
	
	
	$ret.=$sm->fetch("admin/admin_users_cell.html");
}elseif(isset($_POST['action'])&&($_POST['action']=="draw_row_rights")){
	 
	$user_id=abs((int)$_POST['user_id']);
	$gr_id=abs((int)$_POST['gr_id']);
	if($gr_id===0) $gr_id=NULL;
	
	$_ui=new UserSItem;
	$user=$_ui->Getitembyid($user_id);
	//$object_id=abs((int)$_POST['object_id']);
	
	//echo $gr_id;
	$sm=new SmartyAj;
	
	$dt=new DiscrTable($gr_id);
	
	$rights=$dt->GetTableRowArr($user_id);
	
	$sm->assign('user',$user);
	$sm->assign('rights_arr',$rights);
	
	
	$ret.=$sm->fetch("admin/admin_users_row.html");
}


elseif(isset($_POST['action'])&&($_POST['action']=="save_changes")){
	//сохранение данных	
	
	//$user_id=abs((int)$_POST['user_id']);
	
	$group_id=abs((int)$_POST['group_id']);
	$storage_id=abs((int)$_POST['storage_id']);
	
	$set_folders=($_POST['set_folders']); 
	$del_folders=($_POST['del_folders']); 
	$set_rights=($_POST['set_rights']); 
	$del_rights=($_POST['del_rights']); 
	
	//найдем набор действий для этого объекта
	$_rlg=new RLGrObjectItem;
	$rlg=$_rlg->GetItemByfields(array('tablename'=>'file',
										'additional_id'=>$storage_id));
	//
	$_folder=new FileFolderItem;
	
	$_rlm=new RLMan;
	$_rlo=new RLObjectItem;
	
	//включим доступы к папкам...
	foreach($set_folders as $k=>$v){
		//найдем запись для ограничений
		$_rlr=new RLRecordItem;
	
		$rlr=$_rlr->GetItemByFields(array('record_id'=>$v, 'rl_group_id'=>$rlg['id']));
		
		
		if($rlr===false){
			
			$rl_record_id=$_rlr->Add(array('record_id'=>$v, 'rl_group_id'=>$rlg['id']));
			
			
			$folder=$_folder->GetItemById($v);
			$value='Объект '.SecStr($rlg['name']).', запись '.SecStr($folder['filename']).', описание записи: '.SecStr($folder['txt']);
			
			$log->PutEntry($result['id'],'включил разграничение доступа к записи', NULL, 1,NULL,$value,$v);
		}
	}
	
	//удалми доступы к папкам...
	foreach($del_folders as $k=>$v){
		//найдем запись для ограничений
		$_rlr=new RLRecordItem;
	
		$rlr=$_rlr->GetItemByFields(array('record_id'=>$v, 'rl_group_id'=>$rlg['id']));
		
		
		if($rlr!==false){
			
			//$rl_record_id=$_rlr->Add(array('record_id'=>$v, 'rl_group_id'=>$rlg['id']));
			$_rlr->Del($rlr['id']);
			
			
			$folder=$_folder->GetItemById($v);
			$value='Объект '.SecStr($rlg['name']).', запись '.SecStr($folder['filename']).', описание записи: '.SecStr($folder['txt']);
			
			$log->PutEntry($result['id'],'выключил разграничение доступа к записи', NULL, 1,NULL,$value,$v);
		}
	}
	
	//раздадим права
	foreach($set_rights as $k=>$v){
		$valarr=explode(';',$v);
		
		
		$_folder_id=abs((int)$valarr[0]);
		$_user_id=abs((int)$valarr[1]);
		$_object_id=abs((int)$valarr[2]);
		
			//найдем запись для ограничений
		
		$_rlr=new RLRecordItem;
	
		$rlr=$_rlr->GetItemByFields(array('record_id'=>$_folder_id, 'rl_group_id'=>$rlg['id']));
		
	
		$rl_record_id=$rlr['id'];
		
		
		
		if($rlr!==false){
			
			
			
			$has=$_rlm->CheckAccess($rl_record_id,$_user_id,'w',$_object_id);
			
			if(!$has){
				$rlo=$_rlo->GetItemById($_object_id);
				
			
				$folder=$_folder->GetItemById($_folder_id);
				
				$value='Объект '.SecStr($rlg['name']).', запись '.SecStr($folder['filename']).', описание записи: '.SecStr($folder['txt']);
				$value1=$value.', '.SecStr($rlo['name']);
				
				$_rlm->GrantAccess($rl_record_id, $_user_id, 'w',  $_object_id);
					
					 
					
				$log->PutEntry($result['id'],'установил доступ к записи',$_user_id, 1,NULL,$value1,$_folder_id);
			}
		}
	}
	
	//удалим права
	foreach($del_rights as $k=>$v){
		$valarr=explode(';',$v);
		
		
		$_folder_id=abs((int)$valarr[0]);
		$_user_id=abs((int)$valarr[1]);
		$_object_id=abs((int)$valarr[2]);
		
			//найдем запись для ограничений
		
		$_rlr=new RLRecordItem;
	
		$rlr=$_rlr->GetItemByFields(array('record_id'=>$_folder_id, 'rl_group_id'=>$rlg['id']));
		
	
		$rl_record_id=$rlr['id'];
		
		
		
		if($rlr!==false){
			
			
			
			$has=$_rlm->CheckAccess($rl_record_id,$_user_id,'w',$_object_id);
			
			if($has){
				$rlo=$_rlo->GetItemById($_object_id);
				
			
				$folder=$_folder->GetItemById($_folder_id);
				
				$value='Объект '.SecStr($rlg['name']).', запись '.SecStr($folder['filename']).', описание записи: '.SecStr($folder['txt']);
				$value1=$value.', '.SecStr($rlo['name']);
				
				$_rlm->RevokeAccess($rl_record_id, $_user_id, 'w',  $_object_id);
					
					 
					
				$log->PutEntry($result['id'],'удалил доступ к записи',$_user_id, 1,NULL,$value1,$_folder_id);
			}
		}
	}
	
	/*$is_checked=abs((int)$_POST['is_checked']);
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	
	$record_name=SecStr(iconv('utf-8','windows-1251',$_POST['record_name']));
	$record_description=SecStr(iconv('utf-8','windows-1251',$_POST['record_description']));
	
	
	$users=($_POST['users']);
	$objects=($_POST['objects']);
	$letters=($_POST['letters']);
	$actions=($_POST['actions']);
	
	
	//найдем набор действий для этого объекта
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>$tablename,
										'additional_id'=>$additional_id));
	$value='Объект '.SecStr($rlg['name']).', запись '.SecStr($record_name).', описание записи: '.SecStr($record_description);
	
	
	
							
	//найдем запись для ограничений
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
	
	
	
	 
	
//	echo $sql;
	
	if($rlr!==false){
		//запись есть!
		$rl_record_id=$rlr['id'];
		if($is_checked==0){
			$_rlr->Del($rl_record_id);
			
			
			if(($rlr['rl_group_id']==5)||($rlr['rl_group_id']==6)){
				$_dman=new DiscrMan;
				$_sender->LoadAndSend($eqs, $_dman->GetUsersByRight('w', 696));
			}
			
			$log->PutEntry($result['id'],'выключил разграничение доступа к записи', NULL, 1,NULL,$value,$record_id);
		}
	}else{
		//записи нет!
		if($is_checked==1) {
			$rl_record_id=$_rlr->Add(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
			$log->PutEntry($result['id'],'включил разграничение доступа  к записи',NULL, 1,NULL,$value,$record_id);
		}
	}
	
	
	$_rlm=new RLMan;
	$_rlo=new RLObjectItem;
	if($is_checked==1) {
		//проверять и заносить права!
		
		foreach($users as $k=>$user){
			
			$letter=$letters[$k];
			$object=$objects[$k];
			$action=$actions[$k];
			
			$has=$_rlm->CheckAccess($rl_record_id,$user,$letter,$object);
			
			
			$rlo=$_rlo->GetItemById($object);

			$value1=$value.', '.SecStr($rlo['name']);
			
			if(($action==0)&&($has)){
				//отнять право
				echo 'away '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->RevokeAccess($rl_record_id, $user, $letter,$object);	
				
				$log->PutEntry($result['id'],'удалил доступ к записи',$user, 1,NULL,$value1,$record_id);
				
				
			}
			elseif(($action==1)&&(!$has)){
				//дать право
				echo 'to '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->GrantAccess($rl_record_id, $user, $letter,  $object);
				
				if(($rlr['rl_group_id']==5)||($rlr['rl_group_id']==6)){
					//print_r($eqs); print_r(array($user));
					 
					$_sender->LoadAndSend($eqs, array($user));
				}
				
				
				$log->PutEntry($result['id'],'установил доступ к записи',$user, 1,NULL,$value1,$record_id);
			}
			
		}
			
		
	}*/
	
}




//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>