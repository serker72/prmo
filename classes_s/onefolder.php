<?
class OneFolder{
	protected $base_path;//основной путь (для вычитания при генерации ссылок)
	protected $init_path; //путь к начальной папке структуры
	protected $path; //путь текущий
	
	protected $number; //номер текущей папки (для id И javascript)
	protected $offs; //оффсет для подпапок
	protected $step_offset; //шаг по смещению слева
	protected $folder_class_name='fldr';
	protected $active_folder_name='afldr';
	protected $folder_plus_img='../img/plus.gif';
	
	protected $result; //текст для вывода результата
	
	protected $link_class_name; //имя класса для гиперссылок (у каталогов)
	
	//массивы для папок и файлов
	protected $dir_arr=Array();
	protected $file_arr=Array();
	
	protected $draw_files=true; //флаг рисования файлов
	
	public function __construct($init_path,$base_path, $draw_files=true){
		$this->init($init_path,$base_path, $draw_files);
	}
	
	protected function init($path,$base_path, $draw_files){
		$this->init_path=$path;
		$this->base_path=$base_path;
		$this->number=0;
		$this->offs=0;
		$this->result='';
		$this->step_offset=20;
		$this->draw_files=$draw_files;
	}
	
	
	public function SetMode($mode){
		$this->mode=$mode;
	}
	
	
	public function SetOffs($offs){
		$this->offs=$offs;
	}
	
	//простое получение массивов
	public function GetDirArr(){
		ksort($this->dir_arr);
		return $this->dir_arr;
	}
	
	public function GetFileArr(){
		ksort($this->file_arr);
		return $this->file_arr;
	}	
	
	
	//функция показа папок
	public function ReadDirect($has_files=false){
		$this->ReadDirectory($this->init_path,$has_files);
		//return $this->DrawArrays();
	}
	
	
	
		//служебная функция показа папок
	protected function ReadDirectory($path, $has_files=false){
		$hnd=opendir($path);
		
		/*if(file_exists($path)) echo 'qqq!';
		else echo 'asss';*/
		//$this->offs+=$this->step_offset;
		//$this->level++;
		//echo $path;
		//echo 'qq';
		while(($name=readdir($hnd))!=false){
			if(($name==".")||($name=="..")) continue;
			$this->number++;
			
			

			
			if(@is_dir($path.'/'.$name)){
				//это каталог, загоним его в массив
				//$this->dir_arr[$path.'/'.$name]=$this->offs;
				$this->dir_arr[$path.'/'.$name]=Array(
					'offs'=>$this->offs,
					'rel_path'=>substr($path.'/'.$name, strlen($this->base_path)),
					'name'=>$name,
					'path'=>substr($path, strlen($this->base_path))
				);
			}else{
				//это файл, загоним его в массив
//				echo $name;
				//if($has_files) $this->file_arr[$path.'/'.$name]=$this->offs+10;
				if($has_files) $this->file_arr[$path.'/'.$name]=Array(
					'offs'=>$this->offs+10,
					'rel_path'=>substr($path.'/'.$name, strlen($this->base_path)),
					'name'=>$name,
					'path'=>substr($path, strlen($this->base_path))
				);
			}

		}
		closedir($hnd);
	}
	
	
	

	
	//служебная папка подсчета подпапок
	protected function CountSubs($path){
		$counter=0;
		$hnd=opendir($path);
		while(($name=readdir($hnd))!=false){
			if(($name==".")||($name=="..")) continue;
			$counter++;
		}
		closedir($hnd);
		return $counter;
	}
	
	
	//вывод всех массивов каталогов и файлов
	protected function DrawArrays(){
		$txt='';
		foreach($this->dir_arr as $k=>$v){
			$txt.='<div id="al'.$this->number.'" style="margin-left: '.$this->offs.'px;">'.$k.'</div>'."\n";
		}
		
		if($this->draw_files){
			foreach($this->file_arr as $k=>$v){
				$txt.='<div id="al'.$this->number.'" style="margin-left: '.$this->offs.'px;">'.$k.'</div>'."\n";
			}
		}
		
		return $txt;	
	}
	
};
?>