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


require_once('classes/an_sched.php');
require_once('classes/an_sched_su.php');

require_once('classes/an_sched_newcli.php');

require_once('classes/an_sched_suresp.php');

require_once('classes/sched.class.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Отчет Планировщик');

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

if(!isset($_GET['sortmode'])){
	if(!isset($_POST['sortmode'])){
		$sortmode=1;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);


 

if($print!=0){
	if(!$au->user_rights->CheckAccess('w',903)&&!$au->user_rights->CheckAccess('w',903)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);





$log=new ActionLog;
 $log->PutEntry($result['id'],'перешел в Отчет Планировщик',NULL,903,NULL,NULL);
 
//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
/*elseif(isset($_GET['doSub7'])||isset($_GET['doSub7_x'])){
	$smarty->display('top_print.html');
}*/

unset($smarty);

	
	$_menu_id=67;

	if($print==0) include('inc/menu.php');
	
	

	 
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	
	
	
	
	$sm=new SmartyAdm;
	$as=new AnSched;
	
	
	
//******************************************************************************************************************	 
	//Вкладка  Задачи
	
	$prefix=1;
	
	$decorator=new DBDecorator;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	//блок фильтров статуса
	$decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
	$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE));
	
	 
	//фильтр по контрагенту
	//фильтр по контрагенту
	include('inc/an_sched_supplier_inc.php');
	


	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	//приоритет
	$priority=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'priority'])&&is_array($_GET[$prefix.'priority'])) $cou_stat=count($_GET[$prefix.'priority']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $priority=$_GET[$prefix.'priority'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_priority',1));
	}
	
	if(count($priority)>0){
		$of_zero=true; foreach($priority as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_priority',1));
		}else{
		
			foreach($priority as $k=>$v) $decorator->AddEntry(new UriEntry('priority_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($priority as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'priority[]',$v));
			
			$_priority=array(); foreach($priority as $k=>$v) $_priority[]='"'.$v.'"';
			$decorator->AddEntry(new SqlEntry('p.priority', NULL, SqlEntry::IN_VALUES, NULL,$_priority));
		 
		}
	} 
	
	
	
	//содержание
	if(isset($_GET['description'.$prefix])&&(strlen($_GET['description'.$prefix])>0)){
		if($print==1){
			 
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.description',SecStr(iconv("utf-8","windows-1251",$_GET['description'.$prefix])), SqlEntry::LIKE));
				
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select sched_id from sched_history where txt LIKE "%'.SecStr(iconv("utf-8","windows-1251",$_GET['description'.$prefix])).'%"', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));	
		 
			
			$decorator->AddEntry(new UriEntry('description',  iconv("utf-8","windows-1251",$_GET['description'.$prefix])));

		}else{
			//$decorator->AddEntry(new SqlEntry('p.description',SecStr($_GET['description'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.description',SecStr(($_GET['description'.$prefix])), SqlEntry::LIKE));
				
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select sched_id from sched_history where txt LIKE "%'.SecStr(($_GET['description'.$prefix])).'%"', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));	
			 
			
			$decorator->AddEntry(new UriEntry('description',  $_GET['description'.$prefix]));
		}
	 }
	 
	  
	 //поиск по файлам 
	if(isset($_GET['contents'.$prefix])&&(strlen($_GET['contents'.$prefix])>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['contents'.$prefix]);
		}else{
			$crit= $_GET['contents'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		 
	 //поиск по ИМЕНАМ ФАЙЛОВ задачи
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from sched_history as h inner join sched_history_file as f on f.history_id=h.id    WHERE f.orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		
		//поиск по СОДЕРЖАНИЮ файла задачи
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct h.sched_id from sched_history as h inner join sched_history_file as f on f.history_id=h.id    WHERE MATCH (f.text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('contents', $crit));
	 }
	 

	
	 //добавить содержание в отчет
	 if(isset($_GET['has_content'.$prefix])){
	 	$decorator->AddEntry(new UriEntry('has_content', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_content', 0));
	
	
	
	$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E)); 
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	
	 
			 
	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	
	
	//видимость данных
	$_plans=new Sched_Group;
	$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,1);
	$notes_viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,5);
	
	
	
	$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id in ('.implode(', ',$viewed_ids).')', SqlEntry::IN_SQL));	
	
	
	
	//фильтры по постановщику, и т.д.
	for($i=1; $i<=4; $i++){
		if(isset($_GET['user'.$i.$prefix])&&(strlen($_GET['user'.$i.$prefix])>0)){
			$_users1=explode(';', $_GET['user'.$i.$prefix]);
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id in ('.implode(', ',$_users1).') and kind_id='.$i, SqlEntry::IN_SQL));
			$decorator->AddEntry(new UriEntry('user'.$i,  $_GET['user'.$i.$prefix]));
		}
	}
	
	
	
	 //сортировка
	 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
	 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
	
	
	
	
	$filetext=$as->ShowData(    'an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Задачи',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Задачи',NULL,903,NULL, NULL);	
	}
	
	
	
	
	
	
	
	
//******************************************************************************************************************	 
	//Вкладка  Командировки
	
	$prefix=2;
	
	$decorator=new DBDecorator;
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	//блок фильтров статуса
	 $decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
	$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE));
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	//план/факт
	 
	$planfact=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'planfact'])&&is_array($_GET[$prefix.'planfact'])) $cou_stat=count($_GET[$prefix.'planfact']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $planfact=$_GET[$prefix.'planfact'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_planfact',1));
	}
	
	if(count($planfact)>0){
		$of_zero=true; foreach($planfact as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_planfact',1));
		}else{
		
			foreach($planfact as $k=>$v) $decorator->AddEntry(new UriEntry('planfact_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($planfact as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'planfact[]',$v));
			
			$_planfact=array(); foreach($planfact as $k=>$v) $_planfact[$k]='"'.$v.'"';
			
			$decorator->AddEntry(new SqlEntry('p.plan_or_fact', NULL, SqlEntry::IN_VALUES, NULL,$_planfact));
		 
		}
	} 
	
	
	$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E)); 
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	
	 
			 
	$decorator ->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
		 
		  
	//фильтры по виду контрагента
	$supplier_kinds=NULL;
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'supplier_kinds'])&&is_array($_GET[$prefix.'supplier_kinds'])) $cou_stat=count($_GET[$prefix.'supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'supplier_kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			
			foreach($kinds as $k=>$v) {
				$decorator->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator->AddEntry(new UriEntry($prefix.'supplier_kinds[]',$v));
				
				if($v==1) $supplier_kinds[]='is_customer'; 
				elseif($v==2) $supplier_kinds[]='is_supplier';
				elseif($v==3) $supplier_kinds[]='is_partner';
				elseif($v==4) $supplier_kinds[]='none';	
			
			}
			
			//если выбраны вообще все виды - то блок исключаем!
			if(count($supplier_kinds)>=4) $supplier_kinds=NULL; 
		}
	} 	  
		  
	
		  
	
	//видимость данных
	
	$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,2);
		
	
	
	//$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
	 
	//var_dump($viewed_ids);
	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
	$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	
	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users1=explode(';', $_GET['user'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));
	}
	 
	//фильтр по контрагенту
	include('inc/an_sched_supplier_inc.php');
	
	
	
	 
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		 
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_country as sc on sc.id=u.country_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_district as sc on sc.id=u.district_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}
		
	

	
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_cities where city_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
	}
	
		 if(isset($_GET['report'.$prefix])&&(strlen($_GET['report'.$prefix])>0)){
		if($print==1){
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.report',SecStr(iconv("utf-8","windows-1251",$_GET['report'.$prefix])), SqlEntry::LIKE)); 
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.sched_id from sched_suppliers as ss    WHERE MATCH (ss.result) AGAINST ("'.SecStr(iconv("utf-8","windows-1251",$_GET['report'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			
			$decorator->AddEntry(new UriEntry('report',  iconv("utf-8","windows-1251",$_GET['report'.$prefix])));
			
		}else{
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			$decorator->AddEntry(new SqlEntry('p.report',SecStr($_GET['report'.$prefix]), SqlEntry::LIKE)); 
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ss.sched_id from sched_suppliers as ss    WHERE MATCH (ss.result) AGAINST ("'.SecStr(($_GET['report'.$prefix])).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			
			
			$decorator->AddEntry(new UriEntry('report',  $_GET['report'.$prefix]));
		}
	 }
	 

	
	  
	 
	 //поиск по файлам 
	if(isset($_GET['contents'.$prefix])&&(strlen($_GET['contents'.$prefix])>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['contents'.$prefix]);
		}else{
			$crit= $_GET['contents'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('contents', $crit));
	 }
	
	

	
	 //сортировка
	 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
	 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
	
	
	
	
	$filetext=$as->ShowData(    'an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result, $supplier_kinds);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		
		 
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Командировки',NULL,903,NULL,  'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Командировки',NULL,903,NULL, NULL);	
		
	}
	
	
	
	
	
	
//******************************************************************************************************************	 
	//Вкладка  Встречи
	
	$prefix=3;
	
	$decorator=new DBDecorator;
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	//блок фильтров статуса
	$decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
	$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE)); 
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	//план/факт
	 
	$planfact=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'planfact'])&&is_array($_GET[$prefix.'planfact'])) $cou_stat=count($_GET[$prefix.'planfact']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $planfact=$_GET[$prefix.'planfact'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_planfact',1));
	}
	
	if(count($planfact)>0){
		$of_zero=true; foreach($planfact as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_planfact',1));
		}else{
		
			foreach($planfact as $k=>$v) $decorator->AddEntry(new UriEntry('planfact_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($planfact as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'planfact[]',$v));
			
			$_planfact=array(); foreach($planfact as $k=>$v) $_planfact[$k]='"'.$v.'"';
			
			$decorator->AddEntry(new SqlEntry('p.plan_or_fact', NULL, SqlEntry::IN_VALUES, NULL,$_planfact));
		 
		}
	} 
	
	
	//виды
	 
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'kinds'])&&is_array($_GET[$prefix.'kinds'])) $cou_stat=count($_GET[$prefix.'kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_kinds',1));
		}else{
		
			foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kind_id_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'kinds[]',$v));
			
						
			$decorator->AddEntry(new SqlEntry('p.meet_id', NULL, SqlEntry::IN_VALUES, NULL,$kinds));
		 
		}
	} 
	
	
	//фильтры по виду контрагента
	$supplier_kinds=NULL;
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'supplier_kinds'])&&is_array($_GET[$prefix.'supplier_kinds'])) $cou_stat=count($_GET[$prefix.'supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'supplier_kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			
			foreach($kinds as $k=>$v) {
				$decorator->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator->AddEntry(new UriEntry($prefix.'supplier_kinds[]',$v));
				
				if($v==1) $supplier_kinds[]='is_customer'; 
				elseif($v==2) $supplier_kinds[]='is_supplier';
				elseif($v==3) $supplier_kinds[]='is_partner';
				elseif($v==4) $supplier_kinds[]='none';	
			
			}
			
			//если выбраны вообще все виды - то блок исключаем!
			if(count($supplier_kinds)>=4) $supplier_kinds=NULL; 
		}
	} 
	

	
	
	$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E)); 
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	
	 
			 
	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
	 

	
	//видимость данных
	$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,3);
	
	//	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
	 
	//var_dump($viewed_ids);
	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
	$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));		
	
	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users1=explode(';', $_GET['user'.$prefix]);

		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));
	}
	 
	//фильтр по контрагенту
	include('inc/an_sched_supplier_inc.php');
	
	
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		 
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_country as sc on sc.id=u.country_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_district as sc on sc.id=u.district_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}

	

	
	
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_cities where city_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
	}
	
	
	if(isset($_GET['report'.$prefix])&&(strlen($_GET['report'.$prefix])>0)){
		if($print==1){
			$decorator->AddEntry(new SqlEntry('p.report',SecStr(iconv("utf-8","windows-1251",$_GET['report'.$prefix])), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('report',  iconv("utf-8","windows-1251",$_GET['report'.$prefix])));
		}else{
			$decorator->AddEntry(new SqlEntry('p.report',SecStr($_GET['report'.$prefix]), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('report',  $_GET['report'.$prefix]));
		}
	 }
	 
	 
	  if(isset($_GET['meet_value'.$prefix])&&(strlen($_GET['meet_value'.$prefix])>0)){
		if($print==1){
			$decorator->AddEntry(new SqlEntry('p.meet_value',SecStr(iconv("utf-8","windows-1251",$_GET['meet_value'.$prefix])), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('meet_value',  iconv("utf-8","windows-1251",$_GET['meet_value'.$prefix])));
		}else{
			$decorator->AddEntry(new SqlEntry('p.meet_value',SecStr($_GET['meet_value'.$prefix]), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('meet_value',  $_GET['meet_value'.$prefix]));
		}
	 }
	

	 
	 
	//поиск по файлам 
	if(isset($_GET['contents'.$prefix])&&(strlen($_GET['contents'.$prefix])>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['contents'.$prefix]);
		}else{
			$crit= $_GET['contents'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('contents', $crit));
	 }
	
	

	
	
	 //сортировка
	 
	 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
	 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
	 
	
	
	
	$filetext=$as->ShowData(    'an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result, $supplier_kinds);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Встречи',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Встречи',NULL,903,NULL, NULL);	
	}
	
	


	
//******************************************************************************************************************	 
	//Вкладка  Звонки
	
	$prefix=4;
	
	$decorator=new DBDecorator;
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	//блок фильтров статуса
	 $decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
	$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE));
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	
	//план/факт
	 
	$planfact=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'planfact'])&&is_array($_GET[$prefix.'planfact'])) $cou_stat=count($_GET[$prefix.'planfact']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $planfact=$_GET[$prefix.'planfact'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_planfact',1));
	}
	
	if(count($planfact)>0){
		$of_zero=true; foreach($planfact as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_planfact',1));
		}else{
		
			foreach($planfact as $k=>$v) $decorator->AddEntry(new UriEntry('planfact_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($planfact as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'planfact[]',$v));
			
			$_planfact=array(); foreach($planfact as $k=>$v) $_planfact[$k]='"'.$v.'"';
			
			$decorator->AddEntry(new SqlEntry('p.plan_or_fact', NULL, SqlEntry::IN_VALUES, NULL,$_planfact));
		 
		}
	} 
	
	
	//виды
	 
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'incoming_or_outcomings'])&&is_array($_GET[$prefix.'incoming_or_outcomings'])) $cou_stat=count($_GET[$prefix.'incoming_or_outcomings']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $incoming_or_outcomings=$_GET[$prefix.'incoming_or_outcomings'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_incoming_or_outcomings',1));
	}
	
	if(count($incoming_or_outcomings)>0){
		$of_zero=true; foreach($incoming_or_outcomings as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_incoming_or_outcomings',1));
		}else{
		
			foreach($incoming_or_outcomings as $k=>$v) $decorator->AddEntry(new UriEntry('incoming_or_outcoming_'.$v,1));
			//$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
			foreach($incoming_or_outcomings as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'incoming_or_outcomings[]',$v));
			
			$_incoming_or_outcomings=array(); foreach($incoming_or_outcomings as $k=>$v) $_incoming_or_outcomings[$k]='"'.$v.'"';
			
			$decorator->AddEntry(new SqlEntry('p.incoming_or_outcoming', NULL, SqlEntry::IN_VALUES, NULL,$_incoming_or_outcomings));			
			 
		 
		}
	} 
	
	
	
	//фильтры по виду контрагента
	$supplier_kinds=NULL;
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'supplier_kinds'])&&is_array($_GET[$prefix.'supplier_kinds'])) $cou_stat=count($_GET[$prefix.'supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'supplier_kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			
			foreach($kinds as $k=>$v) {
				$decorator->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator->AddEntry(new UriEntry($prefix.'supplier_kinds[]',$v));
				
				if($v==1) $supplier_kinds[]='is_customer'; 
				elseif($v==2) $supplier_kinds[]='is_supplier';
				elseif($v==3) $supplier_kinds[]='is_partner';
				elseif($v==4) $supplier_kinds[]='none';	
			
			}
			
			//если выбраны вообще все виды - то блок исключаем!
			if(count($supplier_kinds)>=4) $supplier_kinds=NULL; 
		}
	} 
	

	
	$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E)); 
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	
	 
	$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2)))); 
	 
	
	//видимость данных
	$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,4);
	
	
	//$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
	 
	//var_dump($viewed_ids);
	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
	$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
			
	
	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users1=explode(';', $_GET['user'.$prefix]);

		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));
	}
	 
	//фильтр по контрагенту
	//фильтр по контрагенту
	
	 if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_users1=explode(';', $_GET['supplier'.$prefix]);
		
		$decorator->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		
		//поиск по субхолдингам
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
			
			//0. исходный вариант
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_contacts where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//найти 4 варианта:
			//1. записи по тем контрагентам, у кого холдинг=заданному к-ту
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_contacts as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.holding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//2. найти все субхолдинги заданного к-та (у кого он холдинг, связь через контрагентов)
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_contacts as ps inner join supplier as ss on ss.id=ps.supplier_id where ss.is_active=1 and ss.id in(select distinct subholding_id from supplier where is_active=1 and holding_id in(  '.implode(', ',$_users1).'))', SqlEntry::IN_SQL));
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			//3. найти все дочерние предприятия субхолдингов заданного предприятия
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_contacts as ps 
			inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1  /*запись контрагента, у кого холдинг и субхолдинг определены */
			inner join supplier as sub on sub.id=ss.subholding_id and sub.is_active=1  /*запись субхолдинга*/
			inner join supplier as doch on sub.id=doch.subholding_id and doch.is_active=1  /*дочерняя компания субхолдинга */
			where  ss.holding_id in(  '.implode(', ',$_users1).')  ', SqlEntry::IN_SQL));
			
			//4. найти всех контрагентов, у кого субхолдинг - задан
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			
			$decorator->AddEntry(new SqlEntry('p.id','select distinct ps.sched_id from  sched_contacts as ps inner join supplier as ss on ss.id=ps.supplier_id and ss.is_active=1 and ss.subholding_id in( '.implode(', ',$_users1).')', SqlEntry::IN_SQL));
			
			
			$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
			 
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
			$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_contacts where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));	
		}
	}

	 
	 
	 
	 
	 //фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_contacts where supplier_id in(select supplier_id from supplier_sprav_city where city_id in ('.implode(', ',$_users1).'))', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
	}
	
	 if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		 
		$decorator->AddEntry(new SqlEntry('p.id',' select distinct  sched_id from  sched_contacts where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_country as sc on sc.id=c.country_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct  sched_id from  sched_contacts where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_district as sc on sc.id=c.district_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}
	 
	 

	 
	 
	 
	 
 
	 
	
	 if(isset($_GET['report'.$prefix])&&(strlen($_GET['report'.$prefix])>0)){
		if($print==1){
			$decorator->AddEntry(new SqlEntry('p.report',SecStr(iconv("utf-8","windows-1251",$_GET['report'.$prefix])), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('report',  iconv("utf-8","windows-1251",$_GET['report'.$prefix])));
		}else{
			$decorator->AddEntry(new SqlEntry('p.report',SecStr($_GET['report'.$prefix]), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('report',  $_GET['report'.$prefix]));
		}
	 }
	 
	 

	 //поиск по файлам 
	if(isset($_GET['contents'.$prefix])&&(strlen($_GET['contents'.$prefix])>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['contents'.$prefix]);
		}else{
			$crit= $_GET['contents'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('contents', $crit));
	 }

	

	
	 //сортировка
	 
	 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
	 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
	
	
	
	
	$filetext=$as->ShowData('an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result,$supplier_kinds);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Звонки',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Звонки',NULL,903,NULL, NULL);	
	}
	
			
	
	
	


	
//******************************************************************************************************************	 
	//Вкладка  Заметки
	
	$prefix=5;
	
	$decorator=new DBDecorator;
	

	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	   
	
	
	$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E)); 
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	
	
	 
	$decorator->AddEntry(new SqlEntry('p.pdate', DateFromdmY($pdate1) , SqlEntry::BETWEEN, DateFromdmY($pdate2)+24*60*60));
		  
	
	//видимость данных
	
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
	$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$notes_viewed_ids));	
		
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
	
	$decorator->AddEntry(new SqlEntry('p.id','select sched_id from sched_users where user_id="'.$result['id'].'"', SqlEntry::IN_SQL));
	
	$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));	
		
	
	//фильтр по контрагенту
	  
	include('inc/an_sched_supplier_inc.php');
	

	
	
	
	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users1=explode(';', $_GET['user'.$prefix]);

		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$_users1));
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));
	}
	 
	//поделился с...
	if(isset($_GET['share_user'.$prefix])&&(strlen($_GET['share_user'.$prefix])>0)){
		$_users1=explode(';', $_GET['share_user'.$prefix]);
		 $decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from   sched_users where user_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new UriEntry('share_user',  $_GET['share_user'.$prefix]));
	} 
	 
	
	 if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		if($print==1){
			$decorator->AddEntry(new SqlEntry('p.topic',SecStr(iconv("utf-8","windows-1251",$_GET['topic'.$prefix])), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('topic',  iconv("utf-8","windows-1251",$_GET['topic'.$prefix])));			
		}else{
			$decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('topic',  $_GET['topic'.$prefix]));
		}
	 }
	 
	 if(isset($_GET['description'.$prefix])&&(strlen($_GET['description'.$prefix])>0)){
		if($print==1){
			$decorator->AddEntry(new SqlEntry('p.description',SecStr(iconv("utf-8","windows-1251",$_GET['description'.$prefix])), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('description',  iconv("utf-8","windows-1251",$_GET['description'.$prefix])));

		}else{
			$decorator->AddEntry(new SqlEntry('p.description',SecStr($_GET['description'.$prefix]), SqlEntry::LIKE)); 
			$decorator->AddEntry(new UriEntry('description',  $_GET['description'.$prefix]));
		}
	 }
	 
	 
	  //поиск по файлам 
	if(isset($_GET['contents'.$prefix])&&(strlen($_GET['contents'.$prefix])>0)){
		if($print==1){
		 	$crit=iconv("utf-8","windows-1251",$_GET['contents'.$prefix]);
		}else{
			$crit= $_GET['contents'.$prefix];
		}
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		//поиск по ИМЕНАМ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE  orig_name LIKE "%'.SecStr($crit).'%" ', SqlEntry::IN_SQL));
			
			
		//поиск по СОДЕРЖИМОМУ ФАЙЛОВ
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct bill_id from sched_file    WHERE MATCH (text_contents) AGAINST ("'.SecStr($crit).'" IN BOOLEAN MODE) ', SqlEntry::IN_SQL));
		
	 
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('contents', $crit));
	 }
	

	
	
	 //добавить содержание в отчет
	 if(isset($_GET['has_content'.$prefix])){
	 	$decorator->AddEntry(new UriEntry('has_content', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_content', 0));
	
	
	 //сортировка
	 
	  $decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
	 
	
	$filetext=$as->ShowData(    'an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Заметки',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Заметки',NULL,903,NULL, NULL);	
	}
	
			
	
	




	
//******************************************************************************************************************	 
	//Вкладка  Контрагент/сотрудник
	
	$prefix=6;
	
	$decorator=new DBDecorator;
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	 
	
	//блок фильтров статуса
	$decorator->AddEntry(new SqlEntry('p.status_id', 3, SqlEntry::NE));
	$decorator->AddEntry(new SqlEntry('p.status_id', 18, SqlEntry::NE));
	
	 
	$status_ids=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $status_ids=$_GET[$prefix.'statuses'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_statuses',1));
	}
	
	if(count($status_ids)>0){
		$of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_statuses',1));
		}else{
		
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
			
			foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			
			$decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));
		 
		}
	} 
	
	//фильтры по виду контрагента
	$supplier_kinds=NULL;
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'supplier_kinds'])&&is_array($_GET[$prefix.'supplier_kinds'])) $cou_stat=count($_GET[$prefix.'supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'supplier_kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			foreach($kinds as $k=>$v) {
				$decorator->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator->AddEntry(new UriEntry($prefix.'supplier_kinds[]',$v));
				
				if($v==1) $supplier_kinds[]='is_customer'; 
				elseif($v==2) $supplier_kinds[]='is_supplier';
				elseif($v==3) $supplier_kinds[]='is_partner';
				elseif($v==4) $supplier_kinds[]='none';	
			
			}
			
			
			//если выбраны вообще все виды - то блок исключаем!
			if(count($supplier_kinds)>=4) $supplier_kinds=NULL; 
			
			 
		}
	} 
	
	
	 //выбрать виды действий
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'kinds'])&&is_array($_GET[$prefix.'kinds'])) $cou_stat=count($_GET[$prefix.'kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_kinds',1));
		}else{
		
			foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry('kind_id_'.$v,1));
			$decorator->AddEntry(new SqlEntry('p.kind_id', NULL, SqlEntry::IN_VALUES, NULL,$kinds));	
			foreach($kinds as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'kinds[]',$v));
			
	
		}
	} 

	
	
	
	   //совершенные/несовершенные действия
	  $is_fulfil=NULL;
	  $kinds=array();
	  $cou_stat=0;   
	  if(isset($_GET[$prefix.'is_fulfil'])&&is_array($_GET[$prefix.'is_fulfil'])) $cou_stat=count($_GET[$prefix.'is_fulfil']);
	  if($cou_stat>0){
		//есть гет-запросы	
		$kinds=$_GET[$prefix.'is_fulfil'];
		
	  }else{
		  
		   $decorator->AddEntry(new UriEntry('all_is_fulfil',1));
	  }
	  
	  if(count($kinds)>0){
		  $of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		  
		  if($of_zero){
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_is_fulfil',1));
		  }else{
		  
			  foreach($kinds as $k=>$v) {
				  $decorator->AddEntry(new UriEntry('is_fulfil_'.$v,1));
			  //$decorator->AddEntry(new SqlHavingEntry('`document_type_id`', NULL, SqlHavingEntry::IN_VALUES, NULL,$kinds));	
				  $decorator->AddEntry(new UriEntry($prefix.'is_fulfil[]',$v));
			  
				  if($v==1) $is_fulfil[]=1; 
				  elseif($v==2) $is_fulfil[]=2;
			  }
		  }
	  } 
		
	

	
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	

	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users=explode(';', $_GET['user'.$prefix]);
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));

		
	}else $_users=NULL;
	 
	 
	
	 
	//фильтр по контрагенту
	if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier'.$prefix]);
		$decorator->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		//поиск по субхолдингам - передаем его в отчет через UriEntry
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
		}
		

		
	}else $_suppliers=NULL;
	 
	
	
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		 
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_cities where city_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		 $decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from   sched_contacts where supplier_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
		
	} 
	
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		 
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_country as sc on sc.id=u.country_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.id',' select distinct  sched_id from  sched_contacts where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_country as sc on sc.id=c.country_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
	 
		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$decorator->AddEntry(new SqlEntry('p.id','select distinct sr.sched_id from  sched_cities as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_district as sc on sc.id=u.district_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		$decorator->AddEntry(new SqlEntry('p.id','select distinct  sched_id from  sched_contacts where supplier_id in(select supplier_id from supplier_sprav_city where city_id in (select distinct c.id from sprav_city as c inner join  sprav_district as sc on sc.id=c.district_id and sc.id in ('.implode(', ',$_users1).') ))', SqlEntry::IN_SQL));
		
		 
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}

	

	
	 //сортировка
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=-1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	 
	 
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.incoming_or_outcoming',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.incoming_or_outcoming',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('meet_name',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('meet_name',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('p.priority',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('p.priority',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
			
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::DESC));
			
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::ASC));
		break;
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::DESC));
			
		break;	
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('status_name',SqlOrdEntry::ASC));
			
		break;
		
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('manager_name',SqlOrdEntry::DESC));
			
		break;	
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('manager_name',SqlOrdEntry::ASC));
			
		break;
		
		case 20:
			$decorator->AddEntry(new SqlOrdEntry('user_name_1',SqlOrdEntry::DESC));
			
		break;	
		case 21:
			$decorator->AddEntry(new SqlOrdEntry('user_name_1',SqlOrdEntry::ASC));
			
		break;
		
		case 22:
			$decorator->AddEntry(new SqlOrdEntry('user_name_2',SqlOrdEntry::DESC));
			
		break;	
		case 23:
			$decorator->AddEntry(new SqlOrdEntry('user_name_2',SqlOrdEntry::ASC));
			
		break;
		
		case 24:
			$decorator->AddEntry(new SqlOrdEntry('cr_name',SqlOrdEntry::DESC));
			
		break;	
		case 25:
			$decorator->AddEntry(new SqlOrdEntry('cr_name',SqlOrdEntry::ASC));
			
		break;
		
		default:
			 $decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
			 $decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
		break;	
		
	}
	 
	 
	 
	
	$as1=new AnSchedSu;
	$_list=new Sched_Group;
	$viewed_ids_arr=array();
	for($i=1; $i<=5; $i++) $viewed_ids_arr[$i]=$_list->GetAvailableUserIds($result['id'],false,$i);
	$viewed_ids_arr[1]=$_list->GetAvailableUserIds($result['id'],false,1);
	$viewed_ids_arr[2]=$_list->GetAvailableUserIds($result['id'],false,2);
	$viewed_ids_arr[3]=$_list->GetAvailableUserIds($result['id'],false,3);
	$viewed_ids_arr[4]=$_list->GetAvailableUserIds($result['id'],false,4);
	$viewed_ids_arr[5]=$_list->GetAvailableUserIds($result['id'],false,5);
	
	//var_dump($viewed_ids_arr[5]);
	
	$filetext=$as1->ShowData($_suppliers, $_users,  $viewed_ids_arr,  $pdate1, $pdate2, 'an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,  $au->user_rights->CheckAccess('w',903),  $au->user_rights->CheckAccess('w',905), $alls, $result, $supplier_kinds, $is_fulfil);
	

	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	//var_dump($alls);
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Контрагент/сотрудник',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Контрагент/сотрудник',NULL,903,NULL, NULL);	
	}
	
			
	




	
//******************************************************************************************************************	 
	//Вкладка Новые клиенты
	
	$prefix=7;
	
	$decorator=new DBDecorator;
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator->AddEntry(new UriEntry('print',$print));
	$decorator->AddEntry(new UriEntry('prefix',$prefix));
	
	 
	$decorator->AddEntry(new SqlEntry('p.org_id', $result['org_id'], SqlEntry::E));
	$decorator->AddEntry(new SqlEntry('p.is_org', 0, SqlEntry::E));
 
	
	if(!isset($_GET['pdate_1'.$prefix])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate_1'.$prefix];
	
	
	
	if(!isset($_GET['pdate_2'.$prefix])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate_2'.$prefix];
	
	
	$decorator->AddEntry(new UriEntry('pdate_1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate_2',$pdate2));
	

	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users=explode(';', $_GET['user'.$prefix]);
		$decorator->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));

		
	}else $_users=NULL;
	 
	//фильтр по контрагенту
	if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier'.$prefix]);
		$decorator->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		
		//поиск по субхолдингам - передаем его в отчет через UriEntry
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator->AddEntry(new UriEntry('has_holdings', 1));
		}else {
			$decorator->AddEntry(new UriEntry('has_holdings', 0));
		}
		

		
	}else $_suppliers=NULL;
	
	
	
	//фильтры по виду контрагента
	 
	$kinds=array(); $supplier_kinds=NULL;
	$cou_stat=0;   
	if(isset($_GET[$prefix.'supplier_kinds'])&&is_array($_GET[$prefix.'supplier_kinds'])) $cou_stat=count($_GET[$prefix.'supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'supplier_kinds'];
	  
	}else{
		
		 $decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			foreach($kinds as $k=>$v) {
				$decorator->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator->AddEntry(new UriEntry($prefix.'supplier_kinds[]',$v));
				
			 
		
				if($v==1) $supplier_kinds[]='is_customer'; 
				elseif($v==2) $supplier_kinds[]='is_supplier';
				elseif($v==3) $supplier_kinds[]='is_partner';
				elseif($v==4) $supplier_kinds[]='none';
					
			
			}
			
			 
			
			 
		}
	} 
	 
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct supplier_id from  supplier_sprav_city where city_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
	} 
	 
	
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		
		$_users1=explode(';', $_GET['country'.$prefix]);
		 
		$decorator->AddEntry(new SqlEntry('p.id','select distinct supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_country as sc on sc.id=u.country_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	//ФО
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_district as sc on sc.id=u.district_id where sc.id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		$decorator->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}

	 
 
	 
	 
	 //фильтр по отрасли
	if(isset($_GET['branch'.$prefix])&&(strlen($_GET['branch'.$prefix])>0)){
		$_users1=explode(';', $_GET['branch'.$prefix]);
		$decorator->AddEntry(new SqlEntry('p.id','select distinct id from  supplier where branch_id in ('.implode(', ',$_users1).') or  subbranch_id in ('.implode(', ',$_users1).') or  subbranch_id1 in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
		
		
		$decorator->AddEntry(new UriEntry('branch',  $_GET['branch'.$prefix]));
	} 
	 
	
	 //расширенная форма
	 if(isset($_GET['has_extended'.$prefix])){
	 	$decorator->AddEntry(new UriEntry('has_extended', 1));
		 
	 
	 }else $decorator->AddEntry(new UriEntry('has_extended', 0));
	

	
	 //сортировка
	if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=-1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	 
	 
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
			
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.active_first_pdate',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.active_first_pdate',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sr.name_s',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sr.name_s',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('sb.name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('ssb.name',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('sb.name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('ssb.name',SqlOrdEntry::ASC));
			
		break;
		
		 
		
		default:
			 $decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC)); 
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
		break;	
		
	}
	 
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	} 
	 
	
	
	$as1=new AnSchedNewCli;
	
	$filetext=$as1->ShowData($_suppliers, $_users,   $limited_supplier,  $pdate1, $pdate2, 'an_sched/an_sched'.$prefix.$print_add.'.html',$decorator,'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,   $au->user_rights->CheckAccess('w',903) ,   $au->user_rights->CheckAccess('w',87), $alls, $result, $supplier_kinds);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	//var_dump($alls);
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Новые клиенты',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Новые клиенты',NULL,903,NULL, NULL);	
	}
	
		



	
//******************************************************************************************************************	 
	//Вкладка Кураторы/контрагенты
	
	$prefix=8;
	
	$decorator1=new DBDecorator;
	$decorator2=new DBDecorator;
	$decorator3=new DBDecorator;
	
	
	
	if($print==0) $print_add='';
	else $print_add='_print';

	$decorator3->AddEntry(new UriEntry('print',$print));
	$decorator3->AddEntry(new UriEntry('prefix',$prefix));
	
	 
	$decorator1->AddEntry(new SqlEntry('p.org_id', $result['org_id'], SqlEntry::E));
	$decorator1->AddEntry(new SqlEntry('p.is_org', 0, SqlEntry::E));
 
	 $decorator2->AddEntry(new SqlEntry('p.group_id', 1, SqlEntry::E));
 

	
	//фильтры по сотруднику
	 
	if(isset($_GET['user'.$prefix])&&(strlen($_GET['user'.$prefix])>0)){
		$_users=explode(';', $_GET['user'.$prefix]);
		$decorator3->AddEntry(new UriEntry('user',  $_GET['user'.$prefix]));

		
	}else $_users=NULL;
	 
	//фильтр по контрагенту
	if(isset($_GET['supplier'.$prefix])&&(strlen($_GET['supplier'.$prefix])>0)){
		$_suppliers=explode(';', $_GET['supplier'.$prefix]);
		$decorator3->AddEntry(new UriEntry('supplier',  $_GET['supplier'.$prefix]));
		
		//поиск по субхолдингам - передаем его в отчет через UriEntry
		if(isset($_GET['has_holdings'.$prefix])){
	 		$decorator3->AddEntry(new UriEntry('has_holdings', 1));
		}else {
			$decorator3->AddEntry(new UriEntry('has_holdings', 0));
		}
		

		
	}else $_suppliers=NULL;
	
	
	 //без контрагента
	 $wo_kur=false;
	 if(isset($_GET['wo_kur'.$prefix])){
	 	$decorator3->AddEntry(new UriEntry('wo_kur', 1));
		$wo_kur=true; 
	 
	 }else $decorator3->AddEntry(new UriEntry('wo_kur', 0));
	
	    
  
	  
	  //с куратором
	 $w_kur=false;
	 if(isset($_GET['w_kur'.$prefix])){
	 	$decorator3->AddEntry(new UriEntry('w_kur', 1));
		$w_kur=true; 
	 
	 }else{
		
		//нет других гет-запросов с нашим префиксом - значит активна
		$_cter=0;
		foreach($_GET as $k=>$v) if(eregi($prefix.'$', $k)) $_cter++;
		
		if($_cter>0) $decorator3->AddEntry(new UriEntry('w_kur', 0));
		else{
			$decorator3->AddEntry(new UriEntry('w_kur', 1));
			$w_kur=true; 
		}
		  
	 }
	   
	  
	 
	 
	 //фильтры по виду контрагента
	$supplier_kinds=NULL;
	$kinds=array();
	$cou_stat=0;   
	if(isset($_GET[$prefix.'supplier_kinds'])&&is_array($_GET[$prefix.'supplier_kinds'])) $cou_stat=count($_GET[$prefix.'supplier_kinds']);
	if($cou_stat>0){
	  //есть гет-запросы	
	  $kinds=$_GET[$prefix.'supplier_kinds'];
	  
	}else{
		
		 $decorator3->AddEntry(new UriEntry('all_supplier_kinds',1));
	}
	
	if(count($kinds)>0){
		$of_zero=true; foreach($kinds as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
		
		if($of_zero){
			//ничего нет - выбираем ВСЕ!	
			$decorator3->AddEntry(new UriEntry('all_supplier_kinds',1));
		}else{
		
			
			foreach($kinds as $k=>$v) {
				$decorator3->AddEntry(new UriEntry('supplier_kind_'.$v,1));
			
				$decorator3->AddEntry(new UriEntry($prefix.'supplier_kinds[]',$v));
				
				if($v==1) $supplier_kinds[]='p.is_customer'; 
				elseif($v==2) $supplier_kinds[]='p.is_supplier';
				elseif($v==3) $supplier_kinds[]='p.is_partner';
				elseif($v==4) $supplier_kinds[]='none';	
			
			}
			
			//если выбраны вообще все виды - то блок исключаем!
			if(count($supplier_kinds)>=4) $supplier_kinds=NULL; 
		}
	} 
	   
  	
	
	//фильтр по городу
	if(isset($_GET['city'.$prefix])&&(strlen($_GET['city'.$prefix])>0)){
		$_users1=explode(';', $_GET['city'.$prefix]);
		
	
		$decorator1->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id  where sr.city_id in ('.implode(', ',$_users1).')', SqlEntry::IN_SQL));
	 
		
		$decorator3->AddEntry(new UriEntry('city',  $_GET['city'.$prefix]));
		
	} 
	
	
	if(isset($_GET['country'.$prefix])&&(strlen($_GET['country'.$prefix])>0)){
		$_users1=explode(';', $_GET['country'.$prefix]);
		
		$decorator1->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_country as sc on sc.id=u.country_id where sc.id in ('.implode(', ',$_users1).') ', SqlEntry::IN_SQL));
		
		$decorator3->AddEntry(new UriEntry('country',  $_GET['country'.$prefix]));
	}
	
	if(isset($_GET['fo'.$prefix])&&(strlen($_GET['fo'.$prefix])>0)){
		$_users1=explode(';', $_GET['fo'.$prefix]);
		
		$decorator1->AddEntry(new SqlEntry('p.id','select distinct sr.supplier_id from  supplier_sprav_city as sr inner join sprav_city as u on u.id=sr.city_id inner join sprav_district as sc on sc.id=u.district_id where sc.id in ('.implode(', ',$_users1).') ', SqlEntry::IN_SQL));
		
		$decorator3->AddEntry(new UriEntry('fo',  $_GET['fo'.$prefix]));
	}


	
	 //сортировка
	/*if(!isset($_GET['sortmode'.$prefix])){
		$sortmode=-1;	
	}else{
		$sortmode=abs((int)$_GET['sortmode'.$prefix]);
	}
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	 
	 
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
		break;
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::DESC));
			
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC));
		break;
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('opf_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.active_first_pdate',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.active_first_pdate',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('sr.name_s',SqlOrdEntry::DESC));
			
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('sr.name_s',SqlOrdEntry::ASC));
			
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
			
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
			
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('sb.name',SqlOrdEntry::DESC));
			$decorator->AddEntry(new SqlOrdEntry('ssb.name',SqlOrdEntry::DESC));
			
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('sb.name',SqlOrdEntry::ASC));
			$decorator->AddEntry(new SqlOrdEntry('ssb.name',SqlOrdEntry::ASC));
			
		break;
		
		 
		
		default:
			 $decorator->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC)); 
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
		break;	
		
	}*/
	
	$decorator1->AddEntry(new SqlOrdEntry('p.full_name',SqlOrdEntry::ASC)); 
	$decorator1->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));

	$decorator2->AddEntry(new SqlOrdEntry('p.name_s',SqlOrdEntry::ASC)); 
	$decorator2->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));

	 
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	} 
	 
	
	
	$as8=new AnSchedSuResp;
	
	
	$filetext=$as8->ShowData($wo_kur, $w_kur, $_suppliers, $_users,   $limited_supplier,   'an_sched/an_sched'.$prefix.$print_add.'.html', $decorator1, $decorator2, $decorator3, 'an_sched.php',   isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/,    $au->user_rights->CheckAccess('w',903) ,      $au->user_rights->CheckAccess('w',909),    $au->user_rights->CheckAccess('w',87), $alls, $result, $supplier_kinds);
	
	
	
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	//var_dump($alls);
	
	$sm->assign('log'.$prefix,$filetext);
	

	 //фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])/*||($print==1)*/){
		if($print==1) $log->PutEntry($result['id'],'открыл отчет Планировщик:Кураторы/контрагенты',NULL,903,NULL, 'открыта версия для печати');	
		else $log->PutEntry($result['id'],'открыл отчет Планировщик:Кураторы/контрагенты',NULL,903,NULL, NULL);	
	}
	
			
	
	
	
	
	








	
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('an_sched/an_sched_form'.$print_add.'.html');
	
	
	
	
	
	$smarty->assign('fast_menu', $menu_arr_fast);
	$smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	if($print==0) $smarty->display('page.html');
	/*elseif(isset($_GET['doSub7'])||isset($_GET['doSub7_x'])){
		$smarty->display('page_gydex.html');
		
	}*/
	else {
		
		//echo $content; die();
		
		
		$sm2=new SmartyAdm;
		
		$content=$sm2->fetch('plan_pdf/pdf_header_lo.html').$content;
		
		

		
		$tmp=time();
	
		$f=fopen(ABSPATH.'/tmp/'.$tmp.'.html','w');
		fputs($f, $content);
		fclose($f);
		
		$cd = "cd ".ABSPATH.'/tmp';
		exec($cd);
		
		
		//скомпилируем подвал
		$sm=new SmartyAdm;
		$sm->assign('print_pdate', date("d.m.Y H:i:s"));
			//$username=$result['login'];
			$username=stripslashes($result['name_s']); //.' '.$username;	
			$sm->assign('print_username',$username);
		$foot=$sm->fetch('plan_pdf/pdf_footer_lo.html');
		$ftmp='f'.time();
		
		$f=fopen(ABSPATH.'/tmp/'.$ftmp.'.html','w');
		fputs($f, $foot);
		fclose($f);
		
		
		if( isset($_GET['doSub6'])||isset($_GET['doSub6_x'])){
			$orient='--orientation Landscape ';
		}else $orient='--orientation Portrait';
		
	 
		//$comand = "wkhtmltopdf-i386  --load-error-handling skip --encoding windows-1251 --page-size A4 ".$orient." --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".SITEURL."/tmp/".$ftmp.".html ".SITEURL.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf 1>".ABSPATH.'/tmp/'."data 2> ".ABSPATH.'/tmp/'."exit";
		
		
		$comand = "wkhtmltopdf-i386  --load-error-handling skip --encoding windows-1251 --page-size A4 ".$orient." --margin-bottom 27mm --margin-left 10mm --margin-right 10mm --footer-html ".ABSPATH."/tmp/".$ftmp.".html ".ABSPATH.'/tmp/'.$tmp.'.html'."  ".ABSPATH.'/tmp/'."$tmp.pdf 1>".ABSPATH.'/tmp/'."data 2> ".ABSPATH.'/tmp/'."exit";
		
		
	//echo $comand;
		 
	 
		 
	 	exec($comand);	
		
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="Отчет_Планировщик.pdf'.'"');
		readfile(ABSPATH.'/tmp/'.$tmp.'.pdf');
	 
		
	
	
		unlink(ABSPATH.'/tmp/'.$tmp.'.pdf');
		unlink(ABSPATH.'/tmp/'.$tmp.'.html');
		unlink(ABSPATH.'/tmp/'.$ftmp.'.html');  
		 
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
/*elseif(isset($_GET['doSub7'])||isset($_GET['doSub7_x'])){
	 $smarty->display('bottom_print.html');
}*/

unset($smarty);
?>