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
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');


require_once('classes/an_fill.php');

require_once('classes/an_fill_abstract_entry.php');
require_once('classes/an_fill_simple_entry.php');
require_once('classes/an_fill_complex_entry.php');
require_once('classes/an_fill_subsequent_entry.php');
require_once('classes/an_fill_select_entry.php');

require_once('classes/suppliercontactkindgroup.php');
require_once('classes/suppliercontactgroup.php');
require_once('classes/supplier_cities_group.php');
require_once('classes/fagroup.php');
require_once('classes/bdetailsgroup.php');

require_once('classes/supcontract_group.php');

require_once('classes/an_fill_supcontract_group.php');

require_once('classes/an_fill_supcontract_group_in.php');

require_once('classes/db_decorator.php');

require_once('classes/an_files.php');
require_once('classes/an_files_item.php');


require_once('classes/supplier_branches_item.php');
require_once('classes/supplier_branches_group.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Заполняемость данными');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;



if(!$au->user_rights->CheckAccess('w',598)&&!$au->user_rights->CheckAccess('w',878)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);


if($print!=0){
	if(!$au->user_rights->CheckAccess('w',599)&&!$au->user_rights->CheckAccess('w',879)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


$log->PutEntry($result['id'],'перешел в Отчеты Заполненность данными',NULL,NULL,NULL,NULL);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print_alb.html');
unset($smarty);


	$_menu_id=57;
	
	if($print==0) include('inc/menu.php');
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	

/******************************************** вкладка Контрагент *************************************************/
	
	
	// вкладка Контрагент
	$as=new AnFill;
	$prefix=$as->prefix;
	
	//сформируем набор проверяемых полей для отчета
	$fields=array();
	
	
	/*
	
№ дог-ра:
отсрочка по дог-ру, дней:
дни:
дата дог-ра:
*/
	
	 
	
	//справочник договоров
	//нужно разбить на вход и исход.
	
	/*
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_no', '№ дог-ра', '', NULL,  true, 'Договоры',   'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_prolongation', 'Отсрочка по дог-ру, дней', '', NULL, true, 'Договоры', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_pdate', 'Дата дог-ра', '', NULL, true, 'Договоры', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_dog', 'Наличие оригинала договора',  0,  NULL, true,  'Договоры', 'contract_no' );
	
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'supplier_contract', 'Договоры', 0, $_fc_fields, isset($_GET['supplier_contract'.$prefix])&&($_GET['supplier_contract'.$prefix]==1), 'Договоры',  'supplier_contract');
	$_fc->SetDataSource(new SupContractGroup());
	
	
	$fields[]=$_fc;*/
	//конец справочник договоров
	
	
	//исходящие договоры
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_no', '№ дог-ра', '', NULL,  true, 'Исх. договоры',   'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_prolongation', 'Отсрочка по дог-ру, дней', '', NULL, true, 'Исх. договоры', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_pdate', 'Дата дог-ра', '', NULL, true, 'Исх. договоры', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_dog', 'Наличие оригинала договора',  0,  NULL, true,  'Исх. договоры', 'contract_no' );
	
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'supplier_contract',  'Исходящие договоры', 0, $_fc_fields, isset($_GET['supplier_contract'.$prefix])&&($_GET['supplier_contract'.$prefix]==1),  'Исходящие договоры',  'supplier_contract');
	$_fc->SetDataSource(new AnFillSupContractGroup());
	
	
	$fields[]=$_fc;
	//конец справочник договоров
	
	//входящие договоры
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_no', '№ дог-ра', '', NULL,  true, 'Вх. договоры',   'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_prolongation', 'Отсрочка по дог-ру, дней', '', NULL, true, 'Вх. договоры', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_pdate', 'Дата дог-ра', '', NULL, true, 'Вх. договоры', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_dog', 'Наличие оригинала договора',  0,  NULL, true,  'Вх. договоры', 'contract_no' );
	
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX,  'supplier_contract',  'Входящие договоры', 0, $_fc_fields, isset($_GET['supplier_contract'.$prefix])&&($_GET['supplier_contract'.$prefix]==1),  'Входящие договоры',  'supplier_contract');
	$_fc->SetDataSource(new AnFillSupContractGroupIn());
	
	
	$fields[]=$_fc;
	//конец справочник вх. договоров
	
	
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_uch', 'Наличие оригинала учредительных документов', '0', NULL, isset($_GET['has_uch'.$prefix])&&($_GET['has_uch'.$prefix]==1));
	
	
	
	//справочник контактов
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'name', 'Фамилия, имя, отчество', '', NULL, true, 'Контакт',  'name'  );
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'position', 'Должность', '', NULL, true, 'Контакт', 'name' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'birthdate', 'День рождения', '', NULL, true, 'Контакт', 'name' );
	
	//как быть со вложенными полями??? телефон и прочее???
	$_kg=new SupplierContactKindGroup();
	$kg=$_kg->GetItemsArr();
	foreach($kg as $k=>$v){
		 $_fc_fields[]=new AnFillSubsequentEntry(2, 'pc_name', 'value',  $v['name'],'',NULL, true,  'Контакт', 'name',  'pc_name' );
	}
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'contacts', 'Контактная информация', 0, $_fc_fields, isset($_GET['contacts'.$prefix])&&($_GET['contacts'.$prefix]==1),'Контакт', 'name',  'pc_name');
	
	$_fc->SetDataSource(new SupplierContactGroup());
	
	$fields[]=$_fc;
	//конец справочника контактов
	
	
	$_fc=new AnFillSelectEntry(AnFillAbstractEntry::SELECT, 'opf_id', 'ОПФ', '0', NULL, isset($_GET['opf_id'.$prefix])&&($_GET['opf_id'.$prefix]==1));
	$_fc->SetDataSource(new OpfItem());
	$fields[]=$_fc;
	
	
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'inn', 'ИНН', '', NULL, isset($_GET['inn'.$prefix])&&($_GET['inn'.$prefix]==1));
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'kpp', 'КПП', '', NULL, isset($_GET['kpp'.$prefix])&&($_GET['kpp'.$prefix]==1));
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'okpo', 'ОКПО', '', NULL, isset($_GET['okpo'.$prefix])&&($_GET['okpo'.$prefix]==1));
	
	
	//справочник городов
	$_fc_fields=array();
	
	//$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'city_id', 'Город контрагента', '0', NULL, true, 'Город контрагента', 'name' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'name', 'Город', '', NULL, true, 'Город', 'name' );
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'city_id', 'Город ', 0, $_fc_fields, isset($_GET['city_id'.$prefix])&&($_GET['city_id'.$prefix]==1),'Город контрагента',  'name',  'pc_name');
	$_fc->SetDataSource(new SupplierCitiesGroup());
	
	
	$fields[]=$_fc;
	//конец справочника городов
	
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'chief', 'Генеральный директор', '', NULL,  isset($_GET['chief'.$prefix])&&($_GET['chief'.$prefix]==1));
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'main_accountant', 'Главный бухгалтер',  '', NULL, isset($_GET['main_accountant'.$prefix])&&($_GET['main_accountant'.$prefix]==1));
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'legal_address', 'Юридический адреc', '', NULL, isset($_GET['legal_address'.$prefix])&&($_GET['legal_address'.$prefix]==1));
	
	
	
	//справочник факт адресов
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'address', 'Адрес', '', NULL, true, 'Фактические адреса', 'address' );
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'address', 'Фактические адреса ', 0, $_fc_fields, isset($_GET['address'.$prefix])&&($_GET['address'.$prefix]==1),'Фактические адреса', 'address');
	$_fc->SetDataSource(new FaGroup());
	
	
	$fields[]=$_fc;
	//конец справ-ка факт адресов
	
	
	//справочник реквизитов
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'bank', 'Банк', '', NULL, true, 'Банк',  'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'city', 'Город банка', '', NULL, true, 'Банк', 'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'rs', 'р/с', '', NULL, true, 'Банк', 'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'ks', 'к/с', '', NULL, true, 'Банк', 'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'bik', 'БИК', '', NULL, true, 'Банк', 'bank' );
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'banking_details', 'Реквизиты', 0, $_fc_fields, isset($_GET['banking_details'.$prefix])&&($_GET['banking_details'.$prefix]==1),'Реквизиты',  'bank');
	$_fc->SetDataSource(new BDetailsGroup());
	
	
	$fields[]=$_fc;
	//конец справочник реквизитов
	
	
	
	$_fc=new AnFillSelectEntry(AnFillAbstractEntry::SELECT, 'branch_id', 'Отрасль', '0', NULL, isset($_GET['branch_id'.$prefix])&&($_GET['branch_id'.$prefix]==1));
	$_fc->SetDataSource(new SupplierBranchesItem());
	$fields[]=$_fc;
	
	 $_fc=new AnFillSelectEntry(AnFillAbstractEntry::SELECT, 'subbranch_id', 'Подотрасль', '0', NULL, isset($_GET['subbranch_id'.$prefix])&&($_GET['subbranch_id'.$prefix]==1));
	$_fc->SetDataSource(new SupplierBranchesItem());
	$fields[]=$_fc;
	
	
	
	if(isset($_GET['quests_fill_only'.$prefix])) $quests_fill_only=1;
	else $quests_fill_only=0;
	
	if(isset($_GET['quests_unfill_only'.$prefix])) $quests_unfill_only=1;
	else $quests_unfill_only=0;
	
	
	
	$decorator=new DBDecorator;
	
	
	
	
	$filetext=$as->ShowData($fields, 
	'an_fill/an_fill_supplier'.$print_add.'.html',
	
	 $decorator,  
	 
	isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)), 
	$au->user_rights->CheckAccess('w',599), $print, $result['org_id'],
	$quests_fill_only, $quests_unfill_only
	); 
	
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	$sm->assign('can_fill',  $au->user_rights->CheckAccess('w',598));
	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		$log->PutEntry($result['id'],'открыл отчет Заполненность данными контрагентов',NULL,598,NULL, NULL);	
	}
	
	
	$sm->assign('log',$filetext);
	
	


/******************************* вкладка Файлы без описания ************************************************/
	$as1=new AnFiles;
	$prefix=$as1->prefix;
	
	$decorator=new DBDecorator;
	$input_params=array();
	
	require_once('an_fill_includes.php');
	 
	//огранизации...
	//учр. док.
	$id_count=1;
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Организация');  //Открываем группу данных
	
	
		$input_params[]=new AnFilesItem('Учредительные документы организации', $id_count, new ContractOrgItem(), 'contract_org.html', 117, false, NULL,  array('is_org'=>1), 'user_d_id',  'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'организация ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//договор
		$input_params[]=new AnFilesItem('Договор организации', $id_count, new ContractItem(), 'contract.html', 87, false, NULL, array('is_org'=>1),   'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'организация ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//акты сверок
		$input_params[]=new AnFilesItem('Акты сверки организации', $id_count, new Supplier_Akt_Item(), 'akt.html', 87, false, NULL, array('is_org'=>1),  'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'организация ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//схема проезда
		$input_params[]=new AnFilesItem('Схема проезда к организации', $id_count, new Supplier_Sh_Item(), 'shema.html', 87, false, NULL, array('is_org'=>1),  'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'организация ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//файлы
		$input_params[]=new AnFilesItem('Файлы организации', $id_count, new SupplierFileItem(), 'supplier_file.html', 87, false, NULL, array('is_org'=>1),  'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'организация ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
	
	
	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Контрагент');  //Открываем группу данных
	
	
		$input_params[]=new AnFilesItem('Учредительные документы контрагента', $id_count, new ContractUchItem(), 'uchcontract.html', 87, false, NULL,  array('is_org'=>0), 'user_d_id',  'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'контрагент ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//договор
		$input_params[]=new AnFilesItem('Договор контрагента', $id_count, new ContractItem(), 'contract.html', 87, false, NULL, array('is_org'=>0),   'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'контрагент ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//акты сверок
		$input_params[]=new AnFilesItem('Акты сверки контрагента', $id_count, new Supplier_Akt_Item(), 'akt.html', 87, false, NULL, array('is_org'=>0),  'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'контрагент ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//схема проезда
		$input_params[]=new AnFilesItem('Схема проезда к контрагенту', $id_count, new Supplier_Sh_Item(), 'shema.html', 87, false, NULL, array('is_org'=>0),  'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'контрагент ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//файлы
		$input_params[]=new AnFilesItem('Файлы контрагента', $id_count, new SupplierFileItem(), 'supplier_file.html', 87, false, NULL, array('is_org'=>0),  'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'контрагент ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
	
	
	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Сотрудники');  //Открываем группу данных
	
	
		 
		$input_params[]=new AnFilesItem('Документы сотрудника', $id_count, new UserPasportItem, 'document.html', 119, false, NULL, NULL,   'user_d_id',  'user',  'user_s.php', AnFilesItem::DO_NOT,  'user_id',array('name_s','login'),'сотрудник ', new UserSItem(), 'user_pasport_file_folder'); 
		$id_count++;
	
	
	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	
	
	
	
//	$input_params[]=array('kind'=>'begin', 'label'=>'Справочная информация'); //Открываем группу данных
		
		//справочная информация
		/*$input_params[]=new AnFilesItem('Справочная информация', $id_count, new SpItem(), 'load_pl.html', 29, true, 35, array('additional_id'=>2),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
		//спецдокументы
		$input_params[]=new AnFilesItem('Спецдокументы', $id_count, new SpsItem(), 'load_spl.html', 476, true, 36, array('additional_id'=>3, 'tab_page'=>2),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;*/
		
	 	

	//$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Файлы и документы'); //Открываем группу данных
		
		 
		$input_params[]=new AnFilesItem('Файлы и документы', $id_count, new FilePoItem(), 'load.html', 28, true, 37, array('additional_id'=>1),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('Письма', $id_count, new FileLetItem(), 'load_l.html', 556, true, 38, array('additional_id'=>4, 'tab_page'=>'tabs-3'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('Файлы +/-', $id_count, new FilePmItem(), 'load_pm.html', 560, true, 47, array('additional_id'=>5, 'tab_page'=>'tabs-4'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
		
		//справочная информация
		$input_params[]=new AnFilesItem('Справочная информация', $id_count, new SpItem(), 'load_pl.html', 29, true, 35, array('additional_id'=>2, 'tab_page'=>'tabs-5'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
		//спецдокументы
		$input_params[]=new AnFilesItem('Спецдокументы', $id_count, new SpsItem(), 'load_spl.html', 476, true, 36, array('additional_id'=>3, 'tab_page'=>'tabs-6'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Заявки'); //Открываем группу данных
		
	 
		$input_params[]=new AnFilesItem('Файлы заявки', $id_count, new KvFileItem, 'komplekt_ved_file.html', 82, false, NULL, NULL, 'komplekt_ved_id',  'komplekt_ved', 'ed_komplekt.php', AnFilesItem::BY_PARENT_DOC,  'komplekt_ved_id', array('id'), 'заявка № ', new KomplItem(), 'komplekt_ved_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Счета'); //Открываем группу данных
	
		$input_params[]=new AnFilesItem('Файлы исходящего счета', $id_count, new BillFileItem, 'bill_file.html', 97, false, NULL, array('is_incoming'=>0),  'bill_id',  'bill', 'ed_bill.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array(  'code'), 'исх. счет № ', new BillItem(),  'bill_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('Файлы входящего счета', $id_count, new BillInFileItem, 'bill_in_file.html', 606, false, NULL, array('is_incoming'=>1), 'bill_id',  'bill', 'ed_bill_in.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array(  'code'), 'вх. счет № ', new BillInItem(), 'bill_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Поступления/реализации'); //Открываем группу данных
		
		$input_params[]=new AnFilesItem('Файлы реализации', $id_count, new AccFileItem, 'acc_file.html', 235, false, NULL, array('is_incoming'=>0),  'acceptance_id',  'acceptance', 'ed_acc.php', AnFilesItem::BY_PARENT_DOC,  'acc_id', array(  'id'), 'реализация № ', new AccItem(),  'acceptance_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('Файлы поступления', $id_count, new AccInFileItem, 'acc_in_file.html', 664, false, NULL, array('is_incoming'=>1),  'acceptance_id',  'acceptance', 'ed_acc_in.php', AnFilesItem::BY_PARENT_DOC, 'acc_id', array(  'id'), 'поступление № ', new AccInItem(),  'acceptance_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Оплаты'); //Открываем группу данных
		
		$input_params[]=new AnFilesItem('Файлы исх. оплаты', $id_count, new PayFileItem, 'pay_file.html', 272, false, NULL, array('is_incoming'=>0),  'payment_id ',  'payment', 'ed_pay.php', AnFilesItem::BY_PARENT_DOC,  'pay_id', array(  'code'), 'исх. оплата № ', new PayItem(),  'payment_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('Файлы вх. оплаты', $id_count, new PayInFileItem, 'pay_in_file.html', 683, false, NULL, array('is_incoming'=>1),  'payment_id ',  'payment', 'ed_pay_in.php', AnFilesItem::BY_PARENT_DOC,  'pay_id', array(  'code'), 'вх. оплата № ', new PayInItem(),  'payment_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Расход наличных'); //Открываем группу данных
		
	 
	 $decorator=new DBDecorator;
	 if(!$au->user_rights->CheckAccess('w',834)&&$au->user_rights->CheckAccess('w',875)){
		//есть права на просмотр доставок, экспедированией всех сотрудников

		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('doc.kind_id', NULL, SqlEntry::IN_VALUES, NULL,array(2,3)));		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		
		$decorator->AddEntry(new SqlEntry('doc.manager_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('doc.responsible_user_id',$result['id'], SqlEntry::E));
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		//echo 'zz';
	}elseif(!$au->user_rights->CheckAccess('w',834)){
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('doc.manager_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('doc.responsible_user_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	} 
	 
	 	 
		$input_params[]=new AnFilesItem('Файлы расхода наличных', $id_count, new CashFileItem, 'cash_file.html', 836, false, NULL,  NULL, 'payment_id',  'cash', 'ed_cash.php', AnFilesItem::BY_PARENT_DOC,  'pay_id', array('code'), 'расход № ', new CashItem(), 'cash_file_folder', $decorator); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Доверенность'); //Открываем группу данных
	 
		$input_params[]=new AnFilesItem('Файлы доверенности', $id_count, new TrustFileItem, 'trust_file.html', 208, false, NULL, NULL, 'trust_id',  'trust', 'ed_trust.php', AnFilesItem::BY_PARENT_DOC,  'trust_id', array('id'), 'доверенность № ', new TrustItem(), 'trust_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'Инвентаризация'); //Открываем группу данных
	 
		$input_params[]=new AnFilesItem('Файлы инв. остатков', $id_count, new InvFileItem, 'inv_file.html', 326, false, NULL, NULL, 'inventory_id',  'inventory', 'ed_inv.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array('code'), 'акт № ', new InvItem(), 'inventory_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('Файлы инв. взаиморасчетов', $id_count, new InvCalcFileItem, 'invcalc_akt_file.html', 452, false, NULL, NULL, 'invcalc_id',  'invcalc', 'ed_invcalc.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array('code'), 'акт № ', new InvcalcItem(), 'invcalc_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //закрываем группу данных
	
	
	
	//занесем выбранные поля
	//print_r($_GET);
	if(isset($_GET['fields'.$prefix])||is_array($_GET['fields'.$prefix])) $decorator->AddEntry(new UriEntry('fields',implode(',',$_GET['fields'.$prefix])));	
	
	$decorator->AddEntry(new UriEntry('tab_page',2));	
	
	$decorator->AddEntry(new UriEntry('print',$print));	
	 
	
	
	
	$filetext2=$as1->ShowData($input_params, $result, 'an_files/an_files'.$print_add.'.html',$decorator,'an_fill.php',
		isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)),
		$au->user_rights->CheckAccess('w',879),
		$alls);
	

	
//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))){
		$log->PutEntry($result['id'],'открыл отчет Файлы без описания',NULL,878,NULL, NULL);	
	}
	
	
	$sm->assign('can_files',  $au->user_rights->CheckAccess('w',878));
	
	
	$sm->assign('log2',$filetext2);
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	$content=$sm->fetch('an_fill/an_fill_form'.$print_add.'.html');
	
	
	
	
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