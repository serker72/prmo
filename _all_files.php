<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //для протокола HTTP/1.1
Header("Pragma: no-cache"); // для протокола HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // дата и время генерации страницы
header("Expires: " . date("r")); // дата и время время, когда страница будет считаться устаревшей

require_once('classes/global.php');
require_once('classes/authuser.php');
require_once('classes/smarty/SmartyAdm.class.php');
require_once('classes/smarty/Smarty.class.php');
require_once('classes/discr_table_objects.php');
require_once('classes/actionlog.php');
require_once('classes/_all_fileitem.php');
require_once('classes/_all_filegroup.php');


require_once('classes/accfilegroup.php');
require_once('classes/billfilegroup.php');
require_once('classes/contractgroup.php');
require_once('classes/contractorggroup.php');
require_once('classes/contractuchgroup.php');
require_once('classes/filegroup.php');
require_once('classes/filemessagegroup.php');
require_once('classes/invfilegroup.php');
require_once('classes/isfilegroup.php');
require_once('classes/kvfilegroup.php');
require_once('classes/payfilegroup.php');
require_once('classes/posfilegroup.php');
require_once('classes/sh_i_filegroup.php');
require_once('classes/storagefilegroup.php');
require_once('classes/trustfilegroup.php');
require_once('classes/wffilegroup.php');
require_once('classes/supplier_sh_group.php');

require_once('classes/contractuchgroup.php');


$smarty = new SmartyAdm;
$smarty->assign("SITETITLE",'Конвертер файлов');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


//удаление файлов
if(isset($_GET['action'])&&($_GET['action']==2)){
	/*if(!$au->user_rights->CheckAccess('w',32)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
	if(isset($_GET['id'])) $id=abs((int)$_GET['id']);
	else{
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			include("404.php");
			die();	
	}
	
	$_file=new FilePoItem;
	$file=$_file->GetItemById($id);
	
	if($file!==false){
		$_file->Del($id);
		
		$log->PutEntry($result['id'],'удалил файл',NULL,32,NULL,'имя файла '.SecStr($file['orig_name']));
	}
	
	header("Location: files.php");
	die();*/
}



//работа с хедером
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	
	
	//демонстрация страницы
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//строим вкладку администрирования
	/*$sm->assign('has_admin',($au->user_rights->CheckAccess('x',28)||
							$au->user_rights->CheckAccess('x',30)||
							$au->user_rights->CheckAccess('x',32)||
							$au->user_rights->CheckAccess('x',60)
							)
				);
	$dto=new DiscrTableObjects($result['id'],array('28','30','32','60'));
	$admin=$dto->Draw('files.php','admin/admin_objects.html');
	$sm->assign('admin',$admin);
	*/
	
	
	
	
	
	
	
	if(isset($_GET['from'])) $from=abs((int)$_GET['from']);
	else $from=0;
	
	if(isset($_GET['to_page'])) $to_page=abs((int)$_GET['to_page']);
	else $to_page=5;
	
	
	/*
	require_once('classes/accfilegroup.php');
require_once('classes/billfilegroup.php');
require_once('classes/contractgroup.php');
require_once('classes/contractorggroup.php');
require_once('classes/contractuchgroup.php');
require_once('classes/filegroup.php');
require_once('classes/filemessagegroup.php');
require_once('classes/invfilegroup.php');
require_once('classes/isfilegroup.php');
require_once('classes/kvfilegroup.php');
require_once('classes/payfilegroup.php');
require_once('classes/posfilegroup.php');
require_once('classes/sh_i_filegroup.php');
require_once('classes/storagefilegroup.php');
require_once('classes/supplier_sh_group.php');
require_once('classes/trustfilegroup.php');
require_once('classes/wffilegroup.php');
	
	*/
	?>
	<h1>Конвертер файлов</h1>
<?
	$cg=new Supplier_Sh_Group;
	
	$fg=new AllFileGroup;
	
	$fg->SetStoragePath($cg->GetStoragePath());
	$fg->SetTableName($cg->GetTableName());
	
	$items=$fg->GetItemsArr($from,$to_page);
	?>
	<br />
Таблица <?=$cg->GetTableName()?>, путь <?=$cg->GetStoragePath()?><br />

	<?
	$cter=1;
	foreach($items as $k=>$v){
		//если это КАРТИНКА и если размер более 0,5 мегабайта - попробовать ужать
		$extension=0;
		if(eregi("^(.*)\\.(jpg|jpeg|jpe)$", $v['orig_name'],$P)) $extension='.jpg';
		if(eregi("^(.*)\\.(gif)$", $v['orig_name'],$P)) $extension='.gif';
		if(eregi("^(.*)\\.(png)$", $v['orig_name'],$P)) $extension='.png';	
		if(eregi("^(.*)\\.(wbm)$", $v['orig_name'],$P)) $extension='.wbm';		
		
		$can_optim=false;
		
		//echo $extension;
		if(($extension!==0)&&($v['size']>0.5)){
			?>
           <strong><?=$cter?>. пробую сжать изображение <?=$v['orig_name']?>, тип <?=$extension?>, размер <?=$v['size']?>, № док-та:<?=$v['user_id']?></strong><br />

            <?
			$image1='';
			if($extension=='.jpg') $image1 = imageCreatefromjpeg($cg->GetStoragePath().$v['filename']);
			if($extension=='.gif') $image1 = imageCreatefromgif($cg->GetStoragePath().$v['filename']);
			if($extension=='.png') $image1 = imageCreatefrompng($cg->GetStoragePath().$v['filename']);		
			if($extension=='.wbm') $image1 = imageCreatefromwbmp($cg->GetStoragePath().$v['filename']);			
			
				
			if($image1!=''){
				$size = GetImageSize($cg->GetStoragePath().$v['filename']);	
				if(($size)&&( ((int)$size[0]>IMAGE_MAX_SIZE)||((int)$size[1]>IMAGE_MAX_SIZE)    )){
					$ratio = (int)$size[0]/(int)$size[1];
					
					/*echo 'size defined';
					var_dump($size);*/
					$can_optim=true;
				
					//оптимизируем
					//jpeg_quality image_max_size
					if($ratio>=1){
						$w=IMAGE_MAX_SIZE; $h=ceil($w/$ratio);
					}else{
						$h=IMAGE_MAX_SIZE; $w=ceil($ratio*$h);
					}
					$image2 = imagecreatetruecolor($w,$h);
					
					imagecopyresampled($image2, $image1, 0,0,0,0, $w,$h, $size[0],$size[1]);
					
					?>
					размер в пкс.: <?=(int)$size[0]?>*<?=(int)$size[1]?><br />
					сжимаю до: <?=$w?>*<?=$h?><br />

					<?
					if($extension=='.jpg') imageJpeg($image2, $cg->GetStoragePath().$v['filename'], JPEG_QUALITY);							
					if($extension=='.gif') imageGif($image2, $cg->GetStoragePath().$v['filename']);										
					if($extension=='.png') imagePng($image2, $cg->GetStoragePath().$v['filename']);	
					if($extension=='.wbm') imageWbmp($image2, $cg->GetStoragePath().$v['filename']);
					
					
				}
			}
			$cter++;
		}
		
	}
	
	if(count($items)>0){
		//echo 'more...';?>
        <a href="_all_files.php?from=<?=($from+5)?>">Дальше...</a><br />
		
        <script type="text/javascript">
		$(function(){
			window.setTimeout('location.href="_all_files.php?from=<?=($from+5)?>";',1000);
		});
		</script>
        <?
	}else{
		?>
        <strong>Конвертация завершена!</strong><br />

        <?	
	}
	

$smarty = new SmartyAdm;

//работа с футером
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>