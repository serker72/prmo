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
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');

require_once('classes/messagegroup.php');
require_once('classes/messageitem.php');

require_once('classes/filemessagegroup.php');
require_once('classes/filemessageitem.php');
require_once('classes/useritem.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'�������� ���������');

$au=new AuthUser();
$result=$au->Auth();

if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$mi=new MessageItem;

$log=new ActionLog();
//$action=0;
$parent_id=NULL;
//����� ��������� - �� ������  ���� parent_id
if(!isset($_GET['parent_id'])){
	if(!isset($_POST['parent_id'])){
		$parent_id=NULL;
	}else $parent_id=abs((int)$_POST['parent_id']);
}else $parent_id=abs((int)$_GET['parent_id']);

if($parent_id!==NULL){
	$parent_message=$mi->GetItemByFields(array('id'=>$parent_id,'to_id'=>$result['id']));
	if($parent_message===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();
	}
}else{
	//�������� ���������
	
	if(!isset($_GET['resend'])){
		if(!isset($_POST['resend'])){
			//$parent_id=NULL;
			$resend=NULL;
		}else $resend=abs((int)$_POST['resend']);
	}else $resend=abs((int)$_GET['resend']);
		
}


//�������� ���������
if(($parent_id===NULL)&&isset($_POST['send_s'])){
		
	//print_r($_POST['who_s']);	array();
	
	//�������� ������ � ������� ������ ���������
	$is_admin=true; //$au->user_rights->CheckAccess('x',5);
	
	
	 
	
	$addresses=explode(';',$_POST['who']);
	/*
	
	if(is_array($_POST['who'])){
	
	 $addresses=$_POST['who'];
	  if((count($addresses)>1)&&(!$is_admin)){
		  header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();
	  }
	}else{
		$addresses=array();
		$addresses[]=$_POST['who'];
	}*/
	
	
	
	$topic=SecStr($_POST['topic_s']);
	$txt=SecStr($_POST['txt_s']);
	//$parent_id=abs((int)$_POST['parent_id']);
	
	
	$mi=new MessageItem;
	$fmi=new FileMessageItem;
	foreach($addresses as $k=>$v){
	  $params=array();
	
	  
	  $params['topic']=$topic;
		 $params['txt']=$txt;
		 $params['to_id']= abs((int)$v);
		 $params['from_id']=$result['id'];
		 $params['pdate']=time();
		 $message_id=0; //abs((int)$_POST['message_id']);
	  
	  
	//  $code=$mi->Add($params);
		$code=$mi->Send($message_id, $result['id'], $params);
	
	  	$log->PutEntry($result['id'], "�������� ���������",$params['to_id'],NULL,NULL,"���� ���������: ".$params['topic']." ����� ���������: ".$params['txt']);
		
	  foreach($_POST as $k=>$v){
	  	if(eregi("^upload_file_",$k)){
			//echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
			
			$filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
			$fmi->Add(array('message_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_filename'=>SecStr($v)));
		}
	  }
	}
	
	header("Location: messages.php?show_message=1");
		
	die();
}

//�������� ������
if(($parent_id!==NULL)&&(isset($_POST['send_s'])||isset($_POST['send_all']))){
	
	
	$ui=new UserItem;
	$older_message=$mi->GetItemById($parent_id);
	
	
	$topic=SecStr($_POST['topic_s']);
	$txt=SecStr($_POST['txt_s']);
	//$parent_id=abs((int)$_POST['parent_id']);
	
	$now=time();
	
	
	
	$addresses=explode(';',$_POST['who']);
	
	
	
	
	
	$mi=new MessageItem;
	$fmi=new FileMessageItem;
	
	
	
	$users_to_send=$mi->FindRecepientsWithMesIds($parent_id, $older_message);
	
	$actual_users_to_send=array();
	if(in_array($older_message['from_id'], $addresses)) $actual_users_to_send[]=array('user_id'=>$older_message['from_id'], 'message_id'=>$parent_id);
	
	//��������� ������ addresses
	foreach($addresses as $k=>$v){
		if($v==	$older_message['from_id']) continue;
		if($v==	$result['id']) continue;	
		
		//���� $v ���� ����� users_to_send - �� ������ � �������������� ������ ���� �� users_to_send
		//����� - ������ ���� � $v �  � message_id=0
		$our_pair=array('user_id'=>$v, 'message_id'=>0);
		foreach($users_to_send as $key=>$value){
			if($value['user_id']==$v) {
				$our_pair['message_id']=$value['message_id'];	
				break;
			}
		}
		
		$actual_users_to_send[]=$our_pair;
		
	}
		
	/*print_r($actual_users_to_send);
	
	die();*/
	
	foreach($actual_users_to_send as $index=>$pair){
			if($pair['user_id']==$result['id']) continue;
			
			$params=array();
			$params['pdate']=$now;
			$params['parent_id']=$pair['message_id'];
			
			$params['topic']=$topic;
			$params['txt']=$txt;
			$params['to_id']= $pair['user_id'];
			$params['from_id']=$result['id'];
		 
			$message_id=0; //abs((int)$_POST['message_id']);
	  
	  
	 
			$code=$mi->Send($message_id, $result['id'], $params);
		
			$log->PutEntry($result['id'], "�������� ���������",$params['to_id'],NULL,NULL,"���� ���������: ".$params['topic']." ����� ���������: ".$params['txt']);
		
			foreach($_POST as $k=>$v){
			  if(eregi("^upload_file_",$k)){
				  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
				  
				  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
				  $fmi->Add(array('message_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_filename'=>SecStr($v)));
			  }
			}
			
		}
	
	/*
	$params=array();
	
	$params['pdate']=$now;
	$params['parent_id']=$parent_id;
	
	
	
	
	
	 
	  $params['topic']=$topic;
		 $params['txt']=$txt;
		 $params['to_id']= $older_message['from_id'];
		 $params['from_id']=$result['id'];
		 
	
	
	//$code=$mi->Add($params);
	
	$message_id=0; //abs((int)$_POST['message_id']);
	  
	  
 
		$code=$mi->Send($message_id, $result['id'], $params);
	
		$log->PutEntry($result['id'], "�������� ���������",$params['to_id'],NULL,NULL,"���� ���������: ".$params['topic']." ����� ���������: ".$params['txt']);
	
		foreach($_POST as $k=>$v){
		  if(eregi("^upload_file_",$k)){
			  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
			  
			  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
			  $fmi->Add(array('message_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_filename'=>SecStr($v)));
		  }
		}
	
	
	
	
	
	
	
	
	
	if(isset($_POST['send_all'])){
		//�������� ����� ��������� ��������� ���������� ���������
		$users_to_send=$mi->FindRecepientsWithMesIds($parent_id, $older_message);
		
		foreach($users_to_send as $index=>$pair){
			if($pair['user_id']==$result['id']) continue;
			
			$params=array();
			$params['pdate']=$now;
			$params['parent_id']=$pair['message_id'];
			
			$params['topic']=$topic;
			$params['txt']=$txt;
			$params['to_id']= $pair['user_id'];
			$params['from_id']=$result['id'];
		 
			$message_id=0; //abs((int)$_POST['message_id']);
	  
	  
	 
			$code=$mi->Send($message_id, $result['id'], $params);
		
			$log->PutEntry($result['id'], "�������� ���������",$params['to_id'],NULL,NULL,"���� ���������: ".$params['topic']." ����� ���������: ".$params['txt']);
		
			foreach($_POST as $k=>$v){
			  if(eregi("^upload_file_",$k)){
				  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
				  
				  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
				  $fmi->Add(array('message_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_filename'=>SecStr($v)));
			  }
			}
			
		}
		
	}
	*/
	
	header("Location: messages.php?show_message=1");
	die();
}


//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=38;
	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	
	$mg=new MessageGroup;
	
	if($parent_id===NULL){
		//����� ���������
		$sm1=new SmartyAdm;
		
		//���� ��� ���������
		if($resend!==NULL){
			$_pmi=new MessageItem;
			$pmi=$_pmi->GetItemById($resend);
			if(($pmi!==false)&&(($pmi['to_id']==$result['id'])||($pmi['from_id']==$result['id']))){
				//��� ���� ������, ����� ������������ ������
				
				
				$sm1->assign('resend_topic','Fwd: '.htmlspecialchars(stripslashes($pmi['topic'])));
				//��� ���������
				/*$_pmm=new MessageGroup;
				$sm1->assign('resend_text', $_pmm->ShowAllMessagesArr($resend,$result['id']));
				*/
				
				//����� ���������
				$ui=new UserItem;
				$user=$ui->GetItemByid($pmi['from_id']);	
				
				$user_r=$ui->GetItemByid($pmi['to_id']);	
				
				$pmi['s_login']= stripslashes($user['login']);
				$pmi['r_login']= stripslashes($user_r['login']);
				
				$pmi['s_name_s']= stripslashes($user['name_s']);
				$pmi['s_name_d']= stripslashes($user['name_d']);
				
				$pmi['r_name_s']= stripslashes($user_r['name_s']);
				$pmi['r_name_d']= stripslashes($user_r['name_d']);
				
				$pmi['s_group_id']=$user['group_id'];
				$pmi['r_group_id']=$user_r['group_id'];
				
				
				if($pmi['from_id']==$result['id']) $pmi['s_login'].=' (��)';
				
				
				if($pmi['to_id']==$result['id']) $pmi['r_login'].=' (��)';
				
				
				//����������
				$receivers=$mi->FindRecepients($resend, $pmi);
				
				
				$pmi['txt']=(stripslashes($pmi['txt']));
				
				$pmi['pdate']=date("d.m.Y H:i:s", $pmi['pdate']);
				
				$resend_text='<div>&nbsp;</div><div>&nbsp;</div><div>&nbsp;</div>';
				$resend_text.='<div>____________________________������������ ���������___________________________</div>';
				
				$resend_text.='<div>����: '.$pmi['pdate'].'</div>';
				$resend_text.='<div>��: ';
				if(($pmi['s_group_id']==1)||($pmi['s_group_id']==2)){
					$resend_text.=$pmi['s_name_s'].' '.$pmi['s_login'];
				}else{
					$resend_text.=$pmi['s_name_d'].' '.$pmi['s_login'];
				}
				$resend_text.='</div>';
				
				$resend_text.='<div>����: ';
				/*if(($pmi['r_group_id']==1)||($pmi['r_group_id']==2)){
					$resend_text.=$pmi['r_name_s'].' '.$pmi['r_login'];
				}else{
					$resend_text.=$pmi['r_name_d'].' '.$pmi['r_login'];
				}
				*/
				foreach($receivers as $k=>$rec){
					if($rec['id']==$result['id']) $rec['login'].=' (��)';
				
				
					
					//if(($rec['group_id']==1)||($rec['group_id']==2)){
						$resend_text.=$rec['name_s'].' '.$rec['login'];
					/*}else{
						$resend_text.=$rec['name_d'].' '.$rec['login'];
					}*/
					
					if($k<(count($receivers)-1)) $resend_text.=', ';
				}
				
				$resend_text.='</div>';
				
				$resend_text.='<div></div>';
				
				
				$resend_text.=$pmi['txt'];
				$resend_text.='<div></div>';
				
				$resend_text.='<div>____________________________����� ������������� ���������_____________________</div>';
				$sm1->assign('resend_text',$resend_text);
				
				
				$resend_files='';
				
				//��������� ��������
				$_pfi=new FileMessageGroup;
				
				$pfi=$_pfi->GetItemsByIdArr($resend);
				foreach($pfi as $k=>$v){
					//print_r($v);
					
					$tempname=tempnam(MESSAGE_FILES_PATH, '');
					//echo MESSAGE_FILES_PATH;
					copy(MESSAGE_FILES_PATH.$v['filename'], $tempname);
					
					//
					$sm2=new SmartyAdm;
					$sm2->assign('factname',basename($tempname));
					$sm2->assign('realname',$v['orig_filename']);
					
					$resend_files.=$sm2->fetch('messages/uploaded_file.html');
						
				}
				$sm1->assign('resend_files',$resend_files);
				
			}
				
		}
		
		
		if(isset($_COOKIE['sort_mode'])){
			$sort_mode=$_COOKIE['sort_mode'];
		}else $sort_mode=2;
		
		$addresses=$mg->DrawAdrArr($result['id'],true,$sort_mode);
		/*$sm1->assign('has_admin',$au->user_rights->CheckAccess('w',5));
		$sm1->assign('adr',$addresses);
		$sm1->assign('session_id',session_id());
		
		
		$addresses=$mg->DrawAdrArr($result['id'], true);*/
		$sm1->assign('sort_mode',$sort_mode);
		$sm1->assign('has_admin',true);
		$sm1->assign('adr',$addresses);
		$sm1->assign('session_id',session_id());
		
		$content=$sm1->fetch('messages1/compose_new.html');
	}else{
		//����� �� ���������
		$ui=new UserItem;
		$older_message=$mi->GetItemById($parent_id);
		$older_message['txt']=($older_message['txt']);
		$older_user=$ui->GetItemById($older_message['from_id']);
		
		$fmg=new FileMessageGroup;
		$files=$fmg->GetItemsByIdArr($parent_id);
		
		$sm1=new SmartyAdm;	
		
		$sm1->assign('session_id',session_id());
		$sm1->assign('parent_id',$parent_id);
		
		$sm1->assign('older_message',$older_message);
		$sm1->assign('older_user',$older_user);
		
		$sm1->assign('files',$files);
		
		//���������� ������  - ��� ���� ���� ����������� ���������
		$sm1->assign('to_users', $mi->FindRecepients($parent_id, $older_message));
		
		
		//���������� ������: ����� ����������� ������ + ���������� ��� ���� ���� ����� ���������
		$users_to_send=$mi->FindRecepientsWithMesIds($parent_id, $older_message);
		//������� ����� ������ ����������� ���������
		$users_to_send[]=array('user_id'=>$older_message['from_id'], 'message_id'=>$older_message['id']);
		//�������� ������ � ���
		$adresat=array(); $_user=new UserItem;
		foreach($users_to_send as $index=>$pair){
			if($pair['user_id']==$result['id']) continue;
			
			$user=$_user->GetItemById($pair['user_id']);
			$adresat[]=$user;	
		}
		$sm1->assign('adresat',$adresat);
		
		//print_r($adresat);
		
		
		$content=$sm1->fetch('messages1/compose_reply.html');
	}
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);


$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>