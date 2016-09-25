<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	
	require_once('../classes/global.php');
	require_once('../classes/smarty/SmartyAj.class.php');
	require_once('../classes/authuser.php');
	
	require_once('../classes/posfileitem.php');
	require_once('../classes/actionlog.php');
	
	
	$au=new AuthUser();
	$result=$au->Auth(true,true);
	$log=new ActionLog;
	
	if($result===NULL){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();		
	}
	if(!$au->user_rights->CheckAccess('w',68)){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();	
	}
	
	
	if (isset($_POST["bill_id"])){
		$bill_id=abs((int)$_POST["bill_id"]);	
	}else{
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		include("404.php");
		die();			
	}
	
	$_fi=new PosFileItem;
	
	
	if(isset($_FILES['Filedata'])){
		$tempname=tempnam($_fi->GetStoragePath(), '');
		
		$extension=0;
		if(eregi("^(.*)\\.(jpg|jpeg|jpe)$", $_FILES['Filedata']['name'],$P)) $extension='.jpg';
		if(eregi("^(.*)\\.(gif)$", $_FILES['Filedata']['name'],$P)) $extension='.gif';
		if(eregi("^(.*)\\.(png)$", $_FILES['Filedata']['name'],$P)) $extension='.png';	
		if(eregi("^(.*)\\.(wbm)$", $_FILES['Filedata']['name'],$P)) $extension='.wbm';		
		
		$can_optim=false;
		
		//echo $extension;
		if($extension!==0){
			//попробовать оптимизировать фото	
			
			
			
			$image1='';
			if($extension=='.jpg') $image1 = imageCreatefromjpeg($_FILES['Filedata']['tmp_name']);
			if($extension=='.gif') $image1 = imageCreatefromgif($_FILES['Filedata']['tmp_name']);
			if($extension=='.png') $image1 = imageCreatefrompng($_FILES['Filedata']['tmp_name']);		
			if($extension=='.wbm') $image1 = imageCreatefromwbmp($_FILES['Filedata']['tmp_name']);			
			
				
			if($image1!=''){
				$size = GetImageSize($_FILES['Filedata']['tmp_name']);		
				
				//echo 'image created';
				
				
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
					
					if($extension=='.jpg') imageJpeg($image2, $tempname, JPEG_QUALITY);							
					if($extension=='.gif') imageGif($image2, $tempname);										
					if($extension=='.png') imagePng($image2, $tempname);	
					if($extension=='.wbm') imageWbmp($image2, $tempname);
					
					
				}
				
				
			}
		}
		
		
		
		if(!$can_optim) move_uploaded_file ( $_FILES['Filedata']['tmp_name'], $tempname);
		else{
			//оптимизация, сохранение	
		}
		
		
		$code=$_fi->Add(array('position_id'=>$bill_id,'user_id'=>$result['id'], 'orig_name'=>SecStr(iconv("utf-8","windows-1251", $_FILES['Filedata']['name'])), 'folder_id'=>abs((int)$_POST['folder_id']), 'filename'=>basename($tempname), 'pdate'=>time()));
		
		$log->PutEntry( $result['id'],'добавил файл номенклатуры',NULL,68,NULL,'имя файла '.SecStr(iconv("utf-8","windows-1251", $_FILES['Filedata']['name'])),$bill_id);
		
		
		$result='
		AddCode("'.$code.'");
		
		';
		echo $result;
		
		
		require_once('../classes/filecontents.php');
		
		$_ct=new FileContents(SecStr(iconv("utf-8","windows-1251", $_FILES['Filedata']['name'])), $tempname);
		
		$contents='';
		
		try {
    		$contents=$_ct->GetContents();
		} catch (Exception $e) {
			//echo 'Выброшено исключение: ',  $e->getMessage(), "\n";
		}
		
		$_fi->Edit($code, array('text_contents'=>SecStr($contents)));

	}
	
	
	
	
	exit(0);
?>