<?
require_once('abstractgroup.php');

// ����������� ������
class AccPosPMGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='acceptance_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='acceptance_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>