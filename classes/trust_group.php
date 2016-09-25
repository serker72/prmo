<?
require_once('billgroup.php');
require_once('billitem.php');

require_once('authuser.php');
require_once('trust_item.php');
require_once('trust_notesgroup.php');
require_once('trust_notesitem.php');

require_once('trust_view.class.php');

// абстрактная группа
class TrustGroup extends AbstractGroup {
	protected $_auth_result;
	
	//установка всех имен
	protected function init(){
		$this->tablename='trust';
		$this->pagename='trust.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		$this->_view=new Trust_ViewGroup;
		
		$this->_auth_result=NULL;
	}
	
	public function ShowPos($bill_id, $template, DBDecorator $dec,$can_edit=false, $can_delete=false, $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		$_bill=new BillItem;
		
		$_acc=new TrustItem;
		
		//$sm=new SmartyAdm;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					uu.name_s as name_s, uu.login as login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as uu on p.user_id=uu.id
					left join user as mn on p.manager_id=mn.id
				where '.$this->subkeyname.'="'.$bill_id.'"';
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' where '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql);
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
		
		$alls=array();
		$_bng=new TrustNotesGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['valid_pdate']=date("d.m.Y",$f['pdate']+30*24*60*60);
			
			
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			$f['can_annul']=$_acc->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id'],  0,  0,  false, false, false, 0,false);
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_super_confirm',$can_confirm);
		$sm->assign('has_header',$has_header);
		
		$sm->assign('can_restore',$can_restore);
		
		$_au=new AuthUser();
		//$_result=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		$sm->assign('user_id',$_result['id']);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	///показ всех
	public function ShowAllPos($template, DBDecorator $dec,$can_edit=false, $can_delete=false, $from=0, $to_page=ITEMS_PER_PAGE, $can_confirm=false,  $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false, $can_print=false){
	
		
		$_bill=new BillItem;
		
		$_acc=new TrustItem;
		
		//$sm=new SmartyAdm;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		
		
		
	$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					uu.name_s as name_s, uu.login as login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as uu on p.user_id=uu.id
					left join user as mn on p.manager_id=mn.id
				';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as uu on p.user_id=uu.id
					left join user as mn on p.manager_id=mn.id
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
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
		
		
		//page
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
			
		$alls=array();
		
		$_bng=new TrustNotesGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['valid_pdate']=date("d.m.Y",$f['pdate']+30*24*60*60);
			
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			
			
			$f['can_annul']=$_acc->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_super_confirm',$can_confirm);
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('has_header',$has_header);
		$sm->assign('can_print',$can_print);
		
		$_au=new AuthUser();
		//$_result=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		
		$sm->assign('user_id',$_result['id']);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		return $sm->fetch($template);
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id desc');
		
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
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new TrustNotesItem;
		 $_itm=new TrustItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_confirmed=0 and status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			
			$reason='';
			//проверим дату восстановления
			if($f['restore_pdate']>0){
				if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;	
					$reason='прошло более '.$days_after_restore.' дней с даты восстановления доверенности';
				}
			}else{
				//работаем с датой создания	
				if(($f['pdate']+$days*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='прошло более '.$days.' дней с даты создания доверенности';
				}
			}
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
			
				
				$log->PutEntry(0,'автоматическое аннулирование доверенности',NULL,212,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: доверенность была автоматически аннулирована, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
}
?>