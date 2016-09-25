<?
require_once('abstractgroup.php');
require_once('payitem.php');
require_once('payforbillgroup.php');
require_once('paynotesgroup.php');
require_once('paynotesitem.php');
require_once('billitem.php');
require_once('user_s_item.php');

require_once('abstract_paygroup.php');

// группа исход оплат
class PayGroup extends AbstractPayGroup {
	
	public $prefix='';
	protected $is_incoming=0;
	
	
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='payment';
		$this->pagename='ed_bill_in.php';		
		$this->subkeyname='bill_id';	
		$this->vis_name='is_confirmed';		
		$this->sub_tablename='bill';
		
		$this->_item=new PayItem;
		$this->_notes_group=new PaymentNotesGroup;
		$this->_payforbillgroup=new PayForBillGroup; //???
		$this->_posgroup=new PayForBillGroup;
		
		$this->_bill_item=new BillItem;
		
		$this->_view=new Pay_ViewGroup;
		
		$this->_auth_result=NULL;
	}
	
	
	//механизм ограничениия по к-ту:
	//вх. оплата - прямая
	//исх. оплата - через счета, связанные с к-том
	public function LimitBySupplier($limited_supplier=NULL){
		$txt='';
		
		
		if($limited_supplier!==NULL) {
			//$txt='  p.supplier_id in ('.implode(', ',$limited_supplier).')';
			$txt=' (p.id in(select distinct payment_id from payment_for_bill where bill_id in(select id from bill where is_incoming=1 and supplier_id in('.implode(', ', $limited_supplier).')))   or p.inner_user_id="'.$this->_auth_result['id'].'" )';
		}
		
		return $txt;	
	}
	
	
	
	
	//автоматическое аннулирование
	public function AutoAnnul($days=14, $days_after_restore=14, $annul_status_id=3){
		
		$log=new ActionLog();
		//$au=new AuthUser;
		//$_result=$au->Auth();
		$_stat=new DocStatusItem;
		
		$_ni=new PaymentNotesItem;
		 $_itm=new PayItem;
		
		$set=new MysqlSet('select * from '.$this->tablename.' where is_confirmed=0 and is_incoming="'.$this->is_incoming.'" and status_id<>'.$annul_status_id.' order by id desc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		$now=time();
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
					$reason='прошло более '.$days_after_restore.' дней с даты восстановления оплаты, документ не утвержден';
				}
			}else{
				//работаем с датой создания	
				if(($f['pdate']+$days*24*60*60)>$now){
					$can_annul=false;	
				}else{
					$can_annul=true;
					$reason='прошло более '.$days.' дней с даты создания оплаты, документ не утвержден';
				}
			}
			
			if($can_annul){
				$_itm->Edit($f['id'], array('is_confirmed'=>0, 'status_id'=>$annul_status_id));
				
				$stat=$_stat->GetItemById($annul_status_id);
				
			
				
				$log->PutEntry(0,'автоматическое аннулирование исходящей оплаты',NULL,279,NULL,'№ документа: '.$f['code'].' установлен статус '.$stat['name'],$f['id']);
				
				$_ni->Add(array(
				'user_id'=>$f['id'],
				'is_auto'=>1,
				'pdate'=>time(),
				'posted_user_id'=>0,
				'note'=>'Автоматическое примечание: исходящая оплата была автоматически аннулирована, причина: '.$reason.'.'
				));
					
			}
		}
		
	}
	
}
?>