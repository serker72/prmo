<?
require_once('abstractgroup.php');
 

require_once('cashnotesgroup.php');
require_once('cashnotesitem.php');

require_once('cashitem.php');
require_once('cash_percent_item.php');

require_once('cash_to_bill_item.php');
require_once('user_s_item.php');

// группа расходов наличных
class CashPercentGroup extends AbstractGroup {
	 
	protected $_auth_result;
	
	
	
	public $prefix='_cash';
 
	protected $_item;
	protected $_notes_group;
	 
	 
	//установка всех имен
	protected function init(){
		$this->tablename='cash_percent';
		$this->pagename='cash_percents.php';		
		 
		$this->vis_name='is_confirmed';		
		 
		$this->_item=new CashPercentItem;
		$this->_notes_group=new CashNotesGroup;
		 
		
		$this->_auth_result=NULL;
	}
	
	
	
	
	
	
	
	public function ShowAllPos($template, 
	$dec,
	&$alls 
	 
	){
		
		
		
				
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.*,
					 
					pk.code, pk.name
					
					
				from '.$this->tablename.' as p
					
					left join payment_code as pk on p.code_id=pk.id
					
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					
					left join payment_code as pk on p.code_id=pk.id
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
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		 
	 

		
		$alls=array();
		
//		echo $total;
		
		$actual=$this->_item->GetActual($this->_auth_result['org_id']);
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['begin_pdate']=date('d.m.Y', $f['begin_pdate']);
			
			$f['is_active']=(($actual!==false)&&($actual['id']==$f['id'])) ;
			
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
		
		
		$sm->assign('code',37);
		$sm->assign('pdate',date('d.m.Y')); 
		
		$sm->assign('prefix',$this->prefix);
		
		//ссылка для кнопок сортировки
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		
		
		
		return $sm->fetch($template);
	}
	
 
	
	
	
	
	
	
	
	
	
	
	
	
 
	public function SetSubkeyTable($t){
		$this->sub_tablename=$t;	
	}
}
?>