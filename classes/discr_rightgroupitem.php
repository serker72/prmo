<?

require_once('abstractitem.php');

//����������� �������
class DiscrRightGroupItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='group_rights_template';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>