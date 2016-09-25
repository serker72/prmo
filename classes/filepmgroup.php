<?
require_once('abstractfilefoldergroup.php');

// абстрактная группа файлов
class FilePmGroup extends AbstractFileFolderGroup {
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	public function __construct($id=5){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/pm/';	
	}
	
	
	
}
?>