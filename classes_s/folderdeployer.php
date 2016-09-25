<?
require_once('onefolder.php');

class FolderDeployer{

	protected $init_path; //полный путь к папке
	protected $base_path; //основной путь (для вычитания при генерации ссылок)
	protected $fragments=Array(); //массив, содержащий фрагменты пути к файлу
	
	protected $delim='/'; //символ-граница разбиения
	
	//массивы для папок и файлов
	protected $dir_arr=Array();
	protected $file_arr=Array();
	
	protected $result='';
	
	protected $folder_class_name='fldr';
	protected $active_folder_name='afldr';
	protected $folder_plus_img='/img/plus.gif';
	protected $folder_minus_img='/img/minus.gif';
	
	//имя файла-сценария
	protected $prog_name='index.php';
	
	
	public function  __construct($init_path,$base_path,$folder){
		$this->init($init_path,$base_path,$folder);
	}
	
	protected function init($path, $base_path,$folder){
		$this->init_path=$path;
		$this->base_path=$base_path;
		$this->folder=$folder;
		$this->MakeFragments();
	}
	
	//назначим имя сценария
	public function SetProgName($name){
		$this->prog_name=$name;
		return true;
	}
	
	
	//разбиваем исходную строку на куски по символу /
	public function MakeFragments(){
		$some=substr($this->init_path, strlen($this->base_path), strlen($this->init_path));
		
		//echo $some;
		//echo $this->init_path;
		//$this->fragments=explode( $this->delim, $this->init_path);
		$this->fragments=explode( $this->delim, $some);
		
		//foreach($this->fragments as $k=>$v) echo "$k->$v<br>";
		
	}

	//получение массива фрагментов
	public function GetFragments(){
		return $this->fragments;
	}
	
	//пробегаем по массиву фрагментов и строим дерево каталогов
	public function CreateTree($has_files=false,$params=Array()){
		$txt='';
		
		//начало всего дерева
		$txt.='<div class="dirtree">';
		
		$offs=0; $path=$this->base_path;
		for($i=0;$i<count($this->fragments);$i++){
			
			if($i!=0) $path.='/'.$this->fragments[$i];
			else $path.=$this->fragments[$i];
			$d=new OneFolder($path,$this->base_path);
//			echo $this->base_path;
			$d->SetOffs($offs);
			$txt.=$d->ReadDirect($has_files);
			
			$this->dir_arr[]=$d->GetDirArr();
			$this->file_arr[]=$d->GetFileArr();
			$offs+=20;
		}
		
		$this->result='';
		
		$str=DeParams($params);
		
		$this->result.="<div class=\"folder_level\" style=\"margin-left: 0px; \"><a class=\"fldr\" href=\"$this->prog_name?folder=$str\">/Наверх</a></div>

		<div class=\"folder_level\" style=\"margin-left: 0px; margin-bottom: 5px;\">
		<a class=\"fldr\" href=\"#\" onmousedown=\"".COORDFUNC." SetCoords('folderer');\" onClick=\"pt=document.getElementById('folderpath'); pt.value='".SecStr($this->folder)."';  pt=document.getElementById('action'); pt.value='0'; r=document.getElementById('folderer'); r.className='renvis'; \">Новая папка</a>
		
		</div>";
		
		
		$this->RekursArrays($this->base_path,0, $has_files, $params);
		
		if($has_files){
			foreach($this->file_arr[0] as $k=>$v){
				//$this->result.="<div style=\"color: red; margin-left: ".$v."px;\">$k</div>";
				$this->result.=$this->DrawFile($k,$v,$params);
			}
		}
		
		
		$txt.=$this->result;
		
		//конец всего дерева
		$txt.='</div>';
		return $txt;
	}
	
	
	//рекурсивный вывод массивов дерева
	protected function RekursArrays($currpath,$ct, $has_files=false, $params=Array()){
		
		if($ct<count($this->fragments)){
			if($ct!=0) $currpath.='/'.$this->fragments[$ct];
			else $currpath.=$this->fragments[$ct];
			foreach($this->dir_arr[$ct] as $k=>$v){
				//echo $currpath;
				//echo count($this->fragments);
				//$this->result.="<div style=\"color: Green; margin-left: ".$v."px;\">$k</div>";
				$checkpoint =(isset($this->fragments[$ct+1])&&(($currpath.'/'.$this->fragments[$ct+1])==$k));
				
				//echo @$this->fragments[$ct+1].'<br>';
				//echo "$k<br>";
				
				//$params=Array();
				//$params['folder']=$v['rel_path'];
				$str=DeParams($params);
				
				//echo $v['rel_path'];
				//$this->result.=$currpath;
				$this->result.=$this->DrawDir($k, $v, $checkpoint, $params);
				if($checkpoint){
					$ct++;
					
					$this->RekursArrays($currpath,$ct,$has_files,$params);
					
					if($has_files)
						foreach($this->file_arr[$ct] as $k=>$v){
							//$this->result.="<div style=\"color: red; margin-left: ".$v."px;\">$k</div>";
							$this->result.=$this->DrawFile($k,$v,$params);
						}
				}
			}
		}
	}
	
	
	//функция вывода каталога
	protected function DrawDir($name,$props,$is_opened,$params){
		$txt='';
		
		$str=DeParams($params);
		
		$txt.="<div class=\"folder_level\" style=\"margin-left: $props[offs]px;\" id=\"$name\">";
		$txt.="<a name=\"$props[rel_path]\"></a>";
		if($is_opened){
			$txt.="<nobr><a class=\"$this->active_folder_name\" href=\"$this->prog_name?from=0&folder=$props[rel_path]$str\"><img src=\"$this->folder_minus_img\" alt=\"\" border=\"0\" align=\"absmiddle\">$props[name]</a></nobr>";

			//$txt.="$name";
		}else{
			$txt.="<nobr><a class=\"$this->folder_class_name\" href=\"$this->prog_name?from=0&folder=$props[rel_path]$str\"><img src=\"$this->folder_plus_img\" alt=\"\" border=\"0\" align=\"absmiddle\">$props[name]</a></nobr>";
		}
		
		$txt.="<span style=\"font-size: 8pt;\">&nbsp;&nbsp; <a class=\"photolink\" href=\"#$props[rel_path]\" onmousedown=\"".COORDFUNC." SetCoords('folderer');\" onClick=\"pt=document.getElementById('folderpath'); pt.value='".SecStr($props['path'])."'; pt=document.getElementById('oldname'); pt.value='".SecStr($props['name'])."'; pt=document.getElementById('foldername'); pt.value='".SecStr($props['name'])."'; pt=document.getElementById('action'); pt.value='1'; r=document.getElementById('folderer'); r.className='renvis'; \">переименовать</a> &nbsp;&nbsp;&nbsp;&nbsp; 

<a class=\"photolink\" href=\"#\" onClick=\"ct=window.confirm('ВНИМАНИЕ!!! Вы действительно хотите удалить данный каталог?'); if(ct) {".COORDFUNC." RemoveDirFunc('".SecStr($props['rel_path'])."');}\">удалить</a></span>
			</div>";
		return $txt;
		
	}
	
	//функция вывода файла
	protected function DrawFile($name,$offset,$params){
		$txt='';		
		$txt.="<div style=\"color: red; margin-left: ".$offset['offs']."px;\">$name</div>";
		
		return $txt;
	}
	
	
	
	
}

?>