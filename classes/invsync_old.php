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
	
	
	//������� �������� ���������
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
			
			//��������� ���������.
			//������������ �� ������ � ���������
			//������� ��������. 
			//������������ �� ������
			$positions=$_ii->GetPositionsArr($id);
			
			
			
			//� - ���� �� ���� ���������
			
			// !!!!!!! ������
			$set1=new mysqlset('select * from bill where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_in_id=0;
			  if((int)$rc1==0){
				 if($scan_bill){
					
					//echo 'zzzzzzzzz'; die();
					
					//������� ����
					  $inner_params=array();
					 
					  $inner_params['inventory_id']=$id;
					  $inner_params['is_incoming']=1;
					  
					  $inner_params['supplier_id']=$result1['org_id'];
					  $inner_params['org_id']=$result1['org_id'];
					  $lc=new BillInCreator;
					  $lc->ses->ClearOldSessions();
					  $inner_params['code']=$lc->GenLogin($result1['id']);
					  
					 
					  //����� ���������
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
						
					  $log->PutEntry($result1['id'],'������ �������� ���� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,608,NULL,NULL,$bill_in_id);
					 
					   
					  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���� ��� ������ � ��������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					 
				 }
			  }else{
				  $bill_in_id=$f1['id'];
				 //���� ���-� - �����-��
				  if($f1['status_id']==3){
					  $_bill_in->Edit($bill_in_id, array('status_id'=>1)); 
						 $log->PutEntry($result1['id'],'����������� �������� ���� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,627,NULL,NULL,$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���c���������� ����� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				 
				 
				 //������ ���������, ���� ����
				  if($f1['is_confirmed_price']==0){
						$_bill_in->Edit($bill_in_id, array('is_confirmed_price'=>1, 'confirm_price_pdate'=>time(),'user_confirm_price_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� ���� ��������� ����� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,608,NULL,NULL,$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� ��� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
				  if($f1['is_confirmed_shipping']==0){
						$_bill_in->Edit($bill_in_id, array('is_confirmed_shipping'=>1, 'confirm_shipping_pdate'=>time(),'user_confirm_shipping_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� ������� ��������� ����� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,608,NULL,NULL,$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� ������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
				  		//$_reasons[]='��� ���������� ������������ �� �������';
						
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
						$log->PutEntry($result1['id'],'������ ������������ �� ������� �� ��������� ����� �� �����������  ��������� ��������� �������� ������������������� ����',NULL,613,NULL,NULL,$bill_in_id);
						
						$log->PutEntry($result1['id'],'������ ������������ �� ������� �� ��������� ����� �� �����������  ��������� ��������� �������� ������������������� ����',NULL,640,NULL,NULL,$sh_i_in_id);
						  
						
						 $log->PutEntry($result1['id'],'������ ������������ �� ������� �� ��������� ����� �� �����������  ��������� ��������� �������� ������������������� ����',NULL,326,NULL,NULL,$id);	
						 $sni=new ShINotesItem;
						 $sni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������������ ��� ������� � ���������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						  
						
				  }
					  
			  }else {
				  $sh_i_in_id=$f1['id'];
			  	  
				   //���� ���-� - �����-��
				  if($f1['status_id']==3){
					  $_sh_i_in->Edit($sh_i_in_id, array('status_id'=>7)); 
						 $log->PutEntry($result1['id'],'����������� ������������ �� ������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,326,NULL,NULL,$sh_i_in_id);
						    $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���c���������� ������������ �� �������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  //������ ���������, ���� ����
				  if($f1['is_confirmed']==0){
						$_sh_i_in->Edit($sh_i_in_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� ������������ �� ������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,326,NULL,NULL,$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					//$_reasons[]='��� ���������� �����������';	
					
					
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
					  
					  $log->PutEntry($result1['id'],'������ ����������� ������ �� ������������������� ���� �� ����������� ��������� ��������� ��������',NULL,333,NULL,NULL,$item['id']);
					 
					
					 
					 $log->PutEntry($result1['id'],'������ ����������� ������ �� ������������������� ���� �� ����������� ��������� ��������� ��������',NULL,661,NULL,NULL,$acc_in_id);
					 $ani=new AccNotesItem;
					  $ani->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� ���� ������� � ���������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					
					
				 }
			  }else{
				  
				  $acc_in_id=$f1['id'];
				  
				  
				     //���� ���-� - �����-��
				  if($f1['status_id']==6){
					  $_acc_in->Edit($acc_in_id, array('status_id'=>4)); 
						 $log->PutEntry($result1['id'],'����������� ����������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,661,NULL,NULL,$acc_in_id);
						    $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: �������������� ����������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  
				  if($f1['is_confirmed']==0){
						
						$_acc_in->Edit($acc_in_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true,$result1); 
						 
						 $log->PutEntry($result1['id'],'�������� ����������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,661,NULL,NULL,$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
			  }
			  
			  
			  
			  // !!!!!!!!!!!!!!! ��������
			  $set1=new mysqlset('select * from bill where is_incoming=0 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_id=0;
			  if((int)$rc1==0){
				 if($scan_wf){
					
					//echo 'zzzzzzzzz'; die();
					
					//������� ����
					  $inner_params=array();
					 
					  $inner_params['inventory_id']=$id;
					  $inner_params['is_incoming']=0;
					  
					  $inner_params['supplier_id']=$result1['org_id'];
					  $inner_params['org_id']=$result1['org_id'];
					  $lc=new BillCreator;
					  $lc->ses->ClearOldSessions();
					  $inner_params['code']=$lc->GenLogin($result1['id']);
					  
					 
					  //����� ���������
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
						
					  $log->PutEntry($result1['id'],'������ ��������� ���� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,92,NULL,NULL,$bill_id);
					 
					   
					  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���� ��� ������ � ��������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					 
				 }
			  }else{
				  $bill_id=$f1['id'];
				 //���� ���-� - �����-��
				  if($f1['status_id']==3){
					  $_bill->Edit($bill_id, array('status_id'=>1)); 
						 $log->PutEntry($result1['id'],'����������� ��������� ���� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,131,NULL,NULL,$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���c���������� ����� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				 
				 
				 //������ ���������, ���� ����
				  if($f1['is_confirmed_price']==0){
						$_bill->Edit($bill_id, array('is_confirmed_price'=>1, 'confirm_price_pdate'=>time(),'user_confirm_price_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� ���� ���������� ����� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,92,NULL,NULL,$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� ��� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
				  if($f1['is_confirmed_shipping']==0){
						$_bill->Edit($bill_id, array('is_confirmed_shipping'=>1, 'confirm_shipping_pdate'=>time(),'user_confirm_shipping_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� �������� ���������� ����� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,92,NULL,NULL,$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� �������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
				  		//$_reasons[]='��� ���������� ������������ �� �������';
						
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
						$log->PutEntry($result1['id'],'������ ������������ �� �������� �� ���������� ����� �� �����������  ��������� ��������� �������� ������������������� ����',NULL,93,NULL,NULL,$bill_id);
						
						$log->PutEntry($result1['id'],'������ ������������ �� �������� �� ���������� ����� �� �����������  ��������� ��������� �������� ������������������� ����',NULL,215,NULL,NULL,$sh_i_id);
						  
						
						 $log->PutEntry($result1['id'],'������ ������������ �� �������� �� ���������� ����� �� �����������  ��������� ��������� �������� ������������������� ����',NULL,326,NULL,NULL,$id);	
						 $sni=new ShINotesItem;
						 $sni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������������ ��� ������� � ���������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						  
						
				  }
					  
			  }else {
				  $sh_i_id=$f1['id'];
			  	  
				   //���� ���-� - �����-��
				  if($f1['status_id']==3){
					  $_sh_i->Edit($sh_i_id, array('status_id'=>7)); 
						 $log->PutEntry($result1['id'],'����������� ������������ �� �������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,326,NULL,NULL,$sh_i_id);
						    $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���c���������� ������������ �� �������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  //������ ���������, ���� ����
				  if($f1['is_confirmed']==0){
						$_sh_i->Edit($sh_i_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� ������������ �� �������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,326,NULL,NULL,$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					//$_reasons[]='��� ���������� �����������';	
					
					
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
					  
					  $log->PutEntry($result1['id'],'������ ���������� ������ �� ������������������� ���� �� ����������� ��������� ��������� ��������',NULL,333,NULL,NULL,$item['id']);
					 
					
					 
					 $log->PutEntry($result1['id'],'������ ���������� ������ �� ������������������� ���� �� ����������� ��������� ��������� ��������',NULL,229,NULL,NULL,$acc_id);
					 $ani=new AccNotesItem;
					  $ani->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ���������� ���� ������� � ���������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					
					
				 }
			  }else{
				  
				  $acc_id=$f1['id'];
				  
				  
				     //���� ���-� - �����-��
				  if($f1['status_id']==6){
					  $_acc->Edit($acc_id, array('status_id'=>4)); 
						 $log->PutEntry($result1['id'],'����������� ���������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,229,NULL,NULL,$acc_id);
						    $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: �������������� ���������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  
				  if($f1['is_confirmed']==0){
						
						$_acc->Edit($acc_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),'user_confirm_id'=>$result1['id']), true,$result1); 
						 
						 $log->PutEntry($result1['id'],'�������� ���������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,229,NULL,NULL,$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					//$_reasons[]='��� ���������� ������������ �� ��������';	
					
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
					  
					   $log->PutEntry($result1['id'],'������ ������������ �� �������� ������ �� ������������������� ���� �� ����������� ��������� ��������� ��������',NULL,333,NULL,NULL,$item['id']);
					 
					
					 
					 $log->PutEntry($result1['id'],'������ ������������ �� �������� ������ �� ������������������� ���� �� ����������� ��������� ��������� ��������',NULL,105,NULL,NULL,$wf_id);
					 
					  
				  }
			  }else{
				  $wf_id=$f1['id'];
				  
				  
				  
				    //���� ���-� - �����-��
				  if($f1['status_id']==3){
					  $_wf->Edit($wf_id, array('status_id'=>1)); 
						 $log->PutEntry($result1['id'],'����������� ������������ �� �������� �� ����������� ��������� ��������� �������� ������������������� ����',NULL,105,NULL,NULL,$wf_id);
						   $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>'�������������� ����������: �������������� ������������ �� �������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
					  
				  }
				  
				  
				  
				  //������ ���������, ���� ����
				  if($f1['is_confirmed_fill_wf']==0){
						$_wf->Edit($wf_id, array('is_confirmed_fill_wf'=>1, 'confirm_fill_wf_pdate'=>time(),'user_confirm_fill_wf_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� ���������� ������������ �� �������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,105,NULL,NULL,$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� ���������� ������������ �� �������� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
				  
				  if($f1['is_confirmed']==0){
						$_wf->Edit($wf_id, array('is_confirmed'=>1, 'confirm_pdate'=>time(),' 	user_confirm_id'=>$result1['id']), true); 
						 $log->PutEntry($result1['id'],'�������� �������� ��������� ����� �� ����������� ��������� �������� �������� ������������������� ����',NULL,105,NULL,NULL,$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ����������� ��� �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));
						 
				  }
			  }
			  
			  */
			  
			  
			  
			  
			  	//� ��� �� ������� �� ���� ���� � ���� �����
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
			  
			  // !!!!!!! ������
			  
			  $has=true;
			  foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$bill_in_positions);
				  
				  //$qua=($v['quantity_as_is']-$v['quantity_fact']);
				  $qua=($v['quantity_fact']-$v['quantity_as_is']);
				  if(($pos==-1)&&($qua>0)){
					  
					  
					  //$has=$has&&false;
					  //$_reasons[]='������� '.$v['position_name'].' ��� �� �������� �����';	
					  $_bpi_in->Add(array(
					  'bill_id'=>$bill_in_id,
					  'position_id'=>$v['position_id'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'name'=>SecStr($v['position_name']),
					  'dimension'=>SecStr($v['dim_name']),
					  
					  'quantity'=>$qua));
					  
					  //������? ����������?
					  
					   $log->PutEntry($result1['id'],'��������� ������� �� �������� ���� �� ����������� ��������� �������� �������� ������������������� ����',NULL,613,NULL,''.SecStr($v['position_name']).', '.$qua.'',$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� '.$v['position_name'].' ��� � ������������ �� �������';	
					   $_spi_in->Add(array(
					  'sh_i_id'=>$sh_i_in_id,
							  'pl_position_id'=>$v['pl_position_id'],
							  'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
					  //����������? ������?
					   $log->PutEntry($result1['id'],'��������� �������  � ������������ �� ������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,644,''.SecStr($v['position_name']).', '.$qua.'',NULL,$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� '.$v['position_name'].' ��� � �����������';
					  
					   $_api_in->Add(array(
					  'acceptance_id'=>$acc_in_id,
							 'pl_position_id'=>$v['pl_position_id'],
							 'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
							  
					//����������? ������?
					   $log->PutEntry($result1['id'],'��������� �������  � ����������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,664,NULL,''.SecStr($v['position_name']).', '.$qua.'',$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
				  }
			  }
			  $res=$res&&$has;
			}
			
			
			
			if($scan_wf){
			  
			  // !!!!!!!!!!! ������
			  
			   $has=true;
			   foreach($positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$bill_positions);
				  
				  $qua=$v['quantity_as_is']-$v['quantity_fact'];
				  if(($pos==-1)&&($qua>0)){
					  
					  
					  //$has=$has&&false;
					  //$_reasons[]='������� '.$v['position_name'].' ��� �� �������� �����';	
					  $_bpi->Add(array(
					  'bill_id'=>$bill_id,
					  'position_id'=>$v['position_id'],
					  'pl_position_id'=>$v['pl_position_id'],
					  'name'=>SecStr($v['position_name']),
					  'dimension'=>SecStr($v['dim_name']),
					  
					  'quantity'=>$qua));
					  
					  //������? ����������?
					  
					   $log->PutEntry($result1['id'],'��������� ������� �� �������� ���� �� ����������� ��������� �������� �������� ������������������� ����',NULL,613,NULL,''.SecStr($v['position_name']).', '.$qua.'',$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� '.$v['position_name'].' ��� � ������������ �� �������';	
					   $_spi->Add(array(
					  'sh_i_id'=>$sh_i_id,
							  'pl_position_id'=>$v['pl_position_id'],
							  'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
					  //����������? ������?
					   $log->PutEntry($result1['id'],'��������� �������  � ������������ �� ������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,644,''.SecStr($v['position_name']).', '.$qua.'',NULL,$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� '.$v['position_name'].' ��� � �����������';
					  
					   $_api->Add(array(
					  'acceptance_id'=>$acc_id,
							 'pl_position_id'=>$v['pl_position_id'],
							 'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
							  
					//����������? ������?
					   $log->PutEntry($result1['id'],'��������� �������  � ����������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,664,NULL,''.SecStr($v['position_name']).', '.$qua.'',$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� '.$v['position_name'].' ��� � ������������ �� ��������';	
					  
					 
					  
					  $_ipi->Add(array(
					  'interstore_id'=>$wf_id,
							 'pl_position_id'=>$v['pl_position_id'],
							 'position_id'=>$v['position_id'],
							  'name'=>SecStr($v['position_name']),
							  'dimension'=>SecStr($v['dim_name']),
							  'quantity'=>$qua));
							  
					//����������? ������?	
					
					 $log->PutEntry($result1['id'],'��������� ������� � ������������ �� �������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,106,NULL,''.SecStr($v['position_name']).', '.$qua.'',$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������� ������� '.SecStr($v['position_name']).', '.$qua.' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
				  }
				  
				  
			  }
			  $res=$res&&$has;*/
			  
			  
			  
			  
			}
			
			  
			 
			 
			 
			 //� ��� �� ������� ���� ����� ���� � ����
			
			// !!!!!!!!!!	������
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
						    $log->PutEntry($result1['id'],'������� ������� �� ��������� ����� �� ����������� ��������� �������� �������� ������������������� ����',NULL,613,NULL,''.SecStr($v['position_name']).', '.$bpi['quantity'].'',$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$bpi['quantity'].'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
				  
				 
					  //$_reasons[]='������� ������������ �� ������� '.$v['position_name'].' ��� � ����';	
					  $spi=$_spi_in->GetItemByFields(array(
					  	'sh_i_id'=>$sh_i_in_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($spi!==false){
						   $_spi_in->Del($spi['id']);
						   
						   //����������? ������?
					   $log->PutEntry($result1['id'],'������� �������  �� ������������ �� ������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,644,NULL,''.SecStr($v['position_name']).', '.$spi['quantity'].'',$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$spi['quantity'].'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
				  
					//$_reasons[]='������� ����������� '.$v['position_name'].' ��� � ����';	
					  
					  $api=$_api_in->GetItemByFields(array(
					  	'acceptance_id'=>$acc_in_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($api!==false){
						   $_api_in->Del($api['id']);
						   
						   //����������? ������?
					   $log->PutEntry($result1['id'],'������� �������  �� ����������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,664,NULL,''.SecStr($v['position_name']).', '.$api['quantity'].'',$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$api['quantity'].' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					  
				  }
			  }
			  $res=$res&&$has;
			
			
				
			// !!!!!!!!!!! ������ 
			$has=true;
			  foreach($bill_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				 
				   if($pos>=0){
					   $qua=$positions[$pos]['quantity_as_is']-$positions[$pos]['quantity_fact']; 
				  }else $qua=0;
				  
				  if(($pos==-1)||(($pos>=0)&&($qua<=0))){
				
					  //$_reasons[]='������� ��������� ����� '.$v['position_name'].' ��� � ����';	
					  $bpi=$_bpi->GetItemByFields(array(
					  	'bill_id'=>$bill_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']
						));
					  if($bpi!==false){
						  $_bpi->Del($bpi['id']);
						    $log->PutEntry($result1['id'],'������� ������� �� ���������� ����� �� ����������� ��������� �������� �������� ������������������� ����',NULL,93,NULL,''.SecStr($v['position_name']).', '.$bpi['quantity'].'',$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$bpi['quantity'].'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� ������������ �� ������� '.$v['position_name'].' ��� � ����';	
					  $spi=$_spi->GetItemByFields(array(
					  	'sh_i_id'=>$sh_i_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($spi!==false){
						   $_spi->Del($spi['id']);
						   
						   //����������? ������?
					   $log->PutEntry($result1['id'],'������� �������  �� ������������ �� �������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,219,NULL,''.SecStr($v['position_name']).', '.$spi['quantity'].'',$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$spi['quantity'].'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
				  
					//$_reasons[]='������� ����������� '.$v['position_name'].' ��� � ����';	
					  
					  $api=$_api->GetItemByFields(array(
					  	'acceptance_id'=>$acc_id,
						'position_id'=>$v['position_id'],
						'pl_position_id'=>$v['pl_position_id']));
					  if($api!==false){
						   $_api->Del($api['id']);
						   
						   //����������? ������?
					   $log->PutEntry($result1['id'],'������� �������  �� ���������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,235,NULL,''.SecStr($v['position_name']).', '.$api['quantity'].'',$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$api['quantity'].' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
					  //$_reasons[]='������� ������������ �� �������� '.$v['position_name'].' ��� � ����';	
					  $ipi=$_ipi->GetItemByFields(array(
					  	'interstore_id'=>$wf_id,
					  	'position_id'=>$v['id'],
						'pl_position_id'=>$v['pl_position_id'],));
					  if($ipi!==false) {
						  $_ipi->Del($ipi['id']);
						  
						   $log->PutEntry($result1['id'],'������� �������  �� ������������ �� �������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,106,NULL,''.SecStr($v['position_name']).', '.$ipi['quantity'].'',$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ������� ������� '.SecStr($v['position_name']).', '.$ipi['quantity'].'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  
					  }
				  }
			  }
			  $res=$res&&$has;
			  */
			//}
			 
			 
			  //� ������������ ���������
			  
			// !!!!!!!!!! ������  
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� �� �������� ����� '.$ethalon;	
						
						$bpi=$_bpi_in->GetItemByFields(array(
							'bill_id'=>$bill_in_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($bpi!==false){
							 $_bpi_in->Edit($bpi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'��������������� ���-�� ������� �� �������� ����� �� ����������� ��������� �������� �������� ������������������� ����',NULL,613,NULL,''.SecStr($v['position_name']).', ���� '.$bill_in_positions[$pos]['quantity'].', ����� '.$qua.' ',$bill_in_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$bill_in_positions[$pos]['quantity'].', ����� '.$qua.' �� �������� �����  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ������������ �� ������� '.$ethalon;	
						$spi=$_spi_in->GetItemByFields(array(
							'sh_i_id'=>$sh_i_in_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($spi!==false){
							 $_spi_in->Edit($spi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'��������������� ���-�� �������  �� ������������ �� ������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,644,NULL,''.SecStr($v['position_name']).', ���� '.$sh_i_positions[$pos]['quantity'].', ����� '.$qua.'',$sh_i_in_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$sh_i_in_positions[$pos]['quantity'].', ����� '.$qua.'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ����������� '.$ethalon;	
						
						$api=$_api_in->GetItemByFields(array(
							'acceptance_id'=>$acc_in_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($api!==false) {
							$_api_in->Edit($api['id'],array('quantity'=>$qua));
							
							 $log->PutEntry($result1['id'],'��������������� ���-�� �������  � ����������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,664,NULL, ''.SecStr($v['position_name']).', ���� '.$acc_positions[$pos]['quantity'].', ����� '.$qua.'',$acc_in_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_in_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$acc_positions[$pos]['quantity'].', ����� '.$qua.' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
						'posted_user_id'=>$result1['id'],
						'is_auto'=>1));	
					  }
					}
				}
			}
			$res=$res&&$has;
			
			
			// !!!!!!!! ������
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ������������ �� �������� '.$ethalon;	
						$ipi=$_ipi->GetItemByFields(array(
						  'interstore_id'=>$wf_id,
						  'position_id'=>$v['position_id'],
						  'pl_position_id'=>$v['pl_position_id']));
						if($ipi!==false) {
							$_ipi->Edit($ipi['id'],array('quantity'=>$qua));
							
							 $log->PutEntry($result1['id'],'��������������� ���-�� �������  � ������������ �� �������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,106,NULL,''.SecStr($v['position_name']).', ���� '.$wf_positions[$pos]['quantity'].',  ����� '.$qua.'',$wf_id);
						  $bni=new IsNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$wf_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$wf_positions[$pos]['quantity'].',  ����� '.$qua.' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� �� �������� ����� '.$ethalon;	
						
						$bpi=$_bpi->GetItemByFields(array(
							'bill_id'=>$bill_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($bpi!==false){
							 $_bpi->Edit($bpi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'��������������� ���-�� ������� � ��������� ����� �� ����������� ��������� �������� �������� ������������������� ����',NULL,93,NULL,''.SecStr($v['position_name']).', ���� '.$bill_positions[$pos]['quantity'].', ����� '.$qua.' ',$bill_id);
						  $bni=new BillNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$bill_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$bill_positions[$pos]['quantity'].', ����� '.$qua.' �� �������� �����  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ������������ �� ������� '.$ethalon;	
						$spi=$_spi->GetItemByFields(array(
							'sh_i_id'=>$sh_i_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($spi!==false){
							 $_spi->Edit($spi['id'],array('quantity'=>$qua));
							 
							  $log->PutEntry($result1['id'],'��������������� ���-�� �������  �� ������������ �� �������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,219,NULL,''.SecStr($v['position_name']).', ���� '.$sh_i_positions[$pos]['quantity'].', ����� '.$qua.'',$sh_i_id);
						  $bni=new ShINotesItem;
					  $bni->Add(array(
					  	'user_id'=>$sh_i_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$sh_i_positions[$pos]['quantity'].', ����� '.$qua.'  �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
						//$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ����������� '.$ethalon;	
						
						$api=$_api->GetItemByFields(array(
							'acceptance_id'=>$acc_id,
							'position_id'=>$v['position_id'],
							'pl_position_id'=>$v['pl_position_id']));
						if($api!==false) {
							$_api->Edit($api['id'],array('quantity'=>$qua));
							
							 $log->PutEntry($result1['id'],'��������������� ���-�� ������� � ���������� �� ����������� ��������� �������� �������� ������������������� ����',NULL,235,NULL, ''.SecStr($v['position_name']).', ���� '.$acc_positions[$pos]['quantity'].', ����� '.$qua.'',$acc_id);
						  $bni=new AccNotesItem;
					  $bni->Add(array(
					  	'user_id'=>$acc_id,
						'pdate'=>time(),
						'note'=>'�������������� ����������: ��������������� ���-�� ������� '.SecStr($v['position_name']).', ���� '.$acc_positions[$pos]['quantity'].', ����� '.$qua.' �� ��������� ��������� �������� �� ���� �������������� � '.$item['code'],
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
	
	
	
	
	//������� �������� ������� ����� ��������� ���� � ��������� ���� ���� ���
	public function HasNotDifference($id, &$reasons){
		$res=true; 	//��� �������
		
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
			
			
			
			
			
			
			//� - ���� �� ���� ���������
			
			  $set1=new mysqlset('select * from bill where is_incoming=1 and org_id="'.$result1['org_id'].'" and inventory_id="'.$id.'"');
			  
			  $rs1=$set1->getResult();
			  $rc1=$set1->getResultNumRows();
			  $f1=mysqli_fetch_array($rs1);
			  $bill_in_id=0;
			  if((int)$rc1==0){
				  if($scan_bill){
					  $res=$res&&false;
				  	  $_reasons[]='��� ���������� ��������� �����';	
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
				  		$_reasons[]='��� ���������� ������������ �� �������';
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
					$_reasons[]='��� ���������� �����������';	
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
				  	  $_reasons[]='��� ���������� ���������� �����';	
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
				  		$_reasons[]='��� ���������� ������������ �� ��������';
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
					$_reasons[]='��� ��������� ����������';	
				 }
			  }else $acc_id=$f1['id'];
			
			
			
			
		
			
			//� ��� �� ������� �� ���� ���� � ���� �����
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
					  $_reasons[]='������� '.$v['position_name'].' ��� �� �������� �����';	
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
					  $_reasons[]='������� '.$v['position_name'].' ��� � ������������ �� �������';	
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
					  $_reasons[]='������� '.$v['position_name'].' ��� � �����������';	
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
						$_reasons[]='������� '.$v['position_name'].' ��� � ��������� �����';	
					}
				}
				$res=$res&&$has;
				
			   
				
				$has=true;
				foreach($positions as $k=>$v){
				   
					$pos=$this->IsInPos($v['pl_position_id'],$sh_i_positions);
					
					 $qua=($v['quantity_as_is']-$v['quantity_fact']);
					if(($pos==-1)&&($qua>0)){
						$has=$has&&false;
						$_reasons[]='������� '.$v['position_name'].' ��� � ������������ �� ��������';	
					}
				}
				$res=$res&&$has;
				
				
			  
				
				$has=true;
				foreach($positions as $k=>$v){
					$pos=$this->IsInPos($v['pl_position_id'],$acc_positions);
				   	$qua=($v['quantity_as_is']-$v['quantity_fact']);
					if(($pos==-1)&&($qua>0)){
						$has=$has&&false;
						$_reasons[]='������� '.$v['position_name'].' ��� � ����������';	
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
					  $_reasons[]='������� '.$v['position_name'].' ��� � ������������ �� ��������';	
				  }
			  }
			  $res=$res&&$has;*/
			}
			
			//� ��� �� ������� ���� ����� ���� � ����
			//if($scan_bill){
			  $has=true;
			  foreach($bill_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='������� ��������� ����� '.$v['position_name'].' ��� � ����';	
				  }
			  }
			  $res=$res&&$has;
			  
			  $has=true;
			  foreach($sh_i_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='������� ������������ �� ������� '.$v['position_name'].' ��� � ����';	
				  }
			  }
			  $res=$res&&$has;
			  
		  
			  $has=true;
			  foreach($acc_in_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='������� ����������� '.$v['position_name'].' ��� � ����';	
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
					  $_reasons[]='������� ������������ �� �������� '.$v['position_name'].' ��� � ����';	
				  }
			  }
			  $res=$res&&$has;*/
			  $has=true;
			  foreach($bill_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='������� ���������� ����� '.$v['position_name'].' ��� � ����';	
				  }
			  }
			  $res=$res&&$has;
			  
			  $has=true;
			  foreach($sh_i_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='������� ������������ �� �������� '.$v['position_name'].' ��� � ����';	
				  }
			  }
			  $res=$res&&$has;
			  
		  
			  $has=true;
			  foreach($acc_positions as $k=>$v){
				  $pos=$this->IsInPos($v['pl_position_id'],$positions);
				  if($pos==-1){
					  $has=$has&&false;
					  $_reasons[]='������� ���������� '.$v['position_name'].' ��� � ����';	
				  }
			  }
			//}
			
			
			//� ��������� �� ���������� �� ��������
			// !!!!! ������
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
						$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� �� �������� ����� '.$ethalon;	
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
						$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ������������ �� ������� '.$ethalon;	
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
						$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ����������� '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
			// !!! ������
			
			$has=true;
			foreach($positions as $k=>$v){
				$pos=$this->IsInPos($v['pl_position_id'],$bill_positions);
				if($pos!=-1){
					//	
					$qua=round(($v['quantity_as_is']-$v['quantity_fact']),3);
					$ethalon=round($bill_positions[$pos]['quantity'],3);
					if(($qua>0)&&($ethalon!=$qua)){
						$has=$has&&false;
						$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ��������� ����� '.$ethalon;	
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
						$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ������������ �� �������� '.$ethalon;	
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
						$_reasons[]='���������� ������� '.$v['position_name'].' '.$qua.' �� ��������� � ����������� � ���������� '.$ethalon;	
					}
				}
			}
			$res=$res&&$has;
			
			
		}
		
		
		
		$reasons=implode(",<br />",$_reasons);
		return $res;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//��������, ���� �� ����� ������� � �������
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