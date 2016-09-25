<?

require_once('abstractgroup.php');
require_once('posgroupgroup.php');
require_once('posgroupitem.php');
require_once('positem.php');

require_once('catalog_view.class.php');




//  группа каталога
class PosGroup extends AbstractGroup {
	protected $_view;

	
	//установка всех имен
	protected function init(){
		$this->tablename='catalog_position';
		$this->pagename='catalog.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_view=new Catalog_ViewGroup;

		
	}
	
	
	public function ShowPos($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$can_edit=false, $can_delete=false, $is_ajax=false, $can_expand_groups=false,$can_add_group=false, $can_edit_group=false, $can_delete_group=false, $can_add=false, $initial_kol='', $prefix='',  $can_max_val=false, $can_active_position=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					d.name as dim_name,
					g.name as group_name
				from '.$this->tablename.' as p
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join catalog_dimension as d on p.dimension_id=d.id
					left join catalog_group as g on p.group_id=g.id
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
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $prefix));
		$navig->SetFirstParamName('from'.$prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		$_gi=new PosGroupItem;
		$_pi=new PosItem;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			
			$gi=$_gi->GetItemById($f['group_id']);
			if($gi['parent_group_id']>0){
				$gi2=$_gi->GetItemById($gi['parent_group_id']);	
				if($gi2['parent_group_id']>0){
					$gi3=$_gi->GetItemById($gi2['parent_group_id']);		
					/*$current_one_id=$gi3['id'];
					$current_two_id=$gi2['id'];
					$current_three_id=$gi['id'];*/
					$f['group_name']=stripslashes($gi3['name'].'-> '.$gi2['name'].'-> '.$gi['name']);
				}else{
					/*$current_one_id=$gi2['id'];
					$current_two_id=$gi['id'];
					$current_three_id=0;*/
					$f['group_name']=stripslashes($gi2['name'].'-> '.$gi['name']);	
				}
			}else{
				/*$current_one_id=$gi['id'];
				$current_two_id=0;
				$current_three_id=0;	*/
				
			}
			
			 
			
			$f['can_delete']=$_pi->CanDelete($f['id']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$current_two_group='';
		$current_three_group='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			/*if($v->GetName()=='description') $current_action=$v->GetValue();
			if($v->GetName()=='object_id') {
				$current_object=$v->GetValue();
				
			}
			if($v->GetName()=='user_group_id') $current_group=$v->GetValue();*/
			if($v->GetName()=='dimension_id') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			if($v->GetName()=='two_group_id') $current_two_group=$v->GetValue();
			if($v->GetName()=='three_group_id') $current_three_group=$v->GetValue();
			
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		//единицы изм
		$as=new mysqlSet('select * from catalog_dimension order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_dimension_id==$f[0]); 
			$acts[]=$f;
		}
		$sm->assign('dim',$acts);
		
		//тов группы
		$as=new mysqlSet('select * from catalog_group where parent_group_id=0 order by name asc');
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('name'=>'');
		$gr_ids=array(); $gr_names=array();
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_group_id==$f[0]); 
			$acts[]=$f;
			
			$gr_ids[]=$f['id'];
			$gr_names[]=$f['name'];
		}
		$sm->assign('group',$acts);
		
		
		
		//группы
		
		$sm->assign('group_ids',$gr_ids);
		$sm->assign('group_names',$gr_names);
		
		//подгруппы
		if($current_group_id>0){
			$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_group_id.'" order by name asc');
			$rs=$as->GetResult();
			$rc=$as->GetResultNumRows();
			$acts=array();
			$acts[]=array('name'=>'');
			$gr_ids=array(); $gr_names=array();
			
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				foreach($f as $k=>$v) $f[$k]=stripslashes($v);
				$f['is_current']=($current_two_group==$f[0]); 
				$acts[]=$f;
				
				$gr_ids[]=$f['id'];
				$gr_names[]=$f['name'];
			}
			$sm->assign('two_group',$acts);
			
			$sm->assign('two_group_ids',$gr_ids);
			$sm->assign('two_group_names',$gr_names);
			
			
			if($current_two_group>0){
				$as=new mysqlSet('select * from catalog_group where parent_group_id="'.$current_two_group.'" order by name asc');
				$rs=$as->GetResult();
				$rc=$as->GetResultNumRows();
				$acts=array();
				$acts[]=array('name'=>'');
				$gr_ids=array(); $gr_names=array();
				
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					foreach($f as $k=>$v) $f[$k]=stripslashes($v);
					$f['is_current']=($current_three_group==$f[0]); 
					$acts[]=$f;
					
					$gr_ids[]=$f['id'];
					$gr_names[]=$f['name'];
				}
				$sm->assign('three_group',$acts);
				
				$sm->assign('three_group_ids',$gr_ids);
				$sm->assign('three_group_names',$gr_names);
			}
		}
		
		
		
		//действия
		/*$as=new mysqlSet('select distinct description from action_log order by description asc');
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
		$sm->assign('ug',$acts);*/
		
		
			
		 
		
		
		$sm->assign('can_expand_groups', $can_expand_groups);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('initial_kol',$initial_kol);
		$sm->assign('prefix',$prefix);
		
		$sm->assign('can_add_group',$can_add_group);
		$sm->assign('can_edit_group',$can_edit_group);
		$sm->assign('can_delete_group',$can_delete_group);
		
		$sm->assign('can_max_val',$can_max_val);
		$sm->assign('can_active_position',  $can_active_position); //просмотр неактивных позиций
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix);
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		return $sm->fetch($template);
	}
	
}
?>