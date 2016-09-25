<?
 
// список всех баннеров
class BansGroupNew extends BansGroup {
	
	protected $name_multilang;
	protected $name_vis_check;
	
	//установка всех имен
	protected function init(){
		$this->tablename='banners';
		$this->lang_tablename='banners_lang';
		$this->pagename='viewads.php';		
		$this->subkeyname='mid';	
		
		$this->mid_name='ban_id';
		$this->lang_id_name='lang_id';	
		
		
		$this->all_menu_template='banners/items.html';
		$this->menuitem_template='tpl/itemsrow.html';
		$this->menuitem_template_blocked='tpl/itemsrow_blocked.html';
		$this->razbivka_template='tpl/to_page.html';
		
		$this->name_multilang='tpl/firms/subitem_name.html';
		
		$this->name_vis_check='tpl/firms/subitem_lang_vis_check.html';
	}
	
	
	
	//список итемов
	public function GetItems($mode=0,$from=0,$to_page=10, $template=''){
		//список позиций
		$txt='';
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_ban.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('listpagename',$this->pagename);
		
		$params=Array();
		$paramsord=Array();
		$paramsord[]=' ord desc ';
		$query=$this->GenerateSQL($params,NULL, $paramsord, $query_count);
		
		//echo $query;
		
		$items=new mysqlSet($query,$to_page,$from,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		$strs='';
		
		$totalcount=$items->getResultNumRowsUnf();
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=Array();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			//параметры имени
			//параметры
			$names=Array(); $params=Array();
			foreach($this->langs as $lk=>$g){
				$mi=new BanItem();
				$mmi=$mi->GetItemById($f['id'],$g['id']);
				
				if($mmi!=false){
					$is_exist=true;
				}else {
					$is_exist=false;
				}
				
				
				$pt=@pathinfo(stripslashes($mmi['photo_small']));
					
				$names[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'name'=>stripslashes($mmi['small_txt'])  );
				$params[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'pict'=>stripslashes($mmi['photo_small']),
				'is_flash'=>$mmi['is_flash'],
				'flash_width'=>$mmi['flash_width'],
				'flash_height'=>$mmi['flash_height'],
				'flash_src'=>'/'.$pt['dirname'].'/'.$pt['filename']
				
				
				 );
			}
			
			$alls[]=Array('id'=>$f['id'], 'nameitems'=>$names, 'url'=>stripslashes($f['url']), 'kind'=>$f['kind'], 'valitems'=>$params);
		}
		
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		return $txt;
	}
	
}
?>