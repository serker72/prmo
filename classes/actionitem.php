<?

require_once('abstractitem.php');

//����������� �������
class ActionItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='action_log';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>