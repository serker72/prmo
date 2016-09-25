<?
//requires GD 2.0.1 or higher
//note about gif
class ThumbnailImage{
	private $image;
	//not applicable to gif or png
	private $quality = 80;
	private $mimetype;
	private $imageproperties;
	private $initialfilesize;
////////////////////////////////////////////////////////
//constructor
////////////////////////////////////////////////////////
	public function __construct($file, $thumbnailsize = 120){
		//check path
		is_file($file) or die ("File: $file doesn't exist.");
		$this->initialfilesize = filesize($file);
		$this->imageproperties = getimagesize($file) or die ("Incorrect file type.");
		// new function image_type_to_mime_type
		$this->mimetype = image_type_to_mime_type($this->imageproperties[2]);	
		//create image
		switch($this->imageproperties[2]){
			case IMAGETYPE_JPEG:
				$this->image = imagecreatefromjpeg($file);	
				break;
			case IMAGETYPE_GIF:	
				$this->image = imagecreatefromgif($file);
				break;
			case IMAGETYPE_PNG:
				$this->image = imagecreatefrompng($file);
				break;
			default:
				die("Couldn't create image.");
		}
		$this->createThumb($thumbnailsize);
	}
////////////////////////////////////////////////////////
//destructor
////////////////////////////////////////////////////////
	public function __destruct(){
		if(isset($this->image)){
			imagedestroy($this->image);			
		}
	}
////////////////////////////////////////////////////////
//public methods
////////////////////////////////////////////////////////
	public function getImage(){
		header("Content-type: $this->mimetype");
		switch($this->imageproperties[2]){
			case IMAGETYPE_JPEG:
				imagejpeg($this->image,"",$this->quality);
				break;
			case IMAGETYPE_GIF:
				imagegif($this->image);
				break;
			case IMAGETYPE_PNG:
				imagepng($this->image);
				break;
			default:
				die("Couldn't create image.");
		}
	}
////////////////////////////////////////////////////////
	public function getMimeType(){
  
		return $this->mimetype;
	}
////////////////////////////////////////////////////////
	public function getQuality(){
		$quality = null;
		if($this->imageproperties[2] == IMAGETYPE_JPEG){
			$quality = $this->quality;
		}
		return $quality;
	}
////////////////////////////////////////////////////////
	public function setQuality($quality){
		if($quality > 100 || $quality  <  1){
			$quality = 85;
    }
		if($this->imageproperties[2] == IMAGETYPE_JPEG){
			$this->quality = $quality;
		}
	}
////////////////////////////////////////////////////////
	public function getInitialFileSize(){	
		return $this->initialfilesize;
	}
////////////////////////////////////////////////////////
//private methods
////////////////////////////////////////////////////////
	private function createThumb($thumbnailsize){
		//array elements
		$srcW = $this->imageproperties[0];
		$srcH = $this->imageproperties[1];
		//only adjust if larger than reduction size
		if($srcW >$thumbnailsize || $srcH > $thumbnailsize){
			//$reduction = $this->calculateReduction($thumbnailsize);
			
		
		
				$ratio=$srcW/$srcH;
				if($ratio>=1){
				//горизонтальная - ширина=120 80
					if($srcW<$thumbnailsize) $desW=$srcW;
					else $desW = $thumbnailsize;
					$desH = ceil($desW*$srcH/$srcW);
				}else{
				//вертикальная - высота = 90 60
					if($srcH<($thumbnailsize-10)) $desH=$srcH;
					else $desH = $thumbnailsize-10;
					$desW = ceil($srcW*$desH/$srcH);
				}		
				
				
				
		/*
		if($srcW >$thumbnailsize){
			$reduction=$srcW/$srcH;
			$desW=$thumbnailsize;
			$desH=ceil($desW/$reduction);
		}else{
			$desW=$srcW;
			$desH=$srcH;			
		}*/
			//get proportions
  		/*$desW = ($srcW/$reduction);
  		$desH = ($srcH/$reduction);								*/

			$copy = imagecreatetruecolor($desW, $desH);			
			imagecopyresampled($copy,$this->image,0,0,0,0,$desW, $desH, $srcW, $srcH)
				 or die ("Image copy failed.");			
			//destroy original
			imagedestroy($this->image);
			$this->image = $copy;			
		}
	}
////////////////////////////////////////////////////////
	private function calculateReduction($thumbnailsize){
		//adjust
		$srcW = $this->imageproperties[0];
		$srcH = $this->imageproperties[1];
  	if($srcW < $srcH){
  		$reduction = round($srcH/$thumbnailsize);
  	}else{  			
  		$reduction = round($srcW/$thumbnailsize);
  	}
		return $reduction;
	}
}//end class
////////////////////////////////////////////////////////
?>
