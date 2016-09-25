<?
require_once('abstractgroup.php');

require_once('supplier_cities_group.php');
require_once('suppliercontactgroup.php');
require_once('supplier_responsible_user_group.php');

class Quick_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//Отбор поставщиков для события планировщика
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0, $do_show_contacts=true){
	
		$_csg=new SupplierCitiesGroup;
		$_cg=new SupplierContactGroup;
		
		$_sr=new SupplierResponsibleUserGroup;
		
		
		
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		
		$limited_supplier=NULL;
	
		if($au->FltSupplier($resu)){  
			 
			
			$_s_to_u=new SupplierToUser;
			$s_to_u=$_s_to_u->GetExtendedViewedUserIdsArr($resu['id'], $resu);
			$limited_supplier=$s_to_u['sector_ids'];
		}
		
		

		
		$sql='select p.*, po.name as opf_name,
			sb.name as branch_name, ssb.name as subbranch_name, ssb1.name as subbranch_name1,
			holding.full_name as holding_name, holding_opf.name as  holding_opf_name,
			subholding.full_name as subholding_name, subholding_opf.name as  subholding_opf_name 
		

		
		 from supplier as p 
			left join opf as po on p.opf_id=po.id  
			
			left join supplier_branches as sb on p.branch_id=sb.id
			left join supplier_branches as ssb on p.subbranch_id=ssb.id
			
			left join supplier_branches as ssb1 on p.subbranch_id1=ssb1.id
			
			left join supplier as holding on holding.id=p.holding_id
			left join opf as holding_opf on holding_opf.id=holding.opf_id
			
			left join supplier as subholding on subholding.id=p.subholding_id
			left join opf as subholding_opf on subholding_opf.id=subholding.opf_id
			

			';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
	 
		
		
		$sql.=' order by p.full_name asc ';
		
		 
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		$can_edit= $au->user_rights->CheckAccess('w',87);
		$can_super_edit= $au->user_rights->CheckAccess('w',909);
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']= $csg;	 
			
			//контакты
			$f['contacts']=$_cg->GetItemsByIdArr($f['id']);
			
			
			//
			$f['resps']=$_sr->GetUsersArr($f['id'], $ids);
			
			 
			/* 
			
			$f['can_edit']= (
							$can_super_edit
							||(
							$can_edit&&in_array($resu['id'], $ids)	
							)
							
							);*/
			
			$f['can_edit']=$can_super_edit;
			if($limited_supplier!==NULL) $f['can_edit']=$f['can_edit']||($can_edit&&in_array($f['id'], $limited_supplier));
			
			
				
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		$sm->assign('do_show_contacts', $do_show_contacts);
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	  
	
}
?>