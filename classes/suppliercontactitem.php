<?
require_once('abstractitem.php');
require_once('delivery_user_sync.php');


//������� ��������
class SupplierContactItem extends AbstractItem{
	
	
	//��������� ���� ����
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
	
	
	//�������
	public function Del($id){
                // KSK - 24.03.2016
		// ��������� �������� ������� �� ������� supplier_contact_data
		//$query = 'delete from supplier_contact_data where contact_id='.$id.';';
		//$it=new nonSet($query);
		
		DeliveryUserSync::Put(array(array('action'=>2, 'tablename'=>'supplier_contact', 'key'=>$id)));
		
		
                // KSK - 24.03.2016
		// ��������� �������� ������ �� ������� supplier_contact
		//parent::Del($id);
                
                // KSK - 24.03.2016
                // ��������� ���� is_shown=0 ��� ���������� ������ � ������� supplier_contact
		$query = 'update supplier_contact set is_shown=0 where id='.$id.';';
		$it=new nonSet($query);
                unset($it);
	}	
	
	
}
?>