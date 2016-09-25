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

//напоминалка сотруднику просмотреть отчеты
//рассылается планировщиком в 17-15

//какой сегодня день? рабочий или выходной?


$_hd=new HolyDates;

$pdate=mktime(0,0,0,date('m'),date('d'), date('Y'));
//$pdate=mktime(0,0,0,4,11, date('Y'));


$pdate_from=$pdate+17*60*60+15*60; //17-15
$pdate_to=$pdate+20*60*60; //+15*60; //19-15

$activity_time=20; //20 sek
//echo date('d m Y H i s', $pdate_to);


//$pdate=datefromdmy('09.05.2014');
if($_hd->IsHolyday($pdate)) {
	echo 'праздник';

	exit();
}

//получить всех опрашиваемых сотрдников (кто активен, у кого галочка is_viewed и кто сейчас не в отпуске)
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

/*echo 'сотрудники к напоминанию:';
echo '<pre>';
print_r($users_to_send);
echo '</pre>';

*/
//получить сотрудников, кому отчитываемся...
$sql='select * from user where is_active=1 and is_viewer=1';

$set=new mysqlset($sql);
$rs=$set->GetResult();
$rc=$set->GetResultNumRows();


$users_to_report=array();
for($i=0; $i<$rc; $i++){
	$f=mysqli_fetch_array($rs);
	
	if(($f['is_in_vacation']==1)&&(($f['vacation_till_pdate']+24*60*60)>time())){
		continue;	
	}
	$users_to_report[]=$f;
}

/*echo 'сотрудники к отчетности:';
echo '<pre>';
print_r($users_to_report);
echo '</pre>';
*/
/*
//в.к.
//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчет Ведомость по контрагенту',NULL,111,NULL,NULL);	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Ведомость по контрагенту',NULL,111,NULL, NULL);	
	}
	
//заявки
	//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчет Выполнение заявок',NULL,368,NULL,NULL);	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'])||isset($_GET['doSub_x'])){
		$log->PutEntry($result['id'],'открыл отчет Невыполненные заявки',NULL,354,NULL, NULL);	
	}
//фиксировать открытие отчета
	if(isset($_GET['doSub2'])||isset($_GET['doSub2_x'])){
		$log->PutEntry($result['id'],'открыл отчет Выполненные заявки',NULL,366,NULL, NULL);	
	}
//оригиналы
	//фиксировать обращение к отчету
	$log->PutEntry($result['id'],'перешел в отчеты Оригиналы документов',NULL,352,NULL,NULL);	
	//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Оригиналы отгрузочных документов - Входящие',NULL,866,NULL, NULL);	
	}
//фиксировать открытие отчета
	if( isset($_GET['doSub'.$prefix])||isset($_GET['doSub'.$prefix.'_x'])||($print==1)){
		$log->PutEntry($result['id'],'открыл отчет Оригиналы отгрузочных документов - Исходящие',NULL,352,NULL, NULL);	
	}		

*/

$not_actions=array();

foreach($users_to_send as $k=>$v){
	
	$crits=array();
	
	//получить массив событий для ВК
	$crits[]=array(
			'caption'=>'<img src="/img/icons/menu_kontr.png" align="absmiddle" border=0 /> Отчет Ведомость по контрагенту',
			'object_ids'=>array(111),
			'descriptions'=>array('"перешел в отчет Ведомость по контрагенту"',
				'"открыл отчет Ведомость по контрагенту"'
				)
				);
	/*$crits[]=array(
			'caption'=>'Отчет Оригиналы документов',
			'object_ids'=>array(368, 866 ),
			'descriptions'=>array('"перешел в отчеты Оригиналы документов"',
				'"открыл отчет Оригиналы отгрузочных документов - Входящие"',
				'"открыл отчет Оригиналы отгрузочных документов - Исходящие"'
				)
				);*/
	
	$crits[]=array(
			'caption'=>'<img src="/img/icons/menu_naob.png" align="absmiddle"  border=0 /> Отчет Товары на объектах',
			'object_ids'=>array(135, 288 ),
			'descriptions'=>array('"перешел в отчет Товары на объектах"',
				'"открыл отчет Товары на объектах"' 
				)
				);			
					
	//получить массив событий для Заявки
					
	$crits[]=array(
			'caption'=>'<img src="/img/icons/menu_vyp_zayav.png" align="absmiddle"  border=0 />Отчет Выполнение заявок',
			'object_ids'=>array(352, 354, 366),
			'descriptions'=>array('"перешел в отчет Выполнение заявок"',
				'"открыл отчет Невыполненные заявки"',
				'"открыл отчет Выполненные заявки"'
				)
				);
				
	$maked_actions=array();		
	foreach($crits as $kk=>$crit){
	
		$sql='select * from action_log where
			( pdate between '.$pdate_from.' and  '.$pdate_to.')
			and description in(
			 '.implode(', ', $crit['descriptions']).'
			)
			and object_id in('.implode(', ', $crit['object_ids']).')
			and user_subj_id='.$v['id'].'
			order by pdate asc
			';
			
		$set=new mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		//echo $sql.'<br>';
		//$users_to_report=array();
		
		$records=array();	
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);	
			
			$records[]=$f;
			
			 
		}
		
		$maked_actions[]=array('caption'=>$crit['caption'],
								'records'=>$records,
								'status'=>-1, //не проверяли
								'status_descr'=>'проверка не проведена'
								); 
								
	
	}
	
	$v['maked_actions']=$maked_actions;
	$users_to_send[$k]=$v;
		
	 
}

//разобрать что получилось.
//должны по отдельности анализироваться 3 отчета

/*пользователи, у кого число записей =0  - не открывали
пользователи, у кого число записей==1 или >1, но интервал между первой и последней записями менее 20 сек - открывали, но недостаточно
пользователи, у кого число записей >1 И интервал между 1 и послю записями >=20 сек - все ОК
*/

$total_fails=0;
foreach($users_to_send as $k=>$user){
	
	foreach($user['maked_actions'] as $kk=>$vv){
		$records=$vv['records'];
		
		if(count($records)==0){
			$vv['status']=1;
			$vv['status_descr']='Отчет не был открыт';	
			$total_fails++;
			
		}elseif(count($records)>0){
			//нужно понимать, что пользователь ушел... позже ввести другой алгоритм - фиксировать выход
			
			if(($records[count($records)-1]['pdate']-$records[0]['pdate'])<$activity_time){
				$vv['status']=1;
				$vv['status_descr']='Работа с отчетом ниже минимального времени работы';	
				$total_fails++;	
			}else{
				$vv['status']=0;
				$vv['status_descr']='OK';	
			}
		}
		
		$user['maked_actions'][$kk]=$vv;	
	}
	
	$users_to_send[$k]=$user;	
}

/*echo '<pre>';
print_r($users_to_send);
echo '</pre>';*/
//echo $total_fails;


//составить письмо для администратора:
/*пользователь, 
отчет, нарушения- если есть нарушения (т.е. статус >0)*/

if($total_fails>0){
	
	foreach($users_to_report as $k=>$user){
		$_mi=new MessageItem;
	
		$topic='Невыполнение бизнес-процессов сотрудниками'	;
		
		$txt='';
		
		$txt.='<div>';
		$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
		$txt.='</div>';
		
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='Уважаемый(ая) '.$user['name_s'].'!';
		$txt.='</div>';
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
	 	$txt.='Сообщаем Вам, что сотрудниками не были сделаны следующие обязательные действия в конце рабочего дня:';
		$txt.='</div>';
		
		$txt.='<div>&nbsp;</div>';
		
		foreach($users_to_send as $kk=>$user1){
			$local_count_fails=0; $fails_description='';
			foreach($user1['maked_actions'] as $kk=>$vv){
				if($vv['status']>0){
					$local_count_fails++;
					$fails_description.=$vv['caption'].': '.$vv['status_descr'].'<br>';
					
				}
			}
			
			if($local_count_fails>0){
				$txt.='<div><strong>';
				$txt.=$user1['name_s'].' ('.$user1['login'].'):';
				$txt.='</strong><div></div>';
				$txt.=$fails_description;	
				$txt.='</div>';
				
				$txt.='<div>&nbsp;</div>';
			}
		}
		
		$txt.='<div>&nbsp;</div>';
		
		$txt.='<div>';
		$txt.='C уважением, программа "'.SITETITLE.'".';
		$txt.='</div>';
		
		//echo $txt;		
		$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
	}
	
}


//разослать сообщения самим сотрудникам
if($total_fails>0){
	
	foreach($users_to_send as $k=>$user){
		$local_count_fails=0; $fails_description='';
		foreach($user['maked_actions'] as $kk=>$vv){
			if($vv['status']>0){
				$local_count_fails++;
				$fails_description.=$vv['caption'].': '.$vv['status_descr'].'<br>';
				
			}
		}
		
		if($local_count_fails>0){
			$_mi=new MessageItem;
	
			$topic='Невыполнение обязательных бизнес-процессов'	;
		
			$txt='';
			
			$txt.='<div>';
			$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
			$txt.='</div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='Уважаемый(ая) '.$user['name_s'].'!';
			$txt.='</div>';
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='Сообщаем, что Вами не выполнены минимальные требования в конце рабочего дня:';
			$txt.='</div>';
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.=$fails_description;
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='О Вашем бездействии было сообщено сотрудникам:';
			$txt.='</div>';
			
			foreach($users_to_report as $k1=>$user1){
				$txt.='<div>';
				$txt.=$user1['name_s'].' ('.$user1['login'].')';
				$txt.='</div>';
			}
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='Пожалуйста, работайте в программе в рамках действующих бизнес-процессов!';
			$txt.='</div>';
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C уважением, программа "'.SITETITLE.'".';
			$txt.='</div>';
			
			//echo $txt;	
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
			
		}
					
		
	}
}

/*
$topic='Пожалуйста, проверьте заполнение Ваших документов в программе!';
$_mi=new MessageItem;
foreach($users_to_send as $k=>$user){
	
	$txt='<div>';
	$txt.='<em>Данное сообщение сгенерировано автоматически.</em>';
	$txt.=' </div>';
	
	
	$txt.='<div></div>';
	
	$txt.='<div>';
	$txt.='Уважаемый(ая) '.$user['name_s'].'!';
	$txt.='</div>';
	$txt.='<div></div>';
	
	$txt.='<div>';
	$txt.='Пожалуйста, не забудьте проверить правильность заполнения данных в ваших документах в программе.';
	$txt.='</div>';
	
	$txt.='<div></div>';
	
	$txt.='<div>';
	$txt.='Используйте для контроля правильности отчеты Ведомость по контрагенту, Оригиналы отгрузочных документов, Выполнение заявок.';
	$txt.='</div>';
	
	$txt.='<div></div>';
	
	$txt.='<div>';
	$txt.='C уважением, программа "'.SITETITLE.'".';
	$txt.='</div>';
	
	$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
}
 
	*/
	
	
?>