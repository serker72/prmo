<?

require_once('abstractitem.php');

//����������� �������
class CacheReportsItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='cache_reports';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>