<?
require_once('an_fill_abstract_entry.php');
require_once('an_fill_simple_entry.php');
require_once('an_fill_complex_entry.php');
require_once('an_fill_subsequent_entry.php');

//набор полей для отчета заполненность данными

class AnFillSetEntries{
	
	/*public function Compare($value){
		if($this->	
		
	}*/
	
	
	//построение массива форм
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
	
	
	
	
	//сравнить формы с выдачей
	public function Compare(array $f, array $fields, &$global_is_empty){
		$arr=array();
		
		//echo '<h2>'.$f['full_name'].', '.$f['opf_name'].'</h2>';
		$global_is_empty=false;
		
		
		foreach($fields as $k=>$v){
			if($v->is_checked){
				$entry=array();
				//сравнение поля
				switch($v->type){
					case 0: 
					//простое поле	
						$global_is_empty=$global_is_empty||(bool)($f[$v->fieldname]==$v->nf_value);
						
						$entry=array(
						'is_empty'=>(bool)($f[$v->fieldname]==$v->nf_value),
						'text'=>'' 
						);
						
						
						//echo $v->fieldname.' = '.$f[$v->fieldname].' vs '.$v->nf_value.'<br>';  
						
					break;
					case 1: 
					//составное поле	
						//получить все данные
						
						$is_not_empty=true;
						$text='';
						
						$data=$v->GetData($f['id']);
						
						
						
						
						
						//число данных = 0 - поле пустое, пояснительный текст об этом
						//число данных != 0 - перебрать данные, нужно чтобы было заполнено каждое из требуемых полей для каждой строки данных, вывести детализацию в любом случае
						if(count($data)==0){
							$is_not_empty=false;
							$text='нет записей';	
						}else{
							//var_dump($data);
							
							//перебрать простые подчиненные поля
							foreach($v->fields as $kk=>$vv){
								if(($vv->is_checked)&&($vv->type==0)){
									 //echo $vv->fieldname.' = '.$vv->nf_value.'<br>';
									 
									 // писать, какая основная запись.
									 
									 //перебрать справочник
									 foreach($data as $dk=>$dv){
									   $res=$vv->FindInData($vv->fieldname, $dv, $value);
									   //echo (int)$res.' в справочнике '.$value.'<br>';
									   
									   if(!$res){
												  $is_not_empty=$is_not_empty&&false;
												  $text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'запись '.$vv->caption.' не найдена.<br>';
												  
									   }elseif($value==$vv->nf_value){
												   $is_not_empty=$is_not_empty&&false;
												  $text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'поле '.$vv->caption.' не заполнено.<br>';
												  //echo $vv->caption.$vv->fieldname.' = '.$value.' vs '.$vv->nf_value.'<br>';
									   }else{
											  //echo 'запись найдена '.$vv->caption.$vv->fieldname.' = '.$value.' vs '.$vv->nf_value.'<br>'; 
									   }
									   
									 }
								
								}
							}
							
							//перебрать подчиненнные поля
							$count_of_complex_fields=0;
							foreach($v->fields as $kk=>$vv){
								if(($vv->is_checked)&&($vv->type==2)){
										$count_of_complex_fields++;
										
										
										//переперебрать подчиненнные поля справочника
										foreach($data as $dk=>$dv){
											
											 foreach($dv['data'] as $k2=>$v2){
												 //ввести ограничения, проверка повторяется много раз
												 if($v2[$vv->ident_filename]!=$vv->caption) continue;
												 
												 
												
											//и писать, какая для нее основная запись!	
											
													$res=$vv->FindInVauledData($vv->fieldname, $vv->caption, $vv->value_fieldname,  $v2, $value2);
											
													if(!$res){
																$is_not_empty=$is_not_empty&&false;
																$text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'запись '.$vv->caption.' не найдена.<br>';
																
													 }elseif($value2==$vv->nf_value){
																$is_not_empty=$is_not_empty&&false;
																$text.='<strong>'.$vv->descr_text.' '.$dv[$vv->descr_fieldname].':</strong> '.'поле '.$vv->caption.' не заполнено.<br>';
																
													 }	
											 }
										}
									
								}	
							}
						
				
							
							
						}
						
						//проверка на контакты без данных
						if($count_of_complex_fields>0){
						  $count_of_data=0;
						  foreach($data as $dk=>$dv){
							   if(count($dv['data'])==0){
								   $count_of_data++;
								   $text.='<strong>'.$v->descr_text.' '.$dv[$v->descr_fieldname].':</strong> не найдено записей.<br>';	
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
					//поле, подчиненное составному
					break;					
					
				
				}
				$arr[]=$entry;
			}
		}
		
		return $arr;
	}
	
	
	
	
}
?>