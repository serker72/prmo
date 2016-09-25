<?
require_once('abstractitem.php');
require_once('supplieritem.php');
require_once('supplier_branches_item.php');
require_once('supplier_cities_group.php');
require_once('supplier_ruk_item.php');
require_once('smarty/SmartyAdm.class.php');
require_once('smarty/SmartyAj.class.php');

class SuppliersMerge{
	
	protected $item;
	protected $datafields;
	
	
	//выполнить объединение данных
	public function Merge($data, $result=NULL){
		$log=new ActionLog;
		$au=new AuthUser();
		if($result===NULL) $result=$au->Auth();
		
		/*echo '<pre>';
		print_r($data);
		echo '</pre>';	*/
		
		/*
		получить массив из карт объединяемых контрагентов
		перебрать поля по встроенному массиву, из них составить массив для правки
		внести правку в целевую карту
		
		блок "города" - взять из [city_ids], остальные - удалить
		блок договора, реквизиты, факт адреса, файловые реестры - сложить путем update соотв. таблиц БД
		в договорах, реквизитах - оставить основными основные записи от основной карты
		
		*/
		
		$_si=new SupplierItem;
		$_cards=array();
		
		foreach($data['ids'] as $k=>$v){
			$si=$_si->getitembyid($v);
			foreach($si as $kk=>$vv) $si[$kk]=SecStr($vv);
			$_cards[$v]=$si;	
		}
		
		
		//строим массив изменений в основной карте
		$params=array();
		foreach($this->datafields as $k=>$v){
			if($v['name']=='base_id'){
				continue;	
			}
			elseif($v['name']=='chief'){
				continue;		
			}
			elseif($v['name']=='main_accountant'){
				continue;		
			}
			elseif($v['name']=='city_ids'){
				continue;		
			}
			
			
			$new_val=$_cards[$data[$v['name']]][$v['name']];
			$old_val=$_cards[$data['base_id']][$v['name']];
			
			$params[$v['name']]=$new_val;
			
			//$params[$v['name']]=$_cards[$data['base_id']][$v['name']];
			
			 if(($old_val!=$new_val)&&($v['name']=='branch_id')){
				   $_ui=new SupplierBranchesItem;
				  $_user=$_ui->GetItemById($new_val);
				  $descr='-'; 
				  if($_user!==false) $descr=SecStr($_user['name'].' ');
				 
				  $log->PutEntry($result['id'],'редактировал отрасль',NULL,87, NULL, 'при объединении карт контрагентов в поле '.$v['name'].' установлено значение '.$descr,$data['base_id']);
				 
			   }elseif(($old_val!=$new_val)&&(($v['name']=='subbranch_id')||($v['name']=='subbranch_id1'))){
				
				   $_ui=new SupplierBranchesItem;
				  $_user=$_ui->GetItemById($new_val);
				  $descr='-'; 
				  if($_user!==false) $descr=SecStr($_user['name'].' ');
				 
				  $log->PutEntry($result['id'],'редактировал подотрасль',NULL,87, NULL, 'при объединении карт контрагентов в поле '.$v['name'].' установлено значение '.$descr,$data['base_id']);
				 	
			  }elseif($old_val!=$new_val){
				  $log->PutEntry($result['id'],'редактировал контрагента',NULL,87, NULL, 'при объединении карт контрагентов в поле '.$v['name'].' установлено значение '.$new_val,$data['base_id']);		
			  }
			
		}
		
		/*echo '<pre>';
		print_r($params);
		echo '</pre>';*/
		
		
		//обработка утверждений
		//активности:
		//если хотя бы одна из карт активна - результирующая будет активна 
		//дата активности - самая старая (т.е. минимальная)
		$is_active=false; $active_pdate=0;  
		foreach($_cards as $k=>$v){
			if($v['is_active']==1) 	$is_active=$is_active||true;
		}
		if($is_active) $params['is_active']=1;
		else $params['is_active']=0;
		foreach($_cards as $k=>$v){
			if(($v['is_active']==1)&&($v['active_first_pdate']<$active_pdate)){
				 $active_pdate=$v['active_pdate'];
				 $params['user_id']=$v['user_id'];
				 
				 $params['active_first_pdate']=$v['active_first_pdate'];
				 $params['active_first_was_set']=$v['active_first_was_set'];
			}
		}
		$params['active_pdate']=$active_pdate;
		
		
		//внесем правку
		$_si->Edit(	$data['base_id'], $params);
		
		//работа со связанными полями
		
		//руководители - из базовой записи
		new NonSet('delete from supplier_ruk where supplier_id<>"'.$data['chief'].'" and supplier_id in('.implode(', ',$data['ids']).') and kind_id=1');
		new NonSet('update supplier_ruk set supplier_id="'.$data['base_id'].'" where supplier_id="'.$data['chief'].'"  and kind_id=1');
		
		new NonSet('delete from supplier_ruk where supplier_id<>"'.$data['main_accountant'].'" and supplier_id in('.implode(', ',$data['ids']).') and kind_id=2');
		new NonSet('update supplier_ruk set supplier_id="'.$data['base_id'].'" where supplier_id="'.$data['main_accountant'].'"  and kind_id=2');
		
		//города
		//удалим их у всех, кроме выбранной записи
		//перенесем их из выбранной записи в базовую
		new NonSet('delete from supplier_sprav_city where supplier_id<>"'.$data['city_ids'].'" and supplier_id in('.implode(', ',$data['ids']).')');
		new NonSet('update supplier_sprav_city set supplier_id="'.$data['base_id'].'" where supplier_id="'.$data['city_ids'].'"');
		
		
		//остальные реестры объединяем
		//основные договора, банковские реквизиты - из основной карты (если есть)
		//договора вх, исх.
		new NonSet('update supplier_contract set is_basic=0 where user_id<>"'.$data['base_id'].'" and user_id in('.implode(', ',$data['ids']).') ');
		new NonSet('update supplier_contract set  user_id="'.$data['base_id'].'" where user_id in('.implode(', ',$data['ids']).')');
		
		//банк реквизиты
		new NonSet('update banking_details set is_basic=0 where user_id<>"'.$data['base_id'].'" and user_id in('.implode(', ',$data['ids']).')');
		new NonSet('update banking_details set  user_id="'.$data['base_id'].'" where user_id in('.implode(', ',$data['ids']).')');
		
		//факт адреса
		new NonSet('update  fact_address set user_id="'.$data['base_id'].'"  where  user_id in('.implode(', ',$data['ids']).')');
		
		new NonSet('update  supplier_notes set user_id="'.$data['base_id'].'"  where  user_id in('.implode(', ',$data['ids']).')');
		
		//отв. сотрудники - объединяем
		new NonSet('update  supplier_responsible_user set supplier_id="'.$data['base_id'].'"  where  supplier_id in('.implode(', ',$data['ids']).')');
		
		
		new NonSet('update  contract_file set user_d_id="'.$data['base_id'].'"  where  user_d_id in('.implode(', ',$data['ids']).')');
		new NonSet('update  contract_file_folder set sup_id="'.$data['base_id'].'"  where  sup_id in('.implode(', ',$data['ids']).')');
		
		new NonSet('update  supplier_shema_file set user_d_id="'.$data['base_id'].'"  where  user_d_id in('.implode(', ',$data['ids']).')');
		new NonSet('update  supplier_shema_file_folder set sup_id="'.$data['base_id'].'"  where  sup_id in('.implode(', ',$data['ids']).')');
		
		
		new NonSet('update  supplier_contact set supplier_id="'.$data['base_id'].'"  where  supplier_id in('.implode(', ',$data['ids']).')');
		
		/*
		
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
		
		*/
		
		//переносим ДОКУМЕНТЫ!!!
		new NonSet('update  kp set supplier_id="'.$data['base_id'].'"  where  supplier_id in('.implode(', ',$data['ids']).')');
		
		new NonSet('update  plan_fact_fact set supplier_id="'.$data['base_id'].'"  where  supplier_id in('.implode(', ',$data['ids']).')');
		
		//переносим ПЛАНИРОВЩИК!!!
		new NonSet('update  sched_contacts set supplier_id="'.$data['base_id'].'"  where  supplier_id in('.implode(', ',$data['ids']).')');
		
		new NonSet('update  sched_suppliers set supplier_id="'.$data['base_id'].'"  where  supplier_id in('.implode(', ',$data['ids']).')');
		
		
		//удалим не базовые карты
		foreach($data['ids'] as $k=>$v){
			if($v!=$data['base_id']){
				$log->PutEntry($result['id'],'удалил контрагента',NULL,88, NULL, 'при объединении карт контрагентов удален контрагент: '.$_cards[$v]['code'].' '.$_cards[$v]['full_name'],$v);	
				
				$_si->Del($v);
		
			}
		}
		
		return $data['base_id'];
		
	}
	
	
	

	//показать форму сравнения
	public function ShowCompareForm($ids, $template, $is_ajax=true){
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		/*foreach($ids as $k=>$id){	
			$supplier=$this->item->GetItemById($id);
		
		}*/
		
		$shifted_data=$this->CompareFields($ids);
		
		$sm->assign('items', $shifted_data);
		
		return $sm->fetch($template);
	}
	
	
	//сравнить поля
	public function CompareFields($ids){
		$alls=array();
		
		//заполним поля
		$alls1=array();
		foreach($ids as $k=>$id){	
			$supplier=$this->item->GetItemById($id);
			
			$fields=array();
			
			foreach($this->datafields as $kk=>$v){
				
				
				
				$field=array(
					'id'=>$id,
					'name'=>$v['name'],
					'caption'=>$v['caption'],
					'value'=>$supplier[$v['name']]
				);
				
				
				$field=$this->RetrieveField($field);
				
				$fields[]=$field;
			
			}
			$alls1[$id]=$fields;
			
		}
		
		//найдем конфликтующие поля
		foreach($alls1 as $supplier_id=>$supplier){
			
			$has_conflict=false;
			
			foreach($supplier as $k=>$data){
			 
					//сравнить с другими поставщиками
					foreach($alls1 as $supplier_id1=>$supplier1){
						if($supplier_id!=$supplier_id1){
							if($data['value']!=$alls1[$supplier_id1][$k]['value']){
								$alls1[$supplier_id1][$k]['has_conflict']=true;
								$alls1[$supplier_id][$k]['has_conflict']=true;
								
							}
						}
					}
						
					
				 
			}
				
		}
		/*
		echo '<pre>';
		
		print_r($alls1);
		
		echo '</pre>';
		*/
		
		foreach($alls1 as $supplier_id=>$supplier){
			$item=$this->item->GetItemById($supplier_id);
			$_opf=new OpfItem;
			$opf=$_opf->GetItemById($item['opf_id']);
			$alls[]=array(
				'id'=>$supplier_id,
				'full_name'=>$item['full_name'],
				'code'=>$item['code'],
				'opf_name'=>$opf['name'],
				'data'=>$supplier
			)	;
		}
		
		return $alls;
	}
	

	function __construct(){
		$this->item=new SupplierItem;
		
		$this->datafields=array();
		$this->datafields[]=array(
			'name'=>'full_name',
			'caption'=>'Полное наименование'
		);
		$this->datafields[]=array(
			'name'=>'print_name',
			'caption'=>'Наименование для печатных форм'
		);
		$this->datafields[]=array(
			'name'=>'ur_or_fiz',
			'caption'=>'Юридическое или физическое лицо'
		);
		$this->datafields[]=array(
			'name'=>'opf_id',
			'caption'=>'ОПФ'
		);
		$this->datafields[]=array(
			'name'=>'inn',
			'caption'=>'ИНН'
		);
		$this->datafields[]=array(
			'name'=>'kpp',
			'caption'=>'КПП'
		);
		$this->datafields[]=array(
			'name'=>'okpo',
			'caption'=>'ОКПО'
		);
		$this->datafields[]=array(
			'name'=>'has_uch',
			'caption'=>'наличие оригинала учредительных документов'
		);
		$this->datafields[]=array(
			'name'=>'time_from_h',
			'caption'=>'Расписание работы с, час,'
		);
		$this->datafields[]=array(
			'name'=>'time_from_m',
			'caption'=>'Расписание работы с, мин,'
		);
		$this->datafields[]=array(
			'name'=>'time_to_h',
			'caption'=>'Расписание работы по, час,'
		);
		$this->datafields[]=array(
			'name'=>'time_to_m',
			'caption'=>'Расписание работы по, мин,'
		);
		$this->datafields[]=array(
			'name'=>'is_upr_nalog',
			'caption'=>'упрощенная форма налогообложения'
		);
		$this->datafields[]=array(
			'name'=>'upr_nalog_no',
			'caption'=>'№ свид-ва об упрощенной системе налогообложения'
		);
		$this->datafields[]=array(
			'name'=>'is_customer',
			'caption'=>'покупатель'
		);
		$this->datafields[]=array(
			'name'=>'is_supplier',
			'caption'=>'поставщик'
		);
		
		$this->datafields[]=array(
			'name'=>'chief',
			'caption'=>'Руководитель'
		);
		
		$this->datafields[]=array(
			'name'=>'main_accountant',
			'caption'=>'Главный бухгалтер'
		);
		
		$this->datafields[]=array(
			'name'=>'lim_deb_debt',
			'caption'=>'Лимит дебиторской задолженности, руб.'
		);
		$this->datafields[]=array(
			'name'=>'legal_address',
			'caption'=>'Юридический адрес'
		);
		$this->datafields[]=array(
			'name'=>'branch_id',
			'caption'=>'Отрасль'
		);
		$this->datafields[]=array(
			'name'=>'subbranch_id',
			'caption'=>'Подотрасль'
		);
		
		$this->datafields[]=array(
			'name'=>'city_ids',
			'caption'=>'Город'
		);
			
	}
	
	//подтягиваем данные из связанных таблиц
	protected function RetrieveField($field){
		
		if($field['name']=='ur_or_fiz'){
			if($field['value']==0) $field['value']='Юридическое лицо';
			else $field['value']='Физическое лицо';
		}
		elseif($field['name']=='opf_id'){
			$_opf=new OpfItem;
			$opf=$_opf->GetItembyId($field['value']);
			$field['value']=$opf['name'];
		}
		elseif($field['name']=='has_uch'){
			if($field['value']==0) $field['value']='нет';
			else $field['value']='да';
		}
		elseif($field['name']=='is_upr_nalog'){
			if($field['value']==0) $field['value']='нет';
			else $field['value']='да';
		}
		elseif($field['name']=='is_customer'){
			if($field['value']==0) $field['value']='нет';
			else $field['value']='да';
		}
		elseif($field['name']=='is_supplier'){
			if($field['value']==0) $field['value']='нет';
			else $field['value']='да';
		}
		elseif($field['name']=='branch_id'){
			$_opf=new SupplierBranchesItem;
			$opf=$_opf->GetItembyId($field['value']);
			$field['value']=$opf['name'];
		}
		elseif($field['name']=='subbranch_id'){
			$_opf=new SupplierBranchesItem;
			$opf=$_opf->GetItembyId($field['value']);
			$field['value']=$opf['name'];
		}
		elseif($field['name']=='subbranch_id1'){
			$_opf=new SupplierBranchesItem;
			$opf=$_opf->GetItembyId($field['value']);
			$field['value']=$opf['name'];
		}

		elseif($field['name']=='city_ids'){
			$_opf=new SupplierCitiesGroup;
			$opf=$_opf->GetItemsByIdArr($field['id']);
			
			  //GetItembyId($field['value']);
			$_vals='';
			foreach($opf as $v) $_vals[]=$v['name'];
			
			$field['value']=implode(', ',$_vals);
		}
		elseif($field['name']=='chief'){
			$_opf=new SupplierRukItem;
			$opf=$_opf->GetActual($field['id'],1);
			$field['value']=$opf['fio'];
		}
		elseif($field['name']=='main_accountant'){
			$_opf=new SupplierRukItem;
			$opf=$_opf->GetActual($field['id'],2);
			$field['value']=$opf['fio'];
		}
		/*$this->datafields[]=array(
			'name'=>'chief',
			'caption'=>'Руководитель'
		);
		
		$this->datafields[]=array(
			'name'=>'main_accountant',
			'caption'=>'Главный бухгалтер'
		);*/
		
		
		return $field;	
	}
}







class Merge_SupplierGroup extends AbstractGroup {
	 
	public $pagename;
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier';
		$this->pagename='view.php';		
		$this->subkeyname='sched_id';	
		$this->vis_name='is_shown';		
		
		 
		 
	}
	
	//Отбор поставщиков для события планировщика
	public function GetItemsForBill($template, DBDecorator $dec, $is_ajax=false, &$alls,$resu=NULL, $current_id=0){
		$_csg=new SupplierCitiesGroup;
		
		$txt='';
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		$au=new AuthUser();
		if($resu===NULL) $resu=$au->Auth();
		
		$sql='select p.*, po.name as opf_name from supplier as p 
			left join opf as po on p.opf_id=po.id  ';
		
	
		
		
		$db_flt=$dec->GenFltSql(' and ');
		if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
		//	$sql_count.=' and '.$db_flt;	
		}
		
		/*if(strlen($db_flt)>0) $sql.=' and ';
		else $sql.=' where ';
		
		//$sql.='  p.is_active=1 ';
		
		$sql.='     p.org_id="'.$resu['org_id'].'" ';
		*/
		
		
		$sql.=' order by p.full_name asc ';
		
		/*$ord_flt=$dec->GenFltOrd();
		if(strlen($ord_flt)>0){
			$sql.=' order by '.$ord_flt;
		}*/
		
		//echo $sql;
		
		$set=new mysqlSet($sql); //,$to_page, $from,$sql_count);
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
	//	$total=$set->GetResultNumRowsUnf();
		
		
		$alls=array(); $_acc=new SupplierItem;
		
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			//
			$csg=$_csg->GetItemsByIdArr($f['id']);
			$f['cities']= $csg;	 
			 
			$f['is_current']=($f['id']==$current_id);
			
			//print_r($f);
			$alls[]=$f;
		}
		
		
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
		$sm->assign('items',$alls);
		
		if($is_ajax) $sm->assign('pos',$alls);
		
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link='suppliers.php?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		return $sm->fetch($template);
	}
	
	
	  
	
}
?>