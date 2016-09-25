<?
require_once('abstractitem.php');
require_once('acc_pospmitem.php');
require_once('acc_item.php');
require_once('acc_in_item.php');
//require_once('billitem.php');
require_once('billpositem.php');

require_once('authuser.php');
require_once('actionlog.php');
require_once('acc_notesitem.php');

require_once('billnotesitem.php');

//абстрактный элемент
class AccInPosItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='acceptance_id';	
	}
	
	
	
	//добавить 
	public function Add($params, $pms=NULL,$change_high_mode=0,$change_low_mode=0){
		
		$code=AbstractItem::Add($params);
		
		if($pms!==NULL){
			//создать +/- для позиции
			$bpm=new AccPosPMItem;
			
			if($code>0){
				$pms['acceptance_position_id']=$code;
				$bpm->Add($pms);	
			}
		}
		$_acc=new AccInItem; $_bpi=new BillPosItem;
		
		if(isset($params['quantity'])&&isset($params['acceptance_id'])){
			$acc=$_acc->getitembyid($params['acceptance_id']);
			if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 'position_id'=>$item['position_id'], 
				'komplekt_ved_id'=>$params['komplekt_ved_id'],
				'out_bill_id'=>$params['out_bill_id']
				));	
				if(($bpi!==false)&&($bpi['quantity']==$params['quantity'])){
					$params['total']=$bpi['total'];	
				}elseif(($bpi!==false)){
					//решам проблему несход. копеек.
					$price=round($bpi['total']/$bpi['quantity'],10);
					$params['total']=round($params['quantity']*$price,2);
				}
				
			}
			
		}
		
		
		
		
		return $code;
	}
	
	
	//редактировать
	public function Edit($id,$params,$pms=NULL,$change_high_mode=0,$change_low_mode=0){
		//если меняем кол-во - поменять поле total
		//
		$_acc=new AccInItem; $_bpi=new BillPosItem;
		$item=$this->GetItemById($id);
		
		if(isset($params['quantity'])&&($params['quantity']!=$item['quantity'])){
			 if(isset($params['price_pm'])&&($params['price_pm']!=$item['price_pm'])) $price=$params['price_pm'];
			 else $price=$item['price_pm'];
			 
			  //прайс рассчитать из счета как тотал/кол-во, окр. до 10 знаков
			 //решаем проблему с несходящимися копейками при дроблении поступлений
			 $acc=$_acc->getitembyid($params['acceptance_id']);
			 if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 'position_id'=>$item['position_id'], 
				
				'komplekt_ved_id'=>$item['komplekt_ved_id'],
				'out_bill_id'=>$item['out_bill_id']
				));	
				if(($bpi!==false)){
					$price=round($bpi['total']/$bpi['quantity'],10);
					$params['price_pm']=$price;
					//echo $price; die();
				}
			 }
			
			
			
			 
			 $params['total']=$params['quantity']*$price;
		}
		
		//если кол-во=кол-ву по счету - значит тотал=тотал по счету...
			  
		if(isset($params['quantity'])&&isset($params['acceptance_id'])){
			$acc=$_acc->getitembyid($params['acceptance_id']);
			if($acc!==false){
				$bpi=$_bpi->GetItemByFields(array('bill_id'=>$acc['bill_id'], 'position_id'=>$item['position_id'], 
				'komplekt_ved_id'=>$item['komplekt_ved_id'],
				'out_bill_id'=>$item['out_bill_id']
				));	
				if(($bpi!==false)&&($bpi['quantity']==$params['quantity'])){
					$params['total']=$bpi['total'];	
				}
				
			}
			
		}
		
		AbstractItem::Edit($id,$params);
		
		if($pms!==NULL){
			//если уже есть пм, то найти иобработать его
			//если нет - то создать
			$_bpm=new AccPosPMItem;
			$bpm=$_bpm->GetItemByFields(array('acceptance_position_id'=>$id));
			if($bpm===false){
				$pms['acceptance_position_id']=$id;
				$_bpm->Add($pms);	
			}else{
				$pms['acceptance_position_id']=$id;
				$_bpm->Edit($bpm['id'],$pms);	
			}
		}
		
		
		
	}
	
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from acceptance_position_pm where acceptance_position_id='.$id.';';
		$it=new nonSet($query);
		
		
		parent::Del($id);
	}	
	
	
	
	
	public function SetChainQuantity($acceptance_id,$position_id, $komplekt_ved_id, $quantity,$change_high_mode=0,$change_low_mode=0, $_result=NULL, $item=NULL){
		//if(($change_high_mode==0)&&($change_low_mode==0)) return;
		
		
		$log=new ActionLog;
		$auth=new AuthUser;
		
		if($_result===NULL) $_result=$auth->Auth();
		
		
		$_ai=new AccInItem;
		if($item===NULL) $ai=$_ai->getitembyid($acceptance_id);
		else $ai=$item;
		
		
		
		if($ai!==false){
			$_bpi=new BillPosItem;
			$_bpmi=new BillPosPmItem;
			
			
			
			$bpi=$_bpi->GetItemByfields(array('bill_id'=>$ai['bill_id'], 'position_id'=>$position_id, 'komplekt_ved_id'=>$komplekt_ved_id));
			if($bpi!==false){
				//echo 'zzzzzzzzzzzzzzzzzz'; die();	
				//найдем количество в распоряжении
				//$bpi['quantity']
				
				
				$delta_rasp=0;
				//найдем количество в других поступлениях распоряжения
				$set=new MysqlSet('select sum(quantity) from acceptance_position where acceptance_id<>"'.$acceptance_id.'" and position_id="'.$position_id.'" 
					and komplekt_ved_id="'.$komplekt_ved_id.'"
				 
					 and acceptance_id in(select id from acceptance where bill_id="'.$bpi['bill_id'].'" and is_confirmed=1)');
				$rs=$set->getResult();
				
				$f=mysqli_fetch_array($rs);
				
				$delta_rasp=round(($quantity-($bpi['quantity']-(float)$f[0])),3);
				
				//echo ' вносим='.$quantity.'  в счете='.$bpi['quantity'].' в других реал='.(float)$f[0];
				//die();
				
				//превышение - это дельта >0
				//сокращение - это дельта <0
				//echo $delta_rasp; 
				
				if(($delta_rasp!=0)
				/*(($delta_rasp>0)&&($change_high_mode==1))||
				
				(($delta_rasp<0)&&($change_low_mode==1))*/
				){
					
				
					//echo ($delta_rasp); die;
					
					
					//правка счета....
					//во всех случаях, кроме понижения кол-ва и наличия дозавоза
					if(!(($delta_rasp<0)&&($change_low_mode==0))){
					
					  $_bill_positem=new BillInPosItem;
					  $_bill_pospmitem=new BillPosPMItem;
					  
					  
					  
					  $bill_positem=$_bill_positem->Getitembyfields(array('bill_id'=>$ai['bill_id'], 'position_id'=>$position_id, 'storage_id'=>0/*$ai['storage_id']*/, 'sector_id'=>0/*$ai['sector_id']*/, 'komplekt_ved_id'=>$komplekt_ved_id)); //добавить фильтры по складу, участку, заявке...
					  
					//  echo 'ищем позицию род.счета:'; var_dump(array('bill_id'=>$ai['bill_id'], 'position_id'=>$position_id, 'storage_id'=>0/*$ai['storage_id']*/, 'sector_id'=>0/*$ai['sector_id']*/, 'komplekt_ved_id'=>$komplekt_ved_id)); die();
					  
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
						  
						  //создать автопримечание в счете
						  $_ni=new BillNotesItem;
					  
						  $note='Количество позиции '.SecStr($bill_positem['name']).' автоматически выровнено при утверждении связанного поступления №'.$acceptance_id.' сотрудником '.$_result['name_s'].' ('.$_result['login'].')'.'. Первичное количество '.$bill_positem['quantity'].', конечное количество '.$new_bill_quantity;
						  
						  $_ni->Add(array(
							  'user_id'=>$ai['bill_id'],
							  'is_auto'=>1,
							  'pdate'=>time(),
							  'posted_user_id'=>$_result['id'],
							  'note'=>$note
						  
						  ));
						  
						  //запись в журнал счета - ,
						  $log->PutEntry($_result['id'],'автоматическое выравнивание позиции входящего счета при утверждении связанного поступления',  NULL, 610,NULL, $note, $ai['bill_id']);
					  
					  	  
						  //автопримечание в поступление
						  $_ani=new AccNotesItem;
						  $_bi=new BillInItem;
						  $bill=$_bi->GetItemById($ai['bill_id']);
						  $note='Количество позиции '.SecStr($bpi['name']).' во входящем счете №'.$bill['code'].' автоматически выровнено при утверждении поступления сотрудником '.$_result['name_s'].' ('.$_result['login'].')'.'. Первичное количество '.$bill_positem['quantity'].', конечное количество '.$new_bill_quantity;
						  
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