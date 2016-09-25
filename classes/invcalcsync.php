<?
require_once('abstractitem.php');

require_once('invcalcitem.php');

class InvCalcSync{
	
	
	//функция внесения изменений
	public function PutChanges($id){
		$_au1=new AuthUser;
		$result1=$_au1->Auth();
		$log=new ActionLog;
		
		$_ii=new InvCalcItem;
		$item=$_ii->GetItemById($id);
		
		$_bill=new BillItem;
		$_sh_i=new ShIItem;
		$_acc=new AccItem;
		$_wf=new WfItem;
		
		if($item!==false){
			
			
		}
		
		
		
		$reasons=implode(",<br />",$_reasons);
		return $res;
	}
	
	
	//функция проверки разницы между позициями акта и позициями всех подч док
	public function HasNotDifference($id, &$reasons){
		$res=true; 	//нет разницы
		
		$reasons=''; $_reasons=array();
		
		
		
		
		$reasons=implode(",<br />",$_reasons);
		return $res;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//проверка, есть ли такая позиция в массиве
	/*protected function IsInPos($position_id, $haystack, $keyname='position_id'){
		$res=-1;
		
		foreach($haystack as $k=>$v){
			if($v[$keyname]==$position_id){
				$res=$k;
				break;	
			}
		
		}
		return $res;
	}*/
}
?>