<?
define("DebugMode","DM_TRUE"); //DM_FALSE

//функция обработки строки перед занесением в бд
function SecStr($str="",$level=0){
	if($level==10){
	//удаляем все теги и им подобные символы, строгая зачистка
		$str=strip_tags($str);
		$str=eregi_replace("<", "", $str);
		$str=eregi_replace(">", "", $str);		
		$str=htmlspecialchars($str);		
	}
	
	if($level==9){
	//заменяем теги на спецсимволы, нестрогая зачистка
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
	$fld=strtr($fld, "абвгдеёжзиклмнопрстуфхцчшщйьъыэюя", "abvgdeejziklmnoprstufhc4ww___yeua");
	$fld=strtr($fld, "АБВГДЕЁЖЗИКЛМНОПРСТУФХЦЧШЩЙЬЪЫЭЮЯ", "ABVGDEEJZIKLMNOPRSTUFHC4WW___YEUA");
	
	return $fld;
}


function SecurePath($fld){
	$fld=strtr($fld, "?* :", "____");
	
	$fld=SecureCyr($fld);
	
	return $fld;
}	





//строка кода джаваскрипт для определения координат события
define("COORDFUNC", "
	  e = event;
	  coord=GetCoords(e);
");

?>