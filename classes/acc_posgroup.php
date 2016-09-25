<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('maxformer.php');
require_once('acc_item.php');
require_once('billpositem.php');



// абстрактная группа
class AccPosGroup extends AbstractGroup {
	protected static $uslugi;
	
	protected static $semi_uslugi;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance_position';
		$this->pagename='view.php';		
		$this->subkeyname='acceptance_id';	
		$this->vis_name='is_shown';		
		
		if(self::$uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		  self::$uslugi/*$this->uslugi*/=array();
		  self::$uslugi/*$this->uslugi*/[]=SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$uslugi/*$this->uslugi*/)) self::$uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$uslugi/*$this->uslugi*/))  self::$uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
		
		if(self::$semi_uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SEMI_SERVICE_CODE); // услуги
		  self::$semi_uslugi/*$this->uslugi*/=array();
		  self::$semi_uslugi/*$this->uslugi*/[]=SEMI_SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$semi_uslugi/*$this->uslugi*/)) self::$semi_uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$semi_uslugi/*$this->uslugi*/))  self::$semi_uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0,$has_goods=true,$has_usl=true, $show_statiscits=true, $show_boundaries=true){
		$arr=Array();
		
		
		
		$_bpf=new BillPosPMFormer;
		$sql='select p.id as p_id, p.acceptance_id, p.komplekt_ved_pos_id, p.position_id as id,
					 p.name as position_name, p.dimension as dim_name, 
					 p.quantity, p.price, p.price_pm, p.total, p.komplekt_ved_id,
					 pd.id as dimension_id,
					 pm.plus_or_minus, pm.value, pm.rub_or_percent,	
					 p.acceptance_in_id,		 
					 cat.group_id
		from '.$this->tablename.' as p 
			left join acceptance_position_pm as pm on pm.acceptance_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join catalog_position as cat on cat.id=p.position_id
		where p.'.$this->subkeyname.'="'.$id.'" order by p.id asc';
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_bpi=new BillPosItem;
		$_ac=new AccItem;
		$_mf=new MaxFormer;
		$ac=$_ac->GetItemById($id);
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			//исключить позиции с услугами
			if(!$has_usl){
				if($this->IsUsl($f['group_id'])||$this->IsSemiUsl($f['group_id'])) continue;	
			}
			
			//исключить позиции с товарами
			if(!$has_goods){
				if(!$this->IsUsl($f['group_id'])&&!$this->IsSemiUsl($f['group_id'])) continue;		
			}
			
			
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
			$f['has_pm']=($f['plus_or_minus']!="");
			
			$pm=$_bpf->Form($f['price'],$f['quantity'],$f['has_pm'],$f['plus_or_minus'],$f['value'],$f['rub_or_percent'],$f['price_pm'],$f['total']);
			
			$f['price_pm']=$pm['price_pm'];
			
			
			//получить из счета цену_пм как окр. до 10 знаков ст-ть/кол-во по счету
			$bpi=$_bpi->GetItemByFields(array('bill_id'=>$ac['bill_id'], 'position_id'=>$f['id'], 'storage_id'=>$ac['storage_id'], 'sector_id'=>$ac['sector_id'],
				
				'komplekt_ved_id'=>$f['komplekt_ved_id']
				));	
			if($bpi!==false){
				//устраняем потерю копеек при округлении - берем цену из счета с макс. точностью до 10 знаков!
				$some_price_pm=round($bpi['total']/$bpi['quantity'],10);
				
				$f['price_pm_unf']=$some_price_pm;	
			}else{
				$f['price_pm_unf']=	$pm['price_pm'];
			}
			
			
			$f['cost']=$pm['cost'];
			$f['total']=$pm['total'];
			
			//обнулим незаполненный плюс/минус
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			
			if($show_statiscits){
			 //	echo ' zzzzZZZ ';
			  $f['in_bill']=round($_mf->MaxInBill($ac['bill_id'], $f['id'],$ac['storage_id'],$ac['sector_id'],$f['komplekt_ved_id']),3);
				  $f['in_rasp']=round($_mf->MaxInShI($ac['bill_id'], $f['id'],$ac['sh_i_id'],$ac['storage_id'],$ac['sector_id'],$f['komplekt_ved_id']),3);
				  $f['in_acc']=round($_mf->MaxInAcc($ac['bill_id'], $f['id'],$id,$ac['sh_i_id'],$ac['storage_id'],$ac['sector_id'],$f['komplekt_ved_id']),3);
			}
			
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			
			$f['nds_price']=sprintf("%.2f",($f['price_pm']-$f['price_pm']/((100+NDS)/100)));
			
			if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
			else $f['komplekt_ved_name']='-';
			
			$f['hash']=md5($f['id'].'_'.$f['komplekt_ved_id'].'_'.$f['acceptance_in_id']);
			
			
			
			
			//добавлены для контроля редактирования числа позиций поступления
			//если это поступление по распоряжению на приемку...
			if(($ac['inventory_id']==0)&&($ac['interstore_id']==0)){
				if($show_statiscits){
				  $f['max_quantity']=$_mf->MaxForAcc($ac['bill_id'], $f['id'], $id, $f['komplekt_ved_id']);
			  
				  //всего в соответствующей строке счета
				  $f['max_bill_quantity']=$_mf->MaxInBill($ac['bill_id'],$f['id'],$ac['storage_id'],$ac['sector_id'],$f['komplekt_ved_id']);
				  
				  //всего в соотв. строке заявки
				  $f['max_komplekt_quantity']=$_mf->MaxInKomplekt($f['komplekt_ved_id'],$f['id']);
				  
				   
				  //всего доступно согласно поступлениям
				  $f['max_incoming_quantity']=$_mf->MaxForAccByAccIn($ac['bill_id'], $f['id'], $id, NULL, $ac['storage_id'],$ac['sector_id'], $f['komplekt_ved_id'], $f['acceptance_in_id']);
					
				}
			}elseif($ac['inventory_id']!=0){
				
				if($show_statiscits){
				//если это поступление по распоряжению на инвентаризацию
				
					$f['max_quantity']=$_mf->MaxForAcc($ac['bill_id'], $f['id'], $id, $f['komplekt_ved_id']);
			  
				  //всего в соответствующей строке счета
				  $f['max_bill_quantity']=$_mf->MaxInBill($ac['bill_id'],$f['id'],$ac['storage_id'],$ac['sector_id'],$f['komplekt_ved_id']);
				  
				  //всего в соотв. строке заявки
				  $f['max_komplekt_quantity']=$f['max_quantity'];
				  
				}
			
			}elseif($ac['interstore_id']!=0){
				
				if($show_statiscits){
				//если это поступление по распоряжению на межсклад
					
				  $f['max_quantity']=$_mf->MaxForAccIs($ac['interstore_id'], $f['id'], $f['p_id'], $f['komplekt_ved_id']);
					
				  //всего в соответствующей строке счета
				  $f['max_bill_quantity']=$f['max_quantity']; 
				 
				  //всего в соотв. строке заявки
				  $f['max_komplekt_quantity']=$f['max_quantity'];
				  
				}
			
			}
		  
			 
			
			$f['is_usl']=(int)$this->IsUsl($f['group_id']);
			
			$f['is_semi_usl']=(int)$this->IsSemiUsl($f['group_id']);
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//принадлежит ли данная категория категории услуг
	protected function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная категория категории услуг по заявкам
	protected function IsSemiUsl($id){
		return in_array($id,self::$semi_uslugi/*$this->uslugi*/);
	}
	
}
?>