<?
require_once('abstractitem.php');

//edinica izmereinya �������a ��������
class PosDimItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='catalog_dimension';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
}
?>