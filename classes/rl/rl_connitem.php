<?



//������ ����� ����� object_id, rl_object_id
class RLConnItem extends AbstractItem{
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='rl_connections';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
}
?>