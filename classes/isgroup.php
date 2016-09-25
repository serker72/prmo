<?
require_once('billgroup.php');
require_once('billitem.php');
require_once('is_custom_group.php');

require_once('isitem.php');
require_once('isnotesitem.php');

// абстрактная группа
class IsGroup extends IsCustomGroup {
	protected $is_or_writeoff;
	protected $_auth_result;
	
	public function __construct($is_or_writeoff=0){
		$this->init($is_or_writeoff);
	}
	
	//установка всех имен
	protected function init($is_or_writeoff=0){
		$this->tablename='interstore';
		$this->pagename='interstore.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		$this->is_or_writeoff=$is_or_writeoff;	
		
		$this->_auth_result=NULL;
	}
	
	
	
	public function ShowPos($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE, $can_edit=false, $can_delete=false, $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL, $can_unconfirm=false, $can_two_confirm=false,$can_two_unconfirm=false,$user_id=NULL,$can_create=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		$_bill=new BillItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					sr.name as sender_storage_name,
					sc.name as sender_sector_name,
					rr.name as receiver_storage_name,
					rc.name as receiver_sector_name,
					
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					us.name_s as confirmed_wf_name, us.login as confirmed_wf_login,  p.confirm_wf_pdate as confirm_wf_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join storage as sr on p.sender_storage_id=sr.id
					left join sector as sc on p.sender_sector_id=sc.id
					left join storage as rr on p.receiver_storage_id=rr.id
					left join sector as rc on p.receiver_sector_id=rc.id
				
					left join user as u on p.user_confirm_id=u.id
					left join user as us on p.user_confirm_wf_id=us.id
					left join user as mn on p.manager_id=mn.id
				where is_or_writeoff="'.$this->is_or_writeoff.'"
				';
				
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join storage as sr on p.sender_storage_id=sr.id
					left join sector as sc on p.sender_sector_id=sc.id
					left join storage as rr on p.receiver_storage_id=rr.id
					left join sector as rc on p.receiver_sector_id=rc.id
				
					left join user as u on p.user_confirm_id=u.id
					left join user as us on p.user_confirm_wf_id=us.id
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
		$item=new IsItem;
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
			
			
			if($f['confirm_wf_pdate']!=0) $f['confirm_wf_pdate']=date("d.m.Y H:i:s",$f['confirm_wf_pdate']);
			else $f['confirm_wf_pdate']='-';
			
			
			$f['can_annul']= $item->DocCanAnnul($f['id'],$reason, $f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			
			$reason='';
			$f['can_confirm']=$item->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			//var_dump($user_id);
			
			$reason='';
			$f['can_confirm_wf']=$item->DocCanConfirmWf($f['id'],$reason,$user_id, $f)&&$can_two_confirm;
			if(!$can_two_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_wf_reason']=$reason;
			
			
			if($f['can_annul']) $f['binded_to_annul']=$item->GetBindedDocumentsToAnnul($f['id']);
			else $f['binded_to_annul']='';
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id']);
			
			$item->isblink->OverallBlink($f, $color);
			$f['color']=$color;
				
				
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_storage='';
		//$current_supplier='';
		$user_confirm_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sender_sector_id') $current_sender_sector=$v->GetValue();
			//if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='sender_storage_id') $current_sender_storage=$v->GetValue();
			
			if($v->GetName()=='receiver_sector_id') $current_receiver_sector=$v->GetValue();
			//if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='receiver_storage_id') $current_receiver_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//sklad
		$as=new mysqlSet('select * from storage order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_sender_storage==$f[0]); 
			$acts[]=$f;
		}
		
		$acts1=array();
		$acts1[]=array('description'=>'');
		foreach($acts as $k=>$f){
		
			$f['is_current']=($current_receiver_storage==$f[0]); 
			$acts1[]=$f;
		}
		
		$sm->assign('ug',$acts);
		$sm->assign('ug1',$acts1);
		
		
		//u4astok
		if($limited_sector!==NULL) $_sql='select * from sector where id in('.implode(', ',$limited_sector).') order by name asc';
		else $_sql='select * from sector order by name asc';
		//echo $_sql;
		$as=new mysqlSet($_sql);
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_sender_sector==$f[0]); 
			$acts[]=$f;
		}
		
		$acts1=array();
		$acts1[]=array('description'=>'');
		foreach($acts as $k=>$f){
			//foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_receiver_sector==$f[0]); 
			$acts1[]=$f;
		}
		
		$sm->assign('sc',$acts);
		$sm->assign('sc1',$acts1);
		
	
		
		$sm->assign('can_create',$can_create);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		
		$sm->assign('can_confirm_wf',$can_two_confirm);
		$sm->assign('can_super_confirm_wf',$can_super_confirm);
		$sm->assign('can_unconfirm_wf',$can_two_unconfirm);
		
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
		
		//ссылка для кнопок сортировки
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
		
		 $_itm=new IsItem;
		
		$_ni=new IsNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_or_writeoff="'.$this->is_or_writeoff.'" and status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			//проверить наличие связ. документов утв
			
			
			$sql1='select count(id) from bill where  interstore_id="'.$f['id'].'" and (is_confirmed_price=1 or is_confirmed_shipping=1)';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			$sql1='select count(id) from sh_i_id where is_confirmed=1 and bill_id in(select id from bill where  interstore_id="'.$f['id'].'" and (is_confirmed_price=1 or is_confirmed_shipping=1))';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			$sql1='select count(id) from acceptance where is_confirmed=1 and bill_id in(select id from bill where  interstore_id="'.$f['id'].'" and (is_confirmed_price=1 or is_confirmed_shipping=1)) ';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления распоряжения на межсклад, нет утвержденных связанных документов, документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания распоряжения на межсклад, нет утвержденных связанных документов, документ не утвержден';
					}
				}
			}elseif(($f['is_confirmed']==1)&&($f['is_confirmed_wf']==0)){
				//работаем с датой простановки 1 галочки	
					if(($f['confirm_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения заполнения распоряжения на межсклад, нет утвержденных связанных документов';
					}
				
			}elseif(($f['is_confirmed']==1)&&($f['is_confirmed_wf']==1)){
				//работаем с датой простановки 2 галочки	
					if(($f['confirm_wf_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения списания по распоряжению на межсклад, нет утвержденных связанных документов';
					}
				
			}
			
			
			
			
			
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'is_confirmed_wf'=>0, 'status_id'=>$annul_status_id),false,false);
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование распоряжения на межсклад',NULL,336,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: распоряжение на межсклад было автоматически аннулировано, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
}
?>