<?

 

//����������� �������
class RLRightItem extends AbstractItem{
	
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