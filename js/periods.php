<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

require_once('../classes/pergroup.php');
require_once('../classes/peritem.php');

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}

$ret='';


if(isset($_POST['action'])&&($_POST['action']=="change_year")){
	$year=abs((int)$_POST['year']);
	$_pg=new PerGroup;
	
	$ret=$_pg->DrawPeriods($result['org_id'],$year,'periods/periods.html',$au->user_rights->CheckAccess('w',467),$au->user_rights->CheckAccess('w',468),  $result['org_id'], true, true,NULL, $au->user_rights->CheckAccess('w',240));
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_is_confirmer")){
	$state=abs((int)$_POST['state']);
	if($state==0){
		$ret='';	
	}elseif($state==1){
		$ret=$result['position_s'].' '.$result['name_s'].' '.' '.$result['login'].' '.date("d.m.Y H:i:s",time());	
	}
	
}elseif(isset($_POST['action'])&&($_POST['action']=="toggle_confirm")){
	$params=array();
	$params['org_id']=$result['org_id'];
	$params['pdate_beg']=abs((int)$_POST['pdate_beg']);
	$params['pdate_end']=abs((int)$_POST['pdate_end']);
	
	
	
	$state=abs((int)$_POST['state']);
	
	
	
	$_pi=new PerItem;
	$test_pi=$_pi->GetItemByFields(array('org_id'=>$result['org_id'], 'pdate_beg'=>$params['pdate_beg'], 'pdate_end'=>$params['pdate_end']));
	
	$can_do=true;
	if($test_pi!==false){
		if($test_pi['is_confirmed']==1){
			$can_do=$au->user_rights->CheckAccess('w',468);
			
			
		}else{
			$can_do=$au->user_rights->CheckAccess('w',467);
			
		}
		
		$params['is_confirmed']=$state;
		$params['confirm_pdate']=time();
		$params['user_confirm_id']=$result['id'];
		
		if($can_do) $_pi->Edit($test_pi['id'], $params);
	}else{
		$can_do=$au->user_rights->CheckAccess('w',467);	
		$params['is_confirmed']=$state; 
		$params['confirm_pdate']=time();
		$params['user_confirm_id']=$result['id'];
		if($can_do) $_pi->Add($params);
	}
	
	if(($test_pi===false)||($test_pi['is_confirmed']!=$state)){
		//запись в журнал!!!!	
		if($state==0){
			$log->PutEntry($result['id'],'снял закрытие периода',NULL,468,NULL,'снято закрытие периода с '.date('d.m.Y', $params['pdate_beg']).' по '.date('d.m.Y',$params['pdate_end']).'',NULL);
		}else{
			$log->PutEntry($result['id'],'закрыл период',NULL,467,NULL,'период с '.date('d.m.Y', $params['pdate_beg']).' по '.date('d.m.Y',$params['pdate_end']).' закрыт',NULL);
		}
	}
	
	
	$_pg=new PerGroup;
	
	$the_only_number=date('m',$params['pdate_beg']);
	switch($the_only_number){
		case 1:
		$the_only_number=1;
		break;
		case 4:
		$the_only_number=2;
		break;
		case 7:
		$the_only_number=3;
		break;
		case 10:
		$the_only_number=4;
		break;
	};
	
	$ret=$_pg->DrawPeriods($result['org_id'],date('Y', $params['pdate_beg']),'periods/periods.html', $au->user_rights->CheckAccess('w',467),$au->user_rights->CheckAccess('w',468),  $result['org_id'], true, false,$the_only_number, $au->user_rights->CheckAccess('w',240));
}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>