<?
 

// список ссылок
class LinksGroupNew extends LinksGroup {
	
 
	
	//установка всех имен
	protected function init(){
		$this->tablename='link_item';
		$this->pagename='viewlinks.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->all_menu_template='tpl/itemstable.html';
		$this->menuitem_template='tpl/itemsrow.html';
		$this->menuitem_template_blocked='tpl/itemsrow_blocked.html';
		$this->razbivka_template='tpl/to_page.html';
		
		$this->name_multilang='tpl/links/subitem_name.html';
	}
	
	
	
	//список итемов
	public function GetItemsById($id,$mode=0,$from=0,$to_page=ITEMS_PER_PAGE, $template=''){
		//список позиций
		$txt=''; $lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_link.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('itemno',$id);
		$smarty->assign('listpagename',$this->pagename);
		
		$params=Array();
		$params[$this->subkeyname]=$id;
		$paramsord=Array();
		$paramsord[]=' ord desc ';
		$paramsord[]=' id ';
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
		
		$alls=Array(); $mi=new LinkItem();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			if($f['use_simple_code']==1){
				//ссылка в виде простого кода
				//параметры имени
				$names=Array(); $params=Array();
				foreach($this->langs as $lk=>$g){
					//$mi=new LinkItem();
					$mmi=$mi->GetItemById($f['id'],$g['id']);
					
					if($mmi!=false){
						$is_exist=true;
					}else {
						$is_exist=false;
					}
					
					$names[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'descr'=>stripslashes($mmi['simple_code']) );
					
				}
				
				$alls[]=Array('id'=>$f['id'],'nameitems'=>$names, 'is_code'=>$f['use_simple_code']);
			}else{
				//ссылка с полями
				//параметры имени
				$names=Array(); $params=Array();
				foreach($this->langs as $lk=>$g){
					
					$mmi=$mi->GetItemById($f['id'],$g['id']);
					
					if($mmi!=false){
						$is_exist=true;
					}else {
						$is_exist=false;
					}
					
					$names[]=Array('is_exist'=>$is_exist, 'lang_name'=>strip_tags($g['lang_name']), 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'is_visible'=>$mmi['is_shown'], 'name'=>stripslashes($mmi['name']), 'photopath'=>'/'.stripslashes($mmi['photo_small']), 'small_txt'=>$mmi['small_txt'] );
					
				}
				
				$alls[]=Array('id'=>$f['id'],'nameitems'=>$names, 'is_code'=>$f['use_simple_code'], 'url'=>stripslashes($f['url']));
			}
		}
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		
		return $txt;
	}
	
	 
	
	
}
?>