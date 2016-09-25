<?
//require_once('actionitem.php');
require_once('db_decorator.php');
require_once('PageNavigator.php');
require_once('usersgroup.php');
require_once('actionlog.php');
require_once('useritem.php');

class UsersActivityCr{
	
	
	public function ShowLog($template, DBDecorator $dec2, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		$ug=new UsersGroup();
		
		$sm=new SmartyAdm;
		$ui=new UserItem();
		
		//���� � ������� �����
		
		//�������� � dec2
		$pars=$dec2->GetSqls();
		$pdate1=0; $pdate2=0;
		$user_login=0;
		foreach($pars as $k=>$v){
			//print_r($v);
			if($v->GetName()=='pdate'){
				$pdate1=$v->GetValue();
				$pdate2=$v->GetValue2();	
			}
			if($v->GetName()=='login'){
				$user_login=$v->GetValue();	
			}
			
			
		}
		
		$ui=new UserItem;
		$user=$ui->GetItemByFields(array('login'=>$user_login));
		
		
		//echo $pdate1,$pdate2;
		$step=60*60*24;
		$curr=$pdate1;
		$dates=array();
		$all_time=0; //����� ����� � ���������
		
		$count_of_worked_days=0; //����� ������������ ����
		
		$_operations=array(); //������ �������� �� � ���
		$_dates_operations=array(); //������ �������� �� � ��� � ���� �� �����
		
		$_all_operations=array(); //������ ������ ���� �������� ���-��
		
		//������ ������ �������� �� � ���
		$set=new mysqlset('select * from action_log where (pdate between "'.$pdate1.'" and "'.$pdate2.'") and user_subj_id="'.$user['id'].'" /*and (description="���� � �������" or description="����� �� �������")*/  order by id asc');
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$in_out=(int)($f['description']=="���� � �������");
			
			if(($f['description']=="���� � �������")||($f['description']=="����� �� �������")){
				 $arr=array(
					'pdate'=>$f['pdate'],
					'ip'=>$f['ip'],
					'in_out'=>$in_out
				);
				
				$_operations[]= $arr;
			}
			
			
			$_all_operations[]=$f;
			
			
		}
		
		
		//��������� ������ �������� �� �����
		while($curr<=$pdate2){
			$ops=array();
			
			foreach($_operations as $k=>$v){
				if(($v['pdate']>$curr)&&($v['pdate']<=$curr+$step)){
					$ops[]=$v;
		//			print_r($v);
				}
			}
			
			$_dates_operations[]=array(
				'pdate'=>$curr,
				'operations'=>$ops
			);
			
			$curr+=$step;	
		}
		
		
		//�������� ������ �������� �� �����...
		foreach($_dates_operations as $do_k=>$do_v){
			$day_time=0;
			$curr=$do_v['pdate'];
			$ssrok='';
			
			
			$_day_was_operated=false;
			
			//�������� ������ �������� �� ������� ����
			foreach($do_v['operations'] as $k=>$v){
				
				if($v['in_out']==1){
					//����� �������� �����
					$ssrok.='����: '.date("d.m.Y H:i:s",$v['pdate']).'<br />';
					
					$current_ip=$v['ip'];
					
					
					//���� ��������� ����� � ������ ����
					
					$next_operation=$this->GetNextOpOut($k, $current_ip, $do_v['operations'] , &$next_index);
					
					if(($next_operation!==NULL)&&($next_operation['in_out']==0)){
						//��� �����, ��������� ������
						$_day_was_operated=$_day_was_operated||true;
						$day_time+=($next_operation['pdate']-$v['pdate']);
						
						$ssrok.='�����: '.date("d.m.Y H:i:s",$next_operation['pdate']).'<br />';
					
					
					
					//��������� ����. �������� c ������ ����:
					/*$next_operation=$this->GetNextOp($k, $current_ip, $do_v['operations'] , &$next_index);
					
					if(($next_operation!==NULL)&&($next_operation['in_out']==0)){
						//��� �����, ��������� ������
						$_day_was_operated=$_day_was_operated||true;
						$day_time+=($next_operation['pdate']-$v['pdate']);
						
						$ssrok.='�����: '.date("d.m.Y H:i:s",$next_operation['pdate']).'<br />';
					
					}elseif(($next_operation!==NULL)&&($next_operation['in_out']==1)){
						
						//��� ������ ����, ���� ��������� �������� � ����� ���� � ���� ����
						$set=new mysqlset('select * from action_log where (pdate between "'.$v['pdate'].'" and "'.($curr+$step).'") and ip="'.$v['ip'].'" and user_subj_id="'.$user['id'].'" and description<>"���� � �������" order by id desc limit 1');	
						
						$rc=$set->getResultNumRows();
						$rs=$set->getResult();
						//$ssrok.='����: '.date("d.m.Y H:i:s",$v['pdate']);
						
						if($rc>0){
							$f=mysqli_fetch_array($rs);	
							$ssrok.='��������� ��������: '.date("d.m.Y H:i:s",$f['pdate']).'<br />';
							$_day_was_operated=$_day_was_operated||true;
							$day_time+=($f['pdate']-$v['pdate']);
						}
						
						*/
					}else{
						//����. �������� ����� ����� ���!
						//��������� ������ ������ � ����. ����, ����� ��������� ����:
						/*$first_operation=$this->GetFirstOpNextDate($do_k, $current_ip, $_dates_operations, $its_index);
						
						
						if($first_operation===NULL){
							//������ ������ � ����. ���� ���!
							//������ �� ���������!
						}else{
							//���� ������ �������� � ��. ���� - ��� �����, �� ��������� ������� ���
							//� ��������� ��� ������
							//���� �� ����� - �� ���� ����� ������ �� ������� ��������
							//if($_dates_operations[$do_k+1]['operations'][0]['
							if($first_operation['in_out']==0){
								//��������� ��� ������
								$_day_was_operated=$_day_was_operated||true;
								$day_time+=$first_operation['pdate']-$v['pdate'];
								
								$ssrok.='����� �� ������ ����: '.date("d.m.Y H:i:s",$first_operation['pdate']).'<br />';
							}else{*/
							
								//���� ����� ������ �� ������� ��������
								
								/*$set=new mysqlset('select * from action_log where (pdate between "'.$v['pdate'].'" and "'.($curr+$step).'") and ip="'.$v['ip'].'" and user_subj_id="'.$user['id'].'" and description<>"���� � �������" order by id desc limit 1');
								
								$rc=$set->getResultNumRows();
								$rs=$set->getResult();
								//$ssrok.='����: '.date("d.m.Y H:i:s",$v['pdate']);
								
								if($rc>0){
									$f=mysqli_fetch_array($rs);	
									$ssrok.='��������� ��������: '.date("d.m.Y H:i:s",$f['pdate']).'<br />';
									$_day_was_operated=$_day_was_operated||true;
									$day_time+=($f['pdate']-$v['pdate']);
								}else{
									$ssrok.='�����: �� ������������'.'<br />';
								} */
								
								
								
								
								$other_operation=$this->FindOutherOperation($v['pdate'], $curr+$step,  $v['ip'], $_all_operations, $index);
								if($other_operation!==NULL){
									$ssrok.='��������� ��������: '.date("d.m.Y H:i:s",$other_operation['pdate']).'<br />';
									$_day_was_operated=$_day_was_operated||true;
									$day_time+=($other_operation['pdate']-$v['pdate']);
									
									//print_r($other_operation);
									
									
								}else{
									$ssrok.='�����: �� ������������'.'<br />';
								}
								
								
								
								
							/*}
								
						}*/
					
					}
					
					
					
				}// �� ����, �� ������ ������
				
			
			}//����� ����� �� ���������
			
			
			if($day_time>0){
				 $psrok=''.round(($day_time/60/60),2).' ���.';
				
			}else $psrok='-';	
			
			
			$dates[]=array(
				'pdate'=>date("d.m.Y",$curr),
				'ptimes'=>$ssrok,
				'psrok'=>$psrok
			);
			
			$all_time+=$day_time;
			
			
			if($_day_was_operated) $count_of_worked_days++;
		}
		
		
		
		/*echo '<pre>';
		print_r($dates);
		echo '</pre>';*/
		
		
		
		
		$sm->assign('works',$dates);
		
		
		
		$sm->assign('total', round(($all_time/60/60),2).' ���.');
		
		
		echo '���������� ���� � ��������� '.$count_of_worked_days.'<br>';
		
		echo '����� ����� � ��������� '.round(($all_time/60/60),2).'<br>';
		
		echo '������� ����� ������ � ����, ��� '.(round(($all_time/60/60)/$count_of_worked_days,2)).'<br>';
		
		
		
		/*echo '<pre>';
		print_r($dates);
		echo '</pre>';*/
		
		
		//�������� ������ ������
	
		$fields=$dec2->GetUris();
		$is_active=0; $print=0;
		foreach($fields as $k=>$v){
			if(($v->GetName()=='is_active')&&($v->GetValue()==1)) $is_active=1;
			$sm->assign($v->GetName(),$v->GetValue());	
			if(($v->GetName()=='print')&&($v->GetValue()==1)) $print=1;
		}
		
		
		//��������� ������ ������������
		
		$log=new ActionLog;
		
		//if($print==1) $llg=$log->ShowLog('syslog/log_print.html',$dec,$from,$to_page,'users_activity.php');
		//else $llg=$log->ShowLog('syslog/log.html',$dec,$from,$to_page,'users_activity.php');
	
		
		
		
		
		//������������
		$users=$ug->GetItemsArr($fields['user'],$is_active);
		
		$u_ids=array(); $u_values=array();
		//$u_ids[]='0';
		//$u_values[]='-��������-';
		foreach($users as $k=>$v){
			$u_ids[]=$v['login'];
			if($v['group_id']==3){
				$u_values[]=stripslashes($v['login']).' '.stripslashes($v['name_d']);	
			}else{
				$u_values[]=stripslashes($v['login']).' '.stripslashes($v['name_s']);	
			}
			
		}
		$sm->assign('user_ids',$u_ids);
		$sm->assign('users',$u_values);
		$sm->assign('users_globals',$users);
		
		$sm->assign('syslog',$llg);
		
		$sm->assign('from',$from);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		//������ ��� ������ ����������
		$link=$dec->GenFltUri();
		$link='users_activity_cr.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	//����. �������� c ������ ����:
	protected function GetNextOp($current_op_index, $ip, $ops_data, &$next_index){
		$res=NULL;
		
		//$ops_data - ������ �������� � ��� ���� �� ����� ip
		foreach($ops_data as $k=>$v){
			if($ip==$v['ip']){
				if($k>$current_op_index){
					
					$res=$v;
					$next_index=$k;
					break;
						
				}
				
			}
				
		}
		
		
		return $res;
	}
	
	//����. �������� ������ c ������ ����:
	protected function GetNextOpOut($current_op_index, $ip, $ops_data, &$next_index){
		$res=NULL;
		
		//$ops_data - ������ �������� � ��� ���� �� ����� ip
		foreach($ops_data as $k=>$v){
			if(($ip==$v['ip'])&&($v['in_out']==0)){
				if($k>$current_op_index){
					
					$res=$v;
					$next_index=$k;
					break;
						
				}
				
			}
				
		}
		
		
		return $res;
	}
	
	//������. �������� c ������ ����:
	protected function GetLastOp($current_op_index, $ip, $ops_data, &$last_index){
		$res=NULL;
		
		//$ops_data - ������ �������� � ��� ���� �� ����� ip
		foreach($ops_data as $k=>$v){
			if($ip==$v['ip']){
				if($k>$current_op_index){
					
					$res=$v;
					$last_index=$k;
					
				}
				
			}
				
		}
		
		
		return $res;
	}
	
	
	//������ ������ � ����. ����, ����� ��������� ����
	protected function GetFirstOpNextDate($current_date_index, $ip, $data, &$its_index){
		$res=NULL;
		
		if(isset($data[$current_date_index+1]['operations'])) foreach($data[$current_date_index+1]['operations'] as $k=>$v){
			if($ip==$v['ip']){
				
				$res=$v;
				$its_index=$k;
				break;
					
			}
			
		}
		
		
		
		return $res;
	}
	
	
	
	//���� ��������� ������ �� ������� ��������
	protected function FindOutherOperation($current_date, $end_pdate, $ip, $data, &$its_index){
		$res=NULL;
		
		foreach($data as $k=>$v){
			if(($v['pdate']>=$current_date)&&($v['pdate']<$end_pdate)&&($v['ip']==$ip)&&($v['description']!="���� � �������")){
				$res=$v;
				$its_index=$k;
				//break;
			}
		}
		
		return $res;
	}
	//							$set=new mysqlset('select * from action_log where (pdate between "'.$v['pdate'].'" and "'.($curr+$step).'") and ip="'.$v['ip'].'" and user_subj_id="'.$user['id'].'" and description<>"���� � �������" order by id desc limit 1');
}
?>