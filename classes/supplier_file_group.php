<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// абстрактная группа файлов
class SupplierFileGroup extends AbstractFileDocFolderGroup {
	protected $storage_id;
	protected $storage_name;
	protected $storage_path;
	 
	
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='supplier_shema_file';
		$this->file_instance=$file_instance; //экземпляр класса файла
		$this->folder_instance=$folder_instance; //экземпляр класса папки
		$this->pagename='supplier_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='user_d_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/supplier_file_file/';	
		
		
		$this->tablename_folder='supplier_shema_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='sup_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	
}
?>