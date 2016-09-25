<?
require_once('abstractfilegroup.php');

// абстрактная группа файлов
class Sched_HistoryFileGroup extends AbstractFileGroup {
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
	
	
	
	
	public function GetItemsByIdArr($id){
		$arr=Array();
		
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'"  order by id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			
			$f=mysqli_fetch_array($rs);
			
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			
			$f['size']=filesize($this->storage_path.$f['filename'])/1024/1024;
			//$f['address']=nl2br($f['address']);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	
	
	public function ClearLostFiles($ttl=86400){
		//$files=array();
		$message_files=array();
		
		$message_files=$this->GetFilenamesArr();
		//print_r($message_files);
		$iterator = new DirectoryIterator($this->storage_path);
		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isFile()) {
				//echo $fileinfo->__toString();
				//$filenames[$fileinfo->getMTime()] = $fileinfo->getFilename();
				if(!in_array($fileinfo->__toString(), $message_files)){
					
					$tm=$fileinfo->getMTime();
					if($tm<(time()-$ttl)){
					  //проверять дату
					  @unlink($this->storage_path.$fileinfo->__toString());
					}
				}
			}
		}
		
	}
	
	protected function GetFilenamesArr(){
		//список позиций

		$arr=Array();
		$set=new MysqlSet('select * from '.$this->tablename.' order by id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$arr[]=$f['filename'];
		}
		
		return $arr;
		
	}
	
}
?>