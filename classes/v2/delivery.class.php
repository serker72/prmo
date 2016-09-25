<?
//require_once('db_decorator.php');

require_once('delivery_lists.class.php');
require_once('delivery_templates.class.php');
require_once('delivery_links.class.php');
//классы, связанные с рассылками

//список рассылок
class Delivery_Group extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery';
		$this->pagename='delivery_campaigns.php';		
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
			l.name as list_name,
			s.name as segment_name,
			st.name as status_name
				from '.$this->tablename.' as p
				left join delivery_list as l on l.id=p.list_id
				left join delivery_segment as s on s.id=p.segment_id
				left join delivery_status as st on st.id=p.status_id 
					
					';
		$sql_count='select count(p.id)
				from '.$this->tablename.' as p
				left join delivery_list as l on l.id=p.list_id
				left join delivery_segment as s on s.id=p.segment_id
				left join delivery_status as st on st.id=p.status_id 
			 
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
		
		$_li=new  Delivery_Item;
		
		$_links=new Delivery_LinksGroup;
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			$f['pdate_change']=date('d.m.Y H:i:s', $f['pdate_change']);
			if($f['pdate_status_change']!=0) $f['pdate_status_change']=date('d.m.Y H:i:s', $f['pdate_status_change']); else $f['pdate_status_change']='-';
			
			//проверить число подписчиков и число просмотревших
			$sql1='
			select count(*) from delivery_subscriber as ds
			inner join delivery_user as u on u.id=ds.user_id
			where 
				u.is_subscribed=1
				and ds.delivery_id="'.$f['id'].'"
			';
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$f['total']=(int)$g[0];
			
			
			$sql1='
			select count(*) from delivery_subscriber as ds
			inner join delivery_user as u on u.id=ds.user_id
			where 
				u.is_subscribed=1
				and ds.is_viewed=1
				and ds.delivery_id="'.$f['id'].'"
			';
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$f['viewed']=(int)$g[0];
			
			$f['ratio']=0;
			if($f['total']>0) $f['ratio']=round(100*$f['viewed']/$f['total']);
			
			//всего кликов
			if($f['has_clicks_tracking']){
				$f['clicked']=$_links->CountHits($f['id']);
			}
			
			
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
		
		
		//подгрузить список списков
		$sql='select * from delivery_list  order by name asc';
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
		
		//подгрузить список статусов
		$sql='select * from delivery_status   order by name asc';
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$lists=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$lists[]=$f;
		}
		$sm->assign('statuses', $lists);	
	 
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$prefix);
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);	
	}
	
}






//элемент рассылка
class Delivery_Item extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery';
		$this->item=NULL;
		$this->pagename='delivery_ed_campaign.php';	
		$this->vis_name='is_shown';	 
	}
	
	
	public function Add($params){
		if(!isset($params['pdate'])) $params['pdate']=time();
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		return parent::Add($params);	
	}
	
	public function Edit($id, $params){
		$item=$this->GetItemById($id);
		$_dls=new Delivery_LinksGroup;
		
		if(!isset($params['pdate_change'])) $params['pdate_change']=time();
		
		
		//контролировать смену статуса
		//если 1 - 4, то создать подписчиков по выбранным спискам, сбросить данные о кликах
		if(isset($params['status_id'])&&($params['status_id']==4)&&($item['status_id']==1)){
			new NonSet('delete from delivery_subscriber where delivery_id="'.$id.'"');
			
			if(($item['segment_id']!=0)||($item['list_id']!=0))	{
				if($item['segment_id']!=0){
					$sql1='select id from delivery_user where is_subscribed=1 and id in(select user_id from delivery_user_segment where segment_id="'.$item['segment_id'].'")';
					
				}else{
					$sql1='select id from delivery_user where is_subscribed=1 and list_id="'.$item['list_id'].'"';
					
				}
				
				//echo $sql1;
				
				$_ds=new Delivery_SubscriberItem;
				
				$set1=new mysqlSet($sql1);
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				 
				for($i1=0; $i1<$rc1; $i1++){
					$f=mysqli_fetch_array($rs1);
					$_ds->Add(array('user_id'=>$f[0], 'delivery_id'=>$id));
				}
				
				$_dls->ClearStats($id);
				
			}
			//var_dump($item);
			//	die();	
		}
		
		//перевод в статус 1 - редактируется - сброс статистики просмотра
		if(isset($params['status_id'])&&($params['status_id']==1)&&($item['status_id']!=1)){
			
			//$_dls->ClearStats($id);
		}
		
		
		return parent::Edit($id, $params);	 
	}
	
	public function Del($id){
		
		 
		
		$query = 'delete from delivery_subscriber  where delivery_id='.$id.'';
		new nonSet($query);
		
		
		return parent::Del($id);	
	} 
	
	
	//список доступных списков для карты рассылки
	public function GetListsArr($current_id){
		$arr=array();
		$sql='select  p.*,
					 count(s.id) as s_q
				from delivery_list as p
					left join delivery_user as s on s.list_id=p.id
					group by p.id
					 order by name asc, pdate desc';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$sql1='select  p.*,
					 count(s.id) as s_q
				from delivery_segment as p
					left join delivery_user_segment as s on s.segment_id=p.id
					where list_id="'.$f['id'].'"
					group by p.id
					 order by name asc, pdate desc';
			
			//echo $sql1.'<br>';
			$set1=new mysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			$segments=array(); 
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1);
				foreach($g as $k=>$v) $g[$k]=stripslashes($v);
				
				$segments[]=$g;
			}
			$f['segments']=$segments;
			$arr[]=$f;
		}
			
		return $arr;
	}
	
	
	//список доступных шаблонов для карты рассылки
	public function GetTemplatesArr($current_id){
		$arr=array();
		$sql='select  p.* 
				from delivery_template as p
					 
					 order by name asc, pdate desc';
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
			 
			$arr[]=$f;
		}
			
		return $arr;
	}
}






//список получателей рассылки
class Delivery_SubscriberGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_subscriber';
		$this->pagename='delivery_campaigns.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		 
	}
	
	
	
}






//элемент получатель рассылки
class Delivery_SubscriberItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_subscriber';
		$this->item=NULL;
		$this->pagename='delivery_ed_campaign.php';	
		$this->vis_name='is_shown';	 
	}
	
	/*
	public function Add($params){
		if(!isset($params['pdate'])) $params['pdate']=time();
	 
		return parent::Add($params);	
	}
	*/
	
	 
	 
}










//список хитов ссылки рассылки
/*class Delivery_LinkHitsGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_link_hits';
		$this->pagename='delivery_templates.php';		
		$this->subkeyname='link_id';	
		$this->vis_name='is_shown';		
		
		 
	}
	
}

*/




//элемент хит ссылки рассылки
class Delivery_SubscriberHitItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_subscriber_hits';
		$this->item=NULL;
		$this->pagename='delivery_ed_template.php';	
		$this->vis_name='is_shown';	 
	}
	
	
	//внести клик
	public function Put($subscriber_id, $user_id, $ip){
		
		
		$test=$this->GetItemByFields(array('subscriber_id'=>$subscriber_id,'user_id'=>$user_id,'ip'=>$ip, 'pdate'=>time()));
		if($test===false){
			//найти delivery_id
			$_dl=new Delivery_SubscriberItem; $dl=$_dl->GetItemById($subscriber_id);
			if($dl!==false){
			
				$params=array();
				$params['subscriber_id']=$subscriber_id;
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




//список статусов рассылки
class Delivery_StatusGroup extends AbstractGroup{
	protected function init(){
		$this->tablename='delivery_status';
		$this->pagename='delivery_campaigns.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		 
	}
	
}






//элемент статус рассылки
class Delivery_StatusItem extends AbstractItem{
	//установка всех имен
	protected function init(){
		$this->tablename='delivery_status';
		$this->item=NULL;
		$this->pagename='delivery_ed_campaign.php';	
		$this->vis_name='is_shown';	 
	}
	
	
	
}


//обработка полей подстановки
class Delivery_Fields{
	//*|FNAME|* *|LNAME|*
	// *|UNSUB|*
	 
	
	protected $fieldnames;
	function __construct(){
		$this->fieldnames=array(
		'topic',
		'from_name',
		'from_email',
		'to_field',
		'html_content',
		'plain_text_content'
		);
	}
	
	public function ProcessFields($user_id=NULL, &$delivery){
		//найдем получателя (одного) 
		//если такого нет - то выполняем подстановку пустыми выражениями
		$_du=new Delivery_UserItem;
		$_dsu=new Delivery_UserSegmentItem;
		
		$_dls=new Delivery_LinkItem;
		
		$first_name='-имя получателя-'; $last_name='-фамилия получателя-'; $unsub=SITEURL.'/unsubscribe.php'; $user_email='<адрес получателя>';
		$our_user_id=NULL;
		
		if($user_id!==NULL){
			$our_user_id=$user_id;
			 
		}else{
			if($delivery['segment_id']!=0){
				//найдем первого пользователя в сегменте
				$dsu=$_dsu->GetItemByFields(array('segment_id'=>$delivery['segment_id']));
				if($dsu!==false){
					//$user=$_du->GetItemById($dsu['user_id']);
					$our_user_id=$dsu['user_id'];
					
				}
						
			}elseif($delivery['list_id']!=0){
				//найдем первого пользователя в списке
				$user=$_du->GetItemByFields(array('list_id'=>$delivery['list_id']));
				$our_user_id=$user['id'];
			}
		}
		
		if($our_user_id!==NULL){
			$user=$_du->GetItemById($our_user_id);
			$first_name=$user['i']; $last_name=$user['f'];
			$unsub.='?id='.$our_user_id.'&list_id='.$user['list_id'].'&delivery_id='.$delivery['id'];
			$user_email=$user['email'];
		}
			
		$delivery['user_email']=$user_email;
		
		//заменить все ссылки на абсолютные
		foreach($delivery as $k=>$text){
			$text=str_replace(' src="/', ' src="'.SITEURL.'/', $text);
			$text=str_replace(' href="/', ' href="'.SITEURL.'/', $text);
			
			$text=str_replace(' href=\"/', ' href=\"'.SITEURL.'/', $text);
			$text=str_replace(' src=\"/', ' src=\"'.SITEURL.'/', $text);
			
			$text=str_replace(' href=/', ' href='.SITEURL.'/', $text);
			$text=str_replace(' src=/', ' src='.SITEURL.'/', $text);
			
			$text=str_replace('url(/', 'url('.SITEURL.'/', $text);
			 
			
			$delivery[$k]=$text;	
		}
		
		
		//заменить все ссылки с http на редиректы...
		//внести в базу эти ссылки ???
		if($delivery['has_clicks_tracking']){
		 
			  $text=$delivery['html_content'];
			  preg_match_all('/href="([^"]+)"/', $text, $links);
			  
		/*	 echo '<pre>';  
			  print_r($links);
			  echo '</pre>';*/
			  
			   foreach($links[1] as $k=>$link){
				  if(preg_match('/^http/', $link)){
					 // echo "$link<br>";
					  
					  $link_id=$_dls->Put($delivery['id'], SecStr($link));
					  $instead=SITEURL.'/delivery_track.php?id='.$link_id.'&user_id='.$our_user_id;
					//  echo $instead."<br>";
					  
					  
					  //$text=str_replace('href="'.$link, 'href="'.$instead, $text); 
					  $text=eregi_replace('href="'.preg_quote($link).'"', 'href="'.$instead.'"', $text);
					   
				  }
			  }
			   
			  
			  $delivery['html_content']=$text;	
		   
			
		}
		
		
		
		
		foreach($delivery as $k=>$v){
			//if($k==	
			if(in_array($k, $this->fieldnames)){
				$v=str_replace('*|FNAME|*', $first_name, $v);	
				$v=str_replace('*|LNAME|*', $last_name, $v);	
				$v=str_replace('*|UNSUB|*', $unsub, $v);	
				
				$delivery[$k]=$v;
			}
			
		}
		
		$delivery['plain_text_content']=strip_tags($delivery['plain_text_content']);
		
			
	}
	
}

//комплексная проверка рассылки
class Delivery_Check{
	protected $delivery;
	
	function __construct($delivery){
		$this->delivery=$delivery;	
	}
	
	public function CheckStage($step, &$reason){
		$res=true;	
		$_reasons=array();
		
		$_du=new Delivery_UserItem;
		$_dsu=new Delivery_UserSegmentItem;
		
		$_ds=new Delivery_SegmentItem;
		$_dl=new Delivery_ListItem;
		$_dt=new Delivery_TemplateItem;
		
		//шаг 1 - д.б. хотя бы 1 подписчик
		if($step==1){
			if(($this->delivery['list_id']==0)&&($this->delivery['segment_id']==0)){
				$res=$res&&false;
				$_reasons[]="не выбран список и/или сегмент списка подписчиков";	
			}
			elseif($this->delivery['segment_id']!=0){
				$sql='select count(*) from delivery_user where is_subscribed=1 and id in(select user_id from delivery_user_segment where segment_id="'.$this->delivery['segment_id'].'")';
				//echo $sql;
				
				$set=new mysqlSet($sql);
				$rs=$set->GetResult();	
				$f=mysqli_fetch_array($rs);
				if((int)$f[0]==0){
					$ds=$_ds->GetItemById($this->delivery['segment_id']);
					$res=$res&&false;
					$_reasons[]="в выбранном сегменте <a href=\"delivery_list_users.php?id=".$this->delivery['list_id']."&segment=".$this->delivery['segment_id']."\" target=\"_blank\">\"".$ds['name']."\"</a> должен быть хотя бы один активный подписчик";		
				}else{
					$ds=$_ds->GetItemById($this->delivery['segment_id']);
					$ds1=$_dl->GetItemById($this->delivery['list_id']);
					$_reasons[]="выбран сегмент <a href=\"delivery_list_users.php?id=".$this->delivery['list_id']."&segment=".$this->delivery['segment_id']."\" target=\"_blank\">\"".$ds['name']."\"</a> списка <a href=\"delivery_list_users.php?id=".$this->delivery['list_id']."\" target=\"_blank\">\"".$ds1['name']."\"</a>, подписчиков: $f[0]";
				}
			}
			elseif(($this->delivery['list_id']!=0)&&($this->delivery['segment_id']==0)){
				$sql='select count(*) from delivery_user where is_subscribed=1 and list_id="'.$this->delivery['list_id'].'" ';
				$set=new mysqlSet($sql);
				$rs=$set->GetResult();	
				$f=mysqli_fetch_array($rs);
				if((int)$f[0]==0){
					$ds=$_dl->GetItemById($this->delivery['list_id']);
					$res=$res&&false;
					$_reasons[]="в выбранном списке <a href=\"delivery_list_users.php?id=".$this->delivery['list_id']."\" target=\"_blank\">\"".$ds['name']."\"</a> должен быть хотя бы один активный подписчик";		
				}else{
					$ds1=$_dl->GetItemById($this->delivery['list_id']);
					$_reasons[]="выбран весь список <a href=\"delivery_list_users.php?id=".$this->delivery['list_id']."\" target=\"_blank\">\"".$ds1['name']."\"</a>, подписчиков: $f[0]";
				}
			}else{
				//все ОК, показать в списке выбранный список или сегмент и число пользователей в нем
					
				
			}
		}
		//шаг 2: заполнение обязательных полей
		elseif($step==2){
			if(strlen($this->delivery['name'])==0){
				$res=$res&&false;
				$_reasons[]="не заполнено поле Название рассылки";	
			}
			
			if(strlen($this->delivery['from_name'])==0){
				$res=$res&&false;
				$_reasons[]="не заполнено поле От кого: имя";	
			}
			
			if(strlen($this->delivery['from_email'])==0){
				$res=$res&&false;
				$_reasons[]="не заполнено поле От кого: адрес";	
			}
			
			if(!preg_match("/^[a-zA-Z0-9_\-.]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-.]+$/",$this->delivery['from_email'])){
				$res=$res&&false;
				$_reasons[]="некорректный адрес в поле От кого: адрес";	
				
			}
			if($res){
				$_reasons[]="все необходимые поля заполнены корректно";
				$_reasons[]="<br>поле Название рассылки: ".$this->delivery['name'];	
				$_reasons[]="<br>поле От кого: имя: ".$this->delivery['from_name'];	
				$_reasons[]="<br>поле От кого: адрес: ".$this->delivery['from_email']; 
				
			}
		}
		//шаг 3: выбор существуюшего шаблона
		elseif($step==3){
			if($this->delivery['template_id']==0){
				$res=$res&&false;
				$_reasons[]="не выбран шаблон для сообщения";	
			}else{
				$test_t=$_dt->getitembyid($this->delivery['template_id']);
				if($test_t===false){
					$res=$res&&false;
					$_reasons[]="не выбран шаблон для сообщения";	
				}
			}
			if($res){
				$test_t=$_dt->getitembyid($this->delivery['template_id']);
				$_reasons[]="выбран шаблон \"$test_t[name]\""	;
			}
		}
		//шаг 4: заполнение текста
		elseif($step==4){
			if(strlen($this->delivery['html_content'])==0){
				$res=$res&&false;
				$_reasons[]="не заполнен текст сообщения";
			}
			if($res){
				 
				$_reasons[]="текст сообщения заполнен"	;
			}
		}
		
		
		$reason=implode('; ',$_reasons);
		
		return $res;
	}
	
}



/*
function DateFromdmY($string='01.01.2008'){
	return mktime(0,0,0,substr($string,3,2),substr($string,0,2),substr($string,6,4) );
}*/
?>