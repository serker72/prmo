<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// абстрактная группа файлов
class UserVPassportGroup extends AbstractFileDocFolderGroup {
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	
	/*public function __construct($id=1){
		$this->init($id);
	}
	
	//установка всех имен
	protected function init($id){
		$this->tablename='user_pasport_file';
		$this->item=NULL;
		$this->pagename='page.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/users/pasports/';	
	}
	*/
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='user_pasport_file';
		$this->file_instance=$file_instance; //экземпляр класса файла
		$this->folder_instance=$folder_instance; //экземпляр класса папки
		$this->pagename='user_v_pasp.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/users/passports_v/';	
		
		
		$this->tablename_folder='user_pasport_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='user_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	
}
?>