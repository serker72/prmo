<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/supplieritem.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');

require_once('../classes/fagroup.php');
require_once('../classes/faitem.php');

require_once('../classes/opfgroup.php');
require_once('../classes/opfitem.php');

require_once('../classes/suppliercontactgroup.php');
require_once('../classes/suppliercontactitem.php');
require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/suppliercontactdataitem.php');


require_once('../classes/supplier_district_group.php');
require_once('../classes/supplier_district_item.php');

require_once('../classes/supplier_region_group.php');
require_once('../classes/supplier_region_item.php');

require_once('../classes/supplier_city_group.php');
require_once('../classes/supplier_city_item.php');

require_once('../classes/supplier_cities_group.php');
require_once('../classes/supplier_cities_item.php');

require_once('../classes/supcontract_group.php');
require_once('../classes/supcontract_item.php');

require_once('../classes/supplier_notesgroup.php');
require_once('../classes/supplier_notesitem.php');

require_once('../classes/sched.class.php');
require_once('../classes/supplier_branches_group.php');
require_once('../classes/supplier_branches_item.php');

require_once('../classes/supplier_responsible_user_item.php');

require_once('../classes/quick_suppliers_group.php');

require_once('../classes/supplier_view.class.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';

$ui=new SupplierItem;
//РАБОТА С ОПФ
if(isset($_POST['action'])&&($_POST['action']=="redraw_opf_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new OpfGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('suppliers/d_opfs.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_opf_page")){
	//$sm=new SmartyAj;
	
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new OpfGroup;
	$ret=$opg->GetItemsOpt($user_id);
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_opf")){
	
	//dostup
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	$qi=new OpfItem;
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['opf']),9);
	$qi->Add($params);
	
	//$log->PutEntry($result['id'],'добавил ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_opf")){
	/*if(!$au->user_rights->CheckAccess('w',19)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new OpfItem;
	$params=array();
	$id=abs((int)$_POST['id']);
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['question']),9);
	$qi->Edit($id,$params);	
	
	//$log->PutEntry($result['id'],'редактировал ОПФ',NULL,19,NULL,$params['name']);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_opf")){
	
	/*if(!$au->user_rights->CheckAccess('w',13)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}*/
	
	$qi=new OpfItem;
	
	$id=abs((int)$_POST['id']);
	$qi->Del($id);
	
	//$log->PutEntry($result['id'],'удалил ОПФ',NULL,19,NULL,$params['name']);
}elseif(isset($_POST['action'])&&($_POST['action']=="redraw_req")){
	// РАБОТА С РЕКВИЗИТАМИ
	
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new BDetailsGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','req');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Реквизиты');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',167));
	
	
	$ret=$sm->fetch('suppliers/d_rekvizit.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_req")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',167)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new BDetailsItem;
	$ri->Add(array(
				'bank'=>SecStr(iconv("utf-8","windows-1251",$_POST['bank'])),
				'bik'=>SecStr(iconv("utf-8","windows-1251",$_POST['bik'])),
				'city'=>SecStr(iconv("utf-8","windows-1251",$_POST['city'])),
				'rs'=>SecStr(iconv("utf-8","windows-1251",$_POST['rs'])),
				'ks'=>SecStr(iconv("utf-8","windows-1251",$_POST['ks'])),
				'is_basic'=>abs((int)$_POST['is_basic']),
				'user_id'=>$user_id
			));
			
	$description='';
	$description.=' банк '.SecStr(iconv("utf-8","windows-1251",$_POST['bank']));
	$description.=' БИК '.SecStr(iconv("utf-8","windows-1251",$_POST['bik']));
	$description.=' город '.SecStr(iconv("utf-8","windows-1251",$_POST['city']));
	$description.=' р/с '.SecStr(iconv("utf-8","windows-1251",$_POST['rs']));
	$description.=' к/с '.SecStr(iconv("utf-8","windows-1251",$_POST['ks']));
	if(abs((int)$_POST['is_basic'])==1) $description.=' основные реквизиты ';		
	
	$log->PutEntry($result['id'],'добавил реквизиты контрагенту', NULL,167,NULL,$description, $user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_req")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',167)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BDetailsItem;
	$ri->Edit($id,
				array(
				'bank'=>SecStr(iconv("utf-8","windows-1251",$_POST['bank'])),
				'bik'=>SecStr(iconv("utf-8","windows-1251",$_POST['bik'])),
				'city'=>SecStr(iconv("utf-8","windows-1251",$_POST['city'])),
				'rs'=>SecStr(iconv("utf-8","windows-1251",$_POST['rs'])),
				'is_basic'=>abs((int)$_POST['is_basic']),
				'ks'=>SecStr(iconv("utf-8","windows-1251",$_POST['ks']))/*,
				'user_id'=>$user_id*/
			));
			
	$description='';
	$description.=' банк '.SecStr(iconv("utf-8","windows-1251",$_POST['bank']));
	$description.=' БИК '.SecStr(iconv("utf-8","windows-1251",$_POST['bik']));
	$description.=' город '.SecStr(iconv("utf-8","windows-1251",$_POST['city']));
	$description.=' р/с '.SecStr(iconv("utf-8","windows-1251",$_POST['rs']));
	$description.=' к/с '.SecStr(iconv("utf-8","windows-1251",$_POST['ks']));
	if(abs((int)$_POST['is_basic'])==1) $description.=' основные реквизиты ';		
	
	$log->PutEntry($result['id'],'редактировал реквизиты контрагента', NULL,167,NULL, $description, $user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_req")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',167)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BDetailsItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил реквизиты контрагента',NULL,167,NULL,NULL,$user_id);
	
}
//РАБОТА С ФАКТИЧ АДРЕСАМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_fakt_addr")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new FaGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','fakt_addr');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Фактические адреса');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',166));
	
	
	$ret=$sm->fetch('suppliers/d_fakt_addr.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_fakt_addr")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',166)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new FaItem;
	
	$params=array();
	$params['user_id']=$user_id;
	
	$params['form_id']=abs((int)$_POST['form_id']);
	$params['city_id']=abs((int)$_POST['city_id']);
	
	$params['address']=SecStr(iconv("utf-8","windows-1251",$_POST['address']));
	
	$params['post_index']=SecStr(iconv("utf-8","windows-1251",$_POST['post_index']));
	$params['street']=SecStr(iconv("utf-8","windows-1251",$_POST['street']));
	$params['house']=SecStr(iconv("utf-8","windows-1251",$_POST['house']));
	$params['korp']=SecStr(iconv("utf-8","windows-1251",$_POST['korp']));
	$params['str']=SecStr(iconv("utf-8","windows-1251",$_POST['str']));
	$params['office']=SecStr(iconv("utf-8","windows-1251",$_POST['office']));
	$params['flat']=SecStr(iconv("utf-8","windows-1251",$_POST['flat']));
	
	
	
	$ri->Add($params);
	
	//$log->PutEntry($result['id'],'добавил фактический адрес контрагенту', NULL,166,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['address'])),$user_id);
	
	$_sc=new SupplierCityItem;
	foreach($params as $k=>$v){
		
		if(($k=='city_id')){
			
			$sc=$_sc->GetFullCity($v);
			$log->PutEntry($result['id'],'добавил фактический адрес контрагента',NULL,166, NULL, 'в поле Город установлено значение '.SecStr($sc['fullname']),$user_id);	
			continue;
		}
		$log->PutEntry($result['id'],'добавил фактический адрес контрагента',NULL,166, NULL, 'в поле '.$k.' установлено значение '.$v,$user_id);		
			 
	}	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_fakt_addr")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',166)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$params=array();
	 
	$params['form_id']=abs((int)$_POST['form_id']);
	$params['city_id']=abs((int)$_POST['city_id']);
	
	$params['address']=SecStr(iconv("utf-8","windows-1251",$_POST['address']));
	
	$params['post_index']=SecStr(iconv("utf-8","windows-1251",$_POST['post_index']));
	$params['street']=SecStr(iconv("utf-8","windows-1251",$_POST['street']));
	$params['house']=SecStr(iconv("utf-8","windows-1251",$_POST['house']));
	$params['korp']=SecStr(iconv("utf-8","windows-1251",$_POST['korp']));
	$params['str']=SecStr(iconv("utf-8","windows-1251",$_POST['str']));
	$params['office']=SecStr(iconv("utf-8","windows-1251",$_POST['office']));
	$params['flat']=SecStr(iconv("utf-8","windows-1251",$_POST['flat']));
	
	
	
	$ri=new FaItem;
	
	$old=$ri->GetItemById($id);
	$ri->Edit($id,  $params);
	
	//$log->PutEntry($result['id'],'редактировал фактический адрес контрагента', NULL,166,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['address'])),$user_id);
	
	$_sc=new SupplierCityItem;
	foreach($params as $k=>$v){
		if((addslashes($old[$k])!=$v)&&($k=='city_id')){
			
			$sc=$_sc->GetFullCity($v);
			$log->PutEntry($result['id'],'редактировал фактический адрес контрагента',NULL,166, NULL, 'в поле Город установлено значение '.SecStr($sc['fullname']),$user_id);	
			continue;
		}
		if(addslashes($old[$k])!=$v){
			$log->PutEntry($result['id'],'редактировал фактический адрес контрагента',NULL,166, NULL, 'в поле '.$k.' установлено значение '.$v,$user_id);		
		}
			 
	}	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_fakt_addr")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',166)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new FaItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил фактический адрес контрагента', NULL,166,NULL,NULL,$user_id);
	
}
//подсветка утверждений карты
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_active_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.date("d.m.Y H:i:s",time());	
	}
	
}
//РАБОТА С КОНТАКТАМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_contact")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new SupplierContactGroup;
	$_si=new SupplierItem;
	$supplier=$_si->GetItemById($user_id);
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,$au->user_rights->CheckAccess('w',917)));
	
	$sm->assign('word','contact');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Контакты');
	
	$rrg=new SupplierContactKindGroup;
	$sm->assign('kinds',$rrg->GetItemsArr());
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',165)&&($supplier['is_confirmed']!=1));
	
	$sm->assign('can_add_contact',$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',165)); 
	
	//права на удаление контактов
	$can_del_contact=$au->user_rights->CheckAccess('w',87)&&$au->user_rights->CheckAccess('w',165);
	if($supplier['is_confirmed']==0){
		if($supplier['is_active']==1) $can_del_contact=$can_del_contact&&$au->user_rights->CheckAccess('w',917);
	}else $can_del_contact=$can_del_contact&&false;
	$sm->assign('can_del_contact', $can_del_contact); 
	
	
	//блокировка первой записи в связ. спр-ке (города, контакты и т.п.)
	$sm->assign('block_first',($supplier['is_confirmed']==0)&&($supplier['is_active']==1));
		
	
	
	$ret=$sm->fetch('suppliers/contacts.html');

	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	$birthdate=$_POST['birthdate'];
	if(strlen($birthdate)==0) $birthdate=NULL;
	else $birthdate=DateFromDmy($birthdate);
	
	
	$ri=new SupplierContactItem;
	$ri->Add(array(
				'name'=>SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9),
				'position'=>SecStr(iconv("utf-8","windows-1251",$_POST['position']),9),
				'birthdate'=>$birthdate,
				'supplier_id'=>$user_id
			));
	
	$log->PutEntry($result['id'],'добавил контакт контрагенту', NULL,165,NULL,'имя: '.SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9).' должность: '.SecStr(iconv("utf-8","windows-1251",$_POST['position']),9).' дата рождения: '.$birthdate,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
		
	$birthdate=$_POST['birthdate'];
	if(strlen($birthdate)==0) $birthdate=NULL;
	else $birthdate=DateFromDmy($birthdate);
	
	
	
	$params=array();
	$params['name']=SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9);
	$params['position']=SecStr(iconv("utf-8","windows-1251",$_POST['position']),9);
	$params['birthdate']=$birthdate;
	
	
	
	$ri=new SupplierContactItem;
	$ri->Edit($id,  $params);
	
	$log->PutEntry($result['id'],'редактировал контакт контрагента', NULL,165,NULL,'имя: '.SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9).' должность: '.SecStr(iconv("utf-8","windows-1251",$_POST['position']),9).' дата рождения: '.$birthdate,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierContactItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил контакт контрагента', NULL,165,NULL,NULL,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_value_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new SupplierContactDataItem;
	$ri->Add(array(
		'value'=>SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),
		'value1'=>SecStr(iconv("utf-8","windows-1251",$_POST['value1']),9),
		'contact_id'=>abs((int)$_POST['contact_id']),
		'kind_id'=>abs((int)$_POST['kind_id'])
	));
	
	
	$log->PutEntry($result['id'],'добавил данные контакта контрагенту', NULL,165,NULL,SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_nest_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$params=array();
	$params['kind_id']=abs((int)$_POST['kind_id']);
	$params['value']=SecStr(iconv("utf-8","windows-1251",$_POST['value']),9);
	$params['value1']=SecStr(iconv("utf-8","windows-1251",$_POST['value1']),9);
	
	
	$ri=new SupplierContactDataItem;
	$ri->Edit($id, $params);
	
	$log->PutEntry($result['id'],'редактировал данные контакта контрагента', NULL,165,NULL,SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierContactDataItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил данные контакта контрагента', NULL,165,NULL,NULL,$user_id);
	

}elseif(isset($_POST['action'])&&($_POST['action']=="load_phones_contact")){
//подгрузка рабочих телефонов для копирования контакта
	$sm=new SmartyAj;
	
	$log=new ActionLog;
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contact_id=abs((int)$_POST['contact_id']);
	
	$rg=new SupplierContactGroup;
	$_cg=new SupplierContactDataGroup;
	$_si=new SupplierItem;
	$supplier=$_si->GetItemById($supplier_id);
	
	$data=$_cg->GetItemsByIdArr($contact_id);
	$data1=array();
	foreach($data as $k=>$v) if($v['kind_id']==1) $data1[]=$v; 
	
	$sm->assign('items',$data1);
	$sm->assign('word','contact');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Контакты');
	
   
	
	$ret=$sm->fetch('suppliers/contacts_for_copy.html');

}elseif(isset($_POST['action'])&&($_POST['action']=="copy_contact_contact")){
	//копирование контакта
	
//	die();
	
	if(!$au->user_rights->CheckAccess('w',165)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	$log=new ActionLog;
	$user_id=abs((int)$_POST['user_id']);
	$old_contact_id=abs((int)$_POST['old_contact_id']);
	
	$phones=$_POST['phones'];
	
	
	$birthdate=$_POST['birthdate'];
	if(strlen($birthdate)==0) $birthdate=NULL;
	else $birthdate=DateFromDmy($birthdate);
	
	$ri=new SupplierContactItem;
	$contact_id=$ri->Add(array(
				'name'=>SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9),
				'position'=>SecStr(iconv("utf-8","windows-1251",$_POST['position']),9),
				'birthdate'=>$birthdate,
				'supplier_id'=>$user_id
			));
	
	$log->PutEntry($result['id'],'добавил контакт контрагенту при копировании контакта', NULL,165,NULL,'имя: '.SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9).' должность: '.SecStr(iconv("utf-8","windows-1251",$_POST['position']),9).' дата рождения: '.$birthdate,$user_id);
	
	$_cg=new SupplierContactDataGroup;
	$data=$_cg->GetItemsByIdArr($old_contact_id);
	
	//копируем:а телефоны из массива, б - сайт (8), факс 2
	$ri=new SupplierContactDataItem;
	foreach($phones as $k=>$v){
		$valarr=explode('|',$v);
		
		$ri->Add(array(
			'value'=>SecStr(iconv("utf-8","windows-1251",$valarr[0]),9),
			'value1'=>SecStr(iconv("utf-8","windows-1251",$valarr[1]),9),
			'contact_id'=>$contact_id,
			'kind_id'=>1
		));
		
		 
		$log->PutEntry((int)$result['id'],'добавил данные контакта контрагенту при копировании контакта', NULL,165,NULL,SecStr(iconv("utf-8","windows-1251",$valarr[0].' '.$valarr[1])), (int)$user_id);	
	}
	
	foreach($data as $k=>$v){
		if(!in_array($v['kind_id'],  array(2,8))) continue;

		
		$ri->Add(array(
			'value'=>SecStr(iconv("utf-8","windows-1251",$v['value']),9),
			'value1'=>SecStr(iconv("utf-8","windows-1251",$v['value1']),9),
			'contact_id'=>$contact_id,
			'kind_id'=>$v['kind_id']
		));
		
		 
		$log->PutEntry((int)$result['id'],'добавил данные контакта контрагенту при копировании контакта', NULL,165,NULL,SecStr(iconv("utf-8","windows-1251",$v['value'].' '.$v['value1'])), (int)$user_id);	
	}

	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="has_dog_save")){
	if($au->user_rights->CheckAccess('w',87)){
		$supplier_id=abs((int)$_POST['supplier_id']);
		
		$contract_id=abs((int)$_POST['contract_id']);
		
		$state=abs((int)$_POST['state']);
		
		$params=array();
		$params['has_dog']=$state;
		if($state==1){
		  $params['has_dog_confirm_pdate']=time();
		  $params['has_dog_confirm_user_id']=$result['id'];	
		}else{
		  $params['has_dog_confirm_pdate']=0;
		  $params['has_dog_confirm_user_id']=0;				
			
		}
		$_si=new SupContractItem; // SupplierItem;
		$_si->Edit($contract_id,$params);
		if($state==1){
			$log->PutEntry($result['id'],'утвердил наличие оригинала договора', NULL,87,NULL,NULL,$supplier_id);
		}else{
						$log->PutEntry($result['id'],'снял утверждение наличие оригинала договора', NULL,87,NULL,NULL,$supplier_id);	
		}
	}
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="has_uch_save")){
	//dostup
	if($au->user_rights->CheckAccess('w',87)){
		$id=abs((int)$_POST['id']);
		
		$state=abs((int)$_POST['state']);
		
		$params=array();
		$params['has_uch']=$state;
		if($state==1){
			$params['has_uch_confirm_pdate']=time();
			$params['has_uch_confirm_user_id']=$result['id'];	
		}else{
			$params['has_uch_confirm_pdate']=0;
			$params['has_uch_confirm_user_id']=0;	
		}
		$_si=new SupplierItem;
		$_si->Edit($id,$params);
		if($state==1){
			$log->PutEntry($result['id'],'утвердил наличие оригинала учредительных документов', NULL,87,NULL,NULL,$id);
		}else{
						$log->PutEntry($result['id'],'снял утверждение наличие оригинала учредительных документов', NULL,87,NULL,NULL,$id);	
		}
	}
	
	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="confirm_auto")){
	
		$_si=new SupplierItem;
		
		
	
		$id=abs((int)$_POST['id']);
		
		$si=$_si->GetItemById($id);
		
		$state=abs((int)$_POST['state']);
		
		$params['is_confirmed']=1;
		$params['confirm_user_id']=$si['confirm_user_id'];
		$params['confirm_pdate']=time();
		
		
		$_si->Edit($id,$params);
		
		
		$tested_params=array();
		$tested_params['full_name']=SecStr(iconv("utf-8","windows-1251",$_POST['full_name']));
		
		$tested_params['ur_or_fiz']=SecStr(iconv("utf-8","windows-1251",$_POST['ur_or_fiz']));
		$tested_params['opf_id']=SecStr(iconv("utf-8","windows-1251",$_POST['opf_id']));
		
		$tested_params['contract_no']=SecStr(iconv("utf-8","windows-1251",$_POST['contract_no']));
		$tested_params['contract_prolongation']=SecStr(iconv("utf-8","windows-1251",$_POST['contract_prolongation']));
		$tested_params['contract_prolongation_mode']=SecStr(iconv("utf-8","windows-1251",$_POST['contract_prolongation_mode']));
		$tested_params['contract_pdate']=SecStr(iconv("utf-8","windows-1251",$_POST['contract_pdate']));
		
		
		
		
		
		$tested_params['inn']=SecStr(iconv("utf-8","windows-1251",$_POST['inn']));
		$tested_params['kpp']=SecStr(iconv("utf-8","windows-1251",$_POST['kpp']));
		$tested_params['okpo']=SecStr(iconv("utf-8","windows-1251",$_POST['okpo']));
		$tested_params['chief']=SecStr(iconv("utf-8","windows-1251",$_POST['chief']));
		$tested_params['main_accountant']=SecStr(iconv("utf-8","windows-1251",$_POST['main_accountant']));
		$tested_params['time_from_h']=SecStr(iconv("utf-8","windows-1251",$_POST['time_from_h']));
		$tested_params['time_from_m']=SecStr(iconv("utf-8","windows-1251",$_POST['time_from_m']));
		$tested_params['time_to_h']=SecStr(iconv("utf-8","windows-1251",$_POST['time_to_h']));
		$tested_params['time_to_m']=SecStr(iconv("utf-8","windows-1251",$_POST['time_to_m']));
		$tested_params['legal_address']=SecStr(iconv("utf-8","windows-1251",$_POST['legal_address']));
		
		
		
		//получить, что не изменилось
		$description='Пользователь выбрал автоматическое утверждение карты без сохранения изменений.<br />';
		
		foreach($tested_params as $k=>$v){
		  
			if(addslashes($si[$k])!=$v){
				
				$description.=' значение поля '.$k.'='.SecStr($si[$k]).' не было изменено на '.$v.'<br />';
			}
		}
		
		
		
		
		$log->PutEntry($result['id'],'автоматическое утверждение заполнение карты контрагента', NULL,174, NULL,$description,$id);	
		
		
	
	
}
elseif(isset($_POST['action'])&&(($_POST['action']=="load_okrug")||($_POST['action']=="load_okrug_opt"))){
	$_sg=new SupplierDistrictGroup;
	
	$country_id=abs((int)$_POST['country_id']);
	
	if(($_POST['action']=="load_okrug")){
	$sg=$_sg->GetItemsByIdArr($country_id);
	
	$sm=new SmartyAj;
	
	$sm->assign('dis', $sg);
	$sm->assign('can_modify', $au->user_rights->CheckAccess('w',584));
	
	
	$ret=$sm->fetch('org/edit_okrug.html');
	}else{
		$ret=$_sg->GetItemsOptById($country_id, 0,'name',true);	
	}
	
	
	
//load_okrug
}
elseif(isset($_POST['action'])&&($_POST['action']=="add_okrug")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierDistrictItem;
		$country_id=abs((int)$_POST['country_id']);	
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		
		$names=explode("\n", $name);
		
		foreach($names as $k=>$v){
			
		//	$ret.= $v.' |';
			$_com_names=explode(':', $v);
			
			
			$params=array();
			$params['name']=SecStr($_com_names[0]);
			$params['country_id']=$country_id;
			
			$test=$_si->GetItemByFields($params);
			
			if((strlen($params['name'])>0)&&($test===false)) {
				$id=$_si->Add($params);
		
				$log->PutEntry($result['id'],'добавил федеральный округ', NULL,584, NULL,$params['name'],$id);	
			}else $id=$test['id'];
			
			if(isset($_com_names[1])&&(strlen(SecStr($_com_names[1]))>0)){
					//добавим город	
				$_ssi=new SupplierCityItem;
				$params=array();
				$params['name']=SecStr($_com_names[1]);
				$params['district_id']=$id;
				$params['region_id']=0;
				$params['country_id']=$country_id;
				
				$test=$_ssi->GetItemByFields($params);
				
				if((strlen($params['name'])>0)&&($test===false)) {
					$id=$_ssi->Add($params);
			
					$log->PutEntry($result['id'],'добавил город', NULL,584, NULL,$params['name'],$id);	
				}
			}
		}
		
		//print_r($names);
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_okrug")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierDistrictItem;
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		
		$id=abs((int)$_POST['id']);
			$params=array();
			$params['name']=SecStr($name);
			
		
				$_si->Edit($id, $params);
		
				$log->PutEntry($result['id'],'редактировал федеральный округ', NULL,584, NULL,$params['name'],$id);	
			
		
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="del_okrug")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierDistrictItem;
		
		$name=iconv("utf-8","windows-1251",$_POST['name']);
		
		$id=abs((int)$_POST['id']);
		
		$test=$_si->GetItemById($id);
		
			
				$_si->Del($id);
		
				$log->PutEntry($result['id'],'редактировал федеральный округ', NULL,584, NULL,$test['name'],$id);	
			
		
	}
}



elseif(isset($_POST['action'])&&(($_POST['action']=="load_region")||($_POST['action']=="load_region_opt"))){
	$_sg=new SupplierRegionGroup;
	
	$district_id=abs((int)$_POST['district_id']);
	$country_id=abs((int)$_POST['country_id']);
	
	if(($_POST['action']=="load_region")){
	$sg=$_sg->GetItemsByIdArr($district_id, $country_id, 0);
	
	$sm=new SmartyAj;
	
	$sm->assign('dis', $sg);
	$sm->assign('can_modify', $au->user_rights->CheckAccess('w',584));
	
	
	$ret=$sm->fetch('org/edit_region.html');
	}else{
		$ret=$_sg->GetItemsOptById($district_id, $country_id, 0,'name',true);	
	}
//load_region
}
elseif(isset($_POST['action'])&&($_POST['action']=="add_region")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierRegionItem;
		$_ssi=new SupplierCityItem;
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		$district_id=abs((int)$_POST['district_id']);
		$country_id=abs((int)$_POST['country_id']);
		$names=explode("\n", $name);
		
		foreach($names as $k=>$v){
			
		//	$ret.= $v.' |';
			$_com_names=explode(':', $v);
			
			
			$params=array();
			$params['name']=SecStr($_com_names[0]);
			$params['district_id']=$district_id;
			$params['country_id']=$country_id;
			
			$test=$_si->GetItemByFields($params);
			
			if((strlen($params['name'])>0)&&($test===false)) {
				$id=$_si->Add($params);
		
				$log->PutEntry($result['id'],'добавил регион', NULL,584, NULL,$params['name'],$id);	
				
				
				
			}else $id=$test['id'];
			
			if(isset($_com_names[1])&&(strlen(SecStr($_com_names[1]))>0)){
					//добавим город	
				
				$params=array();
				$params['name']=SecStr($_com_names[1]);
				$params['district_id']=$district_id;
				$params['region_id']=$id;
				$params['country_id']=$country_id;
				
				$test=$_ssi->GetItemByFields($params);
				
				if((strlen($params['name'])>0)&&($test===false)) {
					$id=$_ssi->Add($params);
			
					$log->PutEntry($result['id'],'добавил город', NULL,584, NULL,$params['name'],$id);	
				}
			}
		}
		
		//print_r($names);
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_region")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierRegionItem;
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		
		$id=abs((int)$_POST['id']);
			$params=array();
			$params['name']=SecStr($name);
			
		
				$_si->Edit($id, $params);
		
				$log->PutEntry($result['id'],'редактировал регион', NULL,584, NULL,$params['name'],$id);	
			
		
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="del_region")){

	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierRegionItem;
		
		$name=iconv("utf-8","windows-1251",$_POST['name']);
		
		$id=abs((int)$_POST['id']);
		
		$test=$_si->GetItemById($id);
		
			
				$_si->Del($id);
		
				$log->PutEntry($result['id'],'редактировал регион', NULL,584, NULL,$test['name'],$id);	
			
		
	}
}


elseif(isset($_POST['action'])&&(($_POST['action']=="load_city")||($_POST['action']=="load_city_opt"))){
	$_sg=new SupplierCityGroup;
	
	$district_id=abs((int)$_POST['district_id']);
	$region_id=abs((int)$_POST['region_id']);
	$country_id=abs((int)$_POST['country_id']);
	
	if(($_POST['action']=="load_city")){
	$sg=$_sg->GetItemsByIdArr("",$district_id, $region_id, $country_id); //>GetItemsByIdArr($district_id,0);
	
	$sm=new SmartyAj;
	
	$sm->assign('dis', $sg);
	$sm->assign('can_modify', $au->user_rights->CheckAccess('w',584));
	
	
	$ret=$sm->fetch('org/edit_city.html');
	}else{
		$ret=$_sg->GetItemsOptById("",$district_id, $region_id, $country_id, 0,'name',true); //>GetItemsOptById($district_id,0,'name',true);	
	}
//load_region
}
elseif(isset($_POST['action'])&&($_POST['action']=="add_city")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierCityItem;
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		$district_id=abs((int)$_POST['district_id']);
		$region_id=abs((int)$_POST['region_id']);
		$country_id=abs((int)$_POST['country_id']);
		
		$names=explode("\n", $name);
		
		foreach($names as $k=>$v){
			
		//	$ret.= $v.' |';
			$params=array();
			$params['name']=SecStr($v);
			$params['district_id']=$district_id;
			$params['region_id']=$region_id;
			$params['country_id']=$country_id;
			
			$test=$_si->GetItemByFields($params);
			
			if((strlen($params['name'])>0)&&($test===false)) {
				$id=$_si->Add($params);
		
				$log->PutEntry($result['id'],'добавил регион', NULL,584, NULL,$params['name'],$id);	
			}
		}
		
		//print_r($names);
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_city")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierCityItem;
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		
		$id=abs((int)$_POST['id']);
			$params=array();
			$params['name']=SecStr($name);
			
		
				$_si->Edit($id, $params);
		
				$log->PutEntry($result['id'],'редактировал регион', NULL,584, NULL,$params['name'],$id);	
			
		
	}
}elseif(isset($_POST['action'])&&($_POST['action']=="del_city")){
	if($au->user_rights->CheckAccess('w',584)){
		
		$_si=new SupplierCityItem;
		
		$name=iconv("utf-8","windows-1251",$_POST['name']);
		
		$id=abs((int)$_POST['id']);
		
		$test=$_si->GetItemById($id);
		
			
				$_si->Del($id);
		
				$log->PutEntry($result['id'],'редактировал регион', NULL,584, NULL,$test['name'],$id);	
			
		
	}
}


//вставка города поставщика
elseif(isset($_POST['action'])&&($_POST['action']=="add_city_to_supplier")){
	
		
		$_si=new SupplierCitiesItem;
		
		
		
	//	$name=iconv("utf-8","windows-1251",$_POST['name']);
		$city_id=abs((int)$_POST['city_id']);
		$supplier_id=abs((int)$_POST['supplier_id']);
		
		
			
		//	$ret.= $v.' |';
			$params=array();
			
			$params['supplier_id']=$supplier_id;
			$params['city_id']=$city_id;
			
			$test=$_si->GetItemByFields($params);
			
			if($test===false) {
				$id=$_si->Add($params);
		
				$description='';
				
				$_sci=new SupplierCityItem;
				$sci=$_sci->GetItemById($city_id);
				
				$_sri=new SupplierRegionItem;
				$sri=$_sri->GetItemById($sci['region_id']);
				
				$_sdi=new SupplierDistrictItem;
				
				$sdi=$_sdi->GetItemById($sci['district_id']);
				$_si1=new SupplierItem;
				$si1=$_si1->getitembyid($supplier_id);
				
				$description='город '.SecStr($sci['name']).', '.SecStr($sri['name']).', '.SecStr($sdi['name']).', контрагент '.SecStr($si1['full_name']);
				
				$log->PutEntry($result['id'],'добавил город контрагента', NULL,87, NULL,$description,$supplier_id);	
			}
		
		
		//print_r($names);
		
		
}

elseif(isset($_POST['action'])&&($_POST['action']=="del_city_to_supplier")){
	
		
		$_si=new SupplierCitiesItem;
		
		
		$city_id=abs((int)$_POST['city_id']);
		$supplier_id=abs((int)$_POST['supplier_id']);
		
	
			
		//	$ret.= $v.' |';
		$params=array();
		
		$params['supplier_id']=$supplier_id;
		$params['city_id']=$city_id;
		
		$test=$_si->GetItemByFields($params);
		
		if($test!==false) {
			$_si->Del($test['id']);
	
			$description='';
			
			$_sci=new SupplierCityItem;
			$sci=$_sci->GetItemById($city_id);
			
			$_sri=new SupplierRegionItem;
			$sri=$_sri->GetItemById($sci['region_id']);
			
			$_sdi=new SupplierDistrictItem;
			
			$sdi=$_sdi->GetItemById($sci['district_id']);
			$_si1=new SupplierItem;
			$si1=$_si1->getitembyid($supplier_id);
			
			$description='город '.SecStr($sci['name']).', '.SecStr($sri['name']).', '.SecStr($sdi['name']).', контрагент '.SecStr($si1['full_name']);
			
			$log->PutEntry($result['id'],'удалил город контрагента', NULL,87, NULL,$description,$supplier_id);	
		}
	
		
		//print_r($names);
		
		
}
//обновить список городов
	
elseif(isset($_POST['action'])&&(($_POST['action']=="load_supplier_cities"))){
	$_sg=new SupplierCitiesGroup;
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$_supplier=new SupplierItem;
	$supplier=$_supplier->GetItemByid($supplier_id);
	
	$sg=$_sg->GetItemsByIdArr($supplier_id);
	
	$sm=new SmartyAj;
	
	$sm->assign('cities', $sg);
	$sm->assign('can_modify', $au->user_rights->CheckAccess('w',87));
	
	$sm->assign('user', $supplier);
	
	//блокировка первой записи в связ. спр-ке (города, контакты и т.п.)
	$sm->assign('block_first',($supplier['is_confirmed']==0)&&($supplier['is_active']==1));
	
	

	
	$ret=$sm->fetch('suppliers/cities_table.html');
	
//load_region
}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_contract")){
	// РАБОТА С ДОГОВОРАМИ
	// РАБОТА С ДОГОВОРАМИ
	
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	$_si=new SupplierItem;
	$si=$_si->GetItemById($user_id);
	
	$rg=new SupContractGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','contract');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Договоры');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',87)&&($si['is_confirmed']==0));
	$sm->assign('can_add_contract', $au->user_rights->CheckAccess('w',87));
	
	$sm->assign('can_has_dog', $au->user_rights->CheckAccess('w',87));
	
	
	
	$ret=$sm->fetch('suppliers/d_contract.html');

	

	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_contract")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new SupContractItem;
	$ri->Add(array(
				'contract_no'=>SecStr(iconv("utf-8","windows-1251",$_POST['contract_no'])),
				'contract_prolongation'=>abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation'])),
				'contract_prolongation_mode'=>abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation_mode'])),
				'contract_pdate'=>SecStr(iconv("utf-8","windows-1251",$_POST['contract_pdate'])),
				
				'is_basic'=>abs((int)$_POST['is_basic']),
				'is_incoming'=>abs((int)$_POST['is_incoming']),
				'user_id'=>$user_id
			));
			
	
	$description='';
	$description.=' № договора: '.SecStr(iconv("utf-8","windows-1251",$_POST['contract_no']));
	$description.=' отсрочка по договору, дней: '.abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation']));
	
	if(abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation_mode']))==0){
		$description.=' дни банковские ';
	}else{
		$description.=' дни календарные ';	
	}
	
	
	$description.=' дата договора: '.SecStr(iconv("utf-8","windows-1251",$_POST['contract_pdate']));
	
	
	if(abs((int)$_POST['is_basic'])==1) $description.=' основной договор; ';	
	if(abs((int)$_POST['is_incoming'])==1) $description.=' входящий договор ';
	else  $description.=' исходящий договор ';			
	
	$log->PutEntry($result['id'],'добавил договор контрагенту', NULL,167,NULL,$description, $user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_contract")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupContractItem;
	$ri->Edit($id,
				array(
				'contract_no'=>SecStr(iconv("utf-8","windows-1251",$_POST['contract_no'])),
				'contract_prolongation'=>abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation'])),
				'contract_prolongation_mode'=>abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation_mode'])),
				'contract_pdate'=>SecStr(iconv("utf-8","windows-1251",$_POST['contract_pdate'])),
				
				'is_basic'=>abs((int)$_POST['is_basic']),
				'is_incoming'=>abs((int)$_POST['is_incoming']),
			));
			
	$description='';
	$description.=' № договора: '.SecStr(iconv("utf-8","windows-1251",$_POST['contract_no']));
	$description.=' отсрочка по договору, дней: '.abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation']));
	
	if(abs((int)iconv("utf-8","windows-1251",$_POST['contract_prolongation_mode']))==0){
		$description.=' дни банковские ';
	}else{
		$description.=' дни календарные ';	
	}
	
	
	$description.=' дата договора: '.SecStr(iconv("utf-8","windows-1251",$_POST['contract_pdate']));
	
	if(abs((int)$_POST['is_basic'])==1) $description.=' основной договор ';		
	
	if(abs((int)$_POST['is_incoming'])==1) $description.=' входящий договор ';
	else  $description.=' исходящий договор ';			
	
	
	$log->PutEntry($result['id'],'редактировал договор контрагента', NULL,87,NULL, $description, $user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_contract")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupContractItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил договор контрагента',NULL,87,NULL,NULL,$user_id);
	
 

}elseif(isset($_POST['action'])&&($_POST['action']=="delete_contracts")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	//$id=abs((int)$_POST['id']);
	//echo 'z';
	
	
	
	$ri=new SupContractItem;
	$scg=new SupContractGroup;
	$contracts=$scg->GetItemsByIdArr($user_id);
	
	foreach($contracts as $k=>$v){
	
		$ri->Del($v['id']);
	
		$log->PutEntry($result['id'],'удалил договор контрагента при включении режима Без договора',NULL,87,NULL,NULL,$user_id);
	}
	
}



//РАБОТА С ПРИМЕЧАНИЯМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_notes")){
	$sm=new SmartyAj;
	
	$_ship=new SupplierItem;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new SupplierNotesGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id,0,0,false,$au->user_rights->CheckAccess('w',87), $au->user_rights->CheckAccess('w',87),$result['id']));
	$sm->assign('word','notes');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Примечания');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',87));
	
	
	$ret=$sm->fetch('suppliers/d_notes.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	$_ship=new SupplierItem;
	
	
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	
	$ri=new SupplierNotesItem;
	$ri->Add(array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'user_id'=>$user_id,
				'posted_user_id'=>$result['id']
			));
	
	$log->PutEntry($result['id'],'добавил примечания по контрагенту', NULL,87, NULL,SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_ship=new SupplierItem;
	
	
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierNotesItem;
	$ri->Edit($id,
				array(
				'note'=>SecStr(iconv("utf-8","windows-1251",$_POST['note'])),
				'pdate'=>time(),
				'posted_user_id'=>$result['id']/*,
				'user_id'=>$user_id*/
			));
	
	$log->PutEntry($result['id'],'редактировал примечания по контрагенту', NULL,87,NULL, SecStr(iconv("utf-8","windows-1251",$_POST['note']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_notes")){
	//dostup
	$user_id=abs((int)$_POST['user_id']);
	
	$_ship=new SupplierItem;
	
	
	if(!$au->user_rights->CheckAccess('w',87)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierNotesItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил примечания по контрагенту', NULL,87,NULL,NULL,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_inn")){
	//проверка по ИНН
	$id=abs((int)$_POST['id']);
	
	$_ship=new SupplierItem;
	
	$inn=SecStr(iconv("utf-8","windows-1251",$_POST['inn']));
	
	$ship=$_ship->GetItemByFieldsWithExcept(array('inn'=>$inn, 'org_id'=>$result['org_id']), array('id'=>$id));
	
	if($ship!==false) $ret=1;
	else $ret=0;
	
	


}






//подгрузка пол-лей для ответственных
elseif(isset($_POST['action'])&&($_POST['action']=="load_resp")){
	
	 
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$_bi1=new SupplierItem;
	$bi1=$_bi1->GetItemById($supplier_id);
	
	
 
	$already_in_bill=array();
	
	$complex_positions=$_POST['complex_positions'];
	//$except_users=$_POST['except_users'];
	
 
	$_kpg=new Sched_UsersSGroup;
	
 	$dec=new DBDecorator;
	
	$limited_user=NULL;
	if($au->FltUser($result)){
		//echo 'z';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($result['id']);
		$limited_user=$u_to_u['sector_ids'];
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_user));	
	}
	
	 
	
	
	$alls=$_kpg->GetItemsForBill($dec);  
	 
  
	/*echo '<pre>';
	print_r(($alls));
	echo '</pre>';*/
	 
	 
	foreach($alls as $kk=>$v){
				  
	 
		 
		  
		  //print_r($vv);
		  
		
		   //подставим значения, если они заданы ранее
		 
		  //ищем перебором массива  $complex_positions
		  $index=-1;
		  foreach($complex_positions as $ck=>$ccv){
		  	$cv=explode(';',$ccv);
			
			if(
				($cv[0]==$v['id'])
				/*($cv[7]==$vv['storage_id'])&&
				($cv[8]==$vv['sector_id'])&&
				($cv[9]==$vv['komplekt_ved_id'])	*/
				){
					$index=$ck;
					//echo 'nashli'.$vv['position_id'].' - '.$index;
					break;	
				}
		  	
		  }
		  
		  
		  if($index>-1){
			  //echo 'nn '.' '.$v['position_id'];
			  //var_dump($position['id']);
			  
			  
			  $valarr=explode(';',$complex_positions[$index]);
			  $v['is_in']=1;
			  
			  
			  
			  
		  }else{
			  //echo 'no no ';
			   $v['is_in']=0;
			 
		  }
		  
		   
		  
		  
		  
		  
		  $v['hash']=md5($v['user_id']);
		  
		 // print_r($v);
		  
		  //$alls[$k]=$v;
		  $arr[]=$v;
		
	}
	
	$sm=new SmartyAj;
	 
	$sm->assign('pospos',$arr);
	 
	 
 
	
	$ret.=$sm->fetch("suppliers/resp_edit_set.html");
	
	 
	
	


}elseif(isset($_POST['action'])&&(($_POST['action']=="transfer_resp")||($_POST['action']=="transfer_and_add_resp"))){
	//перенос выбранных позиций  на страницу  
		
	$supplier_id=abs((int)$_POST['supplier_id']);
	 $complex_positions=$_POST['complex_positions'];
	
	$alls=array();
	$_user=new UserSItem;
	 
	$_bg=new SupplierResponsibleUserItem;
	
	foreach($complex_positions as $k=>$kv){
		$f=array();	
		$v=explode(';',$kv);
		//print_r($v);
		//$do_add=true;
		
		
		
		$user=$_user->GetItemById($v[0]);
		if($user===false) continue;
		
		 
		$f['id']=$v[0];
		$f['user_id']=$v[0];
		
		 
		
		$f['name_s']=$user['name_s'];
		$f['login']=$user['login'];
		
		$f['is_active']=$user['is_active'];
		
		$f['hash']=md5($v[0]);
		
		
		/*if($_POST['action']=='transfer_and_add_resp'){
			
	 
			$positions=array(
				'supplier_id'=>$supplier_id,
				 
				'user_id'=>$v[0]
			);
			
			$_bg->Add($positions); 
			//внесем позиции
			 $description=SecStr($user['name_s'].' '.$user['login']).',  ';
			$log->PutEntry($result['id'],'добавил ответственного сотрудника в карту контрагента',NULL,910,NULL,$description,$supplier_id);	
		}
		*/
		
	//	$ret.=$v.' ';
		$alls[]=$f;
	}
	
	//print_r($alls);
	
	$sm=new SmartyAj;
	$sm->assign('pospos',$alls);
	
	$sm->assign('can_modify',true);
	$sm->assign('can_edit_resp', $au->user_rights->CheckAccess('w',910));
	
	
	if($_POST['action']=='transfer_and_add_resp') $sm->assign('has_no_header',true);
	 
	$ret=$sm->fetch("suppliers/resp_on_page_set.html");
	
	





//работа с отраслями
}elseif(isset($_POST['action'])&&($_POST['action']=="find_branches")){
	$branch_id=abs((int)$_POST['branch_id']);
	$_bg=new SupplierBranchesGroup;
	
	$sm=new SmartyAj;
	
	$sm->assign('pos', $_bg->LoadBranchArr($branch_id));
	$sm->assign('can_edit_branch', $au->user_rights->CheckAccess('w',911));
	
	$sm->assign('parent_id', $branch_id);
	
	$ret=$sm->fetch('suppliers/branches_list.html');
	


}elseif(isset($_POST['action'])&&($_POST['action']=="add_branch")){
	
	if(!$au->user_rights->CheckAccess('w',911)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	
	$_sbi=new SupplierBranchesItem;
	
	$_sbi->Add(array('parent_id'=>$branch_id, 'name'=>$name));
	
	$log->PutEntry($result['id'],'добавил отрасль', NULL,911, NULL,$name);	
			

 

}elseif(isset($_POST['action'])&&($_POST['action']=="del_branch")){
	
	if(!$au->user_rights->CheckAccess('w',911)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	 
	$_sbi=new SupplierBranchesItem;
	
	$sbi=$_sbi->GetItemById($branch_id);
	
	$_sbi->Del($branch_id);
	
	$log->PutEntry($result['id'],'удалил отрасль', NULL,911, NULL,SecStr($sbi['name']));	


}elseif(isset($_POST['action'])&&($_POST['action']=="edit_branch")){
	
	if(!$au->user_rights->CheckAccess('w',911)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	
	$branch_id=abs((int)$_POST['branch_id']);
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name']));
	
	$_sbi=new SupplierBranchesItem;
	$sbi=$_sbi->GetItemById($branch_id);
	
	$_sbi->Edit($branch_id, array( 'name'=>$name));
	
	$log->PutEntry($result['id'],'отредактировал отрасль', NULL,911, NULL,'Старое название: '.SecStr($sbi['name']).', новое название: '.$name);	
			
			

}
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_branch")){
	$_si=new SupplierBranchesItem;
	$id=abs((int)$_GET['id']);
	$si=$_si->GetItemById($id);
	
	
	 
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		
		
		$rret[]='"branch_subbranch":"'.$_si->CountSubs($id).'"';
		 
		$ret='{'.implode(', ',$rret).'}';
	}
	
}




elseif(isset($_POST['action'])&&($_POST['action']=="check_unconfirm_active")){
	//проверить, можно ли снять утв. активности
	
		$id=abs((int)$_POST['id']);
		
	
		
		$_ki=new SupplierItem;
		
		$res=$_ki->CanUnConfirmActive($id,$rss55);
		if($res!=="") $ret=$rss55;
		else $ret=0;
		
		
		//если ноль - то все хорошо
 
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_inn_kpp")){
	//проверка по ИНН, КПП
	$id=abs((int)$_POST['id']);
	
	$_ship=new SupplierItem;
	
	$inn=SecStr(iconv("utf-8","windows-1251",$_POST['inn']));
	$kpp=SecStr(iconv("utf-8","windows-1251",$_POST['kpp']));
	
	$ship=$_ship->GetItemByFieldsWithExcept(array('inn'=>$inn, 'org_id'=>$result['org_id']), array('id'=>$id));
	
	$ship1=$_ship->GetItemByFieldsWithExcept(array('kpp'=>$kpp, 'org_id'=>$result['org_id']), array('id'=>$id));
	
	/*if($ship!==false) $ret=1;
	else $ret=0;
	*/
	
	/*
	if(($ship===false)&&($ship1===false)){
		$ret=0;	
	}elseif(($ship!==false)&&($ship1===false)){
		$ret=1;
	}elseif(($ship===false)&&($ship1!==false)){	
		$ret=2;
	}elseif(($ship!==false)&&($ship1!==false)){	
		$ret=3;
	}
	*/

	if(($ship===false)){
		$ret=0;	
	}else $ret=1;
 

}elseif(isset($_POST['action'])&&($_POST['action']=="check_full_name")){
	//проверка дублирования названия
	$id=abs((int)$_POST['id']);
	
	$_ship=new SupplierItem;
	
	$name=SecStr(iconv("utf-8","windows-1251",$_POST['name'])); 
	
	$ship=$_ship->GetItemByFieldsWithExcept(array('full_name'=>$name, 'org_id'=>$result['org_id']), array('id'=>$id));
	
 	
	
	if($ship!==false){
		 $ret=1;
		 
		 //карта: опф, айди, код, название, город, контакт(ы), имейл
		 $card_str='';
		 
		 $_opf=new OpfItem;
		 $opf=$_opf->GetItemById($ship['opf_id']);
		 
		 
		 $card_str.='<ul><li><a href="supplier.php?action=1&id='.$ship['id'].'" target="_blank">'.$ship['code'].' '.$opf['name'].' '.$ship['full_name'].'</a></li></ul>';
		 
		 //города
		 $_csg=new SupplierCitiesGroup;
		 $csg=$_csg->GetItemsByIdArr($ship['id']);
		 if(count($csg)>0){
			 $card_str.='<br> <strong>Город(а):</strong> <ul>';
			 foreach($csg as $k=>$v){
				$card_str.='<li>'.$v['name'].'</li>';	 
			 }
			 $card_str.='</ul>';	 
		 }
		 
		 //контакты
		 $rg=new SupplierContactGroup;
		 $contacts=$rg->GetItemsByIdArr($ship['id']);
		  if(count($contacts)>0){
			 $card_str.='<br><strong> Контакт(ы):</strong> <ul>';
			 foreach($contacts as $k=>$v){
				$card_str.='<li>'.$v['name'].', '.$v['position'];
				
				$card_str.='<ul>';
				foreach($v['data'] as $kk=>$vv){
					if($vv['kind_id']==5) $card_str.='<li>'.$vv['value'].'</li>';
				}
				$card_str.='</ul>';	 
				
				$card_str.='</li>';	 
			 }
			 $card_str.='</ul>';	 
		 }
		 
		 $ret=$card_str;
	}else $ret=0;
	 
	
	 

}


//получим город для карты фактического адреса
elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_city")){
	$_si=new SupplierCityItem;
	
	$si=$_si->GetFullCity(abs((int)$_GET['id']));
	
	
 
	
	if($si!==false){
		$rret=array();
		foreach($si as $k=>$v){
			 
			
			$rret[]='"'.$k.'":"'.htmlspecialchars(str_replace("\r", "", str_replace("\n",  "",  $v))).'"';
		}
		
		 
		
		$ret='{'.implode(', ',$rret).'}';
	}
	
}



//быстрый поиск контрагентов
elseif(isset($_POST['action'])&&(($_POST['action']=="quick_find_suppliers")||($_POST['action']=='holding_suppliers'))){
		
	$log=new ActionLog;
	//получим список позиций по фильтру
	$_pg=new Quick_SupplierGroup;
	
	$dec=new DBDecorator;
	
	$dec->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
	
	if($_POST['action']=='holding_suppliers') $dec->AddEntry(new SqlEntry('p.is_active',1, SqlEntry::E));

	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['code'])))>0){
	 
			$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['code'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.code', NULL, SqlEntry::LIKE_SET, NULL,$names));	
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['full_name'])))>0){
		 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['full_name'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['inn'])))>0){
		 
		
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['inn'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.inn', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['kpp'])))>0) {
 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['kpp'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('p.kpp', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	 
 
 
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['holding'])))>0) {
 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['holding'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('holding.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['subholding'])))>0) {
 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['subholding'])));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$dec->AddEntry(new SqlEntry('subholding.full_name', NULL, SqlEntry::LIKE_SET, NULL,$names));
	}
	 
 

 
	 if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['city'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['city'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
			//$dec->AddEntry(new SqlEntry('p.kpp', NULL, SqlEntry::LIKE_SET, NULL,$names));
			
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_sprav_city where city_id in( select id from sprav_city where '.implode(' or ',$names).')', SqlEntry::IN_SQL));
		
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['branch'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['branch'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			 
		$dec->AddEntry(new SqlEntry('p.branch_id',' select id from supplier_branches where '.implode(' or ',$names).'', SqlEntry::IN_SQL));
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$dec->AddEntry(new SqlEntry('p.subbranch_id',' select id from supplier_branches where '.implode(' or ',$names).'', SqlEntry::IN_SQL));
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$dec->AddEntry(new SqlEntry('p.subbranch_id1',' select id from supplier_branches where '.implode(' or ',$names).'', SqlEntry::IN_SQL));
		
		$dec->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
	}
	
	
	if(strlen(SecStr(iconv("utf-8","windows-1251",$_POST['contact'])))>0) {
	 
		$names=explode(';', trim(iconv("utf-8","windows-1251",$_POST['contact'])));
			foreach($names as $k=>$v) $names[$k]='name like "%'.SecStr($v).'%"';
			
		
			 // KSK - 29.03.2016
                        // добавляем условие выбора записей для отображения is_shown=1
			//$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  '.implode(' or ',$names).'', SqlEntry::IN_SQL));
			$dec->AddEntry(new SqlEntry('p.id','select distinct supplier_id from supplier_contact where  is_shown=1 and ('.implode(' or ',$names).')', SqlEntry::IN_SQL));
		


		
	}
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
		
		$dec->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
	}
 		
	
	 
	$template='';
	if($_POST['action']=="quick_find_suppliers") $template	='suppliers/quick_suppliers_list.html';
	else  $template	='suppliers/holding_suppliers_list.html';
	
	 $ret=$_pg->GetItemsForBill($template,  $dec,true,$all7,$result);
	
	 
}


//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Supplier_ViewGroup;
	$_view=new Supplier_ViewItem;
	
	$cols=$_POST['cols'];
	
	$_views->Clear($result['id']);
	$ord=0;
	foreach($cols as $k=>$v){
		$params=array();
		$params['col_id']=(int)$v;
		$params['user_id']=$result['id'];
		$params['ord']=$ord;
			
		$ord+=10;
		$_view->Add($params);
		
		 
	}
}
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr_clear"))){
	$_views=new Supplier_ViewGroup;
	  
	
	$_views->Clear($result['id']);
	 


}elseif(isset($_GET['action'])&&($_GET['action']=="retrieve_supplier")){
	$_si=new SupplierItem;
	
	$si=$_si->GetItemById(abs((int)$_GET['id']));
	
	
	$_opf=new OpfItem;
	$opf=$_opf->GetItemById($si['opf_id']);
	
 	
	$si['opf_name']=$opf['name'];
	 
		foreach($si as $k=>$v){
			$si[$k]=iconv('windows-1251', 'utf-8', $v);
			
		}
		
		 
		$ret=json_encode($si);
	 
	
}


//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>