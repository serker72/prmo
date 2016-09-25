<?
require_once('abstractgroup.php');
require_once('billitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('billnotesgroup.php');
require_once('billnotesitem.php');
require_once('payforbillgroup.php');

require_once('period_checker.php');

require_once('bill_view.class.php');
require_once('bill_in_view.class.php');

// абстрактная группа счетов
class AbstractBillGroup extends AbstractGroup {
	protected $_auth_result;
	
	public $prefix='_1';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill';
		$this->pagename='bills.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		
		$this->_auth_result=NULL;
		
		$this->_view=new Bill_ViewGroup;
	}
	
	
	
	
	public function GainSql(&$sql, &$sql_count){
		
		$sql='select p.*,
					sc.name as sector_name, sc.id as sector_id,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login,  p.confirm_shipping_pdate as confirm_shipping_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					st.name as status_name,
					 
					con.contract_no as bill_contract_no,
					bd.rs, bd.ks, bd.bank, bd.bik, bd.city, bd.is_basic,
					sp1.full_name as ship_supplier_name, sp1.id as ship_supplier_id,
					spo1.name as ship_supplier_opf_name
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join sector as sc on p.sector_id=sc.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					left join document_status as st on p.status_id=st.id
					left join supplier_contract as con on con.id=p.contract_id
					left join banking_details as bd on bd.id=p.bdetails_id
					 
					left join supplier as sp1 on p.ship_supplier_id=sp1.id
				 
					left join opf as spo1 on spo1.id=sp1.opf_id
				where p.is_incoming="'.$this->is_incoming.'" 
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join sector as sc on p.sector_id=sc.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					left join document_status as st on p.status_id=st.id
					left join supplier_contract as con on con.id=p.contract_id
					left join banking_details as bd on bd.id=p.bdetails_id
					 
					left join supplier as sp1 on p.ship_supplier_id=sp1.id
				 
					left join opf as spo1 on spo1.id=sp1.opf_id
					
				where p.is_incoming="'.$this->is_incoming.'" 
					';
		
	}
	
	
	
	
	
	
	public function ShowPos($template,  //0
		DBDecorator $dec,	//1
		$from=0,	//2
		$to_page=ITEMS_PER_PAGE, 	//3
		$can_add=false, 	//4
		$can_edit=false, 	//5
		$can_delete=false, 	//6
		$add_to_bill='', 	//7
		$can_confirm=false,  //8
		$can_super_confirm=false, 	//9
		$has_header=true, 	//10
		$is_ajax=false, 	//11
		$can_restore=false,	//12
		$limited_sector=NULL,	//13
		$nested_bill_positions=NULL,	//14 
		$can_confirm_ship=false,	//15
		$can_unconfirm=false, 	//16
		$can_unconfirm_ship=false, 	//17
		&$alls, 	//18
		$can_print=false,	//19
		$can_email_pdf=false,	//20
		$can_make_cash=false,	//21
		$limited_supplier=NULL,  //22
		$can_confirm_price_bigger=false,  //23 можно утвердить цены больше, чем в исх. счете
		$can_add_usl=false  //24 создание счета на услуги из реестра
		 
	){
				
		
	
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$this->GainSql($sql, $sql_count);
				 
		$db_flt=$dec->GenFltSql(' and ');
		
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			
			
			$db_flt.=' (  p.supplier_id in ('.implode(', ',$limited_supplier).')';
			if($this->is_incoming==1){
				//также подгрузить вход счета, связанные с его исходящими
				$db_flt.=' or p.out_bill_id in(select id from bill where is_incoming=0 and supplier_id in ('.implode(', ',$limited_supplier).')) ';	
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
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			if($f['supplier_bill_pdate']>0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			$f['total_cost']=$this->_item->CalcCost($f['id']);
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date("d.m.Y H:i:s",$f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id'],  0,  0,  false,  false, false, 0, false);
			
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$this->_item->GetBindedDocumentsToAnnul($f['id']);
			
			
			$f['binded_payments']=$this->_item->GetBindedPayments($f['id'],$binded_summ);
			$f['binded_payments_summ']=$binded_summ;
			
			
			$f['avans_payments']=$this->_payforbillgroup->GetAvans($f['supplier_id'],$f['org_id'],$f['id'],$avans, $raw_ids, $raw_invs, $f['contract_id']);
			$f['avans_payments_summ']=$avans;
			
			//echo $f['code'].' = '.$avans.' <br>';
			
			$f['sum_by_bill']=$this->_payforbillgroup->SumByBill($f['id']);
			
			//снятие утверждения отгрузки
			$reason='';
			$f['can_unconfirm_by_document']=$this->_item->DocCanUnconfirmShip($f['id'],$reason,$f);
			$f['can_unconfirm_by_document_reason']=$reason;
			
			
			
			if($f['pdate_shipping_plan']!=0) $f['pdate_shipping_plan']=date("d.m.Y",$f['pdate_shipping_plan']);
			else $f['pdate_shipping_plan']='-';
			
			if($f['pdate_payment_contract']!=0) $f['pdate_payment_contract']=date("d.m.Y",$f['pdate_payment_contract']);
			else $f['pdate_payment_contract']='-';
			
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
			
			
			/*if(('action'.$add_to_bill ==$v->GetName())) $sm->assign('action',$v->GetValue());	
			elseif(('id'.$add_to_bill== $v->GetName())) $sm->assign('id',$v->GetValue());	
			else */
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
		
		
		//u4astok
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
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
			$sm->assign('can_confirm_price',$can_confirm);
			$sm->assign('can_unconfirm_price',$can_unconfirm);
		$sm->assign('can_super_confirm_price',$can_unconfirm);
		
		$sm->assign('can_confirm_shipping', $can_confirm_ship);
		$sm->assign('can_unconfirm_shipping', $can_unconfirm_ship);
		$sm->assign('can_super_confirm_shipping',$can_unconfirm_ship);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('can_print',$can_print);
		
		$sm->assign('can_email_pdf',$can_email_pdf);
		$sm->assign('can_make_cash',$can_make_cash);
		
		$sm->assign('prefix',$this->prefix);
		
		$sm->assign('can_confirm_price_bigger', $can_confirm_price_bigger);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('can_add_usl', $can_add_usl);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('&action'.$this->prefix,'&action',$link);
		$link=eregi_replace('&id'.$this->prefix,'&id',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	//упрощенный метод для вывода списка связанных счетов,
	//например, в реестре заявок
	public function ShowPosSimple($template,  //0
		DBDecorator $dec,	//1
		$from=0,	//2
		$to_page=ITEMS_PER_PAGE, 	//3
		$can_add=false, 	//4
		$can_edit=false, 	//5
		$can_delete=false, 	//6
		$add_to_bill='', 	//7
		$can_confirm=false,  //8
		$can_super_confirm=false, 	//9
		$has_header=true, 	//10
		$is_ajax=false, 	//11
		$can_restore=false,	//12
		$limited_sector=NULL,	//13
		$nested_bill_positions=NULL,	//14 
		$can_confirm_ship=false,	//15
		$can_unconfirm=false, 	//16
		$can_unconfirm_ship=false, 	//17
		&$alls, 	//18
		$can_print=false,	//19
		$can_email_pdf=false,	//20
		$can_make_cash=false,	//21
		$limited_supplier=NULL,  //22
		$can_confirm_price_bigger=false,  //23 можно утвердить цены больше, чем в исх. счете
		$can_add_usl=false  //24 создание счета на услуги из реестра
		 
	){
				
		
	
		
	 
		
		$this->GainSql($sql, $sql_count);
				 
		$db_flt=$dec->GenFltSql(' and ');
		
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			
			
			$db_flt.=' (  p.supplier_id in ('.implode(', ',$limited_supplier).')';
			if($this->is_incoming==1){
				//также подгрузить вход счета, связанные с его исходящими
				$db_flt.=' or p.out_bill_id in(select id from bill where is_incoming=0 and supplier_id in ('.implode(', ',$limited_supplier).')) ';	
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
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			if($f['supplier_bill_pdate']>0) $f['supplier_bill_pdate']=date("d.m.Y",$f['supplier_bill_pdate']);
			else $f['supplier_bill_pdate']='-';
			
			$f['total_cost']=$this->_item->CalcCost($f['id']);
			
			$reason='';
			 
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date("d.m.Y H:i:s",$f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			 
			
		 
			
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
	 		
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			
			
		 
		//	$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		 
		
		
		 
		
		
		 
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('&action'.$this->prefix,'&action',$link);
		$link=eregi_replace('&id'.$this->prefix,'&id',$link);
		 
		
		return $alls;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and is_incoming="'.$this->is_incoming.'" order by  id asc');
		
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
	
	
	
}
?>