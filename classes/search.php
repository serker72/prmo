<?
require_once('abstractitem.php');
require_once('db_decorator.php');
/*require_once('kpitem.php');
require_once('pl_positem.php');
require_once('plan_fact_fact_item.class.php');*/
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

require_once('supplieritem.php');

require_once('spitem.php');
require_once('filefolderitem.php');

require_once('fileitem.php');
require_once('filepmitem.php');
require_once('filelitem.php');
require_once('spsitem.php');

require_once('sched.class.php');



class Search{
	
	 
	protected $docs_list;
	
	
	function __construct(){
		$docs_list=array();	
	}
	
	public function AddDoc($doc){
		$this->docs_list[]=$doc;	
	}
	
	
	
	
	public function GetData($data, $do_it=false, &$total){
		$sql='';
		
		 
		$_sqls=array();
		foreach($this->docs_list as $k=>$v){
			
			
			
			$strr='( select distinct p.id,  "'.$k.'" as lab  '.$v->base_sql.'  ';
			
			$flt=array();
			foreach($v->fields as $kk=>$vv){
				$flt[]=' '.$vv.' LIKE "%'.$data.'%" ';	
			}
			$strr.= ' WHERE ';
			
			$strr.='('.implode(' OR ',$flt).')';
			
			$db_flt=$v->view_decorator->GenFltSql(' and ');
			if(strlen($db_flt)>0){
				$strr.=' and '.$db_flt;
			 
			
			}
			
			
			$strr.=')  ';
			
			$_sqls[]= $strr;
			
		}
		
		$sql='' .implode(' UNION ALL ', $_sqls).'  order by 2 asc, 1 desc ';
		
		
		
		
		 
		$alls=array();
		if($do_it){
			
			//echo $sql.'<br>';
			$set=new Mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				
				//найдем совпадения в полях
				$f['matched_fields']=$this->docs_list[(int)$f['lab']]->ConstructMatchedFields($f['id'], $data);
				$f['date']=$this->docs_list[(int)$f['lab']]->ConstructDate($f['id']);
				
				$f['name']=$this->docs_list[(int)$f['lab']]->ConstructName($f['id']);
				 
				$f['url']=$this->docs_list[(int)$f['lab']]->ConstructUrl($f['id']);
				
				$f['registry_url']=$this->docs_list[(int)$f['lab']]->ConstructRegistryUrl($f['id']);
				
				$alls[]=$f;
			}
		}
		 
		
		$total=count($alls);
		
		//разбиваем результат по блокам
		$alls1=array();
		foreach($this->docs_list as $k=>$v){
			
			$docs=array();
			
			foreach($alls as $kk=>$vv){
				if($vv['lab']==$k) $docs[]=$vv;	
			}
			
			$alls1[]=array('name'=>$v->block_name,
						   'docs'=>$docs);
		}
		
		return $alls1;
	}
	
}

class Search_AbstractDoc{
	public $block_name;
	 
	public $base_sql;
	public $fields; public $fields_names;
	public $view_decorator;
	
	
	public $pagename;
	public $extra_sting;
	public $id_name;
	
	public $registry_pagename;
	public $registry_extra_sting;
	public $registry_id_name;
	
	
	
	function __construct($block_name, $base_sql, $fields, $fields_names, $view_decorator, $pagename, $extra_sting, $id_name,$registry_pagename, $registry_extra_sting, $registry_id_name  ){
		$this->block_name=$block_name;
		$this->base_sql=$base_sql;
		$this->fields=$fields;
		$this->fields_names=$fields_names;
		$this->view_decorator=$view_decorator;
		
		$this->pagename=$pagename;
		$this->extra_sting=$extra_sting;
		$this->id_name=$id_name;
		
		$this->registry_pagename=$registry_pagename;
		$this->registry_extra_sting=$registry_extra_sting;
		$this->registry_id_name=$registry_id_name;
		
	}
	
	
	public function ConstructUrl($id){
		return $this->pagename.'?'.$this->id_name.'='.$id.'&'.$this->extra_sting;
	}
	
	public function ConstructRegistryUrl($id){
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$id.'&'.$this->registry_extra_sting;
	}
	
	public function ConstructName($id){
		
	}
	public function ConstructDate($id){
		
	}
	
	public function ConstructMatchedFields($id, $str){
		$sql='select '.implode(', ', $this->fields).' '.$this->base_sql.' where p.id="'.$id.'" ';
		$flt=array();
			foreach($this->fields as $kk=>$vv){
				$flt[]=' '.$vv.' LIKE "%'.$str.'%" ';	
			}
			$sql.= ' and ';
			
			$sql.='('.implode(' OR ',$flt).')';
		
		/*if($id==12468){
			//echo $sql;
		}*/
		
		//
		$set=new Mysqlset($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$matched=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs,  MYSQLI_NUM);
			
			
			 
			
			foreach($this->fields as $k=>$v){
				/*$v=eregi_replace('^([a-z]+)\.', '', $v);
				if($id==12468){
					echo $v; echo $f[$v];
				}
						
				if(stripos($f[$v], $str)!==false){
					if(!in_array($this->fields_names[$k], $matched)) $matched[]=$this->fields_names[$k];
					if($id==12468) echo "<br> $k $v $str vs $f[$v] <br> ";
				}*/
				
				if(stripos($f[$k], $str)!==false){
					if(!in_array($this->fields_names[$k], $matched)) $matched[]=$this->fields_names[$k];
					//if($id==12468) echo "<br> $k $v $str vs $f[$v] <br> ";
				}
			}
		 
		}
		//echo implode(', ', $matched);
		return implode(', ', $matched);
	}
		

		
}


class Search_Supplier extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new SupplierItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id); // $stat=$_stat->getitembyid($kp['status_id']);
		
		return 'Контрагент '.$kp['full_name']; //.', контрагент '.$kp['supplier_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		/*$_kp=new KpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);*/
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new SupplierItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}

class Search_Komplekt extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new KomplItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Заявка '.$kp['id'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new KomplItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new KomplItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?id='.$kp['id'].'&'.$this->registry_extra_sting;
	}
	

}




class Search_Bill extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new BillItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Исходящий счет '.$kp['code'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new BillItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new BillItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}



class Search_BillIn extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new BillInItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Входящий счет '.$kp['code'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new BillInItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new BillInItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code_1='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}



class Search_Acc extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new AccItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		 $_bill=new BillItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$bill=$_bill->GetItemById($kp['bill_id']);
		$sp=$_sp->GetItemById($bill['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Реализация '.$kp['id'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new AccItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['given_pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new AccItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?id='.$kp['id'].'&'.$this->registry_extra_sting;
	}
	

}



class Search_AccIn extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new AccInItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		 $_bill=new BillInItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$bill=$_bill->GetItemById($kp['bill_id']);
		$sp=$_sp->GetItemById($bill['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Поступление '.$kp['id'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new AccInItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['given_pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new AccInItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?id_1='.$kp['id'].'&'.$this->registry_extra_sting;
	}
	

}



class Search_Trust extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new TrustItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		 $_bill=new BillItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$bill=$_bill->GetItemById($kp['bill_id']);
		$sp=$_sp->GetItemById($bill['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Доверенность '.$kp['id'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new TrustItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new TrustItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?id='.$kp['id'].'&'.$this->registry_extra_sting;
	}
	

}




class Search_Pay extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PayItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Исходящая оплата '.$kp['code'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new PayItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['given_pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new PayItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}



class Search_PayIn extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new PayInItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Входящая оплата '.$kp['code'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new PayInItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['given_pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new PayInItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code_in='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}




class Search_InvCalc extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new InvCalcItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
		$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Акт инвентаризации взаиморасчетов '.$kp['code'].', контрагент  '.$opf['name'].' '.$sp['full_name'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new InvCalcItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['invcalc_pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new InvCalcItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code2='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


class Search_Inv  extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new InvItem; $_stat=new DocStatusItem;
		 $_sp=new SupplierItem; $_opf=new OpfItem;
		
		$kp=$_kp->getitembyid($id); $stat=$_stat->getitembyid($kp['status_id']);
	//	$sp=$_sp->GetItemById($kp['supplier_id']); $opf=$_opf->GetItemById($sp['opf_id']);
		
		return 'Акт инвентаризации остатков '.$kp['code'].', статус '.$stat['name'];
	}
	
	public function ConstructDate($id){
		$_kp=new InvItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['invcalc_pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new InvItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?code='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}

 


//файл справ. информ
class Search_SpravFile extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new SpItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new SpItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new SpItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}



//файл ф и д
class Search_File extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new FilePoItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new FilePoItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new FilePoItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}

//файл письма
class Search_FileL extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new FileLetItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new FileLetItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new FileLetItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}

//файл спец
class Search_Sps extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new SpSItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new SpSItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new SpSItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}

//файл +/-
class Search_Pm extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new FilePmItem;   $_fold=new FileFolderItem($_kp->GetStorageId());
		$kp=$_kp->GetItemById($id);
		
		$fld='';
		if($kp['folder_id']!=0) {
			$fold=$_fold->GetItemById($kp['folder_id']);
			$fld=', папка '.$fold['filename'];
		}
		
		return 'Файл '.$kp['orig_name'].', описание: '.$kp['txt'].''.$fld;
	}
	
	public function ConstructDate($id){
		$_kp=new FilePmItem;  
		
		$kp=$_kp->getitembyid($id);  
		
		return date('d.m.Y', $kp['pdate']);
	}
	
	
	public function ConstructRegistryUrl($id){
		$_kp=new FilePmItem; $_stat=new DocStatusItem;
		
		$kp=$_kp->getitembyid($id);
		return $this->registry_pagename.'?folder_id='.$kp['folder_id'].'&'.$this->registry_extra_sting;
	}
	

}



//активность планировщика - любая
class Search_Sched extends Search_AbstractDoc{
	public function ConstructName($id){
		$_kp=new Sched_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new Sched_Resolver($kp['kind_id']);
		
	 
		
		return $res->instance->ConstructFullName($id);
	}
	
	public function ConstructDate($id){
		$_kp=new Sched_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		
		$res=new Sched_Resolver($kp['kind_id']);
		
	 
		
		if($kp['kind_id']==5) return date('d.m.Y', $kp['pdate']);
		else return  DateFromYMD($kp['pdate_beg']);
	}
	
	
	public function ConstructRegistryUrl($id){
			$_kp=new Sched_AbstractItem; $_stat=new DocStatusItem;
		
		 
		
		$kp=$_kp->getitembyid($id); 
		return $this->registry_pagename.'?'.$this->registry_id_name.'='.$kp['code'].'&'.$this->registry_extra_sting;
	}
	

}


?>