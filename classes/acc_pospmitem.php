<?
require_once('abstractitem.php');

//����������� �������
class AccPosPMItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='acceptance_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='acceptance_position_id';	
	}
	
	
	
	
}
?>