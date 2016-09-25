<?

class PropisDrob{
	
	function propis($rub){
		$ish=$rub;
		
$dop0 = Array("","тысяч","миллионов","миллиардов");
$dop1 = Array("","тысяча","миллион","миллиард");
$dop2 = Array("","тысячи","миллиона","миллиарда");
$s1 = Array("","одна","две","три","четыре","пять","шесть","семь","восемь","девять");
$s11 = Array("","одна","две","три","четыре","пять","шесть","семь","восемь","девять");
$s2 = Array("","десять","двадцать","тридцать","сорок","пятьдесят","шестьдесят","семьдесят","восемьдесят","девяносто");
$s22 = Array("десять","одиннадцать","двенадцать","тринадцать","четырнадцать","пятнадцать","шестнадцать","семнадцать","восемнадцать","девятнадцать");
$s3 = Array("","сто","двести","триста","четыреста");

if($rub==0)
{// если это 0
return " ".$dop0[0].'';
}

// разбиваем полученное число на тройки и загоняем в массив $triplet
$t_count = ceil(strlen($rub)/3);
for($i=0;$i<$t_count;$i++)
{
$k = $t_count - $i - 1;
$triplet[$k] = $rub%1000;
$rub = floor($rub/1000);
}

// пробегаем по триплетам
for($i=0;$i<$t_count;$i++)
{
$t = $triplet[$i]; // это текущий триплет - с ним и работаем
$k = $t_count - $i - 1;
$n1 = floor($t/100);
$n2 = floor(($t-$n1*100)/10);
$n3 = $t-$n1*100-$n2*10;

// обрабатываем сотни
if($n1<5) $res .= $s3[$n1]." ";
elseif($n1) $res .= $s1[$n1]."сот ";

if($n2>1)
{// второй десяток
$res .= $s2[$n2]." ";
if($n3 and $k==1)
{// если есть единицы в триплете и это триплет ТЫСЯЧ
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
{// если есть единицы в триплете и это триплет ТЫСЯЧ
$res .= $s11[$n3]." ";
}
elseif($n3)
{
$res .= $s1[$n3]." ";
}

// прилепляем в конец триплета коммент
if($n3==1 and $n2!=1)
{// в конце триплета стоит 1, но не 11.
$res .= $dop1[$k]." ";
}
elseif($n3>1 and $n3<5 and $n2!=1)
{// в конце триплета стоит 2, 3 или 4, но не 12, 13 или 14
$res .= $dop2[$k]." ";
}
elseif($t or $k==0)
{
$res .= $dop0[$k]." ";
}
}
$kk=''; 
switch($ish){
	case 1:
		$kk=' десятая';
	break;	
	case 2:
		$kk=' десятых';
	break;	
	case 3:
		$kk=' десятых';
	break;	
	case 4:
		$kk=' десятых';
	break;	
	case 5:
		$kk=' десятых';
	break;	
	case 6:
		$kk=' десятых';
	break;	
	case 7:
		$kk=' десятых';
	break;	
	case 8:
		$kk=' десятых';
	break;	
	case 9:
		$kk=' десятых';
	break;
	case 10:
		$kk=' десятых';
	break;	
	
	case 11:
		$kk=' десятых';
	break;	
		
	case 12:
		$kk=' десятых';
	break;	
	case 13:
		$kk=' десятых';
	break;	
	case 14:
		$kk=' десятых';
	break;	
	case 15:
		$kk=' десятых';
	break;	
	case 16:
		$kk=' десятых';
	break;	
	case 17:
		$kk=' десятых';
	break;	
	case 18:
		$kk=' десятых';
	break;	
	case 19:
		$kk=' десятых';
	break;	
	case 20:
		$kk=' десятых';
	break;	
	
	case 21:
		$kk=' десятая';
	break;	
		
	case 22:
		$kk=' десятых';
	break;	
	case 23:
		$kk=' десятых';
	break;	
	case 24:
		$kk=' десятых';
	break;	
	case 25:
		$kk=' десятых';
	break;	
	case 26:
		$kk=' десятых';
	break;	
	case 27:
		$kk=' десятых';
	break;	
	case 28:
		$kk=' десятых';
	break;	
	case 29:
		$kk=' десятых';
	break;	
	case 30:
		$kk=' десятых';
	break;	
	
	case 31:
		$kk=' десятых';
	break;	
		
	case 32:
		$kk=' десятых';
	break;	
	case 33:
		$kk=' десятых';
	break;	
	case 34:
		$kk=' десятых';
	break;	
	case 35:
		$kk=' десятых';
	break;	
	case 36:
		$kk=' десятых';
	break;	
	case 37:
		$kk=' десятых';
	break;	
	case 38:
		$kk=' десятых';
	break;	
	case 39:
		$kk=' десятых';
	break;	
	case 40:
		$kk=' десятых';
	break;
	
	case 41:
		$kk=' десятая';
	break;	
		
	case 42:
		$kk=' десятых';
	break;	
	case 43:
		$kk=' десятых';
	break;	
	case 44:
		$kk=' десятых';
	break;	
	case 45:
		$kk=' десятых';
	break;	
	case 46:
		$kk=' десятых';
	break;	
	case 47:
		$kk=' десятых';
	break;	
	case 48:
		$kk=' десятых';
	break;	
	case 49:
		$kk=' десятых';
	break;	
	case 50:
		$kk=' десятых';
	break;	
	
	
	case 51:
		$kk=' десятая';
	break;	
		
	case 52:
		$kk=' десятых';
	break;	
	case 53:
		$kk=' десятых';
	break;	
	case 54:
		$kk=' десятых';
	break;	
	case 55:
		$kk=' десятых';
	break;	
	case 56:
		$kk=' десятых';
	break;	
	case 57:
		$kk=' десятых';
	break;	
	case 58:
		$kk=' десятых';
	break;	
	case 59:
		$kk=' десятых';
	break;	
	case 60:
		$kk=' десятых';
	break;
	
	
	case 61:
		$kk=' десятая';
	break;	
		
	case 62:
		$kk=' десятых';
	break;	
	case 63:
		$kk=' десятых';
	break;	
	case 64:
		$kk=' десятых';
	break;	
	case 65:
		$kk=' десятых';
	break;	
	case 66:
		$kk=' десятых';
	break;	
	case 67:
		$kk=' десятых';
	break;	
	case 68:
		$kk=' десятых';
	break;	
	case 69:
		$kk=' десятых';
	break;	
	case 70:
		$kk=' десятых';
	break;
	
	case 71:
		$kk=' десятая';
	break;	
		
	case 72:
		$kk=' десятых';
	break;	
	case 73:
		$kk=' десятых';
	break;	
	case 74:
		$kk=' десятых';
	break;	
	case 75:
		$kk=' десятых';
	break;	
	case 76:
		$kk=' десятых';
	break;	
	case 77:
		$kk=' десятых';
	break;	
	case 78:
		$kk=' десятых';
	break;	
	case 79:
		$kk=' десятых';
	break;	
	case 80:
		$kk=' десятых';
	break;	
	
	case 81:
		$kk=' десятая';
	break;	
		
	case 82:
		$kk=' десятых';
	break;	
	case 83:
		$kk=' десятых';
	break;	
	case 84:
		$kk=' десятых';
	break;	
	case 85:
		$kk=' десятых';
	break;	
	case 86:
		$kk=' десятых';
	break;	
	case 87:
		$kk=' десятых';
	break;	
	case 88:
		$kk=' десятых';
	break;	
	case 89:
		$kk=' десятых';
	break;	
	case 90:
		$kk=' десятых';
	break;	
	
	case 91:
		$kk=' десятая';
	break;	
		
	case 92:
		$kk=' десятых';
	break;	
	case 93:
		$kk=' десятых';
	break;	
	case 94:
		$kk=' десятых';
	break;	
	case 95:
		$kk=' десятых';
	break;	
	case 96:
		$kk=' десятых';
	break;	
	case 97:
		$kk=' десятых';
	break;	
	case 98:
		$kk=' десятых';
	break;	
	case 99:
		$kk=' десятых';
	break;	
	case 100:
		$kk=' десятых';
	break;	
}


return $res.$kk;
} 
	
	/*
	function MakePropis($number){
		$res='';
		$number=(int)$number;
		
		//трлн
		
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
					$res.=' сто ';
				break;
				case 2:
					$res.=' двести ';
				break;
				case 3:
					$res.=' триста ';
				break;
				case 4:
					$res.=' четыреста ';
				break;
				case 5:
					$res.=' пятьсот ';
				break;
				case 6:
					$res.=' шестьсот ';
				break;
				case 7:
					$res.=' семьсот ';
				break;
				case 8:
					$res.=' восемьсот ';
				break;
				case 9:
					$res.=' девятьсот ';
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
					$res.=' десять ';
				break;
				case 2:
					$res.=' двадцать ';
				break;
				case 3:
					$res.=' тридцать ';
				break;
				case 4:
					$res.=' сорок ';
				break;
				case 5:
					$res.=' пятьдесят ';
				break;
				case 6:
					$res.=' шестьдесят ';
				break;
				case 7:
					$res.=' семьдесят ';
				break;
				case 8:
					$res.=' восемьдесят ';
				break;
				case 9:
					$res.=' девяносто ';
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
					$res.=' один ';
				break;
				case 2:
					$res.=' два ';
				break;
				case 3:
					$res.=' три ';
				break;
				case 4:
					$res.=' четыре ';
				break;
				case 5:
					$res.=' пять ';
				break;
				case 6:
					$res.=' шесть ';
				break;
				case 7:
					$res.=' семь ';
				break;
				case 8:
					$res.=' восемь ';
				break;
				case 9:
					$res.=' девять ';
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