<?
require_once('abstractitem.php');

//����������� �������
class UserIntItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_work_intervals';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';	
	}
	
	
}
?>