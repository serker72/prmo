<?

 

//абстрактный элемент
class RLUserItem extends UserItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='user';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>