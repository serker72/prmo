<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class Sched_View2Group extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_2_view';
		$this->col_tablename='sched_2_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class Sched_View2Item extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='sched_2_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class Sched_Col2Item extends Abstract_ColItem{
	protected function init(){
		$this->tablename='sched_2_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class Sched_Col2Group extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='sched_2_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>