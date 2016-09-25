<?
require_once('abstractitem.php');
require_once('komplitem.php');
/*require_once('storageitem.php');
require_once('sectoritem.php');*/


class KomplBlink{
	
	
	
	
	
	
	//обобщенная функция цвета и мигания
	public function OverallBlink($komplekt_id, $komplekt_status_id, $user_id, $is_supply_user, &$color, $komplekt_sector_id=NULL, $komplekt_storage_id=NULL,$komplekt_sector_ss=NULL, $komplekt_storage_ss=NULL){
		$res=false;
		
		$_ki=new KomplItem;
		$color='black';
		
		//оптимизация - не опр-ть участок и об-т каждый раз
		
		
		if(($is_supply_user)&&(($komplekt_status_id==12)||($komplekt_status_id==2)||($komplekt_status_id==13))){
			//проверить, есть ли утв. счета
			//если нет - красная, мигает
			//иначе - черная
			//проверить связ утв счета
			$sql='select count(*) from bill_position as bp inner join bill as b on bp.bill_id=b.id where bp.komplekt_ved_id="'.$komplekt_id.'" and b.is_confirmed_price=1 and b.is_confirmed_shipping=1';
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]==0){
				$res=true;
				//оранжевая
				$color='#a48300';
			}
		}elseif($komplekt_status_id==11){
			//статус новая 
			//нет утверждений - проверять 85 или 182 (начальник участка)
			//если есть права на утверждение (об-ты 85, 180-183, 296)
			$sql='select count(*) from user_rights where user_id="'.$user_id.'" and object_id in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85,  182)','(85, 388)')).' and right_id=2';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			//var_dump($rs);
			$color='#666666';
			
			if((int)$f[0]>0){
				$sql2='select count(*) from komplekt_ved_confirm as kc inner join komplekt_ved_confirm_roles as kr on kc.role_id=kr.id where kc.komplekt_ved_id="'.$komplekt_id.'" and kr.'.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('confirm_object_id','ss_confirm_object_id')).' in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85,  182)','(85, 388)')).'';
			  //echo $sql2;
			  $set2=new mysqlset($sql2);
			  $rs2=$set2->GetResult();
			  $h=mysqli_fetch_array($rs2);
			  if((int)$h[0]<1){
				  $res=true;
				  $color='red';	
			  }else{
				  $res=true;
				  $color='green';
			  }
			}
		
		}elseif(($komplekt_status_id==1)){
			//статус не утверждена
			
			//проверить, утв-ны ли все доступные права
			//да - зеленая, мигает
			//нет - красная, мигает
			//нет прав - черная, не мигает
			
			$color='#666666';
			//опрашиваем цвет
			//если есть права на утверждение (об-ты 85, 180-183, 296)
			$sql='select count(*) from user_rights where user_id="'.$user_id.'" and object_id in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359, 532,548,549, 590)','(85, 386, 387, 388, 390, 391, 392, 533,552,553, 592)')).'
			 and right_id=2';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			//echo $komplekt_id.'zzzzzzzzzzzzz'.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359)','(85, 386, 387, 388, 390, 391, 392)'));
			
			//var_dump($rs);
			
			if((int)$f[0]>0){
				//права есть...
				$res=true;
				
				//echo 'zzzzzzzzzz';	
				
				//найдем суперправа - значит ВСЕ объекты
				$sql1='select count(*) from user_rights where user_id="'.$user_id.'" and object_id=85 and right_id=2';
				//echo $sql1;
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$g=mysqli_fetch_array($rs1);
				
				$objects=array(); $additional_objects=array();
				if((int)$g[0]>0){
					//ЕСТЬ ВСЕ ПРАВА
					
					
					$objects=$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array(array(180, 181, 182, 183, 296, 359, 532, 469), array(386, 387, 388, 390, 391, 392, 533, 471)));
					
					//$objects=array(180, 181, 182, 183, 296, 359);
					
				}else{
				     
					 $role5_1='296, ';
					 $role5_2='391, ';
					 if($komplekt_sector_id==9){
						$role5_1='548, ';
						$role5_2='552, '; 
					 }elseif($komplekt_sector_id==7){
						$role5_1='549, ';
						$role5_2='553, '; 
					 }elseif($komplekt_sector_id==18){
						$role5_1='590, ';
						$role5_2='592, '; 
					 }
					 
					 //если это роль Рук-ль объекта (5) и у заявки участок - участок механизации (7) - проверить доступ к объекту 548 552
					
					
					//если это роль Рук-ль объекта (5) и у заявки участок - участок мех. цех (9) - проверить доступ к объекту 549 553
					
					 //если это роль Рук-ль объекта (5) и у заявки участок - участок метромострой (18) - проверить доступ к объекту 590 592
					
					 
					//найдем объекты по которым пользователь может утверждать заявку
					$sql2='select distinct object_id from user_rights where user_id="'.$user_id.'" and object_id in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(180, 181, 182, 183, '.$role5_1.' 359, 532, 469)', '(386, 387, 388, 390, '.$role5_2.' 392, 533, 471)')).' and right_id=2';
					
					$set2=new mysqlset($sql2);
					$rs2=$set2->GetResult();
					$rc2=$set2->GetResultNumRows();
					for($i=0; $i<$rc2; $i++){
						$h=mysqli_fetch_array($rs2);
						$objects[]=(int)$h[0];
						
						
						
					}
				}
				
				if(count($objects)>0){
				  //$sql='select distinct object_id from user_rights where 
				  //найдем все утверждения заявки по этим объектам. если все есть - зеленая, если хотя бы 1 нет - красная	
				  // print_r($objects);
				   
				   //сюда дать довесок для прав
					 //если это роль Рук-ль объекта (5) и у заявки участок - участок механизации (7) - проверить доступ к объекту 548 552
					
					
					//если это роль Рук-ль объекта (5) и у заявки участок - участок мех. цех (9) - проверить доступ к объекту 549 553
					
					//если это роль Рук-ль объекта (5) и у заявки участок - участок метромостстрой (18) - проверить доступ к объекту 590 592
					
					 $add=''; 
					 if(($komplekt_sector_id==9)&&(in_array(548, $objects)||in_array(552, $objects))){
						 
						$add.=' or (kc.role_id=5) ';
					 }
					 
					 if(($komplekt_sector_id==7)&&(in_array(549, $objects)||in_array(553, $objects))){
						 
						$add.=' or (kc.role_id=5) ';
					 }
					 
					  if(($komplekt_sector_id==18)&&(in_array(590, $objects)||in_array(592, $objects))){
						 
						$add.=' or (kc.role_id=5) ';
					 }
					
				    
					$sql2='select count(*) from 
					komplekt_ved_confirm as kc inner join komplekt_ved_confirm_roles as kr on kc.role_id=kr.id 
						where kc.komplekt_ved_id="'.$komplekt_id.'" 
							and ( kr.'.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('confirm_object_id', 'ss_confirm_object_id')).' in ('.implode(', ',$objects).') '.$add.' )';
					
					
					
					//echo $sql2;
					$set2=new mysqlset($sql2);
					$rs2=$set2->GetResult();
					$h=mysqli_fetch_array($rs2);
				    
					
					
					
					
					if((int)$h[0]<count($objects)){
						$color='red';	
					}else{
						$color='green';
					}
				}
				
			}else{
				$res=false;
				$color='#666666';	
			}
			
		}
		
		//отдельная логика для нач. участка!
		
		if((!$is_supply_user)&&(($komplekt_status_id==12)||($komplekt_status_id==2)||($komplekt_status_id==13)||($komplekt_status_id==1))){
			//проверка, является ли сотрудник начальником участка (182)
			//если является - то:
			//если в заявке есть его утверждение + нет завоза по заявке - зеленая, мигает
			
			$sql='select count(*) from user_rights where user_id="'.$user_id.'" and object_id=182 and object_id not in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359, 532, 469)', '(85, 386, 387, 388, 390, 391, 392, 533, 471)')).' and right_id=2';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			//var_dump($rs);
			
			//у сотрудника нет других ролей
			$sql1='select count(*) from user_rights where user_id="'.$user_id.'" and object_id  in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359, 532, 469)', '(85, 386, 387, 388, 390, 391, 392, 533, 471)')).' and right_id=2';
			//echo $sql1;
			$set1=new mysqlset($sql1);
			$rs1=$set1->GetResult();
			$f_1=mysqli_fetch_array($rs1);
			
			//var_dump($f_1);
			
			if(((int)$f[0]>0)&&((int)$f_1[0]==0)){
				
				//echo $komplekt_id.' '.$sql1.'<br>';
				
				$sql2='select count(*) from komplekt_ved_confirm as kc inner join komplekt_ved_confirm_roles as kr on kc.role_id=kr.id where kc.komplekt_ved_id="'.$komplekt_id.'" and kr.'.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('confirm_object_id', 'ss_confirm_object_id')).'=182';
			  //echo $sql2;
			  $set2=new mysqlset($sql2);
			  $rs2=$set2->GetResult();
			  $h=mysqli_fetch_array($rs2);
			  if((int)$h[0]<1){
				
				  //нет утверждений - оставим результат как есть
			  }else{
				  
				  //есть утверждения - проверим завоз!
				  $sql3='select count(*) from bill where komplekt_ved_id="'.$komplekt_id.'" and is_confirmed_price=1 and is_confirmed_shipping=1';
				  //echo $sql;
				  $set3=new mysqlSet($sql3);
				  $rs3=$set3->GetResult();
				  
				  $f3=mysqli_fetch_array($rs3);
				  
				  if((int)$f3[0]==0){
					  $res=true;
					  $color='green';
				  }
				  
			  }
			}
			
			
			
		}
		
		
		
		return $res;
	}
}
?>