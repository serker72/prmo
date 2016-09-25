<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// абстрактная группа файлов
class AccInFileGroup extends AbstractFileDocFolderGroup {
	
	
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='acceptance_file';
		$this->file_instance=$file_instance; //экземпляр класса файла
		$this->folder_instance=$folder_instance; //экземпляр класса папки
		$this->pagename='acc_in_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='acceptance_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/accs_in/';	
		
		
		$this->tablename_folder='acceptance_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='acc_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	
	
}
?>