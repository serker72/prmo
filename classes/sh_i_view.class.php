<?
 
require_once('abstract_view.class.php');


//группа стобцов конфигурации
class Sh_I_ViewGroup extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i_view';
		$this->col_tablename='sh_i_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class Sh_I_ViewItem extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='sh_i_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class Sh_I_ColItem extends Abstract_ColItem{
	protected function init(){
		$this->tablename='sh_i_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class Sh_I_ColGroup extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='sh_i_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>