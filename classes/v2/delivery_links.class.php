<?
//require_once('db_decorator.php');

//классы, связанные с шаблонам рассылки: 

//список ссылок рассылки
class Delivery_LinksGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_link';
		$this->pagename='delivery_templates.php';		
		$this->subkeyname='delivery_id';	
		$this->vis_name='is_shown';		
		
		 
	}
	
	
	//удалить все данные по ссылкам рассылки
	public function ClearStats($delivery_id){
		
		 $query = 'delete from delivery_subscriber_hits where   delivery_id="'.$delivery_id.'" ';
		new nonSet($query);
		
		 
		
		$query = 'delete from delivery_link_hits where   delivery_id="'.$delivery_id.'" ';
		new nonSet($query);
		
		$query = 'delete from delivery_link where delivery_id="'.$delivery_id.'"';
		new nonSet($query);
		 
	} 
	
	
	//найти число хитов по рассылке
	public function CountHits($delivery_id){
		$query = 'select sum(hits) from delivery_link_hits where delivery_id="'.$delivery_id.'" ';
		$set=new mysqlSet($query);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		return (int)$f[0];
	}
	
	
	
	/*
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
	*/
}






//элемент ссылка рассылки
class Delivery_LinkItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_link';
		$this->item=NULL;
		$this->pagename='delivery_ed_template.php';	
		$this->vis_name='is_shown';	 
	}
	
	
	
	public function Del($id){
		
		  
		
		$query = 'delete from delivery_link_hits where link_id='.$id.'';
		new nonSet($query);
		
		
		return parent::Del($id);	
	} 
	
	//внести ссылку (если ее нет)
	public function Put($delivery_id, $url){
		$test=$this->GetItemByFields(array('delivery_id'=>$delivery_id,'url'=>$url));
		if($test===false){
			
			$params=array();
			$params['delivery_id']=$delivery_id;
			$params['url']=$url;
		 
			return parent::Add($params);	
		}else return $test['id'];
		
	}
	
	//найти число хитов по ссылке
	public function CountHits($link_id){
		$query = 'select sum(hits) from delivery_link_hits where link_id="'.$link_id.'" ';
		$set=new mysqlSet($query);
		$rs=$set->GetResult();
		$f=mysqli_fetch_array($rs);
		return (int)$f[0];
	}
}




//список хитов ссылки рассылки
class Delivery_LinkHitsGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_link_hits';
		$this->pagename='delivery_templates.php';		
		$this->subkeyname='link_id';	
		$this->vis_name='is_shown';		
		
		 
	}
	
}






//элемент хит ссылки рассылки
class Delivery_LinkHitItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_link_hits';
		$this->item=NULL;
		$this->pagename='delivery_ed_template.php';	
		$this->vis_name='is_shown';	 
	}
	
	
	//внести клик
	public function Put($link_id, $user_id, $ip){
		
		
		$test=$this->GetItemByFields(array('link_id'=>$link_id,'user_id'=>$user_id,'ip'=>$ip, 'pdate'=>time()));
		if($test===false){
			//найти delivery_id
			$_dl=new Delivery_LinkItem; $dl=$_dl->GetItemById($link_id);
			if($dl!==false){
			
				$params=array();
				$params['link_id']=$link_id;
				$params['user_id']=$user_id;
				$params['ip']=$ip;
				$params['delivery_id']=$dl['delivery_id'];
				$params['hits']=1;
				$params['pdate']=time();
				return parent::Add($params);	
			}else return 0;
		}else{
			
			$params=array();
			$params['hits']=(int)$test['hits']+1;
			parent::Edit($test['id'], $params);
			return $test['id'];
		}
		
	}
	
	 
}

?>