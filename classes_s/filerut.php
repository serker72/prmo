<?
class FileRut{
	//вызов чмода на файл
	public function FileChmod($path,$rights=0777){
		if(!$this->CheckFile($path)) return false;
		
		$res=chmod($path, $rights);
		return $res;
	}
	

	//переименование файла
	public function RenameFile($path,$newname){
		if(!$this->CheckFile($path)) return false;
		//$newname= SecurePath(iconv('utf-8', 'windows-1251', $newname)); 
		
		$res=rename($path, $newname);
		return $res;
	}
	
	//удаление файла
	public function DeleteFile($path){
		if(!$this->CheckFile($path)) return false;
		$res=unlink($path);
		return $res;
	}
	
	//копирование файла
	public function CopyFile($path,$dest){
		if(!$this->CheckFile($path)) return false;
		$res=copy($path,$dest);
		return $res;
	}
	
	//проверка файла
	public function CheckFile($path){
		return (file_exists($path)&&is_file($path));
	}
}
?>