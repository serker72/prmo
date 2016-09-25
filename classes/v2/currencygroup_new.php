<?
 
// группа валют
class CurrencyGroupNew  extends CurrencyGroup{
		
 
		
	//установка всех имен
	protected function init(){
		$this->tablename='currency';
				$this->lang_tablename='currency_lang';
		$this->pagename='viewcurrs.php';		
		$this->subkeyname='mid';	
		
		$this->mid_name='value_id';
		$this->lang_id_name='lang_id';	
		
		$this->all_menu_template='currs/items.html';
		$this->menuitem_template='tpl/currs/itemsrow.html';
		$this->menuitem_template_blocked='tpl/currs/itemsrow.html';
		$this->razbivka_template='tpl/currs/to_page.html';
		
		$this->name_multilang='tpl/currs/subitem_name.html';
		
		$this->name_vis_check='tpl/currs/subitem_lang_vis_check.html';
	}
	
	
	
	//список итемов
	public function GetItems($from=0,$to_page=ITEMS_PER_PAGE, $template=''){
		//список позиций
		$txt='';
		$lg=new LangGroup(); $this->langs=$lg->GetLangsList();
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_curr.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('listpagename',$this->pagename);
		
		$params=Array(); 
		$query=$this->GenerateSQL($params,NULL,NULL,$query_count);
		//$txt.=$sql_count;
		$items=new mysqlSet($query,$to_page,$from,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		$strs='';
		
		$totalcount=$items->getResultNumRowsUnf();
		
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$all_=Array();
		$rules=new CurrUse(); $mi=new CurrencyItem();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			//имя валюты
			$names=Array(); $params=Array();
			foreach($this->langs as $lk=>$g){
				
				
				$mmi=$mi->GetItemById($f['id'],$g['id']);
				
				if($mmi!=false){
					$is_exist=true;
				}else {
					$is_exist=false;
				}
				$names[]=Array( 'is_exist'=>$is_exist,  'lang_name'=>strip_tags($g['lang_name']),
				 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'name'=>stripslashes($mmi['name']) );
				 
				 $params[]=Array( 'is_exist'=>$is_exist,  'lang_name'=>strip_tags($g['lang_name']),
				 'lang_flag'=>stripslashes($g['lang_flag']), 'lang_id'=>$g['id'], 'name'=>stripslashes($mmi['signat']) );
			}
			
			//правила использования
			$usest=$rules->DrawLangsOptNot();
			$used=$rules->GetRulesByCurrId($f['id'],$from,$to_page);
			
			$all_[]=Array('id'=>$f['id'], 'is_base_shop'=>$f['is_base_shop'], 'is_base_rate'=>$f['is_base_rate'],'kurs'=>sprintf("%.6f",$f['rate']), 'usein'=>$usest, 'used'=>$used, 'nameitems'=>$names, 'valitems'=>$params);
		}
		
		$smarty->assign('items',$all_);
		$smarty->assign('pages',$pages);
		$txt.=$smarty->fetch($template); //Выводим нашу страничку
		
		return $txt;
	}
	
	
	
	//список итемов для вывода в сводную таблицу
	/*
	public function GetItemsForOverall(&$ids,$template='tpl/goodsprices/cell.html',$lang_id=LANG_CODE){
		//список позиций
		$txt=''; $ids=Array();
		
		$query='select t.id, t.is_base_rate, t.rate, l.name, l.signat from '.$this->tablename.' as t, '.$this->lang_tablename.' as l where t.id=l.'.$this->mid_name.' and '.$this->lang_id_name.'="'.$lang_id.'" order by t.id';

		$query_count='select count(t.id) from '.$this->tablename.' as t, '.$this->lang_tablename.' as l where t.id=l.'.$this->mid_name.' and '.$this->lang_id_name.'="'.$lang_id.'"';
		
		$items=new mysqlSet($query,NULL,NULL,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			$ids[]=$f['id'];
			$parse_item=new parse_class();
			$parse_item->get_tpl($template);
			
			$rules=new CurrUse();
			$usest=$rules->GetRulesByCurrId($f['id'],0,0,false);
			$parse_item->set_tpl('{buttonplace}',''); //Установка переменной 
				$parse_item->set_tpl('{textplace}','<strong>'.stripslashes($f['name']).'</strong>, '.stripslashes($f['signat']).'<br>'.$usest);
			
			$parse_item->set_tpl('{align}','center');
			$parse_item->set_tpl('{valign}','middle');
			$parse_item->set_tpl('{class}','itemshead');			
			
			$parse_item->tpl_parse();
			
			$txt.=$parse_item->template;
		}
		return $txt;
	}
	*/
	
	//список валют для вывода в шапку сводной таблицы
	//smarty!
	public function GetItemsForOverall(&$ids,$lang_id=LANG_CODE){
		//список позиций
		$txt=''; $ids=Array();
		
		$query='select t.id, t.is_base_rate, t.rate, l.name, l.signat from '.$this->tablename.' as t, '.$this->lang_tablename.' as l where t.id=l.'.$this->mid_name.' and '.$this->lang_id_name.'="'.$lang_id.'" order by t.id';

		$query_count='select count(t.id) from '.$this->tablename.' as t, '.$this->lang_tablename.' as l where t.id=l.'.$this->mid_name.' and '.$this->lang_id_name.'="'.$lang_id.'"';
		
		$items=new mysqlSet($query,NULL,NULL,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			
			$rules=new CurrUse();
			$usest=$rules->GetRulesByCurrId($f['id'],0,0,false);
			
			$ids[]=Array('id'=>$f['id'], 'curr_info'=>'<strong>'.stripslashes($f['name']).'</strong>, '.stripslashes($f['signat']).'<br>'.$usest);
			
			/*
			$ids[]=$f['id'];
			$parse_item=new parse_class();
			$parse_item->get_tpl($template);
			
			$rules=new CurrUse();
			$usest=$rules->GetRulesByCurrId($f['id'],0,0,false);
	
			$parse_item->set_tpl('{textplace}','<strong>'.stripslashes($f['name']).'</strong>, '.stripslashes($f['signat']).'<br>'.$usest);
			
			$parse_item->set_tpl('{align}','center');
			$parse_item->set_tpl('{valign}','middle');
			$parse_item->set_tpl('{class}','itemshead');			
			
			$parse_item->tpl_parse();
			
			$txt.=$parse_item->template;*/
			
			
		}
		return $txt;
	}
	
	
	
	
	
	
	
	public function GetItemsById($id, $mode=0,$from=0,$to_page=10){
		//список позиций
		$txt='';
		
		
		return $txt;
	}
	
	
	
	
	
	//список итемов
	public function GetItemsCliById($id, $filtermode=0,$params=false){
		//список позиций
		$txt=''; $filter=''; $fll='';
		
		
		return $txt;
	}
	
	//список итемов версия для печати
	public function GetItemsCliPrintById($id, $filtermode=0,$params=false){
		//список позиций
		$txt=''; $filter=''; $fll='';
		
		
		
		return $txt;
	}
	
	
	
	protected function GenerateSQL($params, $notparams=NULL, $orderbyparams=NULL, &$sql_count=''){
		$sql='';
		
		$sql='select * from '.$this->tablename;
		
		//запрос для посчета общего числа итемов
		$sql_count='select count(*) from '.$this->tablename;
		
		if(($notparams!=NULL)||($params!=NULL)){
			$sql.='  where ';
			$sql_count.='  where ';
		}
		
		$qq=''; $cter=0;
		foreach($params as $k=>$v){
			if($cter!=0) $qq.=' and ';
			$qq.=$k.'="'.$v.'" ';
			$cter++;
		}
		if($notparams!=NULL){
			$cter=0;
			foreach($notparams as $k=>$v){
				if($cter!=0) $qq.=' and ';
				$qq.=$k.'<>"'.$v.'" ';
				$cter++;
			}
		}
		
		$qq2='';
		if($orderbyparams!=NULL){
			$cter=0;
			foreach($orderbyparams as $k=>$v){
				if($cter==0) $qq2.=' order by ';
				$qq2.=$v.'';
				$cter++;
				if($cter!=count($orderbyparams)) $qq2.=', ';
			}
		}
		
		$sql=$sql.$qq.$qq2;
		$sql_count=$sql_count.$qq;
		
		return $sql;
	}
	
	/*
	//получение списка имен на разных языках
	protected function GetNameParams($f,$from,$to_page){
		$txt='';
		
		
			
		foreach($this->langs as $lk=>$g){
			
			$names=new parse_class();
			$names->get_tpl($this->name_multilang);
			$names->set_tpl('{itemno}',$f['id']);
			
			$names->set_tpl('{filename}','ed_curr.php');
			$names->set_tpl('{fromno}',$from);
			$names->set_tpl('{topage}',$to_page);
			$names->set_tpl('{langid}',$g['id']);
			$names->set_tpl('{langtext}','<img src="/'.stripslashes($g['lang_flag']).'" alt="правка на языке '.strip_tags($g['lang_name']).'" title="правка на языке '.strip_tags($g['lang_name']).'" border="0">');
			
			$mi=new CurrencyItem();
			$mmi=$mi->GetItemById($f['id'],$g['id']);
			
			if($mmi!=false){
				$names->set_tpl('{itemname}',stripslashes($mmi['name']));
				//$names->set_tpl('{vistext}',$this->interface->GetCheckbox($mmi,'','is_shown', 'видим','','tpl/alldicts/subitem_lang_vis_check.html',$g['id'].'_'));		
			}else {
				$names->set_tpl('{itemname}','<em>не создано</em>');
				//$names->set_tpl('{vistext}','');		
			}
			$names->tpl_parse();
			$txt.=$names->template;
			unset($names);
		}
		return $txt;
	}
	
	
	//получение списка обозначений
	protected function GetSigParams($f,$from,$to_page){
		$txt='';
		
		
		foreach($this->langs as $lk=>$g){
			
			$names=new parse_class();
			$names->get_tpl($this->name_multilang);
			$names->set_tpl('{itemno}',$f['id']);
			
			$names->set_tpl('{filename}','ed_curr.php');
			$names->set_tpl('{fromno}',$from);
			$names->set_tpl('{topage}',$to_page);
			$names->set_tpl('{langid}',$g['id']);
			$names->set_tpl('{langtext}','<img src="/'.stripslashes($g['lang_flag']).'" alt="правка на языке '.strip_tags($g['lang_name']).'" title="правка на языке '.strip_tags($g['lang_name']).'" border="0">');
			
			$mi=new CurrencyItem();
			$mmi=$mi->GetItemById($f['id'],$g['id']);
			
			if($mmi!=false){
				$names->set_tpl('{itemname}',stripslashes($mmi['signat']));
				//$names->set_tpl('{vistext}',$this->interface->GetCheckbox($mmi,'','is_shown', 'видим','','tpl/alldicts/subitem_lang_vis_check.html',$g['id'].'_'));		
			}else {
				$names->set_tpl('{itemname}','<em>не создано</em>');
				//$names->set_tpl('{vistext}','');		
			}
			$names->tpl_parse();
			$txt.=$names->template;
			unset($names);
		}
		return $txt;
	}
	*/
	
	//функция нахождения id базовой валюты сайта
	public function GetBaseCurrencyId(){
		$sql='select id from '.$this->tablename.' where is_base_shop="1"';
		$set=new mysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			return $f[0];
		}else return false;
	}
	
	//функция нахождения  базовой валюты сайта
	public function GetBaseCurrency(){
		$sql='select * from '.$this->tablename.' as t, '.$this->lang_tablename.' as l where t.id=l.value_id and l.lang_id="'.LANG_CODE.'" and t.is_base_shop="1"';
		$set=new mysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			return $f;
		}else return false;
	}
	
	//функция нахождения id базовой курсовой валюты сайта
	public function GetBaseRateCurrencyId(){
		$sql='select id from '.$this->tablename.' where is_base_rate="1"';
		$set=new mysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			return $f[0];
		}else return false;
	}
	
	
	//функция нахождения базовой курсовой валюты сайта
	public function GetBaseRateCurrency(){
		$sql='select * from '.$this->tablename.' as t, '.$this->lang_tablename.' as l where t.id=l.value_id and l.lang_id="'.LANG_CODE.'" and t.is_base_rate="1"';
		$set=new mysqlSet($sql);
		$rc=$set->GetResultNumRows();
		$rs=$set->GetResult();
		if($rc>0){
			$f=mysqli_fetch_array($rs);
			return $f;
		}else return false;
	}
	
}
?>