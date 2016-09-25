<?
 

// список товаров
class PriceGroupNew extends PriceGroup {
 
	
	//установка всех имен
	protected function init(){
		$this->tablename='price_item';
		
		$this->lang_tablename='price_lang';
		$this->pagename='viewpriceitems.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->mid_name='price_id';
		$this->lang_id_name='lang_id';	
		
		$this->all_menu_template='tpl/itemstable.html';
		$this->menuitem_template='tpl/itemsrow.html';
		$this->menuitem_template_blocked='tpl/itemsrow_blocked.html';
		$this->razbivka_template='tpl/to_page.html';
		
		$this->name_multilang='tpl/price/subitem_name.html';
	}
	
	
	
	//список итемов
	public function GetItemsById($id,$mode=0,$from=0,$to_page=GOODS_PER_PAGE, $template=''){
		//список позиций
		$txt='';
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		$fg=new FirmsGroup();
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		$smarty->assign('filename','ed_price.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('itemno',$id);
		$smarty->assign('listpagename',$this->pagename);
		$smarty->assign('manyfilename','add_goods.php');
		
		
		
		$params=Array();
		$params[$this->subkeyname]=$id;
		$paramsord=Array();
		$paramsord[]=' ord desc ';
		$query=$this->GenerateSQL($params,NULL, $paramsord, $query_count);
		
		$items=new mysqlSet($query,$to_page,$from,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		$strs='';
		
		$totalcount=$items->getResultNumRowsUnf();
		
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page.'&id='.$id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=Array(); $it1=new PriceItem(); $mi=new PriceItem(); $rg=new RekomGroup();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
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
			
			//реком товары
			
			$crg=$rg->CalcItemsById($f['id']);
			
			
			//цена
			
			
			$alls[]=Array('id'=>$f['id'], 'photopath'=>stripslashes($f['photo_small']), 'nameitems'=>$names, 'valitems'=>$params, 'firmopt'=>$fg->GetItemsOptByLang_id($f['firmid'],'name'),'rekom_count'=>$crg, 'priceplace'=>$it1->price_disp->GetGoodBasePrice($f['id']));
			
		}
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		
		return $txt;
	}
	
	
	
	 
	
}
?>