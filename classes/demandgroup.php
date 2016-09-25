<?
require_once('abstractgroup.php');
require_once('payitem.php');
require_once('abstract_payforbillgroup.php');


require_once('demandnotesgroup.php');
require_once('demandnotesitem.php');
require_once('demanditem.php');
require_once('billitem.php');
require_once('user_s_item.php');

require_once('demand_view.class.php');

// абстрактная группа оплат
class DemandGroup extends AbstractGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='demand';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_confirmed';		
	 
		$this->_item=new DemandItem;
				$this->_notes_group=new DemandNotesGroup;
		 
		 
		
		
		$this->_auth_result=NULL;
		
		$this->_view=new  Demand_ViewGroup;
	}
	
	
	
	
	
	
	
	public function ShowAllPos($template, DBDecorator $dec,$can_edit=false, $can_delete=false, $from=0, $to_page=ITEMS_PER_PAGE,  $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$can_unconfirm=false, $can_print=false){
		
		
		
				
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.*,
					o.id as o_id, o.code as o_code, o.pdate as o_pdate, o.given_pdate as o_given_pdate, o.given_no as o_given_no,
					sp.full_name as supplier_name,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
				
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					inu.name_s as inu_name_s, inu.login as inu_login,
					st.name as status_name,
					p.value as summa,
					
					bd.rs, bd.ks, bd.bank, bd.bik, bd.city, bd.is_basic,
					org_bd.rs as org_rs, org_bd.ks as org_ks, org_bd.bank as org_bank, org_bd.bik as org_bik, org_bd.city as org_city, org_bd.is_basic as org_is_basic
					
				from '.$this->tablename.' as p
					left join payment as o on p.pay_id=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
					left join user as inu on p.inner_user_id=inu.id
					left join document_status as st on p.status_id=st.id
					
					left join banking_details as bd on bd.id=p.supplier_bdetails_id
					left join banking_details as org_bd on org_bd.id=p.org_bdetails_id
				 
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join payment as o on p.pay_id=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
					left join user as inu on p.inner_user_id=inu.id
					left join document_status as st on p.status_id=st.id
					
					left join banking_details as bd on bd.id=p.supplier_bdetails_id
					left join banking_details as org_bd on org_bd.id=p.org_bdetails_id
					
			 
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
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $this->prefix));
		$navig->SetFirstParamName('from'.$this->prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
	
		
		$alls=array();
		
//		echo $total;
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			//print_r($f);	
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			/*$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='недостаточно прав для данной операции';
			$f['can_confirm_reason']=$reason;*/
			
		//	$this->_payforbillgroup->SetIdName('payment_id');
			//$f['osnovanie']=$this->_payforbillgroup->GetItemsByIdForPage($f['id']);
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
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
		$sm->assign('can_print',$can_print);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('prefix',$this->prefix);
		
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
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
	}
	
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}
?>