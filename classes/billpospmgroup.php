<?
require_once('abstractgroup.php');

// ����������� ������
class BillPosPMGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='bill_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='bill_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>