<?
require_once('abstractitem.php');

//����������� �������
class BillPosPMItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bill_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='bill_position_id';	
	}
	
	
	//�������
	public function Del($id){
		
		
		
		parent::Del($id);
	}	
	
	
	
}
?>