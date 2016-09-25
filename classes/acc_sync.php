<?
require_once('abstractitem.php');
 
require_once('docstatusitem.php');

require_once('trust_group.php');
 
require_once('acc_group.php');
require_once('paygroup.php');
require_once('wfgroup.php');

require_once('acc_item.php');
require_once('wfitem.php');


require_once('actionlog.php');
require_once('authuser.php');


require_once('billitem.php');
//require_once('sh_i_item.php');
require_once('acc_item.php');
//require_once('ispositem.php');
require_once('billpospmformer.php');
//require_once('isposgroup.php');
//require_once('is_custom_item.php');
//require_once('iswfpositem.php');

/*require_once('iswfposgroup.php');
require_once('iswf_group.php');
require_once('komplgroup.php');
require_once('komplitem.php');*/
require_once('maxformer.php');
require_once('authuser.php');
require_once('billcreator.php');
require_once('bdetailsitem.php');
require_once('actionlog.php');
require_once('billgroup.php');
require_once('invitem.php');

require_once('billnotesitem.php');
//require_once('sh_i_notesitem.php');
require_once('acc_notesitem.php');
//require_once('isnotesitem.php');


require_once('billpositem.php');
 
require_once('acc_positem.php');
//require_once('ispositem.php');
//require_once('isitem.php');
require_once('wfitem.php');


//синхронизация списаний по межскладу
class AccSync{
	
	
	public function PutChanges($id,$item,$result=NULL){
			$_wi=new WfItem;
			
				$log=new ActionLog();
		$au=new AuthUser;
		if($result===NULL) $result=$au->Auth();
		$_stat=new DocStatusItem;
		$_wi=new WfItem;
		$_is=new IsItem;
		
		$_wfpg=new IsPosGroup; $_wfp=new IsPosItem;
		$is=$_is->GetItemById($item['interstore_id']);
		
					
					$no_lacks=$this->CheckLack($id,$lss,$lacks,'fact_quantity');
					if(!$no_lacks){
						
						$check_wf=$_wi->GetItemByFields(array('interstore_id'=>$item['interstore_id']));
						
						$wf_params=array();
						$wf_params['sender_storage_id']=$is['sender_storage_id'];
						$wf_params['sender_sector_id']=$is['sender_sector_id'];
						
						$wf_params['is_or_writeoff']=1;
						$wf_params['pdate']=time();
						$wf_params['org_id']=$result['org_id'];
						$wf_params['manager_id']=$result['id'];
						$wf_params['status_id']=2;
						$wf_params['is_confirmed_fill_wf']=1;
						$wf_params['confirm_fill_wf_pdate']=time();
						$wf_params['user_confirm_fill_wf_id']=$result['id'];
						$wf_params['interstore_id']=$item['interstore_id'];
						$wf_params['is_j']=1;
						
						if($check_wf===false){
							//акта нет - создадим его
							$wf_id=$_wi->Add($wf_params);
							
							//автом. примечания, записи в журнал
							$log->PutEntry($result['id'],'автоматическое создание акта списания  недостачи в связи с утверждением списания по межскладу',NULL,105,NULL,NULL,$wf_id);
						   //внести примечание
							$_ni=new IsNotesItem;
							$_ni->Add(array(
								'user_id'=>$wf_id,
								'posted_user_id'=>$result['id'],
								'note'=>'Автоматическое примечание: документ был автоматически создан, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', были обнаружен недостачи по позициям.',
								'is_auto'=>1,
								'pdate'=>time()
							));	
							
						}else{
							$wf_id=$check_wf['id'];
							//акт есть - восстановим его
							$_wi->Edit($wf_id, $wf_params);
							//автом. примечания, записи в журнал	
							$log->PutEntry($result['id'],'автоматическое редактирование акта списания  недостачи в связи с утверждением списания по межскладу',NULL,106,NULL,NULL,$wf_id);
							
							$_ni=new IsNotesItem;
							$_ni->Add(array(
								'user_id'=>$wf_id,
								'posted_user_id'=>$result['id'],
								'note'=>'Автоматическое примечание: документ был автоматически восстановлен/отредактирован, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', были обнаружен недостачи по позициям.',
								'is_auto'=>1,
								'pdate'=>time()
							));	
							
						}
						
						//синхронизация позиций полученного акта с нашей недостачей...
						//перебираем позиции акта... если такие есть в недостаче - ставим кол-ва как в недостаче
						//если таких нет в недостаче - удаляем.
						//перебираем позиции недостачи
						//если в недостаче есть позиции, каких нет в акте - добавим.
					
						$wfpositions=$_wfpg->GetItemsByIdArr($wf_id);
						foreach($wfpositions as $wk=>$wv){
							$in_lack=false; $lack_index=0;
							foreach($lacks as $kk=>$vv){
								if($vv['id']==$wv['id']){
									$in_lack=true; $lack_index=$kk;
									break;	
								}
							}
							
							if($in_lack){
								$_wfp->Edit($wv['p_id'],array('quantity'=>$lacks[$lack_index]['lack']));	
								//автом. примечания, записи в журнал
								if($wv['quantity']!=$lacks[$lack_index]['lack']){
								  $descr=SecStr($wv['position_name']).', кол-во '.$lacks[$lack_index]['lack'].' '.SecStr($wv['dim_name']);
								  $log->PutEntry($result['id'],'автоматическое редактирование позиции акта списания недостачи в связи с утверждением списания по межскладу',NULL,257,NULL,$descr,$wf_id);
							  
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$wf_id,
									  'posted_user_id'=>$result['id'],
									  'note'=>'Автоматическое примечание: позиция '.$descr.' отредактирована, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', была обнаружена недостача по данной позиции.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));		
								}
							}else{
								$_wfp->Del($wv['p_id']);
								//автом. примечания, записи в журнал
								
								 $descr=SecStr($wv['position_name']).', кол-во '.$wv['quantity'].' '.SecStr($wv['dim_name']);
								 $log->PutEntry($result['id'],'автоматическое удаление позиции акта списания недостачи в связи с утверждением списания по межскладу',NULL,258,NULL,$descr,$wf_id);
								 //внести примечание
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$wf_id,
									  'posted_user_id'=>$result['id'],
									  'note'=>'Автоматическое примечание: позиция '.$descr.' удалена, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по данной позиции не обнаружено.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));		
							}
						}
						//если в недостаче есть позиции, каких нет в акте - добавим.
						foreach($lacks as $kk=>$vv){
							$in_wf=false; $wf_index=0;
							foreach($wfpositions as $kk=>$vv){
								if($vv['id']==$wv['id']){
									$in_wf=true; $wf_index=$kk;
									break;	
								}
							}
							
							if(!$in_wf){
								$_wfp->Add(array(
									'interstore_id'=>$wf_id,
									'position_id'=>$vv['id'],
									'name'=>SecStr($vv['position_name']),
									'dimension'=>SecStr($vv['dim_name']),
									'quantity'=>$vv['lack']
								));	
								
								//автом. примечания, записи в журнал	
								$descr=SecStr($vv['position_name']).', кол-во '.$vv['lack'].' '.SecStr($wv['dim_name']);
								$log->PutEntry($result['id'],'автоматическое создание позиции акта списания недостачи в связи с утверждением списания по межскладу',NULL,257,NULL,$descr,$wf_id);
							
								$_ni=new IsNotesItem;
								$_ni->Add(array(
									'user_id'=>$wf_id,
									'posted_user_id'=>$result['id'],
									'note'=>'Автоматическое примечание: позиция '.$descr.' добавлена, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', была обнаружена недостача по данной позиции.',
									'is_auto'=>1,
									'pdate'=>time()
								));		
							}
						}
						
						//разослать сообщения всем сотрудникам ОС
						$usql='select u.* from user as u where u.is_active=1 and u.is_supply_user=1 and u.id in(select distinct user_id from supplier_to_user where org_id="'.$result['org_id'].'")';
						
						$uset=new mysqlSet($usql);
						$urs=$uset->GetResult();
						$urc=$uset->GetResultNumRows();
						$_mi=new MessageItem;
						
						$_ui=new UserSItem;
						$_si=new SectorItem;
						$_sti=new StorageItem;
						$ssi=$_si->GetItemById($is['sender_sector_id']);
						$ssti=$_sti->getitembyid($is['sender_storage_id']);
						
						$rsi=$_si->GetItemById($is['receiver_sector_id']);
						$rsti=$_sti->getitembyid($is['receiver_storage_id']);
						
						
						$ui=$_ui->getitembyid($is['user_confirm_wf_id']);
						
						$message_text='<div><em>Данное сообщение сгенерировано автоматически.</em></div>
						<div><strong>Внимание! Кража продукции!</strong></div>
						<br />
						<div>При утверждении списания по межскладу № '.$item['interstore_id'].' от '.date('d.m.Y', $item['pdate']).' пользователем '.SecStr($ui['name_s']).' (логин '.SecStr($ui['login']).') 
						с участка '.SecStr($ssi['name']).', объекта '.SecStr($ssti['name']).' на участок '.SecStr($rsi['name']).', объект '.SecStr($rsti['name']).'
						были приняты следующие количества:</div>
						';
						
						$message_text.='
						<br />';
						
						$ispositions=$_is->GetPositionsArr($item['interstore_id']);
						foreach($ispositions as $pk=>$pv){
							$message_text.='<div>'.sprintf("%05d", $pv['id']).' '.SecStr($pv['position_name']).' '.SecStr($pv['quantity']).' '.SecStr($pv['dim_name']).'</div>';	
							
						}
						
						$message_text.='
						<br />';
						
						$message_text.='<div>Недостача при списании с участка '.SecStr($ssi['name']).', объекта '.SecStr($ssti['name']).':</div>';
						
						$test_new_pos=$_is->GetPositionsArr($wf_id);
						foreach($test_new_pos as $pk=>$pv){
							$message_text.='<div>'.sprintf("%05d", $pv['id']).' '.SecStr($pv['position_name']).' '.SecStr($pv['quantity']).' '.SecStr($pv['dim_name']).'</div>';	
							
						}
						
						$message_text.='
						<br />
						<div>Был автоматически заведен акт на списание недостачи № '.$wf_id.'.</div>';
						
						$message_text.='
						<br />
						
						
						<div>При утверждении списания по межскладу сотрудник  '.SecStr($ui['name_s']).' (логин '.SecStr($ui['login']).') был предупрежден.</div>
						<div>Пожалуйста, примите меры для расследования данной недостачи.</div>
						';
						
						for($ui=0; $ui<$urc; $ui++){
							$uf=mysqli_fetch_array($urs);
							
							
							
							$params1=array();
							$params1['topic']='Внимание! Кража продукции!';
							$params1['txt']=$message_text;
							$params1['to_id']= $uf['id'];
							$params1['from_id']=-1;
							$params1['pdate']=time();	
							$_mi->Send(0,0,$params1,false);	
							
							//echo $message_text;
						}
						
						
						//конец синхронизации позиций акта списания
						
						//если акт содержит ноль позиций или вообще их не содержит - автоматически его аннулируем
						
						$in_act_count=0;
						foreach($test_new_pos as $tk=>$tv){
							$in_act_count+=$tv['quantity'];	
						}
						if($in_act_count==0){
							//аннулируем акт.
							$check_wf=$_wi->GetItemById($wf_id);
							if(($check_wf!==false)&&($check_wf['status_id']<>3)){
								 //снимаем утв. 2
								 if($check_wf['is_confirmed']==1){
									 
									 $log->PutEntry($result['id'],'автоматическое снятие утверждение списания акта списания',NULL,265,NULL,NULL,$wf_id);
									 //внести примечание
									  $_ni=new IsNotesItem;
									  $_ni->Add(array(
										  'user_id'=>$wf_id,
										  'posted_user_id'=>$result['id'],
										  'note'=>'Автоматическое примечание: утверждение списания документа было автоматически снято, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по всем позициям не обнаружено.',
										  'is_auto'=>1,
										  'pdate'=>time()
									  ));	
								 }
								
								//снимаем утв. 1
								if($check_wf['is_confirmed_fill_wf']==1){
									 
									 $log->PutEntry($result['id'],'автоматическое снятие утверждение заполнения акта списания',NULL,263,NULL,NULL,$wf_id);
									 //внести примечание
									  $_ni=new IsNotesItem;
									  $_ni->Add(array(
										  'user_id'=>$wf_id,
										  'posted_user_id'=>$result['id'],
										  'note'=>'Автоматическое примечание: утверждение заполнения документа было автоматически снято, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по всем позициям не обнаружено.',
										  'is_auto'=>1,
										  'pdate'=>time()
									  ));	
								 }
							
								//аннулируем акт
								
								 $_wi->Edit($wf_id, array(
									'status_id'=>3,
									'is_confirmed_fill_wf'=>0,
									'confirm_fill_wf_pdate'=>time(),
									'user_confirm_fill_wf_id'=>$result['id'],
									'is_confirmed'=>0,
									'confirm_pdate'=>time(),
									'user_confirm_id'=>$result['id']								
								));
								
								$log->PutEntry($result['id'],'автоматическое аннулирование акта списания',NULL,107,NULL,NULL,$wf_id);
							   //внести примечание
								$_ni=new IsNotesItem;
								$_ni->Add(array(
									'user_id'=>$wf_id,
									'posted_user_id'=>$result['id'],
									'note'=>'Автоматическое примечание: документ был автоматически аннулирован, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по всем позициям не обнаружено.',
									'is_auto'=>1,
									'pdate'=>time()
								));
							}
						}
						
					}else{
						//недостач не найдено, проверим наличие акта и аннулируем его
						
						//
						$check_wf=$_wi->GetItemByFields(array('interstore_id'=>$item['interstore_id']));
						if(($check_wf!==false)&&($check_wf['status_id']<>3)){
							//echo 'akt';
							
							//аннулируем акт
							 
							 //очистим позиции акта
							 $wfpositions=$_wfpg->GetItemsByIdArr($check_wf['id']);
							 foreach($wfpositions as $wk=>$wv){
								 $_wfp->Del($wv['p_id']);
								 //записи в журнал, автоматические примечания...
								 $descr=SecStr($wv['position_name']).', кол-во '.$wv['quantity'].' '.SecStr($wv['dim_name']);
								 $log->PutEntry($result['id'],'автоматическое удаление позиции акта списания недостачи в связи с утверждением списания по межскладу',NULL,258,NULL,$descr,$check_wf['id']);
								 //внести примечание
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$check_wf['id'],
									  'posted_user_id'=>$result['id'],
									  'note'=>'Автоматическое примечание: позиция '.$descr.' удалена, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по данной позиции не обнаружено.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));	
								 
							 }
							 
							 //снимаем утв. 2
							 if($check_wf['is_confirmed']==1){
								 
								 $log->PutEntry($result['id'],'автоматическое снятие утверждение списания акта списания',NULL,265,NULL,NULL,$check_wf['id']);
								 //внести примечание
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$check_wf['id'],
									  'posted_user_id'=>$result['id'],
									  'note'=>'Автоматическое примечание: утверждение списания документа было автоматически снято, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по всем позициям не обнаружено.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));	
							 }
							
							//снимаем утв. 1
							if($check_wf['is_confirmed_fill_wf']==1){
								 
								 $log->PutEntry($result['id'],'автоматическое снятие утверждение заполнения акта списания',NULL,263,NULL,NULL,$check_wf['id']);
								 //внести примечание
								  $_ni=new IsNotesItem;
								  $_ni->Add(array(
									  'user_id'=>$check_wf['id'],
									  'posted_user_id'=>$result['id'],
									  'note'=>'Автоматическое примечание: утверждение заполнения документа было автоматически снято, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по всем позициям не обнаружено.',
									  'is_auto'=>1,
									  'pdate'=>time()
								  ));	
							 }
							 
							 $_wi->Edit($check_wf['id'], array(
								'status_id'=>3,
								'is_confirmed_fill_wf'=>0,
								'confirm_fill_wf_pdate'=>time(),
								'user_confirm_fill_wf_id'=>$result['id'],
								'is_confirmed'=>0,
								'confirm_pdate'=>time(),
								'user_confirm_id'=>$result['id']								
							));
							
							$log->PutEntry($result['id'],'автоматическое аннулирование акта списания',NULL,107,NULL,NULL,$check_wf['id']);
						   //внести примечание
							$_ni=new IsNotesItem;
							$_ni->Add(array(
								'user_id'=>$check_wf['id'],
								'posted_user_id'=>$result['id'],
								'note'=>'Автоматическое примечание: документ был автоматически аннулирован, причина: пользователем '.SecStr($result['name_s']).' ('.$result['login'].') было утверждено списание по межскладу №'.$item['interstore_id'].', недостачи по всем позициям не обнаружено.',
								'is_auto'=>1,
								'pdate'=>time()
							));	
							
							
							
							
						}
						//echo 'zzzz'; die();
						
					
					}
				
						//пройти позиции поступления
						//внести позиции в поле quantity в межскладе - чтобы списано было столько, сколько привезли
						$sql='select ap.*, sum(ap.quantity) as s_q from acceptance_position as ap 
						where ap.acceptance_id="'.$id.'" group by ap.position_id';
						$set=new mysqlSet($sql);
						$rs=$set->getresult();
						$rc=$set->getresultnumrows();
						for($i=0; $i<$rc; $i++){
							$f=mysqli_fetch_array($rs);
							$twfp=$_wfp->GetItemByFields(array('interstore_id'=>$item['interstore_id'],'position_id'=>$f['position_id']));
							
							//var_dump($f);
							if($twfp!==false) $_wfp->Edit($twfp['id'], array('quantity'=>$f['s_q']),NULL,false);	
							
						}	
			
			
			//die();
				
	}
	
	
	
	
	//проверка недостачи...
	public function CheckLack($id, &$rss, &$lack, $field_name='quantity'){
		$res=true;
		$rss='';
		$lack=array();
		$_is=new IsItem;
		$_acc=new AccItem;
		$acc=$_acc->GetItemById($id);
		
		$is_positions=$_is->GetPositionsArr($acc['interstore_id']);
		$acc_positions=$_acc->GetPositionsArr($id);
		
		foreach($is_positions as $k=>$v){
			
			/*$index=$this->IsInPos($v['id'], $acc_positions, 'id');
			
			if($index==-1){
				$delta=round($v[$field_name],3);
			}else{
				$delta=round(round($v[$field_name],3)-round($acc_positions[$index]['quantity'],3),3);
			}
			*/
			
			//echo " $qua ";
			$qua=$this->SummInPos($v['id'], $acc_positions, 'id', $not_in_pos);
			if($not_in_pos){
				$delta=round($v[$field_name],3);
			}else{
				$delta=round(round($v[$field_name],3)-round($qua,3),3);
			}
			
			if($delta>0){
				$res=$res&&false;
				
				$rss.='по позиции '.sprintf("%05d",$v['id']).' '.$v['position_name'].' недостача составляет '.$delta.' '.$v['dim_name']."\n";
				$lack[]=array(
					'id'=>$v['id'],
					'position_name'=>$v['position_name'],
					'dim_name'=>$v['dim_name'],
					'lack'=>$delta,
					'dimension_id'=>$v['dimension_id']
				);
			}
		}
		
		
		return $res;
	}
	
	
	
	
	
	//проверка, есть ли такая позиция в массиве
	protected function IsInPos($position_id, $haystack, $keyname='position_id'){
		$res=-1;
		
		foreach($haystack as $k=>$v){
			if($v[$keyname]==$position_id){
				$res=$k;
				break;	
			}
		
		}
		return $res;
	}
	
	protected function SummInPos($position_id, $haystack, $keyname='position_id', &$not_in_pos){
		$res=0;
		$not_in_pos=true;
		foreach($haystack as $k=>$v){
			if($v[$keyname]==$position_id){
				$res+=$v['quantity'];
				//break;	
				$not_in_pos=false;
			}
		
		}
		return $res;
	}
}
?>