<?
 
require_once('abstract_view.class.php');


//группа стобцов конфигурации
class Sched_View2Group extends Abstract_ViewGroup{
	 
	//установка всех имен
	protected function init(){
		$this->tablename='sched_2_view';
		$this->col_tablename='sched_2_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='user_id';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}

//элемент столбец конфигурации
class Sched_View2Item extends Abstract_ViewItem{
	protected function init(){
		$this->tablename='sched_2_view';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//элемент колонка
class Sched_Col2Item extends Abstract_ColItem{
	protected function init(){
		$this->tablename='sched_2_view_field';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
}

//группа колонок
class Sched_Col2Group extends Abstract_ColGroup {
	 
	//установка всех имен
	protected function init(){
		$this->tablename='sched_2_view_field';
		$this->pagename='view.php';		
		$this->subkeyname='mid';	
		$this->vis_name='is_shown';		
		
		$this->_auth_result=NULL;
		
	}
}
?>