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


require_once('classes/an_kompnot.php');


require_once('classes/supplier_to_user.php');

$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Выполнение заявок');

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
		$sortmode=0;
	}else $sortmode=abs((int)$_POST['sortmode']); 
}else $sortmode=abs((int)$_GET['sortmode']);


if(!isset($_GET['sortmode2'])){
	if(!isset($_POST['sortmode2'])){
		$sortmode2=0;
	}else $sortmode2=abs((int)$_POST['sortmode2']); 
}else $sortmode2=abs((int)$_GET['sortmode2']);



if($print!=0){
	if(!$au->user_rights->CheckAccess('w',355)&&!$au->user_rights->CheckAccess('w',367)){
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

//внесение изменений в права
if(isset($_POST['doInp'])){
	
	$man=new DiscrMan;
	$log=new ActionLog;
	
	foreach($_POST as $k=>$v){
		if(eregi("^do_edit_",$k)&&($v==1)){
			//echo($k);
			//do_edit_w_4_2
			//1st letter - 	right
			//2nd figure - object_id
			//3rd figure - user_id
			eregi("^do_edit_([[:alpha:]])_([[:digit:]]+)_([[:digit:]]+)$",$k,$regs);
			//var_dump($regs);
			if(($regs!==NULL)&&isset($_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]])){
				$state=$_POST['state_'.$regs[1].'_'.$regs[2].'_'.$regs[3]];
				
				//установить проверку, есть ли права на администрирование данного объекта данным пользователем
				if(!$au->user_rights->CheckAccess('x',$regs[2])){
					continue;
				}
				
				
				//public function PutEntry($user_subject_id, $description, $user_object_id=NULL, $object_id=NULL, $user_group_id=NULL)
				if($state==1){
					$man->GrantAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "установил доступ ".$regs[1],$regs[3],$regs[2]);
					
				}else{
					$man->RevokeAccess($regs[3], $regs[1], $regs[2]);
					$pro=$au->GetProfile();
					$log->PutEntry($pro['id'], "удалил доступ ".$regs[1],$regs[3],$regs[2]);
				}
				
				
			}
		}
	}
	
	header("Location:an_kompnot.php");	
	die();
}

if(!$au->user_rights->CheckAccess('w',354)&&!$au->user_rights->CheckAccess('w',366)){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}


 

//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

if($print==0) $smarty->display('top.html');
else $smarty->display('top_print.html');
unset($smarty);

	$_menu_id=61;
	
	if($print==0) include('inc/menu.php');
	
	

	 
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	
	if(!isset($_GET['tab_page'])) $tab_page=1;
	else $tab_page=abs((int)$_GET['tab_page']);
	
	
	//ограничения по к-ту
	$limited_supplier=NULL;
	
	if($au->FltSupplier($result)){  
		 
		
		$_s_to_u=new SupplierToUser;
		$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($result['id'], $result);
		$limited_supplier=$s_to_u['sector_ids'];
	}
	//var_dump($limited_supplier);
	
	
	//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчет Выполнение заявок',NULL,368,NULL,NULL);	
	
	
	
	//вкладка Не выполненные заявки
	
	
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',($au->user_rights->CheckAccess('x',135)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('135'));
	$admin=$dto->Draw('goods_on_stor.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	//Вкладка
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';

	//$decorator->AddEntry(new SqlEntry('p.storage_id',abs((int)$_GET['storage_id']), Sq//объект
	if(isset($_GET['storage_id'])&&((int)($_GET['storage_id'])>0)){
		$decorator->AddEntry(new UriEntry('storage_id',$_GET['storage_id']));
		
		
			$storage_id=array();
			$storage_id[]=abs((int)$_GET['storage_id']);
		
	}else{
		$storage_id=array();
	}
	
	 
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		/*
		if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name']));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'])));
		}else{*/
			 $supplier_name=SecStr($_GET['supplier_name']);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		//}
	}else $supplier_name='';
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
	
	
	
	//поиск по позициям
	$dec2=new DBDecorator;
	
	if(isset($_GET['id2'])&&(strlen($_GET['id2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.id',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['id2']))));
		$dec2->AddEntry(new UriEntry('id2',$_GET['id2']));
	}
	
	if(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('group_id',abs((int)$_GET['group_id'])));
	}
	
	
	if(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('two_group_id',abs((int)$_GET['two_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		//$dec2->AddEntry(new SqlEntry('p.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('three_group_id',abs((int)$_GET['three_group_id'])));
	}
	
	if(isset($_GET['three_group_id'])&&(abs((int)$_GET['three_group_id'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['three_group_id']), SqlEntry::E));
	}elseif(isset($_GET['two_group_id'])&&(abs((int)$_GET['two_group_id'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['two_group_id']), SqlEntry::E));
		
		//найти подгруппы
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec2->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
	}elseif(isset($_GET['group_id'])&&(abs((int)$_GET['group_id'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['group_id']), SqlEntry::E));
		
		
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec2->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_GET['name2'])&&(strlen($_GET['name2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['name2']))));//SqlEntry::LIKE));
		$dec2->AddEntry(new UriEntry('name2',$_GET['name2']));
	}
	
	if(isset($_GET['gost_tu2'])&&(strlen($_GET['gost_tu2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.gost_tu',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['gost_tu2']))));
		$dec2->AddEntry(new UriEntry('gost_tu2',$_GET['gost_tu2']));
	}
	
	
	
	if(isset($_GET['length2'])&&(strlen($_GET['length2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.length',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['length2']))));
		$dec2->AddEntry(new UriEntry('length2',$_GET['length2']));
	}
	
	if(isset($_GET['width2'])&&(strlen($_GET['width2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.width',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['width2']))));
		$dec2->AddEntry(new UriEntry('width2',$_GET['width2']));
	}
	
	if(isset($_GET['height2'])&&(strlen($_GET['height2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.height',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['height2']))));
		$dec2->AddEntry(new UriEntry('height2',$_GET['height2']));
	}
	
	if(isset($_GET['diametr2'])&&(strlen($_GET['diametr2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.diametr',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['diametr2']))));
		$dec2->AddEntry(new UriEntry('diametr2',$_GET['diametr2']));
	}
	
	
	
	
	if(isset($_GET['dimension_id2'])&&(strlen($_GET['dimension_id2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.dimension_id',abs((int)$_GET['dimension_id2']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('dimension_id2',$_GET['dimension_id2']));
	}
	
	
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	
	
	
	
	
	
	
	
	$as=new AnKompnot;
	$filetext=$as->ShowData( $supplier_name,$sector_id, $result['org_id'],$decorator,'an_kompnot/an_kompnot'.$print_add.'.html','an_kompnot.php',$limited_sector, $au->user_rights->CheckAccess('w',355),isset($_GET['doSub'])||isset($_GET['doSub_x'])/*||($print==1)*/,$dec2,true,NULL,NULL,$_extended_limited_sector_pairs, $limited_supplier,$data, $au->user_rights->CheckAccess('w',870));
	
	//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])){
		$log->PutEntry($result['id'],'открыл отчет Невыполненные заявки',NULL,354,NULL, NULL);	
	}
	
	$sm->assign('log',$filetext);
	
	$sm->assign('has_1',  $au->user_rights->CheckAccess('w',354));
	
	
//********************************************************************************************

	//вкладка Выполненные заявки	
	
	
	
	
	
	//Вкладка Выбор по объектам
	
	$decorator=new DBDecorator;
	
	if($print==0) $print_add='';
	else $print_add='_print';
	
	
	
	
	if(!isset($_GET['pdate2_1'])){
			
			//$_pdate1=DateFromdmY(date("d.m.Y"))-60*60*24*30*3;
			//$pdate1=date("d.m.Y", $_pdate1);//"01.01.2006";
			
			
			$pdate1=date("d.m.Y",DateFromdmY(date("d.m.Y"))-60*60*24*30*3);
			$pdate_decor1=$pdate1; 
		
	}else{
		 if(($_GET['pdate2_1']=='-')||($_GET['pdate2_1']=='')){
			 $pdate1=date('d.m.Y',0);
		 }else {
			 $pdate1 = $_GET['pdate2_1'];
			 
		 }
		 $pdate_decor1=$_GET['pdate2_1'];
		 
	}
	
	
	
	
	if(!isset($_GET['pdate2_2'])){
			/*
			$_pdate2=DateFromdmY(date("d.m.Y"))+60*60*24;
			$pdate2=date("d.m.Y", $_pdate2);//"01.01.2006";	
			*/
			
			$pdate2=date("d.m.Y",DateFromdmY(date("d.m.Y"))+60*60*24*30); //'31.12.2030';
			$pdate_decor2=$pdate2;//'-';
		
	}else{
		 if(($_GET['pdate2_2']=='-')||($_GET['pdate2_2']=='')){
			 $pdate2='31.12.2030';
		 }else {
			 $pdate2 = $_GET['pdate2_2'];
			 
		 }
		 $pdate_decor2=$_GET['pdate2_2'];
		 
	}
	
	
	
	$decorator->AddEntry(new UriEntry('pdate2_1',$pdate_decor1));
	$decorator->AddEntry(new UriEntry('pdate2_2',$pdate_decor2));
	
	
	
	
	
	 
	if(isset($_GET['supplier_name_2'])&&(strlen($_GET['supplier_name_2'])>0)){
		
		/*if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name_2']));
			 $decorator->AddEntry(new UriEntry('supplier_name_2',iconv("utf-8","windows-1251",$_GET['supplier_name_2'])));
		}else{*/
			 $supplier_name=SecStr($_GET['supplier_name_2']);
			 $decorator->AddEntry(new UriEntry('supplier_name_2',$_GET['supplier_name_2']));
		//}
	}else $supplier_name='';
	
	
	 
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('sortmode2',$sortmode2));
	
	switch($sortmode2){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::ASC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('p.pdate',SqlOrdEntry::DESC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::DESC));
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('p.status_id',SqlOrdEntry::ASC));
		break;
		
		
		default:
			$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
		break;	
		
	}
	$decorator->AddEntry(new SqlOrdEntry('p.id',SqlOrdEntry::ASC));
	
	
	
	
	//поиск по позициям
	$dec2=new DBDecorator;
	
	if(isset($_GET['id2'])&&(strlen($_GET['id2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.id',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['id2_2']))));
		$dec2->AddEntry(new UriEntry('id2_2',$_GET['id2_2']));
	}
	
	if(isset($_GET['group_id2_'])&&(abs((int)$_GET['group_id2_'])>0)){
		$dec2->AddEntry(new UriEntry('group_id2_',abs((int)$_GET['group_id2_'])));
	}
	
	
	if(isset($_GET['two_group_id2_'])&&(abs((int)$_GET['two_group_id2_'])>0)){
		$dec2->AddEntry(new UriEntry('two_group_id2_',abs((int)$_GET['two_group_id2_'])));
	}
	
	if(isset($_GET['three_group_id2_'])&&(abs((int)$_GET['three_group_id2_'])>0)){
		$dec2->AddEntry(new UriEntry('three_group_id2_',abs((int)$_GET['three_group_id2_'])));
	}
	
	if(isset($_GET['three_group_id2_'])&&(abs((int)$_GET['three_group_id2_'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['three_group_id2_']), SqlEntry::E));
	}elseif(isset($_GET['two_group_id2_'])&&(abs((int)$_GET['two_group_id2_'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['two_group_id2_']), SqlEntry::E));
		
		//найти подгруппы
		//найти подподгруппы
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['two_group_id2_']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
		}
		
		if(count($arg)>0){
			$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec2->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
		
	}elseif(isset($_GET['group_id2_'])&&(abs((int)$_GET['group_id2_'])>0)){
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_L));
		
		$dec2->AddEntry(new SqlEntry('pos.group_id',abs((int)$_GET['group_id2_']), SqlEntry::E));
		
		
		$_pgg=new PosGroupGroup;
		$arr=$_pgg->GetItemsByIdArr(abs((int)$_GET['group_id2_']));
		$arg=array();
		foreach($arr as $k=>$v){
			if(!in_array($v['id'],$arg)) $arg[]=$v['id'];
			$arr2=$_pgg->GetItemsByIdArr($v['id']);
			foreach($arr2 as $kk=>$vv){
				if(!in_array($vv['id'],$arg))  $arg[]=$vv['id'];
			}
		}
		
		if(count($arg)>0){
			$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::AE_OR));
			$dec2->AddEntry(new SqlEntry('pos.group_id', NULL, SqlEntry::IN_VALUES, NULL,$arg));	
		}
		
		$dec2->AddEntry(new SqlEntry(NULL,NULL, SqlEntry::SKOBKA_R));
	}
	
	
	if(isset($_GET['name2_2'])&&(strlen($_GET['name2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.name',NULL, SqlEntry::LIKE_SET, NULL, explode(';',SecStr($_GET['name2_2']))));
		$dec2->AddEntry(new UriEntry('name2_2',$_GET['name2_2']));
	}
	
	if(isset($_GET['gost_tu2_2'])&&(strlen($_GET['gost_tu2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.gost_tu',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['gost_tu2_2']))));
		$dec2->AddEntry(new UriEntry('gost_tu2_2',$_GET['gost_tu2_2']));
	}
	
	
	
	if(isset($_GET['length2_2'])&&(strlen($_GET['length2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.length',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['length2_2']))));
		$dec2->AddEntry(new UriEntry('length2_2',$_GET['length2_2']));
	}
	
	if(isset($_GET['width2_2'])&&(strlen($_GET['width2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.width',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['width2_2']))));
		$dec2->AddEntry(new UriEntry('width2_2',$_GET['width2_2']));
	}
	
	if(isset($_GET['height2_2'])&&(strlen($_GET['height2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.height',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['height2_2']))));
		$dec2->AddEntry(new UriEntry('height2_2',$_GET['height2_2']));
	}
	
	if(isset($_GET['diametr2_2'])&&(strlen($_GET['diametr2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.diametr',NULL, SqlEntry::LIKE_SET,NULL,explode(';',SecStr($_GET['diametr2_2']))));
		$dec2->AddEntry(new UriEntry('diametr2_2',$_GET['diametr2_2']));
	}
	
	
	
	
	if(isset($_GET['dimension_id2_2'])&&(strlen($_GET['dimension_id2_2'])>0)){
		$dec2->AddEntry(new SqlEntry('pos.dimension_id',abs((int)$_GET['dimension_id2_2']), SqlEntry::E));
		$dec2->AddEntry(new UriEntry('dimension_id2_2',$_GET['dimension_id2_2']));
	}
	
	
	
	
	
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',$tab_page));
	
	
	
	
	
	
	
	
	
	$as=new AnKompnot;
	$filetext=$as->ShowData( $supplier_name,$sector_id, $result['org_id'],$decorator,'an_kompnot/an_kompnot_ful'.$print_add.'.html','an_kompnot.php',$limited_sector, $au->user_rights->CheckAccess('w',367),isset($_GET['doSub2'])||isset($_GET['doSub2_x'])/*||($print==1)*/,$dec2,false,DateFromDmy($pdate1), DateFromDmy($pdate2),$_extended_limited_sector_pairs,  $limited_supplier, $data, $au->user_rights->CheckAccess('w',871));
//	$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
	$sm->assign('log2',$filetext);
	
	
	$sm->assign('has_2',  $au->user_rights->CheckAccess('w',366));
	
	
	//фиксировать открытие отчета
	if(isset($_GET['doSub2'])||isset($_GET['doSub2_x'])){
		$log->PutEntry($result['id'],'открыл отчет Выполненные заявки',NULL,366,NULL, NULL);	
	}
	
	
	
	
	//общие поля
	
	$sm->assign('pdate', date("d.m.Y H:i:s"));
	//$username=$result['login'];
	$username=stripslashes($result['name_s']).' '.$username;	$sm->assign('username',$username);
	
	$sm->assign('tab_page',$tab_page);
	
	$content=$sm->fetch('an_kompnot/an_kompnot_form'.$print_add.'.html');
	
	
	
	
	
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