<?

require_once('abstractitem.php');

//����������� �������
class DiscrRightUserItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_rights';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>