<?
//require_once('actionitem.php');
require_once('db_decorator.php');
require_once('PageNavigator.php');
require_once('usersgroup.php');
require_once('actionlog.php');
require_once('useritem.php');

class UsersActivity{
	
	
	public function ShowLog($template, DBDecorator $dec2, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE, &$total){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		$ug=new UsersGroup();
		
		$sm=new SmartyAdm;
		$ui=new UserItem();
		
		//даты в течение срока
		
		//работаем с dec2
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
		$all_time=0; //всего часов в программе
		
		$count_of_worked_days=0; //число отработанных дней
		
		$_operations=array(); //массив операций вх и вых
		$_dates_operations=array(); //массив операций вх и вых с разб по датам
		
		$_all_operations=array(); //массив вообще всех операций пол-л€
		
		//строим массив операций вх и вых
		$sql='select * from action_log where (pdate between "'.$pdate1.'" and "'.$pdate2.'") and user_subj_id="'.$user['id'].'" /*and (description="вход в систему" or description="выход из системы")*/  order by id asc';
		//echo $sql;
		$set=new mysqlset($sql);
		$rc=$set->getResultNumRows();
		$rs=$set->getResult();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$in_out=(int)($f['description']=="вход в систему");
			
			if(($f['description']=="вход в систему")||($f['description']=="выход из системы")){
				 $arr=array(
					'pdate'=>$f['pdate'],
					'ip'=>$f['ip'],
					'in_out'=>$in_out
				);
				
				$_operations[]= $arr;
			}
			
			
			$_all_operations[]=$f;
			
			
		}
		
		
		//разбиваем массив операций по датам
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
		
		
		//проходим массив операций по датам...
		foreach($_dates_operations as $do_k=>$do_v){
			$day_time=0;
			$curr=$do_v['pdate'];
			$ssrok='';
			
			
			$_day_was_operated=false;
			
			//проходим массив операций за текущую дату
			foreach($do_v['operations'] as $k=>$v){
				
				if($v['in_out']==1){
					//нашли операцию входа
					$ssrok.='вход: '.date("d.m.Y H:i:s",$v['pdate']).'<br />';
					
					$current_ip=$v['ip'];
					
					
					//ищем следующий выход с данным айпи
					
					$next_operation=$this->GetNextOpOut($k, $current_ip, $do_v['operations'] , $next_index);
					
					$next_operation_in=$this->GetNextOpIn($k, $current_ip,  $do_v['operations'] , $next_index);
					

										
					//найдем следующий вход с данным айпи.
					//если выход не обнаружен, учитываем следующий вход
					//print($next_operation['pdate']
					if(($next_operation===NULL)&&($next_operation_in['in_out']==1)){
						//echo 'zzzzzzzzzz';
						 $next_operation=$next_operation_in;
						
						
						
					}
					
					
					//выхода не обгнаружено, найден другой вход
					if(($next_operation!==NULL)&&($next_operation['in_out']==1)){
						//это выход, учитываем сессию
						$_day_was_operated=$_day_was_operated||true;
						$day_time+=($next_operation['pdate']-$v['pdate']);
						
						$ssrok.='выход не зафиксирован, учтен следующий вход: '.date("d.m.Y H:i:s",$next_operation['pdate']).'<br />';
					
						
					
					
					}
					
					elseif(($next_operation!==NULL)&&($next_operation['in_out']==0)){
						//это выход, учитываем сессию
						$_day_was_operated=$_day_was_operated||true;
						$day_time+=($next_operation['pdate']-$v['pdate']);
						
						$ssrok.='выход: '.date("d.m.Y H:i:s",$next_operation['pdate']).'<br />';
					
						
					
					
					}else{
						//след. операции после входа нет!
						//проверить первую запись в след. дату, везде учитывать айпи:
								
						
					/*	if(date('d.m.Y',$v['pdate'])=='14.04.2014'){
							//print_r($next_operation_in);	
							echo date('d.m.Y H:i:s  ',$v['pdate']);
							echo ' vs. ';
							echo date('d.m.Y H:i:s  ',$next_operation_in['pdate']).
							'<br>';
						}					
					*/
										
								$other_operation=$this->FindOutherOperation($v['pdate'], $curr+$step,  $v['ip'], $_all_operations, $index);
								if($other_operation!==NULL){
									$ssrok.='последн€€ операци€: '.date("d.m.Y H:i:s",$other_operation['pdate']).'<br />';
									$_day_was_operated=$_day_was_operated||true;
									$day_time+=($other_operation['pdate']-$v['pdate']);
									
									//print_r($other_operation);
									
									
								}else{
									$ssrok.='выход: не зафиксирован'.'<br />';
								}
								
								
								
					
					}
					
					
					
				}// не вход, не делаем ничего
				
			
			}//конец цикла по операци€м
			
			
			if($day_time>0){
				 $psrok=''.round(($day_time/60/60),2).' час.';
				
			}else $psrok='-';	
			
			
			$dates[]=array(
				'pdate'=>date("d.m.Y",$curr),
				'ptimes'=>$ssrok,
				'psrok'=>$psrok
			);
			
			$all_time+=$day_time;
			
			
			if($_day_was_operated) $count_of_worked_days++;
		}
		
		
		
		
		
		
		$sm->assign('works',$dates);
		
		$total=round(($all_time/60/60),2);
		$sm->assign('total', $total.' час.');
		
		/*echo '<pre>';
		print_r($dates);
		echo '</pre>';*/
		
		
		
	
		//
		//заполним шаблон пол€ми
	
		$fields=$dec2->GetUris();
		$is_active=0; $print=0;
		foreach($fields as $k=>$v){
			if(($v->GetName()=='is_active')&&($v->GetValue()==1)) $is_active=1;
			$sm->assign($v->GetName(),$v->GetValue());	
			if(($v->GetName()=='print')&&($v->GetValue()==1)) $print=1;
		}
		
		
		//системный журнал пользовател€
		
		$log=new ActionLog;
		
		if($print==1) $llg=$log->ShowLog('syslog/log_print.html',$dec,$from,$to_page,'users_activity.php');
		else $llg=$log->ShowLog('syslog/log_id.html',$dec,$from,$to_page,'users_activity.php');
	
		
		
		
		//пользователи
		$users=$ug->GetItemsByFieldsArr(array('is_active'=>$is_active, 'group_id'=>1)); //GetItemsArr($fields['user'],$is_active);
		
		//var_dump($fields['user']);
		
		$u_ids=array(); $u_values=array();
		//$u_ids[]='0';
		//$u_values[]='-выберите-';
		foreach($users as $k=>$v){
			$u_ids[]=$v['login'];
			
				$u_values[]=stripslashes($v['login']).' '.stripslashes($v['name_s']);	
			
			
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
		
		//ссылка дл€ кнопок сортировки
		$link=$dec->GenFltUri();
		$link='users_activity.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	//след. операцию c данным айпи:
	protected function GetNextOp($current_op_index, $ip, $ops_data, &$next_index){
		$res=NULL;
		
		//$ops_data - массив операций в эту дату со всеми ip
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
	
	
	//след. операцию входа c данным айпи:
	protected function GetNextOpIn($current_op_index, $ip, $ops_data, &$next_index){
		$res=NULL;
		
		//$ops_data - массив операций в эту дату со всеми ip
		foreach($ops_data as $k=>$v){
			if(($ip==$v['ip'])&&($v['in_out']==1)){
				if($k>$current_op_index){
					
					$res=$v;
					$next_index=$k;
					break;
						
				}
				
			}
				
		}
		
		
		return $res;
	}
	
	
	//след. операцию выхода c данным айпи:
	protected function GetNextOpOut($current_op_index, $ip, $ops_data, &$next_index){
		$res=NULL;
		
		//$ops_data - массив операций в эту дату со всеми ip
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
	
	//послед. операцию c данным айпи:
	protected function GetLastOp($current_op_index, $ip, $ops_data, &$last_index){
		$res=NULL;
		
		//$ops_data - массив операций в эту дату со всеми ip
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
	
	
	//первую запись в след. дату, везде учитывать айпи
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
	
	
	
	//ищем последнюю другую не входную операцию
	protected function FindOutherOperation($current_date, $end_pdate, $ip, $data, &$its_index){
		$res=NULL;
		
		foreach($data as $k=>$v){
			if(($v['pdate']>=$current_date)&&($v['pdate']<$end_pdate)&&($v['ip']==$ip)&&($v['description']!="вход в систему")){
				$res=$v;
				$its_index=$k;
				//break;
			}
		}
		
		return $res;
	}
}
?>