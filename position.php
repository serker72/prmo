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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/positem.php');

require_once('classes/posdimgroup.php');
require_once('classes/posdimitem.php');
require_once('classes/posgroupgroup.php');
require_once('classes/posgroupitem.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Позиция каталога');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new PosItem;
//$lc=new LoginCreator;
$log=new ActionLog;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=67;
	break;
	case 1:
	$object_id=68;
	break;
	case 2:
	$object_id=69;
	break;
	default:
	$object_id=67;
	break;
}
//echo $object_id;
//die();
if(!$au->user_rights->CheckAccess('w',$object_id)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
}


if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл позицию номенклатуры',NULL,68, NULL, $editing_user['code'].' '.$editing_user['name'],$id);			
}



if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',67)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	$params=array();
	
	
   
   
    //обычная загрузка прочих параметров
	//$params['code']=SecStr($_POST['code']);
	$group_id=abs((int)$_POST['group_id']);
	$group_id2=abs((int)$_POST['group_id2']);
	$group_id3=abs((int)$_POST['group_id3']);
	
	if($group_id3>0) $params['group_id']=$group_id3;
	elseif($group_id2>0) $params['group_id']=$group_id2;
	else $params['group_id']=$group_id;
	
	
	//$params['group_id']=abs((int)$_POST['group_id']);
	
	$params['name']=SecStr($_POST['name']);
	$params['gost_tu']=SecStr($_POST['gost_tu']);
	
	$params['dimension_id']=abs((int)$_POST['dimension_id']);
	
	$params['notes']=SecStr($_POST['notes']);
	
	$params['txt_for_kp']=SecStr($_POST['txt_for_kp']);
	
	$params['length']=SecStr($_POST['length']);
	$params['width']=SecStr($_POST['width']);
	$params['height']=SecStr($_POST['height']);
	$params['weight']=SecStr($_POST['weight']);
	$params['volume']=SecStr($_POST['volume']);
	$params['diametr']=SecStr($_POST['diametr']);
	
	if($au->user_rights->CheckAccess('w',595)){
		if(isset($_POST['is_active'])) $params['is_active']=1;
		else  $params['is_active']=0;	
	}else  $params['is_active']=0;
	
	$code=$ui->Add($params);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал позицию каталога',NULL,67,NULL,$params['name'],$code);	
		
		foreach($params as $k=>$v){
			
		  
				$log->PutEntry($result['id'],'создал позицию каталога',NULL,67, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
	}
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: catalog.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',68)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: position.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: catalog.php");
		die();
	}
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',68)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$params=array();
	
	
	//$params['code']=SecStr($_POST['code']);
	//$params['group_id']=abs((int)$_POST['group_id']);
	
	$group_id=abs((int)$_POST['group_id']);
	$group_id2=abs((int)$_POST['group_id2']);
	$group_id3=abs((int)$_POST['group_id3']);
	
	if($group_id3>0) $params['group_id']=$group_id3;
	elseif($group_id2>0) $params['group_id']=$group_id2;
	else $params['group_id']=$group_id;
	
	
	$params['name']=SecStr($_POST['name']);
	$params['gost_tu']=SecStr($_POST['gost_tu']);
	
	$params['dimension_id']=abs((int)$_POST['dimension_id']);
	
	$params['notes']=SecStr($_POST['notes']);
	
	$params['length']=SecStr($_POST['length']);
	$params['width']=SecStr($_POST['width']);
	$params['height']=SecStr($_POST['height']);
	$params['weight']=SecStr($_POST['weight']);
	$params['volume']=SecStr($_POST['volume']);
	$params['diametr']=SecStr($_POST['diametr']);
	
	/*$params['price']=($_POST['price']);
	$params['price']=str_replace(",",".",$params['price']);
	$params['price']=(float)($params['price']);
	*/
	
	$params['txt_for_kp']=SecStr($_POST['txt_for_kp']);
	$params['photo_for_kp']=SecStr($_POST['photo_for_kp']);
	
	
	if($au->user_rights->CheckAccess('w',595)){
		if(isset($_POST['is_active'])) $params['is_active']=1;
		else  $params['is_active']=0;	
	} 
	
	
	$ui->Edit($id,$params);
	
	
	
	//die();
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if($k=='is_active'){
			if(addslashes($editing_user[$k])!=$v){
			  if($v==0) $log->PutEntry($result['id'],'снял активность позиции каталога',NULL, 595, NULL,NULL,$id);
			  elseif($v==1) $log->PutEntry($result['id'],'установил активность позиции каталога',NULL,595, NULL,NULL,$id);
			}
			continue;	
		}
		
		
		if(addslashes($editing_user[$k])!=$v){
			$log->PutEntry($result['id'],'редактировал позицию каталога',NULL,68, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		}
	}
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: catalog.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',68)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: position.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: catalog.php");
		die();
	}
	
	die();
}elseif(($action==2)){
	if(!$au->user_rights->CheckAccess('w',69)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	if($ui->CanDelete($id)){
		$ui->Del($id);
	
		$log->PutEntry($result['id'],'удалил позицию каталога',NULL,69, NULL, NULL,$id);	
	
	}
	header("Location: catalog.php");
	die();
}







//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=7;

	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		//тест
		
		
	
		$_dim_group=new PosDimGroup;
		$_group_group=new PosGroupGroup;
		$dim_gr=$_dim_group->GetItemsArr();
		$dim_ids=array(); $dim_vals=array();
		
		$dim_ids[]=0; $dim_vals[]='-выберите-';
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('dim_ids',$dim_ids); 
		$sm1->assign('dims',$dim_vals);
		$sm1->assign('dim_items',$dim_gr);
		
		$dim_gr=$_group_group->GetItemsArr();
		$dim_ids=array(); $dim_vals=array();
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('group_ids',$dim_ids); 
		$sm1->assign('group_values',$dim_vals);
		$sm1->assign('items',$dim_gr);
		
		
		
		$gr_gr=$_group_group->GetItemsArr();
		
		
		$sm1->assign('can_active_position',$au->user_rights->CheckAccess('w',595));
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',67)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',68)); 

		$sm1->assign('can_expand_groups',$au->user_rights->CheckAccess('w',70)); 
		
		$sm1->assign('can_expand_dims',$au->user_rights->CheckAccess('w',71)); 
	 
		
	
		$user_form=$sm1->fetch('catalog/position_create.html');
	}elseif($action==1){
		//редактирование позиции
		
		
		
		
		$sm1=new SmartyAdm;
		$sm1->assign('position',$editing_user);
		
		$sm1->assign('session_id',session_id());
		
		
		
		$_dim_group=new PosDimGroup;
		$_group_group=new PosGroupGroup;
		$dim_gr=$_dim_group->GetItemsArr();
		$dim_ids=array(); $dim_vals=array();
		$dim_ids[]=0; $dim_vals[]='-выберите-';
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
		}
		$sm1->assign('dim_ids',$dim_ids); 
		$sm1->assign('dims',$dim_vals);
		$sm1->assign('dim',$editing_user['dimension_id']);
		$sm1->assign('dim_items',$dim_gr);
		
		$_gi=new PosGroupItem;
		$gi=$_gi->GetItemById($editing_user['group_id']);
		if($gi['parent_group_id']>0){
			$gi2=$_gi->GetItemById($gi['parent_group_id']);	
			if($gi2['parent_group_id']>0){
				$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
				$current_one_id=$gi3['id'];
				$current_two_id=$gi2['id'];
				$current_three_id=$gi['id'];
			}else{
				$current_one_id=$gi2['id'];
				$current_two_id=$gi['id'];
				$current_three_id=0;	
			}
		}else{
			$current_one_id=$gi['id'];
			$current_two_id=0;
			$current_three_id=0;	
		}
		
		/*echo $current_one_id;
			echo $current_two_id=0;
			echo $current_three_id=0;
		*/
		
		
		$dim_gr=$_group_group->GetItemsArr();
		$dim_ids=array(); $dim_vals=array(); 
		
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
			
		}
		$sm1->assign('group_ids',$dim_ids); 
		$sm1->assign('group_values',$dim_vals);
		$sm1->assign('group_id',$current_one_id); //$editing_user['group_id']);
		$sm1->assign('items',$dim_gr);
		
		
		
		//подгруппы
		$dim_gr=$_group_group->GetItemsByIdArr($current_one_id);
		$dim_ids=array(); $dim_vals=array(); 
		$dim_ids[]=''; $dim_vals[]='-выберите-';
		foreach($dim_gr as $k=>$v){
			$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
			
		}
		$sm1->assign('gr_ids2',$dim_ids); 
		$sm1->assign('gr_names2',$dim_vals);
		$sm1->assign('gr_id2',$current_two_id);
		
		//подподгруппы
		if($current_two_id>0){
			$dim_gr=$_group_group->GetItemsByIdArr($current_two_id);
			$dim_ids=array(); $dim_vals=array(); 
			$dim_ids[]=''; $dim_vals[]='-выберите-';
			foreach($dim_gr as $k=>$v){
				$dim_ids[]=$v['id']; $dim_vals[]=$v['name'];
				
			}
			$sm1->assign('gr_ids3',$dim_ids); 
			$sm1->assign('gr_names3',$dim_vals);
			$sm1->assign('gr_id3',$current_three_id);
		}
		
		
		$gr_gr=$_group_group->GetItemsArr();
		
		$sm1->assign('can_active_position',$au->user_rights->CheckAccess('w',595));
		
		$sm1->assign('can_expand_groups',$au->user_rights->CheckAccess('w',70)); 
		
		$sm1->assign('can_expand_dims',$au->user_rights->CheckAccess('w',71)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',67)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',68)); 
		
		
	 
		
		
		$user_form=$sm1->fetch('catalog/position_edit.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',517)){
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
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(64,
70,
150,
151,
67,
71,
68,
69,
601,
602,
603,
604,
605
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'position.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('catalog/position.html');
	
	
	
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