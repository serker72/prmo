<?
require_once('abstractitem.php');
require_once('invpositem.php');
//require_once('billpospmformer.php');
require_once('invposgroup.php');
require_once('docstatusitem.php');

require_once('sh_i_group.php');
require_once('acc_group.php');


require_once('acc_item.php');



require_once('actionlog.php');
require_once('authuser.php');


require_once('billitem.php');
require_once('sh_i_item.php');
require_once('acc_item.php');
require_once('billpospmformer.php');


require_once('maxformer.php');
require_once('authuser.php');
require_once('billcreator.php');
require_once('bill_in_creator.php');
require_once('bdetailsitem.php');
require_once('actionlog.php');
require_once('billgroup.php');
require_once('invitem.php');

require_once('billnotesitem.php');
require_once('sh_i_notesitem.php');
require_once('acc_notesitem.php');
require_once('isnotesitem.php');


require_once('bill_in_item.php');
require_once('sh_i_in_item.php');
require_once('acc_in_item.php');
require_once('bill_in_group.php');
require_once('sh_i_in_group.php');
require_once('acc_in_group.php');


class InvSync{
	
	
	//функция внесения изменений
	public function PutChanges($id, $result1=NULL){
		$_au1=new AuthUser;
		if($result1===NULL) $result1=$_au1->Auth();
		$log=new ActionLog;
		
		$_ii=new InvItem;
		$item=$_ii->GetItemById($id);
		
		$_bill=new BillItem;
		$_sh_i=new ShIItem;
		$_acc=new AccItem;
		
		$_bill_in=new BillInItem;
		$_sh_i_in=new ShIInItem;
		$_acc_in=new AccInItem;
		
		
		
		$_wf=new WfItem;
		
		if($item!==false){
			$positions=$_ii->GetPositionsArr($id);
			
			$scan_bill=false; $scan_wf=false;
			foreach($positions as $k=>$v){
			
				if(($v['quantity_as_is']-$v['quantity_fact'])>0) $scan_wf=$scan_wf||true;
				
				if(($v['quantity_fact']-$v['quantity_as_is'])>0) $scan_bill=$scan_bill||true;
			}
			
			//проверить недостачи.
			//сформировать их список и сохранить
			//позиции излищков. 
			//сформировать их список
			$positions=$_ii->GetPositionsArr($id);
			
			
			
			//а - есть ли связ документы
			
			// !!!!!!! приход
			$set1=new mysqlset('select * from bill where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_in_id=0;
			  if((int)$rc1==0){
				 if($scan_bill){
					
					//echo 'zzzzzzzzz'; die();
					
					//создаем счет
					  $inner_params=array();
					 
					  $inner_params['inventory_id']=$id;
					  $inner_params['is_incoming']=1;
					  
					  $inner_params['supplier_id']=$result1['org_id'];
					  $inner_params['org_id']=$result1['org_id'];
					  $lc=new BillInCreator;
					  $lc->ses->ClearOldSessions();
					  $inner_params['code']=$lc->GenLogin($result1['id']);
					  
					 
					  //найти реквизиты
					  $_bdi=new BDetailsItem;
					  $bdi=$_bdi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$result1['org_id']));
					  $inner_params['bdetails_id']=$bdi['id'];
					  
					 
					
					  $inner_params['manager_id']=$result1['id'];
					  $inner_params['pdate']=$item['inventory_pdate'];
					  $inner_params['status_id']=2;
					  $inner_params['is_confirmed_price']=1;
					  $inner_params['is_confirmed_shipping']=1;
					  
					  $inner_params['user_confirm_shipping_id']=$result1['id'];
					  $inner_params['user_confirm_price_id']=$result1['id'];
					  $inner_params['confirm_price_pdate']=$item['inventory_pdate'];
					  $inner_params['confirm_shipping_pdate']=$item['inventory_pdate'];
					  $bill_in_id=$_bill_in->Add($inner_params);
						
					  $log->PutEntry($result1['id'],'создал входящий счет по утверждению коррекции складских остатков инвентаризационного акта',NULL,608,NULL,NULL,$bill_in_id);
					 
					   
					  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: счет был создан и утвержден на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					 
				 }
			  }else{
				  $bill_in_id=$f1['id'];
				 //если анн-н - востт-ть
				  if($f1['status_id']==3){
					  $_bill_in->Edit($bill_in_id, array('status_id'=>1)); 
						 $log->PutEntry($result1['id'],'восстановил входящий счет по утверждению коррекции складских остатков инвентаризационного акта',NULL,627,NULL,NULL,$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: восcтановление счета на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				 
				 
				 //должны утвредить, если есть
				  if($f1['is_confirmed_price']==0){
						$_bill_in->Edit($bill_in_id, array('is_confirmed_price'=>1, 'confirm_price_pdate'=>time(),'user_confirm_price_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил цены входящего счета по утверждению коррекции складских остатков инвентаризационного акта',NULL,608,NULL,NULL,$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение цен на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
				  if($f1['is_confirmed_shipping']==0){
						$_bill_in->Edit($bill_in_id, array('is_confirmed_shipping'=>1, 'confirm_shipping_pdate'=>time(),'user_confirm_shipping_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил приемку входящего счета по утверждению коррекции складских остатков инвентаризационного акта',NULL,608,NULL,NULL,$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение приемки на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
			  }
			  
			  
			  
			  $set1=new mysqlset('select * from sh_i where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $sh_i_in_id=0;
			  if((int)$rc1==0){
				  if($scan_bill){
					  //$res=$res&&false;
				  		//$_reasons[]='нет связанного распоряжения на приемку';
						
					   $inner_params=array();
						$inner_params['bill_id']=$bill_in_id;
						$inner_params['is_incoming']=1;
						$inner_params['inventory_id']=$item['id'];
						$inner_params['user_confirm_id']=$result1['id'];
						$inner_params['confirm_pdate']=$item['inventory_pdate'];
						$inner_params['pdate']=$item['inventory_pdate'];
						$inner_params['is_confirmed']=1;
						
						$inner_params['org_id']=$result1['org_id'];
						$inner_params['manager_id']=$result1['id'];
						$inner_params['status_id']=2;
						
						
						$sh_i_in_id=$_sh_i_in->Add($inner_params);
						$log->PutEntry($result1['id'],'создал распоряжение на приемку по входящему счету по утверждению  коррекции складских остатков инвентаризационного акта',NULL,613,NULL,NULL,$bill_in_id);
						
						$log->PutEntry($result1['id'],'создал распоряжение на приемку по входящему счету по утверждению  коррекции складских остатков инвентаризационного акта',NULL,640,NULL,NULL,$sh_i_in_id);
						  
						
						 $log->PutEntry($result1['id'],'создал распоряжение на приемку по входящему счету по утверждению  коррекции складских остатков инвентаризационного акта',NULL,326,NULL,NULL,$id);	
						 $sni=new ShINotesItem;
						 $sni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: распоряжение был создано и утверждено на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						  
						
				  }
					  
			  }else {
				  $sh_i_in_id=$f1['id'];
			  	  
				   //если анн-н - востт-ть
				  if($f1['status_id']==3){
					  $_sh_i_in->Edit($sh_i_in_id, array('status_id'=>7)); 
						 $log->PutEntry($result1['id'],'восстановил распоряжение на приемку по утверждению коррекции складских остатков инвентаризационного акта',NULL,326,NULL,NULL,$sh_i_in_id);
						    $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: восcтановление распоряжения на отгрузку на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  //должны утвредить, если есть
				  if($f1['is_confirmed']==0){
						$_sh_i_in->Edit($sh_i_in_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил распоряжение на приемку по утверждению коррекции складских остатков инвентаризационного акта',NULL,326,NULL,NULL,$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						
					
						 
				  }
				  	
			  }
			  
			  
			  $set1=new mysqlset('select * from acceptance where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $acc_in_id=0;
			  if((int)$rc1==0){
				 if($scan_bill){
					//$res=$res&&false;
					//$_reasons[]='нет связанного поступления';	
					
					
					 $inner_params=array();
					  $inner_params['bill_id']=$bill_in_id;
					  $inner_params['sh_i_id']=$sh_i_in_id;
					  $inner_params['is_incoming']=1;
					   $inner_params['inventory_id']=$item['id'];
					  $inner_params['user_confirm_id']=$result1['id'];
					  $inner_params['confirm_pdate']=$item['inventory_pdate'];
					  $inner_params['pdate']=$item['inventory_pdate'];
					   $inner_params['given_pdate']=$item['inventory_pdate'];
					  $inner_params['is_confirmed']=1;
					  
					  $inner_params['org_id']=$result1['org_id'];
					  $inner_params['manager_id']=$result1['id'];
					  $inner_params['status_id']=5;
					 
					  $acc_in_id=$_acc_in->Add($inner_params);
					  
					  $log->PutEntry($result1['id'],'создал поступление товара по инвентаризационному акту по утверждению коррекции складских остатков',NULL,333,NULL,NULL,$item['id']);
					 
					
					 
					 $log->PutEntry($result1['id'],'создал поступление товара по инвентаризационному акту по утверждению коррекции складских остатков',NULL,661,NULL,NULL,$acc_in_id);
					 $ani=new AccNotesItem;
					  $ani->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: поступление было создано и утверждено на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					
					
				 }
			  }else{
				  
				  $acc_in_id=$f1['id'];
				  
				  
				     //если анн-н - востт-ть
				  if($f1['status_id']==6){
					  $_acc_in->Edit($acc_in_id, array('status_id'=>4)); 
						 $log->PutEntry($result1['id'],'восстановил поступление по утверждению коррекции складских остатков инвентаризационного акта',NULL,661,NULL,NULL,$acc_in_id);
						    $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: восстановление поступления на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  
				  if($f1['is_confirmed']==0){
						
						$_acc_in->Edit($acc_in_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true,$result1); 
						 
						 $log->PutEntry($result1['id'],'утвердил поступление по утверждению коррекции скласких остатков инвентаризационного акта',NULL,661,NULL,NULL,$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
			  }
			  
			  
			  
			  // !!!!!!!!!!!!!!! списание
			  $set1=new mysqlset('select * from bill where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_id=0;
			  if((int)$rc1==0){
				 if($scan_wf){
					
					//echo 'zzzzzzzzz'; die();
					
					//создаем счет
					  $inner_params=array();
					 
					  $inner_params['inventory_id']=$id;
					  $inner_params['is_incoming']=0;
					  
					  $inner_params['supplier_id']=$result1['org_id'];
					  $inner_params['org_id']=$result1['org_id'];
					  $lc=new BillCreator;
					  $lc->ses->ClearOldSessions();
					  $inner_params['code']=$lc->GenLogin($result1['id']);
					  
					 
					  //найти реквизиты
					  $_bdi=new BDetailsItem;
					  $bdi=$_bdi->GetItemByFields(array('is_basic'=>1, 'user_id'=>$result1['org_id']));
					  $inner_params['bdetails_id']=$bdi['id'];
					  
					  
					
					  $inner_params['manager_id']=$result1['id'];
					  $inner_params['pdate']=$item['inventory_pdate'];
					  $inner_params['status_id']=2;
					  $inner_params['is_confirmed_price']=1;
					  $inner_params['is_confirmed_shipping']=1;
					  
					  $inner_params['user_confirm_shipping_id']=$result1['id'];
					  $inner_params['user_confirm_price_id']=$result1['id'];
					  $inner_params['confirm_price_pdate']=$item['inventory_pdate'];
					  $inner_params['confirm_shipping_pdate']=$item['inventory_pdate'];
					  $bill_id=$_bill->Add($inner_params);
						
					  $log->PutEntry($result1['id'],'создал исходящий счет по утверждению коррекции складских остатков инвентаризационного акта',NULL,92,NULL,NULL,$bill_id);
					 
					   
					  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: счет был создан и утвержден на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					 
				 }
			  }else{
				  $bill_id=$f1['id'];
				 //если анн-н - востт-ть
				  if($f1['status_id']==3){
					  $_bill->Edit($bill_id, array('status_id'=>1)); 
						 $log->PutEntry($result1['id'],'восстановил исходящий счет по утверждению коррекции складских остатков инвентаризационного акта',NULL,131,NULL,NULL,$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: восcтановление счета на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				 
				 
				 //должны утвредить, если есть
				  if($f1['is_confirmed_price']==0){
						$_bill->Edit($bill_id, array('is_confirmed_price'=>1, 'confirm_price_pdate'=>time(),'user_confirm_price_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил цены исходящего счета по утверждению коррекции складских остатков инвентаризационного акта',NULL,92,NULL,NULL,$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение цен на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
				  if($f1['is_confirmed_shipping']==0){
						$_bill->Edit($bill_id, array('is_confirmed_shipping'=>1, 'confirm_shipping_pdate'=>time(),'user_confirm_shipping_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил отгрузку исходящего счета по утверждению коррекции складских остатков инвентаризационного акта',NULL,92,NULL,NULL,$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение отгрузки на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
			  }
			  
			  
			  
			  
			  
			  
			  
			  $set1=new mysqlset('select * from sh_i where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $sh_i_id=0;
			  if((int)$rc1==0){
				  if($scan_wf){
					  //$res=$res&&false;
				  		//$_reasons[]='нет связанного распоряжения на приемку';
						
					   $inner_params=array();
						$inner_params['bill_id']=$bill_id;
						$inner_params['is_incoming']=0;
						$inner_params['inventory_id']=$item['id'];
						$inner_params['user_confirm_id']=$result1['id'];
						$inner_params['confirm_pdate']=$item['inventory_pdate'];
						$inner_params['pdate']=$item['inventory_pdate'];
						$inner_params['is_confirmed']=1;
						
						$inner_params['org_id']=$result1['org_id'];
						$inner_params['manager_id']=$result1['id'];
						$inner_params['status_id']=2;
						
						
						$sh_i_id=$_sh_i->Add($inner_params);
						$log->PutEntry($result1['id'],'создал распоряжение на отгрузку по исходящему счету по утверждению  коррекции складских остатков инвентаризационного акта',NULL,93,NULL,NULL,$bill_id);
						
						$log->PutEntry($result1['id'],'создал распоряжение на отгрузку по исходящему счету по утверждению  коррекции складских остатков инвентаризационного акта',NULL,215,NULL,NULL,$sh_i_id);
						  
						
						 $log->PutEntry($result1['id'],'создал распоряжение на отгрузку по исходящему счету по утверждению  коррекции складских остатков инвентаризационного акта',NULL,326,NULL,NULL,$id);	
						 $sni=new ShINotesItem;
						 $sni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: распоряжение был создано и утверждено на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						  
						
				  }
					  
			  }else {
				  $sh_i_id=$f1['id'];
			  	  
				   //если анн-н - востт-ть
				  if($f1['status_id']==3){
					  $_sh_i->Edit($sh_i_id, array('status_id'=>7)); 
						 $log->PutEntry($result1['id'],'восстановил распоряжение на отгрузку по утверждению коррекции складских остатков инвентаризационного акта',NULL,326,NULL,NULL,$sh_i_id);
						    $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: восcтановление распоряжения на отгрузку на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  //должны утвредить, если есть
				  if($f1['is_confirmed']==0){
						$_sh_i->Edit($sh_i_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил распоряжение на отгрузку по утверждению коррекции складских остатков инвентаризационного акта',NULL,326,NULL,NULL,$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						
					
						 
				  }
				  	
			  }
			  
			  
			  
			  
			  
			  $set1=new mysqlset('select * from acceptance where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $acc_id=0;
			  if((int)$rc1==0){
				 if($scan_wf){
					//$res=$res&&false;
					//$_reasons[]='нет связанного поступления';	
					
					
					 $inner_params=array();
					  $inner_params['bill_id']=$bill_id;
					  $inner_params['sh_i_id']=$sh_i_id;
					  $inner_params['is_incoming']=0;
					   $inner_params['inventory_id']=$item['id'];
					  $inner_params['user_confirm_id']=$result1['id'];
					  $inner_params['confirm_pdate']=$item['inventory_pdate'];
					  $inner_params['pdate']=$item['inventory_pdate'];
					   $inner_params['given_pdate']=$item['inventory_pdate'];
					  $inner_params['is_confirmed']=1;
					  
					  $inner_params['org_id']=$result1['org_id'];
					  $inner_params['manager_id']=$result1['id'];
					  $inner_params['status_id']=5;
					 
					  $acc_id=$_acc->Add($inner_params);
					  
					  $log->PutEntry($result1['id'],'создал реализацию товара по инвентаризационному акту по утверждению коррекции складских остатков',NULL,333,NULL,NULL,$item['id']);
					 
					
					 
					 $log->PutEntry($result1['id'],'создал реализацию товара по инвентаризационному акту по утверждению коррекции складских остатков',NULL,229,NULL,NULL,$acc_id);
					 $ani=new AccNotesItem;
					  $ani->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: реализация была создана и утверждена на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					
					
				 }
			  }else{
				  
				  $acc_id=$f1['id'];
				  
				  
				     //если анн-н - востт-ть
				  if($f1['status_id']==6){
					  $_acc->Edit($acc_id, array('status_id'=>4)); 
						 $log->PutEntry($result1['id'],'восстановил реализацию по утверждению коррекции складских остатков инвентаризационного акта',NULL,229,NULL,NULL,$acc_id);
						    $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: восстановление реализации на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  
				  if($f1['is_confirmed']==0){
						
						$_acc->Edit($acc_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true,$result1); 
						 
						 $log->PutEntry($result1['id'],'утвердил реализацию по утверждению коррекции скласких остатков инвентаризационного акта',NULL,229,NULL,NULL,$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
			  }
			  
			  
			  
			  
			  /*$set1=new mysqlset('select * from interstore where org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $wf_id=0;
			  if((int)$rc1==0){
				  if($scan_wf){
					//$res=$res&&false;
					//$_reasons[]='нет связанного распоряжения на списание';	
					
					$inner_params=array();
					  $inner_params['is_or_writeoff']=1; 
					  $inner_params['inventory_id']=$item['id'];
					 
					  $inner_params['user_confirm_id']=$result1['id'];
					  $inner_params['confirm_pdate']=$item['inventory_pdate'];
					  $inner_params['is_confirmed']=1;
					  
					  
					   $inner_params['user_confirm_fill_wf_id']=$result1['id'];
					  $inner_params['confirm_fill_wf_pdate']=$item['inventory_pdate'];
					  $inner_params['is_confirmed_fill_wf']=1;
					 
					 
					  $inner_params['pdate']=$item['inventory_pdate'];
					   
					  $inner_params['org_id']=$result1['org_id'];
					  $inner_params['manager_id']=$result1['id'];
					  $inner_params['status_id']=2;
					  
					
					  
					  $wf_id=$_wf->Add($inner_params);
					  
					   $log->PutEntry($result1['id'],'создал распоряжение на списание товара по инвентаризационному акту по утверждению коррекции складских остатков',NULL,333,NULL,NULL,$item['id']);
					 
					
					 
					 $log->PutEntry($result1['id'],'создал распоряжение на списание товара по инвентаризационному акту по утверждению коррекции складских остатков',NULL,105,NULL,NULL,$wf_id);
					 
					  
				  }
			  }else{
				  $wf_id=$f1['id'];
				  
				  
				  
				    //если анн-н - востт-ть
				  if($f1['status_id']==3){
					  $_wf->Edit($wf_id, array('status_id'=>1)); 
						 $log->PutEntry($result1['id'],'восстановил распоряжение на списание по утверждению коррекции складских остатков инвентаризационного акта',NULL,105,NULL,NULL,$wf_id);
						   $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>'Автоматическое примечание: восттановление распоряжения на списание на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  
				  //должны утвредить, если есть
				  if($f1['is_confirmed_fill_wf']==0){
						$_wf->Edit($wf_id, array('is_confirmed_fill_wf'=>1, 'confirm_fill_wf_pdate'=>time(),'user_confirm_fill_wf_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил заполнение распоряжения на списание по утверждению коррекции скласких остатков инвентаризационного акта',NULL,105,NULL,NULL,$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение заполнения распоряжения на списание на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
				  if($f1['is_confirmed']==0){
						$_wf->Edit($wf_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),' 	user_confirm_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'утвердил отгрузку входящего счета по утверждению коррекции скласких остатков инвентаризационного акта',NULL,105,NULL,NULL,$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: утверджение цен на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
			  }
			  
			  */
			  
			  
			  
			  
			  	//б все ли позиции из акта есть в связ докум
			$bill_positions=$_bill->GetPositionsArr($bill_id);
			//echo $bill_id; var_dump($bill_positions);
			 $sh_i_positions=$_sh_i->GetPositionsArr($sh_i_id);
			// var_dump($sh_i_positions);
			$acc_positions=$_acc->GetPositionsArr($acc_id); 
			
			
			$bill_in_positions=$_bill->GetPositionsArr($bill_in_id);
			$sh_i_in_positions=$_sh_i->GetPositionsArr($sh_i_in_id);
			$acc_in_positions=$_acc->GetPositionsArr($acc_in_id); 
			
			
			$wf_positions=$_wf->GetPositionsArr($wf_id);
			
			$_bpi=new BillPosItem;
			$_spi=new ShIPosItem;
			$_api=new AccPosItem;
			
			$_bpi_in=new BillInPosItem;
			$_spi_in=new ShIPosItem;
			$_api_in=new AccInPosItem;
			
			
			
			$_ipi=new IsPosItem;
			 
			if($scan_bill){
			  
			  // !!!!!!! приход
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$bill_in_positions);
				  
				  //$qua=($v['quantity_as_is']-$v['quantity_fact']);
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					  
					  
					  //$has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет во входящем счете';	
					  $_bpi_in->Add(array(
					  'bill_id'=>$bill_in_id,
					  'position_id'=>$v['position_id'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'name'=>SecStr($v['position_name']),
					  'dimension'=>SecStr($v['dim_name']),
					  
					  'quantity'=>$qua));
					  
					  //журнал? примечания?
					  
					   $log->PutEntry($result1['id'],'добавлена позиция во входящий счет по утверждению коррекции скласких остатков инвентаризационного акта',NULL,613,NULL,''.SecStr($v['position_name']).', '.$qua.'',$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
			  }
			  $res=$res&&$has;
			  
			 
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				 
				  $pos=$this->IsInPos($v['pl_position_id'],$sh_i_in_positions);
				  
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					  //$has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет в распоряжении на приемку';	
					   $_spi_in->Add(array(
					  'sh_i_id'=>$sh_i_in_id,
							  'pl_position_id'=>$v['pl_position_id'],
							  'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
					  //примечания? журнал?
					   $log->PutEntry($result1['id'],'добавлена позиция  в распоряжение на приемку по утверждению коррекции скласких остатков инвентаризационного акта',NULL,644,''.SecStr($v['position_name']).', '.$qua.'',NULL,$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
					  
				  }
			  }
			  $res=$res&&$has;
			  
			  
			
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$acc_in_positions);
				  
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					 // $has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет в поступлении';
					  
					   $_api_in->Add(array(
					  'acceptance_id'=>$acc_in_id,
							 'pl_position_id'=>$v['pl_position_id'],
							 'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
							  
					//примечания? журнал?
					   $log->PutEntry($result1['id'],'добавлена позиция  в поступление по утверждению коррекции скласких остатков инвентаризационного акта',NULL,664,NULL,''.SecStr($v['position_name']).', '.$qua.'',$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
				  }
			  }
			  $res=$res&&$has;
			}
			
			
			
			if($scan_wf){
			  
			  // !!!!!!!!!!! расход
			  
			   $has=true;
			   foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$bill_positions);
				  
				  $qua=$v['quantity_as_is']-$v['quantity_fact'];
				  if(($pos==-1)&&($qua>0)){
					  
					  
					  //$has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет во входящем счете';	
					  $_bpi->Add(array(
					  'bill_id'=>$bill_id,
					  'position_id'=>$v['position_id'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'name'=>SecStr($v['position_name']),
					  'dimension'=>SecStr($v['dim_name']),
					  
					  'quantity'=>$qua));
					  
					  //журнал? примечания?
					  
					   $log->PutEntry($result1['id'],'добавлена позиция во входящий счет по утверждению коррекции скласких остатков инвентаризационного акта',NULL,613,NULL,''.SecStr($v['position_name']).', '.$qua.'',$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
			  }
			  $res=$res&&$has;
			  
			 
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				 
				  $pos=$this->IsInPos($v['pl_position_id'],$sh_i_positions);
				  
				  $qua=$v['quantity_as_is']-$v['quantity_fact'];
				  if(($pos==-1)&&($qua>0)){
					  //$has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет в распоряжении на приемку';	
					   $_spi->Add(array(
					  'sh_i_id'=>$sh_i_id,
							  'pl_position_id'=>$v['pl_position_id'],
							  'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
					  //примечания? журнал?
					   $log->PutEntry($result1['id'],'добавлена позиция  в распоряжение на приемку по утверждению коррекции скласких остатков инвентаризационного акта',NULL,644,''.SecStr($v['position_name']).', '.$qua.'',NULL,$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
					  
				  }
			  }
			  $res=$res&&$has;
			  
			  
			
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$acc_positions);
				  
				  $qua=$v['quantity_as_is']-$v['quantity_fact'];
				  if(($pos==-1)&&($qua>0)){
					 // $has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет в поступлении';
					  
					   $_api->Add(array(
					  'acceptance_id'=>$acc_id,
							 'pl_position_id'=>$v['pl_position_id'],
							 'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
							  
					//примечания? журнал?
					   $log->PutEntry($result1['id'],'добавлена позиция  в поступление по утверждению коррекции скласких остатков инвентаризационного акта',NULL,664,NULL,''.SecStr($v['position_name']).', '.$qua.'',$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
				  }
			  }
			  $res=$res&&$has;
			  
			  
			 
			  /*$has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$wf_positions);
				 // $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  $qua=$v['quantity_as_is']-$v['quantity_fact'];
				  if(($pos==-1)&&($qua>0)){
					  //$has=$has&&false;
					  //$_reasons[]='позиции '.$v['position_name'].' нет в распоряжении на списание';	
					  
					 
					  
					  $_ipi->Add(array(
					  'interstore_id'=>$wf_id,
							 'pl_position_id'=>$v['pl_position_id'],
							 'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
					//примечания? журнал?	
					
					 $log->PutEntry($result1['id'],'добавлена позиция в распоряжение на списание по утверждению коррекции скласких остатков инвентаризационного акта',NULL,106,NULL,''.SecStr($v['position_name']).', '.$qua.'',$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: добавлена позиция '.SecStr($v['position_name']).', '.$qua.' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
				  }
				  
				  
			  }
			  $res=$res&&$has;*/
			  
			  
			  
			  
			}
			
			  
			 
			 
			 
			 //в все ли позиции связ докум есть в акте
			
			// !!!!!!!!!!	приход
			  $has=true;
			  foreach($bill_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				 
				   if($pos>=0){
					   $qua=$positions[$pos]['quantity_fact']-$positions[$pos]['quantity_as_is']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				
					  $bpi=$_bpi_in->GetItemByFields(array(
					  	'bill_id'=>$bill_in_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']
						));
					  if($bpi!==false){
						  $_bpi_in->Del($bpi['id']);
						    $log->PutEntry($result1['id'],'удалена позиция из входящего счета по утверждению коррекции скласких остатков инвентаризационного акта',NULL,613,NULL,''.SecStr($v['position_name']).', '.$bpi['quantity'].'',$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$bpi['quantity'].'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  }
					 
				  }
			  }
			  $res=$res&&$has;
			  
			  $has=true;
			  foreach($sh_i_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  
				   if($pos>=0){
					   $qua=$positions[$pos]['quantity_fact']-$positions[$pos]['quantity_as_is']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				  
				 
					  //$_reasons[]='позиции распоряжения на приемку '.$v['position_name'].' нет в акте';	
					  $spi=$_spi_in->GetItemByFields(array(
					  	'sh_i_id'=>$sh_i_in_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($spi!==false){
						   $_spi_in->Del($spi['id']);
						   
						   //примечания? журнал?
					   $log->PutEntry($result1['id'],'удалена позиция  из распоряжения на приемку по утверждению коррекции скласких остатков инвентаризационного акта',NULL,644,NULL,''.SecStr($v['position_name']).', '.$spi['quantity'].'',$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$spi['quantity'].'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						
					  }
				  }
			  }
			  $res=$res&&$has;
			  
		  
			  $has=true;
			  foreach($acc_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  
				  
				  if($pos>=0){
					   $qua=$positions[$pos]['quantity_fact']-$positions[$pos]['quantity_as_is']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				  
					//$_reasons[]='позиции поступления '.$v['position_name'].' нет в акте';	
					  
					  $api=$_api_in->GetItemByFields(array(
					  	'acceptance_id'=>$acc_in_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($api!==false){
						   $_api_in->Del($api['id']);
						   
						   //примечания? журнал?
					   $log->PutEntry($result1['id'],'удалена позиция  из поступления по утверждению коррекции скласких остатков инвентаризационного акта',NULL,664,NULL,''.SecStr($v['position_name']).', '.$api['quantity'].'',$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$api['quantity'].' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					  
				  }
			  }
			  $res=$res&&$has;
			
			
				
			// !!!!!!!!!!! расход 
			$has=true;
			  foreach($bill_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				 
				   if($pos>=0){
					   $qua=$positions[$pos]['quantity_as_is']-$positions[$pos]['quantity_fact']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				
					  //$_reasons[]='позиции входящего счета '.$v['position_name'].' нет в акте';	
					  $bpi=$_bpi->GetItemByFields(array(
					  	'bill_id'=>$bill_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']
						));
					  if($bpi!==false){
						  $_bpi->Del($bpi['id']);
						    $log->PutEntry($result1['id'],'удалена позиция из исходящего счета по утверждению коррекции скласких остатков инвентаризационного акта',NULL,93,NULL,''.SecStr($v['position_name']).', '.$bpi['quantity'].'',$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$bpi['quantity'].'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  }
					  
					  //
				  }
			  }
			  $res=$res&&$has;
			  
			  $has=true;
			  foreach($sh_i_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  
				   if($pos>=0){
					   $qua=$positions[$pos]['quantity_as_is']-$positions[$pos]['quantity_fact']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				  
				  //if($pos==-1){
					  //$has=$has&&false;
					  //$_reasons[]='позиции распоряжения на приемку '.$v['position_name'].' нет в акте';	
					  $spi=$_spi->GetItemByFields(array(
					  	'sh_i_id'=>$sh_i_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($spi!==false){
						   $_spi->Del($spi['id']);
						   
						   //примечания? журнал?
					   $log->PutEntry($result1['id'],'удалена позиция  из распоряжения на отгрузку по утверждению коррекции скласких остатков инвентаризационного акта',NULL,219,NULL,''.SecStr($v['position_name']).', '.$spi['quantity'].'',$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$spi['quantity'].'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						
					  }
				  }
			  }
			  $res=$res&&$has;
			  
		  
			  $has=true;
			  foreach($acc_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  
				  
				  if($pos>=0){
					   $qua=$positions[$pos]['quantity_as_is']-$positions[$pos]['quantity_fact']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				  
					//$_reasons[]='позиции поступления '.$v['position_name'].' нет в акте';	
					  
					  $api=$_api->GetItemByFields(array(
					  	'acceptance_id'=>$acc_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($api!==false){
						   $_api->Del($api['id']);
						   
						   //примечания? журнал?
					   $log->PutEntry($result1['id'],'удалена позиция  из реализации по утверждению коррекции скласких остатков инвентаризационного акта',NULL,235,NULL,''.SecStr($v['position_name']).', '.$api['quantity'].'',$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$api['quantity'].' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					  
				  }
			  }
			  $res=$res&&$has;
			/*	
			  $has=true;
			  foreach($wf_positions as $k=>$v){
				  
				  
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  
				  if($pos>=0){
					   $qua=$positions[$pos]['quantity_as_is']-$positions[$pos]['quantity_fact']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
					 
					 
					 // $has=$has&&false;
					  //$_reasons[]='позиции распоряжения на списание '.$v['position_name'].' нет в акте';	
					  $ipi=$_ipi->GetItemByFields(array(
					  	'interstore_id'=>$wf_id,
					  	'position_id'=>$v['id'],
						'pl_position_id'=>$v['pl_position_id'],));
					  if($ipi!==false) {
						  $_ipi->Del($ipi['id']);
						  
						   $log->PutEntry($result1['id'],'удалена позиция  из распоряжения на списание по утверждению коррекции скласких остатков инвентаризационного акта',NULL,106,NULL,''.SecStr($v['position_name']).', '.$ipi['quantity'].'',$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: удалена позиция '.SecStr($v['position_name']).', '.$ipi['quantity'].'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  
					  }
				  }
			  }
			  $res=$res&&$has;
			  */
			//}
			 
			 
			  //Г выравнивание количеств
			  
			// !!!!!!!!!! приход  
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$bill_in_positions);
				if($pos!=-1){
					//	
					//$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$qua=($v['quantity_fact']-$v['quantity_as_is']);
					$ethalon=$bill_in_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством во входящем счете '.$ethalon;	
						
						$bpi=$_bpi_in->GetItemByFields(array(
							'bill_id'=>$bill_in_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($bpi!==false){
							 $_bpi_in->Edit($bpi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'отредактировано кол-во позиции во входящем счете по утверждению коррекции скласких остатков инвентаризационного акта',NULL,613,NULL,''.SecStr($v['position_name']).', было '.$bill_in_positions[$pos]['quantity'].', стало '.$qua.' ',$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$bill_in_positions[$pos]['quantity'].', стало '.$qua.' во входящем счете  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						}
					}
				}
			}
			$res=$res&&$has;
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$sh_i_in_positions);
				if($pos!=-1){
					//	
					//$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$qua=($v['quantity_fact']-$v['quantity_as_is']);
					$ethalon=$sh_i_in_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в распоряжении на приемку '.$ethalon;	
						$spi=$_spi_in->GetItemByFields(array(
							'sh_i_id'=>$sh_i_in_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($spi!==false){
							 $_spi_in->Edit($spi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'отредактировано кол-во позиции  из распоряжения на приемку по утверждению коррекции скласких остатков инвентаризационного акта',NULL,644,NULL,''.SecStr($v['position_name']).', было '.$sh_i_positions[$pos]['quantity'].', стало '.$qua.'',$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$sh_i_in_positions[$pos]['quantity'].', стало '.$qua.'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						
					  
						}
					}
				}
			}
			$res=$res&&$has;
			
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$acc_in_positions);
				if($pos!=-1){
					//	
					//$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$qua=($v['quantity_fact']-$v['quantity_as_is']);
					$ethalon=$acc_in_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в поступлении '.$ethalon;	
						
						$api=$_api_in->GetItemByFields(array(
							'acceptance_id'=>$acc_in_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($api!==false) {
							$_api_in->Edit($api['id'],array('quantity'=>$qua));
							
							 $log->PutEntry($result1['id'],'отредактировано кол-во позиции  в поступлении по утверждению коррекции скласких остатков инвентаризационного акта',NULL,664,NULL, ''.SecStr($v['position_name']).', было '.$acc_positions[$pos]['quantity'].', стало '.$qua.'',$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$acc_positions[$pos]['quantity'].', стало '.$qua.' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					}
				}
			}
			$res=$res&&$has;
			
			
			// !!!!!!!! расход
			/*$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$wf_positions);
				if($pos!=-1){
					//	
					//$qua=($v['quantity_fact']-$v['quantity_as_is']);
					$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$ethalon=$wf_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в распоряжении на списание '.$ethalon;	
						$ipi=$_ipi->GetItemByFields(array(
						  'interstore_id'=>$wf_id,
						  'position_id'=>$v['position_id'],
						  'pl_position_id'=>$v['pl_position_id']));
						if($ipi!==false) {
							$_ipi->Edit($ipi['id'],array('quantity'=>$qua));
							
							 $log->PutEntry($result1['id'],'отредактировано кол-во позиции  в распоряжении на списание по утверждению коррекции скласких остатков инвентаризационного акта',NULL,106,NULL,''.SecStr($v['position_name']).', было '.$wf_positions[$pos]['quantity'].',  стало '.$qua.'',$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$wf_positions[$pos]['quantity'].',  стало '.$qua.' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					  
							
							
						
					}
				}
			}
			$res=$res&&$has;*/
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$bill_positions);
				if($pos!=-1){
					//	
					//$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$ethalon=$wf_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством во входящем счете '.$ethalon;	
						
						$bpi=$_bpi->GetItemByFields(array(
							'bill_id'=>$bill_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($bpi!==false){
							 $_bpi->Edit($bpi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'отредактировано кол-во позиции в исходящем счете по утверждению коррекции скласких остатков инвентаризационного акта',NULL,93,NULL,''.SecStr($v['position_name']).', было '.$bill_positions[$pos]['quantity'].', стало '.$qua.' ',$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$bill_positions[$pos]['quantity'].', стало '.$qua.' во входящем счете  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						}
					}
				}
			}
			$res=$res&&$has;
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$sh_i_positions);
				if($pos!=-1){
					//	
					$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$ethalon=$wf_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в распоряжении на приемку '.$ethalon;	
						$spi=$_spi->GetItemByFields(array(
							'sh_i_id'=>$sh_i_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($spi!==false){
							 $_spi->Edit($spi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'отредактировано кол-во позиции  из распоряжения на отгрузку по утверждению коррекции скласких остатков инвентаризационного акта',NULL,219,NULL,''.SecStr($v['position_name']).', было '.$sh_i_positions[$pos]['quantity'].', стало '.$qua.'',$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$sh_i_positions[$pos]['quantity'].', стало '.$qua.'  на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						
					  
						}
					}
				}
			}
			$res=$res&&$has;
			
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$acc_positions);
				if($pos!=-1){
					//	
					$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$ethalon=$wf_positions[$pos]['quantity'];
					if(($qua>0)&&($ethalon!=$qua)){
						//$has=$has&&false;
						//$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в поступлении '.$ethalon;	
						
						$api=$_api->GetItemByFields(array(
							'acceptance_id'=>$acc_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($api!==false) {
							$_api->Edit($api['id'],array('quantity'=>$qua));
							
							 $log->PutEntry($result1['id'],'отредактировано кол-во позиции в реализации по утверждению коррекции скласких остатков инвентаризационного акта',NULL,235,NULL, ''.SecStr($v['position_name']).', было '.$acc_positions[$pos]['quantity'].', стало '.$qua.'',$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'Автоматическое примечание: отредактировано кол-во позиции '.SecStr($v['position_name']).', было '.$acc_positions[$pos]['quantity'].', стало '.$qua.' на основании коррекции остатков по акту инвентаризации № '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					}
				}
			}
			$res=$res&&$has;
			  
			
			if($sh_i_id!=0) $_sh_i->ScanDocStatus($sh_i_id,array(),array(),NULL,$result1);
			if($bill_id!=0) $_bill->ScanDocStatus($bill_id,array(),array(),NULL,$result1);
			
			if($sh_i_in_id!=0) $_sh_i_in->ScanDocStatus($sh_i_id,array(),array(),NULL,$result1);
			if($bill_in_id!=0) $_bill_in->ScanDocStatus($bill_id,array(),array(),NULL,$result1);
			
			//die();
		}
		
	}
	
	
	
	
	//функция проверки разницы между позициями акта и позициями всех подч док
	public function HasNotDifference($id, &$reasons){
		$res=true; 	//нет разницы
		
		$reasons=''; $_reasons=array();
		
		$_au1=new AuthUser;
		$result1=$_au1->Auth();
		$log=new ActionLog;
		
		$_ii=new InvItem;
		$ii=$_ii->GetItemById($id);
		
		$_bill=new BillItem;
		$_sh_i=new ShIItem;
		$_acc=new AccItem;
		
		$_bill_in=new BillInItem;
		$_sh_i_in=new ShIInItem;
		$_acc_in=new AccInItem;
		
		
		$_wf=new WfItem;
		
		if($item!==false){
			$positions=$_ii->GetPositionsArr($id);
			
			$scan_bill=false; $scan_wf=false;
			foreach($positions as $k=>$v){
			
				if(($v['quantity_as_is']-$v['quantity_fact'])>0) $scan_wf=$scan_wf||true;
				
				if(($v['quantity_fact']-$v['quantity_as_is'])>0) $scan_bill=$scan_bill||true;
			}
			
			
			
			
			
			
			//а - есть ли связ документы
			
			  $set1=new mysqlset('select * from bill where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_in_id=0;
			  if((int)$rc1==0){
				  if($scan_bill){
					  $res=$res&&false;
				  	  $_reasons[]='нет связанного входящего счета';	
				  }
			  }else{
				  $bill_in_id=$f1['id'];
				  //echo 'ZZZZZZZZZZZZZZZZZ'.$bill_id;	
			  }
			  
			  
			  $set1=new mysqlset('select * from sh_i where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $sh_i_in_id=0;
			  if((int)$rc1==0){
				  if($scan_bill){
					  $res=$res&&false;
				  		$_reasons[]='нет связанного распоряжения на приемку';
				  }
					  
			  }else $sh_i_in_id=$f1['id'];
			  
			  
			  $set1=new mysqlset('select * from acceptance where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $acc_in_id=0;
			  if((int)$rc1==0){
				 if($scan_bill){
					$res=$res&&false;
					$_reasons[]='нет связанного поступления';	
				 }
			  }else $acc_in_id=$f1['id'];
			
			
			
			$set1=new mysqlset('select * from bill where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_id=0;
			  if((int)$rc1==0){
				  if($scan_wf){
					  $res=$res&&false;
				  	  $_reasons[]='нет связанного исходящего счета';	
				  }
			  }else{
				  $bill_id=$f1['id'];
				  //echo 'ZZZZZZZZZZZZZZZZZ'.$bill_id;	
			  }
			  
			  
			  $set1=new mysqlset('select * from sh_i where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $sh_i_id=0;
			  if((int)$rc1==0){
				  if($scan_wf){
					  $res=$res&&false;
				  		$_reasons[]='нет связанного распоряжения на отгрузку';
				  }
					  
			  }else $sh_i_id=$f1['id'];
			  
			  
			  $set1=new mysqlset('select * from acceptance where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $acc_id=0;
			  if((int)$rc1==0){
				 if($scan_wf){
					$res=$res&&false;
					$_reasons[]='нет связанной реализации';	
				 }
			  }else $acc_id=$f1['id'];
			
			
			
			
		
			
			//б все ли позиции из акта есть в связ докум
			$bill_positions=$_bill->GetPositionsArr($bill_id);
			//echo $bill_id; var_dump($bill_positions);
			 $sh_i_positions=$_sh_i->GetPositionsArr($sh_i_id);
			// var_dump($sh_i_positions);
			$acc_positions=$_acc->GetPositionsArr($acc_id); 
			
			
			$bill_in_positions=$_bill_in->GetPositionsArr($bill_in_id);
			//echo $bill_id; var_dump($bill_positions);
			 $sh_i_in_positions=$_sh_i_in->GetPositionsArr($sh_i_in_id);
			// var_dump($sh_i_positions);
			$acc_in_positions=$_acc_in->GetPositionsArr($acc_in_id); 
			
			
			//$wf_positions=$_wf->GetPositionsArr($wf_id);
			 
			if($scan_bill){
			  
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$bill_in_positions);
				  
				  //$qua=($v['quantity_as_is']-$v['quantity_fact']);
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					  
					  
					  $has=$has&&false;
					  $_reasons[]='позиции '.$v['position_name'].' нет во входящем счете';	
				  }
			  }
			  $res=$res&&$has;
			  
			 
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				 
				  $pos=$this->IsInPos($v['pl_position_id'],$sh_i_in_positions);
				  
				  //$qua=($v['quantity_as_is']-$v['quantity_fact']);
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					  $has=$has&&false;
					  $_reasons[]='позиции '.$v['position_name'].' нет в распоряжении на приемку';	
				  }
			  }
			  $res=$res&&$has;
			  
			  
			
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$acc_in_positions);
				 // $qua=($v['quantity_as_is']-$v['quantity_fact']);
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					  $has=$has&&false;
					  $_reasons[]='позиции '.$v['position_name'].' нет в поступлении';	
				  }
			  }
			  $res=$res&&$has;
			}
			
			
			
			if($scan_wf){
			    $has=true;
				foreach($positions as $k=>$v){
					$pos=$this->IsInPos($v['pl_position_id'],$bill_positions);
					
					 $qua=($v['quantity_as_is']-$v['quantity_fact']);
					if(($pos==-1)&&($qua>0)){
						
						
						$has=$has&&false;
						$_reasons[]='позиции '.$v['position_name'].' нет в исходящем счете';	
					}
				}
				$res=$res&&$has;
				
			   
				
				$has=true;
				foreach($positions as $k=>$v){
				   
					$pos=$this->IsInPos($v['pl_position_id'],$sh_i_positions);
					
					 $qua=($v['quantity_as_is']-$v['quantity_fact']);
					if(($pos==-1)&&($qua>0)){
						$has=$has&&false;
						$_reasons[]='позиции '.$v['position_name'].' нет в распоряжении на отгрузку';	
					}
				}
				$res=$res&&$has;
				
				
			  
				
				$has=true;
				foreach($positions as $k=>$v){
					$pos=$this->IsInPos($v['pl_position_id'],$acc_positions);
				   	$qua=($v['quantity_as_is']-$v['quantity_fact']);
					if(($pos==-1)&&($qua>0)){
						$has=$has&&false;
						$_reasons[]='позиции '.$v['position_name'].' нет в реализации';	
					}
				}
				$res=$res&&$has;
			 
			 /* $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$wf_positions);
				  //$qua=($v['quantity_fact']-$v['quantity_as_is']);
				  $qua=($v['quantity_as_is']-$v['quantity_fact']);
				  if(($pos==-1)&&($qua>0)){
					  $has=$has&&false;
					  $_reasons[]='позиции '.$v['position_name'].' нет в распоряжении на списание';	
				  }
			  }
			  $res=$res&&$has;*/
			}
			
			//в все ли позиции связ докум есть в акте
			//if($scan_bill){
			  $has=true;
			  foreach($bill_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции входящего счета '.$v['position_name'].' нет в акте';	
				  }
			  }
			  $res=$res&&$has;
			  
			  $has=true;
			  foreach($sh_i_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции распоряжения на приемку '.$v['position_name'].' нет в акте';	
				  }
			  }
			  $res=$res&&$has;
			  
		  
			  $has=true;
			  foreach($acc_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции поступления '.$v['position_name'].' нет в акте';	
				  }
			  }
			  $res=$res&&$has;
			//}
			
			//if($scan_wf){
			  /*$has=true;
			  foreach($wf_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции распоряжения на списание '.$v['position_name'].' нет в акте';	
				  }
			  }
			  $res=$res&&$has;*/
			  $has=true;
			  foreach($bill_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции исходящего счета '.$v['position_name'].' нет в акте';	
				  }
			  }
			  $res=$res&&$has;
			  
			  $has=true;
			  foreach($sh_i_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции распоряжения на отгрузку '.$v['position_name'].' нет в акте';	
				  }
			  }
			  $res=$res&&$has;
			  
		  
			  $has=true;
			  foreach($acc_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='позиции реализации '.$v['position_name'].' нет в акте';	
				  }
			  }
			//}
			
			
			//г совпадают ли количества по позициям
			// !!!!! приход
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$bill_in_positions);
				if($pos!=-1){
					//	
					//$qua=($v['quantity_as_is']-$v['quantity_fact']);
					$qua=round(($v['quantity_fact']-$v['quantity_as_is']),3);
					$ethalon=round($bill_in_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством во входящем счете '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$sh_i_in_positions);
				if($pos!=-1){
					//	
					$qua=round(($v['quantity_fact']-$v['quantity_as_is']),3);
					$ethalon=round($sh_i_in_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в распоряжении на приемку '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$acc_in_positions);
				if($pos!=-1){
					//	
					$qua=round(($v['quantity_fact']-$v['quantity_as_is']),3);
					$ethalon=round($acc_in_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в поступлении '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
			// !!! расход
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$bill_positions);
				if($pos!=-1){
					//	
					$qua=round(($v['quantity_as_is']-$v['quantity_fact']),3);
					$ethalon=round($bill_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в исходящем счете '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$sh_i_positions);
				if($pos!=-1){
					//	
					$qua=round(($v['quantity_as_is']-$v['quantity_fact']),3);
					$ethalon=round($sh_i_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в распоряжении на отгрузку '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$acc_positions);
				if($pos!=-1){
					//	
					$qua=round(($v['quantity_as_is']-$v['quantity_fact']),3);
					$ethalon=round($acc_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='количество позиции '.$v['position_name'].' '.$qua.' не совпадает с количеством в реализации '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
		}
		
		
		
		$reasons=implode(",<br />",$_reasons);
		return $res;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//проверка, есть ли такая позиция в массиве
	protected function IsInPos($position_id, $haystack, $keyname='pl_position_id'){
		$res=-1;
		
		foreach($haystack as $k=>$v){
			if($v[$keyname]==$position_id){
				$res=$k;
				break;	
			}
		
		}
		return $res;
	}
}
?>