<?
require_once('abstractitem.php');


//����������� �������
class InvCalcReasItem extends AbstractItem{

	
	//��������� ���� ����
	protected function init(){
		$this->tablename='invcalc_reasons';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';
	}
	
	
	
	
}
?>