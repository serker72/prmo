<?
require_once('abstractgroup.php');


// ����������� ������
class KomplMarkGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker';
		$this->pagename='view.php';		
		$this->subkeyname='komplekt_ved_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	
	
}
?>