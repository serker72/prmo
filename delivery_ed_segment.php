<?
session_start();
require_once('classes/global.php');
require_once('classes/smarty/SmartyAdm.class.php');
 require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/supplieritem.php');
 

require_once('classes/v2/delivery_lists.class.php');

require_once('classes/authuser.php');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',944)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	} 
 

$razd=new Delivery_SegmentItem;
$_list=new Delivery_SegmentGroup;
$_par=new Delivery_ListItem;

if(!isset($_GET['from'])){
	if(!isset($_POST['from'])){
		$from=0;
	}else $from = $_POST['from'];	
}else $from = $_GET['from'];	
$from=abs((int)$from);	


if(!isset($_GET['to_page'])){
	if(!isset($_POST['to_page'])){
		$to_page=ITEMS_PER_PAGE;
	}else $to_page = $_POST['to_page'];	
}else $to_page = $_GET['to_page'];	
$to_page=abs((int)$to_page);	


if(!isset($_GET['action']))
	if(!isset($_POST['action'])) $action = 0;
	else $action = $_POST['action'];		
else $action = $_GET['action'];		
$action=abs((int)$action);
if(($action!=0)&&($action!=1)&&($action!=2)) $action=0;

 
if(!isset($_GET['nonvisual']))
	if(!isset($_POST['nonvisual'])) $nonvisual = 0;
	else $nonvisual = $_POST['nonvisual'];		
else $nonvisual = $_GET['nonvisual'];		
$nonvisual=abs((int)$nonvisual);	


if($action==0){
	//проверим id
	if(!isset($_GET['list_id']))
		if(!isset($_POST['list_id'])) {
			header('Location: index.php');
			die();
		}
		else $list_id = $_POST['list_id'];		
	else $list_id = $_GET['list_id'];		
	$list_id=abs((int)$list_id);
	
	
	
	$par=$_par->GetItemById($list_id);
	
	
	if($par==false){
		header('Location: index.php');
		die();	
	}
	 
}

 
	
if(($action==1)||($action==2)){
	//проверим id
	if(!isset($_GET['id']))
		if(!isset($_POST['id'])) {
			header('Location: index.php');
			die();
		}
		else $id = $_POST['id'];		
	else $id = $_GET['id'];		
	$id=abs((int)$id);
	
	
	 
 
		$razdel=$razd->GetItemById($id);
	 

	
	//echo $razdel['parent_id']; die();
	
	if($razdel==false){
		header('Location: index.php');
		die();	
	}
	
	$list_id=$razdel['list_id'];
	$par=$_par->GetItemById($list_id);
	
	 
}
 

if(($action==0)&&(isset($_POST['doInp'])||isset($_POST['doApply']))){
	//заносим новую запись
	$params=array(); 
	$params['name']=SecStr($_POST['name']);
	 
	$params['comment']=SecStr($_POST['comment']);	
	
 
	$params['list_id']=abs((int)$_POST['list_id']);
	
	 
	  
		    
	  $r_code=$razd->Add($params);
	  
	  
	  //введем подписчиков
	  if(isset($_POST['mode_change'])&&($_POST['mode_change']=="list")){
			$list=($_POST['list'] );
			$_users=explode("\n", $list);
			
			$_dsu=new Delivery_UserSegmentItem;
			$_user=new Delivery_UserItem;
			foreach($_users as $k=>$user){
				if(strlen(trim($user))==0) continue;
				
				//найти такого подписчика в этом списке
				//добавить его айди в сегмент
				$check_params=array('list_id'=>$params['list_id'], 'email'=>SecStr($user));
				$check_user=$_user->GetItemByFields($check_params);
				
				
				if($check_user!==false){
				
					$u_params=array(); $u_params['segment_id']=$r_code; $u_params['user_id']=$check_user['id'];
					$_dsu->Add($u_params);
					
				}
			}
			
			//print_r($_users);
			//die();
	  }elseif(isset($_POST['mode_change'])&&($_POST['mode_change']=="check")){
		  $users=array();
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  
				  $users[]=(int)$val;
			  }
		  }
		  
		  $razd->AddUsers($r_code, $users);
		  
		 /* print_r($users);
		  die();*/
	  }
	  
	  
	  
	  
	  
	  if(isset($_POST['doInp']))
		  header('Location: '.$_list->GetPageName().'?id='.$list_id.'&from='.$from.'&to_page='.$to_page);
	  else if(isset($_POST['doApply']))
		  header('Location: '.$razd->GetPageName().'?action=1&id='.$r_code.'&from='.$from.'&to_page='.$to_page);
	  die();
	 
}
 


if(($action==1)&&(isset($_POST['doInp'])||isset($_POST['doApply'])||isset($_POST['doNew']))){
	
	$params=array(); 
	$params['name']=SecStr($_POST['name']);
	 
	$params['comment']=SecStr($_POST['comment']);	
	
	
	
	
	if(isset($_POST['doNew'])){
		 
			$id=$razd->Add($params);
		 
	}else	{
		 
			$razd->Edit($id, $params);
			
			
			//введем подписчиков
			  if(isset($_POST['mode_change'])&&($_POST['mode_change']=="list")){
					$list=($_POST['list'] );
					$_users=explode("\n", $list);
					
					$_dsu=new Delivery_UserSegmentItem;
					$_user=new Delivery_UserItem;
					foreach($_users as $k=>$user){
						if(strlen(trim($user))==0) continue;
						
						//найти такого подписчика в этом списке
						//добавить его айди в сегмент
						$check_params=array('list_id'=>$razdel['list_id'], 'email'=>SecStr($user));
						$check_user=$_user->GetItemByFields($check_params);
						
						
						if($check_user!==false){
							$u_params=array(); $u_params['segment_id']=$id; $u_params['user_id']=$check_user['id'];
							if($_POST['list_mode']==1){
								
								$_dsu->Add($u_params);
								//echo 'zzzzzzzzzz';
								//print_r($u_params);
							}else{
								$test_dsu=$_dsu->GetItemByFields($u_params);
								if($test_dsu!==false) $_dsu->Del($test_dsu['id']);
							
							}
						}
					}
					
					 
			  }elseif(isset($_POST['mode_change'])&&($_POST['mode_change']=="check")){
				  $users=array();
				  foreach($_POST as $key=>$val){
					  if(eregi("_do_process",$key)){
						  //echo $key; echo $val;
						  
						  
						  $users[]=(int)$val;
					  }
				  }
				  
				  $razd->AddUsers($id, $users);
				  
				 /* print_r($users);
				  die();*/
			  }
			  
		 
	}
	if(isset($_POST['doInp']))
		header('Location: '.$_list->GetPageName().'?id='.$list_id.'&from='.$from.'&to_page='.$to_page.'#'.$id);
	else if(isset($_POST['doApply'])||isset($_POST['doNew']))
		header('Location: '.$razd->GetPageName().'?action=1&id='.$id.'&from='.$from.'&to_page='.$to_page);
	die();
}


if($action==2){
	 	 $razd->Del($id);
		 
		 header('Location: '.$_list->GetPageName().'?id='.$list_id.'&from='.$from.'&to_page='.$to_page);
		die();
	 
}


 

//вывод из шаблона
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;

$smarty->assign("SITETITLE",'Правка сегмента списка - '.SITETITLE);

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

 

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=70;
	if($print==0) include('inc/menu.php'); 

 $smarty = new SmartyAdm;



require_once('classes/v2/context.php');
//(  $object_id, $right_kind, $module_constant, $name, $url, $is_help , $_auth_result)
$_context=new Context;

//$_context->AddContext(new ContextItem( 26, 'a', "", "Создать список", "delivery_ed_list.php",  false , $global_profile  ));

$_context->AddContext(new ContextItem( "", '',  "", "Справка", "delivery_lists.html", true , $global_profile  ));

$context=$_context->BuildContext();
$smarty->assign('context', $context);
$smarty->assign('context_caption', 'Быстрые действия');



//хлебные крошки

require_once('classes/v2/bc.php');

$_bc=new Bc();
$_bc->AddContext(new BcItem('GYDEX.Рассылки', 'delivery_index.php'));
$_bc->AddContext(new BcItem('Списки', 'delivery_lists.php'));

$_bc->AddContext(new BcItem('Список '.$par['name'], 'delivery_list_segments.php?id='.$list_id));

$_bc->AddContext(new BcItem('Редактирование сегмента'));

//foreach($_razd_bc as $item) $_bc->AddContext(new BcItem($item['name'], $item['url']));


$bc=$_bc->BuildContext();
$smarty->assign('bc', $bc);


$_dmenu_id=49;
require_once('delivery_menu.php');
$smarty->assign("vmenu",$vmenu);

 
 
 



$sm=new SmartyAdm();

$sm->assign('pagename', $razd->GetPageName());
$sm->assign('list_pagename', $_list->GetPageName());
$sm->assign('action', $action);
$sm->assign('from', $from);
$sm->assign('to_page', $to_page);
$sm->assign('list_id', $list_id);


if($action==0){
	
	
	$content=$sm->fetch('delivery/create_segment.html');	
}elseif($action==1){
	foreach($razdel as $k=>$v) $razdel[$k]=stripslashes($v);
	
	$sm->assign('data', $razdel);
	
	
	$content=$sm->fetch('delivery/edit_segment.html');
}


  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	 $smarty->display('page_site.html');









$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>