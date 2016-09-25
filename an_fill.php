<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������

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
$smarty->assign("SITETITLE",'����� ������������� �������');

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


$log->PutEntry($result['id'],'������� � ������ ������������� �������',NULL,NULL,NULL,NULL);


//������ � �������
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
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	

/******************************************** ������� ���������� *************************************************/
	
	
	// ������� ����������
	$as=new AnFill;
	$prefix=$as->prefix;
	
	//���������� ����� ����������� ����� ��� ������
	$fields=array();
	
	
	/*
	
� ���-��:
�������� �� ���-��, ����:
���:
���� ���-��:
*/
	
	 
	
	//���������� ���������
	//����� ������� �� ���� � �����.
	
	/*
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_no', '� ���-��', '', NULL,  true, '��������',   'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_prolongation', '�������� �� ���-��, ����', '', NULL, true, '��������', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_pdate', '���� ���-��', '', NULL, true, '��������', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_dog', '������� ��������� ��������',  0,  NULL, true,  '��������', 'contract_no' );
	
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'supplier_contract', '��������', 0, $_fc_fields, isset($_GET['supplier_contract'.$prefix])&&($_GET['supplier_contract'.$prefix]==1), '��������',  'supplier_contract');
	$_fc->SetDataSource(new SupContractGroup());
	
	
	$fields[]=$_fc;*/
	//����� ���������� ���������
	
	
	//��������� ��������
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_no', '� ���-��', '', NULL,  true, '���. ��������',   'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_prolongation', '�������� �� ���-��, ����', '', NULL, true, '���. ��������', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_pdate', '���� ���-��', '', NULL, true, '���. ��������', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_dog', '������� ��������� ��������',  0,  NULL, true,  '���. ��������', 'contract_no' );
	
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'supplier_contract',  '��������� ��������', 0, $_fc_fields, isset($_GET['supplier_contract'.$prefix])&&($_GET['supplier_contract'.$prefix]==1),  '��������� ��������',  'supplier_contract');
	$_fc->SetDataSource(new AnFillSupContractGroup());
	
	
	$fields[]=$_fc;
	//����� ���������� ���������
	
	//�������� ��������
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_no', '� ���-��', '', NULL,  true, '��. ��������',   'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_prolongation', '�������� �� ���-��, ����', '', NULL, true, '��. ��������', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'contract_pdate', '���� ���-��', '', NULL, true, '��. ��������', 'contract_no' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_dog', '������� ��������� ��������',  0,  NULL, true,  '��. ��������', 'contract_no' );
	
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX,  'supplier_contract',  '�������� ��������', 0, $_fc_fields, isset($_GET['supplier_contract'.$prefix])&&($_GET['supplier_contract'.$prefix]==1),  '�������� ��������',  'supplier_contract');
	$_fc->SetDataSource(new AnFillSupContractGroupIn());
	
	
	$fields[]=$_fc;
	//����� ���������� ��. ���������
	
	
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'has_uch', '������� ��������� ������������� ����������', '0', NULL, isset($_GET['has_uch'.$prefix])&&($_GET['has_uch'.$prefix]==1));
	
	
	
	//���������� ���������
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'name', '�������, ���, ��������', '', NULL, true, '�������',  'name'  );
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'position', '���������', '', NULL, true, '�������', 'name' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'birthdate', '���� ��������', '', NULL, true, '�������', 'name' );
	
	//��� ���� �� ���������� ������??? ������� � ������???
	$_kg=new SupplierContactKindGroup();
	$kg=$_kg->GetItemsArr();
	foreach($kg as $k=>$v){
		 $_fc_fields[]=new AnFillSubsequentEntry(2, 'pc_name', 'value',  $v['name'],'',NULL, true,  '�������', 'name',  'pc_name' );
	}
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'contacts', '���������� ����������', 0, $_fc_fields, isset($_GET['contacts'.$prefix])&&($_GET['contacts'.$prefix]==1),'�������', 'name',  'pc_name');
	
	$_fc->SetDataSource(new SupplierContactGroup());
	
	$fields[]=$_fc;
	//����� ����������� ���������
	
	
	$_fc=new AnFillSelectEntry(AnFillAbstractEntry::SELECT, 'opf_id', '���', '0', NULL, isset($_GET['opf_id'.$prefix])&&($_GET['opf_id'.$prefix]==1));
	$_fc->SetDataSource(new OpfItem());
	$fields[]=$_fc;
	
	
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'inn', '���', '', NULL, isset($_GET['inn'.$prefix])&&($_GET['inn'.$prefix]==1));
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'kpp', '���', '', NULL, isset($_GET['kpp'.$prefix])&&($_GET['kpp'.$prefix]==1));
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'okpo', '����', '', NULL, isset($_GET['okpo'.$prefix])&&($_GET['okpo'.$prefix]==1));
	
	
	//���������� �������
	$_fc_fields=array();
	
	//$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'city_id', '����� �����������', '0', NULL, true, '����� �����������', 'name' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'name', '�����', '', NULL, true, '�����', 'name' );
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'city_id', '����� ', 0, $_fc_fields, isset($_GET['city_id'.$prefix])&&($_GET['city_id'.$prefix]==1),'����� �����������',  'name',  'pc_name');
	$_fc->SetDataSource(new SupplierCitiesGroup());
	
	
	$fields[]=$_fc;
	//����� ����������� �������
	
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'chief', '����������� ��������', '', NULL,  isset($_GET['chief'.$prefix])&&($_GET['chief'.$prefix]==1));
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'main_accountant', '������� ���������',  '', NULL, isset($_GET['main_accountant'.$prefix])&&($_GET['main_accountant'.$prefix]==1));
	
	
	$fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'legal_address', '����������� ����c', '', NULL, isset($_GET['legal_address'.$prefix])&&($_GET['legal_address'.$prefix]==1));
	
	
	
	//���������� ���� �������
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'address', '�����', '', NULL, true, '����������� ������', 'address' );
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'address', '����������� ������ ', 0, $_fc_fields, isset($_GET['address'.$prefix])&&($_GET['address'.$prefix]==1),'����������� ������', 'address');
	$_fc->SetDataSource(new FaGroup());
	
	
	$fields[]=$_fc;
	//����� �����-�� ���� �������
	
	
	//���������� ����������
	$_fc_fields=array();
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'bank', '����', '', NULL, true, '����',  'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'city', '����� �����', '', NULL, true, '����', 'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'rs', '�/�', '', NULL, true, '����', 'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'ks', '�/�', '', NULL, true, '����', 'bank' );
	
	$_fc_fields[]=new AnFillSimpleEntry(AnFillAbstractEntry::SIMPLE, 'bik', '���', '', NULL, true, '����', 'bank' );
	
	
	$_fc=new AnFillComplexEntry(AnFillAbstractEntry::COMPLEX, 'banking_details', '���������', 0, $_fc_fields, isset($_GET['banking_details'.$prefix])&&($_GET['banking_details'.$prefix]==1),'���������',  'bank');
	$_fc->SetDataSource(new BDetailsGroup());
	
	
	$fields[]=$_fc;
	//����� ���������� ����������
	
	
	
	$_fc=new AnFillSelectEntry(AnFillAbstractEntry::SELECT, 'branch_id', '�������', '0', NULL, isset($_GET['branch_id'.$prefix])&&($_GET['branch_id'.$prefix]==1));
	$_fc->SetDataSource(new SupplierBranchesItem());
	$fields[]=$_fc;
	
	 $_fc=new AnFillSelectEntry(AnFillAbstractEntry::SELECT, 'subbranch_id', '����������', '0', NULL, isset($_GET['subbranch_id'.$prefix])&&($_GET['subbranch_id'.$prefix]==1));
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
	
	
	//$filetext='<em>��������, � ��� ��� ���� ��� ������� � ���� ������.</em>';
	$sm->assign('can_fill',  $au->user_rights->CheckAccess('w',598));
	
	
	//����������� �������� ������
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		$log->PutEntry($result['id'],'������ ����� ������������� ������� ������������',NULL,598,NULL, NULL);	
	}
	
	
	$sm->assign('log',$filetext);
	
	


/******************************* ������� ����� ��� �������� ************************************************/
	$as1=new AnFiles;
	$prefix=$as1->prefix;
	
	$decorator=new DBDecorator;
	$input_params=array();
	
	require_once('an_fill_includes.php');
	 
	//�����������...
	//���. ���.
	$id_count=1;
	
	$input_params[]=array('kind'=>'begin', 'label'=>'�����������');  //��������� ������ ������
	
	
		$input_params[]=new AnFilesItem('������������� ��������� �����������', $id_count, new ContractOrgItem(), 'contract_org.html', 117, false, NULL,  array('is_org'=>1), 'user_d_id',  'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'����������� ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//�������
		$input_params[]=new AnFilesItem('������� �����������', $id_count, new ContractItem(), 'contract.html', 87, false, NULL, array('is_org'=>1),   'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'����������� ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//���� ������
		$input_params[]=new AnFilesItem('���� ������ �����������', $id_count, new Supplier_Akt_Item(), 'akt.html', 87, false, NULL, array('is_org'=>1),  'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'����������� ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//����� �������
		$input_params[]=new AnFilesItem('����� ������� � �����������', $id_count, new Supplier_Sh_Item(), 'shema.html', 87, false, NULL, array('is_org'=>1),  'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'����������� ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//�����
		$input_params[]=new AnFilesItem('����� �����������', $id_count, new SupplierFileItem(), 'supplier_file.html', 87, false, NULL, array('is_org'=>1),  'user_d_id', 'supplier', 'ed_organization.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'����������� ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
	
	
	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'����������');  //��������� ������ ������
	
	
		$input_params[]=new AnFilesItem('������������� ��������� �����������', $id_count, new ContractUchItem(), 'uchcontract.html', 87, false, NULL,  array('is_org'=>0), 'user_d_id',  'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'���������� ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//�������
		$input_params[]=new AnFilesItem('������� �����������', $id_count, new ContractItem(), 'contract.html', 87, false, NULL, array('is_org'=>0),   'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'���������� ', new SupplierItem(), 'contract_file_folder'); 
		$id_count++;
		
		//���� ������
		$input_params[]=new AnFilesItem('���� ������ �����������', $id_count, new Supplier_Akt_Item(), 'akt.html', 87, false, NULL, array('is_org'=>0),  'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'���������� ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//����� �������
		$input_params[]=new AnFilesItem('����� ������� � �����������', $id_count, new Supplier_Sh_Item(), 'shema.html', 87, false, NULL, array('is_org'=>0),  'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'���������� ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
		
		//�����
		$input_params[]=new AnFilesItem('����� �����������', $id_count, new SupplierFileItem(), 'supplier_file.html', 87, false, NULL, array('is_org'=>0),  'user_d_id', 'supplier', 'supplier.php', AnFilesItem::BY_PARENT_DOC, 'sup_id',array('code', 'full_name'),'���������� ', new SupplierItem(), 'supplier_shema_file_folder'); 
		$id_count++;
	
	
	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'����������');  //��������� ������ ������
	
	
		 
		$input_params[]=new AnFilesItem('��������� ����������', $id_count, new UserPasportItem, 'document.html', 119, false, NULL, NULL,   'user_d_id',  'user',  'user_s.php', AnFilesItem::DO_NOT,  'user_id',array('name_s','login'),'��������� ', new UserSItem(), 'user_pasport_file_folder'); 
		$id_count++;
	
	
	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	
	
	
	
//	$input_params[]=array('kind'=>'begin', 'label'=>'���������� ����������'); //��������� ������ ������
		
		//���������� ����������
		/*$input_params[]=new AnFilesItem('���������� ����������', $id_count, new SpItem(), 'load_pl.html', 29, true, 35, array('additional_id'=>2),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
		//�������������
		$input_params[]=new AnFilesItem('�������������', $id_count, new SpsItem(), 'load_spl.html', 476, true, 36, array('additional_id'=>3, 'tab_page'=>2),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;*/
		
	 	

	//$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'����� � ���������'); //��������� ������ ������
		
		 
		$input_params[]=new AnFilesItem('����� � ���������', $id_count, new FilePoItem(), 'load.html', 28, true, 37, array('additional_id'=>1),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('������', $id_count, new FileLetItem(), 'load_l.html', 556, true, 38, array('additional_id'=>4, 'tab_page'=>'tabs-3'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('����� +/-', $id_count, new FilePmItem(), 'load_pm.html', 560, true, 47, array('additional_id'=>5, 'tab_page'=>'tabs-4'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
		
		//���������� ����������
		$input_params[]=new AnFilesItem('���������� ����������', $id_count, new SpItem(), 'load_pl.html', 29, true, 35, array('additional_id'=>2, 'tab_page'=>'tabs-5'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
		//�������������
		$input_params[]=new AnFilesItem('�������������', $id_count, new SpsItem(), 'load_spl.html', 476, true, 36, array('additional_id'=>3, 'tab_page'=>'tabs-6'),NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'file_folder'); 
		$id_count++;
		
		
	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'������'); //��������� ������ ������
		
	 
		$input_params[]=new AnFilesItem('����� ������', $id_count, new KvFileItem, 'komplekt_ved_file.html', 82, false, NULL, NULL, 'komplekt_ved_id',  'komplekt_ved', 'ed_komplekt.php', AnFilesItem::BY_PARENT_DOC,  'komplekt_ved_id', array('id'), '������ � ', new KomplItem(), 'komplekt_ved_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'�����'); //��������� ������ ������
	
		$input_params[]=new AnFilesItem('����� ���������� �����', $id_count, new BillFileItem, 'bill_file.html', 97, false, NULL, array('is_incoming'=>0),  'bill_id',  'bill', 'ed_bill.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array(  'code'), '���. ���� � ', new BillItem(),  'bill_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('����� ��������� �����', $id_count, new BillInFileItem, 'bill_in_file.html', 606, false, NULL, array('is_incoming'=>1), 'bill_id',  'bill', 'ed_bill_in.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array(  'code'), '��. ���� � ', new BillInItem(), 'bill_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'�����������/����������'); //��������� ������ ������
		
		$input_params[]=new AnFilesItem('����� ����������', $id_count, new AccFileItem, 'acc_file.html', 235, false, NULL, array('is_incoming'=>0),  'acceptance_id',  'acceptance', 'ed_acc.php', AnFilesItem::BY_PARENT_DOC,  'acc_id', array(  'id'), '���������� � ', new AccItem(),  'acceptance_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('����� �����������', $id_count, new AccInFileItem, 'acc_in_file.html', 664, false, NULL, array('is_incoming'=>1),  'acceptance_id',  'acceptance', 'ed_acc_in.php', AnFilesItem::BY_PARENT_DOC, 'acc_id', array(  'id'), '����������� � ', new AccInItem(),  'acceptance_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'������'); //��������� ������ ������
		
		$input_params[]=new AnFilesItem('����� ���. ������', $id_count, new PayFileItem, 'pay_file.html', 272, false, NULL, array('is_incoming'=>0),  'payment_id ',  'payment', 'ed_pay.php', AnFilesItem::BY_PARENT_DOC,  'pay_id', array(  'code'), '���. ������ � ', new PayItem(),  'payment_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('����� ��. ������', $id_count, new PayInFileItem, 'pay_in_file.html', 683, false, NULL, array('is_incoming'=>1),  'payment_id ',  'payment', 'ed_pay_in.php', AnFilesItem::BY_PARENT_DOC,  'pay_id', array(  'code'), '��. ������ � ', new PayInItem(),  'payment_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'������ ��������'); //��������� ������ ������
		
	 
	 $decorator=new DBDecorator;
	 if(!$au->user_rights->CheckAccess('w',834)&&$au->user_rights->CheckAccess('w',875)){
		//���� ����� �� �������� ��������, ��������������� ���� �����������

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
	 
	 	 
		$input_params[]=new AnFilesItem('����� ������� ��������', $id_count, new CashFileItem, 'cash_file.html', 836, false, NULL,  NULL, 'payment_id',  'cash', 'ed_cash.php', AnFilesItem::BY_PARENT_DOC,  'pay_id', array('code'), '������ � ', new CashItem(), 'cash_file_folder', $decorator); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'������������'); //��������� ������ ������
	 
		$input_params[]=new AnFilesItem('����� ������������', $id_count, new TrustFileItem, 'trust_file.html', 208, false, NULL, NULL, 'trust_id',  'trust', 'ed_trust.php', AnFilesItem::BY_PARENT_DOC,  'trust_id', array('id'), '������������ � ', new TrustItem(), 'trust_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	$input_params[]=array('kind'=>'begin', 'label'=>'��������������'); //��������� ������ ������
	 
		$input_params[]=new AnFilesItem('����� ���. ��������', $id_count, new InvFileItem, 'inv_file.html', 326, false, NULL, NULL, 'inventory_id',  'inventory', 'ed_inv.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array('code'), '��� � ', new InvItem(), 'inventory_file_folder'); 
		$id_count++;
		
		$input_params[]=new AnFilesItem('����� ���. ��������������', $id_count, new InvCalcFileItem, 'invcalc_akt_file.html', 452, false, NULL, NULL, 'invcalc_id',  'invcalc', 'ed_invcalc.php', AnFilesItem::BY_PARENT_DOC,  'bill_id', array('code'), '��� � ', new InvcalcItem(), 'invcalc_file_folder'); 
		$id_count++;

	$input_params[]=array('kind'=>'end', 'label'=>''); //��������� ������ ������
	
	
	
	//������� ��������� ����
	//print_r($_GET);
	if(isset($_GET['fields'.$prefix])||is_array($_GET['fields'.$prefix])) $decorator->AddEntry(new UriEntry('fields',implode(',',$_GET['fields'.$prefix])));	
	
	$decorator->AddEntry(new UriEntry('tab_page',2));	
	
	$decorator->AddEntry(new UriEntry('print',$print));	
	 
	
	
	
	$filetext2=$as1->ShowData($input_params, $result, 'an_files/an_files'.$print_add.'.html',$decorator,'an_fill.php',
		isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)),
		$au->user_rights->CheckAccess('w',879),
		$alls);
	

	
//����������� �������� ������
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))){
		$log->PutEntry($result['id'],'������ ����� ����� ��� ��������',NULL,878,NULL, NULL);	
	}
	
	
	$sm->assign('can_files',  $au->user_rights->CheckAccess('w',878));
	
	
	$sm->assign('log2',$filetext2);
	
	
	
	
	//����� ����
	
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

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

if($print==0) $smarty->display('bottom.html');
else $smarty->display('bottom_print.html');
unset($smarty);
?>