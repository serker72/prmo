<?
 
require_once('abstract_view.class.php');


//������ ������� ������������
class Pay_In_ViewGroup extends Abstract_ViewGroup{
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='payment_in_view';
		$this->col_tablename='payment_in_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//������� ������� ������������
class Pay_In_ViewItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='payment_in_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������� �������
class Pay_In_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='payment_in_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//������ �������
class Pay_In_ColGroup extends Abstract_ColGroup {
	 
	//��������� ���� ����
	protected function init(){
		$this->tablename='payment_in_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>