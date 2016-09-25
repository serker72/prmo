<?
session_start();
require_once('../classes/global.php');
require_once('../classes/supplieritem.php');
require_once('../classes/v2/delivery.class.php');
require_once('../classes/smarty/SmartyAj.class.php');

require_once('../classes/phpmailer/class.phpmailer.php');

$_di=new Delivery_Item;
$_ds=new Delivery_SubscriberItem;

header('Content-Type: image/png');

$user_id=abs((int)$_GET['user_id']);
$delivery_id=abs((int)$_GET['delivery_id']);

$img = imagecreatetruecolor(1, 1);
$transparent = imagecolorallocatealpha($img, 0, 0, 0,  127);
imagefill($img,  0, 0, $transparent);

imagesavealpha($img, true);

imagepng( $img );
imagedestroy($img); 


$ds=$_ds->GetItemByFields(array('user_id'=>$user_id, 'delivery_id'=>$delivery_id));
$_ds->Edit($ds['id'], array('is_viewed'=>1));


//внести статистику
$_dstat=new Delivery_SubscriberHitItem;

if($ds!==false) $_dstat->Put($ds['id'], $user_id,getenv('HTTP_X_REAL_IP')/* $_SERVER['REMOTE_ADDR']*/, time());

// Вывод изображения в броузер

//echo $user_id.' '.$delivery_id;
?>