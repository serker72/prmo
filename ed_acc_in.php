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


require_once('classes/bill_in_item.php');

require_once('classes/user_s_item.php');
require_once('classes/user_s_group.php');


require_once('classes/acc_group.php');
require_once('classes/acc_item.php');

require_once('classes/acc_in_group.php');
require_once('classes/acc_in_item.php');


require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');
 

require_once('classes/acc_notesgroup.php');
require_once('classes/acc_notesitem.php');

require_once('classes/maxformer.php');

require_once('classes/propisun.php');

require_once('classes/period_checker.php');

require_once('classes/pergroup.php');
require_once('classes/sectoritem.php');


require_once('classes/supcontract_item.php');

require_once('classes/supplier_ruk_item.php');

require_once('classes/PHPExcel.php');



if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);


$smarty = new SmartyAdm;
if($print==0) $smarty->assign("SITETITLE",'Редактирование поступления товара');
else $smarty->assign("SITETITLE",'');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}


if(!isset($_GET['force_print'])){
	if(!isset($_POST['force_print'])){
		$force_print=0;
	}else $force_print=abs((int)$_POST['force_print']); 
}else $force_print=abs((int)$_GET['force_print']);


if(!isset($_GET['force_print_xls'])){
	if(!isset($_POST['force_print_xls'])){
		$force_print_xls=0;
	}else $force_print_xls=abs((int)$_POST['force_print_xls']); 
}else $force_print_xls=abs((int)$_GET['force_print_xls']);




if(!isset($_GET['printmode'])){
	if(!isset($_POST['printmode'])){
		$printmode=0;
	}else $printmode=abs((int)$_POST['printmode']); 
}else $printmode=abs((int)$_GET['printmode']);


$_acc=new AccInItem;
$_sector=new sectoritem;
 
$_bill=new BillInItem;
$_bpi=new BillPosItem;
$_position=new PosItem;

$_supplier=new SupplierItem;
//$lc=new LoginCreator;
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
	
	//проверка наличия пользователя
	$editing_user=$_acc->GetItemByFields(array('id'=>$id, 'is_incoming'=>1));
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	$bill_id=$editing_user['bill_id'];
	
	$bill=$_bill->GetItemById($editing_user['bill_id']);
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);

	if($limited_supplier!==NULL){
		if(!in_array($editing_user['supplier_id'], $limited_supplier)){
			
			//также проверить, чтобы этот счет был связан с исход. счетом от этого п-ка
			$out_bill_ids=array();
			$sql='select id from bill where is_incoming=0 and supplier_id in ('.implode(', ',$limited_supplier).')';
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$out_bill_ids[]=$f['id'];
			}
			
			if(!in_array($editing_user['out_bill_id'], $out_bill_ids)){
			
				header("HTTP/1.1 403 Forbidden");
				header("Status: 403 Forbidden");
				include("403.php");
				die();	
			}
		}
	}
	
}



$object_id=array();
switch($action){
	case 0:
	$object_id[]=661;
	break;
	case 1:
	$object_id[]=664;  //664;
	$object_id[]=673;
	$object_id[]=930;
	break;
	case 2:
	$object_id[]=674;
	break;
	default:
	$object_id[]=661;
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
	if(!$au->user_rights->CheckAccess('w',673)){
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
	$log->PutEntry($result['id'],'открыл карту поступления',NULL,664, NULL, 'поступление № '.$editing_user['id'],$id);
	elseif($print==1)
	$log->PutEntry($result['id'],'открыл карту поступления: версия для печати',NULL,673, NULL, 'поступление № '.$editing_user['id'],$id);
	
	else
	$log->PutEntry($result['id'],'открыл карту поступления: Excel-версия',NULL,930, NULL, 'поступление № '.$editing_user['id'],$id);
				
}





if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',661)){
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
	
	$params['out_bill_id']=abs((int)$_POST['out_bill_id']);
	$params['sector_id']=abs((int)$_POST['sector_id']);
	
	$params['given_no']=SecStr($_POST['given_no']);
	$params['is_incoming']=1;
	
	//$params['notes']=SecStr($_POST['notes']);
	
	$params['is_confirmed']=0;
	
	$params['manager_id']=$result['id'];
	
	$params['change_high_mode']=abs((int)$_POST['change_high_mode']);
	$params['change_low_mode']=abs((int)$_POST['change_low_mode']);
	
	
	
	$code=$_acc->Add($params);
	
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал поступление товара по входящему счету',NULL,613,NULL,NULL,$params['bill_id']);	
		
		//$log->PutEntry($result['id'],'создал поступление товара по входящему счету',NULL,644,NULL,NULL,$params['sh_i_id']);	
		
		$log->PutEntry($result['id'],'создал поступление товара по входящему счету',NULL,661,NULL,NULL,$code);
		
		foreach($params as $k=>$v){
			 
				$log->PutEntry($result['id'],'создал поступление товара по входящему счету',NULL,613, NULL, 'в поле '.$k.' установлено значение '.$v,$params['bill_id']);	
				
				//$log->PutEntry($result['id'],'создал поступление товара по входящему счету',NULL,644, NULL, 'в поле '.$k.' установлено значение '.$v,$params['sh_i_id']);	
				
				$log->PutEntry($result['id'],'создал поступление товара по входящему счету',NULL,661, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
		
			
		
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',661))){
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
					'out_bill_id'=>(int)$_POST['new_out_bill_id_'.$hash],
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
				
				$log->PutEntry($result['id'],'добавил позицию поступления товара', NULL, 613,NULL,$descr,$bill_id);
				
				
				$log->PutEntry($result['id'],'добавил позицию поступления товара', NULL, 661,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: ed_bill_in.php?action=1&id=".$bill_id."#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',664)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_acc_in.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: ed_bill_in.php?action=1&id=".$bill_id);
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',664)){
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
		if(strlen($_POST['given_pdate'])==10) $params['given_pdate']=DateFromdmY($_POST['given_pdate']);
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
					$log->PutEntry($result['id'],'редактировал заданную дату с/ф',NULL,613,NULL,'дата: '.$_POST['given_pdate'],$bill_id);
					
					$log->PutEntry($result['id'],'редактировал заданную дату с/ф',NULL,644,NULL,'дата: '.$_POST['given_pdate'],$editing_user['sh_i_id']);
					
					$log->PutEntry($result['id'],'редактировал заданную дату с/ф',NULL,664,NULL,'дата: '.$_POST['given_pdate'],$id);
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'редактировал поступление товара',NULL,613, NULL, 'в поле '.$k.' установлено значение '.$v,$bill_id);
				
				$log->PutEntry($result['id'],'редактировал поступление товара',NULL,644, NULL, 'в поле '.$k.' установлено значение '.$v,$editing_user['sh_i_id']);
				
				$log->PutEntry($result['id'],'редактировал поступление товара',NULL,664, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
				
			}
			
		}
		
		if($au->user_rights->CheckAccess('w',664)){$positions=array();
		  
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
					  'out_bill_id'=>(int)$_POST['new_out_bill_id_'.$hash],
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
				  $log->PutEntry($result['id'],'добавил позицию поступления товара',NULL,613,NULL,$description,$bill_id);
				  
				  
				  $log->PutEntry($result['id'],'добавил позицию поступления товара',NULL,664,NULL,$description,$id);
				  
				  	
			  }elseif($v['action']==1){
				  $log->PutEntry($result['id'],'редактировал позицию поступления товара',NULL,613,NULL,$description.' первичное кол-во: '.$v['old_quantity'],$bill_id);
				  
				  $log->PutEntry($result['id'],'редактировал позицию поступления товара',NULL,664,NULL,$description.' первичное кол-во: '.$v['old_quantity'],$id);
				  
			  }elseif($v['action']==2){
				  $log->PutEntry($result['id'],'удалил позицию поступления товара',NULL,613,NULL,$description,$bill_id);
				  
				  
				  $log->PutEntry($result['id'],'удалил позицию поступления товара',NULL,664,NULL,$description,$id);
				  
			  }
			  
		  }
		}
		
		
	}
	
	
	//ввод заданного номера при утв. пост
	if($editing_user['is_confirmed']==1){
		
		$params=array();
		//обычная загрузка прочих параметров
		
	 
		$params['given_no']=SecStr($_POST['given_no']);
		 
		
		$_acc->Edit($id, $params,false,$result);
		
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				
				 
				$log->PutEntry($result['id'],'редактировал поступление товара',NULL,613, NULL, 'в поле '.$k.' установлено значение '.$v,$bill_id);
				
				$log->PutEntry($result['id'],'редактировал поступление товара',NULL,644, NULL, 'в поле '.$k.' установлено значение '.$v,$editing_user['sh_i_id']);
				
				$log->PutEntry($result['id'],'редактировал поступление товара',NULL,664, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
				
				
			}
			
		}
	}
	
	
	//утверждение 
	if($editing_user['is_confirmed']==1){
		//есть права :
		$can=true;
		$bill_has_pms=$_acc->ParentBillHasPms($id, $editing_user);
		if(!$bill_has_pms||($editing_user['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',672);
		else $can=$can&&$au->user_rights->CheckAccess('w',722);
		
		if($can){
			if(!isset($_POST['is_confirmed'])&&($editing_user['status_id']==5)&&($_POST['current_status_id']==5)){
				
				
				
				$_acc->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'снял утверждение поступления товара',NULL,613, NULL, NULL,$bill_id);
				
			 
				
				$log->PutEntry($result['id'],'снял утверждение поступления товара',NULL,722, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',671)||$au->user_rights->CheckAccess('w',96)){
			if(isset($_POST['is_confirmed'])&&($editing_user['status_id']==4)&&($_POST['current_status_id']==4)){
				
				
				$_acc->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true,$result);
				
				$log->PutEntry($result['id'],'утвердил поступление товара',NULL,613, NULL, NULL,$bill_id);	
				
			 
				$log->PutEntry($result['id'],'утвердил поступление товара',NULL,671, NULL, NULL,$id);	
				
				
					
			}
		}else{
			//do nothing
		}
	}
	
	
	//die();
	
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: ed_bill_in.php?action=1&id=".$bill_id.'&do_show_acc=1');
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',664)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_acc_in.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: ed_bill_in.php?action=1&id=".$bill_id.'&do_show_acc=1');
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
elseif($print==1){
	  if(($printmode==0)||($printmode==1))  $smarty->display('top_print_alb.html');
	else $smarty->display('top_print.html');
}
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
		$org_opf=$_opf->GetItemById($orgitem['opf_id']);
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		
		$sm1->assign('bill_id',$bill_id);
		$sm1->assign('out_bill_id',$bill['out_bill_id']);
		
		
		
		//поставщик
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($supplier['opf_id']);
		$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
		
		
		$sm1->assign('sdelka_string', 'Входящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		$sm1->assign('supplier_id' ,$bill['supplier_id']);
		$nds=$_supplier->FindNDS($bill['supplier_id'],$supplier);
		
		
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
				
				
				$pos_id=$_t_arr[0];
				$qua=$_t_arr[1];
				$pos_komplekt_ved_id=$_t_arr[2];
				$pos_out_bill_id=$_t_arr[3];
				
				$input_array[]=array('pos_id'=>$pos_id,'qua'=>$qua,'komplekt_ved_id'=>$pos_komplekt_ved_id, 'out_bill_id'=>$pos_out_bill_id);
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
		foreach($input_array as $kk=>$vv){
			$was_in=true;
			
			$pos_id=((int)$vv['pos_id']);
			$qua=((float)$vv['qua']);
			$pos_komplekt_ved_id=((int)$vv['komplekt_ved_id']);
				
				$sql='select p.id as p_id,   p.komplekt_ved_pos_id, p.position_id as id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_pm, p.total, p.komplekt_ved_id,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 cg.group_id,
					 out_bill.code as out_bill_code, p.out_bill_id 
		
		from bill_position as p 
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join catalog_position as cg on cg.id=p.position_id
			left join bill as out_bill on p.out_bill_id=out_bill.id
		where p.bill_id="'.$bill_id.'" and position_id="'.$pos_id.'" and p.komplekt_ved_id="'.$pos_komplekt_ved_id.'" order by position_name asc, id asc';
		
			//	echo $sql.'<br>';
				
				
				
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				$h=mysqli_fetch_array($rs);
				
				//если кол-во равно кол-ву по счету - ставить тотал по счету
				$some_price_pm=$h['price_pm'];
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$bill_id, 'position_id'=>$h['id'], 'storage_id'=>$sh_i['storage_id'], 'sector_id'=>$sh_i['sector_id'], 'komplekt_ved_id'=>$h['komplekt_ved_id']));	
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
				 
				$h['in_acc']=$_mf->MaxInAcc($bill_id, $pos_id,0,$sh_i_id,$sh_i['storage_id'],$sh_i['sector_id'],$pos_komplekt_ved_id);
				
				
				//обнулим незаполненный плюс/минус
				if($h['plus_or_minus']=="") $h['plus_or_minus']=0;
				if($h['rub_or_percent']=="") $h['rub_or_percent']=0;
				if($h['value']=="") $h['value']=0;
				
				$h['nds_summ']=sprintf("%.2f",($h['total']-$h['total']/((100+$nds)/100)));
				
				$total_cost+=$h['total'];
				$total_nds+=$h['nds_summ'];
				
				//print_r($h);
				if($qua>0){
					
					if($h['komplekt_ved_id']!=0) $komplekt_ved_name='Заявка № '.$h['komplekt_ved_id'];
					  else $komplekt_ved_name='-';	
					  
					  
					  
				
				  //всего в соответствующей строке счета
				  $h['max_bill_quantity']=$_mf->MaxInBill($bill_id,$pos_id,$sh_i['storage_id'],$sh_i['sector_id'],$pos_komplekt_ved_id);
				  
				  //всего в соотв. строке Исходящего счета!!!!
				  $h['max_komplekt_quantity']=$_mf->MaxForIncomingBill($h['out_bill_id'], $pos_id, $h['komplekt_ved_id'],$bill_id); //>MaxInKomplekt($h['komplekt_ved_id'],$pos_id);
					
				  if(in_array($h['group_id'],$harg)) $h['is_usl']=1;
				  else $h['is_usl']=0;	  
					
				  $positions[]=array(
					  'id'=>$pos_id,
					  'hash'=>md5($pos_id.'_'.$h['komplekt_ved_id']),
					  'position_name'=>$h['position_name'],
					  'dim_name'=>$h['dim_name'],
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
					  'nds_proc'=>$nds,
					  'nds_summ'=>$h['nds_summ'],
					  'quantity_confirmed'=>$qua,
					  'max_quantity'=>$qua,
					  'in_rasp'=>$h['in_rasp'],
					  'in_bill'=>$h['in_bill'],
					  'in_acc'=>$h['in_acc'],
				  'komplekt_ved_id'=>$h['komplekt_ved_id'],
				  	  'max_bill_quantity'=>$h['max_bill_quantity'],
					  'max_komplekt_quantity'=>$h['max_komplekt_quantity'],
					  'is_usl'=>$h['is_usl'],
					   
				  //'komplekt_ved_name'=>'Заявка № '.$h['komplekt_ved_id'],
				  //'komplekt_ved_name'=>'Заявка № '.$h['komplekt_ved_id'],
						  'komplekt_ved_name'=>$komplekt_ved_name,
						  'out_bill_id'=>$h['out_bill_id'],
						  'out_bill_code'=>$h['out_bill_code']
				  );
				}
				
			//}
		}
		 
		
		
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		
		
		
		if($bill['status_id']==10){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',663));
		
		$sm1->assign('can_modify',true);
			 
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		$sm1->assign('positions',$positions);
		
		//допустимые доли превышения позиций
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',662));
		$sm1->assign('PPUP',PPUP);
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',661)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',661)); 
		
		//можно ли править количества?
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',661));
		
		$cannot_edit_quantities_reason='';
		if(!$au->user_rights->CheckAccess('w',661)){
			//if(strlen($cannot_edit_quantities_reason)>0) $cannot_edit_quantities_reason.=', ';
			$cannot_edit_quantities_reason.='недостаточно прав для данного действия';
		}
		$sm1->assign('cannot_edit_quantities_reason',$cannot_edit_quantities_reason);
		
		//для активности кнопки "ред-ть кол-во позиции"
		$sm1->assign('acc', array('is_leading'=>-1));
		
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',661)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',664)); 
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		$user_form=$sm1->fetch('acc_in/acc_create.html');
		
		$origs='В данном режиме указать, есть ли оригиналы документов, невозможно.<br />
Пожалуйста, сохраните и утвердите поступление для работы с вкладкой "Оригиналы документов".';
	}elseif($action==1){
		//редактирование позиции
		
		if($print==0) $print_add='';
		else $print_add='_print';
		
		
		$orgitem=$_orgitem->getitembyid($editing_user['org_id']);
		
		
		$sm1=new SmartyAdm;
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($orgitem['opf_id']);
		$org_opf=$_opf->GetItemById($orgitem['opf_id']);
		
		$sm1->assign('force_print', $force_print);
		
		$sm1->assign('force_print_xls', $force_print_xls);
		
			
		//склад
		$sector=$_sector->GetItemById($editing_user['sector_id']); //$bill['storage_id']);
		$sm1->assign('sector_id_string' ,$sector['name']);
		$sm1->assign('sector_id' ,$editing_user['sector_id']);
		
		
		
		$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
		$sm1->assign('print_org' ,$orgitem);
		//рекв. орг.
		require_once('classes/bdetailsitem.php');
		$_bd=new BDetailsItem;
		$print_org_bdetail=$_bd->GetBasic($orgitem['id']);
		$sm1->assign('print_org_bdetail' ,$print_org_bdetail);
		
		
		//поставщик
		$supplier=$_supplier->GetItemById($bill['supplier_id']);
		$_opf=new OpfItem;
		$opf=$_opf->GetItemById($supplier['opf_id']);
		$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
		
		
	//добавим подписи, печати
		$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActualByPdate($supplier['id'],date("d.m.Y", $editing_user['given_pdate']), 1);
		$sri_2=$_sri->GetActualByPdate($supplier['id'], date("d.m.Y",$editing_user['given_pdate']), 2);
		
		
		$supplier['chief']=$sri_1['fio'];
		$supplier['print_sign_dir']=$sri_1['sign'];
		
		$supplier['main_accountant']=$sri_2['fio'];
		$supplier['print_sign_buh']=$sri_2['sign'];
		
		
		$sm1->assign('print_supplier' ,$supplier);
		//реквизиты для печати
		require_once('classes/bdetailsitem.php');
		$_bdetail=new BDetailsItem;
		$bdetail=$_bdetail->GetItemById($bill['bdetails_id']);
		$sm1->assign('print_bdetail' ,$bdetail);
		
		$sm1->assign('supplier_id' ,$bill['supplier_id']);
		$nds=$_supplier->FindNDS($bill['supplier_id'],$supplier);
		
		
		
		$sm1->assign('sdelka_string', 'Входящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		
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
		
		
		
		//блок аннулирования
			$editing_user['can_annul']=$_acc->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',674);
			if(!$au->user_rights->CheckAccess('w',674)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
		
		$editing_user['can_restore']=$_acc->DocCanRestore($editing_user['id'],$reason)&&$au->user_rights->CheckAccess('w',675);
			if(!$au->user_rights->CheckAccess('w',675)) $reason='недостаточно прав для данной операции';
		

		if($bill['status_id']==10){
			$sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',629));
		}else $sm1->assign('cannot_view_pm',!$au->user_rights->CheckAccess('w',663));
	
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);
		//массив запрещенных периодов	
		$_pergroup=new PerGroup;
		$sm1->assign('cdates', $_pergroup->GetItemsByIdArr($result['org_id'],0,1));
		
		
		//позиции!
		$sm1->assign('has_positions',true);
		$_bpg=new AccInPosGroup;
		
		if(($print==1)&&($printmode==0)) $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,true,false);
		elseif(($print==1)&&($printmode==2)) $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0,false,true);
		else $bpg=$_bpg->GetItemsByIdArr($editing_user['id'],0);
		
		$_posdi=new PosDimItem;
		
		foreach($bpg as $k=>$v){
			$posdi=$_posdi->GetItemByFields(array('name'=>$v['dim_name']));
			$v['okei']=$posdi['okei'];
			$bpg[$k]=$v;
		}
		
		//print_r($bpg);
		$sm1->assign('positions',$bpg);
		$_bpf=new BillPosPMFormer;
		$total_cost=$_bpf->CalcCost($bpg);
		$total_nds=$_bpf->CalcNDS($bpg,true,$bill['supplier_id']);
		$sm1->assign('total_cost',$total_cost);
		$sm1->assign('total_nds',$total_nds);
		
		
		require_once('classes/propis.php');
		$_pn=new PropisUn(); $_pp=new Propis;
		$sm1->assign('count_propis',$_pn->propis(count($bpg)));
		
		$summa_propis=trim( $_pp->propis(floor($total_cost)));
		
		$summa_propis= mb_convert_case(substr($summa_propis, 0, 1), MB_CASE_UPPER, 'windows-1251').substr($summa_propis, 1,strlen($summa_propis));
		
		$sm1->assign('total_cost_rub_propis',$summa_propis);
		
		//$sm1->assign('total_cost_rub_propis',$_pp->propis(floor($total_cost)));
		
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
		
		$sm1->assign('to_pay',number_format($totals_by_all-$nds_sums_by_all,2,'.',' '));
		
		$sm1->assign('cols_by_two',$cols_by_two);
		$sm1->assign('costs_by_two',$costs_by_two);
		$sm1->assign('nds_sums_by_two',$nds_sums_by_two);
		$sm1->assign('totals_by_two',$totals_by_two);
		
		$sm1->assign('cols_by_all',$cols_by_all);
		$sm1->assign('costs_by_all',$costs_by_all);
		$sm1->assign('nds_sums_by_all',number_format($nds_sums_by_all,2,'.',' '));
		$sm1->assign('totals_by_all',number_format($totals_by_all,2,'.',' '));
		
		$sm1->assign('cols_by_one',$cols_by_one);
		$sm1->assign('costs_by_one',$costs_by_one);
		$sm1->assign('nds_sums_by_one',$nds_sums_by_one);
		$sm1->assign('totals_by_one',$totals_by_one);
		
		
		
		//Примечания
		$rg=new AccNotesGroup;
		$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,$editing_user['is_confirmed']==1, $au->user_rights->CheckAccess('w',666), $au->user_rights->CheckAccess('w',667),$result['id']));
		$sm1->assign('can_notes',true);
		$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',665)/*&&($editing_user['is_confirmed']==0)*/);
		
		
		
		//допустимые доли превышения позиций
		$sm1->assign('can_exclude_positions',$au->user_rights->CheckAccess('w',662));
		$sm1->assign('PPUP',PPUP);
		
		
		//получить список поступлений для перечисления при смене даты
		$_acc_ins=$_acc->GetAccs($editing_user['bill_id']);
		$acc_ins=''; $__acc_ins=array();
		foreach($_acc_ins as $k=>$v){
			$__acc_ins[]='реализация №'.$v['id'].', заданный № '.$v['given_no'].' от '.$v['given_pdate'];
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
			if(!$bill_has_pms||($editing_user['inventory_id']!=0)) $can=$can&&$au->user_rights->CheckAccess('w',672);
			else $can=$can&&$au->user_rights->CheckAccess('w',722);
			
			if($can){
				//есть права 
				$can_confirm_price=true;	
			}else{
				$can_confirm_price=false;
			}
		}else{
			//95
			$can_confirm_price=$au->user_rights->CheckAccess('w',671)&&($editing_user['status_id']==4);
		}
		$sm1->assign('can_confirm',$can_confirm_price);
		
		
		
		//сообщения о невозможности снять вновь выставляемое утверждение + список сотрудников, кто может
		if(($bill_has_pms)&&($editing_user['is_confirmed']==0)&&($editing_user['inventory_id']==0)){
			$_usg=new UsersSGroup;	
			$cannot_unconfirm=!$au->user_rights->CheckAccess('w',722);
			
			$sm1->assign('cannot_unconfirm',$cannot_unconfirm);
			
			$usg_can=$_usg->GetUsersByRightArr('w',722);
			$_usg_can_str=array(); 
			foreach($usg_can as $k=>$v) $_usg_can_str[]=htmlspecialchars($v['name_s'].'');
			
			$usg_can_str=implode(', ', $_usg_can_str);
			//echo "$usg_can_str";
			$sm1->assign('can_unconfirm_users', $usg_can_str);
			
		}
		
		
		
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',664)); 
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',664)); 
		
		
		//можно ли править количества?
		$sm1->assign('can_edit_quantities',$au->user_rights->CheckAccess('w',664)&&($editing_user['is_confirmed']==0));
		$sm1->assign('cannot_select_positions', !($au->user_rights->CheckAccess('w',664)&&($editing_user['is_confirmed']==0)));
		
		$cannot_edit_quantities_reason='';
		if($editing_user['is_confirmed']==1){
			$cannot_edit_quantities_reason='поступление утверждено';
		}
		if(!$au->user_rights->CheckAccess('w',664)){
			if(strlen($cannot_edit_quantities_reason)>0) $cannot_edit_quantities_reason.=', ';
			$cannot_edit_quantities_reason.='недостаточно прав для данного действия';
		}
		
		$sm1->assign('cannot_edit_quantities_reason',$cannot_edit_quantities_reason);
		
		
		$sm1->assign('can_print',$au->user_rights->CheckAccess('w',673)); 
		$sm1->assign('has_usl',$_acc->HasUsl($editing_user['id'])); 
		$sm1->assign('has_tov',$_acc->HasTov($editing_user['id'])); 
		
		//наличие с/ф is_upr_nalog
		$sm1->assign('is_upr_nalog',$supplier['is_upr_nalog']==1 );
		
		$sm1->assign('printmode', $printmode);
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',661)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',664)); 
		
		$sm1->assign('can_xls',$au->user_rights->CheckAccess('w',930)); 
		
		
		//$user_form=$sm1->fetch('acc_in/acc_edit'.$print_add.'.html');
		if($print==0) $user_form=$sm1->fetch('acc_in/acc_edit'.$print_add.'.html');
		elseif($printmode==0) $user_form=$sm1->fetch('acc_in/acc_edit'.$print_add.'.html');
		elseif($printmode==1) $user_form=$sm1->fetch('acc_in/acc_edit_fakt.html');
		elseif($printmode==2) $user_form=$sm1->fetch('acc_in/acc_edit_akt.html');
		else $user_form=$sm1->fetch('acc_in/acc_edit_fakt.html');
		
		//var_dump( $_acc->CanConfirmByPositions($id,&$reason));
		//echo $reason;
		
		//echo $_acc->sync->CheckLack($id, $rss, &$lack,'fact_quantity');
		//echo $rss;
		
		
		//работаем с xls-версиями
		if($print==2){
			include_once('ed_acc_in_xls.php');
			
		}
		
		
		
		
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
				if($au->user_rights->CheckAccess('w',672)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',671)&&($editing_user['status_id']==5);
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
				if($au->user_rights->CheckAccess('w',672)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',671)&&($editing_user['status_id']==5);
			}
			////наличие с/ф is_upr_nalog $sm1->assign('is_upr_nalog',$supplier['is_upr_nalog']==1 );
			$sm1->assign('can_confirm_has_fakt',$can_confirm_price&&($supplier['is_upr_nalog']==0));
			
			
			if(($editing_user['has_akt']==1)&&($editing_user['has_akt_confirm_user_id']!=0)){
				$confirmer='';
				$_user_temp=new UserSItem;
				$_user_confirmer=$_user_temp->GetItemById($editing_user['has_akt_confirm_user_id']);
				$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['has_akt_confirm_pdate']);
				
				$sm1->assign('has_akt_confirmer',$confirmer);
			}
			
			$can_confirm_price=false;
			if($editing_user['has_akt']==1){
				if($au->user_rights->CheckAccess('w',672)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',671)&&($editing_user['status_id']==5);
			}
			
			$can_confirm_price=$can_confirm_price&&$_acc->HasUsl($editing_user['id']);
			$sm1->assign('can_confirm_has_akt',$can_confirm_price);
			
			
			
			$origs=$sm1->fetch('acc_in/acc_origs.html');
		}else{
			$origs='В данном режиме указать, есть ли оригиналы документов, невозможно.<br />
Пожалуйста, утвердите поступление для работы с вкладкой "Оригиналы документов".';
		}
		
		
		
		
		
		
		
		
		//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',676)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(659,
660,
661,
662,
663,
664,
665,
666,
667,
668,
669,
670,
671,
672,
673,
674,
675,
676,
637
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
			$sm->assign('has_ship', ($editing_user['is_confirmed_shipping']==1));
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_acc_in.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
	
		
	}
	
	
	
	$sm->assign('users',$user_form);
	
	if($au->user_rights->CheckAccess('w',671)) $sm->assign('origs',$origs);
	$sm->assign('can_origs',$au->user_rights->CheckAccess('w',671));
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);
	
	
	
	$content=$sm->fetch('acc_in/ed_acc_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	elseif($print==1)  echo $content;
	else echo $content;
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
elseif($print==1)  $smarty->display('bottom_print.html');

unset($smarty);

?>