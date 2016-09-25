<?
require_once('configfile.php');

define("ABSPATH",'/home/prmo.gydex.su/www/');

//путь к странице 404
define("PATH404",'/404.php');

//адрес сайта (дл€ рсс-ленты, генерации абсолютных url)
define("SITEURL",'http://www.prmo.gydex.su');

//принудительна€ перекодировка xml
define("DO_XML_RECODE",1);

//папка дл€ вложений к сообщени€м
define("MESSAGE_FILES_PATH",ABSPATH.'upload/messages/');

$_big_time_marker_begin=time();

/*
//папка файлов и п/о
define("PO_FILES_PATH",ABSPATH.'upload/files/po/');
*/

//загрузка общих настроек из файла
//при ошибке загрузки используем настройки по умолчанию
$cnf=new ConfigFile();
$cnf->SetFileName(ABSPATH.'cnf/init.xml');
$global_settings=$cnf->LoadFromFile();



//функци€ обработки строки перед занесением в бд
function SecStr($str="",$level=0){
	if($level==10){
	//удал€ем все теги и им подобные символы, строга€ зачистка
		$str=strip_tags($str);
		$str=eregi_replace("<", "", $str);
		$str=eregi_replace(">", "", $str);		
		$str=htmlspecialchars($str);		
	}
	
	if($level==9){
	//замен€ем теги на спецсимволы, нестрога€ зачистка
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
	$fld=strtr($fld, "абвгдеЄжзиклмнопрстуфхцчшщйьъыэю€", "abvgdeejziklmnoprstufhc4ww___yeua");
	$fld=strtr($fld, "јЅ¬√ƒ≈®∆«» ЋћЌќѕ–—“”‘’÷„Ўў…№ЏџЁёя", "ABVGDEEJZIKLMNOPRSTUFHC4WW___YEUA");
	
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



//форматирование цены
function FormatPrice($value, $dims, $afternil=0){
	return sprintf("%.".$afternil."f".$dims, $value);
}

function validateEmail($value){	
		//корр форматы адресов: *@*.* *@.*.* *.*@*.* *.*@*.*.*
		return (!eregi('^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[_a-z0-9-]+(\.[_a-z0-9-]+)*(\.[a-z]{2,6})$', $value))?0:1;
}

?>