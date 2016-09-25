<?
$docname='Товарная накладная № '.$editing_user['given_no'];	
		$objPHPExcel->getDefaultStyle()->getFont()
				->setName('Arial')
				->setSize(8);			
				
				
				//ширины стб.
				$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1);
 				$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(2);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(11);
				$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(2.5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(1.8);
				$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(1.8);
				$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(1);
				$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(1.5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(1);
				$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(1);
				$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(4);
				$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(1.5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(3.0);
				$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(3.0);
				$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(3.0);
				$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(6);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(1.8);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(3);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(1.8);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(3);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(7);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(0.12);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(1);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(0.3);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(9);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(4.5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(2.8);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(1.8);
				
				$objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(9.5);
				$objPHPExcel->getActiveSheet()->getColumnDimension('AO')->setWidth(1);
				

				
				
				
				
				$working_row=1;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"\"Унифицированная форма № ТОРГ-12"));
			
			$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);
			
				$objPHPExcel->getActiveSheet()->mergeCells('A'.$working_row.':AN'.$working_row);
				
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$working_row.':N'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"
Утверждена постановлением Госкомстата России от 25.12.98 № 132\""));

	$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);

				$objPHPExcel->getActiveSheet()->mergeCells('A'.$working_row.':AN'.$working_row);
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.$working_row.':N'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',"
Коды"));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('AN'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
				
				

				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"Форма по ОКУД 
"))
->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',"0330212"))

->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$opf['name'].' '.$supplier['full_name'].', '.'ИНН '.$supplier['inn'].', '.str_replace("\n", " ", $supplier['legal_address']).', р/с '.$bdetail['rs'].', в банке '.$bdetail['bank'].', БИК '.$bdetail['bik'].', к/с '.$bdetail['ks'])
);
			
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':AE'.($working_row+1));
				 $objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':AE'.($working_row+1))->getAlignment()->setWrapText(true);
$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':AE'.($working_row+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_BOTTOM);
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':AE'.($working_row+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);	
				
				
				//выравнивание кодов
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('AN3:AN19')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('AM3:AM21')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				

				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"по ОКПО"))
			->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',$supplier['okpo'])
			);
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"организация-грузоотправитель, адрес, телефон, факс, банковские реквизиты"));
			
			$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
				
				
			
				$working_row++;
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"структурное подразделение"));
			
			$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray);
			
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
			
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"Вид деятельности по ОКДП
")			
				);
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Грузополучатель"))
			
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$org_opf['name'].' '.$orgitem['full_name'].",  ИНН $orgitem[inn], ".str_replace("\n", " ", $orgitem['legal_address']).", р/с $print_org_bdetail[rs], в банке $print_org_bdetail[bank], БИК $print_org_bdetail[bik], к/с $print_org_bdetail[ks] "))
			  ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"по ОКПО"))
			->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',$orgitem['okpo'])				
				);
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AJ'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.$working_row.':AJ'.($working_row))->getAlignment()->setWrapText(true);
				$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(25);

				
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':C'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"организация, адрес, телефон, факс, банковские реквизиты"));
			
				$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
			
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Адрес доставки"))
			
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$orgitem['legal_address'])
			 			
				);
				
				
					
				
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':C'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				
				$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"адрес доставки
"));
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
					
				$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray); 

$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
				

				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Поставщик"))
			
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$opf['name'].' '.$supplier['full_name'].', '.'ИНН '.$supplier['inn'].', '.str_replace("\n", " ", $supplier['legal_address']).', р/с '.$bdetail['rs'].', в банке '.$bdetail['bank'].', БИК '.$bdetail['bik'].', к/с '.$bdetail['ks']))
			  ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"по ОКПО"))
			->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',$supplier['okpo'])				
				);
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AJ'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.$working_row.':AJ'.($working_row))->getAlignment()->setWrapText(true);
				$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(25);

				
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':C'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"организация, адрес, телефон, факс, банковские реквизиты"));
			
				$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray);
			
			$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Плательщик"))
			
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$org_opf['name'].' '.$orgitem['full_name'].",  ИНН $orgitem[inn], $orgitem[legal_address], р/с $print_org_bdetail[rs], в банке $print_org_bdetail[bank], БИК $print_org_bdetail[bik], к/с $print_org_bdetail[ks] "))
			  ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"по ОКПО"))
			->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',$orgitem['okpo'])				
				);
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AJ'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.$working_row.':AJ'.($working_row))->getAlignment()->setWrapText(true);
				$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(25);
				
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':C'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"организация, адрес, телефон, факс, банковские реквизиты"))
			->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"Номер"))
			->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',"")
			
			);
			
				$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray);
			
			$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
			
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Основание"))
			
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','Договор поставки № '.$sci['contract_no'].' от '. $sci['contract_pdate'])
						
				);
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AJ'.($working_row));
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row).':C'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',"договор, заказ-наряд
"))
			->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"дата"))
			->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',"")
			
				);
				
					$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row)->applyFromArray($styleArray);
				
				$styleArray = array(
					 
					'borders' => array(
						'top'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('D'.$working_row.':AJ'.($working_row))->applyFromArray($styleArray);
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':AE'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('D'.($working_row).':AE'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',"Номер документа"))			
            ->setCellValue('P'.$working_row, iconv('windows-1251', 'utf-8','Дата составления'))
			->setCellValue('AJ'.$working_row, iconv('windows-1251', 'utf-8','Транспортная накладная'))
			->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8','номер')
						
				);
				
				
				
				$objPHPExcel->getActiveSheet()->mergeCells('L'.($working_row).':O'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('L'.($working_row).':O'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$objPHPExcel->getActiveSheet()->mergeCells('P'.($working_row).':U'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('P'.($working_row).':U'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('AJ'.($working_row) )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',"ТОВАРНАЯ НАКЛАДНАЯ"))			
            ->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['given_no']))
			->setCellValue('P'.$working_row, iconv('windows-1251', 'utf-8',$editing_user['given_pdate']))
			->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8','дата')
						
				);
				
				
					$styleArray = array(
	'font' => array(
		'bold' => TRUE,
		'size'=>10
	)
);

$objPHPExcel->getActiveSheet()->getStyle('K'.$working_row)->applyFromArray($styleArray);
				
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('K'.($working_row) )->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
				$objPHPExcel->getActiveSheet()->mergeCells('L'.($working_row).':O'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('L'.($working_row).':O'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				$objPHPExcel->getActiveSheet()->mergeCells('P'.($working_row).':U'.($working_row));
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('P'.($working_row).':U'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	
				
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"Вид операции
"));

				//граница кодов
				$styleArray = array(
					 
					'borders' => array(
						'allborders'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('AN3:AN21')->applyFromArray($styleArray);
				$styleArray = array(
					 
					'borders' => array(
						'outline'=> array(
							'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('AN3:AN21')->applyFromArray($styleArray);
				
				//номер документа
				$styleArray = array(
					 
					'borders' => array(
						'allborders'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('L19:U20')->applyFromArray($styleArray);
				$styleArray = array(
					 
					'borders' => array(
						'outline'=> array(
							'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('L20:U20')->applyFromArray($styleArray);
				
				
			   $working_row++;
			   
			   //цикл по ценам
			   	//позиции для накладной
				$print_positions=array();
				
				$cter=1; $page=1;
				$cols_by_page=0;
				$costs_by_page=0;
				$nds_sums_by_page=0;
				$totals_by_page=0;
				
				$cols_by_all=0;
				$costs_by_all=0;
				$nds_sums_by_all=0;
				$totals_by_all=0;
				
				$_posdi=new PosDimItem;
				
				$header_from=$working_row;
				$header_to=$working_row;
				foreach($bpg as $k=>$v){
					//1 23
					
					$cols_by_page+=$v['quantity'];
					$costs_by_page+=$v['total']-$v['nds_summ'];
					$nds_sums_by_page+=$v['nds_summ'];
					$totals_by_page+=$v['total'];	
					
					
					$cols_by_all+=$v['quantity'];
					$costs_by_all+=$v['total']-$v['nds_summ'];
					$nds_sums_by_all+=$v['nds_summ'];
					$totals_by_all+=$v['total'];
					
					$posdi=$_posdi->GetItemByFields(array('name'=>$v['dim_name']));
					$v['okei']=$posdi['okei'];
					
					//var_dump($posdi);
					$v['price_pm_wo_nds']=number_format($v['price_pm']-$v['nds_price'],2,',','');
					$v['total_wo_nds']=number_format($v['total']-$v['nds_summ'],2,',','');
					$v['nds_summ_f']=number_format($v['nds_summ'],2,',','');
					$v['total_f']=number_format($v['total'],2,',','');
					
					$v['cols_by_page']=$cols_by_page;
						$v['costs_by_page']=$costs_by_page;
						$v['nds_sums_by_page']=$nds_sums_by_page;
						$v['totals_by_page']=$totals_by_page;
					
					$v['costs_by_page_f']=number_format($costs_by_page,2,',','');
						$v['nds_sums_by_page_f']=number_format($nds_sums_by_page,2,',','');
						$v['totals_by_page_f']=number_format($totals_by_page,2,',','');
					
					$v['break_after']=false;
					
					
					
					
					//вывод шапки
					if(($cter==1)||
					($cter==2)||
					
					( ($cter>1)&& ( (($cter-1)%35==0) /* || ($cter>=count($bpg))*/ ))
					)
					{
						
						//
						$header_from=$working_row;
						
						$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue('AN'.$working_row, iconv('windows-1251', 'utf-8',"Страница $page"));
						$working_row++;
						$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"Но-мер по по-рядку"
				))
							->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',"Товар"))
							->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',"Единица измерения				
				"))
							->setCellValue('N'.$working_row, iconv('windows-1251', 'utf-8',"Вид упаковки
				
				"))
							->setCellValue('O'.$working_row, iconv('windows-1251', 'utf-8',"Количество					
				"))
							->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8','Масса брутто'))
							->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8',"Коли-чество (масса нетто)"	
					
				))
							->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8',"Цена, руб. коп."		
						
				))
							
							->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8',"Сумма без учета НДС, руб. коп."				
								
				))
							
							->setCellValue('AH'.$working_row, iconv('windows-1251', 'utf-8',"НДС				
				"))
							
							->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"Сумма с учетом НДС, руб. коп.")
				
							
							);
							
							$working_row++;
							
							$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',"наименование, характеристика, сорт, артикул товара				
"))
							->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',"код
"))
							->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',"наиме- нование			
"))
							->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',"код по ОКЕИ
"))
							->setCellValue('O'.$working_row, iconv('windows-1251', 'utf-8',"в одном месте		
"))
							->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8',"мест,
штук"		
))
							->setCellValue('AH'.$working_row, iconv('windows-1251', 'utf-8',"ставка, %	
"))
							
							->setCellValue('AJ'.$working_row, iconv('windows-1251', 'utf-8',"сумма, 
руб. коп."		
))
							
							;
							
							
				//			$objPHPExcel->getActiveSheet()->mergeCells('L'.($working_row).':O'.($working_row));
							
							
							
							$working_row++;
							$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"1"))
							->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',"2"))
							->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',"3"))
							->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',"4"))
							->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',"5"))
							->setCellValue('N'.$working_row, iconv('windows-1251', 'utf-8',"6"))
							->setCellValue('O'.$working_row, iconv('windows-1251', 'utf-8',"7"))
							->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8',"8"))
							->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8',"9"))
							->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8',"10"))
							->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8',"11"))
							->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8',"12"))
							->setCellValue('AH'.$working_row, iconv('windows-1251', 'utf-8',"13"))
							->setCellValue('AJ'.$working_row, iconv('windows-1251', 'utf-8',"14"))
							->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',"15"));

							
							
							
							
							
							$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(($working_row-2))->setRowHeight(15);	
							$objPHPExcel->setActiveSheetIndex(0)->getRowDimension(($working_row-1))->setRowHeight(35);	
							
							$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row-2).':B'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('C'.($working_row-2).':H'.($working_row-2));
							$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row-2).':M'.($working_row-2));
							$objPHPExcel->getActiveSheet()->mergeCells('O'.($working_row-2).':T'.($working_row-2));
							$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row-2).':AL'.($working_row-2));
							$objPHPExcel->getActiveSheet()->mergeCells('C'.($working_row-1).':G'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('C'.($working_row).':G'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row-1).':L'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row).':L'.($working_row ));
							$objPHPExcel->getActiveSheet()->mergeCells('N'.($working_row-2).':N'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('U'.($working_row-2).':W'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row-2).':Y'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row-2).':AB'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('AC'.($working_row-2).':AG'.($working_row-1));
						$objPHPExcel->getActiveSheet()->mergeCells('AJ'.($working_row-1).':AL'.($working_row-1));
						
						$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row-1).':AI'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('AM'.($working_row-2).':AN'.($working_row-1));
							
							$objPHPExcel->getActiveSheet()->mergeCells('O'.($working_row-1).':Q'.($working_row-1));
							$objPHPExcel->getActiveSheet()->mergeCells('R'.($working_row-1).':T'.($working_row-1));
							
							
							$objPHPExcel->getActiveSheet()->mergeCells('O'.($working_row).':Q'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('R'.($working_row).':T'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('U'.($working_row).':W'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row).':Y'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row).':AB'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('AC'.($working_row).':AG'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row).':AI'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('AJ'.($working_row).':AL'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('AM'.($working_row).':AN'.($working_row));
							
							
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row-2).':AN'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);							
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row-2).':AN'.($working_row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);							
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row-2).':N'.($working_row))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('Z'.($working_row-2).':AB'.($working_row-1))->getAlignment()->setWrapText(true);
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('AC'.($working_row-2).':AG'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('AM'.($working_row-2).':AN'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('O'.($working_row-1).':Q'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('R'.($working_row-1).':T'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('AH'.($working_row-1).':AI'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('AJ'.($working_row-1).':AL'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('U'.($working_row-2).':V'.($working_row-1))->getAlignment()->setWrapText(true);
							
							$objPHPExcel->setActiveSheetIndex(0)->getStyle('X'.($working_row-2).':Y'.($working_row-1))->getAlignment()->setWrapText(true);
						 	
							$working_row++;	
					 
					}
					 
					 
					//вывод данных
					
					
					$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$cter))
							->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8',$v['position_name']))
							->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$v['id']))
							->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',$v['dim_name']))
							->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',$v['okei']))
							->setCellValue('N'.$working_row, iconv('windows-1251', 'utf-8',''))
							->setCellValue('O'.$working_row, iconv('windows-1251', 'utf-8',''))
							->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8',''))
							->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8',''))
							->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8',$v['quantity']))
							->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8',$v['price_pm_wo_nds']))
							->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8',$v['total_wo_nds']))
							->setCellValue('AH'.$working_row, iconv('windows-1251', 'utf-8',$v['nds_proc']))
							->setCellValue('AJ'.$working_row, iconv('windows-1251', 'utf-8',$v['nds_summ_f']))
							->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',$v['total_f']))
							
							
							;
					 $objPHPExcel->getActiveSheet()->mergeCells('C'.($working_row).':G'.($working_row));
					$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row).':L'.($working_row ));
					$objPHPExcel->getActiveSheet()->mergeCells('O'.($working_row).':Q'.($working_row));
					$objPHPExcel->getActiveSheet()->mergeCells('R'.($working_row).':T'.($working_row));
					
					$objPHPExcel->getActiveSheet()->mergeCells('U'.($working_row).':W'.($working_row));
					$objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row).':Y'.($working_row));
					
					$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row).':AB'.($working_row));
					
					$objPHPExcel->getActiveSheet()->mergeCells('AC'.($working_row).':AG'.($working_row));
					
					$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row).':AI'.($working_row));
					$objPHPExcel->getActiveSheet()->mergeCells('AJ'.($working_row).':AL'.($working_row));
					$objPHPExcel->getActiveSheet()->mergeCells('AM'.($working_row).':AN'.($working_row));
					
					
					$objPHPExcel->setActiveSheetIndex(0)->getStyle('C'.$working_row.':G'.($working_row))->getAlignment()->setWrapText(true);
					
					$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(25);
					 
					
					//вывод подвала
					
					if(($cter==1)
					
					||
					
					( ($cter>1)&& ( (($cter-1)%20==0)  || ($cter>=count($bpg)) ))
					
					)
					{
						$working_row++;
						
						
						 	$objPHPExcel->setActiveSheetIndex(0)
            				->setCellValue('Q'.$working_row, iconv('windows-1251', 'utf-8','Итого'))
							->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8',$v['cols_by_page']))
							
							->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8','X'))
							->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8',$v['costs_by_page_f']))
							->setCellValue('AH'.$working_row, iconv('windows-1251', 'utf-8','X'))
							->setCellValue('AJ'.$working_row, iconv('windows-1251', 'utf-8',$v['nds_sums_by_page_f']))
							->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8',$v['totals_by_page_f']))
							
							;
							
								$objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row).':Y'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row).':AB'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('AC'.($working_row).':AG'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row).':AI'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('AJ'.($working_row).':AL'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('AM'.($working_row).':AN'.($working_row));
							
						 
						
						$cols_by_page=0;
						$costs_by_page=0;
						$nds_sums_by_page=0;
						$totals_by_page=0;	
						
						$page++;	
						
						//
						$header_to=$working_row;
	
						$styleArray = array(
							 
							'borders' => array(
								'allborders'=> array(
									'style' => PHPExcel_Style_Border::BORDER_THIN,
								),
							)
						);
						$objPHPExcel->getActiveSheet()->getStyle('B'.($header_from+1).':AN'.($header_to-1))->applyFromArray($styleArray);
						
						$objPHPExcel->getActiveSheet()->getStyle('X'.($header_to).':AN'.($header_to))->applyFromArray($styleArray);
						
						
						//разрыв страницы
						if(!($cter>=count($bpg))){
							$objPHPExcel->getActiveSheet()->setBreak( 'A'.$working_row , PHPExcel_Worksheet::BREAK_ROW );
						}
						 
					}
					
					
					 
					$cter++;	
					
					
					
					$working_row++;
				}
				
				
				//всего по накладной
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('Q'.$working_row, iconv('windows-1251', 'utf-8','Всего по накладной'))
					->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8',$cols_by_all))
					
					->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8','X'))
					->setCellValue('AC'.$working_row, iconv('windows-1251', 'utf-8',number_format($costs_by_all,2,',','')))
					->setCellValue('AH'.$working_row, iconv('windows-1251', 'utf-8','X'))
					->setCellValue('AJ'.$working_row, iconv('windows-1251', 'utf-8',number_format($nds_sums_by_all,2,',','')))
					->setCellValue('AM'.$working_row, iconv('windows-1251', 'utf-8', number_format($totals_by_all,2,'.',' ')))
					
					;
						
					$objPHPExcel->getActiveSheet()->mergeCells('X'.($working_row).':Y'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('Z'.($working_row).':AB'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('AC'.($working_row).':AG'.($working_row));
							
							$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row).':AI'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('AJ'.($working_row).':AL'.($working_row));
							$objPHPExcel->getActiveSheet()->mergeCells('AM'.($working_row).':AN'.($working_row));
							
							$styleArray = array(
							 
							'borders' => array(
								'allborders'=> array(
									'style' => PHPExcel_Style_Border::BORDER_THIN,
								),
							)
						);
						 
						$objPHPExcel->getActiveSheet()->getStyle('X'.($working_row).':AN'.($working_row))->applyFromArray($styleArray);
				if($cter>10){
							$objPHPExcel->getActiveSheet()->setBreak( 'A'.$working_row , PHPExcel_Worksheet::BREAK_ROW );
						}					
						 
				//cколько листов
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Товарная накладная имеет приложение на'))
					
				;
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('K'.$working_row.':V'.($working_row))->applyFromArray($styleArray);
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','и содержит
'))
					->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$_pn->propis(count($bpg))))
					->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8','порядковых номеров записей'))
					
				;
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row.':X'.($working_row))->applyFromArray($styleArray);
				
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','прописью
'));
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','     Масса груза (нетто)'));
					
					
				$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row-1).':AN'.$working_row);	
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('S'.$working_row.':AF'.($working_row))->applyFromArray($styleArray);	
				
				
					
					
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('S'.$working_row, iconv('windows-1251', 'utf-8','прописью
'));	
					
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Всего мест
'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','     Масса груза (брутто)'));
					
				$objPHPExcel->getActiveSheet()->mergeCells('F'.($working_row-2).':I'.$working_row);		
					
					$objPHPExcel->getActiveSheet()->mergeCells('AH'.($working_row-1).':AN'.$working_row);	
					
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('F'.$working_row.':I'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('S'.$working_row.':AF'.($working_row))->applyFromArray($styleArray);	
					
					
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','прописью
'))
					->setCellValue('S'.$working_row, iconv('windows-1251', 'utf-8','прописью
'));		
					
					
			$styleArray = array(
					 
					'borders' => array(
						'allborders'=> array(
							'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('AH'.($working_row-4).':AN'.($working_row-1))->applyFromArray($styleArray);		
					
				
				//блок подписей
				$working_row++;
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Приложение (паспорта, сертификаты и т.п.) на'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','листах'))
					->setCellValue('X'.$working_row, iconv('windows-1251', 'utf-8','По доверенности №'))
					->setCellValue('AD'.$working_row, iconv('windows-1251', 'utf-8','от'))
					
					
					;
					
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('G'.$working_row.':I'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->getActiveSheet()->getStyle('Y'.$working_row.':AC'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('AE'.$working_row.':AN'.($working_row))->applyFromArray($styleArray);	
				
				
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('X'.$working_row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);	
				
					
					
				$working_row++;		
					 
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8','прописью'))
				;
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Всего отпущено  на сумму'))
					->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8','выданной'))
				;
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				 	
				
				$objPHPExcel->getActiveSheet()->getStyle('Y'.$working_row.':AN'.($working_row))->applyFromArray($styleArray);	
				
				$styleArray = array(
	'font' => array(
		'bold' => true,
		'size'=>8
	)
);
$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row)->applyFromArray($styleArray);	
				
				
				$working_row++;
				
				$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':Q'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$summa_propis))
					->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8','кем, кому (организация, должность, фамилия, и. о.)'))
					
					;
					
					
					
				
				$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':Q'.$working_row);		
					
					
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				 	
				
				$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row.':Q'.($working_row))->applyFromArray($styleArray);		
					
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','прописью'))
				;
				
				$working_row++;
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Отпуск разрешил'))
					->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8','Генеральный директор	'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',$supplier['chief']))
				;
				
				$objPHPExcel->getActiveSheet()->mergeCells('E'.($working_row).':F'.$working_row);		
				$objPHPExcel->getActiveSheet()->mergeCells('K'.($working_row).':P'.$working_row);		
				
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				 	
				
				$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row.':I'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->getActiveSheet()->getStyle('K'.$working_row.':P'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('Y'.$working_row.':AN'.($working_row))->applyFromArray($styleArray);	
				
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',' должность
'))
					->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8','подпись
'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','расшифровка подписи
'))
				;
				
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Главный (старший) бухгалтер
'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',$supplier['main_accountant']))
					->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8','Груз принял
'))
					;
					
					
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				 	
				
				$objPHPExcel->getActiveSheet()->getStyle('G'.$working_row.':I'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->getActiveSheet()->getStyle('K'.$working_row.':P'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('Y'.$working_row.':Z'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('AB'.$working_row.':AE'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->getActiveSheet()->getStyle('AI'.$working_row.':AN'.($working_row))->applyFromArray($styleArray);		
				
				$working_row++;	
				$objPHPExcel->setActiveSheetIndex(0)
					
					->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8','подпись
'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','расшифровка подписи
'))
					->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8',' должность
'))
					->setCellValue('AB'.$working_row, iconv('windows-1251', 'utf-8','подпись
'))
					->setCellValue('AI'.$working_row, iconv('windows-1251', 'utf-8','расшифровка подписи'))
				;
				
				$working_row++;	
				
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','Отпуск груза произвел'))
					 
					->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8','Груз получил 

'))
					;
					
				$objPHPExcel->getActiveSheet()->mergeCells('E'.($working_row).':F'.$working_row);		
				
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				 	
				
				$objPHPExcel->getActiveSheet()->getStyle('E'.$working_row.':I'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->getActiveSheet()->getStyle('K'.$working_row.':P'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('Y'.$working_row.':Z'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('AB'.$working_row.':AE'.($working_row))->applyFromArray($styleArray);	
				
				$objPHPExcel->getActiveSheet()->getStyle('AI'.$working_row.':AN'.($working_row))->applyFromArray($styleArray);			
					
				$working_row++;	
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('E'.$working_row, iconv('windows-1251', 'utf-8',' должность
'))
					->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8','подпись
'))
					->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','расшифровка подписи
'))
					->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8',' грузополучатель
'))
					->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8',' должность
'))
					->setCellValue('AB'.$working_row, iconv('windows-1251', 'utf-8','подпись
'))
					->setCellValue('AI'.$working_row, iconv('windows-1251', 'utf-8','расшифровка подписи'))
				;
				
				$working_row++;
				$working_row++;
				$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','М.П.'))
					 
					->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','"'.date('d',$editing_user['given_pdate_unf']).'"'))
					->setCellValue('G'.$working_row, iconv('windows-1251', 'utf-8',$m))
					->setCellValue('J'.$working_row, iconv('windows-1251', 'utf-8',date('Y',$editing_user['given_pdate_unf']).' года'))
					
					->setCellValue('W'.$working_row, iconv('windows-1251', 'utf-8','М.П.'))
					 
					->setCellValue('AA'.$working_row, iconv('windows-1251', 'utf-8','"   "'))

					->setCellValue('AI'.$working_row, iconv('windows-1251', 'utf-8',' года'))
					;
				
				$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				 	
				
				$objPHPExcel->getActiveSheet()->getStyle('G'.$working_row.':I'.($working_row))->applyFromArray($styleArray);
				
				$objPHPExcel->getActiveSheet()->getStyle('AC'.$working_row.':AE'.($working_row))->applyFromArray($styleArray);
				
				$styleArray = array(
					 
					'borders' => array(
						'left'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);	
				
				$objPHPExcel->getActiveSheet()->getStyle('R'.($working_row-12).':R'.($working_row))->applyFromArray($styleArray);

?>