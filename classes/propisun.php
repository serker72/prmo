<?

class PropisUn{
	
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
return "���� ".$dop0[0].'';
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