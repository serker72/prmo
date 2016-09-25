<?
require_once('abstractgroup.php');
//require_once('storagegroup.php');
//require_once('sectorgroup.php');
require_once('user_s_item.php');
require_once('actionlog.php');
require_once('messageitem.php');


require_once('usercontactdatagroup.php');
require_once('suppliercontactkindgroup.php');

require_once('user_int_group.php');
require_once('user_v_item.php');

// users V
class UsersVGroup extends AbstractGroup {
	protected $group_id;
	public $instance;
	public $pagename;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->pagename='users_s.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		$this->group_id=2;
		$this->instance=new UserVItem;
	}
	
	
	public function GetItems($template, DBDecorator $dec, $from=0,$to_page=ITEMS_PER_PAGE, $in_storage=NULL, $in_sector=NULL,$can_view_inactive=false, $can_print=false, $prefix='', $tab_page=3){
		$txt='';
		
		$sm=new SmartyAdm;
		
		
		$sql='select u.*     from '.$this->tablename.'  as u
	 
		 where u.group_id="'.$this->group_id.'"  
			  ';
		
		$sql_count='select count(*) from '.$this->tablename.' as u
		 
		 
		 
		 where u.group_id="'.$this->group_id.'"  
		 ';
		
		
		
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
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		
		$_ukg=new UserContactDataGroup;
		
		$_uints=new UserIntGroup;
		
		$au=new AuthUser();
		//проверка возможности показа кнопки Создать карту сотрудника
		$sm->assign('can_create', $au->user_rights->CheckAccess('w', 771));
		
		
		//проверка возможности показа кнопки подробно
		$sm->assign('can_edit', $au->user_rights->CheckAccess('w',772));
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//print_r($f);
			
			$f['is_in_vac']=(($f['vacation_till_pdate']+24*60*60)>=time())&&($f['is_in_vacation']==1);
			$f['vacation_till_pdate_f']=date("d.m.Y",$f['vacation_till_pdate']);
			
			//контакты
			$ukg=$_ukg->GetItemsByIdArr($f['id']);
			//1,3 - rab,sot
			$f['phone_work_s']='';
			$f['phone_cell_s']='';
			$f['email_s']='';
			
			
			//5 - email
			
			foreach($ukg as $k=>$v){
				if($v['kind_id']==1) $f['phone_work_s'].=' '.stripslashes($v['value']);	
				if($v['kind_id']==3) $f['phone_cell_s'].=' '.stripslashes($v['value']);	
				if($v['kind_id']==5) $f['email_s'].=' '.stripslashes($v['value']);	
			}
			
			
			$f['ints']=$_uints->GetItemsByIdArr($f['id']);
			
			
			$f['can_edit']=($au->user_rights->CheckAccess('w',772)||($f['id']==$this->_auth_result['id']));
			

			
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		$current_storage='';
		$current_sector='';
		foreach($fields as $k=>$v){
			if($v->GetName()=='storage') $current_storage=$v->GetValue();
			if($v->GetName()=='sector') $current_sector=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('storage',$current_storage);
		$sm->assign('sector',$current_sector);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('prefix', $prefix);
		$sm->assign('pagename', $this->pagename);
		$sm->assign('ed_pagename', $this->instance->pagename);
		$sm->assign('tab_page',$tab_page);
		
		
		
		//echo 'zzzzzzzzzzzzzzzzzzzzzzzz';
		
		$sm->assign('can_view_inactive', $can_view_inactive);
		$sm->assign('can_print', $can_print);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $prefix, array('tab_page'));
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	//список позиций
	public function GetItemsArr($current_id=0,  $is_shown=0, $sortmode=0){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		if($sortmode==0) $ord_flt=' order by login asc';
		else $ord_flt=' order by name_s asc, login asc';
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.'  '.$ord_flt);
		else $set=new MysqlSet('select * from '.$this->tablename.' where  '.$this->vis_name.'="1" '.$ord_flt);
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
	
	
	//сотрудники отдела снабжения
	public function GetSupplyUsers(){
		$arr=array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		$set=new MysqlSet('select * from '.$this->tablename.' where  '.$this->vis_name.'="1" and is_supply_user=1');
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	
	//Отбор сотрудников для задачи и других карт
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL){
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		$sql='select p.*, up.name as position_name
		 from 
		 '.$this->tablename.' as p 
		 left join user_position as up on p.position_id=up.id
		 where p.group_id="'.$this->group_id.'"
			 ';
		
	
		
		//$sql.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		//$sql_count.=' where p.'.$this->is_org_name.'="'.$this->is_org.'" ';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		  $sql.=' and ';
		 
		
		$sql.=' p.is_active=1 ';
		
		
		
		$sql.=' order by p.name_s asc, p.login asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array();
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
		
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
		$link='users.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	
	
	//список пользователей, имеющих право на действие
	public function GetUsersByRightArr($letter='w', $object_id=241){
		$arr=array();
		
		$sql='select distinct u.* from user as u 
		inner join user_rights as ur on u.id=ur.user_id and ur.object_id="'.$object_id.'"
		inner join rights as r on r.id=ur.right_id and r.name="'.$letter.'"
		where u.is_active=1 and u.group_id="'.$this->group_id.'"
		order by u.name_s asc, u.login, u.id
		';
		
		//echo $sql;
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
		
	}
	
	
	
	
	
	//список пользователей с должностями по ключу в справочнике должностей
	public function GetUsersByPositionKeyArr($keyname, $keyvalue, $department_ids=NULL){
		$arr=array();
		
		
		$dep_filter='';
		if($department_ids!==NULL){
			$dep_filter=' and department_id in('.implode(', ',$department_ids).')';	
		}
		
		
		$sql='select distinct u.*, pos.name as position_name
		 from user as u 
		inner join user_position as pos on u.position_id=pos.id and pos.'.$keyname.'="'.$keyvalue.'"
		
		where u.is_active=1 and u.group_id="'.$this->group_id.'" '.$dep_filter.'
		order by u.name_s asc, u.login, u.id
		';
		
		//echo $sql;
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
		
	}
	
	
	//список пол-лей по декоратору в тегах option
	public function GetItemsByDecOpt($current_id=0,$fieldname='name', DBDecorator $dec, $do_no=false, $no_caption='-выберите-'){
		$txt='';
			
		$sql='select u.*, pos.name as position_name, dep.name as department_name from '.$this->tablename.'  as u
		left join user_position as pos on pos.id=u.position_id
		left join user_department as dep on dep.id=u.department_id 
		
		/*where u.group_id="'.$this->group_id.'" */
		';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			//$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		if($do_no){
		  $txt.="<option value=\"0\" ";
		  if($current_id==0) $txt.='selected="selected"';
		  $txt.=">". $no_caption."</option>";
		}
		
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				$txt.="<option value=\"$f[id]\" ";
				
				if($current_id==$f['id']) $txt.='selected="selected"';
				
				$txt.=">".htmlspecialchars(stripslashes($f[$fieldname].' '.$f['login'].' '.$f['position_name']))."</option>";
			}
		}
		return $txt;
	}
	
	//список пол-лей по декоратору массив
	public function GetItemsByDecArr( DBDecorator $dec){
		$arr= array();
			
		$sql='select u.*, pos.name as position_name, dep.name as department_name from '.$this->tablename.'  as u
		left join user_position as pos on pos.id=u.position_id
		left join user_department as dep on dep.id=u.department_id 
		
	/*	where u.group_id="'.$this->group_id.'" */
		';
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			//$sql_count.=' and '.$db_flt;	
		}
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		
		
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		 
		if($tc>0){
			$rs=$set->GetResult();
			for($i=0;$i<$tc;$i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				
				$arr[]=$f;
			}
		}
		return $arr;
	}
	
}
?>