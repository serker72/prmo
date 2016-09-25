<?
 

// список всех словарей
class AllDictsGroupNew extends AllDictsGroup {
	
	protected $name_multilang;
	protected $name_vis_check;
	
	//установка всех имен
	protected function init(){
		$this->tablename='dict';
		$this->pagename='viewdicts.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->all_menu_template='alldicts/items.html';
		$this->menuitem_template='tpl/itemsrow.html';
		$this->menuitem_template_blocked='tpl/itemsrow_blocked.html';
		$this->razbivka_template='tpl/to_page.html';
		
		$this->name_multilang='tpl/alldicts/subitem_name.html';
		
		$this->name_vis_check='subitem_lang_vis_check.html';
	}
	
	
	
	//список итемов
	public function GetItems($mode=0,$from=0,$to_page=10, $template=''){
		//список позиций
		$txt='';
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_dict.php');
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
		
		$all_=Array(); $mi=new AllDictItem();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			//строим имя
			$names_=Array();
			foreach($this->langs as $lk=>$g){
				
				$mmi=$mi->GetItemById($f['id'],$g['id']);
				if($mmi!=false){
					$is_exist=true;
					$is_shown=$mmi['is_shown'];
					
				}else {
					$is_exist=false;
					$is_shown=false;
				}
				$names_[]=Array( 'is_exist'=>$is_exist, 'is_visible'=>$is_shown, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'lang_shown'=>$mmi['is_shown'], 'name'=>stripslashes($mmi['name']) );
			}
			
			//вид словаря
			$params='';
			$mi=new AllDictItem();
			$params.=$mi->GetBehaviorsOpt($f['kind_id']);
			
			$all_[]=Array('id'=>$f['id'], 'nameitems'=>$names_, 'itemdescr'=>$params);
		}
		$smarty->assign('items',$all_);
		$smarty->assign('pages',$pages);
		$txt.=$smarty->fetch($template); //Выводим нашу страничку
		
		return $txt;
	}
	
	 
}
?>