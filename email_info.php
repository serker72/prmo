<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251" />
<title>�������� ��� ��������</title>
</head>

<body>

<?
require_once('classes/abstractitem.php');
require_once('classes/smarty/SmartyAdm.class.php');

require_once('classes/phpmailer/class.phpmailer.php');


//require_once('classes/supcontract_item.php');

//require_once('classes/actionlog.php');

require_once('classes/messageitem.php');

require_once('classes/user_s_group.php');


if(!isset($_GET['from'])) $from=0;
else  $from=$_GET['from'];

class UsersSGroup1 extends UsersSGroup {
	
	//������ ���-��� �� ���������� ������
	public function GetItemsByDecArr( DBDecorator $dec){
		$arr= array();
			
		$sql='select u.*  from '.$this->tablename.'  as u
		 
		
	/*	where u.group_id="'.$this->group_id.'" */
		';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			//$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		 
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$arr[]=$f;
			}
		}
		return $arr;
	}
}

$_us=new UsersSGroup1;

$dec=new DBDecorator;

$dec->AddEntry(new SqlEntry('is_active',1, SqlEntry::E));
//$dec->AddEntry(new SqlEntry('group_id',1, SqlEntry::E));

$dec->AddEntry(new SqlEntry('id','select distinct user_id from user_rights where object_id=903', SqlEntry::IN_SQL));
			


$users=$_us->GetItemsByDecArr(  $dec);  //>GetItemsArr(0, 1);



/*echo '<pre>';
var_dump($users);
echo '</pre>';
die();*/

$counter=-1; 

 
foreach($users as $k=>$user){
	$counter++;
	
	if($from!=$counter) continue;
	
	
	
	
	$text='';
	
	$mail = new PHPMailer();
	
	
	$sm=new smartyadm;
	
	$sm->assign('name_s', $user['name_s']);
	$sm->assign('email_s', $user['email_s']);
	
	
	$body=$sm->fetch('email_info.html');
	
	
	
	$mail->SetFrom(FEEDBACK_EMAIL, 'GYDEX: �������������� �������� ���������');
	
	$mail->AddAddress(trim($user['email_s']),   $user['email_s']);
	$mail->Subject = "����� ������������ ������ GYDEX.�����������"; 
	$mail->Body=$body;
	
	
	
	$mail->CharSet = "windows-1251";
	$mail->IsHTML(true);  
	
	echo '������������ '.$user['login'].' '.$user['name_s'] .'  '.$user['email_s'].' <br>';
	
	if($user['id']==1) {
		echo 'demo-mode, skipping...<br />
';
		continue;
		
	}
	
	if(!$mail->Send())
	{
		 echo "������ �������� ������: " . $mail->ErrorInfo;
	}
	else 
	{
		 echo "������ ����������!";
	}
	
	//if($from>=20) break; 
}

if($from>= (count($users)-1) ){
	echo '�������� ���������!';
	
	
}else{
?>
	��������, ���������...<br />
	<script>
	window.setTimeout('location.href="<?='email_info.php?from='.($from+1)?>"', 2000);
	</script>
<?	
}


?>
</body>
</html>