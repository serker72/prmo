<?
require_once('abstractfileitem.php');

//����������� ����
class ContractOrgItem extends AbstractFileItem{
	
	
	
	public function __construct($id=3){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='contract_file';
		$this->item=NULL;
		$this->pagename='contracts_org.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/contracts/';	
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
	
	
}
?>