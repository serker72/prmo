<?
session_start();
header('Content-type: text/html; charset=windows-1251');

require_once('../classes/global.php');
require_once('../classes/authuser.php');
require_once('../classes/smarty/SmartyAdm.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/discr_table_objects.php');
require_once('../classes/actionlog.php');
 
require_once('../classes/news.class.php');
require '../classes/vendor/autoload.php';

use PicoFeed\Reader\Reader;


//обновление списка новостей!
if(isset($_GET['action'])&&($_GET['action']=="check_news")){
	
	$_ng=new News_StreamGroup;
	
	$_ng->ReduildNews();
	
	exit();	
}
//рассылка списка новостей
if(isset($_GET['action'])&&($_GET['action']=="send_news")){
	
	$_ng=new News_StreamGroup;
	
	$_ng->SendNews();
	
	exit();	
}


  

$au=new AuthUser();
$result=$au->Auth();
$log=new ActionLog;

if($result===NULL){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("403.php");
	die();		
}
  
  
  
$ret='';
if(isset($_POST['action'])&&($_POST['action']=="redraw_news")){
	
	$_ng=new News_StreamGroup;
	
	$dec=new DBDecorator();
	$dec->AddEntry(new SqlOrdEntry('n.pdate',SqlOrdEntry::DESC));
	
	$news=$_ng->ShowNewsArr($dec, true,true,'');
	

	
	$sm=new SmartyAj;
	
	$sm->assign('news', $news);
	$sm->assign('ps', $streams);
	
	 
	$sm->assign('show_index',true);
	$sm->assign('can_add_stream', $au->user_rights->CheckAccess('w',1119));
	
	$ret=$sm->fetch('index_news.html');
}
elseif(isset($_POST['action'])&&($_POST['action']=="redraw_streams")){
	
	$_ng=new News_StreamGroup;
	
 	$dec=new DBDecorator();
	$dec->AddEntry(new SqlOrdEntry('p.is_active',SqlOrdEntry::DESC));
	
	$streams=$_ng->ShowStreamArr($dec, $au->user_rights->CheckAccess('w',1119),$au->user_rights->CheckAccess('w',1119), true,true,$au->user_rights->CheckAccess('w',1119), '');
	
	$sm=new SmartyAj;
	
	$sm->assign('news', $news);
	$sm->assign('ps', $streams);
	
	 
	
	$sm->assign('can_add_stream', $au->user_rights->CheckAccess('w',1119));
	
	$ret=$sm->fetch('index_news_streams.html');
}

elseif(isset($_POST['action'])&&($_POST['action']=="delete_stream")){
	$_ns=new News_StreamItem;
	
	if($au->user_rights->CheckAccess('w',1119)){
		$id=abs((int)$_POST['id']);
		
		$ns=$_ns->getitembyid($id);
			
		//$_ns->Del($id);
		
		$params=array();
		$params['is_active']=0;
		$params['del_id']=$result['id'];
		$params['del_pdate']=time();
		$_ns->Edit($id, $params);
		
		$log->PutEntry($result['id'],'удалил сайт-источник новостей', NULL,1119, NULL,SecStr($ns['name']),$id);
	}
		
}
elseif(isset($_POST['action'])&&($_POST['action']=="edit_stream")){
	$_ns=new News_StreamItem;
	
	if($au->user_rights->CheckAccess('w',1119)){
		$id=abs((int)$_POST['id']);
		
		$ns=$_ns->getitembyid($id);
		$params=array();
		$params['num_news']=abs((int)$_POST['num_news']);
			
		$_ns->Edit($id, $params);
		$log->PutEntry($result['id'],'редактировал сайт-источник новостей', NULL,1119, NULL,SecStr($ns['name'].' установлено число новостей для отображения '.$params['num_news']),$id);
	}
		
}
/*elseif(isset($_POST['action'])&&($_POST['action']=="check_streams")){
	$url=$_POST['url'];
	$_ns=new News_StreamItem;
	
	$ret=count($_ns->FindStreams($url));	
	
}*/

elseif(isset($_POST['action'])&&($_POST['action']=="add_stream")){
	$_ns=new News_StreamItem;
	
	if($au->user_rights->CheckAccess('w',1119)){
		$urls=$_POST['urls'];
		$charsets=$_POST['charsets'];
		
		foreach($urls as $k=>$url){
		
			
			$params=array();
			$params['name']=SecStr(iconv('utf-8', 'windows-1251', $_POST['name']));
			$params['site_url']=SecStr(iconv('utf-8', 'windows-1251', $_POST['site_url']));
			$params['url']=SecStr(iconv('utf-8', 'windows-1251', $url));
			$params['charset']=SecStr(iconv('utf-8', 'windows-1251', $charsets[$k]));
			
			
			//$params['num_news']=abs((int) $_POST['num_news']);
			
			$params['created_id']=$result['id'];
			$params['pdate']=time();
			
			$code=$_ns->Add($params);
			$log->PutEntry($result['id'],'добавил сайт-источник новостей', NULL,1119, NULL,$params['name'].', адрес '.$params['site_url'],$code);
		}
	}
}
elseif(isset($_POST['action'])&&($_POST['action']=="find_streams")){
	
	$url=$_POST['url'];
	//найти по УРЛ ленты новостей.
	//для каждой вывести ее адрес, кодировку, заголовок, контент
	$data=array();
	
	try {

		$reader = new Reader;
		$resource = $reader->download($url);
	
		$feeds = $reader->find(
			$resource->getUrl(),
			$resource->getContent()
		);
	
		//print_r($feeds);
		
		foreach($feeds as $k=> $feed){
			$all=array();
			$all['id']=md5($k.$feed);
			$all['url']=$feed;
			
			
			try {
					
					//найдем все свойства ленты
					$resource1 = $reader->download($feed); //, $last_modified, $etag);
				
				//echo 'zzzzzzzzzzzzzzzzzzzzzz2';
			 
					$parser = $reader->getParser(
						$resource1->getUrl(),
						$resource1->getContent(),
						$resource1->getEncoding()
					);
			
					$lenta = $parser->execute();
					
					
					//кодировка
					$encoding=$resource1->getEncoding();
					if(strlen($encoding)==0) $encoding='utf-8';
					$all['charset']=$encoding;
					//var_dump( $encoding);
					
					$items=$lenta->getItems(); 
					foreach($items as $k1=>$item){
						 if($k1>0) break;
						 
					   
					   
						 $all['title']=(iconv($encoding, 'windows-1251', $item->getTitle()));
						 $all['content']=(iconv($encoding, 'windows-1251', $item->getContent()));
				   
						   
					 }
					
					$data[]	=$all;
					}
			catch (Exception $e) {
				// Do something...
			}
			
		}
	}
	catch (Exception $e) {
		// Do something...
	}
	
	
	$sm=new SmartyAj;
	$sm->assign('streams', $data);
	
	$ret=$sm->fetch('index_news_avail_streams.html');

}

elseif(isset($_POST['action'])&&($_POST['action']=="redraw_preview")){
	//обновить превью ленты по указанной кодировке
	$url=$_POST['url'];
	$charset=$_POST['charset'];	
	
	
	try {
		
		$reader = new Reader;
		$resource1 = $reader->download($url);
		
		$parser = $reader->getParser(
			$resource1->getUrl(),
			$resource1->getContent(),
			$resource1->getEncoding()
		);

		$lenta = $parser->execute();
		
		$items=$lenta->getItems(); 
		foreach($items as $k1=>$item){
			 if($k1>0) break;
			 $ret='<strong>'.(iconv($charset, 'windows-1251', $item->getTitle())).'</strong><br>';
			$ret.=(iconv($charset, 'windows-1251', $item->getContent()));
		}
			
		
	}catch (Exception $e) {
		// Do something...
	}		
}

elseif(isset($_POST['action'])&&($_POST['action']=="add_preview_stream")){
	
	$url=$_POST['url'];
	//найти по УРЛ ленты новостей.
	//для каждой вывести ее адрес, кодировку, заголовок, контент
	$data=array();
	
	try {

		$reader = new Reader;
		 
			$all=array();
			$all['id']=md5($k.time().$url);
			$all['url']=$url;
			
			//найдем все свойства ленты
			try {
				$resource1 = $reader->download($url); //, $last_modified, $etag);
			
			//echo 'zzzzzzzzzzzzzzzzzzzzzz2';
		 
				$parser = $reader->getParser(
					$resource1->getUrl(),
					$resource1->getContent(),
					$resource1->getEncoding()
				);
		
				$lenta = $parser->execute();
				
				
				//кодировка
				$encoding=$resource1->getEncoding();
				if(strlen($encoding)==0) $encoding='utf-8';
				$all['charset']=$encoding;
				//var_dump( $encoding);
				
				$items=$lenta->getItems(); 
				foreach($items as $k1=>$item){
					 if($k1>0) break;
					 
				   
				   
					 $all['title']=(iconv($encoding, 'windows-1251', $item->getTitle()));
					 $all['content']=(iconv($encoding, 'windows-1251', $item->getContent()));
			   
					   
				 }
				
				$data[]	=$all;
				
			}
			catch (Exception  $e) {
				// Do something...
				$tt='';
			}
	
		 
	}
	catch (Exception  $e) {
		// Do something...
		$tt='';
	}
	
	
	$sm=new SmartyAj;
	$sm->assign('streams', $data);
	
	$ret=$sm->fetch('index_news_avail_streams.html');

}

//if(DO_RECODE) $ret=iconv('windows-1251','utf-8',$ret);
echo $ret;	
?>