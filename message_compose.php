<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей


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
$smarty->assign("SITETITLE",'Создание сообщения');

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
//новое сообщение - не задано  поле parent_id
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
	//проверим пересылку
	
	if(!isset($_GET['resend'])){
		if(!isset($_POST['resend'])){
			//$parent_id=NULL;
			$resend=NULL;
		}else $resend=abs((int)$_POST['resend']);
	}else $resend=abs((int)$_GET['resend']);
		
}


//отправка сообщения
if(($parent_id===NULL)&&isset($_POST['send_s'])){
		
	//print_r($_POST['who_s']);	array();
	
	//проверим доступ к полному списку адресатов
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
	
	  	$log->PutEntry($result['id'], "отправил сообщение",$params['to_id'],NULL,NULL,"тема сообщения: ".$params['topic']." текст сообщения: ".$params['txt']);
		
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

//отправка ответа
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
	
	//разбираем массив addresses
	foreach($addresses as $k=>$v){
		if($v==	$older_message['from_id']) continue;
		if($v==	$result['id']) continue;	
		
		//если $v есть среди users_to_send - то внести в результирующий массив пару из users_to_send
		//иначе - внести пару с $v и  с message_id=0
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
		
			$log->PutEntry($result['id'], "отправил сообщение",$params['to_id'],NULL,NULL,"тема сообщения: ".$params['topic']." текст сообщения: ".$params['txt']);
		
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
	
		$log->PutEntry($result['id'], "отправил сообщение",$params['to_id'],NULL,NULL,"тема сообщения: ".$params['topic']." текст сообщения: ".$params['txt']);
	
		foreach($_POST as $k=>$v){
		  if(eregi("^upload_file_",$k)){
			  //echo eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k)).' = '.$v;
			  
			  $filename=eregi_replace("^upload_file_", '', eregi_replace("_tmp$",".tmp",$k));
			  $fmi->Add(array('message_id'=>$code, 'filename'=>SecStr(basename($filename)), 'orig_filename'=>SecStr($v)));
		  }
		}
	
	
	
	
	
	
	
	
	
	if(isset($_POST['send_all'])){
		//отправим копию сообщения остальным участникам переписки
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
		
			$log->PutEntry($result['id'], "отправил сообщение",$params['to_id'],NULL,NULL,"тема сообщения: ".$params['topic']." текст сообщения: ".$params['txt']);
		
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


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);


$_menu_id=38;
	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	$mg=new MessageGroup;
	
	if($parent_id===NULL){
		//новое сообщение
		$sm1=new SmartyAdm;
		
		//если это ПЕРЕСЫЛКА
		if($resend!==NULL){
			$_pmi=new MessageItem;
			$pmi=$_pmi->GetItemById($resend);
			if(($pmi!==false)&&(($pmi['to_id']==$result['id'])||($pmi['from_id']==$result['id']))){
				//это наше письмо, можем обрабатывать данные
				
				
				$sm1->assign('resend_topic','Fwd: '.htmlspecialchars(stripslashes($pmi['topic'])));
				//вся переписка
				/*$_pmm=new MessageGroup;
				$sm1->assign('resend_text', $_pmm->ShowAllMessagesArr($resend,$result['id']));
				*/
				
				//текст сообщения
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
				
				
				if($pmi['from_id']==$result['id']) $pmi['s_login'].=' (Вы)';
				
				
				if($pmi['to_id']==$result['id']) $pmi['r_login'].=' (Вы)';
				
				
				//получатели
				$receivers=$mi->FindRecepients($resend, $pmi);
				
				
				$pmi['txt']=(stripslashes($pmi['txt']));
				
				$pmi['pdate']=date("d.m.Y H:i:s", $pmi['pdate']);
				
				$resend_text='<div>&nbsp;</div><div>&nbsp;</div><div>&nbsp;</div>';
				$resend_text.='<div>____________________________пересылаемое сообщение___________________________</div>';
				
				$resend_text.='<div>Дата: '.$pmi['pdate'].'</div>';
				$resend_text.='<div>От: ';
				if(($pmi['s_group_id']==1)||($pmi['s_group_id']==2)){
					$resend_text.=$pmi['s_name_s'].' '.$pmi['s_login'];
				}else{
					$resend_text.=$pmi['s_name_d'].' '.$pmi['s_login'];
				}
				$resend_text.='</div>';
				
				$resend_text.='<div>Кому: ';
				/*if(($pmi['r_group_id']==1)||($pmi['r_group_id']==2)){
					$resend_text.=$pmi['r_name_s'].' '.$pmi['r_login'];
				}else{
					$resend_text.=$pmi['r_name_d'].' '.$pmi['r_login'];
				}
				*/
				foreach($receivers as $k=>$rec){
					if($rec['id']==$result['id']) $rec['login'].=' (Вы)';
				
				
					
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
				
				$resend_text.='<div>____________________________конец пересылаемого сообщения_____________________</div>';
				$sm1->assign('resend_text',$resend_text);
				
				
				$resend_files='';
				
				//обеспечим вложения
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
		//ответ на сообщение
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
		
		//получатели письма  - для поля Кому отвечаемого сообщения
		$sm1->assign('to_users', $mi->FindRecepients($parent_id, $older_message));
		
		
		//получатели ответа: автор отвечаемого письма + соадресаты для поля Кому этого сообщения
		$users_to_send=$mi->FindRecepientsWithMesIds($parent_id, $older_message);
		//добавим также автора отвечаемого сообщения
		$users_to_send[]=array('user_id'=>$older_message['from_id'], 'message_id'=>$older_message['id']);
		//построим массив с ФИО
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

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>