<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class User_ViewGroup extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_view';
		$this->col_tablename='user_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class User_ViewItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='user_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class User_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='user_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class User_ColGroup extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='user_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>