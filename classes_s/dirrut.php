<?
class DirRut{
	
	//создание каталога
	public function CreateFolder($path){
		if($this->CheckDir($path)) return false;
//		$path= SecurePath(iconv('utf-8', 'windows-1251', $path)); 
		
		$result=mkdir($path);
		return $result;
	}
	
	//переименование каталога
	public function RenameFolder($path, $oldname, $newname){
		if(!$this->CheckDir($path.'/'.$oldname)) return false;
		
//		$newname= SecurePath(iconv('utf-8', 'windows-1251', $newname)); 
		
		@mkdir($path.'/'.$newname);
		$this->get_listing($path.'/'.$oldname,$path.'/'.$newname);
		//echo $fullpath1; die();
		
		//затем удалим исходную папку
		$this->DeleteFolder($path.'/'.$oldname);
		
	}
	
	//копирование каталога
	public function CopyFolder($path, $newpath){
		if(!$this->CheckDir($path)) return false;
		
		//@mkdir($newpath);
		
		//copy($path,$newpath);
		//$this->copy_files($path,$newpath);
		//echo $fullpath1; die();
		
		
		
	}
	
	
	//удаление каталога
	public function DeleteFolder($path){
	//удаление всех файлов в данной папке
		$h = opendir($path);	
		while(($nm=readdir($h))!==false){	
			if(($nm==".")||($nm=="..")) continue;
			if(is_file($path."/".$nm)){
			  unlink($path."/".$nm);
			}else $this->DeleteFolder($path."/".$nm);
		}
		closedir($h);
		//удаление самой папки	
		rmdir($path);	
	}
	
	//массированная смена прав
	public function ChmodFolder($path, $perm=0777) {
	   $handle = opendir($path);
	   while ( false !== ($file = readdir($handle)) ) {
	     if ( ($file !== ".") && ($file !== "..") ) {
	       if ( is_file($path."/".$file) ) {
	         chmod($path . "/" . $file, $perm);
	       }
	       else {
	         chmod($path . "/" . $file, $perm);
	         $this->ChmodFolder($path . "/" . $file, $perm);
	       }
	     }
	   }
	   closedir($handle);
	}

	
	
	//проверим, есть ли такая папка
	public function CheckDir($path){
		return (file_exists($path)&&@is_dir($path));
	}
	
	
	//обработка файлов при переименовании каталога
	protected function get_listing($listpath, $fullpath1){
		$hand_list = opendir($listpath);
		//$sub=0;
					//echo 'qqq';
		while(($name=readdir($hand_list))!==false){
	//		$sub++;
			if($name==".") continue;
			if($name=="..") continue;

			if(!is_dir($listpath."/".$name)){
				//если ЭТО ФАЙЛ!
				//проверим есть ли такой в перемещаемом каталоге
				if(file_exists($fullpath1.'/'.$name)){
					//переименуем файл
					$seed=1;
					$pat = '('.$seed.')'.$name;
					while(file_exists($fullpath1.'/'.$pat)){
						$seed++;
						$pat = '('.$seed.')'.$name;
						//echo $pat;
					}
					//echo $pat;
					copy($listpath.'/'.$name, $fullpath1.'/'.$pat);
				}else{
					//просто копируем
					copy($listpath.'/'.$name, $fullpath1.'/'.$name);
				}
			}else{
				//ЭТО КАТАЛОГ
				@mkdir($fullpath1.'/'.$name);
			}
			
			if(is_dir($listpath."/".$name)) {
				$this->get_listing($listpath."/".$name, $fullpath1.'/'.$name);
			}
		}	
		closedir($hand_list);
	}
	
	
	
	
}
?>