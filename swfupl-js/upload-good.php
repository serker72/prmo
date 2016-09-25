<?php
	// Work-around for setting up a session because Flash Player doesn't send the cookies
	if (isset($_POST["PHPSESSID"])) {
		session_id($_POST["PHPSESSID"]);
	}
	session_start();
	/*загрузка фото к товару из страницы редактирвоани€ товара*/	
	
	  require_once('../../classes/mmenuitem.php');
	  require_once('../../classes_s/resize_applyer_nonglob.php');
  
  
  	if(isset($_POST['width'])) $width=abs((int)$_POST['width']);
	else $width=120;
	
	if(isset($_POST['height'])) $height=abs((int)$_POST['height']);
	else $height=120;
  
  if(isset($_POST['prefix'])) $prefix=$_POST['prefix'];
	else $prefix='';
	
	if(isset($_POST['folder_prefix'])) $folder_prefix=$_POST['folder_prefix'];
	else $folder_prefix='';
	
	if(isset($_POST['div_name'])) $div_name=$_POST['div_name'];
	else $div_name='';
	
  
	  $visib_path = '';//'img';
	  $upload_init_path=ABSPATH.'img/'.$folder_prefix.'pic';//$visib_path;
	  
	  $zz='';
	  
	  /*if (!isset($_POST["mid"])) {
		  echo iconv('windows-1251', 'utf-8', 'alert("ќшибка определени€ каталога загрузки!")');
		  
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
		  //создаем каталоги от корн€
			foreach($dirs as $k=>$v){
				$zz.='/'.$v;
				if(file_exists($upload_init_path.$zz)&&is_dir($upload_init_path.$zz)){
					
				}else mkdir($upload_init_path.$zz);
				
			}
		  }
		 //echo 'alert("'.$zz.'")';
		  
		  
	  }*/
	  
	  //обработка файла
	  
	 
	  
	  $resize_tn=Array(
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
	 
	 
	  
	  //$newname1=$pa->MakePhoto($_FILES['Filedata'],$resize_main,$upload_init_path.$zz,$addname);
	  $newname2=$pa->MakePhoto($_FILES['Filedata'],$resize_tn,$upload_init_path.$zz,$addname);
	  echo '
  
		
		$("#'.$div_name.'").attr("value", "'./*$visib_path.$zz.'/'.*/basename($newname2).'");
		
	
		
		';
	
	
	
	exit(0);
?>