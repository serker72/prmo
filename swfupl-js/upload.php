<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	//загрузка фото в каталоге
	
	
	  require_once('../../classes/mmenuitem.php');
	  require_once('../../classes_s/resize_applyer_nonglob.php');
  
  		if(isset($_POST['width'])) $width=abs((int)$_POST['width']);
	else $width=120;
	
	if(isset($_POST['height'])) $height=abs((int)$_POST['height']);
	else $height=120;
  
  if(isset($_POST['prefix'])) $prefix=$_POST['prefix'];
	else $prefix='';
	
	if(isset($_POST['pprefix'])) $pprefix=$_POST['pprefix'];
	else $pprefix='';
	
	if(isset($_POST['id'])) $id=$_POST['id'];
	else $id=0;
  
	  $visib_path = '';
	  $upload_init_path=ABSPATH.'img/'.$pprefix.'pic';//$visib_path;
	  
	  $zz='';
	  
	 /* if (!isset($_POST["mid"])) {
		  echo iconv('windows-1251', 'utf-8', 'alert("Ошибка определения каталога загрузки!")');
		  
	  }else {
		  $mid=abs((int)$_POST["mid"]);
	  
		  $mi=new MmenuItem;
		  $path= $mi->RetrievePath($mid, $flaglost, $vloj);
		  
		  
		  $dirs=Array();
				  
		  foreach($path as $k=>$v) {
			  foreach($v as $kk=>$vv){
				  //если есть поле "путь", то пишем его, если нет - то айди раздела
				  if($vv['path']==''){
					  $dirs[]=$kk;
				  }else $dirs[]=SecurePath($vv['path']);
			  }
		  }
		  
		  
		  if(file_exists($upload_init_path)&&is_dir($upload_init_path)){
		  //создаем каталоги от корня
			foreach($dirs as $k=>$v){
				if($zz!='') $zz.='/';
				$zz.=$v;
				if(file_exists($upload_init_path.$zz)&&is_dir($upload_init_path.$zz)){
					
				}else mkdir($upload_init_path.$zz);
				
			}
		  }
		 // echo 'alert("'.$zz.'")';
		  
		  
	  }*/
	  
	  //обработка файла
	  
	  $resize_main=Array(
					  'doit'=>true,
					  'do_resize'=>true,
					  'resize_kind'=>0,
					  'pre'=>$prefix,
					  'resize_params'=>Array(
						  'w'=>$width,
						  'h'=>$height,
						  'cutit'=>false
					  )
				  );
	  
	 
	  $pa=new ResizeApplyer();
	  $addname=time();
	  $_FILES['Filedata']['name']=SecurePath(iconv("utf-8","windows-1251",$_FILES['Filedata']['name']));
	 
	 

	  
	  $newname1=$pa->MakePhoto($_FILES['Filedata'],$resize_main,$upload_init_path.$zz,$addname);
	 // $newname2=$pa->MakePhoto($_FILES['Filedata'],$resize_tn,$upload_init_path.$zz,$addname);
	  echo '
  
			an=document.getElementById("'.$pprefix.'uploader_'.$id.'");
					an.style.display="none";
					
					an=document.getElementById("'.$pprefix.'postuploader_'.$id.'");
					an.style.display="block";
					
					an=document.getElementById("'.$pprefix.'pic_ar_'.$id.'");
					an.value="'./*$visib_path.$zz.'/'.*/basename($newname1).'";
					
					an=document.getElementById("arom_id_'.$id.'");
					an.checked=true;
		
		';
	
	
	
	
	exit(0);
?>