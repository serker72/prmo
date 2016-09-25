<?
class OneFolder{
	protected $base_path;//�������� ���� (��� ��������� ��� ��������� ������)
	protected $init_path; //���� � ��������� ����� ���������
	protected $path; //���� �������
	
	protected $number; //����� ������� ����� (��� id � javascript)
	protected $offs; //������ ��� ��������
	protected $step_offset; //��� �� �������� �����
	protected $folder_class_name='fldr';
	protected $active_folder_name='afldr';
	protected $folder_plus_img='../img/plus.gif';
	
	protected $result; //����� ��� ������ ����������
	
	protected $link_class_name; //��� ������ ��� ����������� (� ���������)
	
	//������� ��� ����� � ������
	protected $dir_arr=Array();
	protected $file_arr=Array();
	
	protected $draw_files=true; //���� ��������� ������
	
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
	
	//������� ��������� ��������
	public function GetDirArr(){
		ksort($this->dir_arr);
		return $this->dir_arr;
	}
	
	public function GetFileArr(){
		ksort($this->file_arr);
		return $this->file_arr;
	}	
	
	
	//������� ������ �����
	public function ReadDirect($has_files=false){
		$this->ReadDirectory($this->init_path,$has_files);
		//return $this->DrawArrays();
	}
	
	
	
		//��������� ������� ������ �����
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
				//��� �������, ������� ��� � ������
				//$this->dir_arr[$path.'/'.$name]=$this->offs;
				$this->dir_arr[$path.'/'.$name]=Array(
					'offs'=>$this->offs,
					'rel_path'=>substr($path.'/'.$name, strlen($this->base_path)),
					'name'=>$name,
					'path'=>substr($path, strlen($this->base_path))
				);
			}else{
				//��� ����, ������� ��� � ������
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
	
	
	

	
	//��������� ����� �������� ��������
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
	
	
	//����� ���� �������� ��������� � ������
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