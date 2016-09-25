<?
require_once('abstractitem.php');
require_once('positem.php');
require_once('komplblink.php');
require_once('komplpositem.php');
require_once('komplposgroup.php');
require_once('komplconfgroup.php');
require_once('komplconfitem.php');
require_once('komplconfrolegroup.php');
require_once('komplconfroleitem.php');

require_once('docstatusitem.php');

require_once('trust_group.php');
require_once('acc_item.php');
require_once('acc_positem.php');
require_once('acc_pospmitem.php');
require_once('acc_in_item.php');

require_once('acc_group.php');
require_once('paygroup.php');

require_once('actionlog.php');
require_once('authuser.php');

require_once('komplmarkgroup.php');
require_once('komplmarkitem.php');


require_once('discr_man.php');
require_once('rights_detector.php');

require_once('period_checker.php');
require_once('komplnotesitem.php');
require_once('positem.php');

require_once('posgroupgroup.php');

require_once('bdetailsitem.php');
require_once('supcontract_item.php');
require_once('billitem.php');

require_once('supplieritem.php');
require_once('opfitem.php');

require_once('authuser.php');
require_once('actionlog.php');

require_once('komplitem.php');

require_once('billitem.php');
require_once('bill_in_item.php');
require_once('billpositem.php');
require_once('billpospmitem.php');
require_once('billcreator.php');
require_once('bill_in_creator.php');

require_once('komplpositem.php');
require_once('komplconfitem.php');
 
require_once('komplnotesitem.php');
require_once('billnotesitem.php');
require_once('acc_notesitem.php');

//синхронизация заявки между организациями
class KompSync  {
	protected static $uslugi;
	protected static $position_uslugi;
	
	 
	
	protected static $semi_uslugi;
	protected static $position_semi_uslugi;
	
 
 	public $id;
	public $is_standart;
	public $org_id;
	public $new_org_id;
	public $auth_result;
	
	public $new_komplekt_ved_id=NULL;
	
	public $standart_koef=1.005;
	
	public function __construct($id, $is_standart, $org_id, $new_org_id, $auth_result=NULL){
			$this->init($id, $is_standart, $org_id, $new_org_id,  $auth_result);
	}
 
	
	//установка всех имен
	protected function init($id, $is_standart, $org_id, $new_org_id, $auth_result=NULL){
		
	
		//массив групп услуг
		if(self::$uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SERVICE_CODE); // услуги
		  self::$uslugi/*$this->uslugi*/=array();
		  self::$uslugi/*$this->uslugi*/[]=SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$uslugi/*$this->uslugi*/)) self::$uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$uslugi/*$this->uslugi*/))  self::$uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
		//массив самих услуг
		if(self::$position_uslugi===NULL){
			self::$position_uslugi=array();
			$sql='select id from catalog_position where group_id in('.implode(', ',self::$uslugi).')';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				self::$position_uslugi[]=$f['id'];	
			}
		}
		
		
		if(self::$semi_uslugi===NULL){
		  $_pgg=new PosGroupGroup;
		  $arc=$_pgg->GetItemsByIdArr(SEMI_SERVICE_CODE); // услуги
		  self::$semi_uslugi/*$this->uslugi*/=array();
		  self::$semi_uslugi/*$this->uslugi*/[]=SEMI_SERVICE_CODE;
		  foreach($arc as $k=>$v){
			  if(!in_array($v['id'],self::$semi_uslugi/*$this->uslugi*/)) self::$semi_uslugi/*$this->uslugi*/[]=$v['id'];
			  $arr2=$_pgg->GetItemsByIdArr($v['id']);
			  foreach($arr2 as $kk=>$vv){
				  if(!in_array($vv['id'],self::$semi_uslugi/*$this->uslugi*/))  self::$semi_uslugi/*$this->uslugi*/[]=$vv['id'];
			  }
		  }
		  //var_dump(self::$uslugi);
		}
		
		//массив самих услуг
		if(self::$position_semi_uslugi===NULL){
			self::$position_semi_uslugi=array();
			$sql='select id from catalog_position where group_id in('.implode(', ',self::$semi_uslugi).')';
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$rc=$set->GetResultNumRows();
			for($i=0; $i<$rc; $i++){
				$f=mysqli_fetch_array($rs);
				self::$position_semi_uslugi[]=$f['id'];	
			}
		}
		
		
		$this->id=$id;
		$this->is_standart=$is_standart;
		$this->org_id=$org_id;
		$this->new_org_id=$new_org_id;
		
		$au=new AuthUser;
		if($auth_result===NULL) $auth_result=$au->Auth();
		$this->auth_result=$auth_result;
	}
	
	
	
	//главный метод
	
	public function Sync(){
		$ret_array=array();
		
		//проверить корректность заявки...
		$log=new ActionLog;
		$_ki=new KomplItem; $_si=new SupplierItem; $_opf=new OpfItem;
		
		$_org=new SupplierItem; 
		$org=$_org->GetItemById($this->org_id); $opf=$_opf->GetItemById($org['opf_id']);
		
		$new_org=$_org->GetItemById($this->new_org_id); $new_opf=$_opf->GetItemById($new_org['opf_id']);
		
		
		  $kpi=new KomplPosItem; $kci=new KomplConfItem;

		$_bill=new BillItem; $bpi=new BillPosItem;  $bpmi=new BillPosPMItem;
		$crea=new BillCreator; $crea_in=new BillInCreator; $kni=new KomplNotesItem;
		$_bill_in=new BillInItem;
		
		$_bdi=new BDetailsItem; $_ci=new SupContractItem; $_acc=new AccItem; $_acc_in=new AccInItem;
		$_api=new AccPosItem; $_apmi=new AccPosPMItem;
		
		$_bni=new BillNotesItem; $_ani=new AccNotesItem;
		
		//$current_ki=$_ki->getitembyid($this->id);
			$sql='select * from komplekt_ved where
			id="'.$this->id.'"
		 ';
				 
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		if($rc>0){
			$current_ki=mysqli_fetch_array($rs, MYSQL_ASSOC);
		}else $current_ki=false;
		
		//$komplekt_ved.is_active==1 and $supplier.is_org==1 and $supplier.id!=$org.id and ($komplekt_ved.is_leading==-1 or $komplekt_ved.is_leading==1)
		$current_si=$_si->GetItemById($current_ki['supplier_id']);
		
		//var_dump($current_ki);
		//var_dump($current_si);
		
		
		if(
			($current_ki['is_active']!=1)||
			($current_si['is_org']!=1)||
			!($current_si['id']!=$this->org_id)||
			( $current_ki['is_leading']==0)
		)
		{
			if($current_ki['is_active']!=1) return 'заявка не активна';
			if($current_si['is_org']!=1) return 'контрагент заявки не организация';
			if(!($current_si['id']!=$this->org_id)) return 'контрагент заявки - текущая организация';
			if( $current_ki['is_leading']==0) return 'неподходящий статус ведущая-ведомая';
		}
		
		
		//этой заявке - выставить статус ведущей
		$params=array();
		$params['is_leading']=1;
		$_ki->Edit($this->id, $params);
		
		//проверить наличие подчиненной заявки 
		$sql='select * from komplekt_ved where
			org_id="'.$this->new_org_id.'"
			and is_leading=0
			and leading_komplekt_ved_id="'.$this->id.'" ';
				 
		$set=new mysqlset($sql);
		$rs=$set->getresult();
		$rc=$set->getresultnumrows();
		
		if($rc>0){
			$binded_ki=mysqli_fetch_array($rs, MYSQL_ASSOC);	
			$this->new_komplekt_ved_id=$binded_ki['id'];
		}else{
			$binded_ki=false;
		}
		
		$params=array();
		/*if($binded_ki!==false){
			$params=$binded_ki;
			unset($params['id']);
		}*/
		
		$params=$current_ki;
		unset($params['id']);
		
		$params['org_id']=$this->new_org_id;
		$params['supplier_id']=0;
		$params['is_active']=0;
		$params['status_id']=1;
		$params['is_leading']=0;
		$params['leading_komplekt_ved_id']=$this->id;
		//если есть подчиненная заявка - то удаляем ее утверждения
		//и правим ее
		if($binded_ki!==false){
			// 
			$set1=new mysqlSet('select k.*, p.name as role_name, p.unconfirm_object_id  from komplekt_ved_confirm as k left join komplekt_ved_confirm_roles as p on p.id=k.role_id 	 where k.komplekt_ved_id='.$this->new_komplekt_ved_id);
		
			$rs1=$set1->getResult();
			$rc1=$set1->getResultnumrows();
			
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1, MYSQL_ASSOC);
				$kci->Del($g['id']);
				$log->PutEntry($this->auth_result['id'], 'автоматическое снятие утвеждения заявки в роли '.$g['role_name'].' при копировании заявки',NULL,$g['unconfirm_object_id'],NULL,'',$this->new_komplekt_ved_id);
			}
			
			
			$_ki->Edit($this->new_komplekt_ved_id, $params);
			
		}else{
		//если нет подчиненной заявки - то создаем ее - копируя текущую без утверждений, контрагента.
			
			 
			$this->new_komplekt_ved_id=$_ki->Add($params);
			$log->PutEntry($this->auth_result['id'],'автоматическое создание заявки при копировании заявки',NULL,81,NULL,SecStr('заявка №'.$this->new_komplekt_ved_id.' автоматически скопирована в организацию '.$new_opf['name'].' '.$new_org['full_name'].' из заявки № '.$this->id.' организации '.$opf['name'].' '.$org['full_name']),$this->new_komplekt_ved_id);
		}
		
		//перенести примечания к старой заявке в новую
		$set1=new nonSet('delete from komplekt_ved_notes where user_id='.$this->new_komplekt_ved_id);
		$set1=new mysqlSet('select * from komplekt_ved_notes where user_id='.$this->id);
	
		$rs1=$set1->getResult();
		$rc1=$set1->getResultnumrows();
		
		for($i1=0; $i1<$rc1; $i1++){
			$g=mysqli_fetch_array($rs1, MYSQL_ASSOC);
			
			unset($g['id']);
			$notes_params=$g;
			$notes_params['note']=SecStr($g['note']);
			$notes_params['user_id']=$this->new_komplekt_ved_id;
			
			$kni->Add($notes_params);
		}
		
		//дописать в новую заявку примечания о копировании из старой
		$notes_params=array();
		$notes_params['pdate']=time();
		$notes_params['user_id']=$this->new_komplekt_ved_id;;
		$notes_params['note']=SecStr('Заявка была скопирована из заявки № '.$this->id.' базы организации '.$opf['name'].' '.$org['full_name']);
		$notes_params['posted_user_id']=-1;
		$notes_params['is_auto']=1;
		$kni->Add($notes_params);
		
		$ret_array[]='заявка № '.$this->new_komplekt_ved_id;
		
		//удаление позиций новой заявки и занесение их из старой
		$set1=new nonSet('delete from komplekt_ved_pos where komplekt_ved_id='.$this->new_komplekt_ved_id);
		$set1=new mysqlSet('select * from komplekt_ved_pos where komplekt_ved_id='.$this->id.' order by id asc');
		
		$rs1=$set1->getResult();
		$rc1=$set1->getResultnumrows();
		
		for($i1=0; $i1<$rc1; $i1++){
			$g=mysqli_fetch_array($rs1, MYSQL_ASSOC);
			
			unset($g['id']);
			$notes_params=$g;
		 
			$notes_params['komplekt_ved_id']=$this->new_komplekt_ved_id;
			
			$kpi->Add($notes_params);
		}
		
		
		//работаем с исход. счетами при их наличии
		//найдем счет с утв. ценой
		$sql1='select * from bill 
			where
				is_incoming=0
				and org_id="'.$this->org_id.'"
				and is_confirmed_price=1
				and (id in(
						select bill_id from bill_position where komplekt_ved_id="'.$this->id.'"
					) or
					komplekt_ved_id="'.$this->id.'"
					)
				order by id asc';
					
		$set1=new mysqlSet($sql1);
		
		$rs1=$set1->getResult();
		$rc1=$set1->getResultnumrows();
		
		$bills=array();
		for($i1=0; $i1<$rc1; $i1++){
			$g=mysqli_fetch_array($rs1, MYSQL_ASSOC);
			$bills[]=$g;
			//$ret_array[]=' найден счет '. $g['code'];
		}
		
		//перебираем найденные счета. по каждому из них создаем: 
		//входящий счет с такими же ценами от нашей организации, с заданным номером как у нашей организацией
		//даты оплат мы чистим 
		//исходящий - от пустого контрагента
		//если стд режим - то цены +0,5% 
		//если не стд режим - то нулевые цены
		
		foreach($bills as $k=>$bill){
			//сделаем счет ведущим
			$params=array();
			$params['is_leading']=1;
			$params['leading_bill_id']=0;
			
			$_bill->Edit($bill['id'], $params);
					
			//заведем исходящие счета
			//$test_bill=$_bill->getitembyfields(array('is_incoming'=>0, 
			//проверим, есть ли исх. счет 
			$sql1='select * from bill where 
				is_incoming=0
				and org_id="'.$this->new_org_id.'"
				and is_leading=0
				and leading_bill_id="'.$bill['id'].'" ';
			$set1=new mysqlSet($sql1);
		
			$rs1=$set1->getResult();
			$rc1=$set1->getResultnumrows();
			
			
			$params=array();
			if($rc1>0){
				$test_bill=mysqli_fetch_array($rs1, MYSQL_ASSOC);
				$new_bill_id=$test_bill['id'];
				//проверить наличие реализаций по счету
				//аннулировать их все!
				$sql2='select * from acceptance where is_incoming=0 and bill_id="'.$new_bill_id.'" and status_id<>6';
				$set2=new mysqlSet($sql2);
		
				$rs2=$set2->getResult();
				$rc2=$set2->getResultnumrows();
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					$acc_params=array();
					$acc_params['is_confirmed']=0;
					$acc_params['status_id']=6;
					$_acc->Edit($h['id'], $acc_params);
					
						
					//запись об аннуляции реализации
					$log->PutEntry($this->auth_result['id'],'автоматическое аннулирование реализации при копировании заявки',NULL,242,NULL,SecStr('Реализация № '.$h['id'].' автоматически аннулирована при копировании заявки.'),$h['id']);
					
					
					//дописать об аннуляции реализации
					$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$h['id'];
					$notes_params['note']=SecStr('Реализация № '.$h['id'].' автоматически аннулирована при копировании заявки.');
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$_ani->Add($notes_params);
					
						
				}
				
			}else $test_bill=false;
			
			if($test_bill!==false){
				$params=$test_bill;
				unset($params['id']);
				
				 
			}else{
				$params['is_incoming']=0;
				$params['pdate']=time();
				$params['code']=$crea->GenLogin($this->auth_result['id']);//GenLogin(2);
				$params['manager_id']=$this->auth_result['id'];
			}
			
			$params['org_id']=$this->new_org_id;
			$params['status_id']=1;
			$params['is_confirmed_price']=0;
			$params['is_confirmed_shipping']=0;
			$params['supplier_bill_no']='';
			$params['supplier_id']=0;
			$params['bdetails_id']=0;
			$params['contract_id']=0;
			$params['sector_id']=1;
			$params['komplekt_ved_id']=$this->new_komplekt_ved_id;
			
			$params['pdate_shipping_plan']=0;
			$params['pdate_shipping_fact']=0;
			$params['pdate_payment_contract']=0;
			$params['pdate_payment_ind']=0;
			
			$params['supplier_bill_pdate']=0;
			
			$params['is_in_buh']=0;
			$params['cannot_eq']=0;
			$params['cannot_an']=0;
			$params['is_leading']=0;
			$params['leading_bill_id']=$bill['id'];
			
			if($test_bill!==false){
				$_bill->Edit($new_bill_id, $params);
				
				 
			}else{
				$new_bill_id=$_bill->Add($params);
				
					
			}
			
			//запись о создании счета
			$log->PutEntry($this->auth_result['id'],'автоматическое создание исходящего счета при копировании заявки',NULL,92,NULL,SecStr('исх. счет № '.$params['code'].' автоматически скопирован в организацию '.$new_opf['name'].' '.$new_org['full_name'].' из исх.счета № '.$bill['code'].' организации '.$opf['name'].' '.$org['full_name']),$new_bill_id);
			
			
			//дописать в новую счет примечания о копировании из старой
			$notes_params=array();
			$notes_params['pdate']=time();
			$notes_params['user_id']=$new_bill_id;
			$notes_params['note']=SecStr('исх. счет № '.$params['code'].' автоматически скопирован в организацию '.$new_opf['name'].' '.$new_org['full_name'].' из исх.счета № '.$bill['code'].' организации '.$opf['name'].' '.$org['full_name']);
			$notes_params['posted_user_id']=-1;
			$notes_params['is_auto']=1;
			$_bni->Add($notes_params);
			
			
			$_bill->FreeBindedPayments($new_bill_id,true,$this->auth_result);
			
			$ret_array[]='исходящий счет № '.$params['code'];
			
			//перенесем позиции счета
			//очистим имеющиеся позиции счета
			new NonSet('delete from bill_position_pm where bill_position_id in(select id from bill_position where bill_id="'.$new_bill_id.'")');
			new NonSet('delete from cash_bill_position where bill_position_id in(select id from bill_position where bill_id="'.$new_bill_id.'")');
			new NonSet('delete  from bill_position where bill_id="'.$new_bill_id.'"');
			
			//применим либо стд схему, либо нулевые цены
			$sql2='select * from bill_position where bill_id="'.$bill['id'].'" order by id asc';
			$set2=new mysqlset($sql2);
			$rs2=$set2->getResult();
			$rc2=$set2->getResultNumRows();
			
			for($i2=0; $i2<$rc2; $i2++){
				$g2=mysqli_fetch_array($rs2, MYSQL_ASSOC);	
				
				$pos_params=$g2;
				unset($pos_params['id']);
				
				$pos_params['bill_id']=$new_bill_id;
				if(self::IsPosUsl($g2['position_id'])) $pos_params['komplekt_ved_id']=0;
				else $pos_params['komplekt_ved_id']=$this->new_komplekt_ved_id;
				
				if($this->is_standart){
					if(!self::IsPosUsl($g2['position_id'])&&!self::IsSemiPosUsl($g2['position_id'])){
						/*$delta_pm=$g2['price_pm']-$g2['price'];
						
						 
						$pos_params['total']=$g2['total']*$this->standart_koef;
						 
						$pos_params['price_pm']=$pos_params['total']/$pos_params['quantity'];
						$pos_params['price']=$pos_params['price_pm']-$delta_pm;*/
						$pos_params['total']=$g2['total']*$this->standart_koef;
						$pos_params['price_pm']=$pos_params['total']/$pos_params['quantity'];
						$pos_params['price']=$pos_params['price_pm'];
					}
				}else{
					$pos_params['price']=0;
					$pos_params['total']=0;
					$pos_params['price_pm']=0;
				}
				
				 $pos_params['name']=SecStr($pos_params['name']);
				$bpi_id=$bpi->Add($pos_params);
				
				//перенесем +/-
				if($this->is_standart){
					/*$sql3='select * from bill_position_pm where bill_position_id="'.$g2['id'].'"';
					$set3=new mysqlset($sql3);
					$rs3=$set3->getResult();
					$rc3=$set3->getResultNumRows();
					
					for($i3=0; $i3<$rc3; $i3++){
						$g3=mysqli_fetch_array($rs3, MYSQL_ASSOC);	
						
						$pos_params=$g3;
						unset($pos_params['id']);
						$pos_params['bill_position_id']=$bpi_id;
						$bpmi->Add($pos_params);
					
					}*/
				}
			}
			
			
			
			//заведем входящие счета
			//проверить, есть ли вх. счет 
			$sql1='select * from bill where 
				is_incoming=1
				and org_id="'.$this->new_org_id.'"
				and is_leading=0
				and leading_bill_id="'.$bill['id'].'" ';
			$set1=new mysqlSet($sql1);
		
			$rs1=$set1->getResult();
			$rc1=$set1->getResultnumrows();
			
			$params=array();
			if($rc1>0){
				$test_bill_in=mysqli_fetch_array($rs1, MYSQL_ASSOC);
				$new_bill_in_id=$test_bill_in['id'];
			}else $test_bill_in=false;
			
			if($test_bill_in!==false){
				$params=$test_bill_in;
				unset($params['id']);
				
				 
			}else{
				$params['is_incoming']=1;
				$params['pdate']=time();
				$params['code']=$crea_in->GenLogin($this->auth_result['id']);//GenLogin(2);
				$params['manager_id']=$this->auth_result['id'];
			}
			
			$params['org_id']=$this->new_org_id;
			$params['out_bill_id']=$new_bill_id;
			$params['status_id']=1;
			$params['is_confirmed_price']=0;
			$params['is_confirmed_shipping']=0;
			$params['supplier_bill_no']=$bill['supplier_bill_no'];
			
			
			
			$params['supplier_id']=$this->org_id;
			$test_bdi=$_bdi->GetItemByFields(array('user_id'=>$this->org_id, 'is_basic'=>1));
			$params['bdetails_id']=$test_bdi['id'];
			$test_ci=$_ci->GetItemByFields(array('user_id'=>$this->org_id, 'is_incoming'=>1, 'is_basic'=>1));
			$params['contract_id']=$test_ci['id'];
			
			
			
			$params['sector_id']=1;
			$params['komplekt_ved_id']=0; //$this->new_komplekt_ved_id;
			
			$params['pdate_shipping_plan']=0;
			$params['pdate_shipping_fact']=0;
			$params['pdate_payment_contract']=0;
			$params['pdate_payment_ind']=0;
			
			$params['supplier_bill_pdate']=0;
			
			$params['is_in_buh']=0;
			$params['cannot_eq']=0;
			$params['cannot_an']=0;
			$params['is_leading']=0;
			$params['leading_bill_id']=$bill['id'];
			
			if($test_bill_in!==false){
				$_bill_in->Edit($new_bill_in_id, $params);
			}else $new_bill_in_id=$_bill_in->Add($params);
			
			
			//запись о создании счета
			$log->PutEntry($this->auth_result['id'],'автоматическое создание входящего счета при копировании заявки',NULL,607,NULL,SecStr('вх. счет № '.$params['code'].' автоматически скопирован в организацию '.$new_opf['name'].' '.$new_org['full_name'].' из исх.счета № '.$bill['code'].' организации '.$opf['name'].' '.$org['full_name']),$new_bill_in_id);
			
			
			//дописать в новую счет примечания о копировании из старой
			$notes_params=array();
			$notes_params['pdate']=time();
			$notes_params['user_id']=$new_bill_in_id;
			$notes_params['note']=SecStr('вх. счет № '.$params['code'].' автоматически скопирован в организацию '.$new_opf['name'].' '.$new_org['full_name'].' из исх.счета № '.$bill['code'].' организации '.$opf['name'].' '.$org['full_name']);
			$notes_params['posted_user_id']=-1;
			$notes_params['is_auto']=1;
			$_bni->Add($notes_params);
			
			
			
			
			$_bill_in->FreeBindedPayments($new_bill_in_id,true,$this->auth_result);
			
			$ret_array[]='входящий счет № '.$params['code'];
			
			
			//очистим имеющиеся позиции счета
			new NonSet('delete from bill_position_pm where bill_position_id in(select id from bill_position where bill_id="'.$new_bill_in_id.'")');
			new NonSet('delete from cash_bill_position where bill_position_id in(select id from bill_position where bill_id="'.$new_bill_in_id.'")');
			new NonSet('delete  from bill_position where bill_id="'.$new_bill_in_id.'"');
			
			
			//перенесем позиции во вход. счет 
			//полностью без изменений!
			//только у услуг айди заявки =0
			$sql2='select * from bill_position where bill_id="'.$bill['id'].'" order by id asc';
			$set2=new mysqlset($sql2);
			$rs2=$set2->getResult();
			$rc2=$set2->getResultNumRows();
			
			for($i2=0; $i2<$rc2; $i2++){
				$g2=mysqli_fetch_array($rs2, MYSQL_ASSOC);	
				
				$pos_params=$g2;
				unset($pos_params['id']);
				
				$pos_params['bill_id']=$new_bill_in_id;
				$pos_params['out_bill_id']=$new_bill_id;
				if(self::IsPosUsl($g2['position_id'])) $pos_params['komplekt_ved_id']=0;
				else $pos_params['komplekt_ved_id']=$this->new_komplekt_ved_id;
				
				$pos_params['name']=SecStr($pos_params['name']); 
				
				//уравнниваем стартовую цену с итоговой
				$pos_params['price']=($pos_params['price_pm']);
				
				$bpi_id=$bpi->Add($pos_params);
				
				//перенесем +/-
				 
				/*$sql3='select * from bill_position_pm where bill_position_id="'.$g2['id'].'"';
				$set3=new mysqlset($sql3);
				$rs3=$set3->getResult();
				$rc3=$set3->getResultNumRows();
				
				for($i3=0; $i3<$rc3; $i3++){
					$g3=mysqli_fetch_array($rs3, MYSQL_ASSOC);	
					
					$pos_params=$g3;
					unset($pos_params['id']);
					$pos_params['bill_position_id']=$bpi_id;
					$bpmi->Add($pos_params);
				
				}*/
				 
			}
			
			
			
			//поступления заводить только если есть реализации по счету!
			$sql1='select * from acceptance where 
				org_id="'.$this->org_id.'"
				and is_incoming=0
				and bill_id="'.$bill['id'].'"
				and is_confirmed=1 
			order by id';
			$set1=new mysqlset($sql1);
			$rs1=$set1->getResult();
			$rc1=$set1->getResultNumRows();
		
			if($rc1>0){
				
				
				$accs=array();
				for($i1=0; $i1<$rc1; $i1++){
					$f=mysqli_fetch_array($rs1, MYSQL_ASSOC);
					$accs[]=$f;
				}
				
				//перебираем реализации
				//по каждой находим ведомое поступление
				//помечаем реализацию как ведущую
				foreach($accs as $kk=>$acc){
					$params=array();
					$params['is_leading']=1;
					$params['leading_acceptance_id']=0;
					$_acc->Edit($acc['id'], $params);
					
					
					//есть ли уже поступления???
					 $sql1='select * from acceptance where 
						org_id="'.$this->new_org_id.'"
						and is_incoming=1
						and bill_id="'.$new_bill_in_id.'"
						and out_bill_id="'.$new_bill_id.'"
						and is_leading=0
						and leading_acceptance_id="'.$acc['id'].'"
					order by id';
					
					$set1=new mysqlset($sql1);
					$rs1=$set1->getResult();
					$rc1=$set1->getResultNumRows();
					
					$params=array();
					if($rc1>0){
						$test_acc_in=mysqli_fetch_array($rs1, MYSQL_ASSOC);
						$new_acc_in_id=$test_acc_in['id'];
					}else $test_acc_in=false;
					
					
					if($test_acc_in!==false){
						$params=$test_acc_in;
						unset($params['id']);
						
						 
					}else{
						$params['is_incoming']=1;
						$params['pdate']=time();
						 
						$params['manager_id']=$this->auth_result['id'];
					}
					$params['out_bill_id']=$new_bill_id;
					$params['bill_id']=$new_bill_in_id;
					$params['is_confirmed']=0;
					$params['org_id']=$this->new_org_id;
					$params['given_no']=$acc['given_no'];
					$params['given_pdate']=0;
					$params['status_id']=4;
					$params['sector_id']=1;
					$params['has_nakl']=0;
					$params['has_fakt']=0;
					$params['has_akt']=0;
					
					$params['is_leading']=0;
					
					$params['leading_acceptance_id']=(int)$acc['id'];
					
					if($test_acc_in!==false){
						$_acc_in->Edit($new_acc_in_id, $params);
					}else $new_acc_in_id=$_acc_in->Add($params);
							
					$ret_array[]='поступление № '.$new_acc_in_id;
					
					
					//запись о создании поступления
					$log->PutEntry($this->auth_result['id'],'автоматическое создание поступления при копировании заявки',NULL,661,NULL,SecStr('поступление № '.$new_acc_in_id.' автоматически создано в организации '.$new_opf['name'].' '.$new_org['full_name'].' из реализации № '.$acc['id'].' организации '.$opf['name'].' '.$org['full_name']),$new_acc_in_id);
					
					
					//дописать в новую поступление примечания о копировании из старой
					$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$new_acc_in_id;
					$notes_params['note']=SecStr('поступление № '.$new_acc_in_id.' автоматически создано в организации '.$new_opf['name'].' '.$new_org['full_name'].' из реализации № '.$acc['id'].' организации '.$opf['name'].' '.$org['full_name']);
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$_ani->Add($notes_params);
					
					
					
					//скопируем позиции
					
					//очистим имеющиеся позиции поступления
					new NonSet('delete from  acceptance_position_pm where acceptance_position_id in(select id from  acceptance_position where acceptance_id="'.$new_acc_in_id.'")');
					 
					new NonSet('delete  from  acceptance_position where acceptance_id="'.$new_acc_in_id.'"');
					
					
					//перенесем позиции в поступление
					//полностью без изменений!
					//только у услуг айди заявки =0
					$sql2='select * from acceptance_position where acceptance_id="'.$acc['id'].'" order by id asc';
					$set2=new mysqlset($sql2);
					$rs2=$set2->getResult();
					$rc2=$set2->getResultNumRows();
					
					for($i2=0; $i2<$rc2; $i2++){
						$g2=mysqli_fetch_array($rs2, MYSQL_ASSOC);	
						
						$pos_params=$g2;
						unset($pos_params['id']);
						
						$pos_params['out_bill_id']=$new_bill_id;
						$pos_params['acceptance_id']=$new_acc_in_id;
						if(self::IsPosUsl($g2['position_id'])) $pos_params['komplekt_ved_id']=0;
						else $pos_params['komplekt_ved_id']=$this->new_komplekt_ved_id;
						
						  $pos_params['name']=SecStr($pos_params['name']);
						
						//уравнивание цен для +/-
						$pos_params['price']=$pos_params['price_pm'];
						
						
						$api_id=$_api->Add($pos_params);
						
						//перенесем +/-
						 
						/*$sql3='select * from acceptance_position_pm where acceptance_position_id="'.$g2['id'].'"';
						$set3=new mysqlset($sql3);
						$rs3=$set3->getResult();
						$rc3=$set3->getResultNumRows();
						
						for($i3=0; $i3<$rc3; $i3++){
							$g3=mysqli_fetch_array($rs3, MYSQL_ASSOC);	
							
							$pos_params=$g3;
							unset($pos_params['id']);
							$pos_params['acceptance_position_id']=$bpi_id;
							$_apmi->Add($pos_params);
						
						}*/
						 
					}
					
					
					
				} //цикл по поступлениям
				
				
				 
				 
			}//else $ret_array[]='реализаций нет, поступления не заводим!'; условие по реализациям
				
		}//цикл по исх. счетам
		
		
		return implode(', ',$ret_array);
	}
	
	
	
	
	
	//строим список привязанных не аннулированных документов
	public  function GetLeadingDocs($id, &$kompl_ids, &$bill_ids, &$acc_ids){
		$ret_array=array();
		
		//получим заявку
		$sql='select * from komplekt_ved where is_leading=0 and  status_id<>3  and leading_komplekt_ved_id="'.$id.'" ';
		$set=new mysqlset($sql );
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		$kompl_ids=array();
		for($i3=0; $i3<$rc; $i3++){
			$f=mysqli_fetch_array($rs);
			$ret_array[]='заявка № '.$f['id'];	
			$kompl_ids[]=$f['id'];
		}
		
		
		//получим связ.  счета...
		$sql='select * from bill where is_leading=0 and status_id<>3 and leading_bill_id in(select id from bill where status_id<>3 and (komplekt_ved_id="'.$id.'" or id in(select bill_id from bill_position where komplekt_ved_id="'.$id.'") ))';
		
		$set=new mysqlset($sql );
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		$bill_ids=array();
		for($i3=0; $i3<$rc; $i3++){
			$f=mysqli_fetch_array($rs);
			if($f['is_incoming']==1) $ret_array[]='входящий счет № '.$f['code'];
			else  $ret_array[]='исходящий счет № '.$f['code'];	
			$bill_ids[]=$f['id'];
		}
		
		
		//получим связанные поступления и реализации - по ранее полученным счетам
		$sql='select * from acceptance where status_id<>6 and bill_id in('.implode(', ',$bill_ids).')';
		$set=new mysqlset($sql );
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		$acc_ids=array();
		for($i3=0; $i3<$rc; $i3++){
			$f=mysqli_fetch_array($rs);
			if($f['is_incoming']==1) $ret_array[]='поступление № '.$f['id'];
			else  $ret_array[]='реализация № '.$f['id'];	
			$acc_ids[]=$f['id'];
		}
		 
			
		return implode(', ',$ret_array);
	}
	
	
	//аннулируем все привязанные документы (например, при смене к-та заявки)
	public function AnnulLeadingDocs($id, $annul_reason='при смене контрагента заявки'){
		
		//echo 'ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ';
		
		$this->GetLeadingDocs($id, $kompl_ids, $bill_ids, $acc_ids);
		
		$log=new ActionLog; $_ki=new KomplItem;
		 $kpi=new KomplPosItem; $kci=new KomplConfItem;

		$_bill=new BillItem; $bpi=new BillPosItem;  $bpmi=new BillPosPMItem;
		$crea=new BillCreator; $crea_in=new BillInCreator; $kni=new KomplNotesItem;
		$_bill_in=new BillInItem;
		
		$_bdi=new BDetailsItem; $_ci=new SupContractItem; $_acc=new AccItem; $_acc_in=new AccInItem;
		$_api=new AccPosItem; $_apmi=new AccPosPMItem;
		
		$_bni=new BillNotesItem; $_ani=new AccNotesItem;
		
		$sql='select * from komplekt_ved where id in('.implode(', ',$kompl_ids).') and status_id<>3';
		//echo $sql;
		$set=new mysqlset($sql );
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		for($i3=0; $i3<$rc; $i3++){
			$f=mysqli_fetch_array($rs);
			 
			//снимаем все утверждения
			$set1=new mysqlSet('select k.*, p.name as role_name, p.unconfirm_object_id  from komplekt_ved_confirm as k left join komplekt_ved_confirm_roles as p on p.id=k.role_id 	 where k.komplekt_ved_id='.$f['id']);
		
			$rs1=$set1->getResult();
			$rc1=$set1->getResultnumrows();
			
			for($i1=0; $i1<$rc1; $i1++){
				$g=mysqli_fetch_array($rs1 );
				$kci->Del($g['id']);
				$log->PutEntry($this->auth_result['id'], 'автоматическое снятие утвеждения заявки в роли '.$g['role_name'].'  ',NULL,$g['unconfirm_object_id'],NULL,'',$f['id']);
			}
			
			$params=array();
			$params['is_active']=0;
			$params['status_id']=3;
			$_ki->Edit($f['id'], $params);
			
			$log->PutEntry($this->auth_result['id'], 'автоматическое аннулирование заявки ',NULL, 83,NULL,SecStr('заявка № '.$f['id'].' автоматически аннулирована '.$annul_reason.' № '.$id),$f['id']);
			
				
				$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$f['id'];
					$notes_params['note']=SecStr('заявка № '.$f['id'].' автоматически аннулирована '.$annul_reason.' № '.$id);
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$kni->Add($notes_params);	
				
		}
		
		
		$sql='select * from bill where id in('.implode(', ',$bill_ids).') and status_id<>3';
		$set=new mysqlset($sql );
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		for($i3=0; $i3<$rc; $i3++){
			$f=mysqli_fetch_array($rs);
			
			if($f['is_confirmed_price']==1) $_bill->FreeBindedPayments($f['id'],0,$this->auth_result);
			
			
			$params=array();
			$params['is_confirmed_price']=0;
			$params['is_confirmed_shipping']=0;
			$params['status_id']=3;
			$_bill->Edit($f['id'], $params);
			
			
			if($f['is_incoming']==1){
				$_bill_in->FreeBindedPayments($f['id'],true,$this->auth_result);
				$log->PutEntry($this->auth_result['id'], 'автоматическое аннулирование входящего счета ',NULL, 626,NULL,SecStr('входящий счет № '.$f['code'].' автоматически аннулирован '.$annul_reason.' № '.$id),$f['id']);
				
				$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$f['id'];
					$notes_params['note']=SecStr('входящий счет № '.$f['code'].' автоматически аннулирован '.$annul_reason.' № '.$id);
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$_bni->Add($notes_params);	
				
			}else{
				$_bill->FreeBindedPayments($f['id'],true,$this->auth_result);
				$log->PutEntry($this->auth_result['id'], 'автоматическое аннулирование исходящего счета ',NULL, 94,NULL,SecStr('исходящий счет № '.$f['code'].' автоматически аннулирован '.$annul_reason.' № '.$id),$f['id']);
				
				$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$f['id'];
					$notes_params['note']=SecStr('исходящий счет № '.$f['code'].' автоматически аннулирован '.$annul_reason.' № '.$id);
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$_bni->Add($notes_params);	
			}
			 
		}
		
		
		
		$sql='select * from acceptance where id in('.implode(', ',$acc_ids).') and status_id<>6';
		$set=new mysqlset($sql );
		$rs=$set->getResult();
		$rc=$set->getResultNumRows();
		
		for($i3=0; $i3<$rc; $i3++){
			$f=mysqli_fetch_array($rs);
			
			$params=array();
			$params['is_confirmed']=0;
			 
			$params['status_id']=6;
			$_acc->Edit($f['id'], $params);
			
			if($f['is_incoming']==1){
				$log->PutEntry($this->auth_result['id'], 'автоматическое аннулирование поступления ',NULL, 674,NULL,SecStr('поступление № '.$f['id'].' автоматически аннулировано '.$annul_reason.' № '.$id),$f['id']);
			 
					$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$f['id'];
					$notes_params['note']=SecStr('поступление № '.$f['id'].' автоматически аннулировано '.$annul_reason.' № '.$id);
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$_ani->Add($notes_params);	
				
			}else{
				$log->PutEntry($this->auth_result['id'], 'автоматическое аннулирование реализации',NULL, 242,NULL,SecStr('реализация № '.$f['id'].' автоматически аннулирована '.$annul_reason.' № '.$id),$f['id']);
					$notes_params=array();
					$notes_params['pdate']=time();
					$notes_params['user_id']=$f['id'];
					$notes_params['note']=SecStr('реализация № '.$f['id'].' автоматически аннулирована '.$annul_reason.' № '.$id);
					$notes_params['posted_user_id']=-1;
					$notes_params['is_auto']=1;
					$_ani->Add($notes_params);	
				
			}
			 
		}
		
		
	}
	
	
	
	
	
	
	 
	          
	
	
	//принадлежит ли данная категория категории услуг
	public function IsUsl($id){
		return in_array($id,self::$uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная позиция категории услуг
	public function IsPosUsl($position_id){
		return in_array($position_id,self::$position_uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная категория категории услуг
	public function IsSemiUsl($id){
		return in_array($id,self::$semi_uslugi/*$this->uslugi*/);
	}
	
	//принадлежит ли данная позиция категории услуг
	public function IsSemiPosUsl($position_id){
		return in_array($position_id,self::$position_semi_uslugi/*$this->uslugi*/);
	}
}
?>