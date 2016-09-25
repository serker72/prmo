<?
require_once('abstractgroup.php');
 
require_once('paycodegroup.php');
require_once('cashnotesgroup.php');
require_once('cashnotesitem.php');

require_once('cashitem.php');

require_once('cash_to_bill_item.php');
require_once('user_s_item.php');
require_once('billitem.php');

require_once('cash_view.class.php');

// группа расходов наличных
class CashGroup extends AbstractGroup {
	 
	protected $_auth_result;
	
	
	
	public $prefix='_cash';
 
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	 
	//установка всех имен
	protected function init(){
		$this->tablename='cash';
		$this->pagename='view.php';		
		 
		$this->vis_name='is_confirmed';		
		 
		$this->_item=new  CashItem;
		$this->_notes_group=new CashNotesGroup;
		 
		
		$this->_view=new Cash_ViewGroup;
		
		$this->_auth_result=NULL;
	}
	
	
	
	
	
	
	
	public function ShowAllPos($template, //0
	DBDecorator $dec,//1
	$can_edit=false, //2
	$can_delete=false, //3
	$from=0, //4
	$to_page=ITEMS_PER_PAGE,  //5
	$can_confirm=false, //6
	$can_super_confirm=false, //7
	$has_header=true, //8
	$is_ajax=false, //9
	$can_restore=false,//10
	$can_unconfirm=false,//11
	$can_create=false,//12
	$can_confirm_given=false,//13
	$can_unconfirm_given=false,//14
	&$alls,//15
	$can_percent=false,//16
	$can_print=false//17
	){
		
		
		$_pcg=new PayCodeGroup;
				
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.*,
					 
					sp.full_name as supplier_name,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					u1.name_s as confirmed_given_name, u1.login as confirmed_given_login,
				
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					ru.name_s as  ru_name, ru.login as ru_login,
					p.value as summa,
					
					ck.name as kind_name,
					b.code as bill_code,
					
					pc.code as code_code, pc.name as code_name,
					
					st.name as status_name
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as u1 on p.user_confirm_given_id=u1.id
					
					left join user as mn on p.manager_id=mn.id
					left join user as ru on p.responsible_user_id=ru.id
					
					left join cash_kind as ck on ck.id=p.kind_id
					left join bill as b on b.id=p.bill_id
					left join payment_code as pc on pc.id=p.code_id
				    left join document_status as st on st.id=p.status_id
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
					left join user as ru on p.responsible_user_id=ru.id
					
					left join cash_kind as ck on ck.id=p.kind_id
					left join bill as b on b.id=p.bill_id
					left join payment_code as pc on pc.id=p.code_id
				 left join document_status as st on st.id=p.status_id
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
		//echo $sql.'<br>';
		
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
		
//		echo $total;
		
		$_cbi=new CashToBillItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			//print_r($f);	
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			if($f['confirmed_given_pdate']!=0) $f['confirmed_given_pdate']=date("d.m.Y H:i:s",$f['confirmed_given_pdate']);
			else $f['confirmed_given_pdate']='-';
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			
			//счета, по которым доставка, экспед.
			$f['bills']=$_cbi->GetBillsbyCashArr($f['id'], $f['org_id']);
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_supplier='';
		$user_confirm_id='';
		$current_code='';
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='code_code') $current_code=$v->GetValue();
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
						
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//kontragent
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
		$sm->assign('can_super_confirm',$can_unconfirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('can_create',$can_create&&($this->_auth_result['org_id']==1)); //костыль для воз-ти созд-ия наличных только в СЯ
		$sm->assign('can_confirm_given',$can_confirm_given);
		$sm->assign('can_unconfirm_given',$can_unconfirm_given);
		
		$sm->assign('can_percent', $can_percent);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('prefix',$this->prefix);
		
		$sm->assign('can_print',$can_print);
		
		
		
		$pcg=$_pcg->GetItemsArrFlatted(0,$current_code);
		$_code_ids=array(''); $_code_vals=array('-все-');
		
		foreach($pcg as $k=>$v){
			
			$_code_ids[]=$v['id'];
			$_code_vals[]=$v['code'].' '.$v['name'];	
		}
		$sm->assign('code_code_ids',$_code_ids);
		$sm->assign('code_code_vals',$_code_vals);
		
		
		
		//ссылка для кнопок сортировки
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
			//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		
		return $sm->fetch($template);
	}
	
	
	//получить смежные счета по номеру счета и виду расхода
	public function GetNestedBills($bill_id, $kind_id){
		$alls=array();	
		$sql='select * from bill where id<>"'.$bill_id.'" and id in(select cb.bill_id from cash_to_bill as cb inner join cash as c on c.id=cb.cash_id where c.kind_id="'.$kind_id.'" and c.id in(select cash_id from cash_to_bill where bill_id="'.$bill_id.'"))';
		
		//echo $sql;
		$set=new mysqlSet($sql );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			
			$alls[]=$f;
		}
		
		return $alls;
	}
	
	
	
	
	
	
	
	//список счетов для доставки или экспедирования
	
	 
	public function ShowBillsForCash( 
		DBDecorator $dec,	 
		$kind,
		$checked
		 
	){
				
		
	
	 
		
		$sql='select p.*,
					sc.name as sector_name, sc.id as sector_id,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login,  p.confirm_shipping_pdate as confirm_shipping_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					st.name as status_name
				from bill as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join sector as sc on p.sector_id=sc.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					left join document_status as st on p.status_id=st.id
					
				where is_incoming="0" 
					and p.id not in(select distinct bill_id from cash_to_bill where cash_id in(select id from cash where kind_id="'.$kind.'" and is_confirmed=1))
					';
		$sql_count='select count(*)
				from bill as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join sector as sc on p.sector_id=sc.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					left join document_status as st on p.status_id=st.id
					
				where is_incoming="0" 
				and p.id not in(select distinct bill_id from cash_to_bill where cash_id in(select id from cash where kind_id="'.$kind.'" and is_confirmed=1))
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
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$_bi=new billitem; 
		 
		
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			if($f['supplier_bill_pdate']>0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			$f['total_cost']=$_bi->CalcCost($f['id']);
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date("d.m.Y H:i:s",$f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
		 	 $f['is_checked']=(in_array($f['id'], $checked));
			
			
			//echo $f['binded_payments'];
			
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price=''; $current_user_confirm_price_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sector_id'.$add_to_bill) $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id'.$add_to_bill) $current_supplier=$v->GetValue();
		//	if($v->GetName()=='storage_id'.$add_to_bill) $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			
			

			//$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		 
		
		return $alls;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
	}
	
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}
?>