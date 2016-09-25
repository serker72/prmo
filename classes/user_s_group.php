<?
require_once('abstractgroup.php');
//require_once('storagegroup.php');
//require_once('sectorgroup.php');
require_once('user_s_item.php');
require_once('actionlog.php');
require_once('messageitem.php');


require_once('usercontactdatagroup.php');
require_once('suppliercontactkindgroup.php');

require_once('user_int_group.php');
require_once('user_s_item.php');

require_once('authuser.php');
require_once('user_to_user.php');

require_once('user_view.class.php');


// users S
class UsersSGroup extends AbstractGroup {
	protected $group_id;
	public $instance;
	public $pagename;

	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='users_s.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=1;
		$this->instance=new UserSItem;
		
		$this->_view=new User_ViewGroup;


	}
	
	
	public function GetItems($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE, $in_storage=NULL, $in_sector=NULL,$can_view_inactive=false, $can_print=false, $prefix='', $tab_page=3, $limited_user=NULL){
		$txt='';
		
		$sm=new SmartyAdm;
		
		
		$sql='select u.*,
		man.name_s as manager_name
		
		from '.$this->tablename.'  as u 
		left join user as man on man.id=u.manager_id
		
		 where u.group_id="'.$this->group_id.'"
			  ';
		
		$sql_count='select count(*) from '.$this->tablename.' as u 
		left join user as man on man.id=u.manager_id
		 where u.group_id="'.$this->group_id.'"
			
		 ';
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		
		/*if($in_storage!==NULL){
			if(strlen($db_flt)>0) $db_flt.=' and ';
			$db_flt.=' (id in(select distinct nach_user_id from storage where id="'.$in_storage.'") or id in(select distinct zamnach_user_id from storage where id="'.$in_storage.'"))';
			
		}
		
		if($in_sector!==NULL){
			if(strlen($db_flt)>0) $db_flt.=' and ';
			$db_flt.=' (id in(select distinct nach_user_id from sector where id="'.$in_sector.'") or id in(select distinct zamnach_user_id from sector where id="'.$in_sector.'"))';	
		
		}*/
		
		if($limited_user!==NULL){
			if(strlen($db_flt)>0) $db_flt.=' and ';
			$db_flt.=' u.id in('.implode(', ', $limited_user).')';
		}
		
		if(strlen($db_flt)>0){
			
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from'.$prefix);

		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
		$_ukg=new UserContactDataGroup;
		
		$_uints=new UserIntGroup;
		$_ui=new UserSItem;
		
		
		$au=new AuthUser();
		//проверка возможности показа кнопки Создать карту сотрудника
		$sm->assign('can_create', $au->user_rights->CheckAccess('w',10));
		
		
		//проверка возможности показа кнопки подробно
		$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11));
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//print_r($f);
			
			$f['is_in_vac']=(($f['vacation_till_pdate']+24*60*60)>=time())&&($f['is_in_vacation']==1);
			$f['vacation_till_pdate_f']=date("d.m.Y",$f['vacation_till_pdate']);
			
			 $f['basic_email_s']= $f['email_s'];
			

			
			//контакты
			$ukg=$_ukg->GetItemsByIdArr($f['id']);
			//1,3 - rab,sot
			$f['phone_work_s']='';
			$f['phone_cell_s']='';
			$f['email_s']='';
			
			
			//5 - email
			
			foreach($ukg as $k=>$v){
				if($v['kind_id']==1) $f['phone_work_s'].=' '.stripslashes($v['value']);	
				if($v['kind_id']==3) $f['phone_cell_s'].=' '.stripslashes($v['value']);	
				if(($v['kind_id']==5)&&( $f['basic_email_s']!=$v['value'])) $f['email_s'].=' '.stripslashes($v['value']);	
			
			}
			
			
			$f['ints']=$_uints->GetItemsByIdArr($f['id']);
			
			$f['pod']=$_ui->GetSubsArr($f['id']);
			

			
			$f['can_edit']=($au->user_rights->CheckAccess('w',11)||($f['id']==$this->_auth_result['id']));
			

			
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		$current_storage='';
		$current_sector='';
		foreach($fields as $k=>$v){
			if($v->GetName()=='storage') $current_storage=$v->GetValue();
			if($v->GetName()=='sector') $current_sector=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('storage',$current_storage);
		$sm->assign('sector',$current_sector);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		
		$sm->assign('prefix', $prefix);
		$sm->assign('pagename', $this->pagename);
		$sm->assign('ed_pagename', $this->instance->pagename);
		
		$sm->assign('tab_page',$tab_page);

		
		
	/*	$_sto_grp=new StorageGroup;
		$_sec_grp=new SectorGroup;
		
		$storages=$_sto_grp->GetItemsArr();
		$sectors=$_sec_grp->GetItemsArr();
		
		$sts=array(); $stn=array();
		$sts[]=''; $stn[]='';
		foreach($storages as $k=>$v){
			$sts[]=$v['id'];
			$stn[]=stripslashes($v['name']);
			
		}
		$sm->assign('storage_ids',$sts);
		$sm->assign('storage_names',$stn);
		
		$sts=array(); $stn=array();
		$sts[]=''; $stn[]='';
		foreach($sectors as $k=>$v){
			$sts[]=$v['id'];
			$stn[]=stripslashes($v['name']);
			
		}
		$sm->assign('sector_ids',$sts);
		$sm->assign('sector_names',$stn);*/
		
		
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzz';
		
		$sm->assign('can_view_inactive', $can_view_inactive);
		$sm->assign('can_print', $can_print);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		return $sm->fetch($template);
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0, $sortmode=0){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($sortmode==0) $ord_flt=' order by login asc';
		else $ord_flt=' order by name_s asc, login asc';
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.'  '.$ord_flt);
		else $set=new MysqlSet('select * from '.$this->tablename.' where  '.$this->vis_name.'="1" '.$ord_flt);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//сотрудники отдела снабжения
	public function GetSupplyUsers(){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		$set=new MysqlSet('select * from '.$this->tablename.' where  '.$this->vis_name.'="1" and is_supply_user=1');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	public function AutoBirthdayMessages($days){
		$_log=new ActionLog;
		$mi=new MessageItem;
		$d2=time();
		$y2=mktime(0,0,0,0,0,date("Y"));
		
		$sql='select * from '.$this->tablename.' where is_active=1 and pasp_bithday<>0';
		$set1=new mysqlSet($sql);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		//echo 'число дней: '.$days.'<br>';
		if($days==0) $days_c=0;
		else $days_c=$days-1;
	//	$days_c=$days;
		
		
		
		
		for($i=0; $i<$rc1; $i++){
			$f=mysqli_fetch_array($rs1);
			
			$bd=$f['pasp_bithday'];
			$y1=mktime(0,0,0,0,0,date("Y",$bd));
			
			//echo ($bd+($y2-$y1)).'  ('.date('d.m.Y H:i:s',($bd+($y2-$y1))).') ? '.datefromdmy(date("d.m.Y",$d2)).' ('.date('d.m.Y H:i:s',datefromdmy(date("d.m.Y",$d2))).')<br>'; //.$d2.'('.date('d.m.Y H:i:s',$d2).')<br>';
			//$run=false;
			if($days_c==0){
				//если 0 дней - сегодня
				if( 
				mktime(0,0,0,date("m",$bd),date("d",$bd),0) == mktime(0,0,0,date("m",$d2),date("d",$d2),0) ){
					//срок наступил	
						if(($f['is_bith_'.$days.'_days']==0)){
							//не рассылали
							
							//выполним рассылку, 
							$sql2='select * from '.$this->tablename.' where is_active=1 and id<>"'.$f['id'].'"';
							$set2=new mysqlSet($sql2);
							$rs2=$set2->GetResult();
							$rc2=$set2->GetResultNumRows();
							
							for($j=0; $j<$rc2; $j++){
								$g=mysqli_fetch_array($rs2);
								$_dl='';
								
								if($days==0) $_dl="Сегодня";
								elseif($days==1) $_dl="Через один день";
								elseif($days==3) $_dl="Через три дня";
								elseif($days==7) $_dl="Через семь дней";
								
								$message_to_managers="
							  <div><em>Данное сообщение сгенерировано автоматически.</em></div>
							  <div>Уважаемые коллеги!</div>
							  <div>".$_dl.", а именно, ".date("d.m.",$f['pasp_bithday']).date("Y",$d2).", день рождения сотрудника:</div>
							  <div>".stripslashes($f['name_s'])." (логин ".stripslashes($f['login']).").</div>
							 
							  ";
							  
							  
							  
							  //echo($message_to_dealer);
							  //echo $message_to_managers;
							  //дилеру
							  
								$params1=array();
								
								$params1['topic']=$_dl.' день рождения сотрудника '.stripslashes($f['name_s'])." (логин ".stripslashes($f['login']).')';
								$params1['txt']=$message_to_managers;
								$params1['to_id']= $g['id'];
								$params1['from_id']=-1; //Автоматическая система рассылки сообщений
								$params1['pdate']=time();
								
								$mi->Send(0,0,$params1,false);
								
								
							   // $_log->PutEntry(0, "Автоматическая система рассылки сообщений",$params1['to_id'],NULL,NULL,"тема сообщения: ".$params1['topic']." текст сообщения: ".$params1['txt']);
								
							}
							
							//внесем флаг 1
							$_ui=new UserSItem;
							$_ui->Edit($f['id'],array('is_bith_'.$days.'_days'=>1));
						}
				}elseif(mktime(0,0,0,date("m",$bd),date("d",$bd),0) >= mktime(0,0,0,date("m",$d2),date("d",$d2),0)){
					//echo " еще не был, срок не наступил, обнуляю флаг: ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days_c*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
						
						$_ui=new UserSItem;
						$_ui->Edit($f['id'],array('is_bith_'.$days.'_days'=>0));
						
				}
				
			}else{ //если не 0 дней - не сегодня
				if(($bd+($y2-$y1))>=datefromdmy(date("d.m.Y",$d2))){ //$d2){
					//д.р. в этом году еще не было
					//echo "еще не был, ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days_c*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
					
					
					if(($bd+($y2-$y1)-($days_c*60*60*24))==datefromdmy(date("d.m.Y",$d2))){
						
						//echo "еще не был, срок дней: ".$days." ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days_c*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
						
						//срок наступил	
						if(($f['is_bith_'.$days.'_days']==0)){
							//не рассылали
							
							//выполним рассылку, 
							$sql2='select * from '.$this->tablename.' where is_active=1 and id<>"'.$f['id'].'"';
							$set2=new mysqlSet($sql2);
							$rs2=$set2->GetResult();
							$rc2=$set2->GetResultNumRows();
							
							for($j=0; $j<$rc2; $j++){
								$g=mysqli_fetch_array($rs2);
								$_dl='';
								
								if($days==0) $_dl="Сегодня";
								elseif($days==1) $_dl="Через один день";
								elseif($days==3) $_dl="Через три дня";
								elseif($days==7) $_dl="Через семь дней";
								
								$message_to_managers="
							  <div><em>Данное сообщение сгенерировано автоматически.</em></div>
							  <div>Уважаемые коллеги!</div>
							  <div>".$_dl.", а именно, ".date("d.m.",$f['pasp_bithday']).date("Y",$d2).", день рождения сотрудника:</div>
							  <div>".stripslashes($f['name_s'])." (логин ".stripslashes($f['login']).").</div>
							 
							  ";
							  
							  
							  
							  //echo($message_to_dealer);
							  //echo $message_to_managers;
							  //дилеру
							  
								$params1=array();
								
								$params1['topic']=$_dl.' день рождения сотрудника '.stripslashes($f['name_s'])." (логин ".stripslashes($f['login']).')';
								$params1['txt']=$message_to_managers;
								$params1['to_id']= $g['id'];
								$params1['from_id']=-1; //Автоматическая система рассылки сообщений
								$params1['pdate']=time();
								
								$mi->Send(0,0,$params1,false);
								
								
							   // $_log->PutEntry(0, "Автоматическая система рассылки сообщений",$params1['to_id'],NULL,NULL,"тема сообщения: ".$params1['topic']." текст сообщения: ".$params1['txt']);
								
							}
							
							//внесем флаг 1
							$_ui=new UserSItem;
							$_ui->Edit($f['id'],array('is_bith_'.$days.'_days'=>1));	
						}
					}else{
						//срок не наступил, обнулим флаг отправки
						
						//echo " еще не был, срок не наступил, обнуляю флаг: ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days_c*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
						
						$_ui=new UserSItem;
						$_ui->Edit($f['id'],array('is_bith_'.$days.'_days'=>0));
					}
				}else{
					//echo " уже был: ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days_c*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
				}
				  
				}
		}
		
		
	}
	
	
	//рассылки о днях рождения (на маркерах)
	public function AutoBirthdayMessagesMarkers($days,$debug=false, $result=NULL){
		$_log=new ActionLog;
		$mi=new MessageItem;
		
		//$au=new AuthUser;
		//if($result===NULL) $result=$au->Auth();
		
		
		$d2=time();
		$y2=mktime(0,0,0,1,1,date("Y"));
		$now_midnight=mktime(0,0,0,date('m'),date('d'),date('Y'));
		
		
		
		
		
		
		$sql='select * from '.$this->tablename.' where is_active=1 and pasp_bithday<>0 ';
		//echo $sql.'<br>';
		
		$set1=new mysqlSet($sql);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		
		if($debug) echo 'число дней: '.$days.'<br>';
		
		for($i=0; $i<$rc1; $i++){
			$f=mysqli_fetch_array($rs1);
			
			$bd=$f['pasp_bithday'];
			$y1=mktime(0,0,0,1,1,date("Y",$bd));
			$birth_midnight=mktime(0,0,0,date('m',$bd),date('d',$bd), date('Y'));
			
			//был ли еще др в этом году
			if($birth_midnight>=$now_midnight){
				//еще не было или есть сегодня	
				if($debug) echo "еще не был, ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($birth_midnight))." now: ".date("d.m.Y",$d2)." <br>";
				
				//проверить попадание по дням
				$compare_midnight=$birth_midnight-$days*24*60*60;
				
				if($compare_midnight==$now_midnight){
					
					if($debug) echo "срок НАСТУПИЛ, дней: ".$days." ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($compare_midnight))." now: ".date("d.m.Y",$d2)." <br>";
					
					//проверить наличие маркера в этом году
					
					$sql2='select count(*) from user_birthday_markers where user_id='.$f['id'].' and kind='.$days.' and (pdate between '.mktime(0,0,0,1,1,date('Y')).' and '.mktime(0,0,0,12,31,date('Y')).')';
					$set2=new mysqlSet($sql2);
					$rs2=$set2->GetResult();
					$g=mysqli_fetch_array($rs2);
					if($g[0]==0){
						//нет маркера, отправим сообщение
						
						if($debug) echo "маркера нет, вносим сообщение, ставим маркер <br>";	
						
						
		$flt='';
		$_u_to_u=new UserToUser();
		$u_to_u=$_u_to_u->GetExtendedViewedUserIdsArr($f['id']);
		$limited_user=$u_to_u['sector_ids'];
		
		$flt.=' and id in('.implode(', ', $limited_user).') '; 
						
						
						//выполним рассылку, 
							$sql3='select * from '.$this->tablename.' where is_active=1 and id<>"'.$f['id'].'" '.$flt;
							$set3=new mysqlSet($sql3);
							$rs3=$set3->GetResult();
							$rc3=$set3->GetResultNumRows();
							
							for($k=0; $k<$rc3; $k++){
								$h=mysqli_fetch_array($rs3);
								$_dl='';
								
								if($days==0) $_dl="Сегодня";
								elseif($days==1) $_dl="Через один день";
								elseif($days==3) $_dl="Через три дня";
								elseif($days==7) $_dl="Через семь дней";
								
								$message_to_managers="
							  <div><em>Данное сообщение сгенерировано автоматически.</em></div>
							  <div>&nbsp;</div>
							  <div>Уважаемые коллеги!</div>
							  <div>&nbsp;</div>
							  <div>".$_dl.", а именно, ".date("d.m.",$f['pasp_bithday']).date("Y",$d2).", день рождения сотрудника:</div>
							  <div>&nbsp;</div>
							  <ul><li><strong>".stripslashes($f['name_s'])."</strong> (логин ".stripslashes($f['login']).").</li></ul>
							 
							  ";
							  
							 
							  //echo $message_to_managers;
							  
								$params1=array();
								
								$params1['topic']=$_dl.' день рождения сотрудника '.stripslashes($f['name_s'])." (логин ".stripslashes($f['login']).')';
								$params1['txt']=$message_to_managers;
								$params1['to_id']= $h['id'];
								$params1['from_id']=-1; //Автоматическая система рассылки сообщений
								$params1['pdate']=time();
								
								$mi->Send(0,0,$params1,false);
								
								
							}
						
						
						if($days==0){
								//поздравим с днем рождения
								$params1=array();
								
								
								$message_to_birthday='<div><em>Данное сообщение сгенерировано автоматически.</em></div>
								  <div><br /></div>
								  <div>Уважаемый/ая '.stripslashes($f['name_s']).'!</div>
<div><br /></div>		
<div>Поздравляем Вас с Днем Рождения.</div>
<div>Желаем Вам здоровья, чтобы успех и удача всегда были Вашими неразлучными спутниками, любые жизненные трудности были мимолетны и мгновенно преодолевались.</div>
<div><br /></div>
<div>С уважением, программа &laquo;'.SITETITLE.'&raquo; и команда разработчиков.</div>
								  
								  
								  ';
								
									
								$params1['topic']='С Днем Рождения!';
								$params1['txt']=$message_to_birthday;
								$params1['to_id']= $f['id'];
								$params1['from_id']=-1; //Автоматическая система рассылки сообщений
								$params1['pdate']=time();
								
								$mi->Send(0,0,$params1,false);	
								
							}	
						
						//внесем маркер
						new NonSet('insert into user_birthday_markers (user_id, kind, pdate) values('.$f['id'].', '.$days.', '.time().')');
							
					}else{
						//есть маркер, ничего не делаем
						if($debug) echo "маркер есть, ничего не делаем <br>";
					}
						
				}else{
					if($debug) echo "срок не наступил, дней: ".$days." ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($compare_midnight))." now: ".date("d.m.Y",$d2)." <br>";
				}
				
			}else{
				if($debug) echo 'уже был, '.$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($birth_midnight))." now: ".date("d.m.Y",$d2)." <br>";	
			}
			
		}
		
	}
	
	
	
	
	
	
	//Отбор сотрудников для задачи и других карт
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL){
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		$sql='select p.* from '.$this->tablename.' as p 
		 where p.group_id="'.$this->group_id.'"

			 ';
		
	
		
		//$sql.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		//$sql_count.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' and ';
		
		$sql.=' p.is_active=1 ';
		
		
		
		$sql.=' order by p.name_s asc, p.login asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='users.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	//список пользователей, имеющих право на действие
	public function GetUsersByRightArr($letter='w', $object_id=241){
		$arr=array();
		
		$sql='select distinct u.* from user as u 
		inner join user_rights as ur on u.id=ur.user_id and ur.object_id="'.$object_id.'"
		inner join rights as r on r.id=ur.right_id and r.name="'.$letter.'"
		where u.is_active=1  and u.group_id="'.$this->group_id.'"
		order by u.name_s asc, u.login, u.id
		';
		
		//echo $sql;
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
		
	}
	
	//список ID пользователей, имеющих право на действие
	public function GetUserIdsByRightArr($letter='w', $object_id=241){
		$arr=array();
		
		$sql='select distinct u.id from user as u 
		inner join user_rights as ur on u.id=ur.user_id and ur.object_id="'.$object_id.'"
		inner join rights as r on r.id=ur.right_id and r.name="'.$letter.'"
		where u.is_active=1 and u.group_id="'.$this->group_id.'"
		order by u.name_s asc, u.login, u.id
		';
		
		//echo $sql;
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			 
			$arr[]=$f['id'];
		}
		
		if(count($arr)==0) $arr[]=-1;
		return $arr;
		
	}
	
	
}
?>