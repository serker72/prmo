<?
require_once('abstractgroup.php');
require_once('billpospmformer.php');
require_once('maxformer.php');
require_once('billitem.php');
require_once('supplieritem.php');

// абстрактная группа
class BillPosGroup extends AbstractGroup {
		protected static $uslugi;
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill_position';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
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
		
	}
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $show_statistics=true, $bill=NULL){
		$arr=Array();
		
			$_bill=new BillItem;
		if($bill===NULL) $bill=$_bill->GetItemById($id);
		$_bpf=new BillPosPMFormer;
		
	
		
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
					 m.name_s as manager_name, m.login as manager_login,
					 cat.group_id
		
		from '.$this->tablename.' as p 
			left join bill_position_pm as pm on pm.bill_position_id=p.id
			left join catalog_dimension as pd on pd.name=p.dimension
			left join catalog_position as cat on cat.id=p.position_id
			left join bill as b on b.id=p.out_bill_id
			left join sector as sec on p.sector_id=sec.id
			left join user as m on pm.discount_given_user_id=m.id
		where p.'.$this->subkeyname.'="'.$id.'" order by p.id asc';
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//формируем +/-
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
			
			//обнулим незаполненный плюс/минус
			if($f['plus_or_minus']=="") $f['plus_or_minus']=0;
			if($f['rub_or_percent']=="") $f['rub_or_percent']=0;
			if($f['value']=="") $f['value']=0;
			
			//if($f['discount_plus_or_minus']=="") $f['discount_plus_or_minus']=0;
			if($f['discount_rub_or_percent']=="") $f['discount_rub_or_percent']=0;
			if($f['discount_value']=="") $f['discount_value']=0;
			
			
			//рассчитаем ндс
			$f['nds_proc']=NDS;
			
			$f['nds_summ']=sprintf("%.2f",($f['total']-$f['total']/((100+NDS)/100)));
			
			/*
			'quantity_confirmed'=>$qua,
						'max_quantity'=>$qua,
						'in_rasp'=>0*/
			
			if($show_statistics){
				
			  $f['max_quantity']=round($_mf->MaxForBill($bill['komplekt_ved_id'], $f['id']),3); 
			  $f['quantity_confirmed']=round($_mf->MaxInKomplekt($f['komplekt_ved_id'], $f['id']),3);
			 
			  $f['in_rasp']=round($_mf->MaxInAcc($id, $f['id'],NULL, NULL, $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id']),3);   
			  
			  
			  $f['in_rasp_in']=round($_mf->MaxInAccIn($id, $f['id'], 0,  NULL, $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id']),3);   
			  
			  
			  //для формирования реализации:
			  // в счете - сумма(в реализациях),
			  //но не больше, чем:
			  //сумма(в поступлениях) - сумма(в реализациях)
			  
			   
			  $in_free=$f['quantity']-$f['in_rasp'];
			 
			 
			  if(!$this->IsUsl($f['group_id'])){
				  if( $in_free> ($f['in_rasp_in'] - $f['in_rasp'])) $in_free=$f['in_rasp_in'] - $f['in_rasp'];
			  }
			 /*  if($this->IsUsl($f['group_id'])){
				   echo $f['in_rasp_in'];
			   }*/
			  
			  if($in_free<0) $in_free=0;
			  
			  $f['in_free']=$in_free;
			  
			  
			  //комплексное поле "доступно для формирования реализации"
			  //$f['in_free_complex']
			  /* структура: список пар (id поступления 1, доступное к-во 1), (id поступления 2, доступное к-во 2), ...*/
			  //услуга: $f['quantity']-$f['in_rasp']; (0, доступное к-во)
			  //НЕ услуга: получить список поступлений, по нему - свободное количество данной позиции
			  //в формате нашей структуры
			  //($f['in_rasp_in'] - $f['in_rasp']) 
			  /*
			  MaxInAccInComplex
			  */
			  if(!$this->IsUsl($f['group_id'])){
				 $f['in_free_complex']= $_mf->FreeInAccInComplex($id, $f['id'], 0,  NULL, $f['storage_id'],$f['sector_id'], $f['komplekt_ved_id']);
				  
				 //print_r( $f['in_free_complex']);
				 
				 //собрать вывод в удобный для формы вид:
				 $_in_free_complex=array();
				 foreach($f['in_free_complex'] as $vv){
					$_in_free_complex[]='('.$vv[0].','.$vv[1].')'; 
				 }
				 $f['in_free_complex']=implode(';',$_in_free_complex);
				 
			  }else{
				$f['in_free_complex']='(0,'. ($f['quantity']-$f['in_rasp']).')';
			  }
			  
			  
			  
			  
			  
			  
			  
			  //сколько всего позиций по род. исход. счету?
			  $f['quantity_in_parent_bill']=round($_mf->MaxInBill($f['out_bill_id'],  $f['id'], NULL, NULL,$f['komplekt_ved_id'] ),3);
			  //для формирования вход. счета - получить свободное количество по исх. счету (не занятое в других счетах)
			  $f['max_for_incoming_quantity']=$_mf->MaxForIncomingBill($id,$f['id'],$f['komplekt_ved_id']);
			  //а для не формирования????
			}
			
			if($f['komplekt_ved_id']!=0) $f['komplekt_ved_name']='Заявка № '.$f['komplekt_ved_id'];
			else $f['komplekt_ved_name']='-';
			
			
			if($bill['is_incoming']==1) $f['hash']=md5($f['position_id'].'_'.$f['storage_id'].'_'.$f['sector_id'].'_'.$f['out_bill_id']);
			else $f['hash']=md5($f['position_id'].'_'.$f['storage_id'].'_'.$f['sector_id'].'_'.$f['komplekt_ved_id']);
			
			
			
			if($f['discount_given_pdate']!=0) $f['discount_given_pdate']=date('d.m.Y H:i:s',$f['discount_given_pdate']);
			else $f['discount_given_pdate']='';
			
			
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	//принадлежит ли данная категория категории услуг
	public function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
	
}
?>