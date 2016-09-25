<?
require_once('abstractitem.php');


//ћаркер за€вки
class KomplMarkItem extends AbstractItem{
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='komplekt_ved_restore_marker';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='komplekt_ved_id';	
	}
	
	//удалить
	public function Del($id){
	
		
		$query = 'delete from komplekt_ved_restore_marker_pos where marker_id='.$id.';';
		$it=new nonSet($query);
		
		parent::Del($id);
	}
	
}
?>