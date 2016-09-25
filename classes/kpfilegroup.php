<?
require_once('abstractfiledocfoldergroup.php');

require_once('filedocfolderitem.php');

// ����������� ������ ������
class KpFileGroup extends AbstractFileDocFolderGroup {
	
	
	protected function init($id, $doc_id, $folder_instance){
		$this->tablename='kp_file';
		$this->file_instance=$file_instance; //��������� ������ �����
		$this->folder_instance=$folder_instance; //��������� ������ �����
		$this->pagename='kp_files.php';	
		$this->vis_name='is_shown';	
		$this->subkeyname='kp_id';
			
		$this->storage_id=$id;	
		$this->storage_name='storage_id';	
		$this->storage_path=ABSPATH.'upload/files/kps/';	
		
		
		$this->tablename_folder='kp_file_folder';
		$this->doc_id=$doc_id;
		$this->doc_id_name='kp_id';
		
		$this->folder_instance->tablename=$this->tablename_folder;
		$this->folder_instance->doc_id_name=$this->doc_id_name;
			
	}
	
	
	
}
?>