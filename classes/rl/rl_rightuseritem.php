<?

 

//����������� �������
class RLRightUserItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='rl_user_rights';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>