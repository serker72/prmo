<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	
	require_once('../../classes/global.php');
	
	
	if(isset($_POST['folder'])) $folder=$_POST['folder'];
	else $folder='../../img/';
	
	if(isset($_POST['id'])) $id=abs((int)$_POST['id']);
	else $id=0;
	

	
	if(isset($_POST['prefix'])) $prefix=$_POST['prefix'];
	else $prefix='';
	
	if(isset($_POST['visib_path'])) $visib_path=$_POST['visib_path'];
	else $visib_path='img/';
	
	
	$result='
		alert("Banner load error!");
	
	';
	
	if(isset($_FILES['Filedata'])){
		
		move_uploaded_file ( $_FILES['Filedata']['tmp_name'], $folder.time().'-'.SecurePath(iconv('utf-8', 'windows-1251',$_FILES['Filedata']['name']) ));

		if($id==0){
			$result= '
					
					
					an=document.getElementById("photo");
					an.value="'.$visib_path.time().'-'.SecurePath(iconv('utf-8', 'windows-1251',$_FILES['Filedata']['name'])).'";
					
					
				';
		}else
			$result= '
					
					
					an=document.getElementById("photo'.$prefix.'_'.$id.'");
					an.value="'.$visib_path.time().'-'.SecurePath(iconv('utf-8', 'windows-1251',$_FILES['Filedata']['name'])).'";
					
					an=document.getElementById("changed_'.$id.'");
					an.checked=true;

				';
		
		
	}
	
	echo $result;
	
	exit(0);
?>