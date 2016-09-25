<?
require_once('billgroup.php');
require_once('billitem.php');

require_once('authuser.php');
require_once('acc_item.php');
require_once('acc_notesgroup.php');
require_once('acc_posgroup.php');
require_once('acc_notesitem.php');
require_once('billpospmformer.php');
require_once('period_checker.php');

require_once('user_s_group.php');

require_once('acc_view.class.php');
require_once('acc_in_view.class.php');

// абстрактная группа поступлений
class AbstractAccGroup extends AbstractGroup {
	protected $_auth_result;
	
	public $prefix='_1';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	protected $_posgroup;
	
	public $can_unconfirm_object_id;
	public $can_unconfirm_object_inv_id;
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance';
		$this->pagename='ed_bill.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		
		
			
		$this->_item=new AccItem;
		$this->_notes_group=new AccNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup; //???
		$this->_posgroup=new AccPosGroup;
		
		$this->can_unconfirm_object_id=721;
		$this->can_unconfirm_object_inv_id=241;
		
		$this->_auth_result=NULL;
		
		$this->_view=new Acc_ViewGroup;
		
	}
	
	public function ShowPos($bill_id, //0 
		$template, //1
		DBDecorator $dec, //2
		$can_edit=false, //3
		$can_delete=false, //4
		$can_confirm=false, //5
		$can_super_confirm=false, //6
		$has_header=true, //7
		$is_ajax=false, //8
		$can_restore=false, //9
		$limited_sector=NULL, //10
		$by_is=NULL, //11
		$can_unconfirm=false, //12
		&$alls, //13
		$can_print=false, //14
		$can_email_pdf=false, //15
		$limited_supplier=NULL, //16
		
		$can_xls=false //17
	){
		
		
		//$_acc=new AccItem;
		
		//$sm=new SmartyAdm;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		//$_bpg=new AccPosGroup;
		$_bpf=new BillPosPMFormer;
		
		$_usg=new UsersSGroup;
		
		
		
		$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					sc.name as sector_name, sc.id as sector_id, sc.s_s as sector_s_s,
					
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join sector as sc on p.sector_id=sc.id
					left join user as u on p.user_confirm_id=u.id
					left join user as mn on p.manager_id=mn.id
					
			where p.is_incoming="'.$this->is_incoming.'" 		
					';
					
			if($by_is===NULL){		
				$sql.='	and '.$this->subkeyname.'="'.$bill_id.'"';
			}else{
			//	$sql.=' where bill_id in (select id from bill where interstore_id="'.$by_is.'")';
			}
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.=' ( o.supplier_id in ('.implode(', ',$limited_supplier).')';
			
			if($this->is_incoming==1){
				//также подгрузить вход счета, связанные с его исходящими
				$db_flt.=' or o.out_bill_id in(select id from bill where is_incoming=0 and supplier_id in ('.implode(', ',$limited_supplier).')) ';	
			}
			
			$db_flt.=' )';
			
		}
		
		
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
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		
		$_au=new AuthUser;
		//$_aures=$_au->Auth();
		
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
		
		
		//$_bng=new AccNotesGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate_unformatted']=$f['pdate'];
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			//print_r($f);	
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;//$_acc->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason,$f,$periods)&&$can_confirm;//$_acc->DocCanConfirm($f['id'],$reason,$f,$periods)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			
			$f['can_unconfirm']=true;
			
			$bill_has_pms=$this->_item->ParentBillHasPms($f['id'], $f);
			$f['bill_has_pms']=$bill_has_pms;
			
			//подтягивать права на снятие утв. индивидуально для каждого поступления
			if(!$bill_has_pms||($f['inventory_id']!=0)){
				 $f['can_unconfirm']=$f['can_unconfirm']&&$_au->user_rights->CheckAccess('w',$this->can_unconfirm_object_inv_id);
				 
				 
			}else{
				 $f['can_unconfirm']=$f['can_unconfirm']&&$_au->user_rights->CheckAccess('w',$this->can_unconfirm_object_id);
			}
			
			//найдем списки сотрудников, кто может снять утв-ие поступления
			$f['can_unconfirm_users']=$can_unconfirm_users;
			
			//также нужно понимать, может сотр-к снять утв-ие поступления или нет по текущему объекту-участку
			$f['cannot_unconfirm']=!$f['can_unconfirm'];
			
			
			//если это поступление - то снимая утвержденние, мы снимаем утверждение связанных реализаций
			//вывести об этом предупреждение - в /js/acc.php
			
			
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']);//$_bng->GetItemsByIdArr($f['id']);
			
			
			$bpg=$this->_posgroup->GetItemsByIdArr($f['id'], 0,  true, true,  false, false);//$_bpg->GetItemsByIdArr($f['id'], 0, true, true, false);
		//print_r($bpg);
		
		
			$f['total_cost']=$_bpf->CalcCost($bpg);
			//$total_nds=$_bpf->CalcNDS($bpg);
			
			
			
			//var_dump($reason);
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		
		$current_supplier='';
		$user_confirm_id='';
	
		$current_sh_i_id='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='supplier_name') $current_supplier=$v->GetValue();
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
			
			if($v->GetName()=='sh_i_id'){
				$current_sh_i_id=$v->GetValue();
				continue;	
			}
		}
		
		
		
	
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('action',1);
		if($current_sh_i_id!='') $sm->assign('id',$current_sh_i_id);
		else $sm->assign('id',$bill_id);
		
		
		$sm->assign('prefix',$this->prefix);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('can_xls', $can_xls);
		
		/*$_au=new AuthUser();
		//$_result=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}*/
		
		$sm->assign('user_id',$_aures['id']);
		
		
		$sm->assign('has_header',$has_header);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		
		
		return $sm->fetch($template);
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
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					sc.name as sector_name, sc.id as sector_id, sc.s_s as sector_s_s,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join sector as sc on p.sector_id=sc.id
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$this->is_incoming.'" 		
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join sector as sc on p.sector_id=sc.id
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$this->is_incoming.'" 		
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
		
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.=' ( o.supplier_id in ('.implode(', ',$limited_supplier).')';
			
			if($this->is_incoming==1){
				//также подгрузить вход счета, связанные с его исходящими
				$db_flt.=' or o.out_bill_id in(select id from bill where is_incoming=0 and supplier_id in ('.implode(', ',$limited_supplier).')) ';	
			}
			
			$db_flt.=' )';
			
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
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			if($f['print_pdate']!=0) $f['print_pdate']=date("d.m.Y",$f['print_pdate']);
			else $f['print_pdate']='-';
			
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
			
			$bill_has_pms=$this->_item->ParentBillHasPms($f['id'], $f);
			$f['bill_has_pms']=$bill_has_pms;
			
			//подтягивать права на снятие утв. индивидуально для каждого поступления
			if(!$bill_has_pms||($f['inventory_id']!=0)){
				 $f['can_unconfirm']=$f['can_unconfirm']&&$_au->user_rights->CheckAccess('w',$this->can_unconfirm_object_inv_id);
				 
				 
			}else{
				 $f['can_unconfirm']=$f['can_unconfirm']&&$_au->user_rights->CheckAccess('w',$this->can_unconfirm_object_id);
			}
			
			
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
	
	
	
	
	
	
	
	
	public function CountByShid($sh_i_id, $is_confirmed=0){
		
		$sql='select count(*) from '.$this->tablename.' where sh_i_id="'.$sh_i_id.'" ';
		if($is_confirmed==1) $sql.=' and is_confirmed=1';
		
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$f[0]=(int)$f[0];
		
		return $f[0];
		
		
	}
	
	public function GetByShidArr($sh_i_id, $is_confirmed=0){
		
		$sql='select * from '.$this->tablename.' where sh_i_id="'.$sh_i_id.'" ';
		if($is_confirmed==1) $sql.=' and is_confirmed=1';
		
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			
			$alls[]=$f;
		}
		
		return $alls;
		
		
	}
	
	public function GetByShid($sh_i_id, $is_confirmed=0){
		$acc_list='';
		$_acc_l=$this->GetByShidArr($sh_i_id, $is_confirmed);
		foreach($_acc_l as $k=>$v){
			if(strlen($acc_list)>0) $acc_list.=', ';
			$acc_list.='№ '.$v['id'].' от '.$v['pdate'];	
		}	
		
		return $acc_list;
	}
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and is_incoming="'.$this->is_incoming.'" order by id asc');
		
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
	public function AutoAnnul($days=30, $days_after_restore=30, $annul_status_id=6){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new AccNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_confirmed=0 and status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time(); $_itm=new AccItem;
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
					$reason='прошло более '.$days_after_restore.' дней с даты восстановления реализации, документ не утвержден';
				}
			}else{
				//работаем с датой создания	
				if(($f['pdate']+$days*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='прошло более '.$days.' дней с даты создания реализации, документ не утвержден';
				}
			}
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				$log->PutEntry(0,'автоматическое аннулирование реализации',NULL,93,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['bill_id']);
				
				$log->PutEntry(0,'автоматическое аннулирование реализации',NULL,219,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['sh_i_id']);
				
				$log->PutEntry(0,'автоматическое аннулирование реализации',NULL,235,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: реализация была автоматически аннулирована, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
}
?>