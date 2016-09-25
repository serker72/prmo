<?
require_once('billgroup.php');
require_once('acc_group.php');
require_once('billitem.php');
require_once('sh_i_item.php');

require_once('authuser.php');
require_once('sh_i_notesgroup.php');
require_once('sh_i_notesitem.php');
require_once('period_checker.php');

// абстрактная группа распор на приемку
class AbstractShIGroup extends AbstractGroup {
	protected $_auth_result;
	
	public $prefix='_1';
	protected $is_incoming=0;
	
	protected $_item;
	protected $_notes_group;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i';
		$this->pagename='ed_ship.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
		$this->_item=new ShIItem;
		$this->_notes_group=new ShINotesGroup;
		
		
		$this->_auth_result=NULL;
		
	}
	
	public function GainSqlSingle(&$sql){
		$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					left join user as mn on p.manager_id=mn.id
					
				where p.is_incoming="'.$this->is_incoming.'" 	
					';
				
	}
	
	public function GainSql(&$sql, &$sql_count){
		$sql='select p.*,
					o.id as o_id, o.pdate as o_pdate, o.code,
					sp.full_name as supplier_name, sp.id as supplier_id,
					spo.name as opf_name,
					u.name_s as confirmed_price_name, u.login as confirmed_price_login,
				
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$this->is_incoming.'" 		
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join bill as o on p.bill_id=o.id
					left join supplier as sp on o.supplier_id=sp.id
					left join opf as spo on spo.id=sp.opf_id
					left join user as u on p.user_confirm_id=u.id
					
					left join user as mn on p.manager_id=mn.id
				where p.is_incoming="'.$this->is_incoming.'" 		
					';
				 
	}
	
	
	
	
	public function ShowPos($bill_id, $template, DBDecorator $dec,$can_edit=false, $can_delete=false, $can_confirm=false, $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL,$by_is=NULL,$can_unconfirm=false){
		
		
		$_au=new AuthUser;
		
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		
		$has_filter=$_au->FltSector($_aures);
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		//$_acc=new ShIItem;
		
		
		$this->GainSqlSingle($sql);
		$sql.=' and '.$this->subkeyname.'="'.$bill_id.'"';
				
		
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			
			
			$sql.=' and '.$db_flt;
			
		
		}else{
				
		}
		
		
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		
		
		$alls=array();
	//	$_bng=new ShINotesGroup;
		
		$_au=new AuthUser;
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		$_pch=new PeriodChecker;
		$_pg=new PerGroup;
		$periods=$_pg->GetItemsByIdArr($_aures['org_id'],0,1);	
		
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$this->_item->sh_i_blink->OverallBlink($f, $has_filter, $color);
			$f['color']=$color;
			
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']); //$_bng->GetItemsByIdArr($f['id']);
			
						
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete; //$_acc->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$this->_item->GetBindedDocumentsToAnnul($f['id']);
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		//$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('action',1);
		$sm->assign('id',$bill_id);
		
		
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		
		$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		
			
		$sm->assign('prefix',$this->prefix);
		
		
		$sm->assign('can_restore',$can_restore);
		
		$_au=new AuthUser();
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;
		}
		$sm->assign('user_id',$_result['id']);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		$sm->assign('has_header',$has_header);
		
		return $sm->fetch($template);
	}
	
	
	
	
	///показ всех
	public function ShowAllPos($template, DBDecorator $dec,$can_edit=false, $can_delete=false, $from=0, $to_page=ITEMS_PER_PAGE,  $can_confirm=false,  $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL,$can_unconfirm){
		
		$_au=new AuthUser;
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		
		$has_filter=$_au->FltSector($_aures);
		
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
	//	$_acc=new ShIItem;
		
		$this->GainSql($sql, $sql_count);
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' and '.$db_flt;
			$sql_count.=' and '.$db_flt;	
		}
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo '<br>'.$sql.'<br>';
		
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
		
		//$_bng=new ShINotesGroup;
		
		$_au=new AuthUser;
		//$_aures=$_au->Auth();
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		
		$_pch=new PeriodChecker;
		$_pg=new PerGroup;
		$periods=$_pg->GetItemsByIdArr($_aures['org_id'],0,1);	
		
	
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$this->_item->sh_i_blink->OverallBlink($f, $has_filter, $color);
			$f['color']=$color;
			
			
			
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			$f['o_pdate']=date("d.m.Y",$f['o_pdate']);
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			$f['notes']=$this->_notes_group->GetItemsByIdArr($f['id']);
			
			
			$f['can_annul']=$this->_item->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$this->_item->GetBindedDocumentsToAnnul($f['id']);
			
			
			
			
			
			
			//print_r($f);	
			$alls[]=$f;
		}
		
		//заполним шаблон полями
	
		$current_supplier='';
		$user_confirm_id='';
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			
			
			if($v->GetName()=='supplier_id') $current_supplier=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_id') $current_user_confirm_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
		
		
		//kontragent
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
	
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		$sm->assign('bill_id',$bill_id);
		
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
			$sm->assign('can_confirm',$can_confirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		$sm->assign('can_unconfirm',$can_unconfirm);
		
		$sm->assign('can_restore',$can_restore);
			
		$sm->assign('prefix',$this->prefix);
		
		
		$_au=new AuthUser();
		//$_result=$_au->Auth();
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		
		$sm->assign('user_id',$_result['id']);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri('&', $this->prefix);
		//echo $link;
		$link=$this->pagename.'?'.eregi_replace('&sortmode'.$this->prefix.'=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		$sm->assign('has_header',$has_header);
		
		return $sm->fetch($template);
	}
	
	//список позиций
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and is_incoming="'.$this->is_incoming.'" order by  id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=30, $days_after_restore=30, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new ShINotesItem;
		 $_itm=new ShIItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			
			//проверить наличие связ. документов утв
			
			$sql1='select count(id) from acceptance where sh_i_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			$reason='';
			
			
			if($f['is_confirmed']==0){
				//НЕ УТВЕРЖДЕНО
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления распоряжения на приемку, нет утвержденных связанных документов, документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания распоряжения на приемку, нет утвержденных связанных документов, документ не утвержден';
					}
				}
				
			}else{
				//УТВЕРЖДЕНО
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления распоряжения на приемку, нет утвержденных связанных документов';
					}
				}else{
					//работаем с датой создания	
					if(($f['confirm_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения распоряжения на приемку, нет утвержденных связанных документов';
					}
				}
			}
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
			
				
				$log->PutEntry(0,'автоматическое аннулирование распоряжения на приемку',NULL,226,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: распоряжение на приемку было автоматически аннулировано, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
	
	//автоматическое выравнивание распоряжений на приемку
	public function AutoEq($days=21){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		
		
		
		$_stat=new DocStatusItem;
		
		$_ni=new ShINotesItem;
		 $_itm=new ShIItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id="7" order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$can_annul=false;
			
			$checked_time=$now-$days*24*60*60;
			
			//найти посл (по зад. дате) поступл. по распор.
			$set1=new MysqlSet('select * from acceptance where sh_i_id="'.$f['id'].'" and is_confirmed=1 and given_pdate<"'.$checked_time.'" order by given_pdate desc limit 1');
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			if($rc1==0) continue; //нет  поступлений, не ровняем...
			else $can_annul=true;
			
			//$f=mysqli_fetch_array($rs);
			
			//поступление есть, проводим выравнивание...
			if($can_annul){
					
				//найти позиции распоряжения, сформировать массив args, вызвать выравнивание...
				$posset=new mysqlset('select * from sh_i_position where sh_i_id='.$f['id'].'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				$args=array();
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					
					//$args=array();
					$args[]=$h['position_id'].';'.$h['pl_position_id'].';'.$h['pl_discount_id'].';'.$h['pl_discount_value'].';'.$h['pl_discount_rub_or_percent'].';'.$h['quantity'];	
					
					//найти последнее поступление по этой позиции распоряжения.
					//если нет поступления
					
					//$_itm->DoEq($f['id'],$args,$some_output,1,$f,$_result);
				}
				
				//echo $f['id'].'<br>';
				
				//if($f['id']!=51) continue;
				//$zz=$_itm->ScanEq($f['id'],$args,$some_o,$f);
				
				$_itm->DoEq($f['id'],$args,$some_output,1,$f,$_result);
				
			//	echo "$some_o <br />";
			//	print_r($args);
				//print_r($zz);
			}
			
			
		}
		
	}
}
?>