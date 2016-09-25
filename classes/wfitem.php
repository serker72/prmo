<?
require_once('billitem.php');
require_once('ispositem.php');
require_once('billpospmformer.php');
require_once('isposgroup.php');
require_once('is_custom_item.php');
require_once('period_checker.php');

//абстрактный элемент
class WfItem extends IsCustomItem{
	protected $is_or_writeoff;
	
	public function __construct($is_or_writeoff=1){
		$this->init($is_or_writeoff);
	}
	
	
	//установка всех имен
	protected function init($is_or_writeoff=1){
		$this->tablename='interstore';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		$this->is_or_writeoff=$is_or_writeoff;	
	}
	
	public function Edit($id,$params,$scan_status=false){
		$item=$this->GetItemById($id);
		
		
		//мы устанавливаем утверждение 1 гал.
		if(isset($params['is_confirmed_fill_wf'])&&($params['is_confirmed_fill_wf']==1)&&($item['is_confirmed_fill_wf']==0)){
			$params['restore_pdate']=0;	
		}
		
		if(isset($params['status_id'])&&($params['status_id']!=3)&&($item['status_id']==3)){
			$params['restore_pdate']=time();	
		}
		
		
		AbstractItem::Edit($id, $params);
		
		
		
		if($scan_status) $this->ScanDocStatus($id,$item,$params);
		
	}
	
	
	//проверка и автосмена статуса (1-2)
	public function ScanDocStatus($id, $old_params, $new_params){
		$log=new ActionLog();
			$au=new AuthUser;
			$_result=$au->Auth();
			$_stat=new DocStatusItem;
			$item=$this->GetItemById($id);
		
		if(isset($new_params['is_confirmed_fill_wf'])&&isset($old_params['is_confirmed_fill_wf'])){
			
			
			
			if(($new_params['is_confirmed_fill_wf']==1)&&($old_params['is_confirmed_fill_wf']==0)&&($old_params['status_id']==1)){
				//смена статуса с 1 на 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на списание',NULL,106,NULL,'установлен статус '.$stat['name'],$id);
				
			}elseif(($new_params['is_confirmed_fill_wf']==0)&&($old_params['is_confirmed_fill_wf']==1)&&($old_params['status_id']==2)){
				$this->Edit($id,array('status_id'=>1));
				
				$stat=$_stat->GetItemById(1);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на списание',NULL,106,NULL,'установлен статус '.$stat['name'],$id);
			}
		}
		
		
		if(isset($new_params['is_confirmed'])&&isset($old_params['is_confirmed'])){
			
			if(($new_params['is_confirmed']==1)&&($old_params['is_confirmed']==0)&&($old_params['status_id']==2)){
				//смена статуса с 2 на 17
				$this->Edit($id,array('status_id'=>17));
				
				$stat=$_stat->GetItemById(17);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на списание',NULL,106,NULL,'установлен статус '.$stat['name'],$item['id']);
				
			}elseif(($new_params['is_confirmed']==0)&&($old_params['is_confirmed']==1)&&(($old_params['status_id']==17))){
				//17 => 2
				$this->Edit($id,array('status_id'=>2));
				
				$stat=$_stat->GetItemById(2);
				$log->PutEntry($_result['id'],'смена статуса распоряжения на списание',NULL,106,NULL,'установлен статус '.$stat['name'],$item['id']);
			}	
			
		}
	}
	
	//запрос о возможности аннулирования и возвращение причины, почему нельзя аннулировать
	public function DocCanAnnul($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=1){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
		}
		
		if($item['is_j']!=0){
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='акт создан автоматически по наличию недостачи по межскладу № '.$item['interstore_id'].', аннулирование акта произойдет автоматически при устранении недостачи по межскладу № '.$item['interstore_id'].'';
		}
		
		$reason=implode(', ',$reasons);
		return $can;
	}
	
	
	//запрос о возможности восстановления и возвращение причины, почему нельзя восстановить
	public function DocCanRestore($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		if($item['status_id']!=3){
			
			$can=$can&&false;
			$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='статус документа: '.$dsi['name'];
			$reason.=implode(', ',$reasons);
		}
		
		if($item['is_j']!=0){
			$can=$can&&false;
			//$dsi=$_dsi->GetItemById($item['status_id']);
			$reasons[]='акт был аннулирован автоматически при устранении недостачи по межскладу № '.$item['interstore_id'].', восстановление акта произойдет автоматически при появлении недостачи по межскладу № '.$item['interstore_id'].'';
			$reason.=implode(', ',$reasons);
		}
		
		return $can;
	}
	
	
	//аннулирование документа
	public function DocAnnul($id){
		if($this->DocCanAnnul($id,$rz)){
			$this->Edit($id, array('status_id'=>3));	
		}
	}
	
	//запрос о возможности утверждения заполнения и возвращ причины, почему нельзя утвердить
	public function DocCanConfirmFillWf($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_fill_wf']==1){
			
			$can=$can&&false;
			
			$reasons[]='заполнение утверждено';
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на списание '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//возможность утв. списание!!!!
	public function DocCanConfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed']==1){
			
			$can=$can&&false;
			
			$reasons[]='списание утверждено';
		}
		
		if($item['is_confirmed_fill_wf']==0){
			
			$can=$can&&false;
			
			$reasons[]='заполнение не утверждено';
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на списание '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//запрос о возможности Неутверждения заполнения и возвращ причины, почему нельзя утвердить
	public function DocCanUnConfirmFillWf($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		if($item['is_confirmed_fill_wf']==0){
			
			$can=$can&&false;
			
			$reasons[]='заполнение не утверждено';
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на списание '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
	
	
	//возможность НЕутв. списание!!!!
	public function DocCanUnConfirm($id,&$reason,$item=NULL){
		$can=true;	
		$reason=''; $reasons=array();
		if($item===NULL) $item=$this->GetItemById($id);
		$_dsi=new DocStatusItem;
		$_pch=new PeriodChecker;
		
		
		if($item['is_confirmed']==0){
			
			$can=$can&&false;
			
			$reasons[]='списание не утверждено';
		}
		
		
		//контроль закрытого периода 
		if(!$_pch->CheckDateByPeriod($item['pdate'], $item['org_id'],$rss23,$periods)){
			$can=$can&&false;
			$reasons[]='дата создания распоряжения на списание '.$rss23;	
		}
		
		
		$reason=implode(', ',$reasons);
		
		
		return $can;	
	}
}
?>