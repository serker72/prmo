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

require_once('classes/useritem.php');
require_once('classes/user_s_item.php');
//require_once('classes/user_d_item.php');

require_once('classes/search.php');
//require_once('classes/kpgroup.php');




$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'GYDEX.поиск');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

 //журнал событий 
$log=new ActionLog;
$log->PutEntry($result['id'],'открыл раздел Поиск по сайту');
 
//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	//die();
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	
	
	$_search=new Search;
	
	
	
	//контрагенты
	if($au->user_rights->CheckAccess('w',91)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_Supplier(
			'Контрагенты',
			'   from supplier as p 
			left join opf as po on p.opf_id=po.id
			left join supplier_contact as sc on sc.supplier_id=p.id and sc.is_shown=1
			
			left join user as crea on crea.id=p.created_id
			
			left join supplier_responsible_user as suresp on suresp.supplier_id=p.id
			left join user as resp on resp.id=suresp.user_id
			 ',
			 array('p.full_name','sc.name', 'crea.name_s', 'resp.name_s'),
			 array('Название','ФИО контакта', 'Создатель', 'Ответственный сотрудник'),
			 $decorator,
			  'supplier.php', 'action=1', 'id',
			  'suppliers.php', '', 'code'
			  ));
		
		
	
	}
	
	
	//заявки
	if($au->user_rights->CheckAccess('w',80)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_Komplekt(
			'Заявки',
			'   from komplekt_ved as p left join komplekt_ved_pos as pp on p.id=pp.komplekt_ved_id left join supplier as s on p.supplier_id=s.id left join catalog_position as pos on pos.id=pp.position_id
			 left join  komplekt_ved_file as f on f.komplekt_ved_id=p.id 
			 left join user as crea on crea.id=p.manager_id
			 ',
			 array('s.full_name', 'pos.name', 'f.orig_name', 'f.text_contents' , 'crea.name_s' ),
			 array('Контрагент','Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель' ),
			 $decorator,
			  'ed_komplekt.php', 'action=1', 'id',
			  'komplekt.php',  '', 'id'
			  ));
		
	
	}
	
	
	//исх. счета
	if($au->user_rights->CheckAccess('w',97)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_Bill(
			'Исходящие счета',
			'   from bill as p left join bill_position as pp on p.id=pp.bill_id and p.is_incoming=0 left join supplier as s on p.supplier_id=s.id
			 left join bill_file as f on f.bill_id=p.id 
			  left join user as crea on crea.id=p.manager_id

			 ',
			 array('s.full_name', 'pp.name', 'f.orig_name', 'f.text_contents', 'crea.name_s'  ),
			 array('Контрагент','Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель'  ),
			 $decorator,
			  'ed_bill.php', 'action=1', 'id',
			  'bills.php',  'doFilter=1', 'id'
			  ));
		
	
	}
	
	
	//вх. счета
	if($au->user_rights->CheckAccess('w',606)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_BillIn(
			'Входящие счета',
			'   from bill as p left join bill_position as pp on p.id=pp.bill_id and p.is_incoming=1 left join supplier as s on p.supplier_id=s.id 
			left join bill_file as f on f.bill_id=p.id 
			 left join user as crea on crea.id=p.manager_id
			',
			 array('s.full_name', 'pp.name', 'f.orig_name', 'f.text_contents' ,  'crea.name_s'  ),
			 array('Контрагент','Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель'  ),
			 $decorator,
			  'ed_bill_in.php', 'action=1', 'id',
			  'bills.php',  'doFilter_1=1', 'id'
			  ));
		
	
	}
	
	
	
	//реализации
	if($au->user_rights->CheckAccess('w',200)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_Acc(
			'Реализации',
			'   from acceptance as p left join acceptance_position as pp on p.id=pp.acceptance_id and p.is_incoming=0 left join bill as b on b.id=p.bill_id left join supplier as s on b.supplier_id=s.id
			 left join acceptance_file as f on f.acceptance_id =p.id 
			 left join user as crea on crea.id=p.manager_id

			 ',
			 array('s.full_name', 'pp.name', 'f.orig_name', 'f.text_contents',  'crea.name_s'   ),
			 array('Контрагент','Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель' ),
			 $decorator,
			  'ed_acc.php', 'action=1', 'id',
			  'all_acc.php',  'doFilter=1', 'id'
			  ));
		
	
	}
	
	
	//postupleniya
	if($au->user_rights->CheckAccess('w',659)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_AccIn(
			'Поступления',
			'   from acceptance as p left join acceptance_position as pp on p.id=pp.acceptance_id and p.is_incoming=1 left join bill as b on b.id=p.bill_id left join supplier as s on b.supplier_id=s.id 
			left join acceptance_file as f on f.acceptance_id =p.id 
			left join user as crea on crea.id=p.manager_id
			',
			 array('s.full_name', 'pp.name', 'f.orig_name', 'f.text_contents' ,  'crea.name_s'  ),
			 array('Контрагент','Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель'  ),
			 $decorator,
			  'ed_acc_in.php', 'action=1', 'id',
			  'all_acc.php',  'doFilter_1=1', 'id_1'
			  ));
		
	
	}
	
	
	//доверенность
	if($au->user_rights->CheckAccess('w',198)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_Trust(
			'Доверенность',
			'   from trust as p left join trust_position as pp on p.id=pp.trust_id  left join bill as b on b.id=p.bill_id left join supplier as s on b.supplier_id=s.id
			 left join trust_file as f on f.trust_id=p.id 
			 left join user as crea on crea.id=p.manager_id
			
			 ',
			 array('s.full_name', 'pp.name', 'f.orig_name', 'f.text_contents',  'crea.name_s'   ),
			 array('Контрагент','Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель' ),
			 $decorator,
			  'ed_trust.php', 'action=1', 'id',
			  'all_trust.php',  'doFilter=1', 'id'
			  ));
		
	
	}
	
	
	
	//оплата
	if($au->user_rights->CheckAccess('w',266)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_Pay(
			'Исходящая оплата',
			'   from payment as p  left join supplier as s on p.supplier_id=s.id and p.is_incoming=0
			 left join payment_file as f on f.payment_id=p.id 
			 left join user as crea on crea.id=p.manager_id
			 ',
			 array('s.full_name', 'f.orig_name', 'f.text_contents',  'crea.name_s'  ),
			 array('Контрагент', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель' ),
			 $decorator,
			  'ed_pay.php', 'action=1', 'id',
			  'all_pay.php',  'doFilter=1', 'id'
			  ));
		
	
	}
	
	
	//оплата
	if($au->user_rights->CheckAccess('w',677)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_PayIn(
			'Входящая оплата',
			'   from payment as p  left join supplier as s on p.supplier_id=s.id and p.is_incoming=1
			left join payment_file as f on f.payment_id=p.id 
			left join user as crea on crea.id=p.manager_id
			 ',
			 array('s.full_name', 'f.orig_name', 'f.text_contents',  'crea.name_s'  ),
			 array('Контрагент', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель' ),
			 $decorator,
			  'ed_pay_in.php', 'action=1', 'id',
			  'all_pay.php',  'doFilter_in=1', 'id'
			  ));
		
	
	}
	
	//инввзр
	if($au->user_rights->CheckAccess('w',677)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		//ограничения по к-ту
		$limited_supplier=NULL;
		
		if($au->FltSupplier($result)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
			$limited_supplier=$s_to_u['sector_ids'];
			
			$decorator->AddEntry(new SqlEntry('p.supplier_id', NULL, SqlEntry::IN_VALUES, NULL,$limited_supplier));	
		}
		
		 
		
		$_search->AddDoc( new Search_InvCalc(
			'Инвентаризация взаиморасчетов',
			'   from invcalc as p  left join supplier as s on p.supplier_id=s.id 
			 left join invcalc_file as f on f.invcalc_id=p.id
			 left join user as crea on crea.id=p.manager_id
			',
			 array('s.full_name', 'f.orig_name', 'f.text_contents',  'crea.name_s'  ),
			 array('Контрагент', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель' ),
			 $decorator,
			  'ed_invcalc.php', 'action=1', 'id',
			  'invent.php',  'doFilter2=1', 'id'
			  ));
		
	
	}
	
	
	//инв ост
	if($au->user_rights->CheckAccess('w',321)){
		$decorator=new DBDecorator;
		
		
		//ТОЛЬКО ДАННОЙ ОРГАНИЗАЦИИ!!
		$decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));
		
		
		 
		
		$_search->AddDoc( new Search_Inv(
			'Инвентаризация остатков',
			'   from inventory as p  left join inventory_position as pp  on p.id=pp.inventory_id
			 left join inventory_file as f on f.inventory_id=p.id
			 left join user as crea on crea.id=p.manager_id
			 ',
			 array('pp.name', 'f.orig_name', 'f.text_contents',  'crea.name_s'  ),
			 array('Позиция', 'Имя прикрепленного файла', 'Содержание прикрепленного файла', 'Создатель'  ),
			 $decorator,
			  'ed_inv.php', 'action=1', 'id',
			  'invent.php',  'doFilter=1', 'id'
			  ));
		
	
	}
	
	
	
	
	
	
	
	
	
	
	//справ. информация
	if($au->user_rights->CheckAccess('w',841)){
		$kp_decorator=new DBDecorator;
		
		$_file=new spitem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  35, 'w',  $_file->GetTableName(), 2);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_SpravFile(
			'Справочная информация', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
					left join user as u on u.id=p.user_id
				
				 ', //1
			  array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_pl.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-5', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	
	//ф и д
	if($au->user_rights->CheckAccess('w',840)){
		$kp_decorator=new DBDecorator;
		
		$_file=new FilePoItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  37, 'w',  $_file->GetTableName(), 1);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_File(
			'Файлы и документы', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
					left join user as u on u.id=p.user_id
				
				 ', //1
			 array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-1', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	//письма
	if($au->user_rights->CheckAccess('w',840)){
		$kp_decorator=new DBDecorator;
		
		$_file=new FileLetItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  38, 'w',  $_file->GetTableName(), 4);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_FileL(
			'Письма', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
					left join user as u on u.id=p.user_id
				
				 ', //1
			  array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_l.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-3', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	//спец
	if($au->user_rights->CheckAccess('w',476)){
		$kp_decorator=new DBDecorator;
		
		$_file=new SpSItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  36, 'w',  $_file->GetTableName(), 3);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_Sps(
			'Спецдокументы', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
					left join user as u on u.id=p.user_id
				
				 ', //1
			 array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_spl.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-6', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	//файлы +/-
	if($au->user_rights->CheckAccess('w',560)){
		$kp_decorator=new DBDecorator;
		
		$_file=new FilePmItem;
		
		$kp_decorator->AddEntry(new SqlEntry('p.storage_id',$_file->GetStorageId(), SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry('p.org_id',$result['org_id'], SqlEntry::E));	
		
		$_rl=new RLMan;
		$restricted_ids=$_rl->GetBlockedItemsArr($result['id'],  47, 'w',  $_file->GetTableName(), 5);
		
		if(count($restricted_ids)>0) $kp_decorator->AddEntry(new SqlEntry('p.folder_id', NULL, SqlEntry::NOT_IN_VALUES, NULL,$restricted_ids));	
		 
		
			
		$_search->AddDoc( new Search_Sps(
			'Файлы +/-', //0
			'   
				from file as p
				left join file_folder as s on s.id=p.folder_id
					left join user as u on u.id=p.user_id
				
				 ', //1
			 array(  'p.orig_name', 'p.text_contents', 'u.name_s'), //2
			 array(  'Имя файла', 'Содержание файла', 'Загрузил файл'), //3
			 $kp_decorator, //4
			  'load_pm.html', //5
			  '', //6
			   'id', //7
			  'files.php', //8
			   'tab_page=tabs-4', //9
			    'code' //10
			  ));
		
		
	
	} 
	
	
	
	
	
	
	
	
	
/******************************************************************************************************/	
	
	//план-к
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 3);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 3, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Встречи',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'c.name', 'ss.note', 'cont.name', 'cont1.name', 'ss.result',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент', 'Город',   'Цель', 'Контакт','Контакт', 'Результат', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter3=1', 'code3'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 1);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 1, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Задачи',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join sched as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
				
				 ',
			 array('p.code', 'sup.full_name',     'cont.name',  'p.description',   'u1.name_s', 'u2.name_s'),
			 array('Номер',   'Контрагент',     'Контакт',  'Описание задачи', 'Постановщик', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched_task.php', 'action=1', 'id',
			  'shedule.php',  'doFilter1=1', 'code1'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 4);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 4, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Звонки',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'c.name', 'ss.note', 'cont.name', 'cont1.name', 'ss.result',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент', 'Город',   'Цель', 'Контакт','Контакт', 'Результат', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter4=1', 'code4'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 5);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 5, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Заметки',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'p.topic', 'p.description',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент',  'Тема', 'Текст', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter5=1', 'code5'
			  ));
			
	} 
	
	
	if($au->user_rights->CheckAccess('w',903)){
		$kp_decorator=new DBDecorator;
		
		$_plans=new Sched_Group; 
		$_plans->SetAuthResult($result);
		
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 2);
		
		 
		$kp_decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$kp_decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$kp_decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		$kp_decorator->AddEntry(new SqlEntry('p.kind_id', 2, SqlEntry::E));	 
			
		$_search->AddDoc( new Search_Sched(
			'Планировщик: Командировки',
			'   
		from sched as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				left join sched_suppliers_contacts as scont on ss.id=scont.sc_id
				left join supplier_contact as cont on cont.id=scont.contact_id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				left join supplier_contact as cont1 on cont1.id=ss1.contact_id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
			 
				
				 ',
			 array('p.code', 'sup.full_name',  'sup1.full_name',  'c.name', 'ss.note', 'cont.name', 'cont1.name', 'ss.result',   'cr.name_s', 'u.name_s'),
			 array('Номер',   'Контрагент', 'Контрагент', 'Город',   'Цель', 'Контакт','Контакт', 'Результат', 'Создатель', 'Ответственный сотрудник'),
			 $kp_decorator,
			  'ed_sched.php', 'action=1', 'id',
			  'shedule.php',  'doFilter2=1', 'code2'
			  ));
			
	} 
	  
	  
	
	
	
	
	
	  
	$do_it=(bool)(strlen(trim($_GET['data']))>=3);
	
	 $search=$_search->GetData(SecStr($_GET['data']), $do_it, $total);
	// print_r($search);
	
	 if($do_it){
			 //журнал событий 

		$log->PutEntry($result['id'],'выполнил Поиск по сайту',NULL,NULL,NULL,'поисковый запрос '.SecStr($_GET['data'])); 
	 }
		 
	 $sm1=new SmartyAdm;
	 $sm1->assign('data', $_GET['data']);
	 $sm1->assign('do_it', $do_it);
	 $sm1->assign('total', $total);
	 $sm1->assign('search', $search);
	 
	 
	 $content=$sm1->fetch('search.html');
	
	
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