<?
require_once('abstractgroup.php');

require_once('orgitem.php');
require_once('org_view.class.php');



// 
class OrgsGroup extends AbstractGroup {
	protected $group_id;
	protected $is_org;
	protected $is_org_name;
	protected $_view;
	
	public function __construct($is_org=1){
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
		
		$this->_view=new Org_ViewGroup;

	}
	
	
	public function GetItems($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE, $can_bind_pays_acc=false,
	$can_view_period=false,
		$can_view_rates=false
	){
		$txt='';
		
		$sm=new SmartyAdm;
		
		
		$sql='select p.*, po.name as opf_name, crea.name_s as crea_name_s, crea.email_s as crea_email_s
		 from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id
			left join user as crea on crea.id=p.created_id
		 ';
		
		$sql_count='select count(*) from '.$this->tablename.' as p 
			left join opf as po on p.opf_id=po.id
			left join user as crea on crea.id=p.created_id
		 ';
		

		
		$sql.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		$sql_count.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		
		$db_flt=$dec->GenFltSql(' and ');
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
		
		$_pi=new OrgItem;
		
		
		$_scg=new SupContractGroup;
		$_sri=new SupplierRukItem;
		$_sfg=new FaGroup;
		$_srekg=new BDetailsGroup;
			$_csg=new SupplierCitiesGroup;
		

		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//$f['cannot_delete']=(bool)($f['is_active']==0);
			//print_r($f);
			
			$f['can_delete']=$_pi->CanDelete($f['id']);
			
			$f['contracts']=$_scg->GetItemsByIdArr($f['id']);
			
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']=$csg;
			
			//var_dump($f['contracts']);
			
			$sri_1=$_sri->GetActual($f['id'], 1);
			$sri_2=$_sri->GetActual($f['id'], 2);
		
			
			$f['chief']=$sri_1['fio'];
			$f['main_accountant']=$sri_2['fio'];
			
			$f['fa']=$_sfg->GetItemsByIdArr($f['id']);
			$f['bd']=$_srekg->GetItemsByIdArr($f['id']);
			

			
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
		
		
		$au=new AuthUser();
		//проверка возможности показа кнопки Создать карту сотрудника
		$sm->assign('can_create', $au->user_rights->CheckAccess('w',120));
		
		
		//проверка возможности показа кнопки подробно
		$sm->assign('can_edit', $au->user_rights->CheckAccess('w',121));
		
		//проверка возможности показа кнопки подробно
		$sm->assign('can_delete', $au->user_rights->CheckAccess('w',122));
		
		$sm->assign('can_bind_pays_acc', $can_bind_pays_acc);
		
		$sm->assign('can_view_period', $can_view_period);
		$sm->assign('can_view_rates', $can_view_rates);
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='organizations.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		return $sm->fetch($template);
	}
	
	
		//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($is_shown==0) $set=new MysqlSet('select t.*, opf.name as opf_name from '.$this->tablename.' as t left join opf on t.opf_id=opf.id where t.'.$this->is_org_name.'="'.$this->is_org.'"  order by t.full_name asc');
		else $set=new MysqlSet('select t.*, opf.name as opf_name from '.$this->tablename.' as t left join opf on t.opf_id=opf.id  where t.'.$this->is_org_name.'="'.$this->is_org.'" and  t.'.$this->vis_name.'="1" order by t.full_name asc');
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
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		
		$item=new mysqlSet('select * from '.$this->tablename.' where '.$this->is_org_name.'="'.$this->is_org.'" and '.$qq.' order by id asc;');
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		//unset($item);
		for($i=0;$i<$rc; $i++){
			$ress=mysqli_fetch_array($result);
			
			foreach($res as $k=>$v){
				$ress[$k]=stripslashes($v);	
			}
			$res[]=$ress;
		}
		
		
		return $res;
	}
	
	
	
	//список орг., доступных пользователю
	public function GetItemsByUserIdArr($user_id, $current_id){
		$arr=Array();
		 $set=new MysqlSet('select p.*, op.name as opf_name from '.$this->tablename.' as p left join opf as op on p.opf_id=op.id where is_active=1 and p.id in(select distinct org_id from supplier_to_user where user_id="'.$user_id.'") order by p.name asc, p.id asc');
		
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
	
	
}
?>