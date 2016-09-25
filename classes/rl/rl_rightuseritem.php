<?

 

//абстрактный элемент
class RLRightUserItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='rl_user_rights';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
}
?>