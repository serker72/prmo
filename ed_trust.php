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

require_once('classes/user_s_group.php');


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
 

require_once('classes/orgitem.php');
require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/trust_notesgroup.php');
require_once('classes/trust_notesitem.php');


require_once('classes/trust_group.php');
require_once('classes/trust_item.php');

require_once('classes/trust_positem.php');


require_once('classes/bill_in_item.php');
require_once('classes/bill_in_positem.php');
require_once('classes/bill_in_posgroup.php');
require_once('classes/billpospmformer.php');

require_once('classes/user_s_item.php');


require_once('classes/paygroup.php');

require_once('classes/propisun.php');
require_once('classes/propis_drob.php');
require_once('classes/propis_drob1.php');
require_once('classes/propis_drob2.php');


require_once('classes/orgitem.php');
require_once('classes/opfitem.php');

require_once('classes/bdetailsitem.php');

require_once('classes/supcontract_item.php');

require_once('classes/suppliersgroup.php');

require_once('classes/maxformer.php');

require_once('classes/period_checker.php');

require_once('classes/supplier_ruk_item.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Редактирование доверенности');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_trust=new TrustItem;
 
$_bill=new BillInItem;
$_bpi=new BillInPosItem;
$_position=new PosItem;

$_supplier=new SupplierItem;
//$lc=new LoginCreator;
$log=new ActionLog;

$_posgroupgroup=new PosGroupGroup;

$_supgroup=new SuppliersGroup;

$_supcontract=new SupContractItem;

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();


if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',284)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

$printmode=1;
if(isset($_GET['printmode'])){
	$printmode=$_GET['printmode'];
}



$_orgitem=new OrgItem;
$orgitem=$_orgitem->GetItemById($result['org_id']);
$_opf=new OpfItem;
$opfitem=$_opf->GetItemById($orgitem['opf_id']);

if(!isset($_GET['action'])){
	if(!isset($_POST['action'])){
		$action=0;
	}else $action=abs((int)$_POST['action']);
}else $action=abs((int)$_GET['action']);

$object_id=array();
switch($action){
	case 0:
	$object_id[]=203;
	break;
	case 1:
	$object_id[]=208;
	$object_id[]=284;
	break;
	case 2:
	$object_id[]=212;
	break;
	default:
	$object_id[]=203;
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
	$editing_user=$_trust->GetItemById($id);
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

//журнал событий 
if($action==1){
	$log=new ActionLog;
	if($print==0)
		$log->PutEntry($result['id'],'открыл карту доверенности',NULL,208, NULL,  ' доверенность № '.$editing_user['id'],$id);			
	else
		$log->PutEntry($result['id'],'открыл карту доверенности: версия для печати',NULL,284, NULL,  ' доверенность № '.$editing_user['id'],$id);	
}

if(($action==0)&&(isset($_POST['doNew'])||isset($_POST['doNewEdit']))){
	if(!$au->user_rights->CheckAccess('w',203)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}	
	
	
	
	
	
	$params=array();
	//обычная загрузка прочих параметров
	$params['org_id']=abs((int)$result['org_id']);
	$params['pdate']=DateFromdmY($_POST['pdate'])+(time() -DateFromdmY($_POST['pdate']));;
	$params['bill_id']=abs((int)$_POST['bill_id']);
	
	$params['user_id']=abs((int)$_POST['user_id']);
	$params['contract_id']=abs((int)$_POST['contract_id']);
	
	//$params['notes']=SecStr($_POST['notes']);
	
	$params['is_confirmed']=0;
	
	
	$params['manager_id']=$result['id'];
	
	$params['given_no']=SecStr($_POST['given_no']);
	
	if(isset($_POST['has_another_bills'])) $params['has_another_bills']=1;
	else  $params['has_another_bills']=0;
	$params['another_supplier_id']=abs((int)$_POST['another_supplier_id']);
	
	
	$code=$_trust->Add($params);
	
	
	//запись в журнале
	if($code>0){
		$log->PutEntry($result['id'],'создал доверенность по исходящему счету',NULL,93,NULL,NULL,$bill_id);	
		
		$log->PutEntry($result['id'],'создал доверенность по исходящему счету',NULL,203,NULL,NULL,$code);	
		
		foreach($params as $k=>$v){
			
		 
				if($k=='user_id'){
					$_si=new UserSItem;
					$si=$_si->GetItemById($v);  
					
					
					$log->PutEntry($result['id'],'создал доверенность по исходящему счету',NULL,203, NULL, SecStr('установлен получатель '.' '.$si['name_s']),$code);			
					continue;	
				}
				
				
				$log->PutEntry($result['id'],'создал доверенность по исходящему счету',NULL,203, NULL, 'в поле '.$k.' установлено значение '.$v,$code);		
			 
		}
	}
	
	
	if(($code>0)&&($au->user_rights->CheckAccess('w',204))){
		//позиции
		$positions=array();
		
		
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		
		foreach($_POST as $k=>$v){
			if(eregi("^new_hash_",$k)){
				
				 $hash=eregi_replace("^new_hash_","",$k);
			  	
				// echo $hash;
			  	 $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			 	
				
				  $pos=$_pos->GetItemById(abs((int)$_POST['new_position_id_'.$hash]));
				   $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
					  
				  $positions[]=array(
					  'trust_id'=>$code,
					  'position_id'=>$pos_id,
					  
					  'name'=>SecStr($pos['name']),
					  'dimension'=>SecStr($dimension['name']),
					  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
					  'bill_id'=>(int)$_POST['new_bill_id_'.$hash],
					  'price'=>0,
					  'pms'=>NULL
				  );
			}
			
			
		}
		
		
		//внесем позиции
		$_trust->AddPositions($code,$positions);
		
		/*echo '<pre>';
		//print_r($_POST);
		print_r($positions);
		echo '</pre>';
		die();
		*/
		
		//запишем в журнал
		foreach($positions as $k=>$v){
			$pos=$_pos->GetItemById($v['position_id']);
			if($pos!==false) {
				$descr=SecStr($pos['name']).'<br /> кол-во '.$v['quantity'].'<br /> ';
				
				
				$log->PutEntry($result['id'],'добавил позицию доверенности', NULL, 93,NULL,$descr,$bill_id);
				
				$log->PutEntry($result['id'],'добавил позицию доверенности', NULL, 204,NULL,$descr,$code);	
				
			}
		}	
	}
	
	
	
	
	//перенаправления
	if(isset($_POST['doNew'])){
		header("Location: trust.php?bill_id=".$bill_id."#user_".$code);
		die();
	}elseif(isset($_POST['doNewEdit'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',208)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_trust.php?action=1&id=".$code);
		die();	
		
	}else{
		header("Location: trust.php?bill_id=".$bill_id);
		die();
	}
	
	
	die();
	
}elseif(($action==1)&&(isset($_POST['doEdit'])||isset($_POST['doEditStay']))){
	//редактирование pozicii
	if(!$au->user_rights->CheckAccess('w',208)){
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
		
		$params['user_id']=abs((int)$_POST['user_id']);
		
		
		$params['given_no']=SecStr($_POST['given_no']);
		
		
		if(isset($_POST['has_another_bills'])) $params['has_another_bills']=1;
		else  $params['has_another_bills']=0;
		$params['another_supplier_id']=abs((int)$_POST['another_supplier_id']);
		
		
		
		$_trust->Edit($id, $params);
		
		
		//die();
		//запись в журнале
		//записи в лог. сравнить старые и новые записи
		foreach($params as $k=>$v){
			
			if(addslashes($editing_user[$k])!=$v){
				$log->PutEntry($result['id'],'редактировал доверенность',NULL,93, NULL, 'в поле '.$k.' установлено значение '.$v,$bill_id);
				
				$log->PutEntry($result['id'],'редактировал доверенность',NULL,208, NULL, 'в поле '.$k.' установлено значение '.$v,$id);
			}
			
		}
		
		
		$positions=array();
		
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		
		foreach($_POST as $k=>$v){
			if(eregi("^new_hash_",$k)){
				
				 $hash=eregi_replace("^new_hash_","",$k);
			  	
				// echo $hash;
			  	 $pos_id=abs((int)$_POST['new_position_id_'.$hash]);
			 	
				
				  $pos=$_pos->GetItemById(abs((int)$_POST['new_position_id_'.$hash]));
				   $dimension=$_pdi->GetItemById(abs((int)$_POST['new_dimension_id_'.$hash]));
					  
				  $positions[]=array(
					  'trust_id'=>$id,
					  'position_id'=>$pos_id,
					 
					  'name'=>SecStr($pos['name']),
					  'dimension'=>SecStr($dimension['name']),
					  'quantity'=>((float)str_replace(",",".",$_POST['new_quantity_'.$hash])),
					  'bill_id'=>(int)$_POST['new_bill_id_'.$hash],
					  'price'=>0,
					  'pms'=>NULL
				  );
			}
			
			
		}
		
		
		
		//
		/*
		echo '<pre>';
		print_r($_POST);
		print_r($positions);
		echo '</pre>';
		//внесем позиции
		die();*/
		
		$log_entries=$_trust->AddPositions($id,$positions);
		
		
		
		
		
		//выводим в журнал сведения о редактировании позиций
		foreach($log_entries as $k=>$v){
			$description=SecStr($v['name']).' <br /> Кол-во: '.$v['quantity'].'<br /> ';
			
			
			if($v['action']==0){
				$log->PutEntry($result['id'],'добавил позицию доверенности',NULL,93,NULL,$description,$bill_id);	
				
				$log->PutEntry($result['id'],'добавил позицию доверенности',NULL,204,NULL,$description,$id);	
			}elseif($v['action']==1){
				$log->PutEntry($result['id'],'редактировал позицию доверенности',NULL,93,NULL,$description,$bill_id);
				$log->PutEntry($result['id'],'редактировал позицию доверенности',NULL,205,NULL,$description,$id);
				
				
			}elseif($v['action']==2){
				$log->PutEntry($result['id'],'удалил позицию доверенности',NULL,93,NULL,$description,$bill_id);
				
				$log->PutEntry($result['id'],'удалил позицию доверенности',NULL,206,NULL,$description,$id);
			}
			
		}
		
		
	}
	
	
	
	//утверждение цен
	if($editing_user['is_confirmed']==1){
		//есть права: либо сам утв.+есть права, либо есть искл. права:
		if(($au->user_rights->CheckAccess('w',211))||$au->user_rights->CheckAccess('w',96)){
			if(!isset($_POST['is_confirmed'])&&($editing_user['status_id']==2)&&($_POST['current_status_id']==2)){
				$_trust->Edit($id,array('is_confirmed'=>0, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'снял утверждение доверенности',NULL,93, NULL, NULL,$bill_id);	
				
				$log->PutEntry($result['id'],'снял утверждение доверенности',NULL,211, NULL, NULL,$id);	
			}
		}else{
			//нет прав	
		}
		
	}else{
		//есть права
		if($au->user_rights->CheckAccess('w',210)||$au->user_rights->CheckAccess('w',96)){
			if(isset($_POST['is_confirmed'])&&($editing_user['status_id']==1)&&($_POST['current_status_id']==1)){
				$_trust->Edit($id,array('is_confirmed'=>1, 'user_confirm_id'=>$result['id'], 'confirm_pdate'=>time()),true);
				
				$log->PutEntry($result['id'],'утвердил доверенность',NULL,93, NULL, NULL,$bill_id);	
				
				$log->PutEntry($result['id'],'утвердил доверенность',NULL,210, NULL, NULL,$id);	
					
			}
		}else{
			//do nothing
		}
	}
	
	
	
	//die();
	
	//перенаправления
	if(isset($_POST['doEdit'])){
		header("Location: trust.php?bill_id=".$bill_id);
		die();
	}elseif(isset($_POST['doEditStay'])){
		//если есть доступ к объекту 11 - правка пользователя - то переход туда		
		if(!$au->user_rights->CheckAccess('w',208)){
			header("HTTP/1.1 403 Forbidden");
			header("Status: 403 Forbidden");
			include("403.php");
			die();	
		}
		header("Location: ed_trust.php?action=1&id=".$id);
		die();	
		
	}else{
		header("Location: trust.php?bill_id=".$bill_id);
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
 
unset($smarty);

$_menu_id=36;

	if($print==0) include('inc/menu.php');
	
	
	//демонстрация стартовой страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	if($print==0) $print_add='';
		else $print_add='_print';
		
		
	
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
		
		$sm1->assign('sdelka_string', 'Входящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
		
		//договор
		$supcontract=$_supcontract->GetItemById($bill['contract_id']);
		$sm1->assign('contract_id' ,$bill['contract_id']);
		$sm1->assign('contract_pdate' ,$supcontract['contract_pdate']);
		$sm1->assign('contract_no' ,$supcontract['contract_no']);
		
		//сотрудник
		$_usg=new UsersSGroup;
		$users=$_usg->GetItemsArr(0, 1,1);
		
		$u_ids=array(); $u_names=array();
		$u_ids[]=0; $u_names[]='-выберите-';
		
		foreach($users as $k=>$v){
			$u_ids[]=$v['id'];
			$u_names[]=$v['name_s'];
		}
		$sm1->assign('user_ids',$u_ids);
		$sm1->assign('user_names',$u_names);
		$sm1->assign('user_id',0);
		
		
		
		$sm1->assign('supplier_id',$bill['supplier_id']);
		
		//передать позиции...
		$positions=array(); $intrust=array();
		$_tpi=new TrustPosItem;
		$_bpf1=new BillPosPMFormer;
		
		$total_cost=0; $total_nds=0;
		
	$_mf=new MaxFormer;				
				$sql='select sum(p.quantity) as quantity,  p.bill_id, p.komplekt_ved_pos_id, p.position_id as id, p.position_id as position_id,
					 p.name as position_name, p.dimension as dim_name, 
					  p.price,
					 pd.id as dimension_id,
					
					 b.id, b.code, b.pdate as bill_pdate			 
		
		from bill_position as p 
			
			left join catalog_dimension as pd on pd.name=p.dimension
			left join bill as b on p.bill_id=b.id
		where p.bill_id="'.$bill_id.'" 
		group by p.position_id
		order by p.name asc
		';
				
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				
				$rc=$set->getResultNumRows();
				//echo $sql;
				$total_quantity=0;
				for($i=0; $i<$rc; $i++){
				  $h=mysqli_fetch_array($rs);
				  //echo 'zzz';
				  
				 
				  //print_r($h);
				  $max_quantity=$_mf->MaxForTrust($bill_id,$h['position_id']);
				  //echo $max_quantity;
				  if($max_quantity>0){
				  
					$total_quantity+=$max_quantity;
					
					$positions[]=array(
						'id'=>$h['position_id'],
						'position_name'=>$h['position_name'],
						'dim_name'=>$h['dim_name'],
						'dimension_id'=>$h['dimension_id'],
						'quantity'=>$max_quantity,
					   
						'quantity_confirmed'=>$max_quantity, //??? проверить по КВ!
						'max_quantity'=>$h['quantity'], 
						'in_rasp'=>0,
						'bill_code'=>$h['code'],
						'bill_id'=>$bill_id,
						'bill_pdate'=>date("d.m.Y",$h['bill_pdate']),
						'hash'=>md5($h['position_id'].'_'.$bill_id)
						
					);
					
					  $others=$_tpi->GetOtherPosArr($h['position_id'], $bill_id, 0);
					  foreach($others as $ok=>$ov) $intrust[]=$ov;
					}
				}
		
		
		
		if(count($positions)>0) {
			 $sm1->assign('has_positions',true);
			
		}
		$sm1->assign('total_quantity',$total_quantity);
		$sm1->assign('can_modify',true);
			 
	
		$sm1->assign('positions',$positions);
		$sm1->assign('intrust',$intrust);
		
		//другие счета для выборки
		$sm1->assign('bills',$_trust->GetRelatedBillsArr(0, $bill_id, $bill['supplier_id'], $bill['contract_id']));
		
		
	
		
		$sm1->assign('can_create',$au->user_rights->CheckAccess('w',203)); 
		$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',203));
		
		
		$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',204));
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',206));
		$sm1->assign('can_use_other_sup',$au->user_rights->CheckAccess('w',207)); 
		
		
		//допустимые доли превышения позиций
			$sm1->assign('TRUSTUP',TRUSTUP);
		
		//дата начала периода
		$sm1->assign('pch_date', $pch_date);	
		
		$user_form=$sm1->fetch('trust/trust_create.html');
	}elseif($action==1){
		//редактирование позиции
		
	
		$sm1=new SmartyAdm;
		
		
		//кем создано
		require_once('classes/user_s_item.php');
		$_cu=new UserSItem();
		$cu=$_cu->GetItemById($editing_user['manager_id']);
		if($cu!==false){
			$ccu=$cu['name_s'];
		}else $ccu='-';
		$sm1->assign('created_by',$ccu);
		
		
		
		if($print==0){
		
		
			
			$_opf=new OpfItem;
			$opf=$_opf->GetItemById($orgitem['opf_id']);
			
			$sm1->assign('org',stripslashes($opf['name'].' '.$orgitem['full_name']));
			
			//$sm1->assign('org',stripslashes($opfitem['name'].' '.$orgitem['full_name']));
			
			$sm1->assign('bill_id',$editing_user['bill_id']);
			
		
			
			//поставщик
			$supplier=$_supplier->GetItemById($bill['supplier_id']);
			$_opf=new OpfItem;
			$opf=$_opf->GetItemById($supplier['opf_id']);
			$sm1->assign('supplier_id_string' ,$opf['name'].' '.$supplier['full_name']);
			
			$sm1->assign('sdelka_string', 'Входящий счет №'.$bill['code'].' от '.date("d.m.Y H:i:s",$bill['pdate']));
			
			//даты
			$editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);
			
			
			//договор
			$supcontract=$_supcontract->GetItemById($editing_user['contract_id']);
			$sm1->assign('contract_id' ,$editing_user['contract_id']);
			$sm1->assign('contract_pdate' ,$supcontract['contract_pdate']);
			$sm1->assign('contract_no' ,$supcontract['contract_no']);
			
			
			
			//сотрудник
			$_usg=new UsersSGroup;
			$users=$_usg->GetItemsArr(0, 1,1);
			
			$u_ids=array(); $u_names=array();
			$u_ids[]=0; $u_names[]='-выберите-';
			
			foreach($users as $k=>$v){
				$u_ids[]=$v['id'];
				$u_names[]=$v['name_s'];
			}
			$sm1->assign('user_ids',$u_ids);
			$sm1->assign('user_names',$u_names);
			$sm1->assign('user_id',$editing_user['user_id']);
			
			
			//другие поставщики
			$another_sup=$_supgroup->GetItemsByFieldsArr(array('org_id'=>$result['org_id'],'is_org'=>0, 'is_active'=>1));
			$another_supplier_ids=array(); $another_supplier_names=array();
			foreach($another_sup as $k=>$v){
				$another_supplier_ids[]=$v['id'];
				$another_supplier_names[]=$v['opf_name'].' '.$v['full_name'];
			}
			
			$sm1->assign('another_supplier_ids',$another_supplier_ids);
			$sm1->assign('another_supplier_names',$another_supplier_names);
			$sm1->assign('supplier_id',$editing_user['another_supplier_id']);
			
			
			//другие счета для выборки
		$sm1->assign('bills',$_trust->GetRelatedBillsArr($editing_user['id'], $editing_user['bill_id'], $bill['supplier_id'], $editing_user['contract_id']));
		
			
			
			//позиции!
			$sm1->assign('has_positions',true);
			$_bpg=new TrustPosGroup;
			$bpg=$_bpg->GetItemsByIdArr($editing_user['id']);
			//print_r($bpg);
			$sm1->assign('positions',$bpg);
			$_bpf=new BillPosPMFormer;
			$total_cost=$_bpf->CalcCost($bpg);
			$total_nds=$_bpf->CalcNDS($bpg);
			$sm1->assign('total_cost',$total_cost);
			$sm1->assign('total_nds',$total_nds);
			
			
			//позиции др. доверенностей
		
			$intrust=$_bpg->GetOtherItemsByIdArr($editing_user['id']);
		//print_r($bpg);
			$sm1->assign('intrust',$intrust);
			
			
			//Примечания
			$rg=new TrustNotesGroup;
			$sm1->assign('notes',$rg->GetItemsByIdArr($editing_user['id'],0,0,($editing_user['is_confirmed']==1),$au->user_rights->CheckAccess('w',340), $au->user_rights->CheckAccess('w',340),$result['id']));
			$sm1->assign('can_notes',true);
			$sm1->assign('can_notes_edit',$au->user_rights->CheckAccess('w',209)/*&&($editing_user['is_confirmed']==0)*/);
			
			
			//блок аннулирования
			$editing_user['can_annul']=$_trust->DocCanAnnul($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',212);
			if(!$au->user_rights->CheckAccess('w',212)) $reason='недостаточно прав для данной операции';
			$editing_user['can_annul_reason']=$reason;
			
			
			$editing_user['can_restore']=$_trust->DocCanRestore($editing_user['id'],$reason,$editing_user)&&$au->user_rights->CheckAccess('w',213);
			if(!$au->user_rights->CheckAccess('w',213)) $reason='недостаточно прав для данной операции';
			
			$sm1->assign('ship',$editing_user);
			
			//возможность РЕДАКТИРОВАНИЯ - только если is_confirmed_price==0
			//$sm1->assign('can_modify',$editing_user['is_confirmed']==0);
			$sm1->assign('can_modify', in_array($editing_user['status_id'],$_editable_status_id));  
			
			$sm1->assign('can_add_positions',$au->user_rights->CheckAccess('w',204));
		$sm1->assign('can_del_positions',$au->user_rights->CheckAccess('w',206));
		$sm1->assign('can_use_other_sup',$au->user_rights->CheckAccess('w',207)); 
		
			
			//блок утверждения!
			if(($editing_user['is_confirmed']==1)&&($editing_user['user_confirm_id']!=0)){
				$confirmer='';
				$_user_temp=new UserSItem;
				$_user_confirmer=$_user_temp->GetItemById($editing_user['user_confirm_id']);
				$confirmer=$_user_confirmer['position_s'].' '.$_user_confirmer['name_s'].' '.' '.date("d.m.Y H:i:s",$editing_user['confirm_pdate']);
				
				$sm1->assign('is_confirmed_confirmer',$confirmer);
			}
			
			$can_confirm_price=false;
			if($editing_user['is_confirmed']==1){
				if($au->user_rights->CheckAccess('w',96)){
					//полные права
					$can_confirm_price=true;	
				}elseif($au->user_rights->CheckAccess('w',211)){
					//есть права + сам утвердил
					$can_confirm_price=true;	
				}else{
					$can_confirm_price=false;
				}
			}else{
				//95
				$can_confirm_price=$au->user_rights->CheckAccess('w',210)&&($editing_user['status_id']==1);
			}
			$sm1->assign('can_confirm',$can_confirm_price);
			
			//допустимые доли превышения позиций
			$sm1->assign('TRUSTUP',TRUSTUP);
			
			$sm1->assign('can_print',$au->user_rights->CheckAccess('w',284)); 
			
			$sm1->assign('can_create',$au->user_rights->CheckAccess('w',203)); 
			$sm1->assign('can_edit',$au->user_rights->CheckAccess('w',208)); 
			
			//дата начала периода
			$sm1->assign('pch_date', $pch_date);
		
		}else{
			
			
		  $_bpg=new TrustPosGroup;
		  $bpg=$_bpg->GetItemsByIdPrintArr($editing_user['id']);
		  $bpg_1=$_bpg->GetItemsByIdArr($editing_user['id']);
		  //print_r($bpg);
		  $our_bills=array();
		  foreach($bpg_1 as $k=>$v){
		  	$arr=array('bill_id'=>$v['bill_id'], 'supplier_bill_no'=>$v['supplier_bill_no'], 'supplier_bill_pdate'=>$v['supplier_bill_pdate']);
		  	if(!in_array($arr, $our_bills)) $our_bills[]=$arr;
		  }
		  
		  $_pn=new PropisUn;
		  foreach($bpg as $k=>$v){
			  $bpg[$k]['summa_p']=trim($_pn->propis($v['summa']));
			  
			  $ost=round(($v['summa']-(int)$v['summa'])*1000);
			  
			 /* echo $ost.' ';
			  echo ($ost%100).' ';
			  echo ($ost%10).' ';
			   */
			   
			  
			  if($ost>0){
				  $ostt=$ost;
				 $class=new PropisDrob2;
				if(($ost%10)==0){
					  //echo 'sotye';
					  $ostt=floor($ost/10);
					  $class=new PropisDrob1;
				}
				
				if(($ost%100)==0){
					$class= new PropisDrob;
					$ostt=floor($ost/100);
					//echo 'десятые';
				}
				
				if((($ost%100)!=0)&&(($ost%10)!=0)){
					$class=new PropisDrob2;
				
					//echo 'tysachnye';
				}
				$bpg[$k]['summa_p'].=' и '.trim($class->propis($ostt));
				
			  }
			  
			  
		  }
		  
		  //перебор позиций, добавление разрывов
		  $cter=1; $bpg2=array(); $first=true;
		  foreach($bpg as $k=>$v){
			  $has_break=false;
			  if(($cter==21)&&$first){ $has_break=true; $cter=1; $first=false; } 
			  elseif(($cter%60==0)) $has_break=true;
			  
			  
			  $v['has_break']=$has_break;
			  
			  $bpg2[]=$v;
			  $cter++;
		  }
		  
		  
		  $sm1->assign('positions',$bpg2);
		  $sm1->assign('positions1',$bpg_1);
		  $sm1->assign('our_bills',$our_bills);
		  
		  //руководитель, гл.бух.
	//добавим подписи, печати
		$_sri=new SupplierRukItem;
		$sri_1=$_sri->GetActualByPdate($orgitem['id'],date("d.m.Y", $editing_user['pdate']), 1);
		$sri_2=$_sri->GetActualByPdate($orgitem['id'], date("d.m.Y",$editing_user['pdate']), 2);
		
		
		$orgitem['chief']=$sri_1['fio'];
		$orgitem['print_sign_dir']=$sri_1['sign'];
		
		$orgitem['main_accountant']=$sri_2['fio'];
		$orgitem['print_sign_buh']=$sri_2['sign'];
		  
		  
		  
		  $sm1->assign('org', $orgitem); //$orgitem['name']);
		  $sm1->assign('order',$editing_user);
		  
		  $_si=new SupplierItem;
		  $si=$_si->GetItemById($bill['supplier_id']);
		  
		  
		  $_opf_sup=new OpfItem;
		  $opfsup=$_opf_sup->getItemById($si['opf_id']);
		  
		  $letter=array();
		  $letter['id']=$editing_user['id'];
		   $letter['code']=$bill['code'];
		   
		    $letter['given_no']=$editing_user['given_no'];
		   $letter['supplier_bill_no']=$bill['supplier_bill_no'];
		  $letter['pdate']=date("d.m.Y",$editing_user['pdate']);
		  
		  
		  $letter['srok']=date("d.m.Y",$editing_user['pdate']+60*60*24*30);
		  
		  $letter['supplier_bill_pdate']=date("d.m.Y",$bill['supplier_bill_pdate']);
		  
		  $_user_s=new UserSItem;
		  $user_s=$_user_s->getitembyid($editing_user['user_id']);
		  
		  $letter['fio']=$user_s['name_s'];
		  
		  if(strlen($si['print_name'])>0) $letter['supplier_name']=$opfsup['name'].' '.$si['print_name'].'';
		  else $letter['supplier_name']=$opfsup['name'].' "'.$si['full_name'].'"';
		  
		  
		  if(strlen($orgitem['print_name'])>0) $letter['organization']=$opfitem['name'].' '.($orgitem['print_name']).', ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
		  else   $letter['organization']=$opfitem['name'].' "'.($orgitem['full_name']).'", ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
		  
		 // $letter['organization']=$opfitem['name'].' "'.eregi_replace('Торговый Дом Строительная Ярмарка', 'ТД "Строительная Ярмарка',$orgitem['full_name']).'", ИНН '.$orgitem['inn'].', '.$orgitem['legal_address'];
		  
		  $_bd=new BDetailsItem;
		  $bank=$_bd->getitembyfields(array('user_id'=>$orgitem['id'],'is_basic'=>1));
		  
		  if($bank===false){
			  $bank=$_bd->getitembyfields(array('user_id'=>$orgitem['id']));
		  }
		  
		  $letter['rs']=$bank['rs'];
		  $letter['bank']=$bank['bank'];
		  
		  $letter['city']=$bank['city'];
		  $letter['bik']=$bank['bik'];
		  
		  $letter['ks']=$bank['ks'];
		  
		 
		  
		  $letter['pasp_ser']=$user_s['pasp_ser'];
		  $letter['pasp_no']=$user_s['pasp_no'];
		  $letter['pasp_kem']=$user_s['pasp_kem'];
		  $letter['pasp_kogda']=$user_s['pasp_kogda'];
		  
		  
		  $letter['chief']=$orgitem['chief'];
		  $letter['main_accountant']=$orgitem['main_accountant'];
		  
		  
		  $sm1->assign('letter',$letter);
		}
		
		$sm1->assign('printmode',$printmode);
		
		if($print==0) $user_form=$sm1->fetch('trust/trust_edit.html');
		else $user_form=$sm1->fetch('letter.html');
		
		
	}
	
	
	$sm->assign('users',$user_form);
	
	
	//покажем журнал событий по позиции
		if($au->user_rights->CheckAccess('w',524)){
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
		
			$decorator->AddEntry(new SqlEntry('object_id',NULL, SqlEntry::IN_VALUES, NULL, array(198,
202,
203,
204,
205,
206,
207,
208,
209,
210,
211,
284,
212,
213)));
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
			
			$llg=$log->ShowLog('syslog/log_doc.html',$decorator,$from,$to_page,'ed_pay.php',true,true,true);
			
			$sm->assign('syslog',$llg);		
				
		}
	
	
	
	$sm->assign('print_pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	
	$sm->assign('print_username',$username);

	
	$content=$sm->fetch('trust/ed_trust_page'.$print_add.'.html');
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	//else echo $content;
	
	else {
		
	 
		
		//echo $content; die();
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		$comand = "wkhtmltopdf-i386 --page-size A4 --orientation Portrait --encoding windows-1251 --image-quality 100 --margin-top 5mm --margin-bottom 5mm --margin-left 10mm --margin-right 10mm  ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf";
		
 		 
	 
		exec($comand);
	
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Доверенность_'.$editing_user['id'].'.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf'); 
		
	//	readfile(ABSPATH.'/tmp/'.$tmp.'.html');
		
		
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
	//	unlink(ABSPATH.'/tmp/'.$tmp1.'.html'); 
		
	exit;
	}
	
	
	unset($smarty);


$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
 
unset($smarty);
?>