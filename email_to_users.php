<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>Документ без названия</title>
</head>

<body>

<?
require_once('classes/abstractitem.php');



require_once('classes/supcontract_item.php');

require_once('classes/actionlog.php');

require_once('classes/messageitem.php');


$topic='Доступ к программе Электронный менеджер';

$users=array(

	array(
		'name_s'=>'Семкин Владислав Юрьевич',
		'email'=>'semkin_vlad@mail.ru',
		//'email'=>'vpolikarpov@mail.ru',
		'password'=>'222222w',
		'login'=>'S0005'
		
	),
	
	array(
		'name_s'=>'Семкин Владислав Юрьевич',
		'email'=>'vl1234534@gmail.com',
		//'email'=>'vpolikarpov@gmail.com',
		'password'=>'222222w',
		'login'=>'S0005'
		
	),
	 
);

 
foreach($users as $k=>$user){
	$text='';
	
	$text.='<div>';
	
	$text.='<em>Данное сообщение сформировано автоматически, просьба не отвечать на него.</em>';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	$text.='<div>';
	
	$text.='Уважаемый/(ая) '.$user['name_s'].'!';
	
	$text.='</div>';
	
	
	$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';	
	
	
	$text.='<div>';
	
	$text.='Ваш доступ к программе "Электронный менеджер" <a href="http://www.program.syarmarka.ru">www.program.syarmarka.ru</a>:';
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='Логин: '.$user['login'];
	
	
	
	$text.='</div>';
	
	$text.='<div>';
	
	$text.='Пароль: '.$user['password'];
	
	
	
	$text.='</div>';
	
	
	
		$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';


/*	$text.='<div>';
	
	$text.='Доступна тестовая версия программы "Электронный менеджер" <a href="http://www.test1.syarmarka.ru">www.test1.syarmarka.ru</a>, логин и пароль для доступа - те же.';
	
	
	
	$text.='</div>';
	*/
	
		$text.='<div>';
	$text.='&nbsp;';
	$text.='</div>';

	
	
	$text.='<div>';
	
	$text.='С уважением, программа "Электронный менеджер".';
	
	
	
	$text.='</div>';
	
	$res=@mail($user['email'],$topic,$text,"From: \"".FEEDBACK_EMAIL."\" <".FEEDBACK_EMAIL.">\n"."Reply-To: ".FEEDBACK_EMAIL."\n"."Content-Type: text/html; charset=\"windows-1251\"\n");
	
	var_dump($res);
	
}	


?>
</body>
</html>