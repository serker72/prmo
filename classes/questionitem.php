<?
require_once('abstractitem.php');

//����������� �������
class QuestionItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='question';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>