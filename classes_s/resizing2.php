<?
class ImgResizing{
	
	protected $picpath; //���� � �����, ��� ����� ��������
	protected $filename; //��� ����� �������� ��� ���������
	protected $image1; //��������� �������� ��������
	protected $image2; //��������� �������� ��������
	
	protected $extension; //���������� ����� �����
	protected $size; //������ ��������� �������� ��������
	protected $ratio; //����������� �����/������ ��� �������� ��������
	
	public function __construct($filename){
		$this->init($filename);
		$this->DefineExtension($this->filename);
	}
	
	//�������������
	protected function init($filename){
		$this->filename=$filename;
		$this->image1=-1;
		$this->image2=-1;	
		$this->size=-1;
		$this->ratio=-1;	
	}
	
	//����������
	public function __destruct(){
		if($this->image1!=-1) ImageDestroy($this->image1);
		if($this->image2!=-1) ImageDestroy($this->image2);		
	}
	
	//���������� ���������� ��������
	protected function DefineExtension($name=''){
		$extension=0;
		if(eregi("^(.*)\\.(jpg|jpeg|jpe)$",$name,$P)) $extension='.jpg';
		if(eregi("^(.*)\\.(gif)$",$name,$P)) $extension='.gif';
		if(eregi("^(.*)\\.(png)$",$name,$P)) $extension='.png';		
		if(eregi("^(.*)\\.(wbm)$",$name,$P)) $extension='.wbm';				
		$this->extension = $extension;
	}
	
	
	//�������� �� ������ �������� (120*90 ��� 90*120 � ����������� �� ���������)
	public function ResizeHard($prefix='', $size1=120, $size2=90){
		$this->CreateImage();
		if($this->size!=-1){
			//�������� ������
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
	
	
	//�������� �� ��������� ������ ��������� (������������ ������ - �� ����� 120)
	public function ResizeOneDimension($prefix='', $maxsize=120){
		$this->CreateImage();
		if($this->size!=-1){
			//�������� ������
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
	
	
	//�������� �� ������� (15% �� ��������)
	public function ResizePercent($prefix='', $percent=15){
		$this->CreateImage();
		if(($percent<0)||($percent>100)) $percent=100;
		if($this->size!=-1){
			//�������� ������
			$newname=$this->MakeNewName($prefix);
			//echo $newname;
			if($this->ratio>=1){
				//������
				$w=$this->size[0]*$percent/100; $h=ceil($this->ratio*$w);
			}else{
				$h=$this->size[1]*$percent/100; $w=ceil($this->ratio*$h);
			}
			$this->image2 = imagecreatetruecolor($w,$h);
			$this->SaveImage($newname,$w,$h);
		}
	
	}
	
	//�������� �� ���� �������� �������� (120*90, ���� ��������������� ������������ ������ ������ ��������� ��������, �� ��������� ��� � ��������������� ��������).
	public function ResizeByMaxSize($prefix='', $width=120, $height=90){
		$this->CreateImage();
		if($this->size!=-1){
			//�������� ������
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
	
	//������� ������� �� ��������� ������� (120*90, � ��� ������ - ������ ��������).
	public function ResizeCut($prefix='', $width=120, $height=90){
		$this->CreateImage();
		if($this->size!=-1){
			//�������� ������
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
	
	//�������� ������-��������
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
	
	//��������� �������� �� ����
	protected function SaveImage($newname,$w,$h){
		imagecopyresampled($this->image2, $this->image1, 0,0,0,0, $w,$h, $this->size[0],$this->size[1]);
		if($this->extension=='.jpg') imageJpeg($this->image2, $newname);							
		if($this->extension=='.gif') imageGif($this->image2, $newname);										
		if($this->extension=='.png') imagePng($this->image2, $newname);	
		if($this->extension=='.wbm') imageWbmp($this->image2, $newname);			
	}
	
	//�������� ����� ��� ����� (������+�������)
	protected function MakeNewName($prefix=''){
		$newname=eregi_replace('([[:alnum:]])(\\.[[:alnum:].]*)?$','\\1'.'-'.$prefix.$this->extension,$this->filename);		
		return $newname;
	}
	
};
?>