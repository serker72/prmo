<?

require_once('abstractitem.php');

//����������� �������
class SupplierRegionItem extends AbstractItem{

	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sprav_region';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	

}
?>