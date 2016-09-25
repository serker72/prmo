<?
 


// список групп клиентов
class GroupsGroupNew extends GroupsGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='cl_groups';
		$this->pagename='viewgroups.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		//$this->all_menu_template='';
	}
	
	
	
	//список всех групп
	public function GetItems($from=0,$to_page=10, $template){
		//список позиций
		$txt='';
		
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_group.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		
		
		$query='select * from '.$this->tablename.' order by name';
		$query_count='select count(*) from '.$this->tablename.' ';
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
			
			$alls[]=Array('id'=>$f['id'], 'name'=>stripslashes($f['name']), 'descr'=>stripslashes($f['descr']));
		}
		
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		
		return $txt;
	} 
	
}
?>