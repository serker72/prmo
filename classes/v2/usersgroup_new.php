<?
 

// список клиентов
class UsersGroupNew extends UsersGroup {
	 
	
	//установка всех имен
	protected function init(){
		$this->tablename='clients';
		$this->pagename='viewclients.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->name_vis_check='tpl/clients/subitem_lang_vis_check.html';
	}
	
	
	
	//список итемов
	public function GetItemsById($group_id=0,$from=0,$to_page=10,$template=''){
		//список позиций
		$txt='';
		
		$smarty = new SmartyAdm;
		$smarty->debugging = DEBUG_INFO;
		
		$smarty->assign('filename','ed_client.php');
		$smarty->assign('fromno',$from);
		$smarty->assign('topage',$to_page);
		$smarty->assign('group_id',$group_id);
		
		$smarty->assign('listpagename',$this->pagename);
		
		$query='select c.id, c.login, c.username, c.address, c.email, c.phone, c.is_mailed, c.is_blocked, rk.name, l.lang_flag, l.lang_name, c.skidka from 
		((clients as c LEFT JOIN langs as l ON c.lang_id=l.id) 
		LEFT JOIN reg_kind as rk ON c.reg_id=rk.id)';
		if($group_id!=0) $query.=' where c.id in (select clid from cl_by_groups where gr_id="'.$group_id.'") ';
		$query.='order by c.username, c.login ';
		
		$query_count='select count(*) from 
		((clients as c LEFT JOIN langs as l ON c.lang_id=l.id) 
		LEFT JOIN reg_kind as rk ON c.reg_id=rk.id)';
		if($group_id!=0) $query_count.=' where c.id in (select clid from cl_by_groups where gr_id="'.$group_id.'")';
		
//		echo $query;
		
		$items=new mysqlSet($query,$to_page,$from,$query_count);
		
		$rs=$items->GetResult();
		$rc=$items->GetResultNumRows();
		$strs='';
		
		$totalcount=$items->getResultNumRowsUnf();
		
		$navig = new PageNavigator($this->pagename,$totalcount,$to_page,$from,10,'&to_page='.$to_page.'&group_id='.$group_id);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$gg=new GroupsGroup(); $u=new UserItem();
		
		$alls=Array();
		for($i=0;$i<$rc;$i++){
			$f=mysqli_fetch_array($rs);
			//c.id, c.login, c.username, c.address, c.email, c.phone, c.is_mailed, c.is_blocked, rk.name, l.lang_flag, l.lang_name, c.skidka
			
			
			
			$alls[]=Array('id'=>$f['id'], 
			'itemname'=>'<strong>'.stripslashes($f[1]).'</strong><br><em>'.stripslashes($f[2]).'</em><br>'.'<img src="/'.stripslashes($f[9]).'" alt="'.stripslashes($f[10]).'" border="0">',
			'email'=>stripslashes($f[4]),
			'is_mailed'=>$f[6],
			'phone'=>stripslashes($f[5]).'&nbsp;',
			'address'=>stripslashes($f[3]).'&nbsp;',
			'skidka'=>sprintf("%.0f",$f[11]),
			'is_banned'=>$f[7],
			'origin'=>stripslashes($f[8]),
			'orders_count'=>$u->CalcItemsByClid($f[0]),
			'in_groups'=>$gg->GetItemsByClid($f[0],$from,$to_page,$group_id,'clients/in_group.html'),
			'usein'=>$gg->DrawGroupsOptNot($f[0])
			);
		}
		
		
		$smarty->assign('all_groups',$gg->GetItemsOpt($group_id));
		$smarty->assign('pages',$pages);
		$smarty->assign('items',$alls);
		$txt=$smarty->fetch($template);
		
		
		return $txt;
	}
	
	 
	
}
?>