<?
require_once('billdates.php');

class KomplDates extends BillDates{
	public $prazdnik1=array();
	
	
	
	public function FindMinPer($delay_in_work_days, $begin_date){
		$per=0;
		$curr_date=$begin_date;
		
		
		for($i=0; $i<$delay_in_work_days; $i++){
			 
			 $curr_date+=24*60*60;
			 
			 if(in_array($curr_date,$this->prazdnik)){
				$i--;	 
			 }
			 
			 
			 
		}
		$per=$curr_date-$begin_date;
		
		foreach($this->prazdnik as $k=>$v){
			$this->prazdnik1[]=date('d.m.Y H:i:s', $v);	
		}
		
		return $per;
			
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
		
		
		//sb, vs
		$chk_date=mktime(0,0,0,1,1,$year);
		for($i=0; $i<=365; $i++){
			
			$dn=date('N',$chk_date);
			if(($dn==6)||($dn==7)) $this->prazdnik[]=$chk_date;
			$chk_date+=24*60*60;
		}
	}
	
};
?>