<?
require_once('abstractitem.php');


//������ ������
class KomplMarkPosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker_pos';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='marker_id';	
	}
	
	
	
}
?>