<?
require_once('abstractgroup.php');
require_once('opfitem.php');

require_once('supplieritem.php');

require_once('supplier_responsible_user_group.php');
require_once('supplier_cities_group.php');

require_once('supplier_view.class.php');

require_once('supcontract_group.php');
require_once('supplier_ruk_item.php');
require_once('bdetailsgroup.php');
require_once('fagroup.php');



// users S
class SuppliersGroup extends AbstractGroup {
	protected $group_id;
	protected $is_org;
	protected $is_org_name;
	
	public function __construct($is_org=0){
		$this->init($is_org);
	}
	
	//установка всех имен
	protected function init($is_org){
		
		$this->is_org=$is_org;
		$this->is_org_name='is_org';
		
		$this->tablename='supplier';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=2;
		
		$this->_view=new Supplier_ViewGroup;
	}
	
	
	public function GetItems($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE,$is_ajax=false, $can_print=false, $limited_supplier=NULL, $result=NULL, $can_merge=false){
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$au=new AuthUser();
		if($result===NULL) $result=$au->Auth();
		
		
		$sql='select distinct p.*, po.name as opf_name,
			crea.name_s as crea_name_s, crea.email_s as crea_email_s,
			sb.name as branch_name, sb1.name as subbranch_name, sb2.name as subbranch_name1,
			
			holding.full_name as holding_name, holding_opf.name as  holding_opf_name,
			subholding.full_name as subholding_name, subholding_opf.name as  subholding_opf_name 
			
		
		 from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id 
			left join supplier_responsible_user as sr on sr.supplier_id=p.id
			left join user as u on u.id=sr.user_id
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
			left join supplier_branches as sb on sb.id=p.branch_id
			left join supplier_branches as sb1 on sb1.id=p.subbranch_id
			left join supplier_branches as sb2 on sb2.id=p.subbranch_id1

			left join supplier as holding on holding.id=p.holding_id
			left join opf as holding_opf on holding_opf.id=holding.opf_id
			
			left join supplier as subholding on subholding.id=p.subholding_id
			left join opf as subholding_opf on subholding_opf.id=subholding.opf_id
			
			
			
			 ';
		
		$sql_count='select count(distinct p.id) from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id
			left join supplier_responsible_user as sr on sr.supplier_id=p.id
			left join user as u on u.id=sr.user_id 
			left join supplier_sprav_city as ssc on ssc.supplier_id=p.id
			left join sprav_city as sc on ssc.city_id=sc.id
			left join  sprav_country as sc1 on sc1.id=sc.country_id
			left join user as crea on crea.id=p.created_id
			left join supplier_branches as sb on sb.id=p.branch_id
			left join supplier_branches as sb1 on sb1.id=p.subbranch_id
			left join supplier_branches as sb2 on sb2.id=p.subbranch_id1

			
			left join supplier as holding on holding.id=p.holding_id
			left join opf as holding_opf on holding_opf.id=holding.opf_id
			
			left join supplier as subholding on subholding.id=p.subholding_id
			left join opf as subholding_opf on subholding_opf.id=subholding.opf_id
			
			
			';
		
		$sql.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		$sql_count.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		
		$db_flt=$dec->GenFltSql(' and ');
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt)>0)){
				$db_flt.=' and ';	
			}
			$db_flt.='  p.id in ('.implode(', ',$limited_supplier).')';
			
		}
		
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
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
		
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator('suppliers.php',$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array(); $_acc=new SupplierItem;
		$_sr=new SupplierResponsibleUserGroup;
		 
			$_scg=new SupContractGroup;
		$_sri=new SupplierRukItem;
		$_sfg=new FaGroup;
		$_srekg=new BDetailsGroup;
		
		
		
		//проверка возможности показа кнопки подробно
		$can_delete=$au->user_rights->CheckAccess('w',88);
		$sm->assign('can_delete', $au->user_rights->CheckAccess('w',88));
		
		$can_edit= $au->user_rights->CheckAccess('w',87);
		$can_super_edit= $au->user_rights->CheckAccess('w',909);
		$_csg=new SupplierCitiesGroup;
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//$f['cannot_delete']=(bool)($f['is_active']==0);
			
			$f['can_annul']=$_acc->DocCanAnnul($f['id'],$reason)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['resps']=$_sr->GetUsersArr($f['id'], $ids);
			
			
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']=$csg;

			$f['contracts']=$_scg->GetItemsByIdArr($f['id']);
			
			//var_dump($f['contracts']);
			
			$sri_1=$_sri->GetActual($f['id'], 1);
			$sri_2=$_sri->GetActual($f['id'], 2);
		
			
			$f['chief']=$sri_1['fio'];
			$f['main_accountant']=$sri_2['fio'];
			
			$f['fa']=$_sfg->GetItemsByIdArr($f['id']);
			$f['bd']=$_srekg->GetItemsByIdArr($f['id']);


		$f['can_edit']= (
							$can_super_edit
							||$can_edit
							/*||(
							$can_edit&&in_array($result['id'], $ids)	
							)
							лишнее, переехало в limited_supplier
							*/
							
							);


			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		//проверка возможности показа кнопки Создать карту сотрудника
		$sm->assign('can_create', $au->user_rights->CheckAccess('w',87));
		
		
		//проверка возможности показа кнопки подробно
		$sm->assign('can_edit', $can_edit);
		
		
		$sm->assign('can_print', $can_print);
		
		
		$sm->assign('can_merge', $can_merge);
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
			
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		return $sm->fetch($template);
	}
	
	
	public function GetItemsWithOpfArr($only_sups=false, $org_id=1){
		
		
		
		$sql='select p.*, po.name as opf_name from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id  ';
		
		
		$flt='';
		if($only_sups) $flt=' and p.is_org=0 ';
		
		$sql.=' where p.is_active=1 '.$flt.' and (org_id="'.$org_id.'" or (p.is_org=1 and p.id<>"'.$org_id.'")) order by p.full_name asc';
		
		
	//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//$f['cannot_delete']=(bool)($f['is_active']==0);
			
			
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		return $alls;
	}
	
	
		//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.'   order by name asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where   '.$this->vis_name.'="1" order by name asc');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//получение итемov по набору полей
	public function GetItemsByFieldsArr($params){
		$res=array();
		$_opf=new OpfItem;
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		
		$item=new mysqlSet('select * from '.$this->tablename.' where  '.$qq.' order by full_name asc, id asc;');
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		//unset($item);
		for($i=0;$i<$rc; $i++){
			$ress=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v){
				$ress[$k]=stripslashes($v);	
			}
			
			$opf=$_opf->GetItemById($ress['opf_id']);
			if($opf!==false) $ress['opf_name']=stripslashes($opf['name']);
			
			$res[]=$ress;
		}
		
		
		return $res;
	}
	
	
	
	//Отбор поставщиков для вх.счета
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		$sql='select p.*, po.name as opf_name from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		//$sql.='  p.is_active=1 ';
		
		$sql.='(( p.is_org=0 and p.is_active=1 and p.org_id='.$resu['org_id'].') or (p.is_org=1 and p.is_active=1 and p.id<>'.$resu['org_id'].')) ';
		
		
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	//Отбор поставщиков для оплаты
	public function GetItemsForPay($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		$sql='select p.*, po.name as opf_name from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		$sql.='  p.is_active=1 and ((p.is_org=1 /*and p.id='.$resu['org_id'].'*/) or (p.is_org=0 and p.org_id='.$resu['org_id'].'))';
		
		 
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	//Отбор поставщиков для заявки
	public function GetItemsForKomplekt($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		$sql='select p.*, po.name as opf_name from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		//$sql.='  p.is_active=1 ';
		
		$sql.='(( p.is_org=0 and p.is_active=1  and p.org_id='.$resu['org_id'].') or (p.is_org=1 and p.is_active=1 and p.id<>'.$resu['org_id'].')) ';
		
		
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
}
?>