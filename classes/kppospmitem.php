<?
require_once('abstractitem.php');

//����������� �������
class KpPosPMItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='kp_position_id';	
	}
	
	
	//�������
	public function Del($id){
		
		
		
		parent::Del($id);
	}	
	
	
	
}
?>