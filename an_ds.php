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


require_once('classes/an_ds.php');

require_once('classes/an_payp.php');
require_once('classes/an_payp_in.php');

require_once('classes/an_paybuh.php');
require_once('classes/an_paybuh_in.php');
require_once('classes/an_cash.php');
require_once('classes/an_cash_in.php');



$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Денежные средства');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;





if(!isset($_GET['print'])){
	if(!isset($_POST['print'])){
		$print=0;
	}else $print=abs((int)$_POST['print']); 
}else $print=abs((int)$_GET['print']);

if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);

if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);

if(!isset($_GET['sortmode3'])){
	if(!isset($_POST['sortmode3'])){
		$sortmode3=0;
	}else $sortmode3=abs((int)$_POST['sortmode3']); 
}else $sortmode3=abs((int)$_GET['sortmode3']);


if(!isset($_GET['sortmode5'])){
	if(!isset($_POST['sortmode5'])){
		$sortmode5=11;
	}else $sortmode5=abs((int)$_POST['sortmode5']); 
}else $sortmode5=abs((int)$_GET['sortmode5']);


if($print!=0){
	if(!$au->user_rights->CheckAccess('w',370)&&!$au->user_rights->CheckAccess('w',373)&&!$au->user_rights->CheckAccess('w',375)&&!$au->user_rights->CheckAccess('w',483)&&!$au->user_rights->CheckAccess('w',859)&&!$au->user_rights->CheckAccess('w',902)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
}

if(!$au->user_rights->CheckAccess('w',369)&&!$au->user_rights->CheckAccess('w',372)&&!$au->user_rights->CheckAccess('w',374)&&!$au->user_rights->CheckAccess('w',482)&&!$au->user_rights->CheckAccess('w',858)&&!$au->user_rights->CheckAccess('w',901)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}

$log->PutEntry($result['id'],'перешел в Отчеты Движение д/с',NULL,NULL,NULL,NULL);


//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

	
	$_menu_id=22;

	if($print==0) include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	
	//****************************** вкладка движение д/с ******************************
	
	
	
	//декоратор используем для многостраничности (если понадобится)
	$decorator=new DBDecorator;
	
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	
	if(!isset($_GET['pdate1'])){
	
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate1'];
	
	
	
	if(!isset($_GET['pdate2'])){
			
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	

	
	if(isset($_GET['bank_id'])&&(is_array($_GET['bank_id']))){
		
		
		
		$decorator->AddEntry(new UriEntry('bank_id',$_GET['bank_id']));
	
		if(is_array($_GET['bank_id'])){
	
			$bank_id=$_GET['bank_id'];
		  
		}else{
			$bank_id=array();
			$bank_id[]=abs((int)$_GET['bank_id']);
		}
		
	}else{
		//$storage_id=0;
		$bank_id=array();
	}

	
	
	if(isset($_GET['extended_an'])&&($_GET['extended_an']==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	
	$as=new AnDs;
	$filetext=$as->ShowData(DateFromdmY($pdate1), DateFromdmY($pdate2), $bank_id, $extended_an, $result['org_id'], 'an_ds/an_ds_list'.$print_add.'.html',$decorator,'an_ds.php',isset($_GET['doSub'])||isset($_GET['doSub_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1)),$au->user_rights->CheckAccess('w',370),DEC_SEP,$alls);
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log',$filetext);
	
	$sm->assign('has_1',  $au->user_rights->CheckAccess('w',369));
	
	
	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==1))){
		$log->PutEntry($result['id'],'открыл отчет Движение д/с',NULL,369,NULL, NULL);	
	}
	
	
	
	//********************************************************************************************

	//вкладка план оплаты ВХОДЯЩИЕ	
	
	$as1=new AnPayUnivIn;
	$prefix=$as1->prefix;
	
	
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			
			$pdate1=date('d.m.Y',0);
			$pdate_decor1='-';
		
	}else{
		 if(($_GET['pdate'.$prefix.'_1']=='-')||($_GET['pdate'.$prefix.'_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate'.$prefix.'_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate'.$prefix.'_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
	
			
			$pdate2='31.12.2030';
			$pdate_decor2='-';
		
	}else{
		 if(($_GET['pdate'.$prefix.'_2']=='-')||($_GET['pdate'.$prefix.'_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate'.$prefix.'_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate'.$prefix.'_2'];
		 
	}
	
	
	if(isset($_GET['only_vyp'.$prefix])&&($_GET['only_vyp'.$prefix]==1)){
		$only_vyp=1;
		$decorator->AddEntry(new UriEntry('only_vyp',1));	
			
	}else{
		$only_vyp=0;
		$decorator->AddEntry(new UriEntry('only_vyp',0));	
	}
	
	if(isset($_GET['only_not_vyp'.$prefix])&&($_GET['only_not_vyp'.$prefix]==1)){
		$only_not_vyp=1;
		$decorator->AddEntry(new UriEntry('only_not_vyp',1));	
			
	}else{
		$only_not_vyp=0;
		$decorator->AddEntry(new UriEntry('only_not_vyp',0));	
	}
	
	
	if(isset($_GET['only_not_payed'.$prefix])&&($_GET['only_not_payed'.$prefix]==1)){
		$only_not_payed=1;
		$decorator->AddEntry(new UriEntry('only_not_payed',1));	
			
	}else{
		$only_not_payed=0;
		$decorator->AddEntry(new UriEntry('only_not_payed',0));	
	}
	
	if(isset($_GET['only_semi_payed'.$prefix])&&($_GET['only_semi_payed'.$prefix]==1)){
		$only_semi_payed=1;
		$decorator->AddEntry(new UriEntry('only_semi_payed',1));	
			
	}else{
		$only_semi_payed=0;
		$decorator->AddEntry(new UriEntry('only_semi_payed',0));	
	}
	
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate_decor2));
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name'.$prefix]);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
	}else $supplier_name='';

	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',2));
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::DESC));
		break;
		
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::ASC));
		break;
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('is_in_buh',SqlOrdEntry::DESC));
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('is_in_buh',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::ASC));
		break;	
		
	}
	
	

	$filetext=$as1->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $only_vyp, $only_not_vyp,  $only_not_payed, false, false,  'an_payp/an_payp_list'.$print_add.'.html', $decorator,'an_ds.php',isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2)), $au->user_rights->CheckAccess('w',373),DEC_SEP, $alls,$au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635),$only_semi_payed);
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log3',$filetext);
	
	
	$sm->assign('has_2',  $au->user_rights->CheckAccess('w',372));
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==2))){
		$log->PutEntry($result['id'],'открыл отчет План оплаты входящие',NULL,372,NULL, NULL);	
	}
	
	
	//********************************************************************************************

	//вкладка план оплаты ИСХОДЯЩИЕ	
	
	$as1=new AnPayUniv;
	$prefix=$as1->prefix;
	
	
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			
			$pdate1=date('d.m.Y',0);
			$pdate_decor1='-';
		
	}else{
		 if(($_GET['pdate'.$prefix.'_1']=='-')||($_GET['pdate'.$prefix.'_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate'.$prefix.'_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate'.$prefix.'_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
	
			
			$pdate2='31.12.2030';
			$pdate_decor2='-';
		
	}else{
		 if(($_GET['pdate'.$prefix.'_2']=='-')||($_GET['pdate'.$prefix.'_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate'.$prefix.'_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate'.$prefix.'_2'];
		 
	}
	
	
	if(isset($_GET['only_vyp'.$prefix])&&($_GET['only_vyp'.$prefix]==1)){
		$only_vyp=1;
		$decorator->AddEntry(new UriEntry('only_vyp',1));	
			
	}else{
		$only_vyp=0;
		$decorator->AddEntry(new UriEntry('only_vyp',0));	
	}
	
	if(isset($_GET['only_not_vyp'.$prefix])&&($_GET['only_not_vyp'.$prefix]==1)){
		$only_not_vyp=1;
		$decorator->AddEntry(new UriEntry('only_not_vyp',1));	
			
	}else{
		$only_not_vyp=0;
		$decorator->AddEntry(new UriEntry('only_not_vyp',0));	
	}
	
	
	if(isset($_GET['only_not_payed'.$prefix])&&($_GET['only_not_payed'.$prefix]==1)){
		$only_not_payed=1;
		$decorator->AddEntry(new UriEntry('only_not_payed',1));	
			
	}else{
		$only_not_payed=0;
		$decorator->AddEntry(new UriEntry('only_not_payed',0));	
	}
	
	if(isset($_GET['only_semi_payed'.$prefix])&&($_GET['only_semi_payed'.$prefix]==1)){
		$only_semi_payed=1;
		$decorator->AddEntry(new UriEntry('only_semi_payed',1));	
			
	}else{
		$only_semi_payed=0;
		$decorator->AddEntry(new UriEntry('only_semi_payed',0));	
	}
	
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate_decor2));
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name'.$prefix]);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
	}else $supplier_name='';

	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',3));
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::DESC));
		break;
		
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::ASC));
		break;
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('is_in_buh',SqlOrdEntry::DESC));
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('is_in_buh',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('id',SqlOrdEntry::ASC));
		break;	
		
	}
	
	

	$filetext=$as1->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $only_vyp, $only_not_vyp,  $only_not_payed, false, false,  'an_payp/an_payp_list'.$print_add.'.html', $decorator,'an_ds.php',isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3)), $au->user_rights->CheckAccess('w',373),DEC_SEP, $alls,$au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481), $only_semi_payed);
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log4',$filetext);
	
	
	$sm->assign('has_2',  $au->user_rights->CheckAccess('w',372));
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==3))){
		$log->PutEntry($result['id'],'открыл отчет План оплаты исходящие',NULL,372,NULL, NULL);	
	}
	
	
	
//********************************************************************************************

	//вкладка факт оплаты ВХОДЯЩИЕ	
	
	$as1=new AnPayUnivIn;
	$as1->prefix='_5';
	$prefix=$as1->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
		
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			
			//$pdate1=date('d.m.Y',0);
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);
			$pdate_decor1=$pdate1;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_1']=='-')||($_GET['pdate'.$prefix.'_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate'.$prefix.'_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate'.$prefix.'_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
	
			
			//$pdate2='31.12.2030';
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			$pdate_decor2=$pdate2;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_2']=='-')||($_GET['pdate'.$prefix.'_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate'.$prefix.'_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate'.$prefix.'_2'];
		 
	}
	
	
	if(isset($_GET['only_vyp'.$prefix])&&($_GET['only_vyp'.$prefix]==1)){
		$only_vyp=1;
		$decorator->AddEntry(new UriEntry('only_vyp',1));	
			
	}else{
		$only_vyp=0;
		$decorator->AddEntry(new UriEntry('only_vyp',0));	
	}
	
	if(isset($_GET['only_not_vyp'.$prefix])&&($_GET['only_not_vyp'.$prefix]==1)){
		$only_not_vyp=1;
		$decorator->AddEntry(new UriEntry('only_not_vyp',1));	
			
	}else{
		$only_not_vyp=0;
		$decorator->AddEntry(new UriEntry('only_not_vyp',0));	
	}
	
	
	if(isset($_GET['only_payed'.$prefix])&&($_GET['only_payed'.$prefix]==1)){
		$only_payed=1;
		$decorator->AddEntry(new UriEntry('only_payed',1));	
			
	}else{
		$only_payed=0;
		$decorator->AddEntry(new UriEntry('only_payed',0));	
	}
	
	if(isset($_GET['only_semi_payed'.$prefix])&&($_GET['only_semi_payed'.$prefix]==1)){
		$only_semi_payed=1;
		$decorator->AddEntry(new UriEntry('only_semi_payed',1));	
			
	}else{
		$only_semi_payed=0;
		$decorator->AddEntry(new UriEntry('only_semi_payed',0));	
	}
	
	$decorator->AddEntry(new UriEntry('pdate3_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate3_2',$pdate_decor2));
	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name'.$prefix]);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
	}else $supplier_name='';
	

	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',4));
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::DESC));
		break;
		
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::ASC));
		break;
		
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('b.id',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	//...

	$filetext=$as1->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $only_vyp, $only_not_vyp,  false, $only_payed, true, 'an_payp/an_payf_list'.$print_add.'.html', $decorator,'an_ds.php',isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==4)), $au->user_rights->CheckAccess('w',375),DEC_SEP, $alls, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481), $only_semi_payed);
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log5',$filetext);
	
	
	$sm->assign('has_3',  $au->user_rights->CheckAccess('w',374));
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==4))){
		$log->PutEntry($result['id'],'открыл отчет Факт оплаты входящие',NULL,374,NULL, NULL);	
	}
	
	
	//********************************************************************************************

	//вкладка факт оплаты ИСХОДЯЩИЕ	
	
	$as1=new AnPayUniv;
	$as1->prefix='_4';
	$prefix=$as1->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
		
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			
			//$pdate1=date('d.m.Y',0);
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);
			$pdate_decor1=$pdate1;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_1']=='-')||($_GET['pdate'.$prefix.'_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate'.$prefix.'_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate'.$prefix.'_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
	
			
			//$pdate2='31.12.2030';
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			$pdate_decor2=$pdate2;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_2']=='-')||($_GET['pdate'.$prefix.'_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate'.$prefix.'_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate'.$prefix.'_2'];
		 
	}
	
	
	if(isset($_GET['only_vyp'.$prefix])&&($_GET['only_vyp'.$prefix]==1)){
		$only_vyp=1;
		$decorator->AddEntry(new UriEntry('only_vyp',1));	
			
	}else{
		$only_vyp=0;
		$decorator->AddEntry(new UriEntry('only_vyp',0));	
	}
	
	if(isset($_GET['only_not_vyp'.$prefix])&&($_GET['only_not_vyp'.$prefix]==1)){
		$only_not_vyp=1;
		$decorator->AddEntry(new UriEntry('only_not_vyp',1));	
			
	}else{
		$only_not_vyp=0;
		$decorator->AddEntry(new UriEntry('only_not_vyp',0));	
	}
	
	
	if(isset($_GET['only_payed'.$prefix])&&($_GET['only_payed'.$prefix]==1)){
		$only_payed=1;
		$decorator->AddEntry(new UriEntry('only_payed',1));	
			
	}else{
		$only_payed=0;
		$decorator->AddEntry(new UriEntry('only_payed',0));	
	}
	
	if(isset($_GET['only_semi_payed'.$prefix])&&($_GET['only_semi_payed'.$prefix]==1)){
		$only_semi_payed=1;
		$decorator->AddEntry(new UriEntry('only_semi_payed',1));	
			
	}else{
		$only_semi_payed=0;
		$decorator->AddEntry(new UriEntry('only_semi_payed',0));	
	}
	
	
	$decorator->AddEntry(new UriEntry('pdate3_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate3_2',$pdate_decor2));
	
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix]));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'.$prefix])));
		}else{
			 $supplier_name=SecStr($_GET['supplier_name'.$prefix]);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		}
	}else $supplier_name='';
	

	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',5));
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('code',SqlOrdEntry::DESC));
		break;
		
		
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('status_id',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('pdate_payment_contract',SqlOrdEntry::ASC));
		break;
		
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('b.id',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	//...

	$filetext=$as1->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $only_vyp, $only_not_vyp,  false, $only_payed, true, 'an_payp/an_payf_list'.$print_add.'.html', $decorator,'an_ds.php',isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==5)), $au->user_rights->CheckAccess('w',375),DEC_SEP, $alls, $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481), $only_semi_payed);
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log6',$filetext);
	
	
	$sm->assign('has_3',  $au->user_rights->CheckAccess('w',374));
	
	
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==5))){
		$log->PutEntry($result['id'],'открыл отчет Факт оплаты исходящие',NULL,374,NULL, NULL);	
	}
	
	
	
	//********************************************************************************************

	//вкладка документы в бухгалтерию ВХОД
	

	
	$as=new AnPayBuhIn();
	$prefix=$as->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else  $print_add='_print';
	//else $print_add='_printreestr';
	
		
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	
	switch($print){
		case 0:
		 $templ='an_payp/an_paybuh_list.html';
		break;
		case 1:
		 $templ='an_payp/an_paybuh_list_print.html';
		break;
		default: 
		 $templ='an_payp/an_paybuh_list_printreestr.html';
		break;
	};
	
	
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			
			//$pdate1=date('d.m.Y',0);
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*6;
			$pdate1=date("d.m.Y", $_pdate1);
			$pdate_decor1=$pdate1;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_1']=='-')||($_GET['pdate'.$prefix.'_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate'.$prefix.'_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate'.$prefix.'_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
	
			
			//$pdate2='31.12.2030';
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			$pdate_decor2=$pdate2;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_2']=='-')||($_GET['pdate'.$prefix.'_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate'.$prefix.'_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate'.$prefix.'_2'];
		 
	}
	
	
	if(isset($_GET['only_vyp'.$prefix])&&($_GET['only_vyp'.$prefix]==1)){
		$only_vyp=1;
		$decorator->AddEntry(new UriEntry('only_vyp',1));	
			
	}else{
		$only_vyp=0;
		$decorator->AddEntry(new UriEntry('only_vyp',0));	
	}
	
	if(isset($_GET['only_not_vyp'.$prefix])&&($_GET['only_not_vyp'.$prefix]==1)){
		$only_not_vyp=1;
		$decorator->AddEntry(new UriEntry('only_not_vyp',1));	
			
	}else{
		$only_not_vyp=0;
		$decorator->AddEntry(new UriEntry('only_not_vyp',0));	
	}
	
	
	if(isset($_GET['only_not_payed'.$prefix])&&($_GET['only_not_payed'.$prefix]==1)){
		$only_not_payed=1;
		$decorator->AddEntry(new UriEntry('only_not_payed',1));	
			
	}else{
		$only_not_payed=0;
		$decorator->AddEntry(new UriEntry('only_not_payed',0));	
	}
	
	if(isset($_GET['only_in_buh'.$prefix])&&($_GET['only_in_buh'.$prefix]==1)){
		$only_in_buh=1;
		$decorator->AddEntry(new UriEntry('only_in_buh',1));	
			
	}else{
		$only_in_buh=0;
		$decorator->AddEntry(new UriEntry('only_in_buh',0));	
	}
	
	if(isset($_GET['only_not_in_buh'.$prefix])&&($_GET['only_not_in_buh'.$prefix]==1)){
		$only_not_in_buh=1;
		$decorator->AddEntry(new UriEntry('only_not_in_buh',1));	
			
	}else{
		$only_not_in_buh=0;
		$decorator->AddEntry(new UriEntry('only_not_in_buh',0));	
	}
	
	
	if(isset($_GET['bills_not_payed'.$prefix])&&($_GET['bills_not_payed'.$prefix]==1)){
		$bills_not_payed=1;
		$decorator->AddEntry(new UriEntry('bills_not_payed',1));	
			
	}else{
		$bills_not_payed=0;
		$decorator->AddEntry(new UriEntry('bills_not_payed',0));	
	}
	
	
	if(isset($_GET['bills_payed'.$prefix])&&($_GET['bills_payed'.$prefix]==1)){
		$bills_payed=1;
		$decorator->AddEntry(new UriEntry('bills_payed',1));	
			
	}else{
		$bills_payed=0;
		$decorator->AddEntry(new UriEntry('bills_payed',0));	
	}
	
	if(isset($_GET['bills_semi_payed'.$prefix])&&($_GET['bills_semi_payed'.$prefix]==1)){
		$bills_semi_payed=1;
		$decorator->AddEntry(new UriEntry('bills_semi_payed',1));	
			
	}else{
		$bills_semi_payed=0;
		$decorator->AddEntry(new UriEntry('bills_semi_payed',0));	
	}
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('pdate5_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate5_2',$pdate_decor2));
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		$supplier_name=SecStr($_GET['supplier_name'.$prefix]);
	}else $supplier_name='';

	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',6));
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::DESC));
		break;
		
		case 2:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('b.in_buh_pdate',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('b.in_buh_pdate',SqlOrdEntry::ASC));
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('b.is_in_buh',SqlOrdEntry::DESC));
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('b.is_in_buh',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('b.is_in_buh',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	
	
	//...
	
	//$filetext5=$as->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $only_vyp, $only_not_vyp,  $only_not_payed, $only_in_buh, $only_not_in_buh, $templ, $decorator,'an_ds.php',isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==6)), $au->user_rights->CheckAccess('w',483), DEC_SEP, $alls,  $au->user_rights->CheckAccess('w',634), $au->user_rights->CheckAccess('w',635),$result, $bills_payed, $bills_not_payed, $bills_semi_payed);
	$filetext5='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log7',$filetext5);
	
	
	$sm->assign('has_5',  $au->user_rights->CheckAccess('w',482));
	
	
	
	//********************************************************************************************

	//вкладка документы в бухгалтерию ВХОД
	

	
	$as=new AnPayBuh();
	$prefix=$as->prefix;
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else  $print_add='_print';
	//else $print_add='_printreestr';
	
		
	if(!isset($_GET['sortmode'.$prefix])){
		if(!isset($_POST['sortmode'.$prefix])){
			$sortmode=0;
		}else $sortmode=abs((int)$_POST['sortmode'.$prefix]); 
	}else $sortmode=abs((int)$_GET['sortmode'.$prefix]);
	
	
	switch($print){
		case 0:
		 $templ='an_payp/an_paybuh_list.html';
		break;
		case 1:
		 $templ='an_payp/an_paybuh_list_print.html';
		break;
		default: 
		 $templ='an_payp/an_paybuh_list_printreestr.html';
		break;
	};
	
	
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			
			//$pdate1=date('d.m.Y',0);
			$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*6;
			$pdate1=date("d.m.Y", $_pdate1);
			$pdate_decor1=$pdate1;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_1']=='-')||($_GET['pdate'.$prefix.'_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate'.$prefix.'_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate'.$prefix.'_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
	
			
			//$pdate2='31.12.2030';
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			$pdate_decor2=$pdate2;
		
	}else{
		 if(($_GET['pdate'.$prefix.'_2']=='-')||($_GET['pdate'.$prefix.'_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate'.$prefix.'_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate'.$prefix.'_2'];
		 
	}
	
	
	if(isset($_GET['only_vyp'.$prefix])&&($_GET['only_vyp'.$prefix]==1)){
		$only_vyp=1;
		$decorator->AddEntry(new UriEntry('only_vyp',1));	
			
	}else{
		$only_vyp=0;
		$decorator->AddEntry(new UriEntry('only_vyp',0));	
	}
	
	if(isset($_GET['only_not_vyp'.$prefix])&&($_GET['only_not_vyp'.$prefix]==1)){
		$only_not_vyp=1;
		$decorator->AddEntry(new UriEntry('only_not_vyp',1));	
			
	}else{
		$only_not_vyp=0;
		$decorator->AddEntry(new UriEntry('only_not_vyp',0));	
	}
	
	
	if(isset($_GET['only_not_payed'.$prefix])&&($_GET['only_not_payed'.$prefix]==1)){
		$only_not_payed=1;
		$decorator->AddEntry(new UriEntry('only_not_payed',1));	
			
	}else{
		$only_not_payed=0;
		$decorator->AddEntry(new UriEntry('only_not_payed',0));	
	}
	
	if(isset($_GET['only_in_buh'.$prefix])&&($_GET['only_in_buh'.$prefix]==1)){
		$only_in_buh=1;
		$decorator->AddEntry(new UriEntry('only_in_buh',1));	
			
	}else{
		$only_in_buh=0;
		$decorator->AddEntry(new UriEntry('only_in_buh',0));	
	}
	
	if(isset($_GET['only_not_in_buh'.$prefix])&&($_GET['only_not_in_buh'.$prefix]==1)){
		$only_not_in_buh=1;
		$decorator->AddEntry(new UriEntry('only_not_in_buh',1));	
			
	}else{
		$only_not_in_buh=0;
		$decorator->AddEntry(new UriEntry('only_not_in_buh',0));	
	}
	
	
	
	if(isset($_GET['bills_not_payed'.$prefix])&&($_GET['bills_not_payed'.$prefix]==1)){
		$bills_not_payed=1;
		$decorator->AddEntry(new UriEntry('bills_not_payed',1));	
			
	}else{
		$bills_not_payed=0;
		$decorator->AddEntry(new UriEntry('bills_not_payed',0));	
	}
	
	
	if(isset($_GET['bills_payed'.$prefix])&&($_GET['bills_payed'.$prefix]==1)){
		$bills_payed=1;
		$decorator->AddEntry(new UriEntry('bills_payed',1));	
			
	}else{
		$bills_payed=0;
		$decorator->AddEntry(new UriEntry('bills_payed',0));	
	}
	
	if(isset($_GET['bills_semi_payed'.$prefix])&&($_GET['bills_semi_payed'.$prefix]==1)){
		$bills_semi_payed=1;
		$decorator->AddEntry(new UriEntry('bills_semi_payed',1));	
			
	}else{
		$bills_semi_payed=0;
		$decorator->AddEntry(new UriEntry('bills_semi_payed',0));	
	}
	
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('pdate5_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate5_2',$pdate_decor2));
	
	
	if(isset($_GET['supplier_name'.$prefix])&&(strlen($_GET['supplier_name'.$prefix])>0)){
		$decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name'.$prefix]));
		$supplier_name=SecStr($_GET['supplier_name'.$prefix]);
	}else $supplier_name='';

	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',7));
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('b.code',SqlOrdEntry::DESC));
		break;
		
		case 2:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('b.status_id',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('b.in_buh_pdate',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('b.in_buh_pdate',SqlOrdEntry::ASC));
		break;
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('b.is_in_buh',SqlOrdEntry::DESC));
		break;	
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('b.is_in_buh',SqlOrdEntry::ASC));
		break;
		
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('b.is_in_buh',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	
	
	//...
	
	//$filetext5=$as->ShowData($supplier_name, $result['org_id'], DateFromdmY($pdate1), DateFromdmY($pdate2), $only_vyp, $only_not_vyp,  $only_not_payed, $only_in_buh, $only_not_in_buh, $templ, $decorator,'an_ds.php',isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==6)), $au->user_rights->CheckAccess('w',483), DEC_SEP, $alls,  $au->user_rights->CheckAccess('w',480), $au->user_rights->CheckAccess('w',481),$result, $bills_payed, $bills_not_payed, $bills_semi_payed);
	$filetext5='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log8',$filetext5);
	
	
	$sm->assign('has_5',  $au->user_rights->CheckAccess('w',482));
	
	
	
	
	
	








//***************************** ОТЧЕТ РАСХОД НАЛИЧНЫХ ****************************************************************/

	
	$as1=new AnCash;
	$prefix=$as1->prefix;
	
	
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	 
	
		
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			$_pdate1=mktime(0,0,0,date('m'),1,date('Y')); //DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate'.$prefix.'_1'];
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
			
			$_pdate2=mktime(0,0,0,(int)date('m')+1,0,date('Y')); //DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate'.$prefix.'_2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['extended_an'.$prefix])&&($_GET['extended_an'.$prefix]==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	 
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate_decor2));
	
	 
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',9));
	
	
	
	 
	
	

	$filetext9=$as1->ShowData( $result['org_id'],
		 DateFromdmY($pdate1), 
		 DateFromdmY($pdate2),     
		 'an_cash/an_cash_list'.$print_add.'.html',  
		 $decorator, 
		 $extended_an,
		 isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==9)),
		 $au->user_rights->CheckAccess('w',859),
		 'an_ds.php'			 
			 );
	//$filetext9='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	  
	
	$sm->assign('log9',$filetext9);
	
	
	$sm->assign('has_9',  $au->user_rights->CheckAccess('w',858));


	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==9))){
		$log->PutEntry($result['id'],'открыл отчет Расход наличных',NULL,858,NULL, NULL);	
	}



	
	
	
	
	
	








//***************************** ОТЧЕТ ПРИХОД НАЛИЧНЫХ ****************************************************************/

	
	$as1=new AnCashIn;
	$prefix=$as1->prefix;
	
	
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	 
	
		
	if(!isset($_GET['pdate'.$prefix.'_1'])){
	
			$_pdate1=mktime(0,0,0,date('m'),1,date('Y')); //DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
		
	}else $pdate1 = $_GET['pdate'.$prefix.'_1'];
	
	
	
	if(!isset($_GET['pdate'.$prefix.'_2'])){
			
			$_pdate2=mktime(0,0,0,(int)date('m')+1,0,date('Y')); //DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
	}else $pdate2 = $_GET['pdate'.$prefix.'_2'];
	
	
	$decorator->AddEntry(new UriEntry('pdate1',$pdate1));
	$decorator->AddEntry(new UriEntry('pdate2',$pdate2));
	
	
	
	
	if(isset($_GET['extended_an'.$prefix])&&($_GET['extended_an'.$prefix]==1)){
		$extended_an=1;
		$decorator->AddEntry(new UriEntry('extended_an',1));	
			
	}else{
		$extended_an=0;
		$decorator->AddEntry(new UriEntry('extended_an',0));	
	}
	
	 
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate_decor2));
	
	 
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',10));
	
	
	
	 
	
	

	$filetext10=$as1->ShowData( $result['org_id'],
		 DateFromdmY($pdate1), 
		 DateFromdmY($pdate2),     
		 'an_cash_in/an_cash_list'.$print_add.'.html',  
		 $decorator, 
		 $extended_an,
		 isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==10)), 
		 $au->user_rights->CheckAccess('w',902),
		 'an_ds.php'			 
			 );
	//$filetext9='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	  
	
	$sm->assign('log10',$filetext10);
	
	
	$sm->assign('has_10',  $au->user_rights->CheckAccess('w',901));


	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||(($print==1)&&isset($_GET['tab_page'])&&($_GET['tab_page']==10))){
		$log->PutEntry($result['id'],'открыл отчет Приход наличных',NULL,901,NULL, NULL);	
	}



	
	
	
	
		
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	
	
	
	
	
	
	$content=$sm->fetch('an_ds/an_ds_form'.$print_add.'.html');
	
	
	
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