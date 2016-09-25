<?
require_once('abstractitem.php');
require_once('billpositem.php');
require_once('billpospmformer.php');
require_once('billposgroup.php');
require_once('docstatusitem.php');

require_once('trust_group.php');
//require_once('sh_i_group.php');
require_once('acc_group.php');
require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('payforbillitem.php');
require_once('payitem.php');
require_once('pay_in_item.php');
require_once('invcalcitem.php');
require_once('payforbillgroup.php');
require_once('payforbill_in_group.php');

//require_once('komplitem.php');
require_once('period_checker.php');
require_once('billnotesitem.php');

require_once('komplitem.php');
require_once('supplieritem.php');
require_once('opfitem.php');

require_once('invcalcitem.php');

require_once('invcalcnotesitem.php');
require_once('paynotesitem.php');
require_once('bill_in_item.php');

require_once('cashitem.php');
require_once('posgroupgroup.php');

//исходящий счет
class BillItem extends AbstractItem{
	protected static $uslugi;
	protected static $position_uslugi;
	
	protected static $semi_uslugi;

	protected static $position_semi_uslugi;

	
	//установка всех имен
	protected function init(){
		$this->tablename='bill';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
		
		//массив групп услуг
		if(self::$uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		  self::$uslugi/*$this->uslugi*/=array();
		  self::$uslugi/*$this->uslugi*/[]=SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$uslugi/*$this->uslugi*/)) self::$uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$uslugi/*$this->uslugi*/))  self::$uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
		//массив самих услуг
		if(self::$position_uslugi===NULL){
			self::$position_uslugi=array();
			$sql='select id from catalog_position where group_id in('.implode(', ',self::$uslugi).')';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				self::$position_uslugi[]=$f['id'];	
			}
		}	
		
		
		if(self::$semi_uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SEMI_SERVICE_CODE); // услуги
		  self::$semi_uslugi/*$this->uslugi*/=array();
		  self::$semi_uslugi/*$this->uslugi*/[]=SEMI_SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$semi_uslugi/*$this->uslugi*/)) self::$semi_uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$semi_uslugi/*$this->uslugi*/))  self::$semi_uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}



		//массив самих услуг
		if(self::$position_semi_uslugi===NULL){
			self::$position_semi_uslugi=array();
			$sql='select id from catalog_position where group_id in('.implode(', ',self::$semi_uslugi).')';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				self::$position_semi_uslugi[]=$f['id'];	
			}
		}
		
	}
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from bill_position_pm where bill_position_id in(select id from bill_position where bill_id='.$id.');';
		$it=new nonSet($query);
		
		
		$query = 'delete from bill_position where bill_id='.$id.';';
		$it=new nonSet($query);
		
		
		
		parent::Del($id);
	}	
	
	
	
	public function Edit($id,$params,$scan_status=false,$_result=NULL){
		$item=$this->GetItemById($id);
		
		
		//мы устанавливаем утверждение 1 гал.
		if(isset($params['is_confirmed_price'])&&($params['is_confirmed_price']==1)&&($item['is_confirmed_price']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		
		AbstractItem::Edit($id, $params);
		
		//$this->billpaysync->CatchStatus($
		
		//снятие утв. вход счетов:
		if(isset($params['is_confirmed_shipping'])&&($params['is_confirmed_shipping']==0)&&($item['is_confirmed_shipping']==1)){
			$this->DocUnconfirmBindedBills($id, $reason,$item, $_result);
			//$this->DocUnconfirmBindedCash($id, $reason,$item, $_result);
		}
		
		//утв. вход счетов:
		if(isset($params['is_confirmed_shipping'])&&($params['is_confirmed_shipping']==1)&&($item['is_confirmed_shipping']==0)){
			$this->DocConfirmBindedBills($id,  $reason,$item, $_result);
		}
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,NULL,$_result);
	}
	
	
	
	//добавим позиции
	public function AddPositions($current_id, array $positions,$can_change_cascade=false, $check_delta_summ=false, $result=NULL){
		$_kpi=new BillPosItem;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetPositionsArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('bill_id'=>$v['bill_id'],'position_id'=>$v['position_id'],'storage_id'=>$v['storage_id'],'sector_id'=>$v['sector_id'],'komplekt_ved_id'=>$v['komplekt_ved_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['bill_id']=$v['bill_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				$add_array['storage_id']=$v['storage_id'];
				
				$add_array['sector_id']=$v['sector_id'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				
				
				$add_pms=$v['pms'];
				$_kpi->Add($add_array, $add_pms);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
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
				$add_array['bill_id']=$v['bill_id'];
				$add_array['komplekt_ved_pos_id']=$v['komplekt_ved_pos_id'];
				$add_array['position_id']=$v['position_id'];
				$add_array['name']=$v['name'];
				$add_array['dimension']=$v['dimension'];
				$add_array['quantity']=$v['quantity'];
				$add_array['price']=$v['price'];
				$add_array['storage_id']=$v['storage_id'];
				
				$add_array['price_pm']=$v['price_pm'];
				$add_array['total']=$v['total'];
				
				$add_array['sector_id']=$v['sector_id'];
				$add_array['komplekt_ved_id']=$v['komplekt_ved_id'];
				
				$add_pms=$v['pms'];
				$_kpi->Edit($kpi['id'],$add_array, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились кол-ва, цены, +/-, 
				
				$to_log=false;
				if($kpi['quantity']!=$add_array['quantity']) $to_log=$to_log||true;
				if($kpi['storage_id']!=$add_array['storage_id']) $to_log=$to_log||true;
				if($kpi['sector_id']!=$add_array['sector_id']) $to_log=$to_log||true;
				if($kpi['price']!=$add_array['price']) $to_log=$to_log||true;
				if($kpi['price_pm']!=$add_array['price_pm']) $to_log=$to_log||true;
				if($kpi['total']!=$add_array['total']) $to_log=$to_log||true;
				
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
				if(($vv['position_id']==$v['id'])&&($vv['storage_id']==$v['storage_id'])
				&&($vv['sector_id']==$v['sector_id'])
				&&($vv['komplekt_ved_id']==$v['komplekt_ved_id'])
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
	public function GetPositionsArr($id,$show_statistics=true, $bill=NULL){
		$kpg=new BillPosGroup;
		$arr=$kpg->GetItemsByIdArr($id,0,$show_statistics,$bill);
		
		return $arr;		
		
	}
	
	
	
	//найдем стоииость по заказу
	public function CalcCost($id, $positions=NULL){
		if($positions===NULL) $positions=$this->GetPositionsArr($id, false);	
		$_bpm=new BillPosPMFormer;
		$total_cost=$_bpm->CalcCost($positions);
		return round($total_cost,2);
	}
	
	//найдем стоимость по подвозу
	public function CalcAcc($id, $item=NULL, $positions=NULL, $before_pdate=NULL){
		//  $sql3='select * from acceptance where bill_id="'.$id.'" and is_confirmed=1 order by given_pdate asc';
		  
		  $before_flt='';	
		  if($before_pdate!==NULL)	$before_flt=' and given_pdate<="'.$before_pdate.'" ';
		  	
		  $sql3='select sum(total) from acceptance_position where acceptance_id in(select id from acceptance where bill_id="'.$id.'" and is_confirmed=1 '.$before_flt.')';
			
			  
		  $set3=new mysqlSet($sql3);//,$to_page, $from,$sql_count);
		  $rs3=$set3->GetResult();
		  $rc3=$set3->GetResultNumRows();	
		  
		  
		  $g=mysqli_fetch_array($rs3);
		  
		  return round((float)$g[0],2);
	}
	
	//контроль возможности удаления
	public function CanDelete($id, &$reason){
		$can_delete=true;
		
		$reason='';
		
		$itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='счет утвержден';
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		$set=new mysqlSet('select * from payment where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются оплаты: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='№'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		
		
		return $can_delete;
	}
	
	
	public function HasR($id){
		$coun=0;
		
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		//$rc=$set->GetResultNumRows();
		$f=mysqli_fetch_array($rs);
		$coun+=(int)$f[0];
		
		return $coun;
	}
	
	public function HasRList($id){
		$txt='';
		
		$nums=array();
		
		$set=new mysqlSet('select * from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='реализация №'.$f['id'];
				
			}
			
		$txt=implode(', ',$nums);
		
		return $txt;
	}
	
	
	
	public function CalcPayed($id, $except_id=NULL, $except_inv=NULL){
		
		$res=0;
		
		$sql='select sum(bp.value) from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.bill_id="'.$id.'" ';
		
		if($except_id!==NULL) $sql.=' and p.id<>"'.$except_id.'"';
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		//$rc=$set->GetResultNumRows();
		//echo $rc;
		
		$res+=(float)$f[0];
		
		
		$sql='select sum(bp.value) from payment_for_bill as bp inner join invcalc as p on bp.invcalc_id=p.id where p.is_confirmed_inv=1 and bp.bill_id="'.$id.'" ';
		
		if($except_inv!==NULL) $sql.=' and p.id<>"'.$except_inv.'"';
		
		$set=new mysqlset($sql);
		
		
		
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		$res+=(float)$f[0];
		
		return round($res,2);
		
	}
	
	
	
	
	
	
	
	//проверка и автосмена статуса 
	public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$setted_status_id=$item['status_id'];
		if(isset($new_params['is_confirmed_price'])&&isset($old_params['is_confirmed_price'])){
			
			
			
			if(($new_params['is_confirmed_price']==1)&&($old_params['is_confirmed_price']==0)){
				//смена статуса на 2
				$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_price']==0)&&($old_params['is_confirmed_price']==1)){
				$setted_status_id=1;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$item['id']);
			}
			
			//проверять оплаченность счета!!!
			
			
		}elseif(isset($new_params['is_confirmed_shipping'])&&isset($old_params['is_confirmed_shipping'])){
			
			if(($new_params['is_confirmed_shipping']==1)&&($old_params['is_confirmed_shipping']==0)){
				//смена статуса на 9
				$setted_status_id=9;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_shipping']==0)&&($old_params['is_confirmed_shipping']==1)){
				$setted_status_id=2;
				$this->Edit($id,array('status_id'=>$setted_status_id));
				
				$stat=$_stat->GetItemById($setted_status_id);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$item['id']);
			}
			
			//проверять завоз позиций!!!
			
			
		}
		
		//echo $setted_status_id.'<br>';
		
		//если выставлен либо имеется без выставления статус 2, 20, 21 - проверять оплаченность
		if(($setted_status_id==2)||($setted_status_id==20)||($setted_status_id==21)){
			$summ_by_payed=$this->CalcPayed($id);
			$summ_by_cost=$this->CalcCost($id);
			
			//echo $summ_by_cost.' vs '.$summ_by_payed.'<br>';
			
			//оплачено ноль при ненулевой сумме счета
			if(($summ_by_cost>0)&&($summ_by_payed==0)){
			//ставим статус утвержден - нет оплат
				if($setted_status_id!=2){
					  
					  $setted_status_id=2;	
					  $this->Edit($id,array('status_id'=> $setted_status_id));
					  
					  $stat=$_stat->GetItemById( $setted_status_id);
					  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
				  
				}	
				
			}elseif(($summ_by_cost==0)&&($summ_by_payed==0)){
			//нулевая сумма счета - оплачено ноль - статус ставим оплачен (21)
				if($setted_status_id!=21){
					  
					  $setted_status_id=21;	
					  $this->Edit($id,array('status_id'=> $setted_status_id));
					  
					  $stat=$_stat->GetItemById( $setted_status_id);
					  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
				  
				}	
				
			}elseif(($summ_by_cost>0)&&($summ_by_cost>$summ_by_payed)){
			//частично оплачено	
				if($setted_status_id!=20){
					  
					  $setted_status_id=20;	
					  $this->Edit($id,array('status_id'=> $setted_status_id));
					  
					  $stat=$_stat->GetItemById( $setted_status_id);
					  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
				  
				}	
				
			}elseif(($summ_by_cost>0)&&($summ_by_cost<=$summ_by_payed)){
			//полностью оплачено	
				if($setted_status_id!=21){
					  
					  $setted_status_id=21;	
					  $this->Edit($id,array('status_id'=> $setted_status_id));
					  
					  $stat=$_stat->GetItemById( $setted_status_id);
					  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
				  
				}		
			}
				
		}
		
		//если выставлен либо имеется без выставления статус 9, 10 - проверять завоз позиций
		if(($setted_status_id==9)||($setted_status_id==10)){
			if($this->CheckDeltaPositions($id)){
				  if($setted_status_id!=10){
					  
					  $setted_status_id=10;	
					  $this->Edit($id,array('status_id'=> $setted_status_id));
					  
					  $stat=$_stat->GetItemById( $setted_status_id);
					  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
				  
				  }
			}else{
				 if($setted_status_id!=9){
					  $setted_status_id=9;	
					  $this->Edit($id,array('status_id'=> $setted_status_id));
					  
					  $stat=$_stat->GetItemById( $setted_status_id);
					  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);  
				 }	
			}
		}
		
		//добавить контроль смены статуса заявки
		//получить все заявки по счету
		//перебрать их и для каждой - вызвать контроль
		$_ki=new KomplItem;
		$sql='select distinct komplekt_ved_id from bill_position where bill_id="'.$id.'"';
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//print_r($f);
			$_ki->ScanDocStatus($f['komplekt_ved_id'],array(),array(), NULL,$_result);	
		}
		
		//die();
	}
	
	
	/*public function ScanDocStatus($id, $old_params, $new_params, $item=NULL, $_result=NULL){
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_stat=new DocStatusItem;
		if($item===NULL) $item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed_price'])&&isset($old_params['is_confirmed_price'])){
			
			
			
			if(($new_params['is_confirmed_price']==1)&&($old_params['is_confirmed_price']==0)&&($old_params['status_id']==1)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
				
			}elseif(($new_params['is_confirmed_price']==0)&&($old_params['is_confirmed_price']==1)&&(($old_params['status_id']==2)||($old_params['status_id']==9)||($old_params['status_id']==10))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$item['bill_id']);
			}
		}else{
			//отследить переходы из 2-9, 9-2, 9-10, 10-9
		  
		  //переход 2-9
		  if($item['status_id']==2){
			  //проверить количество п	
			  //также может произойти переход 2-10
			  if($this->CheckDeltaPositions($id)){
				  $this->Edit($id,array('status_id'=>10));
				  
				  $stat=$_stat->GetItemById(10);
				  $log->PutEntry($_result['id'],'смена статуса счета',NULL,93,NULL,'установлен статус '.$stat['name'],$id);
				  
				  
				  //выровнять стоимости в реализациях, если есть расхождения по суммам
				  
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
				  
				  //выровнять стоимости в поступлениях, если есть расхождения по суммам
				  
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
			
			
			
			
		}
		
		//добавить контроль смены статуса заявки
		//получить все заявки по счету
		//перебрать их и для каждой - вызвать контроль
		$_ki=new KomplItem;
		$sql='select distinct komplekt_ved_id from bill_position where bill_id="'.$id.'"';
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->getresultnumrows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//print_r($f);
			$_ki->ScanDocStatus($f['komplekt_ved_id'],array(),array(), NULL,$_result);	
		}
		//die();
	}
	*/
	
	
	
	
	//запрос о совпадении позиций по подчиненным поступлениям
	public function CheckDeltaPositions($id){
		$res=false;
		
		
		$positions=$this->GetPositionsArr($id,false);
		
		$delta=0;
		foreach($positions as $k=>$v){
			
			
			 
			
			
			$sql='select sum(quantity) as s_q from acceptance_position 
			where 
			acceptance_id in(
				select id from acceptance where is_confirmed=1  and bill_id="'.$v['bill_id'].'"  /*and storage_id="'.$v['storage_id'].'" and sector_id="'.$v['sector_id'].'"*/ ) and position_id="'.$v['id'].'" and komplekt_ved_id="'.$v['komplekt_ved_id'].'"';
			
			
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			
			$f=mysqli_fetch_array($rs);
			//$delta+=($v['quantity']-$f['s_q']);
			$zc=($v['quantity']-$f['s_q']);
			if($zc>=0) $delta+=$zc; 
			
			/*echo $sql;
			echo '<pre>';
			print_r($v);
			print_r($f);
			echo '</pre>';*/
		}
		
		//print_r($delta);
		//die();
		
		$res=($delta==0);
		
		return $res;
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
		
		  //проверить связанные реализации
		 
		  $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where is_confirmed=1 and bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  	  
			  //if($v['is_confirmed']==1) {
				  $can=$can&&false;
				  $reasons[]=' реализация <a target=_blank href=ed_acc.php?id='.$v['id'].'&action=1&from_begin=1>№ '.$v['id'].'</a> статус документа: '.$v['name'];	
			  //}
			  
		  }
		  if(count($reasons)>0) $reason.="<br />По счету имеются утвержденные реализации: ";
		  $reason.=implode('<br /> ',$reasons);
		  
		  
		  
		  
		  //проверить связанные вход. счета
		  $reasons=array();
		  $sql='select p.*, s.name from bill as p
		  left join document_status as s on p.status_id=s.id
		   where is_incoming=1 and is_confirmed_price=1 and p.id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
			 $can=$can&&false;
			 //$reasons[]=' счет № '.$f['code'].'';	
			 $reasons[]=' входящий счет <a target=_blank href=ed_bill_in.php?id='.$f['id'].'&action=1&from_begin=1>№ '.$f['code'].'</a> статус документа: '.$f['name'];	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.="<br />По счету имеются утвержденные входящие счета: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		  
		  //проверить связанные поступления
		  $reasons=array();
		  $sql='select p.*, s.name from acceptance as p
		   left join document_status as s on p.status_id=s.id
		   where is_incoming=1 and is_confirmed=1 and p.id in(select distinct acceptance_id from acceptance_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
			 $can=$can&&false;
			 
				  $reasons[]=' поступление <a target=_blank href=ed_acc_in.php?id='.$f['id'].'&action=1&from_begin=1>№ '.$f['id'].'</a> статус документа: '.$f['name'];	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.="<br />По счету имеются утвержденные поступления: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
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
		
		
		 //проверить связанные поступления
		  $set=new mysqlSet('select p.*, s.name from acceptance as p inner join document_status as s on p.status_id=s.id where  p.status_id=4  and bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  
			  //if($v['status_id']==4) {
				  $can=$can&&false;
				  $reasons[]=' реализация № '.$v['id'].' статус документа: '.$v['name'];	
			 // }
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные реализации: ";
		  $reason.=implode(', ',$reasons);
		  
		  
		  //проверить связанные неутвержденные вход. счета
		   $reasons=array();
		  $sql='select p.*, s.name from bill as p
		  left join document_status as s on p.status_id=s.id
		   where is_incoming=1 and is_confirmed_price=0 and p.id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  //echo $sql; 
		   
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
			 $can=$can&&false;
			 //$reasons[]=' счет № '.$f['code'].'';	
			 $reasons[]=' входящий счет № '.$f['code'].' статус документа: '.$f['name'];	
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются утвержденные входящие счета: ";
		  $reason.=implode(', ',$reasons);
		 
		  
		  
		  //проверить связанные неутвержденные поступления
		  $reasons=array();
		  $sql='select p.*, s.name from acceptance as p
		   left join document_status as s on p.status_id=s.id
		   where is_incoming=1 and is_confirmed=0 and p.id in(select distinct acceptance_id from acceptance_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
			 $can=$can&&false;
			 
				  $reasons[]=' поступление № '.$f['id'].' статус документа: '.$f['name'];	
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные поступления: ";
		  $reason.=implode(', ',$reasons);
		
		
		  
		  
		  
		  
		  //проверить связанные распоряжения на отгрузку
		/*
 		$set=new mysqlSet('select p.*, s.name from sh_i as p inner join document_status as s on p.status_id=s.id where bill_id="'.$id.'"');
		  $rs=$set->getResult();
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);	  			  
			  if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' распоряжение на отгрузку № '.$v['id'].' статус документа: '.$v['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные распоряжения на отгрузку: ";
		  $reason.=implode(', ',$reasons);*/
		  
		  //проверить связанные доверенности
		  /*$_accg=new TrustGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  if($v['status_id']==1) {
				  $can=$can&&false;
				  $reasons[]=' доверенность № '.$v['id'].' статус документа: '.$dsi['name'];	
			  }
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные доверенности: ";
		  $reason.=implode(', ',$reasons);
		  
		  //проверить связ оплаты
		  $set=new mysqlSet('select * from payment where status_id=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		  $rs=$set->getResult();
		  
		  $rc=$set->GetResultNumRows();
		  $reasons=array();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
			  $dsi=$_dsi->GetItemById($v['status_id']);
			
				  $can=$can&&false;
				  $reasons[]=' оплата № '.$v['code'].' статус документа: '.$dsi['name'];	
			 
			  
		  }
		  if(count($reasons)>0) $reason.=" по счету имеются неутвержденные оплаты: ";
		  $reason.=implode(', ',$reasons);*/
		
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		$set=new MysqlSet('select * from acceptance where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование реализации в связи с аннулированием счета',NULL,94,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'аннулирование реализации в связи с аннулированием счета',NULL,242,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update acceptance set status_id=6 where bill_id="'.$id.'"');
		
		
		
		//проверить связанные неутвержденные вход. счета
		 $reasons=array();
		$sql='select p.*, s.name from bill as p
		left join document_status as s on p.status_id=s.id
		 where is_incoming=1 and is_confirmed_price=0 and p.id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		//echo $sql; 
		 
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(3);
		for($i=0; $i<$rc; $i++){
		   $f=mysqli_fetch_array($rs);  
		    
		   //$reasons[]=' входящий счет № '.$f['code'].' статус документа: '.$f['name'];	
		   $log->PutEntry($_result['id'],'аннулирование входящего счета в связи с аннулированием исходящего счета',NULL,626,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
			
		}
		 
	    $ns=new NonSet('update bill set status_id=3 where is_incoming=1 and is_confirmed_price=0 and id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")');
		
		
		//проверить связанные неутвержденные поступления
		$reasons=array();
		$sql='select p.*, s.name from acceptance as p
		 left join document_status as s on p.status_id=s.id
		 where is_incoming=1 and is_confirmed=0 and p.id in(select distinct acceptance_id from acceptance_position where out_bill_id="'.$id.'")';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(6);
		
		for($i=0; $i<$rc; $i++){
		   $f=mysqli_fetch_array($rs);  
		  
			//	$reasons[]=' поступление № '.$f['id'].' статус документа: '.$f['name'];
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием исходящего счета',NULL,626,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'аннулирование поступления в связи с аннулированием исходящего счета',NULL,674,NULL,'реализация № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);	
			
		}
		
		
		$ns=new NonSet('update acceptance set status_id=6 where  is_incoming=1 and is_confirmed=0 and id in(select distinct acceptance_id from acceptance_position where out_bill_id="'.$id.'")');
		
		
		
		
		/*$set=new MysqlSet('select * from trust where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование доверенности в связи с аннулированием счета',NULL,94,NULL,'доверенность № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
		}	
		
		$ns=new NonSet('update trust set status_id=3 where bill_id="'.$id.'"');*/
		
		
		
		/*
		$set=new MysqlSet('select * from sh_i where bill_id="'.$id.'"');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование распоряжения на отгрузку в связи с аннулированием счета',NULL,94,NULL,'распоряжение № '.$f['id'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			
			$log->PutEntry($_result['id'],'аннулирование распоряжения на отгрузку в связи с аннулированием счета',NULL,226,NULL,'распоряжение № '.$f['id'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update sh_i set status_id=3 where bill_id="'.$id.'"');
		
		*/
		/*$set=new MysqlSet('select * from payment where id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$log->PutEntry($_result['id'],'аннулирование оплаты в связи с аннулированием счета',NULL,93,NULL,'оплата № '.$f['code'].': установлен статус '.$stat['name'],$f['bill_id']);
			
			$log->PutEntry($_result['id'],'аннулирование оплаты в связи с аннулированием счета',NULL,279,NULL,'оплата № '.$f['code'].': установлен статус '.$stat['name'],$f['id']);
		}	
		
		$ns=new NonSet('update payment set status_id=3 where id in(select distinct payment_id from payment_for_bill where bill_id="'.$id.'")');	*/
	}
	
	
	
	//получить список связанных оплат
	public function GetBindedPayments($bill_id,&$summ){
		$summ=0;
		$names=array();	
		$_inv=new InvCalcItem;
		
		 
		$set=new mysqlSet('select distinct pb.payment_id, pb.id, b.code as bill_code, p.code, pb.value, p.given_no, p.given_pdate
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1  order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$names[]=$f['code'];
			$summ+=(float)$f['value'];
		}
		
		//добавим оплаты по инв. актам
		
		$set=new mysqlSet('select distinct pb.invcalc_id, pb.id, b.code as bill_code, p.code, pb.value, p.given_no, p.invcalc_pdate
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 order by p.invcalc_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$names[]=$f['code'];
			$summ+=(float)$f['value'];
		}
		
		
		
		return implode(', ',$names);
	}
	
	public function GetBindedPaymentsFull($bill_id){
		$summ=0;
		$alls=array();	
		
		$_inv=new InvCalcItem;
		
	 
		$set=new mysqlSet('select distinct pb.payment_id, "0" as kind, pb.id, b.code as bill_code, p.code, p.value, p.given_no, p.given_pdate as given_pdate,  p.given_pdate as given_payment_pdate, p.given_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$alls[]=$f;
		}
		
		//добавим инв. акты
		$sql='select distinct pb.invcalc_id, "1" as kind, pb.id, b.code as bill_code, p.code, pb.value, p.given_no,  p.invcalc_pdate as invcalc_pdate, p.invcalc_pdate as given_payment_pdate, p.invcalc_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 order by p.invcalc_pdate desc';
		//echo $sql;
		$set=new mysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$real_debt_stru=$_inv->FindRealDebt($f['invcalc_id']);
			//$f['real_debt']=$real_debt_stru['real_debt'];
			//$f['real_debt_id']=$real_debt_stru['real_debt_id'];
			$f['debt']=$real_debt_stru['real_debt'];
			
			
			//$f['value']=$f['real_debt'];
			
			$alls[]=$f;
		}
		
		
		
		return $alls;
	}
	
	//удалить платежи из связанных оплат
	public function FreeBindedPayments($bill_id, $is_auto=0, $_result=NULL){
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_pi=new PayInItem;
		
		$_pb=new PayForBillItem;
		$_inv=new InvCalcItem;
		
		$auto_flt='';
		if($is_auto==1) $auto_flt=' and pb.is_auto=1 ';
		
		$sql='select distinct pb.payment_id, pb.id, b.code as bill_code, p.code, pb.value
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed=1 '.$auto_flt;
		
		$set=new mysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_pb->Del($f['id']);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из исходящей оплаты',NULL,93,NULL,'исходящая оплата № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$bill_id);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из исходящей оплаты',NULL,272,NULL,'исходящая оплата № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$f['payment_id']);
			
			//привяжем оплату по счету к неоплаченным счетам данного контрагента...
			$_pi->BindPayments($f['payment_id'], $_result['org_id']);
		}	
		
		//удалим платежи из инв. актов
		$sql='select distinct pb.invcalc_id, pb.id, b.code as bill_code, p.code, pb.value
					 from payment_for_bill as pb 
						inner join invcalc as p on p.id=pb.invcalc_id
						inner join bill as b on pb.bill_id=b.id
		where pb.bill_id="'.$bill_id.'" and p.is_confirmed_inv=1 '.$auto_flt;
		$set=new mysqlSet($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$_pb->Del($f['id']);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,93,NULL,'акт № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$bill_id);
			
			$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,452,NULL,'акт № '.$f['code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. ',$f['invcalc_id']);
			
			//привяжем оплату по счету к неоплаченным счетам данного контрагента...
			//$_pi->BindPayments($f['payment_id'], $_result['org_id']);
		}
		
		
	}
	
	//привязать счет к оплатам с авансом
	public function BindPayments($bill_id,$org_id, $_result=NULL){
		$bill=$this->GetItemById($bill_id);
		if($bill===false) return;
		$_bpi=new PayForBillItem;
		$_pfg=new PayForBillInGroup;
		$_bpf=new BillPosPMFormer;
		
		
		$_inv=new InvCalcItem;
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		
		$opl_codes=$_pfg->GetAvans($bill['supplier_id'],$org_id, $bill_id, $avans,$opl_ids,$inv_ids, $bill['contract_id']);
		if((count($opl_ids)==0)&&(count($inv_ids)==0)) return;
		if($avans<=0) return;
		
		
		
		$total_cost=$_bpf->CalcCost($this->GetPositionsArr($bill_id));
		
		$sum_by_bill=$_pfg->SumByBill($bill_id);
		if($total_cost<=$sum_by_bill) return;
		
		$delta=$total_cost-$sum_by_bill;
		
		
		//суммы по инв актам...
		if(count($inv_ids)>0){
			$set=new mysqlset('select * from invcalc where is_confirmed_inv=1 and supplier_id="'.$bill['supplier_id'].'" and org_id="'.$org_id.'" and id in('.implode(', ',$inv_ids).') and invcalc_pdate<"'.$bill['supplier_bill_pdate'].'"');
			
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  $real_debt_stru=$_inv->FindRealDebt($f['id'],$f);
			  $f['real_debt']=$real_debt_stru['real_debt'];
			  $f['real_debt_id']=$real_debt_stru['real_debt_id'];
			  
			 // echo 'zz';
			  if(!(($real_debt_stru['real_debt_id']==3)&&($real_debt_stru['real_debt']!=0))) continue;
			  
			  $set1=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			   //если эта оплата уже в счете - то ее величину добавлять к value (без влияние на дельту)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_bill where invcalc_id="'.$f['id'].'" and bill_id="'.$bill_id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  
			  if((float)$f['real_debt']>(float)$g[0]){
				  
				  $delta_local=((float)$f['real_debt']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('invcalc_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету в инвентаризационный акт',NULL,93,NULL,'акт № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$bill_id);
				  
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету в инвентаризационный акт',NULL,452,NULL,'акт № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$f['id']);
				  
				  if($delta==0) break;	
			  }
			  
		  }
			//die();
		}
		
		
		if($delta==0) return;
		//суммы по оплатам
		if(count($opl_ids)>0){
			
		$sql='select * from payment
		   where 
		   	is_confirmed=1 
			and is_incoming=1 
			and contract_id="'.$bill['contract_id'].'"
			and supplier_id="'.$bill['supplier_id'].'" 
			and org_id="'.$org_id.'" 
			and id in('.implode(', ',$opl_ids).')';
		  
		//  echo $sql.'<br>'; die();	
			
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  //echo 'zz';
			  
			  $set1=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'"');
			  $rs1=$set1->GetResult();
			  $g=mysqli_fetch_array($rs1);
			  
			  
			  //если эта оплата уже в счете - то ее величину добавлять к value (без влияние на дельту)
			  $by_bill=0;
			  $set2=new mysqlset('select sum(value) from payment_for_bill where payment_id="'.$f['id'].'" and bill_id="'.$bill_id.'"');
			  $rs2=$set2->GetResult();
			  $g2=mysqli_fetch_array($rs2);
			  $by_bill+=(float)$g2[0];
			  
			  if((float)$f['value']>(float)$g[0]){
				  
				  $delta_local=((float)$f['value']-(float)$g[0]);
				  
				  $test=$_bpi->GetItemByFields(array('payment_id'=>$f['id'],'bill_id'=>$bill_id));
				  if($delta_local>$delta){
					  $delta_local=$delta;
					  $delta=0;	
				  }else{
					  $delta-=$delta_local;	
				  }
				  if($test===false){
					  $_bpi->Add(array('payment_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }else{
					  //$delta_local-=$test['value'];
					  $_bpi->Edit($test['id'],array('payment_id'=>$f['id'],'bill_id'=>$bill_id,'value'=>$delta_local+$by_bill, 'is_auto'=>1));	
				  }
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету во входящую оплату',NULL,93,NULL,'исходящая оплата № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$bill_id);
				  
				  
				  $log->PutEntry($_result['id'],'добавление платежа по счету во входящую оплату',NULL,272,NULL,'исходящая оплата № '.$f['code'].': добавлен платеж по счету '.$bill['code'].' на сумму '.($delta_local+$by_bill).' руб. ',$f['id']);
				  
				  if($delta==0) break;	
			  }
			  
		  }
		}
		
	}
	
	
	
	
	
	
	
	
	//проверим дату на попадание в закрытый период
	public function CheckClosePdate($id, &$rss, $item=NULL, $periods=NULL){
		$can=true;
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pch=new PeriodChecker;
		
		//var_dump($item);
		//echo $item['supplier_bill_pdate'];
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $rss=' дата счета контрагента '.$rss23;
			  //echo'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';	
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
		
		if($item['is_confirmed_price']!=1){
			
			$can=$can&&false;
			$reasons[]='у счета не утверждены цены';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_shipping']==1){
			
			$can=$can&&false;
			$reasons[]='у счета утверждена отгрузка';
			$reason.=implode(', ',$reasons);
		}
		
		else{
		
		  
		   //контроль закрытого периода 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' дата счета контрагента '.$rss23;	
		  }
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
		
		if($item['is_confirmed_price']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета утверждены цены';
			$reason.=implode(', ',$reasons);
		}else{
			//контроль закрытого периода 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='дата счета контрагента '.$rss23;	
			}
			
			
			
			//контроль совпадения контрагента с родительской заявкой
			$_ki=new KomplItem;
			$ki=$_ki->getitembyid($item['komplekt_ved_id']);
			if(($item['komplekt_ved_id']!=0)&&($ki['supplier_id']!=$item['supplier_id'])){
				$_si=new SupplierItem; $_opf=new OpfItem;
				$si1=$_si->GetItemById($ki['supplier_id']); $opf1=$_opf->GetItemById($si1['opf_id']);
				$si2=$_si->GetItemById($item['supplier_id']); $opf2=$_opf->GetItemById($si2['opf_id']);
				
				$can=$can&&false;
				$reasons[]='не совпадает контрагент заявки '.$si1['code'].' '.$opf1['name'].' '.$si1['full_name']. ' и контрагент счета '.$si2['code'].' '.$opf2['name'].' '.$si2['full_name'];
				
			}
			
			
			
			
			 $reason.=implode(', ',$reasons);
			 
			 
			 //проверка по числу позиций 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} 
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
		
		if($item['is_confirmed_shipping']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета утверждена отгрузка';
			$reason.=implode(', ',$reasons);
		}elseif($item['is_confirmed_price']==0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета не утверждена цена';
			$reason.=implode(', ',$reasons);
		}
		
		else{
			//контроль закрытого периода 
		    $reasons=array();
			if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
				$can=$can&&false;
				$reasons[]='дата счета контрагента '.$rss23;	
			}
			 $reason.=implode(', ',$reasons);
			 
			
			/*//проверка по числу позиций 
			$can=$can&&$this->CanConfirmByPositions($id,$rss,$item);
			if(strlen($rss)>0){
				if(strlen($reason)>0){
					$reason.=', ';
				}
				$reason.=$rss;
			} */
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
		
		if($item['is_confirmed_shipping']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у счета не утверждена отгрузка';
			$reason.=implode(', ',$reasons);
		}else{
		
		  //проверить связанные реализации
		  $_accg=new AccGroup;
		  $_accg->setidname('bill_id');
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
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.=" по счету имеются утвержденные реализации: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  //проверить связанные вход. счета
		  $reasons=array();
		  $sql='select * from bill where is_incoming=1 and is_confirmed_price=1 and id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
			// $can=$can&&false; - не является причиной без наличия пост., реал.
			 $reasons[]=' счет № '.$f['code'].'';	
			  
		  }
		 /* if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.=" по счету имеются утвержденные входящие счета: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);*/
		  
		  
		  
		  
		  //проверить связанные поступления
		  $reasons=array();
		  $sql='select * from acceptance where is_incoming=1 and is_confirmed=1 and id in(select distinct acceptance_id from acceptance_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
			 $can=$can&&false; 
			 $reasons[]=' поступление № '.$f['id'].'';	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.=" по счету имеются утвержденные поступления: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  //проверить связанные затраты...
		   //проверить связанные затраты
		  $reasons=array();
		  $sql='select c.*, ck.name as kind_name 
		  
		  from cash as c 
		  left join cash_kind as ck on c.kind_id=ck.id 
		  
		  where c.is_confirmed=1  and c.id in(select cash_id from cash_to_bill where bill_id ="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 	$can=$can&&false; 
			 $reasons[]=' '.$f['kind_name'].' № '.$f['code'].'';	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.="  по счету имеются связанные утвержденные затраты: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		   //контроль закрытого периода 
		    $reasons=array();
		  if(!$_pch->CheckDateByPeriod($item['supplier_bill_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]=' дата счета контрагента '.$rss23;	
		  }
		  if(count($reasons)>0) {
		   if(strlen($reason)!=0) $reason.='; ';
		   $reason.=implode(' ',$reasons);
		  }
		  
		  
		  
		 
		  
		}
		
		return $can;
	}
	
	
	//список утв. связ вход счетов для снятия утв.
	public function DocGetBindedBills($id,&$reason,$item=NULL, $periods=NULL){
		 
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		
		  
		  
		  //проверить связанные вход. счета
		  $reasons=array();
		  $sql='select * from bill where is_incoming=1 and is_confirmed_price=1 and is_confirmed_shipping=1 and id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 
			 $reasons[]=' счет № '.$f['code'].'';	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.="  ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		 
		  
		  
		 
		return $reason;
	}
	
	//снять утверждение связ вход счетов
	public function DocUnconfirmBindedBills($id,&$reason,$item=NULL, $_result=NULL){
		 
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		$_bill_in=new BillInItem;
		
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$log=new ActionLog;
		
		
		  
		 
		  $reasons=array();
		  $sql='select * from bill where is_incoming=1 and is_confirmed_price=1 and is_confirmed_shipping=1 and id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 
			// $reasons[]=' счет № '.$f['code'].'';	
			 $params=array();
			/* $params['is_confirmed_price']=0;
			  $params['confirm_price_pdate']=time();
				 $params['user_confirm_price_id']=$_result['id'];*/
			 
			 $params['is_confirmed_shipping']=0;
			  $params['confirm_shipping_pdate']=time();
				 $params['user_confirm_shipping_id']=$_result['id'];
				 
			 
			 $_bill_in->Edit($f['id'], $params, true, $_result);
			 if($f['is_confirmed_shipping']==1){
					$log->PutEntry($_result['id'], 'снял утверждение приемки входящего счета в связи со снятием утверждения исходящего счета', NULL, 623, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $f['id']);
					$log->PutEntry($_result['id'], 'снял утверждение приемки входящего счета в связи со снятием утверждения исходящего счета', NULL, 197, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $id);
			 }
			 
			 
			/* if($f['is_confirmed_price']==1){
					$log->PutEntry($_result['id'], 'снял утверждение цен входящего счета в связи со снятием утверждения исходящего счета', NULL, 622, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $f['id']);
					$log->PutEntry($_result['id'], 'снял утверждение цен входящего счета в связи со снятием утверждения исходящего счета', NULL, 196, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $id);
			 }*/
			 
			  
		  }
		 
		 
		  
		 
		  
		  
		 
		return $reason;
	}
	
	
	//список утв. связ затрат для снятия утв.
	public function DocGetBindedCash($id,&$reason,$item=NULL, $periods=NULL){
		 
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		
		  
		  
		  //проверить связанные затраты
		  $reasons=array();
		  $sql='select c.*, ck.name as kind_name 
		  
		  from cash as c 
		  left join cash_kind as ck on c.kind_id=ck.id 
		  
		  where c.is_confirmed=1  and c.bill_id ="'.$id.'"';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 
			 $reasons[]=' '.$f['kind_name'].' № '.$f['code'].'';	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.="  ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		
		 
		return $reason;
	}
	
	
	//снять утверждение связ затрат
	public function DocUnconfirmBindedCash($id,&$reason,$item=NULL, $_result=NULL){
		 
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		$_bill_in=new CashItem;
		
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$log=new ActionLog;
		
		
		  
		 
		  $reasons=array();
		   $sql='select c.*, ck.name as kind_name 
		  
		  from cash as c 
		  left join cash_kind as ck on c.kind_id=ck.id 
		  
		  where c.is_confirmed=1  and c.bill_id ="'.$id.'"';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 
			// $reasons[]=' счет № '.$f['code'].'';	
			 $params=array();
			 $params['is_confirmed']=0;
			  $params['confirm_pdate']=time();
				 $params['user_confirm_id']=$_result['id']; 
			 
			 $params['is_confirmed_given']=0;
			  $params['confirmed_given_pdate']=time();
				 $params['user_confirm_given_id']=$_result['id'];
				 
			 
			 $_bill_in->Edit($f['id'],  $params,  true, $_result);
			 if($f['is_confirmed_given']==1){
					$log->PutEntry($_result['id'], 'снял утверждение выдачи суммы по расходу в связи со снятием утверждения исходящего счета', NULL, 845, NULL, $f['kind_name'].' '.$f['code'].', исход. счет '.$item['code'], $f['id']);
					$log->PutEntry($_result['id'], 'снял утверждение выдачи суммы по расходу в связи со снятием утверждения исходящего счета', NULL, 197, NULL, $f['kind_name'].' '.$f['code'].', исход. счет '.$item['code'], $id);
			 }
			 
			 
			 if($f['is_confirmed']==1){
					$log->PutEntry($_result['id'], 'снял утверждение затрат расхода в связи со снятием утверждения исходящего счета', NULL, 843, NULL, $f['kind_name'].' '.$f['code'].', исход. счет '.$item['code'], $f['id']);
					$log->PutEntry($_result['id'], 'снял утверждение затрат расхода в связи со снятием утверждения исходящего счета', NULL, 196, NULL, $f['kind_name'].' '.$f['code'].', исход. счет '.$item['code'], $id);
			 }
			 
			  
		  }
		 
		 
		  
		 
		  
		  
		 
		return $reason;
	}
	
	
	
	
	//список не утв. связ вход счетов для установки утв.
	public function DocGetBindedUnBills($id,&$reason,$item=NULL, $periods=NULL){
		 
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		
		  
		  
		  //проверить связанные вход. счета
		  $reasons=array();
		  $sql='select * from bill where is_incoming=1 and is_confirmed_price=1 and  is_confirmed_shipping=0 and status_id<>3 and id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 
			 $reasons[]=' счет № '.$f['code'].'';	
			  
		  }
		  if(count($reasons)>0) {
			 if(strlen($reason)!=0) $reason.='; ';
			  $reason.="  ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  
		  
		  
		 
		  
		  
		 
		return $reason;
	}
	
	
	// утвердить связ вход счета
	public function DocConfirmBindedBills($id,&$reason,$item=NULL, $_result=NULL){
		 
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		$_bill_in=new BillInItem;
		
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$log=new ActionLog;
		
		
		  
		 
		  $reasons=array();
		  $sql='select * from bill where is_incoming=1 and is_confirmed_price=1 and  is_confirmed_shipping=0 and status_id<>3 and id in(select distinct bill_id from bill_position where out_bill_id="'.$id.'")';
		  
		  //echo $sql; die();
		  $set=new mysqlset($sql);
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  for($i=0; $i<$rc; $i++){
			 $f=mysqli_fetch_array($rs);  
		 
			// $reasons[]=' счет № '.$f['code'].'';	
			
			
			 if($_bill_in->CanConfirmByPositions($f['id'], $rss2, $f)){
				//echo 'doing';
				
				
				 $params=array();
				/* $params['is_confirmed_price']=1;
				 $params['confirm_price_pdate']=time();
				 $params['user_confirm_price_id']=$_result['id'];
				 */
				 
				 $params['is_confirmed_shipping']=1;
				 $params['confirm_shipping_pdate']=time();
				 $params['user_confirm_shipping_id']=$_result['id'];
				 
				 
				 $_bill_in->Edit($f['id'], $params, true, $_result);
				  
						$log->PutEntry($_result['id'], 'утвердил приемку входящего счета в с утверждением исходящего счета', NULL, 621, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $f['id']);
						$log->PutEntry($_result['id'], 'утвердил приемку входящего счета в с утверждением исходящего счета', NULL, 195, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $id);
				  
					/*	$log->PutEntry($_result['id'], 'утвердил цены входящего счета в связи с утверждением исходящего счета', NULL, 620, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $f['id']);
						$log->PutEntry($_result['id'], 'утвердил цены входящего счета в связи с утверждением исходящего счета', NULL, 95, NULL, 'вход. счет '.$f['code'].', исход. счет '.$item['code'], $id);*/
				 
			 
			 }
		  }
		 
		 
		  
		 
		  
		 
		return $reason;
	}
	
	
	
	//проверка, свободны ли позиции счета для восстановления/утверждения
	public function CanConfirmByPositions($id,&$reason,$item=NULL){
		$reason=''; $reasons=array();
		$can=true;	
		
		if($item===NULL) $item=$this->getitembyid($id);
		
		//if($item['komplekt_ved_id']==0) return true; //исключение для межсклада или инвост
		if(($item['interstore_id']!=0)||($item['inventory_id']!=0)) return true; //исключение для межсклада или инвост
		
		$mf=new MaxFormer;
		
		$_sh=new KomplItem;
		
		//в счете могут быть позиции разных заявок
		$positions=$this->GetPositionsArr($id);
		
		$komplekt_ved_ids=array();
		foreach($positions as $k=>$v){
			if(!in_array($v['komplekt_ved_id'], $komplekt_ved_ids)) $komplekt_ved_ids[]=$v['komplekt_ved_id'];	
		}
		
		//найти суммарные позиции заявок
		$ship_positions=array();
		foreach($komplekt_ved_ids as $cv){
			$_ship_positions=$_sh->GetPositionsArr($cv, false);	
			
			foreach($_ship_positions as $cck=>$ccv){
				$ship_positions[]=$ccv;
			}
		}
		
		
		
		
		//переберем позиции поступления
		//сравним со статистикой распоряжения на отгрузку
		//если превышение - то заносим в список причин
		
		foreach($positions as $k=>$v){
			if($v['komplekt_ved_id']==0) continue;
			
			if(!$this->PosInSh($v,$ship_positions,$find_pos)){
				$can=$can&&false;
				$reasons[]='в родительской заявке № '.$v['komplekt_ved_id'].' не найдена позиция '.SecStr($v['position_name'].', выберите другую позицию, нажав кнопку Редактировать позиции..., затем сохраните счет и повторите попытку');	
				continue;	
			}
			
			//найдена.. сравним количества
			$vsego=$find_pos['quantity'];
			
			//MaxForBill($komplekt_id, $position_id, $except_bill_id=NULL, $except_is_id=NULL){
			$free=$mf->MaxForBill($v['komplekt_ved_id'],$v['id']) ;
			
						
			if($v['quantity']>$free*PPUP){
				//превышение
				$can=$can&&false;
				$reasons[]='количество позиции '.SecStr($v['position_name']).' '.$v['quantity'].' '.SecStr($v['dim_name']).', заявка №'.$v['komplekt_ved_id'].', превышает доступное количество по заявке ('.round($free*PPUP,3).'  '.SecStr($v['dim_name']).')';	
				continue;		
			}
			
			
		} 
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//есть ли данная позиция в расп. на отгрузку. есть - вернуть поз. расп на пр, нет - false
	protected function PosInSh($acc_position, $sh_positions, &$find_pos){
		$has=false;
		$find_pos=NULL;
		foreach($sh_positions as $k=>$v){
			if(($v['position_id']==$acc_position['id'])&&($v['komplekt_ved_id']==$acc_position['komplekt_ved_id'])){
				$has=true;
				$find_pos=$v;
				break;	
			}
		}
		
		return $has;
	}
	
	
	
	
	public function HasShsorAccs($id){
		 
		  $can=false;
		 
		//проверить связанные поступления
		  $_accg=new AccGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  //$dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  if($v['is_confirmed']==1) {
				  $can=$can||true;
				  //$reasons[]=' поступление № '.$v['id'].'';	
			  }
			  
		  }
		  /*if(count($reasons)>0) {
			// if(strlen($reason)!=0) $reason.='; ';
			//  $reason.=" по счету имеются утвержденные поступления: ";
			 // if(strlen($reason)>0) $reason.=',';
		  }
		  $reason.=implode(', ',$reasons);
		  */
		  
		  //проверить связанные распоряжения на отгрузку
		  /*$_accg=new ShIGroup;
		  $_accg->setidname('bill_id');
		  $reasons=array();
		  $arr=$_accg->getitemsbyidarr($id);
		  foreach($arr as $k=>$v){
			  if($v['is_confirmed']==1) {
				  $can=$can||true;
				//  $reasons[]=' распоряжение на отгрузку № '.$v['id'].'';	
			  }
			  
		  }*/
		 /* if(count($reasons)>0) {
			  if(strlen($reason)>0) $reason.='; ';
			 // $reason.=" по счету имеются утвержденные распоряжения на отгрузку: ";
		  }
		  $reason.=implode(', ',$reasons);*/
		  
		  return $can;	
	}
	
	
	
	
	//метод для выравнивания по поступлениям
	public function DoEq($id, array $args, &$output, $is_auto=0, $sh=NULL, $_result=NULL, $express_scan=false, $extra_reason=''){
		$output=''; $items=array();
		if($sh===NULL) $sh=$this->GetItemById($id);
	 
		$log=new ActionLog();
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($sh['is_confirmed_shipping']==0){
			$output='Выравнивание позиций невозможно: не утверждена отгрузка счета.';
			return;
		}
		
		//проверить число утвержд. поступлений
		$items=$this->ScanEq($id,  $args, $output1, $sh, $express_scan);
		
		$_ni=new BillNotesItem;
		
		//находим все вход. счета, ровняем их
		$_bill_in=new BillInItem;
		if($is_auto==0){
		  foreach($args as $k=>$v){
			  $_t_arr=explode(';',$v);
			  
			  $sql='select sp.quantity, s.id from bill as s
			   inner join bill_position as sp on s.id=sp.bill_id 
			  where 
			   s.is_confirmed_shipping=1 
			   and s.is_incoming=1
			   and s.out_bill_id="'.$id.'" 
			  
			  
			   
			   and sp.position_id="'.$_t_arr[0].'" 
			   and sp.komplekt_ved_id="'.$_t_arr[4].'"';
			  
			//  echo $sql;
			  
			  $set=new MysqlSet($sql);
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
				  $f=mysqli_fetch_array($rs);
				  $args_sh=array();
				 // $args_sh[]=$_t_arr[0].';'.$f['quantity'].';'.$_t_arr[4];
				  //echo $_t_arr[0].';'.$f['quantity'].';'.$_t_arr[4];
				  
				   $args_sh=array($_t_arr[0].';'.$f['quantity'].';'.$_t_arr[2].';'.$_t_arr[3].';'.$_t_arr[4].';'.$id);
				   
				//  print_r($args_sh);
				  
				  $_bill_in->DoEq($f['id'],  $args_sh,$output);
				  
			  }
			  
		  }
		}
		//выравниваем позиции счета
		
		$_sh_p=new BillPosItem;
		$_sh_pm=new BillPosPMItem;
		foreach($items as $k=>$v){
			if($v['delta']==0) continue;
			$sh_p=$_sh_p->GetItemByFields(array('bill_id'=>$id, 'storage_id'=>0/*$v['storage_id']*/, 'sector_id'=>0/*$v['sector_id']*/, 'position_id'=>$v['position_id'],'komplekt_ved_id'=>$v['komplekt_ved_id']));
			
			
			
			if($sh_p!==false){
				$params=array();
				
				if($v['delta']>=0){ //выравнивать только недовоз! перевоз не выравнивать!
				
				  $params['quantity']=round(($v['quantity']-$v['delta']),3);
				  
				  //получить +/- его подставить
				  $sh_pm=$_sh_pm->GetItemByFields(array('bill_position_id'=>$sh_p['id']));
				  if($sh_pm!==false){
					  $pms=array(
						  'plus_or_minus'=>$sh_pm['plus_or_minus'],
						  'rub_or_percent'=>$sh_pm['rub_or_percent'],
						  'value'=>$sh_pm['value'],
						  //'discount_plus_or_minus'=>$sh_pm['discount_plus_or_minus'],
						  'discount_rub_or_percent'=>$sh_pm['discount_rub_or_percent'],
						  'discount_value'=>$sh_pm['discount_value']
					  );	
				  }else $pms=NULL;
				  
				  
				  $_sh_p->Edit($sh_p['id'], $params, $pms);
				  
				 // $description=$sh_p['name'].' <br /> Кол-во: '.$params['quantity'].'<br /> ';
				  $description='Счет №'.$sh['code'].': '.$sh_p['name'].' <br /> Кол-во: '.$v['quantity'].' было изменено на:  '.round($params['quantity'],3).'<br /> ';
				 
				  //создать примечание 
				  if($is_auto==1){
					 $log->PutEntry(0,'автоматическое редактирование позиции счета в связи с выравниванием позиций',NULL,93,NULL,SecStr($description.$extra_reason),$id);	 
					 $posted_user_id=0;
					 $note='Автоматическое примечание: позиция счета '.$sh_p['name'].' была выровнена при автоматическом выравнивании, кол-во '.$v['quantity'].' было изменено на '.round($params['quantity'],3).''.$extra_reason;
				  }else{
					 $log->PutEntry($_result['id'],'редактировал позицию счета в связи с выравниванием позиций',NULL,93,NULL,SecStr($description.$extra_reason),$id);	
					 
					 $posted_user_id=$_result['id'];
					 $note='Автоматическое примечание: позиция счета '.$sh_p['name'].' была выровнена, кол-во '.$v['quantity'].' было изменено на '.round($params['quantity'],3).''.$extra_reason; 
				  }
				  
				   $_ni->Add(array(
						'user_id'=>$id,
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$posted_user_id,
						'note'=>SecStr($note)
						));
				  
				}
			}
			
		}
		$output='Выравнивание позиций завершено.';
		if(!$express_scan) $this->ScanDocStatus($id,array(),array());	
	}
	
	
	
	//сканирование утвержденных подчиненных докум-тов с позицией
	public function ScanEq($id, array $args, &$output, $sh=NULL, $express_scan=false, $continue_message=".\nПродолжить выравнивание данной позиции?"){
		if($sh===NULL) $sh=$this->GetItemById($id);
		$items=array();
		$total_summ=0; $summ_in_doc=0;
		
		$total_summ1=0; $summ_in_doc1=0;
		
		$output='<ul>';
		$docs=array(); $docs1=array();
		
		$docs_ext=array(); $docs_ext1=array();
		$_pos=new PosItem;
		$_pdi=new PosDimItem;
		
		$count_acc=0;   $count_acc1=0; 
		//перебор по позициям
		foreach($args as $k=>$v){
			$_t_arr=explode(';',$v);
			$summ=0;
			
			$summ_in_doc+=$_t_arr[1];
			
			//по каждой позиции перебрать все позиции подчиненных реализаций
			$sql='select * from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and bill_id="'.$id.'" ) and position_id="'.$_t_arr[0].'" and komplekt_ved_id="'.$_t_arr[4].'"';
			
			//echo $sql;
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$summ+=$f['quantity'];
				$count_acc+=$f['quantity'];
				if(!in_array('№'.$f['acceptance_id'],$docs)){
					 $docs[]='№'.$f['acceptance_id'];
					 $docs_ext[]='<a href="ed_acc.php?action=1&id='.$f['acceptance_id'].'" target="_blank">'.'№'.$f['acceptance_id'].'</a>';
				}
			}
			
			$pos=$_pos->GetItemById($_t_arr[0]);
			$pdi=$_pdi->GetItemById($pos['dimension_id']);
			
			
			
			
			$total_summ+=$summ;	
			
			
			//перебрать суммы подчиненных поступлений
			$sql='select * from acceptance_position where acceptance_id in(select id from acceptance where is_confirmed=1 and out_bill_id="'.$id.'" ) and position_id="'.$_t_arr[0].'" and komplekt_ved_id="'.$_t_arr[4].'"';
			
			$summ1=0;
			
			$set=new MysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				$summ1+=$f['quantity'];
				$count_acc1+=$f['quantity'];
				if(!in_array('№'.$f['acceptance_id'],$docs1)){
					 $docs1[]='№'.$f['acceptance_id'];
					$docs_ext1[]= '<a href="ed_acc_in.php?action=1&id='.$f['acceptance_id'].'" target="_blank">'.'№'.$f['acceptance_id'].'</a>';	 
				}
			}
			
			$pos=$_pos->GetItemById($_t_arr[0]);
			$pdi=$_pdi->GetItemById($pos['dimension_id']);
			
			$total_summ1+=$summ1;
			
			
			//как нам считать дельту?
			//по поступлениям - для товаров
			//по реализациям - для услуг
			
			if(!$this->IsPosUsl($_t_arr[0])) $items[]=array('position_id'=>$_t_arr[0], 'quantity'=>$_t_arr[1], 'storage_id'=>$_t_arr[2], 'sector_id'=>$_t_arr[3], 'komplekt_ved_id'=>$_t_arr[4], 'delta'=>round(($_t_arr[1]-$summ1),3));
			else $items[]=array('position_id'=>$_t_arr[0], 'quantity'=>$_t_arr[1], 'storage_id'=>$_t_arr[2], 'sector_id'=>$_t_arr[3], 'komplekt_ved_id'=>$_t_arr[4], 'delta'=>round(($_t_arr[1]-$summ),3));
			
		}
			
			
		
		 
		
		
		if(($total_summ==0)&&($total_summ1==0)){
			$output.="<li>\nПозиция ".htmlspecialchars($pos["name"])." не найдена ни в одном утвержденном подчиненном документе. Количество будет обнулено. </li>";
		}else{
			
			 
			
			//в реализациях 
			if(count($docs)){
				 $output.="<li>\nПозиция ".htmlspecialchars($pos["name"])." найдена в утвержденных реализациях: ".implode(", ",$docs_ext)." в количестве ".$count_acc." ".htmlspecialchars($pdi["name"]);
				 if($count_acc>$summ_in_doc){
					 $output.=', что превышает количество в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }elseif($count_acc<$summ_in_doc){
					 $output.=', что меньше количества в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }
				  $output.='</li>';
			}else $output.="<li>\nПозиция ".htmlspecialchars($pos["name"])." не найдена в утвержденных реализациях.</li>";
			
			
			//в поступлениях
			if(count($docs1)){
				 $output.="<li>\nПозиция ".htmlspecialchars($pos["name"])." найдена в утвержденных поступлениях: ".implode(", ",$docs_ext1)." в количестве ".$count_acc1." ".htmlspecialchars($pdi["name"]);
				 if($count_acc1>$summ_in_doc){
					 $output.=', что превышает количество в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }elseif($count_acc1<$summ_in_doc){
					 $output.=', что меньше количества в cчете '.$summ_in_doc.' '.htmlspecialchars($pdi['name']).'';
				 }
				 $output.='</li>';
			}else $output.="<li>\nПозиция ".htmlspecialchars($pos["name"])." не найдена в утвержденных поступлениях.</li>";
			
			
			
			if(($count_acc>=$summ_in_doc)&&($count_acc1>=$summ_in_doc)){
				$output.="<li>\nПозиция выравниванию не подлежит.</li>";
			}else $output.=$continue_message; //".\nПродолжить выравнивание данной позиции?";
			//.
		}
		
		
		$output.='</ul>';
		
		
		
		return $items;
	}
	
	
	
	
	
	
	
	
	
	
	
	//контроль возможности редактирования kol-va позиций
	public function CanEditQuantities($id, &$reason, $itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed_price']!=0)||($itm['is_confirmed_shipping']!=0))) {
			$reason.='счет утвержден';
			$can_delete=$can_delete&&false;
		}
		
		
		/*$set=new mysqlSet('select * from sh_i where bill_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются утвержденные распоряжения на отгрузку: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='№'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}*/
		
		$set=new mysqlSet('select * from acceptance where is_confirmed=1 and sh_i_id in(select id from sh_i where bill_id="'.$id.'" and is_confirmed=1)');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		if($rc>0){
			if(strlen($reason)>0) $reason.=', ';
			$reason.='по счету имеются утвержденные реализации: ';
		 	$nums=array();
			for($i=0;$i<$rc;$i++){
				$f=mysqli_fetch_array($rs);
				$nums[]='№'.$f['id'];
				
			}
			$reason.=implode(', ',$nums);
			$can_delete=$can_delete&&false;
		}
		
		
		
		
		return $can_delete;
	}
	
	
	//проверка возможности редактирования "счет в бухгалтерии"
	public function CanIsInBuh($bill_id, &$rss, $bill_item=NULL, $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $summ_by_bill=NULL, $summ_by_payed=NULL){
		$can_is_in_buh=true;
		
		//echo '<h1>zzzz</h1>';
		
		$_rss=array();
		if($bill_item===NULL) $bill_item=$this->getitembyid($bill_id);
		
		if($summ_by_bill===NULL) $summ_by_bill=$this->CalcCost($bill_id);
		if($summ_by_payed===NULL) $summ_by_payed=$this->CalcPayed($bill_id);
		
		
		if($bill_item['is_in_buh']==1){
			$can_is_in_buh=$can_is_in_buh&&$can_unconfirm_in_buh;
			
			if(!$can_unconfirm_in_buh) $_rss[]=' у Вас недостаточно прав для снятия опции, пожалуйста, обратитесь к администратору для получения прав доступа';
		}else{
			$can_is_in_buh=$can_is_in_buh&&$can_confirm_in_buh;
			if(!$can_confirm_in_buh) $_rss[]=' у Вас недостаточно прав для установки опции, пожалуйста, обратитесь к администратору для получения прав доступа';
		}
		
		if(!(($bill_item['is_confirmed_price']==1)&&($bill_item['is_confirmed_shipping']==1))){
			$can_is_in_buh=$can_is_in_buh&&$false;	
			$_rss[]=' у счета должны быть утверждены цены и отгрузка ';
		}
		
		
		if($summ_by_bill<=$summ_by_payed){
			$can_is_in_buh=$can_is_in_buh&&$false;	
			$_rss[]=' счет полностью оплачен ';	
		}
		
		
		if($bill_item['is_in_buh']==1){
			if($bill_item['user_in_buh_id']==-1) $_rss[]=' наличие счета в бухгалтерии утверждено автоматически на основании 100% оплаты счета';
		}
		
		$rss=implode(', ',$_rss);
		
		return $can_is_in_buh;
	}
	
		
		
	
	
	//уменьшение суммы привязанных к счету оплат при уменьшении сумммы счета
	public function LowPayments($id, $_result=NULL, $old_bill_summ=0, $new_bill_summ=NULL, $calc_payed=NULL, $actor_id=NULL){
		
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		
		if($new_bill_summ===NULL) $new_bill_summ=$this->CalcCost($id);
		if($calc_payed===NULL) $calc_payed=$this->CalcPayed($id);
		
		
		$_pbi=new PayForBillItem;
		$_pi=new PayInItem;
		
		//уменьшили сумму счета, и по счету были оплаты
		//если сумма оплат больше, чем сумма счета - уменьшить сумму оплат.
		if(($new_bill_summ<$old_bill_summ)&&($calc_payed>0)&&($calc_payed>$new_bill_summ)){
			$delta=$calc_payed-$new_bill_summ;
			
			//перебираем прикрепленные к счету инв акты, оплаты
			$sql='select pb.*, p.code as invcalc_code, b.code as bill_code, pp.code as payment_code
			 from payment_for_bill as pb
				left join invcalc as p on p.id=pb.invcalc_id
				left join bill as b on b.id=pb.bill_id
				left join payment as pp on pb.payment_id=pp.id
			where pb.bill_id="'.$id.'"';
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				if($delta<=0) break;
				
				//$delta_local=$delta-(float)$f['value'];
				if($delta>=(float)$f['value']){
					//сумма прикрепления меньше, чем разница
					//выбросить это прикрепление полностью
					$_pbi->Del($f['id']);
					
					//автоматическое примечание (в счет, оплату, инвкальк)
					if($f['payment_id']!=0){
						$note='входящая оплата № '.$f['payment_code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении реализации №'.$actor_id.' без довоза.';	
					}elseif($f['invcalc_id']!=0){
						$note='акт № '.$f['invcalc_code'].': удален платеж по счету '.$f['bill_code'].' на сумму '.$f['value'].' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении реализации №'.$actor_id.' без довоза.';
					}
					
					
					$delta-=(float)$f['value'];
						
				}else{
					//сумма прикрепления больше, чем разница
					//уменьшить сумму прикрепления	
					$_pbi->Edit($f['id'], array('value'=>((float)$f['value']-$delta)));
					
					//автоматическое примечание (в счет, оплату, инвкальк)
					if($f['payment_id']!=0){
						$note='входящая оплата № '.$f['payment_code'].': уменьшен платеж по счету '.$f['bill_code'].' с суммы '.$f['value'].' руб. на сумму '.((float)$f['value']-$delta).' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении реализации №'.$actor_id.' без довоза.';	
					}elseif($f['invcalc_id']!=0){
						$note='акт № '.$f['invcalc_code'].': уменьшен платеж по счету '.$f['bill_code'].' с суммы '.$f['value'].' руб. на сумму '.((float)$f['value']-$delta).' руб. на основании уменьшения суммы счета с '.$old_bill_summ.' руб. на '.$new_bill_summ.' руб. при утверждении реализации №'.$actor_id.' без довоза.';	
					}
					
					$delta=0;
				}
				
				
				
				
				//автоматическое примечание (в счет, оплату, инвкальк)
				$_bni=new BillNotesItem;	
				if($f['payment_id']!=0){
					$log->PutEntry($_result['id'],'удаление платежа по счету из входящей оплаты',NULL,93,NULL,$note,$f['bill_id']);
		
					$log->PutEntry($_result['id'],'удаление платежа по счету из входящей оплаты',NULL,272,NULL,$note,$f['payment_id']);
					
					$_pni=new PaymentNotesItem;	
					
					 $_pni->Add(array(
							  'user_id'=>$f['payment_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
					));
				}elseif($f['invcalc_id']!=0){
					$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,93,NULL,$note,$bill_id);
		
					$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,452,NULL,$note,$f['invcalc_id']);
					
					$_pni=new InvCalcNotesItem();
					 $_pni->Add(array(
							  'user_id'=>$f['invcalc_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
					));
				}
				
				 $_bni->Add(array(
							  'user_id'=>$f['bill_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
				));
				
				//переприкрепить аванс к счетам...
				if($f['payment_id']!=0){
					//привязать аванс оплаты к счетам
					//$_pi->BindPayments(	$f['payment_id'], $_result['org_id']);	
					//сделать позже
				}
			}
		}
	}
	
	
	
	
	 
	//принадлежит ли данная категория категории услуг
	public function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная позиция категории услуг
	public function IsPosUsl($position_id){
		return in_array($position_id,self::$position_uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная категория категории услуг по заявкам
	protected function IsSemiUsl($id){
		return in_array($id,self::$semi_uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная позиция категории услуг по заявкам
	public function IsPosSemiUsl($position_id){
		return in_array($position_id,self::$position_semi_uslugi/*$this->uslugi*/);
	}
}
?>