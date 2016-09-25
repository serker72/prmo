<?
require_once('abstractgroup.php');
require_once('billitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('billnotesgroup.php');
require_once('billnotesitem.php');
require_once('payforbillgroup.php');

require_once('period_checker.php');

// 
class AnWaaBillsAsbtract extends AbstractGroup {
	protected $_auth_result;
	
	public $prefix='_2';
	public $url_prefix='_in';
	protected $is_incoming=1;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='bill';
		$this->pagename='an_waa.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_item=new BillItem;
		$this->_notes_group=new BillNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup;
		
		
		
		$this->_auth_result=NULL;
	}
	
	public function ShowPos(
		$template,  //1
		DBDecorator $dec, //2
		$from=0, //3
		$to_page=ITEMS_PER_PAGE, //4
		$can_add=false, //5
		$can_edit=false, //6
		$can_delete=false, //7
		$add_to_bill='', //8
		$can_confirm=false, //9 
		$can_super_confirm=false, //10
		$has_header=true, //11
		$is_ajax=false, //12
		$can_restore=false, //13
		$limited_sector=NULL, //14
		$nested_bill_positions=NULL, //15
		$can_confirm_ship=false, //16
		$can_unconfirm=false, //17
		$can_unconfirm_ship=false, //18 
		$can_eq, //19
		$do_show=false //20
	){
		
		
		
		//if($this->is_incoming==0) var_dump($do_show);
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					us.name_s as confirmed_shipping_name, us.login as confirmed_shipping_login,  p.confirm_shipping_pdate as confirm_shipping_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login,
					utv.id as utv_id, utv.name_s as utv_name, utv.login as utv_login
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					left join user as utv on p.cannot_an_id=utv.id
				where p.is_incoming="'.$this->is_incoming.'" 
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_price_id=u.id
					left join user as us on p.user_confirm_shipping_id=us.id
					left join user as mn on p.manager_id=mn.id
					left join user as utv on p.cannot_an_id=utv.id
				where p.is_incoming="'.$this->is_incoming.'" 
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
			
			if($nested_bill_positions!==NULL){
				
			}
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		//echo 'zzzzzzzzzzzzzzzzzzzzzzz';
		if($do_show){
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRows();
		
		
	
	
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['total_cost']=$this->_item->CalcCost($f['id']);
			
			$reason='';
		
			
			if($f['cannot_an_pdate']==0){
				$f['utv_pdate']=' ';	
			}else $f['utv_pdate']=date("d.m.Y H:i:s",$f['cannot_an_pdate']);
			
			
			if($f['confirm_price_pdate']!=0) $f['confirm_price_pdate']=date("d.m.Y H:i:s",$f['confirm_price_pdate']);
			else $f['confirm_price_pdate']='-';
			
			
			if($f['confirm_shipping_pdate']!=0) $f['confirm_shipping_pdate']=date("d.m.Y H:i:s",$f['confirm_shipping_pdate']);
			else $f['confirm_shipping_pdate']='-';
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']);
			
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$this->_item->GetBindedDocumentsToAnnul($f['id']);
			
			
			$f['binded_payments']=$this->_item->GetBindedPayments($f['id'],$binded_summ);
			$f['binded_payments_summ']=$binded_summ;
			
			
			$f['avans_payments']=$this->_payforbillgroup->GetAvans($f['supplier_id'],$f['org_id'],$f['id'],$avans, $raw_ids);
			$f['avans_payments_summ']=$avans;
			$f['sum_by_bill']=$this->_payforbillgroup->SumByBill($f['id']);
			
			//снятие утверждения отгрузки
			$reason='';
			$f['can_unconfirm_by_document']=$this->_item->DocCanUnconfirmShip($f['id'],$reason,$f);
			$f['can_unconfirm_by_document_reason']=$reason;
			
			
			//echo $f['binded_payments'];
			
			$alls[]=$f;
		}
		
		}
		
		//заполним шаблон полями
		$current_storage='';
		$current_supplier='';
		$current_user_confirm_price=''; $current_user_confirm_price_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='supplier_id'.$add_to_bill) $current_supplier=$v->GetValue();
		
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
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
		
			$sm->assign('prefix',$this->prefix);
		$sm->assign('url_prefix',$this->url_prefix);
		$sm->assign('is_incoming',$this->is_incoming);
		
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('can_eq',$can_eq);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$link=eregi_replace('tab_page'.$this->prefix, 'tab_page', $link);
		
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
}
?>