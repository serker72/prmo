<?

class ConfigFile{
	protected $filename;
	protected $settings;
	//protected 
	
	function __construct(){
		$this->init();	
	}
	
	//установка всех имен
	protected function init(){
		$this->filename='../cnf/init.xml';
		$this->settings=Array();
		
	}
	
	//установка имени файла
	public function SetFileName($name){
		$this->filename=$name;
	}
	
	//получение массива установок
	public function GetSettings(){
		
		return $this->settings;
	}
	
	//запись установок в файл
	public function SaveToFile($settings){
		$res=false;
		
		$xml=simplexml_load_string('<?xml version="1.0" encoding="windows-1251"?><settings />');
		foreach ($settings as $k=>$v){  
			$xml->addChild(strtolower($k),"<![CDATA[".htmlentities($v)."]]>");
		}
		
		//echo htmlspecialchars(html_entity_decode($xml->asXML()));
		$txt=html_entity_decode($xml->asXML());
		$this->WriteStringToFile($txt);
		return $res;
	}
	
	
	
	//считывание установок из файла
	public function LoadFromFile(){
		
		$this->settings=array();
		$xml=simplexml_load_file($this->filename);
		foreach ($xml as $k=>$v){  
			if(DO_XML_RECODE){
				$this->settings[strtoupper($k)]=iconv('utf-8','windows-1251',$v[0]); 
				define(strtoupper($k),iconv('utf-8','windows-1251',$v[0]));  
			}else{
				$this->settings[strtoupper($k)]=$v[0]; 
				define(strtoupper($k),$v);
			}
		}
		return $this->settings;
	}
	
	
	
//----------------------------------------------------------------------------------------
	
	
	
	//служебная функция записи строки в файл
	protected function WriteStringToFile($txt){
		$f=fopen($this->filename, "w");
		fwrite($f, ($txt), strlen($txt));
		fclose($f);
		return true;
	}
};
?>