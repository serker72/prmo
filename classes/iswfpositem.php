<?
require_once('abstractitem.php');
//require_once('sh_i_pospmitem.php');

//����������� �������
class IsWfPosItem extends AbstractItem{
	
	
	//��������� ���� ����
	protected function init(){
		$this->tablename='interstore_wf_position';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='iswf_id';	
	}
	
	
	
	
	
	
}
?>