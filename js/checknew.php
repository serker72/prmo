<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/user_s_item.php');
require_once('../classes/questionitem.php');


require_once('../classes/messagegroup.php');
require_once('../classes/komplgroup.php');

require_once('../classes/positem.php');

require_once('../classes/actionlog.php');


require_once('../classes/positem.php');

require_once('../classes/usersgroup.php');
require_once('../classes/messageitem.php');


require_once('../classes/usersgroup.php');
require_once('../classes/messageitem.php');
require_once('../classes/messenger_group.php');
require_once('../classes/komplitem.php');
require_once('../classes/komplmarkposgroup.php');




if(isset($_GET['action'])&&($_GET['action']=="do_holyday_messages")){
	
		require_once('../classes/user_s_group.php');
		require_once('../classes/suppliers_birthday_group.php');
		$usg=new UsersSGroup;
		
	  $usg->AutoBirthdayMessagesMarkers(0);
	  $usg->AutoBirthdayMessagesMarkers(1);
	  
	  $usg->AutoBirthdayMessagesMarkers(3);
	  
	  $usg->AutoBirthdayMessagesMarkers(7);
	
	
	//�������� �� ��������� ������������
	$_sg=new SuppliersBirthGroup;
	$_sg->AutoBirthdayMessages(0);
	$_sg->AutoBirthdayMessages(1);
	$_sg->AutoBirthdayMessages(3);
	$_sg->AutoBirthdayMessages(7);
	
	  	
	
	//�������� �� �����
	
	$now=time();
	$ddate=date('d.m',$now);
	
	
	
	/*
	����� ������ ����������� �������, �������� ��� �����.	
	*/
	//������ 1� ������� ���� ����
	$day_of_stroy='';
	
	$pd=mktime(0,0,0,8,1,date("Y"));
	$_cter=0;
	for($i=$pd; $i<($pd+31*24*60*60); $i=$i+24*60*60){
		$td=date(' d.m ',$i);
		//echo $td.' '.date('w',$i);
		if(date('w',$i)==0){
			$_cter++;
		}
		if($_cter==2){
			$day_of_stroy=date('d.m', $i-24*60*60);
			break;
		}
	}
	
	
	echo '<strong>'.$day_of_stroy.'</strong><br />';
	
	//��������� ���������� - ������ �������� ����. ���������� ��������� � ������� ����� ����
	
	$day_of_meb='';
	
	$pd=mktime(0,0,0,6,1,date("Y"));
	$_cter=0;
	for($i=$pd; $i<($pd+31*24*60*60); $i=$i+24*60*60){
		$td=date(' d.m ',$i);
		//echo $td.' ';
		if(date('w',$i)==6){
			$_cter++;
		}
		if($_cter==2){
			$day_of_meb=date('d.m', $i-24*60*60);
			break;
		}
	}
	
	
	//echo '<strong>'.$day_of_meb.'</strong>';
	
	
		
	$time_to=false; $txt=''; $kind=0;
	switch($ddate){
		case '30.12':
			$time_to=true;
			$txt='<div>������� ������!</div>
<div><br /></div>			
<div>������������ ����� ������ � �������� �������� � ����� ���. �� ���� ���������� ��� � �������� � ����������� ���� ������� � ������, ������� ������� � ����� �� ���� ����������!</div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
';
			$kind=1;
		break;
		
		case '06.01':
			$time_to=true;
			$txt='
<div>������� ������!</div>
<div><br /></div>
<div>����������� ��� � ���������� ��������� ��������.</div>
<div>������ ��� ������� � ��������, ���� � �����!</div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
			
			';
			$kind=2;
		break;
		
		case '22.02':
			$time_to=true;
			$txt='
			<div>������� ������!</div>
			<div><br /></div>
<div>����������� ������� ����� ������������� ����� ��������� � ���� ��������� ���������. � ���� ����� ��� ���� � ����-��������� ��������� � �� ���� �������� ��� �������. ������ ���������, �������� � �����!</div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>

			
			';
			$kind=3;
		break;
		
		case '07.03':
			$time_to=true;
			$txt='<div>������� ������!</div>
<div><br /></div>			
<div>� ����� �������������� �������� ��� �� ���������� � ����� �����, ���������� � ����� ���������. ������ ��� � ���� �������� ����������� ���� ������, �����, ��������, ��������������� � ����������� ������� �������.</div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
';
			$kind=4;
		break;
		
		case '08.05':
			$time_to=true;
			$txt='
<div>������� ������!</div>
<div><br /></div>
<div>����������� ��� � ���� ������! ����� ��� ����� ���� ���� � ������� ��������� ��� ��� ��� � ���� �����. �� ����� ������� �� ���� ������ � ����� �� ������.</div>
<div><br /></div>
<div>������ � ������� ������!</div>
<div>� �� � ���� � ������ �� �����! </div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
			
			
			';
			$kind=5;
		break;
		
		
		/*case $day_of_stroy:
			$time_to=true;
			$txt='
			<div>������� ������!</div>
			<div><br /></div>
			<div>����������� ��� � ���������������� ���������� - ���� ���������. </div>
<div>������ ��� ���������� ��������, ������ ����������, �������� ���������� �, �������, ������ � ����������� ��������.<div>

			<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
			
			
			';
			$kind=6;
		break;*/
		
		case '03.11':
			$time_to=true;
			$txt='
<div>������� ������!</div>
<div><br /></div>
<div>����������� ��� � ���� ��������� ��������. �� � ����������������� ������ � ���� ������� ������� �����!</div>
<div>������ ��� ���������� �������� � �������� ������� ����! </div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
			
			
			';
			$kind=7;
		break;
		
		case '11.06':
			$time_to=true;
			$txt='
<div>������� ������!</div>
<div><br /></div>
<div>����������� ��� � ���� ������! ������ � ������ � ������� ������� � �� ������� � �� ������� �������! ������ ������, ���������� ����� ��� ��� � ����� ������!</div>
<div><br /></div>
<div>� ���������, ��������� �'.SITETITLE.'� � ������� �������������.</div>
			
			
			';
			$kind=8;
		break;
		
	};
	
	if($time_to){
		//��������� ���� �� ���, ��������� ������. ���� ������� ��� - �� ���������, ��������� ������	
		
		
		$s1=new mysqlSet('select * from holyday_markers where kind="'.$kind.'" and (ptime between '.mktime(0,0,0,date('n'),date('d'),date('Y')).' and '.mktime(23,59,59,date('n'),date('d'),date('Y')).') order by ptime desc limit 1');
		//echo mysql_error();
		$rc1=$s1->getResultNumRows();
		$rs1=$s1->getResult();
		
		
		if($rc1==0){
			$_ug=new UsersGroup;
			$users=$_ug->GetItemsArr(0,1);
			//GetItemsArr($current_id=0,  $is_shown=0)
			
			$_mi=new MessageItem;
			foreach($users as $k=>$v){
				$mparams=array();
				$mparams['topic']='������������ � ����������';
				$mparams['txt']=$txt;
				$mparams['to_id']= $v['id'];
				$mparams['from_id']=-1; //�������������� ������� �������� ���������
				$mparams['pdate']=time();
				
				$_mi->Send(0,0,$mparams);
			}
			
			
			$s3=new NonSet('insert into holyday_markers (ptime, expiration, kind) values('.time().', 31536000, '.$kind.')');
		}
	}
	
	exit();	
}







$au=new AuthUser();
$result=$au->Auth(false,false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

//��������� �������� �� �����-�� ������
function CheckKomplektMarkers(){
	$mg=new KomplMarkGroup; $pg=new KomplMarkPosGroup;
	$_mi=new KomplMarkItem;
	$_ki=new KomplItem; $_kpi=new KomplPosItem;
	$_kci=new KomplConfItem;
	$_pos=new PosItem;
	$all_markers=$mg->GetItemsArr();
	$_kni=new KomplNotesItem;
	$log=new ActionLog;
	$_kcri=new KomplConfRoleItem;
	
	foreach($all_markers as $k=>$v){
		if(((int)$v['ptime']+(int)$v['expiration'])<=time()){
			//���������� ������	
			
			$ki=$_ki->GetItemById($v['komplekt_ved_id']);
			if((($ki['status_id']==1)||($ki['status_id']==11))&&(!$_ki->DocCanUnconfirm($v['komplekt_ved_id']))){
				
				//������������ ������
				//������ ������ ���-��
				$positions_to_change=$pg->GetItemsByIdArr($v['id']);
				
				//var_dump($positions_to_change);
				 $notes_texts=array(); $log_texts=array();
				foreach($positions_to_change as $kk=>$vv){
					$test_kpi=$_kpi->GetItemByFields(array('komplekt_ved_id'=>$v['komplekt_ved_id'], 'position_id'=>$vv['position_id'] ));
					
					if($test_kpi!==false){ 
						$_kpi->Edit($test_kpi['id'], array('quantity_confirmed'=>$vv['quantity']));
					
						if($test_kpi['quantity_confirmed']!=$vv['quantity']){
						$pos=$_pos->GetItemById($vv['position_id']);
						  //� ������, � ����������...
						  $notes_texts[]='�������������� ����������: ���������� ������� '.SecStr($pos['name']).' ��������������� ������������� ��� �������������� ����������� ������, ���� '.$test_kpi['quantity_confirmed'].', ����� '.$vv['quantity'];
						  
						  $log_texts[]='���������� ������� '.SecStr($pos['name']).' ��������������� ������������� ��� �������������� ����������� ������, ���� '.$test_kpi['quantity_confirmed'].', ����� '.$vv['quantity'];
						}
					}
				}
				
				foreach($notes_texts as $kk=>$vv){
					$_kni->Add(array('user_id'=>$v['komplekt_ved_id'], 	'pdate'=>time(), 	'note'=>$vv, 	'posted_user_id'=>0, 	'is_auto'=>1));
				}
				foreach($log_texts as $kk=>$vv){
					$log->PutEntry(0,'�������������� ���-�� ������� ������ � ����� � ��������������� �����������', NULL, 82, NULL, $vv, $v['komplekt_ved_id']);
				}
					
				
				 
				
				//������������� ������...
				$_ki->ScanDocStatus($v['komplekt_ved_id'],array(),array());
					
			}
			
			//������� ������
			$_mi->Del($v['id']);
			
		}
	} 
	
}


$ret='';
if(isset($_POST['action'])&&($_POST['action']=="check_s_orders")){
	//��������, ���� �� ������� ��� s-������
	$id=abs((int)$_POST['id']);
	
	$myh=new SOrderHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_d_orders")){
	//��������, ���� �� ������� ��� d-������
	$id=abs((int)$_POST['id']);
	
	$myh=new MyOrderHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_d_pret")){
	//��������, ���� �� ������� ��� d-reklamaciy
	$id=abs((int)$_POST['id']);
	
	$myh=new MyReclamHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_s_pret")){
	//��������, ���� �� ������� ��� s-reklamaciy
	$id=abs((int)$_POST['id']);
	
	$myh=new SReclamHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_d_claim")){
	//��������, ���� �� ������� ��� d-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new MyClaimHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}elseif(isset($_POST['action'])&&($_POST['action']=="check_s_claim")){
	//��������, ���� �� ������� ��� s-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new SClaimHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}

//�������� �������� ������� � �������
/*elseif(isset($_POST['action'])&&($_POST['action']=="check_task_1")){
	//��������, ���� �� ������� ��� s-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new TaskIncomingHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}

//�������� ��������� ������� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_task_2")){
	//��������, ���� �� ������� ��� s-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new TaskOutcomingHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}
//�������� ������������ � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_mission")){
	//��������, ���� �� ������� ��� s-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new MissionHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}
//��������, ������� ����� ������������ � ����
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_missions")){
	
	 $mg=new MissionHistoryGroup;
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}

//�������� �������� ������� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_task_3")){
	//��������, ���� �� ������� ��� s-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new TaskAllHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}
//��������, ������� ����� ������� � ����
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_tasks")){
	
	 $mg=new TaskIncomingHistoryGroup;
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}
//��������, ������� ����� ������� � ���������� �������
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_tasks_mode")){
	
	$mode=abs((int)$_POST['mode']);
	switch($mode){
		case 1:
			$mg=new TaskIncomingHistoryGroup;
		break;
		case 2:
			$mg=new TaskOutcomingHistoryGroup;
		break;
		case 3:
			$mg=new TaskAllHistoryGroup;
		break;
		default:
			$mg=new TaskIncomingHistoryGroup;
		break;
	};
	
	
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}
*/
elseif(isset($_POST['action'])&&($_POST['action']=="check_messages")){
	//��������, ���� �� ������� ��� s-zayavok
	$id=abs((int)$_POST['id']);
	
	$myh=new MessageGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	$ret=$count_new;
	
	
	
	
}elseif(isset($_POST['action'])&&($_POST['action']=="calc_new_messages")){
	//��������, ������� ����� ���������
	
	$mg=new MessageGroup();
	
	$count_new=$mg->CalcNew($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	CheckKomplektMarkers();
	
	

}elseif(isset($_POST['action'])&&($_POST['action']=="calc_new_messenger")){
	//��������, ������� ����� ��������� �����������
	
	$mg=new MessengerGroup;
	
	$count_new=$mg->CalcNew($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;

	
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="total_count_orders")){
	//��������, ������� ����� zakazov
	 $mg=new SOrderHistoryGroup();
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}elseif(isset($_POST['action'])&&($_POST['action']=="total_count_myorders")){
	//��������, ������� ����� zakazov
	 $mg=new MyOrderHistoryGroup();
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}elseif(isset($_POST['action'])&&($_POST['action']=="total_count_pret")){
	//��������, ������� ����� zakazov
	 $mg=new SReclamHistoryGroup();
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}elseif(isset($_POST['action'])&&($_POST['action']=="total_count_mypret")){
	//��������, ������� ����� zakazov
	 $mg=new MyReclamHistoryGroup();
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}elseif(isset($_POST['action'])&&($_POST['action']=="total_count_claim")){
	//��������, ������� ����� zakazov
	 $mg=new SClaimHistoryGroup();
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}elseif(isset($_POST['action'])&&($_POST['action']=="total_count_myclaim")){
	//��������, ������� ����� zakazov
	 $mg=new MyClaimHistoryGroup();
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}


elseif(isset($_POST['action'])&&($_POST['action']=="total_count_komplekts")){
	//��������, ������� ����� zakazov
	 $mg=new KomplGroup;
	

	
	if($result['is_supply_user']==1){
	  if($au->FltSector($result)){
		  //������ �� �������
		   $_sectors_to_user=new SectorToUser();
		
		  $filter=$_sectors_to_user->GetSectorIdsArr($result['id']);
	  }else{
		  //��� ������	
		  $filter=array();
		  
		 
	  }
	  
	  $_kg=new KomplGroup;
	   $count_of_komplekts=$_kg->CalcNew($filter);
	  if($count_of_komplekts>0) $count_of_komplekts='('.$count_of_komplekts.')';
	  
	}else $count_of_komplekts=0;
	
	
	$ret=$count_of_komplekts;
	
}

/*
//�������� �������� �� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_memos_1")){
	 
	$id=abs((int)$_POST['id']);
	
	$myh=new MemoIncomingHistoryGroup();
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}

//�������� ��������� �� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_memos_2")){
	 
	$id=abs((int)$_POST['id']);
	
	$myh=new MemoOutcomingHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}

//�������� �������� �� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_memos_3")){
	 
	$id=abs((int)$_POST['id']);
	
	$myh=new MemoAllHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}
//��������, ������� ����� �� � ����
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_memos")){
	
	 $mg=new MemoIncomingHistoryGroup;
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}
//��������, ������� ����� ������� � ���������� �������
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_memos_mode")){
	
	$mode=abs((int)$_POST['mode']);
	switch($mode){
		case 1:
			$mg=new MemoIncomingHistoryGroup;
		break;
		case 2:
			$mg=new MemoOutcomingHistoryGroup;
		break;
		case 3:
			$mg=new MemoAllHistoryGroup;
		break;
		default:
			$mg=new MemoIncomingHistoryGroup;
		break;
	};
	
	
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}


//��������, ������� ����� ������� � ���������� �������
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_petitions_mode")){
	
	$mode=abs((int)$_POST['mode']);
	switch($mode){
		case 1:
			$mg=new PetitionMyHistoryGroup;
		break;
		case 2:
			$mg=new PetitionMyHistoryGroup;
		break;
		case 3:
			$mg=new PetitionAllHistoryGroup;
		break;
		default:
			$mg=new PetitionMyHistoryGroup;
		break;
	};
	
	
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}
//�������� ��������� �� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_petitions_2")){
	 
	$id=abs((int)$_POST['id']);
	
	$myh=new PetitionMyHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}

//�������� �������� �� � �������
elseif(isset($_POST['action'])&&($_POST['action']=="check_petitions_3")){
	 
	$id=abs((int)$_POST['id']);
	
	$myh=new PetitionAllHistoryGroup;
	$count_new=$myh->CountNew($id, $result['id']);	
	
	if($count_new>0){
		$sm=new SmartyAj;
		$ret=$sm->fetch('newdata.html');
	}else $ret='';
	
}
//��������, ������� ����� �� � ����
elseif(isset($_POST['action'])&&($_POST['action']=="total_count_petitions")){
	
	 $mg=new PetitionMyHistoryGroup;
	
	$count_new=$mg->CountNewOrders($result['id']);
	if($count_new>0) $count_new='('.$count_new.')';
	$ret=$count_new;
	
}

*/




	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>