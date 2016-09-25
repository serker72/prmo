<?
require_once('abstractfileitem.php');

//����������� ����
class InvCalcFileItem extends AbstractFileItem{
	
	public function __construct($id=4){
		$this->init($id);
	}
	
	//��������� ���� ����
	protected function init($id){
		$this->tablename='invcalc_file';
		$this->item=NULL;
		$this->pagename='invcalc_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='invcalc_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/invcalc/';	
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