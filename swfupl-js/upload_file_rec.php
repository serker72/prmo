<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	
	require_once('../classes/global.php');
	require_once('../classes/smarty/SmartyAj.class.php');
	require_once('../classes/authuser.php');
	require_once('../classes/reclamfileitem.php');
	
	
	$au=new AuthUser();
	$result=$au->Auth(true,true);
	
	if($result===NULL){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();		
	}

	
	//MESSAGE_FILES_PATH
	$fi=new ReclamFileItem;
	
	
	//echo $tempname;
	
	
	if(isset($_FILES['Filedata'])){
		$tempname=tempnam($fi->GetStoragePath(), '');
		
		move_uploaded_file ( $_FILES['Filedata']['tmp_name'], $tempname);
		
		
		$result='
		AddCode("'.basename($tempname).'", "'.$_FILES['Filedata']['name'].'");
		
		
		';
		echo $result;
	}
	
	
	exit(0);
?>