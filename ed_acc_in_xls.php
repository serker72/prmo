<?
			
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();
			
			// Set document properties
			$objPHPExcel->getProperties()->setCreator(iconv('windows-1251', 'utf-8',SITETITLE))
										 ->setLastModifiedBy(iconv('windows-1251', 'utf-8',SITETITLE))
										 ->setTitle(iconv('windows-1251', 'utf-8',"������� �����������"))
										 ->setSubject(iconv('windows-1251', 'utf-8',"������� �����������"))
										 ->setDescription(iconv('windows-1251', 'utf-8',"������� �����������, ������������� ������ ���������� ".SITETITLE."."))
										 ->setKeywords("")
										 ->setCategory(iconv('windows-1251', 'utf-8',"�����"));
			
			
			
			
			$objPHPExcel->getDefaultStyle()->getFont()
				->setName('Arial')
				->setSize(8);
			
			
			
			if($printmode==0){
				//���������
				include_once('ed_acc_in_xls_nakl.php');	
				
			}
			elseif($printmode==1){
				//�/�
				include_once('ed_acc_in_xls_sf.php');	
				
					
			}else{
				//���	
				include_once('ed_acc_in_xls_akt.php');
				
			}
			
			
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$docname.'.xls"');
			header('Cache-Control: max-age=0');
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			
			
			$objWriter->save('php://output');
			die();
			
			?>