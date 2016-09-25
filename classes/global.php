<?
require_once('configfile.php');

define("ABSPATH",'/home/prmo.gydex.su/www/');

//���� � �������� 404
define("PATH404",'/404.php');

//����� ����� (��� ���-�����, ��������� ���������� url)
define("SITEURL",'http://www.prmo.gydex.su');

//�������������� ������������� xml
define("DO_XML_RECODE",1);

//����� ��� �������� � ����������
define("MESSAGE_FILES_PATH",ABSPATH.'upload/messages/');

$_big_time_marker_begin=time();

/*
//����� ������ � �/�
define("PO_FILES_PATH",ABSPATH.'upload/files/po/');
*/

//�������� ����� �������� �� �����
//��� ������ �������� ���������� ��������� �� ���������
$cnf=new ConfigFile();
$cnf->SetFileName(ABSPATH.'cnf/init.xml');
$global_settings=$cnf->LoadFromFile();



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
	$fld=strtr($fld, "+-?* :'&`\"", "__________");
	$fld=eregi_replace("(\.\.)+", "", $fld);
	
	$fld=SecureCyr($fld);
	
	return $fld;
}	

function SecureFilename($fld){
	$fld=strtr($fld, "\\\/", "__");
	$fld=SecurePath($fld);
	$fld=SecureCyr($fld);
	
	return $fld;
}	
	

function DateFromdmY($string='01.01.2008'){
	return mktime(0,0,0,substr($string,3,2),substr($string,0,2),substr($string,6,4) );
}


function DateFromYmd($string='2008-01-01'){
	return date('d.m.Y',mktime(0,0,0,substr($string,5,2),substr($string,8,2),substr($string,0,4) ));
}

function UnixDateFromYmd($string='2008-01-01'){
	return date('r',mktime(0,0,0,substr($string,5,2),substr($string,8,2),substr($string,0,4) ));
}


function DateYmd_from_dmY($string='01.01.2013'){
	return substr($string,6,4).'-'.substr($string,3,2).'-'.substr($string, 0,2)	;
}



//�������������� ����
function FormatPrice($value, $dims, $afternil=0){
	return sprintf("%.".$afternil."f".$dims, $value);
}

function validateEmail($value){	
		//���� ������� �������: *@*.* *@.*.* *.*@*.* *.*@*.*.*
		return (!eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.[a-z]{2,6})$', $value))?0:1;
}

?>