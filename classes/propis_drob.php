<?

class PropisDrob{
	
	function propis($rub){
		$ish=$rub;
		
$dop0 = Array("","�����","���������","����������");
$dop1 = Array("","������","�������","��������");
$dop2 = Array("","������","��������","���������");
$s1 = Array("","����","���","���","������","����","�����","����","������","������");
$s11 = Array("","����","���","���","������","����","�����","����","������","������");
$s2 = Array("","������","��������","��������","�����","���������","����������","���������","�����������","���������");
$s22 = Array("������","�����������","����������","����������","������������","����������","�����������","����������","������������","������������");
$s3 = Array("","���","������","������","���������");

if($rub==0)
{// ���� ��� 0
return " ".$dop0[0].'';
}

// ��������� ���������� ����� �� ������ � �������� � ������ $triplet
$t_count = ceil(strlen($rub)/3);
for($i=0;$i<$t_count;$i++)
{
$k = $t_count - $i - 1;
$triplet[$k] = $rub%1000;
$rub = floor($rub/1000);
}

// ��������� �� ���������
for($i=0;$i<$t_count;$i++)
{
$t = $triplet[$i]; // ��� ������� ������� - � ��� � ��������
$k = $t_count - $i - 1;
$n1 = floor($t/100);
$n2 = floor(($t-$n1*100)/10);
$n3 = $t-$n1*100-$n2*10;

// ������������ �����
if($n1<5) $res .= $s3[$n1]." ";
elseif($n1) $res .= $s1[$n1]."��� ";

if($n2>1)
{// ������ �������
$res .= $s2[$n2]." ";
if($n3 and $k==1)
{// ���� ���� ������� � �������� � ��� ������� �����
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
{// ���� ���� ������� � �������� � ��� ������� �����
$res .= $s11[$n3]." ";
}
elseif($n3)
{
$res .= $s1[$n3]." ";
}

// ���������� � ����� �������� �������
if($n3==1 and $n2!=1)
{// � ����� �������� ����� 1, �� �� 11.
$res .= $dop1[$k]." ";
}
elseif($n3>1 and $n3<5 and $n2!=1)
{// � ����� �������� ����� 2, 3 ��� 4, �� �� 12, 13 ��� 14
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
		$kk=' �������';
	break;	
	case 2:
		$kk=' �������';
	break;	
	case 3:
		$kk=' �������';
	break;	
	case 4:
		$kk=' �������';
	break;	
	case 5:
		$kk=' �������';
	break;	
	case 6:
		$kk=' �������';
	break;	
	case 7:
		$kk=' �������';
	break;	
	case 8:
		$kk=' �������';
	break;	
	case 9:
		$kk=' �������';
	break;
	case 10:
		$kk=' �������';
	break;	
	
	case 11:
		$kk=' �������';
	break;	
		
	case 12:
		$kk=' �������';
	break;	
	case 13:
		$kk=' �������';
	break;	
	case 14:
		$kk=' �������';
	break;	
	case 15:
		$kk=' �������';
	break;	
	case 16:
		$kk=' �������';
	break;	
	case 17:
		$kk=' �������';
	break;	
	case 18:
		$kk=' �������';
	break;	
	case 19:
		$kk=' �������';
	break;	
	case 20:
		$kk=' �������';
	break;	
	
	case 21:
		$kk=' �������';
	break;	
		
	case 22:
		$kk=' �������';
	break;	
	case 23:
		$kk=' �������';
	break;	
	case 24:
		$kk=' �������';
	break;	
	case 25:
		$kk=' �������';
	break;	
	case 26:
		$kk=' �������';
	break;	
	case 27:
		$kk=' �������';
	break;	
	case 28:
		$kk=' �������';
	break;	
	case 29:
		$kk=' �������';
	break;	
	case 30:
		$kk=' �������';
	break;	
	
	case 31:
		$kk=' �������';
	break;	
		
	case 32:
		$kk=' �������';
	break;	
	case 33:
		$kk=' �������';
	break;	
	case 34:
		$kk=' �������';
	break;	
	case 35:
		$kk=' �������';
	break;	
	case 36:
		$kk=' �������';
	break;	
	case 37:
		$kk=' �������';
	break;	
	case 38:
		$kk=' �������';
	break;	
	case 39:
		$kk=' �������';
	break;	
	case 40:
		$kk=' �������';
	break;
	
	case 41:
		$kk=' �������';
	break;	
		
	case 42:
		$kk=' �������';
	break;	
	case 43:
		$kk=' �������';
	break;	
	case 44:
		$kk=' �������';
	break;	
	case 45:
		$kk=' �������';
	break;	
	case 46:
		$kk=' �������';
	break;	
	case 47:
		$kk=' �������';
	break;	
	case 48:
		$kk=' �������';
	break;	
	case 49:
		$kk=' �������';
	break;	
	case 50:
		$kk=' �������';
	break;	
	
	
	case 51:
		$kk=' �������';
	break;	
		
	case 52:
		$kk=' �������';
	break;	
	case 53:
		$kk=' �������';
	break;	
	case 54:
		$kk=' �������';
	break;	
	case 55:
		$kk=' �������';
	break;	
	case 56:
		$kk=' �������';
	break;	
	case 57:
		$kk=' �������';
	break;	
	case 58:
		$kk=' �������';
	break;	
	case 59:
		$kk=' �������';
	break;	
	case 60:
		$kk=' �������';
	break;
	
	
	case 61:
		$kk=' �������';
	break;	
		
	case 62:
		$kk=' �������';
	break;	
	case 63:
		$kk=' �������';
	break;	
	case 64:
		$kk=' �������';
	break;	
	case 65:
		$kk=' �������';
	break;	
	case 66:
		$kk=' �������';
	break;	
	case 67:
		$kk=' �������';
	break;	
	case 68:
		$kk=' �������';
	break;	
	case 69:
		$kk=' �������';
	break;	
	case 70:
		$kk=' �������';
	break;
	
	case 71:
		$kk=' �������';
	break;	
		
	case 72:
		$kk=' �������';
	break;	
	case 73:
		$kk=' �������';
	break;	
	case 74:
		$kk=' �������';
	break;	
	case 75:
		$kk=' �������';
	break;	
	case 76:
		$kk=' �������';
	break;	
	case 77:
		$kk=' �������';
	break;	
	case 78:
		$kk=' �������';
	break;	
	case 79:
		$kk=' �������';
	break;	
	case 80:
		$kk=' �������';
	break;	
	
	case 81:
		$kk=' �������';
	break;	
		
	case 82:
		$kk=' �������';
	break;	
	case 83:
		$kk=' �������';
	break;	
	case 84:
		$kk=' �������';
	break;	
	case 85:
		$kk=' �������';
	break;	
	case 86:
		$kk=' �������';
	break;	
	case 87:
		$kk=' �������';
	break;	
	case 88:
		$kk=' �������';
	break;	
	case 89:
		$kk=' �������';
	break;	
	case 90:
		$kk=' �������';
	break;	
	
	case 91:
		$kk=' �������';
	break;	
		
	case 92:
		$kk=' �������';
	break;	
	case 93:
		$kk=' �������';
	break;	
	case 94:
		$kk=' �������';
	break;	
	case 95:
		$kk=' �������';
	break;	
	case 96:
		$kk=' �������';
	break;	
	case 97:
		$kk=' �������';
	break;	
	case 98:
		$kk=' �������';
	break;	
	case 99:
		$kk=' �������';
	break;	
	case 100:
		$kk=' �������';
	break;	
}


return $res.$kk;
} 
	
	/*
	function MakePropis($number){
		$res='';
		$number=(int)$number;
		
		//����
		
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
					$res.=' ��� ';
				break;
				case 2:
					$res.=' ������ ';
				break;
				case 3:
					$res.=' ������ ';
				break;
				case 4:
					$res.=' ��������� ';
				break;
				case 5:
					$res.=' ������� ';
				break;
				case 6:
					$res.=' �������� ';
				break;
				case 7:
					$res.=' ������� ';
				break;
				case 8:
					$res.=' ��������� ';
				break;
				case 9:
					$res.=' ��������� ';
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
					$res.=' ������ ';
				break;
				case 2:
					$res.=' �������� ';
				break;
				case 3:
					$res.=' �������� ';
				break;
				case 4:
					$res.=' ����� ';
				break;
				case 5:
					$res.=' ��������� ';
				break;
				case 6:
					$res.=' ���������� ';
				break;
				case 7:
					$res.=' ��������� ';
				break;
				case 8:
					$res.=' ����������� ';
				break;
				case 9:
					$res.=' ��������� ';
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
					$res.=' ���� ';
				break;
				case 2:
					$res.=' ��� ';
				break;
				case 3:
					$res.=' ��� ';
				break;
				case 4:
					$res.=' ������ ';
				break;
				case 5:
					$res.=' ���� ';
				break;
				case 6:
					$res.=' ����� ';
				break;
				case 7:
					$res.=' ���� ';
				break;
				case 8:
					$res.=' ������ ';
				break;
				case 9:
					$res.=' ������ ';
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