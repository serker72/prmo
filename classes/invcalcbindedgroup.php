<?
require_once('abstractgroup.php');
require_once('payitem.php');
require_once('payforbillgroup.php');
require_once('paynotesgroup.php');
require_once('paynotesitem.php');

require_once('invcalcitem.php');
require_once('invcalcnotesgroup.php');
require_once('invcalcnotesitem.php');

// абстрактная группа
class InvCalcBindedGroup extends AbstractGroup {
	protected $sub_tablename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_confirmed';		
		$this->sub_tablename='bill';
		
		
	}
	
	
	
	public function ShowPays($bill_id, $supplier_id, $template, DBDecorator $dec, $can_edit=false, $can_delete=false,  $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$can_unconfirm=false,$only_bill=NULL){
		
		
		$_bill=new BillItem;
		
		$_bpg=new PayForBillGroup;
		
		$_acc=new PayItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select pb.value as value, 
					p.`id`, p.`code`, p.`bill_id`, p.`supplier_id`, p.`user_confirm_id`, p.`confirm_pdate`, p.`pdate`,  p.`is_confirmed`, p.`notes`, p.`org_id`, p.`manager_id`, p.`supplier_bdetails_id`, p.`org_bdetails_id`, p.`pay_for_dogovor`, p.`pay_for_bill`, p.value as summa, p.status_id, p.given_pdate, p.given_no,
					o.id as o_id, o.pdate as o_pdate,
					sp.full_name as supplier_name,
					spo.name as opf_name,
					u.name_s as confirmed_name, u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from payment as p
					inner join payment_for_bill as pb on p.id=pb.payment_id
					left join invcalc as o on p.invcalc_id=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as mn on p.manager_id=mn.id
				where pb.invcalc_id="'.$bill_id.'"
				  and p.supplier_id="'.$supplier_id.'" ';
		
				 
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
		
		$_bng=new PaymentNotesGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			$f['can_annul']=$_acc->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$_acc->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;
			
			
			
			
			$_bpg->SetIdName('payment_id');
			
			if($only_bill===NULL) $f['osnovanie']=$_bpg->GetItemsByIdForPage($f['id']);
			else $f['osnovanie']=$_bpg->GetItemsByIdForPage($f['id'],$bill_id);
			$f['notes']=$_bng->GetItemsByIdArr($f['id']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$user_confirm_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		$sm->assign('supplier_id',$supplier_id);
		
		$sm->assign('action',1);
		$sm->assign('id',$bill_id);
		
		
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('has_header',$has_header);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	public function ShowBills($invcalc_id, $template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE, $can_add=false, $can_edit=false, $can_delete=false, $add_to_bill='', $can_confirm=false,  $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL,$nested_bill_positions=NULL, $can_confirm_ship=false, $can_unconfirm=false, $can_unconfirm_ship=false, $result=NULL, $is_incoming=1, $can_print=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		$_bill=new BillItem;
		
		$_pfg=new PayForBillGroup;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login,  p.confirm_shipping_pdate as confirm_shipping_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from bill as p
					inner join payment_for_bill as pb on (pb.bill_id=p.id and pb.invcalc_id="'.$invcalc_id.'")
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$is_incoming.'"
					';
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			
			if($nested_bill_positions!==NULL){
				$sql.=' and p.id in(select distinct bill_id from  bill_position where komplekt_ved_id = "'.$nested_bill_positions.'") ';
				
			}
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		
		//page
		
		
		$_pi=new BillItem;
		$_bng=new BillNotesGroup;
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['total_cost']=$_bill->CalcCost($f['id']);
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date("d.m.Y H:i:s",$f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id']);
			
			
			$f['can_annul']=$_bill->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($f['id']);
			
			
			$f['binded_payments']=$_bill->GetBindedPayments($f['id'],$binded_summ);
			$f['binded_payments_summ']=$binded_summ;
			
			
			$f['avans_payments']=$_pfg->GetAvans($f['supplier_id'],$f['org_id'],$f['id'],$avans, $raw_ids);
			$f['avans_payments_summ']=$avans;
			$f['sum_by_bill']=$_pfg->SumByBill($f['id']);
			
			//снятие утверждения отгрузки
			$reason='';
			$f['can_unconfirm_by_document']=$_bill->DocCanUnconfirmShip($f['id'],$reason,$f);
			$f['can_unconfirm_by_document_reason']=$reason;
			
			
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
			if($v->GetName()=='storage_id'.$add_to_bill) $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		
		$sm->assign('items',$alls);
		$sm->assign('id',$invcalc_id);
		
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
		$sm->assign('can_print', $can_print);
		
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('prefix', $add_to_bill);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	
	
	
	
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}
?>