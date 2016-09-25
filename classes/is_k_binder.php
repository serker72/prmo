<?
require_once('abstractitem.php');

require_once('is_to_k_item.php');
require_once('is_to_k_group.php');
require_once('is_custom_item.php');

require_once('billitem.php');
require_once('acc_item.php');
require_once('ispositem.php');
require_once('billpospmformer.php');
require_once('isposgroup.php');
require_once('is_custom_item.php');
require_once('isnotesitem.php');
require_once('iswfpositem.php');

require_once('iswfposgroup.php');
require_once('iswf_group.php');
require_once('komplgroup.php');
require_once('komplitem.php');
require_once('maxformer.php');
require_once('authuser.php');

//привязка межсклада к заявке
class IsToKBinder{
	
	
	//комплексная функция проверки доступности заявки
	public function CheckKomplekt($is_id,$org_id,$quantity_field='quantity',&$komplekt_ved_ids){
		$res=false;
		
		
		
		
		$_ii=new IsCustomItem;
		
		$komplekt_ved_ids=array();	
		
		$item=$_ii->getitembyid($is_id);
		
		
		if($item['sender_sector_id']==$item['receiver_sector_id']){
			//$komplekt_ved_id=0;
			return true;
				
		}
		
		//
		
		$positions=$_ii->GetPositionsArr($is_id);
		//добавим поле- количество в найденных заявках
		//обнулим его
		foreach($positions as $k=>$v){
			$positions[$k]['finded_quantity']=0;	
		}
		
		
		$_kg=new KomplGroup; $_ki=new KomplItem; $_mf=new MaxFormer; $_iswf=new IswfGroup;
		
		//перебираем заявки
		$sql='select * from komplekt_ved where storage_id="'.$item['receiver_storage_id'].'" and sector_id="'.$item['receiver_sector_id'].'" and org_id="'.$org_id.'" and status_id in(2, 12) order by pdate asc';
	    
		//echo $sql;
		
		$set=new mysqlset($sql);
		
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);		
		
		   
			
			$kpositions=$_ki->GetPositionsArr($f['id']);
			//перебрать позиции заявки. если позиция в заявке (свободное кол-во) совпадает с текущей, то добавим в таблицу тек позиций свободное кол-во по заявке; добавим айди заявки в общий массив айди заявок
			foreach($kpositions as $k=>$v){
				
				if($this->PosInArr($v['position_id'], $positions, $key)){
					//echo 'zzz';
					
					// print_r($f);
					
					//ищем свободное количество по заявке
					$kquantity=$_mf->MaxForBill($f['id'],$v['position_id'],NULL,$is_id);		
					
					if($kquantity>0){
						//echo $kquantity.' ';
						//сколько нам еще нужно по межскладу...
						$delta=$positions[$key][$quantity_field]-$positions[$key]['finded_quantity'];
					
						//нам еще нужны позиции, берем из заявки
						if($delta>0){
							//ищем слагаемое, которое мы можем отнять из заявки и прибавить в межсклад
							if($delta>$kquantity){
								$delta=$kquantity;	
							}
							
							$positions[$key]['finded_quantity']=$positions[$key]['finded_quantity']+$delta;
							if(!in_array($f['id'],$komplekt_ved_ids)) $komplekt_ved_ids[]=$f['id'];
							
						}
					}
				}
			}
		}
		
		//проверить, во всех ли позициях
		//$positions[$key][$quantity_field]<=$positions[$key]['finded_quantity'];
		//если да - то вернуть true
		
		//print_r($positions);
		
		///echo $quantity_field;
		$is_in=true;
		foreach($positions as $k=>$v){
			if($v[$quantity_field]>$v['finded_quantity']*PPUP) $is_in=$is_in&&false;
		}
		$res=$is_in;
		//print_r($positions);
		
		return $res;
	}
	
	
	
	//построение таблицы привязки межсклада к заявке
	public function BindKomplekt($is_id,$org_id,$quantity_field='quantity'){
		//очистим таблицу по межскладу
		new NonSet('delete from interstore_to_komplekt where interstore_id="'.$is_id.'"');
			
		if($this->CheckKomplekt($is_id,$org_id,$quantity_field, $kvs)){
			
			 
			$_ik=new IsToKItem;
			$_ii=new IsCustomItem;
		
			$komplekt_ved_ids=array();	
			
			$item=$_ii->getitembyid($is_id);
			
			if($item['sender_sector_id']==$item['receiver_sector_id']){
				//$komplekt_ved_id=0;
				return true;
					
			}
			
			$positions=$_ii->GetPositionsArr($is_id);
			//добавим поле- количество в найденных заявках
			//обнулим его
			foreach($positions as $k=>$v){
				$positions[$k]['finded_quantity']=0;	
			}
			
			
			$_kg=new KomplGroup; $_ki=new KomplItem; $_mf=new MaxFormer; $_iswf=new IswfGroup;
			
			//перебираем заявки
			$sql='select * from komplekt_ved where storage_id="'.$item['receiver_storage_id'].'" and sector_id="'.$item['receiver_sector_id'].'" and org_id="'.$org_id.'" and status_id in(2, 12) order by pdate asc';
			
			$set=new mysqlset($sql);
			
			
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);		
			
				$kpositions=$_ki->GetPositionsArr($f['id']);
				//перебрать позиции заявки. если позиция в заявке (свободное кол-во) совпадает с текущей, то добавим в таблицу тек позиций свободное кол-во по заявке; добавим айди заявки в общий массив айди заявок
				foreach($kpositions as $k=>$v){
					
					if($this->PosInArr($v['position_id'], $positions, $key)){
						//ищем свободное количество по заявке
						$kquantity=$_mf->MaxForBill($f['id'],$v['position_id'],NULL,$is_id);		
						
						if($kquantity>0){
						
							//сколько нам еще нужно по межскладу...
							$delta=$positions[$key][$quantity_field]-$positions[$key]['finded_quantity'];
						
							//нам еще нужны позиции, берем из заявки
							if($delta>0){
								//ищем слагаемое, которое мы можем отнять из заявки и прибавить в межсклад
								if($delta>$kquantity){
									
									//if($delta>$kquantity)
									
									
									
									
									if($delta>($kquantity+(PPUP-1)*$kquantity)){
										
										
										
										$delta=$kquantity+(PPUP-1)*$kquantity;
									}else{
										//нужен какой-то довесок к дельте, т.к. 10% превышение даст избыточный товар в счете и т.д.
										 
										
										$delta=$kquantity+($delta-$kquantity);
										 
									}
									
								}
								
								/*if($delta>$kquantity){
									$delta=$kquantity;	
								}*/
								
								
								$positions[$key]['finded_quantity']=$positions[$key]['finded_quantity']+$delta;
								$iparams=array();
								$iparams['interstore_id']=$is_id;
								$iparams['komplekt_ved_id']=$f['id'];
								
								$iparams['position_id']=$v['position_id'];
								$iparams['quantity']=$delta;
								
								$_ik->Add($iparams);
								
								
								
							}
						}
					}
				}
			}
				
			
		}
			
	}
	
	
	
	
	
	
	
	protected function PosInArr($pos_id, $where, &$key){
		$key=NULL;
		$res=false;
		foreach($where as $k=>$v){
			if($pos_id==$v['id']){
				$key=$k;
				$res=true;
				break;	
			}
		}
		
		return $res;
	}
}
?>