<?
$sm=new SmartyAdm;

$au=new AuthUser();

if($au->GetProfile()!==NULL){
	
	//echo 'zzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzzz';
	$profile=$au->GetProfile();
	$sm->assign('whoisonline',$au->users_sessions->GetUsersOnline($profile['group_id']));	
}

$footer_res=$sm->fetch('footer.html');
unset($sm);

/*echo mysqlSet::$inst_count.' �������� � �� �� �������<br />';
echo nonSet::$inst_count.' �������� �� ���������� ��<br />';
echo mysqlSet::$inst_count+nonSet::$inst_count.' ����� �������� � ��<br />';


echo (time()-$_big_time_marker_begin).' ���. <br />';*/
?>