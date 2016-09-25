<?
require_once('abstractitem.php');
require_once('positem.php');
require_once('posdimitem.php');
require_once('authuser.php');
require_once('actionlog.php');
require_once('komplnotesitem.php');
require_once('billnotesitem.php');

require_once('acc_notesitem.php');
require_once('komplitem.php');

require_once('billpositem.php');

require_once('acc_positem.php');

require_once('billpospmitem.php');

require_once('acc_pospmitem.php');


//элемент каталога
class KomplPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_pos';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';	
	}
	
	
	//есть ли связанные документы по данной позиции данной заявки
	public function CheckBindedDocuments($komplekt_ved_id, $position_id, &$docs){
		$res=0;
		$sql='select count(*) from bill_position where komplekt_ved_id="'.$komplekt_ved_id.'" and position_id="'.$position_id.'"';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0){
			$res+=(int)$f[0];
		}
		
		/*$sql='select count(*) from sh_i_position where komplekt_ved_id="'.$komplekt_ved_id.'" and position_id="'.$position_id.'"';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0){
			$res+=(int)$f[0];
		}*/
		
		$sql='select count(*) from acceptance_position where komplekt_ved_id="'.$komplekt_ved_id.'" and position_id="'.$position_id.'"';
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$f=mysqli_fetch_array($rs);
		
		if((int)$f[0]>0){
			$res+=(int)$f[0];
		}
		
		
		return $res;
		
	}
	
	
	//переименование позиции
	public function ChangePosition($position_id, $komplekt_ved_id, $kol, $new_position_id, $storage_id){
		$_au=new AuthUser;
		$result=$_au->Auth();
		$log=new ActionLog;
		
		$_ki=new KomplItem;
		
		
		$ki=$_ki->getitembyid($komplekt_ved_id);
		
		$old_row=$this->GetItemByFields(array('position_id'=>$position_id, 'komplekt_ved_id'=>$komplekt_ved_id));
		
		$new_row=$this->GetItemByFields(array('position_id'=>$new_position_id, 'komplekt_ved_id'=>$komplekt_ved_id));
		
		
		
		
		//если есть такой же ряд - то приплюсуем ему значения...
		
		//старый ряд удалить отовсюду!
		$kolvo=0;
		if($new_row!==false){
			$kolvo=	(float)$new_row['quantity_confirmed']+(float)$old_row['quantity_confirmed'];
			$this->Edit($new_row['id'], array('quantity_confirmed'=>($kolvo), 'quantity_initial'=>(/*(float)$new_row['quantity_initial']+*/(float)$old_row['quantity_confirmed'])));
			
		}else{
			$kolvo=$kol;
			$this->Add(array(
				'komplekt_ved_id'=>$komplekt_ved_id,
				'position_id'=>$new_position_id,
				'storage_id'=>$storage_id,
				'quantity_confirmed'=>$kol,
				'quantity_initial'=>(float)$old_row['quantity_initial']//$kol
			));	
			
			
			
		}
		$this->Del($old_row['id']);
		
		
		$_pi=new PosItem; $_pdi=new PosDimItem;
		$oldpos=$_pi->getitembyid($position_id);
		$newpos=$_pi->getitembyid($new_position_id);
		$newpdi=$_pdi->GetItemById($newpos['dimension_id']);
		
		$log->PutEntry($result['id'],'изменил наименование позиции заявки',NULL,347,NULL,'исходная позиция '.SecStr($oldpos['name']).' количество '.$old_row['quantity_confirmed'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$komplekt_ved_id);
		
		$_ni=new KomplNotesItem;
		
		$_ni->Add(array('user_id'=>$komplekt_ved_id, 'note'=>'Автоматическое примечание: исходная позиция '.SecStr($oldpos['name']).' количество '.$old_row['quantity_confirmed'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo, 'posted_user_id'=>$result['id'], 'is_auto'=>1, 'pdate'=>time()));
		
		$log->PutEntry($result['id'],'добавил примечания по заявке', NULL,179, NULL,'Автоматическое примечание: исходная позиция '.SecStr($oldpos['name']).' количество '.$old_row['quantity_confirmed'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$komplekt_ved_id);
		
	//	
		//echo 'yyyyyyyyyyyyyy';
		
		
		//обработать все связанные документы
		
		
		
		//найдем список связ счетов из таблицы позиций счета...
		$sql='select distinct bill_id from bill_position where komplekt_ved_id="'.$komplekt_ved_id.'" and position_id="'.$position_id.'"';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->Getresultnumrows();
		
		for($i=0; $i<$rc; $i++){
			 //для каждого счета обработаем позиции, внесем примечания
			 $f=mysqli_fetch_array($rs);
			 //$f[0]
			 
			 
			 $sql2='select * from bill_position where bill_id="'.$f[0].'" and position_id="'.$position_id.'" and  komplekt_ved_id="'.$komplekt_ved_id.'"';
			 $set2=new mysqlset($sql2);
			 $rs2=$set2->GetResult();
			 $rc2=$set2->Getresultnumrows();
			
			 for($i2=0; $i2<$rc2; $i2++){
				 $f2=mysqli_fetch_array($rs2); 
			 	
				 
				$_ppi=new BillPosItem;
				
				$oldppi=$_ppi->GetItemById($f2['id']);
				 $newppi=$_ppi->GetItemByFields(array( 'bill_id'=>$f[0],'position_id'=>$new_position_id, 'storage_id'=>$f2['storage_id'], 'komplekt_ved_id'=>$komplekt_ved_id));
				 
				 $kolvo=0;
				 
				
				  //если есть ПМ - перекинуть его
				 $_ppim=new BillPosPMItem;
				 $oldppim=$_ppim->getitembyfields(array('bill_position_id'=>$oldppi['id']));
				 $newppim=$_ppim->getitembyfields(array('bill_position_id'=>$newppi['id']));
				 if($newppi!==false){
					 
					  
					  $kolvo=	(float)$newppi['quantity']+(float)$oldppi['quantity'];
					  if($newppim!==false){
						 $pms=array(
							  'plus_or_minus'=>$newppim['plus_or_minus'],
							  'rub_or_percent'=>$newppim['rub_or_percent'],
							  'value'=>$newppim['value'],
							  //'discount_plus_or_minus'=>$newppim['discount_plus_or_minus'],
							  'discount_rub_or_percent'=>$newppim['discount_rub_or_percent'],
							  'discount_value'=>$newppim['discount_value']
						  );	
						  
						  
					  }else{
						 $pms=NULL; 
					  }
					  
					 
					  
					  $_ppi->Edit($newppi['id'], array('quantity'=>$kolvo), $pms);
					  
				  }else{
					  $kolvo=$kol;
					  $cde=$_ppi->Add(array(
						  'bill_id'=>$f[0],
						  'position_id'=>$new_position_id,
						  
						  'quantity'=>$oldppi['quantity'],
						  'name'=>SecStr($newpos['name']),
						  'dimension'=>$newpdi['name'],
						  'price'=>$oldppi['price'],
						  'price_pm'=>$oldppi['price_pm'],
						  'total'=>$oldppi['total'],
						  'komplekt_ved_pos_id'=>(int)$new_row['id'],
						  'komplekt_ved_id'=>$komplekt_ved_id,
						  'storage_id'=>$oldppi['storage_id'],
						  'sector_id'=>$oldppi['sector_id'],
						  'out_bill_id'=>$oldppi['out_bill_id']
					  ));	
					
					  
					
					 if($oldppim!==false){
						$_ppim->Edit($oldppim['id'], array('bill_position_id'=>$cde)); 
					 }
					 
				  }
				  $_ppi->Del($oldppi['id']);
					
				  $log->PutEntry($result['id'],'изменил наименование позиции счета при изменении наименования позиции заявки',NULL,347,NULL,'исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$komplekt_ved_id);
				  
				  $log->PutEntry($result['id'],'изменил наименование позиции счета при изменении наименования позиции заявки',NULL,189,NULL,'исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$f[0]);
				  
			
				  $_ni=new BillNotesItem;
				  
				  $_ni->Add(array('user_id'=>$f[0], 'note'=>'Автоматическое примечание: исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo, 'posted_user_id'=>$result['id'], 'is_auto'=>1, 'pdate'=>time()));
				  
				  $log->PutEntry($result['id'],'добавил примечания по счету', NULL,191, NULL,'Автоматическое примечание: исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$f[0]);
		
			 }
		}
		
		
		
		
		//найдем список связ счетов из таблицы позиций поступлений...
		$sql='select distinct acceptance_id from acceptance_position where komplekt_ved_id="'.$komplekt_ved_id.'" and position_id="'.$position_id.'"';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->Getresultnumrows();
		
		for($i=0; $i<$rc; $i++){
			 //для каждого счета обработаем позиции, внесем примечания
			 $f=mysqli_fetch_array($rs);
			 //$f[0]
			 
			 
			 
			 $_ppi=new AccPosItem;// BillPosItem;
			 $oldppi=$_ppi->GetItemByFields(array( 'acceptance_id'=>$f[0],'position_id'=>$position_id,'komplekt_ved_id'=>$komplekt_ved_id));
			 
			 $newppi=$_ppi->GetItemByFields(array( 'acceptance_id'=>$f[0],'position_id'=>$new_position_id,'komplekt_ved_id'=>$komplekt_ved_id));
			
			 
			 $kolvo=0;
			 
			  //если есть ПМ - перекинуть его
			 $_ppim=new AccPosPMItem; // BillPosPMItem;
			 $oldppim=$_ppim->getitembyfields(array('acceptance_position_id'=>$oldppi['id']));
			 $newppim=$_ppim->getitembyfields(array('acceptance_position_id'=>$newppi['id']));
			 if($newppi!==false){
				 
				  
				  $kolvo=	(float)$newppi['quantity']+(float)$oldppi['quantity'];
				  if($newppim!==false){
					 $pms=array(
						  'plus_or_minus'=>$newppim['plus_or_minus'],
						  'rub_or_percent'=>$newppim['rub_or_percent'],
						  'value'=>$newppim['value']
					  );	
					  
					  
				  }else{
					 $pms=NULL; 
				  }
				  
				 
				  
				  $_ppi->Edit($newppi['id'], array('quantity'=>$kolvo), $pms);
				  
			  }else{
				  $kolvo=$kol;
				  $cde=$_ppi->Add(array(
					  'acceptance_id'=>$f[0],
					  'position_id'=>$new_position_id,
					  
					  'quantity'=>$oldppi['quantity'],
					  'name'=>SecStr($newpos['name']),
					  'dimension'=>$newpdi['name'],
					  'price'=>$oldppi['price'],
					   'price_pm'=>$oldppi['price_pm'],
					  'total'=>$oldppi['total'],
					  'komplekt_ved_pos_id'=>(int)$new_row['id'],
					  'komplekt_ved_id'=>$komplekt_ved_id,
					  'out_bill_id'=>$oldppi['out_bill_id']
					  /*'storage_id'=>$oldppi['storage_id'],
					  'sector_id'=>$oldppi['sector_id']*/
				  ));	
				 
				
				 if($oldppim!==false){
					$_ppim->Edit($oldppim['id'], array('acceptance_position_id'=>$cde)); 
				 }
				 
			  }
			  $_ppi->Del($oldppi['id']);
			 	
			  $log->PutEntry($result['id'],'изменил наименование позиции реализации при изменении наименования позиции заявки',NULL,347,NULL,'исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$komplekt_ved_id);
			  
			  
			  $log->PutEntry($result['id'],'изменил наименование позиции реализации при изменении наименования позиции заявки',NULL,235,NULL,'исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$f[0]);
			  
			 
			  $_ni=new AccNotesItem;
			  
			  $_ni->Add(array('user_id'=>$f[0], 'note'=>'Автоматическое примечание: исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo, 'posted_user_id'=>$result['id'], 'is_auto'=>1, 'pdate'=>time()));
			  
			  $log->PutEntry($result['id'],'добавил примечания по реализации', NULL,236, NULL,'Автоматическое примечание: исходная позиция '.SecStr($oldpos['name']).' количество '.$oldppi['quantity'].' сменена на '.SecStr($newpos['name']).' количество '.$kolvo,$f[0]);
		
		}
		
		
	}
}
?>