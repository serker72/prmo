<?

require_once('abstractitem.php');

//����������� �������
class DiscrRightItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='rights';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>