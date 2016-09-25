<?
if(!isset($vmenu)) $vmenu='';
$smarty_s=new SmartyAdm;



require_once('classes/v2/menubuilder.php');

if(!isset($_dmenu_id)) $_dmenu_id=0;


$_menu=new GYDEX_MenuBuilder(4);

$menu=$_menu->BuildMenu($result['id'], $_dmenu_id);

$smarty_s->assign("menu", $menu);


 

$vmenu=$smarty_s->fetch('delivery/menu.html');
?>