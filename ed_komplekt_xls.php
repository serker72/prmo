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
require_once('classes/discr_table_group.php');
require_once('classes/discr_table_objects.php');
require_once('classes/discr_table_user.php');
require_once('classes/actionlog.php');

require_once('classes/storageitem.php');
require_once('classes/storagegroup.php');
require_once('classes/sectorgroup.php');
require_once('classes/sectoritem.php');
require_once('classes/posgroupgroup.php');
require_once('classes/positem.php');

require_once('classes/storagesector.php');
require_once('classes/komplitem.php');
require_once('classes/komplconfitem.php');
require_once('classes/komplconfgroup.php');
require_once('classes/komplscanconfgroup.php');

require_once('classes/komplnotesgroup.php');
require_once('classes/komplnotesitem.php');
require_once('classes/billgroup.php');

require_once('classes/komplmarkitem.php');
require_once('classes/komplmarkpositem.php');
require_once('classes/komplmarkposgroup.php');
require_once('classes/komplmarkgroup.php');
require_once('classes/period_checker.php');
require_once('classes/pergroup.php');

require_once('classes/messageitem.php');
require_once('classes/user_s_group.php');
require_once('classes/orgitem.php');
require_once('classes/docstatusitem.php');

require_once('classes/PHPExcel.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Заявка');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$_position=new PosItem;
$ui=new KomplItem;
$_storage=new StorageItem;
$_storages=new StorageGroup;
$_sectors=new SectorGroup;
//$lc=new LoginCreator;
$log=new ActionLog;
$ssgr=new StorageSector;
$_posgroupgroup=new PosGroupGroup;
$_scanconf=new KomplScanConf;

$_pch=new PeriodChecker;
$pch_date=$_pch->GetDate();
$_pch_pergroup=new PerGroup;
$pch_periods=$_pch_pergroup->GetItemsByIdArr($result['org_id'],0,1);



	if(!isset($_GET['id'])){
		if(!isset($_POST['id'])){
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include('404.php');
			die();
		}else $id=abs((int)$_POST['id']);	
	}else $id=abs((int)$_GET['id']);
	
	//проверка наличия пользователя
	$editing_user=$ui->GetItemById($id);
	if($editing_user===false){
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include('404.php');
		die();
	}
	foreach($editing_user as $k=>$v) $editing_user[$k]=stripslashes($v);
	
	if($au->FltSector($result)){
		//накладываем фильтр по участку
		$_sectors_to_user=new SectorToUser();
		
		$sectors_to_user_ids=$_sectors_to_user->GetSectorIdsArr($result['id']);
		if(!in_array($editing_user['sector_id'],$sectors_to_user_ids)){
			 header("HTTP/1.1 403 Forbidden");
	 		 header("Status: 403 Forbidden");
	  		include("403.php");
	  		die();	
		}
	}
	
	
	$_est=new StorageItem;
	$_esc=new SectorItem;
	
	$editing_user_sector=$_esc->GetItemById($editing_user['sector_id']);
	$editing_user_storage=$_est->GetItemById($editing_user['storage_id']);


	$object_id[]=$ui->rd->FindRId(NULL,NULL,NULL,NULL,$editing_user_sector['s_s'],$editing_user_storage['s_s'],array(282,400));
	
	
$_editable_status_id=array();
$_editable_status_id[]=11;

//echo $object_id;
//die();
$cond=false;
foreach($object_id as $k=>$v){
if($au->user_rights->CheckAccess('r',$v)){
	$cond=$cond||true;
}
}
if(!$cond){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();	
}



//даты
$editing_user['begin_pdate_unf']=$editing_user['begin_pdate'];
$editing_user['end_pdate_unf']=$editing_user['end_pdate'];

$editing_user['begin_pdate']=date("d.m.Y",$editing_user['begin_pdate']);
$editing_user['end_pdate']=date("d.m.Y",$editing_user['end_pdate']);


if($editing_user['pdate']==0) $editing_user['pdate']='-';
else $editing_user['pdate']=date("d.m.Y",$editing_user['pdate']);

//кем создано
require_once('classes/user_s_item.php');
$_cu=new UserSItem();
$cu=$_cu->GetItemById($editing_user['manager_id']);
if($cu!==false){
	$ccu=$cu['name_s'].' ('.$cu['login'].')';
}else $ccu='-';
$editing_user['created_by']=$ccu;
		
		
//статус
$_dsi=new DocStatusItem;
$status=$_dsi->GetItemById($editing_user['status_id']);
$editing_user['status_name']=$status['name']; 		
		

//участки
		$do_limit_sector=$au->FltSector($result);
		if($do_limit_sector){
			$_sectors_to_user=new SectorToUser();
		
	
		}
		
		$sectors=$_sectors->GetItemsArr(0,1);
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		foreach($sectors as $k=>$v){
			if($do_limit_sector&&(!in_array($v['id'],$sectors_to_user_ids))) continue;
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
			if($v['id']==$editing_user['sector_id']) $editing_user['print_sector_name']=$v['name'];
				
		}
		 
		
		//объекты - по участку
		$_sector=new SectorItem();
		$tested_sector=$_sector->GetItemById($editing_user['sector_id']);
		//var_dump($tested_sector);
		$_bd=new StorageSector();
		$arr=$_bd->GetCategsBookArr($editing_user['sector_id'], 1);
		
		$st_ids=array(); $st_names=array();
		$st_ids[]=0; $st_names[]='-выберите-';
		$do_limit_sector=$au->FltSector($result);
		foreach($arr as $k=>$v){
			
			if((($tested_sector['s_s']==1)&&($v['s_s']==1))&&!$au->user_rights->CheckAccess('w',380)) continue;
			$st_ids[]=$v['id'];
			$st_names[]=$v['name'];
			if($v['id']==$editing_user['storage_id']) $editing_user['print_storage_name']=$v['name'];
				
		}
	 











// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setLastModifiedBy(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setTitle(iconv('windows-1251', 'utf-8',"Экспорт заявки"))
							 ->setSubject(iconv('windows-1251', 'utf-8',"Экспорт заявки Office 2007 XLSX Test Document"))
							 ->setDescription(iconv('windows-1251', 'utf-8',"Экспорт заявки Office 2007 XLSX, автоматически создан программой ".SITETITLE."."))
							 ->setKeywords("")
							 ->setCategory(iconv('windows-1251', 'utf-8',"Отчет"));




$objPHPExcel->getDefaultStyle()->getFont()
    ->setName('Arial')
    ->setSize(9);

$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);


	// заголовок
	$_oi=new OrgItem;
	$oi=$_oi->getItembyid($result['org_id']);
	
	
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('I1', iconv('windows-1251', 'utf-8','Утверждаю '))
			->setCellValue('I2', iconv('windows-1251', 'utf-8','Генеральный директор'))
			->setCellValue('M2', iconv('windows-1251', 'utf-8',$oi['chief']));


$styleArray = array(
	'font' => array(
		'bold' => true,
		'size'=>13
	)
);

$objPHPExcel->getActiveSheet()->getStyle('I1:M2')->applyFromArray($styleArray);


$styleArray = array(
	'font' => array(
		'bold' => true,
		'size'=>14
	)
);
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(3)->setRowHeight(5);


$working_row=4;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Заявка № '.$id));
			
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);			
			
$working_row++; 
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Заданный номер:'))		
			->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8','Название:'))
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Дата создания:'))
			->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8','Статус:'));


$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['code']))		
			->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8','Заявка № '.$id))
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['pdate']))
			
			->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['status_name'])); 

$styleArray = array(
	'font' => array(
		'bold' => true
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row.':G'.$working_row)->applyFromArray($styleArray);

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            
           
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',"создана:".$editing_user['created_by']));
$styleArray = array(
	'font' => array(
		'size' =>6
	)
);
$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row)->applyFromArray($styleArray);
			 

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"Объект получения:"))
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',"Участок получения:"));
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['print_storage_name']))
			->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['print_sector_name']));			
$styleArray = array(
	'font' => array(
		'size' =>13,
		'bold'=>true
	)
);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row.':E'.$working_row)->applyFromArray($styleArray);		
		
		
$working_row++;		
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);
$working_row++;		
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"с:"))
			->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',"по:"));
			
$working_row++;				
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"Период:"))
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['begin_pdate']))
			->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['end_pdate']));
$styleArray = array(
	'font' => array(
		'bold'=>true
	)
);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row.':C'.$working_row)->applyFromArray($styleArray);	
			
	
			
$working_row++;	
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);
$working_row++;				
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"Позиции каталога:"));
$styleArray = array(
	'font' => array(
		'bold'=>true
	)
);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);				
			
$working_row++;				
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"№ п/п"))
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Наименование"))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',"ед. изм."))
			->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8',"Первоначально по заявке "))
			->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',"Всего по заявке"))
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',"Во вх. счетах "))
			->setCellValue('J'.$working_row, iconv('windows-1251', 'utf-8',"В расп. на приемку"))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',"Получено"))
			->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',"Доступно"))
			->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',"Объект получения"));			
//объединить ячейки
$objPHPExcel->getActiveSheet()->mergeCells('B'.$working_row.':E'.$working_row);
$objPHPExcel->getActiveSheet()->mergeCells('M'.$working_row.':N'.$working_row);
//разместить по ширине в текстовой колонке
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$working_row.':N'.($working_row))->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$working_row.':N'.($working_row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);			
$styleArray = array(
	'font' => array(
		'bold'=>true,
		'size'=>8
	),
	'borders' => array(
		'allborders'=> array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	)
);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row.':N'.($working_row))->applyFromArray($styleArray);

$working_row++;	
//позиции заявки
$some_positions=$ui->GetPositionsArr($editing_user['id'],true);


$begin_row=$working_row;		
foreach($some_positions as $k=>$v){
	$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',$k+1))
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$v['position_name']))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$v['dim_name']))
			->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8',$v['quantity_initial']))
			->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$v['quantity_confirmed']))
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',$v['in_bills']))
			->setCellValue('J'.$working_row, iconv('windows-1251', 'utf-8',$v['in_sh']))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',$v['in_pol']))
			->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',$v['in_free']))
			->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',$v['storage_name']));	
	
	$styleArray = array(
	'font' => array(
		'size'=>8
	),
	);
	$objPHPExcel->getActiveSheet()->getStyle('M'.$working_row.':N'.$working_row)->applyFromArray($styleArray);
//	$objPHPExcel->getActiveSheet()->getStyle('M'.$working_row)->applyFromArray($styleArray);
	
	//объединить ячейки
	$objPHPExcel->getActiveSheet()->mergeCells('B'.$working_row.':E'.$working_row);
	$objPHPExcel->getActiveSheet()->mergeCells('M'.$working_row.':N'.$working_row);
	
	//разместить по ширине в текстовой колонке
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$working_row)->getAlignment()->setWrapText(true);
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('M'.$working_row)->getAlignment()->setWrapText(true);
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('M'.$working_row.':N'.$working_row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	
	
	$working_row++;		
}

//все границы: тело таблицы
$styleArray = array(
	
	'borders' => array(
		'allborders'=> array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	)
);	
$objPHPExcel->getActiveSheet()->getStyle('A'.$begin_row.':N'.($working_row-1).'')->applyFromArray($styleArray);		

$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);			

//примечания
$rg=new KomplNotesGroup;
$notes=$rg->GetItemsByIdArr($editing_user['id'], 0,0, false,false,false,false,false);
$working_row++;	
					
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Примечания:'));
$styleArray = array(
	'font' => array(
		'bold'=>true,
		'size'=>11
	),
	);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);


$working_row++;	
foreach($notes as $k=>$v){
	$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',$v['pdate']))
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$v['user_name_s'].' ('.$v['user_login'].')'));
			
	
	$working_row++;
	$objPHPExcel->setActiveSheetIndex(0)		
			->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$v['note']));	
	$objPHPExcel->getActiveSheet()->mergeCells('B'.$working_row.':M'.$working_row);
	$styleArray = array(
	'font' => array(
		'size'=>8
	),
	);
	$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row)->applyFromArray($styleArray);
	
	$working_row++;		
}

$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);
$working_row++;	

				
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Утверждение заявки:'));
$styleArray = array(
	'font' => array(
		'bold'=>true,
		'size'=>11
	),
	);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);



$some_confirming=$ui->GetConfirmingArr($editing_user['id'],$result['id'],84,$editing_user,$editing_user_sector['s_s'],$editing_user_storage['s_s']);

$working_row++;	
$begin_row=$working_row;
foreach($some_confirming as $k=>$v){
	$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',$v['name'].' (или лицо, его заменяющее):'));
	$objPHPExcel->getActiveSheet()->mergeCells('A'.$working_row.':E'.$working_row);

	if($v['u_id']!=""){
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$v['position_s'].' '.$v['u_name_s'].''.' '.$v['u_login'].''.' '.$v['pdate'].''));
	}else{
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','не утверждено'));
	}
	$objPHPExcel->getActiveSheet()->mergeCells('F'.$working_row.':N'.$working_row);
	
	//высота ряда
	$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(25);
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$working_row.':N'.$working_row)->getAlignment()->setWrapText(true);
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$working_row.':N'.$working_row)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
	$working_row++;		
}

$styleArray = array(
	'font' => array(
		'bold'=>true
	),
	);
$objPHPExcel->getActiveSheet()->getStyle('A'.$begin_row.':N'.($working_row-1))->applyFromArray($styleArray);



$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Начальник отдела снабжения:'))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','Атмачева Татьяна Николаевна'));
$styleArray = array(
	'font' => array(
		'bold'=>true
	),
	);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row.':N'.($working_row))->applyFromArray($styleArray);

	

$username=$result['login'];
$username=stripslashes($result['name_s']).' '.$username;	
		
		
	 
/*
выводится в колонтитул. раскомментировать при необходимости!
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(5);
$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8','Отчет сформирован '.date("d.m.Y H:i:s").' пользователем '.$username));	
$styleArray = array(
	'font' => array(
		'size'=>8
	),
	);
$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);	*/
	
	
	
	
	
	
	
	
//print orientation^
$objPHPExcel->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$objPHPExcel->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
/*$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
$objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);*/

//print footer
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setOddFooter(iconv('windows-1251', 'utf-8','&L' .'Отчет сформирован '.date("d.m.Y H:i:s").' пользователем '.$username . '&RСтраница &P из &N'));
$objPHPExcel->getActiveSheet()->getHeaderFooter()->setEvenFooter(iconv('windows-1251', 'utf-8','&L' .'Отчет сформирован '.date("d.m.Y H:i:s").' пользователем '.$username. '&RСтраница &P из &N'));
	
	
	
	
	// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle(iconv('windows-1251', 'utf-8',"Заявка № ".$editing_user['id']));


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);



// Redirect output to a clientвЂ™s web browser (Excel2007)
/*header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.iconv('windows-1251', 'utf-8','kompl_'.$editing_user['id']).'.xls"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');*/

/*header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="01simple.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');*/

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Заявка № '.$editing_user['id'].'.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');
die();
	
		
?>