<?
require_once('an_fill_abstract_entry.php');
require_once('an_fill_simple_entry.php');
require_once('an_fill_complex_entry.php');
require_once('an_fill_subsequent_entry.php');

//����� ����� ��� ������ ������������� �������

class AnFillSetEntries{
	
	/*public function Compare($value){
		if($this->	
		
	}*/
	
	
	//���������� ������� ����
	public function DeployForms(array $fields){
		
		
		$arr=array();
		
		foreach($fields as $k=>$v){
			if(($v->type==0)||($v->type==1)){
				$arr[]=array(
					'fieldname'=>$v->fieldname,
					'caption'=>$v->caption,
					'is_checked'=>$v->is_checked
				);
			}
		}
		
		return $arr;
	}
	
	
	
	
	//�������� ����� � �������
	public function Compare(array $f, array $fields, &$global_is_empty){
		$arr=array();
		
		//echo '<h2>'.$f['full_name'].', '.$f['opf_name'].'</h2>';
		$global_is_empty=false;
		
		
		foreach($fields as $k=>$v){
			if($v->is_checked){
				$entry=array();
				//��������� ����
				switch($v->type){
					case 0: 
					//������� ����	
						$global_is_empty=$global_is_empty||(bool)($f[$v->fieldname]==$v->nf_value);
						
						$entry=array(
						'is_empty'=>(bool)($f[$v->fieldname]==$v->nf_value),
						'text'=>'' 
						);
						
						
						//echo $v->fieldname.' = '.$f[$v->fieldname].' vs '.$v->nf_value.'<br>';  
						
					break;
					case 1: 
					//��������� ����	
						//�������� ��� ������
						
						$is_not_empty=true;
						$text='';
						
						$data=$v->GetData($f['id']);
						
						
						
						
						
						//����� ������ = 0 - ���� ������, ������������� ����� �� ����
						//����� ������ != 0 - ��������� ������, ����� ����� ���� ��������� ������ �� ��������� ����� ��� ������ ������ ������, ������� ����������� � ����� ������
						if(count($data)==0){
							$is_not_empty=false;
							$text='��� �������';	
						}else{
							//var_dump($data);
							
							//��������� ������� ����������� ����
							foreach($v->fields as $kk=>$vv){
								if(($vv->is_checked)&&($vv->type==0)){
									 //echo $vv->fieldname.' = '.$vv->nf_value.'<br>';
									 
									 // ������, ����� �������� ������.
									 
									 //��������� ����������
									 foreach($data as $dk=>$dv){
									   $res=$vv->FindInData($vv->fieldname, $dv, $value);
									   //echo (int)$res.' � ����������� '.$value.'<br>';
									   
									   if(!$res){
												  $is_not_empty=$is_not_empty&&false;
												  $text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'������ '.$vv->caption.' �� �������.<br>';
												  
									   }elseif($value==$vv->nf_value){
												   $is_not_empty=$is_not_empty&&false;
												  $text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'���� '.$vv->caption.' �� ���������.<br>';
												  //echo $vv->caption.$vv->fieldname.' = '.$value.' vs '.$vv->nf_value.'<br>';
									   }else{
											  //echo '������ ������� '.$vv->caption.$vv->fieldname.' = '.$value.' vs '.$vv->nf_value.'<br>'; 
									   }
									   
									 }
								
								}
							}
							
							//��������� ������������ ����
							$count_of_complex_fields=0;
							foreach($v->fields as $kk=>$vv){
								if(($vv->is_checked)&&($vv->type==2)){
										$count_of_complex_fields++;
										
										
										//������������� ������������ ���� �����������
										foreach($data as $dk=>$dv){
											
											 foreach($dv['data'] as $k2=>$v2){
												 //������ �����������, �������� ����������� ����� ���
												 if($v2[$vv->ident_filename]!=$vv->caption) continue;
												 
												 
												
											//� ������, ����� ��� ��� �������� ������!	
											
													$res=$vv->FindInVauledData($vv->fieldname, $vv->caption, $vv->value_fieldname,  $v2, $value2);
											
													if(!$res){
																$is_not_empty=$is_not_empty&&false;
																$text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'������ '.$vv->caption.' �� �������.<br>';
																
													 }elseif($value2==$vv->nf_value){
																$is_not_empty=$is_not_empty&&false;
																$text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'���� '.$vv->caption.' �� ���������.<br>';
																
													 }	
											 }
										}
									
								}	
							}
						
				
							
							
						}
						
						//�������� �� �������� ��� ������
						if($count_of_complex_fields>0){
						  $count_of_data=0;
						  foreach($data as $dk=>$dv){
							   if(count($dv['data'])==0){
								   $count_of_data++;
								   $text.='<strong>'.$v->descr_text.' '.$dv[$v->descr_fieldname].':</strong> �� ������� �������.<br>';	
							   }
						  }
						  if($count_of_data>0){
							  $is_not_empty=$is_not_empty&&false;
							  
						  }
						}
						
						
						$global_is_empty=$global_is_empty||(!$is_not_empty);
						$entry=array(
							'is_empty'=>!$is_not_empty,
							'text'=>$text
						);
						
						//echo $v->fieldname.' = '.$f[$v->fieldname].' vs '.$v->nf_value.'<br>';
					break;
					case 2: 
					//����, ����������� ����������
					break;					
					
				
				}
				$arr[]=$entry;
			}
		}
		
		return $arr;
	}
	
	
	
	
}
?>