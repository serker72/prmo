<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
 
 
require_once('../classes/bdetailsgroup.php');
require_once('../classes/bdetailsitem.php');
require_once('../classes/suppliersgroup.php');
require_once('../classes/supplieritem.php');
 
require_once('../classes/acc_notesitem.php');
 
require_once('../classes/user_s_item.php');
 
 
require_once('../classes/period_checker.php');
require_once('../classes/holy_dates.php');
require_once('../classes/messageitem.php');

require_once('../classes/sched.class.php');

/* письма от планировщика по задачам для:
ответственного
исполнителя
наблюдателя

*/

/*
письма о просроченных задачах.
*/ 

//$sql='select distinct user_id from ';


$sql='select * from user where is_active=1 and id in( select distinct user_id from sched_task_users where was_informed=0 and  kind_id in(2,3,4) and sched_id in(select id from sched where status_id<>3 and status_id<>10))';

//echo $sql;

$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();


$users_to_send=array();
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	if(($f['is_in_vacation']==1)&&(($f['vacation_till_pdate']+24*60*60)>time())){
		//continue;	
	}
	$users_to_send[]=$f;
}

//print_r($users_to_send); die();


$topic='Новые задачи в GYDEX.Планировщик';
$_mi=new MessageItem; $_task=new Sched_TaskItem; 
foreach($users_to_send as $k1=>$user){
	
	$txt='<div>';
	$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='Уважаемый(ая) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div>&nbsp;</div>';
	
	
	$txt.='<div>';
	$txt.='<strong>В GYDEX.Планировщике у Вас появились следующие задачи:</strong>';
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div><ul>';
	
	$sql='select * from sched where status_id<>3 and status_id<>10  and id in(select distinct sched_id from  sched_task_users where  was_informed=0 and  kind_id in(2,3,4) and user_id="'.$user['id'].'" )';
	
	$set1=new mysqlset($sql);
	$rs1=$set1->GetResult();
	$rc1=$set1->GetResultNumRows();
	
	

	for($j=0; $j<$rc1; $j++){
		$g=mysqli_fetch_array($rs1);
		
		$txt.='<li>';
		$txt.='<a href="ed_sched_task.php?action=1&id='.$g['id'].'" target="_blank">'.$_task->ConstructFullName($g['id'], $g).'</a>';
		
		if($g['pdate_beg']!="") $txt.=',<strong> крайний срок:</strong><em> '.DateFromYmd($g['pdate_beg']).' '.$g['ptime_beg'].'</em>';
		
		$txt.=', <strong>Ваша роль:</strong> <em>';
		
		//найдем роли...
		$sql2=' select distinct k.kind_id, p.name 
		from sched_task_users as k
		inner join sched_task_users_kind as p on p.id=k.kind_id
		where k.sched_id="'.$g['id'].'" and k.user_id="'.$user['id'].'"
		order by k.kind_id';
		
		//echo $sql2;
		
		$set2=new mysqlset($sql2);
		$rs2=$set2->GetResult();
		$rc2=$set2->GetResultNumRows();
		
		
		$roles=array();
		for($k=0; $k<$rc2; $k++){
			$h=mysqli_fetch_array($rs2);
			$roles[]=$h['name'];
			
			
			
		}
		
		$txt.=implode(', ', $roles);
		
		$txt.='</em></li>';
		
		//обновим поле "информирован"
		$sql3='update  sched_task_users set was_informed=1 where user_id="'.$user['id'].'" and sched_id="'.$g['id'].'" ';
		new NonSet($sql3);
		
		
		
	//	print_r($g);	
	}
	
	
	
	$txt.='</ul><div>&nbsp;</div>';
	
	$txt.='<div><strong>Просим своевременно выполнять все поставленные задачи!</strong></div>';
	
	
	$txt.='<div>&nbsp;</div>';

	$txt.='<div>';
	$txt.='C уважением, программа "'.SITETITLE.'".';
	$txt.='</div>';
	
	
	
	//echo $txt;
	
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}



/*
письма о просроченных задачах.
найти всех: ответственных, соисполнителей, у кого задача просрочена: есть первая галочка, нет второйо галочки.

по каждому сотруднику: найти просрочнные задачи, в которых он: отв, соисп-ль.

по каждой задаче расписать фулл нейм, рол(и) сотрудника



*/ 

$sql='
(
select * from user where is_active=1 and id in( select distinct user_id from sched_task_users where kind_id in(2,3) and sched_id in(select id from sched where kind_id=1 and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'"))
)
UNION
(
select * from user where is_active=1 and id in(select distinct manager_id from sched where kind_id in(2, 3, 4) and plan_or_fact="0" and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'")

)

';

//echo $sql;  

$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();


$users_to_send=array();
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	if(($f['is_in_vacation']==1)&&(($f['vacation_till_pdate']+24*60*60)>time())){
		//continue;	
	}
	$users_to_send[]=$f;
}

//print_r($users_to_send); die();


$topic='Просроченные встречи/задачи/звонки/командировки в GYDEX.Планировщик';
$_mi=new MessageItem; $_task=new Sched_TaskItem; 
foreach($users_to_send as $k1=>$user){
	
	$txt='<div>';
	$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div>&nbsp;</div>';
	
	$txt.='<div>';
	$txt.='Уважаемый(ая) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div>&nbsp;</div>';
	
	
	$txt.='<div>';
	$txt.='<strong>В GYDEX.Планировщик у Вас просрочены следующие встречи/задачи/звонки/командировки:</strong>';
	$txt.='</div>';
	
	$txt.='<div>&nbsp;</div><ul>';
	
	$sql='
	
	(
	select * from sched where kind_id=1 and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'"  and id in(select distinct sched_id from  sched_task_users where   kind_id in(2,3 ) and user_id="'.$user['id'].'" )
	)
	UNION(
	 select * from sched where kind_id in(2, 3, 4) and plan_or_fact="0" and status_id<>3 and is_confirmed="1" and is_confirmed_done="0" and pdate_beg is not null and ptime_beg is not null and pdate_beg<="'.date('Y-m-d').'" and ptime_beg<="'.date('H:i:s').'" and manager_id="'.$user['id'].'"
	)
	
	';
	
	
	
	$set1=new mysqlset($sql);
	$rs1=$set1->GetResult();
	$rc1=$set1->GetResultNumRows();
	
	

	for($j=0; $j<$rc1; $j++){
		$g=mysqli_fetch_array($rs1);
		
		 $_res=new Sched_Resolver($g['kind_id']);
		
		$txt.='<li>';
		$txt.='<a href="/ed_sched.php?action=1&id='.$g['id'].'" target="_blank">'.$_res->instance->ConstructFullName($g['id'], $g).'</a>';
		if($g['kind_id']==1){
		
			if($g['pdate_beg']!="") $txt.=',<strong> крайний срок:</strong> <em>'.DateFromYmd($g['pdate_beg']).' '.$g['ptime_beg'].'</em>';
			$txt.=', <strong>Ваша роль:</strong> <em>';
			
			//найдем роли...
			$sql2=' select distinct k.kind_id, p.name 
			from sched_task_users as k
			inner join sched_task_users_kind as p on p.id=k.kind_id
			where k.sched_id="'.$g['id'].'" and k.user_id="'.$user['id'].'"
			order by k.kind_id';
			
			//echo $sql2;
			
			$set2=new mysqlset($sql2);
			$rs2=$set2->GetResult();
			$rc2=$set2->GetResultNumRows();
			
			
			$roles=array();
			for($k=0; $k<$rc2; $k++){
				$h=mysqli_fetch_array($rs2);
				$roles[]=$h['name'];
				
				
				
			}
			
			$txt.=implode(', ', $roles);
			
			$txt.='</em></li>';
		}else{
			if($g['pdate_beg']!="") $txt.=', <strong>дата/время начала:</strong> <em>'.DateFromYmd($g['pdate_beg']).' '.$g['ptime_beg'].'</em>';
		}
		 
		
		
	//	print_r($g);	
	}
	
		
	$txt.='</ul><div>&nbsp;</div>';
	
	$txt.='<div><strong>Просим своевременно выполнять все поставленные задачи!<br>
О данной проблеме мы автоматически уведомили постановщиков задач!</strong>
</div>';
	
	
	$txt.='<div>&nbsp;</div>';

	$txt.='<div>';
	$txt.='C уважением, программа "'.SITETITLE.'".';
	$txt.='</div>';
	
	
	
//	echo $txt;
	
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}



exit();
	
?>