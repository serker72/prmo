<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/positem.php');
require_once('../classes/posgroupitem.php');
require_once('../classes/posgroupgroup.php');

require_once('../classes/posdimitem.php');
require_once('../classes/posdimgroup.php');
require_once('../classes/posgroup.php');

require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');

require_once('../classes/billitem.php');


require_once('../classes/billpospmformer.php');

require_once('../classes/billposgroup.php');

require_once('../classes/maxformer.php');


require_once('../classes/acc_notesgroup.php');
require_once('../classes/acc_notesitem.php');

require_once('../classes/acc_posgroup.php');
require_once('../classes/acc_notesitem.php');

require_once('../classes/acc_item.php');
require_once('../classes/acc_group.php');
require_once('../classes/user_s_item.php');
require_once('../classes/accreports.php');

require_once('../classes/acc_item.php');
require_once('../classes/period_checker.php');
require_once('../classes/holy_dates.php');
require_once('../classes/messageitem.php');

$result=array('id'=>0);

//����������� ���������� ����������� ������
//����������� ������������� � 17-15

//����� ������� ����? ������� ��� ��������?


$_hd=new HolyDates;

$pdate=mktime(0,0,0,date('m'),date('d'), date('Y'));
//$pdate=mktime(0,0,0,4,11, date('Y'));

//$pdate=datefromdmy('09.05.2014');
if($_hd->IsHolyday($pdate)) {
	echo '��������';

	exit();
}

//�������� ���� ������������ ���������� (��� �������, � ���� ������� is_viewed � ��� ������ �� � �������)
//($__ui['is_in_vacation']==1)&&(($__ui['vacation_till_pdate']+24*60*60)>time()

$sql='select * from user where is_active=1 and is_viewed=1';

$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();


$users_to_send=array();
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	if(($f['is_in_vacation']==1)&&(($f['vacation_till_pdate']+24*60*60)>time())){
		continue;	
	}
	$users_to_send[]=$f;
}

/*echo '���������� � �����������:';
echo '<pre>';
print_r($users_to_send);
echo '</pre>';
*/

$topic='����������, ��������� ������������ ��������� ������ � ����� ���������� �� ����!';
$_mi=new MessageItem;
foreach($users_to_send as $k=>$user){
	
	$txt='<div>';
	$txt.='<em>������ ��������� ������������� �������������.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='���������(��) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div></div>';
	
	$txt.='<div>';
	$txt.='����������, �� �������� ��������� ������������ ���������� ������ � ����� ���������� � ���������.';
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='����������� ��� �������� ������������ ������  <img src="/img/icons/menu_kontr.png" align="absmiddle" border=0 /> ��������� �� ����������� , <img src="/img/icons/menu_naob.png" align="absmiddle"  border=0 /> ������ �� ��������, <img src="/img/icons/menu_vyp_zayav.png" align="absmiddle"  border=0 />���������� ������.'; 
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='C ���������, ��������� "'.SITETITLE.'".';
	$txt.='</div>';
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}
 
	
?>