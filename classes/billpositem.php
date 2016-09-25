<?
require_once('abstractitem.php');
require_once('billitem.php');
require_once('billpospmitem.php');


require_once('acc_positem.php');
require_once('acc_item.php');
require_once('authuser.php');

require_once('actionlog.php');

//����������� �������
class BillPosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bill_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_id';	
	}
	
	
	
	//�������� 
	public function Add($params, $pms=NULL){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//������� +/- ��� �������
			$bpm=new BillPosPMItem;
			
			if($code>0){
				$pms['bill_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		
		return $code;
	}
	
	
	//�������������
	public function Edit($id,$params,$pms=NULL,$can_change_cascade=false, $check_delta_summ=false, $result=NULL){
		$_log=new ActionLog;
		$_au=new AuthUser;
		if($result===NULL) $result=$_au->Auth();
		
		if(!isset($params['total'])){
		  $item=$this->GetItemById($id);
		  
		  
		  
		  if(isset($params['quantity'])&&($params['quantity']!=$item['quantity'])){
			   if(isset($params['price_pm'])&&($params['price_pm']!=$item['price_pm'])) $price=$params['price_pm'];
			   else $price=$item['price_pm'];
			   
			   $params['total']=$params['quantity']*$price;
		  }
		}
		
		
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//���� ��� ���� ��, �� ����� ����������� ���
			//���� ��� - �� �������
			$_bpm=new BillPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$id));
			if($bpm===false){
				$pms['bill_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['bill_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}else{
			$_bpm=new BillPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('bill_position_id'=>$id));
			if($bpm!==false){
				$_bpm->Del($bpm['id']);
			}
		}
		
		if($can_change_cascade){
		 
		  //�������� ����� �����  ����������� ��� ����� ����� ����� ��� �������������� +/- � ���. ����� � ���. ���-����
		  
		  
		  //����� ��� ����� �� ������� � ������������� � ������������, ������� � � ��� ���
		  //������� ����, � ���
		  
		  //������� ������������
		  
		  //28.03.2012 !!!!! �������� � ������ �� ������������� ����� storage_id, sector_id
		  //��� ����, ���� � ��� ������� �� ������ ������? �������� ������ �� komplekt_ved_pos_id
		  
		  //�����: ���� ������� �����. ������ - ���� �����, �� ���� - ���� ���� ������������
		  //select id from sh_i_position where position_id=$position_id and sh_i_id in(select id from sh_i where bill_id=$bill_id)
		  $itm=$this->GetItemById($id);
		  if($itm===false) return;
		  
		  
		    $_ai=new AccItem;
			
		  $_shi=new AccPosItem;
		  $_shipm=new AccPosPMItem;
		  $_acc_notes=new accnotesitem;
			
			
		  if($check_delta_summ){
			  $old_summs=array();
		
			  $sql1='select * from acceptance where bill_id="'.$itm['bill_id'].'" 
				';
			  $set=new mysqlSet($sql1);
			  
			  $rs=$set->GetResult();
			  $rc=$set->GetResultNumRows();
			  for($i=0; $i<$rc; $i++){
			  	$f=mysqli_fetch_array($rs);
				$old_summs[]=array('id'=>$f['id'], 'summ'=>$_ai->CalcCost($f['id']));
				
			  }
		   }
		  
		
		  
		  $sql1='select * from acceptance_position 
		  where 
		  	position_id="'.$itm['position_id'].'" and komplekt_ved_id="'.$itm['komplekt_ved_id'].'"
			and acceptance_id in(select id from acceptance where bill_id="'.$itm['bill_id'].'"  and status_id<>6) 
			';
			
		  //echo $sql1; die();
		  $set=new mysqlSet($sql1);
		  
		  $rs=$set->GetResult();
		  $rc=$set->GetResultNumRows();
		  
		  if($pms!==NULL){
			  unset($pms['bill_position_id']);
			  unset($pms['sh_i_position_id']);
			   unset($pms['discount_plus_or_minus']);
			  unset($pms['discount_rub_or_percent']);
			  unset($pms['discount_value']);
			  unset($pms['discount_given']);
		  }
		  
		  
		  for($i=0; $i<$rc; $i++){
			  $f=mysqli_fetch_array($rs);
			  
			  /*echo '<pre>';
			  print_r($f);
			  echo '</pre>';*/
			  
			
			 $do_log_delta=false; $do_prim_delta=false; //����������� �� ��������� � ��, ��������. 
			 $log_message=''; $prim_message='';
			  
			 if(isset($params['price'])){
				   $inner_params=array();
				   $inner_params['price']=$params['price'];
				   $inner_params['price_pm']=$params['price_pm'];
				   if(isset($params['total'])&&($params['quantity'])) $inner_params['total']=$f['quantity']*$params['total']/$params['quantity'];
				   else $inner_params['total']=$params['price_pm']*$f['quantity'];
				   
				   //$inner_params['total']=$params['price_pm']*$f['quantity'];
				   	
				   
				   //$params['total'];
				   $_shi->Edit($f['id'],$inner_params);
				   
				   //������ � ������, �������������� ����������...
				   //��� ��� � ����� �����������
				   if((($inner_params['price']!=$f['price'])/*($inner_params['total']!=$f['total'])*/||($inner_params['price_pm']!=$f['price_pm']))/*&&($params['quantity']==$f['quantity'])*/){
					   $do_log_delta=$do_log_delta||true;
					   $do_prim_delta=$do_prim_delta||true;
					   
					   
				   }
				   
				   $log_message.= '������ ���� '.$f['price_pm'].' ���., ����� '.$f['total'].' ���., ����� ���� '.$inner_params['price_pm'].' ���., ����� '.round($inner_params['total'],2).' ���.';
				   $prim_message.= '������ ���� '.$f['price_pm'].' ���., ����� '.$f['total'].' ���., ����� ���� '.$inner_params['price_pm'].' ���., ����� '.round($inner_params['total'],2).' ���.';
				   
			  }
			  
			  
			   $old_pms=$_shipm->GetItemByFields(array('acceptance_position_id'=>$f['id']));
			  
			  new NonSet('delete from acceptance_position_pm where acceptance_position_id="'.$f['id'].'"');
			 
			  if($pms!==NULL){
				  $pms['acceptance_position_id']=$f['id'];
					  
				  $_shipm->Add($pms);	
				 // var_dump($old_pms); die();
				  
				  if(($pms['plus_or_minus']!=$old_pms['plus_or_minus'])||
				  ($pms['rub_or_percent']!=$old_pms['rub_or_percent'])||
				  ($pms['value']!=$old_pms['value'])){
					  $do_log_delta=$do_log_delta||true;
					  
					  $description='<br /> ';
					  if($pms['plus_or_minus']==0){
						  $description.=' + ';
					  }else{
						  $description.=' - ';
					  }
					  $description.=$pms['value'];
					  if($pms['rub_or_percent']==0){
						  $description.=' ���. ';
					  }else{
						  $description.=' % ';
					  }
					  
					  $description.=' ������� +/-: ';
					 
					  $description.=$pms['discount_value'];
					  if($pms['discount_rub_or_percent']==0){
						  $description.=' ���. ';	
					  }else{
						  $description.=' % ';	
					  }
					   
					  $log_message.=' '.$description; 
				  }
				 /* echo '<pre>';
				  print_r($pms);
				  echo '</pre>';
				  die();*/
			  }
			  
			  $log_message=SecStr($f['name']).'  ���-�� '.$f['quantity'].' '.$log_message;
			  
			  $prim_message='�������������� ����������: �������� ���� ������� ��� ���������� �����: '.SecStr($f['name']).' ������������� '.SecStr($result['login'].' '.SecStr($result['name_s'])).'  ���-�� '.$f['quantity'].' '.$prim_message;
					
			  if($do_log_delta){
					//������� ��������� � ��, ��������.	  
					
					$_log->PutEntry($result['id'], '��������� ���� ������� ���������� ��� ��������� ���� � �����',NULL,235,NULL,$log_message,$f['acceptance_id']);
					
					
			  }
			  if($do_prim_delta){
				  $posted_user_id=$result['id'];
				   	 $_acc_notes->Add(array(
						'user_id'=>$f['acceptance_id'],
						'is_auto'=>1,
						'pdate'=>time(),
						'posted_user_id'=>$posted_user_id,
						'note'=>$prim_message
						));
					
					
			  }
		  }
		  
		  //�������� ����� �����  ����������� ��� ����� ����� ����� ��� �������������� +/- � ���. ����� � ���. ���-����
		  
		  if($check_delta_summ){
				foreach($old_summs as $k=>$v){
					$new_summ=$_ai->CalcCost($v['id']);
					$old_summ=$v['summ'];
					if($new_summ!=$old_summ){
						$description='������ �����: '.$old_summ.' ���., ����� �����: '.$new_summ.' ���.';
					 	$_log->PutEntry($result['id'],'��������� ����� ���������� ��� ��������� ����� ����� ��� �������������� +/-',NULL,523,NULL,$description,$v['id']);
						
					}
				}
		  }
			 
		  //die();
		  
		}
	}
	
	
	
	//�������
	public function Del($id){
		
		$query = 'delete from bill_position_pm where bill_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	
	
}
?>