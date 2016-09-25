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


require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');
require_once('classes/sectorgroup.php');


require_once('classes/posdimitem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/invitem.php');
require_once('classes/invpositem.php');
require_once('classes/invposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

 
require_once('classes/acc_group.php');
require_once('classes/wfgroup.php');

require_once('classes/paygroup.php');

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/invnotesgroup.php');
require_once('classes/invnotesitem.php');

require_once('classes/invcreator.php');

//require_once('classes/payforbillgroup.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

$_orgitem=new OrgItem;


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование инвентаризационного акта');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_bill=new InvItem;
$_bpi=new InvPosItem;
$_position=new PosItem;

$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;

$lc=new InvCreator;

$_supgroup=new SuppliersGroup;
$_opf=new OpfItem;

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',335)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

if(!isset($_GET['from_begin'])){
	if(!isset($_POST['from_begin'])){
		$from_begin=0;
	}else $from_begin=1; 
}else $from_begin=1;


$object_id=array();
switch($action){
	case 0:
	$object_id[]=322;
	break;
	case 1:
	$object_id[]=322;
	$object_id[]=335;
	break;
	case 2:
	$object_id[]=326;
	break;
	default:
	$object_id[]=322;
	break;
}
//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('w',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


$_editable_status_id=array();
$_editable_status_id[]=1;


if($action==0){
	$orgitem=$_orgitem->getitembyid($result['org_id']);	
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
	$editing_user=$_bill->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
}


//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту инвентаризационного акта',NULL,322, NULL, $editing_user['code'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту инвентаризационного акта: версия для печати',NULL,335, NULL, $editing_user['code'],$id);
				
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',322)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	
	$params['inventory_pdate']=  DateFromdmY($_POST['inventory_pdate']);
	$params['pdate']=time();
	
	
	
	$params['code']=SecStr($_POST['code']);
	
	$params['sector_id']=abs((int)$_POST['sector_id']);
	
	
	$params['is_confirmed']=0;
	$params['is_confirmed_inv']=0;
	
	
	$params['manager_id']=$result['id'];
	
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	
	
	
	$code=$_bill->Add($params);
	//$code=1;
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал инвентаризационный акт',NULL,322,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		  
				$log->PutEntry($result['id'],'создал инвентаризационный акт',NULL,322, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',323))){
		//позиции
		$positions=array();
		
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		
		
			
		foreach($_POST as $k=>$v){
		  if(eregi("^new_position_id_([0-9]+)",$k)){
			  
			  $hash=eregi_replace("^new_position_id_","",$k);
			  
			  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			
			  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			 
			  $pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$hash]));
			  $positions[]=array(
				  'inventory_id'=>$code,
				 
				  'position_id'=>$pos_id,
				  
				  'name'=>SecStr($pos['name']),
				  'dimension'=>SecStr($dimension['name']),
				  'quantity_as_is'=>((float)str_replace(",",".", $_POST['new_quantity_as_is_'.$hash])),
				  'quantity_fact'=>((float)str_replace(",",".", $_POST['new_quantity_fact_'.$hash]))
				 
			  );
			  
		  }
		}
			
		
		/*
		echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		//die();
		*/
		
		//внесем позиции
		$_bill->AddPositions($code,$positions);
		//die();
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
				$descr=SecStr($v['name']).'<br /> кол-во по программе '.($v['quantity_as_is']).'<br /> фактическое кол-во '.($v['quantity_fact']);
				
				
				$log->PutEntry($result['id'],'добавил позицию инвентаризационного акта', NULL, 323,NULL,$descr,$code);	
				
				
			}
		}	
		
	}
	
	//die();
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: invent.php");
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',326)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_inv.php?action=1&id=".$code.'&from_begin='.$from_begin.'&tab_page=1');
		die();	
		
	}else{
		header("Location: invent.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',326)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	//редактирование возможно, если is_confirmed==0
	
	$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
		$params=array();
		//обычная загрузка прочих параметров
		
		
		
		if(isset($_POST['inventory_pdate'])) $params['inventory_pdate'] =  DateFromdmY($_POST['inventory_pdate']);
	
	
	
		
		if(isset($_POST['given_no'])) $params['given_no']=SecStr($_POST['given_no']);
		
		
		if(isset($_POST['sector_id'])) $params['sector_id']=abs((int)$_POST['sector_id']);
		
		
		$_bill->Edit($id, $params,false,$result);
		
	
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				if($k=='inventory_pdate'){
					$log->PutEntry($result['id'],'редактировал дату инвентаризации',NULL,326,NULL,'дата: '.$_POST['inventory_pdate'],$id);
					continue;	
				}
				
			
				
				$log->PutEntry($result['id'],'редактировал инвентаризационный акт',NULL,326, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
		if($au->user_rights->CheckAccess('w',324)){
		  $positions=array();
		  
		 
		  
		  $_pos=new PosItem;
		  $_pdi=new PosDimItem;
		 
		   foreach($_POST as $k=>$v){
			if(eregi("^new_position_id_([0-9]+)",$k)){
			
				$hash=eregi_replace("^new_position_id_","",$k);
				
				$pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			  
				$dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
			   
				$pos=$_pos->GetItemById( abs((int)$_POST['new_position_id_'.$hash]));
				$positions[]=array(
					'inventory_id'=>$id,
				   
					'position_id'=>$pos_id,
					 
					'name'=>SecStr($pos['name']),
					'dimension'=>SecStr($dimension['name']),
					'quantity_as_is'=>((float)str_replace(",",".", $_POST['new_quantity_as_is_'.$hash])),
					'quantity_fact'=>((float)str_replace(",",".", $_POST['new_quantity_fact_'.$hash]))
				   
				);
				
			}
		  }
		  
		/*  echo '<pre>';
		 print_r($positions);
		   echo '</pre>';
		 die();*/
		 
		  $log_entries=$_bill->AddPositions($id,$positions);
		  
		  //предусмотреть блок удаления совсем удаленных у товара позиций!
		  // перенесено в функцию AddPositions!!!
		  
		  
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  $pos=$_pos->GetItemById($v['pl_position_id']);
			  $description=SecStr($pos['name']).'<br /> кол-во по программе '.($v['quantity_as_is']).'<br /> фактическое кол-во '.($v['quantity_fact']);
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию инвентаризационного акта',NULL,323,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию инвентаризационного акта',NULL,324,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию инвентаризационного акта',NULL,325,NULL,$description,$id);
			  }
			  
		  }
		
		}
	}
	
	
	
	//утверждение заполнения
	
	if($editing_user['is_confirmed_inv']==0){
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',332))){
			  if((!isset($_POST['is_confirmed']))&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2)) ){
				  $_bill->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения',NULL,332, NULL, NULL,$id);	
				 // $_bill->FreeBindedPayments($id);
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',331)){
			  if(isset($_POST['is_confirmed'])&&($_POST['is_confirmed']==1)&&in_array($editing_user['status_id'], array(1))&&in_array($_POST['current_status_id'], array(1))){
				  $_bill->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение',NULL,331, NULL, NULL,$id);	
				  
				  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	//утверждение отгрузки
	if($editing_user['is_confirmed']==1){
	  if($editing_user['is_confirmed_inv']==1){
		  
		  
		 
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',334))){
			  //if(!isset($_POST['is_confirmed_shipping'])){
			 
			  if((!isset($_POST['is_confirmed_inv']))&&in_array($editing_user['status_id'], array(2,16))&&in_array($_POST['current_status_id'], array(2,16)) ){
				if($_bill->DocCanUnconfirmShip($id,$rss)){  
				  
				  $_bill->Edit($id,array('is_confirmed_inv'=>0, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'снял утверждение коррекции складских остатков',NULL,334, NULL, NULL,$id);	
				}
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',333)){
			  if(isset($_POST['is_confirmed_inv'])&&in_array($editing_user['status_id'], array(2,16))&&in_array($_POST['current_status_id'], array(2,16))){
				  $_bill->Edit($id,array('is_confirmed_inv'=>1, 'user_confirm_inv_id'=>$result['id'], 'confirm_inv_pdate'=>time()),true,$result);
				  
				  $log->PutEntry($result['id'],'утвердил коррекцию складских остатков',NULL,333, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	

	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: invent.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',326)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_inv.php?action=1&id=".$id.'&from_begin='.$from_begin.'&tab_page=1');
		die();	
		
	}else{
		header("Location: invent.php");
		die();
	}
	
	die();
}





//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

$_menu_id=39;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	$opf=$_opf->GetItemById($orgitem['opf_id']);
	
	
	
	if($action==0){
		//создание инвентаризацц
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
			$_sectors=new SectorGroup;
		$sgs= $_sectors->GetItemsArr(0,1);
		$sender_storage_ids=array();
		$sender_storage_names=array();
		
		
		 
		
		foreach($sgs as $k=>$v){
			 
				$sender_storage_ids[]=$v['id'];
				$sender_storage_names[]=$v['name'];	
			
			
		
		}
		$sm1->assign('sector_ids',$sender_storage_ids);
		$sm1->assign('sector_names',$sender_storage_names);
		
		
		
		
		//тов группы
		$posgroupgroup=$_posgroupgroup->GetItemsArr(); // >GetItemsTreeArr();
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($posgroupgroup as $k=>$v){
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		$sm1->assign('tov_group_ids', $st_ids);
		$sm1->assign('tov_group_names', $st_names);
		
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$acts[]=$f;
		}
		$sm1->assign('dim',$acts);
		
		$lc->ses->ClearOldSessions();
		$sm1->assign('code', $lc->GenLogin($result['id']));
		
		$sm1->assign('can_modify',true); 
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',322)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',326)); 
		
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',323));
		
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',323)); 
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',324)); 
		
		
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		$user_form=$sm1->fetch('inv/inv_create.html');
		
		
		
		$sm->assign('has_is', true); //($editing_user['is_confirmed_shipping']==1));
		$sm->assign('is','В данном режиме просмотр распоряжений на списание по распоряжению на инвентаризацию недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать распоряжение на инвентаризацию и перейти к утверждению" на вкладке "Распоряжение на инвентаризацию" для получения возможности просмотра распоряжений на списание.');		
 
 
 		$sm->assign('has_acc', true); //($editing_user['is_confirmed_shipping']==1));
		$sm->assign('accs','В данном режиме просмотр поступлений товара по распоряжению на инвентаризацию недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать распоряжение на инвентаризацию и перейти к утверждению" на вкладке "Распоряжение на инвентаризацию" для получения возможности просмотра поступлений товара.');		
 		
 
		
			
		
		
		if($au->user_rights->CheckAccess('w',530)){
			$sm->assign('has_syslog',true);
			
			$sm->assign('syslog','В данном режиме просмотр журнала событий распоряжения на инвентаризацию недоступен.<br />
 Пожалуйста, нажмите кнопку "Создать распоряжение на инвентаризацию и перейти к утверждению" на вкладке "Распоряжение на инвентаризацию" для получения возможности просмотра журнала событий.');		
		}
		
		
		
		
	}elseif($action==1){
		//редактирование позиции
		
		
		
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$sm1=new SmartyAdm;
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		$editing_user['inventory_pdate']=date("d.m.Y",$editing_user['inventory_pdate']);
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		//тов группы
		$posgroupgroup=$_posgroupgroup->GetItemsArr(); // >GetItemsTreeArr();
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($posgroupgroup as $k=>$v){
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
				
		}
		$sm1->assign('tov_group_ids', $st_ids);
		$sm1->assign('tov_group_names', $st_names);
		
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$acts[]=$f;
		}
		$sm1->assign('dim',$acts);
		
		
			
		//участки
		 
		
		$_sectors=new SectorGroup;
		$rcs=$_sectors->GetItemsArr(0,1);
		$sender_sector_ids=array('0');
		$sender_sector_names=array('-выберите-');
		
		foreach($rcs as $k=>$v){
		 
				$sender_sector_ids[]=$v['id'];
				$sender_sector_names[]=$v['name'];	
					
			
		}
		
		$sm1->assign('sector_ids',$sender_sector_ids);
		$sm1->assign('sector_id',$editing_user['sector_id']);
		$sm1->assign('sector_names',$sender_sector_names);
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new InvPosGroup; // BillPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,$result);
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		
		
		
		//блок аннулирования
		
		$editing_user['can_annul']=$_bill->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',336);
		if(!$au->user_rights->CheckAccess('w',336)) $reason='недостаточно прав для данной операции';
		$editing_user['can_annul_reason']=$reason;
		
		$editing_user['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($editing_user['id']);
		
		$editing_user['can_restore']=$_bill->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',337);
			if(!$au->user_rights->CheckAccess('w',337)) $reason='недостаточно прав для данной операции';
		
		
		
		//$sm1->assign('org',$orgitem['name']);
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('org_id',$result['org_id']);
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id)); //$editing_user['is_confirmed']==0);
		
	
	
		
		
		
	
		//Примечания
		$rg=new InvNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'], 0,0, $editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',339), $au->user_rights->CheckAccess('w',349),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',327)/*&&($editing_user['is_confirmed_price']==0)*/);
		
		
		
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',335)); 
		//$sm1->assign('can_eq',$au->user_rights->CheckAccess('w',292)); 
		
		
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',324)); 
		$sm1->assign('can_delete_positions',$au->user_rights->CheckAccess('w',325)); 
		
		$sm1->assign('can_edit_quantities',(in_array($editing_user['status_id'],$_editable_status_id))&&$au->user_rights->CheckAccess('w',324));
		
		$cannot_select_positions=($editing_user['is_confirmed']==1);
	
		$cannot_select_positions=$cannot_select_positions||!$au->user_rights->CheckAccess('w',324);
	
	
	
		$sm1->assign('cannot_select_positions', $cannot_select_positions);
		
		
		//блок утверждения заполнения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			
			$sm1->assign('confirmer',$confirmer);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		$can_confirm=false;
		if($editing_user['is_confirmed_inv']==0){
			
			  
		  
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',331)){
				  //полные права
				  $can_confirm=true;	
			  }else{
				  $can_confirm=false;
			  }
		  }else{
			  //95
			  $can_confirm=$au->user_rights->CheckAccess('w',332)&&in_array($editing_user['status_id'],$_editable_status_id);
		  }
		}
		$sm1->assign('can_confirm',$can_confirm);
		
		
		//блок утв. отгрузки
		if(($editing_user['is_confirmed_inv']==1)&&($editing_user['user_confirm_inv_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_inv_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_inv_pdate']);
			
			$sm1->assign('is_confirmed_inv_confirmer',$confirmer);
		}
		
		$can_confirm_inv=false;
		if($editing_user['is_confirmed']==1){
		
		  if($editing_user['is_confirmed_inv']==1){
			  if($au->user_rights->CheckAccess('w',334)){
				  //полные права
				  $can_confirm_inv=true;	
			  }else{
				  $can_confirm_inv=false;
			  }
		  }else{
			  //95
			  $can_confirm_inv=$au->user_rights->CheckAccess('w',333);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_inv=$can_confirm_inv&&($editing_user['is_confirmed']==1);
		
		
		$sm1->assign('can_confirm_inv',$can_confirm_inv);
		
		
		
		$reason='';
		
		
		$sm1->assign('can_unconfirm_by_document',(int)$_bill->DocCanUnconfirmShip($editing_user['id'],$reason));
		$sm1->assign('can_unconfirm_by_document_reason',$reason);
		
		
		
	/*	$ig=$_bill->sync->HasNotDifference($editing_user['id'],$reasons);
		
		var_dump($ig);
		echo $reasons;*/
	
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		if($editing_user['is_confirmed_inv']==1){
			
			
			
			
			
			//!!!! приход
			
			
			$bg=new BillInGroup; 
			$prefix=$bg->prefix;
			
			//echo $prefix;
			$bg->SetPageName('ed_inv.php');
			//Разбор переменных запроса
			if(isset($_GET['from_bill'.$prefix])) $from_bill=abs((int)$_GET['from_bill'.$prefix]);
			else $from_bill=0;
			
			if(isset($_GET['to_page_bill'.$prefix])) $to_page_bill=abs((int)$_GET['to_page_bill'.$prefix]);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			$decorator->AddEntry(new SqlEntry('p.inventory_id',$editing_user['id'], SqlEntry::E));
			
			
			/*
			
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_in_bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
			$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['bill_in_bill_statuses'])&&is_array($_GET['bill_in_bill_statuses'])) $cou_stat=count($_GET['bill_in_bill_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['bill_in_bill_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^kom_in_bill_bill_in_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_in_bill_bill_in_bill_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_in_bill_statuses[]',$v));
			  }
		  } 
		


			
			
			if(!isset($_GET['pdate_bill1'.$prefix])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_bill1'.$prefix];
			
			
			
			if(!isset($_GET['pdate_bill2'.$prefix])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_bill2'.$prefix];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_bill1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_bill2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_bill'.$prefix])&&(strlen($_GET['code_bill'.$prefix])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_bill'.$prefix]), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_bill',$_GET['code_bill'.$prefix]));
			}
			
			if(isset($_GET['name_bill'])&&(strlen($_GET['name_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name_bill'.$prefix]), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('name_bill',$_GET['name_bill'.$prefix]));
			}
			
			
			
			if(isset($_GET['supplier_name_bill'.$prefix])&&(strlen($_GET['supplier_name_bill'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_bill'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name_bill',$_GET['supplier_name_bill'.$prefix]));
	}
			
			
			
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode_bill'.$prefix])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_bill'.$prefix]);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode_bill',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			
			$bg->SetAuthResult($result);
			
			$llg='<h2>Связанные входящие счета</h2>'.$bg->ShowPos('bills_in/bills_list_komplekt.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',607), $au->user_rights->CheckAccess('w',613)||$au->user_rights->CheckAccess('w',625), $au->user_rights->CheckAccess('w',94), '_bill',$au->user_rights->CheckAccess('w',620),$au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',627),$limited_sector, NULL, $au->user_rights->CheckAccess('w',621),$au->user_rights->CheckAccess('w',622), $au->user_rights->CheckAccess('w',623));
			
			
			
		
			
			
			
			
			
			//связанные поступления
			//вывод поступлений
			$_acg=new AccInGroup;
			$_acg->prefix='_acc';
			$prefix=$_acg->prefix;
			
			$_acg->SetPagename('ed_inv.php');
			$_acg->SetIdName('p.inventory_id');
			
			$dec2=new DBDecorator;
			
		/*
			
			
			//блок фильтров статуса
			$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^'.$_acg->prefix.'acc_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^'.$_acg->prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$_acg->prefix.'acc_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_'.$_acg->prefix.'acc_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$dec2->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('acc_status_id_'.$v,1));
				$dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
				$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$_acg->prefix.'acc_statuses'])&&is_array($_GET[$_acg->prefix.'acc_statuses'])) $cou_stat=count($_GET[$_acg->prefix.'acc_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$_acg->prefix.'acc_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^acc_'.$_acg->prefix.'acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_'.$_acg->prefix.'acc_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $dec2->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $dec2->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('status_id_'.$v,1));
				  $dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				    foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry($_acg->prefix.'acc_statuses[]',$v));
			  }
		  } 
			
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$_acg->SetAuthResult($result);
			$llg.='<h2>Связанные поступления</h2>'.$_acg->ShowPos($editing_user['id'],'acc_in/acc_list.html', $dec2, $au->user_rights->CheckAccess('w',664),  $au->user_rights->CheckAccess('w',674), $au->user_rights->CheckAccess('w',671), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',675),$limited_sector,NULL,$au->user_rights->CheckAccess('w',672),
			 $temp_accs, //13
			  $au->user_rights->CheckAccess('w',673), //14
			  false, //15
			  $limited_supplier,  //16
			  $au->user_rights->CheckAccess('w',930)
			
			 );
			
			$sm->assign('accs',$llg);	
			
			
			
			
			
			
			
			
			
			//!!!! расход
			
			
			$bg=new BillGroup; 
			$bg->SetPageName('ed_inv.php');
			//Разбор переменных запроса
			if(isset($_GET['from_bill'])) $from_bill=abs((int)$_GET['from_bill']);
			else $from_bill=0;
			
			if(isset($_GET['to_page_bill'])) $to_page_bill=abs((int)$_GET['to_page_bill']);
			else $to_page_bill=ITEMS_PER_PAGE;
			
			$decorator=new DBDecorator;
			
			
			
			
			
			$decorator->AddEntry(new SqlEntry('p.org_id',abs((int)$result['org_id']), SqlEntry::E));
			$decorator->AddEntry(new SqlEntry('p.inventory_id',$editing_user['id'], SqlEntry::E));
			
			
			
			//блок фильтров статуса
			/*$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^bill_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^bill_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_bill_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$decorator->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			 
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_status_id_'.$v,1));
				$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
				$status_ids=array();
			$cou_stat=0;   
			if(isset($_GET['bill_statuses'])&&is_array($_GET['bill_statuses'])) $cou_stat=count($_GET['bill_statuses']);
			if($cou_stat>0){
			  //есть гет-запросы	
			  $status_ids=$_GET['bill_statuses'];
			  
			}else{
			  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $cou_stat++;
			  
			  if($cou_stat>0){
				  //есть кукисы
				  foreach($_COOKIE as $k=>$v) if(eregi('^kom_bill_status_id_', $k)) $status_ids[]=(int)eregi_replace('^kom_bill_status_id_','',$k);
			  }else{
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }
		  }
		   
			 if(count($status_ids)>0){
				  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
				  
				  if($of_zero){
					  //ничего нет - выбираем ВСЕ!	
					  $decorator->AddEntry(new UriEntry('all_statuses',1));
				  }else{
				  
					  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
					  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
					   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('bill_statuses[]',$v));
				  }
			  } 
			



			
			if(!isset($_GET['pdate_bill1'])){
			
					$_pdate_bill1=DateFromdmY('01.07.2012'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
					$pdate_bill1=date("d.m.Y", $_pdate_bill1);//"01.01.2006";
				
			}else $pdate_bill1 = $_GET['pdate_bill1'];
			
			
			
			if(!isset($_GET['pdate_bill2'])){
					
					$_pdate_bill2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate_bill2=date("d.m.Y", $_pdate_bill2);//"01.01.2006";	
			}else $pdate_bill2 = $_GET['pdate_bill2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate_bill1), SqlEntry::BETWEEN,DateFromdmY($pdate_bill2)));
			$decorator->AddEntry(new UriEntry('pdate_bill1',$pdate_bill1));
			$decorator->AddEntry(new UriEntry('pdate_bill2',$pdate_bill2));
			
			
			
			
			if(isset($_GET['code_bill'])&&(strlen($_GET['code_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('code_bill',$_GET['code_bill']));
			}
			
			if(isset($_GET['name_bill'])&&(strlen($_GET['name_bill'])>0)){
				$decorator->AddEntry(new SqlEntry('p.name',SecStr($_GET['name_bill']), SqlEntry::LIKE));
				$decorator->AddEntry(new UriEntry('name_bill',$_GET['name_bill']));
			}
			
			
			
			if(isset($_GET['supplier_name_bill'])&&(strlen($_GET['supplier_name_bill'])>0)){
		$decorator->AddEntry(new SqlEntry('sp.full_name',SecStr($_GET['supplier_name_bill']), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('supplier_name_bill',$_GET['supplier_name_bill']));
	}
			
			
						
			//сортировку можно подписать как дополнительный параметр для UriEntry
			if(!isset($_GET['sortmode_bill'])){
				$sortmode_bill=0;	
			}else{
				$sortmode_bill=abs((int)$_GET['sortmode_bill']);
			}
			
			
			switch($sortmode_bill){
				case 0:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;
				case 1:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
				break;
				case 2:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
				break;	
				case 3:
					$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
				break;
				
				case 4:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
				break;	
				case 5:
					$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
				break;
				case 6:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::DESC));
				break;	
				case 7:
					$decorator->AddEntry(new SqlOrdEntry('storage_name',SqlOrdEntry::ASC));
				break;
				case 8:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
					
				break;	
				case 9:
					$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
					
				break;
				case 10:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::DESC));
					
				break;	
				case 11:
					$decorator->AddEntry(new SqlOrdEntry('confirmed_price_name',SqlOrdEntry::ASC));
					
				break;
				
				default:
					$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				break;	
				
			}
			//$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
			
			$decorator->AddEntry(new UriEntry('sortmode_bill',$sortmode_bill));
			
			$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$decorator->AddEntry(new UriEntry('action',1));
			$decorator->AddEntry(new UriEntry('id',$editing_user['id']));
			
			
			$bg->SetAuthResult($result);
			
			$llg='<h2>Связанные исходящие счета</h2>'.$bg->ShowPos('bills/bills_list_komplekt.html',$decorator,$from,$to_page, $au->user_rights->CheckAccess('w',92), $au->user_rights->CheckAccess('w',93)||$au->user_rights->CheckAccess('w',283), $au->user_rights->CheckAccess('w',94),'_bill',$au->user_rights->CheckAccess('w',95),$au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',131),$limited_sector, NULL, $au->user_rights->CheckAccess('w',195),$au->user_rights->CheckAccess('w',196), $au->user_rights->CheckAccess('w',197));
			
			
			
			
			
			
			
			
			//связанные поступления
			//вывод поступлений
			$_acg=new AccGroup;
			
			$_acg->SetPagename('ed_inv.php');
			$_acg->SetIdName('p.inventory_id');
			
			$dec2=new DBDecorator;
			
			
			 //блок фильтров статуса
			 //блок фильтров статуса
			/*$status_ids=array();
			$cou_stat=0; foreach($_GET as $k=>$v) if(eregi('^acc_status_id_', $k)) $cou_stat++;
			if($cou_stat>0){
				//есть гет-запросы	
				
				foreach($_GET as $k=>$v) if(eregi('^acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_status_id_','',$k);
			}else{
				$cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $cou_stat++;
				
				if($cou_stat>0){
					//есть кукисы
					foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_acc_status_id_','',$k);
				}else{
					//ничего нет - выбираем ВСЕ!	
					$dec2->AddEntry(new UriEntry('all_statuses',1));
				}
			}
			
			//print_r($status_ids);
			if(count($status_ids)>0){
				foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('acc_status_id_'.$v,1));
				$dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			}*/
			
			$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET['acc_statuses'])&&is_array($_GET['acc_statuses'])) $cou_stat=count($_GET['acc_statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET['acc_statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^acc_acc_status_id_', $k)) $status_ids[]=(int)eregi_replace('^acc_acc_status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $dec2->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $dec2->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('status_id_'.$v,1));
				  $dec2->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $dec2->AddEntry(new UriEntry('acc_statuses[]',$v));
			  }
		  } 
			
			$dec2->AddEntry(new UriEntry('to_page_bill',$to_page_bill));$decorator->AddEntry(new UriEntry('to_page_bill',$to_page_bill));
			
			$_acg->SetAuthResult($result);
			$llg.='<h2>Связанные реализации</h2>'.$_acg->ShowPos($editing_user['id'],'acc/acc_list.html', $dec2, $au->user_rights->CheckAccess('w',235),  $au->user_rights->CheckAccess('w',242), $au->user_rights->CheckAccess('w',240), $au->user_rights->CheckAccess('w',96),true,false,$au->user_rights->CheckAccess('w',243),$limited_sector,NULL,$au->user_rights->CheckAccess('w',241) );
			
			$sm->assign('is',$llg);	
			
			
			
			
			
			
			
		}else{
			
		
			
			$sm->assign('is',''); 
			
			$sm->assign('accs','В данном режиме просмотр связанных документов по распоряжению на инвентаризацию недоступен.<br />
 Пожалуйста,  проставьте галочку "Утверждаю коррекцию складских остатков" и нажмите кнопку "Сохранить и остаться" на вкладке "Распоряжение на инвентаризацию" для получения возможности просмотра связанных документов.');
			
		}
		
		if(!isset($_GET['do_show_log'])){
			$do_show_log=false;
		}else{
			$do_show_log=true;
		}
		$sm->assign('do_show_log',$do_show_log);
		
		if(!isset($_GET['do_show_shi'])){
			$do_show_shi=false;
		}else{
			$do_show_shi=true;
		}
		$sm->assign('do_show_shi',$do_show_shi);
		
		if(!isset($_GET['do_show_acc'])){
			$do_show_acc=false;
		}else{
			$do_show_acc=true;
		}
		$sm->assign('do_show_acc',$do_show_acc);
		
		
			
		
		
		$sm->assign('has_is', true); //($editing_user['is_confirmed_shipping']==1));
		
		
		
		$sm->assign('has_acc', true);	
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',322)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',326)); 
		//$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',129)); 
		
		
		$user_form=$sm1->fetch('inv/inv_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',530)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(321,
322,
323,
324,
325,
326,
327,
346,
328,
329,
330,
331,
332,
333,
334,
335,
336,
337)));
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_inv.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	$sm->assign('from_begin',$from_begin);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$content=$sm->fetch('inv/ed_inv_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else echo $content;
	
	unset($smarty);


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