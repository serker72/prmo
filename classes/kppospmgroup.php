<?
require_once('abstractgroup.php');

// ����������� ������
class KpPosPMGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='kp_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='kp_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>