<?
require_once('abstractitem.php');
require_once('komplitem.php');
/*require_once('storageitem.php');
require_once('sectoritem.php');*/


class KomplBlink{
	
	
	
	
	
	
	//���������� ������� ����� � �������
	public function OverallBlink($komplekt_id, $komplekt_status_id, $user_id, $is_supply_user, &$color, $komplekt_sector_id=NULL, $komplekt_storage_id=NULL,$komplekt_sector_ss=NULL, $komplekt_storage_ss=NULL){
		$res=false;
		
		$_ki=new KomplItem;
		$color='black';
		
		//����������� - �� ���-�� ������� � ��-� ������ ���
		
		
		if(($is_supply_user)&&(($komplekt_status_id==12)||($komplekt_status_id==2)||($komplekt_status_id==13))){
			//���������, ���� �� ���. �����
			//���� ��� - �������, ������
			//����� - ������
			//��������� ���� ��� �����
			$sql='select count(*) from bill_position as bp inner join bill as b on bp.bill_id=b.id where bp.komplekt_ved_id="'.$komplekt_id.'" and b.is_confirmed_price=1 and b.is_confirmed_shipping=1';
			//echo $sql;
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]==0){
				$res=true;
				//���������
				$color='#a48300';
			}
		}elseif($komplekt_status_id==11){
			//������ ����� 
			//��� ����������� - ��������� 85 ��� 182 (��������� �������)
			//���� ���� ����� �� ����������� (��-�� 85, 180-183, 296)
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
			//������ �� ����������
			
			//���������, ���-�� �� ��� ��������� �����
			//�� - �������, ������
			//��� - �������, ������
			//��� ���� - ������, �� ������
			
			$color='#666666';
			//���������� ����
			//���� ���� ����� �� ����������� (��-�� 85, 180-183, 296)
			$sql='select count(*) from user_rights where user_id="'.$user_id.'" and object_id in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359, 532,548,549, 590)','(85, 386, 387, 388, 390, 391, 392, 533,552,553, 592)')).'
			 and right_id=2';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			//echo $komplekt_id.'zzzzzzzzzzzzz'.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359)','(85, 386, 387, 388, 390, 391, 392)'));
			
			//var_dump($rs);
			
			if((int)$f[0]>0){
				//����� ����...
				$res=true;
				
				//echo 'zzzzzzzzzz';	
				
				//������ ���������� - ������ ��� �������
				$sql1='select count(*) from user_rights where user_id="'.$user_id.'" and object_id=85 and right_id=2';
				//echo $sql1;
				$set1=new mysqlset($sql1);
				$rs1=$set1->GetResult();
				$g=mysqli_fetch_array($rs1);
				
				$objects=array(); $additional_objects=array();
				if((int)$g[0]>0){
					//���� ��� �����
					
					
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
					 
					 //���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ����������� (7) - ��������� ������ � ������� 548 552
					
					
					//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ���. ��� (9) - ��������� ������ � ������� 549 553
					
					 //���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ������������ (18) - ��������� ������ � ������� 590 592
					
					 
					//������ ������� �� ������� ������������ ����� ���������� ������
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
				  //������ ��� ����������� ������ �� ���� ��������. ���� ��� ���� - �������, ���� ���� �� 1 ��� - �������	
				  // print_r($objects);
				   
				   //���� ���� ������� ��� ����
					 //���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ����������� (7) - ��������� ������ � ������� 548 552
					
					
					//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� ���. ��� (9) - ��������� ������ � ������� 549 553
					
					//���� ��� ���� ���-�� ������� (5) � � ������ ������� - ������� �������������� (18) - ��������� ������ � ������� 590 592
					
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
		
		//��������� ������ ��� ���. �������!
		
		if((!$is_supply_user)&&(($komplekt_status_id==12)||($komplekt_status_id==2)||($komplekt_status_id==13)||($komplekt_status_id==1))){
			//��������, �������� �� ��������� ����������� ������� (182)
			//���� �������� - ��:
			//���� � ������ ���� ��� ����������� + ��� ������ �� ������ - �������, ������
			
			$sql='select count(*) from user_rights where user_id="'.$user_id.'" and object_id=182 and object_id not in '.$_ki->rd->FindRId(NULL,NULL,NULL,NULL, $komplekt_sector_ss,$komplekt_storage_ss,array('(85, 180, 181, 182, 183, 296, 359, 532, 469)', '(85, 386, 387, 388, 390, 391, 392, 533, 471)')).' and right_id=2';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			//var_dump($rs);
			
			//� ���������� ��� ������ �����
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
				
				  //��� ����������� - ������� ��������� ��� ����
			  }else{
				  
				  //���� ����������� - �������� �����!
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