<?
 
require_once('abstractitem.php');
require_once('schednotesgroup.php');
require_once('supplieritem.php');
require_once('suppliercontactitem.php');
require_once('suppliercontactgroup.php');
require_once('suppliercontactdatagroup.php');
require_once('user_s_group.php');
require_once('supplier_cities_group.php');

require_once('sched_field_rules.php');

require_once('sched_history_group.php');

require_once('authuser.php');
require_once('discr_man.php');



require_once('sched_view1.class.php');
require_once('sched_view2.class.php');
require_once('sched_view3.class.php');
require_once('sched_view4.class.php');
require_once('sched_view5.class.php');

require_once('holy_dates.php');

//библиотека классов планировщика


//абстрактная запись планировщика

class Sched_AbstractItem extends AbstractItem{
	public $kind_id=1;
	protected function init(){
		$this->tablename='sched';
		$this->item=NULL;
		$this->pagename='ed_sched.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function Add($params){
		/**/
		$digits=5;
		
		switch($params['kind_id']){
			case 1:
				$begin='З';
			break;
			case 2:
				$begin='КМ';
			break;
			case 3:
				$begin='ВС';
			break;
			case 4:
				$begin='ЗВ';
			break;
			case 5:
				$begin='ЗМ';
			break;
			default:
				$begin='З';
			break;
				
			
		}
		
		$sql='select max(code) from '.$this->tablename.' where kind_id="'.$params['kind_id'].'" and code REGEXP "^'.$begin.'[0-9]+"';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			$f=mysqli_fetch_array($rs);	
			
			//$f1=mysqli_fetch_array($rs1);	
			
			//echo $f[0];
			eregi($begin."([[:digit:]]{".$digits."})",$f[0],$regs);
			//print_r($regs);
			
			
			$number=(int)$regs[1];
			//print_r($regs); die();
			$number++;
			
			$test_login=$begin.sprintf("%0".$digits."d",$number);
			
			 
			$login=$test_login;
		}else{
			
			//$f=mysqli_fetch_array($rs);	
			$login=$begin.sprintf("%0".$digits."d",1);
			
		 
		}
		
		// echo $login; die();
		
		$params['code']=$login;
		
		
		return AbstractItem::Add($params);
		
	}
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		 
		return $can;
	}
	
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		 
		
		return $can;
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		 
	}
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Задача, статус '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Задача '.$item['code'].', статус '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		return date('d.m.Y', $item['pdate_beg']);
	}
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	//получить адресата или их список
	public function ConstructContacts($id, $item=NULL){
		
	}
	
	//статусы, доступные к переходу
	public function GetStatuses($current_id=0){
		 
		$arr=Array();
		 $set=new MysqlSet('select * from document_status where id in(9,10,3) order by  id asc');
		 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	 
	}
	
	
	//получить список пол-лей, кто видит заметку
	public function GetUsersArr($id, $item=NULL){
		$_ug=new Sched_UserGroup;
		return $_ug->GetItemsByIdArr($id, $item);
	}
	public function GetUserIdsArr($id, $right_id=NULL){
		$_ug=new Sched_UserGroup;
		return $_ug->GetItemsIdsById($id, $right_id);
	}
	
	
	//правка состава пол-лей, кто видит
	//добавим позиции
	public function AddUsers($current_id, array $positions,  $result=NULL){
		$_kpi=new Sched_UserItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetUsersArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'user_id'=>$v['user_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['right_id']=$v['right_id'];
			 
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'right_id'=>$v['right_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['right_id']=$v['right_id'];
				
				 
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				if($kpi['right_id']!=$add_array['right_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'right_id'=>$v['right_id'] 
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['sched_id']==$v['sched_id'])&&($vv['user_id']==$v['user_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'right_id'=>$v['right_id'] 
			);
			
			//удаляем позицию
			$_kpi->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
}


/*************************************************************************************************/
//командировка
class Sched_MissionItem extends Sched_AbstractItem{
	public $kind_id=2;
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	

	//запрос о возможности  утв прием и возвращение причины, почему нельзя 
	public function DocCanConfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки утверждено принятие';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_done']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки не утверждено выполнение';
		 
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв приема и возвращение причины, почему нельзя 
	public function DocCanUnconfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки не утверждено принятие';
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у командировки не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у командировки утверждено выполнение';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			 
		}
		
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirm_done']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки утверждено выполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки не утверждено заполнение';
		}
		
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShipByResults($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		 
			
		//проверить, чтобы по всем контрагентам была вписан результат 10 символов либо стояла галочка	
		$_sg=new Sched_SupplierGroup;
		$sups=$_sg->GetItemsByIdArr($id);
		
		foreach($sups as $k=>$v){
			if(($v['not_meet']==0)&&(strlen($v['result'])<10))	{
				$can=$can&&false;	
				
				$reasons[]='не указан результат по контрагенту '.$v['opf_name'].' '.$v['full_name'];
				$reason.=implode(', ',$reasons);
			}
		}
		
		return $can;
	}
	

	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_done']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у командировки не утверждено выполнение';
			$reason.=implode(', ',$reasons);
		} 
		elseif($item['is_fulfiled']!=0){
			$can=$can&&false;
			$reasons[]='у командировки утверждено принятие работы';
			$reason.=implode(', ',$reasons);
		}
		

		
		return $can;
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
		if($item['plan_or_fact']==0){
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 22
					$setted_status_id=22;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//смена статуса на 26
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=22;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)){
					//смена статуса на 10
					$setted_status_id=10;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)){
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}

			
			
		}elseif($item['plan_or_fact']==1){
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 2
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//смена статуса на 26
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			 
			
			}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)){
					//смена статуса на 10
					$setted_status_id=10;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)){
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса командировки',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}

		}
		
		
		//die();
	}
	
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.='Командировка, статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Sched_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$res.='Командировка '.$item['code'].', статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Sched_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
		$_sg=new Sched_CityGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', город '.$v['name'].', '.$v['okrug_name'].', '.$v['region_name'].', '.$v['country_name'];	
		}
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
		$res.=$item['pdate_beg'].'T'.$item['ptime_beg'];
		
		//if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
		
		//return date('d.m.Y', $item['pdate_beg']);
	}
	
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	
	public function ConstructContacts($id, $item=NULL){
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		 
				 
		
			$_addr=new SchedContactItem;
			$addr=$_addr->GetItemByFields(array('sched_id'=>$id));
			
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($addr['contact_id']);
			$res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$addr['value'];
		
		 
		
		
		return $res;
	}
	
	 
}






/*************************************************************************************************/
//встреча
class Sched_MeetItem extends Sched_AbstractItem{
	public $kind_id=3;
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	//запрос о возможности  утв прием и возвращение причины, почему нельзя 
	public function DocCanConfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи утверждено принятие';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_done']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи не утверждено выполнение';
		 
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв приема и возвращение причины, почему нельзя 
	public function DocCanUnconfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи не утверждено принятие';
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}

	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у встречи не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у встречи утверждено выполнение';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			 
		}
		
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirm_done']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи утверждено выполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_done']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у встречи не утверждено выполнение';
			$reason.=implode(', ',$reasons);
		} 
		elseif($item['is_fulfiled']!=0){
			$can=$can&&false;
			$reasons[]='у встречи утверждено принятие работы';
			$reason.=implode(', ',$reasons);
		}
		

		
		return $can;
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
		if($item['plan_or_fact']==0){
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 22
					$setted_status_id=22;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//смена статуса на 26
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=22;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
				
			}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)){
					//смена статуса на 10
					$setted_status_id=10;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)){
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}

			
			
		}elseif($item['plan_or_fact']==1){
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 2
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//смена статуса на 26
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)){
					//смена статуса на 10
					$setted_status_id=10;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)){
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса встречи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}

		}
		
		
		//die();
	}
	
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$_kind=new Sched_KindMeetItem;
		$kind=$_kind->GetItemById($item['meet_id']);
		 
		$res.='Встреча, вид: '.$kind['name'].', статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Sched_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		$_kind=new Sched_KindMeetItem;
		$kind=$_kind->GetItemById($item['meet_id']);
		 
		$res.='Встреча '.$item['code'].', вид: '.$kind['name'].', статус '.$stat['name'];
		
		//список к-тов
		$_sg=new Sched_SupplierGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', контрагент '.$v['opf_name'].' '.$v['full_name'];	
		}
		
		//список городов
		$_sg=new Sched_CityGroup;
		$sg=$_sg->GetItemsByIdArr($id);
		foreach($sg as $k=>$v){
			$res.=', город '.$v['name'].', '.$v['okrug_name'].', '.$v['region_name'].', '.$v['country_name'];	
		}
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
		$res.=$item['pdate_beg'].'T'.$item['ptime_beg'];
		
		//if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
		
		//return date('d.m.Y', $item['pdate_beg']);
	}
	
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	
	public function ConstructContacts($id, $item=NULL){
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		 
				 
		
			$_addr=new SchedContactItem;
			$addr=$_addr->GetItemByFields(array('sched_id'=>$id));
			
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($addr['contact_id']);
			$res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$addr['value'];
		
		 
		
		
		return $res;
	}
	
	 
}












/*************************************************************************************************/
//звонок
class Sched_CallItem extends Sched_AbstractItem{
	public $kind_id=4;
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у звонка не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у звонка утверждено выполнение';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у звонка утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			 
		}
		
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirm_done']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у звонка утверждено выполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у звонка не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_done']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у звонка не утверждено выполнение';
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
		if($item['plan_or_fact']==0){
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 22
					$setted_status_id=22;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//смена статуса на 10
					$setted_status_id=10;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=22;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}
			
			
		}elseif($item['plan_or_fact']==1){
			if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 2
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
			}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//смена статуса на 10
					$setted_status_id=10;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=2;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса звонка',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}
		}
		
		
		//die();
	}
	
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		if($item['incoming_or_outcoming']==0){
			$res.='Входящий звонок';
		}else{
			$res.='Исходящий звонок';
		}
		
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.=', статус '.$stat['name'];
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); 
		$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		if($item['incoming_or_outcoming']==0){
			$res.='Входящий звонок';
		}else{
			$res.='Исходящий звонок';
		}
		
		$res.=' '.$item['code'].', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
		$res.=$item['pdate_beg'].'T'.$item['ptime_beg'];
		
		//if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
		
		//return date('d.m.Y', $item['pdate_beg']);
	}
	
	
	public function ConstructEndDate($id, $item=NULL){
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
	 	if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
	}
	
	
	public function ConstructContacts($id, $item=NULL, $do_href=false){
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		if($item['contact_mode']==0){
				 
		
			$_addr=new SchedContactItem;
			$addr=$_addr->GetItemByFields(array('sched_id'=>$id));
			
			
			$_si=new SupplierItem; $_sci=new SupplierContactItem; $_opf=new OpfItem;
			
			$si=$_si->getitembyid($addr['supplier_id']); $opf=$_opf->GetItemById($si['opf_id']);
			$sci=$_sci->getitembyid($addr['contact_id']);
			if(!$do_href) $res=SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$addr['value'];
			else  $res='<a href="supplier.php?action=1&id='.$addr['supplier_id'].'" target="_blank">'.SecStr($opf['name'].' '.$si['full_name'].', '.$sci['name'].', '.$sci['position']).': '.$addr['value'].'</a>';

		
		}else{
			$res=$item['contact_name'].'  '.$item['contact_value'];
		}
		
		
		return $res;
	}
	
	//статусы, доступные к переходу
	/*public function GetStatuses($current_id=0){
		
	}*/
}



/**************************************************************************************************/

//заметка
class Sched_NoteItem extends Sched_AbstractItem{
	public $kind_id=5;
	
	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); //$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.='Заметка "'.$item['topic'].'" ';
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); //$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		$res.='Заметка '.$item['code'].' "'.$item['topic'].'" ';
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['note_is_actual']!=1){
			
			$can=$can&&false;
			$reasons[]='заметка уже отмечена неактуальной';
			$reason.=implode(', ',$reasons);
		} 
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['note_is_actual']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='заметка уже отмечена актуальной';
			$reason.=implode(', ',$reasons);
		}else{
			 
		}
		
		return $can;
	}
	
}







/**************************************************************************************************/

//Задача
class Sched_TaskItem extends Sched_AbstractItem{
	public $kind_id=1;
	
	protected function init(){
		$this->tablename='sched';
		$this->item=NULL;
		$this->pagename='ed_sched_task.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	
	public function ConstructName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); //$stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		 
		//$res.=', контакт: '.$this->ConstructContacts($id, $item).', статус '.$stat['name'];
		$res.='Задача '.$item['code'].' "'.$item['topic'].'" ';
		
		
		return $res; //', статус '.$stat['name'];
	}
	
	
	public function ConstructFullName($id, $item=NULL){
		 $_stat=new DocStatusItem;
		
		if($item===NULL) $item=$this->getitembyid($id); $stat=$_stat->getitembyid($item['status_id']);
		
		$res='';
		$res.='Задача '.$item['code'].', Тема: "'.$item['topic'].'"'; //, статус '.$stat['name'];
		
		
		return $res; //
	}
	
	
	
	

	
	public function ConstructBeginDate($id, $item=NULL){
		 
		
		if($item===NULL) $item=$this->getitembyid($id);  
		
		$res='';
		
		$res.=$item['pdate_beg'].'T'.$item['ptime_beg'];
		
		//if($item['pdate_end']!="") $res.=$item['pdate_end'].'T'.$item['ptime_end'];
		
		return $res; 
		
		//return date('d.m.Y', $item['pdate_beg']);
	}
	
	
	
	
	//правка состава пол-лей, кто видит
	//добавим позиции
	public function AddKindUsers($current_id, $kind_id, array $positions,  $result=NULL){
		$_kpi=new Sched_TaskUserItem; $_kpg=new Sched_TaskUserGroup;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$_kpg->GetItemsByIdArr($current_id, $kind_id); //>GetUsersArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'user_id'=>$v['user_id'], 'kind_id'=>$kind_id));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['kind_id']=$v['kind_id'];
			 
				$add_array['was_informed']=0;
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'kind_id'=>$v['kind_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['user_id']=$v['user_id'];
				
				$add_array['kind_id']=$v['kind_id'];
				
				$add_array['was_informed']=1;
				
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				if($kpi['right_id']!=$add_array['right_id']) $to_log=$to_log||true;
				if($kpi['kind_id']!=$add_array['kind_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'kind_id'=>$v['kind_id'] 
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['sched_id']==$v['sched_id'])&&($vv['user_id']==$v['user_id'])&&($vv['kind_id']==$v['kind_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					'sched_id'=>$v['sched_id'],
					'user_id'=>$v['user_id'],
					'kind_id'=>$v['kind_id'] 
			);
			
			//удаляем позицию
			$_kpi->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL, $result=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth(false,false);
		
		
		$_dsi=new DocStatusItem;
		if($item['status_id']!=18){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
			//контроль спецправ, либо числа комментариев
			if(!$au->user_rights->CheckAccess('w',946)){
				$_hg=new Sched_HistoryGroup;
				$cou=$_hg->CountHistory($id);
				if($cou>0) {
					$can=$can&&false;
					$reasons[]='по задаче написано '.$cou.' комментариев';
					$reason.=implode(', ',$reasons);
				}
			}
			
		}
		
		return $can;
	}

	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	//Запрос о возм снятия утв цен
	public function DocCanUnconfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у задачи не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirm_done']==1){
			
			$can=$can&&false;
			$reasons[]='у задачи утверждено выполнение';
			$reason.=implode(', ',$reasons);
		 
		}
		
		return $can;
	}
	
	//запрос о возм утв цен
	public function DocCanConfirmPrice($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			 
		}
		
		return $can;
	}
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirm_done']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи утверждено выполнение';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_done']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи не утверждено выполнение';
			$reason.=implode(', ',$reasons);
		} 
		elseif($item['is_fulfiled']!=0){
			$can=$can&&false;
			$reasons[]='у задачи утверждено принятие работы';
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	//запрос о возможности  утв отгр и возвращение причины, почему нельзя 
	public function DocCanConfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи утверждено принятие';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_done']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи не утверждено выполнение';
		 
		}elseif($item['is_confirmed']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи не утверждено заполнение';
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв отгр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmFulfil($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_fulfiled']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у задачи не утверждено принятие';
			$reason.=implode(', ',$reasons);
		} 
		
		return $can;
	}
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		 
		
		AbstractItem::Edit($id, $params);
		
		//перехватывать состояния, рассылать письма
		//была не утверждена (заполнение) - утвердили
		if(isset($params['is_confirmed'])&&isset($item['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			//отправить письма исполнителям, ответственным
			//найдем отв, соисполнителей
			$users_to_send=array();
			$sql='select * from user where is_active=1 and id in( select distinct user_id from sched_task_users where kind_id in(2,3) and sched_id="'.$id.'")';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			$users_to_send=array();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$users_to_send[]=$f;
			}
			$topic='Новая задача в GYDEX.Планировщик';
			$_mi=new MessageItem;  
			foreach($users_to_send as $k1=>$user){
				
				$txt='<div>';
				$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
				$txt.=' </div>';
				
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>';
				$txt.='Уважаемый(ая) '.$user['name_s'].'!';
				$txt.='</div>';
				$txt.='<div>&nbsp;</div>';
				
				
				$txt.='<div>';
				$txt.='<strong>В GYDEX.Планировщик у Вас появилась новая задача:</strong>';
				$txt.='</div>';
				
				$txt.='<div>&nbsp;</div><ul>';
				
				$txt.='<li>';
				$txt.='<strong><a href="ed_sched_task.php?action=1&id='.$id.'" target="_blank">'.$this->ConstructFullName($id, $item).'</a></strong>';
				if($item['pdate_beg']!="") $txt.=',<strong> крайний срок:</strong> <em>'.DateFromYmd($item['pdate_beg']).' '.$item['ptime_beg'].'</em>';
				$txt.=', <strong>Ваша роль:</strong> <em>';
				
				//найдем роли...
				$sql2=' select distinct k.kind_id, p.name 
				from sched_task_users as k
				inner join sched_task_users_kind as p on p.id=k.kind_id
				where k.sched_id="'.$id.'" and k.user_id="'.$user['id'].'"
				order by k.kind_id';
				
				//echo $sql2;
				
				$set2=new mysqlset($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				
				
				$roles=array();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					$roles[]=$h['name'];
					
					
					
				}
				
				$txt.=implode(', ', $roles);
				
				$txt.='</em></li>';
				
				$txt.='</ul><div>&nbsp;</div>';
	
				$txt.='<div><strong>Просим своевременно выполнять все поставленные задачи!</strong></div>';
				
				
				$txt.='<div>&nbsp;</div>';
			
				$txt.='<div>';
				$txt.='C уважением, программа "'.SITETITLE.'".';
				$txt.='</div>';
				
				$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
			}
		}
		
		////была не выполнена- выполнили (2я галочка)
		if(isset($params['is_confirmed_done'])&&isset($item['is_confirmed_done'])&&($params['is_confirmed_done']==1)&&($item['is_confirmed_done']==0)){
			//если требует проверки - то одно сообщение, если не требует - то другое
			//отправить письма постановщику задачи
			
			
			$users_to_send=array();
			$sql='select * from user where is_active=1 and id in( select distinct user_id from sched_task_users where kind_id in(1) and sched_id="'.$id.'")';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			$users_to_send=array();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$users_to_send[]=$f;
			}
			if($item['do_check']==1) $topic='Проверьте выполнение задачи в GYDEX.Планировщик';
			else $topic='Ваша задача в GYDEX.Планировщик выполнена';
			
			$_mi=new MessageItem;  
			foreach($users_to_send as $k1=>$user){
				
				$txt='<div>';
				$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
				$txt.=' </div>';
				
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>';
				$txt.='Уважаемый(ая) '.$user['name_s'].'!';
				$txt.='</div>';
				$txt.='<div>&nbsp;</div>';
				
				
				$txt.='<div>';
				if($item['do_check']==1) $txt.='<strong>Ваша задача выполнена. Пожалуйста, проверьте задачу и утвердите прием работы:</strong>';
				else $txt.='<strong>Ваша задача в GYDEX.Планировщик выполнена:</strong>';
				$txt.='</div>';
				
				$txt.='<div>&nbsp;</div><ul>';
				
				$txt.='<li>';
				$txt.='<a href="ed_sched_task.php?action=1&id='.$id.'" target="_blank">'.$this->ConstructFullName($id, $item).'</a>';
				if($item['pdate_beg']!="") $txt.=', <strong>крайний срок:</strong> <em>'.DateFromYmd($item['pdate_beg']).' '.$item['ptime_beg'].'</em>';
				$txt.=', <strong>Ваша роль:</strong> <em>';
				
				//найдем роли...
				$sql2=' select distinct k.kind_id, p.name 
				from sched_task_users as k
				inner join sched_task_users_kind as p on p.id=k.kind_id
				where k.sched_id="'.$id.'" and k.user_id="'.$user['id'].'"
				order by k.kind_id';
				
				//echo $sql2;
				
				$set2=new mysqlset($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				
				
				$roles=array();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					$roles[]=$h['name'];
					
					
					
				}
				
				$txt.=implode(', ', $roles);
				
				$txt.='</em></li>';
				
				$txt.='</ul><div>&nbsp;</div>';
	
			 	if($item['do_check']==1) $txt.='<div><strong>Пожалуйста, подтвердите правильность выполнения задачи!</strong></div>';
				
				
				$txt.='<div>&nbsp;</div>';
			
				$txt.='<div>';
				$txt.='C уважением, программа "'.SITETITLE.'".';
				$txt.='</div>';
				
				$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
			}
		}
		
		////отправили на доработку (снята 2я галочка, установлен статус 23)
		if(isset($params['is_confirmed_done'])&&isset($item['is_confirmed_done'])&&($params['is_confirmed_done']==0)&&($item['is_confirmed_done']==1)&&($params['status_id']==23)&&($item['status_id']==26)){
			//отправить письма исполнителям, ответственным
			//найдем отв, соисполнителей
			$users_to_send=array();
			$sql='select * from user where is_active=1 and id in( select distinct user_id from sched_task_users where kind_id in(2,3) and sched_id="'.$id.'")';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			$users_to_send=array();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$users_to_send[]=$f;
			}
			$topic='Ваша задача в GYDEX.Планировщик отправлена на доработку';
			$_mi=new MessageItem;  
			foreach($users_to_send as $k1=>$user){
				
				$txt='<div>';
				$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
				$txt.=' </div>';
				
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>';
				$txt.='Уважаемый(ая) '.$user['name_s'].'!';
				$txt.='</div>';
				$txt.='<div>&nbsp;</div>';
				
				
				$txt.='<div>';
				$txt.='<strong>В GYDEX.Планировщик Ваша задача отправлена на доработку:</strong>';
				$txt.='</div>';
				
				$txt.='<div>&nbsp;</div><ul>';
				
				$txt.='<li>';
				$txt.='<a href="ed_sched_task.php?action=1&id='.$id.'" target="_blank">'.$this->ConstructFullName($id, $item).'</a>';
				if($item['pdate_beg']!="") $txt.=', <strong>крайний срок:</strong> <em>'.DateFromYmd($item['pdate_beg']).' '.$item['ptime_beg'].'</em>';
				$txt.=', <strong>Ваша роль:</strong> <em>';
				
				//найдем роли...
				$sql2=' select distinct k.kind_id, p.name 
				from sched_task_users as k
				inner join sched_task_users_kind as p on p.id=k.kind_id
				where k.sched_id="'.$id.'" and k.user_id="'.$user['id'].'"
				order by k.kind_id';
				
				//echo $sql2;
				
				$set2=new mysqlset($sql2);
				$rs2=$set2->GetResult();
				$rc2=$set2->GetResultNumRows();
				
				
				$roles=array();
				for($k=0; $k<$rc2; $k++){
					$h=mysqli_fetch_array($rs2);
					$roles[]=$h['name'];
					
					
					
				}
				
				$txt.=implode(', ', $roles);
				
				$txt.='</em></li>';
				
				$txt.='</ul><div>&nbsp;</div>';
	
				$txt.='<div><strong>Просим своевременно внести доработки и повторно утвердить выполнение задачи!</strong></div>';
				
				
				$txt.='<div>&nbsp;</div>';
			
				$txt.='<div>';
				$txt.='C уважением, программа "'.SITETITLE.'".';
				$txt.='</div>';
				
				$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
			}
			
		}
		
		 
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
		
		//фиксация даты смены статуса
		if(isset($params['status_id'])&&isset($item['status_id'])&&($params['status_id']!=$item['status_id'])){
			AbstractItem::Edit($id, array('pdate_status_change'=>time()));
			
			
			if(($item['status_id']==24)&&($params['status_id']!=24)){
				$_wi=new Sched_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>1, 'pdate'=>time()));
					
			}elseif(($item['status_id']!=24)&&($params['status_id']==24)){
				$_wi=new Sched_WorkingItem;
				$_wi->Add(array('sched_id'=>$id, 'in_or_out'=>0, 'pdate'=>time()));
			}
				
		}
	}
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth(false,false);
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$setted_status_id=$item['status_id'];
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
				if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)){
					//смена статуса на 23
					$setted_status_id=23;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)){
					//смена статуса на 18
					$setted_status_id=18;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
		}elseif(isset($new_params['is_confirmed_done'])&&isset($old_params['is_confirmed_done'])){
			
				if(($new_params['is_confirmed_done']==1)&&($old_params['is_confirmed_done']==0)){
					//либо 10, либо 26
					if($item['do_check']==1) $setted_status_id=26;
					else $setted_status_id=10;
					
					
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_confirmed_done']==0)&&($old_params['is_confirmed_done']==1)){
					$setted_status_id=24;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			 
			
		}elseif(isset($new_params['is_fulfiled'])&&isset($old_params['is_fulfiled'])){
			
				if(($new_params['is_fulfiled']==1)&&($old_params['is_fulfiled']==0)){
					//
					$setted_status_id=10;
					
					
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
					
				}elseif(($new_params['is_fulfiled']==0)&&($old_params['is_fulfiled']==1)){
					$setted_status_id=26;
					$this->Edit($id,array('status_id'=>$setted_status_id));
					
					$stat=$_stat->GetItemById($setted_status_id);
					$log->PutEntry($_result['id'],'смена статуса задачи',NULL,905,NULL,'установлен статус '.$stat['name'],$item['id']);
				}
				
			}	
		
		
		
		//die();
	}
	
	
	
}






/***********************************************************************************************/
//определение класса записи
class Sched_Resolver{
	public $instance;
	function __construct($kind_id){
		switch($kind_id){
			case 1:
				$this->instance= new Sched_TaskItem;
			break;
			case 2:
				$this->instance= new Sched_MissionItem;
			break;
			case 3:
				$this->instance= new Sched_MeetItem;
			break;
			case 4:
				$this->instance= new Sched_CallItem;
			break;
			case 5:
				$this->instance= new Sched_NoteItem;
			break;
			default:
				$this->instance=new Sched_AbstractItem;
			break;
		};
	}
	
	//public function GetInstance(){ return $this->instance; }
}





// группа записей планировщика
class  Sched_Group extends AbstractGroup {
	protected $_auth_result;
	protected $_view1,$_view2,$_view3,$_view4,$_view5;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched';
		$this->pagename='shedule.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		
		$this->_auth_result=NULL;
		$this->_view1=new Sched_View1Group;
		$this->_view2=new Sched_View2Group;
		$this->_view3=new Sched_View3Group;
		$this->_view4=new Sched_View4Group;
		$this->_view5=new Sched_View5Group;

	} 

    public function ShowPos($kind_id, //0
		$template, //1
		DBDecorator $dec, //2
		$can_edit=false,  //3
		$from, //4
		$to_page, //5
		$has_header=true,  //6
		$is_ajax=false, //7
		$can_delete=true, //8
		$can_restore=true, //9
		$can_confirm_price=true, //10
		$can_unconfirm_price=true, //11
		$can_confirm_shipping=true, //12
		$can_unconfirm_shipping=true, //13
		$can_confirm_all_shipping=false, //14
		$can_confirm_fulfil=false, //15
		
		$can_unconfirm_all_shipping=false, //16
		$can_unconfirm_fulfil=false, //17
		
		$can_unconfirm_ish_plan=false, //18
		$can_confirm_ish_fact=false, //19
		$can_confirm_vh_fact=false //20


		){
		 
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 $sm->assign('can_confirm_shipping', $can_confirm_shipping);
		 $sm->assign('can_unconfirm_shipping', $can_unconfirm_shipping);
		 
		  
		 $sm->assign('can_unconfirm_all_shipping', $can_unconfirm_all_shipping);
		 $sm->assign('can_unconfirm_fulfil', $can_unconfirm_fulfil);
		 
		 $sm->assign('can_unconfirm_ish_plan', $can_unconfirm_ish_plan);
		 $sm->assign('can_confirm_ish_fact', $can_confirm_ish_fact);
		 $sm->assign('can_confirm_vh_fact', $can_confirm_vh_fact);
		

		
		$_bng=new SchedNotesGroup;
		
		$_addr=new SchedContactItem;
		
		$_cg=new Sched_CityGroup;
		$_sg=new Sched_SupplierGroup;
		
		
		$sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			m.name as meet_name,
			
			
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login		
				
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
					 
				where '.$this->subkeyname.'="'.$kind_id.'"';
				
		$sql_count='select count(distinct p.id) 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				left join user as cr on cr.id=p.created_id
				left join user as uf on uf.id=p.user_fulfiled_id
					 
				where '.$this->subkeyname.'="'.$kind_id.'"';
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $kind_id));
		$navig->SetFirstParamName('from'.$kind_id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		
		$_available_user_ids=$this->GetAvailableUserIds($this->_auth_result['id'],false, $kind_id);
		
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//просрочено или нет
			/*
			статус != 10 !=3 !=1
			и крайний срок !=null <now
			*/
			$expired=false;
			$exp_ptime=NULL;
			if($f['pdate_beg']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_beg']))+ (int)substr($f['ptime_beg'], 0,2)*60*60 + (int)substr($f['ptime_beg'],3,2)*60;
				
			 
				 
				//echo date('d.m.Y H:i:s', $exp_ptime).'<br>';
			}
			
			if(
			
			($f['status_id']!=10) && ($f['status_id']!=3) && ($f['status_id']!=1)
			
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			

			 
			$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-';
			 

			 
				$_res=new Sched_Resolver($f['kind_id']);
				$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f,true);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			
			if(($f['kind_id']==5)){
				//определить доступ к заметке: только чтение или еще правка
				 
				$f['can_edit']= ($f['manager_id']==$this->_auth_result['id'])||
				 (($f['manager_id']!=$this->_auth_result['id'])&&in_array($this->_auth_result['id'],$_res->instance->GetUserIdsArr($f['id'],2)))
				  || in_array($f['manager_id'],$_available_user_ids);	
				  
				 
			}

			 
			
			if(($f['kind_id']==2)||($f['kind_id']==3)){
				//города, контрагенты
				$f['cities']=$_cg->GetItemsByIdArr($f['id']);
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			}
			
			
			if(($f['kind_id']==1)||($f['kind_id']==5)){
				//контрагенты
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
			}
			

			
			/*if(($f['kind_id']==2)||($f['kind_id']==3)){
				$f['can_confirm_done']=$can_confirm_shipping&&($can_confirm_all_shipping||($f['manager_id']==$this->_auth_result['id']));
				
				
				//можно утв/разутв. прием работы
				$f['can_confirm_fulfil']=$can_confirm_fulfil;//&&$_res->instance->canco
				if($f['is_fulfiled']==1) $f['can_confirm_fulfil']=$f['can_confirm_fulfil']&&$_res->instance->DocCanUnconfirmFulfil($f['id'],$reason,$f);
				else  $f['can_confirm_fulfil']=$f['can_confirm_fulfil']&&$_res->instance->DocCanConfirmFulfil($f['id'],$reason,$f);
				

			}*/
			
			if(($f['kind_id']==2)||($f['kind_id']==3)){
				//встреча, к-ка
				
				//можно утв, разутв вып работы
				
				if($f['is_confirmed_done']==1) $f['can_unconfirm_done']=$can_confirm_shipping&&($can_unconfirm_all_shipping||($f['manager_id']==$this->_auth_result['id']))&&($f['is_fulfiled']==0);
				else $f['can_confirm_done']=$can_confirm_shipping&&($can_confirm_all_shipping||($f['manager_id']==$this->_auth_result['id']))&&($f['is_fulfiled']==0);
				
				
				
				
				
				
				
				
				//можно утв/разутв. прием работы
				$f['can_confirm_fulfil']=$can_confirm_fulfil;
				$f['can_unconfirm_fulfil']=$can_unconfirm_fulfil;
				 
				if($f['is_fulfiled']==1) $f['can_unconfirm_fulfil']=$f['can_unconfirm_fulfil']&&$_res->instance->DocCanUnconfirmFulfil($f['id'],$reason,$f);
				else  $f['can_confirm_fulfil']=$f['can_confirm_fulfil']&&$_res->instance->DocCanConfirmFulfil($f['id'],$reason,$f);
				
			}else{
				//звонок
				
				if($f['is_confirmed_done']==1){
					 //3 типа звонка
					 
					 
					if(($f['incoming_or_outcoming']==1)&&($f['plan_or_fact']==0)) $our_perm=$can_unconfirm_ish_plan;
					elseif(($f['incoming_or_outcoming']==1)&&($f['plan_or_fact']==1)) $our_perm=$can_confirm_ish_fact;
					elseif(($f['incoming_or_outcoming']==0)&&($f['plan_or_fact']==1)) $our_perm=$can_confirm_vh_fact;
					else $our_perm=$can_confirm_vh_fact;
					
					
					
					$f['can_unconfirm_done']=$can_confirm_shipping&&($our_perm||($f['manager_id']==$this->_auth_result['id']))&&($f['is_fulfiled']==0);
					
					 
					 
				}
				else $f['can_confirm_done']=$can_confirm_shipping;
				
			}
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
		 
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('can_confirm_fulfil', $can_confirm_fulfil);
		
		
		$sm->assign('can_confirm_all_shipping', $can_confirm_all_shipping);
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('kind_id',$kind_id);
		$sm->assign('prefix',$kind_id);
		
		$sm->assign('can_edit',$can_edit);
	 
	/*	$_au=new AuthUser();
		//$_result=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		$sm->assign('user_id',$_result['id']);*/
		
		//ссылка для кнопок сортировки
			$link=$dec->GenFltUri('&', $kind_id);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$kind_id.'=[[:digit:]]+','',$link);
		$link=eregi_replace('&action'.$kind_id,'&action',$link);
		$link=eregi_replace('&id'.$kind_id,'&id',$link);
		$sm->assign('link',$link);
		
		
		//показ конфигурации
		switch($kind_id){
			case 1:
				$view=$this->_view1;
			break;	
			case 2:
				$view=$this->_view2;
			break;	
			case 3:
				$view=$this->_view3;
				
			break;	
			case 4:
				$view=$this->_view4;
			break;	
			case 5:
				$view=$this->_view5;
			break;	
			default:
				$view=$this->_view1;
			break;
			
		}
		
		/*echo $kind_id.'<br>';
		print_r($view);
		*/
		$sm->assign('view', $view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $view->GetColsUnArr($this->_auth_result['id']));
		
		/*echo '<pre>';
		print_r($view->GetColsArr($this->_auth_result['id']));
		echo '</pre>';*/


		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	//группа задач для ленты,  
	public function ShowPosADayArr($pdate, DBDecorator $dec, $user_id, $can_edit=false){
		  
		
		
		
		$sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
					 
				where (pdate_beg="'.$pdate.'" 
				
				or (pdate_end is not null and pdate_beg<="'.$pdate.'"  and pdate_end>= "'.$pdate.'" ))
				
				and(
				(p.manager_id="'.$user_id.'" AND p.kind_id IN (2, 3, 4) )
				or
				
				( p.kind_id IN (1) AND p.id IN (select distinct sched_id from sched_task_users where user_id ="'.$user_id.'") ) 
				)
				';
				
		 
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		 
		$alls=array();
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			
			
			 
			$_res=new Sched_Resolver($f['kind_id']);
			//$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			
			$f['ribbon_short_title']=$_res->instance->ConstructName($f['id'], $f);
			
			$f['ribbon_title']=$_res->instance->ConstructFullName($f['id'], $f);
			
			$f['ribbon_begin']=$_res->instance->ConstructBeginDate($f['id'], $f);
			
			//округляем время. все что меньше 60 минут - делаем 60 минут
			if(($f['pdate_beg']==$f['pdate_end'])&&($f['ptime_end']!="")&&($f['ptime_beg']!="")){
				$ptime_end=mktime(substr($f['ptime_end'], 0, 2), substr($f['ptime_end'], 3, 2), substr($f['ptime_end'], 6, 2));
				
				$ptime_beg=mktime(substr($f['ptime_beg'], 0, 2), substr($f['ptime_beg'], 3, 2), substr($f['ptime_beg'], 6, 2));
				
				if(($ptime_end-$ptime_beg)<(60*60)){
					
					$f['ribbon_end']=$f['pdate_end'].'T'.date("H:i:s", $ptime_beg+60*60);
				}else $f['ribbon_end']=$_res->instance->ConstructEndDate($f['id'], $f);
			
			}else 			
				$f['ribbon_end']=$_res->instance->ConstructEndDate($f['id'], $f);
			
			 
			 
			 
			$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']); 
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
		$current_supplier='';
		$user_confirm_id='';
		
		  
		return $alls;
	}
	
	
	 //данные для главной страницы
	 public function ShowPosIndex(  DBDecorator $dec,  $user_id
		){
		 
		$_addr=new SchedContactItem;
		
		$_cg=new Sched_CityGroup;
		$_sg=new Sched_SupplierGroup;
		
		
		$sql='select distinct p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			m.name as meet_name,
			kind.name as kind_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active		
					 
				from '.$this->tablename.' as p
				left join sched_kind as kind on p.kind_id=kind.id
				
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_cities as sc on sc.sched_id=p.id
				left join sprav_city as c on sc.city_id=c.id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				left join sched_contacts as ss1 on ss1.sched_id=p.id
				left join supplier as sup1 on ss1.supplier_id=sup1.id
				
				left join user as cr on cr.id=p.created_id
				 
				where 
				
				(
					(p.manager_id="'.$user_id.'" AND p.kind_id IN (2, 3, 4) )
				or
				
				( p.kind_id=1 AND p.id IN (select distinct sched_id from sched_task_users where user_id ="'.$user_id.'") ) 
				)
				';
				
	 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			//$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		 
		
		$alls=array();
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			$f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			 
				$_res=new Sched_Resolver($f['kind_id']);
				$f['contact_value']=$_res->instance->ConstructContacts($f['id'], $f);
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			
			//$f['notes']=$_bng->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
			
			
			if(($f['kind_id']==2)||($f['kind_id']==3)){
				//города, контрагенты
				 
				$f['cities']=$_cg->GetItemsByIdArr($f['id']);
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
				
			}
			
			
			if(($f['kind_id']==1)||($f['kind_id']==5)){
				//контрагенты
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
			}
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
		 
		return $alls;
	}
	
	
	
	////список ID других сотрудников, которых может видеть текущий сотрудник
	public function GetAvailableUserIds($user_id, $except_me=false, $kind_id=1){
	
		$arr=array();
		
		
		//проверить супердоступ
		$_testv=new Sched_ViewItem;
		$testv=$_testv->GetItemByFields(array('user_id'=>$user_id, 'allowed_id'=>0, 'kind_id'=>$kind_id));
		
		if($testv!==false){
			//все сотрудники
			$sql='select id from user';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
		
			//если нет супердоступа, проверить обычные доступы
			$sql='select allowed_id from sched_view_users where user_id="'.$user_id.'" and kind_id="'.$kind_id.'"';
			
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['allowed_id'];	
			}
		
		}
		
		//вставка себя для корректности
		if(!$except_me) $arr[]=$user_id;
		else $arr[]=-1;
		
		return $arr;	
		
	}
	
	
	
	// ДЛЯ ЗАМЕТОК: список ID других сотрудников, которых может видеть текущий сотрудник
	public function GetNotesAvailableUserIds($user_id, $except_me=false){
		$arr=array();
		
		
		$_man=new DiscrMan;
		if($_man->CheckAccess($user_id,'w',971)){
			//все сотрудники
			$sql='select id from user';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}
		
		
		//проверить супердоступ
		/*$_testv=new Sched_ViewItem;
		$testv=$_testv->GetItemByFields(array('user_id'=>$user_id, 'allowed_id'=>0));
		if($testv!==false){
			//все сотрудники
			$sql='select id from user';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['id'];	
			}
		}else{
		
			//если нет супердоступа, проверить обычные доступы
			$sql='select allowed_id from sched_view_users where user_id="'.$user_id.'" ';
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
				$arr[]=$f['allowed_id'];	
			}
		
		}*/
		
		//вставка себя для корректности
		if(!$except_me) $arr[]=$user_id;
		else $arr[]=-1;
		
		return $arr;	
		
	}
	
	
	 
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		$log=new ActionLog();
		
		$_stat=new DocStatusItem;
		
	 
		 	 
		//не утверждено
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and status_id=18 and kind_id in(1,2,3,4) order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			 
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления записи планировщика,  документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания записи планировщика,  документ не утвержден';
					}
				}
			 }
			 
			
			
			
			
			
			
			if($can_annul){
				$_res=new Sched_Resolver($f['kind_id']);
		
		
					//$_res->instance->Edit($id, $params);
				
				$_res->instance->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование записи планировщика',NULL,904,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				/*$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: коммерческое предложение было автоматически аннулировано, причина: '.$reason.'.'
				));*/
					
			}
		}
		
		//встречи, командировки не отмечены выполненными более 3 месяцев
		$sql='select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and status_id in(18,2,22) and kind_id in(2,3) and pdate_beg<="'.date('Y-m-d', time()-3*30*24*60*60).'" order by id desc';
		//echo $sql;
		$set=new MysqlSet($sql);
		
		
		//Создан, Утвержден, Запланирован
		//18, 2, 22, 
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			 
			
			
			
			//случай 1 - нет  второй галочки:
			if($f[' is_confirmed_done']==0){
				
				
					
				//проверим дату записи
				 
				 	
					
					if((DateFromdmY(DateFromYmd($f['pdate_beg']))+3*30*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.(3*30).' дней с даты встречи/командировки, у встречи/командировки не отмечено выполнение';
					}
				 
			 }
			 
			
			
			
			
			
			
			if($can_annul){
				$_res=new Sched_Resolver($f['kind_id']);
		
		 
				$_res->instance->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование записи планировщика',NULL,904,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'].' причина: '.$reason,$f['id']);
				
			 
			}
		}
		
	}
	
	


	
}








/**********************************************************************************************/
// группа ЗАДАЧ планировщика
class  Sched_TaskGroup extends Sched_Group {
	protected $_auth_result;
	
	protected $_view;

	protected $new_list; //список документов с уведомлениями
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched';
		$this->pagename='shedule.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		
		$this->_auth_result=NULL;
		$this->new_list=NULL;
		

		
		$this->_view=new Sched_View1Group;

	} 

       public function ShowPos($kind_id, //0
		$template, //1
		DBDecorator $dec, //2
		$can_edit=false, //3
		$from, //4
		$to_page, //5
		$has_header=true, //6
		$is_ajax=false, //7
		$can_delete=true, //8
		$can_restore=true, //9
		$can_confirm_price=true, //10
		$can_unconfirm_price=true, //11
		$can_confirm_shipping=true, //12
		$can_unconfirm_shipping=true, //13
		$can_confirm_fulfil=true,  //14
		$can_unconfirm_fulfil=true,  //15
		$from_card=false, //16
		$force_has_rows=false, //17
		$can_super_delete=false //18
		){
		  
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('from_card', $from_card);
		 $sm->assign('force_has_rows', $force_has_rows);
		  $sm->assign('has_header', $has_header);
		 $sm->assign('can_restore', $can_restore);
		 $sm->assign('can_delete', $can_delete);
		 $sm->assign('can_confirm_price', $can_confirm_price);
		 $sm->assign('can_unconfirm_price', $can_unconfirm_price);
		 $sm->assign('can_confirm_shipping', $can_confirm_shipping);
		 $sm->assign('can_unconfirm_shipping', $can_unconfirm_shipping);
		 
		 $sm->assign('can_confirm_fulfil', $can_confirm_fulfil);
		 $sm->assign('can_unconfirm_fulfil', $can_unconfirm_fulfil);
		 $sm->assign('can_super_delete', $can_super_delete);
		 

		  
		 $_au=new AuthUser();
		//$_result=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_result=$_au->Auth(false,false);
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		//$sm->assign('user_id',$_result['id']);
		
		$_bng=new SchedNotesGroup;
		
		$_hg=new Sched_HistoryGroup;
		
		$_roles=new Sched_FieldRules($this->_auth_result);
		 
		$_sg=new Sched_SupplierGroup;
		
		
		

		
		
			$sql='select distinct p.id, p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			m.name as meet_name,
			
			
			u1.name_s as user_name_1, u1.login as user_login_1, u1.is_active as u_is_active1,
			u2.name_s as user_name_2, u2.login as user_login_2, u2.is_active as u_is_active2,
			
			
			uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
			par.code as parent_code, par.topic as parent_topic, ps.name as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				

				left join user as uf on uf.id=p.user_fulfiled_id
				left join '.$this->tablename.' as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
					 
				where p.'.$this->subkeyname.'="'.$kind_id.'"';
				
		$sql_count='select count(distinct p.id) 
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join '.$this->tablename.' as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
					 
				where p.'.$this->subkeyname.'="'.$kind_id.'"';
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $kind_id));
		$navig->SetFirstParamName('from'.$kind_id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		
		$this->new_list=NULL;
		 


		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$_res=new Sched_Resolver($f['kind_id']);
			
			 
			
			//просрочено или нет
			/*
			статус != 10 !=3 !=1
			и крайний срок !=null <now
			*/
			$expired=false;
			$exp_ptime=NULL;
			if($f['pdate_beg']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_beg']))+ (int)substr($f['ptime_beg'], 0,2)*60*60 + (int)substr($f['ptime_beg'],3,2)*60;
				
			 
				 
				//echo date('d.m.Y H:i:s', $exp_ptime).'<br>';
			}
			
			if(
			
			($f['status_id']!=10) && ($f['status_id']!=3) && ($f['status_id']!=1)
			
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			 
			 

			 
			if($f['pdate_beg']!=="") $f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			 
			 
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-'; 
			 
			 
		
			//для каждой записи:
			//проверять доступность полей
			//и эту доступность заносить в соотв. поля
			$field_rights= $_roles-> GetFieldsRoles($f, $this->_auth_result['id'] );
			$f['field_rights']= $field_rights;
			 
			 
			 
			
			$f['can_annul']=$_res->instance->DocCanAnnul($f['id'],$reason,$f, $this->_auth_result)&&$can_delete&&$field_rights['can_annul'];
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			if(!$field_rights['can_annul']) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			

			
			//$f['notes']=$_bng->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
			
			
			if(($f['kind_id']==1)||($f['kind_id']==5)){
				//контрагенты
				$f['suppliers']=$_sg->GetItemsByIdArr($f['id']);	
			}
			
 
			//найти число новых комментов
			$f['count_new']=$_hg->CalcNewByTask($f['id'], $this->_auth_result['id'] );
			
			//получить блоки "новый документ"
			$f['new_blocks']=$this->DocumentNewBlocks($f['id'], $this->_auth_result['id']);
			 
			
			
		
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//найти число нов ком по ролям
		$sm->assign('count_new_1', $_hg->CalcNewByRole(1, $this->_auth_result['id']));
		$sm->assign('count_new_2', $_hg->CalcNewByRole(2, $this->_auth_result['id']));
		$sm->assign('count_new_3', $_hg->CalcNewByRole(3, $this->_auth_result['id']));
		$sm->assign('count_new_4', $_hg->CalcNewByRole(4, $this->_auth_result['id']));
		$sm->assign('count_new_all', $_hg->CalcNewByAllRoles($this->_auth_result['id']));
		
		
		//заполним шаблон полями
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
		 
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('kind_id',$kind_id);
		$sm->assign('prefix',$kind_id);
		
		$sm->assign('can_edit',$can_edit);
	 
	/*	*/
		
		//ссылка для кнопок сортировки
			$link=$dec->GenFltUri('&', $kind_id);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$kind_id.'=[[:digit:]]+','',$link);
		$link=eregi_replace('&action'.$kind_id,'&action',$link);
		$link=eregi_replace('&id'.$kind_id,'&id',$link);
		$sm->assign('link',$link);
		
		
		
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		


		
		return $sm->fetch($template);
	}
	
	
	
	//попадает ли текущий документ при текущем пользователе в индикацию, если попадает - вернуть данные для построения блока
	public function DocumentNewBlocks($document_id, $user_id){
		$data=array();
		
		if($this->new_list===NULL) $this->ConstructNewList($user_id);
		
		 
		
		/*$data[]=array(
					'class'=>'menu_new_m',
					
					'url'=>$url,
					'comment'=>'Примите лиды в работу!'
				
				);
		*/
		
		//пересмотреть список данных
		foreach($this->new_list as $k=>$type){
			if(in_array($document_id, $type['doc_ids'])){
				
				$url=str_replace('{id}',$document_id, $type['url'], $subst_count);
				
				if($subst_count==0) $url=$type['url'].$document_id;
				$data[]=array(
					'class'=>$type['class'],
					
					'url'=>$url,
					'comment'=>$type['comment'],
					'doc_counters'=>(int)$type['doc_counters'][array_search($document_id, $type['doc_ids'])]
				
				);	
			}
		}
		
		
		return $data;	
	}
	
	
	//конструирование списка документов с уведомлением
	protected function ConstructNewList($user_id){
		$this->new_list=array();
		
		/*
		$this->new_list[]=array(
					'class'=>'menu_new_m',
					'num'=>(int)$f[0],
					'url'=>''
					'doc_ids'=>array(),
					'doc_counters'=>array(),
					'comment'=>'Примите лиды в работу!'
				
				);
		*/	
		
		//$user_ids=$this->GetAvailableUserIds($user_id, false, 1);  //->GetAvailableTenderIds($user_id);
		
		  
		$man=new DiscrMan;
		
		
		//документ, доступный хотя бы по одной из ролей, 
		//где статус = 24 выполняется
		//где пользователь - постановщик, и где is_waiting_new_pdate=1
		
		//примите в работу тендер: тендеры в статусе утв-н их менеджеру
		$sql='select count(*) from 
		sched as t
		
		where   t.id in (select distinct sched_id from sched_task_users where user_id="'.$user_id.'" and kind_id=1)
		 
		and t.status_id=24
		and t.is_waiting_new_pdate=1
		 
		 
		';
		
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);	
		
		if((int)$f[0]>0){
			//получим первый УРЛ, сформируем выходной элемент
			$sql='select t.* from 
				sched as t
			
			where   t.id in (select distinct sched_id from sched_task_users where user_id="'.$user_id.'" and kind_id=1)
		 
		and t.status_id=24
		and t.is_waiting_new_pdate=1
			order by t.id asc  
			';
			
			
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$doc_ids=array();
			for($j=0; $j<$rc; $j++){ 
				$g=mysqli_fetch_array($rs);		
			 
				$doc_ids[]=$g['id'];
			}
			
			$this->new_list[]=array(
				'class'=>'reestr_menu_new_att',
				'num'=>(int)$rc,
				'doc_ids'=>$doc_ids,
				'doc_counters'=>array(),
				'url'=>'ed_sched_task.php?action=1&move_srok=1&id=',
				'comment'=>'Перенос срока задачи!'
			
			);
		}
		
		 
		 
		
		//var_dump($this->new_list);	
	}

	

	
	
	
}



















//напоминание
class SchedRemindItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_reminds';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	

}

//группа напоминаний
class SchedRemindGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='sched_reminds';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
	
	//автонапоминание о просроченных событиях
	//добавляем только исходящие звонки! возможно позже добавим другие события
	public function PutAutoReminds($user_id){
		
		$sql='select s.* from sched as s
			  
			where 	
				s.status_id<>3 and s.status_id<>10
				and s.kind_id=4
				and s.incoming_or_outcoming="1"
				and s.pdate_beg is not null and s.pdate_beg<="'.date('Y-m-d').'"
				and s.ptime_beg is not null and s.ptime_beg<="'.date('H:i:s', time()-5*60).'"
				and s.manager_id="'.$user_id.'"
				';
		
		//echo $sql;	
		$set=new MysqlSet($sql);	
			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_it=new SchedRemindItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			//проверить, есть ли напоминание
			$it=$_it->GetItemByFields(array('user_id'=>$user_id, 'sched_id'=>$f['id']));
			
			$do_it=true;
			if(($it!==false)&&(($it['is_viewed']==0)||($it['is_viewed']==2)) ) $do_it=false;
			if($do_it){
				if($it	===false){
					$_it->Add(array(	'user_id'=>$user_id, 'sched_id'=>$f['id'], 'is_viewed'=>0, 'action_time'=>(time()+15*60)));
					
				}else{
					$_it->Edit($it['id'], array( 'action_time'=>(time()+15*60)));
				}
				
			}
			
		}
			
			
	}
	
	
	//сколько новых событий
	public function CalcNewPlans($user_id){
		
		$sql='select count(*) from sched_reminds as r
		left join sched as s on s.id=r.sched_id
		
		where r.is_viewed="0"
		and r.user_id="'.$user_id.'"
		and s.status_id<>3 and s.status_id<>10 and s.status_id<>26
		and (r.action_time between "'.datefromdmy(date('d.m.Y')).'" and "'.time().'" )';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);	
			
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
			
			
	}
	
	//какие новые события??
	public function LoadNewPlans($user_id){
		$arr=array();
		
		$sql='select distinct s.id, s.*, r.id as r_id,  r.action_time as r_pdate from sched_reminds as r
		left join sched as s on s.id=r.sched_id
		
		where r.is_viewed="0"
		and r.user_id="'.$user_id.'"
		and s.status_id<>3 and s.status_id<>10 and s.status_id<>26
		and (r.action_time between "'.datefromdmy(date('d.m.Y')).'" and "'.time().'" )
		
		order by s.kind_id, s.pdate_beg, s.ptime_beg, s.pdate
		
		';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);	
			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$_res=new Sched_Resolver($f['kind_id']);
			
			$f['short_title']=$_res->instance->ConstructName($f['id'], $f);
			$f['full_title']=$_res->instance->ConstructFullName($f['id'], $f);
			
			$f['r_pdate']=date('d.m.Y H:i', $f['r_pdate']);
			
			//$f['data']=$_sdg->GetItemsByIdArr($f['id'], $current_k_id, array(1,3,4) );
			
			$arr[]=$f;
		}
		
		
		return $arr;	
	}
}




//связанный контакт
class SchedContactItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	

}

//группа связанных контактов
class SchedContactGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='sched_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
}













//вид записи
class SchedKindItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_kind';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	

}

//группа видов записи
class SchedKindGroup extends AbstractGroup {

	//установка всех имен
	protected function init(){
		$this->tablename='sched_kind';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		
		
	}
}





// справочник контактов к-та
class Sched_SupplierContactGroup extends SupplierContactGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact';
		$this->pagename='view.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $current_k_id=0){
		$arr=Array();
		
		 // KSK - 29.03.2016
                // добавляем условие выбора записей для отображения is_shown=1
		//$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'=1 order by id asc');
		
		
		
		
		$_sdg=new Sched_SupplierContactDataGroup;
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$f['data']=$_sdg->GetItemsByIdArr($f['id'], $current_k_id, array(1,3,4) );
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
}


//  список телефонов контакта контрагента
class Sched_SupplierContactDataGroup extends SupplierContactDataGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact_data';
		$this->pagename='view.php';		
		$this->subkeyname='contact_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, array $kinds){
		$arr=Array();
		$set=new MysqlSet('select p.*,
			pc.name as pc_name, pc.icon as pc_icon
		
		 from '.$this->tablename.' as p left join supplier_contact_kind as pc on pc.id=p.kind_id
		  where p.'.$this->subkeyname.'="'.$id.'" and pc.id in('.implode(', ', $kinds).') order by p.id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}













//поделиться заметкой
class Sched_UserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_users';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}



class Sched_UserGroup extends AbstractGroup {
		protected static $uslugi;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_users';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id,  $bill=NULL){
		$arr=Array();
		
		$sql='select p.*, u.name_s, u.login, u.is_active /*, up.name as position_s*/
		from '.$this->tablename.' as p
		inner join user as u on u.id=p.user_id 
	/*	left join user_position as up on up.id=u.position_id*/
		where
		'.$this->subkeyname.'="'.$id.'"
		order by u.name_s asc, u.login asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['hash']=md5($f['user_id'].'_'.$f['right_id']); 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//список id сотрудников по праву или без него
	public function GetItemsIdsById($id,  $right_id=NULL){
		$arr=Array();
		
		/*$_bill=new Sched_AbstractItem;
		if($bill===NULL) $bill=$_bill->GetItemById($id);
	 	*/
		
		$flt='';
		if($right_id!==NULL) $flt.=' and p.right_id="'.$right_id.'" ';
		
		$sql='select p.user_id
		from '.$this->tablename.' as p
		inner join user as u on u.id=p.user_id 
		where
		'.$this->subkeyname.'="'.$id.'" '.$flt.'
		order by u.name_s asc, u.login asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			 
			$arr[]=$f['user_id'];
		}
		
		return $arr;
	}
	
	 
	
}


//группа сотрудников



// users S
class Sched_UsersSGroup extends UsersSGroup {
	protected $group_id;
	public $instance;
	public $pagename;

	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='users_s.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=1;
		$this->instance=new UserSItem;

	}
	
	 
	
	
	
	
	//Отбор сотрудников для задачи и других карт
	public function GetItemsForBill(  DBDecorator $dec){
		$txt='';
		
		 
		
		$sql='select p.*,
		/*, up.name as position_s, */ p.id as user_id from '.$this->tablename.' as p 
		
		/*left join user_position as up on up.id=p.position_id*/
		 where p.group_id="'.$this->group_id.'"

			 ';
		
	
		
		 
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		/*if(strlen($db_flt)>0) $sql.=' and ';
		else */$sql.=' and ';
		
		$sql.=' p.is_active=1 ';
		
		
		
		$sql.=' order by p.name_s asc, p.login asc ';
		
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
			//print_r($f);
			$alls[]=$f;
		}
		
		 
	 
		
		return $alls;
	}
	
	//Отбор сотрудников для ответственного и п-ка задачи 
	public function GetItemsForTask(  DBDecorator $dec, $always_id=0){
	
		$txt='';
		
		 
		
			
		$sql='select p.*,
		/*, up.name as position_s, */ p.id as user_id from '.$this->tablename.' as p 
		
		/*left join user_position as up on up.id=p.position_id*/
		 where p.group_id="'.$this->group_id.'"

			 ';
		
	 
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
	 
		}
		
	 
		$sql.=' and  (p.is_active=1 or p.id="'.$always_id.'")';
		
		
		$sql.=' order by p.name_s asc, p.login asc ';
		
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	 	
		$alls=array();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
			//print_r($f);
			$alls[]=$f;
		}
		 
		return $alls;
	}
	
	

	
	 
}







//разрешенные другие пользователи для сотрудника
class Sched_ViewItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_view_users';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Sched_ViewGroup extends AbstractGroup {
	protected $group_id;
	public $instance;
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_view_users';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->group_id=1;
		 
	}
	
	
	
	
	
	
	//список для карты сотрудника
	public function ForCard($user_id, $template, $can_edit=false, $is_ajax=false){
		if($is_ajax) $sm=new smartyaj;
		else $sm=new smartyadm;
		
		
		$alls=array();
		
		//строим сотрудников, по каждому из них - цепочку связанных прав по видам
		$_kinds=array(1,2,3,4,5);
		
		//уточняем, какие есть права по супердоступу
		$rights=array();
		$sql='select distinct kind_id from sched_view_users where user_id="'.$user_id.'" and allowed_id=0';
		$set=new MysqlSet($sql);
			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$rights[$f['kind_id']]=$f['kind_id'];
		}
		
		if(count($rights)>0) $alls[]=array(
				'id'=>0,
				'name_s'=>'Все сотрудники',
				'login'=>'-',
				'position_s'=>'-',
				'is_active'=>1,
				'kind'=>0,
				'rights'=>$rights
			);
		
		
		 	
			
		
		$sql='select distinct u.id, u.name_s, u.login, u.is_active 
			from '.$this->tablename.' as p
			
			inner join user as u on u.id=p.allowed_id 	 
		 
			where
			 u.group_id="'.$this->group_id.'"
			and p.user_id="'.$user_id.'"
			
			order by u.name_s asc, u.login asc';
		
		$set=new MysqlSet($sql);
			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//уточняем, какие есть права 
			$rights=array();
			$sql1='select distinct kind_id from sched_view_users where user_id="'.$user_id.'" and allowed_id="'.$f['id'].'"';
			$set1=new MysqlSet($sql1);
				
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			for($i1=0; $i1<$rc1; $i1++){
				$f1=mysqli_fetch_array($rs1);
				$rights[$f1['kind_id']]=$f1['kind_id'];
			}
			
			$f['rights']=$rights;
			$f['kind']=1;
			
			$alls[]=$f;
		}
		
		/*echo '<pre>';	
		print_r($alls);
		echo '</pre>';*/
		
		
		//проверить на СУПЕРДОСТУП
		/*$_svi=new Sched_ViewItem;
		$test_super=$_svi->GetItemByFields(array('user_id'=>$user_id, 'allowed_id'=>0));
		if($test_super!==false){
				
		}else{
			
			//проверить на выделенный доступ
			$sql='select u.id, u.name_s, u.login, u.is_active, up.name as position_s
			from '.$this->tablename.' as p
			
			inner join user as u on u.id=p.allowed_id 	 
			left join user_position as up on up.id=u.position_id
			where
			 u.group_id="'.$this->group_id.'"
			and p.user_id="'.$user_id.'"
			
			order by u.name_s asc, u.login asc';
			
			//echo $sql;
			$set=new MysqlSet($sql);
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				 
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				//$f['hash']=md5($f['user_id'].'_'.$f['right_id']); 
				$f['kind']=1;
				
				$alls[]=$f;
			}
			
		 
		}*/
		
		$sm->assign('can_edit', $can_edit);
		$sm->assign('items', $alls);
		$sm->assign('user_id', $user_id);
			
		return $sm->fetch($template);
	}
	
	
	//список для диалогового окна
	public function ForWindow($user_id, $template, $can_edit=false, $is_ajax=false){
		if($is_ajax) $sm=new smartyaj;
		else $sm=new smartyadm;
		
		$has_super=false;
		
		$alls=array();
		
		
		//строим сотрудников, по каждому из них - цепочку связанных прав по видам
		$_kinds=array(1,2,3,4,5);
		
		//уточняем, какие есть права по супердоступу
		$rights=array();
		$sql='select distinct kind_id from sched_view_users where user_id="'.$user_id.'" and allowed_id=0';
		$set=new MysqlSet($sql);
			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$rights[$f['kind_id']]=$f['kind_id'];
		}
		
		$alls[]=array(
				'id'=>0,
				'name_s'=>'Все сотрудники',
				'login'=>'-',
				'position_s'=>'-',
				'is_active'=>1,
				'kind'=>0,
				'rights'=>$rights
			);
		
		$sql='select distinct u.id, u.name_s, u.login, u.is_active
			from  user as u 
			 
			where
			 u.group_id="'.$this->group_id.'"
			and u.id<>"'.$user_id.'"
			
			order by u.name_s asc, u.login asc';
		
		$set=new MysqlSet($sql);
			
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//уточняем, какие есть права 
			$rights=array();
			$sql1='select distinct kind_id from sched_view_users where user_id="'.$user_id.'" and allowed_id="'.$f['id'].'"';
			$set1=new MysqlSet($sql1);
				
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			for($i1=0; $i1<$rc1; $i1++){
				$f1=mysqli_fetch_array($rs1);
				$rights[$f1['kind_id']]=$f1['kind_id'];
			}
			
			$f['rights']=$rights;
			$f['kind']=1;
			
			$alls[]=$f;
		}
		
		
		//проверить на СУПЕРДОСТУП
		/*$_svi=new Sched_ViewItem;
		$test_super=$_svi->GetItemByFields(array('user_id'=>$user_id, 'allowed_id'=>0));
		if($test_super!==false){
			
			
			
			//$mode=1;
			
			$has_super=true;
		}
		
  
		  
		  //проверить на выделенный доступ
		  $sql='select u.id, u.name_s, u.login, u.is_active, up.name as position_s,
		  p.id as p_id
		  
		  from user as u
		  left join user_position as up on up.id=u.position_id
		  left join  '.$this->tablename.' as p on u.id=p.allowed_id   and p.user_id="'.$user_id.'" 	 
		  where
		   u.group_id="'.$this->group_id.'"
		   and u.id<>"'.$user_id.'"
		 
		   
		  order by u.name_s asc, u.login asc';
		  
		  //echo $sql; 
		  
		  $set=new MysqlSet($sql);
		  
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			   
			  foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			  
			  //$f['hash']=md5($f['user_id'].'_'.$f['right_id']); 
			  
			//  echo 'access is '.$f['p_id'].'<br>';
			  
			  $alls[]=$f;
		  }
		  
	   */
	  
		
		$sm->assign('can_edit', $can_edit);
		$sm->assign('items', $alls);
		$sm->assign('user_id', $user_id);
		$sm->assign('has_super', $has_super);
			
		return $sm->fetch($template);
	}
	
	
	//список позиций, какие были
	public function GetItemsByIdArr($id){
		$arr=array();
		
		$sql='select p.*, u.name_s, u.login
		from '.$this->tablename.' as p
		
		left join user as u on u.id=p.allowed_id  
		where
		'.$this->subkeyname.'="'.$id.'"
		order by u.name_s asc, u.login asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	//добавим позиции
	public function AddUsers($current_id, array $positions,  $result=NULL){
		$_kpi=new Sched_ViewItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('user_id'=>$v['user_id'],'allowed_id'=>$v['allowed_id'], 'kind_id'=>$v['kind_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['user_id']=$v['user_id'];
				$add_array['allowed_id']=$v['allowed_id'];
				$add_array['kind_id']=$v['kind_id'];
				
			 
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'user_id'=>$v['user_id'],
					'allowed_id'=>$v['allowed_id'],
					'kind_id'=>$v['kind_id']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['user_id']=$v['user_id'];
				
				$add_array['allowed_id']=$v['allowed_id'];
				$add_array['kind_id']=$v['kind_id'];
				
				
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				if($kpi['user_id']!=$add_array['user_id']) $to_log=$to_log||true;
				if($kpi['allowed_id']!=$add_array['allowed_id']) $to_log=$to_log||true;
				if($kpi['kind_id']!=$add_array['kind_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'user_id'=>$v['user_id'],
					'allowed_id'=>$v['allowed_id'] ,
					'kind_id'=>$v['kind_id']
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['user_id']==$v['user_id'])&&($vv['allowed_id']==$v['allowed_id'])&&($vv['kind_id']==$v['kind_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'user_id'=>$v['user_id'],
					'allowed_id'=>$v['allowed_id'],
					'kind_id'=>$v['kind_id'] 
			);
			
			//удаляем позицию
			$_kpi->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	 
	

	 
	
}






/*************************************************************************************************/
//города ком-ки, встречи



class Sched_CityItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_cities';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Sched_CityGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_cities';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
	
	
	 
	//список позиций, какие были
	public function GetItemsByIdArr($id){
		$arr=array();
		
		$sql='select ct.sched_id, ct.id as c_id, c.name as name, r.name as region_name, o.name as okrug_name, 
			sc.name as country_name,
		c.id as city_id, c.id as id
		
		 from '.$this->tablename.' as ct 
		 left join sprav_city as c on ct.city_id=c.id
		 left join sprav_region as r on c.region_id=r.id
		 left join sprav_district as o on o.id=c.district_id
		 left join sprav_country as sc on sc.id=c.country_id
		
		where ct.sched_id="'.$id.'" order by c.name asc, r.name asc, o.name asc';
		 
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	//добавим позиции
	public function AddCities($current_id, array $positions,  $result=NULL){
		$_kpi=new Sched_CityItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'city_id'=>$v['city_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['city_id']=$v['city_id'];
				
			 
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'city_id'=>$v['city_id']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				
				$add_array['city_id']=$v['city_id'];
				
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				if($kpi['city_id']!=$add_array['city_id']) $to_log=$to_log||true;
				if($kpi['sched_id']!=$add_array['sched_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'city_id'=>$v['city_id'] 
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['city_id']==$v['city_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'city_id'=>$v['city_id'] 
			);
			
			//удаляем позицию
			$_kpi->Del($v['c_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	 
	
}










/*************************************************************************************************/
//контрагенты ком-ки, встречи



class Sched_SupplierItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_suppliers';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Sched_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_suppliers';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//Отбор поставщиков для события планировщика
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$_csg=new SupplierCitiesGroup;
		
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth(false,false);
		
		$sql='select p.*, po.name as opf_name from supplier as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		//$sql.='  p.is_active=1 ';
		
		$sql.='(( p.is_org=0 and p.is_active=1 and p.org_id='.$resu['org_id'].') or (p.is_org=1 and p.is_active=1 and p.id<>'.$resu['org_id'].')) ';
		
		
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']= $csg;	 
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	 
	//список позиций, какие были
	public function GetItemsByIdArr($id){
		$arr=array(); $_csg=new SupplierCitiesGroup;
		$_sc=new Sched_ScGroup;
		
	$sql='select ct.sched_id, ct.id as c_id, ct.note,
		ct.result, ct.not_meet,
		  s.full_name, opf.name as opf_name,
		s.id as supplier_id, s.id as id
		
		 from '.$this->tablename.' as ct 
		 left join supplier as s on ct.supplier_id=s.id
		 left join opf as opf on s.opf_id=opf.id
		  
		
		where ct.sched_id="'.$id.'" order by s.full_name asc';
		 

		 
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			
			$csg=$_csg->GetItemsByIdArr($f['supplier_id']);
			$f['cities']= $csg;	
			
			$f['contacts']=$_sc->GetItemsByIdArr( $f['c_id']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	//добавим позиции
	public function AddSuppliers($current_id, array $positions,  $result=NULL){
		$_kpi=new Sched_SupplierItem; $_cg=new Sched_ScGroup;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'supplier_id'=>$v['supplier_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				if(isset($v['result'])) $add_array['result']=$v['result'];
				if(isset($v['not_meet'])) $add_array['not_meet']=$v['not_meet'];
				
				if(isset($v['note'])) $add_array['note']=$v['note'];
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				
				$_cg->ClearById($code); $_cg->PutIds($code, $v['contacts']);
				
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'not_meet'=>$v['not_meet'],
					'result'=>$v['result'],
					'note'=>$v['note']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				if(isset($v['result'])) $add_array['result']=$v['result'];
				if(isset($v['not_meet'])) $add_array['not_meet']=$v['not_meet'];
				
				if(isset($v['note'])) $add_array['note']=$v['note'];
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
				$_cg->ClearById($kpi['id']); 
				$_cg->PutIds($kpi['id'], $v['contacts']);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				if($kpi['city_id']!=$add_array['city_id']) $to_log=$to_log||true;
				if($kpi['supplier_id']!=$add_array['supplier_id']) $to_log=$to_log||true;
				if($kpi['note']!=$add_array['note']) $to_log=$to_log||true;
				if($kpi['result']!=$add_array['result']) $to_log=$to_log||true;
				if($kpi['not_meet']!=$add_array['not_meet']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'not_meet'=>$v['not_meet'],
					'result'=>$v['result'],
					'note'=>$v['note']
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'not_meet'=>$v['not_meet'],
					'result'=>$v['result'],
					'note'=>$v['note']
			);
			
			//удаляем позицию
			$_kpi->Del($v['c_id']);
			
			$_cg->ClearById($v['c_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//добавим контрагентов без контактов
	public function AddSuppliersWoCont($current_id, array $positions,  $result=NULL){
		$_kpi=new Sched_SupplierItem;  
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetItemsByIdArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('sched_id'=>$v['sched_id'],'supplier_id'=>$v['supplier_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				 
				
				$add_array=array();
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				if(isset($v['note'])) $add_array['note']=$v['note'];
				
				if(isset($v['result'])) $add_array['result']=$v['result'];
				if(isset($v['not_meet'])) $add_array['not_meet']=$v['not_meet'];
				
				
			 
				
				 
				$code=$_kpi->Add($add_array);
				 
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'not_meet'=>$v['not_meet'],
					'result'=>$v['result'],
					'note'=>$v['note']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				
				$add_array['sched_id']=$v['sched_id'];
				$add_array['supplier_id']=$v['supplier_id'];
				
				if(isset($v['note'])) $add_array['note']=$v['note'];
				
				if(isset($v['result'])) $add_array['result']=$v['result'];
				if(isset($v['not_meet'])) $add_array['not_meet']=$v['not_meet'];
				
				 
				$_kpi->Edit($kpi['id'],$add_array);
				
			 
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить
				
				$to_log=false;
				 
				if($kpi['supplier_id']!=$add_array['supplier_id']) $to_log=$to_log||true;
				if($kpi['note']!=$add_array['note']) $to_log=$to_log||true;
				if($kpi['result']!=$add_array['result']) $to_log=$to_log||true;
				if($kpi['not_meet']!=$add_array['not_meet']) $to_log=$to_log||true;
				
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					 
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'not_meet'=>$v['not_meet'],
					'result'=>$v['result'],
					'note'=>$v['note']
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['sched_id']==$v['sched_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					
					'sched_id'=>$v['sched_id'],
					'supplier_id'=>$v['supplier_id'],
					'not_meet'=>$v['not_meet'],
					'result'=>$v['result'],
					'note'=>$v['note']
			);
			
			//удаляем позицию
			$_kpi->Del($v['c_id']);
			
		 
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	 


	 
	
}




/***********************************************************************************************/
//место встречи


 
class Sched_KindMeetItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_meet';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}



class Sched_MeetGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_meet';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	
}


/****************************************************************************************************/
//контакты контрагента по встерче, командировке
	
class Sched_ScItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_suppliers_contacts';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sc_id';	
	}
	
}



class Sched_ScGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_suppliers_contacts';
		$this->pagename='view.php';		
		$this->subkeyname='sc_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//список, какие есть
	public function GetItemsByIdArr($id){
		$arr=Array();
		
		$sql='select p.sc_id, p.contact_id, p.contact_id as id, p.id as c_id,
		
		c.name, c.position
		
		
		
		 from '.$this->tablename.' as p
		 
		 inner join supplier_contact as c on p.contact_id=c.id
		 
		  where p.sc_id="'.$id.'" order by  c.name asc';
		//echo $sql;
		 $set=new MysqlSet($sql);
	 
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
 	
	public function ClearById($id){
		$sql='delete
		
		 from '.$this->tablename.' 
		 
		 
		  where  sc_id="'.$id.'" ';
		  
		  //echo $sql; die();
		
		 $set=new nonSet($sql);
	}
	
	public function PutIds($id, $ids){
		
		$sql='insert into  '.$this->tablename.' (sc_id, contact_id) values ';
		
		$_pairs=array();
		foreach($ids as $k=>$v) $_pairs[]=' ("'.$id.'", "'.$v.'") ';
		$sql.= implode(', ',$_pairs);
		
		if(count($ids)>0) $set=new nonSet($sql);
	}
		
}












//связанных с задачей сотрудников
class Sched_TaskUserItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_task_users';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//список связанных с задачей сотрудников
class Sched_TaskUserGroup extends AbstractGroup {
		protected static $uslugi;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_task_users';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id,  $kind_id){
		$arr=Array();
		
		$sql='select p.*, u.name_s, u.login, u.is_active, u.position_s
		from '.$this->tablename.' as p
		inner join user as u on u.id=p.user_id 
		where
		'.$this->subkeyname.'="'.$id.'"
		and p.kind_id="'.$kind_id.'"
		order by u.name_s asc, u.login asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['hash']=md5($f['user_id']); 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//список id сотрудников по виду отношения
	public function GetItemsIdsByKindId($id,  $kind_id){
		$arr=Array();
		
		 
		
		$flt='';
		 $flt.=' and p.kind_id="'.$kind_id.'" ';
		
		$sql='select p.user_id
		from '.$this->tablename.' as p
		inner join user as u on u.id=p.user_id 
		where
		'.$this->subkeyname.'="'.$id.'" '.$flt.'
		order by u.name_s asc, u.login asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			 
			$arr[]=$f['user_id'];
		}
		
		return $arr;
	}
	
	  
	
}











//запись в журнал о входе/выходе задачи из статуса "выполняется"
class Sched_WorkingItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_working';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
}


//список записей в журнал о входе/выходе задачи из статуса "выполняется"
class Sched_WorkingGroup extends AbstractGroup {
		protected static $uslugi;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_working';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		
		 
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id){
		$arr=array();
		
		$sql='select p.*
		from '.$this->tablename.' as p
		 
		where
		'.$this->subkeyname.'="'.$id.'"
		 
		order by p.id asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			 
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//найти время выполнения заявки - анализировать лог
	public function CalcWorkingTime($id, &$formatted, &$arr){
		$working_time=0; $formatted=''; $arr=array();
		
		$arr=$this->GetItemsByIdArr($id);
		
		
		$last_record=array();
		
		foreach($arr as $k=>$v){
			//ищем точки входа
			if($v['in_or_out']==0){
				
				//для этой точки входа ищем точку выхода
				//это следующий после нее in_or_out==1
				//либо при его отсутствии - не учитываем период
				$found=false; $out_data=array();	
				foreach($arr as $k1=>$v1){
					if($k1>$k){
						if($v1['in_or_out']==1){
							$found=true;
							$out_data=$v1;
						}
						break;
					}
				}
				
				if($found){
					$delta=$out_data['pdate']-$v['pdate'];	
				}else $delta=0;
				
				$working_time+=$delta;
			}
			$last_record=$v;
		}
		
		if(($last_record!=array())&&($last_record['in_or_out']==0)){
			//добавить разницу между этим временем и сейчас
			$working_time+=time()-$last_record['pdate'];	
		}
		
		
		$days=floor($working_time/(24*60*60));
		
		$hours = floor(($working_time - $days*24*60*60)/(60*60));
		
		$mins= floor(($working_time - $days*24*60*60 - $hours*60*60)/(60));
		
		$secs=$working_time - $days*24*60*60 - $hours*60*60 - $mins*60;
		
		$formatted=" $days д. $hours ч. $mins мин. $secs сек.";
		
		$arr=array(
			'days'=>$days,
			'hours'=>$hours,
			'mins'=>$mins,
			'secs'=>$secs
		
		);
		
		//echo $working_time;
		
		return $working_time;
	}
	
	
}

 
/****************************************************************************************************/
//маркер о факте проверки для всплывающих окон
class Sched_MarkerItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched_marker';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
}

//класс для получения данных для всплывающих окон
class Sched_PopupGroup extends AbstractGroup{
	
	protected function GetPdates(){
		$_hd=new HolyDates;
		//построить 3 даты - 5,10,15 рд назад
		$delays=array(5,10,15);
		$delay_dates=array();
		
		foreach($delays as $k=>$delay){
			$cter=1; $step=0;
			while($cter<$delay){
				
				$step++;
				
				if(!$_hd->IsHolyday( (mktime( 0,0,0, date('m'),date('d'),date('Y')))-$step*24*60*60 )) $cter++;
			}
			
			
			$delay_dates[$k]= (mktime(0,0,0,date('m'),date('d'),date('Y'))) - $step*24*60*60;
		}
		
		return $delay_dates;
	}
	
	//расчет числа задач для вида (1)  - нет комментариев Х рабочих дней
	public function CalcKind1($user_id){
		
		$delay_dates=$this->GetPdates();
		
		
		
		//foreach($delay_dates as $v) echo date('d.m.Y H:i:s', $v).'<br>';
		
		
		
		$sql='select count(distinct p.id) from sched as p
		
		where p.kind_id=1
		and p.status_id=24
		and p.id in(select distinct sched_id from sched_task_users where kind_id=2 and user_id="'.$user_id.'" )
		and p.id not in(select distinct sched_id from sched_task_users where kind_id=1 and user_id="'.$user_id.'" )
		and(
			(p.id not in(select distinct sched_id from sched_history where pdate>="'.$delay_dates[0].'" and user_id<>0 and user_id not in(select user_id from sched_task_users where kind_id=1 and sched_id=p.id))
			and p.priority="2")
			or 
			(p.id not in(select distinct sched_id from sched_history where pdate>="'.$delay_dates[1].'" and user_id<>0 and user_id not in(select user_id from sched_task_users where kind_id=1 and sched_id=p.id))
			and p.priority="1")
			or 
			(p.id not in(select distinct sched_id from sched_history where pdate>="'.$delay_dates[2].'" and user_id<>0 and user_id not in(select user_id from sched_task_users where kind_id=1 and sched_id=p.id))
			and p.priority="0")
		)
		';
		
		
		//echo $sql;
		
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		return (int)$f[0];
		
			
		
	}
	
	
	
	
		//показ  задач для вида (1)  - нет комментариев Х рабочих дней
	public function ShowKind1($user_id){
		$sql=$this->GainSql($user_id, 1);
		
		$delay_dates=$this->GetPdates();
		
		
		$sql.='  
		and p.status_id=24
		and p.id in(select distinct sched_id from sched_task_users where kind_id=2 and user_id="'.$user_id.'" )
		and p.id not in(select distinct sched_id from sched_task_users where kind_id=1 and user_id="'.$user_id.'" )
		and(
			(p.id not in(select distinct sched_id from sched_history where pdate>="'.$delay_dates[0].'" and user_id<>0 and user_id not in(select user_id from sched_task_users where kind_id=1 and sched_id=p.id))
			and p.priority="2")
			or 
			(p.id not in(select distinct sched_id from sched_history where pdate>="'.$delay_dates[1].'" and user_id<>0 and user_id not in(select user_id from sched_task_users where kind_id=1 and sched_id=p.id))
			and p.priority="1")
			or 
			(p.id not in(select distinct sched_id from sched_history where pdate>="'.$delay_dates[2].'" and user_id<>0 and user_id not in(select user_id from sched_task_users where kind_id=1 and sched_id=p.id))
			and p.priority="0")
		)    order by p.code desc';
		
		//echo $sql;
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$arr=array();
		for($i=0;$i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$this->ProcessFields($f);
			
			$arr[]=$f;		
		}
		//echo $rc;
		
		//print_r($arr);
		
		return $arr;
	}
	
	
	
	protected $_auth_result, $_sg;
	
	//установка всех имен
	protected function init(){
		$this->tablename='sched';
		$this->pagename='leads.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		$this->_sg=new Sched_SupplierGroup;
		
		$this->_auth_result=NULL;
	} 
	
	
	protected function GainSql($user_id=0){
		
		$sql='select distinct p.id, p.*,
		s.name as status_name,
		u.name_s as manager_name, u.login as manager_login, u.is_active as manager_is_active,
		
		up.name_s as confirmed_price_name, up.login as confirmed_price_login, p.confirm_pdate as confirm_price_pdate,
		us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login, p.confirm_done_pdate as confirm_shipping_pdate,
			m.name as meet_name,
			
			u1.name_s as user_name_1, u1.login as user_login_1,
			u2.name_s as user_name_2, u2.login as user_login_2,
			
			uf.name_s as confirmed_fulfil_name, uf.login as confirmed_fulfil_login,
			par.code as parent_code, par.topic as parent_topic, ps.name as parent_status_name,
			
			cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active
					 
				from '.$this->tablename.' as p
				left join document_status as s on s.id=p.status_id
				left join user as u on u.id=p.manager_id
				left join user as up on up.id=p.user_confirm_id
				left join user as us on us.id=p.user_confirm_done_id
				left join sched_meet as m on p.meet_id=m.id
				
				left join sched_task_users as stu on stu.sched_id=p.id and stu.kind_id=1
				left join user as u1 on u1.id=stu.user_id
				
				
				
				left join sched_task_users as stu2 on stu2.sched_id=p.id and stu2.kind_id=2
				left join user as u2 on u2.id=stu2.user_id
				
				left join sched_suppliers as ss on ss.sched_id=p.id
				left join supplier as sup on ss.supplier_id=sup.id
				
				
				left join user as uf on uf.id=p.user_fulfiled_id
				left join '.$this->tablename.' as par on par.id=p.task_id
				left join document_status as ps on ps.id=par.status_id
				
				left join user as cr on cr.id=p.created_id
					 
				where p.kind_id="1"';
				
		
		 
		return $sql;		 	
	}
	
	//обработка всех полей задачи
	protected function ProcessFields(&$f){
	 
		 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$_res=new Sched_Resolver($f['kind_id']);
			
			 
			
			//просрочено или нет
			/*
			статус != 10 !=3 !=1
			и крайний срок !=null <now
			*/
			$expired=false;
			$exp_ptime=NULL;
			if($f['pdate_beg']!==""){
				$exp_ptime	= Datefromdmy( DateFromYmd($f['pdate_beg']))+ (int)substr($f['ptime_beg'], 0,2)*60*60 + (int)substr($f['ptime_beg'],3,2)*60;
				
			 
				 
				//echo date('d.m.Y H:i:s', $exp_ptime).'<br>';
			}
			
			if(
			
			($f['status_id']!=10) && ($f['status_id']!=3) && ($f['status_id']!=1)
			
			&&
			($exp_ptime!==NULL) && ($exp_ptime<time())
			
			) $expired=true;
			$f['expired']=$expired; 
			 
			 

			 
			if($f['pdate_beg']!=="") $f['pdate_beg']=DateFromYmd($f['pdate_beg']);
			
			if($f['pdate_end']!=="") $f['pdate_end']=DateFromYmd($f['pdate_end']);
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date('d.m.Y H:i:s', $f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			 
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date('d.m.Y H:i:s', $f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			 
			 
			if($f['fulfiled_pdate']!=0) $f['fulfiled_pdate']=date('d.m.Y H:i:s', $f['fulfiled_pdate']);
			else $f['fulfiled_pdate']='-'; 
			 
			 $f['full_title']=$_res->instance->ConstructFullName($f['id']);
			 
			
			 
			 
	}
}



?>