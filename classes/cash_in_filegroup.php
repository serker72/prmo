<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

//  группа файлов
class CashInFileGroup extends AbstractFileDocFolderGroup {
	
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='cash_in_file';
		$this->file_instance=$file_instance; //экземпл€р класса файла
		$this->folder_instance=$folder_instance; //экземпл€р класса папки
		$this->pagename='cash_in_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='payment_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/cash_in/';	
		
		
		$this->tablename_folder='cash_in_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='pay_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	
}
?>