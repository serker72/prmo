<?

require_once('abstractgroup.php');
require_once('komplitem.php');
require_once('komplblink.php');
require_once('authuser.php');
require_once('komplnotesgroup.php');
require_once('komplnotesitem.php');
require_once('komplconfgroup.php');

require_once('bill_in_group.php');

require_once('billitem.php');
require_once('bill_in_item.php');
require_once('billnotesitem.php');

require_once('komplekt_view.class.php');

//  группа каталога
class KomplGroup extends AbstractGroup {
	
	protected $_auth_result;
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved';
		$this->pagename='komplekt.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		$this->_auth_result=NULL;
		
		$this->_view=new Komplekt_ViewGroup;
		
	}
	
	
	public function ShowPos(
		$template, 
		DBDecorator $dec,
		$from=0,
		$to_page=ITEMS_PER_PAGE,
		$can_edit=false, 
		$can_delete=false,
		$pdate1=NULL, 
		$pdate2=NULL, 
		$has_header=true, 
		$is_ajax=false, 
		$can_restore=false,
		$limited_sector=NULL, 
		$can_create=false,
		$can_print=false,
		$can_print_full=false,
		$can_re=false,
		$can_re_rub=false,
		$limited_supplier=NULL
	){
		/*echo $dec->GenFltSql(' and ');
		echo $dec->GenFltUri();
		echo $dec->GenFltOrd();*/
		
		$_au=new AuthUser;
		//$_aures=$_au->Auth();
		
		if($this->_auth_result===NULL){
			$_aures=$_au->Auth();
			$this->_auth_result=$_aures;
		}else{
			$_aures=$this->_auth_result;	
		}
		
		$_kov=new KomplItem;
		
		if($is_ajax) $sm=new SmartyAj;
		else $sm=new SmartyAdm;
		
		
		
		$sql='select p.*,
					sup.full_name as supplier_name, opf.name as opf_name,
					se.name as sector_name, se.id as sector_id, se.s_s as sector_s_s,
					mn.id as manager_id, mn.name_s as  manager_name, mn.login as manager_login
				from '.$this->tablename.' as p
					left join supplier as sup on p.supplier_id=sup.id 
					left join opf on opf.id=sup.opf_id
					left join sector as se on p.sector_id=se.id
					left join user as mn on p.manager_id=mn.id
					';
		$sql_count='select count(*)
				from '.$this->tablename.' as p
				 	left join supplier as sup on p.supplier_id=sup.id 
					left join opf on opf.id=sup.opf_id
					left join sector as se on p.sector_id=se.id
					left join user as mn on p.manager_id=mn.id
					';
				 
		$db_flt1=$dec->GenFltSql(' and ');
		/*if(strlen($db_flt)>0){
			$sql.=' where '.$db_flt;
			$sql_count.=' where '.$db_flt;	
		}*/
		
		if($limited_supplier!==NULL) {
			if((strlen($db_flt1)>0)){
				$db_flt1.=' and ';	
			}
			$db_flt1.='  p.supplier_id in ('.implode(', ',$limited_supplier).')';
			
		}
		
		
		//добавим работу с датами
		$date_flt='';
		if(($pdate1!==NULL)&&($pdate2!==NULL)){
			$date_flt='
			(
			(begin_pdate>="'.$pdate1.'" AND end_pdate<="'.$pdate2.'") or
			
			(begin_pdate<"'.$pdate1.'" AND end_pdate<="'.$pdate2.'") or
			(begin_pdate>="'.$pdate1.'" AND end_pdate>"'.$pdate2.'") or
			(begin_pdate<"'.$pdate1.'" AND end_pdate>"'.$pdate2.'")
			
			)
			';	
		}
		
		if((strlen($db_flt1)>0)||(strlen($date_flt)>0)){
			if(strlen($db_flt1)>0){
				$sql.=' where '.$db_flt1;
				$sql_count.=' where '.$db_flt1;		
			}
			
			if(strlen($date_flt)>0){
				if(strlen($db_flt1)>0){
					$sql.=' and '.$date_flt;
					$sql_count.=' and '.$date_flt;
				}else{
					$sql.=' where '.$date_flt;
					$sql_count.=' where '.$date_flt;
				}
			}
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
		
		//echo $dec->GenFltUri();
		
		$alls=array();
		$_bng=new KomplNotesGroup;
		
		$bg=new BillInGroup;
		$bg->SetAuthResult($this->_auth_result); 
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['begin_pdate']=date("d.m.Y",$f['begin_pdate']);
			$f['end_pdate']=date("d.m.Y",$f['end_pdate']);
			
			if($f['pdate']==0) $f['pdate']='-';
			else $f['pdate']=date("d.m.Y",$f['pdate']);
			
			//print_r($f);	
			
			
			$color='black';
			$f['blink']=$_kov->kompl_blink->OverallBlink($f['id'], $f['status_id'], $_aures['id'], $_aures['is_supply_user'], $color,NULL,NULL,$f['sector_s_s'],$f['storage_s_s']);
			$f['color']=$color;
			
			$f['notes']=$_bng->GetItemsByIdArr($f['id'],0,0,false,false,false,0,false,false);
			
			$f['can_annul']=$_kov->DocCanAnnul($f['id'],$reason, $_aures['id'],$f,NULL,NULL,$f['sector_s_s'],$f['storage_s_s'])&&$can_delete;
			if(!$can_delete) $reason='недостаточно прав для данной операции';
			$f['can_annul_reason']=$reason;
			
			$f['binded_to_annul']=$_kov->GetBindedDocumentsToAnnul($f['id'],  $f);
			
			
			//связанные счета
			 
			$decorator_in=new DBDecorator;
			 
			$decorator_in->AddEntry(new SqlEntry('p.org_id',abs((int)$this->_auth_result['org_id']), SqlEntry::E));
			 
			$decorator_in->AddEntry(new SqlEntry('p.id','select distinct bill_id from bill_position where komplekt_ved_id="'.$f['id'].'"', SqlEntry::IN_SQL));
			
			$bg->SetAuthResult($result);
			
			$llg=$bg->ShowPosSimple(
				'bills_in/bills_list_komplekt.html',  //0
				$decorator_in,	//1
				0,	//2
				1000, 	//3
				false, //4
				false, //5
				false,	//6
				'_in_bill', //7
				false,	//8
				false,	//9
				true,	//10
				false,	//11
				false,	//12
				NULL, //13
				$f['id'],	//14
				false,	//15
				false, //16
				false,	//17
				$temp_alls,
				false,
				false,
				false,
				NULL,
				false
				
				);
			  
			$f['bills_in']=$temp_alls;
			
			
			$alls[]=$f;
		}
		
		//заполним шаблон полями
		$current_action='';
		$current_object='';
		$current_group='';
		$current_storage='';
		$current_sector='';
		$fields=$dec->GetUris();
		foreach($fields as $k=>$v){
			/*if($v->GetName()=='description') $current_action=$v->GetValue();
			if($v->GetName()=='object_id') {
				$current_object=$v->GetValue();
				
			}
			if($v->GetName()=='user_group_id') $current_group=$v->GetValue();*/
			//if($v->GetName()=='dimension_id') $current_dimension_id=$v->GetValue();
			if($v->GetName()=='storage_id') $current_storage=$v->GetValue();
			if($v->GetName()=='sector_id') $current_sector=$v->GetValue();
			$sm->assign($v->GetName(),$v->GetValue());	
		}
		
	
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
		$sm->assign('sug',$acts);
		
		
		
		
		
		$sm->assign('pagename',$this->pagename);
		
		$sm->assign('from',$from);
		$sm->assign('to_page',$to_page);
		$sm->assign('pages',$pages);
		$sm->assign('items',$alls);
		
		$sm->assign('can_create',$can_create);
		$sm->assign('can_edit',$can_edit);
		$sm->assign('can_delete',$can_delete);
		
		$sm->assign('can_restore',$can_restore);
		
		$sm->assign('can_print',$can_print);
		$sm->assign('can_print_full',$can_print_full);
		
		$sm->assign('can_re',$can_re);
		$sm->assign('can_re_rub',$can_re_rub);
		
		$sm->assign('has_header',$has_header);
		
		//проверка активности кнопки создания заявки: если доступно для создания заявки хотя бы одно сочетание участка или объекта - кнопка активна
	 	//$sm->assign('can_create_by_sector', $this->CanCreateBySector($_aures['id']));
		
		
		
		//ссылка для кнопок сортировки
		$link=$dec->GenFltUri();
		$link=$this->pagename.'?'.eregi_replace('&sortmode=[[:digit:]]+','',$link);
		$sm->assign('link',$link);
		
		//показ конфигурации
		$sm->assign('view', $this->_view->GetColsArr($this->_auth_result['id']));
		$sm->assign('unview', $this->_view->GetColsUnArr($this->_auth_result['id']));
		
		
		
		return $sm->fetch($template);
	}
	
	public function ShowActiveArr($current_id=0, $org_id=0){
		$arr=Array();
		//$set=new MysqlSet('select * from '.$this->tablename);
		$now=time();
		$set=new MysqlSet('select p.* 
					 
				from '.$this->tablename.' as p
				 
				where p.is_active="1" and (p.begin_pdate<="'.$now.'" and p.end_pdate>="'.$now.'") 	
				and org_id="'.$org_id.'"
				order by p.id asc');
		
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
	
	
	public function CalcNew(array $filter){
		$flt='';
		if(count($filter)==0) {}
		else{
			$flt=' and sector_id in('.implode(', ',$filter).') ';
		}
		
		$sql='select count(*) from '.$this->tablename.' where status_id=11 '.$flt.' ';
		$set=new MysqlSet($sql);
		$rs=$set->GetResult();
		
		$f=mysqli_fetch_array($rs);
		
		//echo 'UUUUUUUUUUUUUUUUUUUUUUUUUU';
		
		//print_r($f);
		
		return (int)$f[0];
		
	}
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=60, $days_after_restore=60, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		 $_itm=new KomplItem; $_cg=new KomplConfGroup;
		
		$_ni=new KomplNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where status_id<>'.$annul_status_id.' and (cannot_an=0 or (cannot_an=1 and is_active=0)) order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			
			
			//проверить наличие связ. документов утв
			
			
			/*$sql1='select count(id) from bill where id in(select distinct bill_id from bill_position where  komplekt_ved_id="'.$f['id'].'") and (is_confirmed_price=1 or is_confirmed_shipping=1)';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			*/
			
		
			
			$sql1='select count(id) from acceptance where is_confirmed=1 and id in(select distinct acceptance_id from acceptance_position where komplekt_ved_id="'.$f['id'].'" ) ';
			$set1=new MysqlSet($sql1);
		
			$rs1=$set1->GetResult();
			$g=mysqli_fetch_array($rs1);
			$has_binded_docs= ((int)$g[0]>0);
			if($has_binded_docs) continue;
			
			
			
		 
			
			//проверим дату восстановления
			/*if($f['restore_pdate']>0){
				if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;	
					$reason='прошло более '.$days_after_restore.' дней с даты восстановления заявки, нет утвержденных связанных документов';
				}
			}else{
				//работаем с датой создания	
				
				
				if(($f['pdate']+$days*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='прошло более '.$days.' дней с даты создания заявки, нет утвержденных связанных документов';
				}
			}*/
		
			//определеить дату последней операции по счетам или заявке
			//что есть дата последней операции???
			//это дата утверждения
			
			$last_dates=array();
			
			
			 
			//последняя дата по заявке - последняя дата утверждения
			$sql2='select pdate from action_log where affected_object_id="'.$f['id'].'" and description="смена статуса заявки" and value="установлен статус утвержден" ';
			
			/*
			$sql2='select pdate from action_log where affected_object_id="'.$f['id'].'" and description not in("открыл карту заявки", "открыл карту заявки: версия для печати") and object_id in(80,
81,
177,
178,
82,
179,
180,
181,
182,
183,
296,
184,
185,
186,
187,
297,
291,
282,
83,
132,
298,
299,
300,
301,
84,
85,
338,
347,
446,
473,
536,
537,
540,
541) order by pdate desc limit 1';*/
			$set2=new mysqlset($sql2);
			$rc2=$set2->getresultnumrows();
			$rs2=$set2->getresult();
			if($rc2>0){
				$h=mysqli_fetch_array($rs2);
				$last_dates[]=$h['pdate'];
			}
			
			
			 
			//последняя дата по вход счету - дата утверждения  
			$sql2='select confirm_price_pdate from bill where confirm_price_pdate<>0 and  status_id<>3 and is_incoming=1 and id in(select distinct bill_id from   bill_position where komplekt_ved_id="'.$f['id'].'") order by confirm_price_pdate desc limit 1';
			
			
			/*$sql2='select pdate from action_log where affected_object_id in(select distinct p.bill_id from bill_position as p inner join bill as b on p.bill_id=b.id where p.komplekt_ved_id="'.$f['id'].'"  and b.status_id<>3 and b.is_incoming=1 ) and description not in("открыл карту входящего счета", "открыл карту входящего счета: версия для печати") and object_id in(606,
607,
608,
609,
610,
611,
612,
613,
614,
615,
616,
617,
618,
619,
620,
621,
622,
623,
624,
625,
626,
627,
628,
629,
630,
631,
632,
633,
634,
635,
636,
637) order by pdate desc limit 1';*/
			$set2=new mysqlset($sql2);
			$rc2=$set2->getresultnumrows();
			$rs2=$set2->getresult();
			if($rc2>0){
				$h=mysqli_fetch_array($rs2);
				$last_dates[]=$h['confirm_price_pdate'];
			}
			 
			 
			
			 
			//последняя дата по исход счету
			$sql2='select confirm_price_pdate from bill where confirm_price_pdate<>0 and status_id<>3 and is_incoming=0 and id in(select distinct bill_id from   bill_position where komplekt_ved_id="'.$f['id'].'") order by confirm_price_pdate desc limit 1';
			
			/*$sql2='select pdate from action_log where affected_object_id in(select distinct p.bill_id from bill_position as p inner join bill as b on p.bill_id=b.id where p.komplekt_ved_id="'.$f['id'].'"  and b.status_id<>3 and b.is_incoming=0) and description not in("открыл карту исходящего счета", "открыл карту исходящего счета: версия для печати") and object_id in(97,
92,
128,
188,
189,
190,
129,
133,
130,
93,
191,
339,
349,
192,
193,
194,
95,
195,
196,
197,
292,
283,
94,
131,
302,
365,
474,
485,
538,
539,
480,
481,
522,
523,
860) order by pdate desc limit 1';*/
			$set2=new mysqlset($sql2);
			$rc2=$set2->getresultnumrows();
			$rs2=$set2->getresult();
			if($rc2>0){
				$h=mysqli_fetch_array($rs2);
				$last_dates[]=$h['confirm_price_pdate'];
			} 
			
			 
			/* print_r($last_dates);
			
			echo 'заявка без поступлений, реализаций №'.$f['id'].' <br>';
			continue;*/
			
			if(count($last_dates)==0){
				/*$can_annul=true;
				//echo 'операций не зафиксировано, аннулировать<br>';
				$reason='операций по заявке и связанным счетам не зафиксировано, нет связанных утвержденных поступлений, реализаций';	*/
				if($f['restore_pdate']>0){
					if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;	
						$reason='прошло более '.$days_after_restore.' дней с даты восстановления заявки, нет утвержденных связанных документов';
					}
				}else{
					//работаем с датой создания	
					
					
					if(($f['pdate']+$days*24*60*60)>$now){
						$can_annul=false;	
					}else{
						$can_annul=true;
						$reason='прошло более '.$days.' дней с даты создания заявки, нет утвержденных связанных документов';
					}
				}
			}else{
				$last_date=max($last_dates);
				//echo 'дата последней операции: '.date('d.m.Y', $last_date).'<br>';	
				
				if(($last_date+$days*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='прошло более '.$days.' дней с даты последней операции: '.date('d.m.Y', $last_date).', нет связанных утвержденных поступлений, реализаций';
				}	
			}
			
			
			
			if($can_annul){
				
				//echo 'заявка без поступлений, реализаций №'.$f['id'].' '.$reason.'<br>';
				
				 $_itm->Edit($f['id'], array('status_id'=>$annul_status_id));
				
				 $_itm->ClearConfirms($f['id']);
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				
				$log->PutEntry(0,'автоматическое аннулирование заявки',NULL,83,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: заявка была автоматически аннулирована, причина: '.$reason.'.'
				));
				
				//автоматически аннулировать связанные счета
				
				$sql1='select * from bill where id in(select distinct bill_id from bill_position where  komplekt_ved_id="'.$f['id'].'") and status_id<>3  ';
				$set1=new MysqlSet($sql1);
		
				$rs1=$set1->GetResult();
				$rc1=$set1->GetResultNumRows();
				
				$_bni=new BillNotesItem;
				
				$stat=$_stat->GetItemById(3);
					
				for($i=0; $i<$rc1; $i++){
					$g=mysqli_fetch_array($rs1);	
					if($g['is_incoming']==1) {
						$_bi=new BillInItem;
						$code=626;
					}else{
						 $_bi=new BillItem;
						 $code=94;
						 
					}
					
					 $_bi->Edit($g['id'], array('is_confirmed_price'=>0, 'is_confirmed_shipping'=>0, 'status_id'=>3));
					
					$log->PutEntry(0,'автоматическое аннулирование счета при автоматическом аннулировании заявки',NULL,$code,NULL,'№ документа: '.$g['code'].' установлен статус '.$stat['name'],$g['id']);
					$_bni->Add(array(
					'user_id'=>$g['id'],
					'is_auto'=>1,
					'pdate'=>time(),
					'posted_user_id'=>0,
					'note'=>'Автоматическое примечание: счет был автоматически аннулирован при автоматическом аннулировании заявки, причина: '.$reason.'.'
					));
					
				}
					
			}
		}
		
	}
	
	
	//автоматическое выравнивание
	public function AutoEq($days=45, $days_no_acc=60){
		$log=new ActionLog();
		$au=new AuthUser;
		$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new KomplNotesItem;
		$_itm=new KomplItem;
		
		/*
		перебрать все заявки в статусе "утв" или "не вып"
		
		*/ 
		 
		
		$now=time();
		
		$sql='select * from komplekt_ved where status_id in(2,12) and cannot_eq=0 order by id desc';
		
		$set=new MysqlSet($sql);
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		
		
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			 
			
			$can_annul=false;
		
			//проверка по завозу: получить последний завоз по заявке и дату поступления по нему
			$checked_time=$now-$days*24*60*60;
			$checked_time_noacc=$now-$days_no_acc*24*60*60;
			
			$sql1='select distinct a.* from acceptance as a 
			inner join acceptance_position as ap on a.id=ap.acceptance_id
			 where 
			 	ap.komplekt_ved_id="'.$f['id'].'" 
				and a.is_confirmed=1  
				and a.is_incoming=0
				order by a.given_pdate desc limit 1';
			$set1=new MysqlSet($sql1);
			$rs1=$set1->GetResult();
			$rc1=$set1->GetResultNumRows();
			
			if($rc1>0){
				$can_annul=true;	
				$g=mysqli_fetch_array($rs1);
				//print_r($g);
			}
			
			
			
			//работать с заявкой, только если есть завоз (неважно какой даты)
			if($can_annul){
				//найти позиции заявки
				
				$posset=new mysqlset('select kv.*, p.name from komplekt_ved_pos as kv inner join catalog_position as p on kv.position_id=p.id where kv.komplekt_ved_id='.$f['id'].'');
				$rs2=$posset->GetResult();
				$rc2=$posset->GetResultNumRows();
				
				
				$was_eqed=false;
				for($j=0; $j<$rc2; $j++){
					$h=mysqli_fetch_array($rs2);
					$args=array();
					$can_annul_position=false;
					//по каждой из позиций - получим связанные поступления
					//если дата последнего поступления (и оно есть) раньше 45 дней - выравн. позицию
					//иначе - проверим дату последнего поступления...
					
					$sql3='select distinct a.* 
					from acceptance as a 
					inner join acceptance_position as ap on a.id=ap.acceptance_id 
					where 
					a.is_confirmed=1   
					and a.is_incoming=0
					and a.sector_id='.$f['sector_id'].' 
					and ap.komplekt_ved_id='.$f['id'].' 
					and ap.position_id='.$h['position_id'].' 
					order by a.given_pdate desc limit 1';
					//echo $sql3.' <br>';
					$acset=new mysqlset($sql3);
					$rs3=$acset->GetResult();
					$rc3=$acset->GetResultNumRows();
					if($rc3>0){
						//завоз есть, проверить дату завоза
						$hh=mysqli_fetch_array($rs3);
						if($hh['given_pdate']<$checked_time){
							$can_annul_position=true;
							//echo 'Позиция '.$h['name'].' подлежит выравниванию, дата последнего поступления '.date('d.m.Y',$hh['given_pdate']).' '.$hh['id'].' по позиции более 45 дней назад<br>';
						}
					}else{
						//нет завоза - проверка по дате последнего завоза
						
						if($g['given_pdate']<$checked_time_noacc){
							$can_annul_position=true;
						//	echo 'Позиция '.$h['name'].' подлежит обнулению, дата последнего поступления '.date('d.m.Y',$g['given_pdate']).' '.$g['id'].' по заявке более 60 дней назад<br>';
						}
						
					}
					
					//если выравниваем...
					if($can_annul_position){
						
						$args[]=$h['position_id'].';'.$h['quantity_confirmed'].';'.$h['storage_id'].';'.$f['sector_id'].';'.$f['id'];
					//	echo 'Выравниваю позицию '.$h['name'].' <br>';
						
						$_itm->DoEq($f['id'],$args,$some_output, 1, $f, $_result,true);
						$was_eqed=$was_eqed||true;
					}else{
					//	echo 'НЕ выравниваю позицию '.$h['name'].' <br>';
					}
				}// of kompl positions
				if($was_eqed){
				//	echo 'Заявка '.$f['id'].' подлежит выравниванию <br><br>';
					$_itm->ScanDocStatus($f['id'],array(),array(),$f,$_result);		
				}
				
			}//of kompl
		}
		 
		
	}
	
	
	
	
	
	
	
	//проверка активности кнопки создания заявки: если доступно для создания заявки хотя бы одно сочетание участка или объекта - кнопка активна
	public function CanCreateBySector($user_id){
		$res=false;
		

		
		//$_extended_limited_sector['pairs'];
		if(is_array($_extended_limited_sector['pairs'])){
			
			/*echo '<pre>';
			var_dump($_extended_limited_sector['pairs']);
			echo '</pre>';
			*/
			//
			$_str=array();
			
			foreach($_extended_limited_sector['pairs'] as $k=>$v){
				$_str[]= ' (sector_id="'.$v[0].'" and storage_id="'.$v[1].'") ';	
			}
			
			$sql='select count(*) from storage_sector where can_make_komplekt=1 and ('.implode(' or ', $_str).')';
			//echo $sql;
			$set=new mysqlset($sql);
			$rs=$set->GetResult();
			$f=mysqli_fetch_array($rs);
			
			if((int)$f[0]>0) $res=true;
		}
		
		return $res;
	}
}
?>