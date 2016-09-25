<?
require_once('abstractgroup.php');


require_once('user_s_item.php');
require_once('user_s_group.php');
require_once('opfitem.php');

require_once('an_fill_abstract_entry.php');
require_once('an_fill_simple_entry.php');
require_once('an_fill_complex_entry.php');
require_once('an_fill_subsequent_entry.php');
require_once('an_fill_set_entries.php');
require_once('an_fill_set_entries_suppliers.php');


//заполненность карт контрагентов
class AnFill {
	public $prefix='_1';
	
	
	public function ShowData(array $fields, $template='', $dec, $do_it=false, $can_print=true, $print=0, $org_id=1, $quests_fill_only=0, $quests_unfill_only=0){
		$txt='';
		$alls=array();
		
		$_se=new AnFillSetEntriesSuppliers; //AnFillSetEntries;
		
		
		
		$sm=new SmartyAdm;
		
		$sm->assign('prefix', $this->prefix);
		$se= $_se->DeployForms($fields);
		$sm->assign('qsts',$se);
		$sm->assign('per_page',ceil(count($se)/2));
		
		
		//найдем всех поставщиков
		$sql='select s.*, opf.name as opf_name from supplier as s left join opf on s.opf_id=opf.id
		where s.is_active=1 and ((org_id="'.$org_id.'" and s.is_org=0) or (s.is_org=1 and s.id<>"'.$org_id.'")) ';
		
		
		$db_flt=$dec->GenFltSql(' and ');
		
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			 
		}
		
		$sql.=' order by s.full_name asc, opf.name asc, s.id asc';
		//echo $sql;
		
		$count_of_fields=0;
		foreach($fields as $k=>$v) if($v->is_checked) $count_of_fields++;
		
		
		if($do_it&&($count_of_fields>0)){
			
			
			$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				
				$f['subs']=$_se->Compare($f, $fields, $is_empty);
				
				$f['is_empty']= $is_empty;
				
				$alls[]=$f;	
			}
			
		}
		
		
		
		$sm->assign('items',$alls);
		
	
		$sm->assign('print',$print);
		$sm->assign('can_print',$can_print);
		
		$sm->assign('quests_fill_only', $quests_fill_only);
		$sm->assign('quests_unfill_only',$quests_unfill_only);
		
		$sm->assign('do_it',$do_it&&($count_of_fields>0));
		
		$txt=$sm->fetch($template);
		
		return $txt;
	}
	
	
}
?>