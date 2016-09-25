<?

require_once('abstractgroup.php');
require_once('authuser.php');

require_once('invoice_item.php');
require_once('invoice_posgroup.php');
require_once('invoice_notesgroup.php');
require_once('invoice_notesitem.php');
require_once('invoice_view.class.php');

require_once('period_checker.php');

// группа реализаций
class InvoiceGroup extends AbstractGroup {
	protected $_auth_result;
	
		
	public $prefix='';
	protected $is_incoming=0;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='invoice';
		$this->pagename='ed_invoice.php';		
		$this->subkeyname='acceptance_id';	
		$this->vis_name='is_shown';		
		
		$this->_item=new InvoiceItem;
		$this->_notes_group=new InvoiceNotesGroup;
		$this->_posgroup=new InvoicePosGroup;
		
		$this->can_unconfirm_object_id=1124;
		
		$this->_auth_result=NULL;
		
		$this->_view=new Invoice_ViewGroup;
	}

/*******************************************************************************************************************/	
	
	
	
	
	
	public function ShowAllPos(
		$template, //0
		DBDecorator $dec, //1
		$can_edit=false, //2
		$can_delete=false, //3
		$from=0, //4
		$to_page=ITEMS_PER_PAGE, //5
		$can_confirm=false,  //6
		$can_super_confirm=false, //7
		$has_header=true, //8
		$is_ajax=false, //9
		$can_restore=false, //10
		$limited_sector=NULL, //11
		$can_unconfirm=false, //12
		$can_print=false, //13
		$can_email_pdf=false, //14
		$limited_supplier=NULL, //15
		$can_xls=false //16
	){
		
		
		
		//$_bill=new BillItem;
		//$_acc=new AccItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		//$_bpg=new AccPosGroup;
		$_bpf=new BillPosPMFormer;
		
		$_usg=new UsersSGroup;
		
		
		$sql='select p.*,
					ac.id as ac_id, ac.pdate as ac_pdate, ac.code as ac_code,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					sc.name as sector_name, sc.id as sector_id, sc.s_s as sector_s_s,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join acceptance as ac on p.acceptance_id=ac.id
					left join bill as o on ac.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join sector as sc on p.sector_id=sc.id
					left join user as mn on p.manager_id=mn.id
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join acceptance as ac on p.acceptance_id=ac.id
					left join bill as o on ac.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join sector as sc on p.sector_id=sc.id
					left join user as mn on p.manager_id=mn.id
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
		
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.=' o.supplier_id in ('.implode(', ',$limited_supplier).')';
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
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $this->prefix));
		$navig->SetFirstParamName('from'.$this->prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
	
		
		$alls=array();
		//$_bng=new AccNotesGroup;
		
		$_au=new AuthUser;
	//	$_aures=$_au->Auth();
	
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		
		$_pch=new PeriodChecker;
		$_pg=new PerGroup;
		$periods=$_pg->GetItemsByIdArr($_aures['org_id'],0,1);	
		
		
			//найдем списки сотрудников, кто может снять утв-ие поступления
		 
		$usg_can=$_usg->GetUsersByRightArr('w',$this->can_unconfirm_object_id);
		$_usg_can_str=array(); 
		foreach($usg_can as $k=>$v) $_usg_can_str[]=htmlspecialchars($v['name_s'].' ('.$v['login'].')');
		$can_unconfirm_users=implode(', ', $_usg_can_str);
		
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			
			//if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			//else $f['given_pdate']='-';
			
			//if($f['print_pdate']!=0) $f['print_pdate']=date("d.m.Y",$f['print_pdate']);
			//else $f['print_pdate']='-';
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason, $f,$periods)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			
			$f['can_unconfirm']=true;
			
			//$bill_has_pms=$this->_item->ParentBillHasPms($f['id'], $f);
			//$f['bill_has_pms']=$bill_has_pms;
			
			$f['can_unconfirm']=$f['can_unconfirm']&&$_au->user_rights->CheckAccess('w',$this->can_unconfirm_object_id);
			
			
			//найдем списки сотрудников, кто может снять утв-ие поступления
			$f['can_unconfirm_users']=$can_unconfirm_users;
			
			//также нужно понимать, может сотр-к снять утв-ие поступления или нет по текущему объекту-участку
			$f['cannot_unconfirm']=!$f['can_unconfirm'];
			
			
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
			
			
			$bpg=$this->_posgroup->GetItemsByIdArr($f['id'],0,true,true,false,false);
		//print_r($bpg);
			 
		
			$f['total_cost']=$_bpf->CalcCost($bpg);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			
			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//kontragent
		/*$au=new AuthUser();
	//	$result=$au->Auth();
	    
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		*/
	
		$_sql='select * from sector order by name asc';
		
		$as=new mysqlSet($_sql);
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_sector==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('sc',$acts);
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_restore',$can_restore);
		
			$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		
		$sm->assign('prefix',$this->prefix);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_email_pdf',$can_email_pdf);
		$sm->assign('can_xls', $can_xls);
		
		/*$_au=new AuthUser();
		//$_result=$_au->Auth();
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		*/
		$sm->assign('user_id',$_aures['id']);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		$sm->assign('has_header',$has_header);
		
		
		return $sm->fetch($template);
	}
        
}
?>