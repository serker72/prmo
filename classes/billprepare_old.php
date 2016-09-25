<?

require_once('maxformer.php');
require_once('kpitem.php');

// класс для редактирования позиций счета на основе позиций прайслиста
class BillPrepare {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='pl_position';
		$this->pagename='view.php';		
		$this->subkeyname='pl_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
	//список позиций
	public function GetItemsByIdArr(array $pos, $id=0,$do_find_max=false){
		$arr=array();
		
		$mf=new MaxFormer;
		$_pdm=new PlDisMaxValGroup;
		$_kp=new KpItem;
		
		//нам нужно получить список позиций в счете по набору позиций
		//$id - это айди счета, его может не быть
		//$pos - это набор уже введенных позиций, может быть пустым
		foreach($pos as $k=>$vv){
			$v=explode(';',$vv);
			$sql='select 
				p.id as pl_position_id,  p.price as price, p.discount_id as pl_discount_id,	p.discount_value as pl_discount_value, p.discount_rub_or_percent as pl_discount_rub_or_percent,
				
				pos.name as position_name, pos.id as position_id,
				
				dim.name as dim_name, dim.id as dimension_id 
				
				
			from pl_position as p 
				inner join catalog_position as pos on p.position_id=pos.id 
				left join catalog_dimension as dim on pos.dimension_id=dim.id 
				
				where p.id="'.$v[0].'" order by position_name asc, p.id asc';
				
				//echo $sql.'<br>';
				
				$set=new mysqlset($sql);
				$rs=$set->getResult();
				$rc=$set->getResultNumRows();
				$h=mysqli_fetch_array($rs);
				//print_r($h);
				
				//также получить набор макс. скидок для позиции
				$max_vals=array();
				$max_vals=$_pdm->GetItemsByIdArr($h['pl_position_id']);
				
				$kp=$_kp->getitembyid($v[18]);
				
				//if($qua>0){
					$in_free=$mf->InFree($id,$f['position_id'],$_c_id);
			
					if($do_find_max){
						
						//echo 'ZZZZZZZZZZZZ';
						
						$h['in_bills']=$mf->InBills($id,$h['position_id'])-$mf->InSh($id,$h['position_id']);
						$h['in_sh']=$mf->InSh($id,$h['position_id'])-$mf->InAcc($id,$h['position_id']);
						$h['in_free']=$in_free;
						$h['in_pol']=$mf->InAcc($id,$h['position_id']);
					}else{
						$h['in_bills']='-';
						$h['in_sh']='-';
						$h['in_free']='-';
						$h['in_pol']='-';
					}
					
				 
				  
				  $arr[]=array(
					  'pl_position_id'=>$h['pl_position_id'],
					  'position_id'=>$h['position_id'],
					  'hash'=>md5($h['pl_position_id'].'_'.$h['position_id'].'_'.$h['pl_discount_id'].'_'.$h['pl_discount_value'].'_'.$h['pl_discount_rub_or_percent'].'_'.$v[18]),
					  'position_name'=>$h['position_name'],
					  'dim_name'=>$h['dim_name'],
					  'dimension_id'=>$h['dimension_id'],
					  'quantity'=>$qua,
					  'price'=>0,
					  'price_f'=>0,
					  'price_pm'=>0,
					  'has_pm'=>false,
					  'cost'=>0,
					  'total'=>0,
					  'plus_or_minus'=>0,
					  'rub_or_percent'=>0,
					  'value'=>0,
					  
					  'discount_rub_or_percent'=>0,
					  'discount_value'=>0,
					  'nds_proc'=>NDS,
					  'nds_summ'=>0,
					  'pl_discount_id'=>	$h['pl_discount_id'],
					  'pl_discount_value'=>	$h['pl_discount_value'],
					  'pl_discount_rub_or_percent'=>	$h['pl_discount_rub_or_percent'],
					  'discs1'=>$max_vals,
					  
					  'in_bills'=>$h['in_bills'],
					  'in_sh'=>$h['in_sh'],
					  'in_free'=>$h['in_free'],
					  'in_pol'=>$h['in_pol'],
					  
					  'kp_id'=>$v[18],
					  'kp_code'=>$kp['code']
					  
											  
				  );
				//}
		
		
		}
		
		
		return $arr;
	}
	
	
	
	
}
?>