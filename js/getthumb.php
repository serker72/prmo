<?
//this file will be the src for an img tag
require_once('../classes/global.php');
require_once('../classes/ThumbnailImage.php');

$path = @$_GET['path'];
$maxsize = @$_GET['size'];
if(!isset($maxsize)){
	$maxsize=50;
}
if(isset($path)){
  $thumb = new ThumbNailImage(ABSPATH.$path, $maxsize);	
  $thumb->getImage();
}
?>
