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
require_once('classes/posdimitem.php');

require_once('classes/user_s_item.php');
require_once('classes/user_s_group.php');


require_once('classes/acc_group.php');
require_once('classes/acc_item.php');


require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');
require_once('classes/sectoritem.php');


require_once('classes/acc_notesgroup.php');
require_once('classes/acc_notesitem.php');

require_once('classes/maxformer.php');

require_once('classes/propisun.php');

require_once('classes/period_checker.php');

require_once('classes/pergroup.php');


require_once('classes/supcontract_item.php');
require_once('classes/payforaccgroup.php');

require_once('classes/komplitem.php');

require_once('classes/payforaccgroup.php');

require_once('classes/supplier_ruk_item.php');
require_once('classes/accsync.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование реализации товара');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['force_print'])){
	if(!isset($_POST['force_print'])){
		$force_print=0;
	}else $force_print=abs((int)$_POST['force_print']); 
}else $force_print=abs((int)$_GET['force_print']);




if(!isset($_GET['printmode'])){
	if(!isset($_POST['printmode'])){
		$printmode=0;
	}else $printmode=abs((int)$_POST['printmode']); 
}else $printmode=abs((int)$_GET['printmode']);


$_acc=new AccItem;


$_bill=new BillItem;
$_bpi=new BillPosItem;
$_position=new PosItem;
$ui=new KomplItem;
$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;
$_posgroupgroup=new PosGroupGroup;

$_supgroup=new SuppliersGroup;

$_sector=new SectorItem;


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


if($action==0){
	
	
	
	if(!isset($_GET['bill_id'])){
		if(!isset($_POST['bill_id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $bill_id=abs((int)$_POST['bill_id']);	
	}else $bill_id=abs((int)$_GET['bill_id']);
	
	//проверка наличия s4eta
	$bill=$_bill->GetItemById($bill_id);
	if($bill===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	
	 
	
}elseif(($action==1)||($action==2)){
	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	
	//запомним данные для печати
	/*if(isset($_GET['print'])&&($_GET['print']==1)&&(isset($_GET['print_pdate'])||isset($_GET['print_no']))){
		$_acc->Edit($id, array('print_pdate'=>datefromdmy($_GET['print_pdate']), 'print_no'=>SecStr($_GET['print_no'])));
	}*/
	
	//проверка наличия пользователя
	$editing_user=$_acc->GetItemByFields(array('id'=>$id, 'is_incoming'=>0));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	$bill_id=$editing_user['bill_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
	
	
	
	
}



$object_id=array();
switch($action){
	case 0:
	$object_id[]=229;
	break;
	case 1:
	$object_id[]=235;  //235;
	$object_id[]=286;
	break;
	case 2:
	$object_id[]=242;
	break;
	default:
	$object_id[]=229;
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
$_editable_status_id[]=4;


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',286)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}



//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
	$log->PutEntry($result['id'],'открыл карту реализации',NULL,235, NULL, 'реализация № '.$editing_user['id'],$id);
	else
	$log->PutEntry($result['id'],'открыл карту реализации: версия для печати',NULL,286, NULL, 'реализация № '.$editing_user['id'],$id);
				
}



if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',229)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=$bill['org_id']; //abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));;
	
	$params['given_pdate']=DateFromdmY($_POST['given_pdate']);
	$params['bill_id']=abs((int)$_POST['bill_id']);
	
	//$params['sh_i_id']=abs((int)$_POST['sh_i_id']);
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	
	$params['sector_id']=abs((int)$_POST['sector_id']);
	
	$params['is_confirmed']=0;
	
	$params['manager_id']=$result['id'];
	
	$params['change_high_mode']=abs((int)$_POST['change_high_mode']);
	$params['change_low_mode']=abs((int)$_POST['change_low_mode']);
	
	
	
	$code=$_acc->Add($params);
	
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал реализацию товара по исходящему счету',NULL,93,NULL,NULL,$params['bill_id']);	
		
	//	$log->PutEntry($result['id'],'создал реализацию товара по исходящему счету',NULL,219,NULL,NULL,$params['sh_i_id']);	
		
		$log->PutEntry($result['id'],'создал реализацию товара по исходящему счету',NULL,229,NULL,NULL,$code);	
		
		
		foreach($params as $k=>$v){
			
		 $log->PutEntry($result['id'],'создал реализацию товара по исходящему счету',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v,$params['bill_id']);	
				 
				
				$log->PutEntry($result['id'],'создал реализацию товара по исходящему счету',NULL,229, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',229))){
		//позиции
		$positions=array();
		
		
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		$_kpi=new KomplPosItem;
		/*foreach($_POST as $k=>$v){
			
			if(eregi("^new_position_id_([0-9]+)",$k)){
				
				$pos_id=abs((int)eregi_replace("^new_position_id_","",$k));*/
		foreach($_POST as $k=>$v){
			if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
			  $hash=eregi_replace("^new_hash_","",$k);
			  
			  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			  $komplekt_ved_id=abs((int)$_POST['new_komplekt_ved_id_'.$hash]);
			 
				
				if($_POST['new_has_pm_'.$hash]==0) $pms=NULL;
				else{
					$pms=array(
						'plus_or_minus'=>abs((int)$_POST['new_plus_or_minus_'.$hash]),
						'rub_or_percent'=>abs((int)$_POST['new_rub_or_percent_'.$hash]),
						'value'=>(float)str_replace(",",".",$_POST['new_value_'.$hash])
					);	
				}
				$dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
				
				//$komplekt_ved_i =?
				
				$kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$komplekt_ved_id, 'position_id'=>$pos_id));
				
				$pos=$_pos->GetItemById($pos_id);
				
				$positions[]=array(
					'acceptance_id'=>$code,
					'komplekt_ved_pos_id'=>(int)$kpi['id'],
					'position_id'=>$pos_id,
					'name'=>SecStr($pos['name']),
					'dimension'=>SecStr($dimension['name']),
					'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
					'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
					'price_pm'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash]),
					'total'=>(float)str_replace(",",".",$_POST['new_total_'.$hash]),
					'komplekt_ved_id'=>(int)$_POST['new_komplekt_ved_id_'.$hash],
					'acceptance_in_id'=>(int)$_POST['new_acceptance_in_id_'.$hash],
					'pms'=>$pms
				);
			}
		}
		//print_r($_POST);
		//print_r($positions);
		//die();
		//внесем позиции
		$_acc->AddPositions($code,$positions,0,0); //abs((int)$_POST['change_high_mode']),abs((int)$_POST['change_low_mode']));
		
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'].'<br /> цена '.$v['price'].' руб. <br />';
				if($v['pms']!==NULL){
					if($v['pms']['plus_or_minus']==0){
						$descr.=' + ';	
					}else{
						$descr.=' - ';	
					}
					$descr.=$v['pms']['value'];
					if($v['pms']['rub_or_percent']==0){
						$descr.=' руб. ';	
					}else{
						$descr.=' % ';	
					}
				}
				
				$log->PutEntry($result['id'],'добавил позицию реализации товара', NULL, 93,NULL,$descr,$bill_id);
				
				$log->PutEntry($result['id'],'добавил позицию реализации товара', NULL, 229,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: ed_bill.php?action=1&id=".$bill_id."#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',235)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_acc.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: ed_bill.php?action=1&id=".$bill_id);
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',235)){
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
		
		
		
		//$params['notes']=SecStr($_POST['notes']);
		if(isset($_POST['given_pdate'])) if(strlen($_POST['given_pdate'])==10) $params['given_pdate']=DateFromdmY($_POST['given_pdate']);
		
		
		$params['given_no']=SecStr($_POST['given_no']);
		
		$params['change_high_mode']=abs((int)$_POST['change_high_mode']);
		$params['change_low_mode']=abs((int)$_POST['change_low_mode']);
	
		
		
		$_acc->Edit($id, $params,false,$result);
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				if($k=='given_pdate'){
					$log->PutEntry($result['id'],'редактировал заданную дату с/ф',NULL,93,NULL,'дата: '.$_POST['given_pdate'],$bill_id);
					
					 
					
					$log->PutEntry($result['id'],'редактировал заданную дату с/ф',NULL,235,NULL,'дата: '.$_POST['given_pdate'],$id);
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'редактировал реализацию товара',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v,$bill_id);
				
				 
				$log->PutEntry($result['id'],'редактировал реализацию товара',NULL,235, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
				
			}
			
		}
		
		if($au->user_rights->CheckAccess('w',235)){
		  $positions=array();
		  
		  $_pos=new PosItem;
		  $_pdi=new PosDimItem;
		  $_kpi=new KomplPosItem;
		  foreach($_POST as $k=>$v){
			  
		 
			if(eregi("^new_hash_([0-9a-z]+)",$k)){
			  
				  $hash=eregi_replace("^new_hash_","",$k);
				  
				  $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
				  $komplekt_ved_id=abs((int)$_POST['new_komplekt_ved_id_'.$hash]);
				  
				  
				  if($_POST['new_has_pm_'.$hash]==0) $pms=NULL;
				  else{
					  $pms=array(
						  'plus_or_minus'=>abs((int)$_POST['new_plus_or_minus_'.$hash]),
						  'rub_or_percent'=>abs((int)$_POST['new_rub_or_percent_'.$hash]),
						  'value'=>(float)str_replace(",",".",$_POST['new_value_'.$hash])
					  );	
				  }
				  $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
				  
				  
				  $kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$komplekt_ved_id, 'position_id'=>$pos_id));
				  $pos=$_pos->GetItemById($pos_id);
				  $positions[]=array(
					  'acceptance_id'=>$id,
					  'komplekt_ved_pos_id'=>(int)$kpi['id'],
					  'position_id'=>$pos_id,
					  'name'=>SecStr($pos['name']),
					  'dimension'=>SecStr($dimension['name']),
					  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
					  'price'=>(float)str_replace(",",".",$_POST['new_price_'.$hash]),
					  'price_pm'=>(float)str_replace(",",".",$_POST['new_price_pm_'.$hash]),
					  'total'=>(float)str_replace(",",".",$_POST['new_total_'.$hash]),
					  'komplekt_ved_id'=>(int)$_POST['new_komplekt_ved_id_'.$hash],
					  'acceptance_in_id'=>(int)$_POST['new_acceptance_in_id_'.$hash],
					  'pms'=>$pms
				  );
				  
				  //print_r($pms);
			  }
		  }
		  //print_r($_POST);
		  //print_r($positions);
		  //внесем позиции
		  //die();
		  
		  $log_entries=$_acc->AddPositions($id,$positions,0,0); //abs((int)$_POST['change_high_mode']),abs((int)$_POST['change_low_mode']));
		  
		  //var_dump($log_entries); die();
		  
		  //выводим в журнал сведения о редактировании позиций
		  foreach($log_entries as $k=>$v){
			  $description=SecStr($v['name']).' <br /> конечное кол-во: '.$v['quantity'].'<br /> '.'Цена '.$v['price'].' руб. <br />';
			  if($v['pms']!==NULL){
				  $description.='<br /> ';
				  if($v['pms']['plus_or_minus']==0){
					  $description.=' + ';
				  }else{
					  $description.=' - ';
				  }
				  $description.=$v['pms']['value'];
				  if($v['pms']['rub_or_percent']==0){
					  $description.=' руб. ';
				  }else{
					  $description.=' % ';
				  }
			  }
			  
			  if($v['action']==0){
				  $log->PutEntry($result['id'],'добавил позицию реализации товара',NULL,93,NULL,$description,$bill_id);
				  
				  
				  $log->PutEntry($result['id'],'добавил позицию реализации товара',NULL,235,NULL,$description,$id);
				  
				  	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию реализации товара',NULL,93,NULL,$description.' первичное кол-во: '.$v['old_quantity'],$bill_id);
				  
				  $log->PutEntry($result['id'],'редактировал позицию реализации товара',NULL,219,NULL,$description.' первичное кол-во: '.$v['old_quantity'], $editing_user['sh_i_id']);
				  
				  $log->PutEntry($result['id'],'редактировал позицию реализации товара',NULL,235,NULL,$description.' первичное кол-во: '.$v['old_quantity'],$id);
				  
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию реализации товара',NULL,93,NULL,$description,$bill_id);
				  
				   
				  
				  $log->PutEntry($result['id'],'удалил позицию реализации товара',NULL,235,NULL,$description,$id);
				  
			  }
			  
		  }
		}
		
		
	}
	
	
	
	//утверждение 
	if($editing_user['is_confirmed']==1){
		//есть права :
		$can=true;
		$bill_has_pms=$_acc->ParentBillHasPms($id, $editing_user);
		if(!$bill_has_pms||($editing_user['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',241);
		else $can=$can&&$au->user_rights->CheckAccess('w',721);
		
		if($can){
			if(!isset($_POST['is_confirmed'])&&($editing_user['status_id']==5)&&($_POST['current_status_id']==5)){
				
				
				
				$_acc->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,93, NULL, NULL,$bill_id);
			 
				$log->PutEntry($result['id'],'снял утверждение реализации товара',NULL,721, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',240)||$au->user_rights->CheckAccess('w',96)){
			if(isset($_POST['is_confirmed'])&&($editing_user['status_id']==4)&&($_POST['current_status_id']==4)){
				
				
				$_acc->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил реализацию товара',NULL,93, NULL, NULL,$bill_id);	
				
				 
				$log->PutEntry($result['id'],'утвердил реализацию товара',NULL,240, NULL, NULL,$id);	
				
				
					
			}
		}else{
			//do nothing
		}
	}
	
	
	
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: ed_bill.php?action=1&id=".$bill_id.'&do_show_acc=1');
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',235)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_acc.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: ed_bill.php?action=1&id=".$bill_id.'&do_show_acc=1');
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
//elseif(($printmode==0)||($printmode==1))  $smarty->display('top_print_alb.html');
//else $smarty->display('top_print.html');
unset($smarty);


$_menu_id=34;
		if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	if($action==0){
		//создание позиции
		
		$sm1=new SmartyAdm;
		$sm1->assign('now',date("d.m.Y"));
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($orgitem['opf_id']);
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		
		$sm1->assign('bill_id',$bill_id);
		 
		
		//поставщик
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($supplier['opf_id']);
		
		
		
		
		$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
		
		$sm1->assign('sdelka_string', 'Исходящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		
		//дог-р
		$_sci=new SupContractItem;
		$sci=$_sci->GetItemById($bill['contract_id']);
		$sm1->assign('contract_no', $sci['contract_no']);
		$sm1->assign('contract_pdate', $sci['contract_pdate']);
		
				//склад
		$sector=$_sector->GetItemById($bill['sector_id']); //$bill['storage_id']);
		$sm1->assign('sector_id_string' ,$sector['name']);
		$sm1->assign('sector_id' ,$bill['sector_id']);
		
		
		
		//передать позиции...
		$positions=array();
		$_bpf1=new BillPosPMFormer;
		
		$_mf=new MaxFormer;
		
		$total_cost=0; $total_nds=0; $was_in=false;
		
		
		foreach($_GET as $k=>$v){
			//print_r($v);
			//сделать оптимизацию входных данных из счета
			if(eregi("^to_ship_",$k)){
				
				$_t_arr=explode(';',$v);
				
				/*$pos_id=abs((int)eregi_replace("^to_ship_","",$k));
				$qua=((float)$v);	*/
				$pos_id=$_t_arr[0];
				$qua=$_t_arr[1];
				$pos_komplekt_ved_id=$_t_arr[2];
				$acceptance_in_id=$_t_arr[3];
				
				$input_array[]=array('pos_id'=>$pos_id,'qua'=>$qua,'komplekt_ved_id'=>$pos_komplekt_ved_id, 'acceptance_in_id'=>$acceptance_in_id);
			}
			
			
			
			
		}
		 
		
		
		$_pgg=new PosGroupGroup;
		$harc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		$harg=array();
		$harg[]=SERVICE_CODE;
		foreach($harc as $k=>$v){
			if(!in_array($v['id'],$harg)) $harg[]=$v['id'];
			$harr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($harr2 as $kk=>$vv){
				if(!in_array($vv['id'],$harg))  $harg[]=$vv['id'];
			}
		}
		
		
		$_bpi=new BillPosItem;
		$_acceptance_in_ids=array();
		foreach($input_array as $kk=>$vv){
			$was_in=true;
			
			$pos_id=((int)$vv['pos_id']);
			$qua=((float)$vv['qua']);
			$pos_komplekt_ved_id=((int)$vv['komplekt_ved_id']);
			$acceptance_in_id=((int)$vv['acceptance_in_id']);
			if(!in_array($acceptance_in_id, $_acceptance_in_ids)) $_acceptance_in_ids[]=$acceptance_in_id;
				
				$sql='select p.id as p_id,   p.komplekt_ved_pos_id, p.position_id as id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_pm, p.total, p.komplekt_ved_id,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 cg.group_id			 			 
		
		from bill_position as p 
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join catalog_position as cg on cg.id=p.position_id
		where p.bill_id="'.$bill_id.'" and position_id="'.$pos_id.'" and p.komplekt_ved_id="'.$pos_komplekt_ved_id.'" order by position_name asc, id asc';
		
				//echo $sql.'<br>';
				
				
				
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				$h=mysqli_fetch_array($rs);
				
				//если кол-во равно кол-ву по счету - ставить тотал по счету
				$some_price_pm=$h['price_pm'];
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$bill_id, 'position_id'=>$h['id'], 'storage_id'=>$sh_i['storage_id'], 'sector_id'=>$sh_i['sector_id'], 'komplekt_ved_id'=>$h['komplekt_ved_id']));
				 
				//var_dump($bpi);
					
				if(($bpi!==false)&&($bpi['quantity']==$qua)){
					$h['total']=$bpi['total'];	
					$some_price_pm=round($bpi['total']/$bpi['quantity'],10);	
				}else{
					
					if($bpi!==false){
						$some_price_pm=round($bpi['total']/$bpi['quantity'],10);	
					}
					
					$h['total']=round($some_price_pm*$qua,2);
					
					//echo 'zzz';
				}
				
				//echo $h['total'] ;
				$h['has_pm']=($h['plus_or_minus']!="");
			
				$pm=$_bpf1->Form($h['price'],$qua,$h['has_pm'],$h['plus_or_minus'],$h['value'],$h['rub_or_percent'], $h['price_pm'], $h['total']);
				
				$h['price_pm']=$pm['price_pm'];
				$h['cost']=$pm['cost'];
				$h['total']=$pm['total'];
				
				
				$h['in_bill']=$_mf->MaxInBill($bill_id, $pos_id,$sh_i['storage_id'],$sh_i['sector_id'],$pos_komplekt_ved_id);
				 
				$h['in_acc']=$_mf->MaxInAcc($bill_id, $pos_id,0,$sh_i_id,$sh_i['storage_id'],$sh_i['sector_id'],$pos_komplekt_ved_id, $acceptance_in_id);
				
				
				//обнулим незаполненный плюс/минус
				if($h['plus_or_minus']=="") $h['plus_or_minus']=0;
				if($h['rub_or_percent']=="") $h['rub_or_percent']=0;
				if($h['value']=="") $h['value']=0;
				
				$h['nds_summ']=sprintf("%.2f",($h['total']-$h['total']/((100+NDS)/100)));
				
				$total_cost+=$h['total'];
				$total_nds+=$h['nds_summ'];
				
				//echo $qua;
				
				//print_r($h);
				if($qua>0){
					
					if($h['komplekt_ved_id']!=0) $komplekt_ved_name='Заявка № '.$h['komplekt_ved_id'];
					  else $komplekt_ved_name='-';	
					  
					  
					  
				
				  //всего в соответствующей строке счета
				  $h['max_bill_quantity']=$_mf->MaxInBill($bill_id,$pos_id,$sh_i['storage_id'],$sh_i['sector_id'],$pos_komplekt_ved_id);
				  
				  //всего в соотв. строке заявки
				  $h['max_komplekt_quantity']=$_mf->MaxInKomplekt($h['komplekt_ved_id'],$pos_id);
				  
				  //всего доступно по поступлениям (за вычетом других реализаций)
				  $h['max_incoming_quantity']=$_mf->MaxForAccByAccIn($bill_id, $pos_id, 0, NULL, $sh_i['storage_id'],$sh_i['sector_id'], $h['komplekt_ved_id'],$acceptance_in_id);
					
				  if(in_array($h['group_id'],$harg)) $h['is_usl']=1;
				  else $h['is_usl']=0;	  
					
				  $positions[]=array(
					  'id'=>$pos_id,
					  'hash'=>md5($pos_id.'_'.$h['komplekt_ved_id'].'_'.$acceptance_in_id),
					  'position_name'=>$h['position_name'],
					  'dim_name'=>$h['dim_name'],
					  'acceptance_in_id'=>$acceptance_in_id,
					  'dimension_id'=>$h['dimension_id'],
					  'quantity'=>$qua,
					  'price'=>$h['price'],
					  'price_pm'=>$h['price_pm'],
					  'price_pm_unf'=>$some_price_pm,
					  'has_pm'=>$h['has_pm'],
					  'cost'=>$h['cost'],
					  'total'=>$h['total'],
					  'plus_or_minus'=>$h['plus_or_minus'],
					  'rub_or_percent'=>$h['rub_or_percent'],
					  'value'=>$h['value'],
					  'nds_proc'=>NDS,
					  'nds_summ'=>$h['nds_summ'],
					  'quantity_confirmed'=>$qua,
					  'max_quantity'=>$qua,
					  'in_rasp'=>$h['in_rasp'],
					  'in_bill'=>$h['in_bill'],
					  'in_acc'=>$h['in_acc'],
				  'komplekt_ved_id'=>$h['komplekt_ved_id'],
				  	  'max_bill_quantity'=>$h['max_bill_quantity'],
					  'max_komplekt_quantity'=>$h['max_komplekt_quantity'],
					  'max_incoming_quantity'=>$h['max_incoming_quantity'],
					  'is_usl'=>$h['is_usl'],
					   
				  //'komplekt_ved_name'=>'Заявка № '.$h['komplekt_ved_id'],
				  //'komplekt_ved_name'=>'Заявка № '.$h['komplekt_ved_id'],
						  'komplekt_ved_name'=>$komplekt_ved_name
				  );
				}
				
			//}
		}
		
		/*echo '<pre>';
		print_r($positions);
		echo '</pre>';*/
		//создаем postuplenie по кнопке реестра
		if((count($positions)==0)&&(!$was_in)){
			//echo 'zzzzzzzzzzzzzzzzzzzzzzzz';
		
			
		}
		
		
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		
		
		//подгрузить заданную дату по поступлениям (брать самую позднюю)
		$given_pdate=$_acc->GetGivenPdate($bill['id'],$_acceptance_in_ids);
		if($given_pdate!==NULL) $sm1->assign('given_pdate', date('d.m.Y', $given_pdate));
		
		$sm1->assign('can_given_pdate',$au->user_rights->CheckAccess('w',864));
		
		//получить список поступлений для перечисления при смене даты
		$_acc_ins=$_acc->GetAccIns($bill['id'], $_acceptance_in_ids);
		$acc_ins=''; $__acc_ins=array();
		foreach($_acc_ins as $k=>$v){
			$__acc_ins[]='поступление №'.$v['id'].', заданный № '.$v['given_no'].' от '.$v['given_pdate'];
		}
		$acc_ins=implode('; ', $__acc_ins);
		$sm1->assign('acc_ins',$acc_ins);
		
		
		
		if($bill['status_id']==10){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',234));
		
		$sm1->assign('can_modify',true);
			 
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		$sm1->assign('positions',$positions);
		
		//допустимые доли превышения позиций
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',233));
		$sm1->assign('PPUP',PPUP);
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',229)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',229)); 
		
		//можно ли править количества?
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',229));
		
		$cannot_edit_quantities_reason='';
		if(!$au->user_rights->CheckAccess('w',229)){
			//if(strlen($cannot_edit_quantities_reason)>0) $cannot_edit_quantities_reason.=', ';
			$cannot_edit_quantities_reason.='недостаточно прав для данного действия';
		}
		$sm1->assign('cannot_edit_quantities_reason',$cannot_edit_quantities_reason);
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',229)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',235)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		//для активности кнопки "ред-ть кол-во позиции"
		$sm1->assign('acc', array('is_leading'=>-1));
		
		
		$user_form=$sm1->fetch('acc/acc_create.html');
		
		$origs='В данном режиме указать, есть ли оригиналы документов, невозможно.<br />
Пожалуйста, сохраните и утвердите реализацию для работы с вкладкой "Оригиналы документов".';
	}elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
		
		
		$sm1=new SmartyAdm;
		
		$sm1->assign('force_print', $force_print);
		
		
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($orgitem['opf_id']);
		
		
			//склад
		$sector=$_sector->GetItemById($editing_user['sector_id']); //$bill['storage_id']);
		$sm1->assign('sector_id_string' ,$sector['name']);
		$sm1->assign('sector_id' ,$editing_user['sector_id']);
		
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		
		
			$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActualByPdate($orgitem['id'],date("d.m.Y", $editing_user['given_pdate']), 1);
		$sri_2=$_sri->GetActualByPdate($orgitem['id'], date("d.m.Y",$editing_user['given_pdate']), 2);
		
		
		$orgitem['chief']=$sri_1['fio'];
		$orgitem['print_sign_dir']=$sri_1['sign'];
		
		$orgitem['main_accountant']=$sri_2['fio'];
		$orgitem['print_sign_buh']=$sri_2['sign'];
		
		
		$sm1->assign('print_org' ,$orgitem);
		
		
		//костыль для замены ТД СЯ на ТД "СЯ
		//костыль для замены ТД СЯ на ТД "СЯ
	$orgitem_fact=$orgitem;
	
	
	 
	//$orgitem_fact['full_name']=eregi_replace('Торговый Дом Строительная Ярмарка', 'Торговый Дом "Строительная Ярмарка',$orgitem_fact['full_name']);
	$sm1->assign('print_org_fact' ,$orgitem_fact);
	
	$sm1->assign('print_org_opf' ,$opf);
		
		
		//рекв. орг.
		require_once('classes/bdetailsitem.php');
		$_bd=new BDetailsItem;
		$print_org_bdetail=$_bd->GetBasic($orgitem['id']);
		$sm1->assign('print_org_bdetail' ,$print_org_bdetail);
		
		
		//поставщик
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($supplier['opf_id']);
		$sm1->assign('supplier_id' ,$bill['supplier_id']);
		$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
		$sm1->assign('print_supplier' ,$supplier);
		$sm1->assign('print_supplier_opf' ,$opf);
		
		
			$_sri=new SupplierRukItem;
		$sri_3=$_sri->GetActualByPdate($supplier['id'],date("d.m.Y", $editing_user['given_pdate']), 1);
		$sri_4=$_sri->GetActualByPdate($supplier['id'], date("d.m.Y",$editing_user['given_pdate']), 2);
		
		$sm1->assign('print_supplier_chief' ,$sri_3['fio']);
		$sm1->assign('print_supplier_main_accountant' ,$sri_4['fio']);
		
		 
		 
		
		
		//покупатель=грузополучатель?
		$sm1->assign('suppliers_are_equal', $bill['suppliers_are_equal']);
		//грузополучатель
	 	$supplier1=$_supplier->GetItemById($bill['ship_supplier_id']);
		$opf1=$_opf->GetItemById($supplier1['opf_id']);
		$sm1->assign('ship_supplier_id' ,$bill['ship_supplier_id']);
		$sm1->assign('ship_supplier_id_string' ,$opf1['name'].' '.$supplier1['full_name']);
		$sm1->assign('print_ship_supplier' ,$supplier1);
		$sm1->assign('print_ship_supplier_opf' ,$opf1);
		
		
		//реквизиты для печати
		require_once('classes/bdetailsitem.php');
		$_bdetail=new BDetailsItem;
		$bdetail=$_bdetail->GetItemById($bill['bdetails_id']);
		$sm1->assign('print_bdetail' ,$bdetail);
		
		
		$sm1->assign('sdelka_string', 'Исходящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		
		//дог-р
		$_sci=new SupContractItem;
		$sci=$_sci->GetItemById($bill['contract_id']);
		$sm1->assign('contract_no', $sci['contract_no']);
		$sm1->assign('contract_pdate', $sci['contract_pdate']);
		
		
		
		//даты
		$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
		
		
		
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		if($editing_user['given_pdate']>0){
			$sm1->assign('given_pdate_date',date('d',$editing_user['given_pdate']));
			$m='';
			switch(date('m',$editing_user['given_pdate'])){
				case 1:
					$m='января';
				break;
				case 2:
				$m='февраля';
				break;
				case 3:
				$m='марта';
				break;
				case 4:
				$m='апреля';
				break;
				case 5:
				$m='мая';
				break;
				case 6:
				$m='июня';
				break;
				case 7:
				$m='июля';
				break;
				case 8:
				$m='августа';
				break;
				case 9:
				$m='сентября';
				break;
				case 10:
				$m='октября';
				break;
				case 11:
				$m='ноября';
				break;
				case 12:
				$m='декабря';
				break;	
				
			}
		
		
			$sm1->assign('given_pdate_month',$m);
			$sm1->assign('given_pdate_year',date('Y',$editing_user['given_pdate']));
		}
		
		$editing_user['given_pdate_unf']=$editing_user['given_pdate'];
		
		if($editing_user['given_pdate']>0) $editing_user['given_pdate']=date("d.m.Y",$editing_user['given_pdate']);
		else $editing_user['given_pdate']='-';
		
		if($editing_user['print_pdate']>0) $editing_user['print_pdate']=date("d.m.Y",$editing_user['print_pdate']);
		else $editing_user['print_pdate']='-';
		
		//блок аннулирования
			$editing_user['can_annul']=$_acc->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',242);
			if(!$au->user_rights->CheckAccess('w',242)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
		
		$editing_user['can_restore']=$_acc->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',243);
			if(!$au->user_rights->CheckAccess('w',243)) $reason='недостаточно прав для данной операции';
		

		if($bill['status_id']==10){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',365));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',234));
	
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new AccPosGroup;
		
		if(($print==1)&&($printmode==0)) $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,true,false);
		elseif(($print==1)&&($printmode==2)) $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,false,true);
		else $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0);
		//print_r($bpg);
		
		foreach($bpg as $k=>$v){
			
			$v['price_pm_formatted']=number_format($v['price_pm'],2,'.',' ');
			$v['total_formatted']=number_format($v['total'],2,'.',' ');
			$bpg[$k]=$v;	
		}
		
		$sm1->assign('positions',$bpg);
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
	
		
		require_once('classes/propis.php');
		$_pn=new PropisUn(); $_pp=new Propis;
		$sm1->assign('count_propis',$_pn->propis(count($bpg)));
		
		
		$summa_propis=trim( $_pp->propis(floor($total_cost)));
		
		$summa_propis= mb_convert_case(substr($summa_propis, 0, 1), MB_CASE_UPPER, 'windows-1251').substr($summa_propis, 1,strlen($summa_propis));
		
		$sm1->assign('total_cost_rub_propis',$summa_propis);
		
		 // strtoupper(substr(  $_pp->propis(floor($total_cost)), 1,1)). substr(  $_pp->propis(floor($total_cost)), 2, strlen($_pp->propis(floor($total_cost)))));
		$sm1->assign('total_cost_kop_propis',100*((float)$total_cost-floor($total_cost)));
		
		
		/*
		cols_by_two
		costs_by_two
		nds_sums_by_two
		totals_by_two
		cols_by_all
		costs_by_all
		nds_sums_by_all
		totals_by_all
		
		cols_by_one
		costs_by_one
		nds_sums_by_one
		totals_by_one
		
		*/
		
		$cols_by_two=0;
		$costs_by_two=0;
		$nds_sums_by_two=0;
		$totals_by_two=0;
		
		$cols_by_all=0;
		$costs_by_all=0;
		$nds_sums_by_all=0;
		$totals_by_all=0;
		
		$cols_by_one=0;
		$costs_by_one=0;
		$nds_sums_by_one=0;
		$totals_by_one=0;
		
		$ic=0;
		foreach($bpg as $k=>$v){
			if($ic==0){
				$cols_by_one=$v['quantity'];
				$costs_by_one=$v['total']-$v['nds_summ'];
				$nds_sums_by_one=$v['nds_summ'];
				$totals_by_one=$v['total'];	
			}else{
				$cols_by_two+=$v['quantity'];
				$costs_by_two+=$v['total']-$v['nds_summ'];
				$nds_sums_by_two+=$v['nds_summ'];
				$totals_by_two+=$v['total'];	
			}
			
			$cols_by_all+=$v['quantity'];
			$costs_by_all+=$v['total']-$v['nds_summ'];
			$nds_sums_by_all+=$v['nds_summ'];
			$totals_by_all+=$v['total'];
				
			$ic++;	
		}
		
		
		/*$sm1->assign('cols_by_two',$cols_by_two);
		$sm1->assign('costs_by_two',$costs_by_two);
		$sm1->assign('nds_sums_by_two',$nds_sums_by_two);
		$sm1->assign('totals_by_two',$totals_by_two);*/
	
		
	/*	$sm1->assign('cols_by_one',$cols_by_one);
		$sm1->assign('costs_by_one',$costs_by_one);
		$sm1->assign('nds_sums_by_one',$nds_sums_by_one);
		$sm1->assign('totals_by_one',$totals_by_one);*/
		
		
		//позиции для накладной
		$print_positions=array();
		
		$cter=1; $page=1;
		$cols_by_page=0;
		$costs_by_page=0;
		$nds_sums_by_page=0;
		$totals_by_page=0;
		
		$cols_by_all=0;
		$costs_by_all=0;
		$nds_sums_by_all=0;
		$totals_by_all=0;
		
		$_posdi=new PosDimItem;
		
		foreach($bpg as $k=>$v){
			//1 23
			
			$cols_by_page+=$v['quantity'];
			$costs_by_page+=$v['total']-$v['nds_summ'];
			$nds_sums_by_page+=$v['nds_summ'];
			$totals_by_page+=$v['total'];	
			
			
			$cols_by_all+=$v['quantity'];
			$costs_by_all+=$v['total']-$v['nds_summ'];
			$nds_sums_by_all+=$v['nds_summ'];
			$totals_by_all+=$v['total'];
			
			$posdi=$_posdi->GetItemByFields(array('name'=>$v['dim_name']));
			$v['okei']=$posdi['okei'];
			
			//var_dump($posdi);
			$v['price_pm_wo_nds']=number_format($v['price_pm']-$v['nds_price'],2,'.',' ');
			$v['total_wo_nds']=number_format($v['total']-$v['nds_summ'],2,'.',' ');
			$v['nds_summ_f']=number_format($v['nds_summ'],2,'.',' ');
			$v['total_f']=number_format($v['total'],2,'.',' ');
			
			$v['cols_by_page']=$cols_by_page;
				$v['costs_by_page']=$costs_by_page;
				$v['nds_sums_by_page']=$nds_sums_by_page;
				$v['totals_by_page']=$totals_by_page;
			
			$v['costs_by_page_f']=number_format($costs_by_page,2,'.',' ');
				$v['nds_sums_by_page_f']=number_format($nds_sums_by_page,2,'.',' ');
				$v['totals_by_page_f']=number_format($totals_by_page,2,'.',' ');
			
			$v['break_after']=false;
			if($cter==1){
				
				
				
				$cols_by_page=0;
				$costs_by_page=0;
				$nds_sums_by_page=0;
				$totals_by_page=0;	
				
				$v['break_after']=true;	
				$page++;			
			}elseif(($cter>1)&&(($cter-1)%33==0)){
				
				
				
				
				$cols_by_page=0;
				$costs_by_page=0;
				$nds_sums_by_page=0;
				$totals_by_page=0;	
				
				$v['break_after']=true;	
				$page++;		
					
			}
			$v['page']=$page;
			
			
			
			$print_positions[]=$v;
			$cter++;	
		}
		$sm1->assign('print_positions', $print_positions);
		
			
		$sm1->assign('cols_by_all',$cols_by_all);
		$sm1->assign('costs_by_all',number_format($costs_by_all,2,'.',' '));
		$sm1->assign('nds_sums_by_all',number_format($nds_sums_by_all,2,'.',' '));
		
		$sm1->assign('totals_by_all', number_format($totals_by_all,2,'.',' '));
		
		
		
		//позиции для c/ф
		$print_positions1=array();
		
		$cter=1; $page=1;
		 
		$sm1->assign('to_pay',number_format($totals_by_all-$nds_sums_by_all,2,'.',' '));
		
		foreach($bpg as $k=>$v){
			//1 11
			
		 	
			$posdi=$_posdi->GetItemByFields(array('name'=>$v['dim_name']));
			$v['okei']=$posdi['okei'];
			
			$v['price_pm_wo_nds']=number_format($v['price_pm']-$v['nds_price'],2,'.',' ');
			$v['total_wo_nds']=number_format($v['total']-$v['nds_summ'],2,'.',' ');
			$v['nds_summ_f']=number_format($v['nds_summ'],2,'.',' ');
			$v['total_f']=number_format($v['total'],2,'.',' ');
			
			
			$v['break_after']=false;
			if($cter==8){
				
				
			 
				$v['break_after']=true;	
				$page++;			
			}elseif(($cter>8)&&(($cter-8)%17==0)){
				
				
				
			 
				
				$v['break_after']=true;	
				$page++;		
					
			}
			$v['page']=$page;
			
			
			
			$print_positions1[]=$v;
			$cter++;	
		}
		
		//избегаем висячей подписи...
		//если позиций было от 4 по 8...
		if((count($print_positions1)>=4)&&(count($print_positions1)<=8)){
			//перед последней позицией... т.е. в предпоследней! поставить  $v['break_after']=true;	
			foreach($print_positions1 as $k=>$v){	
				if($k==(count($print_positions1)-2)) $v['break_after']=true;
				$print_positions1[$k]=$v;
			}
		}
		
		//две страницы и более страниц - вычесть 8, вычесть целое число раз по 17, если осталось более 12 - то после 12й позиции поставить разрыв!
		if(count($print_positions1)>8){
			$rest=count($print_positions1)-8-floor( (count($print_positions1)-8)/17)*17;
			if($rest>12){
				$seek=	8+   floor( (count($print_positions1)-8)/17)*17     +12;
				foreach($print_positions1 as $k=>$v){
					if($k==($seek-1)) $v['break_after']=true;
					$print_positions1[$k]=$v;
				}
			}
		}
		
		
		
		$sm1->assign('print_positions1', $print_positions1);
		
		
		//закрепленные входящие оплаты для вывода в с/ф
		$_pac=new PayForAccGroup;
		$binded_payments=$_pac->GetPayForSF($id);
		$sm1->assign('binded_payments', $binded_payments);
		
		
		//Примечания
		$rg=new AccNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',342), $au->user_rights->CheckAccess('w',351),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',236)/*&&($editing_user['is_confirmed']==0)*/);
		
		
		
		//допустимые доли превышения позиций
		//допустимые доли превышения позиций
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',233));
		$sm1->assign('PPUP',PPUP);
		
		
		$sm1->assign('can_given_pdate',$au->user_rights->CheckAccess('w',864));
		
		//получить список поступлений для перечисления при смене даты
		$_acc_ins=$_acc->GetAccIns($editing_user['bill_id']);
		$acc_ins=''; $__acc_ins=array();
		foreach($_acc_ins as $k=>$v){
			$__acc_ins[]='поступление №'.$v['id'].', заданный № '.$v['given_no'].' от '.$v['given_pdate'];
		}
		$acc_ins=implode('; ', $__acc_ins);
		$sm1->assign('acc_ins',$acc_ins);
		
		$sm1->assign('acc',$editing_user);
		
		//возможность РЕДАКТИРОВАНИЯ 
		$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
		
		//блок утверждения!
		if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
			$confirmer='';
			$_user_temp=new UserSItem;
			$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
			$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
			
			$sm1->assign('is_confirmed_confirmer',$confirmer);
		}
		
		//есть ли +/- в род счете
		$bill_has_pms=$_acc->ParentBillHasPms($id, $editing_user);
		$sm1->assign('bill_has_pms', $bill_has_pms);
		
		
		$can_confirm_price=false;
		if($editing_user['is_confirmed']==1){
			$can=true;
			if(!$bill_has_pms||($editing_user['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',241);
			else $can=$can&&$au->user_rights->CheckAccess('w',721);
			
			if($can){
				//есть права 
				$can_confirm_price=true;	
			}else{
				$can_confirm_price=false;
			}
		}else{
			//95
			$can_confirm_price=$au->user_rights->CheckAccess('w',240)&&($editing_user['status_id']==4);
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		//сообщения о невозможности снять вновь выставляемое утверждение + список сотрудников, кто может
		if(($bill_has_pms)&&($editing_user['is_confirmed']==0)&&($editing_user['inventory_id']==0)){
			$_usg=new UsersSGroup;	
			$cannot_unconfirm=!$au->user_rights->CheckAccess('w',721);
			
			$sm1->assign('cannot_unconfirm',$cannot_unconfirm);
			
			$usg_can=$_usg->GetUsersByRightArr('w',721);
			$_usg_can_str=array(); 
			foreach($usg_can as $k=>$v) $_usg_can_str[]=htmlspecialchars($v['name_s'].'');
			
			$usg_can_str=implode(', ', $_usg_can_str);
			//echo "$usg_can_str";
			$sm1->assign('can_unconfirm_users', $usg_can_str);
			
		}
		
		
		
		//testing		
		/*$_pac=new PayForAccGroup; // echo $bill['supplier_id'];
		$pays=$_pac->GetAvans($bill['supplier_id'], $result['org_id'], $editing_user['given_pdate_unf'], NULL, $avans, $pay_ids, $inv_ids);
		
		echo $avans.' ';
		var_dump($pays);
		*/
		/*
		$acc_ids=$_acc->GetLatestAccs($bill['supplier_id'], $result['org_id'], $editing_user['given_pdate_unf']);
		print_r($acc_ids);
		*/
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',235)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',235)); 
		
		
		//можно ли править количества?
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',235)&&($editing_user['is_confirmed']==0));
		$sm1->assign('cannot_select_positions', !($au->user_rights->CheckAccess('w',235)&&($editing_user['is_confirmed']==0)));
		
		$cannot_edit_quantities_reason='';
		if($editing_user['is_confirmed']==1){
			$cannot_edit_quantities_reason='реализация утверждена';
		}
		if(!$au->user_rights->CheckAccess('w',235)){
			if(strlen($cannot_edit_quantities_reason)>0) $cannot_edit_quantities_reason.=', ';
			$cannot_edit_quantities_reason.='недостаточно прав для данного действия';
		}
		
		$sm1->assign('cannot_edit_quantities_reason',$cannot_edit_quantities_reason);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',286)); 
		$sm1->assign('has_usl',$_acc->HasUsl($editing_user['id'])); 
		$sm1->assign('has_tov',$_acc->HasTov($editing_user['id'])); 
		$sm1->assign('printmode', $printmode);
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',229)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',235)); 
		
		$sm1->assign('can_email_pdf',$au->user_rights->CheckAccess('w',861));
		
		
		if(isset($_GET['do_print_sign'])&&($_GET['do_print_sign']==1)){
			$do_print_sign=1;
		}else $do_print_sign=0;
		$sm1->assign('do_print_sign', $do_print_sign);
		
		if(isset($_GET['do_print_summ'])&&($_GET['do_print_summ']==1)){
			$do_print_summ=1;
		}elseif(!isset($_GET['do_print_summ'])) $do_print_summ=1;
		else $do_print_summ=0;
		$sm1->assign('do_print_summ', $do_print_summ);
		
		//$user_form=$sm1->fetch('acc/acc_edit'.$print_add.'.html');
		if($print==0) $user_form=$sm1->fetch('acc/acc_edit'.$print_add.'.html');
				elseif(($printmode==0)&&(($editing_user['org_id']==33))) $user_form=$sm1->fetch('acc/acc_edit_nt'.$print_add.'.html');
				elseif(($printmode==0)&&(($editing_user['org_id']==272))) $user_form=$sm1->fetch('acc/acc_edit_sya'.$print_add.'.html');
		elseif($printmode==0) $user_form=$sm1->fetch('acc/acc_edit'.$print_add.'.html');
		elseif($printmode==1) $user_form=$sm1->fetch('acc/acc_edit_fakt.html');
		elseif($printmode==2) $user_form=$sm1->fetch('acc/acc_edit_akt.html');
		else $user_form=$sm1->fetch('acc/acc_edit_fakt.html');
		
		//var_dump( $_acc->CanConfirmByPositions($id,&$reason));
		//echo $reason;
		
		//echo $_acc->sync->CheckLack($id, $rss, &$lack,'fact_quantity');
		//echo $rss;
		
		//Вкладка Оригиналы документов
		if($editing_user['is_confirmed']==1){
			$sm1=new SmartyAdm;
			$sm1->assign('acc',$editing_user);
			
			if(($editing_user['has_nakl']==1)&&($editing_user['has_nakl_confirm_user_id']!=0)){
				$confirmer='';
				$_user_temp=new UserSItem;
				$_user_confirmer=$_user_temp->GetItemById($editing_user['has_nakl_confirm_user_id']);
				$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['has_nakl_confirm_pdate']);
				
				$sm1->assign('has_nakl_confirmer',$confirmer);
			}
			
			$can_confirm_price=false;
			if($editing_user['has_nakl']==1){
				if($au->user_rights->CheckAccess('w',241)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',240)&&($editing_user['status_id']==5);
			}
			$sm1->assign('can_confirm_has_nakl',$can_confirm_price&&$_acc->HasTov($editing_user['id']));
			
			
			if(($editing_user['has_fakt']==1)&&($editing_user['has_fakt_confirm_user_id']!=0)){
				$confirmer='';
				$_user_temp=new UserSItem;
				$_user_confirmer=$_user_temp->GetItemById($editing_user['has_fakt_confirm_user_id']);
				$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['has_fakt_confirm_pdate']);
				
				$sm1->assign('has_fakt_confirmer',$confirmer);
			}
			
			$can_confirm_price=false;
			if($editing_user['has_fakt']==1){
				if($au->user_rights->CheckAccess('w',241)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',240)&&($editing_user['status_id']==5);
			}
			$sm1->assign('can_confirm_has_fakt',$can_confirm_price);
			
			
			if(($editing_user['has_akt']==1)&&($editing_user['has_akt_confirm_user_id']!=0)){
				$confirmer='';
				$_user_temp=new UserSItem;
				$_user_confirmer=$_user_temp->GetItemById($editing_user['has_akt_confirm_user_id']);
				$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['has_akt_confirm_pdate']);
				
				$sm1->assign('has_akt_confirmer',$confirmer);
			}
			
			$can_confirm_price=false;
			if($editing_user['has_akt']==1){
				if($au->user_rights->CheckAccess('w',241)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',240)&&($editing_user['status_id']==5);
			}
			
			$can_confirm_price=$can_confirm_price&&$_acc->HasUsl($editing_user['id']);
			$sm1->assign('can_confirm_has_akt',$can_confirm_price);
			
			
			
			$origs=$sm1->fetch('acc/acc_origs.html');
		}else{
			$origs='В данном режиме указать, есть ли оригиналы документов, невозможно.<br />
Пожалуйста, утвердите реализацию для работы с вкладкой "Оригиналы документов".';
		}
		
		
		
		
		
		
		
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',526)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(200,
228,
229,
230,
231,
232,
233,
234,
235,
236,
237,
238,
239,
240,
241,
286,
242,
243,
523,
861,
864)));
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
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_acc.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
	
		
	}
	
	
	
	$sm->assign('users',$user_form);
	
	if($au->user_rights->CheckAccess('w',240)) $sm->assign('origs',$origs);
	$sm->assign('can_origs',$au->user_rights->CheckAccess('w',240));
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	
	
	$content=$sm->fetch('acc/ed_acc_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	else{
		// echo $content;
		
		
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		if($printmode==2) $ori=' --orientation Portrait ';
		else $ori=' --orientation Landscape ';
		
		$comand = "wkhtmltopdf-i386 --page-size A4 ".$ori." --encoding windows-1251 --margin-top 5mm --margin-bottom 0mm --margin-left 10mm --margin-right 10mm   ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
	
	
		exec($comand);
	
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Реализация_'.$editing_user['id'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
		
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		 
	}
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
//else $smarty->display('bottom_print.html');
unset($smarty);

//$_acc->UnconfirmBindedDocuments($id);

/*
$_sync=new AccSync2($id, $result['org_id'], $result);

$_sync->Sync();*/
/*$_acc->DocCanUnConfirm($id,  $wrd);
var_dump($wrd);*/
?>