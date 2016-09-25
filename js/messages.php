<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/messagegroup.php');
require_once('../classes/messageitem.php');

require_once('../classes/user_s_group.php');




$au=new AuthUser();
$result=$au->Auth();

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';

$ui=new UserItem;
$log=new ActionLog();


if(isset($_POST['action'])&&($_POST['action']=="show_user_data")){
	
	$user=$ui->GetItemById((int)$_POST['id']);
	if($user!==false){
	  $sm=new SmartyAj;
	  $sm->assign('user',$user);
	  $ret=$sm->fetch('messages/user.html'); 
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="send_message")){
	//проверим доступ к полному списку адресатов
	$is_admin=$au->user_rights->CheckAccess('x',5);
	
	if(is_array($_POST['to'])){
	
	 $addresses=$_POST['to'];
	  if((count($addresses)>1)&&(!$is_admin)){
		  header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();
	  }
	}else{
		$addresses=array();
		$addresses[]=$_POST['to'];
	}
	
	$topic=SecStr(iconv("utf-8","windows-1251",$_POST['topic']),9);
	$txt=SecStr(iconv("utf-8","windows-1251",$_POST['txt']),9);
	$parent_id=abs((int)$_POST['parent_id']);
	
	$mi=new MessageItem;
	
	foreach($addresses as $k=>$v){
	  $params=array();
	  $params['pdate']=time();
	  $params['parent_id']=$parent_id;
	  $params['topic']=$topic;
	  $params['txt']=$txt;
	  $params['from_id']=$result['id'];
	  $params['to_id']=abs((int)$v);
	  //$params['is_new']=1;
	  //$mi->Add($params);
	  $message_id=0;
	  $code=$mi->Send($message_id, $result['id'], $params);
	  
	    $log->PutEntry($result['id'], "отправил сообщение",$params['to_id'],NULL,NULL,"тема сообщения: ".$params['topic']." текст сообщения: ".$params['txt']);
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="show_chain")){
	//покажем цепочку сообщений
	$mg=new MessageGroup;
	

	$ret=$mg->GetChain(abs((int)$_POST['parent_id']),'messages1/chain_handler.html','messages1/chain_item.html',$result['id'],abs((int)$_POST['folder_id']));

}elseif(isset($_POST['action'])&&($_POST['action']=="answer_message")){
	//ответ на сообщение
	$parent_id=abs((int)$_POST['parent_id']);
	$txt=SecStr(iconv("utf-8","windows-1251",$_POST['txt']),9);
	$receiver_id=abs((int)$_POST['to']);
	
	$mi=new MessageItem;

	$params=array();
	$params['pdate']=time();
	$params['parent_id']=$parent_id;
	$params['txt']=$txt;
	$params['from_id']=$result['id'];
	$params['to_id']=$receiver_id;
	//$params['is_new']=1;
	//$mi->Add($params);
	
	$message_id=0;
	  $code=$mi->Send($message_id, $result['id'], $params);
		$log->PutEntry($result['id'], "отправил сообщение",$params['to_id'],NULL,NULL,"тема сообщения: ".$params['topic']." текст сообщения: ".$params['txt']);

}elseif(isset($_POST['action'])&&($_POST['action']=="mark_as_read")){
	//ответ на сообщение
	$id=abs((int)$_POST['id']);
	
	$mi=new MessageItem;

	
	//$mi->Edit($id,array('is_new'=>0));
	$mi->SetRead($id);	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="show_chain_messages")){
	$id=abs((int)$_POST['id']);
	$mg=new MessageGroup;
	$ret=$mg->ShowAllMessages($id,'messages1/chain_item.html', $result['id']);
}elseif(isset($_POST['action'])&&($_POST['action']=="show_chain_messages")){
	$id=abs((int)$_POST['id']);
	$mg=new MessageGroup;
	$ret=$mg->ShowAllMessages($id,'messages1/chain_item.html', $result['id']);
}elseif(isset($_POST['action'])&&($_POST['action']=="show_addresses")){
	
	
	$sort_mode=abs((int)$_POST['sort_mode']);
	$sm=new SmartyAj;
	
	$mg=new MessageGroup;
	$addresses=$mg->DrawAdrArr($result['id'],true,$sort_mode);
	$sm->assign('adr',$addresses);
	
	$sm->assign('sort_mode',$sort_mode);
	
	$ret=$sm->fetch('messages1/addresses.html');
	
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Message_ViewGroup;
	$_view=new Message_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new Message_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}



elseif($_GET['term']) 	{
		$limited_user=NULL; $flt=''; $ret_arrs=array();
	 
	/*$_plans=new Sched_Group;
	$viewed_ids=$_plans->GetAvailableUserIds($result['id']);
	$flt=' and p.id in('.implode(', ', $viewed_ids).') ';*/
	
	$flt='';
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		
		$flt=' and p.id in('.implode(', ', $limited_user).') ';
	}
	
	
	
	
	$sql='select p.* from 
	user as p

	where p.is_active=1 and (p.name_s like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%" or p.login like "%'.iconv("utf-8","windows-1251",SecStr($_GET['term'])).'%") and p.id<>"'.$result['id'].'" '.$flt.' order by p.name_s asc ';
	//echo $sql;
	
	$set=new mysqlset($sql);
	$rs=$set->GetResult();
	$rc=$set->GetResultNumRows();
	$ret_arrs=array();
	for($i=0; $i<$rc; $i++){
		$v=mysqli_fetch_array($rs);

		$vv=array();
		$vv['id']=$v['id'];
		//$vv['label']=iconv('windows-1251','utf-8',$v['name_s'].$v['name_d'].', '.$v['position_s']);
		$vv['text']=iconv('windows-1251','utf-8',$v['name_s'].$v['name_d']);
		
		 array_push($ret_arrs, $vv);
		 
	}
	

 
	$ret = array();
	 
	 
	$ret['results'] = $ret_arrs;
	 
	echo json_encode($ret);
	exit();
	
	
}

//подгрузка сотрудников-адресатов
elseif(isset($_POST['action'])&&($_POST['action']=="load_users")){
	$_kpg=new MessageGroup;
	 
	
	 
	
	$except_ids=$_POST['except_ids'];
	
	
 
	$sm=new SmartyAj; 
	 
	$alls=$_kpg->DrawAdrArr($result['id'], true, 2);
	
	 //>GetItemsForBill($dec); 
	//echo mysqlSet::$inst_count.' запросов к БД на выборку<br />';
	
	/*echo '<pre>';
	print_r($alls);
	echo '</pre>';
	*/
	
 
	 
	foreach($alls as $kk=>$v){
				  
	 	  if($v['id']==0) continue;	
		  if($v['id']<0) $v['is_active']=1;
		  
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($except_ids as $ck=>$ccv){
		  	 	
				//echo $v['id'].' vs '.$ccv.' ';
				
				if(($ccv==$v['id'])
				 
				){
					$index=$ck;
					//echo 'nashli'.$v['id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  
			  $v['is_in']=1;
			 
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			  
		  }
		  
		   
		  
		  
		  
		  
		  $v['hash']=md5($v['id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
	
 
	
	$ret.=$sm->fetch("messages1/who_edit_rows.html");
	
	 
	
	


}

	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>