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
 
if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
else $from=0; 

if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
else $to_page=ITEMS_PER_PAGE;
 
 if(!isset($_GET['id']))
	if(!isset($_POST['id'])) {
		 header('Location: index.php');
		 die();
		 
	}
	else $id = $_POST['id'];		
else $id = $_GET['id'];		
$id=abs((int)$id);

$_list=new Delivery_UserGroup;
$_razd=new Delivery_ListItem;
$razd=$_razd->GetItemById($id);

if($razd===false){
	 header('Location: index.php');
		 die();
		 
}

 
  if(isset($_POST['Update'])||isset($_POST['Update1'])){
	  $kind=(int)$_POST['kind'];
	  
	  
	  
	  
	  if($kind==2){
		  //Обновляем базу
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				   
				  	$r=new Delivery_UserItem;
				    $r->Del($lid);
				   
				  
				  
			  }
		  }
	  }
	  
	  if($kind==4){
		   
		   //скопировать в список
		   foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  $lid=(int)$val;
				  
				      
					$r=new Delivery_UserItem;
					$ours=$r->getitembyid($lid);
					if($ours!==false){
						$params=array();
						$params['email']=$ours['email'];
						$params['f']=$ours['f'];
						$params['i']=$ours['i'];
						$params['o']=$ours['o'];
						
						$params['comment']=$ours['comment'];
						
						$params['list_id']=abs((int)$_POST['target_list']);
						$params['is_subscribed']=1;
						
						$test=$r->GetItemByFields(array('email'=>$params['email'], 'list_id'=>$params['list_id']));
						if($test===false) $r->Add($params);
						else $r->Edit($test['id'], $params);
						
							
					}
					 
					
					 
				  
				}
		  }
	 
		  
	  }
	  
	  if($kind==5){
		   
		   //переместить в список
		   foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  $lid=(int)$val;
				  
				   
					$r=new Delivery_UserItem;
				    $r->Edit($lid, array('list_id'=>abs((int) $_POST['target_list'])));
				    
				}
		  }
	 
		  
	  }
	  
	  if($kind==6){
		  //отписать от рассылки
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				   
				  	$r=new Delivery_UserItem;
				    $r->Edit($lid, array('is_subscribed'=>0, 'unsubscribe_way'=>'Отписан администратором', 'unsubscribe_reason'=>'Выбор администратора'));
				   
				  
				  
			  }
		  }
	  }
	  
	   if($kind==7){
		  //восстановить подписку
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				  
				  	$r=new Delivery_UserItem;
				    $r->Edit($lid, array('is_subscribed'=>1, 'unsubscribe_way'=>'', 'unsubscribe_reason'=>''));
				   
				  
				  
			  }
		  }
	  }
	  
	   
	  header('Location: '.$_list->GetPageName().'?id='.$id.'&from='.$from.'&to_page='.$to_page);
	  die();
  }
 


//вывод из шаблона
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;
$smarty->clear_all_assign();

$smarty->assign("SITETITLE",'Подписчики - GYDEX.Рассылки - '.SITETITLE);

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


$_segments=new Delivery_SegmentGroup;

if(isset($_GET['status'])) $status=(int)$_GET['status'];
else $status=-1; 


if(isset($_GET['segment'])) $segment=(int)$_GET['segment'];
else $segment=-1; 

if(!isset($_GET['sortmode'])){
	$sortmode=1;	
}else{
	$sortmode=abs((int)$_GET['sortmode']);
}



require_once('classes/v2/context.php');
//(  $object_id, $right_kind, $module_constant, $name, $url, $is_help , $_auth_result)
$_context=new Context;

$_context->AddContext(new ContextItem( 944, 'w', "", "Добавить подписчика", "delivery_ed_user.php?list_id=".$id,  false , $global_profile  ));

$_context->AddContext(new ContextItem( 944, 'w', "", "Добавить списком", "delivery_mass_user.php?list_id=".$id,  false , $global_profile  ));

$_context->AddContext(new ContextItem( 944, 'r', "", "Список сегментов (".$_segments->CalcItemsById($id).")", "delivery_segments.php?id=".$id,  false , $global_profile  )); 

$_context->AddContext(new ContextItem( 944, 'r', "", "Экспорт подписчиков ", "delivery_list_users_export.php?id=$id&status=$status&segment=$segment&sortmode=$sortmode",  false , $global_profile  )); 


$_context->AddContext(new ContextItem( "", '',  "", "Справка", "delivery_lists.html", true , $global_profile  ));

$context=$_context->BuildContext();
$smarty->assign('context', $context);
$smarty->assign('context_caption', 'Быстрые действия');


 


//хлебные крошки

require_once('classes/v2/bc.php');

 
$_bc=new Bc();
$_bc->AddContext(new BcItem('GYDEX.Рассылки', 'delivery_index.php'));
$_bc->AddContext(new BcItem('Списки', 'delivery_lists.php'));

 

$_bc->AddContext(new BcItem('Список подписчиков '.$razd['name'], 'delivery_list_users.php?id='.$id));

$bc=$_bc->BuildContext();
$smarty->assign('bc', $bc);


$_dmenu_id=49;
 require_once('delivery_menu.php');
$smarty->assign("vmenu",$vmenu);

 
 

 
 
 
 
 
 
$decorator=new DBDecorator;

$_list=new Delivery_UserGroup;




if(($status==1)||($status==0)) $decorator->AddEntry(new SqlEntry('p.is_subscribed',$status, SqlEntry::E));

$decorator->AddEntry(new UriEntry('status',$status));




if($segment>0){
	$decorator->AddEntry(new SqlEntry('p.id','select user_id from delivery_user_segment where segment_id ="'.$segment.'"', SqlEntry::IN_SQL));
			
}elseif($segment==0){
	$decorator->AddEntry(new SqlEntry('p.id','select user_id from delivery_user_segment where segment_id in (select id from delivery_segment where list_id="'.$id.'")', SqlEntry::NOT_IN_SQL));
}

$decorator->AddEntry(new UriEntry('segment',$segment));

 		


$decorator->AddEntry(new UriEntry('to_page',$to_page));
$decorator->AddEntry(new UriEntry('id',$id));




$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.email',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.email',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.is_subscribed',SqlOrdEntry::DESC));
		break;
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.is_subscribed',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.f',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.f',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.i',SqlOrdEntry::DESC));
		break;
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.i',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('p.o',SqlOrdEntry::DESC));
		break;
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('p.o',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.email',SqlOrdEntry::ASC));

 		
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}		
 
		
 
 
$content= $_list->GetItemsById($id, 'delivery/users.html', $from, $to_page, $decorator, 
	$au->user_rights->CheckAccess('w',944),
	$au->user_rights->CheckAccess('w',944),
	$au->user_rights->CheckAccess('w',944) );
 
 
 
  

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