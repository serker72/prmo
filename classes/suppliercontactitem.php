<?
require_once('abstractitem.php');
require_once('delivery_user_sync.php');


//элемент каталога
class SupplierContactItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='supplier_contact';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='supplier_id';	
	}
	
	
	public function Edit($id,$params){
		
		 
		$item=$this->GetItemById($id);
		if($item['name']!=$params['name']) DeliveryUserSync::Put(array(array('action'=>1, 'tablename'=>'supplier_contact', 'key'=>$id, 'field'=>'name', 'value'=>$params['name'])));
		
		parent::Edit($id, $params);
	}
	
	
	//удалить
	public function Del($id){
                // KSK - 24.03.2016
		// отключаем удаление записей из таблицы supplier_contact_data
		//$query = 'delete from supplier_contact_data where contact_id='.$id.';';
		//$it=new nonSet($query);
		
		DeliveryUserSync::Put(array(array('action'=>2, 'tablename'=>'supplier_contact', 'key'=>$id)));
		
		
                // KSK - 24.03.2016
		// отключаем удаление записи из таблицы supplier_contact
		//parent::Del($id);
                
                // KSK - 24.03.2016
                // обновляем поле is_shown=0 для скрываемой записи в таблице supplier_contact
		$query = 'update supplier_contact set is_shown=0 where id='.$id.';';
		$it=new nonSet($query);
                unset($it);
	}	
	
	
}
?>