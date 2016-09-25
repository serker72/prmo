<?
require_once('abstractgroup.php');
require_once('maxformer.php');

// абстрактная группа
class KomplPosGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_pos';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsByIdArr($id,$current_id=0,$do_find_max=false, $dec2=NULL, $sortmode=0){
		$mf=new MaxFormer;
		
		$arr=array();
		
		$db_flt='';
		
		if($dec2!==NULL){
		  $db_flt=$dec2->GenFltSql(' and ');
		  if(strlen($db_flt)>0){
			  $db_flt=' and '.$db_flt;
		  //	$sql_count.=' and '.$db_flt;	
		  }
		}
		
		
		$ord='';
		if($sortmode==0){
			//$ord='order by position_name asc, id asc';
			$ord='order by p.id asc';
		}elseif($sortmode==1){
			$ord='order by pos.id asc';
		}elseif($sortmode==2){
			$ord='order by pos.id desc';
		}elseif($sortmode==3){
			$ord='order by position_name asc';
		
		}elseif($sortmode==4){
			$ord='order by position_name desc';
		}else{
			$ord='order by position_name asc, id asc';
		}
		
		
		
		$sql='select p.*, pos.name as position_name, dim.name as dim_name, dim.id as dimension_id,  kp.sector_id
		from '.$this->tablename.' as p 
		inner join catalog_position as pos on p.position_id=pos.id 
		left join catalog_dimension as dim on pos.dimension_id=dim.id 
		 
		left join komplekt_ved as kp on kp.id=p.komplekt_ved_id
		where '.$this->subkeyname.'="'.$id.'" '.$db_flt.' '.$ord;
		
		//echo $sql.' <br>';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			if($do_find_max){
				
				
				//входящие документы
				$_in_acc_in=$mf->InAccIn($id,$f['position_id']);
				 
				
				$f['in_bills_in']=round($mf->InBillsIn($id,$f['position_id'])-$_in_acc_in,3); //-$mf->InSh($id,$f['position_id']);
				 
				$f['in_free_in']=round($mf->InFreeIn($id, $f['position_id']),3);
				$f['in_pol_in']=$_in_acc_in;
				
				
				
				
				
				
				
				//исход. документы
				$_in_acc=$mf->InAcc($id,$f['position_id']);
				 
				
				$f['in_bills']=round($mf->InBills($id,$f['position_id'])-$_in_acc,3); //-$mf->InSh($id,$f['position_id']);
				 
				$f['in_free']=round($mf->InFree($id, $f['position_id']),3);
				$f['in_pol']=$_in_acc;
				
				
				
				//итог. рез-т 
				$f['itog']=$f['in_pol']-$f['in_pol_in']; //%{$in_pol-$in_pol_in}%
				
			}else{
				//echo 'zzzzzzzzzzz';
				$f['in_bills']='-';
				
				$f['in_free']='-';
				$f['in_pol']='-';
				
				
				$f['in_bills_in']='-';
				
				$f['in_free_in']='-';
				$f['in_pol_in']='-';
				
				$f['itog']='-';
			}
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
}
?>