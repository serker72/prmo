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

require_once('classes/an_re.php');


 

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


 
	if(!$au->user_rights->CheckAccess('w',854)){
	  header("HTTP/1.1 403 Forbidden");
	  header("Status: 403 Forbidden");
	  include("403.php");
	  die();	
	}
 


  
 
	
	
	
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
	
	
	if(isset($_GET['supplier_name'])&&(strlen($_GET['supplier_name'])>0)){
		
		/*if(isset($_GET['print'])&&($_GET['print']==1)){
			 $supplier_name=SecStr(iconv("utf-8","windows-1251",$_GET['supplier_name']));
			 $decorator->AddEntry(new UriEntry('supplier_name',iconv("utf-8","windows-1251",$_GET['supplier_name'])));
		}else{*/
			 $supplier_name=SecStr($_GET['supplier_name']);
			 $decorator->AddEntry(new UriEntry('supplier_name',$_GET['supplier_name']));
		//}
	}else $supplier_name='';
	
	$decorator->AddEntry(new SqlEntry('b.org_id',abs((int)$result['org_id']), SqlEntry::E));
	
	

	 
	
	
	$decorator->AddEntry(new UriEntry('print',$_GET['print']));
	$decorator->AddEntry(new UriEntry('tab_page',1));
	
	if($print==0){
		$template='an_re/an_re_list.html';	
	}else{
		
		$template='an_re/an_re_list'.$print_add.'.html';	
		
	}
	
	
	
	
	if(!isset($_GET['sortmode'])){
		if(!isset($_POST['sortmode'])){
			$sortmode=-1;
		}else $sortmode=((int)$_POST['sortmode']); 
	}else $sortmode=((int)$_GET['sortmode']);

	
	
	
	$decorator->AddEntry(new UriEntry('sortmode',$sortmode));
	
	switch($sortmode){
		case 0:
			$decorator->AddEntry(new SqlOrdEntry('name',SqlOrdEntry::DESC));
		break;
		case 1:
			$decorator->AddEntry(new SqlOrdEntry('name',SqlOrdEntry::ASC));
		break;
		
		case 2:
			$decorator->AddEntry(new SqlOrdEntry('dimension',SqlOrdEntry::DESC));
		break;	
		case 3:
			$decorator->AddEntry(new SqlOrdEntry('dimension',SqlOrdEntry::ASC));
		break;
		case 4:
			$decorator->AddEntry(new SqlOrdEntry('out_ap_quantity',SqlOrdEntry::DESC));
		break;	
		case 5:
			$decorator->AddEntry(new SqlOrdEntry('out_ap_quantity',SqlOrdEntry::ASC));
		break;
		case 6:
			$decorator->AddEntry(new SqlOrdEntry('in_price_pm',SqlOrdEntry::DESC)); //составное!
		break;	
		case 7:
			$decorator->AddEntry(new SqlOrdEntry('in_price_pm',SqlOrdEntry::ASC));
		break;
		case 8:
			$decorator->AddEntry(new SqlOrdEntry('in_ap_total',SqlOrdEntry::DESC));
		break;	
		case 9:
			$decorator->AddEntry(new SqlOrdEntry('in_ap_total',SqlOrdEntry::ASC));
		break;
		
		
		case 10:
			$decorator->AddEntry(new SqlOrdEntry('in_supplier_name',SqlOrdEntry::DESC)); //sostavnoe
		break;
		case 11:
			$decorator->AddEntry(new SqlOrdEntry('in_supplier_name',SqlOrdEntry::ASC));
		break;
		
		case 12:
			$decorator->AddEntry(new SqlOrdEntry('in_given_no',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 13:
			$decorator->AddEntry(new SqlOrdEntry('in_given_no',SqlOrdEntry::ASC));
		break;
		
		case 14:
			$decorator->AddEntry(new SqlOrdEntry('in_given_pdate',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 15:
			$decorator->AddEntry(new SqlOrdEntry('in_given_pdate',SqlOrdEntry::ASC));
		break;
		
		case 16:
			$decorator->AddEntry(new SqlOrdEntry('price',SqlOrdEntry::DESC));
		break;	
		case 17:
			$decorator->AddEntry(new SqlOrdEntry('price',SqlOrdEntry::ASC));
		break;
		case 18:
			$decorator->AddEntry(new SqlOrdEntry('price_pm',SqlOrdEntry::DESC));
		break;	
		case 19:
			$decorator->AddEntry(new SqlOrdEntry('price_pm',SqlOrdEntry::ASC));
		break;
		case 20:
			$decorator->AddEntry(new SqlOrdEntry('total',SqlOrdEntry::DESC));
		break;	
		case 21:
			$decorator->AddEntry(new SqlOrdEntry('total',SqlOrdEntry::ASC));
		break;
		
		
		case 22:
			$decorator->AddEntry(new SqlOrdEntry('out_supplier_name',SqlOrdEntry::DESC)); //sostavnoe!!!
		break;
		case 23:
			$decorator->AddEntry(new SqlOrdEntry('out_supplier_name',SqlOrdEntry::ASC));
		break;
		
		case 24:
			$decorator->AddEntry(new SqlOrdEntry('supplier_bill_no',SqlOrdEntry::DESC));
		break;	
		case 25:
			$decorator->AddEntry(new SqlOrdEntry('supplier_bill_no',SqlOrdEntry::ASC));
		break;
		case 26:
			$decorator->AddEntry(new SqlOrdEntry('out_given_no',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 27:
			$decorator->AddEntry(new SqlOrdEntry('out_given_no',SqlOrdEntry::ASC));
		break;
		case 28:
			$decorator->AddEntry(new SqlOrdEntry('out_given_pdate',SqlOrdEntry::DESC)); //sostavnoe
		break;	
		case 29:
			$decorator->AddEntry(new SqlOrdEntry('out_given_pdate',SqlOrdEntry::ASC));
		break;
		case 30:
			$decorator->AddEntry(new SqlOrdEntry('sum_2',SqlOrdEntry::DESC));
		break;	
		case 31:
			$decorator->AddEntry(new SqlOrdEntry('sum_2',SqlOrdEntry::ASC));
		break;
		
		case 32:
			$decorator->AddEntry(new SqlOrdEntry('sum_3',SqlOrdEntry::DESC));
		break;
		case 33:
			$decorator->AddEntry(new SqlOrdEntry('sum_3',SqlOrdEntry::ASC));
		break;
		
		case 34:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::DESC));
		break;	
		case 35:
			//$decorator->AddEntry(new SqlOrdEntry('sector_name',SqlOrdEntry::ASC));
		break;
		case 36:
			//$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::DESC));
		break;	
		case 37:
			//$decorator->AddEntry(new SqlOrdEntry('supplier_name',SqlOrdEntry::ASC));
		break;
		case 38:
			$decorator->AddEntry(new SqlOrdEntry('percent_percent',SqlOrdEntry::DESC));
		break;	
		case 39:
			$decorator->AddEntry(new SqlOrdEntry('percent_percent',SqlOrdEntry::ASC));
		break;
		case 40:
			$decorator->AddEntry(new SqlOrdEntry('cash',SqlOrdEntry::DESC));
		break;	
		case 41:
			$decorator->AddEntry(new SqlOrdEntry('cash',SqlOrdEntry::ASC));
		break;
		
		
		case 42:
			$decorator->AddEntry(new SqlOrdEntry('pm_res',SqlOrdEntry::DESC));
		break;
		case 43:
			$decorator->AddEntry(new SqlOrdEntry('pm_res',SqlOrdEntry::ASC));
		break;
		
		case 44:
			$decorator->AddEntry(new SqlOrdEntry('pm_to_give',SqlOrdEntry::DESC));
		break;	
		case 45:
			$decorator->AddEntry(new SqlOrdEntry('pm_to_give',SqlOrdEntry::ASC));
		break;
		case 46:
			$decorator->AddEntry(new SqlOrdEntry('pm_given',SqlOrdEntry::DESC));
		break;	
		case 47:
			$decorator->AddEntry(new SqlOrdEntry('pm_given',SqlOrdEntry::ASC));
		break;
		case 48:
			$decorator->AddEntry(new SqlOrdEntry('pribyl',SqlOrdEntry::DESC));
		break;	
		case 49:
			$decorator->AddEntry(new SqlOrdEntry('pribyl',SqlOrdEntry::ASC));
		break;
	
		
		
		default:
		//	$decorator->AddEntry(new SqlOrdEntry('name',SqlOrdEntry::ASC));
		break;	
		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	$as=new AnRe;
	$filetext=$as->ShowData(DateFromdmY($pdate1), DateFromdmY($pdate2),$supplier_name, $template,$decorator, 'an_re.php', isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1),  $au->user_rights->CheckAccess('w',854), DEC_SEP, $alls );
	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет РЕ: Excel-версия',NULL,853,NULL, NULL);	
	}
	
	
	// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set document properties
$objPHPExcel->getProperties()->setCreator(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setLastModifiedBy(iconv('windows-1251', 'utf-8',SITETITLE))
							 ->setTitle(iconv('windows-1251', 'utf-8',"Экспорт отчета РЕ"))
							 ->setSubject(iconv('windows-1251', 'utf-8',"Экспорт отчета РЕ Office 2007 XLSX Test Document"))
							 ->setDescription(iconv('windows-1251', 'utf-8',"Экспорт отчета РЕ Office 2007 XLSX, автоматически создан программой ".SITETITLE."."))
							 ->setKeywords("")
							 ->setCategory(iconv('windows-1251', 'utf-8',"Отчет РЕ"));




$objPHPExcel->getDefaultStyle()->getFont()
    ->setName('Arial')
    ->setSize(9);
	
$begin_row=5;		
$working_row=$begin_row;
$working_col='A';

//заголовки таблицы
/* № п/п	Наименование продукции	Ед. Изм.	К-во, ед.изм.	Цена Закупки с НДС, руб.	Сумма закупки с НДС, руб.	Поставщик	№ с/ф 	Дата отгрузки	Цена базовая с НДС, руб.	Цена итоговая с НДС, руб.	Сумма продажи с НДС, руб	№ счета	№ с/ф	Дата отгрузки	Транспорт, руб	Экспедирование, руб	Прочее, руб	Примечание	%	Затраты кеша, руб.	Сумма ЭХМЗ, руб.	К выдаче ЭХМЗ, руб.	Выдано ЭХМЗ, руб.	Прибыль	№ п/п	Дата	Сумма, руб.
*/
$_titles=array(
'№ п/п',	//a
'Наименование продукции', //b
'Ед. Изм.', //c
'К-во, ед.изм.', //d
'Цена Закупки с НДС, руб.', //e
'Сумма закупки с НДС, руб.',  //f
'Поставщик',  //g
'№ с/ф',	//h
'Дата отгрузки',	//i
'Цена базовая с НДС, руб.',	//j
'Цена итоговая с НДС, руб.',	//k
'Сумма продажи с НДС, руб.',	//l
'№ счета',	//m
'№ с/ф',	//n
'Дата отгрузки',	//o
'Транспорт, руб',	//q
'Экспедирование, руб',	//p
'Прочее, руб',	//r
'Примечание',		//s
'%',	//t
'Затраты кеша, руб.', //u
'Сумма +/-, руб.', //v
'К выдаче +/-, руб.', //w
'Выдано +/-, руб.', //x
'Прибыль' //y
);

foreach($_titles as $k=>$v){
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$v));
	$working_col++;		
}


$working_row++; $counter=1;

//вносим данные...
foreach($alls as $k=>$data){
	
	
	//разбивка на строки, т.к. может быть несколько вложенных док-тов
	//ins
	//outs
	$_num_strs=array(1, count($data['ins']), count($data['outs']));
	
	$num_strs=max($_num_strs);
	
	for($i=1; $i<=$num_strs; $i++){
		$working_col='A';		
		
		//№ п/п - 1я строка единица, остальыне по формуле
		if($counter==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, $counter);
		else $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, '='.$working_col.($working_row-1).'+1');
		$working_col++;		
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['name']));
		$working_col++;	
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['dimension']));
		$working_col++;	
		
		
		//единые количественные поля для нескольких документов - пишем только в первой строке из набора!!!
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['out_ap_quantity']));
		$working_col++;			
		
		//разные  поля для нескольких документов - пишем при условии, что они сущ-ют
		if(isset($data['ins'][$i-1]['price_pm'])) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['ins'][$i-1]['price_pm']));
		$working_col++;		
		
		
		//единые количественные поля для нескольких документов - пишем только в первой строке из набора!!!
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['in_ap_total']));
		$working_col++;	
		
		//разные  поля для нескольких документов - пишем при условии, что они сущ-ют
		if(isset($data['ins'][$i-1]['supplier_name'])) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['ins'][$i-1]['opf_name'].' "'.$data['ins'][$i-1]['supplier_name'].'"'));
		$working_col++;	
		
		
		//разные  поля для нескольких документов - пишем при условии, что они сущ-ют
		if(isset($data['ins'][$i-1]['given_no'])) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['ins'][$i-1]['given_no']));
		$working_col++;	
		
		
		//разные  поля для нескольких документов - пишем при условии, что они сущ-ют
		if(isset($data['ins'][$i-1]['given_pdate'])) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['ins'][$i-1]['given_pdate']));
		$working_col++;	
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['price']));
		$working_col++;	
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['price_pm']));
		$working_col++;	
		
		/*if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['total']));
		$working_col++;*/
		//единые количественные поля для нескольких документов - пишем только в первой строке из набора!!!
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['out_ap_total']));
		$working_col++;	
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['supplier_bill_no']));
		$working_col++;
		
		//разные  поля для нескольких документов - пишем при условии, что они сущ-ют
		if(isset($data['outs'][$i-1]['given_no'])) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['outs'][$i-1]['given_no']));
		$working_col++;	
		
		
		//разные  поля для нескольких документов - пишем при условии, что они сущ-ют
		if(isset($data['outs'][$i-1]['given_pdate'])) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['outs'][$i-1]['given_pdate']));
		$working_col++;	
		
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['sum_2']));
		$working_col++;
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['sum_3']));
		$working_col++;
		
		//прочее, примечания
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, '');
		$working_col++;
		
		$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, '');
		$working_col++;
		
		
		//+/-, прибыль
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['percent']['percent']/100));
		$working_col++;
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['cash']));
		$working_col++;
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['pm_res']));
		$working_col++;
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['pm_to_give']));
		$working_col++;
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['pm_given']));
		$working_col++;
		
		
		if($i==1) $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue($working_col.$working_row, iconv('windows-1251', 'utf-8',$data['pribyl']));
		$working_col++;
		
		
		
		$working_row++; $counter++;
	}
		
	
	
}


//форматирование

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(21);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(11);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(7);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(6);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(9);
$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(10);

//сетка
$styleArray = array(
	 
	'borders' => array(
		'allborders'=> array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
		),
	)
);
$objPHPExcel->getActiveSheet()->getStyle('A'.$begin_row.':Y'.($counter+$begin_row-1))->applyFromArray($styleArray);

//разместить по ширине в текстовой колонке
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$begin_row.':Y'.($begin_row))->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$begin_row.':Y'.($begin_row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$begin_row.':B'.($counter+$begin_row-1))->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('G'.$begin_row.':G'.($counter+$begin_row-1))->getAlignment()->setWrapText(true);


//залить ном-лу
$styleArray = array(
	 
	'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
		
		'startcolor' => array(
			'rgb' => '00FFFF',
		),
		
	),


);

$objPHPExcel->getActiveSheet()->getStyle('B'.($begin_row+1).':B'.($counter+$begin_row-1))->applyFromArray($styleArray);


//суммы
$styleArray = array(
	'font' => array(
		'bold'=>true 
		 
	) 
);
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('F'.($begin_row-1), '=SUM(F'.($begin_row+1).':F'.($counter+$begin_row-1).')');
$objPHPExcel->getActiveSheet()->getStyle('F'.($begin_row-1))->applyFromArray($styleArray);			
			
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('L'.($begin_row-1), '=SUM(L'.($begin_row+1).':L'.($counter+$begin_row-1).')');
$objPHPExcel->getActiveSheet()->getStyle('L'.($begin_row-1))->applyFromArray($styleArray);

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('V'.($begin_row-1), '=SUM(V'.($begin_row+1).':V'.($counter+$begin_row-1).')');
$objPHPExcel->getActiveSheet()->getStyle('V'.($begin_row-1))->applyFromArray($styleArray);

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('W'.($begin_row-1), '=SUM(W'.($begin_row+1).':W'.($counter+$begin_row-1).')');
$objPHPExcel->getActiveSheet()->getStyle('W'.($begin_row-1))->applyFromArray($styleArray);

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('Y'.($begin_row-1), '=SUM(Y'.($begin_row+1).':Y'.($counter+$begin_row-1).')');
$objPHPExcel->getActiveSheet()->getStyle('Y'.($begin_row-1))->applyFromArray($styleArray);			



//формат данных 

$objPHPExcel->getActiveSheet()
    ->getStyle('F'.($begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00'); 
	
$objPHPExcel->getActiveSheet()
    ->getStyle('L'.($begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00'); 

$objPHPExcel->getActiveSheet()
    ->getStyle('V'.($begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00'); 


$objPHPExcel->getActiveSheet()
    ->getStyle('W'.($begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00'); 


$objPHPExcel->getActiveSheet()
    ->getStyle('Y'.($begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00'); 


/*$objPHPExcel->getActiveSheet()
    ->getStyle('D'.($begin_row+1).':D'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.000'); */
	
$objPHPExcel->getActiveSheet()
    ->getStyle('E'.($begin_row+1).':E'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('F'.($begin_row+1).':F'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');

$objPHPExcel->getActiveSheet()
    ->getStyle('J'.($begin_row+1).':J'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('K'.($begin_row+1).':K'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('L'.($begin_row+1).':L'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('P'.($begin_row+1).':P'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('Q'.($begin_row+1).':Q'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	

$objPHPExcel->getActiveSheet()
    ->getStyle('U'.($begin_row+1).':U'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('V'.($begin_row+1).':V'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	

$objPHPExcel->getActiveSheet()
    ->getStyle('W'.($begin_row+1).':W'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('X'.($begin_row+1).':X'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('Y'.($begin_row+1).':Y'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00');
	
	$objPHPExcel->getActiveSheet()
    ->getStyle('T'.($begin_row+1).':T'.($counter+$begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.000');
	
$objPHPExcel->getActiveSheet()
    ->getStyle('Y'.($begin_row-1))
    ->getNumberFormat()
    ->setFormatCode('0.00'); 
	

	// Rename worksheet
$objPHPExcel->getActiveSheet()->setTitle(iconv('windows-1251', 'utf-8',"Отчет РЕ"));

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="Отчет РЕ.xls"');
header('Cache-Control: max-age=0');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');


$objWriter->save('php://output');
exit();
	
 
?>