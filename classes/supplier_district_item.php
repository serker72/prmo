<?

require_once('abstractitem.php');

//����������� �������
class SupplierDistrictItem extends AbstractItem{

	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sprav_district';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	

}
?>