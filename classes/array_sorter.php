<?

//сортировка массива по числовому значению
class ArraySorter{
	
	
	//сортировка массива
	static public function SortArr($arr, $fieldname, $direction){
		$result=array();
		
		
		
		while(count($arr)>0){
			if($direction==0){
				//min
				$a=self::FindMin($arr, $fieldname, $index);
				if($index>-1){
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}else{
				//max
				$a=self::FindMax($arr, $fieldname, $index);
				
				if($index>-1){
					
					$result[]=$a;
					unset($arr[$index]);
				}else array_pop($arr);
			}
			
		}
		
		
		return $result;	
	}
	
	
	
	static protected function FindMin($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$minval=999999999999999999999999999999999999999;
		foreach($arr as $k=>$v){
			if($v[$fieldname]<$minval){
				$minval=$v[$fieldname];
				$res=$v;
				$index=$k;	
			}
			
		}
		
			
		return $res;
	}
	
	static protected function FindMax($arr, $fieldname, &$index){
		$index=-1;
		$res=array();
		$maxval=-999999999999999999999;
		foreach($arr as $k=>$v){
			
			if($v[$fieldname]>$maxval){
				
				$maxval=$v[$fieldname];
				$res=$v;
				$index=$k;	
				
			}
			
		}
		
			
		return $res;
	}

}

?>