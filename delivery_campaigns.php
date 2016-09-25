<?
session_start();
require_once('classes/global.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/supplieritem.php');
 

require_once('classes/v2/delivery.class.php');

//административная авторизация
//require_once('inc/adm_header.php');

require_once('classes/authuser.php');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!$au->user_rights->CheckAccess('w',942)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	} 
 
if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
else $from=0; 

if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
else $to_page=ITEMS_PER_PAGE;
 

$_list=new Delivery_Group;



   
	  
  if(isset($_POST['Update'])||isset($_POST['Update1'])){
	  $kind=(int)$_POST['kind'];
	  
	  
	  
	  
	  if($kind==2){
		  //Обновляем базу
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				  
				  	$r=new Delivery_Item;
				    $r->Del($lid);
				   
				  
				  
			  }
		  }
	  }
	  
	  
	  if($kind==3){
		  //Обновляем базу
		  foreach($_POST as $key=>$val){
			  if(eregi("_do_process",$key)){
				  //echo $key; echo $val;
				  
				  //удаляем 
				  
				  $lid=(int)$val;
				   
				  	$r=new Delivery_Item;
					
					$params=array();
					
					$params['status_id']=1; $params['pdate_status_change']=time();
					
				    $r->Edit($lid, $params);
				   
				  
				  
			  }
		  }
	  }
	  
	  
	  
	  
	  header('Location: '.$_list->GetPageName().'?from='.$from.'&to_page='.$to_page);
	  die();
  }
 

//вывод из шаблона
$smarty = new SmartyAdm;
$smarty->debugging = DEBUG_INFO;
 

$smarty->assign("SITETITLE",'GYDEX.Рассылки - '.SITETITLE);



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

$_context->AddContext(new ContextItem( 942, 'w', "", "Создать рассылку", "delivery_ed_campaign.php",  false , $global_profile  ));

//$_context->AddContext(new ContextItem( "", '',  "", "Справка", "delivery_campaigns.html", true , $global_profile  ));

$context=$_context->BuildContext();
$smarty->assign('context', $context);
$smarty->assign('context_caption', 'Быстрые действия');



//хлебные крошки
//require_once('classes/v2/mmenuitem_new.php');
require_once('classes/v2/bc.php');
//$_r2=new MmenuItemNew;
 
$_bc=new Bc();
$_bc->AddContext(new BcItem('GYDEX.Рассылки', 'delivery_index.php'));
$_bc->AddContext(new BcItem('Рассылки', 'delivery_campaigns.php'));

 

 
$bc=$_bc->BuildContext();
$smarty->assign('bc', $bc);


$_dmenu_id=47; 
require_once('delivery_menu.php');
$smarty->assign('vmenu', $vmenu);



//var_dump($vmenu);





 
$decorator=new DBDecorator;

$_list=new Delivery_Group;


 if(isset($_GET['status_id'])) $status=(int)$_GET['status_id'];
else $status=-1; 
if(($status!=-1)) $decorator->AddEntry(new SqlEntry('p.status_id',$status, SqlEntry::E));

$decorator->AddEntry(new UriEntry('status_id',$status));	


 if(isset($_GET['list_id'])) $list_id=(int)$_GET['list_id'];
else $list_id=-1; 
if(($list_id!=-1)) $decorator->AddEntry(new SqlEntry('p.list_id',$list_id, SqlEntry::E));

$decorator->AddEntry(new UriEntry('list_id',$list_id));

	


$decorator->AddEntry(new UriEntry('to_page',$to_page));


if(!isset($_GET['sortmode'])){
		$sortmode=1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode']);
	}
$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.name',SqlOrdEntry::ASC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('l.name',SqlOrdEntry::DESC));
		break;
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('l.name',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('st.name',SqlOrdEntry::DESC));
		break;
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('st.name',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));

 		
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		break;	
		
	}		
 
 
$content= $_list->GetItems('delivery/campaigns.html', $from, $to_page, $decorator, 
	$au->user_rights->CheckAccess('w',942) ,
	$au->user_rights->CheckAccess('w',942),
	$au->user_rights->CheckAccess('w',942) );
 
 
 
 
 

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