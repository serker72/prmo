<?
session_start();
require_once('../classes/global.php');
require_once('../classes/supplieritem.php');
require_once('../classes/v2/delivery.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/phpmailer/class.phpmailer.php');

$_di=new Delivery_Item;
$_ds=new Delivery_SubscriberItem;

$_messages_per_call=15;

//каждый вызов - вытягиваем по 15 получателей рассылок, у кого is_sent=0
//рассылки д.б. в статусах 4,2
//отправляем им соответствующие письма
//заносим в is_sent 1
//проверяем, если по рассылке все отосланы - ставим статус 3 Отправлена и меняем дату смены статуса
$delivery_ids=array();

//условия по дням рождения
$birth_flt='';
if((int)date('H')>=9){
	$available_dates1=array(); $available_dates2=array();
	for($i=1902; $i<=date('Y'); $i++){
		$date1=mktime(0,0,0,date('n'), date('j'),$i);
		$date2=mktime(23,59,59,date('n'), date('j'),$i);
		
		
		//$date1=mktime(0,0,0,9,6,$i);
		//$date2=mktime(23,59,59,9,6,$i);
		
 	//echo date('d.m.Y H:i:s', -545454000).' '.$i.'<br>';	
	
	 //	echo date('d.m.Y H:i:s', $date1).' '.$i.'<br>';	
		
		$available_dates1[]=   '( sc.birthdate between "'.$date1.'" and "'.$date2.'" ) ' ;
		$available_dates2[]=   '( us.pasp_bithday between "'.$date1.'" and "'.$date2.'" ) ' ;
	}
	
	$birth_flt.=' or (
		dev.is_birth=1
		and ( 
		('.implode(' or ', $available_dates1).')
		or ('.implode(' or ', $available_dates2).')
		)
	
	)';	
}


$sql='select p.id as p_id, p.delivery_id, p.is_sent,
u.*
 from delivery_subscriber as p 
 inner join delivery_user as u on u.id=p.user_id and u.is_subscribed=1
 inner join delivery as dev on dev.id=p.delivery_id
 left join user as us on u.user_id=us.id
 left join supplier_contact as sc on sc.id=u.supplier_contact_id
 
 where p.is_sent="0" and dev.schedule_pdate<"'.time().'"
 
	and dev.status_id in(2,4) 
	and( dev.is_birth=0
	'.$birth_flt.'
	)
	order by p.id asc
	limit '.$_messages_per_call;
//echo $sql.'<br>';	
	
$set=new mysqlSet($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();

/*echo 'zzzzzzzzzzzzz';
var_dump(mail('vpvp32@yandex.ru','ee','ww'));*/
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	foreach($f as $k=>$v) $f[$k]=stripslashes($v);
	//print_r($f);				
	if(!in_array($f['delivery_id'], $delivery_ids)) $delivery_ids[]=$f['delivery_id'];
	
	$di=$_di->getitembyid($f['delivery_id']);
	
	$_fl=new Delivery_Fields;
	$_fl->ProcessFields($f['id'], $di);
	
	//!!!!!!!!!!!! добавить счетчик к html
	
	//print_r($di);
	
	$sm=new SmartyAj;
	$sm->assign('SITEURL', SITEURL);
	$html=$sm->fetch('page_email_top.html');
	
	
	$html.=$di['html_content'];
	
	//
	if($di['has_tracking']) $html.='<img src="'.SITEURL.'/img/campaign_'.$f['id'].'_'.$f['delivery_id'].'.png" width="1" height="1" >';
	
	
	$sm=new SmartyAj;
	$html.=$sm->fetch('page_email_bottom.html');
	
	//echo htmlspecialchars($html);
	
	$mail = new PHPMailer();
	
	if($di['to_is_personal']) $mail->AddAddress($f['email'],  $di['to_field']);
	else $mail->AddAddress($f['email'],  $f['email']);
	
	$mail->SetFrom($di['from_email'], $di['from_name']);
	
	$mail->Subject = $di['topic']; 
	$mail->Body=$html;
	$mail->AltBody=$di['plain_text_content'];
	 
	  
	$mail->CharSet = "windows-1251";
	$mail->IsHTML(true);
	
	$res=$mail->Send();
	
	//var_dump($res);
	
	$_ds->Edit($f['p_id'], array('is_sent'=>1, 'pdate'=>time()));
}

//проверить и обновить статусы раассылок

$sql1='select * from delivery as dev 
 where  dev.schedule_pdate<"'.time().'"
	and dev.status_id in(2,4) 
	order by id asc
';
//echo $sql;	
	
$set1=new mysqlSet($sql1);
$rs1=$set1->GetResult();
$rc1=$set1->GetResultNumRows();	
for($i1=0; $i1<$rc1; $i1++){
	$g=mysqli_fetch_array($rs1);
	$d_id=$g['id'];
	
 
	//сколько отослано (активных)
	$sql='select count(ds.id) from delivery_subscriber as ds
	inner join delivery as d on d.id=ds.delivery_id
	inner join delivery_user as u on u.id=ds.user_id
	where 
		ds.delivery_id="'.$d_id.'"
		and ds.is_sent=1
		and u.is_subscribed=1
		';
	
		
	$set=new mysqlSet($sql);
	$rs=$set->GetResult();	
	$f=mysqli_fetch_array($rs);
	$sent=(int)$f[0];
	
	//сколько запланировано (активных)
	$sql='select count(ds.id) from delivery_subscriber as ds
	inner join delivery as d on d.id=ds.delivery_id
	inner join delivery_user as u on u.id=ds.user_id
	where 
		ds.delivery_id="'.$d_id.'"
		 
		and u.is_subscribed=1
		';
	//echo $sql;
		
	$set=new mysqlSet($sql);
	$rs=$set->GetResult();	
	$f=mysqli_fetch_array($rs);
	$total=(int)$f[0];
	
	
	
	$params=array();
	
	
	if((int)$total>(int)$sent){
		echo "more $d_id $sent vs $total ";
		$params['status_id']=2;	
		if($g['status_id']!=2) {
			$params['pdate_status_change']=time();
			$_di->Edit($d_id, $params);
		}
	}else{
		echo "sent $d_id $sent vs $total ";
		$params['status_id']=3;
		if($g['status_id']!=3){
			 $params['pdate_status_change']=time();
			 $_di->Edit($d_id, $params);
		}
	}
	
	
	
		
	
}


?>