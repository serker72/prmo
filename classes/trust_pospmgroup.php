<?
require_once('abstractgroup.php');

// ����������� ������
class TrustPosPMGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='trust_position_pm';
		$this->pagename='view.php';		
		$this->subkeyname='trust_position_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
}
?>