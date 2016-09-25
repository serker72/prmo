<?
class Folderer{
	protected $path; //���� � ��������� ����� ���������
	protected $current_path; //������� �����
	protected $fullpath; //���� �������
	
	protected $number; //����� ������� ����� (��� id � javascript)
	protected $offs; //������ ��� ��������
	protected $mode; //����� ������ �������� �����
	protected $folder; //������� ����� �������� �����
	protected $step_offset; //��� �� �������� �����
	protected $folder_class_name='fldr';
	protected $active_folder_name='afldr';
	protected $folder_plus_img='../img/plus.gif';
	
	protected $result; //����� ��� ������ ����������
	
	protected $link_class_name; //��� ������ ��� ����������� (� ���������)
	
	protected $level=0;

	
	public function __construct($path){
		$this->init($path);
	}
	
	protected function init($path){
		$this->path=$path;
		$this->number=0;
		$this->offs=0;
		$this->result='';
		$this->step_offset=20;
	}
	
	public function SetFolder($folder){
		$this->folder=$folder;
	}
	
	public function SetMode($mode){
		$this->mode=$mode;
	}
	
	public function SetPath($path){
		$this->path=$path;
	}
	
	public function SetOffs($offs){
		$this->offs=$offs;
	}
	
	public function SetCurrentPath($current){
		$this->current_path=$current;
	}
	
	//������� ������ �����
	public function ReadDirect(){
		$this->fullpath=$this->path;
		$this->ReadDirectoryNew($this->path);
		return $this->result;
	}
	
	//��������� ������� ������ �����
	/*
	protected function ReadDirectory($path, $offs){
		$hnd=opendir($path);
		$this->offs+=$this->step_offset;
		while(($name=readdir($hnd))!=false){
			if(($name==".")||($name=="..")) continue;
			$this->number++;
			
			$ifplus=$this->CountSubs($path.'/'.$name);
			if($ifplus>0) {
				$hrefcode1="<a href=\"#\" class=\"$this->link_class_name\" onclick=\"PressFolder('$path"."/$name',$this->offs);\">\n";
				$src='../img/plus.gif';
				$hrefcode2="</a>";
			}
			else{
				$src='../img/ugolok.gif';
				$hrefcode1='';
				$hrefcode2='';
			}
			$this->result.='<div id="al'.$this->number.'" class="padding-left: '.$offs.'px;">'.$hrefcode1.'<img src="'.$src.'" alt="" width="7" height="7" border="0" id="im'.$this->number.'" align="absmiddle">'.$name.$hrefcode2.'<div id="sub'.$this->number.'"></div></div>';
		}
		closedir($hnd);
	}
*/	
	
		//��������� ������� ������ �����
	protected function ReadDirectoryNew($path){
		$hnd=opendir($path);
		$this->offs+=$this->step_offset;
		$this->level++;
		while(($name=readdir($hnd))!=false){
			if(($name==".")||($name=="..")) continue;
			$this->number++;
			

			if(@is_dir($path.'/'.$name))
				$this->result.='<div id="al'.$this->number.'" style="margin-left: '.$this->offs.'px;">'.$path.'/'.$name.'</div>'."\n";
			
			flush();			
			
			if(!@chdir($path.'/'.$name)) continue;	
			
			//��������, ������� �� ��� �������, �� ������� � �� �������� ��� ���������, �� �� ���������
			//$this->result.='<!--dirgroup_sub'.$this->number.'--><div id="sub'.$this->number.'">';
			
			$this->ReadDirectoryNew($path.'/'.$name);
			chdir('..'); $this->level--; $this->offs-=$this->step_offset; 
			//$this->result.='</div><!--/dirgroup_sub'.$this->number.'-->'."\n";

		}
		closedir($hnd);
	}
	
		
	
	
	
	
	
	//������� ��������� ������ �������� � xml
	public function DrawFoldersXml($path, $offs){
		$hnd=opendir($path);
		//$this->offs+=$this->step_offset;
		echo '<result>';
		while(($name=readdir($hnd))!=false){
			if(($name==".")||($name=="..")) continue;
			$this->number++;
			echo "<name>$name</name>";
			$ifplus=$this->CountSubs($path.'/'.$name);
			
		}
		closedir($hnd);
		echo '</result>';
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
	
	
};
?>