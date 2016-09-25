<?
require_once("global.php");
require_once('PHPExcel/IOFactory.php');
require_once('PHPExcel.php'); 
 
class FileContents{
	
	protected $fullname;
	protected $name;
	
	const TXT="txt";
	
	const DOCX="docx";
	
	const XLS="xls";
	const XLSX="xlsx";
	const CSV="csv";
	
	const PPTX="pptx";
	const UNDEFINED="";
	
	function __construct($name, $fullname){
		$this->name=$name;
		$this->fullname=$fullname;	
	}
	
	public function GetContents(){
		$content='';
		
		//определить тип и вызвать нужный обработчик
		$type=$this->DefineType();
		
		//echo $type;
		
		if(($type==self::TXT)){
			$content=$this->ReadTxt($this->fullname);
		}elseif(($type==self::DOCX)){
			$content=$this->ReadDOCX($this->fullname);
		}elseif(($type==self::XLS)||($type==self::XLSX)||($type==self::CSV)){
			$content=$this->ReadXLS($this->fullname);
		}elseif(($type==self::PPTX)){		
			$content=$this->ReadPPTX($this->fullname);
		}
		
		return $content;			
	}
	
	protected function DefineType(){
		$name=strtolower($this->name);

		$data=explode('.', $name);
		//print_r($data);
		if(isset($data[count($data)-1])){
			$type= $data[count($data)-1];
			if(($type==self::TXT)){
				return $type;
			}elseif(($type==self::DOCX)){
				return $type;
			}elseif(($type==self::XLS)||($type==self::XLSX)||($type==self::CSV)){
				return $type;
			}elseif(($type==self::PPTX)){
				return $type;	
			}else return self::UNDEFINED;
			
		}else return self::UNDEFINED;
	}
	
	//разбор текста
	protected function ReadTxt($f){
		 $s=file_get_contents($f);
		 return $s;	
	}
	
	//разбор PPTX
	protected function ReadPPTX($f){
		$s='';
		$path = pathinfo(realpath($f));     
        $path =  ABSPATH.'/tmp/'.rand(10000000, 99999999); // $path['dirname'].'/'.rand(10000000, 99999999);
        $zip = new ZipArchive;
        $res = $zip->open($f);  
        if($res == true) {
            $zip->extractTo($path);
            $zip->close();

            //in unzipped file read /docProps/app.xml and look for <Words>4</Words>
            
			
			 $dir=$path.'/ppt/slides/';
			 if ($handle = opendir($dir)) {
				$array = array();
				while (false !== ($file = readdir($handle))) {
					if ($file != "." && $file != "..") {
						if(is_dir($dir.$file)) {
							 
						}
						else {
							 
								
								
								 
								$s.=' '.strip_tags(iconv('utf-8', 'windows-1251', file_get_contents($dir.$file)));
							/*	echo '<pre>';
							echo strip_tags(iconv('utf-8', 'windows-1251', $s));
							echo '</pre>';*/
								 
						}
					}
				}
				closedir($handle);
				 
			}
			
 
			//delete directory
            $d = $this->delete_directory($path.'/'); 
			
		     
            
			
        } else {
         //   echo 'Could not unzip file';
        }
		
		return $s;
	}
	
	
	protected function delete_directory($dir) {
		if ($handle = opendir($dir)) {
			$array = array();
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if(is_dir($dir.$file)) {
						if(!@rmdir($dir.$file)) {
							$this->delete_directory($dir.$file.'/'); 
						}
					}
					else {
					 @unlink($dir.$file);
					}
				}
			}
			closedir($handle);
			@rmdir($dir);
		}
	}
	
	//разбор DOCX
	protected function ReadDOCX($filename){
		 $striped_content = '';
		$content = '';
	
		if(!$filename || !file_exists($filename)) return false;
	
		$zip = zip_open($filename);
	
		if (!$zip || is_numeric($zip)) return false;
	
		while ($zip_entry = zip_read($zip)) {
	
			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;
	
			if (zip_entry_name($zip_entry) != "word/document.xml") continue;
	
			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
	
			zip_entry_close($zip_entry);
		}// end while
	
		zip_close($zip);
	
		//echo $content;
		//echo "<hr>";
		//file_put_contents('1.xml', $content);
	
		$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		$content = str_replace('</w:r></w:p>', "\r\n", $content);
		$striped_content = strip_tags($content);
		
		$striped_content =iconv('utf-8', 'windows-1251', $striped_content);
		return $striped_content;
	}
	
	//разбор XLSx
	protected function ReadXLS($f){
		//processing..
		$s='';
		$objPHPExcel = PHPExcel_IOFactory::load($f);
		
		 
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			 
			//now do whatever you want with the active sheet
			$sheetData = $worksheet->toArray(null,true,true,true);
			//var_dump($sheetData);
			foreach($sheetData as $k=>$v) foreach($v as $k1=>$v1){
				$s.=' '.$v1;	
			}
			
		}
		
		$s =iconv('utf-8', 'windows-1251', $s);
		return $s;
	}
	
	

}
?>