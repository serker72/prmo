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

require_once('classes/PHPExcel.php');

require_once('classes/an_kompnot.php');

require_once('classes/posdimitem.php');
require_once('classes/posgroupitem.php');


require_once('classes/supplier_to_user.php');


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



 
	if(!$au->user_rights->CheckAccess('w',870)&&!$au->user_rights->CheckAccess('w',871)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
 


if(!isset($_GET['tab_page'])){
	if(!isset($_POST['tab_page'])){
		$tab_page=1;
	}else $tab_page=abs((int)$_POST['tab_page']); 
}else $tab_page=abs((int)$_GET['tab_page']);





$log=new ActionLog;
 
 
 
	
	

	 
	
	
	//демонстрация страницы
//	$smarty = new SmartyAdm;
	
	
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
	
	
	
	
	//вкладка Не выполненные заявки
	
	if(isset($_GET['doSub'])||isset($_GET['doSub_x'])){
	
 
		 
		
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
		$filetext=$as->ShowData( $supplier_name,$sector_id, $result['org_id'],$decorator,'an_kompnot/an_kompnot'.$print_add.'.html','an_kompnot.php',$limited_sector, $au->user_rights->CheckAccess('w',355),isset($_GET['doSub'])||isset($_GET['doSub_x'])/*||($print==1)*/,$dec2,true,NULL,NULL,$_extended_limited_sector_pairs, $limited_supplier, $data);
		
		//$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
		
		//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])/*||($print==1)*/){
		$log->PutEntry($result['id'],'открыл отчет Выполнение заявок: невыполненные заявки: Excel-версия',NULL,354,NULL, NULL);	
	}
	
	}
 
	
	
//********************************************************************************************

	//вкладка Выполненные заявки	
	
	elseif(isset($_GET['doSub2'])||isset($_GET['doSub2_x'])){
	
	
		
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
		
		
		
		
		
		
		//echo 'zzzzzzz';
		
		
		$as=new AnKompnot;
		$filetext=$as->ShowData( $supplier_name,$sector_id, $result['org_id'],$decorator,'an_kompnot/an_kompnot_ful'.$print_add.'.html','an_kompnot.php',$limited_sector, $au->user_rights->CheckAccess('w',367),isset($_GET['doSub2'])||isset($_GET['doSub2_x'])/*||($print==1)*/,$dec2,false,DateFromDmy($pdate1), DateFromDmy($pdate2),$_extended_limited_sector_pairs,  $limited_supplier, $data);
	//	$filetext='<em>Извините, у Вас нет прав для доступа в этот раздел.</em>';
	
		if( isset($_GET['doSub2'])||isset($_GET['doSub2_x'])/*||($print==1)*/){
		$log->PutEntry($result['id'],'открыл отчет Выполнение заявок: выполненные заявки: Excel-версия',NULL,366,NULL, NULL);	
	}
	
	}


	
	
//****************************** РАБОТАЕМ С ЭКСЕЛЬ_файлом *******************************************************/

	// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setLastModifiedBy(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setTitle(iconv('windows-1251', 'utf-8',"Экспорт отчета Выполнение заявок"))
							 ->setSubject(iconv('windows-1251', 'utf-8',"Экспорт отчета Выполнение заявок Office 2007 XLSX Test Document"))
							 ->setDescription(iconv('windows-1251', 'utf-8',"Экспорт отчета Выполнение заявок Office 2007 XLSX, автоматически создан программой ".SITETITLE."."))
							 ->setKeywords("")
							 ->setCategory(iconv('windows-1251', 'utf-8',"Отчет Выполнение заявок"));




$objPHPExcel->getDefaultStyle()->getFont()
    ->setName('Arial')
    ->setSize(8);
	
$begin_row=1;		
$working_row=$begin_row;
$working_col='A';	



	// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle(iconv('windows-1251', 'utf-8',"Отчет Выполнение заявок"));

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);



//разобрать входные данные...

//заголовок отчета
$title=''; $prefix=''; $grp_prefix='';
if(isset($_GET['doSub'])||isset($_GET['doSub_x'])){
	$title='Невыполненные заявки';
}elseif(isset($_GET['doSub2'])||isset($_GET['doSub2_x'])){
	$title='Выполненные заявки';
	$prefix='_2';
	$grp_prefix='2_';
}

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$title));

$working_row++;
			
//покупатель, период - для второго отчета
$fields=$decorator->GetUris();
foreach($fields as $k=>$v){
	
 
	if($v->GetName()=='supplier_name'.$prefix) {
		$working_col='B';	
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Покупатель:'));	
			$styleArray = array(
		'font' => array(
			'bold' => true,
			'size'=>9
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row)->applyFromArray($styleArray);
		$working_col++;
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$v->GetValue()));	
			
		$working_row++;		
	}
	
	
	
	if($prefix=='_2'){
		
		
		if($v->GetName()=='pdate2_1') {
			$working_col='B';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Период:'));
			$styleArray = array(
		'font' => array(
			'bold' => true,
			'size'=>9
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row)->applyFromArray($styleArray);	
					
			$working_col++;	
			
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','с:'));	
			$styleArray = array(
		'font' => array(
			'bold' => true,
			'size'=>9
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row)->applyFromArray($styleArray);
			
			 $working_col++;
			
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$v->GetValue()));	
				
			$working_row++;	
			 
		}
		
		
		
		
		if($v->GetName()=='pdate2_2') {
			$working_col='C';
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','по:'));	
			
			$styleArray = array(
		'font' => array(
			'bold' => true,
			'size'=>9
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row)->applyFromArray($styleArray);
			
			 $working_col++;
			
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$v->GetValue()));	
			 
			$working_row++;	
		}
		
		
		
	}
	
	$working_col++; 
	
}			


//фильтры по названию и прочим св-вам товара
$by_good=array();

$fields=$dec2->GetUris();
foreach($fields as $k=>$v){
	//echo 'zzzzzzzzzz';
	if($v->GetName()=='id2'.$prefix) {
		$by_good[]=array('name'=>'Код позиции', 'value'=>$v->GetValue());
	}
	
	if($v->GetName()=='name2'.$prefix) {
		$by_good[]=array('name'=>'Наименование', 'value'=>$v->GetValue());
		 
	}
	
	if($v->GetName()=='gost_tu2'.$prefix) {
		$by_good[]=array('name'=>'ГОСТ/ТУ', 'value'=>$v->GetValue());
	}
	
	if($v->GetName()=='length2'.$prefix) {
		$by_good[]=array('name'=>'Длина, мм', 'value'=>$v->GetValue());
	}
	
	if($v->GetName()=='width2'.$prefix) {
		$by_good[]=array('name'=>'Ширина, мм', 'value'=>$v->GetValue());
	}
	
	if($v->GetName()=='height2'.$prefix) {
		$by_good[]=array('name'=>'Высота/ толщина, мм', 'value'=>$v->GetValue());
	}
	
	if($v->GetName()=='diametr2'.$prefix) {
		$by_good[]=array('name'=>'Диаметр, мм', 'value'=>$v->GetValue());
	}
	
	if($v->GetName()=='dimension_id2'.$prefix) {
		//$by_good[]=array('name'=>'Диаметр, мм', 'value'=>$v->GetValue());
		$_pdi=new PosDimItem;
		$name=$_pdi->GetItemById($v->GetValue());
		$by_good[]=array('name'=>'ед.изм.', 'value'=>$name['name']);
	}
	
	if(($v->GetName()=='group_id'.$grp_prefix)||($v->GetName()=='two_group_id'.$grp_prefix)||($v->GetName()=='three_group_id'.$grp_prefix)) {
		//$by_good[]=array('name'=>'Диаметр, мм', 'value'=>$v->GetValue());
		 if($v->GetName()=='group_id'.$grp_prefix) $nm='Товарная группа';
		 elseif($v->GetName()=='two_group_id'.$grp_prefix) $nm='подгруппа 1 ур.';
		 elseif($v->GetName()=='three_group_id'.$grp_prefix) $nm='подгруппа 2 ур.';
		 
		 $_pdi=new PosGroupItem;
		$name=$_pdi->GetItemById($v->GetValue());
		 
		$by_good[]=array('name'=> $nm, 'value'=>$name['name']);
		
	}
	

}




foreach($by_good as $k=>$v){
	//$working_col='B';
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',$v['name'].':'));	
		
	$styleArray = array(
		'font' => array(
			'bold' => true,
			'size'=>9
		)
	);
	$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row)->applyFromArray($styleArray);
		
		$working_col++;	
		
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$v['value']));	
		$working_row++;
}



$working_col='A';
$working_row++; $working_row++;

//заголовок таблицы

$header_row_no=$working_row;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','№'));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Дата создания'));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Заданный номер '));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Контрагент'));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Дата исполнения (план.) '));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Текущий статус заявки'));	

$working_col++;		


		


if($prefix=='_2') $pos_name='Завезенные позиции'; else $pos_name='Незавезенные позиции';
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$pos_name));	

$working_col++;	

if($prefix=='_2') $upto=9;
else $upto=11;
for($i=1; $i<=$upto; $i++) $working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Создал '));	

$working_col++;	


//2 ряд заголовка

$working_row++;

/*№ п/п 	Наименование 	ед. изм. 	Перв. по заявке 	Всего по заявке 	Во вх. счетах 	Получе- но 	Доступ- но к закупке 	В исх. счетах 	Отгруже- но 	Доступ- но к продаже 	Итоговый рез-т */
$working_col='G';

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','№ п/п '));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Наименование '));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','ед. изм. '));	

$working_col++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Перв. по заявке '));	

$working_col++;	


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Всего по заявке  '));	

$working_col++;	


if($prefix==''){
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Во вх. счетах '));	
	
	$working_col++;	
}

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Получено  '));	

$working_col++;	


if($prefix=='_2'){
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Дата исполнения (факт.) '));	
	
	$working_col++;	
}

if($prefix=='_2'){
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',' Поставщик '));	
	
	$working_col++;	
}

if($prefix==''){
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Доступно к закупке '));	
	
	$working_col++;	
}

if($prefix==''){
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','В исх. счетах '));	
	
	$working_col++;	
}


	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Отгружено  '));	
	
	$working_col++;	

if($prefix==''){
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Доступно к продаже '));	
	
	$working_col++;	
}

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','Итоговый рез-т '));	

$working_col++;	

$working_row++;

//данные отчета!!!!!!!!!!!!!!!
$table_row_count=2;
foreach($data as $k=>$dat){
	$working_col='A';	
	
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$dat['id']));	
	$working_col++;
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$dat['pdate']));	
	$working_col++;
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$dat['code']));	
	$working_col++;
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$dat['supplier_name'].' '.$dat['opf_name']));	
	$working_col++;
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8','с '.$dat['begin_pdate'].' по '.$dat['end_pdate']));	
	$working_col++;
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$dat['status_name']));	
	$working_col++;
	
	
	
	
	
	$merging_begin=$working_row;
	$merging_end=count($dat['positions']);
	$begin_merging_col=$working_col;
	
	
	//перебираем позиции
	foreach($dat['positions'] as $kk=>$pos){
		$working_merging_col=$begin_merging_col;
			
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$kk+1));	
		$working_merging_col++;
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['position_name']));	
		$working_merging_col++;
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['dim_name']));	
		$working_merging_col++;
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['quantity_initial']));	
		$working_merging_col++;
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['quantity_confirmed']));	
		$working_merging_col++;
		
		if($prefix==''){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['in_bills_in']));	
			$working_merging_col++;
		}
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['in_pol_in']));	
		$working_merging_col++;
		
		
		if($prefix=='_2'){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['given_pdate']));	
			$working_merging_col++;
		}
		
		if($prefix=='_2'){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['supplier_name'].', '.$pos['opf_name']));	
			$working_merging_col++;
		}
		
		if($prefix==''){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',round($pos['quantity_confirmed']-$pos['in_bills_in']-$pos['in_pol_in'],3)));	
			$working_merging_col++;
		}
		
		if($prefix==''){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['in_bills']));	
			$working_merging_col++;
		}
		
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['in_pol']));	
		$working_merging_col++;
		
		
		if($prefix==''){
			$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',round($pos['quantity_confirmed']-$pos['in_bills']-$pos['in_pol'],3)));	
			$working_merging_col++;
		}
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_merging_col.$working_row, iconv('windows-1251', 'utf-8',$pos['itog']));	
		$working_merging_col++;
		
		
		$working_col=$working_merging_col;
		$working_row++;
		$table_row_count++;
	}
	
	//echo 'A'.$merging_begin.':A'.($merging_begin+$merging_end)
	
	if($merging_end>1){ 
		$objPHPExcel->getActiveSheet()->mergeCells('A'.$merging_begin.':A'.($merging_begin+$merging_end-1));
		$objPHPExcel->getActiveSheet()->mergeCells('B'.$merging_begin.':B'.($merging_begin+$merging_end-1));
		$objPHPExcel->getActiveSheet()->mergeCells('C'.$merging_begin.':C'.($merging_begin+$merging_end-1));
		$objPHPExcel->getActiveSheet()->mergeCells('D'.$merging_begin.':D'.($merging_begin+$merging_end-1));
		$objPHPExcel->getActiveSheet()->mergeCells('E'.$merging_begin.':E'.($merging_begin+$merging_end-1));
		$objPHPExcel->getActiveSheet()->mergeCells('F'.$merging_begin.':F'.($merging_begin+$merging_end-1));
		$objPHPExcel->getActiveSheet()->mergeCells($working_col.$merging_begin.':'.$working_col.($merging_begin+$merging_end-1));		
		
	}
	
	
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$merging_begin, iconv('windows-1251', 'utf-8',$dat['manager_name'].' ('.$dat['manager_login'].')'));	
			
			
	$working_col++;
	
	
	
	
	
	
	
	if(count($dat['positions'])==0){ $working_row++; $table_row_count++; }
}



//форматирование отчета
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(9);

if($prefix==''){
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(3);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(24);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(5);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(7);	
	
	$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(12);
	$table_last_col='S';  $table_sublast_col='R';	
	
}else{
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(3);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(24);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(5);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(10);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(7);	
	$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(7);	
 
	$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
	$table_last_col='Q';  $table_sublast_col='P';		
}




//сетка
$styleArray = array(
	 
	'borders' => array(
		'allborders'=> array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	)
);
$objPHPExcel->getActiveSheet()->getStyle('A'.$header_row_no.':'.$table_last_col.($header_row_no+$table_row_count-1))->applyFromArray($styleArray);


//жирный шрифт в шапке таблицы
 $styleArray = array(
	'font' => array(
		'bold' => true 
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.$header_row_no.':'.$table_last_col.($header_row_no+1))->applyFromArray($styleArray);


//меньший шрифт во внутренних колонках
$styleArray = array(
	'font' => array(
		 
		'size'=>7
	)
);

$objPHPExcel->getActiveSheet()->getStyle('G'.($header_row_no+1).':'.$table_sublast_col.($header_row_no+$table_row_count-1))->applyFromArray($styleArray);

//жирный шрифт во внутренних колонках
 $styleArray = array(
	'font' => array(
		'size'=>8,
		'bold' => true 
	)
);
 
if($prefix==''){
	$objPHPExcel->getActiveSheet()->getStyle('N'.($header_row_no+2).':N'.($header_row_no+$table_row_count-1))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('Q'.($header_row_no+2).':Q'.($header_row_no+$table_row_count-1))->applyFromArray($styleArray);
}else{
	$objPHPExcel->getActiveSheet()->getStyle('L'.($header_row_no+2).':L'.($header_row_no+$table_row_count-1))->applyFromArray($styleArray);
	$objPHPExcel->getActiveSheet()->getStyle('O'.($header_row_no+2).':O'.($header_row_no+$table_row_count-1))->applyFromArray($styleArray);
}
 
//разместить по ширине
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$header_row_no.':'.$table_last_col.($header_row_no+$table_row_count-1))->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$header_row_no.':'.$table_last_col.($header_row_no+$table_row_count-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

//объединить в шапке
$objPHPExcel->getActiveSheet()->mergeCells('G'.$header_row_no.':'.$table_sublast_col.$header_row_no);


$objPHPExcel->getActiveSheet()->mergeCells('A'.$header_row_no.':A'.($header_row_no+1));
$objPHPExcel->getActiveSheet()->mergeCells('B'.$header_row_no.':B'.($header_row_no+1));
$objPHPExcel->getActiveSheet()->mergeCells('C'.$header_row_no.':C'.($header_row_no+1));
$objPHPExcel->getActiveSheet()->mergeCells('D'.$header_row_no.':D'.($header_row_no+1));
$objPHPExcel->getActiveSheet()->mergeCells('E'.$header_row_no.':E'.($header_row_no+1));
$objPHPExcel->getActiveSheet()->mergeCells('F'.$header_row_no.':F'.($header_row_no+1));

$objPHPExcel->getActiveSheet()->mergeCells($table_last_col.$header_row_no.':'.$table_last_col.($header_row_no+1));
 

$styleArray = array(
	'font' => array(
		'bold' => true,
		'size'=>13
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray); 
 
 
 
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Отчет Выполнение заявок.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');
exit(); 
		 
	
	 
?>