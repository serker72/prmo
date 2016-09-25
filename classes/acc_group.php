<?

require_once('abstract_acc_group.php');
require_once('billgroup.php');
require_once('billitem.php');

require_once('authuser.php');
require_once('acc_item.php');
require_once('acc_notesgroup.php');
require_once('acc_posgroup.php');
require_once('acc_notesitem.php');
require_once('billpospmformer.php');
require_once('period_checker.php');

// группа реализаций
class AccGroup extends AbstractAccGroup {
	protected $_auth_result;
	
		
	public $prefix='';
	protected $is_incoming=0;
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='acceptance';
		$this->pagename='ed_bill.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_shown';		
		
			$this->_item=new AccItem;
		$this->_notes_group=new AccNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup; //???
		$this->_posgroup=new AccPosGroup;
		
		$this->can_unconfirm_object_id=721;
		$this->can_unconfirm_object_inv_id=241;
		
		$this->_auth_result=NULL;
		
		$this->_view=new Acc_ViewGroup;
		
	}
	
	
	
	
	
	public function CountByShid($sh_i_id, $is_confirmed=0){
		
		$sql='select count(*) from '.$this->tablename.' where sh_i_id="'.$sh_i_id.'" ';
		if($is_confirmed==1) $sql.=' and is_confirmed=1';
		
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$f=mysqli_fetch_array($rs);
		
		$f[0]=(int)$f[0];
		
		return $f[0];
		
		
	}
	
	public function GetByShidArr($sh_i_id, $is_confirmed=0){
		
		$sql='select * from '.$this->tablename.' where sh_i_id="'.$sh_i_id.'" ';
		if($is_confirmed==1) $sql.=' and is_confirmed=1';
		
		$as=new mysqlSet($sql);
		
		$rs=$as->GetResult();
		$rc=$as->GetResultNumRows();
		$alls=array();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$f['pdate']=date("d.m.Y",$f['pdate']);
			//$f['total_cost']=$_bill->CalcCost($f['id']);
			
			$alls[]=$f;
		}
		
		return $alls;
		
		
	}
	
	public function GetByShid($sh_i_id, $is_confirmed=0){
		$acc_list='';
		$_acc_l=$this->GetByShidArr($sh_i_id, $is_confirmed);
		foreach($_acc_l as $k=>$v){
			if(strlen($acc_list)>0) $acc_list.=', ';
			$acc_list.='№ '.$v['id'].' от '.$v['pdate'];	
		}	
		
		return $acc_list;
	}
	
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=30, $days_after_restore=30, $annul_status_id=6){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new AccNotesItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_confirmed=0 and status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time(); $_itm=new AccItem;
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			
			$can_annul=false;
			
			$reason='';
			//проверим дату восстановления
			if($f['restore_pdate']>0){
				if(($f['restore_pdate']+$days_after_restore*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;	
					$reason='прошло более '.$days_after_restore.' дней с даты восстановления реализации, документ не утвержден';
				}
			}else{
				//работаем с датой создания	
				if(($f['pdate']+$days*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='прошло более '.$days.' дней с даты создания реализации, документ не утвержден';
				}
			}
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
				$log->PutEntry(0,'автоматическое аннулирование реализации',NULL,93,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['bill_id']);
				
				$log->PutEntry(0,'автоматическое аннулирование реализации',NULL,219,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['sh_i_id']);
				
				$log->PutEntry(0,'автоматическое аннулирование реализации',NULL,235,NULL,'№ документа: '.$f['id'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: реализация была автоматически аннулирована, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
}
?>