<?
require_once('abstractitem.php');
require_once('discr_man_group.php');
require_once('discr_rightuseritem.php');



require_once('bdetailsgroup.php');
require_once('fagroup.php');

require_once('contractitem.php');
require_once('contractgroup.php');

//require_once('sh_i_group.php');
require_once('acc_group.php');
require_once('paygroup.php');
require_once('billgroup.php');
require_once('trust_group.php');


//require_once('sh_i_in_group.php');
require_once('acc_in_group.php');
require_once('pay_in_group.php');
require_once('bill_in_group.php');
require_once('trust_group.php');


require_once('docstatusitem.php');


//kontragent
class SupplierItem extends AbstractItem{
	protected $is_org;
	protected $is_org_name;
	
	public function __construct($is_org=0){
		$this->init($is_org);
	}
	
	//установка всех имен
	protected function init($is_org){
		$this->is_org=$is_org;
		$this->is_org_name='is_org';
		
		
		$this->tablename='supplier';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_active';	
		//$this->subkeyname='mid';	
	}
	
	
	public function Add($params){
		$params[$this->is_org_name]=$this->is_org;
		$code=parent::Add($params);
		
		if($code!=0){
		  //использовать административные шаблоны
		
		 
		  //добавим вопросы
		  //$this->SetQuestions($code,$questions);
		}
		
		//создавать папку пользователя надо ли?
		
		
		return $code;
	}
	
	public function Edit($id,$params){
		$item=$this->GetItemById($id);
		
		$params[$this->is_org_name]=$this->is_org;
		//$this->SetQuestions($id,$questions);
		
		
		//мы устанавливаем утверждение активности: проверить, первый ли это раз, и подставить дату этого действия
		if(isset($params['is_active'])&&($params['is_active']==1)&&($item['is_active']==0)){
			//$params['restore_pdate']=0;	
			if($item['active_first_was_set']==0){
				$params['active_first_pdate']=time();
				$params['active_first_was_set']=1;
				
					
			}elseif($item['active_first_was_set']==1){
				//это уже происходило, сбросим значения
				$params['active_first_was_set']=2;	
			}
			
		}
		
		
		
		parent::Edit($id,$params);

	}

	
	//удалить
	public function Del($id){
		
		
		new NonSet('delete from banking_details where user_id="'.$id.'"');
		new NonSet('delete from fact_address where user_id="'.$id.'"');
		new NonSet('delete from supplier_notes where user_id="'.$id.'"');
		
		new NonSet('delete from supplier_responsible_user where supplier_id="'.$id.'"');
		
		new NonSet('delete from contract_file where user_d_id="'.$id.'"');
		new NonSet('delete from contract_file_folder where sup_id="'.$id.'"');
		
		new NonSet('delete from supplier_shema_file where user_d_id="'.$id.'"');
		new NonSet('delete from supplier_shema_file_folder where sup_id="'.$id.'"');
		
		
		new NonSet('delete from supplier_sprav_city where supplier_id="'.$id.'"');
		
		new NonSet('delete from supplier_contact_data where contact_id in(select id from supplier_contact where supplier_id="'.$id.'")');
		new NonSet('delete from supplier_contact where supplier_id="'.$id.'"');
		
		new NonSet('delete from supplier_ruk where supplier_id="'.$id.'"');
		
		new NonSet('delete from supplier_contract where user_id="'.$id.'"');
		
		
		parent::Del($id);
	}	
	
	
	
	//запрос о возможности удаленеия и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		
		
		//проверить связанные kp
		/*$set=new mysqlSet('select count(*) from kp where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных коммерческих предложений: '.$g[0];	
		}*/
		
		
		
		//проверить связанные s4eta
		$_accg=new BillGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
				
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='связанных не аннулированных исходящих счетов: '.$cter;
		}
		
		$_accg=new BillInGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
				
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='связанных не аннулированных входящих счетов: '.$cter;
		}
		
		
		//проверить связанные raspor
		$set=new mysqlSet('select count(*) from sh_i where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>3 and is_incoming=0)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных распоряжений на отгрузку: '.$g[0];	
		}
		
		$set=new mysqlSet('select count(*) from sh_i where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>3 and is_incoming=1)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных распоряжений на приемку: '.$g[0];	
		}
		
		
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>6 and is_incoming=1)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных поступлений: '.$g[0];	
		}
		
		$set=new mysqlSet('select count(*) from acceptance where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>6 and is_incoming=0)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных реализаций: '.$g[0];	
		}
		
		
		//проверить связанные doverennosti
		$set=new mysqlSet('select count(*) from trust where bill_id in(select id from bill where supplier_id="'.$id.'" and status_id<>3)');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных доверенностей: '.$g[0];
		}
		
		//проверить связанные oplaty
		$_accg=new PayGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
		
		
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='связанных не аннулированных исходящих оплат: '.$cter;
		}
		
		//проверить связанные oplaty
		$_accg=new PayInGroup;
		$_accg->setidname('supplier_id');
		$arr=$_accg->getitemsbyidarr($id);
		
		
		if(count($arr)>0){
			$cter=0;
			foreach($arr as $k=>$v){
			  if($v['status_id']!=3) {
			  	$can=$can&&false;
				$cter++;
			  }
			  	
			}
			if($cter>0) $reasons[]='связанных не аннулированных входящих оплат: '.$cter;
		}
		
		//весь планировщик
		$set=new mysqlSet('select count(*) from sched where id in(select distinct sched_id from sched_suppliers where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных записей планировщика: '.$g[0];
		}
		$set=new mysqlSet('select count(*) from sched where id in(select distinct sched_id from sched_contacts where supplier_id="'.$id.'" ) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных звонков планировщика: '.$g[0];
		}
		
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	//получение первого итема по набору полей
	public function GetItemByFields($params){
		$params[$this->is_org_name]=$this->is_org;
		return parent::GetItemByFields($params);
	}
	
	
	
	
	//получить НДС в % по айди п-ка
	public function FindNDS($supplier_id, $supplier=NULL){
		if($supplier===NULL) $supplier=$this->GetItemById($supplier_id);
		
		if($supplier['is_upr_nalog']==1){
			return 0;	
		}else return NDS;
		
			
	}
	
	
	
		
	//возможность утвердить активность
	public function CanConfirmActive($id, &$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		
		/*-отрасль
			-название
			-1 контакт
			-1 телефон
			-1 город*/
		
		
		if($item['is_active']!=0){
			
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='активность утверждена';
			$reason.=implode(', ',$reasons);
		}else{
			
			if($item['full_name']==""){
				$can=$can&&false;
				$reasons[]='Не задано название ';
			}
			
			$sql='select count(*) from supplier_sprav_city where supplier_id="'.$id.'"';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='Не выбран город контрагента ';
			}
			
			
			$sql='select count(*) from supplier_contact where supplier_id="'.$id.'"';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='Не введено ни одного контакта ';
			}
			
			$sql='select count(*) from supplier_contact_data as scd inner join supplier_contact  as sc on sc.id=scd.contact_id where sc.supplier_id="'.$id.'" and scd.kind_id in(1,3,4)';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='Не введено ни одного контактного телефона ';
			}
			
			
			$sql='select count(*) from supplier_contact_data as scd inner join supplier_contact  as sc on sc.id=scd.contact_id where sc.supplier_id="'.$id.'" and scd.kind_id in(5)';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			$f=mysqli_fetch_array($rs);
			if((int)$f[0]==0){
				$can=$can&&false;
				$reasons[]='Не введено ни одного контактного email ';
			}
			
			
		
		}
		
		 $reason.=implode(', ',$reasons);
		
		return $can;	
	}
	
	
	
	//запрос о возможности снятия утв-ия активности и возвращение причины, почему нельзя 
	public function CanUnConfirmActive($id,&$reason){
		$can=true;	
		$reason=''; $reasons=array();
		$item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		
		
		
		//проверить связанные kp
		/*$set=new mysqlSet('select count(*) from kp where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных коммерческих предложений: '.$g[0];	
		}*/
		
		
		/*$set=new mysqlSet('select count(*) from plan_fact_fact where supplier_id="'.$id.'" and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных договоров: '.$g[0];	
		}*/
		 
		//связанных не аннулированных записей планировщика
		
	/*	 $set=new mysqlSet('select count(*) from sched where (id in(select distinct sched_id from sched_suppliers where supplier_id="'.$id.'" ) or id in(select distinct sched_id from sched_contacts where supplier_id="'.$id.'" )) and status_id<>3');
		$rs=$set->GetResult();
		$g=mysqli_fetch_array($rs);
		if($g[0]>0){
			$can=$can&&false;
			$reasons[]='связанных не аннулированных записей планировщика: '.$g[0];	
		}
		*/
		$reason=implode(', ',$reasons);
		return $can;
	}

	

}
?>