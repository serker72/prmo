<?
require_once('abstractitem.php');

//����������� �������
class ObjectItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='object';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>