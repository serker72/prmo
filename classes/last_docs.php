<?
require_once('abstractitem.php');
require_once('db_decorator.php');
//require_once('kpitem.php');
require_once('docstatusitem.php');

require_once('komplitem.php');

require_once('billitem.php');
require_once('bill_in_item.php');

require_once('acc_item.php');
require_once('acc_in_item.php');

require_once('payitem.php');
require_once('pay_in_item.php');

require_once('cashitem.php');

require_once('cash_in_item.php');
require_once('trust_item.php');

require_once('invitem.php');

require_once('invcalcitem.php');
require_once('supplieritem.php');

require_once('opfitem.php');



class LastDocs{
	
	protected $result_data;
	protected $docs_list;
	protected $ct=1;
	
	function __construct(){
		$docs_list=array();	
	}
	
	public function AddDoc($doc){
		$this->docs_list[]=$doc;	
	}
	
	
	
	
	public function GetData($user_id){
		$sql='';
		
		$_sqls=array();
		foreach($this->docs_list as $k=>$v){
			$_sqls[]=' (select distinct affected_object_id, "'.$k.'" as lab, pdate from action_log where user_subj_id='.$user_id.' and object_id in('.implode(', ',$v->object_ids).') '.$v->extra_filters_sql.' and affected_object_id<>0  order by pdate desc  limit '.$this->ct.' ) ';
		}
		
		$sql=' ' .implode(' UNION ALL ', $_sqls).' order by 3 desc limit 5 ';
		
	//	echo $sql.'<br>';
		
		$set=new Mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		$data=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			
			$f['name']=$this->docs_list[(int)$f['lab']]->ConstructName($f['affected_object_id']);
			$f['date']=$this->docs_list[(int)$f['lab']]->ConstructDate($f['affected_object_id']);
			
			$f['url']=$this->docs_list[(int)$f['lab']]->ConstructUrl($f['affected_object_id']);
			
			$data[]=$f;
		}
		
		return $data;
	}
	
}

class LastDocs_AbstractDoc{
	public $object_ids;
	public $extra_filters_sql;
	public $pagename;
	public $extra_sting;
	public $id_name;
	
	
	function __construct($object_ids, $extra_filters_sql, $pagename, $extra_sting, $id_name){
		$this->object_ids=$object_ids;
		$this->extra_filters_sql=$extra_filters_sql;
		$this->pagename=$pagename;
		$this->extra_sting=$extra_sting;
		$this->id_name=$id_name;
		
		
		
	}
	
	
	public function ConstructUrl($id){
		return $this->pagename.'?'.$this->id_name.'='.$id.'&'.$this->extra_sting;
	}
	
	public function ConstructName($id){
		
	}
	public function ConstructDate($id){
		
	}
		
}

class LastDocs_KP extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new KpItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Коммерческое предложение '.$kp['code'].', контрагент '.$kp['supplier_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	

}


//заявка
class LastDocs_Kompl extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new KomplItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Заявка  № '.$kp['id'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new KomplItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	

}


//исходящий счет
class LastDocs_Bill extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new BillItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Исходящий счет  № '.$kp['code'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new BillItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['pdate']);
	}
	

}


//входящий счет
class LastDocs_BillIn extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new BillInItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Входящий счет  № '.$kp['code'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new BillInItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['pdate']);
	}
	

}

//поступление
class LastDocs_AccIn extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new AccInItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
		$sp=$_sp->GetItemById($bill['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Поступление  № '.$kp['id'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new AccInItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['given_pdate']);
	}
	

}

//реализация
class LastDocs_Acc extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new AccItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
		$_bill=new BillItem; $bill=$_bill->Getitembyid($kp['bill_id']);
		$sp=$_sp->GetItemById($bill['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Реализация  № '.$kp['id'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new AccItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['given_pdate']);
	}
	

}

//входящая оплата
class LastDocs_PayIn extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PayInItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Входящая оплата  № '.$kp['code'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new PayInItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['given_pdate']);
	}
	

}



//исходящая оплата
class LastDocs_Pay extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PayItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Исходящая оплата  № '.$kp['code'].', контрагент '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new PayItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['given_pdate']);
	}
	

}



//расход наличных
class LastDocs_Cash extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new CashItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
	//	$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Расход наличных № '.$kp['code'].',  статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new CashItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['pdate']);
	}
	

}

//привход наличных
class LastDocs_CashIn extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new CashInItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
	//	$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Приход наличных № '.$kp['code'].',  статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new CashInItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['pdate']);
	}
	

}

//доверенность
class LastDocs_Trust extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new TrustItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
	//	$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Доверенность № '.$kp['id'].',  статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new TrustItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['pdate']);
	}
	

}

//инвост
class LastDocs_Inv extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new InvItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
	//	$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Акт инвентаризации № '.$kp['code'].',  статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new InvItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['inventory_pdate']);
	}
	

}

//инввзр
class LastDocs_InvCalc extends LastDocs_AbstractDoc{
	public function ConstructName($id){
		$_kp=new InvCalcItem; $_stat=new DocStatusItem; $_sp=new SupplierItem; $_opf=new OpfItem;
		
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		
	//	$_bill=new BillInItem; $bill=$_bill->Getitembyid($kp['bill_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Акт инвентаризации № '.$kp['code'].', контрагент '.$sp['full_name'].',  статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new InvCalcItem;  
		
		$kp=$_kp->getitembyid($id);  
	 
		return date('d.m.Y', $kp['invcalc_pdate']);
	}
	

}

?>