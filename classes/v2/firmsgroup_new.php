<?
 

// список всех фирм
class FirmsGroupNew extends FirmsGroup {
	
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='firms';
		$this->lang_tablename='firms_lang';
		$this->pagename='viewfirms.php';		
		$this->subkeyname='mid';	
		
		$this->mid_name='firmid';
		$this->lang_id_name='lang_id';	
		
		
		$this->all_menu_template='firms/items.html';
		$this->menuitem_template='tpl/itemsrow.html';
		$this->menuitem_template_blocked='tpl/itemsrow_blocked.html';
		$this->razbivka_template='tpl/to_page.html';
		
		$this->name_multilang='tpl/firms/subitem_name.html';
		
		$this->name_vis_check='tpl/firms/subitem_lang_vis_check.html';
	}
	
	
	
	//список итемов
	public function GetItems($mode=0,$from=0,$to_page=10, $template){
		//список позиций
		$txt='';
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_firm.php');
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
		
		$alls=Array(); $mi=new FirmItem();
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
				$params[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'descr'=>stripslashes($mmi['info']) );
			}
			
			$alls[]=Array('id'=>$f['id'], 'pic'=>stripslashes($f['photo_small']), 'nameitems'=>$names, 'valitems'=>$params);
		}
		
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		return $txt;
	}
	
	
	 
}
?>