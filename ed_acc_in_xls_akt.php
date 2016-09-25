<?
$docname='Акт № '.$editing_user['given_no'];	

	$objPHPExcel->getDefaultStyle()->getFont()
				->setName('Arial')
				->setSize(10);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(3.5);
$objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(3.5);


				
$working_row=1;

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Акт № ".$editing_user['given_no'].' от '.date('d',$editing_user['given_pdate_unf']).' '.$m.' '.date('Y',$editing_user['given_pdate_unf']).' '));
			
			
			 $objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':AG'.($working_row));

$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
						),
					),
					'font' => array(
		'bold' => true,
		'size'=>14
	)
				);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':AG'.($working_row))->applyFromArray($styleArray);
				 $objPHPExcel->setActiveSheetIndex(0)->getRowDimension(($working_row))->setRowHeight(35);	
	 
			 $objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

$working_row++;
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Исполнитель: "))
			
			 ->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$opf['name'].' '.$supplier['full_name']))
			
			;
			
			$styleArray = array(
	'font' => array(
		'bold' => true
	)
);

$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row)->applyFromArray($styleArray);

			
			
			 $objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':E'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('F'.($working_row).':AG'.($working_row));
			
$working_row++;
$working_row++;

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Заказчик: "))
			
			 ->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$org_opf['name'].' '.$orgitem['full_name']))
			
			;
			
				
			$styleArray = array(
	'font' => array(
		'bold' => true
	)
);

$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row)->applyFromArray($styleArray);

			
			
$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':E'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('F'.($working_row).':AG'.($working_row));
			
			
			
			
$working_row++;
$working_row++;

//позиции head
$tab_begin=$working_row;

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','№'))
			->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','Наименование работ, услуг																
																
'))
			->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8','Кол-во		
		
'))
			->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8','Ед.	
	
'))
			->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8','Цена			
			
'))
			->setCellValue('AD'.$working_row, iconv('windows-1251', 'utf-8','Сумма			
			
'))
			
			
			;
			
			$styleArray = array(
	'font' => array(
		'bold' => true
	)
);

$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':AD'.$working_row)->applyFromArray($styleArray);
			
			$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':T'.($working_row));
			$objPHPExcel->getActiveSheet()->mergeCells('U'.($working_row).':W'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row).':Y'.($working_row));
			$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row).':AC'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('AD'.($working_row).':AG'.($working_row));
			
			
				
//позиции
foreach($bpg as $k=>$v){
	$working_row++;
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$k+1))
			->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$v['position_name']))
			->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8',$v['quantity']))
			->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8',$v['dim_name']))
			->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8',$v['price_pm']))
			->setCellValue('AD'.$working_row, iconv('windows-1251', 'utf-8',$v['total']))
			 
			
			;
			
			$styleArray = array(
	'font' => array(
		'size' => 8
	)
);

$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':AD'.$working_row)->applyFromArray($styleArray);
			
			$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':T'.($working_row));
			$objPHPExcel->getActiveSheet()->mergeCells('U'.($working_row).':W'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row).':Y'.($working_row));
			$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row).':AC'.($working_row));
			 $objPHPExcel->getActiveSheet()->mergeCells('AD'.($working_row).':AG'.($working_row));
			 
			 $objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':T'.($working_row))->getAlignment()->setWrapText(true);
	 $objPHPExcel->setActiveSheetIndex(0)->getRowDimension(($working_row))->setRowHeight(35);
}

$working_row++;
$working_row++;
//podval
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8','Итого:
'))
			->setCellValue('AD'.$working_row, iconv('windows-1251', 'utf-8',number_format($totals_by_all,2,',','')))
			 
			;
			
			$objPHPExcel->getActiveSheet()->mergeCells('AD'.($working_row).':AG'.($working_row));
			
			 $objPHPExcel->setActiveSheetIndex(0)->getStyle('AC'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			 
			  $objPHPExcel->setActiveSheetIndex(0)->getStyle('AD'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8','В том числе НДС
:
'))
			->setCellValue('AD'.$working_row, iconv('windows-1251', 'utf-8',number_format($nds_sums_by_all,2,',','')))
			 
			;
			
			 
		$styleArray = array(
	'font' => array(
		'bold' => true 
	)
);

$objPHPExcel->getActiveSheet()->getStyle('AC'.($working_row-2).':AD'.$working_row)->applyFromArray($styleArray);
			
			
			
			 $objPHPExcel->setActiveSheetIndex(0)->getStyle('AC'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			 
			 $objPHPExcel->setActiveSheetIndex(0)->getStyle('AD'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			
			
			$objPHPExcel->getActiveSheet()->mergeCells('AD'.($working_row).':AG'.($working_row));


$styleArray = array(
					 
					'borders' => array(
						'allborders'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$tab_begin.':AG'.($working_row-3))->applyFromArray($styleArray);


$working_row++;
$working_row++;	


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Всего оказано услуг '.count($bpg).', на сумму '.number_format($totals_by_all,2,',',' ').' руб.																															

'))	

;	
	$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':AG'.($working_row));

$working_row++;	

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$summa_propis.' '.sprintf("%02d", 100*((float)$total_cost-floor($total_cost)) ).' копеек'))	

;
	$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':AG'.($working_row));
	
		$styleArray = array(
	'font' => array(
		'bold' => true 
	)
);

$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row)->applyFromArray($styleArray);
	
	
$working_row++;	
$working_row++;	


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Вышеперечисленные услуги выполнены полностью и в срок. Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.																															
																															
																															

'))	

;

 $objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			
	$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':AG'.($working_row));
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':AG'.($working_row))->getAlignment()->setWrapText(true);
	 $objPHPExcel->setActiveSheetIndex(0)->getRowDimension(($working_row))->setRowHeight(35);

$working_row++;	



$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
						),
					) 
				);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':AG'.($working_row))->applyFromArray($styleArray);

$working_row++;	
$working_row++;	
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Исполнитель

'))	
->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8','Заказчик

'))	

;

 $objPHPExcel->setActiveSheetIndex(0)->getStyle('E'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
		
		$styleArray = array(
	'font' => array(
		'bold' => true 
	)
);

$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row)->applyFromArray($styleArray);
$objPHPExcel->getActiveSheet()->getStyle('R'.$working_row)->applyFromArray($styleArray);


$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					) 
				);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row.':P'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('U'.$working_row.':AF'.($working_row))->applyFromArray($styleArray);

?>