<?
require_once('abstractgroup.php');

// абстрактная группа
class FileMessageGroup extends AbstractGroup {
	
	
	//установка всех имен
	protected function init(){
		$this->tablename='message_file';
		$this->pagename='view.php';		
		$this->subkeyname='message_id';	
		$this->vis_name='is_shown';		
		
		
		
	}
	
	
	public function GetItemsByIdArr($id, $current_id=0, $is_shown=0){
		$arr=Array();
		/*if($is_shown==0) */
		$set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" order by id asc');
		/*else $set=new MysqlSet('select * from '.$this->tablename.' where '.$this->subkeyname.'="'.$id.'" and '.$this->vis_name.'="1" order by ord desc, id asc');*/
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			//$f['address']=nl2br($f['address']);
			$f['size']=filesize(MESSAGE_FILES_PATH.$f['filename'])/1024/1024;
			
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	
	//список позиций
	public function GetFormsArr($current_id=0,  $is_shown=0){
		$arr=Array();
		$set=new MysqlSet('select * from fact_address_form order by name asc, id asc');
		
		$rs=$set->GetResult();
		$rc=$set->GetResultNumRows();
		for($i=0; $i<$rc; $i++){
			$f=mysqli_fetch_array($rs);
			$f['is_current']=(bool)($f['id']==$current_id);
			foreach($f as $k=>$v) $f[$k]=stripslashes($v);
			$arr[]=$f;
		}
		
		return $arr;
	}
	
	public function ClearLostFiles($ttl=86400){
		//$files=array();
		$message_files=array();
		
		$message_files=$this->GetFilenamesArr();
		//print_r($message_files);
		$iterator = new DirectoryIterator(MESSAGE_FILES_PATH);
		foreach ($iterator as $fileinfo) {
			if ($fileinfo->isFile()) {
				//echo $fileinfo->__toString();
				//$filenames[$fileinfo->getMTime()] = $fileinfo->getFilename();
				if(!in_array($fileinfo->__toString(), $message_files)){
					
					$tm=$fileinfo->getMTime();
					if($tm<(time()-$ttl)){
					  //проверять дату
					  @unlink(MESSAGE_FILES_PATH.$fileinfo->__toString());
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
	
	public function GetStoragePath(){
		return MESSAGE_FILES_PATH;	
	}
}
?>