<?

class Propis1{
	
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
return "ноль ".$dop0[0].' копеек';
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
		$kk=' копейка';
	break;	
	case 2:
		$kk=' копейки';
	break;	
	case 3:
		$kk=' копейки';
	break;	
	case 4:
		$kk=' копейки';
	break;	
	case 5:
		$kk=' копеек';
	break;	
	case 6:
		$kk=' копеек';
	break;	
	case 7:
		$kk=' копеек';
	break;	
	case 8:
		$kk=' копеек';
	break;	
	case 9:
		$kk=' копеек';
	break;
	case 10:
		$kk=' копеек';
	break;	
	
	case 11:
		$kk=' копеек';
	break;	
		
	case 12:
		$kk=' копеек';
	break;	
	case 13:
		$kk=' копеек';
	break;	
	case 14:
		$kk=' копеек';
	break;	
	case 15:
		$kk=' копеек';
	break;	
	case 16:
		$kk=' копеек';
	break;	
	case 17:
		$kk=' копеек';
	break;	
	case 18:
		$kk=' копеек';
	break;	
	case 19:
		$kk=' копеек';
	break;	
	case 20:
		$kk=' копеек';
	break;	
	
	case 21:
		$kk=' копейка';
	break;	
		
	case 22:
		$kk=' копейки';
	break;	
	case 23:
		$kk=' копейки';
	break;	
	case 24:
		$kk=' копейки';
	break;	
	case 25:
		$kk=' копеек';
	break;	
	case 26:
		$kk=' копеек';
	break;	
	case 27:
		$kk=' копеек';
	break;	
	case 28:
		$kk=' копеек';
	break;	
	case 29:
		$kk=' копеек';
	break;	
	case 30:
		$kk=' копеек';
	break;	
	
	case 31:
		$kk=' копейка';
	break;	
		
	case 32:
		$kk=' копейки';
	break;	
	case 33:
		$kk=' копейки';
	break;	
	case 34:
		$kk=' копейки';
	break;	
	case 35:
		$kk=' копеек';
	break;	
	case 36:
		$kk=' копеек';
	break;	
	case 37:
		$kk=' копеек';
	break;	
	case 38:
		$kk=' копеек';
	break;	
	case 39:
		$kk=' копеек';
	break;	
	case 40:
		$kk=' копеек';
	break;
	
	case 41:
		$kk=' копейка';
	break;	
		
	case 42:
		$kk=' копейки';
	break;	
	case 43:
		$kk=' копейки';
	break;	
	case 44:
		$kk=' копейки';
	break;	
	case 45:
		$kk=' копеек';
	break;	
	case 46:
		$kk=' копеек';
	break;	
	case 47:
		$kk=' копеек';
	break;	
	case 48:
		$kk=' копеек';
	break;	
	case 49:
		$kk=' копеек';
	break;	
	case 50:
		$kk=' копеек';
	break;	
	
	
	case 51:
		$kk=' копейка';
	break;	
		
	case 52:
		$kk=' копейки';
	break;	
	case 53:
		$kk=' копейки';
	break;	
	case 54:
		$kk=' копейки';
	break;	
	case 55:
		$kk=' копеек';
	break;	
	case 56:
		$kk=' копеек';
	break;	
	case 57:
		$kk=' копеек';
	break;	
	case 58:
		$kk=' копеек';
	break;	
	case 59:
		$kk=' копеек';
	break;	
	case 60:
		$kk=' копеек';
	break;
	
	
	case 61:
		$kk=' копейка';
	break;	
		
	case 62:
		$kk=' копейки';
	break;	
	case 63:
		$kk=' копейки';
	break;	
	case 64:
		$kk=' копейки';
	break;	
	case 65:
		$kk=' копеек';
	break;	
	case 66:
		$kk=' копеек';
	break;	
	case 67:
		$kk=' копеек';
	break;	
	case 68:
		$kk=' копеек';
	break;	
	case 69:
		$kk=' копеек';
	break;	
	case 70:
		$kk=' копеек';
	break;
	
	case 71:
		$kk=' копейка';
	break;	
		
	case 72:
		$kk=' копейки';
	break;	
	case 73:
		$kk=' копейки';
	break;	
	case 74:
		$kk=' копейки';
	break;	
	case 75:
		$kk=' копеек';
	break;	
	case 76:
		$kk=' копеек';
	break;	
	case 77:
		$kk=' копеек';
	break;	
	case 78:
		$kk=' копеек';
	break;	
	case 79:
		$kk=' копеек';
	break;	
	case 80:
		$kk=' копеек';
	break;	
	
	case 81:
		$kk=' копейка';
	break;	
		
	case 82:
		$kk=' копейки';
	break;	
	case 83:
		$kk=' копейки';
	break;	
	case 84:
		$kk=' копейки';
	break;	
	case 85:
		$kk=' копеек';
	break;	
	case 86:
		$kk=' копеек';
	break;	
	case 87:
		$kk=' копеек';
	break;	
	case 88:
		$kk=' копеек';
	break;	
	case 89:
		$kk=' копеек';
	break;	
	case 90:
		$kk=' копеек';
	break;	
	
	case 91:
		$kk=' копейка';
	break;	
		
	case 92:
		$kk=' копейки';
	break;	
	case 93:
		$kk=' копейки';
	break;	
	case 94:
		$kk=' копейки';
	break;	
	case 95:
		$kk=' копеек';
	break;	
	case 96:
		$kk=' копеек';
	break;	
	case 97:
		$kk=' копеек';
	break;	
	case 98:
		$kk=' копеек';
	break;	
	case 99:
		$kk=' копеек';
	break;	
	case 100:
		$kk=' копеек';
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