<?

 

//����������� �������
class RLUserItem extends UserItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>