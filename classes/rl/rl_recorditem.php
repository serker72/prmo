<?

 

//абстрактный элемент
class RLRecordItem extends AbstractItem{
	
	//установка всех имен
	protected function init(){
		$this->tablename='rl_record';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='mid';	
	}
	
	
	//удалить
	public function Del($id){
		
		$query = 'delete from rl_user_rights where rl_record_id='.$id.';';
		$it=new nonSet($query);
		
		AbstractItem::Del($id);
	}	
	
	
}
?>