<?
require_once('abstractitem.php');
require_once('v2/delivery_lists.class.php');

//класс-синхронизатор изменений в контактах контрагентов, сотрудников с абонентами рассылок
class DeliveryUserSync{
	
	
	//принять изменения и транслировать их в рассылки
	static function Put(array $data){
		/*print_r($data);		
		echo 'zzzzzzzzzzz'; die();*/
		
		foreach($data as $k=>$v){
			
			/*
			v=array('action'
					'tablename'
					'key'
					'field'
					'value'
					
					)
			*/
			
			$key_to_find='user_id';
			$reason='Автоматически отписан от рассылки при удалении данных контакта в справочнике';
			
			$field_to_edit='f';
			
			if($v['action']==2){
				//удаление
				if($v['tablename']=='user'){
					$key_to_find='user_id';
					
				}elseif($v['tablename']=='user_contact_data'){
					$key_to_find='user_contact_data_id';
				}elseif($v['tablename']=='supplier'){
					$key_to_find='supplier_id';
				}elseif($v['tablename']=='supplier_contact'){
					$key_to_find='supplier_contact_id';
				}elseif($v['tablename']=='supplier_contact_data'){
					$key_to_find='supplier_contact_data_id';
				}
				
				$sql='update delivery_user set is_subscribed=0, unsubscribe_way="'.$reason.'", unsubscribe_reason="'.$reason.'" where '.$key_to_find.'="'.$v['key'].'" ';
				new NonSet($sql);
					
			}elseif($v['action']==1){
				//правка
				if(($v['tablename']=='supplier_contact')&&($v['field']=='name')){
					$key_to_find='supplier_contact_id';
					$field_to_edit='f';
						
				}
				elseif(($v['tablename']=='supplier_contact_data')&&($v['field']=='value')){
					$key_to_find='supplier_contact_data_id';
					$field_to_edit='email';
				}
				elseif(($v['tablename']=='user')&&($v['field']=='name_s')){
					$key_to_find='user_id';
					$field_to_edit='f';
				}
				elseif(($v['tablename']=='user_contact_data')&&($v['field']=='value')){
					$key_to_find='user_contact_data_id';
					$field_to_edit='email';
					
				}
				
				
				$sql='update delivery_user set '.$field_to_edit.'="'.$v['value'].'" where '.$key_to_find.'="'.$v['key'].'" ';
				
				new NonSet($sql);
				
				//echo $sql; die();
			}
		}
		
	}
		
	
}

?>