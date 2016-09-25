<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

//require_once('../classes/user_d_item.php');
require_once('../classes/opfitem.php');
require_once('../classes/orgitem.php');
//require_once('../classes/dealeritem.php');
require_once('../classes/opfgroup.php');
//require_once('../classes/dealergroup.php');
require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');

require_once('../classes/fagroup.php');
require_once('../classes/faitem.php');

/*require_once('../classes/notesgroup.php');
require_once('../classes/notesitem.php');

require_once('../classes/tugroup.php');
require_once('../classes/tuitem.php');
*/

require_once('../classes/supplierphonegroup.php');
require_once('../classes/supplierphonekindgroup.php');
require_once('../classes/supplierphoneitem.php');


require_once('../classes/suppliercontactgroup.php');
require_once('../classes/suppliercontactitem.php');
require_once('../classes/suppliercontactdatagroup.php');
require_once('../classes/suppliercontactkindgroup.php');
require_once('../classes/suppliercontactdataitem.php');


require_once('../classes/supplier_country_group.php');
require_once('../classes/supplier_country_item.php');

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
require_once('../classes/org_view.class.php');



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

$ui=new OrgItem;

//РАБОТА С ОПФ
if(isset($_POST['action'])&&($_POST['action']=="redraw_opf_dics")){
	$sm=new SmartyAj;
	if(isset($_POST['user_id'])) $user_id=abs((int)$_POST['user_id']);
	else $user_id=0;
	
	$opg=new OpfGroup;
	$sm->assign('opfs_total', $opg->GetItemsArr());
	
	$ret=$sm->fetch('org/d_opfs.html');
	
	
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
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',121));
	
	
	$ret=$sm->fetch('org/d_rekvizit.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_req")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	$log->PutEntry($result['id'],'добавил реквизиты организации', NULL,121,NULL, $description,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_req")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	$log->PutEntry($result['id'],'редактировал реквизиты организации', NULL,121,NULL, $description,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_req")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new BDetailsItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил реквизиты организации',NULL,121,NULL,NULL,$user_id);
	
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
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',121));
	
	
	$ret=$sm->fetch('org/d_fakt_addr.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_fakt_addr")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
			$log->PutEntry($result['id'],'добавил фактический адрес организации',NULL,121, NULL, 'в поле Город установлено значение '.SecStr($sc['fullname']),$user_id);	
			continue;
		}
		$log->PutEntry($result['id'],'добавил фактический адрес организации',NULL,121, NULL, 'в поле '.$k.' установлено значение '.$v,$user_id);		
			 
	}	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_fakt_addr")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
			$log->PutEntry($result['id'],'редактировал фактический адрес организации',NULL,121, NULL, 'в поле Город установлено значение '.SecStr($sc['fullname']),$user_id);	
			continue;
		}
		if(addslashes($old[$k])!=$v){
			$log->PutEntry($result['id'],'редактировал фактический адрес организации',NULL,121, NULL, 'в поле '.$k.' установлено значение '.$v,$user_id);		
		}
			 
	}	
	
 	
	
 
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_fakt_addr")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new FaItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил фактический адрес организации', NULL,121,NULL,NULL,$user_id);
	
}
//РАБОТА С ТЕЛЕФОНАМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_phones")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new SupplierPhoneGroup;// FaGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','phones');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Телефоны');
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',121));
	
	
	$ret=$sm->fetch('org/d_phones.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_phones")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	
	
	$ri=new SupplierPhoneItem;
	$ri->Add(array(
				'phone'=>SecStr(iconv("utf-8","windows-1251",$_POST['phone']),9),
				'kind_id'=>abs((int)$_POST['kind_id']),
				'supplier_id'=>$user_id
			));
	
	$log->PutEntry($result['id'],'добавил телефон организации', NULL,121,NULL,SecStr(iconv("utf-8","windows-1251",$_POST['phone']),9),$user_id);
	//echo 'zzz';
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_phones")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$params=array();
	$params['phone']=SecStr(iconv("utf-8","windows-1251",$_POST['phone']),9);
	$params['kind_id']=abs((int)$_POST['kind_id']);
	
	$ri=new SupplierPhoneItem;
	$ri->Edit($id,  $params);
	
	$log->PutEntry($result['id'],'редактировал телефон организации', NULL,121,NULL,$params['phone'],$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_phones")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierPhoneItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил телефон организации', NULL,121,NULL,NULL,$user_id);
}
//подсветка утверждений карты
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmed_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_active_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_name'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}//РАБОТА С КОНТАКТАМИ
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_contact")){
	$sm=new SmartyAj;
	
	$user_id=abs((int)$_POST['user_id']);
	
	$rg=new SupplierContactGroup;
	
	$sm->assign('items',$rg->GetItemsByIdArr($user_id));
	$sm->assign('word','contact');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Контакты');
	
	$rrg=new SupplierContactKindGroup;
	$sm->assign('kinds',$rrg->GetItemsArr());
	
	$sm->assign('can_edit', $au->user_rights->CheckAccess('w',121));
	
	
	$ret=$sm->fetch('org/contacts.html');
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	$log->PutEntry($result['id'],'добавил контакт организации', NULL,121,NULL, 'имя: '.SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9).', должность:'.SecStr(iconv("utf-8","windows-1251",$_POST['position']),9).' дата рождения: '.$birthdate,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	$log->PutEntry($result['id'],'редактировал контакт организации', NULL,121,NULL,'имя: '.SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9).', должность:'.SecStr(iconv("utf-8","windows-1251",$_POST['position']),9).' дата рождения: '.$birthdate ,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierContactItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил контакт организации', NULL,121,NULL,NULL,$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="add_value_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	
	$log->PutEntry($result['id'],'добавил данные контакта организации', NULL,121,NULL,SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
	
}elseif(isset($_POST['action'])&&($_POST['action']=="edit_nest_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	$log->PutEntry($result['id'],'редактировал данные контакта организации', NULL,121,NULL,SecStr(iconv("utf-8","windows-1251",$_POST['value']),9),$user_id);
}elseif(isset($_POST['action'])&&($_POST['action']=="delete_nest_contact")){
	//dostup
	if(!$au->user_rights->CheckAccess('w',121)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	
	$user_id=abs((int)$_POST['user_id']);
	$id=abs((int)$_POST['id']);
	
	
	$ri=new SupplierContactDataItem;
	$ri->Del($id);
	
	$log->PutEntry($result['id'],'удалил данные контакта организации', NULL,121,NULL,NULL,$user_id);
	

}elseif(isset($_POST['action'])&&($_POST['action']=="load_phones_contact")){
//подгрузка рабочих телефонов для копирования контакта
	$sm=new SmartyAj;
	
	$log=new ActionLog;
	$supplier_id=abs((int)$_POST['supplier_id']);
	$contact_id=abs((int)$_POST['contact_id']);
	
	$rg=new SupplierContactGroup;
	$_cg=new SupplierContactDataGroup;
	$_si=new OrgItem;
	$supplier=$_si->GetItemById($supplier_id);
	
	$data=$_cg->GetItemsByIdArr($contact_id);
	$data1=array();
	foreach($data as $k=>$v) if($v['kind_id']==1) $data1[]=$v; 
	
	$sm->assign('items',$data1);
	$sm->assign('word','contact');
	$sm->assign('user_id',$user_id);
	$sm->assign('named','Контакты');
	
   
	
	$ret=$sm->fetch('org/contacts_for_copy.html');

}elseif(isset($_POST['action'])&&($_POST['action']=="copy_contact_contact")){
	//копирование контакта
	
//	die();
	
	if(!$au->user_rights->CheckAccess('w',121)){
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
	
	$log->PutEntry($result['id'],'добавил контакт организации при копировании контакта', NULL,121,NULL,'имя: '.SecStr(iconv("utf-8","windows-1251",$_POST['fio']),9).' должность: '.SecStr(iconv("utf-8","windows-1251",$_POST['position']),9).' дата рождения: '.$birthdate,$user_id);
	
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
		
		 
		$log->PutEntry((int)$result['id'],'добавил данные контакта организации при копировании контакта', NULL,121,NULL,SecStr(iconv("utf-8","windows-1251",$valarr[0].' '.$valarr[1])), (int)$user_id);	
	}
	
	foreach($data as $k=>$v){
		if(!in_array($v['kind_id'],  array(2,8))) continue;

		
		$ri->Add(array(
			'value'=>SecStr(iconv("utf-8","windows-1251",$v['value']),9),
			'value1'=>SecStr(iconv("utf-8","windows-1251",$v['value1']),9),
			'contact_id'=>$contact_id,
			'kind_id'=>$v['kind_id']
		));
		
		 
		$log->PutEntry((int)$result['id'],'добавил данные контакта организации при копировании контакта', NULL,121,NULL,SecStr(iconv("utf-8","windows-1251",$v['value'].' '.$v['value1'])), (int)$user_id);	
	}

	

	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="confirm_auto")){
		$_si=new OrgItem;
		
	
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
		
		
		$log->PutEntry($result['id'],'автоматическое утверждение заполнение карты организации', NULL,147, NULL, $description,$id);	
		
		
	
	
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
		
		$name=iconv("utf-8","windows-1251//TRANSLIT",$_POST['name']);
		
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
		
				$log->PutEntry($result['id'],'добавил город', NULL,584, NULL,$params['name'],$id);	
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
		
				$log->PutEntry($result['id'],'редактировал город', NULL,584, NULL,$params['name'],$id);	
			
		
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
				$_si1=new OrgItem;
				$si1=$_si1->getitembyid($supplier_id);
				
				$description='город '.SecStr($sci['name']).', '.SecStr($sri['name']).', '.SecStr($sdi['name']).', контрагент '.SecStr($si1['full_name']);
				
				$log->PutEntry($result['id'],'добавил город организации', NULL,121, NULL,$description,$supplier_id);	
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
			$_si1=new OrgItem;
			$si1=$_si1->getitembyid($supplier_id);
			
			$description='город '.SecStr($sci['name']).', '.SecStr($sri['name']).', '.SecStr($sdi['name']).', контрагент '.SecStr($si1['full_name']);
			
			$log->PutEntry($result['id'],'удалил город организации', NULL,121, NULL,$description,$supplier_id);	
		}
	
		
		//print_r($names);
		
		
}
//обновить список городов
	
elseif(isset($_POST['action'])&&(($_POST['action']=="load_supplier_cities"))){
	$_sg=new SupplierCitiesGroup;
	
	$supplier_id=abs((int)$_POST['supplier_id']);
	$_supplier=new OrgItem;
	$supplier=$_supplier->GetItemByid($supplier_id);
	
	$sg=$_sg->GetItemsByIdArr($supplier_id);
	
	$sm=new SmartyAj;
	
	$sm->assign('cities', $sg);
	$sm->assign('can_modify', $au->user_rights->CheckAccess('w',584));
	
	$sm->assign('user', $supplier);
	
	
	$ret=$sm->fetch('org/cities_table.html');
	
//load_region
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

//настройка реестра
elseif(isset($_POST['action'])&&(($_POST['action']=="mode_reestr"))){
	$_views=new Org_ViewGroup;
	$_view=new Org_ViewItem;
	
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
	$_views=new Org_ViewGroup;
 
	 
	
	$_views->Clear($result['id']);
	 
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>