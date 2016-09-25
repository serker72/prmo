<?
require_once('abstractfileitem.php');

//абстрактный файл
class  Sched_HistoryFileItem extends AbstractFileItem{
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	
	public function __construct($id=1){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='sched_history_file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='history_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/sched_history/';	
	}
	
	
	public function GetStoragePath(){
		return $this->storage_path;	
	}
	
	
	//удалить
	public function Del($id){
		$item=$this->GetItemById($id);
		if($item!==false){
		
		  @unlink($this->storage_path.$item['filename']);
		  parent::Del($id);
		}
	}	
	
}
?>