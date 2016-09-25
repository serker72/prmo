<?

require_once('abstractgroup.php');
require_once('sectoritem.php');
require_once('sectornotesgroup.php');

require_once('sector_view.class.php');

//  группа складов
class SectorGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sector';
		$this->pagename='sector.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_active';		
		
		$this->_view=new Sector_ViewGroup;

		
	}
	
	
	public function ShowPos($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE,$can_edit=false, $can_delete=false, $can_print=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		
		$sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
				u1.name_s as nach_user, u2.name_s as zamnach_user
				from '.$this->tablename.' as p
				left join user as u1 on u1.id=p.nach_user_id
				left join user as u2 on u2.id=p.zamnach_user_id					
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
				left join user as u1 on u1.id=p.nach_user_id
				left join user as u2 on u2.id=p.zamnach_user_id		
					
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
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		$_pi=new SectorItem;
		$_bng=new SectorNotesGroup;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			//print_r($f);	
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id']);
			
			$f['can_delete']=$_pi->CanDelete($f['id']);
			
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			/*if($v->GetName()=='description') $current_action=$v->GetValue();
			if($v->GetName()=='object_id') {
				$current_object=$v->GetValue();
				
			}
			if($v->GetName()=='user_group_id') $current_group=$v->GetValue();*/
			//if($v->GetName()=='dimension_id') $current_dimension_id=$v->GetValue();
			//if($v->GetName()=='group_id') $current_group_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_print',$can_print);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
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
		if($is_shown==0) $set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->vis_name.'="1" order by id asc');
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