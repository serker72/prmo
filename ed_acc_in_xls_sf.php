<?
$docname='����/������� � '.$editing_user['given_no'];

	$objPHPExcel->getDefaultStyle()->getFont()
				->setName('Arial')
				->setSize(8);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(32);

$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(8);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(4);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(11);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(20);
$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(0.01);
$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(10);

$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(2);
$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(5);
$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(12);
				
				
				
$working_row=1;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A'.$working_row, iconv('windows-1251', 'utf-8',"\n���������� � 1\n � ������������� ������������� ���������� ��������� \n �� 26 ������� 2011 �. � 1137"));


	$styleArray = array(
	'font' => array(
		'bold' => false,
		'size'=>6
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A'.$working_row)->applyFromArray($styleArray);


	 $objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.($working_row))->getAlignment()->setWrapText(true);
	 
	 $objPHPExcel->setActiveSheetIndex(0)->getStyle('A'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
	 
	 $objPHPExcel->getActiveSheet()->mergeCells('A'.($working_row).':AA'.($working_row));
	 $objPHPExcel->setActiveSheetIndex(0)->getRowDimension(($working_row))->setRowHeight(35);	
	 
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','����-������� � '.$editing_user['given_no'].' �� '.date('d',$editing_user['given_pdate_unf']).' '.$m.' '.date('Y',$editing_user['given_pdate_unf']).' �.'));
			
			
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','����������� � -- ��--'));
			
			
$styleArray = array(
	'font' => array(
		'bold' => true,
		'size'=>14
	)
);

$objPHPExcel->getActiveSheet()->getStyle('B'.($working_row-1).':B'.($working_row))->applyFromArray($styleArray);			
			
			
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','��������: '.$opf['name'].' '.$supplier['full_name']));
			
			$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','�����: '.str_replace("\n", " ", $supplier['legal_address'])));

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','���/��� ��������: '.' '.$supplier['inn'].'/ '.$supplier['kpp']));

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','���������������� � ��� �����: '.$opf['name'].' '.$supplier['full_name']. ' '.str_replace("\n", " ", $supplier['legal_address'])));


$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','��������������� � ��� �����: '.$org_opf['name'].' '.$orgitem['full_name'].', '.$orgitem[legal_address]));

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','� ��������-���������� ��������� � -- �� --'));

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','����������: '.$org_opf['name'].' '.$orgitem['full_name']));

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','�����: '.str_replace("\n", " ", $orgitem['legal_address'])));

$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','���/��� ����������: '.$orgitem['inn'].'/ '.$orgitem['kpp']));
			
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','������: ������������, ��� ���������� �����, 643	'));

				

//�������
//header

$working_row++;		
$tab_begin=$working_row;
 
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','������������ ������ (�������� ����������� �����, ��������� �����), �������������� �����'));
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','�������
���������				
'));			


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8','����-
������ 
(�����)
	
'));


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','���� (�����) �� ������� ���������	
	
'));


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8','��������� ������� (�����, �����), ������������� ���� ��� ������ - �����		
		

'));


$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('P'.$working_row, iconv('windows-1251', 'utf-8','� ���
�����
����� 
������	
	
'));
			
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8','��������� ������

'));
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8','����� ������, ������������� ����������			
			
'));
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('W'.$working_row, iconv('windows-1251', 'utf-8','��������� ������� (�����, �����), ������������� ���� � ������� - �����	
	
'));
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8','������
������������� ������
'));
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('AA'.$working_row, iconv('windows-1251', 'utf-8','�����
����������
����������

'));


$working_row++;

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','���	
'));	
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','�������� ����������� (������������)		
'));	
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8','�������� ���
'));	
			
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8','������� ������������
'));																											


$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','1'))
			->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8','2'))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8','2a'))
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8','3'))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8','4'))
			->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8','5'))
			->setCellValue('P'.$working_row, iconv('windows-1251', 'utf-8','6'))
			->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8','7'))
			->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8','8'))
			->setCellValue('W'.$working_row, iconv('windows-1251', 'utf-8','9'))
			->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8','10'))
			->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8','10a'))
			->setCellValue('AA'.$working_row, iconv('windows-1251', 'utf-8','11'))
			
			
			
			;


$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row-2).':C'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row-2).':H'.($working_row-2));
$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row-2).':J'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('K'.($working_row-2).':L'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('M'.($working_row-2).':O'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('P'.($working_row-2).':Q'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('T'.($working_row-2).':V'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('W'.($working_row-2).':X'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('Y'.($working_row-2).':Z'.($working_row-2));
$objPHPExcel->getActiveSheet()->mergeCells('R'.($working_row-2).':S'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('AA'.($working_row-2).':AA'.($working_row-1));
 
$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row-1).':E'.($working_row-1));
$objPHPExcel->getActiveSheet()->mergeCells('F'.($working_row-1).':H'.($working_row-1));
 

$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':E'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('F'.($working_row).':H'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row).':J'.($working_row));

$objPHPExcel->getActiveSheet()->mergeCells('K'.($working_row).':L'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('M'.($working_row).':O'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('P'.($working_row).':Q'.($working_row));
//$objPHPExcel->getActiveSheet()->mergeCells('S'.($working_row).':R'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('T'.($working_row).':V'.($working_row));
$objPHPExcel->getActiveSheet()->mergeCells('W'.($working_row).':X'.($working_row));



$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row-2).':AA'.($working_row))->getAlignment()->setWrapText(true);

$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row-2).':AA'.($working_row))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row-2).':AA'.($working_row))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);	


//�������
foreach($bpg as $k=>$v){
	$working_row++;
	$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',$v['position_name']))
			->setCellValue('D'.$working_row, iconv('windows-1251', 'utf-8',$v['okei']))
			->setCellValue('F'.$working_row, iconv('windows-1251', 'utf-8',$v['dim_name']))
			->setCellValue('I'.$working_row, iconv('windows-1251', 'utf-8',$v['quantity']))
			->setCellValue('K'.$working_row, iconv('windows-1251', 'utf-8',$v['price_pm']))
			->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',$v['total']-$v['nds_summ']))
			->setCellValue('P'.$working_row, iconv('windows-1251', 'utf-8','--'))
			->setCellValue('R'.$working_row, iconv('windows-1251', 'utf-8',$v['nds_proc']))
			->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8',$v['nds_summ']))
			->setCellValue('W'.$working_row, iconv('windows-1251', 'utf-8',$v['total']))
			->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8','--'))
			->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8','--'))
			->setCellValue('AA'.$working_row, iconv('windows-1251', 'utf-8','--'))
			
			
			
			;
	
	
	 
	$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':C'.($working_row));
	
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.$working_row.':C'.($working_row))->getAlignment()->setWrapText(true);
	
	$objPHPExcel->getActiveSheet()->mergeCells('D'.($working_row).':E'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('F'.($working_row).':H'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('I'.($working_row).':J'.($working_row));
	
	$objPHPExcel->getActiveSheet()->mergeCells('K'.($working_row).':L'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('M'.($working_row).':O'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('P'.($working_row).':Q'.($working_row));
	//$objPHPExcel->getActiveSheet()->mergeCells('S'.($working_row).':R'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('T'.($working_row).':V'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('W'.($working_row).':X'.($working_row));
	
	$objPHPExcel->setActiveSheetIndex(0)->getRowDimension($working_row)->setRowHeight(25);
}

//vsego
$working_row++;
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8','����� � ������
'))
			  
			->setCellValue('M'.$working_row, iconv('windows-1251', 'utf-8',number_format($totals_by_all-$nds_sums_by_all,2,',','')))
			->setCellValue('P'.$working_row, iconv('windows-1251', 'utf-8','X'))
			->setCellValue('T'.$working_row, iconv('windows-1251', 'utf-8',number_format($nds_sums_by_all,2,',','')))
			->setCellValue('W'.$working_row, iconv('windows-1251', 'utf-8',number_format($totals_by_all,2,',','')))
		 
			->setCellValue('Y'.$working_row, iconv('windows-1251', 'utf-8',' '))
			->setCellValue('Z'.$working_row, iconv('windows-1251', 'utf-8',' '))
			->setCellValue('AA'.$working_row, iconv('windows-1251', 'utf-8',' '))
			
			
			
			;
			
$objPHPExcel->getActiveSheet()->mergeCells('B'.($working_row).':L'.($working_row));
	 
	$objPHPExcel->getActiveSheet()->mergeCells('M'.($working_row).':O'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('P'.($working_row).':Q'.($working_row));
	//$objPHPExcel->getActiveSheet()->mergeCells('S'.($working_row).':R'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('T'.($working_row).':V'.($working_row));
	$objPHPExcel->getActiveSheet()->mergeCells('W'.($working_row).':X'.($working_row));			
$tab_end=$working_row;



				$styleArray = array(
					 
					'borders' => array(
						'allborders'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('B'.$tab_begin.':AA'.($tab_end))->applyFromArray($styleArray);



$styleArray = array(
	'font' => array(
		'bold' => true 
	)
);

$objPHPExcel->getActiveSheet()->getStyle('B'.$working_row)->applyFromArray($styleArray);




$working_row++;
$working_row++;

//podpisi

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"������������ �����������\n ��� ���� �������������� ����"
))
			->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8',$supplier['chief']))
			
			->setCellValue('L'.$working_row, iconv('windows-1251', 'utf-8',"������� ���������\n ��� ���� �������������� ����"				

))
			->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8',$supplier['main_accountant']))
			;
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('B'.($working_row))->getAlignment()->setWrapText(true);
	
	$objPHPExcel->setActiveSheetIndex(0)->getStyle('L'.($working_row))->getAlignment()->setWrapText(true);
	 	
		$objPHPExcel->getActiveSheet()->mergeCells('L'.($working_row).':P'.($working_row));
		
		
		$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('C'.$working_row.':F'.($working_row))->applyFromArray($styleArray);
		
		$objPHPExcel->getActiveSheet()->getStyle('H'.$working_row.':K'.($working_row))->applyFromArray($styleArray);
		
		$objPHPExcel->getActiveSheet()->getStyle('Q'.$working_row.':R'.($working_row))->applyFromArray($styleArray);
		
		$objPHPExcel->getActiveSheet()->getStyle('U'.$working_row.':W'.($working_row))->applyFromArray($styleArray);
		
$working_row++;		
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8','(�������)			
'))
		->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8','(�.�.�.)			
'))
		
		->setCellValue('Q'.$working_row, iconv('windows-1251', 'utf-8','(�������)
'))
		->setCellValue('U'.$working_row, iconv('windows-1251', 'utf-8','(�.�.�.)		
'))
		
		;
		
$working_row++;

$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('B'.$working_row, iconv('windows-1251', 'utf-8',"
�������������� ���������������"
))		
			->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8','--			
'))
			->setCellValue('N'.$working_row, iconv('windows-1251', 'utf-8','--			
'))

;


$styleArray = array(
					 
					'borders' => array(
						'bottom'=> array(
							'style' => PHPExcel_Style_Border::BORDER_THIN,
						),
					)
				);
				$objPHPExcel->getActiveSheet()->getStyle('C'.$working_row.':F'.($working_row))->applyFromArray($styleArray);
		
		$objPHPExcel->getActiveSheet()->getStyle('H'.$working_row.':K'.($working_row))->applyFromArray($styleArray);
		
		
		$objPHPExcel->getActiveSheet()->getStyle('N'.$working_row.':W'.($working_row))->applyFromArray($styleArray);
		

$working_row++;

$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('C'.$working_row, iconv('windows-1251', 'utf-8','(�������)			
'))
		->setCellValue('H'.$working_row, iconv('windows-1251', 'utf-8','(�.�.�.)			
'))
		
		->setCellValue('N'.$working_row, iconv('windows-1251', 'utf-8','(�������)
'))
		 
		;

?>