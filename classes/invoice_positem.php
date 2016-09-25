<?
require_once('abstractitem.php');
require_once('acc_pospmitem.php');
require_once('acc_item.php');
//require_once('billitem.php');
require_once('billpositem.php');
require_once('billpospmitem.php');

require_once('authuser.php');
require_once('actionlog.php');
require_once('acc_notesitem.php');

require_once('billnotesitem.php');

//����������� �������
class InvoicePosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='invoice_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='invoice_id';	
	}
	
	
	
//�������� 
	public function Add($params, $pms=NULL,$change_high_mode=0,$change_low_mode=0){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//������� +/- ��� �������
			$bpm=new AccPosPMItem;
			
			if($code>0){
				$pms['invoice_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		$_acc=new AccItem; $_bpi=new BillPosItem;
		
		if(isset($params['quantity'])&&isset($params['invoice_id'])){
			$acc=$_acc->getitembyid($params['invoice_id']);
			if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 'position_id'=>$item['position_id'], 'storage_id'=>$acc['storage_id'], 'sector_id'=>$acc['sector_id'],
				
				'komplekt_ved_id'=>$params['komplekt_ved_id']
				));	
				if(($bpi!==false)&&($bpi['quantity']==$params['quantity'])){
					$params['total']=$bpi['total'];	
				}elseif(($bpi!==false)){
					//����� �������� ������. ������.
					$price=round($bpi['total']/$bpi['quantity'],10);
					$params['total']=round($params['quantity']*$price,2);
				}
				
			}
			
		}
		
		
		
		
		/*if(isset($params['invoice_id'])&&isset($params['position_id'])&&isset($params['quantity'])&&isset($params['komplekt_ved_id'])) 
			$this->SetChainQuantity($params['invoice_id'],$params['position_id'], $params['komplekt_ved_id'],$params['quantity'],$change_high_mode,$change_low_mode);
		
		*/
		
		
		return $code;
	}
	
	
	//�������������
	public function Edit($id,$params,$pms=NULL,$change_high_mode=0,$change_low_mode=0){
		//���� ������ ���-�� - �������� ���� total
		//
		$_acc=new AccItem; $_bpi=new BillPosItem;
		$item=$this->GetItemById($id);
		
		if(isset($params['quantity'])&&($params['quantity']!=$item['quantity'])){
			 if(isset($params['price_pm'])&&($params['price_pm']!=$item['price_pm'])) $price=$params['price_pm'];
			 else $price=$item['price_pm'];
			 
			 //����� ���������� �� ����� ��� �����/���-��, ���. �� 10 ������
			 //������ �������� � ������������� ��������� ��� ��������� �����������
			 $acc=$_acc->getitembyid($params['invoice_id']);
			 if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 'position_id'=>$item['position_id'], 'storage_id'=>$acc['storage_id'], 'sector_id'=>$acc['sector_id'], 
				'komplekt_ved_id'=>$item['komplekt_ved_id']
				));	
				if(($bpi!==false)){
					$price=round($bpi['total']/$bpi['quantity'],10);
					$params['price_pm']=$price;
					//echo $price; die();
				}
			 }
			
			
			 
			 $params['total']=$params['quantity']*$price;
		}
		
		//���� ���-��=���-�� �� ����� - ������ �����=����� �� �����...
			  
		if(isset($params['quantity'])&&isset($params['invoice_id'])){
			$acc=$_acc->getitembyid($params['invoice_id']);
			if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 'position_id'=>$item['position_id'], 'storage_id'=>$acc['storage_id'], 'sector_id'=>$acc['sector_id'], 
				'komplekt_ved_id'=>$item['komplekt_ved_id']
				));	
				if(($bpi!==false)&&($bpi['quantity']==$params['quantity'])){
					$params['total']=$bpi['total'];	
				}
				
			}
			
		}
		
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//���� ��� ���� ��, �� ����� ����������� ���
			//���� ��� - �� �������
			$_bpm=new AccPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('invoice_position_id'=>$id));
			if($bpm===false){
				$pms['invoice_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['invoice_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}
		
		
		
		
		/*if(isset($params['invoice_id'])&&isset($params['position_id'])&&isset($params['quantity'])&&isset($params['komplekt_ved_id'])) 
			$this->SetChainQuantity($params['invoice_id'],$params['position_id'], $params['komplekt_ved_id'], $params['quantity'],$change_high_mode,$change_low_mode);
		*/
		
	}
	
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from invoice_position_pm where invoice_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	

	public function SetChainQuantity($invoice_id,$position_id, $komplekt_ved_id, $quantity,$change_high_mode=0,$change_low_mode=0, $_result=NULL, $item=NULL){
		//if(($change_high_mode==0)&&($change_low_mode==0)) return;
		
		
		$log=new ActionLog;
		$auth=new AuthUser;
		
		if($_result===NULL) $_result=$auth->Auth();
		
		
		$_ai=new AccItem;
		if($item===NULL) $ai=$_ai->getitembyid($invoice_id);
		else $ai=$item;
		
		if($ai!==false){
			$_bpi=new BillPosItem;
			$_bpmi=new BillPosPmItem;
			
			$bpi=$_bpi->GetItemByfields(array('bill_id'=>$ai['bill_id'], 'position_id'=>$position_id, 'komplekt_ved_id'=>$komplekt_ved_id));
			if($bpi!==false){
				
				//������ ���������� � ������������
				//$bpi['quantity']
				
				
				$delta_rasp=0;
				//������ ���������� � ������ ������������ ������������
				$set=new MysqlSet('select sum(quantity) from invoice_position where invoice_id<>"'.$invoice_id.'" and position_id="'.$position_id.'" and komplekt_ved_id="'.$komplekt_ved_id.'" and invoice_id in(select id from invoice where bill_id="'.$bpi['bill_id'].'" and is_confirmed=1)');
				$rs=$set->getResult();
				
				$f=mysqli_fetch_array($rs);
				
				$delta_rasp=round(($quantity-($bpi['quantity']-(float)$f[0])),3);
				
				//echo ' ������='.$quantity.'  � �����='.$bpi['quantity'].' � ������ ����='.(float)$f[0];
				//die();
				
				//���������� - ��� ������ >0
				//���������� - ��� ������ <0
				//echo $delta_rasp; 
				
				if(($delta_rasp!=0)
				/*(($delta_rasp>0)&&($change_high_mode==1))||
				
				(($delta_rasp<0)&&($change_low_mode==1))*/
				){
					
				
					//echo ($delta_rasp); die;
					
					
					//������ �����....
					//�� ���� �������, ����� ��������� ���-�� � ������� ��������
					if(!(($delta_rasp<0)&&($change_low_mode==0))){
					
					  $_bill_positem=new BillPosItem;
					  $_bill_pospmitem=new BillPosPMItem;
					  
					  
					  
					  $bill_positem=$_bill_positem->Getitembyfields(array('bill_id'=>$ai['bill_id'], 'position_id'=>$position_id, 'storage_id'=>0/*$ai['storage_id']*/, 'sector_id'=>0/*$ai['sector_id']*/, 'komplekt_ved_id'=>$komplekt_ved_id)); //�������� ������� �� ������, �������, ������...
					  
					//  echo '���� ������� ���.�����:'; var_dump(array('bill_id'=>$ai['bill_id'], 'position_id'=>$position_id, 'storage_id'=>0/*$ai['storage_id']*/, 'sector_id'=>0/*$ai['sector_id']*/, 'komplekt_ved_id'=>$komplekt_ved_id)); die();
					  
					  if($bill_positem!==false){
						  
						  
						  $bill_pospmitem=$_bill_pospmitem->GetItemByFields(array('bill_position_id'=>$bill_positem['id']));
						  if($bill_pospmitem!==false){
							  $pms=array(
								  'plus_or_minus'=>$bill_pospmitem['plus_or_minus'],
								  'rub_or_percent'=>$bill_pospmitem['rub_or_percent'],
								  'value'=>$bill_pospmitem['value'],
								  //'discount_plus_or_minus'=>$bill_pospmitem['discount_plus_or_minus'],
								  'discount_rub_or_percent'=>$bill_pospmitem['discount_rub_or_percent'],
								  'discount_value'=>$bill_pospmitem['discount_value']
							  );	
						  }else $pms=NULL;
						  
						  
						  
						  $new_bill_quantity=round(($bill_positem['quantity']+$delta_rasp),3);
						  
						  $_bill_positem->Edit($bill_positem['id'], array('quantity'=>$new_bill_quantity), $pms);
						  
						  //������� �������������� � �����
						  $_ni=new BillNotesItem;
					  
						  $note='���������� ������� '.SecStr($bill_positem['name']).' ������������� ��������� ��� ����������� ��������� ���������� �'.$invoice_id.' ����������� '.$_result['name_s'].' ('.$_result['login'].')'.'. ��������� ���������� '.$bill_positem['quantity'].', �������� ���������� '.$new_bill_quantity;
						  
						  $_ni->Add(array(
							  'user_id'=>$ai['bill_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
						  ));
						  
						  //������ � ������ ����� - ,
						  $log->PutEntry($_result['id'],'�������������� ������������ ������� ���������� ����� ��� ����������� ��������� ����������',  NULL, 189,NULL, $note, $ai['bill_id']);
					  
					  	  
						  //�������������� � �����������
						  $_ani=new AccNotesItem;
						  $_bi=new BillItem;
						  $bill=$_bi->GetItemById($ai['bill_id']);
						  $note='���������� ������� '.SecStr($bpi['name']).' � ��������� ����� �'.$bill['code'].' ������������� ��������� ��� ����������� ���������� ����������� '.$_result['name_s'].' ('.$_result['login'].')'.'. ��������� ���������� '.$bill_positem['quantity'].', �������� ���������� '.$new_bill_quantity;
						  
						  $_ani->Add(array(
							  'user_id'=>$ai['id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
						  ));
					  }
					}
					
					
					
				}
				
				
				
			}
		}
		//die();
	}
	
	
}
?>