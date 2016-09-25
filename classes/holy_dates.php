<?

require_once('billdates.php');
class HolyDates extends BillDates{
	
	
	
	public function IsHolyday($pdate){
		$res=false;	
		
		if(in_array($pdate,$this->prazdnik)){
				return true;
		}
		 if((date("w",$pdate)==0)||(date("w",$pdate)==6)) return true;
		 
		return $res;
	}
	
	
	
	/*
	
	protected $prazdnik=array();
	
	
	function __construct(){
		//заполним массив праздников
		$years=array( ((int)date("Y")-1),  date("Y"), ((int)date("Y")+1));
		
		foreach($years as $k=>$v){
			$this->FindPrazdnik($v);	
		}
		
		
		
	}
	
	
	
	public function FindEthalon($pdate,$delay,$mode){
		
		$curr_date=$pdate;
			
		while($curr_date<($pdate+$delay*24*60*60)){
			
		  if($mode==0){
			  //сравнивать еще и с праздниками
			  if(in_array($curr_date,$this->prazdnik)){
				$delay++;  
			  }
			  
			  //проверять субботу и воскресенье
			  if((date("w",$curr_date)==0)||(date("w",$curr_date)==6)) $delay++; 
			  
		  }
			
		  $curr_date+=24*60*60;
		}
		
		return $curr_date;
	}
	
	
	
	
	protected function FindPrazdnik($year){
		//ng
		for($i=1; $i<=7; $i++){
			$this->prazdnik[]=mktime(0,0,0,1,$i,$year);
			
		}
		
		//23.02
		$this->prazdnik[]=mktime(0,0,0,2,23,$year);
		
		//08.03
		$this->prazdnik[]=mktime(0,0,0,3,8,$year);
		
		//1.5
		$this->prazdnik[]=mktime(0,0,0,5,1,$year);
		$this->prazdnik[]=mktime(0,0,0,5,2,$year);
		
		//9.5
		$this->prazdnik[]=mktime(0,0,0,5,9,$year);
		
		//12.06
		$this->prazdnik[]=mktime(0,0,0,6,12,$year);
		
		//4.11
		$this->prazdnik[]=mktime(0,0,0,11,4,$year);
		
		
		
	}*/
	
};
?>