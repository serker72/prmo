<?

require_once('abstractitem.php');

//����������� �������
class DiscrGrUserItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='groups';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
}
?>