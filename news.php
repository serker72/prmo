<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей
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


require_once('classes/news.class.php');

 
$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

 


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE","GYDEX.Новости отрасли");

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
	  $sm=new SmartyAdm;
	  
	  
	  
 	if(!$au->user_rights->CheckAccess('w',1118)){
		$content='<h1>GYDEX.В работе!</h1>';
		
	}else{
		//$content=$sm->fetch('fast_reports.html');
		 $sm1=new SmartyAdm;
		
		$_plans=new News_StreamGroup;
		$_plans->SetAuthResult($result);
		
		$prefix=1;
		
		if(isset($_GET['from'.$prefix])) $from=abs((int)$_GET['from'.$prefix]);
		  else $from=0;
		if(isset($_GET['to_page'.$prefix])) $to_page=abs((int)$_GET['to_page'.$prefix]);
		  else $to_page=30;
		  
		$decorator=new DBDecorator;
		
	 
		 
		
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
				
		}else{
			 $given_pdate2 = $_GET['pdate2'.$prefix];
			  $_given_pdate2= DateFromdmY($_GET['pdate2'.$prefix]);
		}
		
		if(isset($_GET['pdate1'.$prefix])&&isset($_GET['pdate2'.$prefix])&&($_GET['pdate2'.$prefix]!="")&&($_GET['pdate2'.$prefix]!="-")&&($_GET['pdate1'.$prefix]!="")&&($_GET['pdate1'.$prefix]!="-")){
			$decorator->AddEntry(new UriEntry('pdate1',$given_pdate1));
			$decorator->AddEntry(new UriEntry('pdate2',$given_pdate2));
			$decorator->AddEntry(new SqlEntry('n.pdate',  date('Y-m-d H:i:s', DateFromdmY($given_pdate1)), SqlEntry::BETWEEN,date('Y-m-d H:i:s',  DateFromdmY($given_pdate2)+24*60*60-1)));
		}else{
					$decorator->AddEntry(new UriEntry('pdate1',''));
				$decorator->AddEntry(new UriEntry('pdate2',''));
		}
		 
		 
		  
		
		if(!isset($_GET['sortmode'.$prefix])){
			$sortmode=-1;	
		}else{
			$sortmode=((int)$_GET['sortmode'.$prefix]);
		}
		
			
			
		switch($sortmode){
		 	case 0:
				$decorator->AddEntry(new SqlOrdEntry('n.pdate',SqlOrdEntry::DESC));
			break;
			case 1:
				$decorator->AddEntry(new SqlOrdEntry('n.pdate',SqlOrdEntry::ASC));
			break;
			 
			 
			
			default:
					
				 
				$decorator->AddEntry(new SqlOrdEntry('n.pdate',SqlOrdEntry::DESC));
				  
			 
				 
			break;	
			
		}
		 
		$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
		
		
	
	  
		
		 $docs1=$_plans->ShowPos(
		 
			'news/table.html',  //0
			 $decorator,  //1
			  
			  $from, //4
			  $to_page, //5
			  true, //6
			  false,  //7
			  $au->user_rights->CheckAccess('w',1119), //8
			 
			  $prefix //13
	 
			
			 );
 

 


 
		
		
		
		$sm1->assign('log1', $docs1);
	 
		
		$content=$sm1->fetch('news/news.html'); 
		
		
		$log=new ActionLog;
	 
		$log->PutEntry($result['id'],'открыл раздел Новости',NULL,1118, NULL);

		
	}
	  $sm->assign('fast_menu', $menu_arr_fast);
	  
	 

 
 
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