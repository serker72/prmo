<?

require_once('abstractgroup.php');

// ����������� ������
class IsToKGroup extends AbstractGroup {
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='interstore_to_komplekt';
		$this->pagename='view.php';		
		$this->subkeyname='interstore_id';	
		$this->vis_name='interstore_id';		
		
		
		
	}
	
	
}
?>