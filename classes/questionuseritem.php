<?
require_once('abstractitem.php');

//����������� �������
class QuestionUserItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='question_user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>