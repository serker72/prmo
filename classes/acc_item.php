<?
require_once('billitem.php');
require_once('invcalcitem.php');
require_once('acc_positem.php');
require_once('billpospmformer.php');
require_once('acc_posgroup.php');

require_once('docstatusitem.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('maxformer.php');
require_once('rights_detector.php');
require_once('supplieritem.php');
require_once('billdates.php');

require_once('acc_sync.php');
require_once('period_checker.php');


require_once('supcontract_item.php');
require_once('supcontract_group.php');
require_once('period_checker.php');
require_once('acc_in_item.php');

require_once('payforaccgroup.php');
require_once('payforaccitem.php');

require_once('accsync.php');
require_once('acc_notesitem.php');

//абстрактный элемент
class AccItem extends BillItem{
	public $rd;
	public $sync;
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';
		
		$this->rd=new RightsDetector($this);
		$this->sync=new AccSync;	
	}
	
	
	
	public function Add($params){
		$code=AbstractItem::Add($params);
		
		if(isset($params['given_pdate'])){
			$this->SyncPlanShipDate($code, $params['given_pdate']);	
		}
		
	/*	if(isset($params['sh_i_id'])){
			$_sh=new ShIItem;
			$_sh->ScanDocStatus($params['sh_i_id'],array(),array());	
		}
		*/
		return $code;
	}
	
	public function Edit($id,$params,$scan_status=false, $_auth_result=NULL){
		$item=$this->GetItemById($id); $log=new ActionLog;
		
		$_bi=new BillItem;
		$old_bill_summ=$_bi->CalcCost($item['bill_id']);
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=6)&&($item['status_id']==6)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		
		//if(isset($params['given_pdate'])){
		
		//синхронизация даты оплаты по договору в счете	
		$this->SyncPlanShipDate($id);	
		//}
		
		//если утверждаем - то прикрепить входящие оплаты
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)){
			
			
			$item_actual=$this->GetItemById($id);
			$_bi=new BillItem;
			$bill=$_bi->GetItemById($item_actual['bill_id']);
			
			$to_bind_ids=$this->GetLatestAccs($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate']);
			if(count($to_bind_ids)>0){
				$this->FreeBindedPayments(NULL, $to_bind_ids, 1, $_auth_result);
			}
			
			
			$this->BindPayments($id, $item['org_id'], NULL, $_auth_result);
			
			
			$to_bind_ids=$this->GetLatestAccs($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'], array($id));
			if(count($to_bind_ids)>0){
				$this->AutoBind($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'], 	$_auth_result,array($id));
			}
			
			
			
			 
		}
		
		//если утверждаем и есть связь между базами - автоматом создать/восстановить поступление в дочерней базе
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==1)&&($item['is_confirmed']==0)){
			
			$_accsync=new AccSync2($id,  $item['org_id'],   $_auth_result);
			$_accsync->Sync();
			
		}
		
		
		if(isset($params['is_confirmed'])&&($params['is_confirmed']==0)){
			//снимаем утверждение - очистить привязку оплат по цепочке
			$this->FreeBindedPayments($id,   NULL, 1, $_auth_result);
			
			$item_actual=$this->GetItemById($id);
			$_bi=new BillItem;
			$bill=$_bi->GetItemById($item_actual['bill_id']);
			
			$to_bind_ids=$this->GetLatestAccs($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'],array($id));
			if(count($to_bind_ids)>0){
				$this->FreeBindedPayments(NULL, $to_bind_ids, 1, $_auth_result);
			}
			
			if(count($to_bind_ids)>0){
				$this->AutoBind($bill['supplier_id'], $item_actual['org_id'], $item_actual['given_pdate'], 	$_auth_result);
			}
			//echo 'zzzzzzzzzz';
		//	var_dump($to_bind_ids);
			
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$_auth_result);
		
		//если не утверждаем - снять утверждения связанных реализаций
		if(($item['is_confirmed']==1)&&isset($params['is_confirmed'])&&($params['is_confirmed']==0)){
			$this->UnconfirmBindedDocuments($id);
		}
		
		
		if(isset($item['bill_id'])){
			$_sh=new BillItem;
			$_sh->ScanDocStatus($item['bill_id'],array(),array(),NULL,$_auth_result);	
		}
		
		//die();
		
	}
	
	
	//синхронизация даты оплаты по договору в счете
	public function SyncPlanShipDate($id, $pdate_shipping_plan, $item=NULL, $auth=NULL){
		if($item===NULL) $item=$this->getitembyid($id);
		
	
		
		$_log=new ActionLog;
		
		$_au=new AuthUser;
		if($auth===NULL) $auth=$_au->Auth();
		
		// var_dump($item); die();
		////синхронизация даты оплаты по договору в счете
		if(($item['is_confirmed']==1)&&($item['given_pdate']!=0)){
		  
		  $ts=new mysqlSet('select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and given_pdate>"'.$item['given_pdate'].'"');
		  //echo 'select count(*) from '.$this->tablename.' where bill_id="'.$item['bill_id'].'" and id<>"'.$id.'" and given_pdate>"'.$item['given_pdate'].'"';
		  
		 // die();
		  
		  $rs=$ts->getResult();
		  $rc=$ts->getResultNumRows();
		  $f=mysqli_fetch_array($rs);
		  if($f[0]==0){
			  
			//  echo 'nu;';
			  
			  $_bi=new BillItem();
			  $bi=$_bi->GetItemById($item['bill_id']);
			  
			  $_si=new SupplierItem;
			  $supplier=$_si->GetItemById($bi['supplier_id']);
			  
			   $_sci=new SupContractItem;
			  $sci=$_sci->GetItemById($bi['contract_id']);
			  
			  
			  $_bd=new BillDates;
			  
			  $eth=$_bd->FindEthalon($item['given_pdate'],$sci['contract_prolongation'], $sci['contract_prolongation_mode']);
			  
			  if($eth!=$bi['pdate_payment_contract']){
				$_bi->Edit($item['bill_id'], array('pdate_payment_contract'=>$eth),false,$auth);	
				
				$_log->PutEntry($auth['id'],'обновление даты оплаты счета по договору при обновлении заданной даты реализации',NULL, 93,NULL,'старое значение '.date('d.m.Y', $bi['pdate_payment_contract']).', новое значение даты оплаты по договору '.date('d.m.Y', $eth), $item['bill_id']);
				 $_log->PutEntry($auth['id'],'обновление даты оплаты счета по договору при обновлении заданной даты реализации',NULL, 235,NULL,'старое значение '.date('d.m.Y', $bi['pdate_payment_contract']).', новое значение даты оплаты по договору '.date('d.m.Y', $eth), $id);
			  
			  }
			 
			  
		  }
		}
	}
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from acceptance_position_pm where acceptance_position_id in(select id from acceptance_position where acceptance_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from acceptance_position where acceptance_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions,$change_high_mode=0,$change_low_mode=0){
		$_kpi=new AccPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('acceptance_id'=>$v['acceptance_id'],'position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id'], 'acceptance_in_id'=>$v['acceptance_in_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				
				$add_array=array();
				$add_array['acceptance_id']=$v['acceptance_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				$add_array['acceptance_in_id']=$v['acceptance_in_id'];
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms,$change_high_mode,$change_low_mode);
				
				$log_entries[]=array(
					'action'=>0,
					'name'=>$v['name'],
					'old_quantity'=>0,
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$v['pms']
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['acceptance_id']=$v['acceptance_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				$add_array['acceptance_in_id']=$v['acceptance_in_id'];
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$change_high_mode,$change_low_mode);
				//кол-во
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				
				if($to_log){
				  //если есть изменения
				  $log_entries[]=array(
					  'action'=>1,
					  'name'=>$v['name'],
					  'old_quantity'=>$kpi['quantity'],
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
				if(($vv['position_id']==$v['id'])&&($vv['komplekt_ved_id']==$v['komplekt_ved_id'])&&($vv['acceptance_in_id']==$v['acceptance_in_id'])){
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
					'old_quantity'=>$v['quantity'],
					
					'quantity'=>$v['quantity'],
					'price'=>$v['price'],
					'pms'=>$pms
			);
			
			//удаляем позицию
			$_kpi->Del($v['p_id']);
		}
		
		
		$item=$this->getitembyid($current_id);
		if(isset($item['bill_id'])){
			$_sh=new BillItem;
			$_sh->ScanDocStatus($item['bill_id'], array(),array());	
		}
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	
		//ресинхронизировать позиции со счетом и распоряжением
	public function ResyncPositions($id,$change_high_mode=0,$change_low_mode=0, $_result=NULL){
		$positions=$this->GetPositionsArr($id);
		$_kpi=new AccPosItem;
		
		$item=$this->GetItemById($id);
		
		foreach($positions as $k=>$v){
			//$kpi=$_kpi->GetItemByFields(array('acceptance_id'=>$v['acceptance_id'],'position_id'=>$v['position_id']));
			
			//print_r($v); die();
			
			$_kpi->SetChainQuantity($v['acceptance_id'],$v['id'], $v['komplekt_ved_id'],$v['quantity'],$change_high_mode,$change_low_mode, $_result, $item);
		}
			
	}
	
	
	
	//ресинхронизация оплат
	public function ResyncPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL){
		
		
		$au=new AuthUser();
		if($_result===NULL) $_result=$au->Auth();
		
		$item=$this->getitembyid($id);
		$_bi=new BillItem;
		
		if($new_bill_summ===NULL) $new_bill_summ=$_bi->CalcCost($id);
		
		
		$calc_payed=$_bi->CalcPayed($item['bill_id']);
		
		//$summ_by_bill=$_bi->CalcCost($item['bill_id']); $summ_by_acc=$_bi->CalcAcc($item['bill_id']);
		
		if($new_bill_summ>$old_bill_summ) {
		  //если уже были оплаты по счету, и сумма по поступлениям больше суммы счета - то допривязывать оплаты.
		  if(($calc_payed>0)/*&&($summ_by_acc>$summ_by_bill)*/){
			  
			  //echo 'zzz'; die();
			  
			  $_bi->FreeBindedPayments($item['bill_id'], 1, $_result);
			  $_bi->BindPayments(	$item['bill_id'], $_result['org_id'], $_result);
		  }
				
		}elseif($new_bill_summ<$old_bill_summ){
		  	//уменьшили сумму счета, и по счету были оплаты
			//если сумма оплат больше, чем сумма счета - уменьшить сумму оплат.
			if(($calc_payed>0)&&($calc_payed>$new_bill_summ)){
				$_bi->LowPayments($item['bill_id'], $_result, $old_bill_summ, $new_bill_summ,$calc_payed,$id);
			}
		}
		
	}
	
	
	//получим позиции
	public function GetPositionsArr($id, $show_statiscits=true, $show_boundaries=true){
		$kpg=new AccPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,true,true,$show_statiscits, $show_boundaries);
		
		return $arr;		
		
	}
	
	
	
	//найдем стоииость по заказу
	public function CalcCost($id, $positions=NULL, $changed_totals=NULL){
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions,$changed_totals);
		return $total_cost;
	}
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params, $_result=NULL, $item=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==4)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>5));
				
				$stat=$_stat->GetItemById(5);
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,93,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,219,NULL,'установлен статус '.$stat['name'],$item['sh_i_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,235,NULL,'установлен статус '.$stat['name'],$item['id']);
				
				
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&($old_params['status_id']==5)){
				$this->Edit($id,array('status_id'=>4));
				
				$stat=$_stat->GetItemById(4);
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,93,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,219,NULL,'установлен статус '.$stat['name'],$item['sh_i_id']);
				
				$log->PutEntry($_result['id'],'смена статуса реализации',NULL,235,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}
		}
		
		//$_sh=new 
	}
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=4){
			
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
			$this->Edit($id, array('status_id'=>6));	
		}
	}
	
	public function DocCanRestore($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=6){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
		}
		
		
		$can=$can&&$this->CanConfirmByPositions($id,$rss);
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		
		//$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	
	//запрос о возможности утверждения и возвращеня причины, почему нельзя утвердить
	public function DocCanConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='документ утвержден';
		}
		
		if($item['given_pdate']==0){
			$can=$can&&false;
			$reasons[]='не введена заданная дата с/ф ';	
			
		}
		/*elseif($item['given_pdate']>DateFromdmY(date('d.m.Y'))){
			$can=$can&&false;
			$reasons[]='заданная дата с/ф '.date('d.m.Y',$item['given_pdate']).' превышает текущую';	
		}*/
		
		
		if($item['given_no']==''){
			$can=$can&&false;
			$reasons[]='не введен заданный номер с/ф ';	
			
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='заданная дата с/ф '.$rss23;	
		}
		
		
		//контроль заданной даты - должна быть не раньше самого позднего поступления согласно позициям
		//в отдельную проверку!!!
		$can=$can&&($this->CanConfirmByPdates($id, $item['given_pdate'], $rss3));  
		if($rss3!='') $reasons[]=$rss3;
		
		 
		
		 
		
		//контроль простановки утверждения: 1.1*(число свободных позиций по заявке) д.быть больше
		//чем кол-во в реализации
		$can1=$this->CanConfirmByPositions($id,$rss,$item);
		
		$can=$can&&($this->CanConfirmByPositions($id,$rss,$item));
		
		if($rss!='') $reasons[]=$rss;
		
		
		//var_dump($this->CanConfirmByPositions($id,$rss,$item));
		
		$reason=implode(', ',$reasons);
		/*if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		} */
		
		return $can;	
	}
	
	
	
	//проверка, можно ли утвердить с этой заданной датой
	public function CanConfirmByPdates($id, $given_pdate, &$wreason){
		$wreason=''; $reasons=array();
		$can=true;	
		
		
		
		$acc_ins=$this->GetAccInsbyPos($id);
		//print_r($acc_ins);
		//если поступлений нет - лазейка для старых документов - не проверяем ничего
		if(count($acc_ins)>0){
			//если поступления есть - то берем первую позицию - это самая поздняя дата 
			//наша заданная дата д.б. >= этой дате
			 
			//echo date('d.m.Y', $check_pdate);
			
			
			if($acc_ins[0]['given_pdate']>$given_pdate){
				
				/*echo $acc_ins[0]['given_pdate'].' vs '.$given_pdate;
				echo date('d.m.Y',$acc_ins[0]['given_pdate']).' vs '.date('d.m.Y',$given_pdate);
			*/
			

				
				$can=$can&&false;
				
				$reasons[]='введенная заданная дата с/ф '.date('d.m.Y', $given_pdate).' должна быть не ранее заданной даты самого позднего поступления № '.$acc_ins[0]['id'].', заданный номер '.$acc_ins[0]['given_no'].' от '.date('d.m.Y', $acc_ins[0]['given_pdate']).'  ';	
			}
		}
		
		$wreason=implode(', ',$reasons);
		
		return $can;
	}
	
	
	//проверка, свободны ли позиции поступления для восстановления/утверждения
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	

		if($item===NULL) $item=$this->getitembyid($id);
		$mf=new MaxFormer;
		
		
		  
		$_sh=new BillItem;
		$ship=$_sh->GetItemById($item['bill_id']);
		if(($ship['is_confirmed_shipping']==1)&&($ship!==false)){
		  
		  
		  $ship_positions=$_sh->GetPositionsArr($item['bill_id'],false,$ship);
		  $positions=$this->GetPositionsArr($id,false,false);
		  
		  //переберем позиции реализации
		  //сравним со статистикой исх. счета
		  //также сравнить со статистикой по поступлениям!
		  //если превышение - то заносим в список причин
		  
		  foreach($positions as $k=>$v){
			  if(!$this->PosInSh($v,$ship_positions,$find_pos)){
				  $can=$can&&false;
				  $reasons[]='в родительском счете не найдена позиция '.SecStr($v['position_name']);	
				  continue;	
			  }
			  
			 //найдена.. сравним количества
			  $vsego=$find_pos['quantity'];
			  
			  $free=$mf->MaxForAcc($item['bill_id'], $v['id'], $id, $v['komplekt_ved_id']) ;
			  
						  
			  if($v['quantity']>$free*PPUP){
				  //превышение
				  $can=$can&&false;
				  $reasons[]='количество позиции '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).', заявка №'.$v['komplekt_ved_id'].' превышает доступное по счету ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
				  continue;		
			  }
			  
			  
			  //только если это не услуга!!!
			  if($v['is_usl']==0){
				  $free=$mf->MaxForAccByAccIn($item['bill_id'],  $v['id'],  $id,NULL,$v['storage_id'],$v['sector_id'], $v['komplekt_ved_id'], $v['acceptance_in_id']);
				  if($v['quantity']>$free*PPUP){
					  //превышение
					  $can=$can&&false;
					  $reasons[]='количество позиции '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).', заявка №'.$v['komplekt_ved_id'].' превышает доступное по связанным поступлениям ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
					  continue;		
				  }
			  }
			  //$_sh_r->InAcc($v['id'],$item['bill_id'],'',
		  }
		}else{
			$can=$can&&false;
				  $reasons[]='не утвержден счет № '.$ship['code'];	
				  
		}
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	
	
	//запрос о возможности СНЯТИЯ утверждения и возвращеня причины, почему нельзя НЕ утвердить
	public function DocCanUnConfirm($id,&$reason, $item=NULL, $periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='документ не утвержден';
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['given_pdate'], $item['org_id'],$rss1,$periods)){
			$can=$can&&false;
			$reasons[]='заданная дата с/ф '.$rss1;	
		}
		
		
		
		//если по позициям реализации есть выданный +/- то не давать снять утверждение,
		//возвращать список расходов наличных, их статусов и номеров
		$sql='select distinct c.id, c.code, c.status_id, st.name as status_name from cash as c
			left join document_status as st on st.id=c.status_id			
			where c.is_confirmed_given=1
			and c.id in(
				select distinct cb.cash_id from cash_bill_position as cb
				inner join bill_position as bp on bp.id=cb.bill_position_id
				inner join bill as b on b.id=bp.bill_id
				
				inner join acceptance as a on a.bill_id=b.id
				
				inner join acceptance_position as ap on (ap.acceptance_id=a.id and bp.position_id=ap.position_id and bp.komplekt_ved_id=ap.komplekt_ved_id )
				 
				where  ap.acceptance_id="'.$id.'"	
						
				 
			)
			order by c.code asc
			  ';
		
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0){
			$can=$can&&false;
			$rss2='по позициям родительского исходящего счета есть выданный +/- согласно документам: ';
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$rss2.=' расход наличных № '.$f['code'].', статус '.$f['status_name'].'; ';
			}
			$rss2.=' для снятия утверждения реализации необходимо снять утверждение выдачи указанных расходов наличных';
			
			$reasons[]=$rss2;
		}
		
		
		
		
		
		$reason=implode(', ',$reasons);
		if(strlen($rss)>0){
			if(strlen($reason)>0){
				$reason.=', ';
			}
			$reason.=$rss;
		}
		
		return $can;	
	
	}
	
	
	//есть ли данная позиция в расп. на приемку. есть - вернуть поз. расп на пр, нет - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if($v['id']==$acc_position['id']){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	//есть ли услуги в поступлении
	public function HasUsl($id, $positions=NULL){
		
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false,false);
		
		$has=false;
		foreach($positions as $k=>$v){
			if(($v['is_usl']==1)||($v['is_semi_usl']==1)){
				$has=true;
				break;	
			}
		}
			
		return $has;
	}
	
	
	//есть ли товары в поступлении
	public function HasTov($id, $positions=NULL){
		
		if($positions===NULL) $positions=$this->GetPositionsArr($id,false,false);
		
		$has=false;
		foreach($positions as $k=>$v){
			if(($v['is_usl']!=1)&&($v['is_semi_usl']!=1)){
				$has=true;
				break;	
			}
		}
			
		return $has;
	}
	
	
	//есть ли +/- в родительском счете
	public function ParentBillHasPms($acc_id, $acc_item=NULL){
		
		if($acc_item===NULL) $acc_item=$this->getitembyid($acc_id);
		
		$sql='select count(*) from bill_position_pm where bill_position_id in(select id from  bill_position where bill_id="'.$acc_item['bill_id'].'")';	
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		//var_dump((int)$f[0]>0);
		
		return ((int)$f[0]>0);
			
	}
	
	
	
	
	//получить самую позднюю заданную дату по связанным поступлениям
	public function GetGivenPdate($bill_id,  $acc_ids=NULL){
		$flt='';
		if(($acc_ids!==NULL)&&(count($acc_ids)>0)) $flt=' and id in('.implode(', ',$acc_ids).') ';
		
		$sql='select given_pdate from acceptance where is_incoming=1 '.$flt.' and is_confirmed=1 and bill_id in(select id from bill where out_bill_id="'.$bill_id.'") limit 1';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		//$rc=$set->getresultnumrows();
		
		$f=mysqli_fetch_array($rs);
		
		return $f[0];
		
		
	}

	//получить список поступлений по род. счету (связ. поступлений)
	public function GetAccIns($bill_id, $acc_ids=NULL){
		$flt='';
		if(($acc_ids!==NULL)&&(count($acc_ids)>0)) $flt=' and id in('.implode(', ',$acc_ids).') ';
		
		$arr=array();
		$sql='select id, given_no, given_pdate from acceptance where is_incoming=1 and is_confirmed=1 and bill_id in(select id from bill where out_bill_id="'.$bill_id.'") '.$flt.' order by given_pdate';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			$f['given_pdate']=date('d.m.Y', $f['given_pdate']);
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//получить список поступлений по позициям (новый алгоритм)
	public function GetAccInsbyPos($id){
		$arr=array();	
		$sql='select distinct a.id, a.given_pdate, a.given_no from acceptance_position as p
		inner join acceptance as a on a.id=p.acceptance_in_id and p.acceptance_in_id<>0
		where a.is_confirmed=1 
		and p.acceptance_id="'.$id.'"
		order by a.given_pdate desc
		';
		//echo $sql;
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	
	
//****************************************** блок распределения вход оплат ***************************************	
	
	
	
	//привязать реализацию к оплатам с авансом
	public function BindPayments($id,$org_id, $item=NULL, $_result=NULL,  $period_begin=NULL, $period_end=NULL){
		if($item===NULL) $acc=$this->GetItemById($id);
		else $acc=$item;
		
		if($acc===false) return;
		$_bpi=new PayForAccItem;
		$_pfg=new PayForAccGroup;
		//$_bpf=new BillPosPMFormer;
		
		$_bill=new BillItem;
		$bill=$_bill->GetItemById($acc['bill_id']);
		 $_inv=new InvCalcItem;
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		
		$opl_codes=$_pfg->GetAvans($bill['supplier_id'], $org_id, $acc['given_pdate'], $id, $avans, $opl_ids,  $inv_ids,NULL, $period_begin, $period_end);
		
		$do_reverse_pdate=false; 
		
		//echo 'Реализация '.$id.' '.$acc['given_no'].' прямой аванс: '.$avans.'<br>';
		
		if(((count($opl_ids)==0)&&(count($inv_ids)==0))||($avans<=0)){
			//попробуем получить "обратный" аванс по более поздним документам
			
			
				
			$opl_codes=$_pfg->GetAvans($bill['supplier_id'], $org_id, $acc['given_pdate'], $id, $avans, $opl_ids,  $inv_ids,NULL, $period_begin, $period_end,true);
			
			$do_reverse_pdate=true;
			
			//echo 'Реализация '.$id.' '.$acc['given_no'].' обратный аванс: '.$avans.'<br>';
		}
		 
		 
		 
		  
		if((count($opl_ids)==0)&&(count($inv_ids)==0)) return;
		if($avans<=0) return;
		
		
		
		$total_cost=$this->CalcCost($id);   
		
		$sum_by_bill=$_pfg->SumByAcc($id);
		if($total_cost<=$sum_by_bill) return;
		
		$delta=$total_cost-$sum_by_bill;
		
		
		//суммы по актам
		if(count($inv_ids)>0){
			
			$period_flt='';
			//if(($period_begin!==NULL)&&($period_end!==NULL)) $period_flt=' and  (invcalc_pdate between "'.$period_begin.'" and   "'.$period_end.'")';
			
			
			$pdate_flt='';
			if($do_reverse_pdate) $pdate_flt=' and invcalc_pdate>="'.$acc['given_pdate'].'" ';
			else $pdate_flt=' and invcalc_pdate<"'.$acc['given_pdate'].'" ';
			
			$sql='select * from invcalc where 
				is_confirmed_inv=1 
				and supplier_id="'.$bill['supplier_id'].'" 
				and org_id="'.$org_id.'" 
				and id in('.implode(', ',$inv_ids).') 
				/*and invcalc_pdate<"'.$acc['given_pdate'].'"*/
				'.$pdate_flt.'
				'.$period_flt.'
				
				';
		 
			//echo $sql; die();
			
			$set=new mysqlset($sql);
			
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  $real_debt_stru=$_inv->FindRealDebt($f['id'],$f);
			  $f['real_debt']=$real_debt_stru['real_debt'];
			  $f['real_debt_id']=$real_debt_stru['real_debt_id'];
			  
			 // echo 'zz';
			  if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			  
			  $set1=new mysqlset('select sum(value) from payment_for_acceptance where invcalc_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			   //если эта оплата уже в счете - то ее величину добавлять к value (без влияние на дельту)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_acceptance where invcalc_id="'.$f['id'].'" and acceptance_id="'.$id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  
			  if((float)$f['real_debt']>(float)$g[0]){
				  
				  $delta_local=((float)$f['real_debt']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('invcalc_id'=>$f['id'],'acceptance_id'=>$id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('invcalc_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('invcalc_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  if(($_result['id']!==NULL)&&($_result['id']!=0)){
					    //$log->PutEntry($_result['id'],'распределение акта в роли входящей оплаты по реализациям',NULL,235,NULL,'акт № '.$f['code'].' распределен на реализацию '.$acc['id'].' на сумму '.($delta_local+$by_bill).' руб. ',$id);
				  
				  
				  		//$log->PutEntry($_result['id'],'распределение акта в роли входящей оплаты по реализациям',NULL,452,NULL,'акт № '.$f['code'].': распределен на реализацию '.$acc['id'].' на сумму '.($delta_local+$by_bill).' руб. ',$f['id']);
						
				  }
				  
				  if($delta==0) break;	
			  }
			  
		  }	
			
		}
		
		
		
		if($delta==0) return;
		
		//суммы по оплатам
		if(count($opl_ids)>0){
			
			//echo '<br>доступны оплаты '.implode(', ', $opl_ids);
			
			$period_flt=''; 
			
			
			$pdate_flt='';
			if($do_reverse_pdate) $pdate_flt=' and given_pdate>="'.$acc['given_pdate'].'" ';
			else $pdate_flt=' and given_pdate<"'.$acc['given_pdate'].'" ';
			
			$sql='select * from payment
		   where 
		   	is_confirmed=1 
			and is_incoming=1 
			/*and contract_id="'.$acc['contract_id'].'"*/
			and supplier_id="'.$bill['supplier_id'].'" 
			/*and given_pdate<"'.$acc['given_pdate'].'"*/
			'.$pdate_flt.'
			and org_id="'.$org_id.'" 
			and id in('.implode(', ',$opl_ids).')
			'.$period_flt.'
			';
		  
		//  echo $sql.'<br>'; die();	
			
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  //echo 'zz';
			  
			  $set1=new mysqlset('select sum(value) from payment_for_acceptance where payment_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			  
			  //если эта оплата уже в счете - то ее величину добавлять к value (без влияние на дельту)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_acceptance where payment_id="'.$f['id'].'" and acceptance_id="'.$id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  if((float)$f['value']>(float)$g[0]){
				  
				  $delta_local=((float)$f['value']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('payment_id'=>$f['id'],'acceptance_id'=>$id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('payment_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1,'is_shown'=>(int)!$do_reverse_pdate));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('payment_id'=>$f['id'],'acceptance_id'=>$id,'value'=>$delta_local+$by_bill, 'is_auto'=>1,'is_shown'=>(int)!$do_reverse_pdate));	
				  }
				  
				  //echo '<br>прикрепляю реализацию '.$id.' к оплате '.$f['id'];
				  if(($_result['id']!==NULL)&&($_result['id']!=0)){ 
					  //$log->PutEntry($_result['id'],'распределение входящей оплаты по реализациям',NULL,235,NULL,'входящая оплата № '.$f['code'].': распределена на реализацию '.$acc['id'].' на сумму '.($delta_local+$by_bill).' руб. ',$id);
					  
					  
					  //$log->PutEntry($_result['id'],'распределение входящей оплаты по реализациям',NULL,683,NULL,'входящая оплата № '.$f['code'].': распределена на реализацию '.$acc['id'].' на сумму '.($delta_local+$by_bill).' руб. ',$f['id']);
				   }
				  
				  if($delta==0) break;	
			  }
			  
		  }
		}
		
	}
	
	//массовая функция прикрепления оплат (при снятии утверждения реализации, при утверждении реализации задним числом)
	public function AutoBind($supplier_id, $org_id, $given_pdate,  $_result=NULL, $except_ids=NULL, $period_begin=NULL, $period_end=NULL){
	//($id,$org_id, $item=NULL, $_result=NULL){
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		$acc_ids=$this->GetLatestAccs($supplier_id, $org_id, $given_pdate, $except_ids,  $period_begin, $period_end);
		foreach($acc_ids as $k=>$acc_id){
			//echo '<br>прикрепляю к поступлению '.$acc_id;
			
			//в 2 прохода
			$this->BindPayments($acc_id,$org_id,  NULL, $_result,$period_begin, $period_end);
			
			//$this->BindPayments($acc_id,$org_id,  NULL, $_result,$period_begin, $period_end);
		}
		
	}
	
	//получение списка более поздних реализаций по данному контрагенту
	public function GetLatestAccs($supplier_id, $org_id, $given_pdate, $except_ids=NULL, $period_begin=NULL, $period_end=NULL){
		$acc_ids=array();
		
		$period_flt='';
		if(($period_begin!==NULL)&&($period_end!==NULL)) $period_flt=' and  (a.given_pdate between "'.$period_begin.'" and   "'.$period_end.'")';
		
		$except_flt='';
		if($except_ids!==NULL) $except_flt=' and a.id not in('.implode(', ',$except_ids).')';
		
		$sql='select a.id from acceptance as a
		inner join bill as b on a.bill_id=b.id
		where 
			a.org_id="'.$org_id.'" 
			and a.given_pdate>="'.$given_pdate.'"
			and a.is_incoming=0
			and b.supplier_id="'.$supplier_id.'"
			and a.is_confirmed=1
			'.$period_flt.'
			'.$except_flt.'
		order by
			a.given_pdate asc, a.id asc';
		
		/*if($supplier_id==36) {
			echo  $sql."<br>";
			echo date('d.m.Y H:i:s ', $period_begin);
			echo date('d.m.Y H:i:s ', $period_end);
		}*/
			
		$set2=new mysqlset($sql);
		$rs2=$set2->GetResult();
		$rc2=$set2->GetResultNumRows();
		for($i=0; $i<$rc2; $i++){
			$f=mysqli_fetch_array($rs2);
			$acc_ids[]=$f['id'];	
		}
		
		return $acc_ids;
	}
	
	
	//удалить из реализации(ий) связанные входящие оплаты
	public function FreeBindedPayments($acc_id=NULL, $acc_ids=NULL, $is_auto=0, $_result=NULL){
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_pi=new PayInItem;
		
		$_pb=new PayForAccItem;
		 
		if(($acc_id!==NULL)||($acc_ids!==NULL)){
			
			$acc_flt='';
			if($acc_id!==NULL) $acc_flt=' pb.acceptance_id="'.$acc_id.'" ';
			elseif($acc_ids!==NULL) $acc_flt=' pb.acceptance_id in('.implode(', ',$acc_ids).') ';
			
			$auto_flt='';
			//if($is_auto==1) $auto_flt=' and pb.is_auto=1 ';
			
			$sql='select distinct pb.payment_id, pb.id, pb.acceptance_id,  p.code, pb.value
						 from payment_for_acceptance as pb 
							inner join payment as p on p.id=pb.payment_id
							inner join acceptance as b on pb.acceptance_id=b.id
			where  '.$acc_flt.'  /*and p.is_confirmed=1*/ '.$auto_flt;
			
			
		//	echo $sql.'<br>';
			
			$set=new mysqlSet($sql);
			
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				//echo 'удаляю оплату '.$f['payment_id'].' '.$f['code'].'<br>';
				
				$_pb->Del($f['id']);
				
				 if(($_result['id']!==NULL)&&($_result['id']!=0)){ 
					//$log->PutEntry($_result['id'],'удаление распределения входящей оплаты по реализациям',NULL,235,NULL,'входящая оплата № '.$f['code'].': удалена из реализации '.$f['acceptance_id'].' на сумму '.$f['value'].' руб. ',$f['acceptance_id']);
					
					//$log->PutEntry($_result['id'],'удаление распределения входящей оплаты по реализациям',NULL,683,NULL,'входящая оплата № '.$f['code'].': удалена из реализации '.$f['acceptance_id'].' на сумму '.$f['value'].' руб. ',$f['payment_id']);
				 }
				
				
			}
			
			
			//удалим платежи из инв. актов
			$sql='select distinct pb.invcalc_id, pb.id, pb.acceptance_id, p.code, pb.value
						 from payment_for_acceptance as pb 
							inner join invcalc as p on p.id=pb.invcalc_id
							inner join acceptance as b on pb.acceptance_id=b.id
			where '.$acc_flt.'  /*and p.is_confirmed_inv=1 */'.$auto_flt;
			$set=new mysqlSet($sql);
			
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$_pb->Del($f['id']);
				
				 if(($_result['id']!==NULL)&&($_result['id']!=0)){ 
				
					//$log->PutEntry($_result['id'],'удаление распределения инвентаризационного акта по реализациям',NULL,235,NULL,'акт № '.$f['code'].': удален из реализации '.$f['acceptance_id'].' на сумму '.$f['value'].' руб. ',$f['acceptance_id']);
					
					//$log->PutEntry($_result['id'],'удаление распределения инвентаризационного акта по реализациям',NULL,452,NULL,'акт № '.$f['code'].': удален из реализации '.$f['acceptance_id'].' на сумму '.$f['value'].' руб. ',$f['invcalc_id']);
				 }
				 
			}	
		}
		
		
	}
	
	
		//список связанных утв. реализаций для снятия утв.
	public function GetBindedDocumentsToUnconfirm($id, $item=NULL){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		
		  
		
		 
		
		  //если это ВЕДУЩЕЕ поступление - проверить ведомых (поступления, реализации)
		  if($item===NULL) $item=$this->getitembyid($id);
		  if($item['is_leading']==1){
			  
			 $sql1='select  p.*, s.name from acceptance as p
						   left join document_status as s on p.status_id=s.id
				 where 
					   
					  p.is_incoming=1
					  and p.is_confirmed=1 
					  and is_leading=0
					  and leading_acceptance_id="'.$id.'"
				  order by id';
				
				
				// echo $sql1.'<br>';
				  $set1=new mysqlset($sql1);
				  $rs1=$set1->getResult();
				  $rc1=$set1->getResultNumRows();
				  
				  for($i=0; $i<$rc1; $i++){
					 $f=mysqli_fetch_array($rs1);  
					 $can=$can&&false;
					 
						  $reasons[]=' поступление № '.$f['id'].' в базе связанной организации, статус документа: '.$f['name'];	
						  
						  //найти все реализации поступления	
						   
						  $sql2='select p.*, s.name from acceptance as p
						   left join document_status as s on p.status_id=s.id
						   where 
						   p.is_incoming=0 
						   and p.is_confirmed=1 
						   and p.bill_id in(select out_bill_id from acceptance where id="'.$f['id'].'")
						   ';
						   
						 //  echo $sql2;
						  $set2=new mysqlset($sql2);
						  $rs2=$set2->GetResult();
						  $rc2=$set2->GetResultNumRows();
						  for($i1=0; $i1<$rc2; $i1++){
							 $f1=mysqli_fetch_array($rs2);  
							 $can=$can&&false;
							 
								  $reasons[]=' реализация № '.$f1['id'].' в базе связанной организации, статус документа: '.$f1['name'];	
							  
						  }
					  
				  }
				  if(count($reasons)>0) $reason.=" ";
				  $reason.=implode(', ',$reasons);
				  
				  
				  
				  
				 
				  
		  }
		  
		  
		
	
		return $reason;
	}
	
	public function UnconfirmBindedDocuments($id, $item=NULL, $_result=NULL){
		
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		if($item===NULL) $item=$this->getitembyid($id);
	    
		$_ani=new AccNotesItem;
		
		 
		 
		if($item['is_leading']==1){
			$_opf=new OpfItem;
		
			$_org=new SupplierItem; 
			$org=$_org->GetItemById($item['org_id']); $opf=$_opf->GetItemById($org['opf_id']);
		
		//
			  
			 $_stat=new DocStatusItem;
			$stat=$_stat->GetItemById(4); 
			$_acc_in=new AccInItem;
			 $sql1='select * from acceptance where 
					   
					   is_incoming=1
					  and is_confirmed=1 
					  and is_leading=0
					  and leading_acceptance_id="'.$id.'"
				  order by id';
				  
				//echo $sql1;
				  
				  //echo $sql1;
				  $set1=new mysqlset($sql1);
				  $rs1=$set1->getResult();
				  $rc1=$set1->getResultNumRows();
				  
				  for($i=0; $i<$rc1; $i++){
					
						$f=mysqli_fetch_array($rs1);  
		  				
						$new_org=$_org->GetItemById($f['org_id']); $new_opf=$_opf->GetItemById($new_org['opf_id']);
						
		 				$_acc_in->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>4));
					
			
						$log->PutEntry($_result['id'],'снятие утверждения поступления в связанной организации в связи со снятием утверждения реализации',NULL,672,NULL,'автоматически снято утверждение поступления № '.$f['id'].' в связанной организации '.$new_opf['name'].' '.$new_org['full_name'].' в связи со снятием утверждения реализации № '.$id.' в организации '.$opf['name'].' '.$org['full_name'].', поступление № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
						
						$notes_params=array();
						$notes_params['pdate']=time();
						$notes_params['user_id']=$f['id'];
						$notes_params['note']=SecStr('автоматически снято утверждение поступления № '.$f['id'].' в связанной организации '.$new_opf['name'].' '.$new_org['full_name'].' в связи со снятием утверждения реализации № '.$id.' в организации '.$opf['name'].' '.$org['full_name']);
						$notes_params['posted_user_id']=$_result['id'];
						$notes_params['is_auto']=1;
						$_ani->Add($notes_params);
					
					
					//найти все реализации поступления	
					 //найти все реализации поступления	
						   
						  $sql2='select p.*, s.name from acceptance as p
						   left join document_status as s on p.status_id=s.id
						   where p.is_incoming=0 and p.is_confirmed=1 and p.bill_id in(select out_bill_id from acceptance where id="'.$f['id'].'")
						   ';
						  $set2=new mysqlset($sql2);
						  $rs2=$set2->GetResult();
						  $rc2=$set2->GetResultNumRows();
						  for($i2=0; $i2<$rc2; $i2++){
							 $f1=mysqli_fetch_array($rs2);  
							 
							 $this->Edit($f1['id'], array('is_confirmed'=>0, 'status_id'=>4));
							 
							 $log->PutEntry($_result['id'],'снятие утверждения реализации в связанной организации в связи со снятием утверждения реализации',NULL,241,NULL,'автоматически снято утверждение реализации № '.$f1['id'].' в связанной организации '.$new_opf['name'].' '.$new_org['full_name'].' в связи со снятием утверждения реализации № '.$id.' в организации '.$opf['name'].' '.$org['full_name'].', реализация № '.$f1['id'].': установлен статус '.$stat['name'],$f1['id']);
							 
							 $notes_params=array();
							$notes_params['pdate']=time();
							$notes_params['user_id']=$f1['id'];
							$notes_params['note']=SecStr('автоматически снято утверждение реализации № '.$f1['id'].' в связанной организации '.$new_opf['name'].' '.$new_org['full_name'].' в связи со снятием утверждения реализации № '.$id.' в организации '.$opf['name'].' '.$org['full_name']);
							$notes_params['posted_user_id']=$_result['id'];
							$notes_params['is_auto']=1;
							$_ani->Add($notes_params);
						
							  
						  }
					  
				  }
				 
		  }
		 
		
		 
	}
	
	
}
?>