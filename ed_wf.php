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


require_once('classes/posdimitem.php');

require_once('classes/suppliersgroup.php');
require_once('classes/supplieritem.php');

require_once('classes/bdetailsitem.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/billitem.php');
require_once('classes/billpositem.php');
require_once('classes/billposgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');

require_once('classes/sh_i_group.php');

require_once('classes/wfitem.php');
require_once('classes/ispositem.php');
require_once('classes/isposgroup.php');

require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/period_checker.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование распоряжения на списание');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_is=new WfItem;
$_ispi=new IsPosItem;

$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PlPosItem;


$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;

$_supgroup=new SuppliersGroup;

$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);


$object_id=array();
switch($action){
	case 0:
	$object_id[]=105;
	break;
	case 1:
	$object_id[]=106;
	$object_id[]=290;
	break;
	case 2:
	$object_id[]=107;
	break;
	default:
	$object_id[]=105;
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

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',290)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
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
	$editing_user=$_is->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	
	
}


if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',105)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));
	

	$params['is_or_writeoff']=1;
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	$params['is_confirmed']=0;
	$params['manager_id']=$result['id'];
	
	$code=$_is->Add($params);
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал распоряжение на списание',NULL,105,NULL,NULL,$code);	
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',256))){
		//позиции
		$positions=array();
		
		$_pos=new PlPosItem;
		$_pdi=new PosDimItem;

		foreach($_POST as $k=>$v){
			
			if(eregi("^new_position_id_([0-9]+)",$k)){
				
				$pos_id=abs((int)eregi_replace("^new_position_id_","",$k));
				
				
				$dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$pos_id]));
				
				$pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$pos_id]));
				$positions[]=array(
					'interstore_id'=>$code,
					
					'position_id'=>$pos_id,
					'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$pos_id]),
					'name'=>SecStr($pos['name']),
					'dimension'=>SecStr($dimension['name']),
					'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$pos_id])),
					'price'=>0
				);
			}
		}
		//print_r($_POST);
		//print_r($positions);
		//внесем позиции
		$_is->AddPositions($code,$positions);
		
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['pl_position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'];
				$log->PutEntry($result['id'],'добавил позицию распоряжения на списание', NULL, 106,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: writeoff.php#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',106)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_wf.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: writeoff.php");
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',106)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	$condition=true;
	$condition=in_array($_POST['current_status_id'],$_editable_status_id)&&in_array($editing_user['status_id'],$_editable_status_id);
	
	if($condition){
		$params=array();
		//обычная загрузка прочих параметров
		
			
		$params['given_no']=SecStr($_POST['given_no']);
	
		
		$_is->Edit($id, $params);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				
				$log->PutEntry($result['id'],'редактировал распоряжение на списание',NULL,106, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
						
			}
			
			
		}
		
		if($au->user_rights->CheckAccess('w',256)){
		
		  $positions=array();
		  
		  $_pos=new PlPosItem;
		  $_pdi=new PosDimItem;
		 
		  foreach($_POST as $k=>$v){
			  
			  if(eregi("^new_position_id_([0-9]+)",$k)){
				  
				  $pos_id=abs((int)eregi_replace("^new_position_id_","",$k));
				  
				  
				  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$pos_id]));
				  //$kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>abs((int)$_POST['komplekt_ved_id']), 'position_id'=>$pos_id));
				  $pos=$_pos->GetItemById(abs((int)$_POST['new_pl_position_id_'.$pos_id]));
				  $positions[]=array(
					  'interstore_id'=>$id,
					  'position_id'=>$pos_id,
					'pl_position_id'=>abs((int)$_POST['new_pl_position_id_'.$pos_id]),
					'name'=>SecStr($pos['name']),
					'dimension'=>SecStr($dimension['name']),
					'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$pos_id])),
					'price'=>0
				  );
				  
				  //print_r($pms);
			  }
		  }
		  //print_r($_POST);
		  //print_r($positions);
		  //внесем позиции
		  //die();
		  
		  $log_entries=$_is->AddPositions($id,$positions);
		  
		  //предусмотреть блок удаления совсем удаленных у товара позиций!
		  // перенесено в функцию AddPositions!!!
		  
		  
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  $description=SecStr($v['name']).' <br /> Кол-во: '.$v['quantity'];
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию распоряжения на списание',NULL,106,NULL,$description,$id);	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию распоряжения на списание',NULL,106,NULL,$description,$id);
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию распоряжения на списание',NULL,106,NULL,$description,$id);
			  }
			  
		  }
		}
		
	}
	
	
	//утверждение заполнения
	if($editing_user['is_confirmed']==0){
	  if($editing_user['is_confirmed_fill_wf']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',263))||$au->user_rights->CheckAccess('w',109)){
			  if(!isset($_POST['is_confirmed_fill_wf'])&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2))){
				  $_is->Edit($id,array('is_confirmed_fill_wf'=>0, 'user_confirm_fill_wf_id'=>$result['id'], 'confirm_fill_wf_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'снял утверждение заполнения распоряжения на списание',NULL,263, NULL, NULL,$id);	
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',108)||$au->user_rights->CheckAccess('w',109)){
			  if(isset($_POST['is_confirmed_fill_wf'])&&in_array($editing_user['status_id'], array(1,2))&&in_array($_POST['current_status_id'], array(1,2))){
				  $_is->Edit($id,array('is_confirmed_fill_wf'=>1, 'user_confirm_fill_wf_id'=>$result['id'], 'confirm_fill_wf_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'утвердил заполнение распоряжения на списание',NULL,108, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	//утверждение списания
	if($editing_user['is_confirmed_fill_wf']==1){
	  if($editing_user['is_confirmed']==1){
		  //есть права: либо сам утв.+есть права, либо есть искл. права:
		  if(($au->user_rights->CheckAccess('w',265))||$au->user_rights->CheckAccess('w',109)){
			  if((!isset($_POST['is_confirmed'])) &&in_array($editing_user['status_id'], array(1,2,17))&&in_array($_POST['current_status_id'], array(1,2,17))){
				  $_is->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'снял утверждение списания',NULL,265, NULL, NULL,$id);	
			  }
		  }else{
			  //нет прав	
		  }
		  
	  }else{
		  //есть права
		  if($au->user_rights->CheckAccess('w',264)||$au->user_rights->CheckAccess('w',109)){
			  if(isset($_POST['is_confirmed'])&&in_array($editing_user['status_id'], array(2))&&in_array($_POST['current_status_id'], array(2))){
				  $_is->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				  
				  $log->PutEntry($result['id'],'утвердил списание',NULL,264, NULL, NULL,$id);	
					  
			  }
		  }else{
			  //do nothing
		  }
	  }
	}
	
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: writeoff.php#user_".$id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',106)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_wf.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: writeoff.php");
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



	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		$sm1->assign('org', stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',256)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',258)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',105)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',106)); 
		
		$user_form=$sm1->fetch('wf/wf_create.html');
	}elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		
		
		
		$sm1=new SmartyAdm;
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'].' ('.$cu['login'].')';
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		//блок аннулирования
			$editing_user['can_annul']=$_is->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',107);
			if(!$au->user_rights->CheckAccess('w',107)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
		
			$editing_user['can_restore']=$_is->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',138);
			if(!$au->user_rights->CheckAccess('w',138)) $reason='недостаточно прав для данной операции';
		
			$editing_user['can_restore_reason']=$reason;
		
		$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
		
		
		$sm1->assign('bill',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
		//$sm1->assign('can_modify',$editing_user['is_confirmed_fill_wf']==0);
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		
		
	
		
		
		//блок утверждения!
		if(($editing_user['is_confirmed_fill_wf']==1)&&($editing_user['user_confirm_fill_wf_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_fill_wf_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['confirm_fill_wf_pdate']);
			
			$sm1->assign('is_confirmed_fill_wf_confirmer',$confirmer);
		}
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed']==0){
		  if($editing_user['is_confirmed_fill_wf']==1){
			  if($au->user_rights->CheckAccess('w',109)){
				  //полные права
				  $can_confirm_price=true;	
			  }elseif($au->user_rights->CheckAccess('w',263)){
				  //есть права + сам утвердил
				  $can_confirm_price=true;	
			  }else{
				  $can_confirm_price=false;
			  }
		  }else{
			  //95
			  $can_confirm_price=$au->user_rights->CheckAccess('w',108)&&in_array($editing_user['status_id'],$_editable_status_id);
		  }
		}
		$sm1->assign('can_confirm_fill_wf',$can_confirm_price);
		
		
		//блок утв. списания
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.$_user_confirmer['login'].' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		
		$can_confirm_wf=false;
		if($editing_user['is_confirmed_fill_wf']==1){
		
		  if($editing_user['is_confirmed']==1){
			  if($au->user_rights->CheckAccess('w',109)){
				  //полные права
				  $can_confirm_wf=true;	
			  }elseif($au->user_rights->CheckAccess('w',265)){
				  //есть права + сам утвердил
				  $can_confirm_wf=true;	
			  }else{
				  $can_confirm_wf=false;
			  }
		  }else{
			  //95
			  $can_confirm_wf=$au->user_rights->CheckAccess('w',264);
		  }
		}
		// + есть галочка утв. цен
		$can_confirm_wf=$can_confirm_wf&&($editing_user['is_confirmed_fill_wf']==1);
		
		
		$sm1->assign('can_confirm',$can_confirm_wf);
		
		
		
		
	
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new IsPosGroup;
		$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		
		//Примечания
		$rg=new IsNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,$editing_user['is_confirmed_fill_wf']==1,$au->user_rights->CheckAccess('w',345),$au->user_rights->CheckAccess('w',345),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',259)/*&&($editing_user['is_confirmed_fill_wf']==0)*/);
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',256)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',258)); 
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',290)); 
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',105)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',106)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		
		$user_form=$sm1->fetch('wf/wf_edit'.$print_add.'.html');
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',529)){
			$sm->assign('has_syslog',true);
			
			$decorator=new DBDecorator;
	
	
		
			if(!isset($_GET['pdate1'])){
			
					$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30;
					$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
				
			}else $pdate1 = $_GET['pdate1'];
			
			
			
			if(!isset($_GET['pdate2'])){
					
					$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
					$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			}else $pdate2 = $_GET['pdate2'];
			
			$decorator->AddEntry(new SqlEntry('pdate',DateFromdmY($pdate1), SqlEntry::BETWEEN,DateFromdmY($pdate2)));
			$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
			
			
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array('99','105','106','107','108','109','138',256,257,258,259,260,261,262,263,264,265)));
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
			//$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log.html',$decorator,$from,$to_page,'ed_wf.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
		
		
	}
	
	
	$sm->assign('users',$user_form);
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	$content=$sm->fetch('wf/ed_wf_page'.$print_add.'.html');
	
	
	
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