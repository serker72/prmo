<?
require_once('abstractitem.php');

//����������� �������
class TrustPosPMItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='trust_position_pm';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='trust_position_id';	
	}
	
	
	
	
}
?>