<?
require_once('abstractitem.php');
require_once('authuser.php');
require_once('actionlog.php');
/*require_once('sh_i_notesgroup.php');
require_once('sh_i_notesitem.php');
require_once('sh_i_item.php');*/
require_once('docstatusitem.php');


require_once('billnotesitem.php');
require_once('billitem.php');

require_once('komplnotesitem.php');
require_once('komplitem.php');
//require_once('storagesector.php');

//склад
class SectorItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sector';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
	
	//правка
	public function Edit($id,$params, $item=NULL){
		if($item===NULL) $item=$this->GetItemById($id);
		
		AbstractItem::Edit($id,$params);
		
		if(isset($params['is_active'])&&($params['is_active']==0)&&($item['is_active']==1)){
			//снимаем активность - нужно проводить автоаннул и автовыр-е	
			$this->DoAnEq($id,$item);
		}
	}
	
	
	
	
	
	
	
	
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from storage_sector where sector_id='.$id.';';
		$it=new nonSet($query);
		/*
		unset($it);				
		
		$this->item=NULL;*/
		parent::Del($id);
	}	
	
	
	//контроль возможности удаления
	public function CanDelete($id){
		$can_delete=true;
		
		$set=new mysqlSet('select count(*) from bill where sector_id="'.$id.'"  and is_confirmed_shipping=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		$set=new mysqlSet('select count(*) from interstore where (receiver_sector_id="'.$id.'" or sender_sector_id="'.$id.'"  ) and (is_confirmed=1 or is_confirmed_wf=1 or is_confirmed_fill_wf=1)');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		$set=new mysqlSet('select count(*) from komplekt_ved as kv  where kv.status_id in(2, 12, 13) and  kv.sector_id="'.$id.'"');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
	/*	$set=new mysqlSet('select count(*) from sh_i where sector_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		*/
		
		$set=new mysqlSet('select count(*) from acceptance where sector_id="'.$id.'" and is_confirmed=1');
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		
		
		
		
		
		if($f[0]>0) $can_delete=$can_delete&&false;
		
		
		
		return $can_delete;
	}
	
	
	
	public function DocCanUnconfirm($id, $rss=''){
		$cter=0;
		
		//$cter=$this->BuildAnEq($id,$rss,$to_annul_ks, $to_eq_ks, $to_annul_bills, $to_eq_bills, $to_annul_accs, $to_eq_accs);
 
	 
				$sql='
			
			select  t1.position_id, t1.name, t1.dimension, sum(t1.quantity) as s_q
			from acceptance_position as t1 
			where t1.acceptance_id in(select id from acceptance where is_confirmed=1 and sector_id="'.$id.'" 
			group by t1.position_id
			order by 2 asc
			';
			
			$set=new mysqlSet($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				$qua=$f['s_q'];
				
				
				
			//получим всего списано по данной позиции
				$sql2='select sum(quantity) from interstore_position where position_id="'.$f['position_id'].'" and interstore_id in(select id from interstore  where is_confirmed=1 and sender_sector_id="'.$id.'" )';
				$set2=new mysqlSet($sql2);
				$rs2=$set2->GetResult();
				
				$g=mysqli_fetch_array($rs2);
				
				$qua=$qua-(float)$g[0];
				
				if($qua>0) $cter+=$qua;
				
			
			}
		 
		
		

		return $cter;
	}
	
	
	
	
	
	public function DocCanUnconfirmDocs($id, &$rss54){
		$cter=0;
		
		$rss54='';
		
		$cter=$this->BuildAnEq($id,$rss,$to_annul_ks, $to_eq_ks, $to_annul_bills, $to_eq_bills, $to_annul_accs, $to_eq_accs, $to_annul_bill_codes, $to_eq_bill_codes);
		
		//echo $cter;
		
		
		if((count($to_annul_ks)>0)||(count($to_annul_bills)>0)||(count($to_annul_accs)>0)){
			$rss54.="\nдокументы подлежат автоматическому аннулированию:\n";
			if(count($to_annul_ks)>0) $rss54.='Заявки №:'.implode(', ',$to_annul_ks)."\n";
			if(count($to_annul_bill_codes)>0) $rss54.='Входящие счета №:'.implode(', ',$to_annul_bill_codes)."\n";
	//		if(count($to_annul_accs)>0) $rss54.='Распоряжения на приемку №: '.implode(', ',$to_annul_accs)."\n";
			
			
			
		}
		
		if((count($to_eq_ks)>0)||(count($to_eq_bills)>0)||(count($to_eq_accs)>0)){
			$rss54.="\nдокументы подлежат автоматическому выравниванию:\n";
			if(count($to_eq_ks)>0) $rss54.='Заявки №:'.implode(', ',$to_eq_ks)."\n";
			if(count($to_eq_bill_codes)>0) $rss54.='Входящие счета №:'.implode(', ',$to_eq_bill_codes)."\n";
		//	if(count($to_eq_accs)>0) $rss54.='Распоряжения на приемку №: '.implode(', ',$to_eq_accs)."\n";
			
			
		}
		
		
		return $cter;
	}
	
	
	
	
	
	
	
	public function BuildAnEq($id,&$rss,&$to_annul_ks, &$to_eq_ks, &$to_annul_bills, &$to_eq_bills, &$to_annul_accs, &$to_eq_accs,  &$to_annul_bill_codes, &$to_eq_bill_codes){
		$rss='';
		$cter=0;
		$to_annul_ks=array();
		$to_eq_ks=array();
		
		$to_annul_bills=array();
		$to_eq_bills=array();
		
		$to_annul_accs=array();
		$to_eq_accs=array();
		
		$to_annul_bill_codes=array();
		$to_eq_bill_codes=array();
		
		
		//сначала документы без завоза
		$sql='select id from komplekt_ved where sector_id="'.$id.'" and status_id in(1,11)';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$cter+=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$to_annul_ks[]=$f['id'];	
		}
		
		
		
		//затем с частичным завозом	
		//заявки в статусах утв, не вып
		$sql='select id from komplekt_ved where sector_id="'.$id.'" and status_id in(2,12)';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$sql1='select distinct a.* from acceptance as a 
			inner join acceptance_position as ap on a.id=ap.acceptance_id
			 where ap.komplekt_ved_id="'.$f['id'].'" and a.is_confirmed=1  order by a.given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$to_eq_ks[]=$f['id'];
			}else $to_annul_ks[]=$f['id'];
			
			$cter++;
		}
		
		
		//счета
		$sql='select id, code from bill where sector_id="'.$id.'" and status_id in(1)';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$cter+=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$to_annul_bills[]=$f['id'];	
			$to_annul_bill_codes[]=$f['code'];
		}
		
		
		//затем с частичным завозом	
		$sql='select id, code from bill where sector_id="'.$id.'" and status_id in(2,9)';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$sql1='select distinct a.* from acceptance as a 
			inner join acceptance_position as ap on a.id=ap.acceptance_id
			 where a.bill_id="'.$f['id'].'" and a.is_confirmed=1  order by a.given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$to_eq_bills[]=$f['id'];
				$to_eq_bill_codes[]=$f['code'];
			}else{
				 $to_annul_bills[]=$f['id'];
				  $to_annul_bill_codes[]=$f['code'];
			}
			
			$cter++;
		}
		
		
		
		//распор на пр
		/*$sql='select id from sh_i where sector_id="'.$id.'" and status_id in(1)';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$cter+=$rc;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$to_annul_accs[]=$f['id'];	
		}
		
		
		//затем с частичным завозом	
		$sql='select id from sh_i where sector_id="'.$id.'" and status_id in(2,7)';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$sql1='select distinct a.* from acceptance as a 
			inner join acceptance_position as ap on a.id=ap.acceptance_id
			 where a.sh_i_id="'.$f['id'].'" and a.is_confirmed=1  order by a.given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$to_eq_accs[]=$f['id'];
			}else $to_annul_accs[]=$f['id'];
			
			$cter++;
		}*/
		
		return $cter;	
	}
	
	
	
	//выравниване, анн-е связ док-тов
	
	public function DoAnEq($id, $item=NULL, $result=NULL){
		$au=new AuthUser;
		$log=new ActionLog;
		if($item===NULL) $item=$this->GetItemById($id);
		if($result===NULL) $result=$au->Auth();
		
		$cter=$this->BuildAnEq($id,$rss,$to_annul_ks, $to_eq_ks, $to_annul_bills, $to_eq_bills, $to_annul_accs, $to_eq_accs, $to_annul_bill_codes, $to_eq_bill_codes);
		$_stat=new DocStatusItem;
		//распор на пр.
		
		/*echo 'аннул. распор на пр.: ';
		print_r($to_annul_accs);*/
	/*	$_ni=new ShINotesItem;
		$_itm=new ShIItem;
		//аннулирование
		
		foreach($to_annul_accs as $k=>$v){
			$_itm->Edit($v, array('is_confirmed'=>0, 'status_id'=>3));
				
			$stat=$_stat->GetItemById(3);
			
		
			
			$log->PutEntry(0,'автоматическое аннулирование распоряжения на приемку',NULL,226,NULL,'№ документа: '.$v.' установлен статус '.$stat['name'].', причина: снятие активности склада '.SecStr($item['name']),$v);
			
			$_ni->Add(array(
			'user_id'=>$v,
			'is_auto'=>1,
			'pdate'=>time(),
			'posted_user_id'=>0,
			'note'=>'Автоматическое примечание: распоряжение на приемку было автоматически аннулировано, причина: снятие активности склада '.SecStr($item['name']).'.'
			));	
			
			
		}*/
		
		
		//счет
		/*echo 'аннул. счета: ';
		print_r($to_annul_bills);*/
		$_itm=new BillItem;
		
		$_ni=new BillNotesItem;
		foreach($to_annul_bills as $k=>$v){
			$_itm->Edit($v, array('is_confirmed_price'=>0, 'is_confirmed_shipping'=>0, 'status_id'=>3));
			$_itm->FreeBindedPayments($v);	
				$stat=$_stat->GetItemById(3);
				
				
				$log->PutEntry(0,'автоматическое аннулирование входящего счета',NULL,94,NULL,'№ документа: '.$to_annul_bill_codes[$k].' установлен статус '.$stat['name'].', причина: снятие активности склада '.SecStr($item['name']),$v);
				
				$_ni->Add(array(
				'user_id'=>$v,
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: входящий счет был автоматически аннулирован, причина: снятие активности склада '.SecStr($item['name']).'.'
				));	
		}
		
		//заявки
		
		/*echo 'аннул заяв.: ';
		print_r($to_annul_ks);*/
		 $_itm=new KomplItem; $_cg=new KomplConfGroup;
		
		$_ni=new KomplNotesItem;
		foreach($to_annul_ks as $k=>$v){
			$_itm->Edit($v, array('status_id'=>3));
				$_itm->ClearConfirms($v);
				
				$stat=$_stat->GetItemById(3);
				
				
				$log->PutEntry(0,'автоматическое аннулирование заявки',NULL,83,NULL,'№ документа: '.$v.' установлен статус '.$stat['name'].', причина: снятие активности склада '.SecStr($item['name']),$v);
				
				$_ni->Add(array(
				'user_id'=>$v,
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: заявка была автоматически аннулирована, причина: снятие активности склада '.SecStr($item['name']).'.'
				));
					
		}
		
		
		
		
		
		//автовыравнивание...
		//распор на пр.
	/*	$_ni=new ShINotesItem;
		$_itm=new ShIItem;
		
		 
		foreach($to_eq_accs as $k=>$v){
		//найти позиции распоряжения, сформировать массив args, вызвать выравнивание...
				$posset=new mysqlset('select * from sh_i_position where sh_i_id='.$v.'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				$args=array();
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					
					//$args=array();
					$args[]=$h['position_id'].';'.$h['quantity'].';'.$h['komplekt_ved_id'];	
					
					
				}
				
				//$zz=$_itm->ScanEq($f['id'],$args,$some_o,$f);
				
				$_itm->DoEq($v,$args,$some_output,0,NULL,$result,', причина: снятие активности склада '.SecStr($item['name']));
		
		}*/
		
		//счет
		$_itm=new BillItem;
		$_ni=new BillNotesItem;
		
		/*echo 'выр. счета: ';
		print_r($to_eq_bills);*/
		foreach($to_eq_bills as $k=>$v){
			$posset=new mysqlset('select * from bill_position where bill_id='.$v.'');
			$rs2=$posset->GetResult();
			$rc2=$posset->GetResultNumRows();
			
			$args=array();
			$was_eqed=false;
			for($j=0; $j<$rc2; $j++){
				$h=mysqli_fetch_array($rs2);
			
				$args[]=$h['position_id'].';'.$h['quantity'].';'.$h['storage_id'].';'.$h['sector_id'].';'.$h['komplekt_ved_id'];
					//	echo 'Выравниваю позицию '.$h['name'].' <br>';
			}
			$_itm->DoEq($v,$args,$some_output, 1, NULL, $result, false,', причина: снятие активности склада '.SecStr($item['name']));
			
		}
		
		
		//заявка
		//найти позиции заявки
		 $_itm=new KomplItem; $_cg=new KomplConfGroup;
		
		$_ni=new KomplNotesItem;
		/*echo 'выр. заявки: ';
		print_r($to_eq_ks);*/
		foreach($to_eq_ks as $k=>$v){		
				$posset=new mysqlset('select kv.*, p.name from komplekt_ved_pos as kv inner join catalog_position as p on kv.position_id=p.id where kv.komplekt_ved_id='.$v.'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				
				$args=array();
		
				$was_eqed=false;
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					
					$args[]=$h['position_id'].';'.$h['quantity_confirmed'].';'.$h['storage_id'].';'.$item['id'].';'.$v;
					
					
				}
				
				$_itm->DoEq($v,$args,$some_output, 1, NULL, $result, false,', причина: снятие активности склада '.SecStr($item['name']));
					
		}
		//die();
	}
	
}
?>