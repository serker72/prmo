<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('maxformer.php');
require_once('billitem.php');

require_once('supplieritem.php');

//require_once('pl_dismaxvalgroup.php');

// ����������� ������
class BillInPosGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bill_position';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	//������ �������
	//������ �������
	public function GetItemsByIdArr($id, $current_id=0, $show_statistics=true, $bill=NULL){
		$arr=Array();
		
			$_bill=new BillInItem;
		if($bill===NULL) $bill=$_bill->GetItemById($id);
		$_bpf=new BillPosPMFormer;
		
			$_supplier=new SupplierItem;
		
		$nds=NDS;
		if($bill['is_incoming']==1) $nds=$_supplier->FindNDS($bill['supplier_id']);
		
		
		
		$_mf=new MaxFormer;
		$sql='select p.id as p_id, p.bill_id, p.komplekt_ved_pos_id, p.position_id as id, p.position_id as position_id,
					p.bill_id, p.out_bill_id, b.code as out_bill_code,

					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_pm, p.total,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 pm.discount_plus_or_minus, pm.discount_value, pm.discount_rub_or_percent, pm.discount_given, pm.discount_given_pdate, pm.discount_given_user_id, pm.discount_given_pdate as discount_given_pdate_unformatted,
					
					 p.sector_id, sec.name as sector_name,
					 p.komplekt_ved_id,
					 m.name_s as manager_name, m.login as manager_login
		
		from '.$this->tablename.' as p 
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join bill as b on b.id=p.out_bill_id
			left join sector as sec on p.sector_id=sec.id
			left join user as m on pm.discount_given_user_id=m.id
		where p.'.$this->subkeyname.'="'.$id.'" order by  p.id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//��������� +/-
			$f['has_pm']=(int)($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			
			$f['price_pm']=$pm['price_pm'];
			$f['cost']=$pm['cost'];
			$f['total']=$pm['total'];
			
			if(($f['has_pm']==1)&&($f['rub_or_percent']==1)&&($f['value']>0)){
				$f['value_from_percent']=/*round(($f['price_pm']*$f['value'])/100,2);*/((float)$f['price_pm']-(float)$f['price']);
			}elseif(($f['has_pm']==1)&&($f['rub_or_percent']==0)&&($f['value']>0)){
				$f['value_from_percent']=$f['value'];
			}else{
				$f['value_from_percent']=0;
			}
			
			if(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==1)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=round(($f['value_from_percent']*$f['discount_value'])/100,2);
			}elseif(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==0)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=$f['discount_value'];
			}else{
				$f['discount_value_from_percent']=0;
			}
			
			//������� ������������� ����/�����
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			//if($f['discount_plus_or_minus']=="") $f['discount_plus_or_minus']=0;
			if($f['discount_rub_or_percent']=="") $f['discount_rub_or_percent']=0;
			if($f['discount_value']=="") $f['discount_value']=0;
			
			
			//���������� ���
			$f['nds_proc']=$nds;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+$nds)/100)));
			
			/*
			'quantity_confirmed'=>$qua,
						'max_quantity'=>$qua,
						'in_rasp'=>0*/
			
			if($show_statistics){
				
			  $f['max_quantity']= $_mf->MaxForIncomingBill($f['out_bill_id'], $f['id'], $f['komplekt_ved_id'], $id); //round($_mf->MaxForBill($bill['komplekt_ved_id'], $f['id']),3); 
			  $f['quantity_confirmed']= $_mf->MaxInBill($f['out_bill_id'],$f['id'], $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id'] );//round($_mf->MaxInKomplekt($f['komplekt_ved_id'], $f['id']),3);
			 
			  $f['in_rasp']=round($_mf->MaxInInAcc($id, $f['id'],NULL, NULL, $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id']),3); 
			  
			  
			  
			
			}
			
			if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='������ � '.$f['komplekt_ved_id'];
			else $f['komplekt_ved_name']='-';
			
			
			$f['hash']=md5($f['position_id'].'_'.$f['storage_id'].'_'.$f['sector_id'].'_'.$f['komplekt_ved_id'].'_'.$f['out_bill_id']);
			
			
			
			if($f['discount_given_pdate']!=0) $f['discount_given_pdate']=date('d.m.Y H:i:s',$f['discount_given_pdate']);
			else $f['discount_given_pdate']='';
			
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	/*public function GetItemsByIdArr($id, $current_id=0, $show_statistics=true, $bill=NULL){
		$arr=Array();
		
		$_bill=new BillItem;
		if($bill===NULL) $bill=$_bill->GetItemById($id);
		$_bpf=new BillPosPMFormer;
		
		$_mf=new MaxFormer;
		$_pdm=new PlDisMaxValGroup;
		
		$sql='select p.id as p_id, p.bill_id, p.out_bill_id,  p.position_id as id, p.position_id as position_id,
					 p.pl_position_id as pl_position_id,
					 p.pl_discount_id, p.pl_discount_value, p.pl_discount_rub_or_percent,
					 d.name, b.code as out_bill_code,
					 
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_f, p.price_pm, p.total,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,
					 pm.discount_plus_or_minus, pm.discount_value, pm.discount_rub_or_percent, pm.discount_given, pm.discount_given_pdate, pm.discount_given_user_id, pm.discount_given_pdate as discount_given_pdate_unformatted,
					
					 m.name_s as manager_name, m.login as manager_login
		
		from '.$this->tablename.' as p 
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join pl_discount as d on p.pl_discount_id=d.id
			left join user as m on pm.discount_given_user_id=m.id
			left join bill as b on b.id=p.out_bill_id
		where p.'.$this->subkeyname.'="'.$id.'" order by position_name asc, p.out_bill_id asc,  id asc';
		
		
		
	
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//��������� +/-
			$f['has_pm']=(int)($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			
			$f['price_pm']=$pm['price_pm'];
			$f['cost']=round($f['price_f']*$f['quantity'],2);
			$f['total']=$pm['total'];
			
			if(($f['has_pm']==1)&&($f['rub_or_percent']==1)&&($f['value']>0)){
				$f['value_from_percent']=round(((float)$f['price_pm']-(float)$f['price_f']),2);
			}elseif(($f['has_pm']==1)&&($f['rub_or_percent']==0)&&($f['value']>0)){
				$f['value_from_percent']=$f['value'];
			}else{
				$f['value_from_percent']=0;
			}
			
			if(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==1)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=round(($f['value_from_percent']*$f['discount_value'])/100,2);
			}elseif(($f['has_pm']==1)&&($f['value']>0)&&($f['discount_rub_or_percent']==0)&&($f['discount_value']>0)){
				$f['discount_value_from_percent']=$f['discount_value'];
			}else{
				$f['discount_value_from_percent']=0;
			}
			
			//������� ������������� ����/�����
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			//if($f['discount_plus_or_minus']=="") $f['discount_plus_or_minus']=0;
			if($f['discount_rub_or_percent']=="") $f['discount_rub_or_percent']=0;
			if($f['discount_value']=="") $f['discount_value']=0;
			
			
			//���������� ���
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			
			
			if($show_statistics){
				
			//  $f['max_quantity']=round($_mf->MaxForBill($bill['komplekt_ved_id'], $f['id']),3); 
			//  $f['quantity_confirmed']=round($_mf->MaxInKomplekt($bill['komplekt_ved_id'], $f['id']),3);
			  
			  //$f['in_rasp']=round($_mf->MaxInShI($id, $f['id'],NULL,$f['storage_id'],$f['sector_id'],$f['komplekt_ved_id']),3);
			  $f['in_rasp']=round($_mf->MaxInShI($id, $f['position_id'], $f['pl_position_id'], $f['pl_discount_id'], $f['pl_discount_value'], $f['pl_discount_rub_or_percent'],   NULL, $f['out_bill_id']),3);
			}
			
			
			
			
			$f['hash']=md5($f['pl_position_id'].'_'.$f['position_id'].'_'.$f['pl_discount_id'].'_'.$f['pl_discount_value'].'_'.$f['pl_discount_rub_or_percent'].'_'.$f['out_bill_id']);
			
			
			if($f['discount_given_pdate']!=0) $f['discount_given_pdate']=date('d.m.Y H:i:s',$f['discount_given_pdate']);
			else $f['discount_given_pdate']='';
			
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}*/
	
}
?>