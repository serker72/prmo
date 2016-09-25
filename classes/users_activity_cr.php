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
		
		$_all_operations=array(); //массив вообще всех операций пол-ля
		
		//строим массив операций вх и вых
		$set=new mysqlset('select * from action_log where (pdate between "'.$pdate1.'" and "'.$pdate2.'") and user_subj_id="'.$user['id'].'" /*and (description="вход в систему" or description="выход из системы")*/  order by id asc');
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
					
					$next_operation=$this->GetNextOpOut($k, $current_ip, $do_v['operations'] , &$next_index);
					
					if(($next_operation!==NULL)&&($next_operation['in_out']==0)){
						//это выход, учитываем сессию
						$_day_was_operated=$_day_was_operated||true;
						$day_time+=($next_operation['pdate']-$v['pdate']);
						
						$ssrok.='выход: '.date("d.m.Y H:i:s",$next_operation['pdate']).'<br />';
					
					
					
					//проверяем след. операцию c данным айпи:
					/*$next_operation=$this->GetNextOp($k, $current_ip, $do_v['operations'] , &$next_index);
					
					if(($next_operation!==NULL)&&($next_operation['in_out']==0)){
						//это выход, учитываем сессию
						$_day_was_operated=$_day_was_operated||true;
						$day_time+=($next_operation['pdate']-$v['pdate']);
						
						$ssrok.='выход: '.date("d.m.Y H:i:s",$next_operation['pdate']).'<br />';
					
					}elseif(($next_operation!==NULL)&&($next_operation['in_out']==1)){
						
						//это другой вход, ищем последнюю операцию с этого айпи в этот день
						$set=new mysqlset('select * from action_log where (pdate between "'.$v['pdate'].'" and "'.($curr+$step).'") and ip="'.$v['ip'].'" and user_subj_id="'.$user['id'].'" and description<>"вход в систему" order by id desc limit 1');	
						
						$rc=$set->getResultNumRows();
						$rs=$set->getResult();
						//$ssrok.='вход: '.date("d.m.Y H:i:s",$v['pdate']);
						
						if($rc>0){
							$f=mysqli_fetch_array($rs);	
							$ssrok.='последняя операция: '.date("d.m.Y H:i:s",$f['pdate']).'<br />';
							$_day_was_operated=$_day_was_operated||true;
							$day_time+=($f['pdate']-$v['pdate']);
						}
						
						*/
					}else{
						//след. операции после входа нет!
						//проверить первую запись в след. дату, везде учитывать айпи:
						/*$first_operation=$this->GetFirstOpNextDate($do_k, $current_ip, $_dates_operations, $its_index);
						
						
						if($first_operation===NULL){
							//первой записи в след. дату нет!
							//ничего не учитываем!
						}else{
							//если первая операция в др. дату - это выход, то учитываем разницу дат
							//и учитываем эту сессию
							//если не выход - то ищем любую другую не входную операцию
							//if($_dates_operations[$do_k+1]['operations'][0]['
							if($first_operation['in_out']==0){
								//учитываем эту сессию
								$_day_was_operated=$_day_was_operated||true;
								$day_time+=$first_operation['pdate']-$v['pdate'];
								
								$ssrok.='выход на другой день: '.date("d.m.Y H:i:s",$first_operation['pdate']).'<br />';
							}else{*/
							
								//ищем любую другую не входную операцию
								
								/*$set=new mysqlset('select * from action_log where (pdate between "'.$v['pdate'].'" and "'.($curr+$step).'") and ip="'.$v['ip'].'" and user_subj_id="'.$user['id'].'" and description<>"вход в систему" order by id desc limit 1');
								
								$rc=$set->getResultNumRows();
								$rs=$set->getResult();
								//$ssrok.='вход: '.date("d.m.Y H:i:s",$v['pdate']);
								
								if($rc>0){
									$f=mysqli_fetch_array($rs);	
									$ssrok.='последняя операция: '.date("d.m.Y H:i:s",$f['pdate']).'<br />';
									$_day_was_operated=$_day_was_operated||true;
									$day_time+=($f['pdate']-$v['pdate']);
								}else{
									$ssrok.='выход: не зафиксирован'.'<br />';
								} */
								
								
								
								
								$other_operation=$this->FindOutherOperation($v['pdate'], $curr+$step,  $v['ip'], $_all_operations, $index);
								if($other_operation!==NULL){
									$ssrok.='последняя операция: '.date("d.m.Y H:i:s",$other_operation['pdate']).'<br />';
									$_day_was_operated=$_day_was_operated||true;
									$day_time+=($other_operation['pdate']-$v['pdate']);
									
									//print_r($other_operation);
									
									
								}else{
									$ssrok.='выход: не зафиксирован'.'<br />';
								}
								
								
								
								
							/*}
								
						}*/
					
					}
					
					
					
				}// не вход, не делаем ничего
				
			
			}//конец цикла по операциям
			
			
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
		
		
		
		/*echo '<pre>';
		print_r($dates);
		echo '</pre>';*/
		
		
		
		
		$sm->assign('works',$dates);
		
		
		
		$sm->assign('total', round(($all_time/60/60),2).' час.');
		
		
		echo 'Проработал дней в программе '.$count_of_worked_days.'<br>';
		
		echo 'Всего часов в программе '.round(($all_time/60/60),2).'<br>';
		
		echo 'Среднее время работы в день, час '.(round(($all_time/60/60)/$count_of_worked_days,2)).'<br>';
		
		
		
		/*echo '<pre>';
		print_r($dates);
		echo '</pre>';*/
		
		
		//заполним шаблон полями
	
		$fields=$dec2->GetUris();
		$is_active=0; $print=0;
		foreach($fields as $k=>$v){
			if(($v->GetName()=='is_active')&&($v->GetValue()==1)) $is_active=1;
			$sm->assign($v->GetName(),$v->GetValue());	
			if(($v->GetName()=='print')&&($v->GetValue()==1)) $print=1;
		}
		
		
		//системный журнал пользователя
		
		$log=new ActionLog;
		
		//if($print==1) $llg=$log->ShowLog('syslog/log_print.html',$dec,$from,$to_page,'users_activity.php');
		//else $llg=$log->ShowLog('syslog/log.html',$dec,$from,$to_page,'users_activity.php');
	
		
		
		
		
		//пользователи
		$users=$ug->GetItemsArr($fields['user'],$is_active);
		
		$u_ids=array(); $u_values=array();
		//$u_ids[]='0';
		//$u_values[]='-выберите-';
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
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='users_activity_cr.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
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
	//							$set=new mysqlset('select * from action_log where (pdate between "'.$v['pdate'].'" and "'.($curr+$step).'") and ip="'.$v['ip'].'" and user_subj_id="'.$user['id'].'" and description<>"вход в систему" order by id desc limit 1');
}
?>