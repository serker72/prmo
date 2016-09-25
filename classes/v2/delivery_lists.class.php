<?
//require_once('db_decorator.php');

//классы, связанные со списками рассылки: списки, подписчики, сегменты...

//список списков рассылки
class Delivery_ListGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_list';
		$this->pagename='delivery_lists.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		 
	}
	
	public function GetItems($template, $from, $to_page, DBDecorator $dec, $can_add=false, $can_edit=false, $can_delete=false, $is_ajax=false, $prefix=''){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$sm->assign('can_add', $can_add);
		$sm->assign('can_edit', $can_edit);
		$sm->assign('can_delete', $can_delete);
		
		$sql='select  p.*,
					 count(s.id) as s_q
				from '.$this->tablename.' as p
					left join delivery_user as s on s.list_id=p.id
					
					';
		$sql_count='select count(p.id)
				from '.$this->tablename.' as p
				/*	left join delivery_user as s on s.list_id=p.id*/
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
		}
		
		$sql.=' group by p.id ';
		
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
		
		$_li=new Delivery_ListItem;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			$f['pdate_change']=date('d.m.Y H:i:s', $f['pdate_change']);
			
			
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
		
		$sm->assign('listpagename',$this->pagename);
		$sm->assign('ed_pagename', $_li->GetPageName());
		
	 
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix);
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);	
	}
	
}






//элемент список рассылки
class Delivery_ListItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_list';
		$this->item=NULL;
		$this->pagename='delivery_ed_list.php';	
		$this->vis_name='is_shown';	 
	}
	
	
	public function Add($params){
		if(!isset($params['pdate'])) $params['pdate']=time();
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Add($params);	
	}
	
	public function Edit($id, $params){
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Edit($id, $params);	
	}
	
	public function Del($id){
		
		$query = 'delete from delivery_user_segment where user_id in(select id from delivery_user where list_id='.$id.')';
		new nonSet($query);
		
		$query = 'delete from delivery_user_segment where segment_id in(select id from  delivery_segment where list_id='.$id.')';
		new nonSet($query);
		
		$query = 'delete from  delivery_segment where list_id='.$id.'';
		new nonSet($query);
		
		$query = 'delete from delivery_user where list_id='.$id.'';
		new nonSet($query);
		
		
		return parent::Del($id);	
	}
}


//список сегментов
class Delivery_SegmentGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_segment';
		$this->pagename='delivery_segments.php';		
		$this->subkeyname='list_id';	
		$this->vis_name='is_shown';		
		
		 
	}
	
	
	public function GetItemsById($id, $template, $from, $to_page, DBDecorator $dec, $can_add=false, $can_edit=false, $can_delete=false, $is_ajax=false, $prefix=''){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$sm->assign('can_add', $can_add);
		$sm->assign('can_edit', $can_edit);
		$sm->assign('can_delete', $can_delete);
		
		$sql='select  p.*, 
				count(s.id) as s_q
				from '.$this->tablename.' as p
					left join delivery_user_segment as s on s.segment_id=p.id
					left join delivery_user as du on du.id=s.user_id
					
			
					 
				where p.'.$this->subkeyname.'="'.$id.'"	
					';
		$sql_count='select count(p.id)
				from '.$this->tablename.' as p
				/*left join delivery_user_segment as s on s.segment_id=p.id
					left join delivery_user as du on du.id=s.user_id*/
					 
				where p.'.$this->subkeyname.'="'.$id.'"		
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		$sql.=' group by p.id ';
		 
		
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
		
		$_li=new Delivery_SegmentItem;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			$f['pdate_change']=date('d.m.Y H:i:s', $f['pdate_change']);
			
			
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
		$sm->assign('id',$id);
		
		
		
		$sm->assign('listpagename',$this->pagename);
		$sm->assign('ed_pagename', $_li->GetPageName());
		
		 
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix);
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);	
	}
	
	
	
	
}

//элемент сегмент
class Delivery_SegmentItem extends AbstractItem{
	protected function init(){
		$this->tablename='delivery_segment';
		$this->item=NULL;
		$this->pagename='delivery_ed_segment.php';	
		$this->vis_name='is_shown';	 
	}
	
	public function Add($params){
		if(!isset($params['pdate'])) $params['pdate']=time();
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Add($params);	
	}
	
	public function Edit($id, $params){
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Edit($id, $params);	
	}
	
	public function Del($id){
		
		 
		
		$query = 'delete from delivery_user_segment where segment_id='.$id.' ';
		new nonSet($query);
		
		
		return parent::Del($id);	
	}
	
	
	//функция добавки пол-лей в сегмент
	public function AddUsers($id, array $users){
		
		$_kpi=new Delivery_UserSegmentItem;
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		
		$sql='select * from delivery_user_segment where segment_id="'.$id.'" ';
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$old_positions[]=$f;
		}
		
		
		foreach($users as $k=>$v){
			$kpi=$this->GetItemByFields(array('segment_id'=>$id,'user_id'=>$v));
			
			if($kpi===false){
				//dobavim pozicii	
				
				
				$add_array=array();
				$add_array['user_id']=$v;
				$add_array['segment_id']=$id;
				
				
				
				
				$_kpi->Add($add_array);
				
				$log_entries[]=array(
					'action'=>0,
					'segment_id'=>$id,
					'user_id'=>$v
				);
				
			}/*
			секция редактирования не нужна!
			*/
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($users as $kk=>$vv){
				if($vv==$v['user_id']){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			
			
			$log_entries[]=array(
					'action'=>2,
					'segment_id'=>$id,
					'user_id'=>$v['user_id']
			);
			
			//удаляем позицию
			$_kpi->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	//загрузка полного списка подписчиков с отметкой, кто в сегменте
	public function LoadUsersArr($list_id, $segment_id){
		$arr=array();
		
		$sql=' select p.*, s.id as s_id
			from delivery_user as p
			left join delivery_user_segment as s on p.id=s.user_id and s.segment_id="'.$segment_id.'"
			
		order by p.email asc, p.id desc ';
		
		//echo $sql; 
		$set=new mysqlSet($sql);
		$tc=$set->GetResultNumRows();
		
		$rs=$set->GetResult();
		for($i=0;$i<$tc;$i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//в каких сегментах состоит??
			$segments=array();
			$sql1=' select distinct p.* from delivery_segment as p
			inner join delivery_user_segment as ps on p.id=ps.segment_id
			where ps.user_id="'.$f['id'].'"
			order by p.name asc, p.id desc';
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			for($j=0; $j<$rc1; $j++){
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				$segments[]=$g;
			}
			$f['segments']=$segments;
			
			$arr[]=$f;
		}	
		
		return $arr;
	}
}


//список подписчиков
class Delivery_UserGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_user';
		$this->pagename='delivery_list_users.php';		
		$this->subkeyname='list_id';	
		$this->vis_name='is_shown';		
		
		 
	}
	
	public function GetItemsById($id, $template, $from, $to_page, DBDecorator $dec, $can_add=false, $can_edit=false, $can_delete=false, $is_ajax=false, $prefix='', &$alls){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$sm->assign('can_add', $can_add);
		$sm->assign('can_edit', $can_edit);
		$sm->assign('can_delete', $can_delete);
		
		$sql='select p.*,
				sup.full_name as supplier_name,  opf.name as opf_name, sc.name as supplier_contact_name,
				
				us.name_s as user_name
				
				from '.$this->tablename.' as p
					 
				left join supplier as sup on sup.id=p.supplier_id and p.kind_id=1
				left join opf as opf on opf.id=sup.opf_id
				left join supplier_contact as sc on sc.id=p.supplier_contact_id
				
				left join user as us on us.id=p.user_id and p.kind_id=2
					 
				where p.'.$this->subkeyname.'="'.$id.'"	
					';
		$sql_count='select count(p.id)
				from '.$this->tablename.' as p
				
				left join supplier as sup on sup.id=p.supplier_id and p.kind_id=1
				left join opf as opf on opf.id=sup.opf_id
				left join supplier_contact as sc on sc.id=p.supplier_contact_id
				
				left join user as us on us.id=p.user_id and p.kind_id=2
					 
				where p.'.$this->subkeyname.'="'.$id.'"		
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
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $prefix));
		$navig->SetFirstParamName('from'.$prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		$_li=new Delivery_UserItem;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			$f['pdate_change']=date('d.m.Y H:i:s', $f['pdate_change']);
			
			//в каких сегментах состоит??
			$segments=array();
			$sql1=' select distinct p.* from delivery_segment as p
			inner join delivery_user_segment as ps on p.id=ps.segment_id
			where ps.user_id="'.$f['id'].'"
			order by p.name asc, p.id desc';
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			for($j=0; $j<$rc1; $j++){
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				$segments[]=$g;
			}
			$f['segments']=$segments;
			
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
		$sm->assign('id',$id);
		
		
		
		$sm->assign('listpagename',$this->pagename);
		$sm->assign('ed_pagename', $_li->GetPageName());
		
		//подгрузить список других списков
		$sql='select * from delivery_list where id<>"'.$id.'" order by name asc';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$lists=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$lists[]=$f;
		}
		$sm->assign('other_lists', $lists);	
		
		//список всех сегментов
		$sql='select * from delivery_segment where list_id="'.$id.'" order by name asc, id desc';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$lists=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$lists[]=$f;
		}
		$sm->assign('segments', $lists);	
	 
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix);
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);	
	}
	
}

//элемент подписчик
class Delivery_UserItem extends AbstractItem{
	protected function init(){
		$this->tablename='delivery_user';
		$this->item=NULL;
		$this->pagename='delivery_ed_user.php';	
		$this->vis_name='is_shown';	 
	}
	
	public function Add($params){
		if(!isset($params['pdate'])) $params['pdate']=time();
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Add($params);	
	}
	
	public function Edit($id, $params){
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Edit($id, $params);	
	}
	
	
	
		//получение первого итема по набору полей
	public function GetItemByFields($params, $not_params=NULL){
		
		$qq='';
		foreach($params as $key=>$val){
			if($qq=='') $qq.=$key.'="'.$val.'" ';
			else $qq.=' and '.$key.'="'.$val.'" ';
		}
		if($not_params!==NULL) foreach($not_params as $key=>$val){
			if($qq=='') $qq.=$key.'<>"'.$val.'" ';
			else $qq.=' and '.$key.'<>"'.$val.'" ';
		}
		
		$sql='select * from '.$this->tablename.' where '.$qq.'';
		//echo $sql;
		
		$item=new mysqlSet($sql);
		$result=$item->getResult();
		$rc=$item->getResultNumRows();
		unset($item);
		if($rc!=0){
			$res=mysqli_fetch_array($result);
			$this->item= Array();
			foreach($res as $key=>$val){
				$this->item[$key]=$val;
			}
			
			return $this->item;
		} else {
			$this->item=NULL;
			return false;
		}	
		
	}
	
	
}


//подписчик в сегменте
class Delivery_UserSegmentItem extends AbstractItem{
	protected function init(){
		$this->tablename='delivery_user_segment';
		$this->item=NULL;
		$this->pagename='delivery_ed_list.php';	
		$this->vis_name='is_shown';	 
	}
}


?>