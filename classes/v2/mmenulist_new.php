<?
 



// абстрактная группа итемов c языковыми таблицами
class MmenuListNew extends MmenuList{
	
	//собственные шаблоны итема меню
	protected $flags_check_template;
	protected $flags_radio_template;	
	protected $name_multilang;
	protected $name_multilang_blocked;
	
	protected $non_tabs=Array(); //нетабличные разделы сайта
	
	//установка всех имен
	protected function init(){
		$this->tablename='allmenu';
		$this->lang_tablename='menu_lang';
		$this->pagename='razds.php';		
		$this->subkeyname='mid';	
		
		$this->mid_name='mid';
		$this->lang_id_name='lang_id';	
		
		
		$this->all_menu_template='tpl/hmenutable.html';
		$this->menuitem_template='tpl/hmenuitem.html';
		
		$this->flags_check_template='tpl/subitem/subitem_check.html';
		$this->flags_radio_template='subitem_radio.html';
		$this->name_multilang='tpl/subitem/subitem_name.html';
		$this->name_multilang_blocked='tpl/subitem/subitem_name_blocked.html';
		
		
		
	}
	
	

	   
		//список подразделов раздела smarty
	public function GetItemsById($parent_id=0, $mode=0,$from=0,$to_page=10, $template=''){
		//список позиций
		
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		$txt='';
		
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		
		$smarty->assign('filename','ed_razd.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('itemno',$parent_id);
		$smarty->assign('listpagename',$this->pagename);
		
			
		$query='select * from allmenu as t where t.parent_id="'.$parent_id.'" order by t.ord desc, t.id';
		$sql_count='select count(*) from allmenu as t where t.parent_id="'.$parent_id.'"';
		$items=new mysqlSet($query,$to_page,$from,$sql_count);
		
		
		
		$rs=$items->GetResult();
		
		$rc=$items->GetResultNumRows();
		$totalcount=$items->GetResultNumRowsUnf();
		$strs='';
		
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page.'&id='.$parent_id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		
		$pages= $navig->GetNavigator();
		
		$alls=array(); $mi=new MmenuItem();
		
		$pg1=new PriceGroup(); $pg2=new NewsGroup(); $pg3=new LinksGroup(); $pg4=new PapersGroup(); $pg5=new PhotosGroup();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			
			//URL
		 	$path=$mi->ConstructPath($f['id'],LANG_CODE,0,'/');
			$url=$path;
			 
			$names_=array();
			//строим имя раздела
			foreach($this->langs as $lk=>$g){
				
				if(NUM_LEVELS!='-'){
				 
					$mi->RetrievePath($f['id'], $flaglost, $vloj, LANG_CODE, 0);
					//echo $vloj+1;
					if(NUM_LEVELS<=($vloj+1)){
						$is_vloj=0; 
					}else $is_vloj=1; 
				}else{
					$is_vloj=1; 
				}
				
			 
				$mmi=$mi->GetItemById($f['id'],$g['id']);
				
				if($mmi!=false){
					$is_exist=true;
				}else {
					$is_exist=false;
				}
				
				 
				 
				$names_[]=array(
					'is_vloj'=>$is_vloj, 
					'is_exist'=>$is_exist, 
					'lang_name'=>strip_tags($g['lang_name']), 
					'lang_flag'=>stripslashes($g['lang_flag']), 
					'lang_id'=>$g['id'], 
					'lang_shown'=>$mmi['is_shown'], 
					'name'=>stripslashes($mmi['name']), 
					'sub_count'=>$this->CountChildsById($mmi['id'],'parent_id') );
			}
			
		 	
			
			$alls[]=array(
				'id'=>$f['id'],
				'url'=>$url, 
				'nameitems'=>$names_, 
				'data'=>$f,
				'count_of_goods'=>$pg1->CalcItemsById($f['id']),
				'count_of_news'=>$pg2->CalcItemsById($f['id']),
				'count_of_links'=>$pg3->CalcItemsById($f['id']),
				'count_of_papers'=>$pg4->CalcItemsById($f['id']),
				'count_of_photos'=>$pg5->CalcItemsById($f['id'])
				
				//'itemdescr'=>$this->GetRazdParams($f)
			);
		}
		
		$smarty->assign('HAS_PRICE', HAS_PRICE);
		$smarty->assign('HAS_BASKET', HAS_BASKET);
		$smarty->assign('HAS_NEWS', HAS_NEWS);
		$smarty->assign('HAS_LINKS', HAS_LINKS);
		$smarty->assign('HAS_PAPERS', HAS_PAPERS);
		$smarty->assign('HAS_GALLERY', HAS_GALLERY);
		$smarty->assign('HAS_FEEDBACK_FORMS', HAS_FEEDBACK_FORMS);
		
		
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		
		$txt=$smarty->fetch($template);
		return $txt;
	}
	
	
	 
	
}
?>