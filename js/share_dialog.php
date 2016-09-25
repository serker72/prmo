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
require_once('../classes/rl/rl_share_table.php');


require_once('../classes/messageitem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/discr_man.php');

//require_once('../classes/rl_sender/rl_sec_sender.php');


$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 


$ret='';
 
 
if(isset($_POST['action'])&&($_POST['action']=="save_data")){
	//сохранение данных	
	$is_checked=abs((int)$_POST['is_checked']);
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	
	$record_name=SecStr(iconv('utf-8','windows-1251',$_POST['record_name']));
	$record_description=SecStr(iconv('utf-8','windows-1251',$_POST['record_description']));
	
	
	$changed_data=($_POST['changed_data']);
	$all_data=($_POST['all_data']);
	 
	  
	
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
		 
		 
		$affected_data=$changed_data; 
	}else{
		//записи нет!
		 
			$rl_record_id=$_rlr->Add(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
			$log->PutEntry($result['id'],'функция Поделиться папкой: включил разграничение доступа к записи',NULL, 1,NULL,$value,$record_id);
			
			$affected_data=$all_data;
		 
	}
	
	
	$_rlm=new RLMan;
	$_rlo=new RLObjectItem;
 
		//проверять и заносить права!
		
		foreach($affected_data as $k=>$data){
			
			
			$_data=explode(';',$data);
			
			$user=$_data[0];
			
			$letter='w';
			$object=$_data[1];
			$action=$_data[2];
			
			$has=$_rlm->CheckAccess($rl_record_id,$user,$letter,$object);
			
			
			$rlo=$_rlo->GetItemById($object);
			$value1=$value.', '.SecStr($rlo['name']);
			
			if(($action==0)&&($has)){
				//отнять право
				echo 'away '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->RevokeAccess($rl_record_id, $user, $letter,$object);	
				
				$log->PutEntry($result['id'],'функция Поделиться папкой: удалил доступ к записи',$user, 1,NULL,$value1,$record_id);
				
				
			}
			elseif(($action==1)&&(!$has)){
				//дать право
				echo 'to '.$rl_record_id.' '.$user.' '.$letter.' '.$object;
				$_rlm->GrantAccess($rl_record_id, $user, $letter,  $object);
				
				 
				
				$log->PutEntry($result['id'],'функция Поделиться папкой: установил доступ к записи',$user, 1,NULL,$value1,$record_id);
			}
			
		}
			
		
	 
	
}
//retrive_table

elseif(isset($_POST['action'])&&($_POST['action']=="retrive_table")){
	 
	$record_id=abs((int)$_POST['record_id']);
	$tablename=SecStr($_POST['tablename']);
	$additional_id=abs((int)$_POST['additional_id']);
	$parent_record_id=abs((int)$_POST['parent_record_id']);
	
	//найдем набор действий для этого объекта
	$_rlg=new RLGrObjectItem;
	
	$rlg=$_rlg->GetItemByfields(array('tablename'=>$tablename,
										'additional_id'=>$additional_id));
	$value='Объект '.SecStr($rlg['name']);
							
	//найдем запись для ограничений
	$_rlr=new RLRecordItem;
	
	$rlr=$_rlr->GetItemByFields(array('record_id'=>$record_id, 'rl_group_id'=>$rlg['id']));
	
	
	//if($rlr!==false){
		
		$_tab=new  RlShareTable($rlg['id'], $rlr['id'], $result, $parent_record_id);
		$ret=$_tab->Draw('files_share/admin_users.html');
			
	//}else $ret='Для выбранного объекта не включено разграничение доступа. Пожалуйста, отметьте галочку "Включить разграничение доступа" и нажмите "Сохранить и остаться" для включения разграничения доступа и дальнейшего редактирования прав.';
}
 

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>