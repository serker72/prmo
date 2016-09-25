<?



//абстрактный элемент
class RLObjectItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='rl_object';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	
	
}
?>