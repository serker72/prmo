<?
require_once('abstractitem.php');

require_once('docstatusitem.php');

require_once('payitem.php');
require_once('trust_group.php');
//require_once('sh_i_group.php');
require_once('acc_group.php');
require_once('paygroup.php');
require_once('wfgroup.php');

require_once('acc_item.php');
require_once('wfitem.php');


require_once('actionlog.php');
require_once('authuser.php');


require_once('billitem.php');

require_once('bill_in_item.php');
//require_once('sh_i_item.php');
require_once('acc_item.php');

require_once('billpospmformer.php');


require_once('maxformer.php');
require_once('authuser.php');
require_once('invcalccreator.php');
require_once('bdetailsitem.php');
require_once('actionlog.php');
require_once('billgroup.php');

require_once('invcalcsync.php');

require_once('payforbillitem.php');
require_once('period_checker.php');



require_once('supplieritem.php');
require_once('opfitem.php');
require_once('payforbillitem.php');

require_once(ABSPATH.'classes/an_supplier.php');


//абстрактный элемент
class InvCalcItem extends AbstractItem{
	
	public $sync;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='invcalc';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->sync=new InvCalcSync;	
	}
	
	
	public function GetItemById($id,$mode=0){
		$item=AbstractItem::GetItemById($id,$mode);
		
		$rd=$this->FindRealDebt($id, $item);
		
		$item['real_debt']=$rd['real_debt'];
		$item['real_debt_id']=$rd['real_debt_id'];
		
		return $item;		
	}
	
	
	//нахождение реальной корректировки сальдо
	public function FindRealDebt($id, $item=NULL){
		$real_debtt=array(
			'real_debt'=>0,
			'real_debt_id'=>1
		);
		
		if($item===NULL) $item=parent::GetItemById($id);
		
		
		$an=new AnSupplier;
		$deb=$an->OstBySup($item['supplier_id'],$item['invcalc_pdate']+24*60*60-1,$item['org_id'], $id);
		
		$debt=$item['debt'];
		if($item['debt_id']==3){
			$debt=-1.0*$debt;	
		}
		
		$real_debt=$debt-$deb;
		
		if($real_debt==0){
			$real_debt_id=1;	
		}elseif($real_debt>0){
			$real_debt_id=2;
		}elseif($real_debt<0){
			$real_debt_id=3;
		}
			
		$real_debtt=array(
			'real_debt'=>abs($real_debt),
			'real_debt_id'=>$real_debt_id
		);
		
		return $real_debtt;
			
	}
	
	
	
	public function Edit($id,$params,$scan_status=false, $bind_to_bills=false, $result=NULL){
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
		
		//если утверждаем - то автопримечания
		if(isset($params['is_confirmed_inv'])&&($params['is_confirmed_inv']==1)&&($item['is_confirmed_inv']==0)){
			
			
			
			  $_ni=new InvCalcNotesItem;
			  $_si=new SupplierItem; $_opf=new OpfItem;
			  $n_params=array();
			  
			  //Данный акт регулирует конечный остаток взаиморасчетов по текущей организации с выбранным контрагентом ХХХ (подставить) на ХХХ (подставить дату) на сумму  ХХХ (подставить). Данная сумма считается в программе "Исход Оплатой/Вход оплатой"
			  $si=$_si->GetItemById($item['supplier_id']);
			  $opf=$_opf->GetItemById($si['opf_id']);
			  
			 
			  
			  $note="Автоматическое примечание: Данный акт регулирует конечный остаток взаиморасчетов по текущей организации с выбранным контрагентом ".SecStr($si['full_name']).", ".SecStr($opf['name'])." на ".date('d.m.Y', $item['invcalc_pdate'])." на сумму  ".number_format($item['real_debt'],2,'.',' ')." руб. Данная сумма считается в программе ";
			  
			  if($item['real_debt_id']==2){
				 $note.=" Исходящей оплатой."; 
			  }elseif($item['real_debt_id']==3){
				 $note.=" Входящей оплатой.";  
			  }elseif($item['real_debt_id']==1){
				 $note.=" нулевой корректировкой.";  
			  }
			  
			   $n_params=array(
						  'user_id'=>$id,
						  'is_auto'=>1,
						  'pdate'=>time(),
						  'posted_user_id'=>0,
						  'note'=>$note
						  );
			  $_ni->Add($n_params);
			  
			
			
			 //если задана автопривязка - то автопривязать к неоплаченным счетам
			 if($bind_to_bills) $this->BindToBills($id, $item,NULL,$result);
		}
		
		
		//снимаем утв. коррекции... убрать документ из связанных счетов и оплат. перепривязать счета и оплаты
		if(isset($params['is_confirmed_inv'])&&($params['is_confirmed_inv']==0)&&($item['is_confirmed_inv']==1)){
			
			$this->FreeBindedDocs($id, $item,$result);
			
			
		}
		
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params,$result);
		
		//die();
	}
	
	
	
	//проверить наличие неопл. счетов
	function CheckUpayedBills($supplier_id, $org_id, &$bills, $is_incoming=1){
		
		$has=false;
		$bills=array();	
		
		
		$sql='select *, bill.id as position_id from bill 
		where 
		is_confirmed_price=1 
		and is_confirmed_shipping=1 
		and supplier_id="'.$supplier_id.'" 
		and org_id="'.$org_id.'" 
		and is_incoming="'.$is_incoming.'"
		order by pdate_payment_contract asc, id asc';
		$set=new MysqlSet($sql);
		
		//echo $sql;
		$_bi=new BillItem;
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			//$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//if(!$this->FilterBills($f['supplier_bill_pdate'],$_null_pdate)) continue;
			
			$f['kind']=0; //вид документа - счет
			
			if($f['pdate']!=0) $f['pdate']=date("d.m.Y",$f['pdate']);
			else $f['pdate']='-';
			
			
			if($f['supplier_bill_pdate']!=0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			$f['pdate_payment_contract_unf']=$f['pdate_payment_contract'];
			
			if($f['pdate_payment_contract']!=0) $f['pdate_payment_contract']=date("d.m.Y",$f['pdate_payment_contract']);
			else $f['pdate_payment_contract']='-';
			
			
			
			
			
			$f['summ']=$_bi->CalcCost($f['id']);
			$f['payed']=$_bi->CalcPayed($f['id']);
			
			if((float)$f['summ']<=(float)$f['payed']) continue;
			
			//$f['hash']=md5($f['kind'].'_'.$f['id']);
			
			$bills[]=$f;
			
			$has=true;
		}
		
		return $has;
	}
	
	
	//привязать к неопл. счетам
	function BindToBills($id, $item=NULL, $bills=NULL, $result=NULL){
		if($item===NULL) $item=$this->GetItemById($id);
		
		if(($item['real_debt_id']!=2)&&($item['real_debt_id']!=3)) return;
		
		
		
		if($item['real_debt_id']==2){
			$_bi=new BillInItem; $object_id=613; $is_incoming=1;
		}else{
			$_bi=new BillItem; $object_id=93; $is_incoming=0;
		}
		
		
		
		$log=new ActionLog;
		$au=new AuthUser;
		
		if($result===NULL) $result=$au->Auth();
		
		if($bills===NULL) $this->CheckUpayedBills($item['supplier_id'],$item['org_id'],$bills,$is_incoming);
		
		
		$rest_value=$item['real_debt'];
		
		
		
		
		$_pbi=new PayForBillItem; 
		
		foreach($bills as $k=>$v){
			$v['summ']=$_bi->CalcCost($v['id']);
			$v['payed']=$_bi->CalcPayed($v['id']);
			$v['by_acc']=$_bi->CalcAcc($v['id'], NULL,NULL,$item['invcalc_pdate']); //включать в оплату только подвоз до даты инввзр включительно
			
			//echo date('d.m.Y H:i:s',$v['pdate_payment_contract_unf']).' '.date('d.m.Y H:i:s',$item['invcalc_pdate']).'<br>';
			
			//пропускаем счета с план датой оплаты позднее даты коррекции
			if($v['pdate_payment_contract_unf']>$item['invcalc_pdate']) continue;
			
			//echo 'rr';
			
			$our_summ=0;
			if($v['status_id']==10) $our_summ=$v['by_acc'];
			else{
				$our_summ=$v['summ'];
				
								
				//если нет подвоза - то не оплачивать...
				if($v['by_acc']==0) continue;	
			}
			
			
							
			
			
			if($rest_value<=0) break;
			
			
			//echo 'zozozo '.$v['code'].' our_summ='.$our_summ.' rest_value='.$rest_value.' payed='.$v['payed'].' $v[by_acc]='.$v['by_acc'];
				
			
			if((float)$our_summ>(float)$v['payed']){ 
				$delta=(float)$our_summ-(float)$v['payed'];
				
				//echo 'must be '.$our_summ.' vs podvoz='.$v['by_acc'].' vs '.$v['payed'].' delta='.$delta.' $rest_value='.$rest_value;
			
				
				if($delta>$rest_value){
					
					$v['value']=round($rest_value,2);
					$rest_value-=$v['value'];
					$arr[]=$f;
					//break;	
				}else{
					$v['value']=round($delta,2);
					$rest_value-=$delta;
					$arr[]=$f;
				}
				
				
				if($v['value']>0){
					
					
					
					
					$_pbi->Add(array(
						'bill_id'=>$v['id'],
						'invcalc_id'=>$id,
						'value'=>$v['value'],
						'is_auto'=>1
					));	
					
					$descr='Акт № '.$item['code'].', счет №'.$v['code'].'<br /> сумма '.$v['value'].' руб.<br />';
					$log->PutEntry($result['id'],'автоматическое добавление инвентаризационного акта к счету в качестве  оплаты', NULL, $object_id,NULL,$descr,$v['id']);	
					
					$log->PutEntry($result['id'],'автоматическое добавление инвентаризационного акта к счету в качестве  оплаты', NULL, 450,NULL,$descr,$id);	
					
				}
				
				
			}	
		}
		
		//die();
	}
	
	
	
	
	
	
	
	//открепление от связанных документов
	public function FreeBindedDocs($id, $item=NULL, $_result=NULL){
		$log=new ActionLog;
		$au=new AuthUser;
		if($_result===NULL) $_result=$au->Auth();
		$_pi=new PayItem;
		
		$_pb=new PayForBillItem;
		
		if($item===NULL) $item=$this->GetItemById($id);
		
		$_pay=new PayItem;
		//	$_bill=new BillItem;
		if($item['real_debt_id']==2){
			$_bill=new BillInItem; $object_id=613;
		}else{
			$_bill=new BillItem; $object_id=93;
		}
		
		$_pbi=new PayForBillItem;
			
			 //связанные оплаты
			/*$sql='select p.*, pb.id as pb_id, pb.value as pb_value from payment as p inner join payment_for_bill as pb
			on p.id=pb.payment_id and pb.invcalc_id="'.$id.'"
			';
			 $reasons=array();
			$set=new mysqlSet($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			for($i=0; $i<$rc; $i++){
				$v=mysqli_fetch_array($rs);
				
				$_pbi->Del($v['pb_id']);
				
				 $descr=$v['code'].'<br /> сумма '.$v['pb_value'].' руб.<br />';
				$log->PutEntry($_result['id'],'удалил инвентаризационный акт из оплаты', NULL, 452,NULL,$descr,$id);	
					
				$log->PutEntry($_result['id'],'удалил инвентаризационный акт из оплаты', NULL, 270,NULL,$descr,$v['id']);	
				
				$_pi->BindPayments($v['id'], $_result['org_id']);
				
			}
			*/
			
			 //связанные счета
			$sql='select p.*, pb.id as pb_id, pb.value as pb_value from bill as p inner join payment_for_bill as pb
			on p.id=pb.bill_id and pb.invcalc_id="'.$id.'"
			';
			 $reasons=array();
			$set=new mysqlSet($sql);
			$rs=$set->getResult();
			$rc=$set->getResultNumRows();
			for($i=0; $i<$rc; $i++){
				$v=mysqli_fetch_array($rs);
			
				$_pbi->Del($v['pb_id']);
				
				$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,$object_id,NULL,'акт № '.$item['code'].': удален платеж по счету '.$v['code'].' на сумму '.$v['pb_value'].' руб. ',$v['id']);
			
				$log->PutEntry($_result['id'],'удаление платежа по счету из инвентаризационного акта',NULL,452,NULL,'акт № '.$item['code'].': удален платеж по счету '.$v['code'].' на сумму '.$v['pb_value'].' руб. ',$id);
				
				
			}
		
		
		
	}
	
	
	
	
	
	
	//контроль возможности удаления
	public function CanDelete($id, &$reason,$itm=NULL){
		$can_delete=true;
		
		$reason='';
		
		if($itm===NULL) $itm=$this->GetItemById($id);
		
		if(($itm!==false)&&(($itm['is_confirmed']!=0)||($itm['is_confirmed_inv']!=0))) {
			$reason.='инвентаризационный акт утвержден';
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
		
		
		
			
		
		if(isset($new_params['is_confirmed_inv'])&&isset($old_params['is_confirmed_inv'])){
			if(($new_params['is_confirmed_inv']==1)&&($old_params['is_confirmed_inv']==0)&&($old_params['status_id']==1)){
				//смена статуса с 1 на 16
				$this->Edit($id,array('status_id'=>16));
				
				$stat=$_stat->GetItemById(16);
				$log->PutEntry($_result['id'],'смена статуса инвентаризационного акта',NULL,452,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed_inv']==0)&&($old_params['is_confirmed_inv']==1)&&(($old_params['status_id']==16))){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса инвентаризационного акта',NULL,452,NULL,'установлен статус '.$stat['name'],$item['id']);
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
		
		  if($item['is_confirmed']==1){
			  $can=$can&&false; 
			  $reason.='у документа утверждено заполнение';
		  }
		
		
		
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
	
	
	
	//список всех связанных документов, в любом статусе
	public function GetBindedDocuments($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		
		  
		  
		   //связанные оплаты
		  $sql='select p.* from payment as p inner join payment_for_bill as pb
		  on p.id=pb.payment_id and pb.invcalc_id="'.$id.'"
		  ';
		   $reasons=array();
		  //echo $sql;
		  $set=new mysqlSet($sql);
		  $rs=$set->getResult();
		  $rc=$set->getResultNumRows();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
		  
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  
				  //$reasons[]=' оплата <a href="ed_pay.php?action=1&id='.$v['id'].'&from_begin=1" target="_blank">№ '.$v['code'].'</a>, статус документа: '.$dsi['name'];	
				  $reasons[]=' оплата № '.$v['code'].', статус документа: '.$dsi['name'];	
			  
			  
		  }
		  if(count($reasons)>0) $reason.=' По инвентаризационному акту имеются оплаты: ';
		  $reason.=implode(', ',$reasons);
		  
		   //связанные счета
		  $sql='select p.* from bill as p inner join payment_for_bill as pb
		  on p.id=pb.bill_id and pb.invcalc_id="'.$id.'"
		  ';
		   $reasons=array();
		  $set=new mysqlSet($sql);
		  $rs=$set->getResult();
		  $rc=$set->getResultNumRows();
		  for($i=0; $i<$rc; $i++){
			  $v=mysqli_fetch_array($rs);
		  
			  $dsi=$_dsi->GetItemById($v['status_id']);
			  
			  
			  
				 // $reasons[]=' входящий счет <a href="ed_bill.php?action=1&id='.$v['id'].'&from_begin=1" target="_blank">№ '.$v['code'].'</a>, статус документа: '.$dsi['name'];	
				  $reasons[]=' счет № '.$v['code'].', статус документа: '.$dsi['name'];	
			  
			  
		  }
		  if(count($reasons)>0) $reason.=' По инвентаризационному акту имеются счета: ';
		  $reason.=implode(', ',$reasons);
		  
		 
		return $reason;
	}
	
	
	//список связанных не аннулированных не утв документов для автоаннулирования
	public function GetBindedDocumentsToAnnul($id){
		$reason=''; $reasons=array();
		
		$_dsi=new DocStatusItem;
		
		$can=true;
		  
	
		return $reason;
	}
	
	public function AnnulBindedDocuments($id){
		
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		$stat=$_stat->GetItemById(3);
		
		
		$this->FreeBindedDocs($id);
		
		
	}
	
	
	
	
	public function DocCanConfirmShip($id,&$reason,$item=NULL,$periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed_inv']!=0){
			
			$can=$can&&false;
			$reasons[]='у акта утверждена корректировка задолженности';
			$reason.=implode(', ',$reasons);
		}else{
			
			//контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['invcalc_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата проведения корректировки взаиморасчетов '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		
		}
		
		return $can;
	}
	
	
	//запрос о возможности снятия утв корр и возвращение причины, почему нельзя 
	public function DocCanUnconfirmShip($id,&$reason,$item=NULL,$periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed_inv']!=1){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='у акта не утверждена корректировка задолженности';
			$reason.=implode(', ',$reasons);
		}else{
			
			//контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['invcalc_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата проведения корректировки взаиморасчетов '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		
		}
		
		return $can;
	}
	
	
	
	public function DocCanConfirm($id,&$reason,$item=NULL,$periods=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		//var_dump($item);
		if($item['is_confirmed']!=0){
			
			$can=$can&&false;
			$reasons[]='у акта утверждено заполнение';
			$reason.=implode(', ',$reasons);
		}else{
			
			//контроль закрытого периода 
		  if(!$_pch->CheckDateByPeriod($item['invcalc_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата проведения корректировки взаиморасчетов '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		
		}
		
		return $can;
	}
	
	
	public function DocCanUnconfirm($id,&$reason,$item=NULL,$periods=NULL){
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
		  if(!$_pch->CheckDateByPeriod($item['invcalc_pdate'], $item['org_id'],$rss23,$periods)){
			  $can=$can&&false;
			  $reasons[]='дата проведения корректировки взаиморасчетов '.$rss23;	
		  }
		  $reason.=implode(', ',$reasons);
		
		
		}
		
		return $can;
	}
	
	
	//контроль даты инвентаризации!!!
	public function CheckInventoryPdate($pdate,$supplier_id, $org_id, &$rss, $except_id=0){
		$res=true; //все ок
		$_dsi=new DocStatusItem;
		
		$sql='select * from '.$this->tablename.' where 
		invcalc_pdate>="'.$pdate.'"  
		and supplier_id="'.$supplier_id.'" 
		and org_id="'.$org_id.'" 
		and id<>"'.$except_id.'"
		and status_id in(2,16)';
		
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		if($rc>0) $res=false;
		
		$rss=''; $_rss=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$dsi=$_dsi->GetItemById($f['status_id']);
			$_rss[]='инвентаризационный акт <a href="ed_invcalc.php?action=1&id='.$f['id'].'" target="_blank">№ '.$f['code'].'</a>, дата инвентаризации '.date('d.m.Y', $f['invcalc_pdate']).', статус '.$dsi['name'] ;
		}
		
		if(count($_rss)>0) $rss="существуют акты инвентаризации: ".implode(',<br />',$_rss);
		
		return $res;
	}
	
	
	
	//найдем стоииость по заказу
	public function CalcCost($id,$item=NULL){
		if($item===NULL){
			$item=$this->GetItemById($id);	
		}
		
		//var_dump($item);
		$total_cost=$item['real_debt'];
		//echo $total_cost;
		return round($total_cost,2);
	}
	
	
	//найдем стоимость по подвозу
	public function CalcAcc($id, $item=NULL, $positions=NULL, $before_pdate=NULL){
		if($item===NULL){
			$item=$this->GetItemById($id);	
		}
		
		//var_dump($item);
		$total_cost=$item['real_debt'];
		//echo $total_cost;
		return round($total_cost,2);
	}
	
	
	public function CalcPayed($id, $except_id=NULL, $item=NULL){
		$sql='';
		$out=0;
		
		if($item===NULL){
			$item=$this->GetItemById($id);	
		}
		
		
	
			//оплаты	
			$sql='select sum(bp.value) from payment_for_bill as bp inner join payment as p on bp.payment_id=p.id where p.is_confirmed=1 and bp.invcalc_id="'.$id.'" ';
		
			if($except_id!==NULL) $sql.=' and p.id<>"'.$except_id.'"';
			
			
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			$out=(float)$f[0];
			
		
		return round($out,2);
		
	}
	
	
	
	public function GetBindedPaymentsFull($id){
		$summ=0;
		$alls=array();	
		
		$_inv=new InvCalcItem;
		
		//$set=new mysqlSet('select * from payment where is_confirmed=1 and id in(select distinct payment_id from payment_for_bill where bill_id="'.$bill_id.'")');
		$set=new mysqlSet('select distinct pb.payment_id, "0" as kind, pb.id, b.code as bill_code, p.code, p.value, p.given_no, p.given_pdate as given_pdate,  p.given_pdate as given_payment_pdate, p.given_pdate as given_payment_pdate_unf 
					 from payment_for_bill as pb 
						inner join payment as p on p.id=pb.payment_id
						inner join invcalc as b on pb.invcalc_id=b.id
		where pb.invcalc_id="'.$id.'" and p.is_confirmed=1 order by p.given_pdate desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$alls[]=$f;
		}
		
		
		
		
		return $alls;
	}
	
	
	
	
	//проверка возможности редактирования "счет в бухгалтерии"
	public function CanIsInBuh($bill_id, &$rss, $bill_item=NULL, $can_confirm_in_buh=false, $can_unconfirm_in_buh=false, $summ_by_bill=NULL, $summ_by_payed=NULL){
		$can_is_in_buh=false;
		
		//echo '<h1>zzzz</h1>';
		
		$_rss=array();
		$_rss[]=' Для акта инвентаризации взаиморасчетов в роли отгрузки не предусмотрено поле "Счет в бухгалтерии"';
		
		$rss=implode(', ',$_rss);
		
		return $can_is_in_buh;
	}
}
?>