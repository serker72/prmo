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

require_once('classes/sectoritem.php');
 
require_once('classes/user_s_item.php');
require_once('classes/user_s_group.php');

require_once('classes/sectornotesgroup.php');
require_once('classes/sectornotesitem.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование склада');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$ui=new SectorItem;
 
//$lc=new LoginCreator;
$log=new ActionLog;

$_user_s=new UserSItem;
$_user_s_group=new UsersSGroup;

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

switch($action){
	case 0:
	$object_id=72;
	break;
	case 1:
	$object_id=73;
	break;
	case 2:
	$object_id=74;
	break;
	default:
	$object_id=72;
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



//журнал событий 
if($action==1){
	$log=new ActionLog;
	$log->PutEntry($result['id'],'открыл карту склада',NULL,73, NULL, $editing_user['name'],$id);			
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',72)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	 
	$params=array();
	
    //обычная загрузка прочих параметров
	
	$params['name']=SecStr($_POST['name']);
	$params['fact_address']=SecStr($_POST['fact_address']);
	$params['nach_user_id']=abs((int)$_POST['nach_user_id']);
	$params['zamnach_user_id']=abs((int)$_POST['zamnach_user_id']);
	
	$params['time_from_h_s']=SecStr($_POST['time_from_h_s']);
	$params['time_from_m_s']=SecStr($_POST['time_from_m_s']);
	$params['time_to_h_s']=SecStr($_POST['time_to_h_s']);
	$params['time_to_m_s']=SecStr($_POST['time_to_m_s']);
	
	if(isset($_POST['is_active'])) $params['is_active']=1;
	else $params['is_active']=0;
	
	
	if($au->user_rights->CheckAccess('w',377)){
		if(isset($_POST['s_s'])) $params['s_s']=1;
		else $params['s_s']=0;
	}
	
	
	$params['notes']=SecStr($_POST['notes']);
	
	
	$code=$ui->Add($params);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал склад',NULL,72,NULL,$params['name'],$code);
		
		foreach($params as $k=>$v){
			
				$log->PutEntry($result['id'],'создал склад',NULL,72, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}	
	}
	
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: sector.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',73)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_sector.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: sector.php");
		die();
	}
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',73)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	 
	
	
	
	$params=array();
	
	$params['name']=SecStr($_POST['name']);
	$params['fact_address']=SecStr($_POST['fact_address']);
	$params['nach_user_id']=abs((int)$_POST['nach_user_id']);
	$params['zamnach_user_id']=abs((int)$_POST['zamnach_user_id']);
	
	$params['time_from_h_s']=SecStr($_POST['time_from_h_s']);
	$params['time_from_m_s']=SecStr($_POST['time_from_m_s']);
	$params['time_to_h_s']=SecStr($_POST['time_to_h_s']);
	$params['time_to_m_s']=SecStr($_POST['time_to_m_s']);
	
	/*if(isset($_POST['is_active'])) $params['is_active']=1;
	else $params['is_active']=0;*/
	
	
	if($au->user_rights->CheckAccess('w',377)){
		if(isset($_POST['s_s'])) $params['s_s']=1;
		else $params['s_s']=0;
	}
	
	
	$params['notes']=SecStr($_POST['notes']);
	
	
	$ui->Edit($id,$params);
	
	
	//утверждение 
	if($editing_user['is_active']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',161))){
			if(!isset($_POST['is_active'])){
				$ui->Edit($id,array('is_active'=>0));
				
				$log->PutEntry($result['id'],'снял утверждение склада',NULL,161, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',160)){
			if(isset($_POST['is_active'])){
				$ui->Edit($id,array('is_active'=>1));
				
				$log->PutEntry($result['id'],'утвердил склад',NULL,160, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	
	
	//die();
	//записи в лог. сравнить старые и новые записи
	foreach($params as $k=>$v){
		
		if(addslashes($editing_user[$k])!=$v){
			
			
			if(($k=='nach_user_id')&&($v>0)){
						$__user_s=$_user_s->getItemById($v);
						$log->PutEntry($result['id'],'редактировал склад',$v,73,NULL,'установлен начальник склада: '.$__user_s['name_s'].' '.$__user_s['login'],$id);
						continue;	
			}
			
			if(($k=='zamnach_user_id')&&($v>0)){
						$__user_s=$_user_s->getItemById($v);
						$log->PutEntry($result['id'],'редактировал склад',$v,73,NULL,'установлен заместитель начальника склада: '.$__user_s['name_s'].' '.$__user_s['login'],$id);
						continue;	
			}
			
			$log->PutEntry($result['id'],'редактировал склад',NULL,73, NULL, 'в поле '.$k.' установлено значение '.$v,$id);		
		}
	}
	
	
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: sector.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',73)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_sector.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: sector.php");
		die();
	}
	
	die();
}elseif(($action==2)){
	if(!$au->user_rights->CheckAccess('w',74)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	$ui->Del($id);
	
	$log->PutEntry($result['id'],'удалил склад',NULL,74, NULL, NULL,$id);	
	
	header("Location: sector.php");
	die();
}






//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

$_menu_id=9;

	include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		//тест
		
		
	
		
	 
		
		
		$users_arr=$_user_s_group->GetItemsArr(0,1);
		$nach_user_ids=array(); $nach_user_names=array();
		$nach_user_ids[]=''; $nach_user_names[]='';
		foreach($users_arr as $k=>$v){
			$nach_user_ids[]=$v['id'];
			$nach_user_names[]=$v['name_s'];//.' '.$v['login'];
		}
		$sm1->assign('nach_user_id',0);
		$sm1->assign('nach_user_ids',$nach_user_ids);
		$sm1->assign('nach_user_names',$nach_user_names);
		
		$sm1->assign('zamnach_user_id',0);
		$sm1->assign('zamnach_user_ids',$nach_user_ids);
		$sm1->assign('zamnach_user_names',$nach_user_names);
		
		
		$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',"09");
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',"00");
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',"18");
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',"00");
		
		
		$sm1->assign('can_s_s',$au->user_rights->CheckAccess('w',377)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',72)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',73)); 

		$sm1->assign('can_modify_ss',$au->user_rights->CheckAccess('w',126)); 
		$sm1->assign('can_modify_cmk',$au->user_rights->CheckAccess('w',589)); 
		
	
		$user_form=$sm1->fetch('sector/sector_create.html');
	}elseif($action==1){
		//редактирование позиции
		
	 
		
		
		
		$sm1=new SmartyAdm;
		$sm1->assign('sector',$editing_user);
		
		
		
		$users_arr=$_user_s_group->GetItemsArr(0,1);
		$nach_user_ids=array(); $nach_user_names=array();
		$nach_user_ids[]=''; $nach_user_names[]='';
		foreach($users_arr as $k=>$v){
			$nach_user_ids[]=$v['id'];
			$nach_user_names[]=$v['name_s'];//.' '.$v['login'];
		}
		//$sm1->assign('nach_user_id',0);
		$sm1->assign('nach_user_ids',$nach_user_ids);
		$sm1->assign('nach_user_names',$nach_user_names);
		
		//$sm1->assign('zamnach_user_id',0);
		$sm1->assign('zamnach_user_ids',$nach_user_ids);
		$sm1->assign('zamnach_user_names',$nach_user_names);
		
		$from_hrs=array();
		for($i=0;$i<=23;$i++) $from_hrs[]=sprintf("%02d",$i);
		$sm1->assign('from_hrs',$from_hrs);
		$sm1->assign('from_hr',$editing_user['time_from_h_s']);
				
		$from_ms=array();
		for($i=0;$i<=59;$i++) $from_ms[]=sprintf("%02d",$i);
		$sm1->assign('from_ms',$from_ms);
		$sm1->assign('from_m',$editing_user['time_from_m_s']);
		
		
		$to_hrs=array();
		for($i=0;$i<=23;$i++) $to_hrs[]=sprintf("%02d",$i);
		$sm1->assign('to_hrs',$to_hrs);
		$sm1->assign('to_hr',$editing_user['time_to_h_s']);
		
		$to_ms=array();
		for($i=0;$i<=59;$i++) $to_ms[]=sprintf("%02d",$i);
		$sm1->assign('to_ms',$to_ms);
		$sm1->assign('to_m',$editing_user['time_to_m_s']);
		
		//Примечания
		$rg=new SectorNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,false,false,false,$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',73));
		
		$sm1->assign('can_s_s',$au->user_rights->CheckAccess('w',377)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',72)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',73)); 
		$sm1->assign('can_modify_ss',$au->user_rights->CheckAccess('w',126)); 
		$sm1->assign('can_modify_cmk',$au->user_rights->CheckAccess('w',589)); 
		
		
		
		$can_confirm=false;
			if($editing_user['is_active']==1){
				if($au->user_rights->CheckAccess('w',161)){
					//полные права
					$can_confirm=true;	
				}else{
					$can_confirm=false;
				}
			}else{
				//95
				$can_confirm=$au->user_rights->CheckAccess('w',160);
			}
		
		$sm1->assign('can_confirm',$can_confirm); 
		
		
		$user_form=$sm1->fetch('sector/sector_edit.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',518)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(78,
72,
73,
126,
160,
161,
74,
377)));
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_sector.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$content=$sm->fetch('sector/ed_sector_page.html');
	
	
	
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