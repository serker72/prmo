<?
require_once('abstractitem.php');

require_once('docstatusitem.php');

require_once('acc_group.php');
require_once('paygroup.php');


require_once('acc_in_item.php');

require_once('acc_group.php');



require_once('actionlog.php');
require_once('authuser.php');


require_once('billitem.php');
//require_once('sh_i_item.php');
require_once('acc_item.php');


require_once('billpospmformer.php');

require_once('bill_in_item.php');
 
require_once('acc_in_item.php');


require_once('bill_in_group.php');
 
require_once('acc_in_group.php');


require_once('maxformer.php');
require_once('authuser.php');
require_once('billcreator.php');
require_once('bdetailsitem.php');
require_once('actionlog.php');
require_once('billgroup.php');

require_once('invsync.php');
require_once('period_checker.php');




//абстрактный элемент
class InvItem extends AbstractItem{
	
	public $sync;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='inventory';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->sync=new InvSync;	
	}
	
	
	//удалить
	public function Del($id){
		
		
		
		$query = 'delete from inventory_position where inventory_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	public function Edit($id,$params,$scan_status=false,$result=NULL){
		$item=$this->GetItemById($id);
		
		
		
		//мы устанавливаем утверждение 1 гал.
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		//die();
		
		//если утверждаем - то пересобрать позиции
		if(isset($params['is_confirmed_inv'])&&($params['is_confirmed_inv']==1)){
			
			if($item!==false){
				 
				 
				 $this->sync->PutChanges($id,$result);
				 
				 
			}
			
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$result);
		
		//die();
	}
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions,$can_change_cascade=false){
		$_kpi=new InvPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array(
					'inventory_id'=>$v['inventory_id'],
					'position_id'=>$v['position_id'])
					 );
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['inventory_id']=$v['inventory_id'];
				
				$add_array['position_id']=$v['position_id'];
				 
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity_as_is']=$v['quantity_as_is'];
				$add_array['quantity_initial']=$v['quantity_as_is'];
				$add_array['quantity_fact']=$v['quantity_fact'];
				
				$_kpi->Add($add_array);
				
				$log_entries[]=array(
					'action'=>0,
					'position_id'=>$v['position_id'],
					 
					'quantity_as_is'=>$v['quantity_as_is'],
					'quantity_fact'=>$v['quantity_fact']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['inventory_id']=$v['inventory_id'];
				
				$add_array['position_id']=$v['position_id'];
				 
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity_as_is']=$v['quantity_as_is'];
				$add_array['quantity_fact']=$v['quantity_fact'];
			
			
				$_kpi->Edit($kpi['id'],$add_array); //, $add_pms,$can_change_cascade);
				
				//если есть изменения
				
				//как определить? изменились кол-ва
				
				$to_log=false;
				if($kpi['quantity_as_is']!=$add_array['quantity_as_is']) $to_log=$to_log||true;
				if($kpi['quantity_fact']!=$add_array['quantity_fact']) $to_log=$to_log||true;
				/*if($kpi['storage_id']!=$add_array['storage_id']) $to_log=$to_log||true;
				if($kpi['sector_id']!=$add_array['sector_id']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				*/
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'position_id'=>$v['position_id'],
					  
					'quantity_as_is'=>$v['quantity_as_is'],
					'quantity_fact'=>$v['quantity_fact']
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
				if(($vv['position_id']==$v['position_id'])
			 
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
					'position_id'=>$v['position_id'],
				 
					'quantity_as_is'=>$v['quantity_as_is'],
					'quantity_fact'=>$v['quantity_fact']
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
	
	//получим позиции
	public function GetPositionsArr($id,$result=NULL){
		$kpg=new InvPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$result);
		
		return $arr;		
		
	}
	
	
	
	
	//контроль возможности удаления
	public function CanDelete($id, &$reason,$itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed']!=0)||($itm['is_confirmed_inv']!=0))) {
			$reason.='распоряжение на инвентаризацию утверждено';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
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
				$log->PutEntry($_result['id'],'смена статуса инвентаризационного акта',NULL,326,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&(($old_params['status_id']==2))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса инвентаризационного акта',NULL,326,NULL,'установлен статус '.$stat['name'],$item['id']);
			}
		}else{
			//отследить переходы из 2-9, 9-2, 9-10, 10-9
		  
		  //переход 2-9
		 /* if($item['status_id']==2){
			  //проверить количество п	
			  //также может произойти переход 2-10
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
			  }else{
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }
		  
		  //переход 9-10 - все позиции завезены, все совпадает (либо совершили выравнивание)
		  if($item['status_id']==9){
			  //проверить количество п	
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }
		  
		  //переход 10-9 - не все позиции совпадают
		  if($item['status_id']==10){
			  //проверить количество п	
			  if(!$this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>9));
				  
				  $stat=$_stat->GetItemById(9);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
			  }
		  }	
			
			*/
			
			
		}
		
		if(isset($new_params['is_confirmed_inv'])&&isset($old_params['is_confirmed_inv'])){
			if(($new_params['is_confirmed_inv']==1)&&($old_params['is_confirmed_inv']==0)&&($old_params['status_id']==2)){
				//смена статуса с 2 на 16
				$this->Edit($id,array('status_id'=>16));
				
				$stat=$_stat->GetItemById(16);
				$log->PutEntry($_result['id'],'смена статуса инвентаризационного акта',NULL,326,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_inv']==0)&&($old_params['is_confirmed_inv']==1)&&(($old_params['status_id']==16))){
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса инвентаризационного акта',NULL,326,NULL,'установлен статус '.$stat['name'],$item['id']);
			}	
			
		}
		
		
		//die();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}else{
		
		  //проверить связанные поступления
		  $_accg=new AccGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' реализация № '.$v['id'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />По распоряжению на инвентаризацию имеются утвержденные реализации: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		   $_accg=new AccInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />По распоряжению на инвентаризацию имеются утвержденные поступления: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  
		
		  
		  
		  
		  $_accg=new BillGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' счет № '.$v['code'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />По распоряжению на инвентаризацию имеются утвержденные исходящие счета: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  $_accg=new BillInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' счет № '.$v['code'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='<br />По распоряжению на инвентаризацию имеются утвержденные входящие счета: ';
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  
	
		  
		  
		
		}
		
		
		
		return $can;
	}
	
	
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
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//список связанных не аннулированных не утв документов для автоаннулирования
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		$can=true;
		//проверить связанные поступления
		  $_accg=new AccGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==4) {
				  
				  $can=$can&&false;
				  $reasons[]=' реализация № '.$v['id'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\nпо счету имеются неутвержденные реализации: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		   $_accg=new AccInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==4) {
				  
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\nпо счету имеются неутвержденные поступления: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		
		  
		  //проверить связанные поступления
		  $_accg=new BillGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==1) {
				  
				  $can=$can&&false;
				  $reasons[]=' исходящий счет № '.$v['code'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\nпо счету имеются неутвержденные исходящие счета: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		   //проверить связанные поступления
		  $_accg=new BillInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['status_id']==1) {
				  
				  $can=$can&&false;
				  $reasons[]=' входящий счет № '.$v['code'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.='\nпо счету имеются неутвержденные входящие счета: ';
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		  
		  
		
		return $reason;
	}
	
	public function AnnulBindedDocuments($id, $_result=NULL){
		
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		$set=new MysqlSet('select * from acceptance where is_incoming=1 and inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием инвентаризационного акта',NULL,336,NULL,'поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['inventory_id']);
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием инвентаризационного акта',NULL,674,NULL,'поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where is_incoming=1 and inventory_id="'.$id.'"');
		
		$set=new MysqlSet('select * from acceptance where is_incoming=0 and inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование реализации в связи с аннулированием инвентаризационного акта',NULL,336,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['inventory_id']);
			
			$log->PutEntry($_result['id'],'аннулирование реализации в связи с аннулированием инвентаризационного акта',NULL,242,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where is_incoming=0 and inventory_id="'.$id.'"');
		
		
		
		
		
		
		
		
		
		$set=new MysqlSet('select * from bill where  is_incoming=1 and  inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование входящего счета в связи с аннулированием инвентаризационного акта',NULL,626,NULL,'распоряжение № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
			
			
		}	
		
		$ns=new NonSet('update bill set status_id=3 where  is_incoming=1 and   inventory_id="'.$id.'"');
		
		
		
		$set=new MysqlSet('select * from bill where is_incoming=0 and   inventory_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование исходящего счета в связи с аннулированием инвентаризационного акта',NULL,94,NULL,'распоряжение № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
			
			
		}	
		
		$ns=new NonSet('update bill set status_id=3 where  is_incoming=0 and  inventory_id="'.$id.'"');
		
		
	}
	
	
	
	public function DocCanConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			$reasons[]='у акта утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата инвентаризации '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	public function DocCanUnConfirm($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']!=1){
			
			$can=$can&&false;
			$reasons[]='у акта не утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата инвентаризации '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	
	
	public function DocCanConfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_inv']!=0){
			
			$can=$can&&false;
			$reasons[]='у акта утверждена коррекция складского остатка';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата инвентаризации '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв. коррекции и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_inv']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у акта не утверждена коррекция складского остатка';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //проверить связанные поступления
		  $_accg=new AccInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' поступление № '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' по акту имеются утвержденные поступления: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  //проверить связанные поступления
		  $_accg=new AccGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' реализация № '.$v['id'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			  if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' по акту имеются утвержденные реализации: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		 
		  
		
		  
		  
		   //проверить связанные счета
		  $_accg=new BillInGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' входящий счет № '.$v['code'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' по акту имеются утвержденные входящие счета: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		    //проверить связанные счета
		  $_accg=new BillGroup;
		  $_accg->setidname('inventory_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed_price']==1) {
				  $can=$can&&false;
				  $reasons[]=' исходящий счет № '.$v['code'].'';	
			  }
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.=', ';
			  $reason.=' по акту имеются утвержденные исходящие счета: ';
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		 $reasons=array();
		  //контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['inventory_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' дата инвентаризации '.$rss23;	
		  }
		   if(strlen($reason)!=0) $reason.=', ';
		   $reason.=implode(', ',$reasons);
		
		}
		
		return $can;
	}
	
	
	//контроль даты инвентаризации!!!
	public function CheckInventoryPdate($pdate, $sector_id, &$rss, $except_id=0){
		$res=true; //все ок
		$_dsi=new DocStatusItem;
		
		$sql='select * from '.$this->tablename.' where inventory_pdate>="'.$pdate.'" and sector_id="'.$sector_id.'" and id<>"'.$except_id.'" and status_id in(2,16)';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0) $res=false;
		
		$rss=''; $_rss=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$dsi=$_dsi->GetItemById($f['status_id']);
			$_rss[]='инвентаризационный акт № '.$f['code'].', дата инвентаризации '.date('d.m.Y', $f['inventory_pdate']).', статус '.$dsi['name'] ;
		}
		
		if(count($_rss)>0) $rss="существуют акты инвентаризации: ".implode('\n',$_rss);
		
		return $res;
	}
	
	
	
	
	
	
}
?>