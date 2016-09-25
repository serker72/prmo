<?
require_once('abstractgroup.php');
 
 
require_once('supplier_ruk_item.php');
require_once('supplieritem.php');
require_once('opfitem.php');
 

// группа расходов наличных
class SupplierRukGroup extends AbstractGroup {
	 
	protected $_auth_result;
	
	
	
	public $prefix='_cash';
 
	protected $_item;
	protected $_notes_group;
	 
	 
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_ruk';
		$this->pagename='supplier_ruks.php';		
		 
		$this->vis_name='is_confirmed';		
		 
		$this->_item=new SupplierRukItem;
		  
		
		$this->_auth_result=NULL;
	}
	
	
	
	
	
	
	
	public function ShowAllPos($supplier_id, $template, 
	$dec,
	&$alls 
	 
	){
		
		$_si=new SupplierItem;
		$_opf=new OpfItem;
		
				
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		$sql='select p.*,
					 
				  pk.name
					
					
				from '.$this->tablename.' as p
					
					left join supplier_ruk_kind as pk on p.kind_id=pk.id
					
					';
		 
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		 
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
		
		$actual_1=$this->_item->GetActual($supplier_id, 1);
		$actual_2=$this->_item->GetActual($supplier_id, 2);
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['begin_pdate']=date('d.m.Y', $f['begin_pdate']);
			
			//$f['is_active_1']=(($actual_1!==false)&&($actual_1['id']==$f['id'])&&(1==$f['kind_id'])) ;
			
			//$f['is_active_2']=(($actual_2!==false)&&($actual_2['id']==$f['id'])&&(2==$f['kind_id'])) ;
			
			
			$f['is_active']=(($actual_1!==false)&&($actual_1['id']==$f['id'])&&(1==$f['kind_id'])) || (($actual_2!==false)&&($actual_2['id']==$f['id'])&&(2==$f['kind_id'])) ;
			
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_supplier='';
		$user_confirm_id='';
	
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			//if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
						
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
		
		
		$supplier=$_si->getitembyid($supplier_id);
		$opf=$_opf->getitembyid($supplier['opf_id']);
		
		$sm->assign('supplier',$supplier); 
		$sm->assign('opf',$opf); 
		
		$sm->assign('supplier_id',$supplier_id); 
		
	//	$sm->assign('code',37);
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