<?

require_once('abstractgroup.php');
require_once('supplier_responsible_user_item.php');
 
// абстрактная группа
class SupplierResponsibleUserGroup extends AbstractGroup {
	  
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_responsible_user';
		$this->pagename='claim.php';		
		$this->subkeyname='supplier_id';	
		$this->vis_name='is_shown';		
		 
	}
	
	
	
	//добавка ответственных 
	public function AddUsers($current_id, array $positions,  $result=NULL){
		$_kpi=new SupplierResponsibleUserItem ;
		
		$log_entries=array();
		
		//сформируем список старых позиций
		$old_positions=array();
		$old_positions=$this->GetUsersArr($current_id);
		
		foreach($positions as $k=>$v){
			$kpi=$_kpi->GetItemByFields(array('supplier_id'=>$v['supplier_id'],'user_id'=>$v['user_id']));
			
			if($kpi===false){
				//dobavim pozicii	
				//$_kpi->Add(array('komplekt_ved_id'=>$v['komplekt_ved_id'],'position_id'=>$v['position_id'], 'quantity'=>$v['quantity']));
				
				$add_array=array();
				$add_array['supplier_id']=$v['supplier_id'];
				$add_array['user_id']=$v['user_id'];
				
				 
				
				 
				$_kpi->Add($add_array);
				
				/*echo '<pre>1';
				print_r($add_array);
				echo '</pre>';*/
				
				$log_entries[]=array(
					'action'=>0,
					'supplier_id'=>$v['supplier_id'],
					'user_id'=>$v['user_id'] 
				);
				
			}else{
				//++ pozicii
				
				$add_array=array();
				$add_array['supplier_id']=$v['supplier_id'];
				$add_array['user_id']=$v['user_id'];
				
				 
				 
				$_kpi->Edit($kpi['id'],$add_array); //, $add_pms,$can_change_cascade,$check_delta_summ,$result);
				
				/*echo '<pre>';
				echo $kpi['id'];
				print_r($add_array);
				echo '</pre>';*/
				
				//если есть изменения
				
				//как определить? изменились prava
				
				$to_log=false;
				if($kpi['right_id']!=$add_array['right_id']) $to_log=$to_log||true;
				 
				if($to_log){
				
				  $log_entries[]=array(
					  'action'=>1,
					  'supplier_id'=>$v['supplier_id'],
					'user_id'=>$v['user_id']
				  );
				}
				
			}
		}
		
		//найти и удалить удаляемые позиции:
		//удал. поз. - это позиция, которой нет в массиве $positions
		$_to_delete_positions=array();
		foreach($old_positions as $k=>$v){
			//$v['id']
			$_in_arr=false;
			foreach($positions as $kk=>$vv){
				if(($vv['supplier_id']==$v['supplier_id'])&&($vv['user_id']==$v['user_id'])
				 
				){
					$_in_arr=true;
					break;	
				}
			}
			
			if(!$_in_arr){
				$_to_delete_positions[]=$v;	
			}
		}
		
		//удаляем найденные позиции
		foreach($_to_delete_positions as $k=>$v){
			
			//формируем записи для журнала
			 
			
			$log_entries[]=array(
					'action'=>2,
					'supplier_id'=>$v['supplier_id'],
					'user_id'=>$v['user_id'] 
			);
			
			//удаляем позицию
			$_kpi->Del($v['id']);
		}
		
		
		//необходимо вернуть массив измененных записей для журнала
		return $log_entries;
	}
	
	//список позиций
	public function GetUsersArr($id,  &$ids){
		$arr=array(); $ids=array();
		
		$sql='select p.*, u.name_s, u.login, u.is_active, u.position_s
		from '.$this->tablename.' as p
		inner join user as u on u.id=p.user_id 
		where
		'.$this->subkeyname.'="'.$id.'"
		order by u.name_s asc, u.login asc';
		
		 
		 
		
		//echo $sql."<p>";
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			 
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['hash']=md5($f['user_id'].'_'.$f['right_id']); 
			
			
			$ids[]=$f['user_id'];
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function ShowPos($user_id, $template, DBDecorator $dec, $is_ajax=false, $can_create_task=false, &$alls, $from=0, $to_page=ITEMS_PER_PAGE){
		
		//echo $from;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		$alls=array();
		
		
		$this->GainSql($user_id, $sql, $sql_count);
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql.'<br>';
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri('&', $this->prefix));
		$navig->SetFirstParamName('from'.$this->prefix);
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$alls=array();
		
		$_tu=new TaskUserGroup;
		$_ts=new TaskSupplierGroup;
	//	$myh=new TaskHistoryGroup;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y H:i:s",$f['pdate']);
			
			
			$this->_tblink->OverallBlink($f['id'],$color,$f);		
			$f['color']=$color;
			
			$f['task_pdate_unf']=$f['task_pdate'];
			
			$f['task_pdate']=DateFromYmd($f['task_pdate']);
			
			
			//метод проверки новых данных
//			var_dump($this->_thg);
			$count_new=$this->_thg->CountNew($f['id'], $user_id);
			
			$f['is_new']=($count_new>0);	
			
			//отв
			$f['users']=$_tu->GetItemsArrById($f['id']);
			
			
			//к-ты
			$f['konts']=$_ts->GetItemsArrById($f['id']);
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}	
			
		
		
		
		
		//заполним шаблон полями
		
		$current_kind='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='kind_id') $current_kind=$v->GetValue();
			
		/*	if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			if($v->GetName()=='supplier_name') $current_supplier=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
		
			
			if($v->GetName()=='sh_i_id'){
				$current_sh_i_id=$v->GetValue();
				continue;	
			}*/
				$sm->assign($v->GetName(),$v->GetValue());	
		}
		
			//vidy
		$_sts=new TaskKindGroup;
		$sts=$_sts->GetItemsArr($current_kind);
		/*foreach($sts as $kk=>$v){
			//foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$ids[]=$f['id'];
			$names[]=$f['name'];
		}
		$sm->assign('status_ids',$ids);
		$sm->assign('status_names',$names);
		$sm->assign('status_id',$current_status);*/
		$sts[]=array('id'=>0, 'name'=>'все задачи');
		foreach($sts as $kk=>$v){
			if($v['id']==$current_kind) $sts[$kk]['is_current']=true;
			else $sts[$kk]['is_current']=false;
			
		}
		$sm->assign('sc', $sts);
		
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from'.$this->prefix,$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_create_task', $can_create_task);
		$sm->assign('prefix',$this->prefix);
		
		
			//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&',$this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		
		return $sm->fetch($template);
	
	}
	
	 
}
?>