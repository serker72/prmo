<?
require_once('billitem.php');
require_once('ispositem.php');
require_once('billpospmformer.php');
require_once('isposgroup.php');
require_once('authuser.php');
require_once('period_checker.php');

//абстрактный элемент
class IsCustomItem extends BillItem{
	protected $is_or_writeoff;
	
	public function __construct($is_or_writeoff=0){
		$this->init($is_or_writeoff);
	}
	
	
	//установка всех имен
	protected function init($is_or_writeoff=0){
		$this->tablename='interstore';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->is_or_writeoff=$is_or_writeoff;	
	}
	
	
	//удалить
	public function Del($id){
		
		
		
		
		$query = 'delete from interstore_position where interstore_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions){
		$_kpi=new IsPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('interstore_id'=>$v['interstore_id'],'position_id'=>$v['position_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['interstore_id']=$v['interstore_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['interstore_id']=$v['interstore_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms);
				
				//если есть изменения
				
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				
				if($to_log){
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'quantity'=>$v['quantity'],
					  'price'=>$v['price'],
					  'pms'=>$v['pms']
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
				if($vv['position_id']==$v['id']){
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
			$pms=NULL;
			if($v['plus_or_minus']==1){
				$pms=array(
						'plus_or_minus'=>$v['plus_or_minus'],
						'rub_or_percent'=>$v['rub_or_percent'],
						'value'=>$v['value']
					);	
			}
			
			$log_entries[]=array(
					'action'=>2,
					'name'=>$v['position_name'],
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$pms
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//получим позиции
	public function GetPositionsArr($id){
		$kpg=new IsPosGroup;
		$arr=$kpg->GetItemsByIdArr($id);
		
		
		
		return $arr;		
		
	}
	
	//получим позиции (упрощенный вариант)
	public function GetSimplePositions($id){
		//список позиций
	
		$arr=array();
		
		$sql='select p.id as p_id, p.interstore_id, p.komplekt_ved_pos_id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.quantity_initial
					 
		from  interstore_position as p 
			
			
		where p.interstore_id="'.$id.'" order by position_name asc, id asc';
		
		//echo $sql;
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	public function Edit($id,$params,$scan_status=false){
		$item=$this->GetItemById($id);
		
		AbstractItem::Edit($id, $params);
		
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params);
		
	}
	
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL){
		$log=new ActionLog();
			$au=new AuthUser;
			if($_result===NULL) $_result=$au->Auth();
			$_stat=new DocStatusItem;
			$item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==1)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на межсклад',NULL,101,NULL,'установлен статус '.$stat['name'],$id);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на межсклад',NULL,101,NULL,'установлен статус '.$stat['name'],$id);
			}
			
			
			
		}
		
		if(isset($new_params['is_confirmed_wf'])&&isset($old_params['is_confirmed_wf'])){
				
			//echo 'zzzzzzzzzzz'; die();
			if(($new_params['is_confirmed_wf']==1)&&($old_params['is_confirmed_wf']==0)&&($old_params['status_id']==2)){
				//смена статуса с 2 на 17
				$this->Edit($id,array('status_id'=>17));
				
				$stat=$_stat->GetItemById(17);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на межсклад',NULL,101,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_wf']==0)&&($old_params['is_confirmed_wf']==1)&&(($old_params['status_id']==17))){
				//17 => 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на межсклад',NULL,101,NULL,'установлен статус '.$stat['name'],$item['id']);
			}	
			
		}
	}
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
		}
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//запрос о возможности утверждения и возвращеня причины, почему нельзя утвердить
	public function DocCanConfirm($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='документ утвержден';
		}
		
		//проверять позиции. Если нет, если нулевые кол-ва - нельзя утв.
		$positions=$this->GetPositionsArr($id);
		
		if(count($positions)==0){
			$can=$can&&false;
			
			$reasons[]='не выбраны позиции распоряжения на межсклад';
		}
		$total_count=0;
		
		foreach($positions as $k=>$v){
			$total_count+=$v['quantity'];
		}
		if((count($positions)>0)&&($total_count==0)){
			$can=$can&&false;
			
			$reasons[]='указаны нулевые количества позиций распоряжения на межсклад';
		}
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на межсклад '.$rss23;	
		}
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	public function DocCanConfirmWf($id,&$reason,$user_id=NULL, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed_wf']==1){
			
			$can=$can&&false;
			
			$reasons[]='списание утверждено';
		}
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='отгрузка не утверждена';
		}
		
		
		
		//проверять позиции. Если нет, если нулевые кол-ва - нельзя утв.
		$positions=$this->GetPositionsArr($id);
		
		if(count($positions)==0){
			$can=$can&&false;
			
			$reasons[]='не выбраны позиции распоряжения на межсклад';
		}
		$total_count=0;
		
		foreach($positions as $k=>$v){
			$total_count+=$v['quantity'];
		}
		if((count($positions)>0)&&($total_count==0)){
			$can=$can&&false;
			
			$reasons[]='указаны нулевые количества позиций распоряжения на межсклад';
		}
		
		
		
		//добавим контроль по объекту и пользователю
		if(!$this->CheckWfUserSenderStorage($item['sender_storage_id'],$user_id, $item['sender_sector_id'])){
			$can=$can&&false;
			$_st=new StorageItem;
			$st=$_st->GetItemById($item['sender_storage_id']);
			$reasons[]='у Вас недостаточно прав для списания продукции с объекта '.$st['name'];
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на межсклад '.$rss23;	
		}
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//контроль снятия списания
	public function DocCanUnconfirmWf($id,&$reason, $item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		//првоерить связанные счета
		 $set=new mysqlSet('select p.*, s.name from bill as p inner join document_status as s on p.status_id=s.id where interstore_id="'.$id.'" and ( is_confirmed_price=1 or is_confirmed_shipping=1)');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			//  $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' счет № '.$v['code'].' статус документа: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) {
			if(strlen($reason)!=0) $reason.='; ';
			$reason.='имеются связанные счета: ';
			$reason.=implode(', ',$reasons);
		  }
		 
		
		//проверить связанные распоряжения на приемку
		  $set=new mysqlSet('select p.*, s.name from sh_i  as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id in(select  id from bill where interstore_id="'.$id.'" )');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' распоряжение на приемку № '.$v['id'].' статус документа: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) {
			if(strlen($reason)!=0) $reason.='; ';
			$reason.=' имеются связанные распоряжения на приемку: ';
			$reason.=implode(', ',$reasons);
		  
		  }
		
		
		//проверить связанные поступления
		 //проверить связ оплаты
		  $set=new mysqlSet('select p.*, s.name from acceptance  as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id in(select  id from bill where interstore_id="'.$id.'" )');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  
			  
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  //if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].' статус документа: '.$v['name'];	
			 // }
			  
		  }
		  if(strlen($reason)!=0) $reason.='; ';
		  if(count($reasons)>0) $reason.=' имеются связанные поступления: ';
		  $reason.=implode(', ',$reasons);
		  
		  $reasons=array(); 
		  //контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на межсклад '.$rss23;	
		}
		if(count($reasons)>0){
		 if(strlen($reason)!=0) $reason.='; ';
		 $reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	
	
	//контроль по объекту и пользователю
	public function CheckWfUserSenderStorage($storage_id, $user_id=NULL, $sector_id){
		$res=false;
		
		if($user_id===NULL){
			$au=new AuthUser();
			$result=$au->Auth();
			$user_id=$result['id'];
			
			//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
		}
		
		//проверим секретность об-та, уч-ка
		$_sc=new SectorItem;
		$_st=new StorageItem;
		$sc=$_sc->GetItemById($sector_id);
		$st=$_st->GetItemById($storage_id);
		
		if(($sc['s_s']==1)&&($st['s_s']==1)){
			$sql1='select count(*) from user_rights where object_id=448 and user_id="'.$user_id.'" and right_id=2';
			//echo $sql1;
			$set1=new mysqlset($sql1);
	    	$rs1=$set1->getResult();
			$g=mysqli_fetch_array($rs1);
			
			$res=((int)$g[0]>0);
			
		}else{
		
		
			//if(!$au->user_rights->CheckAccess('r',289)
		  
		  //найдем правило для объекта
		  $sql='select object_id from interstore_storage_to_object where storage_id="'.$storage_id.'" limit 1';
		  //echo $sql;
		  
		  $set=new mysqlset($sql);
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		 
		  if($rc>0){
		  //for($i=0; $i<$rc; $i++){
		  //
			  $f=mysqli_fetch_array($rs);
			  //$res=$au->user_rights->CheckAccess('w',$f['object_id']);
			  $sql1='select count(*) from user_rights where object_id="'.$f['object_id'].'" and user_id="'.$user_id.'" and right_id=2';
			  //echo $sql1;
			  $set1=new mysqlset($sql1);
			  $rs1=$set1->getResult();
			  $g=mysqli_fetch_array($rs1);
			  $res=((int)$g[0]>0);
		
		  }/*else $res=true;*/
		  
		}
		return $res;
	}
	
	
	
	//список связанных не аннулированных не утв документов для автоаннулирования
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		
		//првоерить связанные счета
		 $set=new mysqlSet('select p.*, s.name from bill  as p inner join document_status as s on p.status_id=s.id where interstore_id="'.$id.'" and status_id<>3');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' счет № '.$v['code'].' статус документа: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.='\nимеются связанные счета: ';
		  $reason.=implode(', ',$reasons);
		  
		 
		
		//проверить связанные распоряжения на приемку
		  $set=new mysqlSet('select p.*, s.name from sh_i  as p inner join document_status as s on p.status_id=s.id where status_id<>3 and bill_id in(select  id from bill where interstore_id="'.$id.'" and status_id<>3)');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
				  $can=$can&&false;
				  $reasons[]=' распоряжение на приемку № '.$v['id'].' статус документа: '.$v['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.='\nимеются связанные распоряжения на приемку: ';
		  $reason.=implode(', ',$reasons);
		  
		 
		
		
		//проверить связанные поступления
		 //проверить связ оплаты
		  $set=new mysqlSet('select p.*, s.name from acceptance  as p inner join document_status as s on p.status_id=s.id where status_id<>6 and bill_id in(select  id from bill where interstore_id="'.$id.'" and status_id<>3)');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  
			  
			 // $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  //if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].' статус документа: '.$v['name'];	
			 // }
			  
		  }
		  if(count($reasons)>0) $reason.='\nимеются связанные поступления: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		
	
		return $reason;
	}
	
}
?>