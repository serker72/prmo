<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');

  
require_once('../classes/sched.class.php');
require_once('../classes/sched_history_fileitem.php');
require_once('../classes/sched_history_item.php');
require_once('../classes/sched_history_group.php');

$au=new AuthUser();
$result=$au->Auth(false,false,false);
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
 

$ret='';
if(isset($_POST['action'])&&($_POST['action']=="check_by_task")){
	//проверка, есть ли события  
	$id=abs((int)$_POST['id']);
	
	$myh=new Sched_HistoryGroup;
	$count_new=$myh->CalcNewByTask($id, $result['id']);	
	
	$ret=$count_new; 
	
} 

elseif(isset($_POST['action'])&&($_POST['action']=="check_by_role")){
	 
	$role_id=abs((int)$_POST['role_id']);
	
	$myh=new Sched_HistoryGroup;
	$count_new=$myh->CalcNewByRole($role_id, $result['id']);	
	
	$ret=$count_new; 
	
} 

elseif(isset($_POST['action'])&&($_POST['action']=="check_by_all")){
	 
 
	
	$myh=new Sched_HistoryGroup;
	$count_new=$myh->CalcNewByAllRoles( $result['id']);	
	
	$ret=$count_new; 
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="check_by_task_all")){
	//проверка, есть ли события  
	 
	$ids=$_POST['ids'];
	$_ret=array();
	
	foreach($ids as $k=>$id){
		$id=abs((int)$id);
		$myh=new Sched_HistoryGroup;
		$count_new=$myh->CalcNewByTask($id, $result['id']);	
		
		$_ret[]=implode(',', array($id, $count_new));
	}
	$ret=implode(';',$_ret); 
	
} 


//задачи без комментариев
elseif(isset($_GET['action'])&&(($_GET['action']=="calc_new_kind1"))){
	$_rem=new Sched_PopupGroup;
	
	$timeout=60*2; 
	 
	$do_go=true;
 	$kind=1;
	

	 
	
	
	if($do_go){
	
		
		//проверим МАРКЕР
		$sql='select * from sched_marker where kind_id="'.$kind.'" and user_id="'.$result['id'].'"  and ptime>="'.(time()-$timeout*60).'"';
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//$rc=0;
		
		
		if($rc>0){
			//маркер есть, возвращаем -1, ничего не делаем
			$ret=-1;
			
			//echo ' HAS MARK ';
		}else{
			//маркера нет, возвращаем актуальное число и вносим новый маркер	
			$ret=$_rem->CalcKind1($result['id']);
			
			$_mark=new Sched_MarkerItem;
			$mark=$_mark->GetItemByFields(array('kind_id'=>$kind, 'user_id'=>$result['id']));
			if($mark===false) $_mark->Add(array('kind_id'=>$kind, 'ptime'=>time(),  'user_id'=>$result['id']));
			
			else $_mark->Edit($mark['id'], array('ptime'=> time()));
			
			//echo ' NO MARK ';
		}
	
	}else{
		
		//echo ' NO TIME ';
		
		$ret=-1;
		
	}
	
}


elseif(isset($_GET['action'])&&($_GET['action']=="load_kind1")){
	//подгрузка данных для показа
	$_rem=new Sched_PopupGroup;
	$data=$_rem->ShowKind1($result['id']);
	$sm=new SmartyAj;
	
	$sm->assign('session_id', session_id());
	$sm->assign('items', $data);
	$ret=$sm->fetch('plan/plan_head_data1.html');
	
}

elseif(isset($_POST['action'])&&($_POST['action']=="put_work_statuses1")){
	
	$_ti=new Sched_TaskItem;
	$_th=new Sched_HistoryItem;
	$_tf=new Sched_HistoryFileItem;
	$_stat=new DocStatusItem;
	
	//$_lci=new Lead_CountsItem;
	$log=new ActionLog;
	
	//$kind=abs((int)$_POST['kind']);
	
	$data=$_POST['data'];
	if(is_array($data))	foreach($data as $k=>$val){
		$v=explode("/", $val);
		
		$tender_id=abs((int)$v[0]);
		
		$ti=$_ti->GetItemById($tender_id);
		
		if($v[1]==0) {
			
			 //внесем коммент
			$len_params=array();
			$len_params['sched_id']=$tender_id;
			$len_params['txt']=SecStr(iconv('utf-8', 'windows-1251', $v[2]));
			$len_params['user_id']=$result['id'];
			$len_params['pdate']=time();
			
			$len_code= $_th->Add($len_params);
			
			 
			
			$log->PutEntry($result['id'],'добавлен комментарий к задаче планировщика', NULL,905,NULL, $len_params['txt'],$tender_id);
		}else{
			//комментария нет
			
			$len_params=array();
			$len_params['sched_id']=$tender_id;
			$len_params['txt']=SecStr('<div>Автоматический комментарий: сотрудник '.$result['name_s'].' отказался дать комментарий к задаче '.$ti['code'].'.</div>');
			$len_params['user_id']=0;
			$len_params['pdate']=time();
			
			$len_code= $_th->Add($len_params);
			
			$log->PutEntry($result['id'],'отказ сотрудника дать комментарий по задаче',NULL,905,NULL,'отказ сотрудника дать комментарий по задаче '.$ti['code'],$tender_id);
			
			 
		 
		} 
	}

}


	
//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>