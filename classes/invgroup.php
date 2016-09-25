<?
require_once('abstractgroup.php');
require_once('invitem.php');
require_once('authuser.php');
require_once('maxformer.php');
require_once('invnotesgroup.php');
require_once('invnotesitem.php');
require_once('payforbillgroup.php');


require_once('inventory_view.class.php');

// абстрактная группа
class InvGroup extends AbstractGroup {
	protected $_auth_result;
	
	//установка всех имен
	protected function init(){
		$this->tablename='inventory';
		$this->pagename='invent.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_view=new Inventory_ViewGroup;
		
		$this->_auth_result=NULL;
		
	}
	
	public function ShowPos($template, DBDecorator $dec,$from=0,$to_page=ITEMS_PER_PAGE, $can_add=false, $can_edit=false, $can_delete=false,  $can_confirm=false,  $can_super_confirm=false, $has_header=true, $is_ajax=false, $can_restore=false,$limited_sector=NULL, $can_confirm_inv=false, $can_unconfirm=false, $can_unconfirm_inv=false,  $can_print=false){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		
		$_bill=new InvItem;
		
	//	$_pfg=new PayForBillGroup;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					
					sc.name as sector_name, sc.id as sector_id,
					u.name_s as confirmed_name, u.login as confirmed_login,
					us.name_s as confirmed_inv_name, us.login as confirmed_inv_login,  p.confirm_inv_pdate as confirm_inv_pdate,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					
					left join sector as sc on p.sector_id=sc.id
					left join user as u on p.user_confirm_id=u.id
					left join user as us on p.user_confirm_inv_id=us.id
					left join user as mn on p.manager_id=mn.id
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
					left join sector as sc on p.sector_id=sc.id
					left join user as u on p.user_confirm_id=u.id
					left join user as us on p.user_confirm_inv_id=us.id
					left join user as mn on p.manager_id=mn.id
					';
				 
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
			
			
		}
		
		
		
		$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}
		//echo $sql;
		
		$set=new mysqlSet($sql,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$total=$set->GetResultNumRowsUnf();
		
		
		//page
		if($from>$total) $from=ceil($total/$to_page)*$to_page;
		$navig = new PageNavigator($this->pagename,$total,$to_page,$from,10,'&'.$dec->GenFltUri());
		$navig->SetFirstParamName('from');
		$navig->setDivWrapperName('alblinks');
		$navig->setPageDisplayDivName('alblinks1');			
		$pages= $navig->GetNavigator();
		
		$_pi=new InvItem;
		$_bng=new InvNotesGroup; // BillNotesGroup;
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			$f['inventory_pdate']=date("d.m.Y",$f['inventory_pdate']);
		
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			
			$reason='';
			//$f['can_delete']=$_pi->CanDelete($f['id'],$reason);
			//$f['reason']=$reason;
			//print_r($f);	
			
			
			
			if($f['confirm_pdate']!=0) $f['confirm_pdate']=date("d.m.Y H:i:s",$f['confirm_pdate']);
			else $f['confirm_pdate']='-';
			
			
			if($f['confirm_inv_pdate']!=0) $f['confirm_inv_pdate']=date("d.m.Y H:i:s",$f['confirm_inv_pdate']);
			else $f['confirm_inv_pdate']='-';
			
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id']);
			
			
			$f['can_annul']=$_bill->DocCanAnnul($f['id'],$reason,$f)&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$_bill->GetBindedDocumentsToAnnul($f['id']);
			
			
		
			//снятие утверждения отгрузки
			$reason='';
			$f['can_unconfirm_by_document']=$_bill->DocCanUnconfirmShip($f['id'],$reason,$f);
			$f['can_unconfirm_by_document_reason']=$reason;
			
			
			$reason='';
			$f['can_confirm_by_document']=$_bill->CheckInventoryPdate($f['inventory_pdate'],$f['sector_id'], $reason,$f['id']);
			$f['can_confirm_by_document_reason']=$reason;
			
			//echo $f['binded_payments'];
			
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		
		$current_supplier='';
		$current_user_confirm_price=''; $current_user_confirm_price_id='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			if($v->GetName()=='sector_id'.$add_to_bill) $current_sector=$v->GetValue();
			
			if($v->GetName()=='storage_id'.$add_to_bill) $current_storage=$v->GetValue();
			
			
			if($v->GetName()=='user_confirm_price_id'.$add_to_bill) $current_user_confirm_price_id=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		
		$au=new AuthUser();
		//$result=$au->Auth();
		
		if($this->_auth_result===NULL){
			$result=$au->Auth();
			$this->_auth_result=$result;
		}else{
			$result=$this->_auth_result;	
		}
		
		//kontragent
	
		$_sql='select * from sector order by name asc';
		
		$as=new mysqlSet($_sql);
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$acts=array();
		$acts[]=array('description'=>'');
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['is_current']=($current_sector==$f['id']); 
			$acts[]=$f;
		}
		$sm->assign('sc',$acts);
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_add',$can_add);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
			$sm->assign('can_confirm',$can_confirm);
			$sm->assign('can_unconfirm',$can_unconfirm);
		$sm->assign('can_super_confirm',$can_unconfirm);
		
		$sm->assign('can_confirm_inv', $can_confirm_inv);
		$sm->assign('can_unconfirm_inv', $can_unconfirm_inv);
		$sm->assign('can_super_confirm_inv',$can_unconfirm_inv);
		
		$sm->assign('can_restore',$can_restore);
		$sm->assign('can_print',$can_print);
		
		$_au=new AuthUser();
		//$_result=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_result=$_au->Auth();
			$this->_auth_result=$_result;
		}else{
			$_result=$this->_auth_result;	
		}
		
		$sm->assign('user_id',$_result['id']);
		
		$sm->assign('has_header',$has_header);
		
	//	$sm->assign('add_to_field',$add_to_field);
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		return $sm->fetch($template);
	}
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=30, $days_after_restore=30, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		 $_itm=new InvItem;
		
		$_ni=new InvNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			//проверить наличие связ. документов утв
			$sql1='select count(id) from interstore where inventory_id="'.$f['id'].'" and (is_confirmed=1 or is_confirmed_fill_wf=1)';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			$sql1='select count(id) from bill where inventory_id="'.$f['id'].'" and (is_confirmed_price=1 or is_confirmed_shipping=1)';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			$sql1='select count(id) from sh_i_id where inventory_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			$sql1='select count(id) from acceptance where inventory_id="'.$f['id'].'" and is_confirmed=1';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
			
			
			//случай 1 - нет первой галочки:
			if($f['is_confirmed']==0){
				
				
					
				//проверим дату восстановления
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления инвентаризационного акта, нет утвержденных связанных документов, документ не утвержден';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания инвентаризационного акта, нет утвержденных связанных документов, документ не утвержден';
					}
				}
			}elseif(($f['is_confirmed']==1)&&($f['is_confirmed_inv']==0)){
				//работаем с датой простановки 1 галочки	
					if(($f['confirm_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения заполнения инвентаризационного акта, нет утвержденных связанных документов';
					}
				
			}elseif(($f['is_confirmed']==1)&&($f['is_confirmed_inv']==1)){
				//работаем с датой простановки 2 галочки	
					if(($f['confirm_inv_pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты утверждения коррекции остатков инвентаризационного акта, нет утвержденных связанных документов';
					}
				
			}
			
			
			
			
			
			
			
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'is_confirmed_inv'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование инвентаризационного акта',NULL,336,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: инвентаризационный акт был автоматически аннулирован, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
}
?>