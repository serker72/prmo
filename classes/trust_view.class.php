<?
 
require_once('abstract_view.class.php');


//группа стобцов конфигурации
class Trust_ViewGroup extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='trust_view';
		$this->col_tablename='trust_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class Trust_ViewItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='trust_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class Trust_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='trust_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class Trust_ColGroup extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='trust_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>