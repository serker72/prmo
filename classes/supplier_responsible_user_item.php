<?
require_once('abstractitem.php');
 


require_once('authuser.php');
require_once('actionlog.php');
 

//����������� �������
class SupplierResponsibleUserItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='supplier_responsible_user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
		
	}
	
	 
	
}
?>