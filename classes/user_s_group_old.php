<?
require_once('abstractgroup.php');
require_once('storagegroup.php');
require_once('sectorgroup.php');
require_once('user_s_item.php');
require_once('actionlog.php');
require_once('messageitem.php');


require_once('usercontactdatagroup.php');
require_once('suppliercontactkindgroup.php');

// users S
class UsersSGroup extends AbstractGroup {
	protected $group_id;
	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=2;
	}
	
	
	public function GetItems($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE, $in_storage=NULL, $in_sector=NULL){
		$txt='';
		
		$sm=new SmartyAdm;
		
		
		$sql='select * from '.$this->tablename.'  
			  ';
		
		$sql_count='select count(*) from '.$this->tablename.' 
			
		 ';
		
		
		
		$db_flt=$dec->GenFltSql(' and ');
		
		if($in_storage!==NULL){
			if(strlen($db_flt)>0) $db_flt.=' and ';
			$db_flt.=' (id in(select distinct nach_user_id from storage where id="'.$in_storage.'") or id in(select distinct zamnach_user_id from storage where id="'.$in_storage.'"))';
			
		}
		
		if($in_sector!==NULL){
			if(strlen($db_flt)>0) $db_flt.=' and ';
			$db_flt.=' (id in(select distinct nach_user_id from sector where id="'.$in_sector.'") or id in(select distinct zamnach_user_id from sector where id="'.$in_sector.'"))';	
		
		}
		
		if(strlen($db_flt)>0){
			
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
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
		
		$navig = new PageNavigator('users_s.php',$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
		$_ukg=new UserContactDataGroup;
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//print_r($f);
			
			$f['is_in_vac']=($f['vacation_till_pdate']>=time())&&($f['is_in_vacation']==1);
			$f['vacation_till_pdate_f']=date("d.m.Y",$f['vacation_till_pdate']);
			
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
				if($v['kind_id']==5) $f['email_s'].=' '.stripslashes($v['value']);	
			}
			
			
			
			
			
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
		
		
		$_sto_grp=new StorageGroup;
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
		$sm->assign('sector_names',$stn);
		
		
		$au=new AuthUser();
		//проверка возможности показа кнопки Создать карту сотрудника
		$sm->assign('can_create', $au->user_rights->CheckAccess('w',10));
		
		
		//проверка возможности показа кнопки подробно
		$sm->assign('can_edit', $au->user_rights->CheckAccess('w',11));
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzz';
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='users_s.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.'  order by login asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where  '.$this->vis_name.'="1" order by login asc');
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
	
	
	public function AutoBirthdayMessages($days){
		$_log=new ActionLog;
		$mi=new MessageItem;
		$d2=time();
		$y2=mktime(0,0,0,0,0,date("Y"));
		
		$sql='select * from '.$this->tablename.' where is_active=1 and pasp_bithday<>0';
		$set1=new mysqlSet($sql);
		$rs1=$set1->GetResult();
		$rc1=$set1->GetResultNumRows();
		
		for($i=0; $i<$rc1; $i++){
			$f=mysqli_fetch_array($rs1);
			
			$bd=$f['pasp_bithday'];
			$y1=mktime(0,0,0,0,0,date("Y",$bd));
			
			if(($bd+($y2-$y1))>$d2){
				//д.р. в этом году еще не было
				//echo "еще не был, ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
				
				
				if(($bd+($y2-$y1)-($days*60*60*24))==datefromdmy(date("d.m.Y",$d2))){
					
					//echo "еще не был, срок дней: ".$days." ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
					
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
							if($days==3) $_dl="Через три дня";
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
					
					//echo " еще не был, срок не наступил, обнуляю флаг: ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
					
					$_ui=new UserSItem;
					$_ui->Edit($f['id'],array('is_bith_'.$days.'_days'=>0));
				}
			}else{
				//echo " уже был: ".$f['login'].' '." bd: ".date("d.m.Y",$bd)." check: ".date("d.m.Y",($bd+($y2-$y1)-($days*60*60*24)))." now: ".date("d.m.Y",$d2)." <br>";
			}
				
			
		}
		
		
	}
	
	
	
}
?>