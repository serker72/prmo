<?
//require_once('db_decorator.php');

//классы, связанные с шаблонам рассылки: 

//список шаблонов рассылки
class Delivery_TemplateGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_template';
		$this->pagename='delivery_templates.php';		
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
		
		$_li=new Delivery_TemplateItem;
		
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






//элемент шаблон рассылки
class Delivery_TemplateItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_template';
		$this->item=NULL;
		$this->pagename='delivery_ed_template.php';	
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
	
	/*public function Del($id){
		
		$query = 'delete from delivery_user_segment where user_id in(select id from delivery_user where list_id='.$id.')';
		new nonSet($query);
		
		$query = 'delete from delivery_user_segment where segment_id in(select id from  delivery_segment where list_id='.$id.')';
		new nonSet($query);
		
		$query = 'delete from  delivery_segment where list_id='.$id.'';
		new nonSet($query);
		
		$query = 'delete from delivery_user where list_id='.$id.'';
		new nonSet($query);
		
		
		return parent::Del($id);	
	}*/
}



?>