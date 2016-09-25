<?
require_once('billgroup.php');
require_once('billitem.php');
require_once('is_custom_group.php');
/*require_once('isitem.php');*/
require_once('wfitem.php');

//require_once('isnotesitem.php');

require_once('wfitem.php');
require_once('messageitem.php');

//require_once('storageitem.php');
//require_once('sectoritem.php');
require_once('user_s_item.php');


// абстрактна€ группа
class WfGroup extends IsCustomGroup {
	protected $is_or_writeoff;
	
	protected $_auth_result;
	
	public function __construct($is_or_writeoff=1){
		$this->init($is_or_writeoff);
	}
	
	//установка всех имен
	protected function init($is_or_writeoff=1){
		$this->tablename='interstore';
		$this->pagename='interstore.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		$this->is_or_writeoff=$is_or_writeoff;	
		$this->_auth_result=NULL;
		
	}
	
	
	public function ShowPos($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE, $can_edit=false, $can_delete=false, $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL,$can_unconfirm=false, $can_two_confirm=false,$can_two_unconfirm=false, $can_create=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		$_bill=new BillItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					
					
					u.name_s as confirmed_fill_wf_name, u.login as confirmed_fill_wf_login,
					us.name_s as confirmed_name, us.login as confirmed_login,  p.confirm_pdate as confirm_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					
				
					left join user as u on p.user_confirm_fill_wf_id=u.id
					left join user as us on p.user_confirm_id=us.id
					left join user as mn on p.manager_id=mn.id
				where is_or_writeoff="'.$this->is_or_writeoff.'"
				';
				
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join user as u on p.user_confirm_fill_wf_id=u.id
					left join user as us on p.user_confirm_id=us.id
					left join user as mn on p.manager_id=mn.id
				where is_or_writeoff="'.$this->is_or_writeoff.'"
					';
				 
		
				 
		$db_flt=$dec->GenFltSql(' and ');
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
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		$item=new WfItem;
		$alls=array();
		$_bng=new IsNotesGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			
			//print_r($f);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			if($f['confirm_fill_wf_pdate']!=0) $f['confirm_fill_wf_pdate']=date("d.m.Y H:i:s",$f['confirm_fill_wf_pdate']);
			else $f['confirm_fill_wf_pdate']='-';
			
			
			$f['can_annul']= $item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав дл€ данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['can_restore']= $item->DocCanRestore($f['id'],$reason,$f)&&$can_restore;
			if(!$can_restore) $reason='недостаточно прав дл€ данной операции';
			$f['can_restore_reason']=$reason;
			
			$reason='';
			$f['can_confirm_fill_wf']=$item->DocCanConfirmFillWf($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав дл€ данной операции';
			$f['can_confirm_reason_fill_wf']=$reason;
			
			
			//$f['binded_to_annul']=$item->GetBindedDocumentsToAnnul($f['id']);
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id']);
				
				
			$alls[]=$f;
		}
		
		//заполним шаблон пол€ми
		
		//$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
						
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		
		
		$sm->assign('can_confirm_fill_wf',$can_two_confirm);
		$sm->assign('can_super_confirm_fill_wf',$can_two_unconfirm);
		$sm->assign('can_unconfirm_fill_wf',$can_two_unconfirm);
		
		
		$sm->assign('can_create',$can_create);
		
		$sm->assign('can_restore',$can_restore);
		$sm->assign('has_header',$has_header);
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		//ссылка дл€ кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=30, $days_after_restore=30, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		 $_itm=new WfItem;
		
		$_ni=new IsNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_or_writeoff="'.$this->is_or_writeoff.'" and is_confirmed=0 and status_id<>'.$annul_status_id.' and is_j=0 order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed_fill_wf']==0){
				
				
					
				//проверим дату восстановлени€
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановлени€ распор€жени€ на списание, документ не утвержден';
					}
				}else{
					//работаем с датой создани€	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создани€ распор€жени€ на списание, документ не утвержден';
					}
				}
			}elseif(($f['is_confirmed_fill_wf']==1)&&($f['is_confirmed']==0)){
				//работаем с датой простановки 1 галочки	
					if(($f['confirm_fill_wf_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждени€ заполнени€ распор€жени€ на списание, документ не утвержден';
					}
				
			}
			
			
			
			
			
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed_fill_wf'=>0, 'is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование распор€жени€ на списание',NULL,107,NULL,'є документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'јвтоматическое примечание: распор€жение на списание было автоматически аннулировано, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	//рассылка сообщений об актах на списание is_j
	public function SendIsJ($result){
		
		
		
		
		//разослать сообщени€ всем сотрудникам ќ—
			$usql='select u.* from user as u where u.is_active=1 and u.is_supply_user=1 and u.id in(select distinct user_id from supplier_to_user where org_id="'.$result['org_id'].'")';
			
			$uset=new mysqlSet($usql);
			$urs=$uset->GetResult();
			$urc=$uset->GetResultNumRows();
			
			$users=array(); 
			for($ui=0; $ui<$urc; $ui++){
				$uf=mysqli_fetch_array($urs);
				
				$users[]=$uf;
				
				/*$params1=array();
				$params1['topic']='¬нимание!  ража продукции!';
				$params1['txt']=$message_text;
				$params1['to_id']= $uf['id'];
				$params1['from_id']=-1;
				$params1['pdate']=time();	
				$_mi->Send(0,0,$params1,false);	*/
				
				//echo $message_text;
			}	
			
			
			$_mi=new MessageItem;
			
			
			
			
			$_ui=new UserSItem;
			$_si=new SectorItem;
			$_sti=new StorageItem;
			$_is=new IsItem;
			$_wf=new WfItem;
			$_mi=new MessageItem;
			
			$sql='select p.*,
					sr.name as sender_storage_name,
					sc.name as sender_sector_name,
					rr.name as receiver_storage_name,
					rc.name as receiver_sector_name,
					u.name_s as confirmed_fill_wf_name, u.login as confirmed_fill_wf_login,
					us.name_s as confirmed_name, us.login as confirmed_login,  p.confirm_pdate as confirm_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					
					par.id as par_id,
					par.pdate as par_pdate,
					
					parent_u.name_s as parent_confirmed_wf_name, parent_u.login as parent_confirmed_wf_login,
					parent_us.name_s as parent_confirmed_name, parent_us.login as parent_confirmed_login,  
					
					parent_sr.name as parent_sender_storage_name,
					parent_sc.name as parent_sender_sector_name,
					parent_rr.name as parent_receiver_storage_name,
					parent_rc.name as parent_receiver_sector_name
					
					
				from interstore as p
					inner join interstore as par on par.id=p.interstore_id
					
					left join storage as sr on p.sender_storage_id=sr.id
					left join sector as sc on p.sender_sector_id=sc.id
					left join storage as rr on p.receiver_storage_id=rr.id
					left join sector as rc on p.receiver_sector_id=rc.id
					
					left join storage as parent_sr on par.sender_storage_id=parent_sr.id
					left join sector as parent_sc on par.sender_sector_id=parent_sc.id
					left join storage as parent_rr on par.receiver_storage_id=parent_rr.id
					left join sector as parent_rc on par.receiver_sector_id=parent_rc.id
				
					
					
					left join user as u on p.user_confirm_fill_wf_id=u.id
					left join user as us on p.user_confirm_id=us.id
					left join user as mn on p.manager_id=mn.id
					
					left join user as parent_u on par.user_confirm_wf_id=parent_u.id
					left join user as parent_us on par.user_confirm_id=parent_us.id
					
				where p.is_or_writeoff="'.$this->is_or_writeoff.'"
				and p.is_j=1 /*and p.is_confirmed_fill_wf=1*/ and p.is_confirmed=0 and p.status_id<>3
		';
		   // echo $sql;
			
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				/*echo '<pre>';	
				print_r($f);
				echo '</pre>';*/
				
				$message_text='<div><em>ƒанное сообщение сгенерировано автоматически.</em></div>
				<div><strong>¬нимание!  ража продукции!</strong></div>
				<br />
				<div>ѕри утверждении списани€ по межскладу є '.$f['par_id'].' от '.date('d.m.Y', $f['par_pdate']).' пользователем '.SecStr($f['parent_confirmed_wf_name']).' (логин '.SecStr($f['parent_confirmed_wf_login']).') 
				с участка '.SecStr($f['parent_sender_sector_name']).', объекта '.SecStr($f['parent_sender_storage_name']).' на участок '.SecStr($f['parent_receiver_sector_name']).', объект '.SecStr($rsti['parent_receiver_storage_name']).'
				были прин€ты следующие количества:</div>
				';
				
				$message_text.='
				<br />';
				
				$ispositions=$_is->GetPositionsArr($f['par_id']);
				foreach($ispositions as $pk=>$pv){
					$message_text.='<div>'.sprintf("%05d", $pv['id']).' '.SecStr($pv['position_name']).' '.SecStr($pv['quantity']).' '.SecStr($pv['dim_name']).'</div>';	
					
				}
				
				$message_text.='
				<br />';
				
				$message_text.='<div>Ќедостача при списании с участка '.SecStr($f['parent_sender_sector_name']).', объекта '.SecStr($f['parent_sender_storage_name']).':</div>';
				
				$test_new_pos=$_is->GetPositionsArr($f['id']);
				foreach($test_new_pos as $pk=>$pv){
					$message_text.='<div>'.sprintf("%05d", $pv['id']).' '.SecStr($pv['position_name']).' '.SecStr($pv['quantity']).' '.SecStr($pv['dim_name']).'</div>';	
					
				}
				
				$message_text.='
				<br />
				<div>Ѕыл автоматически заведен акт на списание недостачи є '.$f['id'].'.</div>';
				
				$message_text.='
				<br />
				
				
				<div>ѕри утверждении списани€ по межскладу сотрудник  '.SecStr($f['parent_confirmed_wf_name']).' (логин '.SecStr($f['parent_confirmed_wf_login']).') был предупрежден.</div>
				<div>ѕожалуйста, примите меры дл€ расследовани€ данной недостачи.</div>
				';
				
				//echo $message_text;
				
				foreach($users as $uk=>$uv){
				
				  $params1=array();
				  $params1['topic']='¬нимание!  ража продукции!';
				  $params1['txt']=$message_text;
				  $params1['to_id']= $uv['id'];
				  $params1['from_id']=-1;
				  $params1['pdate']=time();	
				  $_mi->Send(0,0,$params1,false);	
				
				}
				//echo $message_text;
				
			}
			
						
						
		
	}
	
}
?>