<?
require_once('abstractgroup.php');
 
require_once('cash_in_codegroup.php');
require_once('cash_in_notesgroup.php');
require_once('cash_in_notesitem.php');

require_once('cash_in_item.php');

require_once('cash_to_bill_item.php');
require_once('user_s_item.php');
require_once('billitem.php');

require_once('cash_in_view.class.php');

// ������ �������� ��������
class CashInGroup extends AbstractGroup {
	 
	protected $_auth_result;
	
	
	
	public $prefix='_cash_in';
 
	protected $_item;
	protected $_notes_group;
	protected $_payforbillgroup;
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='cash_in';
		$this->pagename='view.php';		
		 
		$this->vis_name='is_confirmed';		
		 
		$this->_item=new  CashInItem;
		$this->_notes_group=new CashInNotesGroup;
		 
		$this->_view=new Cash_In_ViewGroup; 
		
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
		
		
		$_pcg=new CashInCodeGroup;
				
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
					left join cash_in_code as pc on pc.id=p.code_id
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
			if(!$can_delete) $reason='������������ ���� ��� ������ ��������';
			$f['can_annul_reason']=$reason;
			
			$reason='';
			$f['can_confirm']=$this->_item->DocCanConfirm($f['id'],$reason,$f)&&$can_confirm;
			if(!$can_confirm) $reason='������������ ���� ��� ������ ��������';
			$f['can_confirm_reason']=$reason;
			
			
			//�����, �� ������� ��������, ������.
			//$f['bills']=$_cbi->GetBillsbyCashArr($f['id'], $f['org_id']);
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id'],   0,  0,  false, false, false, 0,false);
			$alls[]=$f;
		}
		
		//�������� ������ ������
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
		
		$sm->assign('can_create',$can_create&&($this->_auth_result['org_id']==1)); //������� ��� ���-�� ����-�� �������� ������ � ��
		$sm->assign('can_confirm_given',$can_confirm_given);
		$sm->assign('can_unconfirm_given',$can_unconfirm_given);
		
		$sm->assign('can_percent', $can_percent);
		
		$sm->assign('has_header',$has_header);
		
		$sm->assign('prefix',$this->prefix);
		
		$sm->assign('can_print',$can_print);
		
		
		
		$pcg=$_pcg->GetItemsArrFlatted(0,$current_code);
		$_code_ids=array(''); $_code_vals=array('-���-');
		
		foreach($pcg as $k=>$v){
			
			$_code_ids[]=$v['id'];
			$_code_vals[]=$v['code'].' '.$v['name'];	
		}
		$sm->assign('code_code_ids',$_code_ids);
		$sm->assign('code_code_vals',$_code_vals);
		
		
		
		//������ ��� ������ ����������
		//������ ��� ������ ����������
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
			//����� ������������
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		
		return $sm->fetch($template);
	}
	
 
	
	
	
	
	
	
	//�������������� �������������
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
	}
	
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}
?>