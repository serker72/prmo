<?

require_once('abstractitem.php');

//����������� �������
class DiscrGrObjectItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='object_group';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
}
?>