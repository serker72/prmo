<?

require_once('abstractgroup.php');
require_once('user_s_group.php');

// абстрактная группа
class PrikazGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='prikaz';
		$this->pagename='claim.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	//показать заказы
	public function ShowData($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$can_add=false,$can_edit=false,$can_delete=false){
		$sm=new SmartyAdm;
		
		$sql='select o.*, u.login as login, u.name_s as name_s, u.group_id as group_id from '.$this->tablename.' as o
				left join user as u on o.'.$this->subkeyname.'=u.id	
		
		 ';
		
		$sql_count='select count(*) from '.$this->tablename.' as o 
				left join user as u on o.'.$this->subkeyname.'=u.id	
		
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
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_action='';
		$current_login='';
		$current_object='';
		$current_group='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			/*if($v->GetName()=='description') $current_action=$v->GetValue();
			if($v->GetName()=='object_id') {
				$current_object=$v->GetValue();
				
			}
			if($v->GetName()=='user_group_id') $current_group=$v->GetValue();*/
			if($v->GetName()=='login') $current_login=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		/*
		//действия
		$as=new mysqlSet('select distinct description from action_log order by description asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_action==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('ac',$acts);
		
		//объекты
		$as=new mysqlSet('select * from object order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('id'=>'', 'name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_object==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('ob',$acts);
		
		//группы
		//объекты
		$as=new mysqlSet('select * from groups order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('id'=>'', 'name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_group==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('ug',$acts);
		*/
		
		//managery
		$ud=new UsersSGroup;
		$uds=$ud->GetItemsArr();
		$dealer_ids=array(); $dealer_names=array();
		$dealer_ids[]=''; $dealer_names[]='';
		foreach($uds as $k=>$v){
			$dealer_ids[]=$v['login'];
			$dealer_names[]=$v['name_s'].' '.$v['login'];
		}
		$sm->assign('manager_ids',$dealer_ids);
		$sm->assign('manager_names',$dealer_names);
		$sm->assign('login',$current_login);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
		
	}
	
}
?>