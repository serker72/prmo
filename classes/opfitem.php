<?
require_once('abstractitem.php');

//����������� �������
class OpfItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='opf';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>