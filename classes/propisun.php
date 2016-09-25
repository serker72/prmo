<?

class PropisUn{
	
	function propis($rub){
		$ish=$rub;
		
$dop0 = Array("","òûñÿ÷","ìèëëèîíîâ","ìèëëèàğäîâ");
$dop1 = Array("","òûñÿ÷à","ìèëëèîí","ìèëëèàğä");
$dop2 = Array("","òûñÿ÷è","ìèëëèîíà","ìèëëèàğäà");
$s1 = Array("","îäèí","äâà","òğè","÷åòûğå","ïÿòü","øåñòü","ñåìü","âîñåìü","äåâÿòü");
$s11 = Array("","îäíà","äâå","òğè","÷åòûğå","ïÿòü","øåñòü","ñåìü","âîñåìü","äåâÿòü");
$s2 = Array("","äåñÿòü","äâàäöàòü","òğèäöàòü","ñîğîê","ïÿòüäåñÿò","øåñòüäåñÿò","ñåìüäåñÿò","âîñåìüäåñÿò","äåâÿíîñòî");
$s22 = Array("äåñÿòü","îäèííàäöàòü","äâåíàäöàòü","òğèíàäöàòü","÷åòûğíàäöàòü","ïÿòíàäöàòü","øåñòíàäöàòü","ñåìíàäöàòü","âîñåìíàäöàòü","äåâÿòíàäöàòü");
$s3 = Array("","ñòî","äâåñòè","òğèñòà","÷åòûğåñòà");

if($rub==0)
{// åñëè ıòî 0
return "íîëü ".$dop0[0].'';
}

// ğàçáèâàåì ïîëó÷åííîå ÷èñëî íà òğîéêè è çàãîíÿåì â ìàññèâ $triplet
$t_count = ceil(strlen($rub)/3);
for($i=0;$i<$t_count;$i++)
{
$k = $t_count - $i - 1;
$triplet[$k] = $rub%1000;
$rub = floor($rub/1000);
}

// ïğîáåãàåì ïî òğèïëåòàì
for($i=0;$i<$t_count;$i++)
{
$t = $triplet[$i]; // ıòî òåêóùèé òğèïëåò - ñ íèì è ğàáîòàåì
$k = $t_count - $i - 1;
$n1 = floor($t/100);
$n2 = floor(($t-$n1*100)/10);
$n3 = $t-$n1*100-$n2*10;

// îáğàáàòûâàåì ñîòíè
if($n1<5) $res .= $s3[$n1]." ";
elseif($n1) $res .= $s1[$n1]."ñîò ";

if($n2>1)
{// âòîğîé äåñÿòîê
$res .= $s2[$n2]." ";
if($n3 and $k==1)
{// åñëè åñòü åäèíèöû â òğèïëåòå è ıòî òğèïëåò ÒÛÑß×
$res .= $s11[$n3]." ";
}
elseif($n3)
{
$res .= $s1[$n3]." ";
}
}
elseif($n2==1)
{
$res .= $s22[$n3]." ";
}
elseif($n3 and $k==1)
{// åñëè åñòü åäèíèöû â òğèïëåòå è ıòî òğèïëåò ÒÛÑß×
$res .= $s11[$n3]." ";
}
elseif($n3)
{
$res .= $s1[$n3]." ";
}

// ïğèëåïëÿåì â êîíåö òğèïëåòà êîììåíò
if($n3==1 and $n2!=1)
{// â êîíöå òğèïëåòà ñòîèò 1, íî íå 11.
$res .= $dop1[$k]." ";
}
elseif($n3>1 and $n3<5 and $n2!=1)
{// â êîíöå òğèïëåòà ñòîèò 2, 3 èëè 4, íî íå 12, 13 èëè 14
$res .= $dop2[$k]." ";
}
elseif($t or $k==0)
{
$res .= $dop0[$k]." ";
}
}
$kk=''; 



return $res.$kk;
} 
	
	/*
	function MakePropis($number){
		$res='';
		$number=(int)$number;
		
		//òğëí
		
		if($this->DivTrillion($number)>0){
			$res.=$this->OverThsnd($this->DivTrillion($number));		
		}
		
		if($this->DivMilliard($number)>0){
			$res.=$this->OverThsnd($this->DivMilliard($number));		
		}
		
		if($this->DivMillion($number)>0){
			$res.=$this->OverThsnd($this->DivMillion($number));		
		}
		
		
		if($this->DivThousand($number)>0){
			$res.=$this->OverThsnd($this->DivThousand($number));
		}
		
		
		
		return $res;
	}
	
	
	protected function OverThsnd($number){
		$res='';
		if($this->DivHundred($number)>0){
			$fraction=$this->DivHundred($number);
			switch($fraction){
				case 0:	
					$res.='';
				break;
				case 1:
					$res.=' ñòî ';
				break;
				case 2:
					$res.=' äâåñòè ';
				break;
				case 3:
					$res.=' òğèñòà ';
				break;
				case 4:
					$res.=' ÷åòûğåñòà ';
				break;
				case 5:
					$res.=' ïÿòüñîò ';
				break;
				case 6:
					$res.=' øåñòüñîò ';
				break;
				case 7:
					$res.=' ñåìüñîò ';
				break;
				case 8:
					$res.=' âîñåìüñîò ';
				break;
				case 9:
					$res.=' äåâÿòüñîò ';
				break;
			}
			
				
		}
		
		if($this->DivDec($number)>0){
			$fraction=$this->DivDec($number);
			switch($fraction){
				case 0:	
					$res.='';
				break;
				case 1:
					$res.=' äåñÿòü ';
				break;
				case 2:
					$res.=' äâàäöàòü ';
				break;
				case 3:
					$res.=' òğèäöàòü ';
				break;
				case 4:
					$res.=' ñîğîê ';
				break;
				case 5:
					$res.=' ïÿòüäåñÿò ';
				break;
				case 6:
					$res.=' øåñòüäåñÿò ';
				break;
				case 7:
					$res.=' ñåìüäåñÿò ';
				break;
				case 8:
					$res.=' âîñåìüäåñÿò ';
				break;
				case 9:
					$res.=' äåâÿíîñòî ';
				break;
			}
			
				
		}
		
		if($this->DivOne($number)>0){
			$fraction=$this->DivOne($number);
			switch($fraction){
				case 0:	
					$res.='';
				break;
				case 1:
					$res.=' îäèí ';
				break;
				case 2:
					$res.=' äâà ';
				break;
				case 3:
					$res.=' òğè ';
				break;
				case 4:
					$res.=' ÷åòûğå ';
				break;
				case 5:
					$res.=' ïÿòü ';
				break;
				case 6:
					$res.=' øåñòü ';
				break;
				case 7:
					$res.=' ñåìü ';
				break;
				case 8:
					$res.=' âîñåìü ';
				break;
				case 9:
					$res.=' äåâÿòü ';
				break;
			}
			
				
		}
		
		return $res;
	}
	
	
	protected function FracToTxt($frac, $sex){
		$res='';
		if($sex=='male'){
			//switch(	
			
		}elseif($sex=='female'){
			
		}else{
			switch($frac){
				case 0:	
					$res='';
				break;
				case 1:
			}
		}
		return $res;
	}
	
	
	protected function DivHundred($number){
		return (int)($number/100);
	}
	
	protected function DivDec($number){
		return (int)($number/10);
		
	}
	
	protected function DivOne($number){
		return (int)($number/1);
	}
	
	protected function DivThousand($number){
		return (int)($number/1000);
			
	}
	
	protected function DivMillion($number){
		return (int)($number/1000000);
			
	}
	
	protected function DivMilliard($number){
		return (int)($number/1000000000);
			
	}
	
	protected function DivTrillion($number){
		return (int)($number/1000000000000);
			
	}*/
	
}

?>