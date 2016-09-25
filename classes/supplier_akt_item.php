<?
require_once('abstractfileitem.php');

//����������� ����
class Supplier_Akt_Item extends AbstractFileItem{

	
	
	public function __construct($id=4){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='supplier_shema_file';
		$this->item=NULL;
		$this->pagename='supplier_aktsv.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/supplier_akt_file/';	
	}
	
	
	//�������� 
	public function Add($params){
		$params[$this->storage_name]=$this->storage_id;
		
		
		return parent::Add($params);
	}
	
	//�������
	public function Edit($id,$params){
		$params[$this->storage_name]=$this->storage_id;
		
		return parent::Edit($id,$params);
	}
	
	
	//��������� ������� ����� �� ������ �����
	public function GetItemByFields($params){
		$params[$this->storage_name]=$this->storage_id;
		return parent::GetItemByFields($params);
	}
	
	
	
	//�������� ������� ������� ������������ � �����
	public function CheckUserAccess($supplier_id, $user_id, $result=NULL){
		 $au=new AuthUser;
		 if($result===NULL) $result=$au->Auth(false,false);
		 
		 return $this->SupplierCheckUserAccess($supplier_id, $user_id, $result);
		
		 
	}

}
?>