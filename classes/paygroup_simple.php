<?
require_once('abstractgroup.php');
require_once('payitem.php');
require_once('abstract_payforbillgroup.php');


require_once('paynotesgroup.php');
require_once('paynotesitem.php');
require_once('billitem.php');
require_once('user_s_item.php');

// упрощенная группа оплат (сделано для разрешения конфликтов при наследовании групп оплат)
class PayGroup_Simple extends AbstractGroup {
	protected $sub_tablename;
	protected $_auth_result;
	
	
	
	public $prefix='_1';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	protected $_posgroup;
	protected $_bill_item;
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment';
		$this->pagename='view.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_confirmed';		
		$this->sub_tablename='bill';
		
	/*	$this->_item=new PayItem;
		$this->_notes_group=new PaymentNotesGroup;
		
		$this->_posgroup=new AbstractPayForBillGroup;
		
		$this->_bill_item=new BillItem;*/
		
		//$this->_payforbillgroup=new AbstractPayForBillGroup;
		
		$this->_auth_result=NULL;
	}
	
	
	public function ShowPos($bill_id, $supplier_id, $template, DBDecorator $dec, $is_ajax=true){
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select pb.value as value, 
					p.`id`, p.`code`, p.`bill_id`, p.`supplier_id`, p.`user_confirm_id`, p.`confirm_pdate`, p.`pdate`,  p.`is_confirmed`, p.`notes`, p.`org_id`, p.`manager_id`, p.`supplier_bdetails_id`, p.`org_bdetails_id`, p.`pay_for_dogovor`, p.`pay_for_bill`, p.value as summa, p.status_id, p.given_pdate, p.given_no,
					o.id as o_id, o.pdate as o_pdate,
					sp.full_name as supplier_name,
					spo.name as opf_name,
					u.name_s as confirmed_name, u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login/*,
					sum(pb.value) as pb_value*/
				from '.$this->tablename.' as p
					inner join payment_for_bill as pb on p.id=pb.payment_id
					left join '.$this->sub_tablename.' as o on p.'.$this->subkeyname.'=o.id
					left join supplier as sp on p.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as mn on p.manager_id=mn.id
				where pb.'.$this->subkeyname.'="'.$bill_id.'"
				 
				  and p.supplier_id="'.$supplier_id.'" ';
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' where '.$db_flt;	
		}
		
		//$sql.=' group by p.`bill_id` ';
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql);
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
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			if($f['given_pdate']!=0) $f['given_pdate']=date("d.m.Y",$f['given_pdate']);
			else $f['given_pdate']='-';
			
			
			//$this->_payforbillgroup->SetIdName('payment_id');
			//$f['osnovanie']=$this->_payforbillgroup->GetItemsByIdForPage($f['id']);
			
			//var_dump($f['pb_value']);
			
			$f['osnovanie']=array(
				array(
					'value'=>$f['pb_value']
				)
			);
			
			
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
		$sm->assign('supplier_id',$supplier_id);
		
		$sm->assign('action',1);
		$sm->assign('id',$bill_id);
		
		
		
	
		$sm->assign('prefix',$this->prefix);
		
		
		
		
		//ссылка для кнопок сортировки
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		//echo $template;
		
		return $sm->fetch($template);
	}
	
	
	
	
	
}
?>