<?
class ImgResizing{
	
	protected $picpath; //путь к папке, где лежит картинка
	protected $filename; //имя файла картинки для обработки
	protected $image1; //контейнер исходной картинки
	protected $image2; //контейнер конечной картинки
	
	protected $extension; //расширение имени файла
	protected $size; //массив измерений исходной картинки
	protected $ratio; //соотношение длина/ширина для исходной картинки
	
	public function __construct($filename){
		$this->init($filename);
		$this->DefineExtension($this->filename);
	}
	
	//инициализация
	protected function init($filename){
		$this->filename=$filename;
		$this->image1=-1;
		$this->image2=-1;	
		$this->size=-1;
		$this->ratio=-1;	
	}
	
	//деструктор
	public function __destruct(){
		if($this->image1!=-1) ImageDestroy($this->image1);
		if($this->image2!=-1) ImageDestroy($this->image2);		
	}
	
	//определяем расширение картинки
	protected function DefineExtension($name=''){
		$extension=0;
		if(eregi("^(.*)\\.(jpg|jpeg|jpe)$",$name,$P)) $extension='.jpg';
		if(eregi("^(.*)\\.(gif)$",$name,$P)) $extension='.gif';
		if(eregi("^(.*)\\.(png)$",$name,$P)) $extension='.png';		
		if(eregi("^(.*)\\.(wbm)$",$name,$P)) $extension='.wbm';				
		$this->extension = $extension;
	}
	
	
	//изменять на жестко заданный (120*90 или 90*120 в зависимости от пропорций)
	public function ResizeHard($prefix='', $size1=120, $size2=90){
		$this->CreateImage();
		if($this->size!=-1){
			//изменяем размер
			//$newname=eregi_replace('([[:alnum:]])(\\.[[:alnum:].]*)?$','\\1'.'-'.$prefix.$this->extension,$this->filename);		
			$newname=$this->MakeNewName($prefix);
			//echo $newname;
			if($this->ratio>=1){
				$w=$size1; $h=$size2;
			}else{
				$w=$size2; $h=$size1;
			}
			//echo $w.'  '.$h;
			$this->image2 = imagecreatetruecolor($w,$h);
			$this->SaveImage($newname,$w,$h);
		}
	}
	
	
	//Изменять по заданному одному измерению (максимальный размер - не более 120)
	public function ResizeOneDimension($prefix='', $maxsize=120){
		$this->CreateImage();
		if($this->size!=-1){
			//изменяем размер
			$newname=$this->MakeNewName($prefix);
			//echo $newname;
			if($this->ratio>=1){
				$w=$maxsize; $h=ceil($this->ratio*$w);
			}else{
				$h=$maxsize; $w=ceil($this->ratio*$h);
			}
			$this->image2 = imagecreatetruecolor($w,$h);
			$this->SaveImage($newname,$w,$h);
		}
	
	}
	
	
	//изменять на процент (15% от исходной)
	public function ResizePercent($prefix='', $percent=15){
		$this->CreateImage();
		if(($percent<0)||($percent>100)) $percent=100;
		if($this->size!=-1){
			//изменяем размер
			$newname=$this->MakeNewName($prefix);
			//echo $newname;
			if($this->ratio>=1){
				//вертик
				$w=$this->size[0]*$percent/100; $h=ceil($this->ratio*$w);
			}else{
				$h=$this->size[1]*$percent/100; $w=ceil($this->ratio*$h);
			}
			$this->image2 = imagecreatetruecolor($w,$h);
			$this->SaveImage($newname,$w,$h);
		}
	
	}
	
	//изменять по паре заданных размеров (120*90, если соответствующий максимальный размер больше заданного критерия, то приводить его к соответствущему критерию).
	public function ResizeByMaxSize($prefix='', $width=120, $height=90){
		$this->CreateImage();
		if($this->size!=-1){
			//изменяем размер
			$newname=$this->MakeNewName($prefix);
			//echo $newname;
			if($this->ratio>=1){
				if($this->size[0]<$width) $w = $this->size[0];
				else $w = $width;
				$h = ceil($w*$this->size[1]/$this->size[0]);
			}else{
				if($this->size[1]<$height) $h = $this->size[1];
				else $h = $height;
				$w = ceil($this->size[0]*$h/$this->size[1]);
			}
			//echo $w.'  '.$h;
			$this->image2 = imagecreatetruecolor($w,$h);
			$this->SaveImage($newname,$w,$h);
		}
	}
	
	//жесткая обрезка по заданному размеру (120*90, а что больше - просто обрубать).
	public function ResizeCut($prefix='', $width=120, $height=90){
		$this->CreateImage();
		if($this->size!=-1){
			//изменяем размер
			$newname=$this->MakeNewName($prefix);
			//echo $newname;
			if($this->ratio>=1){
				$w=$width;
				
				if($this->size[1]<$height) $h = $this->size[1];
				else $h = $height;
				
			}else{
				$h=$height;
				if($this->size[0]<$width) $w = $this->size[0];
				else $w = $width;

			}
			//echo $w.'  '.$h;
			$this->image2 = imagecreatetruecolor($w,$h);
			$this->SaveImage($newname,$w,$h);
		}
	
	}
	
	//получаем объект-картинку
	protected function CreateImage(){
		$this->DefineExtension($this->filename);
		
		if($this->extension=='.jpg') $this->image1 = imageCreatefromjpeg($this->filename);
		if($this->extension=='.gif') $this->image1 = imageCreatefromgif($this->filename);
		if($this->extension=='.png') $this->image1 = imageCreatefrompng($this->filename);		
		if($this->extension=='.wbm') $this->image1 = imageCreatefromwbmp($this->filename);				
		if($this->image1!=-1){
			$this->size = GetImageSize($this->filename);		
			$this->ratio = $this->size[0]/$this->size[1];
			//echo $this->ratio;
		}
	}
	
	//сохраняем картинку на диск
	protected function SaveImage($newname,$w,$h){
		imagecopyresampled($this->image2, $this->image1, 0,0,0,0, $w,$h, $this->size[0],$this->size[1]);
		if($this->extension=='.jpg') imageJpeg($this->image2, $newname);							
		if($this->extension=='.gif') imageGif($this->image2, $newname);										
		if($this->extension=='.png') imagePng($this->image2, $newname);	
		if($this->extension=='.wbm') imageWbmp($this->image2, $newname);			
	}
	
	//получаем новое имя файла (старое+префикс)
	protected function MakeNewName($prefix=''){
		$newname=eregi_replace('([[:alnum:]])(\\.[[:alnum:].]*)?$','\\1'.'-'.$prefix.$this->extension,$this->filename);		
		return $newname;
	}
	
};
?>