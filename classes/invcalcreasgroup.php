<?
require_once('abstractgroup.php');


// ����������� ������
class InvCalcReasGroup extends AbstractGroup {
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='invcalc_reasons';
		$this->pagename='invent.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
}
?>