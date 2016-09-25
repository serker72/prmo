<?
require_once('abstractitem.php');
require_once('supplieritem.php');

//абстрактный элемент
class BillPosPMFormer{
	
	
	//считаем и возвращаем массив из цен +/-, стоимости и стоимости +/-
	public function Form($price, $quantity, $has_pm, $plus_or_minus, $value, $rub_or_percent,$price_pm, $total){
		$v=array();
		
		
		/*
		$v['price_pm']=0;
			$v['cost']=0;
			$v['total']=0;*/
		
		
		/*if($has_pm){
			
			$slag=0;
			if($rub_or_percent==0){
				$slag=$value;
			}else{
				$slag=$price*$value/100.0;
			}
			
			if($plus_or_minus==1){ //$slag=-1.0*$slag;
				$v['price_pm']=$price-$slag;
			}else{
				$v['price_pm']=$price+$slag;
			}
			
			$v['cost']=$price*$quantity;
			
			$v['total']=$v['price_pm']*$quantity;
		}else{
			$v['price_pm']=$price;
			$v['cost']=$price*$quantity;
			$v['total']=$v['cost'];
			
		}*/
		
		
		$v['price_pm']=$price_pm;
			$v['cost']=$price*$quantity;
			$v['total']=$total;
		
			
		return $v;
	}
	
	
	
	public function FormDiscount($price, $quantity, $has_pm, $plus_or_minus, $value, $rub_or_percent,$price_pm, $total, $discount_value, $discount_rub_or_percent){
		$v=array();
		
		$slag=0;
		$slag_un=0;
		if($has_pm){
			
			$slag=0;
			if($rub_or_percent==0){
				$slag=$value;
			}else{
				$slag=$price*$value/100.0;
			}
			
			$slag_un=$slag;
			if($plus_or_minus==1){ //$slag=-1.0*$slag;
				//$v['price_pm']=$price-$slag;
				$slag=-1.0*$slag;
			}else{
				//$v['price_pm']=$price+$slag;
			}
			
			$v['price_pm']=$price+$slag;
			
			$v['cost']=$price*$quantity;
			
			$v['total']=$v['price_pm']*$quantity;
		}else{
			$v['price_pm']=$price;
			$v['cost']=$price*$quantity;
			$v['total']=$v['cost'];
			
		}
		
		if($discount_rub_or_percent==0){
			//рубли	
			$v['discount_amount']=$discount_value;
		}else{
			//%
			$v['discount_amount']=$discount_value*$slag_un/100.0;
		}
		
		
		
		return $v;
	}
	
	
	
	
	
	//считаем общую стоимость по введенным позици€м
	public function CalcCost(array $positions, $changed_totals=NULL){
		$cost=0;
		foreach($positions as $k=>$v){
			
			//$stru=$this->Form($v['price'],$v['quantity'],$v['has_pm'],$v['plus_or_minus'],$v['value'],$v['rub_or_percent'],$v['price_pm'],$v['total']);
			//$cost+=$stru['total'];
			
			
			//Ё“ќ —ƒ≈ЋјЌќ ƒЋя ѕ–≈ƒ¬ј–»“≈Ћ№Ќќ√ќ јЌјЋ»«ј —”ћћџ ѕќ—“”ѕЋ≈Ќ»я при смене +/- в счете
			//перебрать changed_totals, если нашли совпадение по всем трем параметрам - то тотал считаем как прайс_пм из changed_totals*кол-во  
			
			$was_in=false; $was_in_index=-1;		
			if($changed_totals!==NULL){
				//echo 'zzzzzzzzzzzzzzzz';
				foreach($changed_totals as $kk=>$vv){
					//print_r($vv); print_r($v);
					
					if(($v['id']==$vv['position_id'])&&($v['komplekt_ved_id']==$vv['komplekt_ved_id'])){
						$was_in=true;
						$was_in_index=$kk;
						break;	
					}
				}
			}
			
			
			
			if(($changed_totals!==NULL)&&($was_in)){
				
				//var_dump( $changed_totals[$was_in_index]['price_pm']);
				
				$cost+=$changed_totals[$was_in_index]['price_pm']*$v['quantity'];
				
			}else {
				$stru=$this->Form($v['price'],$v['quantity'],$v['has_pm'],$v['plus_or_minus'],$v['value'],$v['rub_or_percent'],$v['price_pm'],$v['total']);
			
				$cost+=$stru['total'];
			}
				
		}
		
		return $cost;	
	}
	
	
	//расчет % Ќƒ— дл€ счета
	public function CalcNDS(array $positions, $is_incoming=false, $supplier_id=0){
		$cost=0;
		
		
		$nds=NDS;
		
		if($is_incoming){
			$_supplier=new SupplierItem;
			$supplier=$_supplier->getitembyid($supplier_id);
			
			if($supplier['is_upr_nalog']==1) $nds=0;
		}
		
		$cost=sprintf("%.2f",($this->CalcCost($positions)-$this->CalcCost($positions)/((100+$nds)/100)));
		
		return $cost;	
	}
	
}
?>