<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	
	require_once('../classes/global.php');
	require_once('../classes/smarty/SmartyAj.class.php');
	require_once('../classes/authuser.php');
	//require_once('../classes/reclamfileitem.php');
	require_once('../classes/user_s_item.php');
	require_once('../classes_s/resize_applyer_nonglob.php');
	
	
	$au=new AuthUser();
	$result=$au->Auth(true,true);
	
	if($result===NULL){
		header("HTTP/1.1 403 Forbidden");
		header("Status: 403 Forbidden");
		include("403.php");
		die();		
	}


	/*if(!$au->user_rights->CheckAccess('w',62)){
		header("HTTP/1.1 403 Forbidden");
		  header("Status: 403 Forbidden");	
		  die();	
	}
	*/
	
	
	$visib_path = '/uploadphotos';
	$upload_init_path=ABSPATH.$visib_path;
	
	//MESSAGE_FILES_PATH
	//$fi=new ReclamFileItem;
	
	
	//echo $tempname;
	
	
	if(isset($_FILES['Filedata'])){
		
		$id=abs((int)$_POST["id"]);
		
		$ui=new UserSItem;
		$user=$ui->GetItemById($id);
		if($user===false) $login='unknown';
		else $login=$user['login'];
		
		$pa=new ResizeApplyer();
		$addname=time();
	  
		
		$resize_tn=Array(
					  'doit'=>true,
					  'do_resize'=>true,
					  'resize_kind'=>0,
					  'pre'=>'',
					  'resize_params'=>Array(
						  'w'=>360,
						  'h'=>360,
						  'cutit'=>false
					  )
				  );
				  
		$_FILES['Filedata']['name']=$login.'.'.pathinfo($_FILES['Filedata']['name'],PATHINFO_EXTENSION);
		
	  	$newname1=$pa->MakePhoto($_FILES['Filedata'],$resize_tn,$upload_init_path,$addname);
		
		
	   	echo '
	   		$("#photo").attr("value", "'.$visib_path.'/'.basename($newname1).'");
			$("#user_photo").attr("src", "'.$visib_path.'/'.basename($newname1).'");
			$("#user_photo_warning").css("display","block");
	   ';
		
		
		
	}
	
	exit(0);
?>