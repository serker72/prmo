<?

require_once('abstractitem.php');

//����������� �������
class SupplierCountryItem extends AbstractItem{

	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='sprav_country';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	

}
?>