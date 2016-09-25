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

require_once('kompsync.php');

require_once('messageitem.php');

//синхронизация заявки между организациями
class AccSync2   {
	protected static $uslugi;
	protected static $position_uslugi;
	
	 
	
	protected static $semi_uslugi;
	protected static $position_semi_uslugi;
	
 
 	public $id;
	 
	public $org_id;
	public $new_org_id;
	public $auth_result;
	
	public $new_acc_id=NULL;
	
	public $standart_koef=1.005;
	
	public function __construct($id,   $org_id,   $auth_result=NULL){
			$this->init($id,   $org_id,   $auth_result);
	}
 
	
	//установка всех имен
	protected function init($id,  $org_id, $auth_result=NULL){
		
	
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
		 
		$this->org_id=$org_id;
		 
		
		$au=new AuthUser;
		if($auth_result===NULL) $auth_result=$au->Auth();
		$this->auth_result=$auth_result;
	}
	
	
	
	//главный метод
	
	public function Sync(){
		$ret_array=array();
		
		 
		$log=new ActionLog;
		$_ki=new KomplItem; $_si=new SupplierItem; $_opf=new OpfItem;
		
		$_org=new SupplierItem; 
		$org=$_org->GetItemById($this->org_id); $opf=$_opf->GetItemById($org['opf_id']);
		
		//$new_org=$_org->GetItemById($this->new_org_id); $new_opf=$_opf->GetItemById($new_org['opf_id']);
		
		
		  $kpi=new KomplPosItem; $kci=new KomplConfItem;

		$_bill=new BillItem; $bpi=new BillPosItem;  $bpmi=new BillPosPMItem;
		$crea=new BillCreator; $crea_in=new BillInCreator; $kni=new KomplNotesItem;
		$_bill_in=new BillInItem;
		
		$_bdi=new BDetailsItem; $_ci=new SupContractItem; $_acc=new AccItem; $_acc_in=new AccInItem;
		$_api=new AccPosItem; $_apmi=new AccPosPMItem;
		
		$_bni=new BillNotesItem; $_ani=new AccNotesItem;
		
		
		//проверить необходимость копирования реал- в пост-ие в другую базу...
		$do_it=false;
		
		$our_acc=$_acc->GetItemById($this->id);
		
		$our_bill=$_bill->GetItemById($our_acc['bill_id']);
		
		
		
		//родительская заявка. их может быть несколько!
		$our_acc_positions=$_acc->GetPositionsArr($this->id, false, false);
		
		$our_kvs=array(); $our_kv_ids=array();
		foreach($our_acc_positions as $k=>$v){
			if(!in_array($v['komplekt_ved_id'], $our_kv_ids)){
				$our_kv_ids[]=$v['komplekt_ved_id'];
				if($v['komplekt_ved_id']!=0){
					$_kompl=$_ki->getitembyid($v['komplekt_ved_id']);
					$our_kvs[]=$_kompl;
					
				}else $our_kvs[]=NULL;
			}
			
		}
		
		/*echo '<pre>';
		print_r($our_bill);
		print_r($our_kvs);
		echo '</pre>';
		*/
		 
		$do_it=$do_it||($our_bill['is_leading']==1);
		if($do_it){
			foreach($our_kvs as $k=>$v) $do_it=$do_it||($v['is_leading']==1)	;
		}
		 
		if($do_it){
			//выполнить проверки наличия связанной заявки и связанного вход счета
			//именно их поля пойдут в наше создаваемое поступление
			$do_it=true;
			
			$new_bill=$_bill_in->getitembyfields(array(
				'is_leading'=>0,
				'is_incoming'=>1,
				'leading_bill_id'=>$our_bill['id']
			));	
			
			$do_it=$do_it&&($new_bill!==false);
			
			//найдем новую организацию (для записей в журнал и примечания)
			$new_org=$_org->GetItemById($new_bill['org_id']); $new_opf=$_opf->GetItemById($new_org['opf_id']);
		
			
			
			//проверить наличие связанной заявки
			$new_kvs=array(); $new_kv_ids=array();
			if($do_it){
				foreach($our_kvs as $k=>$v)	if($v['is_leading']==1){
					$new_kv=$_ki->getitembyfields(array(
						'is_leading'=>0,
						'leading_komplekt_ved_id'=>$v['id']
					));	
					if($new_kv!==false){
						$new_kv_ids[]=$new_kv['id'];
						$new_kvs[]=$new_kv;
					}
				}
				$do_it=$do_it&&(count($new_kv)>0);
			}
			
			
		}// of do_it
		
		
		//var_dump($do_it);
		//все проверки пройдены, начинаем копирование...
		if($do_it){
			$this->new_org_id=$new_bill['org_id'];
			
			$new_acc=$_acc_in->getitembyfields(array(
				'bill_id'=>$new_bill['id'],
				'is_incoming'=>1,
				'is_leading'=>0,
				'leading_acceptance_id'=>$this->id
			));
			
			$params=array();
			$params['org_id']=$this->new_org_id;
			$params['given_no']=$our_acc['given_no'];
			$params['given_pdate']=$our_acc['given_pdate'];
			$params['is_confirmed']=0;
			$params['status_id']=4;
			
			if($new_acc===false){
				$params['is_incoming']=1;
				$params['out_bill_id']=$new_bill['out_bill_id'];	
				$params['bill_id']=$new_bill['id'];	
				$params['pdate']=time();
				$params['org_id']=$this->new_org_id;
				$params['manager_id']=$this->auth_result['id'];
				$params['sector_id']=$new_bill['sector_id'];
				$params['is_leading']=0;
				$params['leading_acceptance_id']=$this->id;
				
				$this->new_acc_id=$_acc_in->Add($params);
				//echo 'adding';
				
				//примечания, запись в журнале
				//запись о создании поступления
				$log->PutEntry($this->auth_result['id'],'автоматическое создание поступления при утверждении реализации',NULL,661,NULL,SecStr('поступление № '.$this->new_acc_id.' автоматически создано в организации '.$new_opf['name'].' '.$new_org['full_name'].' при утверждении реализации № '.$our_acc['id'].' в организации '.$opf['name'].' '.$org['full_name']),$this->new_acc_id);
					
					
				//дописать в новую поступление примечания о копировании из старой
				$notes_params=array();
				$notes_params['pdate']=time();
				$notes_params['user_id']=$this->new_acc_id;
				$notes_params['note']=SecStr('поступление № '.$this->new_acc_id.' автоматически создано в организации '.$new_opf['name'].' '.$new_org['full_name'].' при утверждении реализации № '.$our_acc['id'].' в организации '.$opf['name'].' '.$org['full_name']);
				$notes_params['posted_user_id']=-1;
				$notes_params['is_auto']=1;
				$_ani->Add($notes_params);
					
				
				
			}else{
				$_acc_in->Edit($new_acc['id'], $params, false, $this->auth_result);
				$this->new_acc_id=$new_acc['id'];
				
				//echo 'editing';
				
				//примечания, запись в журнале
				//запись о создании поступления
				$log->PutEntry($this->auth_result['id'],'автоматическое переоформление поступления при утверждении реализации',NULL,664,NULL,SecStr('поступление № '.$this->new_acc_id.' автоматически создано в организации '.$new_opf['name'].' '.$new_org['full_name'].' при утверждени реализации № '.$our_acc['id'].' в организации '.$opf['name'].' '.$org['full_name']),$this->new_acc_id);
					
					
				//дописать в новую поступление примечания о копировании из старой
				$notes_params=array();
				$notes_params['pdate']=time();
				$notes_params['user_id']=$this->new_acc_id;
				$notes_params['note']=SecStr('поступление № '.$this->new_acc_id.' автоматически переоформлено в организации '.$new_opf['name'].' '.$new_org['full_name'].' при утверждении реализации № '.$our_acc['id'].' в организации '.$opf['name'].' '.$org['full_name']);
				$notes_params['posted_user_id']=-1;
				$notes_params['is_auto']=1;
				$_ani->Add($notes_params);
					
			}
			
			
			
			
			
			
			//обновим поступление...
			$new_acc=$_acc_in->GetItemById($this->new_acc_id);
			
			//занесем в родительскую реализацию данные
			$_acc->Edit($this->id, array('is_leading'=>1),false, $this->auth_result);
			
			//работа с позициями...
			//очистим имеющиеся позиции поступления
			new NonSet('delete from  acceptance_position_pm where acceptance_position_id in(select id from  acceptance_position where acceptance_id="'.$this->new_acc_id.'")');
			
			new NonSet('delete  from  acceptance_position where acceptance_id="'.$this->new_acc_id.'"');
			
			//скопируем старые позиции
			$sql2='select * from acceptance_position where acceptance_id="'.$our_acc['id'].'" and komplekt_ved_id in('.implode(', ',$our_kv_ids).') order by id asc';
			$set2=new mysqlset($sql2);
			$rs2=$set2->getResult();
			$rc2=$set2->getResultNumRows();
			
			for($i2=0; $i2<$rc2; $i2++){
				 
				$g2=mysqli_fetch_array($rs2, MYSQL_ASSOC);	
				
				$pos_params=$g2;
				unset($pos_params['id']);
				
				$pos_params['out_bill_id']=$new_bill['out_bill_id'];
				$pos_params['acceptance_id']=$this->new_acc_id;
				if(self::IsPosUsl($g2['position_id'])) $pos_params['komplekt_ved_id']=0;
				else{
					 $test_komplekt=$_ki->Getitembyfields(array(
					 	'is_leading'=>0,
						'leading_komplekt_ved_id'=>$g2['komplekt_ved_id']
					 ));
					 
					 $pos_params['komplekt_ved_id']= $test_komplekt['id'];
					 
				}
				$pos_params['dimension']=SecStr($pos_params['dimension']);
				$pos_params['name']=SecStr($pos_params['name']);
				
				$api_id=$_api->Add($pos_params);
				
				//перенесем +/-
				 
				$sql3='select * from acceptance_position_pm where acceptance_position_id="'.$g2['id'].'"';
				$set3=new mysqlset($sql3);
				$rs3=$set3->getResult();
				$rc3=$set3->getResultNumRows();
				
				for($i3=0; $i3<$rc3; $i3++){
					$g3=mysqli_fetch_array($rs3, MYSQL_ASSOC);	
					
					$pos_params=$g3;
					unset($pos_params['id']);
					$pos_params['acceptance_position_id']=$bpi_id;
					$_apmi->Add($pos_params);
				
				}
				 
			}
			
			
			
			
			//контроль утверждения
			//если утверждены все родительские заявки, а также утвержден родительский счет - то также утвердить и поступление!
			$can_confirm=true;
			$can_confirm=$can_confirm&&($new_bill['is_confirmed_shipping']==1);
			
			//var_dump($can_confirm);
			
			foreach($new_kvs as $k=>$v) if($v['is_leading']==0) $can_confirm=$can_confirm&&($v['is_active']==1);
			if($can_confirm){
				
				$params=array();
				$params['is_confirmed']=1;
				$params['confirm_pdate']=time();
				$params['user_confirm_id']=$this->auth_result['id'];	
				
				$_acc_in->Edit($this->new_acc_id, $params, true, $this->auth_result);
				
				//примечания, запись в журнале
				
				//запись о создании поступления
				$log->PutEntry($this->auth_result['id'],'автоматическое утверждение поступления при утверждении реализации',NULL,671,NULL,SecStr('поступление № '.$this->new_acc_id.' автоматически утверждено в организации '.$new_opf['name'].' '.$new_org['full_name'].' при утверждении реализации № '.$our_acc['id'].' в организации '.$opf['name'].' '.$org['full_name']),$this->new_acc_id);
					
					
				//дописать в новую поступление примечания о копировании из старой
				$notes_params=array();
				$notes_params['pdate']=time();
				$notes_params['user_id']=$this->new_acc_id;
				$notes_params['note']=SecStr('поступление № '.$this->new_acc_id.' автоматически утверждено в организации '.$new_opf['name'].' '.$new_org['full_name'].' при утверждении реализации № '.$our_acc['id'].' в организации '.$opf['name'].' '.$org['full_name']);
				$notes_params['posted_user_id']=-1;
				$notes_params['is_auto']=1;
				$_ani->Add($notes_params);
				
				
				//если утверждаем автоматом - то проверить наличие связанной реализации
				//если она есть - то выслать сообщение пол-лю об ее утверждении
				//иначе - об ее создании
				$sql='select p.*, s.name from acceptance as p
				   left join document_status as s on p.status_id=s.id
				   where p.is_incoming=0 and p.bill_id in(select out_bill_id from acceptance where id="'.$this->new_acc_id.'")';
				$set=new mysqlset($sql);
				$rs=$set->GetResult();
				$rc=$set->GetResultNumRows();
				
				$nested_accs=array(); 
				for($i=0; $i<$rc; $i++){
					$f=mysqli_fetch_array($rs);
					$nested_accs[]='№ '.$f['id'].', заданный № '.$f['given_no'].' от '.date('d.m.Y',$f['given_pdate']).', статус '.$f['name'];
				}
				if(count($nested_accs)==0){
					//echo 'нет реализаций';		
				}else{
					//echo 'есть реализации: '.implode(', <br>',$nested_accs);
					$_mi=new MessageItem;
					
					$params1=array();
					$message='<div><em>Данное сообщение сгенерировано автоматически.</em></div>
								  <div><br /></div>
								  <div>Уважаемый/ая '.stripslashes($this->auth_result['name_s']).'!</div>
<div><br /></div>		
<div>При утверждении Вами реализации № '.$our_acc['id'].' в базе организации '.$opf['name'].' '.$org['full_name'].' было автоматически утверждено связанное поступление № '.$this->new_acc_id.' в базе организации '.$new_opf['name'].' '.$new_org['full_name'].'.</div>
<div><br /></div>
<div>В базе организации '.$new_opf['name'].' '.$new_org['full_name'].' имеются следующие связанные с этим поступлением реализации:<br />'.implode(', <br>',$nested_accs).'.</div>
<div><br /></div>
<div>Напоминаем Вам о необходимости вручную утвердить необходимые реализации в базе организации '.$new_opf['name'].' '.$new_org['full_name'].'! </div>
<div>С уважением, программа &laquo;'.SITETITLE.'&raquo;.</div>
								  
								  
								  ';
								
						
					$params1['topic']='Напоминание: утвердите связанные реализации';
					$params1['txt']=$message;
					$params1['to_id']= $this->auth_result['id'];
					$params1['from_id']=-1; //Автоматическая система рассылки сообщений
					$params1['pdate']=time();
					$_mi->Send(0,0,$params1,false);	
					
				}
			}
				
			
			
		}// of do_it
		
		/* echo '<pre>';
		var_dump($new_acc);
	 
		echo '</pre>';*/
		 
		return implode(', ',$ret_array); //пока пустой массив!
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