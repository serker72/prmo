<?
require_once('abstractgroup.php');


// ����������� ������
class KomplMarkPosGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker_pos';
		$this->pagename='view.php';		
		$this->subkeyname='marker_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
}
?>