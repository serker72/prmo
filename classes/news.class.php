<?
require_once('abstractitem.php');
require_once('abstractgroup.php');
require_once('user_s_group.php');
require_once('messageitem.php');

require 'vendor/autoload.php';

use PicoFeed\Reader\Reader;


//сбор и показ новостей из подписок


//итем канал
class News_StreamItem extends AbstractItem{
	 
	protected function init(){
		$this->tablename='news_stream';
		$this->item=NULL;
		$this->pagename='ed_doc_out.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}	
	
	//добавить
	public function Add($params){
		$code=parent::Add($params);	
		
		//сообщение всем имеющим права 1118 - добавлены новости отрасли.
		/*$_ug=new UsersSGroup;
		$_mi=new MessageItem;
		
		$topic="ƒобавлен источник новостей отрасли";
		
		
		$users=$_ug->GetUserIdsByRightArr('w',1118);
		foreach($users as $k=>$user){
			$txt='<div>';
			$txt.='<em>ƒанное сообщение сгенерировано автоматически.</em>';
			$txt.=' </div>';
			
			
			$txt.='<div>&nbsp;</div>';
			
			$txt.='<div>';
			$txt.='”важаемый(а€) '.$user['name_s'].'!';
			$txt.='</div>';
			$txt.='<div>&nbsp;</div>';
			
			
			$txt.='<div>';
			$txt.='<strong>ƒобавлен следующий источник новостей отрасли:</strong>';
			$txt.='</div><ul>';
			
			$txt.='<li> '.$params['name'].':  '.$params['site_url'].'</li>';
	
			$txt.='</ul>';
			
			$txt.='<div>&nbsp;</div>';
			$txt.='<div> ќзнакомитьс€ с текущими новост€ми ¬ы можете на странице "Ѕыстрые действи€".</div>';
			
			
			$txt.='<div>&nbsp;</div>';
		
			$txt.='<div>';
			$txt.='C уважением, программа "'.SITETITLE.'".';
			$txt.='</div>';
			
			$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
		}*/
		
		return $code;
	} 
	
	//удалить
	public function Del($id){
		
		$query = 'delete from news_item where stream_id='.$id.';';
		$it=new nonSet($query);
		
		AbstractItem::Del($id);
	}
	
	//нахождение каналов
	public function FindStreams($url){
		$feeds=array();
		
		try {

			$reader = new Reader;
			$resource = $reader->download($url);
		
			$feeds = $reader->find(
				$resource->getUrl(),
				$resource->getContent()
			);
		
			//print_r($feeds);
		}
		catch (PicoFeedException $e) {
			// Do something...
		}

		
		
		return $feeds;
	}
	
}



/****************************************************************************************************/
//группа каналов
class  News_StreamGroup extends AbstractGroup{
	 
	
	protected $_auth_result;
	protected $colors;
 
	protected function init(){
		$this->tablename='news_stream';
		$this->pagename='news.php';		
		$this->subkeyname='kind_id';	
		$this->vis_name='is_shown';		
		
		
		$this->_auth_result=NULL;
		
		$this->colors=array(
			//'#62a2d4',
			'#398fcd',
			'#105fa6',
			'#143d69',
			'#e9510f'
		);
		
	}
	
	
	
	
	//список подписок
    public function ShowStreamArr( 
		 
		DBDecorator $dec, //1
		$can_create=false, //2
		$can_edit=false,  //3
	 
		$has_header=true,  //6
		$is_ajax=false, //7
		$can_delete=true, //8
		 
		 
	 	$prefix='' //9
	 	
		
		){
		 
	 
		 
		 
		 //перестройка списка новостей при необходимости
		// $this->ReduildNews();
		   
		
		$sql='select distinct p.*,
	 
		cr.name_s as cr_name, cr.login as cr_login, cr.is_active as cr_is_active,
		dl.name_s as dl_name, dl.login as dl_login, dl.is_active as dl_is_active  
		 
		 
		 
					 
				from '.$this->tablename.' as p
				 
				left join user as cr on cr.id=p.created_id
				left join user as dl on dl.id=p.del_id
			 	
			 
				 	 
				 ';
				
		 
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		 	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	 
	 
		
		$alls=array();
		 
		
	 
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
			 
			
			$f['pdate']=date('d.m.Y H:i:s', $f['pdate']);
			
			if($f['del_pdate']!=0) $f['del_pdate']=date('d.m.Y H:i:s', $f['del_pdate']);
			 
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон пол€ми
	
		$current_supplier='';
		$user_confirm_id='';
		
		  
	 
  
	 	
		
	 
		
		return $alls;
	}
	
	
	
	//список новостей (гл. стр.)
    public function ShowNewsArr( 
		 
		DBDecorator $dec, //1
	 
		$has_header=true,  //6
		$is_ajax=false, //7
 
		 
		 
	 	$prefix='' //9
	 
		
		){
		 
		 
		 
		 //перестройка списка новостей при необходимости
		//$this->ReduildNews();
		   
		
		$sql='select distinct n.*
	 
	 
		 
					 
				from news_item as n
				 
				inner join news_stream as p on p.id=n.stream_id
			 	
			 
			where p.is_active=1	 	 
				 ';
				
		 
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
		 	
		}
		
		
		//$sql.=' order by p.id ';
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		
		$sql.=' limit 3 ';
		
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	 
	 
		
		$alls=array();
		 
		
	 
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
		 
			$dt= DateTime::createFromFormat('Y-m-d H:i:s', $f['pdate']);
			
			$f['pdate']= $dt->format( 'd.m.Y H:i:s');
		 
		 	
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон пол€ми
	
		 
	 	
		
	 
		
		return $alls;
	}
	
	
	
	//список новостей (реестровый)
    public function ShowPos( 
		$template='', //0
		DBDecorator $dec, //1
	 	$from=0, //2
		$to_page=ITEMS_PER_PAGE, //3
		$has_header=true,  //4
		$is_ajax=false, //5
 		$can_edit=false, //6
		 
		 
	 	$prefix='' //7
	 
		
		){
		 
		  
		 if($is_ajax) $sm=new SmartyAj;
		 else $sm=new SmartyAdm;
		 
		 $sm->assign('has_header', $has_header);
		 $sm->assign('can_edit', $can_edit);
		 
		 $sql_channels='select * from news_stream 	where is_active=1	 ';
		 	$set=new mysqlSet($sql_channels);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		$colors_arr=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			if(isset($this->colors[$i])) $colors_arr[$f['id']] =$this->colors[$i]; else $colors_arr[$f['id']] =$this->colors[0];
		}
		 
		 
		
		$sql='select distinct n.* 
	 
	 
		 
					 
				from news_item as n
				 
				inner join news_stream as p on p.id=n.stream_id
				 
			 
			where p.is_active=1	 	 
				 ';
				
		 $sql_count='select count(distinct n.id)
					 
				from news_item as n
				 
				inner join news_stream as p on p.id=n.stream_id
				 
			 
			where p.is_active=1	 	 
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
		
		 
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		
		$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=eregi_replace('&sortmode'.$prefix.'','&sortmode',$link);
		$link=eregi_replace('action'.$prefix,'action',$link);
		$link=eregi_replace('&id'.$prefix,'&id',$link);
		
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$link);
		$navig->SetFirstParamName('from'.$prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
	 
	 
		
		$alls=array();
		 
		$_nv=new News_DataViewItem;
	 
		//var_dump($colors_arr);		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//var_dump($colors_arr[$f['id']]);
		 
		 	$f['color']=$colors_arr[$f['stream_id']];
			
			$dt= DateTime::createFromFormat('Y-m-d H:i:s', $f['pdate']);
			
			$f['pdate']= $dt->format( 'd.m.Y H:i:s');
		    
			$nv=$_nv->GetItemByFields(array('news_id'=>$f['id'], 'user_id'=>$this->_auth_result['id']));
			$f['is_new']=($nv===false);
			
			
		 	//if($f['is_new']) $this->ToggleRead($f['id'], $this->_auth_result['id']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон пол€ми
	 
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
		 
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('prefix',$prefix);
		 
		 
	 	
			//ссылка дл€ кнопок сортировки
			$link=$dec->GenFltUri('&', $prefix);
		//echo $prefix;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$prefix.'=[[:digit:]]+','',$link);
	 
		$sm->assign('link',$link);
		
//		echo $link;
		
		
		return $sm->fetch($template);
	 
		 
	}
	
	
	
	//перестройка списка новостей при необходимости
	public function ReduildNews(){
			
		$sql='select distinct p.* 
		 
		  
				from '.$this->tablename.' as p
				 
	  		where p.is_active=1
				 ';
				
		//echo 'zzzzzzzzzzzzzzzzzzzz';
		//echo $sql;
		
		$set=new mysqlSet($sql );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	 
	 
		$_item=new News_StreamItem;
		$_news=new News_NewsItem;
		 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			try {

				// Fetch from your database the previous values of the Etag and LastModified headers
				$etag = $f['etag'];
				$last_modified = $f['last_modified']; 
			
				$reader = new Reader;
			
				// Provide those values to the download method
				$resource = $reader->download($f['url'], $last_modified, $etag);
				
				//echo 'zzzzzzzzzzzzzzzzzzzzzz2';
			
				// Return true if the remote content has changed
				if ($resource->isModified()) {
			
					$parser = $reader->getParser(
						$resource->getUrl(),
						$resource->getContent(),
						$resource->getEncoding()
					);
			
					$feed = $parser->execute();
			
					// Save your feed in your database
					// ...
					//подкачать ’ новостей, записать их в Ѕƒ
					//new NonSet('delete from news_item where stream_id="'.$f['id'].'"');
					
					//кодировка
					//$encoding=$resource->getEncoding();
					$encoding=$f['charset'];
					
					$items=$feed->getItems(); 
					foreach($items as $k=>$item){
					   //if($k>=($f['num_news'])) break;
					   
						/*echo iconv($encoding, 'windows-1251', $item->getTitle());
						
						 echo $item->getDate()->format ( 'd.m.Y H:i:s' );
					   
					   echo iconv($encoding, 'windows-1251', $item->getContent());
					   
						echo iconv($encoding, 'windows-1251', $item->getUrl());
					   */
					   $params=array();
					   $params['stream_id']=$f['id'];
					   $params['title']=SecStr(iconv($encoding, 'windows-1251', $item->getTitle()));
					   $params['content']=SecStr(strip_tags(iconv($encoding, 'windows-1251', $item->getContent())));
					   $params['pdate']=$item->getDate()->format ( 'Y-m-d H:i:s' );
					   $params['url']=SecStr(iconv($encoding, 'windows-1251', $item->getUrl()));
					 	
						
						//провер€ть, есть ли уже така€ новость с таким заголовком, датой, каналом
						$test_params=array();
						$test_params['stream_id']=$params['stream_id'];
						$test_params['title']=$params['title'];
						$test_params['pdate']=$params['pdate'];
						$test_new=$_news->GetItemByFields($test_params);	
						
						if($test_new===false) $_news->Add($params);	
				   }
					
					
			
					// Store the Etag and the LastModified headers in your database for the next requests
					$etag = $resource->getEtag();
					$last_modified = $resource->getLastModified();
					
					// ...
					$_item->Edit($f['id'], array('etag'=>$etag, 'last_modified'=>$last_modified));
				}
				else {
			
					echo 'Not modified, nothing to do!';
				}
			}
			catch (PicoFeedException $e) {
				// Do something...
				echo 'zzzzzzzzzz';
			}
		}
	}
	
	//рассылка списка новостей
	public function SendNews(){
		
		
		
		
		
		$sql='select distinct n.*
	 
				from news_item as n
				inner join news_stream as p on p.id=n.stream_id
			 
			where p.is_active=1	 	 
			and (n.pdate between "'.date('Y-m-d H:i:s', (time()-3*26*60*60)).'" and  "'.date('Y-m-d H:i:s').'")
			order by n.pdate desc
				 ';
		//echo $sql;
				 
		
		$set=new mysqlSet($sql );
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	 
	  
		$news=array(); 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$dt= DateTime::createFromFormat('Y-m-d H:i:s', $f['pdate']);
			
			$f['pdate']= $dt->format( 'd.m.Y H:i:s');
			
			$news[]=$f;
		}
		
		//сообщение всем имеющим права 1118 - добавлены новости отрасли.
		$_ug=new UsersSGroup;
		$_mi=new MessageItem;
		
		$topic="Ќовости отрасли за период c ".date('d.m.Y H:i:s', (time()-3*26*60*60))." по ".date('d.m.Y H:i:s');
		
		if(count($news)>0){
			$users=$_ug->GetUsersByRightArr('w',1118);
			foreach($users as $k=>$user){
				$txt='<div>';
				$txt.='<em>ƒанное сообщение сгенерировано автоматически.</em>';
				$txt.=' </div>';
				
				
				$txt.='<div>&nbsp;</div>';
				
				$txt.='<div>';
				$txt.='”важаемый(а€) '.$user['name_s'].'!';
				$txt.='</div>';
				$txt.='<div>&nbsp;</div>';
				
				
				$txt.='<div>';
				$txt.='<strong>«а период c '.date('d.m.Y H:i:s', (time()-3*26*60*60))." по ".date('d.m.Y H:i:s').' поступили следующие новости отрасли:</strong>';
				$txt.='</div><ul>';
				
				foreach($news as $nn) $txt.='<li>'.$nn['pdate'].' '.$nn['title'].'</li>';
		
				$txt.='</ul>';
				
				$txt.='<div>&nbsp;</div>';
				$txt.='<div> ќзнакомитьс€ с новост€ми отрасли ¬ы можете на странице <a href=\"news.php\" target=\"_blank\">"Ќовости отрасли"</a>.</div>';
				
				
				$txt.='<div>&nbsp;</div>';
			
				$txt.='<div>';
				$txt.='C уважением, программа "'.SITETITLE.'".';
				$txt.='</div>';
				
				$_mi->Send(0,0,array('from_id'=>-1, 'to_id'=>$user['id'],'pdate'=>time(), 'parent_id'=>0, 'txt'=>SecStr($txt), 'topic'=>$topic),false);	
			} 
		}
	}
	
	
	
	//пометить прочитанными указанные новости
	public function ToggleRead($id,  $user_id){
		$_vi=new News_DataViewItem;
		 
		$params=array('news_id'=>$id, 'user_id'=>$user_id);
		$vi=$_vi->GetItemByFields($params);
		
		if($vi===false) $_vi->Add($params);
		
		 
	}
}



/********************************************************************************************************/

//итем новость
class News_NewsItem extends AbstractItem{
	 
	protected function init(){
		$this->tablename='news_item';
		$this->item=NULL;
		$this->pagename='ed_doc_out.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='stream_id';	
	}	
	
	
	 
	
}


/*******************************************************************************************************/

//факт просмотра новости
class News_DataViewItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='news_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='sched_id';	
	}
	
	 
	
}

?>