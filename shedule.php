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




require_once('classes/orgsgroup.php');
require_once('classes/user_s_group.php');
require_once('classes/calendar.php');

require_once('classes/sched.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","GYDEX.Планировщик");

$au=new AuthUser();
$result=$au->Auth();

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);

 

if($result!==NULL){
$smarty = new SmartyAdm;


	
	  include('inc/menu.php');
 
 
 
 
 	if(!$au->user_rights->CheckAccess('w',903)){
		$content='<h1>GYDEX.В работе!</h1>';
		
	}else{
  
	  $sm1 = new SmartyAdm;
		 $c= new Calendar();
			  if(!isset($_GET['pdate'])) {
				  $pdate=date('Y-m-d');
			  }else $pdate=$_GET['pdate'];
			  
  
		$calendar= $c->Draw($pdate,'shedule.php','pdate','', $pdate,0);		  
			
		$sm1->assign('calendar', $calendar);
		$sm1->assign('pdate', $pdate);
		
		
		 
		$_plans=new Sched_Group;
		$_plans->SetAuthResult($result);
		$_tasks=new Sched_TaskGroup;

/***************************************************************************************************/
//задачи
		$prefix=1;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=20;
		  
		$decorator=new DBDecorator;
		
		//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		//видимые сотрудники
		
		if(isset($_GET['viewmode'.$prefix])) $viewmode=abs((int)$_GET['viewmode'.$prefix]);
		else $viewmode=0;
		
		 if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		$decorator->AddEntry(new UriEntry('viewmode',$viewmode));
		
		switch($viewmode){
			case 0:
				$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,1);
				 $decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id in ('.implode(', ',$viewed_ids).')', SqlEntry::IN_SQL));
				 //var_dump($viewed_ids);
				//$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
			break;
			case 1:
				$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id="'.$result['id'].'" and kind_id=1', SqlEntry::IN_SQL));
			break;
			case 2:
				$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id="'.$result['id'].'" and kind_id=2', SqlEntry::IN_SQL));
			break;
			case 3:
				$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id="'.$result['id'].'" and kind_id=3', SqlEntry::IN_SQL));
			break;
			case 4:
				$decorator->AddEntry(new SqlEntry('p.id','select distinct sched_id from  sched_task_users where user_id="'.$result['id'].'" and kind_id=4', SqlEntry::IN_SQL));
			break;	
			
		}
	 
	
		
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_given_pdate1=DateFromdmY('01.01.2015'); //DateFromdmY(date("d.m.Y"))-3*60*60*24*30;
				$given_pdate1=date("d.m.Y", $_given_pdate1);//"01.01.2006";
				
			
		}else{
			 $given_pdate1 = $_GET['pdate1'.$prefix];
			 $_given_pdate1= DateFromdmY($_GET['pdate1'.$prefix]);
		}
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_given_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$given_pdate2=date("d.m.Y", $_given_pdate2);//"01.01.2006";	
				
				//$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($given_pdate2))));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate1',''));
		}
		
	 	
		//фильтр по ответственному
		if(isset($_GET['user_role2'.$prefix])&&(strlen($_GET['user_role2'.$prefix])>0)){
			$names=explode(';', trim($_GET['user_role2'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('u2.name_s', NULL, SqlEntry::LIKE_SET, NULL,$names));	
			
			$decorator->AddEntry(new UriEntry('user_role2',$_GET['user_role2'.$prefix]));
		}
		  
		
		//фильтр по постановщику			
	    if(isset($_GET['user_role1'.$prefix])&&(strlen($_GET['user_role1'.$prefix])>0)){
			$names=explode(';', trim($_GET['user_role1'.$prefix]));
			foreach($names as $k=>$v) $names[$k]=SecStr($v);
			
			$decorator->AddEntry(new SqlEntry('u1.name_s', NULL, SqlEntry::LIKE_SET, NULL,$names));
			$decorator->AddEntry(new UriEntry('user_role1',$_GET['user_role1'.$prefix]));
		}
		  
		
		//блок фильтров статуса
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				   foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}
		
		//блок поиска по содержимому (включая файлы)
		include('inc/shedule_contents_inc.php'); 
		
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.priority',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.priority',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('u1.name_s',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('u1.name_s',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('u2.name_s',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('u2.name_s',SqlOrdEntry::ASC));
				
			break;
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			case 12:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
				
			break;	
			case 13:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
			break;
			
			
			case 14:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
				 
			break;	
			case 15:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('s.weight',SqlOrdEntry::DESC));
				  
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
		//$decorator->AddEntry(new SqlEntry('p.task_id',0, SqlEntry::E));
		  
		
		$docs1=$_tasks->ShowPos(
		
			$prefix, //0
			'plan/table_1.html',  //1
			 $decorator, //2
			  $au->user_rights->CheckAccess('w',905), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',905), //14
			  $au->user_rights->CheckAccess('w',905), //15
			  false, //16
			  false, //17
			  $au->user_rights->CheckAccess('w',946) //18
	 
			
			 );














/****************************************************************************************************/
//командировки
	 
		$prefix=2;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=20;
		  
		$decorator=new DBDecorator;
		
		//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false,2);
		//var_dump($viewed_ids);
		//$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		
		
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		
		 
		
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_pdate1=DateFromdmY('01.01.2015');  
				$pdate1=date("d.m.Y", $_pdate1); 
			
		}else $pdate1 = $_GET['pdate1'.$prefix];
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$pdate2=date("d.m.Y", $_pdate2); 
		}else $pdate2 = $_GET['pdate2'.$prefix];
		
	 
		$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
		$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
		
		
		$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
	 
		
		//блок фильтров статуса
	 
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		  
		  
		  
		  
		 //блок план/факт
	 
		$plan_or_fact=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'plan_or_fact'])&&is_array($_GET[$prefix.'plan_or_fact'])) $cou_stat=count($_GET[$prefix.'plan_or_fact']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $plan_or_fact=$_GET[$prefix.'plan_or_fact'];
		  
	  	}else{
		   
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_plan_or_fact',1));
		   
	  }
	   
	   
	   
	     if(count($plan_or_fact)>0){
			  $of_zero=true; foreach($plan_or_fact as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_plan_or_fact',1));
			  }else{
			  	  
				  $_pof=array();	
				  foreach($plan_or_fact as $k=>$v){ 
				  	$decorator->AddEntry(new UriEntry('plan_or_fact_'.$v,1));
				  
				 
				 	$decorator->AddEntry(new UriEntry($prefix.'plan_or_fact[]',$v));
					$_pof[]='"'.$v.'"';
				  }
				  
				   $decorator->AddEntry(new SqlEntry('p.plan_or_fact', NULL, SqlEntry::IN_VALUES, NULL,$_pof));	
			  }
		  }  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		  
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}  
		  
		  
		//блок поиска по содержимому (включая файлы)
		include('inc/shedule_contents_inc.php');  
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E));
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		 
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=abs((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
			break;
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			 
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
			break;
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
			
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
		
		
		  
		
		$docs2=$_plans->ShowPos($prefix, //0
			 'plan/table_2.html', //1
			  $decorator, //2
			  $au->user_rights->CheckAccess('w',905), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
			  $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20
			   );

	
	
	
/****************************************************************************************************/
//встречи
	 
		$prefix=3;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=20;
		  
		$decorator=new DBDecorator;
		
		//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 3);
		//var_dump($viewed_ids);
		//$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		//$viewed_ids=$_plans->GetAvailableUserIds($result['id']);
		//var_dump($viewed_ids);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		if(!isset($_GET['pdate1'.$prefix])){
	
				$_pdate1=DateFromdmY('01.01.2015');  
				$pdate1=date("d.m.Y", $_pdate1); 
			
		}else $pdate1 = $_GET['pdate1'.$prefix];
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$pdate2=date("d.m.Y", $_pdate2); 
		}else $pdate2 = $_GET['pdate2'.$prefix];
		
	 
		$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
		$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
		   
		   
	 
		
		$decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
		
	   if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		  
		
		//блок фильтров статуса
	 		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		
		
		 //блок план/факт
	 
		$plan_or_fact=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'plan_or_fact'])&&is_array($_GET[$prefix.'plan_or_fact'])) $cou_stat=count($_GET[$prefix.'plan_or_fact']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $plan_or_fact=$_GET[$prefix.'plan_or_fact'];
		  
	  	}else{
		   
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_plan_or_fact',1));
		   
	  }
	   
	   
	   
	     if(count($plan_or_fact)>0){
			  $of_zero=true; foreach($plan_or_fact as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_plan_or_fact',1));
			  }else{
			  	  
				  $_pof=array();	
				  foreach($plan_or_fact as $k=>$v){ 
				  	$decorator->AddEntry(new UriEntry('plan_or_fact_'.$v,1));
				  
				 
				 	$decorator->AddEntry(new UriEntry($prefix.'plan_or_fact[]',$v));
					$_pof[]='"'.$v.'"';
				  }
				  
				   $decorator->AddEntry(new SqlEntry('p.plan_or_fact', NULL, SqlEntry::IN_VALUES, NULL,$_pof));	
			  }
		  }  
		
		
		 if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}
		
		
		//блок поиска по содержимому (включая файлы)
		include('inc/shedule_contents_inc.php');
		  
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E));
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		/*
		$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		
		$decorator->AddEntry(new SqlOrdEntry('p.pdate_end',SqlOrdEntry::ASC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		*/
		
			if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=abs((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('m.name',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('m.name',SqlOrdEntry::ASC));
			break;
			
			
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('c.name',SqlOrdEntry::ASC));
			break;
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			 
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			case 12:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
				
			break;	
			case 13:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
			break;
			
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
			
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
		  
		
		//$docs3=$_plans->ShowPos($prefix, 'plan/table_3.html',  $decorator, $au->user_rights->CheckAccess('w',905), $from, $to_page, true, false,  $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905), $au->user_rights->CheckAccess('w',905) );		
		
		 
		$docs3=$_plans->ShowPos($prefix, //0
			 'plan/table_3.html', //1
			  $decorator, //2
			  $au->user_rights->CheckAccess('w',905), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
		      $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20
			   );
	 
		
  //*************************************************************************************	  
		//звонки
		$prefix=4;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=20;
		  
		$decorator=new DBDecorator;
		
		//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'], false, 4);
		//var_dump($viewed_ids);
		//$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		//видимые сотрудники
		//$viewed_ids=$_plans->GetAvailableUserIds($result['id']);
		//var_dump($viewed_ids);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.created_id',$result['id'], SqlEntry::E));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
	 
	   if(!isset($_GET['pdate1'.$prefix])){
	
				$_pdate1=DateFromdmY('01.01.2015');  
				$pdate1=date("d.m.Y", $_pdate1); 
			
		}else $pdate1 = $_GET['pdate1'.$prefix];
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$pdate2=date("d.m.Y", $_pdate2); 
		}else $pdate2 = $_GET['pdate2'.$prefix];
		
	 
		$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
		$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
		   
		   
		//   $decorator->AddEntry(new SqlEntry('p.pdate_beg',$pdate, SqlEntry::E));
		
		   $decorator->AddEntry(new SqlEntry('p.pdate_beg',date('Y-m-d', DateFromdmY($pdate1)), SqlEntry::BETWEEN,date('Y-m-d', DateFromdmY($pdate2))));
		  
		
		//блок фильтров статуса
	 
		
		$status_ids=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'statuses'])&&is_array($_GET[$prefix.'statuses'])) $cou_stat=count($_GET[$prefix.'statuses']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $status_ids=$_GET[$prefix.'statuses'];
		  
	  	}else{
		  $cou_stat=0; foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $cou_stat++;
		  
		  if($cou_stat>0){
			  //есть кукисы
			  foreach($_COOKIE as $k=>$v) if(eregi('^'.$prefix.'sched_'.$prefix.'status_id_', $k)) $status_ids[]=(int)eregi_replace('^'.$prefix.'sched_'.$prefix.'status_id_','',$k);
		  }else{
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_statuses',1));
		  }
	  }
	   
	     if(count($status_ids)>0){
			  $of_zero=true; foreach($status_ids as $k=>$v) if($v>0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_statuses',1));
			  }else{
			  
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry('status_id_'.$v,1));
				  $decorator->AddEntry(new SqlEntry('p.status_id', NULL, SqlEntry::IN_VALUES, NULL,$status_ids));	
				  foreach($status_ids as $k=>$v) $decorator->AddEntry(new UriEntry($prefix.'statuses[]',$v));
			  }
		  } 
		  
		 //блок план/факт
	 
		$plan_or_fact=array();
	  	$cou_stat=0;   
		if(isset($_GET[$prefix.'plan_or_fact'])&&is_array($_GET[$prefix.'plan_or_fact'])) $cou_stat=count($_GET[$prefix.'plan_or_fact']);
		if($cou_stat>0){
		  //есть гет-запросы	
		  $plan_or_fact=$_GET[$prefix.'plan_or_fact'];
		  
	  	}else{
		   
			  //ничего нет - выбираем ВСЕ!	
			  $decorator->AddEntry(new UriEntry('all_plan_or_fact',1));
		   
	  }
	   
	   
	   
	     if(count($plan_or_fact)>0){
			  $of_zero=true; foreach($plan_or_fact as $k=>$v) if($v>=0) $of_zero=$of_zero&&false;
			  
			  if($of_zero){
				  //ничего нет - выбираем ВСЕ!	
				  $decorator->AddEntry(new UriEntry('all_plan_or_fact',1));
			  }else{
			  	  
				  $_pof=array();	
				  foreach($plan_or_fact as $k=>$v){ 
				  	$decorator->AddEntry(new UriEntry('plan_or_fact_'.$v,1));
				  
				 
				 	$decorator->AddEntry(new UriEntry($prefix.'plan_or_fact[]',$v));
					$_pof[]='"'.$v.'"';
				  }
				  
				   $decorator->AddEntry(new SqlEntry('p.plan_or_fact', NULL, SqlEntry::IN_VALUES, NULL,$_pof));	
			  }
		  }    
		  
		
		if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
			$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
			$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
		}
		 
		//блок поиска по содержимому (включая файлы)
		include('inc/shedule_contents_inc.php');   
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E));
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
	/*	
			$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		
		$decorator->AddEntry(new SqlOrdEntry('p.pdate_end',SqlOrdEntry::ASC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		*/
		
			if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=abs((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
			
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.incoming_or_outcoming',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.incoming_or_outcoming',SqlOrdEntry::ASC));
			break;
			
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('p.plan_or_fact',SqlOrdEntry::ASC));
			break;
			
			 
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::DESC));
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('sup1.full_name',SqlOrdEntry::ASC));
			break;
			 
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
				
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
				
			break;
			
			default:
					
				$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::DESC));
			
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		  
		
		$docs4=$_plans->ShowPos(
			$prefix, 
			'plan/table.html',  
			
			 $decorator, //2
			  $au->user_rights->CheckAccess('w',905), //3
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',905), //8
			  $au->user_rights->CheckAccess('w',905),  //9
			  $au->user_rights->CheckAccess('w',905), //10
			  $au->user_rights->CheckAccess('w',905), //11
			  $au->user_rights->CheckAccess('w',905), //12
			  $au->user_rights->CheckAccess('w',905), //13
			  $au->user_rights->CheckAccess('w',915), //14
		      $au->user_rights->CheckAccess('w',916), //15
			  
			  
			  $au->user_rights->CheckAccess('w',923), //16
			  $au->user_rights->CheckAccess('w',924), //17
			  $au->user_rights->CheckAccess('w',925), //18
			  $au->user_rights->CheckAccess('w',926), //19
			  $au->user_rights->CheckAccess('w',927) //20
			 );
		
	   
		
  //*****************************************************************************************	  
		//заметки
	   
		 $prefix=5;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=20;
		  
		$decorator=new DBDecorator;
		
		
		//только актуалные / все заметки
		if(isset($_GET['note_is_actual'.$prefix])&&($_GET['note_is_actual'.$prefix]==1)){
		$decorator->AddEntry(new SqlEntry('p.note_is_actual',1, SqlEntry::E));
		$decorator->AddEntry(new UriEntry('note_is_actual',1));
		}elseif(isset($_GET['note_is_actual'.$prefix])&&($_GET['note_is_actual'.$prefix]==0)){
			 $decorator->AddEntry(new UriEntry('note_is_actual',0));	
		}else{
			$count_of_our_get=0; foreach($_GET as $k=>$v) if(eregi($prefix.'$', $k)) $count_of_our_get++;
			
			//if(count($_GET)>1){
			if($count_of_our_get>0){
				 $decorator->AddEntry(new UriEntry('note_is_actual',0));	
				 //echo 'ZZZZZZZZZZZZzz';
			}else {
				$decorator->AddEntry(new UriEntry('note_is_actual',1));	
				$decorator->AddEntry(new SqlEntry('p.note_is_actual',1, SqlEntry::E));
			}
		}
		
		
		

		
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		 
		//видимые сотрудники
		$viewed_ids=$_plans->GetAvailableUserIds($result['id'],false, 5);
		//var_dump($viewed_ids);
		$decorator->AddEntry(new SqlEntry('p.manager_id', NULL, SqlEntry::IN_VALUES, NULL,$viewed_ids));	
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
		
		$decorator->AddEntry(new SqlEntry('p.id','select sched_id from sched_users where user_id="'.$result['id'].'"', SqlEntry::IN_SQL));
		
		$decorator->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
		
		if(isset($_GET['code'.$prefix])&&(strlen($_GET['code'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.code',SecStr($_GET['code'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('code',$_GET['code'.$prefix]));
		}
		
		
		if(isset($_GET['topic'.$prefix])&&(strlen($_GET['topic'.$prefix])>0)){
		  $decorator->AddEntry(new SqlEntry('p.topic',SecStr($_GET['topic'.$prefix]), SqlEntry::LIKE));
		  $decorator->AddEntry(new UriEntry('topic',$_GET['topic'.$prefix]));
		}
		
	   if(isset($_GET['manager_name'.$prefix])&&(strlen($_GET['manager_name'.$prefix])>0)){
		$decorator->AddEntry(new SqlEntry('u.name_s',SecStr($_GET['manager_name'.$prefix]), SqlEntry::LIKE));
		$decorator->AddEntry(new UriEntry('manager_name',$_GET['manager_name'.$prefix]));
	}
		  
		
		 
		
	 
	   if(!isset($_GET['pdate1'.$prefix])){
	
				$_pdate1=DateFromdmY('01.01.2015');  
				$pdate1=date("d.m.Y", $_pdate1); 
			
		}else $pdate1 = $_GET['pdate1'.$prefix];
		
		
		
		if(!isset($_GET['pdate2'.$prefix])){
				
				$_pdate2=DateFromdmY(date("d.m.Y"))+30*60*60*24;
				$pdate2=date("d.m.Y", $_pdate2); 
		}else $pdate2 = $_GET['pdate2'.$prefix];
		
	 
		$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
		$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
		   
		   
		//   $decorator->AddEntry(new SqlEntry('p.pdate_beg',$pdate, SqlEntry::E));
    
	   $decorator->AddEntry(new SqlEntry('p.pdate', DateFromdmY($pdate1) , SqlEntry::BETWEEN, DateFromdmY($pdate2)+24*60*60));
		
		//блок поиска по содержимому (включая файлы)
		include('inc/shedule_contents_inc.php');
		
		$decorator->AddEntry(new SqlEntry('p.kind_id',$prefix, SqlEntry::E));
		  
		$decorator->AddEntry(new UriEntry('pdate',$pdate));
		
		
		/*
		$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
		*/
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=abs((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
			case 0:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('p.code',SqlOrdEntry::ASC));
			break;
			
			case 2:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		
			break;	
			case 3:
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
	
			break;
			
			case 4:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::DESC));
			break;	
			case 5:
				$decorator->AddEntry(new SqlOrdEntry('p.topic',SqlOrdEntry::ASC));
			break;
			
			case 6:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::DESC));
			break;	
			case 7:
				$decorator->AddEntry(new SqlOrdEntry('u.name_s',SqlOrdEntry::ASC));
			break;
			
			case 8:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::DESC));
				 
			break;	
			case 9:
				$decorator->AddEntry(new SqlOrdEntry('sup.full_name',SqlOrdEntry::ASC));
			break;
			
			
			case 10:
				$decorator->AddEntry(new SqlOrdEntry('p.note_is_actual',SqlOrdEntry::DESC));
				 
			break;	
			case 11:
				$decorator->AddEntry(new SqlOrdEntry('p.note_is_actual',SqlOrdEntry::ASC));
			break;
			
			

			
			default:
				$decorator->AddEntry(new SqlOrdEntry('p.note_is_actual',SqlOrdEntry::DESC));	
					
				$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
				$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::DESC));
			
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		 
		 
		 
		  
		
		$docs5=$_plans->ShowPos($prefix, 'plan/table_5.html',  $decorator, $au->user_rights->CheckAccess('w',905),  $from, $to_page);
		
		
		
		
		
		
		
		
		$sm1->assign('log1', $docs1);
		$sm1->assign('log2', $docs2);
		$sm1->assign('log3', $docs3);
		$sm1->assign('log4', $docs4);
		$sm1->assign('log5', $docs5);
		
		 
  //*****************************************************************************************	  	   
		//ribbon
		
		 $decorator=new DBDecorator;
		
		//$decorator->AddEntry(new SqlEntry('p.manager_id',$result['id'], SqlEntry::E));
		 
		
		$decorator->AddEntry(new SqlEntry('p.status_id',3, SqlEntry::NE));
	  
		$decorator->AddEntry(new SqlOrdEntry('p.pdate_beg',SqlOrdEntry::ASC));
		$decorator->AddEntry(new SqlOrdEntry('p.ptime_beg',SqlOrdEntry::ASC));
		$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		
		
		
		
		$sm1->assign('ribbon',$_plans->ShowPosADayArr($pdate, $decorator, $result['id'])); 
		 
		$sm1->assign('can_create', $au->user_rights->CheckAccess('w',904));
		$sm1->assign('can_edit', $au->user_rights->CheckAccess('w',905));
		
		$sm1->assign('force_create', isset($_GET['force_create']));
		
		
		if(isset($_GET['force_tab'])){
			$sm1->assign('force_tab', $_GET['force_tab']);	
		}
		
		
		$content=$sm1->fetch('plan/plan.html'); 
		
		
		$log=new ActionLog;
	 
		$log->PutEntry($result['id'],'открыл планировщик',NULL,903, NULL);
	 

	}

$smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('fast_menu', $menu_arr_fast);
	  $smarty->assign('main_menu',$menu_res);
	$smarty->assign('content',$content);
	$smarty->display('page.html');
	unset($smarty);
 

 }
 
$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>