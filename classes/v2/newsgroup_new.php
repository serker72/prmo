<?
 
// группа новостей
class NewsGroupNew extends NewsGroup{
	protected $name_multilang;
	
	//установка всех имен
	protected function init(){
		$this->tablename='news_item';
				$this->lang_tablename='news_lang';
		$this->pagename='viewnews.php';		
		$this->subkeyname='mid';	
		
		$this->mid_name='news_id';
		$this->lang_id_name='lang_id';	
		$this->name_multilang='tpl/news/subitem_name.html';
	}
	
	
	
	//список итемов
	public function GetItemsById($id,$sortmode,$sortparams=NULL,$from=0,$to_page=ITEMS_PER_PAGE, $template=''){
		//список позиций
		$txt='';
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		
		if($sortmode==1){
			$sql='select * from 
			'.$this->tablename.'  where pdate="'.$sortparams['pdate'].'" and '.$this->subkeyname.'="'.$id.'" order by ord desc, id desc ';
			$sql_count='select count(*) from 
			'.$this->tablename.'  where pdate="'.$sortparams['pdate'].'"  and '.$this->subkeyname.'="'.$id.'"';
			
		}else{
			$sql='select * from 
			'.$this->tablename.' where '.$this->subkeyname.'="'.$id.'"  order by pdate desc, ord desc, id desc ';
			$sql_count='select count(*) from 
			'.$this->tablename.' where  '.$this->subkeyname.'="'.$id.'" ';
		}
		
		$set=new mysqlSet($sql,$to_page,$from,$sql_count);
		$rc=$set->GetResultNumRows();
		$totalcount=$set->GetResultNumRowsUnf();
		$rs=$set->GetResult();
		//echo $rc;
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_news.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('itemno',$id);
		$smarty->assign('listpagename',$this->pagename);
		$smarty->assign('sortmode',$sortmode);
		
		$srt_str='';
		if($sortparams===NULL){
			$smarty->assign('sortparamname','some');
			$smarty->assign('sortparamvalue','0');			
		}else {
			foreach($sortparams as $k=>$v){
				$smarty->assign('sortparamname',$k);
				$smarty->assign('sortparamvalue',$v);				
				$srt_str.="&$k=$v";
			}
		}
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page.'&sortmode='.$sortmode.$srt_str.'&id='.$id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=Array(); $mi=new NewsItem();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			//параметры имени
			//параметры
			$names=Array(); $params=Array();
			foreach($this->langs as $lk=>$g){
				
				$mmi=$mi->GetItemById($f['id'],$g['id']);
				
				if($mmi!=false){
					$is_exist=true;
				}else {
					$is_exist=false;
				}
				
				$names[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'name'=>stripslashes($mmi['name']) );
				$params[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'descr'=>stripslashes($mmi['small_txt']) );
			}
			
			$alls[]=Array('id'=>$f['id'], 'itemdate'=>DateFromYmd($f['pdate']),'nameitems'=>$names,'valitems'=>$params );
		}
		
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		return $txt;
	}
	
	
	
	 
	
}
?>