<?
define("DebugMode","DM_TRUE"); //DM_FALSE

//������� ��������� ������ ����� ���������� � ��
function SecStr($str="",$level=0){
	if($level==10){
	//������� ��� ���� � �� �������� �������, ������� ��������
		$str=strip_tags($str);
		$str=eregi_replace("<", "", $str);
		$str=eregi_replace(">", "", $str);		
		$str=htmlspecialchars($str);		
	}
	
	if($level==9){
	//�������� ���� �� �����������, ��������� ��������
		$str=htmlspecialchars($str);
	}	
	
	$str = trim($str);	
	$str=addslashes($str);
	
	return $str;
}


function DeParams($params){
	$str='';
	foreach($params as $k=>$v){
		$str.="&$k=$v";
	}
	
	return $str;
}

function SecureCyr($fld){
	$fld=strtr($fld, "��������������������������������", "abvgdeejziklmnoprstufhc4ww___yeua");
	$fld=strtr($fld, "�����Ũ��������������������������", "ABVGDEEJZIKLMNOPRSTUFHC4WW___YEUA");
	
	return $fld;
}


function SecurePath($fld){
	$fld=strtr($fld, "?* :", "____");
	
	$fld=SecureCyr($fld);
	
	return $fld;
}	





//������ ���� ����������� ��� ����������� ��������� �������
define("COORDFUNC", "
	  e = event;
	  coord=GetCoords(e);
");

?>