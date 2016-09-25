<?
 


// виды цен
class PricesGroupNew extends PricesGroup {
		
	//установка всех имен
	protected function init(){
		$this->tablename='price';
		$this->lang_tablename='price__lang';
		$this->pagename='viewprices.php';		
		$this->subkeyname='mid';	
		
		$this->mid_name='value_id';
		$this->lang_id_name='lang_id';	
		
		
		$this->all_menu_template='prices/items.html';
		$this->menuitem_template='tpl/prices/itemsrow.html';
		$this->menuitem_template_blocked='tpl/prices/itemsrow.html';
		$this->razbivka_template='tpl/prices/to_page.html';
		
		$this->name_multilang='tpl/prices/subitem_name.html';
		
		$this->name_vis_check='tpl/prices/subitem_lang_vis_check.html';
	}
	
	
	
	//список итемов
	public function GetItems($from=0,$to_page=ITEMS_PER_PAGE, $template){
		//список позиций
		$txt=''; $lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_pr.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('listpagename',$this->pagename);
		
		$params=Array(); $ord=Array(); $ord[]=' ord desc '; $ord[]=' id ';
		$query=$this->GenerateSQL($params,NULL,$ord,$query_count);
		//$txt.=$query;
		$items=new mysqlSet($query,$to_page,$from,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		$strs='';
		
		$totalcount=$items->getResultNumRowsUnf();
		
		
		
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$all_=Array(); $mi=new PricesItem(); $condd=new CondItem();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			//название цены
			$names=Array();
			foreach($this->langs as $lk=>$g){
				
				$mmi=$mi->GetItemById($f['id'],$g['id']);
				
				if($mmi!=false){
					$is_exist=1;
				}else {
					$is_exist=0;
				}
				
				$names[]=Array('is_exist'=>$is_exist,  'lang_name'=>strip_tags($g['lang_name']),
				 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'name'=>stripslashes($mmi['name']), 'descr'=>stripslashes($mmi['descr']) );
			}
			
			
			//условие действия цены
			
			if($f['cond_id']==0){
				$has_cond=0;
			}else{
				$has_cond=1;
			}
			
			$all_[]=Array('id'=>$f['id'], 'is_formula'=>$f['use_formula'], 'is_base'=>$f['is_base'], 'formula'=>$f['formula'], 'ident'=>$f['formula_name'], 'has_cond'=>$has_cond, 'used'=>$condd->ShowCond($f['cond_id']), 'nameitems'=>$names);
		}
		
		$smarty->assign('items',$all_);
		$smarty->assign('pages',$pages);
		$txt.=$smarty->fetch($template); //Выводим нашу страничку
		
		return $txt;
	}
	
	
	 
}
?>