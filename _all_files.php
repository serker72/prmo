<?
session_start();
Header("Cache-Control: no-store, no-cache, must-revalidate"); //��� ��������� HTTP/1.1
Header("Pragma: no-cache"); // ��� ��������� HTTP/1.1
Header("Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT"); // ���� � ����� ��������� ��������
header("Expires: " . date("r")); // ���� � ����� �����, ����� �������� ����� ��������� ����������

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
$smarty->assign("SITETITLE",'��������� ������');

$au=new AuthUser();
$result=$au->Auth();
if(($result===NULL)||(!$au->CheckOrgId())){
	header("HTTP/1.1 403 Forbidden");
	header("Status: 403 Forbidden");
	include("index.php");
	die();		
}

$log=new ActionLog;


//�������� ������
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
		
		$log->PutEntry($result['id'],'������ ����',NULL,32,NULL,'��� ����� '.SecStr($file['orig_name']));
	}
	
	header("Location: files.php");
	die();*/
}



//������ � �������
require_once('inc/header.php');
if(isset($header_res)){
	$smarty->assign('header',$header_res);
}else $smarty->assign('header','');

$smarty->display('top.html');
unset($smarty);



	include('inc/menu.php');
	
	
	
	//������������ ��������
	$smarty = new SmartyAdm;
	
	$sm=new SmartyAdm;
	
	
	//������ ������� �����������������
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
	<h1>��������� ������</h1>
<?
	$cg=new Supplier_Sh_Group;
	
	$fg=new AllFileGroup;
	
	$fg->SetStoragePath($cg->GetStoragePath());
	$fg->SetTableName($cg->GetTableName());
	
	$items=$fg->GetItemsArr($from,$to_page);
	?>
	<br />
������� <?=$cg->GetTableName()?>, ���� <?=$cg->GetStoragePath()?><br />

	<?
	$cter=1;
	foreach($items as $k=>$v){
		//���� ��� �������� � ���� ������ ����� 0,5 ��������� - ����������� �����
		$extension=0;
		if(eregi("^(.*)\\.(jpg|jpeg|jpe)$", $v['orig_name'],$P)) $extension='.jpg';
		if(eregi("^(.*)\\.(gif)$", $v['orig_name'],$P)) $extension='.gif';
		if(eregi("^(.*)\\.(png)$", $v['orig_name'],$P)) $extension='.png';	
		if(eregi("^(.*)\\.(wbm)$", $v['orig_name'],$P)) $extension='.wbm';		
		
		$can_optim=false;
		
		//echo $extension;
		if(($extension!==0)&&($v['size']>0.5)){
			?>
           <strong><?=$cter?>. ������ ����� ����������� <?=$v['orig_name']?>, ��� <?=$extension?>, ������ <?=$v['size']?>, � ���-��:<?=$v['user_id']?></strong><br />

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
				
					//������������
					//jpeg_quality image_max_size
					if($ratio>=1){
						$w=IMAGE_MAX_SIZE; $h=ceil($w/$ratio);
					}else{
						$h=IMAGE_MAX_SIZE; $w=ceil($ratio*$h);
					}
					$image2 = imagecreatetruecolor($w,$h);
					
					imagecopyresampled($image2, $image1, 0,0,0,0, $w,$h, $size[0],$size[1]);
					
					?>
					������ � ���.: <?=(int)$size[0]?>*<?=(int)$size[1]?><br />
					������ ��: <?=$w?>*<?=$h?><br />

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
        <a href="_all_files.php?from=<?=($from+5)?>">������...</a><br />
		
        <script type="text/javascript">
		$(function(){
			window.setTimeout('location.href="_all_files.php?from=<?=($from+5)?>";',1000);
		});
		</script>
        <?
	}else{
		?>
        <strong>����������� ���������!</strong><br />

        <?	
	}
	

$smarty = new SmartyAdm;

//������ � �������
require_once('inc/footer.php');
if(isset($footer_res)){
	$smarty->assign('footer',$footer_res);
}else $smarty->assign('footer','');

$smarty->display('bottom.html');
unset($smarty);
?>